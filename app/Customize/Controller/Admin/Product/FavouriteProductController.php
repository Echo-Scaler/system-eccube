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
use Customize\Repository\FavouriteProductRepository;
use Eccube\Controller\AbstractController;
use Eccube\Entity\ExportCsvRow;
use Eccube\Entity\Product;
use Eccube\Service\CsvExportService;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

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
     * Admin အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်း ပြသခြင်း
     *
     * @Route("/%eccube_admin_route%/product/favourite", name="admin_product_favourite", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/product/favourite/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_favourite_page", methods={"GET", "POST"})
     * @Template("@admin/Product/product_favourite.twig")
     *
     * @param Request $request
     * @param int|null $page_no
     * @return array
     */
    public function index(Request $request, $page_no = null): array
    {
        $page_no = $page_no ?: $request->query->getInt('page_no', 1);
        $page_count = $this->eccubeConfig->get('eccube_default_page_count');

        $qb = $this->favouriteProductRepository->getFavouriteDb();

        $pagination = $this->paginator->paginate($qb, $page_no, $page_count, ['wrap-queries' => true]);

        return [
            'pagination' => $pagination,
            'page_no' => $page_no,
        ];
    }

    /**
     * Admin Favourite CSV Export (EC-CUBE Standard)
     *
     * @Route("/%eccube_admin_route%/product/favourite/export", name="admin_product_favourite_export", methods={"GET"})
     */
    public function export(Request $request): StreamedResponse
    {
        set_time_limit(0);
        $this->entityManager->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($request) {
            // ၁။ Custom CSV Type (ID: 20) ဖြင့် CsvExportService ကို Initialize လုပ်ခြင်း
            $this->csvExportService->initCsvType(CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT);

            // ၂။ Custom Repository မှ QueryBuilder ခေါ်ယူခြင်း
            $qb = $this->favouriteProductRepository->getFavouriteDb();
            $this->csvExportService->setExportQueryBuilder($qb);

            // ၃။ Excel တွင် ဂျပန်/မြန်မာ စာလုံးမပျက်စေရန် UTF-8 BOM ထည့်သွင်းခြင်း
            $fp = fopen('php://output', 'w');
            fwrite($fp, "\xEF\xBB\xBF");
            fclose($fp);

            // ၄။ dtb_csv မှ Active Columns များအတိုင်း Header တန်း ထုတ်ပေးခြင်း
            $this->csvExportService->exportHeader();

            // ၅။ Data Rows များကို Chunking ဖြင့် Memory Leak ကင်းစွာ ထုတ်ပေးခြင်း
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
                        // Core Product entity field များကို getData ဖြင့် ရယူခြင်း
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
