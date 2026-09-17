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

namespace Customize\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Eccube\Entity\CustomerFavoriteProduct;
use Eccube\Entity\Product;
use Eccube\Repository\AbstractRepository;
use Eccube\Util\StringUtil;

/**
 * Class FavouriteProductRepository
 *
 * EC-CUBE 4.3 Standard Repository for Favourite Products
 */
class FavouriteProductRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Favorite အများဆုံး ကုန်ပစ္စည်းများ စာရင်းနှင့် CSV Export အတွက် Search QueryBuilder
     * (MySQL 8 ONLY_FULL_GROUP_BY 100% Safe & Standard Compliant)
     *
     * @param array $searchData Search form parameters (id, name, customer_name, favorite_count_min, favorite_count_max)
     * @return QueryBuilder
     */
    public function getFavouriteDb(array $searchData = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');
        $qb->addSelect('(SELECT COUNT(cfp.id) FROM ' . CustomerFavoriteProduct::class . ' cfp WHERE cfp.Product = p) AS HIDDEN favorite_count')
            ->where('(SELECT COUNT(cfp2.id) FROM ' . CustomerFavoriteProduct::class . ' cfp2 WHERE cfp2.Product = p) > 0');

        // ၁။ Product ID Search (Single ID သို့မဟုတ် Comma/Space separated IDs)
        if (!empty($searchData['id']) && StringUtil::isNotBlank($searchData['id'])) {
            $ids = preg_split('/[\s,]+/', $searchData['id'], -1, PREG_SPLIT_NO_EMPTY);
            $qb->andWhere('p.id IN (:ids)')
               ->setParameter('ids', $ids);
        }

        // ၂။ Product Name / Code Search
        if (!empty($searchData['name']) && StringUtil::isNotBlank($searchData['name'])) {
            $qb->andWhere('p.name LIKE :pname OR p.code_min LIKE :pname')
               ->setParameter('pname', '%' . $searchData['name'] . '%');
        }

        // ၃။ Customer Name Search (အကြိုက်ဆုံး မှတ်တမ်းတင်ထားသော Customer အမည်၊ ကာန သို့မဟုတ် အီးမေးလ်)
        if (!empty($searchData['customer_name']) && StringUtil::isNotBlank($searchData['customer_name'])) {
            $cleanName = preg_replace('/\s+|[　]+/u', '', $searchData['customer_name']);
            $qb->andWhere('EXISTS (
                SELECT cfp_cust.id FROM ' . CustomerFavoriteProduct::class . ' cfp_cust
                JOIN cfp_cust.Customer cust
                WHERE cfp_cust.Product = p
                AND (
                    CONCAT(cust.name01, cust.name02) LIKE :cname
                    OR CONCAT(COALESCE(cust.kana01, \'\'), COALESCE(cust.kana02, \'\')) LIKE :cname
                    OR cust.email LIKE :cname
                )
            )')
            ->setParameter('cname', '%' . $cleanName . '%');
        }

        // ၄။ Favorite Count Minimum (အနည်းဆုံး အကြိုက်ဆုံး အရေအတွက်)
        if (isset($searchData['favorite_count_min']) && $searchData['favorite_count_min'] !== null && $searchData['favorite_count_min'] !== '') {
            $qb->andWhere('(SELECT COUNT(cfp_min.id) FROM ' . CustomerFavoriteProduct::class . ' cfp_min WHERE cfp_min.Product = p) >= :fav_min')
               ->setParameter('fav_min', (int)$searchData['favorite_count_min']);
        }

        // ၅။ Favorite Count Maximum (အများဆုံး အကြိုက်ဆုံး အရေအတွက်)
        if (isset($searchData['favorite_count_max']) && $searchData['favorite_count_max'] !== null && $searchData['favorite_count_max'] !== '') {
            $qb->andWhere('(SELECT COUNT(cfp_max.id) FROM ' . CustomerFavoriteProduct::class . ' cfp_max WHERE cfp_max.Product = p) <= :fav_max')
               ->setParameter('fav_max', (int)$searchData['favorite_count_max']);
        }

        // Sorting: Favorite Count အများဆုံးမှ အနည်းဆုံးသို့ စီခြင်း
        $qb->orderBy('favorite_count', 'DESC')
           ->addOrderBy('p.id', 'DESC');

        return $qb;
    }
}
