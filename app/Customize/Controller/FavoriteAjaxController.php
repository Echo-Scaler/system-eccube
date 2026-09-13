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

namespace Customize\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class FavoriteAjaxController
 *
 * အကြိုက်ဆုံးပစ္စည်း (Favorite Product) အား စာမျက်နှာ Reload မဖြစ်စေဘဲ AJAX ဖြင့်
 * အဖွင့်/အပိတ် (Toggle Add/Remove) ပြုလုပ်ပေးသည့် Controller ဖြစ်ပါသည်။
 * (AJAX Controller for toggling favorite product status without full page reload)
 */
class FavoriteAjaxController extends AbstractController
{
    /**
     * @var CustomerFavoriteProductRepository
     */
    protected $customerFavoriteProductRepository;

    /**
     * @var BaseInfoRepository
     */
    protected $baseInfoRepository;

    /**
     * FavoriteAjaxController constructor.
     *
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     * @param BaseInfoRepository $baseInfoRepository
     */
    public function __construct(
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        BaseInfoRepository $baseInfoRepository
    ) {
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
        $this->baseInfoRepository = $baseInfoRepository;
    }

    /**
     * AJAX ဖြင့် Favorite အား Add သို့မဟုတ် Remove (Toggle) ပြုလုပ်ခြင်း
     * (Toggle Favorite status via AJAX POST)
     *
     * @Route("/products/ajax_favorite/{id}", name="customize_product_ajax_favorite", requirements={"id" = "\d+"}, methods={"POST"})
     *
     * @param Request $request
     * @param Product $Product
     * @return JsonResponse
     */
    public function toggleFavorite(Request $request, Product $Product)
    {
        // ၁။ BaseInfo တွင် Favorite Feature ဖွင့်ထားခြင်း ရှိ/မရှိ စစ်ဆေးခြင်း
        $BaseInfo = $this->baseInfoRepository->get();
        if (!$BaseInfo->isOptionFavoriteProduct()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Favorite feature is disabled.',
            ], 400);
        }

        // ၂။ Product သည် အများမြင်သာသော အခြေအနေ (Display Show) ဖြစ်မဖြစ် စစ်ဆေးခြင်း
        if ($Product->getStatus()->getId() !== ProductStatus::DISPLAY_SHOW) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Product not found or not active.',
            ], 404);
        }

        // ၃။ Login ဝင်ထားခြင်း ရှိ/မရှိ စစ်ဆေးခြင်း (Authentication Check)
        if (!$this->isGranted('ROLE_USER')) {
            $loginUrl = $this->generateUrl('mypage_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
            return new JsonResponse([
                'success' => false,
                'require_login' => true,
                'login_url' => $loginUrl,
                'message' => 'Please login to add this product to your favorites.',
            ], 401);
        }

        /** @var \Eccube\Entity\Customer $Customer */
        $Customer = $this->getUser();

        // ၄။ Favorite ရှိပြီးသား ဟုတ်/မဟုတ် စစ်ဆေးပြီး Toggle ပြုလုပ်ခြင်း
        $isFavorite = $this->customerFavoriteProductRepository->isFavorite($Customer, $Product);

        if ($isFavorite) {
            // ရှိပြီးသားဖြစ်ပါက Favorite မှ ဖယ်ရှားမည် (Remove Favorite)
            $CustomerFavoriteProduct = $this->customerFavoriteProductRepository->findOneBy([
                'Customer' => $Customer,
                'Product' => $Product,
            ]);

            if ($CustomerFavoriteProduct) {
                $this->customerFavoriteProductRepository->delete($CustomerFavoriteProduct);

                $event = new EventArgs([
                    'Customer' => $Customer,
                    'CustomerFavoriteProduct' => $CustomerFavoriteProduct,
                ], $request);
                $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_MYPAGE_MYPAGE_DELETE_COMPLETE);
            }

            return new JsonResponse([
                'success' => true,
                'is_favorite' => false,
                'product_id' => $Product->getId(),
                'message' => 'Removed from favorites.',
            ]);
        } else {
            // မရှိသေးပါက Favorite ထဲသို့ အသစ်ထည့်သွင်းမည် (Add Favorite)
            $this->customerFavoriteProductRepository->addFavorite($Customer, $Product);

            $event = new EventArgs([
                'Product' => $Product,
            ], $request);
            $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_PRODUCT_FAVORITE_ADD_COMPLETE);

            return new JsonResponse([
                'success' => true,
                'is_favorite' => true,
                'product_id' => $Product->getId(),
                'message' => 'Added to favorites.',
            ]);
        }
    }

    /**
     * AJAX ဖြင့် Product တစ်ခု၏ လက်ရှိ Favorite အခြေအနေကို စစ်ဆေးခြင်း
     * (Get current favorite status of a product via AJAX GET)
     *
     * @Route("/products/ajax_favorite_status/{id}", name="customize_product_ajax_favorite_status", requirements={"id" = "\d+"}, methods={"GET"})
     *
     * @param Product $Product
     * @return JsonResponse
     */
    public function getFavoriteStatus(Product $Product)
    {
        $isLoggedIn = $this->isGranted('ROLE_USER');
        $isFavorite = false;

        if ($isLoggedIn) {
            /** @var \Eccube\Entity\Customer $Customer */
            $Customer = $this->getUser();
            $isFavorite = $this->customerFavoriteProductRepository->isFavorite($Customer, $Product);
        }

        return new JsonResponse([
            'is_logged_in' => $isLoggedIn,
            'is_favorite' => $isFavorite,
            'product_id' => $Product->getId(),
        ]);
    }
}
