# EC-CUBE 4.3.1 - Admin Panel ကုန်ပစ္စည်း စာရင်း စာမျက်နှာအသစ်နှင့် Menu ထည့်သွင်းခြင်း လမ်းညွှန် (Custom Product List & Admin Navigation Guide)

ဤမှတ်တမ်းသည် EC-CUBE 4.3.1 တွင် **Admin Panel (စီမံခန့်ခွဲသူ မျက်နှာပြင်) ၌ သီးသန့် ကုန်ပစ္စည်းစာရင်း စာမျက်နှာအသစ် ဖန်တီးခြင်း (管理画面のカスタム商品一覧画面の作成)** နှင့် **Admin ဘယ်ဘက် Sidebar Menu တွင် Menu Item အသစ် ထည့်သွင်းခြင်း** ကို အတွေ့အကြုံ (၆) လရှိ Junior Developer များ အလွယ်တကူ လိုက်နာနားလည်နိုင်စေရန် မြန်မာဘာသာဖြင့် အဆင့်ဆင့် ရေးသားထားသော လမ်းညွှန်ဖြစ်ပါသည်။

---

## ၁။ အနှစ်ချုပ် ခြုံငုံသုံးသပ်ချက် (Overview & Purpose)

EC-CUBE Core ဖိုင်များကို လုံးဝမထိခိုက်စေဘဲ (`app/Customize/` နှင့် `app/template/` အောက်တွင်သာ) အောက်ပါ အချက်များကို အကောင်အထည်ဖော်ထားပါသည်:

1. **သီးသန့် Custom Product List စာမျက်နှာ (`/admin/product/custom_list`):**
   * ဆိုင်တွင် မှတ်ပုံတင်ထားသော ကုန်ပစ္စည်းများအားလုံးကို စာရင်းဇယား (Data Table) ဖြင့် သပ်ရပ်စွာ ပြသခြင်း။
   * ကုန်ပစ္စည်း ပုံရိပ်ငယ် (Thumbnail Image)၊ အမည်၊ Product Code၊ Category၊ ဈေးနှုန်း (အခွန်ပါ/မပါ)၊ လက်ကျန် Stock၊ နှင့် ရောင်းချမှု အခြေအနေ Status Badge များ ပါဝင်ခြင်း။
2. **အဆင့်မြင့် ရှာဖွေစစ်ထုတ်မှု စနစ် (Advanced Search & Filter):**
   * ကုန်ပစ္စည်း ID, အမည်, သို့မဟုတ် Product Code ဖြင့် ရှာဖွေနိုင်ခြင်း (Multi-Search)။
   * Category ရွေးချယ်၍ စစ်ထုတ်နိုင်ခြင်း (Sub-categories များပါ အလိုအလျောက် ပါဝင်သည်)။
   * Display Status (公開 / 非公開 / 廃止) အလိုက် စစ်ထုတ်နိုင်ခြင်း။
   * Stock Status (在庫あり / 在庫切れ) အလိုက် စစ်ထုတ်နိုင်ခြင်း။
   * ကုန်ပစ္စည်း စတင်ထည့်သွင်းခဲ့သည့် နေ့စွဲ (Create Date Start ~ End) အလိုက် စစ်ထုတ်နိုင်ခြင်း။
3. **စာရင်းအင်း အနှစ်ချုပ် ကတ်များ (Summary Statistics Cards):**
   * စုစုပေါင်း ကုန်ပစ္စည်း အရေအတွက် (Total Products)။
   * လက်ရှိ ရောင်းချနေသော ကုန်ပစ္စည်း အရေအတွက် (Public Products)။
   * ဖျောက်ထားသော ကုန်ပစ္စည်း အရေအတွက် (Hidden Products)။
   * စုစုပေါင်း Category အရေအတွက် (Total Categories)။
4. **လျင်မြန်သော လုပ်ဆောင်ချက်များ (Quick Actions):**
   * ကုန်ပစ္စည်း ရှေ့ပြေး ကြည့်ရှုခြင်း (View on Front Store - Eye Icon)။
   * Admin ကုန်ပစ္စည်း ပြင်ဆင်ခြင်း စာမျက်နှာသို့ သွားရောက်ခြင်း (Edit Product - Pencil Icon)။
