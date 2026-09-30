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
     * Admin Panel Search Data အလိုက် Favorite Products QueryBuilder ရယူခြင်း
     * (product_id, product_name, customer_name စစ်ထုတ်မှုများ ပါဝင်သည်)
     *
     * @param array $searchData
     * @return QueryBuilder
     */
    public function getQueryBuilderBySearchDataForAdmin(array $searchData = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');
        $qb->addSelect('(SELECT COUNT(cfp.id) FROM ' . CustomerFavoriteProduct::class . ' cfp WHERE cfp.Product = p) AS HIDDEN favorite_count')
            ->where('(SELECT COUNT(cfp2.id) FROM ' . CustomerFavoriteProduct::class . ' cfp2 WHERE cfp2.Product = p) > 0')
            ->orderBy('favorite_count', 'DESC')
            ->addOrderBy('p.id', 'DESC');

        // 1. 商品ID (product_id) ဖြင့် စစ်ထုတ်ခြင်း
        if (isset($searchData['product_id']) && StringUtil::isNotBlank($searchData['product_id'])) {
            $qb->andWhere('p.id = :product_id')
                ->setParameter('product_id', $searchData['product_id']);
        }

        // 2. 商品名 (product_name) ဖြင့် စစ်ထုတ်ခြင်း (部分一致 - Multi-keyword Partial Match)
        if (isset($searchData['product_name']) && StringUtil::isNotBlank($searchData['product_name'])) {
            $keywords = preg_split('/[\s　]+/u', $searchData['product_name'], -1, PREG_SPLIT_NO_EMPTY);
            foreach ($keywords as $index => $keyword) {
                $param = 'product_name_' . $index;
                $qb->andWhere('p.name LIKE :' . $param)
                    ->setParameter($param, '%' . str_replace(['%', '_'], ['\\%', '\\_'], $keyword) . '%');
            }
        }

        // 3. 会員名 (customer_name) ဖြင့် စစ်ထုတ်ခြင်း (EC-CUBE Core Standard: OrderRepository & CustomerRepository Pattern)
        // Space များကို ဖယ်ရှားပြီး CONCAT(name01, name02) ဖြင့် စစ်ဆေးကာ ProductRepository အတိုင်း EXISTS Subquery သုံးထားပါသည်
        if (isset($searchData['customer_name']) && StringUtil::isNotBlank($searchData['customer_name'])) {
            $cleanCustomerName = preg_replace('/\s+|[　]+/u', '', $searchData['customer_name']);
            $likeCustomerName = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $cleanCustomerName) . '%';

            $qb->andWhere($qb->expr()->exists(
                'SELECT cfp_sub.id FROM ' . CustomerFavoriteProduct::class . ' cfp_sub ' .
                'JOIN cfp_sub.Customer c_sub ' .
                'WHERE cfp_sub.Product = p AND (' .
                'CONCAT(COALESCE(c_sub.name01, \'\'), COALESCE(c_sub.name02, \'\')) LIKE :customer_name OR ' .
                'CONCAT(COALESCE(c_sub.kana01, \'\'), COALESCE(c_sub.kana02, \'\')) LIKE :customer_name OR ' .
                'c_sub.name01 LIKE :customer_name OR ' .
                'c_sub.name02 LIKE :customer_name' .
                ')'
            ))->setParameter('customer_name', $likeCustomerName);
        }

        return $qb;
    }

    /**
     * SearchData အလိုက် QueryBuilder ရယူခြင်း (Alias method)
     *
     * @param array $searchData
     * @return QueryBuilder
     */
    public function getSearchData(array $searchData = []): QueryBuilder
    {
        return $this->getQueryBuilderBySearchDataForAdmin($searchData);
    }

    /**
     * Favorite အများဆုံး ကုန်ပစ္စည်းများ စာရင်းအတွက် QueryBuilder ရယူခြင်း
     * (Backward compatibility အတွက် searchData ပါ လက်ခံနိုင်ရန် ပြုလုပ်ထားသည်)
     *
     * @param array $searchData
     * @return QueryBuilder
     */
    public function getFavouriteDb(array $searchData = []): QueryBuilder
    {
        return $this->getQueryBuilderBySearchDataForAdmin($searchData);
    }
}
