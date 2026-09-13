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

use Customize\Form\Type\Admin\SearchCustomProductType;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Category;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ProductStock;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Util\StringUtil;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CustomProductListController
 *
 * Admin Panel အတွက် သီးသန့် Custom Product List (カスタム商品一覧) စာမျက်နှာကို ကိုင်တွယ်သော Controller ဖြစ်ပါသည်။
 * (Admin Controller for managing and displaying the dedicated custom product list page)
 */
class CustomProductListController extends AbstractController
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var ProductStatusRepository
     */
    protected $productStatusRepository;

    /**
     * CustomProductListController constructor.
     *
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param ProductStatusRepository $productStatusRepository
     */
    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        ProductStatusRepository $productStatusRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productStatusRepository = $productStatusRepository;
    }

    /**
     * Admin Custom Product List စာမျက်နှာ ပြသခြင်း
     * (Admin Custom Product List Index)
     *
     * @Route("/%eccube_admin_route%/product/custom_list", name="admin_product_custom_list", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/product/custom_list/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_custom_list_page", methods={"GET", "POST"})
     * @Template("@admin/Product/custom_list.twig")
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param int|null $page_no
     * @return array
     */
    public function index(Request $request, PaginatorInterface $paginator, $page_no = null)
    {
        $page_no = $page_no ?: 1;

        // ၁။ Search Form တည်ဆောက်ခြင်း
        $searchForm = $this->createForm(SearchCustomProductType::class);
        $searchForm->handleRequest($request);

        $searchData = [];
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchData = $searchForm->getData();
        } elseif ($request->getMethod() === 'GET') {
            $searchData = $request->query->all($searchForm->getName());
            if ($searchData) {
                $searchForm->submit($searchData, false);
                $searchData = $searchForm->getData();
            }
        }

        // ၂။ Product QueryBuilder တည်ဆောက်ခြင်း
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('DISTINCT p', 'pc', 'pi', 'pct', 'c', 'st')
            ->from(Product::class, 'p')
            ->leftJoin('p.ProductClasses', 'pc')
            ->leftJoin('p.ProductImage', 'pi')
            ->leftJoin('p.ProductCategories', 'pct')
            ->leftJoin('pct.Category', 'c')
            ->leftJoin('p.Status', 'st')
            ->andWhere('pc.visible = :visible')
            ->setParameter('visible', true);

        // Filter: Keyword (ID, Name, Product Code)
        if (!empty($searchData['id']) && StringUtil::isNotBlank($searchData['id'])) {
            $rawKeyword = trim($searchData['id']);
            $escapedKeyword = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $rawKeyword) . '%';

            if (is_numeric($rawKeyword)) {
                $qb->andWhere('p.id = :productId OR p.name LIKE :kwName OR pc.code LIKE :kwCode')
                    ->setParameter('productId', (int)$rawKeyword)
                    ->setParameter('kwName', $escapedKeyword)
                    ->setParameter('kwCode', $escapedKeyword);
            } else {
                $qb->andWhere('p.name LIKE :kwName OR pc.code LIKE :kwCode')
                    ->setParameter('kwName', $escapedKeyword)
                    ->setParameter('kwCode', $escapedKeyword);
            }
        }

        // Filter: Category
        if (!empty($searchData['category_id']) && $searchData['category_id'] instanceof Category) {
            $categories = $searchData['category_id']->getSelfAndDescendants();
            if (!empty($categories)) {
                $qb->andWhere($qb->expr()->in('pct.Category', ':categories'))
                    ->setParameter('categories', $categories);
            }
        }

        // Filter: Product Status (Public, Non-public, etc.)
        if (!empty($searchData['status'])) {
            $statusList = $searchData['status'];
            $qb->andWhere($qb->expr()->in('p.Status', ':statuses'))
                ->setParameter('statuses', $statusList);
        }

        // Filter: Stock (In Stock / Out of Stock)
        if (!empty($searchData['stock'])) {
            $stockChoices = (array)$searchData['stock'];
            $hasInStock = in_array(ProductStock::IN_STOCK, $stockChoices);
            $hasOutOfStock = in_array(ProductStock::OUT_OF_STOCK, $stockChoices);

            if ($hasInStock && !$hasOutOfStock) {
                // In Stock Only: Unlimited or stock > 0
                $qb->andWhere('(pc.stock_unlimited = true OR pc.stock > 0)');
            } elseif ($hasOutOfStock && !$hasInStock) {
                // Out of Stock Only: Not unlimited and (stock is null or stock <= 0)
                $qb->andWhere('(pc.stock_unlimited = false AND (pc.stock IS NULL OR pc.stock <= 0))');
            }
        }

        // Filter: Create Date Range
        if (!empty($searchData['create_date_start']) && $searchData['create_date_start'] instanceof \DateTime) {
            $startDate = (clone $searchData['create_date_start'])->setTime(0, 0, 0);
            $qb->andWhere('p.create_date >= :create_date_start')
                ->setParameter('create_date_start', $startDate);
        }
        if (!empty($searchData['create_date_end']) && $searchData['create_date_end'] instanceof \DateTime) {
            $endDate = (clone $searchData['create_date_end'])->setTime(23, 59, 59);
            $qb->andWhere('p.create_date <= :create_date_end')
                ->setParameter('create_date_end', $endDate);
        }

        // Sort Order: Newest First
        $qb->orderBy('p.create_date', 'DESC')
            ->addOrderBy('p.id', 'DESC');

        // ၃။ Pagination ခွဲထုတ်ခြင်း
        $page_count = $this->eccubeConfig->get('eccube_default_page_count');
        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $page_count,
            ['wrap-queries' => true]
        );

        // ၄။ Summary Statistics တွက်ချက်ခြင်း
        $stats = $this->calculateSummaryStats();

        return [
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'page_no' => $page_no,
            'stats' => $stats,
        ];
    }

    /**
     * အနှစ်ချုပ် ကိန်းဂဏန်းများ တွက်ချက်ပေးသော Helper Method
     * (Calculate summary statistics for dashboard cards)
     *
     * @return array
     */
    protected function calculateSummaryStats()
    {
        // Total Products
        $totalProducts = (int)$this->entityManager->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Product::class, 'p')
            ->getQuery()
            ->getSingleScalarResult();

        // Public Products (Status = 1 / SHOW)
        $publicProducts = (int)$this->entityManager->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Product::class, 'p')
            ->where('p.Status = :status')
            ->setParameter('status', ProductStatus::DISPLAY_SHOW)
            ->getQuery()
            ->getSingleScalarResult();

        // Hidden/Non-public Products (Status = 2 / HIDE)
        $hiddenProducts = (int)$this->entityManager->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Product::class, 'p')
            ->where('p.Status = :status')
            ->setParameter('status', ProductStatus::DISPLAY_HIDE)
            ->getQuery()
            ->getSingleScalarResult();

        // Total Categories
        $totalCategories = (int)$this->entityManager->createQueryBuilder()
            ->select('COUNT(c.id)')
            ->from(Category::class, 'c')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total_products' => $totalProducts,
            'public_products' => $publicProducts,
            'hidden_products' => $hiddenProducts,
            'total_categories' => $totalCategories,
        ];
    }
}
