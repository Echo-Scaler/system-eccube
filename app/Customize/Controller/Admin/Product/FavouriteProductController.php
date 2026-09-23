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

use Customize\Repository\FavouriteProductRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FavouriteProductController
 *
 * Admin Panel တွင် ဝယ်ယူသူများ၏ အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်းကို စီမံပြသသော Controller ဖြစ်ပါသည်။
 */
class FavouriteProductController extends AbstractController
{
    /**
     * @var FavouriteProductRepository
     */
    protected $favouriteProductRepository;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * FavouriteProductController constructor.
     *
     * @param FavouriteProductRepository $favouriteProductRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(
        FavouriteProductRepository $favouriteProductRepository,
        PaginatorInterface $paginator
    ) {
        $this->favouriteProductRepository = $favouriteProductRepository;
        $this->paginator = $paginator;
    }

    /**
     * Admin အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်း ပြသခြင်း
     * (EC-CUBE Standard Routing: Base List URL and Pagination URL)
     *
     * @Route("/%eccube_admin_route%/product/favourite", name="admin_product_favourite", methods={"GET"})
     * @Route("/%eccube_admin_route%/product/favourite/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_favourite_page", methods={"GET"})
     * @Template("@admin/Product/product_favourite.twig")
     *
     * @param Request $request
     * @param int|null $page_no
     * @return array
     */
    public function index(Request $request, $page_no = null): array
    {
        if (null !== $page_no) {
            $this->session->set('eccube.admin.product.favourite.page_no', (int) $page_no);
        } else {
            $page_no = $this->session->get('eccube.admin.product.favourite.page_no', 1);
        }

        $page_count = $this->eccubeConfig->get('eccube_default_page_count');

        // Favorite ကုန်ပစ္စည်းများ QueryBuilder ရယူခြင်း
        $qb = $this->favouriteProductRepository->getFavouriteDb();

        // KnpPaginator ဖြင့် Pagination ပြုလုပ်ခြင်း
        $pagination = $this->paginator->paginate($qb, $page_no, $page_count, ['wrap-queries' => true]);

        return [
            'pagination' => $pagination,
            'page_no' => $page_no,
        ];
    }
}
