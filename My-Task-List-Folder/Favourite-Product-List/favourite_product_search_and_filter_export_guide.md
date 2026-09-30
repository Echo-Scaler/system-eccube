# EC-CUBE 4.3.1 - Favourite Products Search Filter & Filtered CSV Export အဆင့်ဆင့် လမ်းညွှန်
## (Admin Favourite Products Search Filter & Filtered CSV Export Step-by-Step Implementation Guide)

ဤလက်စွဲစာအုပ်သည် EC-CUBE 4.3.1 ၏ Admin Panel ရှိ **Favourite Products (အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်း - `/admin/product/favourite`)** တွင် ရှာဖွေစစ်ထုတ်မှုများ (`product_id`, `product_name`, `customer_name`) ပြုလုပ်နိုင်သော **Search Form စနစ်** ထည့်သွင်းခြင်းနှင့် ထိုစစ်ထုတ်ထားသော ရှာဖွေမှုရလဒ်အတိုင်းသာ တိကျစွာ ထွက်ရှိလာစေမည့် **Filtered CSV Export စနစ်** တည်ဆောက်ခြင်းတို့ကို လုပ်ငန်းအတွေ့အကြုံ (၆) လခန့်ရှိသော Junior Developer များ အလွယ်တကူ လိုက်နာလေ့လာနိုင်စေရန် **မြန်မာဘာသာ** ဖြင့် ပြည့်စုံစွာ ရေးသားထားသော လမ်းညွှန်ဖြစ်ပါသည်။

---

## မာတိကာ (Table of Contents)

