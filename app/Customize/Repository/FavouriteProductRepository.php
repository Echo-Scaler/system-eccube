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
     * Favorite အများဆုံး ကုန်ပစ္စည်းများ စာရင်းနှင့် CSV Export အတွက် QueryBuilder
     * (MySQL 8 ONLY_FULL_GROUP_BY & EC-CUBE Standard Compliant)
     *
     * @return QueryBuilder
     */
    public function getFavouriteDb(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.CustomerFavoriteProducts', 'cfp')
            ->groupBy('p.id')
            ->orderBy('COUNT(cfp.id)', 'DESC')
            ->addOrderBy('p.id', 'DESC');
    }
}
