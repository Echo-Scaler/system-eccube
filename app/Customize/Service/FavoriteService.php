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

namespace Customize\Service;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Customer;
use Eccube\Entity\CustomerFavoriteProduct;
use Eccube\Entity\Product;

/**
 * Class FavoriteService
 *
 * အကြိုက်ဆုံး ကုန်ပစ္စည်းများနှင့် သက်ဆိုင်သော Business Logic များ၊ စာရင်းကောက်ယူမှုများနှင့်
 * အခြား User များမှ Favorite ပြုလုပ်ထားမှု အခြေအနေများကို စီမံတွက်ချက်ပေးသော Service ဖြစ်ပါသည်။
 * (Service for managing favorite product statistics, counts, and other users' favorite indicators)
 */
class FavoriteService
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * In-memory cache for product favorite counts
     * @var array
     */
    protected $countCache = [];

    /**
     * FavoriteService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * ကုန်ပစ္စည်းတစ်ခုအား Favorite ပြုလုပ်ထားသော စုစုပေါင်း User အရေအတွက်ကို ရယူခြင်း
     * (Get total favorite count for a product)
     *
     * @param Product|int $product
     * @return int
     */
    public function getFavoriteCount($product): int
    {
        if (!$product) {
            return 0;
        }

        $productId = $product instanceof Product ? $product->getId() : (int) $product;

        if (isset($this->countCache[$productId])) {
            return $this->countCache[$productId];
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(cf.id)')
            ->from(CustomerFavoriteProduct::class, 'cf')
            ->where('cf.Product = :productId')
            ->setParameter('productId', $productId);

        $count = (int) $qb->getQuery()->getSingleScalarResult();
        $this->countCache[$productId] = $count;

        return $count;
    }

    /**
     * မိမိမှအပ အခြား User များမှ Favorite ပြုလုပ်ထားသော အရေအတွက်ကို ရယူခြင်း
     * (Get favorite count by OTHER users, excluding the current customer if they already favorited)
     *
     * @param Product|int $product
     * @param Customer|null $currentCustomer
     * @return int
     */
    public function getOtherUsersFavoriteCount($product, ?Customer $currentCustomer = null): int
    {
        $totalCount = $this->getFavoriteCount($product);
        if ($totalCount === 0) {
            return 0;
        }

        if ($currentCustomer) {
            $productId = $product instanceof Product ? $product->getId() : (int) $product;
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('COUNT(cf.id)')
                ->from(CustomerFavoriteProduct::class, 'cf')
                ->where('cf.Product = :productId AND cf.Customer = :customerId')
                ->setParameters([
                    'productId' => $productId,
                    'customerId' => $currentCustomer->getId(),
                ]);

            $isCurrentUserFav = (int) $qb->getQuery()->getSingleScalarResult() > 0;
            if ($isCurrentUserFav) {
                return max(0, $totalCount - 1);
            }
        }

        return $totalCount;
    }

    /**
     * ကုန်ပစ္စည်းတစ်ခုအား အခြား User များမှ Favorite ပြုလုပ်ထားခြင်း ရှိ/မရှိ စစ်ဆေးခြင်း
     * (Check if a product is favorited by other users)
     *
     * @param Product|int $product
     * @param Customer|null $currentCustomer
     * @return bool
     */
    public function isFavoritedByOthers($product, ?Customer $currentCustomer = null): bool
    {
        return $this->getOtherUsersFavoriteCount($product, $currentCustomer) > 0;
    }

    /**
     * Product ID အများအပြားအတွက် Favorite Counts များကို တစ်ကြိမ်တည်းဖြင့် Batch Query လုပ်ဆောင်ခြင်း
     * (Batch fetch favorite counts for multiple products to prevent N+1 query on Product List page)
     *
     * @param array $productIds
     * @return array [product_id => count]
     */
    public function preloadCountsForProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        // Filter out already cached IDs
        $uncachedIds = array_diff($productIds, array_keys($this->countCache));
        if (!empty($uncachedIds)) {
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('IDENTITY(cf.Product) AS product_id, COUNT(cf.id) AS fav_count')
                ->from(CustomerFavoriteProduct::class, 'cf')
                ->where($qb->expr()->in('cf.Product', ':productIds'))
                ->setParameter('productIds', $uncachedIds)
                ->groupBy('cf.Product');

            $results = $qb->getQuery()->getResult();
            foreach ($uncachedIds as $id) {
                $this->countCache[$id] = 0; // Default 0
            }
            foreach ($results as $row) {
                $this->countCache[(int) $row['product_id']] = (int) $row['fav_count'];
            }
        }

        return $this->countCache;
    }
}
