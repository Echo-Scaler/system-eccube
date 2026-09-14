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
use Eccube\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FavoriteController
 *
 * Admin Panel တွင် ဝယ်ယူသူများ၏ အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်းနှင့် Favorite Counts များကို ပြသ/စီမံသော Controller ဖြစ်ပါသည်။
 * (Admin Controller for displaying and managing customer favourite products with favorite counts)
 */
class FavoriteController extends AbstractController
{
    /**
     * @var CustomerFavoriteProductRepository
     */
    protected $customerFavoriteProductRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * FavoriteController constructor.
     *
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        ProductRepository $productRepository
    ) {
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Admin အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်းနှင့် Favorite Count ပြသခြင်း
     * (Admin Favourite Product List showing Image, Product Name, Price, and Favorite Counts)
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

        // QueryBuilder ဖြင့် Favorite ပြုလုပ်ထားသော ကုန်ပစ္စည်း ID များနှင့် Favorite Count ကို အများဆုံးမှ အနည်းဆုံးသို့ Group By လုပ်၍ ခေါ်ယူခြင်း
        // (MySQL only_full_group_by standard နှင့် 100% ကိုက်ညီစေရန် DQL Result array အဖြစ် ရယူခြင်း)
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('IDENTITY(cfp.Product) AS product_id, COUNT(cfp.id) AS favorite_count')
            ->from(CustomerFavoriteProduct::class, 'cfp')
            ->groupBy('cfp.Product')
            ->orderBy('favorite_count', 'DESC')
            ->addOrderBy('product_id', 'DESC');

        $rawList = $qb->getQuery()->getResult();

        // KnpPaginator ဖြင့် Array အား စာမျက်နှာ ခွဲထုတ်ခြင်း (Page Pagination)
        $page_count = $this->eccubeConfig->get('eccube_default_page_count');
        $pagination = $paginator->paginate(
            $rawList,
            $page_no,
            $page_count
        );

        // လက်ရှိစာမျက်နှာ (Current Page) ရှိ Product ID များအတွက် Product Entity များကို Query ဆွဲထုတ်ခြင်း
        $productMap = [];
        $productIds = [];
        foreach ($pagination->getItems() as $item) {
            if (!empty($item['product_id'])) {
                $productIds[] = (int) $item['product_id'];
            }
        }

        if (!empty($productIds)) {
            $products = $this->productRepository->findBy(['id' => $productIds]);
            foreach ($products as $p) {
                $productMap[$p->getId()] = $p;
            }
        }

        // စုစုပေါင်း Favorite အကြိမ်ရေ (Total Favorites)
        $total_favorites = (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(cfp.id)')
            ->from(CustomerFavoriteProduct::class, 'cfp')
            ->getQuery()
            ->getSingleScalarResult();

        // စုစုပေါင်း Favorite လုပ်ခံထားရသော ကုန်ပစ္စည်း အရေအတွက် (Total Distinct Favorited Products)
        $total_favorited_products = count($rawList);

        return [
            'pagination' => $pagination,
            'productMap' => $productMap,
            'page_no' => $page_no,
            'total_favorites' => $total_favorites,
            'total_favorited_products' => $total_favorited_products,
        ];
    }

    /**
     * Admin မှ Product တစ်ခု၏ Favorite မှတ်တမ်းအားလုံးကို ဖျက်ပစ်ခြင်း
     * (Admin delete all favorite records of a product)
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

        $favorites = $this->customerFavoriteProductRepository->findBy(['Product' => $id]);
        if (!$favorites || count($favorites) === 0) {
            $this->addError('admin.product.favorite.delete_error', 'admin');
            return $this->redirectToRoute('admin_product_favorite');
        }

        foreach ($favorites as $favorite) {
            $this->customerFavoriteProductRepository->delete($favorite);
        }
        $this->addSuccess('admin.product.favorite.delete_complete', 'admin');

        return $this->redirectToRoute('admin_product_favorite');
    }
}



