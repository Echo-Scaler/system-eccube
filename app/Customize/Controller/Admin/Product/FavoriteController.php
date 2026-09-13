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

use Customize\Form\Type\Admin\SearchFavoriteProductType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Eccube\Controller\AbstractController;
use Eccube\Entity\CustomerFavoriteProduct;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FavoriteController
 *
 * Admin Panel တွင် ဝယ်ယူသူများ၏ အကြိုက်ဆုံးပစ္စည်း စာရင်းများကို ကြည့်ရှု/ရှာဖွေ/စီမံနိုင်သော Controller ဖြစ်ပါသည်။
 * (Admin Controller for viewing, searching, and managing customer favorite products)
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
     * Admin အကြိုက်ဆုံးပစ္စည်းများ စာရင်းနှင့် ရှာဖွေခြင်း
     * (Admin Favorite Product List & Search)
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
        // ၁။ ရှာဖွေရေး Form တည်ဆောက်ခြင်း (Build Search Form)
        $builder = $this->formFactory->createBuilder(SearchFavoriteProductType::class);
        $searchForm = $builder->getForm();

        // ၂။ Session သို့မဟုတ် Request မှ Search Data ရယူခြင်း
        $page_no = $page_no ?: 1;
        $searchData = [];

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
            if ($searchForm->isSubmitted() && $searchForm->isValid()) {
                $searchData = $searchForm->getData();
                $page_no = 1;
                $this->session->set('eccube.admin.product.favorite.search', $searchData);
            }
        } else {
            if ($request->get('resume')) {
                $searchData = $this->session->get('eccube.admin.product.favorite.search', []);
                $searchForm->submit($searchData);
            } else {
                $this->session->remove('eccube.admin.product.favorite.search');
            }
        }

        // ၃။ QueryBuilder တည်ဆောက်၍ ဒေတာ ရှာဖွေခြင်း (Build Search Query)
        $qb = $this->getSearchQueryBuilder($searchData);

        $event = new EventArgs([
            'qb' => $qb,
            'searchData' => $searchData,
        ], $request);
        $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_PRODUCT_INDEX_SEARCH);

        // ၄။ Pagination ခွဲထုတ်ခြင်း
        $page_count = $this->eccubeConfig->get('eccube_default_page_count');
        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $page_count,
            ['wrap-queries' => true]
        );

        // ၅။ စာရင်းအင်း အနှစ်ချုပ် (Summary Stats) တွက်ချက်ခြင်း
        $totalFavorites = $pagination->getTotalItemCount();
        $totalUniqueProducts = $this->getUniqueProductCount($searchData);
        $totalUniqueCustomers = $this->getUniqueCustomerCount($searchData);

        return [
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'page_no' => $page_no,
            'totalFavorites' => $totalFavorites,
            'totalUniqueProducts' => $totalUniqueProducts,
            'totalUniqueCustomers' => $totalUniqueCustomers,
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
            return $this->redirectToRoute('admin_product_favorite', ['resume' => 1]);
        }

        $this->customerFavoriteProductRepository->delete($Favorite);
        $this->addSuccess('admin.product.favorite.delete_complete', 'admin');

        return $this->redirectToRoute('admin_product_favorite', ['resume' => 1]);
    }

    /**
     * Search Form Data အပေါ် အခြေခံ၍ QueryBuilder တည်ဆောက်ခြင်း
     * (Build search QueryBuilder based on filter inputs)
     *
     * @param array $searchData
     * @return QueryBuilder
     */
    protected function getSearchQueryBuilder(array $searchData): QueryBuilder
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('cfp', 'p', 'c')
            ->from(CustomerFavoriteProduct::class, 'cfp')
            ->leftJoin('cfp.Product', 'p')
            ->leftJoin('cfp.Customer', 'c')
            ->addOrderBy('cfp.create_date', 'DESC');

        // Multi Search (Product Name, Customer Name, Email, ID)
        if (!empty($searchData['multi'])) {
            $multi = trim($searchData['multi']);
            $orX = $qb->expr()->orX(
                $qb->expr()->like('p.name', ':multi'),
                $qb->expr()->like('c.name01', ':multi'),
                $qb->expr()->like('c.name02', ':multi'),
                $qb->expr()->like('c.kana01', ':multi'),
                $qb->expr()->like('c.kana02', ':multi'),
                $qb->expr()->like('c.email', ':multi')
            );
            $qb->setParameter('multi', '%' . $multi . '%');

            if (is_numeric($multi)) {
                $orX->add($qb->expr()->eq('p.id', ':multi_id'));
                $orX->add($qb->expr()->eq('c.id', ':multi_id'));
                $qb->setParameter('multi_id', (int) $multi);
            }

            $qb->andWhere($orX);
        }

        // Date Start
        if (!empty($searchData['create_date_start'])) {
            $dateStart = $searchData['create_date_start'];
            $qb->andWhere('cfp.create_date >= :date_start')
               ->setParameter('date_start', $dateStart);
        }

        // Date End
        if (!empty($searchData['create_date_end'])) {
            $dateEnd = clone $searchData['create_date_end'];
            $dateEnd->modify('+1 day');
            $qb->andWhere('cfp.create_date < :date_end')
               ->setParameter('date_end', $dateEnd);
        }

        return $qb;
    }

    /**
     * စုစုပေါင်း Favorite အဖြစ် မှတ်သားခံထားရသော သီးခြား ကုန်ပစ္စည်းအရေအတွက်
     * (Get count of unique products favorited)
     *
     * @param array $searchData
     * @return int
     */
    protected function getUniqueProductCount(array $searchData): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(DISTINCT cfp.Product)')
            ->from(CustomerFavoriteProduct::class, 'cfp')
            ->leftJoin('cfp.Product', 'p')
            ->leftJoin('cfp.Customer', 'c');

        if (!empty($searchData['multi'])) {
            $multi = trim($searchData['multi']);
            $orX = $qb->expr()->orX(
                $qb->expr()->like('p.name', ':multi'),
                $qb->expr()->like('c.name01', ':multi'),
                $qb->expr()->like('c.name02', ':multi'),
                $qb->expr()->like('c.kana01', ':multi'),
                $qb->expr()->like('c.kana02', ':multi'),
                $qb->expr()->like('c.email', ':multi')
            );
            $qb->setParameter('multi', '%' . $multi . '%');

            if (is_numeric($multi)) {
                $orX->add($qb->expr()->eq('p.id', ':multi_id'));
                $orX->add($qb->expr()->eq('c.id', ':multi_id'));
                $qb->setParameter('multi_id', (int) $multi);
            }

            $qb->andWhere($orX);
        }

        if (!empty($searchData['create_date_start'])) {
            $qb->andWhere('cfp.create_date >= :date_start')
               ->setParameter('date_start', $searchData['create_date_start']);
        }

        if (!empty($searchData['create_date_end'])) {
            $dateEnd = clone $searchData['create_date_end'];
            $dateEnd->modify('+1 day');
            $qb->andWhere('cfp.create_date < :date_end')
               ->setParameter('date_end', $dateEnd);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * စုစုပေါင်း Favorite မှတ်သားထားသော သီးခြား ဝယ်ယူသူအရေအတွက်
     * (Get count of unique customers who favorited products)
     *
     * @param array $searchData
     * @return int
     */
    protected function getUniqueCustomerCount(array $searchData): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(DISTINCT cfp.Customer)')
            ->from(CustomerFavoriteProduct::class, 'cfp')
            ->leftJoin('cfp.Product', 'p')
            ->leftJoin('cfp.Customer', 'c');

        if (!empty($searchData['multi'])) {
            $multi = trim($searchData['multi']);
            $orX = $qb->expr()->orX(
                $qb->expr()->like('p.name', ':multi'),
                $qb->expr()->like('c.name01', ':multi'),
                $qb->expr()->like('c.name02', ':multi'),
                $qb->expr()->like('c.kana01', ':multi'),
                $qb->expr()->like('c.kana02', ':multi'),
                $qb->expr()->like('c.email', ':multi')
            );
            $qb->setParameter('multi', '%' . $multi . '%');

            if (is_numeric($multi)) {
                $orX->add($qb->expr()->eq('p.id', ':multi_id'));
                $orX->add($qb->expr()->eq('c.id', ':multi_id'));
                $qb->setParameter('multi_id', (int) $multi);
            }

            $qb->andWhere($orX);
        }

        if (!empty($searchData['create_date_start'])) {
            $qb->andWhere('cfp.create_date >= :date_start')
               ->setParameter('date_start', $searchData['create_date_start']);
        }

        if (!empty($searchData['create_date_end'])) {
            $dateEnd = clone $searchData['create_date_end'];
            $dateEnd->modify('+1 day');
            $qb->andWhere('cfp.create_date < :date_end')
               ->setParameter('date_end', $dateEnd);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
