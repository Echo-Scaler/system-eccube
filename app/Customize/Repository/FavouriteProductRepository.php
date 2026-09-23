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
     * Favorite အများဆုံး ကုန်ပစ္စည်းများ စာရင်းအတွက် QueryBuilder ရယူခြင်း
     * (MySQL 8 ONLY_FULL_GROUP_BY 100% Safe & Standard Compliant)
     *
     * @return QueryBuilder
     */
    public function getFavouriteDb(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');
        $qb->addSelect('(SELECT COUNT(cfp.id) FROM ' . CustomerFavoriteProduct::class . ' cfp WHERE cfp.Product = p) AS HIDDEN favorite_count')
            ->where('(SELECT COUNT(cfp2.id) FROM ' . CustomerFavoriteProduct::class . ' cfp2 WHERE cfp2.Product = p) > 0')
            ->orderBy('favorite_count', 'DESC')
            ->addOrderBy('p.id', 'DESC');

        return $qb;
    }
}
