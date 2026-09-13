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

use Eccube\Controller\AbstractController;
use Eccube\Entity\CustomerFavoriteProduct;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FavoriteController
 *
 * Admin Panel တွင် ဝယ်ယူသူများ၏ အကြိုက်ဆုံးပစ္စည်း စာရင်း (Favourite Products List) ကို ရိုးရှင်းစွာ ပြသ/စီမံသော Controller ဖြစ်ပါသည်။
 * (Admin Controller for displaying and managing customer favourite product list)
 */
class FavoriteController extends AbstractController
{
    /**
     * @var CustomerFavoriteProductRepository
     */
    protected $customerFavoriteProductRepository;

    /**
     * FavoriteController constructor.
     *
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     */
    public function __construct(
        CustomerFavoriteProductRepository $customerFavoriteProductRepository
    ) {
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
    }

    /**
     * Admin အကြိုက်ဆုံးပစ္စည်းများ စာရင်း ပြသခြင်း
     * (Admin Favourite Product List)
     *
     * @Route("/%eccube_admin_route%/product/favorite", name="admin_product_favorite", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/product/favorite/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_favorite_page", methods={"GET", "POST"})
     * @Template("@admin/Product/favorite.twig")
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param int|null $page_no
     * @return array
     */
    public function index(Request $request, PaginatorInterface $paginator, $page_no = null)
    {
        $page_no = $page_no ?: 1;

        // QueryBuilder ဖြင့် CustomerFavoriteProduct အားလုံးကို အသစ်ဆုံးမှစ၍ ခေါ်ယူခြင်း
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('cfp', 'p', 'c')
            ->from(CustomerFavoriteProduct::class, 'cfp')
            ->leftJoin('cfp.Product', 'p')
            ->leftJoin('cfp.Customer', 'c')
            ->addOrderBy('cfp.create_date', 'DESC');

        // Pagination ခွဲထုတ်ခြင်း
        $page_count = $this->eccubeConfig->get('eccube_default_page_count');
        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $page_count,
            ['wrap-queries' => true]
        );

        return [
            'pagination' => $pagination,
            'page_no' => $page_no,
        ];
    }

    /**
     * Admin မှ Favorite တစ်ခုအား ဖျက်ပစ်ခြင်း
     * (Admin delete a customer favorite product record)
     *
     * @Route("/%eccube_admin_route%/product/favorite/{id}/delete", requirements={"id" = "\d+"}, name="admin_product_favorite_delete", methods={"DELETE", "POST"})
     *
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, $id)
    {
        $this->isTokenValid();

        /** @var CustomerFavoriteProduct $Favorite */
        $Favorite = $this->customerFavoriteProductRepository->find($id);
        if (!$Favorite) {
            $this->addError('admin.product.favorite.delete_error', 'admin');
            return $this->redirectToRoute('admin_product_favorite');
        }

        $this->customerFavoriteProductRepository->delete($Favorite);
        $this->addSuccess('admin.product.favorite.delete_complete', 'admin');

        return $this->redirectToRoute('admin_product_favorite');
    }
}
