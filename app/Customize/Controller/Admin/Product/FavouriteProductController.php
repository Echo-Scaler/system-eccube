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

use Customize\Constant\CustomCsvType;
use Customize\Form\Type\Admin\SearchFavoriteProductType;
use Customize\Repository\FavouriteProductRepository;
use Eccube\Controller\AbstractController;
use Eccube\Entity\ExportCsvRow;
use Eccube\Entity\Product;
use Eccube\Service\CsvExportService;
use Eccube\Util\FormUtil;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FavouriteProductController
 *
 * Admin Panel တွင် ဝယ်ယူသူများ၏ အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်း၊ Search Filters နှင့် CSV Export ကို စီမံသော တစ်ခုတည်းသော စံ Controller ဖြစ်ပါသည်။
 */
class FavouriteProductController extends AbstractController
{
    /**
     * @var FavouriteProductRepository
     */
    protected $favouriteProductRepository;

    /**
     * @var CsvExportService
     */
    protected $csvExportService;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * FavouriteProductController constructor.
     *
     * @param FavouriteProductRepository $favouriteProductRepository
     * @param CsvExportService $csvExportService
     * @param PaginatorInterface $paginator
     */
    public function __construct(
        FavouriteProductRepository $favouriteProductRepository,
        CsvExportService $csvExportService,
        PaginatorInterface $paginator
    ) {
        $this->favouriteProductRepository = $favouriteProductRepository;
        $this->csvExportService = $csvExportService;
        $this->paginator = $paginator;
    }

    /**
     * Admin အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်းနှင့် Search Form ပြသခြင်း
     * (Supports both /favourite and /favorite route aliases)
     *
     * @Route("/%eccube_admin_route%/product/favourite", name="admin_product_favourite", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/product/favourite/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_favourite_page", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/product/favorite", name="admin_product_favorite", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/product/favorite/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_favorite_page", methods={"GET", "POST"})
     * @Template("@admin/Product/product_favourite.twig")
     *
     * @param Request $request
     * @param int|null $page_no
     * @return array
     */
    public function index(Request $request, $page_no = null): array
    {
        // ၁။ Search Form တည်ဆောက်ခြင်း
        $searchForm = $this->createForm(SearchFavoriteProductType::class);
        $searchData = [];

        if ($request->getMethod() === 'POST') {
            $searchForm->handleRequest($request);
            if ($searchForm->isSubmitted() && $searchForm->isValid()) {
                $searchData = $searchForm->getData();
                $page_no = 1;
                $this->session->set('eccube.admin.product.favourite.search', FormUtil::getViewData($searchForm));
                $this->session->set('eccube.admin.product.favourite.search.page_no', $page_no);
            }
        } else {
            if (null !== $page_no) {
                $this->session->set('eccube.admin.product.favourite.search.page_no', (int) $page_no);
            } else {
                $page_no = $this->session->get('eccube.admin.product.favourite.search.page_no', 1);
            }

            $viewData = $this->session->get('eccube.admin.product.favourite.search', []);
            $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
        }

        $page_count = $this->eccubeConfig->get('eccube_default_page_count');

        // ၂။ Search Criteria ဖြင့် Filtered QueryBuilder ရယူခြင်း
        $qb = $this->favouriteProductRepository->getFavouriteDb($searchData);

        // ၃။ KnpPaginator ဖြင့် Pagination ပြုလုပ်ခြင်း
        $pagination = $this->paginator->paginate($qb, $page_no, $page_count, ['wrap-queries' => true]);

        return [
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'page_no' => $page_no,
        ];
    }

    /**
     * Admin Favourite Product CSV Export (Search Filtered)
     * (Supports both /favourite/export and /favorite/export route aliases)
     *
     * @Route("/%eccube_admin_route%/product/favourite/export", name="admin_product_favourite_export", methods={"GET"})
     * @Route("/%eccube_admin_route%/product/favorite/export", name="admin_product_favorite_export", methods={"GET"})
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function export(Request $request): StreamedResponse
    {
        set_time_limit(0);
        $this->entityManager->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($request) {
            // ၁။ Custom CSV Type (ID: 20) ဖြင့် CsvExportService ကို Initialize လုပ်ခြင်း
            $this->csvExportService->initCsvType(CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT);

            // ၂။ Session မှ လက်ရှိ Search Criteria ကို ရယူ၍ Filter ပြုလုပ်ခြင်း
            $searchForm = $this->createForm(SearchFavoriteProductType::class);
            $viewData = $this->session->get('eccube.admin.product.favourite.search', []);
            $searchData = FormUtil::submitAndGetData($searchForm, $viewData);

            // ၃။ Filtered QueryBuilder ချိတ်ဆက်ခြင်း
            $qb = $this->favouriteProductRepository->getFavouriteDb($searchData);
            $this->csvExportService->setExportQueryBuilder($qb);

            // ၄။ UTF-8 BOM ထည့်သွင်းခြင်း (Excel encoding safe)
            $fp = fopen('php://output', 'w');
            fwrite($fp, "\xEF\xBB\xBF");
            fclose($fp);

            // ၅။ dtb_csv မှ Active Columns များအတိုင်း Header တန်း ထုတ်ပေးခြင်း
            $this->csvExportService->exportHeader();

            // ၆။ Data Rows များကို Chunking ဖြင့် Memory Leak ကင်းစွာ ထုတ်ပေးခြင်း
            $this->csvExportService->exportData(function (Product $Product, CsvExportService $csvService) use ($request) {
                $Csvs = $csvService->getCsvs();
                $ExportCsvRow = new ExportCsvRow();

                foreach ($Csvs as $Csv) {
                    $fieldName = $Csv->getFieldName();

                    if ($fieldName === 'favorite_count') {
                        $favoriteCount = count($Product->getCustomerFavoriteProducts());
                        $ExportCsvRow->setData($favoriteCount);
                    } elseif ($fieldName === 'Status') {
                        $statusName = $Product->getStatus() ? $Product->getStatus()->getName() : '';
                        $ExportCsvRow->setData($statusName);
                    } else {
                        $ExportCsvRow->setData($csvService->getData($Csv, $Product));
                    }

                    $ExportCsvRow->pushData();
                }

                $csvService->fputcsv($ExportCsvRow->getRow());
            });
        });

        $now = new \DateTime();
        $filename = 'favourite_products_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        log_info('Favourite CSV Export Completed', [$filename]);

        return $response;
    }
}