5. **Admin Sidebar Menu ချိတ်ဆက်မှု (Nav Menu Integration):**
   * Admin ဘယ်ဘက် Sidebar ရှိ **商品管理 (Product Management) -> カスタム商品一覧** အောက်တွင် Menu Link အသစ် ထည့်သွင်းထားခြင်း။
6. **EC-CUBE Route Annotation Standard:**
   * Route များကို EC-CUBE ၏ စံသတ်မှတ်ချက်အတိုင်း PHPDoc Annotation (`@Route(...)`) ပုံစံဖြင့်သာ ရေးသားထားခြင်း။

---

## ၂။ မူရင်း Core ဖိုင်များ စာရင်း (Origin Core File List)

> [!CAUTION]
> **EC-CUBE Core Law (အဓိက ဥပဒေသ):** `src/Eccube/` နှင့် `vendor/` အောက်ရှိ မူရင်း Core ဖိုင်များကို **တိုက်ရိုက် ပြင်ဆင်ခြင်း လုံးဝ မပြုလုပ်ရပါ**။

| စဉ် | မူရင်း Core ဖိုင်လမ်းကြောင်း (Origin Core File Path) | မူရင်း တာဝန် (Default Role) |
| :---: | :--- | :--- |
| ၁ | `src/Eccube/Entity/Product.php` | ကုန်ပစ္စည်း Database Table (`dtb_product`) နှင့် ချိတ်ဆက်ထားသော Core Entity။ |
| ၂ | `src/Eccube/Repository/ProductRepository.php` | ကုန်ပစ္စည်း Database Query များကို ကိုင်တွယ်သည့် Core Repository။ |
| ၃ | `src/Eccube/Controller/Admin/Product/ProductController.php` | မူရင်း Admin Product Controller (မူရင်း Route: `/admin/product`)။ |
| ၄ | `src/Eccube/Form/Type/Admin/SearchProductType.php` | မူရင်း Admin Product Search Form Type Class။ |
| ၅ | `src/Eccube/Resource/template/admin/Product/index.twig` | မူရင်း Admin ကုန်ပစ္စည်း စာရင်း UI Template။ |
| ၆ | `src/Eccube/Resource/template/admin/default_frame.twig` | Admin Panel ၏ ပင်မ Layout Frame Template။ |

---

## ၃။ ပြင်ဆင် / အသစ်ဖန်တီးထားသော ဖိုင်များ စာရင်း (Updated / Created File List)

EC-CUBE Customization စည်းမျဉ်းများနှင့်အညီ အောက်ပါအတိုင်း သီးခြား ဖန်တီး/ပြင်ဆင်ထားပါသည်:

| စဉ် | ပြင်ဆင်/ဖန်တီးထားသော ဖိုင်လမ်းကြောင်း (File Path) | အမျိုးအစား | တာဝန်နှင့် လုပ်ဆောင်ချက် (Role & Description) |
| :---: | :--- | :---: | :--- |
| ၁ | [`SearchCustomProductType.php`](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/Customize/Form/Type/Admin/SearchCustomProductType.php) | **အသစ်ဖန်တီး (NEW)** | ကုန်ပစ္စည်း ရှာဖွေစစ်ထုတ်ရန် (Keyword, Category, Status, Stock, Date) Symfony Form Type Class။ |
| ၂ | [`CustomProductListController.php`](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/Customize/Controller/Admin/Product/CustomProductListController.php) | **အသစ်ဖန်တီး (NEW)** | သီးသန့် ကုန်ပစ္စည်းစာရင်း စာမျက်နှာနှင့် စာရင်းအင်း Stats များ တွက်ချက်ပြသသည့် Controller (Route Annotation ပုံစံဖြင့် ရေးသားထားသည်)။ |
| ၃ | [`custom_list.twig`](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/template/admin/Product/custom_list.twig) | **အသစ်ဖန်တီး (NEW)** | စီမံခန့်ခွဲသူ မျက်နှာပြင် UI Template (Summary Cards, Search Card, Data Table, Pagination)။ |
| ၄ | [`eccube_nav.yaml`](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/config/eccube/packages/eccube_nav.yaml) | **ပြင်ဆင် (UPDATED)** | Admin Sidebar Menu ရှိ 商品管理 အောက်တွင် `カスタム商品一覧` (`admin_product_custom_list`) Menu link အသစ် ထည့်သွင်းထားခြင်း။ |
| ၅ | [`messages.ja.yaml`](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/Customize/Resource/locale/messages.ja.yaml) | **ပြင်ဆင် (UPDATED)** | Custom Product List အတွက် ဂျပန်ဘာသာ Translation String များ။ |
| ၆ | [`messages.en.yaml`](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/Customize/Resource/locale/messages.en.yaml) | **ပြင်ဆင် (UPDATED)** | Custom Product List အတွက် အင်္ဂလိပ်ဘာသာ Translation String များ။ |

