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

namespace Customize\Twig;

use Eccube\Entity\Customer;
use Eccube\Entity\Product;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class FavoriteTwigExtension
 *
 * Twig Template များအတွင်း (အထူးသဖြင့် Product List စာမျက်နှာတွင်) ကုန်ပစ္စည်းတစ်ခုအား
 * ဝယ်ယူသူမှ Favorite ထည့်ထားခြင်း ရှိ/မရှိ စစ်ဆေးပေးသည့် Twig Function Extension ဖြစ်ပါသည်။
 * (Twig extension to check favorite status of products in templates like Product List)
 */
class FavoriteTwigExtension extends AbstractExtension
{
    /**
     * @var Security
     */
    protected $security;

    /**
     * @var CustomerFavoriteProductRepository
     */
    protected $customerFavoriteProductRepository;

    /**
     * @var array|null
     */
    protected $favoriteProductIds = null;

    /**
     * FavoriteTwigExtension constructor.
     *
     * @param Security $security
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     */
    public function __construct(
        Security $security,
        CustomerFavoriteProductRepository $customerFavoriteProductRepository
    ) {
        $this->security = $security;
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
    }

    /**
     * Twig ထဲတွင် အသုံးပြုနိုင်သော Function များကို သတ်မှတ်ပေးခြင်း
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('is_favorite', [$this, 'isFavorite']),
        ];
    }

    /**
     * Product သို့မဟုတ် Product ID အား Favorite ထဲတွင် ရှိမရှိ စစ်ဆေးခြင်း
     * (Check if a product is in customer's favorites with in-memory caching)
     *
     * @param Product|int|null $product
     * @return bool
     */
    public function isFavorite($product)
    {
        if (!$product) {
            return false;
        }

        $user = $this->security->getUser();
        if (!$user instanceof Customer) {
            return false;
        }

        $productId = $product instanceof Product ? $product->getId() : (int) $product;

        // Performance Optimization:
        // Product List တွင် ပစ္စည်းတိုင်းအတွက် Query ထပ်ခါထပ်ခါ မပစ်ရစေရန်
        // လက်ရှိ Request အတွင်း Customer ၏ Favorite Product IDs များကို တစ်ကြိမ်သာ Load လုပ်ပြီး Cache ပြုလုပ်ခြင်း
        if ($this->favoriteProductIds === null) {
            $favorites = $this->customerFavoriteProductRepository->findBy(['Customer' => $user]);
            $this->favoriteProductIds = [];
            foreach ($favorites as $favorite) {
                if ($favorite->getProduct()) {
                    $this->favoriteProductIds[$favorite->getProduct()->getId()] = true;
                }
            }
        }

        return isset($this->favoriteProductIds[$productId]);
    }
}
