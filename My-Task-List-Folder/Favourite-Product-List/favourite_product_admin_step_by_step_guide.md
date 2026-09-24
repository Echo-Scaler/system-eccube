# EC-CUBE 4.3.1 - Admin Panel တွင် Favourite Products Sub-Menu နှင့် စာရင်း ပြသခြင်း အဆင့်ဆင့် လမ်းညွှန်
## (Admin Favourite Products List Step-by-Step Implementation Guide)

ဤလက်စွဲစာအုပ်သည် EC-CUBE 4.3.1 ၏ Admin Panel ရှိ **Product (商品管理)** မီနူးအောက်တွင် **Favourite Product (အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်း)** Sub-menu အသစ် ထည့်သွင်းခြင်း၊ ဝယ်ယူသူများ အကြိုက်ဆုံးအဖြစ် မှတ်သားထားသော ကုန်ပစ္စည်းများ၏ **Image, Name, ID, Price Range, Favourite Count, Status** တို့အား သပ်ရပ်လှပစွာ ပြသပေးခြင်းတို့ကို လုပ်ငန်းအတွေ့အကြုံ (၆) လခန့်ရှိသော Junior Developer များ အလွယ်တကူ နားလည်သဘောပေါက်စေရန် **မြန်မာဘာသာ** ဖြင့် အသေးစိတ် ရေးသားထားသော လမ်းညွှန်ဖြစ်ပါသည်။

---