1. [ဤ Feature ၏ ရည်ရွယ်ချက်နှင့် အကျိုးကျေးဇူးများ (Feature Overview & Business Advantages)](#၁-ဤ-feature-၏-ရည်ရွယ်ချက်နှင့်-အကျိုးကျေးဇူးများ-feature-overview--business-advantages)
2. [ဖိုင်တည်ဆောက်ပုံနှင့် ပြင်ဆင်ဖွဲ့စည်းခဲ့သော ဖိုင်များစာရင်း (File Architecture & Structure)](#၂-ဖိုင်တည်ဆောက်ပုံနှင့်-ပြင်ဆင်ဖွဲ့စည်းခဲ့သော-ဖိုင်များစာရင်း-file-architecture--structure)
3. [Form Type အလုပ်လုပ်ပုံ သဘောတရား (`SearchFavouriteProductType.php`)](#၃-form-type-အလုပ်လုပ်ပုံ-သဘောတရား-searchfavouriteproducttypephp)
   - [Input Fields (၃) ခု၏ အသေးစိတ်](#input-fields-၃-ခု၏-အသေးစိတ်)
   - [အဘယ်ကြောင့် `method => 'GET'` နှင့် `csrf_protection => false` သတ်မှတ်ရသနည်း?](#အဘယ်ကြောင့်-method--get-နှင့်-csrf_protection--false-သတ်မှတ်ရသနည်း)
4. [Repository ၏ Search DQL Query တည်ဆောက်ပုံ Deep-Dive (`FavouriteProductRepository.php`)](#၄-repository-၏-search-dql-query-တည်ဆောက်ပုံ-deep-dive-favouriteproductrepositoryphp)
   - [အဘယ်ကြောင့် `Product::class` ကိုသာ Base Entity အဖြစ် ထားရှိရသနည်း?](#အဘယ်ကြောင့်-productclass-ကိုသာ-base-entity-အဖြစ်-ထားရှိရသနည်း)
   - [အဘယ်ကြောင့် `EXISTS` Subquery ကို အသုံးပြုရသနည်း? (Duplicate & Count Corruption ကာကွယ်ခြင်း)](#အဘယ်ကြောင့်-exists-subquery-ကို-အသုံးပြုရသနည်း-duplicate--count-corruption-ကာကွယ်ခြင်း)
   - [ဂျပန်အမည်များအတွက် `CONCAT(c.name01, c.name02)` နှင့် Space ဖယ်ရှားသည့် စံနှုန်း](#ဂျပန်အမည်များအတွက်-concatcname01-cname02-နှင့်-space-ဖယ်ရှားသည့်-စံနှုန်း)
5. [Controller ၏ Search & Filtered CSV Export အလုပ်လုပ်ပုံ (`FavouriteProductController.php`)](#၅-controller-၏-search--filtered-csv-export-အလုပ်လုပ်ပုံ-favouriteproductcontrollerphp)
   - [Request Parameter နှင့် Session ထဲမှ 検索条件 ရယူခြင်း](#request-parameter-နှင့်-session-ထဲမှ-検索条件-ရယူခြင်း)
   - [CSV Export Closure Callback ထဲသို့ `$searchData` လွှဲပြောင်းပေးခြင်း (`use ($searchData)`)](#csv-export-closure-callback-ထဲသို့-searchdata-လွှဲပြောင်းပေးခြင်း-use-searchdata)
   - [Clear (クリア) ခလုတ်၏ Session Reset အလုပ်လုပ်ပုံ](#clear-クリア-ခလုတ်၏-session-reset-အလုပ်လုပ်ပုံ)
6. [Twig View UI တွင် Search Form Card နှင့် CSV Download Link ချိတ်ဆက်ခြင်း (`product_favourite.twig`)](#၆-twig-view-ui-တွင်-search-form-card-နှင့်-csv-download-link-ချိတ်ဆက်ခြင်း-product_favouritetwig)
   - [`app.request.query.all` ၏ အခန်းကဏ္ဍ](#apprequestqueryall-၏-အခန်းကဏ္ဍ)
   - [Responsive UI Card နှင့် Clear ခလုတ် ချိတ်ဆက်မှု](#responsive-ui-card-နှင့်-clear-ခလုတ်-ချိတ်ဆက်မှု)
7. [Terminal Commands များနှင့် စနစ်စစ်ဆေးအတည်ပြုခြင်း (Verification Checklist)](#၇-terminal-commands-များနှင့်-စနစ်စစ်ဆေးအတည်ပြုခြင်း-verification-checklist)
8. [Junior Developer များ မဖြစ်မနေ ရှောင်ရှားရမည့် အမှားများ (Common Pitfalls & Best Practices)](#၈-junior-developer-များ-မဖြစ်မနေ-ရှောင်ရှားရမည့်-အမှားများ-common-pitfalls--best-practices)

---

## ၁။ ဤ Feature ၏ ရည်ရွယ်ချက်နှင့် အကျိုးကျေးဇူးများ (Feature Overview & Business Advantages)

စတိုးဆိုင်ကြီးများတွင် ကုန်ပစ္စည်းအရေအတွက် ထောင်သောင်းချီ၍ ရှိနိုင်ပြီး အကြိုက်ဆုံးအဖြစ် မှတ်သားထားသော ဒေတာများသည်လည်း အလွန်များပြားပါသည်။

```
                     ┌──────────────────────────────────────────────────┐
                     │ Favourite Product Search & Filtered CSV Export   │
                     └─────────────────────────┬────────────────────────┘
                                               │
               ┌───────────────────────────────┴───────────────────────────────┐
               ▼                                                               ▼
┌──────────────────────────────┐                              ┌──────────────────────────────┐
│  ၁။ တိကျသော ရှာဖွေမှု (Search)  │                              │ ၂။ စစ်ထုတ်ထားသော CSV ထုတ်ယူမှု │
│  - ကုန်ပစ္စည်း ID အလိုက်          │                              │  - ရှာထားသော ဒေတာများကိုသာ    │
│  - ကုန်ပစ္စည်းအမည် (部分一致)     │                              │    CSV File အဖြစ် တိုက်ရိုက်  │
│  - ဝယ်ယူသူအမည် (会員名 部分一致) │                              │    Download ဆွဲယူနိုင်ခြင်း     │
└──────────────────────────────┘                              └──────────────────────────────┘
```

1. **စွမ်းဆောင်ရည်မြင့် ရှာဖွေနိုင်ခြင်း:** ဆိုင်မန်နေဂျာများသည် ကုန်ပစ္စည်းတစ်ခုချင်းစီအလိုက် ဖြစ်စေ၊ သတ်မှတ်ထားသော ဝယ်ယူသူများ အကြိုက်တွေ့နေသော ကုန်ပစ္စည်းများကို ဖြစ်စေ အချိန်ကုန်သက်သာစွာ ရှာဖွေစစ်ထုတ်နိုင်ပါသည်။
2. **Filtered Data CSV Download ရယူနိုင်ခြင်း:** ယခင်က စာရင်းတစ်ခုလုံးကိုသာ အကုန်ထုတ်ယူနိုင်ခဲ့သော်လည်း ယခုအခါ ရှာဖွေထားသော စစ်ထုတ်ချက် (Filter result) အတိုင်းသာ CSV ထုတ်ယူနိုင်သဖြင့် ဒေတာအရွယ်အစား သေးငယ်သွားပြီး Excel သို့မဟုတ် စာရင်းအင်းစနစ်များသို့ တိုက်ရိုက် အသုံးပြုနိုင်ပါသည်။

---

## ၂။ ဖိုင်တည်ဆောက်ပုံနှင့် ပြင်ဆင်ဖွဲ့စည်းခဲ့သော ဖိုင်များစာရင်း (File Architecture & Structure)

EC-CUBE ၏ စံသတ်မှတ်ချက်အတိုင်း Core ဖိုင်များ (`src/Eccube/`) ကို လုံးဝ မပြင်ဆင်ဘဲ `app/Customize/` နှင့် `app/template/` အောက်တွင်သာ သန့်ရှင်းစွာ ဖွဲ့စည်းထားပါသည်-

```
app/
├── Customize/
│   ├── Form/Type/Admin/
│   │   └── SearchFavouriteProductType.php        <-- (၁) Search Form Type (Field များ သတ်မှတ်ခြင်း)
│   ├── Repository/
│   │   └── FavouriteProductRepository.php        <-- (၂) Repository (DQL QueryBuilder စစ်ထုတ်ချက်များ)
│   └── Controller/Admin/Product/
│       └── FavouriteProductController.php        <-- (၃) Controller (List & Filtered CSV Action)
└── template/admin/Product/
    └── product_favourite.twig                    <-- (၄) Twig Template (Search UI & Export Link)
```

---

## ၃။ Form Type အလုပ်လုပ်ပုံ သဘောတရား (`SearchFavouriteProductType.php`)

* **ဖိုင်လမ်းကြောင်း:** `app/Customize/Form/Type/Admin/SearchFavouriteProductType.php`

### Input Fields (၃) ခု၏ အသေးစိတ်

1. **`product_id` (IntegerType):**
   * ကုန်ပစ္စည်း၏ နံပါတ်စဉ် ID ဖြင့် အတိအကျ စစ်ထုတ်ရန် ဖြစ်ပါသည်။
2. **`product_name` (TextType):**
   * ကုန်ပစ္စည်း အမည်ထဲတွင် စာလုံးတစ်စိတ်တစ်ပိုင်း ပါဝင်မှု (部分一致) ဖြင့် ရှာဖွေရန် ဖြစ်ပါသည်။
3. **`customer_name` (TextType):**
   * အကြိုက်ဆုံးအဖြစ် သိမ်းဆည်းထားသော ဝယ်ယူသူ၏ အမည် (姓・名) ဖြင့် ရှာဖွေရန် ဖြစ်ပါသည်။

```php
<?php

namespace Customize\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFavouriteProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product_id', IntegerType::class, [
                'required' => false,
                'label' => '商品ID',
                'attr' => [
                    'placeholder' => '商品ID',
                ],
            ])
            ->add('product_name', TextType::class, [
                'required' => false,
                'label' => '商品名（部分一致）',
                'attr' => [
                    'placeholder' => '商品名',
                ],
            ])
            ->add('customer_name', TextType::class, [
                'required' => false,
                'label' => '会員名（部分一致）',
                'attr' => [
                    'placeholder' => '会員名',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
```

### အဘယ်ကြောင့် `method => 'GET'` နှင့် `csrf_protection => false` သတ်မှတ်ရသနည်း?
* **GET Method:** ရှာဖွေမှု Form များသည် Database ထဲရှိ Data ကို ပြင်ဆင်ခြင်း မဟုတ်ဘဲ ဒေတာဆွဲထုတ်ခြင်း (Read/Fetch) သာ ဖြစ်သောကြောင့် HTTP Standard အရ `GET` method ကို သုံးရပါသည်။
* **Bookmark & Shareable URL:** `GET` သုံးထားသဖြင့် ရှာဖွေထားသော query parameters များသည် URL ပေါ်တွင် `?product_id=1&product_name=shirt` ဟု တိုက်ရိုက် ပေါ်နေမည်ဖြစ်ပြီး Admin များအချင်းချင်း Link ပေးပို့နိုင်ခြင်း၊ Pagination ကူးပြောင်းနိုင်ခြင်းနှင့် CSV Download ဆွဲနိုင်ခြင်းတို့ကို အလွယ်တကူ ဆောင်ရွက်နိုင်စေပါသည်။
* **`getBlockPrefix() => ''`:** Form prefix (ဥပမာ `search_form[product_id]`) မဖြစ်စေဘဲ `?product_id=1` ဟု တိုက်ရိုက် သန့်ရှင်းသော URL Parameter ရရှိစေပါသည်။

---

## ၄။ Repository ၏ Search DQL Query တည်ဆောက်ပုံ Deep-Dive (`FavouriteProductRepository.php`)

* **ဖိုင်လမ်းကြောင်း:** `app/Customize/Repository/FavouriteProductRepository.php`

### အဘယ်ကြောင့် `Product::class` ကိုသာ Base Entity အဖြစ် ထားရှိရသနည်း?
ဤ Repository ၏ ရည်ရွယ်ချက်မှာ **"ဝယ်ယူသူများ အကြိုက်တွေ့နေသော ကုန်ပစ္စည်း (Product) စာရင်း"** ကို ပြသရန် ဖြစ်ပါသည်။
ထို့ကြောင့် Constructor တွင် `parent::__construct($registry, Product::class)` ဟု ပေးထားမှသာ `$this->createQueryBuilder('p')` ကို ခေါ်သည့်အခါ alias `p` သည် `Eccube\Entity\Product` အဖြစ် သတ်မှတ်ပြီး Pagination နှင့် CSV Export တွင် Product Entity အစစ်အမှန်များ ထွက်ပေါ်လာမည် ဖြစ်ပါသည်။

### အဘယ်ကြောင့် `EXISTS` Subquery ကို အသုံးပြုရသနည်း? (Duplicate & Count Corruption ကာကွယ်ခြင်း)

ဤသည်မှာ အလွန်အရေးကြီးသော နည်းပညာဆိုင်ရာ အချက်ဖြစ်ပါသည်။
ကုန်ပစ္စည်း A ကို ဝယ်ယူသူ အယောက် ၁၀၀ က Favorite လုပ်ထားသည်ဆိုပါစို့။

#### အကယ်၍ `INNER JOIN` သုံးမိပါက ဖြစ်မည့် ပြဿနာ-
1. **Duplicate Rows:** `INNER JOIN` ကြောင့် Product A သည် စာရင်းထဲတွင် အတန်းပေါင်း (၁၀၀) တန်း အထပ်ထပ် ထွက်လာပါမည်။
2. **Favorite Count ဒေတာ လွဲမှားသွားခြင်း:** အကယ်၍ Admin က "山田 (Yamada)" ဆိုသော ဝယ်ယူသူကို ရိုက်ရှာလိုက်ပါက `COUNT(cfp.id)` သည် မူလစုစုပေါင်း (၁၀၀) မထွက်တော့ဘဲ "၁" ဟုသာ မှားယွင်းစွာ ထွက်သွားပါမည်။
3. **MySQL 8 ONLY_FULL_GROUP_BY Error:** Duplicate မဖြစ်စေရန် `GROUP BY p.id` ထည့်ပါက MySQL 8 ၏ strict mode တွင် SQL Syntax Error တက်နိုင်ပါသည်။

#### `EXISTS` Subquery အသုံးပြုခြင်း၏ အကျိုးကျေးဇူး (EC-CUBE Core ProductRepository Standard):
```php
$qb->andWhere($qb->expr()->exists(
    'SELECT cfp_sub.id FROM ' . CustomerFavoriteProduct::class . ' cfp_sub ' .
    'JOIN cfp_sub.Customer c_sub ' .
    'WHERE cfp_sub.Product = p AND (' .
    'CONCAT(COALESCE(c_sub.name01, \'\'), COALESCE(c_sub.name02, \'\')) LIKE :customer_name OR ' .
    'c_sub.name01 LIKE :customer_name OR ' .
    'c_sub.name02 LIKE :customer_name' .
    ')'
))->setParameter('customer_name', $likeCustomerName);
```
* Subquery ဖြင့် စစ်ဆေးခြင်းဖြစ်၍ Product များ **လုံးဝ မထပ်ပါ** (Zero Duplication)။
* ကုန်ပစ္စည်း၏ စုစုပေါင်း Favorite Count အစစ်အမှန်လည်း **လုံးဝ မပြောင်းလဲဘဲ မူလအရေအတွက်အတိုင်း တိကျစွာ ထွက်ရှိပါသည်**။

### ဂျပန်အမည်များအတွက် `CONCAT(c.name01, c.name02)` နှင့် Space ဖယ်ရှားသည့် စံနှုန်း
* Core File ကိုးကား: `src/Eccube/Repository/OrderRepository.php` (Line 198)
* ဂျပန်အမည်များတွင် မျိုးရိုးအမည် (`name01`) နှင့် ကိုယ်ပိုင်အမည် (`name02`) ခွဲထားသဖြင့် Admin က `山田 太郎` ဟု အပြည့်အစုံ ရိုက်ရှာပါက အောက်ပါအတိုင်း Space ဖြတ်ပြီး `CONCAT` ဖြင့် တိုက်စစ်မှသာ ရှာဖွေတွေ့ရှိနိုင်ပါသည်-
```php
$cleanCustomerName = preg_replace('/\s+|[　]+/u', '', $searchData['customer_name']);
$likeCustomerName = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $cleanCustomerName) . '%';
```

---

## ၅။ Controller ၏ Search & Filtered CSV Export အလုပ်လုပ်ပုံ (`FavouriteProductController.php`)

* **ဖိုင်လမ်းကြောင်း:** `app/Customize/Controller/Admin/Product/FavouriteProductController.php`

### Request Parameter နှင့် Session ထဲမှ 検索条件 ရယူခြင်း
EC-CUBE ၏ စံနှုန်းအတိုင်း ရှာဖွေထားသော စစ်ထုတ်ချက်များကို Session တွင် မှတ်ထားပေးပြီး၊ CSV Download လုပ်ချိန်တွင်လည်း ထိုစစ်ထုတ်ချက်ကို တိုက်ရိုက် ဆွဲယူအသုံးပြုနိုင်အောင် စီစဉ်ထားပါသည်-

```php
    public function export(Request $request): StreamedResponse
    {
        set_time_limit(0);
        $this->entityManager->getConfiguration()->setSQLLogger(null);

        // ၁။ Form မှတစ်ဆင့် Request Parameters များကို ဖမ်းယူခြင်း
        $searchForm = $this->createForm(SearchFavouriteProductType::class);
        $searchForm->handleRequest($request);
        $searchData = $searchForm->getData() ?? [];

        // ၂။ Request တွင် မပါလာပါက Session ထဲရှိ စစ်ထုတ်ချက်ကို အသုံးပြုခြင်း
        if (empty(array_filter((array) $searchData)) && $this->session->has('eccube.admin.product.favourite.search')) {
            $searchData = $this->session->get('eccube.admin.product.favourite.search', []);
        }

        $response = new StreamedResponse();
        // ၃။ Anonymous Callback Function ထဲသို့ $searchData ကို "use ($searchData)" ဖြင့် ပို့ပေးရပါမည်
        $response->setCallback(function () use ($request, $searchData) {
            $this->csvExportService->initCsvType(CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT);

            // ၄။ Filter ပါဝင်သော QueryBuilder ကို ခေါ်ယူခြင်း
            $qb = $this->favouriteProductRepository->getQueryBuilderBySearchDataForAdmin($searchData ?: []);

            $this->csvExportService->exportHeader();
            $this->csvExportService->setExportQueryBuilder($qb);
            $this->csvExportService->exportData(function (Product $Product, CsvExportService $csvService) use ($request) {
                ...
            });
        });

        ...
        return $response;
    }
```

### CSV Export Closure Callback ထဲသို့ `$searchData` လွှဲပြောင်းပေးခြင်း (`use ($searchData)`)
PHP တွင် `function () use ($searchData)` ဟု variable ကို capture မလုပ်ပေးပါက Callback function အတွင်း၌ `$searchData` သည် `null` ဖြစ်သွားမည် ဖြစ်ပြီး Filter မပါဘဲ စာရင်းအကုန် ထွက်သွားပါမည်။ ထို့ကြောင့် `use ($searchData)` သည် မပါမဖြစ် လိုအပ်ပါသည်။

### Clear (クリア) ခလုတ်၏ Session Reset အလုပ်လုပ်ပုံ
အသုံးပြုသူက "Clear" ခလုတ်ကို နှိပ်လိုက်ပါက `?clear=1` parameter ပေးပို့လာပြီး Controller တွင် Session ကို ရှင်းလင်းပေးကာ စာရင်းတစ်ခုလုံးကို မူလအတိုင်း ပြန်လည်ပြသပေးပါသည်-
```php
if ($request->query->get('clear')) {
    $this->session->remove('eccube.admin.product.favourite.search');
    $this->session->remove('eccube.admin.product.favourite.page_no');
    return $this->redirectToRoute('admin_product_favourite');
}
```

---

## ၆။ Twig View UI တွင် Search Form Card နှင့် CSV Download Link ချိတ်ဆက်ခြင်း (`product_favourite.twig`)

* **ဖိုင်လမ်းကြောင်း:** `app/template/admin/Product/product_favourite.twig`

### `app.request.query.all` ၏ အခန်းကဏ္ဍ
CSV Download ခလုတ်ကို နှိပ်လိုက်သည့်အခါ လက်ရှိ URL ပေါ်ရှိ ရှာဖွေမှု parameter များကို Export URL သို့ လက်ဆင့်ကမ်း ပေးပို့နိုင်ရန် `app.request.query.all` ကို အသုံးပြုထားပါသည်-

```twig
<!-- Actions: Filter Parameters ပါဝင်သော CSV Download ခလုတ် -->
<div class="btn-group" role="group">
    <a href="{{ url('admin_product_favourite_export', app.request.query.all) }}" class="btn btn-ec-regular shadow-sm">
        <i class="fa fa-cloud-download me-1 text-secondary"></i><span>{{ 'admin.common.csv_download'|trans }}</span>
    </a>
</div>
```

### Responsive UI Card နှင့် Clear ခလုတ် ချိတ်ဆက်မှု
Bootstrap 5 grid system ဖြင့် သန့်ရှင်းသပ်ရပ်သော Search Filter Card ကို တည်ဆောက်ထားပါသည်-

```twig
<!-- Search Filter Form Card -->
{% if searchForm is defined %}
    <form method="get" action="{{ url('admin_product_favourite') }}">
        <div class="card rounded border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fa fa-search me-2 text-primary"></i>{{ 'admin.common.search_condition'|trans }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <!-- 商品ID -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">{{ form_label(searchForm.product_id) }}</label>
                        {{ form_widget(searchForm.product_id, {'attr': {'class': 'form-control', 'placeholder': '商品ID'}}) }}
                    </div>
                    <!-- 商品名（部分一致） -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">{{ form_label(searchForm.product_name) }}</label>
                        {{ form_widget(searchForm.product_name, {'attr': {'class': 'form-control', 'placeholder': '商品名'}}) }}
                    </div>
                    <!-- 会員名（部分一致） -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">{{ form_label(searchForm.customer_name) }}</label>
                        {{ form_widget(searchForm.customer_name, {'attr': {'class': 'form-control', 'placeholder': '会員名'}}) }}
                    </div>
                    <!-- Search & Clear Buttons -->
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1 shadow-sm">
                            <i class="fa fa-search me-1"></i>{{ 'admin.common.search'|trans }}
                        </button>
                        <a href="{{ url('admin_product_favourite', {'clear': 1}) }}" class="btn btn-outline-secondary shadow-sm" title="{{ 'admin.common.clear'|trans }}">
                            <i class="fa fa-refresh me-1"></i>{{ 'admin.common.clear'|trans }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
{% endif %}
```

---

## ၇။ Terminal Commands များနှင့် စနစ်စစ်ဆေးအတည်ပြုခြင်း (Verification Checklist)

ဖိုင်များကို ပြင်ဆင်ပြီးပါက အောက်ပါ Command များဖြင့် အမြဲ စစ်ဆေးအတည်ပြုရပါမည်-

### (၁) PHP Syntax အမှား ရှိ/မရှိ စစ်ဆေးခြင်း
```bash
php -l app/Customize/Form/Type/Admin/SearchFavouriteProductType.php
php -l app/Customize/Repository/FavouriteProductRepository.php
php -l app/Customize/Controller/Admin/Product/FavouriteProductController.php
```
*(ရလဒ်: `No syntax errors detected` ထွက်ပေါ်ရပါမည်)*

### (၂) Symfony Dependency Injection Container နှင့် Twig Cache အား ရှင်းလင်းခြင်း
```bash
bin/console cache:clear --no-warmup
```
*(ရလဒ်: `[OK] Cache for the "prod" environment was successfully cleared.` ထွက်ပေါ်ရပါမည်)*

### (၃) Browser တွင် လက်တွေ့ စမ်းသပ်စစ်ဆေးခြင်း
1. Admin Panel သို့ ဝင်ရောက်ပါ (`http://localhost:8080/admin/product/favourite`)
2. **商品ID စမ်းသပ်ခြင်း:** ကုန်ပစ္စည်း ID ရိုက်ထည့်၍ "検索" နှိပ်ပါ။ ထို ID ကုန်ပစ္စည်းတစ်ခုတည်းသာ ထွက်လာမည်။ ထို့နောက် "CSV Download" နှိပ်ပါက ထိုကုန်ပစ္စည်းအတန်းသာ ပါဝင်သော CSV ဖိုင် ရရှိမည်။
3. **商品名 စမ်းသပ်ခြင်း:** ကုန်ပစ္စည်းအမည် တစ်စိတ်တစ်ပိုင်း ရိုက်ရှာပြီး CSV ထုတ်ယူကြည့်ပါ။
4. **会員名 စမ်းသပ်ခြင်း:** ဝယ်ယူသူအမည် (ဥပမာ `山田 太郎`) ရိုက်ရှာပြီး CSV ထုတ်ယူကြည့်ပါ။
5. **クリア (Clear) စမ်းသပ်ခြင်း:** Clear ခလုတ်နှိပ်ပါက စာရင်းအားလုံး မူလအတိုင်း ပြန်လည်ပြသမည်။

---

## ၈။ Junior Developer များ မဖြစ်မနေ ရှောင်ရှားရမည့် အမှားများ (Common Pitfalls & Best Practices)

| မကြာခဏ မှားတတ်သော အမှား (Common Pitfalls) | ဘာကြောင့် မလုပ်သင့်သနည်း? (Why It's Wrong) | မှန်ကန်သော နည်းလမ်း (Correct Best Practice) |
| :--- | :--- | :--- |
| **Controller ထဲတွင် DB Query ရေးခြင်း** | MVC Architecture ကို ပျက်ပြားစေပြီး Controller ကုဒ် ရှုပ်ထွေးသွားစေသည်။ | QueryLogic အားလုံးကို **Repository** ထဲတွင်သာ ရေးသားရမည်။ |
| **`select('p AS Product, COUNT(...)')` ရေးခြင်း** | Query မှ Product Entity မဟုတ်ဘဲ Array ထွက်လာသဖြင့် CSV Export တွင် `TypeError` တက်စေသည်။ | Entity ကို သီးသန့် ရရှိစေရန် **Hidden Select / Subquery** သုံးရမည်။ |
| **Customer Name ရှာဖွေရာတွင် `INNER JOIN` သုံးခြင်း** | Product တစ်ခုတည်း အကြိမ်ကြိမ် Duplicate ဖြစ်ပြီး Favorite Count အရေအတွက် လွဲမှားစေသည်။ | Duplicate မဖြစ်စေရန် **`EXISTS (SELECT ...)` Subquery** ကို သုံးရမည်။ |
| **Closure Callback ထဲ `use ($searchData)` မထည့်ခြင်း** | Callback function အတွင်း၌ `$searchData` မရှိတော့ဘဲ Filter မပါသော CSV ထွက်သွားသည်။ | Callback တွင် **`function () use ($searchData)`** မဖြစ်မနေ ထည့်ရမည်။ |
| **CSV Link တွင် Parameter မထည့်ခြင်း** | URL query parameters များ မပါသွားဘဲ CSV Download အလွတ် ဖြစ်သွားသည်။ | Link တွင် **`app.request.query.all`** ကို ထည့်ပေးရမည်။ |

---

ဤလမ်းညွှန်ပါအတိုင်း လိုက်နာရေးသားထားသဖြင့် မိတ်ဆွေ၏ Favourite Products Search & Filtered CSV Export Feature သည် **EC-CUBE 4.3.1 ၏ Core Code Coding Standards များနှင့် ၁၀၀% ကိုက်ညီပြီး Production-Ready ဖြစ်သော စနစ်ကောင်းတစ်ခု** ဖြစ်တည်သွားပြီ ဖြစ်ပါသည်။