---

## ၄။ အဆင့်ဆင့် အကောင်အထည်ဖော်မှု လမ်းညွှန် (Step-by-Step Implementation Details)

### အဆင့် ၁: Search Form Type ဖန်တီးခြင်း (Search Form Type Creation)
**ဖိုင်တည်နေရာ:** `app/Customize/Form/Type/Admin/SearchCustomProductType.php`

**ဘာကြောင့် ရေးရသလဲ (Why):**
စီမံခန့်ခွဲသူမှ ကုန်ပစ္စည်းအမည်၊ Code၊ Category၊ ရောင်းချမှုအခြေအနေ၊ စတော့အခြေအနေနှင့် ရက်စွဲများဖြင့် စစ်ထုတ်ရှာဖွေနိုင်ရန် Form Type တစ်ခု လိုအပ်ပါသည်။

**ကုဒ်နမူနာ (Code Snippet):**
```php
<?php

namespace Customize\Form\Type\Admin;

use Eccube\Entity\Category;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\ProductStock;
use Eccube\Form\Type\Master\CategoryType as MasterCategoryType;
use Eccube\Form\Type\Master\ProductStatusType;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchCustomProductType extends AbstractType
{
    protected $categoryRepository;
    protected $productStatusRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        ProductStatusRepository $productStatusRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productStatusRepository = $productStatusRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', TextType::class, [
                'label' => 'admin.product.multi_search_label',
                'required' => false,
            ])
            ->add('category_id', MasterCategoryType::class, [
                'choice_label' => 'NameWithLevel',
                'label' => 'admin.product.category',
                'placeholder' => 'common.select__all_products',
                'required' => false,
                'choices' => $this->categoryRepository->getList(null, true),
                'choice_value' => function (Category $Category = null) {
                    return $Category ? $Category->getId() : null;
                },
            ])
            ->add('status', ProductStatusType::class, [
                'label' => 'admin.product.display_status',
                'multiple' => true,
                'required' => false,
                'expanded' => true,
            ])
            ->add('stock', ChoiceType::class, [
                'label' => 'admin.product.stock',
                'choices' => [
                    'admin.product.stock__in_stock' => ProductStock::IN_STOCK,
                    'admin.product.stock__out_of_stock' => ProductStock::OUT_OF_STOCK,
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ])
            ->add('create_date_start', DateType::class, [
                'label' => 'admin.common.create_date__start',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            ->add('create_date_end', DateType::class, [
                'label' => 'admin.common.create_date__end',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'admin_search_custom_product';
    }
}
```

---

### အဆင့် ၂: Custom Product List Controller ဖန်တီးခြင်း (Controller Creation)
**ဖိုင်တည်နေရာ:** `app/Customize/Controller/Admin/Product/CustomProductListController.php`

**ဘာကြောင့် ရေးရသလဲ (Why):**
စစ်ထုတ်ထားသော QueryBuilder ဖြင့် Database မှ Data ဆွဲထုတ်ခြင်း၊ Pagination တွက်ချက်ခြင်း၊ Summary Stats (စုစုပေါင်း၊ ရောင်းချဆဲ၊ ဖျောက်ထား၊ Category အရေအတွက်) တွက်ချက်ပြီး Twig Template သို့ ပေးပို့ရန် ဖြစ်ပါသည်။

