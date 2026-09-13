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

use Customize\Service\FavoriteService;
use Eccube\Entity\Customer;
use Eccube\Entity\Product;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class FavoriteTwigExtension
 *
 * Twig Template များအတွင်း Favorite စစ်ဆေးခြင်း၊ အခြား User များမှ Favorite ပြုလုပ်ထားသော
 * အရေအတွက်နှင့် အမှတ်အသား (Indicator / Badge) များ ပြသနိုင်ရန် ထောက်ပံ့ပေးသော Twig Extension ဖြစ်ပါသည်။
 * (Twig extension for checking favorites and displaying other users' favorite indicators/badges)
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
     * @var FavoriteService
     */
    protected $favoriteService;

    /**
     * @var array|null
     */
    protected $favoriteProductIds = null;

    /**
     * FavoriteTwigExtension constructor.
     *
     * @param Security $security
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     * @param FavoriteService $favoriteService
     */
    public function __construct(
        Security $security,
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        FavoriteService $favoriteService
    ) {
        $this->security = $security;
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
        $this->favoriteService = $favoriteService;
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
            new TwigFunction('favorite_count', [$this, 'getFavoriteCount']),
            new TwigFunction('other_favorite_count', [$this, 'getOtherUsersFavoriteCount']),
            new TwigFunction('is_favorited_by_others', [$this, 'isFavoritedByOthers']),
            new TwigFunction('preload_favorite_counts', [$this, 'preloadFavoriteCounts']),
        ];
    }

    /**
     * လက်ရှိ Login ဝင်ထားသော User မှ Favorite ပြုလုပ်ထားခြင်း ရှိ/မရှိ စစ်ဆေးခြင်း
     * (Check if current logged-in customer has favorited this product)
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

    /**
     * ကုန်ပစ္စည်းတစ်ခုအား Favorite ပြုလုပ်ထားသော စုစုပေါင်း အရေအတွက်
     * (Get total favorite count for a product)
     *
     * @param Product|int|null $product
     * @return int
     */
    public function getFavoriteCount($product): int
    {
        return $this->favoriteService->getFavoriteCount($product);
    }

    /**
     * အခြား User များမှ Favorite ပြုလုပ်ထားသော အရေအတွက် (မိမိအကောင့်မှအပ)
     * (Get count of other users who favorited this product)
     *
     * @param Product|int|null $product
     * @return int
     */
    public function getOtherUsersFavoriteCount($product): int
    {
        $user = $this->security->getUser();
        $customer = $user instanceof Customer ? $user : null;
        return $this->favoriteService->getOtherUsersFavoriteCount($product, $customer);
    }

    /**
     * အခြား User များမှ Favorite ပြုလုပ်ထားခြင်း ရှိ/မရှိ စစ်ဆေးခြင်း (အမှတ်အသား Badge ပြသရန်)
     * (Check if other users have favorited this product)
     *
     * @param Product|int|null $product
     * @return bool
     */
    public function isFavoritedByOthers($product): bool
    {
        $user = $this->security->getUser();
        $customer = $user instanceof Customer ? $user : null;
        return $this->favoriteService->isFavoritedByOthers($product, $customer);
    }

    /**
     * Product List စာမျက်နှာရှိ ပစ္စည်းအားလုံးအတွက် Favorite Counts များကို ကြိုတင် Batch Load ပြုလုပ်ခြင်း
     * (Preload favorite counts for a list of products to optimize performance)
     *
     * @param iterable|array $products
     */
    public function preloadFavoriteCounts($products)
    {
        $ids = [];
        foreach ($products as $item) {
            if ($item instanceof Product) {
                $ids[] = $item->getId();
            } elseif (is_numeric($item)) {
                $ids[] = (int) $item;
            }
        }
        $this->favoriteService->preloadCountsForProductIds($ids);
    }
}
