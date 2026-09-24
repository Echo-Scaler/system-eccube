<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\EventListener;

use Customize\Entity\CustomerFavoriteProductHistory;
use Customize\Repository\CustomerFavoriteProductHistoryRepository;
use Eccube\Entity\Customer;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class FavoriteEventListener
 *
 * အကြိုက်ဆုံးပစ္စည်း (Favorite Product) နှင့် သက်ဆိုင်သော Event များကို စောင့်ကြည့်ဖမ်းယူပြီး History Table ထဲသို့ မှတ်တမ်းတင်ပေးသော Event Subscriber ဖြစ်ပါသည်။
 * (Event Subscriber for listening to Favorite Product events in EC-CUBE)
 */
class FavoriteEventListener implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerFavoriteProductHistoryRepository
     */
    protected $historyRepository;

    /**
     * @var Security
     */
    protected $security;

    /**
     * FavoriteEventListener constructor.
     *
     * @param LoggerInterface $logger
     * @param CustomerFavoriteProductHistoryRepository $historyRepository
     * @param Security $security
     */
    public function __construct(
        LoggerInterface $logger,
        CustomerFavoriteProductHistoryRepository $historyRepository,
        Security $security
    ) {
        $this->logger = $logger;
        $this->historyRepository = $historyRepository;
        $this->security = $security;
    }

    /**
     * စောင့်ကြည့်မည့် Event များနှင့် ခေါ်ယူမည့် Method များကို သတ်မှတ်ခြင်း
     * (Subscribe to EC-CUBE Favorite Events)
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EccubeEvents::FRONT_PRODUCT_FAVORITE_ADD_INITIALIZE => 'onFavoriteAddInitialize',
            EccubeEvents::FRONT_PRODUCT_FAVORITE_ADD_COMPLETE => 'onFavoriteAddComplete',
            EccubeEvents::FRONT_MYPAGE_MYPAGE_DELETE_COMPLETE => 'onMypageDeleteComplete',
        ];
    }

    /**
     * Favorite အဖြစ် စတင်မထည့်သွင်းမီ လုပ်ဆောင်ချက်
     * (Called before adding a product to favorites)
     *
     * @param EventArgs $event
     */
    public function onFavoriteAddInitialize(EventArgs $event)
    {
        $Product = $event->getArgument('Product');
        if ($Product) {
            $this->logger->info(sprintf('[Favorite] Product Favorite Add Initialized: Product ID=%d, Name=%s', $Product->getId(), $Product->getName()));
        }
    }

    /**
     * Favorite အဖြစ် အောင်မြင်စွာ ထည့်သွင်းပြီးချိန် လုပ်ဆောင်ချက်
     * (Called after successfully adding a product to favorites)
     *
     * @param EventArgs $event
     */
    public function onFavoriteAddComplete(EventArgs $event)
    {
        $Product = $event->getArgument('Product');
        $Customer = $this->security->getUser();

        if ($Product && $Customer instanceof Customer) {
            // History Record အသစ် ထည့်သွင်းခြင်း (Action = register)
            $this->historyRepository->addHistory(
                $Customer,
                $Product,
                CustomerFavoriteProductHistory::ACTION_REGISTER
            );

            $this->logger->info(sprintf(
                '[Favorite History] Registered: Customer ID=%d, Product ID=%d, Name=%s',
                $Customer->getId(),
                $Product->getId(),
                $Product->getName()
            ));
        }
    }

    /**
     * Mypage မှ Favorite ပစ္စည်းအား ဖျက်လိုက်သည့်အခါ လုပ်ဆောင်ချက်
     * (Called after deleting a favorite product from Mypage)
     *
     * @param EventArgs $event
     */
    public function onMypageDeleteComplete(EventArgs $event)
    {
        $Customer = $event->getArgument('Customer');
        $CustomerFavoriteProduct = $event->getArgument('CustomerFavoriteProduct');

        if ($Customer instanceof Customer && $CustomerFavoriteProduct && $CustomerFavoriteProduct->getProduct()) {
            $Product = $CustomerFavoriteProduct->getProduct();

            // History Record အသစ် ထည့်သွင်းခြင်း (Action = remove)
            $this->historyRepository->addHistory(
                $Customer,
                $Product,
                CustomerFavoriteProductHistory::ACTION_REMOVE
            );

            $this->logger->info(sprintf(
                '[Favorite History] Removed: Customer ID=%d, Product ID=%d, Favorite ID=%d',
                $Customer->getId(),
                $Product->getId(),
                $CustomerFavoriteProduct->getId()
            ));
        }
    }
}