**ကုဒ်နမူနာ (Full Controller Code):**
```php
<?php

namespace Customize\Controller\Admin\Product;

use Customize\Form\Type\Admin\SearchCustomProductType;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Category;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Entity\ProductStock;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Util\StringUtil;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CustomProductListController extends AbstractController
{
    protected $productRepository;
    protected $categoryRepository;
    protected $productStatusRepository;

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
     * @Route("/%eccube_admin_route%/product/custom_list", name="admin_product_custom_list", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/product/custom_list/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_custom_list_page", methods={"GET", "POST"})
     * @Template("@admin/Product/custom_list.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator, $page_no = null)
    {
        $page_no = $page_no ?: 1;

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

        if (!empty($searchData['category_id']) && $searchData['category_id'] instanceof Category) {
            $categories = $searchData['category_id']->getSelfAndDescendants();
            if (!empty($categories)) {
                $qb->andWhere($qb->expr()->in('pct.Category', ':categories'))
                    ->setParameter('categories', $categories);
            }
        }

        if (!empty($searchData['status'])) {
            $qb->andWhere($qb->expr()->in('p.Status', ':statuses'))
                ->setParameter('statuses', $searchData['status']);
        }

        if (!empty($searchData['stock'])) {
            $stockChoices = (array)$searchData['stock'];
            $hasInStock = in_array(ProductStock::IN_STOCK, $stockChoices);
            $hasOutOfStock = in_array(ProductStock::OUT_OF_STOCK, $stockChoices);

            if ($hasInStock && !$hasOutOfStock) {
                $qb->andWhere('(pc.stock_unlimited = true OR pc.stock > 0)');
            } elseif ($hasOutOfStock && !$hasInStock) {
                $qb->andWhere('(pc.stock_unlimited = false AND (pc.stock IS NULL OR pc.stock <= 0))');
            }
        }

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

        $qb->orderBy('p.create_date', 'DESC')
            ->addOrderBy('p.id', 'DESC');

        $page_count = $this->eccubeConfig->get('eccube_default_page_count');
        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $page_count,
            ['wrap-queries' => true]
        );

        $stats = $this->calculateSummaryStats();

        return [
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'page_no' => $page_no,
            'stats' => $stats,
        ];
    }

    protected function calculateSummaryStats()
    {
        $totalProducts = (int)$this->entityManager->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Product::class, 'p')
            ->getQuery()->getSingleScalarResult();

        $publicProducts = (int)$this->entityManager->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Product::class, 'p')
            ->where('p.Status = :status')
            ->setParameter('status', ProductStatus::DISPLAY_SHOW)
            ->getQuery()->getSingleScalarResult();

        $hiddenProducts = (int)$this->entityManager->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Product::class, 'p')
            ->where('p.Status = :status')
            ->setParameter('status', ProductStatus::DISPLAY_HIDE)
            ->getQuery()->getSingleScalarResult();

        $totalCategories = (int)$this->entityManager->createQueryBuilder()
            ->select('COUNT(c.id)')
            ->from(Category::class, 'c')
            ->getQuery()->getSingleScalarResult();

        return [
            'total_products' => $totalProducts,
            'public_products' => $publicProducts,
            'hidden_products' => $hiddenProducts,
            'total_categories' => $totalCategories,
        ];
    }
}
```

---

### အဆင့် ၃: Admin UI Twig Template ဖန်တီးခြင်း (Twig Template Creation)
**ဖိုင်တည်နေရာ:** `app/template/admin/Product/custom_list.twig`

**ဘာကြောင့် ရေးရသလဲ (Why):**
Admin Layout (`@admin/default_frame.twig`) ကို အခြေခံပြီး စီမံခန့်ခွဲသူများ အသုံးပြုရ လွယ်ကူစေရန် Summary Stats Cards၊ Search Filter Form၊ ကုန်ပစ္စည်း ဓာတ်ပုံ၊ အမည်၊ Code၊ Category၊ ဈေးနှုန်း (`|price`)၊ စတော့နှင့် Status Badges များ ပါဝင်သော Data Table ကို ရေးဆွဲထားခြင်း ဖြစ်ပါသည်။