## မာတိကာ (Table of Contents)
1. [လုပ်ဆောင်ချက် အနှစ်ချုပ်နှင့် အဓိက လိုအပ်ချက်များ (Overview & Requirements)](#၁-လုပ်ဆောင်ချက်-အနှစ်ချုပ်နှင့်-အဓိက-လိုအပ်ချက်များ-overview--requirements)
2. ["List not include own favourite data" ၏ သဘောတရား ရှင်းလင်းချက်](#၂-list-not-include-own-favourite-data-၏-သဘောတရား-ရှင်းလင်းချက်)
3. [ဖိုင်တည်ဆောက်ပုံ ဇယား (Directory & File Structure)](#၃-ဖိုင်တည်ဆောက်ပုံ-ဇယား-directory--file-structure)
4. [အဆင့်ဆင့် ရေးသားတည်ဆောက်ပုံ (Step-by-Step Implementation)](#၄-အဆင့်ဆင့်-ရေးသားတည်ဆောက်ပုံ-step-by-step-implementation)
   - [အဆင့် (၁) - Admin Sub-Menu ချိတ်ဆက်ခြင်း (`eccube_nav.yaml`)](#အဆင့်-၁---admin-sub-menu-ချိတ်ဆက်ခြင်း-eccube_navyaml)
   - [အဆင့် (၂) - Database Repository တည်ဆောက်ခြင်း (`FavouriteProductRepository.php`)](#အဆင့်-၂---database-repository-တည်ဆောက်ခြင်း-favouriteproductrepositoryphp)
   - [အဆင့် (၃) - Admin Controller ရေးသားခြင်း (`FavouriteProductController.php`)](#အဆင့်-၃---admin-controller-ရေးသားခြင်း-favouriteproductcontrollerphp)
     - [(က) အဘယ်ကြောင့် Route (၂) ခု သုံးရသနည်း?](#က-အဘယ်ကြောင့်-route-၂-ခုကို-အသုံးပြုရသနည်း-ec-cube-၏-နေရာတိုင်းတွင်-ဤသို့-သုံးပါသလား-is-this-used-in-every-section-of-ec-cube)
     - [(ခ) `requirements={"page_no" = "\d+"}` ရှင်းလင်းချက်](#ခ-requirementspage_no---d-ဆိုသည်မှာ-အဘယ်နည်း-what-is-requirements)
     - [(ဂ) `@Template` ကို ဘာကြောင့် သုံးရသလဲ?](#ဂ-templateadminproductproduct_favouritetwig-ကို-ဘာကြောင့်-သုံးရသလဲ-why-use-template)
     - [(ဃ) Parameter များနှင့် Return Type (`: array`, Format or Rule?)](#ဃ-parameter-များနှင့်-return-type-ရှင်းလင်းချက်-param-return)
     - [(င) DocBlock `@param`, `@return`, `@var` မရေးရင် မှားသလား?](#င-docblock-param-return-var-များကို-မဖြစ်မနေ-ရေးရန်-လိုအပ်ပါသလား-မရေးရင်-မှားသလား-is-this-a-rule-or-format-will-it-be-wrong-if-omitted)
     - [(စ) Constructor Dependency Injection Architecture](#စ-property-နှင့်-constructor-ရေးသားပုံ-စံနှုန်း-constructor-dependency-injection-architecture)
     - [(ဆ) Repository DQL တွင် `AS HIDDEN` သုံးရသည့် အကြောင်းရင်း](#ဆ-repository-dql-တွင်-as-hidden-favorite_count-ကို-အဘယ်ကြောင့်-သုံးရသလဲ)
     - [(ဇ) `ORDER BY` (၂) ကြိမ် သုံးရသည့် အကြောင်းရင်း (Pagination Drift ကာကွယ်ခြင်း)](#ဇ-အဘယ်ကြောင့်-order-by-၂-ကြိမ်-သုံးရသနည်း-orderby-addorderby)
     - [(ဈ) Controller တွင် Session အသုံးပြုပုံ (Page Memory)](#ဈ-controller-တွင်-session-ကို-အဘယ်ကြောင့်-အသုံးပြုထားသနည်း-session-usage)
     - [(ည) KnpPaginator ၏ `['wrap-queries' => true]` ရှင်းလင်းချက်](#ည-knppaginator-၏-wrap-queries--true-ဆိုသည်မှာ-အဘယ်နည်း)
   - [အဆင့် (၄) - Admin Twig View Template ရေးသားခြင်း (`product_favourite.twig`)](#အဆင့်-၄---admin-twig-view-template-ရေးသားခြင်း-product_favouritetwig)
5. [Cache Clear ပြုလုပ်ခြင်းနှင့် စစ်ဆေးခြင်း (Terminal Commands)](#၅-cache-clear-ပြုလုပ်ခြင်းနှင့်-စစ်ဆေးခြင်း-terminal-commands)
6. [စနစ်စမ်းသပ် စစ်ဆေးခြင်း (Verification & Testing Guide)](#၆-စနစ်စမ်းသပ်-စစ်ဆေးခြင်း-verification--testing-guide)

---

## ၁။ လုပ်ဆောင်ချက် အနှစ်ချုပ်နှင့် အဓိက လိုအပ်ချက်များ (Overview & Requirements)

EC-CUBE တွင် ဝယ်ယူသူ (Customer) များသည် မိမိတို့ နှစ်သက်သော ကုန်ပစ္စည်းများကို Front Store မှတစ်ဆင့် Favorite (お気に入り) အဖြစ် မှတ်သားထားနိုင်ပါသည်။  
ဆိုင်ရှင် (Admin / Merchant) အနေဖြင့် မည်သည့် ကုန်ပစ္စည်းများသည် လူကြိုက်အများဆုံး ဖြစ်သည်ကို Admin Panel မှ အလွယ်တကူ သိရှိစီမံနိုင်ရန် အောက်ပါအတိုင်း တည်ဆောက်ထားပါသည်:

1. **Sub-Menu Placement:** Admin Panel ဘယ်ဘက် Side Navigation ရှိ `Product (商品管理)` အောက်တွင် `Favourite Product (お気に入り商品)` ဟူသော Sub-menu အသစ် ပေါ်လာစေရမည်။
2. **Display Columns (ပြသရမည့် ဒေတာများ):**
   - **ID:** Product ID
   - **Image:** ကုန်ပစ္စည်း Thumbnail ဓာတ်ပုံ
   - **Name:** ကုန်ပစ္စည်း အမည်နှင့် Product Code
   - **Price Range:** အခွန်ပါ ဈေးနှုန်း Range (ဥပမာ- `¥5,500 ～ ¥121,000`)
   - **Favourite Count:** ဝယ်ယူသူများ အကြိုက်ဆုံး မှတ်သားထားသည့် စုစုပေါင်း အကြိမ်အရေအတွက် (Heart Badge ဖြင့်)
   - **Status:** ကုန်ပစ္စည်း Display Status (公開 / 非公開)
3. **Pagination:** ဒေတာများပြားလာပါက စာမျက်နှာ ခွဲခြားပြသနိုင်သော စနစ် ပါဝင်သည်။

---

## ၂။ "List not include own favourite data" ၏ သဘောတရား ရှင်းလင်းချက်

> **အရေးကြီးသော သဘောတရား:**
> Database ဇယားဖြစ်သော `dtb_customer_favorite_product` တွင် Customer တစ်ဦးစီ မှတ်သားထားသော Raw Record များ (Customer ID, Product ID, Create Date) အလိုက် သိမ်းဆည်းထားပါသည်။
> 
> * **Customer ၏ Mypage Favorite (`/mypage/favorite`):** ထိုနေရာသည် ဝယ်ယူသူ တစ်ဦးချင်းစီ၏ **ကိုယ်ပိုင် (Own Favourite Data)** ကိုသာ ပြသသော နေရာဖြစ်ပါသည်။
> * **Admin Panel Favourite Products စာရင်း (`/admin/product/favourite`):** 
>   1. Admin Panel သည် မည်သည့် Customer တစ်ဦးချင်းစီ၏ Private Data ကိုမျှ သီးခြား row အလိုက် မပြသပါ။
>   2. ၎င်းအစား ကုန်ပစ္စည်း (Product) တစ်ခုချင်းစီအလိုက် ဝယ်ယူသူအားလုံး၏ Favourite ပြုလုပ်ထားသော အရေအတွက်ကို **Aggregate (Count)** ပြုလုပ်ပြီး ကုန်ပစ္စည်း စာရင်းအနေဖြင့်သာ ပြသပါသည်။
>   3. ထို့အပြင် ဝယ်ယူသူ မည်သူမျှ Favorite ပြုလုပ်ထားခြင်း မရှိသော (Count = 0) ကုန်ပစ္စည်းများကို စာရင်းထဲတွင် ထည့်သွင်းမပြသဘဲ **Exclude (ဖယ်ထုတ်)** ထားပါသည်။ ထို့ကြောင့် တကယ့် Favourite Data အစစ်အမှန် ရှိသော ကုန်ပစ္စည်းများကိုသာ သန့်ရှင်းစွာ တွေ့မြင်ရမည် ဖြစ်ပါသည်။

---

## ၃။ ဖိုင်တည်ဆောက်ပုံ ဇယား (Directory & File Structure)

EC-CUBE Core စည်းမျဉ်းအရ `src/Eccube/` ထဲမှ မူရင်းဖိုင်များကို လုံးဝ မထိခိုက်စေဘဲ `app/Customize/` နှင့် `app/template/` အောက်တွင်သာ သီးသန့် ရေးသားထားပါသည်:

```text
eccube-4.3.1/
├── app/
│   ├── config/
│   │   └── eccube/
│   │       └── packages/
│   │           └── eccube_nav.yaml                           <-- [ပြင်ဆင်] Admin Sub-Menu ချိတ်ဆက်ခြင်း
│   ├── Customize/
│   │   ├── Controller/
│   │   │   └── Admin/
│   │   │       └── Product/
│   │   │           └── FavouriteProductController.php        <-- [အသစ်] Admin Request & Export Controller
│   │   └── Repository/
│   │       └── FavouriteProductRepository.php                <-- [အသစ်] DQL QueryBuilder တည်ဆောက်သော Repository
│   └── template/
│       └── admin/
│           └── Product/
│               └── product_favourite.twig                    <-- [အသစ်] Admin UI Twig Template
└── My-Task-List-Folder/
    └── Favourite-Product-List/
        └── favourite_product_admin_step_by_step_guide.md     <-- [ယခုဖိုင်] ရှင်းလင်းချက် မြန်မာလက်စွဲ
```

---

## ၄။ အဆင့်ဆင့် ရေးသားတည်ဆောက်ပုံ (Step-by-Step Implementation)

---

### အဆင့် (၁) - Admin Sub-Menu ချိတ်ဆက်ခြင်း (`eccube_nav.yaml`)

📁 **ဖိုင်လမ်းကြောင်း:** [app/config/eccube/packages/eccube_nav.yaml](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/config/eccube/packages/eccube_nav.yaml)

#### ဘာကြောင့် ဒီဖိုင်ကို ပြင်ဆင်ရသလဲ? (Why?)
EC-CUBE 4 တွင် Admin Panel ၏ ဘယ်ဘက် Side Navigation Menu များကို Controller ထဲတွင် မရေးဘဲ `eccube_nav.yaml` ဖိုင်ထဲတွင် Parameter အနေဖြင့် စနစ်တကျ စီမံခန့်ခွဲပါသည်။ ထို့ကြောင့် `Product` မီနူးအောက်တွင် Sub-menu အသစ် ပေါ်လာစေရန် ဤဖိုင်တွင် စာရင်းသွင်းပေးရခြင်း ဖြစ်ပါသည်။

#### မည်သို့ အလုပ်လုပ်သနည်း? (How it works?)
- `favourite_product:` ဟူသော unique key အောက်တွင်:
  - `name:` သည် ဘာသာပြန် Key ဖြစ်ပြီး `admin.favorite.favorite_products` (Japanese: `お気に入り商品` / English: `Favourite Products`) ကို ညွှန်းဆိုပါသည်။
  - `url:` သည် ဖွင့်လှစ်မည့် Symfony Route Name ဖြစ်သော `admin_product_favourite` ကို ချိတ်ဆက်ပေးပါသည်။

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
                # --- အသစ် ထည့်သွင်းထားသော Sub-Menu ---
                favourite_product:
                    name: admin.favorite.favorite_products
                    url: admin_product_favourite
```

---

### အဆင့် (၂) - Database Repository တည်ဆောက်ခြင်း (`FavouriteProductRepository.php`)

📁 **ဖိုင်လမ်းကြောင်း:** [app/Customize/Repository/FavouriteProductRepository.php](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/Customize/Repository/FavouriteProductRepository.php)

#### ဘာကြောင့် ဒီဖိုင်ကို ရေးရသလဲ? (Why?)
Symfony/Doctrine ၏ **Repository Pattern** အရ Database ဆီမှ Data ဆွဲထုတ်ခြင်း၊ SQL/DQL Query တည်ဆောက်ခြင်းများကို Controller ထဲတွင် မရေးဘဲ Repository Class သီးသန့်တွင် ရေးသားရပါမည်။  
အထူးသဖြင့် Memory မပြည့်စေရန်နှင့် KnpPaginator (`$paginator->paginate()`) နှင့် တိုက်ရိုက်တွဲဖက် အသုံးပြုနိုင်ရန် `QueryBuilder ($qb)` ကို Return ပေးရပါမည်။

#### မည်သို့ အလုပ်လုပ်သနည်း? (How it works?)
1. **DQL Correlated Subquery:**
   ```php
   $qb->addSelect('(SELECT COUNT(cfp.id) FROM ' . CustomerFavoriteProduct::class . ' cfp WHERE cfp.Product = p) AS HIDDEN favorite_count')
   ```
   ကုန်ပစ္စည်းတစ်ခုချင်းစီအတွက် `dtb_customer_favorite_product` ဇယားမှ ID အရေအတွက်ကို ရေတွက်ပြီး `favorite_count` အနေဖြင့် တွဲဖက် ယူဆောင်သည်။ `HIDDEN` ဟု သတ်မှတ်ထားသဖြင့် Product Entity Structure ကို မပျက်စီးစေပါ။
2. **`WHERE > 0` စစ်ဆေးခြင်း:**
   ```php
   ->where('(SELECT COUNT(cfp2.id) FROM ' . CustomerFavoriteProduct::class . ' cfp2 WHERE cfp2.Product = p) > 0')
   ```
   ဝယ်ယူသူ မည်သူမျှ Favorite မလုပ်ထားသော ပစ္စည်းများ (Count = 0) ကို ဖယ်ထုတ်ပြီး အနည်းဆုံး ၁ ကြိမ်နှင့်အထက် Favorite အလုပ်ခံထားရသော ပစ္စည်းများကိုသာ ရွေးထုတ်ပေးပါသည်။
3. **Sorting:**
   `favorite_count DESC` ဖြင့် အကြိုက်ဆုံး အများဆုံး ပစ္စည်းကို ထိပ်ဆုံးမှ စီပေးပါသည်။
4. **MySQL 8 ONLY_FULL_GROUP_BY 100% Safe:**
   `GROUP BY` မသုံးဘဲ Subquery ကို သုံးထားသောကြောင့် MySQL 8 ၏ SQL Mode Error များ လုံးဝ မဖြစ်ပေါ်နိုင်ပါ။

```php
<?php

namespace Customize\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Eccube\Entity\CustomerFavoriteProduct;
use Eccube\Entity\Product;
use Eccube\Repository\AbstractRepository;

/**
 * Class FavouriteProductRepository
 *
 * EC-CUBE 4.3 Standard Repository for Favourite Products
 */
class FavouriteProductRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Favorite အများဆုံး ကုန်ပစ္စည်းများ စာရင်းအတွက် QueryBuilder ရယူခြင်း
     * (MySQL 8 ONLY_FULL_GROUP_BY 100% Safe & Standard Compliant)
     *
     * @return QueryBuilder
     */
    public function getFavouriteDb(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');
        $qb->addSelect('(SELECT COUNT(cfp.id) FROM ' . CustomerFavoriteProduct::class . ' cfp WHERE cfp.Product = p) AS HIDDEN favorite_count')
            ->where('(SELECT COUNT(cfp2.id) FROM ' . CustomerFavoriteProduct::class . ' cfp2 WHERE cfp2.Product = p) > 0')
            ->orderBy('favorite_count', 'DESC')
            ->addOrderBy('p.id', 'DESC');

        return $qb;
    }
}
```

---

### အဆင့် (၃) - Admin Controller ရေးသားခြင်း (`FavouriteProductController.php`)

📁 **ဖိုင်လမ်းကြောင်း:** [app/Customize/Controller/Admin/Product/FavouriteProductController.php](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/Customize/Controller/Admin/Product/FavouriteProductController.php)

#### ဘာကြောင့် ဒီဖိုင်ကို ရေးရသလဲ? (Why?)
Admin က Browser မှတစ်ဆင့် `/admin/product/favourite` သို့ ဝင်ရောက်လာသည့် HTTP Request ကို လက်ခံခြင်း၊ Pagination စီမံခြင်းနှင့် Twig Template သို့ Data ပေးပို့ခြင်းတို့ကို စီမံခန့်ခွဲရန် ဖြစ်ပါသည်။

#### မည်သို့ အလုပ်လုပ်သနည်း? (How it works?)
1. **EC-CUBE Standard Routing အဘယ်ကြောင့် Route (၂) ခု လိုအပ်သနည်း?:**
   EC-CUBE ၏ Standard Product List (`ProductController`) နှင့် Order List (`OrderController`) တို့တွင် စာရင်းပြသသည့် `index` Action တစ်ခုစီအတွက် အောက်ပါ Route (၂) ခုသာ သတ်မှတ်ရပါသည်:
   - **Base Route:** `@Route("/%eccube_admin_route%/product/favourite", name="admin_product_favourite", methods={"GET"})`  
     စာမျက်နှာ ၁ ကို စတင်ဖွင့်လှစ်သည့် အဓိက URL ဖြစ်ပါသည်။
   - **Pagination Route:** `@Route("/%eccube_admin_route%/product/favourite/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_favourite_page", methods={"GET"})`  
     စာမျက်နှာ ၂၊ ၃ စသည်ဖြင့် Next/Prev နှိပ်သည့်အခါ စာမျက်နှာနံပါတ် `{page_no}` ပါဝင်သော URL အဖြစ် အသုံးပြုရန် ဖြစ်ပါသည်။
2. **Dependency Injection:** `FavouriteProductRepository` နှင့် `PaginatorInterface` တို့ကို Constructor တွင် ထည့်သွင်းလက်ခံထားပါသည်။
3. **Session Memory for `page_no`:**
   Admin က စာမျက်နှာ ၃ ကို ရောက်နေစဉ် ကုန်ပစ္စည်းတစ်ခုအား Edit ဝင်ပြင်ပြီး ပြန်ထွက်လာပါက Session ထဲမှ စာမျက်နှာ ၃ သို့ ပြန်လည်ရောက်ရှိစေရန် Session Memory ထည့်သွင်းထားပါသည်။
4. **KnpPaginator Pagination:**
   `$this->paginator->paginate($qb, $page_no, $page_count, ['wrap-queries' => true])` ဖြင့် QueryBuilder ကို Pagination ခွဲထုတ်ပေးပါသည်။

---

### အထူးလေ့လာရန် - Controller Annotation များနှင့် Parameter တစ်ခုချင်းစီ၏ အသေးစိတ် အလုပ်လုပ်ပုံ (Deep-Dive Controller Annotations & Arguments)

Junior Developer များ ရှင်းလင်းစွာ သိရှိနားလည်စေရန် Controller တွင် ရေးသားထားသော Annotation တစ်ကြောင်းချင်းစီ၏ တာဝန်နှင့် အသုံးဝင်ပုံကို အောက်တွင် အသေးစိတ် ခွဲခြမ်းရှင်းပြထားပါသည်:

```php
/**
 * @Route("/%eccube_admin_route%/product/favourite", name="admin_product_favourite", methods={"GET"})
 * @Route("/%eccube_admin_route%/product/favourite/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_favourite_page", methods={"GET"})
 * @Template("@admin/Product/product_favourite.twig")
 *
 * @param Request $request
 * @param int|null $page_no
 * @return array
 */
public function index(Request $request, $page_no = null): array
```

#### (က) အဘယ်ကြောင့် Route (၂) ခုကို အသုံးပြုရသနည်း? EC-CUBE ၏ နေရာတိုင်းတွင် ဤသို့ သုံးပါသလား? (Is this used in every section of EC-CUBE?)
> **တိုတိုနှင့် လိုရင်း အဖြေ:**  
> **ဟုတ်ကဲ့၊ အသုံးပြုပါသည်။** EC-CUBE တွင် စာမျက်နှာခွဲ (Pagination) ပါဝင်သော **List Screen (စာရင်းပြ မျက်နှာပြင်) တိုင်း** တွင် ဤ Base Route + Page Route စုံတွဲကို စံအဖြစ် မဖြစ်မနေ အသုံးပြုထားပါသည်။

* **EC-CUBE Core ဖိုင်များရှိ သာဓကများ (Core References):**
  1. **Product List (ကုန်ပစ္စည်းစာရင်း):** `src/Eccube/Controller/Admin/Product/ProductController.php` (Line 151-152)  
     `@Route("/%eccube_admin_route%/product", name="admin_product")`  
     `@Route("/%eccube_admin_route%/product/page/{page_no}", name="admin_product_page")`
  2. **Order List (အော်ဒါစာရင်း):** `src/Eccube/Controller/Admin/Order/OrderController.php` (Line 192-193)  
     `@Route("/%eccube_admin_route%/order", name="admin_order")`  
     `@Route("/%eccube_admin_route%/order/page/{page_no}", name="admin_order_page")`
  3. **Customer List (ဝယ်ယူသူစာရင်း):** `src/Eccube/Controller/Admin/Customer/CustomerController.php` (Line 89-90)  
     `@Route("/%eccube_admin_route%/customer", name="admin_customer")`  
     `@Route("/%eccube_admin_route%/customer/page/{page_no}", name="admin_customer_page")`
  4. **News List (သတင်းများစာရင်း):** `src/Eccube/Controller/Admin/Content/NewsController.php` (Line 52-53)  
     `@Route("/%eccube_admin_route%/content/news", name="admin_content_news")`  
     `@Route("/%eccube_admin_route%/content/news/page/{page_no}", name="admin_content_news_page")`
  5. **Front Store Product List (ဝယ်သူဘက် မျက်နှာပြင်):** `src/Eccube/Controller/ProductController.php` (Line 60-61)  
     `@Route("/products/list", name="product_list")`  
     `@Route("/products/list/page/{page_no}", name="product_list_page")`

* **ဘာကြောင့် တစ်ခုတည်း မရေးဘဲ (၂) ခု ခွဲရေးသနည်း?:**
  1. **Base Route (`/product/favourite`):** Menu ကို နှိပ်ပြီး စတင်ဝင်ရောက်လာချိန်တွင် Page Number မပါသော URL အနေဖြင့် ဝင်ရောက်စေရန် ဖြစ်ပါသည်။
  2. **Page Route (`/product/favourite/page/{page_no}`):** အောက်ခြေရှိ Pager ကလစ်နှိပ်သည့်အခါ စာမျက်နှာနံပါတ်ပါသော Clean URL (`/page/2`, `/page/3`) အဖြစ် တည်ဆောက်နိုင်စေရန် ဖြစ်ပါသည်။ (အကယ်၍ Page Route တစ်ခုတည်းသာ ရေးထားပါက Menu မှ နှိပ်လျှင် `{page_no}` မပါသဖြင့် 404 Error တက်သွားပါမည်)။

---

#### (ခ) `requirements={"page_no" = "\d+"}` ဆိုသည်မှာ အဘယ်နည်း? (What is requirements?)
Symfony Routing တွင် `requirements` ဆိုသည်မှာ URL ထဲတွင် ပါဝင်လာမည့် Parameter ၏ အချက်အလက် အမျိုးအစားကို **Regular Expression (Regex)** ဖြင့် စစ်ဆေးကန့်သတ်ပေးသော စနစ် (Validation Constraint) ဖြစ်ပါသည်။

* **`\d+` ၏ အဓိပ္ပာယ်:**
  - `\d` = Digit (ဂဏန်း ၀ မှ ၉ အထိ)
  - `+` = တစ်လုံး သို့မဟုတ် တစ်လုံးထက်မက (One or more)
  - ထို့ကြောင့် `{page_no}` နေရာတွင် **ကိန်းဂဏန်း အစစ်အမှန်များသာ** ဝင်ရောက်ခွင့် ပြုမည်ဟု သတ်မှတ်ခြင်း ဖြစ်ပါသည်။ (ဥပမာ- `/page/1`, `/page/25`)

* **အဘယ်ကြောင့် `requirements` ကို ထည့်သွင်းရသနည်း? (အကျိုးကျေးဇူး ၂ ချက်):**
  1. **လုံခြုံရေးနှင့် Error ကာကွယ်ခြင်း (Security & Clean 404):**  
     အကယ်၍ Hacker တစ်ဦး သို့မဟုတ် User တစ်ဦးက `/product/favourite/page/abc` သို့မဟုတ် SQL Injection စာသားများ ရိုက်ထည့်လိုက်ပါက Symfony Router က ချက်ချင်းသိရှိပြီး Controller သို့ မရောက်စေဘဲ **404 Not Found အနေဖြင့် လမ်းကြောင်းလွှဲပြီး အလိုအလျောက် ပိတ်ဆို့ပေးပါသည်**။ Controller ထဲတွင် Type Error တက်ခြင်း သို့မဟုတ် Database Crash ဖြစ်ခြင်းမှ ကာကွယ်ပေးပါသည်။
  2. **Route လမ်းကြောင်း ထပ်တိုက်တွေ့မှု မဖြစ်စေခြင်း (Route Collision Prevention):**  
     အကယ်၍ နောက်ထပ် Action အသစ်တစ်ခုဖြစ်သော `/product/favourite/export` သို့မဟုတ် `/product/favourite/new` စသည်ဖြင့် ရေးသားထားပါက Symfony သည် "export" သို့မဟုတ် "new" ကို ဂဏန်းမဟုတ်မှန်း သိရှိသဖြင့် `{page_no}` အဖြစ် မမှားယွင်းဘဲ မိမိဆိုင်ရာ Route အစစ်ဆီသို့ တိကျစွာ လမ်းညွှန်ပေးနိုင်ပါသည်။

---

#### (ဂ) `@Template("@admin/Product/product_favourite.twig")` သည် EC-CUBE ၏ Format လား? Rule လား? (Is this a Format or a Rule in EC-CUBE?)

> 💡 **တိုတိုနှင့် လိုရင်း အဖြေ:**  
> **၎င်းသည် PHP Engine ၏ မဖြစ်မနေ ဥပဒေ (Runtime Rule) မဟုတ်ပါ။**  
> သို့သော် **EC-CUBE ၏ တရားဝင် စံပြု ရေးထုံးစံ (Official Architectural Standard Format / Convention)** ဖြစ်ပါသည်။

* **အဘယ်ကြောင့် Rule (ဥပဒေ) မဟုတ်သနည်း?**
  - PHP နှင့် Symfony Engine အနေဖြင့် `@Template` မပါဘဲ သာမန်အတိုင်း `return $this->render('@admin/...twig', [...]);` ဟု ရေးသားပါကလည်း မည်သည့် Error မျှ မတက်ဘဲ ပုံမှန်အတိုင်း အလုပ်လုပ်ပါသည်။ ထို့ကြောင့် မဖြစ်မနေ လိုက်နာရမည့် Hard Rule မဟုတ်ပါ။

* **အဘယ်ကြောင့် EC-CUBE ၏ Standard Format (စံနှုန်း) ဖြစ်ရသနည်း?**
  - EC-CUBE ၏ Core Controller များ ဖြစ်ကြသော `src/Eccube/Controller/Admin/Product/ProductController.php`၊ `OrderController.php`၊ `CustomerController.php` စသည်တို့ အားလုံးတွင် **၁၀၀% တညီတညွတ်တည်း `@Template` Format ကိုသာ အသုံးပြုထားပါသည်**။

* **EC-CUBE က ဤ Format ကို အဓိက စံအဖြစ် သတ်မှတ်ထားရသည့် အကြောင်းရင်း (၃) ချက်:**
  1. **Plugin နှင့် Event Listener များအတွက် လွယ်ကူစေခြင်း (Plugin Extensibility):**  
     EC-CUBE တွင် Plugin များ သို့မဟုတ် Custom Event Listener များသည် Controller မှ ပေးပို့လိုက်သော Data များကို စာမျက်နှာ မပြသမီ ကြားဖြတ် ဖမ်းယူပြီး ဒေတာ အသစ်များ ထပ်ပေါင်းထည့်လေ့ရှိပါသည် (Event Hook Points)။  
     Controller က `array` ကို ပြန်ပေးမှသာ Plugin များက ထို array ထဲသို့ အလွယ်တကူ Data ဝင်ရောက် ဖြည့်စွက်နိုင်မည် ဖြစ်ပါသည်။ အကယ်၍ `$this->render()` ဖြင့် HTML Response အဖြစ် တန်းပြောင်းပစ်လိုက်ပါက Plugin များက Data ဝင်ပြင်ရန် အလွန် ခက်ခဲသွားပါမည်။
  2. **Core Codebase နှင့် တစ်သားတည်း တူညီနေစေခြင်း (Project Consistency):**  
     EC-CUBE Developer များ၊ Plugin ရေးသားသူများ အားလုံးသည် ဤ `@Template` စံနှုန်းအတိုင်း ရေးသားကြသဖြင့် အဖွဲ့လိုက် လုပ်ကိုင်ရာတွင် Code ဖတ်ရှုရ လွယ်ကူစေပါသည်။
  3. **Clean Code & Single Responsibility:**  
     Controller သည် View Template ကို ရွေးချယ်ဖော်ပြခြင်းနှင့် Data ရယူခြင်းကိုသာ အဓိက အာရုံစိုက်ပြီး View rendering ပြုလုပ်ခြင်းကို Symfony Framework ထံ တာဝန်လွှဲပေးထားသဖြင့် ကုဒ် ပိုမို သန့်ရှင်းစေပါသည်။

---

#### (ဃ) Parameter များနှင့် Return Type ရှင်းလင်းချက် (`@param`, `@return`)

1. **`Request $request` (`@param Request $request`):**
   * Symfony ၏ `HttpFoundation\Request` Object ဖြစ်ပါသည်။
   * Symfony ၏ **Argument Resolver (Dependency Injection)** က အလိုအလျောက် ထည့်သွင်းပေး (Inject လုပ်ပေး) သဖြင့် User ထံမှ လာသော HTTP Request အချက်အလက်များ (GET/POST Parameters, Session, Headers, Client IP) ကို Controller ထဲတွင် အလွယ်တကူ ဆွဲယူသုံးစွဲနိုင်ရန် ဖြစ်ပါသည်။
   * ဥပမာ- `$this->session->set(...)` အတွက် အသုံးပြုပါသည်။

2. **`$page_no = null` (`@param int|null $page_no`):**
   * Base Route (`/product/favourite`) ဖြင့် ဝင်လာပါက URL တွင် `{page_no}` မပါသဖြင့် Default တန်ဖိုး **`null`** ဖြစ်နေမည်။
   * Page Route (`/product/favourite/page/3`) ဖြင့် ဝင်လာပါက Symfony က URL ထဲမှ `3` ကို ဆွဲထုတ်၍ ဤ parameter ထဲသို့ **`3`** အဖြစ် ထည့်သွင်းပေးပါသည်။
   * ထို့ကြောင့် Controller အတွင်း၌:
     ```php
     if (null !== $page_no) {
         // Page URL ဖြင့် ဝင်လာပါက Session တွင် သိမ်းသည်
         $this->session->set('eccube.admin.product.favourite.page_no', (int) $page_no);
     } else {
         // Base URL ဖြင့် ဝင်လာပါက Session မှ ပြန်ယူသည် (မရှိလျှင် 1)
         $page_no = $this->session->get('eccube.admin.product.favourite.page_no', 1);
     }
     ```
     ဟု စနစ်တကျ ကိုင်တွယ်နိုင်ခြင်း ဖြစ်ပါသည်။

3. **`public function index(...): array` (`@return array`):**
   * **Return က `array` ဖြစ်မှန်း ဘယ်လိုသိသလဲ? (How to know return is array?):**
     1. **Code ထဲရှိ `return [...]` ကို ကြည့်ခြင်း:** Controller method ၏ အောက်ဆုံးလိုင်းတွင်:
        ```php
        return [
            'pagination' => $pagination,
            'page_no' => $page_no,
        ];
        ```
        `[...]` သည် PHP Associative Array ဖြစ်သောကြောင့် ဤ Method သည် `array` ကို return ပြန်ပေးနေခြင်းဖြစ်ကြောင်း ချက်ချင်း သိရှိနိုင်ပါသည်။
     2. **PHP Type Declaration `: array`:** Method အမည်ဘေးတွင် `: array` ဟု ရေးထားခြင်းဖြင့် PHP Engine အား ဤ Method သည် array ကလွဲ၍ အခြား Data Type ပြန်ခွင့်မရှိကြောင်း တိကျစွာ သတ်မှတ်ထားခြင်း ဖြစ်ပါသည်။
     3. **DocBlock `@return array`:** Code ဖတ်ရှုသူများနှင့် IDE များ သိရှိနိုင်ရန် အပေါ်က DocBlock တွင် `@return array` ဟု ဖော်ပြပေးထားခြင်း ဖြစ်ပါသည်။

   * **ဒါက Rule လား? Format လား? Controller ရဲ့ `index()` တိုင်း `array` ပြန်ရမှာလား? (Is this a rule or format? Must all index methods return an array?):**
     > 🛑 **အဖြေမှာ - `index()` တိုင်း မဖြစ်မနေ `array` ပြန်ရမည် မဟုတ်ပါ။**  
     > Controller Method တစ်ခု `array` ပြန်နိုင်ခြင်းသည် အပေါ်တွင် **`@Template(...)` Annotation ပါရှိနေသောကြောင့်သာ** ဖြစ်ပါသည်။

     ---

     #### 💡 အရေးကြီးသော ရှင်းလင်းချက် - "စည်းမျဉ်း (၁) - `@Template` ပါရှိလျှင် `array` ပြန်ခွင့်ရှိသည်" ဆိုသည်မှာ အဘယ်နည်း? (Deep Dive)

     Junior Developer များ နားလည်ရ လွယ်ကူစေရန် သာမန် Symfony ပုံစံ နှင့် `@Template` ပါသော ပုံစံကို ဘေးချင်းယှဉ် နှိုင်းယှဉ်ပြပါမည်:

     ##### ၁။ ပုံမှန် သာမန် Symfony Controller (နည်းလမ်း A - Normal Symfony Way):
     Symfony Framework ၏ မူရင်းစည်းကမ်းအရ Controller သည် Browser ဆီသို့ HTML ပြသရန်အတွက် **`Response`** Object ကို မဖြစ်မနေ ကိုယ်တိုင် တည်ဆောက်ပြီး Return ပြန်ပေးရပါသည်:
     ```php
     // @Template မသုံးထားသော သာမန် ပုံစံ
     public function index(Request $request): Response
     {
         $pagination = ...;

         // $this->render() သည် Twig ဖိုင်ကို ခေါ်ယူပြီး 'Response' Object အဖြစ် ထုတ်လုပ်ပေးပါသည်
         return $this->render('@admin/Product/product_favourite.twig', [
             'pagination' => $pagination,
             'page_no' => $page_no,
         ]);
     }
     ```
     * ဤနည်းလမ်းတွင် Developer သည် `$this->render()` ကို **လက်ဖြင့် ကိုယ်တိုင် ရေးရပြီး (Manually Code / Explicitly Invoke)** Return Type သည်လည်း **`Response`** ဖြစ်ရပါမည်။

     ---

     #### 🔍 စကားလုံးရှင်းလင်းချက် - "လက်ဖြင့် ရေးရသည် (Manually Write)" ဆိုသည်မှာ အဘယ်နည်း?
     * **"လက်ဖြင့် ရေးရသည်" (Manual / Explicit):**  
       ဆိုလိုသည်မှာ Developer က `$this->render('@admin/...twig', [...])` ဟူသော Function ခေါ်ဆိုမှု စာကြောင်းကို Controller method ထဲတွင် **keyboard ဖြင့် ကိုယ်တိုင် စာရိုက်ထည့် (Code ရေးသား)** ပေးရခြင်းကို ဆိုလိုပါသည်။
     * **"အလိုအလျောက် ဖြစ်သွားသည်" (Automatic / Implicit):**  
       အကယ်၍ `@Template` ကို သုံးထားပါက ထို `$this->render()` စာကြောင်းကို Developer က ကိုယ်တိုင် ရေးစရာ မလိုတော့ဘဲ Symfony Framework က နောက်ကွယ်မှ **အလိုအလျောက် ကြားဖြတ် ခေါ်ဆိုပေးသွားခြင်း** ကို ဆိုလိုပါသည်။

     ---

     #### ⚖️ `return` နှင့် `render` မည်သို့ ကွာခြားသနည်း? ဘယ်လို သုံးရသလဲ? (Difference between `return` and `render`)

     Junior Developer များ အများဆုံး ရောထွေးလေ့ရှိသော `return` နှင့် `render` ၏ မတူညီသည့် သဘောသဘာဝမှာ အောက်ပါအတိုင်း ဖြစ်ပါသည်:

     | အချက်အလက် | `return` (PHP Keyword) | `render()` / `$this->render()` (Symfony Helper Method) |
     | :--- | :--- | :--- |
     | **သူက ဘာလဲ?** | **PHP Programming Language ၏ မူရင်း စကားလုံး (Core Keyword)** ဖြစ်ပါသည်။ | **Symfony Framework ၏ `AbstractController` တွင် ပါဝင်သော Function (Method)** ဖြစ်ပါသည်။ |
     | **သူ့တာဝန်က ဘာလဲ?** | Function သို့မဟုတ် Method တစ်ခု၏ အလုပ်ပြီးဆုံးချိန်တွင် ရရှိလာသော အဖြေ (Result) ကို Function အပြင်သို့ **ပြန်လည် ပေးပို့ (Return)** ရန်နှင့် အလုပ်ကို ရပ်တန့်ရန် သုံးပါသည်။ | Twig Template ဖိုင်နှင့် PHP Data Array တို့ကို ပေါင်းစပ်၍ HTML အဖြစ် ချဲ့ထွင်ပေးပြီး **`Response` Object အဖြစ် ထုတ်လုပ်ပေးရန်** သုံးပါသည်။ |
     | **Twig သို့မဟုတ် HTML ကို သိသလား?** | **လုံးဝ မသိပါ။** `return 10;`, `return "Hello";`, `return true;`, `return [...]` စသဖြင့် မည်သည့် Data မဆို အပြင်သို့ လှမ်းပေးရုံ သက်သက်သာ ဖြစ်ပါသည်။ | **သိပါသည်။** Twig ဖိုင်ကို ရှာဖွေပြီး Data များနှင့် ပေါင်းစပ်၍ Browser နားလည်သော HTML Response အဖြစ် တည်ဆောက်ပေးသည့် စက်ရုံ ဖြစ်ပါသည်။ |
     | **တစ်လုံးတည်း သီးသန့် ရေးလို့ရသလား?** | ရပါသည်။ ဥပမာ - `return $data;` | မရပါ။ `$this->render(...)` က Response ထုတ်ပေးသော်လည်း `return` မပါပါက Browser ဆီသို့ ရောက်မသွားပါ။ ထို့ကြောင့် `return $this->render(...)` ဟု တွဲသုံးရပါသည်။ |

     ##### လက်တွေ့ အသုံးပြုပုံ (How to use):

     * **ပုံစံ (၁) - `render` နှင့် `return` ကို တွဲသုံးခြင်း (`return $this->render(...)`):**
       ```php
       public function index(): Response
       {
           // အဆင့် ၁: $this->render() က Twig + Data ကို ပေါင်းပြီး Response Object တစ်ခု ထုတ်လုပ်သည်
           // အဆင့် ၂: return က ထို Response Object ကို Symfony အပြင်သို့ လှမ်းပေးလိုက်သည်
           return $this->render('@admin/Product/product_favourite.twig', [
               'pagination' => $pagination,
           ]);
       }
       ```

     * **ပုံစံ (၂) - `@Template` သုံးထားသည့်အခါ `return` တစ်ခုတည်း သုံးခြင်း (`return [...]`):**
       ```php
       /**
        * @Template("@admin/Product/product_favourite.twig")
        */
       public function index(): array
       {
           // Controller ထဲတွင် $this->render() ကို လုံးဝ မရေးတော့ပါ!
           // return သည် data array သက်သက်ကိုသာ အပြင်သို့ လှမ်းပေးလိုက်သည်
           // Symfony ၏ @Template က ထို data ကို ယူပြီး render() ကို နောက်ကွယ်မှ အလိုအလျောက် ခေါ်ပေးသွားသည်
           return [
               'pagination' => $pagination,
           ];
       }
       ```

     ---

     ##### ၂။ `@Template` ဆိုတာ ဘာလဲ? သုံးမယ်ဆိုရင် Code မှာ ဘာတွေ ပြောင်းလဲရမလဲ? (What is @Template & How to change if we use it?)

     * **`@Template` ဆိုတာ ဘာလဲ? (What is `@Template`?):**  
       - ၎င်းသည် Symfony Framework ၏ `SensioFrameworkExtraBundle` မှ ထောက်ပံ့ပေးထားသော **DocBlock Annotation (ဆိုင်းဘုတ် / Metadata Label)** တစ်ခု ဖြစ်ပါသည်။  
       - Full Class Name: `Sensio\Bundle\FrameworkExtraBundle\Configuration\Template`  
       - **အဓိက တာဝန်:** Controller Method ၏ အပေါ်တွင် ဤ Annotation ကို ရေးသားထားခြင်းဖြင့် Symfony အား *"ဤ Method သည် Browser တွင် ပြသရန်အတွက် `@admin/Product/product_favourite.twig` ဖိုင်ကို အသုံးပြုမည် ဖြစ်သည်"* ဟု ကြိုတင် အသိပေး သတ်မှတ်ထားခြင်း ဖြစ်ပါသည်။

     * **`@Template` ကို သုံးမည်ဆိုပါက Code တွင် အဆင့် (၄) ဆင့် ပြောင်းလဲရပါမည် (How to change if use this):**

       | အဆင့် (Step) | သာမန် ပုံစံ (Without `@Template`) | `@Template` သုံးသော ပုံစံ (With `@Template`) |
       | :--- | :--- | :--- |
       | **အဆင့် ၁: Namespace ထည့်ခြင်း** | `use Symfony\Component\HttpFoundation\Response;` | `use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;` |
       | **အဆင့် ၂: Annotation တပ်ဆင်ခြင်း** | (ဘာမှ ရေးစရာ မလိုပါ) | Method အပေါ်တွင် `@Template("@admin/...twig")` ဟု ထည့်ရသည် |
       | **အဆင့် ၃: Return Type ပြောင်းခြင်း** | `public function index(...): Response` | `public function index(...): array` ဟု ပြောင်းရသည် |
       | **အဆင့် ၄: Return Statement ပြောင်းခြင်း** | `return $this->render('@admin/...twig', [...]);` | `return [...];` (ဒေတာ Array သက်သက်သာ ပြန်ပေးရသည်) |

     * **လက်တွေ့ ကုဒ် ပြောင်းလဲပုံ (Side-by-Side Code Diff):**

       ```diff
        namespace Customize\Controller\Admin\Product;
        
       +use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
       -use Symfony\Component\HttpFoundation\Response;
        
        class FavouriteProductController extends AbstractController
        {
            /**
             * @Route("/product/favourite", name="admin_product_favourite")
       +     * @Template("@admin/Product/product_favourite.twig")
             */
       -    public function index(Request $request): Response
       +    public function index(Request $request): array
            {
                $pagination = ...;
                
       -        return $this->render('@admin/Product/product_favourite.twig', [
       -            'pagination' => $pagination,
       -            'page_no' => $page_no,
       -        ]);
       +        return [
       +            'pagination' => $pagination,
       +            'page_no' => $page_no,
       +        ];
            }
        }
       ```

     ---

     ##### ၃။ နောက်ကွယ်တွင် Symfony က မည်သို့ အလိုအလျောက် အလုပ်လုပ်ပေးသနည်း? (Data Flow):
     1. Developer က Controller မှ `return ['pagination' => $pagination];` ဟု ဒေတာ **`array`** သက်သက်ကိုသာ ပေးပို့လိုက်သည်။
     2. Symfony Framework ၏ နောက်ကွယ်တွင်ရှိသော `SensioFrameworkExtraBundle (TemplateListener)` က ထို `array` ကို ကြားဖြတ် ဖမ်းယူလိုက်သည်။
     3. အပေါ်က `@Template("@admin/Product/product_favourite.twig")` ဆိုင်းဘုတ်ကို ကြည့်ပြီး ညွှန်ပြထားသည့် Twig ဖိုင်ထဲသို့ Array ထဲမှ Key/Value များကို အလိုအလျောက် သွင်းပေးလိုက်သည်။
     4. ထို့နောက် Twig က HTML အဖြစ် Render လုပ်ပြီး ထွက်လာသော ရလဒ်ကို **`Response` Object အဖြစ် Symfony က အလိုအလျောက် အသွင်ပြောင်း၍** Browser သို့ ပို့ပေးလိုက်သည်။

     ```text
     [Controller: index()] 
           │ (Return associative array သက်သက်)
           ▼
     ['pagination' => $pagination, 'page_no' => $page_no]
           │
           ▼
     [Symfony TemplateListener] ◄─── `@Template("@admin/Product/product_favourite.twig")`
           │ (အလိုအလျောက် Twig ထဲ ဒေတာသွင်းပြီး Render လုပ်ပေးသည်)
           ▼
     [HTML Response Object] 
           │
           ▼
     [Browser / Client Display]
     ```

     ##### ၄။ လက်တွေ့ ဥပမာဖြင့် နားလည်အောင် ရှင်းပြချက် (Restaurant Analogy):
     * **Developer (သင်)** = စားဖိုမှူး (Chef)
     * **Data Array (`['pagination' => ...]`)** = စားဖိုမှူး ချက်ပြုတ်ထားသော အရသာရှိသည့် ဟင်းလျာ (Food/Data)
     * **Twig Template (`product_favourite.twig`)** = စားသောက်ဆိုင်၏ လှပသော ပန်းကန်ပြား (Plate/UI)
     * **`Response` Object** = ပန်းကန်ထဲ စနစ်တကျ ပြင်ဆင်ထည့်သွင်းပြီး စားသုံးသူရှေ့ ချထားပေးမည့် အသင့်စား ဟင်းပွဲ (Plated Dish)

     * **နည်းလမ်း A (သာမန်ပုံစံ):** စားဖိုမှူးက ဟင်းချက်ရုံတင်မကဘဲ ပန်းကန်ထဲပါ ကိုယ်တိုင် ထည့်ပြင်ဆင်ပြီးမှ စားပွဲဆီ လှမ်းပို့ပေးရသည် (`return $this->render(...)`)။
     * **နည်းလမ်း B (`@Template` ပုံစံ):** အပေါ်က စားပွဲထိုးလေး `@Template` က တာဝန်ယူပေးထားသဖြင့် စားဖိုမှူးက ဟင်းလျာ (Data Array) သက်သက်ကို လှမ်းပေးလိုက်ရုံဖြင့် စားပွဲထိုး (`TemplateListener`) က ပန်းကန်ပြား (Twig) ထဲ အလိုအလျောက် ထည့်ပြင်ပြီး စားသုံးသူ (Browser) ထံ အသင့် ပို့ဆောင်ပေးသွားခြင်း ဖြစ်ပါသည်။

     ##### ၅။ အကယ်၍ `@Template` မပါဘဲ `return [...]` (array) ရေးလိုက်လျှင် ဘာဖြစ်မလဲ? (Why it fails without @Template):
     အကယ်၍ `@Template` Annotation ကို မထည့်ထားဘဲ `return ['pagination' => $pagination];` ဟု ရေးလိုက်ပါက Symfony က စားပွဲထိုး မရှိသည့်အတွက် Array ကို ဘာလုပ်ရမှန်း မသိဘဲ အောက်ပါအတိုင်း **Fatal Error (HTTP 500)** ချက်ချင်း တက်ပါမည်:
     > ❌ **CRITICAL ERROR:**  
     > `The controller must return a "Symfony\Component\HttpFoundation\Response" object but it returned an array.`

     * **နိဂုံးချုပ် စည်းမျဉ်း:**
       - **`@Template` ပါရှိလျှင်:** Developer သည် `$this->render()` ရေးစရာ မလိုဘဲ `array` ကို သန့်ရှင်းစွာ Return ပြန်ခွင့်ရှိပါသည်။ (Return type: `: array`)
       - **`@Template` မပါရှိလျှင်:** `array` လုံးဝ ပြန်ခွင့်မရှိဘဲ `$this->render(...)` သုံးပြီး `Response` Object သာ ပြန်ရပါမည်။ (Return type: `: Response`)
       - **Form POST / Redirect လိုအပ်လျှင်:** `@Template` သုံးထားသော်လည်း Redirect လုပ်ရန်အတွက် `return $this->redirectToRoute(...)` (`RedirectResponse`) ကို ပြန်ရပါမည်။

     ---

     * **အကျဉ်းချုပ် ဇယား (Comparison Table):**
       | အခြေအနေ (Scenario) | အသုံးပြုသည့် Annotation / Method | Return ပြန်ရမည့် Type | ဥပမာ Code |
       | :--- | :--- | :--- | :--- |
       | **၁။ @Template သုံးထားသော စာရင်းကြည့် Action** | `@Template("@admin/...twig")` | **`array`** | `return ['pagination' => $pagination];` |
       | **၂။ @Template မသုံးထားသော သာမန် Action** | (မပါရှိပါ) | **`Response`** | `return $this->render('...', [...]);` |
       | **၃။ Form Save / Delete / Redirect Action** | `@Template` သုံးထားသော်လည်း | **`Response` သို့မဟုတ် `RedirectResponse`** | `return $this->redirectToRoute('admin_product');` |
       | **၄။ AJAX API Endpoint** | (REST API / JSON) | **`JsonResponse`** | `return new JsonResponse(['success' => true]);` |

---

#### (င) DocBlock `@param`, `@return`, `@var` များကို မဖြစ်မနေ ရေးရန် လိုအပ်ပါသလား? မရေးရင် မှားသလား? (Is this a rule or format? Will it be wrong if omitted?)

> **တိုတိုနှင့် လိုရင်း အဖြေ:**  
> **PHP Runtime အနေဖြင့် မရေးလည်း Error မတက်ပါ (အလုပ်လုပ်ပါသည်)။**  
> သို့သော် **EC-CUBE Core Coding Standards (PSR-5, PSR-12, PHP CS Fixer)** နှင့် **Enterprise Architecture** အရ **မဖြစ်မနေ ရေးသားရမည့် စံသတ်မှတ်ချက် (Standard Format)** ဖြစ်ပါသည်။

* **မရေးပါက ဖြစ်ပေါ်လာမည့် ဆိုးကျိုးများ:**
  1. **IDE Autocompletion ပျောက်ဆုံးခြင်း:** VS Code သို့မဟုတ် PhpStorm တွင် `$this->favouriteProductRepository->...` ဟု ရိုက်သည့်အခါ Method နာမည်များ Suggestion မပြတော့ဘဲ အမှားရှာရ ခက်ခဲသွားပါမည်။
  2. **Static Analysis & CI/CD Error တက်ခြင်း:** EC-CUBE တွင် အသုံးပြုသော `PHP CS Fixer`, `PHPStan` ကဲ့သို့သော Code Quality စစ်ဆေးသည့် Tools များက "Missing @param / @var / @return tag" ဟု Warning / Error ထုတ်ပြန်ပြီး Code Commit ကို ငြင်းပယ်ပါမည်။
  3. **Team Collaboration & Readability:** အခြား Developer တစ်ဦး သို့မဟုတ် ၆ လအတွေ့အကြုံရှိ Junior Developer တစ်ဦးက Code ကို ဖတ်ရှုသည့်အခါ Parameter အမျိုးအစားနှင့် Return အမျိုးအစားကို ချက်ချင်း သိရှိနားလည်နိုင်စေရန် ဖြစ်ပါသည်။

---

#### (စ) Property နှင့် Constructor ရေးသားပုံ စံနှုန်း (Constructor Dependency Injection Architecture)
Controller တွင် အောက်ပါအတိုင်း ရေးသားထားခြင်းသည် Symfony / EC-CUBE ၏ **Dependency Injection (DI)** ရွှေရောင် စံနှုန်းဖြစ်ပါသည်:

```php
/**
 * @var FavouriteProductRepository
 */
protected $favouriteProductRepository;

/**
 * @var PaginatorInterface
 */
protected $paginator;

/**
 * FavouriteProductController constructor.
 *
 * @param FavouriteProductRepository $favouriteProductRepository
 * @param PaginatorInterface $paginator
 */
public function __construct(
    FavouriteProductRepository $favouriteProductRepository,
    PaginatorInterface $paginator
) {
    $this->favouriteProductRepository = $favouriteProductRepository;
    $this->paginator = $paginator;
}
```

* **အဘယ်ကြောင့် ဤ Format အတိုင်း ရေးရသနည်း?:**
  1. **Symfony Autowiring:** Symfony Service Container သည် Constructor ထဲရှိ Type-hint (`FavouriteProductRepository $favouriteProductRepository`) ကို ကြည့်ပြီး Repository Instance အစစ်ကို နောက်ကွယ်မှ အလိုအလျောက် New ဆောက်ပြီး ထည့်ပေး (Inject လုပ်ပေး) ပါသည်။
  2. **`protected $...` တွင် သိမ်းဆည်းခြင်း:** Constructor ထဲတွင် လက်ခံရရှိသော Object ကို Class Property `$this->favouriteProductRepository` ထဲသို့ ထည့်သိမ်းထားမှသာ အောက်ရှိ `index()` method ထဲတွင် `$this->favouriteProductRepository->getFavouriteDb()` ဟု လှမ်းခေါ်၍ ရမည် ဖြစ်ပါသည်။
  3. **မရေးပါက ဘာဖြစ်မလဲ?:** အကယ်၍ Constructor သို့မဟုတ် Property မရေးဘဲ ကျော်သွားပါက `$this->favouriteProductRepository` သည် `null` ဖြစ်နေမည်ဖြစ်ပြီး `Error: Call to a member function getFavouriteDb() on null (500 Internal Server Error)` တက်သွားပါမည်။

---

#### (ဆ) Repository DQL တွင် `AS HIDDEN favorite_count` ကို အဘယ်ကြောင့် သုံးရသလဲ?

📁 **ဖိုင်လမ်းကြောင်း:** [app/Customize/Repository/FavouriteProductRepository.php](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/Customize/Repository/FavouriteProductRepository.php#L40)

```php
$qb->addSelect('(SELECT COUNT(cfp.id) FROM ' . CustomerFavoriteProduct::class . ' cfp WHERE cfp.Product = p) AS HIDDEN favorite_count')
   ->where('(SELECT COUNT(cfp2.id) FROM ' . CustomerFavoriteProduct::class . ' cfp2 WHERE cfp2.Product = p) > 0')
   ->orderBy('favorite_count', 'DESC')
   ->addOrderBy('p.id', 'DESC');
```

##### ၁။ Doctrine ORM ၏ Hydration သဘောတရား (Doctrine Entity Hydration):
* ပုံမှန်အားဖြင့် `$this->createQueryBuilder('p')` ရေးသားပါက Doctrine သည် Database မှ Record များကို **`Eccube\Entity\Product` Object သန့်သန့်** အဖြစ် ပြောင်းလဲ (Hydrate) ထုတ်ပေးပါသည်။
* သို့သော် `addSelect('(...) AS favorite_count')` ဟု ရေးပြီး `HIDDEN` မထည့်သွင်းပါက Doctrine သည် Query ရလဒ်ကို Entity သီးသန့် မဟုတ်တော့ဘဲ **Entity + Scalar Value ရောနှောထားသော Mixed Array** အဖြစ် အောက်ပါအတိုင်း ပြောင်းလဲ ထုတ်ပေးလိုက်ပါသည်:

```php
// ❌ HIDDEN မပါပါက Doctrine ထုတ်ပေးမည့် ရလဒ် (Mixed Array):
[
    0 => [
        0 => Product Object,       // Entity
        'favorite_count' => "5"    // Scalar Subquery Result
    ],
    1 => [
        0 => Product Object,
        'favorite_count' => "3"
    ]
]
```

##### ၂။ ဖြစ်ပေါ်လာမည့် ပြဿနာ (The Breaking Bug):
* Twig Template တွင် EC-CUBE Standard အတိုင်း `{% for Product in pagination %}` ဟု Loop ပတ်ပြီး `{{ Product.name }}` သို့မဟုတ် `{{ Product.id }}` ဟု တိုက်ရိုက် ခေါ်ယူထားပါသည်။
* အကယ်၍ Result က အထက်ပါအတိုင်း Mixed Array ဖြစ်နေပါက `$Product` သည် Entity မဟုတ်တော့ဘဲ Array ဖြစ်နေသဖြင့် Twig တွင် **`KeyError` သို့မဟုတ် "Impossible to invoke a method on an array"** ဟူသော Fatal Error (HTTP 500) တက်ပြီး Page တစ်ခုလုံး ပျက်စီးသွားပါမည်။

##### ၃။ `AS HIDDEN` ၏ အစွမ်းနှင့် ဖြေရှင်းပုံ (How AS HIDDEN solves this):
* `AS HIDDEN` သည် Doctrine DQL ၏ အလွန် အသုံးဝင်သော Keyword ဖြစ်ပါသည်။
* ၎င်း၏ အဓိပ္ပာယ်မှာ:  
  *"ဤ `favorite_count` ကို SQL Query တွင် Select လုပ်ပါ၊ `ORDER BY favorite_count DESC` တွင် အသုံးပြုခွင့်ပေးပါ၊ သို့သော် PHP Result ထဲသို့ Hydrate လုပ်သည့်အခါ မထည့်ဘဲ **ဖုံးကွယ် (HIDDEN)** ထားပေးပါ"* ဟု ခိုင်းစေခြင်း ဖြစ်ပါသည်။
* ရလဒ်အနေဖြင့် Controller နှင့် Twig ဆီသို့ **`Product` Entity Object များ သန့်သန့်ရှင်းရှင်း ၁၀၀% အပြည့်အဝ ရရှိစေပြီး** Twig တွင် `{{ Product.name }}` ဟု သာမန်အတိုင်း ခေါ်ယူသုံးစွဲနိုင်ခြင်း ဖြစ်ပါသည်။

---

#### (ဇ) အဘယ်ကြောင့် `ORDER BY` (၂) ကြိမ် သုံးရသနည်း? (`->orderBy(...)->addOrderBy(...)`)

```php
->orderBy('favorite_count', 'DESC')
->addOrderBy('p.id', 'DESC');
```

##### ၁။ `orderBy()` နှင့် `addOrderBy()` ၏ မတူညီသော အလုပ်လုပ်ပုံ (Doctrine QueryBuilder):

* **`->orderBy('field', 'DESC')` (ပထမအကြိမ်):**  
  QueryBuilder တွင် ရှိပြီးသား Sort Order များအားလုံးကို ရှင်းလင်းဖျက်ပစ်ပြီး အသစ်တစ်ခုဖြင့် **အစားထိုး (Overwrite/Reset)** သတ်မှတ်ပေးပါသည်။
* **`->addOrderBy('field', 'DESC')` (ဒုတိယအကြိမ်):**  
  အရင် သတ်မှတ်ထားပြီးသား Sort Order ကို မဖျက်ဘဲ ၎င်း၏ အနောက်သို့ နောက်ထပ် ဦးစားပေး Sort အသစ်တစ်ခု ထပ်မံ **ဖြည့်စွက် (Append)** ပေးပါသည်။

> ⚠️ **သတိပြုရန် အချက်:**  
> အကယ်၍ `->orderBy('favorite_count', 'DESC')->orderBy('p.id', 'DESC')` ဟု `orderBy` ကို နှစ်ကြိမ် ဆက်တိုက် ရေးလိုက်ပါက ဒုတိယ `orderBy` က ပထမ `favorite_count` ကို ဖျက်ပစ်လိုက်သဖြင့် SQL တွင် `ORDER BY p.id DESC` သာ ထွက်လာပြီး Favorite Count အများဆုံးဖြင့် စီခြင်း လုံးဝ ပျောက်ကွယ်သွားပါမည်! ထို့ကြောင့် ဒုတိယအကြိမ်တွင် **`addOrderBy`** ကို မဖြစ်မနေ သုံးရပါမည်။

##### ၂။ SQL Query အဖြစ် မည်သို့ ထွက်လာသနည်း?
အထက်ပါ Doctrine DQL သည် Database Engine (MySQL) ဆီသို့ အောက်ပါအတိုင်း SQL ထွက်ရှိသွားပါသည်:
```sql
ORDER BY favorite_count DESC, p.id DESC
```
(အဓိပ္ပာယ်မှာ: ပထမဦးစားပေးအနေဖြင့် `favorite_count` ကြီးရာမှ ငယ်ရာသို့ စီမည်။ တန်ဖိုးတူနေပါက ဒုတိယဦးစားပေးအနေဖြင့် `p.id` ကြီးရာမှ ငယ်ရာသို့ စီမည်)။

##### ၃။ အဘယ်ကြောင့် Tie-Breaker အဖြစ် `p.id DESC` ကို မဖြစ်မနေ ထည့်ရသနည်း? (Pagination Drift Bug ကာကွယ်ခြင်း)

* **ပြဿနာ (Non-Deterministic Sort Drift):**
  - ဥပမာ- Product A (ID: 5) နှင့် Product B (ID: 8) နှစ်ခုစလုံးသည် Favorite အရေအတွက် **(၃) ကြိမ်စီ တူညီနေသည်** ဆိုပါစို့။
  - အကယ်၍ `orderBy('favorite_count', 'DESC')` တစ်ခုတည်းသာ ရေးထားပါက Database Engine (MySQL) သည် တန်ဖိုးတူနေသော Record များကို မည်သည့်အစီအစဉ်ဖြင့် ပြရမည်ကို အာမမခံပါ (Unstable/Non-Deterministic Order)။
  - Admin က Page 1 မှ Page 2 သို့ Next နှိပ်လိုက်သည့်အခါ MySQL ၏ Internal Row Pointer အစီအစဉ် ပြောင်းလဲသွားပြီး:
    - Product A သည် Page 1 တွင်လည်း တွေ့ရပြီး Page 2 တွင်လည်း ထပ်မံပါလာခြင်း (**Duplicate Item**)
    - Product B မှာမူ Page 1 တွင်လည်း မပါ၊ Page 2 တွင်လည်း မပါဘဲ လုံးဝ ပျောက်ဆုံးသွားခြင်း (**Missing Item**)  
    စသည့် **Pagination Drift Bug** အကြီးအကျယ် ဖြစ်ပေါ်တတ်ပါသည်။

* **Tie-Breaker ဖြင့် ဖြေရှင်းပုံ (Deterministic Ordering):**
  - Unique ဖြစ်သော Primary Key `p.id DESC` ကို ဒုတိယအဆင့် Tie-Breaker (သရေကျခြင်းကို အဆုံးအဖြတ်ပေးသူ) အဖြစ် ထည့်ပေးလိုက်သောအခါ:
    * Favorite Count တူညီနေပါက ID ကြီးသော Product (ID: 8) က အရင်လာမည်ဖြစ်ပြီး ID ငယ်သော Product (ID: 5) က အနောက်မှ လာမည်ဟု Database အား ၁၀၀% တိကျစွာ သတ်မှတ်ပေးလိုက်ခြင်း ဖြစ်ပါသည်။
  - ထို့ကြောင့် မည်သည့်အချိန်တွင်မဆို စာမျက်နှာ ကူးပြောင်းတိုင်း ဒေတာအစီအစဉ်သည် **100% တည်ငြိမ်မှန်ကန် (Deterministic)** နေမည် ဖြစ်ပါသည်။

---

#### (ဈ) Controller တွင် Session ကို အဘယ်ကြောင့် အသုံးပြုထားသနည်း? (Session Usage)

```php
if (null !== $page_no) {
    $this->session->set('eccube.admin.product.favourite.page_no', (int) $page_no);
} else {
    $page_no = $this->session->get('eccube.admin.product.favourite.page_no', 1);
}
```

* **ယခု Controller တွင် Session ကို အသုံးပြုထားပါသည်:**
  - ၎င်းသည် **Page Memory (စာမျက်နှာ မှတ်သားမှု)** အတွက် သုံးထားခြင်း ဖြစ်ပါသည်။
  - Admin က စာမျက်နှာ ၄ ကို ရောက်နေစဉ် ကုန်ပစ္စည်း ID 10 ကို Edit ပြင်ဆင်ပြီး Back ပြန်ဆုတ်လာသည့်အခါ Session ထဲမှ Page 4 ကို ပြန်လည် ဆွဲထုတ်ပေးသဖြင့် စာမျက်နှာ ၁ သို့ ပြုတ်မကျဘဲ စာမျက်နှာ ၄ သို့ အတိအကျ ပြန်ရောက်နေစေပါသည်။
* **Search Form Session မပါဝင်ရသည့် အကြောင်းရင်း:**
  - Core Product List (`ProductController`) တွင် Product Name, Category စသည်ဖြင့် ရှာဖွေနိုင်သော Search Form ပါရှိသဖြင့် `$this->session->set('eccube.admin.product.search', $searchData)` ဟု ရေးသားလေ့ရှိပါသည်။
  - ကျွန်ုပ်တို့၏ Favourite Products စာရင်းတွင် Search Filter Form မလိုအပ်ဘဲ Favorite Products အားလုံးကို တိုက်ရိုက် ပြသထားသောကြောင့် Search Form Session မလိုဘဲ **Page Number Session တစ်ခုတည်းကိုသာ ရိုးရှင်းထိရောက်စွာ သုံးထားခြင်း** ဖြစ်ပါသည်။

---

#### (ည) KnpPaginator ၏ `['wrap-queries' => true]` ဆိုသည်မှာ အဘယ်နည်း?

```php
$pagination = $this->paginator->paginate($qb, $page_no, $page_count, ['wrap-queries' => true]);
```

* **သဘောတရားနှင့် အလုပ်လုပ်ပုံ:**
  - Pagination ပြုလုပ်ရန်အတွက် KnpPaginator သည် Database ဆီမှ စုစုပေါင်း ကုန်ပစ္စည်း အရေအတွက် (`COUNT`) ကို အရင် မေးမြန်းရပါသည်။
  - ကျွန်ုပ်တို့၏ QueryBuilder တွင် Subquery (`SELECT COUNT(cfp.id) ...`) ပါဝင်နေပါသည်။
  - အကယ်၍ `wrap-queries => true` မထည့်ထားပါက Doctrine Paginator သည် မူရင်း Query ထဲမှ Subquery များနှင့် ရှုပ်ထွေးသွားပြီး မှားယွင်းသော SQL `COUNT` Query ကို ထုတ်ပေးတတ်ကာ SQL Syntax Error တက်ခြင်း သို့မဟုတ် Total Item Count ဂဏန်း မှားယွင်းခြင်း ဖြစ်ပေါ်တတ်ပါသည်။
  - **`wrap-queries => true` ထည့်လိုက်သောအခါ:**  
    Doctrine သည် ကျွန်ုပ်တို့၏ QueryBuilder တစ်ခုလုံးကို Subquery အဖြစ် အောက်ပါအတိုင်း အလုံပိတ် ထုပ်ပိုး (Wrap) ပေးလိုက်ပါသည်:
    ```sql
    SELECT COUNT(*) FROM (SELECT p.* FROM dtb_product p WHERE ...) AS dctrn_count;
    ```
    ထို့ကြောင့် Subquery များ မည်မျှပင် ပါဝင်နေစေကာမူ SQL Error လုံးဝ မတက်ဘဲ စုစုပေါင်း အရေအတွက် (`totalItemCount`) ကို ၁၀၀% အတိအကျ တွက်ချက်ပေးနိုင်ခြင်း ဖြစ်ပါသည်။

---

#### အပြည့်အစုံ ကုဒ် (Full Code for FavouriteProductController.php):

📁 **ဖိုင်လမ်းကြောင်း:** [app/Customize/Controller/Admin/Product/FavouriteProductController.php](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/Customize/Controller/Admin/Product/FavouriteProductController.php)

```php
<?php

namespace Customize\Controller\Admin\Product;

use Customize\Repository\FavouriteProductRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FavouriteProductController extends AbstractController
{
    protected $favouriteProductRepository;
    protected $paginator;

    public function __construct(
        FavouriteProductRepository $favouriteProductRepository,
        PaginatorInterface $paginator
    ) {
        $this->favouriteProductRepository = $favouriteProductRepository;
        $this->paginator = $paginator;
    }

    /**
     * Admin အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်း ပြသခြင်း
     * (EC-CUBE Standard Routing: Base List URL and Pagination URL)
     *
     * @Route("/%eccube_admin_route%/product/favourite", name="admin_product_favourite", methods={"GET"})
     * @Route("/%eccube_admin_route%/product/favourite/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_favourite_page", methods={"GET"})
     * @Template("@admin/Product/product_favourite.twig")
     */
    public function index(Request $request, $page_no = null): array
    {
        if (null !== $page_no) {
            $this->session->set('eccube.admin.product.favourite.page_no', (int) $page_no);
        } else {
            $page_no = $this->session->get('eccube.admin.product.favourite.page_no', 1);
        }

        $page_count = $this->eccubeConfig->get('eccube_default_page_count');
        $qb = $this->favouriteProductRepository->getFavouriteDb();

        $pagination = $this->paginator->paginate($qb, $page_no, $page_count, ['wrap-queries' => true]);

        return [
            'pagination' => $pagination,
            'page_no' => $page_no,
        ];
    }
}
```

---

### အဆင့် (၄) - Admin Twig View Template ရေးသားခြင်း (`product_favourite.twig`)

📁 **ဖိုင်လမ်းကြောင်း:** [app/template/admin/Product/product_favourite.twig](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/template/admin/Product/product_favourite.twig)

#### ဘာကြောင့် ဒီဖိုင်ကို ရေးရသလဲ? (Why?)
Admin Panel ၏ User Interface (UI) စာမျက်နှာကို Bootstrap 5 စံနှုန်းများနှင့်အညီ ဖော်ပြပေးရန် ဖြစ်ပါသည်။

#### မည်သို့ အလုပ်လုပ်သနည်း? (How it works?)
1. **Active Side Menu Highlight:**
   `{% set menus = ['product', 'favourite_product'] %}` ဟု ရေးသားထားသဖြင့် Side Navigation တွင် `Product` မီနူး ပွင့်လာပြီး `Favourite Products` ခေါင်းစဉ်တွင် Active (အပြာရောင်) အမှတ်အသား အလိုအလျောက် ပြသပေးပါသည်။
2. **တောင်းဆိုထားသော Column များ ပြသပုံ:**
   - **ID:** `{{ Product.id }}`
   - **Thumbnail Image:** `<img class="fav-product-img" src="{{ asset(Product.mainFileName|no_image_product, 'save_image') }}">` (မရှိပါက No Image ပုံ အလိုအလျောက် ပြပေးသည်)
   - **Name & Code:** `{{ Product.name }}` နှင့် `{{ Product.code_min }} ~ {{ Product.code_max }}`
   - **Price Range:** Class ရှိပါက Min ~ Max ဈေးနှုန်း၊ Class မရှိပါက ပုံမှန် အခွန်ပါ ဈေးနှုန်းကို Format လုပ်၍ ပြသပေးပါသည်။
   - **Favourite Count:** `{{ Product.CustomerFavoriteProducts|length|number_format }}` ကို အနီရောင် Heart အသဲပုံ Icon နှင့် Rounded Badge ဖြင့် ပြသထားပါသည်။
   - **Status:** Public ဖြစ်ပါက အစိမ်းရောင် Badge `公開`၊ Hidden ဖြစ်ပါက မီးခိုးရောင် Badge `非公開` ပြသပေးပါသည်။
3. **Empty State:**
   အကယ်၍ ဝယ်ယူသူများ မည်သည့်ပစ္စည်းကိုမျှ Favorite မလုပ်ထားသေးပါက သပ်ရပ်သော Info Card ဖြင့် အသိပေးထားပါသည်။

```twig
{#
This file is part of EC-CUBE

Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.

http://www.ec-cube.co.jp/

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
#}
{% extends '@admin/default_frame.twig' %}

{% set menus = ['product', 'favourite_product'] %}

{% block title %}{{ 'admin.favorite.favorite_products'|trans }}{% endblock %}
{% block sub_title %}{{ 'admin.product.product_management'|trans }}{% endblock %}

{% block stylesheet %}
    <style>
        .fav-product-img {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .fav-count-badge {
            font-size: 0.875rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
        }
    </style>
{% endblock %}

{% block main %}
    <div class="c-contentsArea__cols">
        <div class="c-contentsArea__primaryCol">
            <div class="c-primaryCol">

                <!-- Header Action & Title Bar -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">
                            <i class="fa fa-heart text-danger me-2"></i>{{ 'admin.favorite.favorite_products'|trans }}
                        </h4>
                        <p class="text-muted small mb-0">{{ 'admin.product.favorite_management'|trans }}</p>
                    </div>
                </div>

                <!-- Products Table Card -->
                <div class="card rounded border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="fa fa-list me-2 text-primary"></i>{{ 'admin.product.favorite_list'|trans }}
                            </h6>
                        </div>
                        <div>
                            {% if pagination and pagination.totalItemCount > 0 %}
                                <span class="badge bg-primary px-3 py-2 fs-7">
                                    {{ 'admin.common.count'|trans({'%count%': pagination.totalItemCount|number_format}) }}
                                </span>
                            {% endif %}
                        </div>
                    </div>

                    <div class="card-body p-0">
                        {% if pagination and pagination.totalItemCount > 0 %}
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th class="ps-3 py-3" style="width: 70px;">ID</th>
                                        <th class="py-3" style="width: 70px;">{{ 'admin.product.image__short'|trans }}</th>
                                        <th class="py-3" style="min-width: 200px;">{{ 'admin.product.name'|trans }}</th>
                                        <th class="py-3 text-end" style="width: 170px;">{{ 'admin.product.price'|trans }}</th>
                                        <th class="py-3 text-center" style="width: 140px;">{{ 'admin.product.favorite_count'|trans }}</th>
                                        <th class="pe-3 py-3 text-center" style="width: 120px;">{{ 'admin.product.display_status__short'|trans }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {% for Product in pagination %}
                                        <tr>
                                            <!-- Product ID -->
                                            <td class="ps-3 fw-bold text-muted">{{ Product.id }}</td>

                                            <!-- Product Image -->
                                            <td>
                                                <a href="{{ url('admin_product_product_edit', { id : Product.id }) }}">
                                                    <img class="fav-product-img" src="{{ asset(Product.mainFileName|no_image_product, 'save_image') }}" alt="{{ Product.name }}">
                                                </a>
                                            </td>

                                            <!-- Product Name & Code -->
                                            <td>
                                                <div>
                                                    <a class="fw-bold text-decoration-none text-dark" href="{{ url('admin_product_product_edit', { id : Product.id }) }}">
                                                        {{ Product.name }}
                                                    </a>
                                                </div>
                                                {% if Product.code_min %}
                                                    <div class="mt-1">
                                                        <span class="badge bg-light text-dark border font-monospace">
                                                            {{ Product.code_min }}{% if Product.code_min != Product.code_max %} {{ 'admin.common.separator__range'|trans }} {{ Product.code_max }}{% endif %}
                                                        </span>
                                                    </div>
                                                {% endif %}
                                            </td>

                                            <!-- Price with Range Display -->
                                            <td class="text-end fw-bold text-dark text-nowrap">
                                                {% if Product.hasProductClass %}
                                                    {% if Product.getPrice02Min == Product.getPrice02Max %}
                                                        {{ Product.getPrice02IncTaxMin|price }}
                                                    {% else %}
                                                        {{ Product.getPrice02IncTaxMin|price }} <span class="text-muted fw-normal">{{ 'admin.common.separator__range'|trans }}</span> {{ Product.getPrice02IncTaxMax|price }}
                                                    {% endif %}
                                                {% else %}
                                                    {{ Product.getPrice02IncTaxMin|price }}
                                                {% endif %}
                                            </td>

                                            <!-- Favourite Counts -->
                                            <td class="text-center">
                                                <span class="fav-count-badge">
                                                    <i class="fa fa-heart me-1"></i> {{ Product.CustomerFavoriteProducts|length|number_format }}
                                                </span>
                                            </td>

                                            <!-- Status -->
                                            <td class="pe-3 text-center">
                                                {% if Product.Status and Product.Status.id == constant('Eccube\\Entity\\Master\\ProductStatus::DISPLAY_SHOW') %}
                                                    <span class="badge bg-success">{{ Product.Status.name }}</span>
                                                {% elseif Product.Status and Product.Status.id == constant('Eccube\\Entity\\Master\\ProductStatus::DISPLAY_HIDE') %}
                                                    <span class="badge bg-secondary">{{ Product.Status.name }}</span>
                                                {% else %}
                                                    <span class="badge bg-light text-dark">-</span>
                                                {% endif %}
                                            </td>
                                        </tr>
                                    {% endfor %}
                                    </tbody>
                                </table>
                            </div>
                        {% else %}
                            <!-- Empty State Card -->
                            <div class="text-center py-5">
                                <i class="fa fa-heart-o fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted fw-bold">お気に入り登録された商品がありません</h5>
                            </div>
                        {% endif %}
                    </div>
                </div>

                <!-- Pagination Footer -->
                {% if pagination and pagination.totalItemCount > 0 %}
                    <div class="row justify-content-md-center mb-4">
                        {% include "@admin/pager.twig" with {'pages': pagination.paginationData, 'routes': 'admin_product_favourite_page'} %}
                    </div>
                {% endif %}

            </div>
        </div>
    </div>
{% endblock %}
```

---

## ၅။ Cache Clear ပြုလုပ်ခြင်းနှင့် စစ်ဆေးခြင်း (Terminal Commands)

`eccube_nav.yaml` ဖိုင် သို့မဟုတ် Controller အသစ်များ ထည့်သွင်းပြီးတိုင်း Symfony Container Cache ကို မဖြစ်မနေ ရှင်းလင်းပေးရပါမည်:

```bash
# Docker Container အတွင်း Cache Clear ပြုလုပ်ခြင်း
docker compose -f docker-compose.yml -f docker-compose.mysql.yml exec ec-cube bin/console cache:clear --no-warmup
```

#### Route စစ်ဆေးခြင်း:
Route မှန်ကန်စွာ Register ဖြစ်မဖြစ် စစ်ဆေးရန်:
```bash
docker compose -f docker-compose.yml -f docker-compose.mysql.yml exec ec-cube bin/console debug:router admin_product_favourite
```

---

## ၆။ စနစ်စမ်းသပ် စစ်ဆေးခြင်း (Verification & Testing Guide)

1. **Browser ဖွင့်ခြင်း:**
   Browser မှတစ်ဆင့် [http://localhost:8080/admin](http://localhost:8080/admin) သို့ ဝင်ရောက်ပြီး Admin အကောင့်ဖြင့် Login ဝင်ပါ။ (Default: ID `admin` / PW `password`)
2. **Sub-Menu စစ်ဆေးခြင်း:**
   ဘယ်ဘက် Side Navigation ရှိ **Product (商品管理)** ကို နှိပ်လိုက်ပါက အောက်တွင် **Favourite Products (お気に入り商品)** ဟူသော Sub-menu အသစ် ပေါ်နေသည်ကို တွေ့ရပါမည်။
3. **စာရင်း ပြသမှု စစ်ဆေးခြင်း:**
   ထို Sub-menu ကို နှိပ်၍ ဝင်ရောက်ကြည့်ရှုပါ:
   - Favorite အလုပ်ခံထားရသော ပစ္စည်းများ (ဥပမာ- Product ID 1 နှင့် Product ID 2) စာရင်း ပေါ်လာမည်။
   - Product ID, ဓာတ်ပုံ Thumbnail, နာမည်၊ ဈေးနှုန်း Range, Favourite Count (ဥပမာ- ❤️ 2, ❤️ 1), Status တို့ မှန်ကန်စွာ ပေါ်နေမည်။
   - ဝယ်ယူသူ မည်သူမျှ Favorite မလုပ်ထားသော အခြား ပစ္စည်းများ စာရင်းထဲတွင် လုံးဝ ပါဝင်လာမည် မဟုတ်ပါ။
