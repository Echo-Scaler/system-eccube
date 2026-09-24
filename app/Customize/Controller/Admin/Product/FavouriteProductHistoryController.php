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

namespace Customize\Controller\Admin\Product;

use Customize\Repository\CustomerFavoriteProductHistoryRepository;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Product;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FavouriteProductHistoryController
 *
 * ကုန်ပစ္စည်းတစ်ခုချင်းစီ၏ Favorite Action History (Register/Remove) များကို ပြသသော Admin Controller
 */
class FavouriteProductHistoryController extends AbstractController
{
    /**
     * @var CustomerFavoriteProductHistoryRepository
     */
    protected $historyRepository;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * FavouriteProductHistoryController constructor.
     *
     * @param CustomerFavoriteProductHistoryRepository $historyRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(
        CustomerFavoriteProductHistoryRepository $historyRepository,
        PaginatorInterface $paginator
    ) {
        $this->historyRepository = $historyRepository;
        $this->paginator = $paginator;
    }

    /**
     * ကုန်ပစ္စည်း၏ Favorite History စာရင်း ပြသခြင်း
     *
     * @Route("/%eccube_admin_route%/product/favourite/{id}/history", name="admin_product_favourite_history", requirements={"id" = "\d+"}, methods={"GET"})
     * @Route("/%eccube_admin_route%/product/favourite/{id}/history/page/{page_no}", name="admin_product_favourite_history_page", requirements={"id" = "\d+", "page_no" = "\d+"}, methods={"GET"})
     * @Template("@admin/Product/product_favourite_history.twig")
     *
     * @param Request $request
     * @param Product $Product
     * @param int|null $page_no
     * @return array
     */
    public function index(Request $request, Product $Product, $page_no = null): array
    {
        if (null !== $page_no) {
            $this->session->set('eccube.admin.product.favourite.history.page_no', (int) $page_no);
        } else {
            $page_no = $this->session->get('eccube.admin.product.favourite.history.page_no', 1);
        }

        $page_count = $this->eccubeConfig->get('eccube_default_page_count');

        // Product အလိုက် History QueryBuilder ရယူခြင်း
        $qb = $this->historyRepository->getQueryBuilderByProduct($Product);

        // Pagination ပြုလုပ်ခြင်း
        $pagination = $this->paginator->paginate($qb, $page_no, $page_count, ['wrap-queries' => true]);

        return [
            'Product' => $Product,
            'pagination' => $pagination,
            'page_no' => $page_no,
        ];
    }
}
