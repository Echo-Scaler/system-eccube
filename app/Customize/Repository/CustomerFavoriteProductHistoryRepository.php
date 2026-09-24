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

use Customize\Entity\CustomerFavoriteProductHistory;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Eccube\Entity\Customer;
use Eccube\Entity\Product;
use Eccube\Repository\AbstractRepository;

/**
 * Class CustomerFavoriteProductHistoryRepository
 */
class CustomerFavoriteProductHistoryRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerFavoriteProductHistory::class);
    }

    /**
     * သမိုင်းမှတ်တမ်းအသစ် ထည့်သွင်းသိမ်းဆည်းခြင်း
     *
     * @param Customer $Customer
     * @param Product $Product
     * @param string $action ('register' သို့မဟုတ် 'remove')
     * @return CustomerFavoriteProductHistory
     */
    public function addHistory(Customer $Customer, Product $Product, string $action): CustomerFavoriteProductHistory
    {
        $history = new CustomerFavoriteProductHistory();
        $history->setCustomer($Customer);
        $history->setProduct($Product);
        $history->setAction($action);
        $history->setCreateDate(new \DateTime());

        $em = $this->getEntityManager();
        $em->persist($history);
        $em->flush();

        return $history;
    }

    /**
     * ကုန်ပစ္စည်းတစ်ခု၏ သမိုင်းမှတ်တမ်းများအတွက် QueryBuilder ရယူခြင်း
     * (ရက်စွဲ နောက်ဆုံးဖြစ်ပေါ်ခဲ့သည်များကို ထိပ်ဆုံးမှ ပြသရန် DESC ဖြင့် စီထားပါသည်)
     *
     * @param Product $Product
     * @return QueryBuilder
     */
    public function getQueryBuilderByProduct(Product $Product): QueryBuilder
    {
        $qb = $this->createQueryBuilder('h')
            ->leftJoin('h.Customer', 'c')
            ->addSelect('c')
            ->where('h.Product = :Product')
            ->setParameter('Product', $Product)
            ->orderBy('h.create_date', 'DESC')
            ->addOrderBy('h.id', 'DESC');

        return $qb;
    }
}