**အဓိက ပါဝင်သော UI အစိတ်အပိုင်းများ:**
1. `{% set menus = ['product', 'custom_product_list'] %}`: Admin Sidebar ရှိ Active Menu ကို အလိုအလျောက် Highlight ပြုလုပ်ပေးသည်။
2. **Summary Cards:** စုစုပေါင်း ကုန်ပစ္စည်း၊ 公開 (Public)၊ 非公開 (Hidden) နှင့် Category စုစုပေါင်း အရေအတွက်ကို Card များဖြင့် ပြသသည်။
3. **Data Table:** Bootstrap 5 Table ဖြင့် Responsive ကျကျ ပြသပြီး Eye Icon ဖြင့် Front Shop Product Detail စာမျက်နှာနှင့် Pencil Icon ဖြင့် Admin Product Edit စာမျက်နှာသို့ သွားရောက်နိုင်သည်။
4. **Pagination:** `{% include "@admin/pager.twig" with {'pages': pagination.paginationData, 'routes': 'admin_product_custom_list_page'} %}` ဖြင့် စာမျက်နှာ ခွဲခြားပေးသည်။

---

### အဆင့် ၄: Admin Navigation Menu ထည့်သွင်းခြင်း (Menu Configuration)
**ဖိုင်တည်နေရာ:** `app/config/eccube/packages/eccube_nav.yaml`

**ဘာကြောင့် ပြင်ရသလဲ (Why):**
Admin Panel ဘယ်ဘက် Sidebar ရှိ `商品管理 (Product Management)` အောက်တွင် `カスタム商品一覧` အဖြစ် Menu item အသစ် ပေါ်လာစေရန် ဖြစ်ပါသည်။

**ပြင်ဆင်မှု ကုဒ်နမူနာ:**
```yaml
parameters:
    eccube_nav:
        product:
            name: admin.product.product_management
            icon: fa-cube
            children:
                product_master:
                    name: admin.product.product_list
                    url: admin_product
                custom_product_list:
                    name: admin.product.custom_product_list
                    url: admin_product_custom_list
                product_edit:
                    name: admin.product.product_registration
                    url: admin_product_product_new
```

---

### အဆင့် ၅: Translation Strings များ ထည့်သွင်းခြင်း (Translations)
**ဖိုင်တည်နေရာ:** `app/Customize/Resource/locale/messages.ja.yaml`

```yaml
# Custom Product List
admin.product.custom_product_list: カスタム商品一覧
admin.product.custom_product_list_description: 登録されている商品の一覧表示、検索、および管理を行うことができます。
admin.product.stat_total_products: 総商品数
admin.product.stat_public_products: 公開中
admin.product.stat_hidden_products: 非公開
admin.product.stat_total_categories: カテゴリ数
admin.product.no_products_message: 条件に一致する商品が見つかりませんでした。
```

---

## ၅။ စစ်ဆေးအတည်ပြုခြင်းနှင့် Terminal Command များ (Verification Commands)

အသစ်ဖန်တီးထားသော Route နှင့် Menu များ အလုပ်လုပ်စေရန် Cache Clear ပြုလုပ်ရပါမည်:

```bash
# ၁။ Cache Clear ပြုလုပ်ခြင်း (Clear Symfony & EC-CUBE Cache)
bin/console cache:clear --no-warmup

# ၂။ Route များ မှန်ကန်စွာ Register ဖြစ်မဖြစ် စစ်ဆေးခြင်း (Check Routes)
bin/console debug:router admin_product_custom_list
bin/console debug:router admin_product_custom_list_page
```

---

## ၆။ စစ်ဆေးရန် အချက်များ (Verification Checklist)

- [x] Admin Panel သို့ ဝင်ရောက်ပြီး ဘယ်ဘက် Sidebar ၏ **商品管理** အောက်တွင် **カスタム商品一覧** Menu ပေါ်နေခြင်း။
- [x] Menu ကို နှိပ်ပါက `/admin/product/custom_list` သို့ ရောက်ရှိပြီး Summary Cards ၄ ခုနှင့် Product Data Table ပြသခြင်း။
- [x] Search Filter တွင် Keyword (အမည်/ID/Code)၊ Category၊ Status၊ Stock၊ ရက်စွဲများဖြင့် စစ်ထုတ်ရှာဖွေနိုင်ခြင်း။
- [x] Action ခလုတ်များ (Eye Icon ဖြင့် Front Product Detail ကြည့်ရှုခြင်း၊ Pencil Icon ဖြင့် Admin Edit သို့ သွားရောက်ခြင်း) အလုပ်လုပ်ခြင်း။
- [x] ကုန်ပစ္စည်း စာရင်း အရေအတွက် များပြားပါက အောက်ခြေရှိ Pagination အလုပ်လုပ်ခြင်း။
