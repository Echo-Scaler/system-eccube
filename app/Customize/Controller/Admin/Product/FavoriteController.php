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

use Customize\Entity\Master\CustomCsvType;
use Customize\Repository\CustomProductRepository;
use Eccube\Controller\AbstractController;
use Eccube\Entity\CustomerFavoriteProduct;
use Eccube\Entity\Product;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Service\CsvExportService;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
     * @var CustomProductRepository
     */
    protected $customProductRepository;

    /**
     * @var CsvExportService
     */
    protected $csvExportService;

    /**
     * FavoriteController constructor.
     *
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     * @param ProductRepository $productRepository
     * @param CustomProductRepository $customProductRepository
     * @param CsvExportService $csvExportService
     */
    public function __construct(
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        ProductRepository $productRepository,
        CustomProductRepository $customProductRepository,
        CsvExportService $csvExportService
    ) {
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
        $this->productRepository = $productRepository;
        $this->customProductRepository = $customProductRepository;
        $this->csvExportService = $csvExportService;
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
     * Admin အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်းအား EC-CUBE Standard CsvExportService ဖြင့် Download ထုတ်ယူခြင်း
     * (Admin Favourite Product CSV Export adhering 100% to EC-CUBE Standard & dtb_csv configuration)
     *
     * @Route("/%eccube_admin_route%/product/favorite/export", name="admin_product_favorite_export", methods={"GET"})
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function exportCsv(Request $request)
    {
        // ၁။ Timeout နှင့် SQL Logger ကို ပိတ်ခြင်း (Core Standard)
        set_time_limit(0);
        $this->entityManager->getConfiguration()->setSQLLogger(null);

        // ၂။ StreamedResponse ဖြင့် Client သို့ Stream ထုတ်ပေးခြင်း
        $response = new StreamedResponse();
        $response->setCallback(function () use ($request) {
            // EC-CUBE Standard: /admin/setting/shop/csv တွင် သတ်မှတ်ထားသော Custom CSV Type (ID: 8) ဖြင့် Initialize လုပ်ခြင်း
            $this->csvExportService->initCsvType(CustomCsvType::CSV_TYPE_FAVORITE_PRODUCT);

            // Repository Pattern Standard: Repository Method ဖြင့် QueryBuilder ခေါ်ယူခြင်း
            $qb = $this->customProductRepository->getQueryBuilderForFavoriteCsv();

            $this->csvExportService->setExportQueryBuilder($qb);

            // EC-CUBE Standard: dtb_csv တွင် Admin ပြင်ဆင်ထားသော Active Columns များဖြင့် Header ထုတ်ပေးခြင်း
            $this->csvExportService->exportHeader();

            // EC-CUBE Standard: CsvExportService ၏ Chunked Pagination & Memory Clear စနစ်ဖြင့် Data ထုတ်ပေးခြင်း
            $this->csvExportService->exportData(function (Product $Product, CsvExportService $csvService) use ($request) {
                $Csvs = $csvService->getCsvs();
                $row = [];

                foreach ($Csvs as $Csv) {
                    $fieldName = $Csv->getFieldName();

                    if ($fieldName === 'price02_inc_tax_min') {
                        // EC-CUBE Standard: Price Range ရှိပါက 商品一覧 အတိုင်း Format ပြုလုပ်ခြင်း
                        if ($Product->hasProductClass() && $Product->getPrice02Min() != $Product->getPrice02Max()) {
                            $value = number_format($Product->getPrice02IncTaxMin()) . ' ～ ' . number_format($Product->getPrice02IncTaxMax());
                        } else {
                            $value = number_format($Product->getPrice02IncTaxMin());
                        }
                    } elseif ($fieldName === 'favorite_count') {
                        $value = count($Product->getCustomerFavoriteProducts());
                    } elseif ($fieldName === 'stock_min') {
                        $value = $Product->getStockMin() !== null ? $Product->getStockMin() : '無制限';
                    } else {
                        // Core Entity Fields များအတွက် standard getData() ဖြင့် ရယူခြင်း
                        $value = $csvService->getData($Csv, $Product);
                    }

                    $row[] = $value;
                }

                $csvService->fputcsv($row);
            });
        });

        // ၃။ Response File Headers သတ်မှတ်ခြင်း
        $now = new \DateTime();
        $filename = 'favorite_products_' . $now->format('YmdHis') . '.csv';

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        // Core Product/Order အတိုင်း Log မှတ်တမ်းတင်ခြင်း
        log_info('Favorite CSV Export Completed', [$filename]);

        return $response;
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



