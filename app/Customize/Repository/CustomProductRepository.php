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
 * Class CustomProductRepository
 *
 * Product Entity ဆိုင်ရာ Custom Database Queries များအား စုစည်းကိုင်တွယ်သော Custom Repository ဖြစ်ပါသည်။
 * (Custom Repository for handling Product custom database queries according to Symfony Best Practices)
 */
class CustomProductRepository extends AbstractRepository
{
    /**
     * CustomProductRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Favorite အများဆုံး ကုန်ပစ္စည်းများ စာရင်းနှင့် CSV Export အတွက် QueryBuilder တည်ဆောက်ပေးခြင်း
     * (QueryBuilder for retrieving products sorted by favorite count for Admin List and CSV Export)
     *
     * @return QueryBuilder
     */
    public function getQueryBuilderForFavoriteCsv(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.CustomerFavoriteProducts', 'cfp')
            ->groupBy('p.id')
            ->orderBy('COUNT(cfp.id)', 'DESC')
            ->addOrderBy('p.id', 'DESC');
    }
}
