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
use Eccube\Event\EventArgs;
use Eccube\Service\CsvExportService;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FavouriteProductController
 *
 * Admin Panel တွင် ဝယ်ယူသူများ၏ အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်းနှင့် CSV Export ကို စီမံသော Controller ဖြစ်ပါသည်။
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
     * @var CsvExportService
     */
    protected $csvExportService;

    /**
     * FavouriteProductController constructor.
     *
     * @param FavouriteProductRepository $favouriteProductRepository
     * @param PaginatorInterface $paginator
     * @param CsvExportService $csvExportService
     */
    public function __construct(
        FavouriteProductRepository $favouriteProductRepository,
        PaginatorInterface $paginator,
        CsvExportService $csvExportService
    ) {
        $this->favouriteProductRepository = $favouriteProductRepository;
        $this->paginator = $paginator;
        $this->csvExportService = $csvExportService;
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

    /**
     * အကြိုက်ဆုံး ကုန်ပစ္စည်းများ CSV ဖိုင် ထုတ်ယူခြင်း (Streamed Export)
     *
     * @Route("/%eccube_admin_route%/product/favourite/export", name="admin_product_favourite_export", methods={"GET"})
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function export(Request $request): StreamedResponse
    {
        // အချိန်ကြာမြင့်စွာ run နိုင်ရန် Execution timeout ကို ပိတ်ထားခြင်း
        set_time_limit(0);

        // Memory အကုန်သက်သာစေရန် SQL Logger ကို ပိတ်ထားခြင်း
        $this->entityManager->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($request) {
            // CSV Type ID: 20 ဖြင့် CsvExportService အား စတင်ခြင်း
            $this->csvExportService->initCsvType(CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT);

            // Favorite ကုန်ပစ္စည်းများအတွက် QueryBuilder ရယူခြင်း
            $qb = $this->favouriteProductRepository->getFavouriteDb();

            // Header (ခေါင်းစဉ်တန်း) ထုတ်ပေးခြင်း
            $this->csvExportService->exportHeader();

            // Data Rows များကို QueryBuilder အသုံးပြု၍ Stream အဖြစ် ထုတ်ပေးခြင်း
            $this->csvExportService->setExportQueryBuilder($qb);
            $this->csvExportService->exportData(function (Product $Product, CsvExportService $csvService) use ($request) {
                $Csvs = $csvService->getCsvs();
                $ExportCsvRow = new ExportCsvRow();

                foreach ($Csvs as $Csv) {
                    $fieldName = $Csv->getFieldName();

                    if ($fieldName === 'favorite_count') {
                        // ဝယ်ယူသူများ အကြိုက်ဆုံး မှတ်သားထားသည့် စုစုပေါင်း အရေအတွက်
                        $count = count($Product->getCustomerFavoriteProducts());
                        $ExportCsvRow->setData((string) $count);
                    } elseif ($fieldName === 'price02_min') {
                        $ExportCsvRow->setData($Product->getPrice02Min());
                    } elseif ($fieldName === 'price02_max') {
                        $ExportCsvRow->setData($Product->getPrice02Max());
                    } elseif ($fieldName === 'code_min') {
                        $ExportCsvRow->setData($Product->getCodeMin());
                    } elseif ($fieldName === 'code_max') {
                        $ExportCsvRow->setData($Product->getCodeMax());
                    } else {
                        $ExportCsvRow->setData($csvService->getData($Csv, $Product));
                        if ($ExportCsvRow->isDataNull() && $fieldName === 'Status') {
                            $ExportCsvRow->setData($Product->getStatus() ? $Product->getStatus()->getName() : '');
                        }
                    }

                    // Hook point: Listener များမှ CSV Row ဒေတာ ပြင်ဆင်နိုင်ရန် Event dispatch ပြုလုပ်ခြင်း
                    $event = new EventArgs(
                        [
                            'csvService' => $csvService,
                            'Csv' => $Csv,
                            'Product' => $Product,
                            'ExportCsvRow' => $ExportCsvRow,
                        ],
                        $request
                    );
                    $this->eventDispatcher->dispatch($event, 'admin.product.favourite.csv.export');

                    $ExportCsvRow->pushData();
                }

                $csvService->fputcsv($ExportCsvRow->getRow());
            });
        });

        $now = new \DateTime();
        $filename = 'favourite_products_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        log_info('お気に入り商品CSV出力ファイル名', [$filename]);

        return $response;
    }
}
