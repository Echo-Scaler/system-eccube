# EC-CUBE 4.3.1 - Admin Panel အကြိုက်ဆုံးပစ္စည်းများ စာရင်း စာမျက်နှာ ဖန်တီးခြင်းနှင့် Route Annotation လမ်းညွှန် (Admin Favorite Product List & Route Annotation Guide)

ဤမှတ်တမ်းသည် EC-CUBE 4.3.1 တွင် **Admin Panel (စီမံခန့်ခွဲသူ မျက်နှာပြင်) ၌ ဝယ်ယူသူများ၏ အကြိုက်ဆုံးပစ္စည်း စာရင်းများအား ကြည့်ရှု/ရှာဖွေ/စီမံနိုင်သော စာမျက်နှာ ဖန်တီးခြင်း (管理画面のお気に入り商品一覧画面の作成)** နှင့် **Route သတ်မှတ်ချက်များကို PHP 8 Attribute အစား EC-CUBE Standard ဖြစ်သော PHPDoc Annotation `@Route(...)` ပုံစံဖြင့် ရေးသားခြင်း** ကို အတွေ့အကြုံ (၆) လရှိ Junior Developer များ အလွယ်တကူ လိုက်နာနားလည်နိုင်စေရန် မြန်မာဘာသာဖြင့် အဆင့်ဆင့် ရေးသားထားသော လမ်းညွှန်ဖြစ်ပါသည်။

---

## ၁။ အနှစ်ချုပ် ခြုံငုံသုံးသပ်ချက် (Overview & Purpose)

EC-CUBE မူရင်းတွင် ဝယ်ယူသူများ မည်သည့်ပစ္စည်းများကို Favorite ပြုလုပ်ထားသည်ကို Admin Panel မှ စုစည်းကြည့်ရှုနိုင်သော စာမျက်နှာ မပါဝင်ပါ။
ဤ Customization ဖြင့် အောက်ပါ အချက်များကို အကောင်အထည်ဖော်ထားပါသည်:

1. **Admin Favorite Dashboard (`/admin/product/favorite`):**
   * ဝယ်ယူသူ မည်သူက မည်သည့်ပစ္စည်းကို မည်သည့်အချိန်တွင် Favorite လုပ်ထားသည်ကို စာရင်းဇယားဖြင့် ပြသခြင်း။
   * ကုန်ပစ္စည်းပုံ၊ အမည်၊ Code၊ ဈေးနှုန်းနှင့် ဝယ်ယူသူ၏ အမည်၊ Email တို့ကို Admin အသေးစိတ် စာမျက်နှာများနှင့် ချိတ်ဆက်ပေးထားခြင်း။
2. **အဆင့်မြင့် ရှာဖွေမှု စနစ် (Advanced Search & Filter):**
   * ကုန်ပစ္စည်းအမည်၊ Product Code၊ ဝယ်ယူသူအမည်၊ Email၊ သို့မဟုတ် Customer ID ဖြင့် ရှာဖွေနိုင်ခြင်း (Multi-search)။
   * စတင်ထည့်သွင်းခဲ့သည့် နေ့စွဲအလိုက် (Date Range: Start 〜 End) စစ်ထုတ်နိုင်ခြင်း။
3. **စာရင်းအင်း အနှစ်ချုပ် ကတ်များ (Summary Statistics Cards):**
   * စုစုပေါင်း Favorite မှတ်ပုံတင်မှု အရေအတွက်။
   * Favorite အဖြစ် မှတ်သားခံထားရသော သီးခြားကုန်ပစ္စည်း အရေအတွက်။
   * Favorite မှတ်သားထားသော သီးခြားဝယ်ယူသူ ဦးရေ။
4. **ဖျက်ပစ်နိုင်သော လုပ်ဆောင်ချက် (Delete Action):**
   * CSRF Token လုံခြုံရေး စနစ်ပါဝင်သော Confirmation Modal ဖြင့် မလိုအပ်သော Favorite Data များကို ဖျက်ပစ်နိုင်ခြင်း။
5. **Admin Menu ချိတ်ဆက်မှု (Nav Menu Integration):**
   * Admin ဘယ်ဘက် Sidebar ရှိ **商品管理 (Product Management) -> お気に入り商品一覧** အောက်တွင် Menu Item အသစ် ထည့်သွင်းထားခြင်း။
6. **Route Annotation Standard:**
   * Route အားလုံးကို EC-CUBE ၏ စံသတ်မှတ်ချက်အတိုင်း Docblock Annotation (`@Route(...)`) ပုံစံဖြင့်သာ တိကျစွာ ရေးသားထားခြင်း။

---

## ၂။ Route သတ်မှတ်ချက်ပုံစံ ရှင်းလင်းချက် (Annotation vs PHP 8 Attribute)

EC-CUBE 4.3 တွင် Controller Route များကို သတ်မှတ်ရာ၌ PHP 8 Attribute (`#[Route(...)]`) အစား **PHPDoc Annotation (`@Route(...)`)** ပုံစံကို အသုံးပြုရပါသည်:

```php
// ✅ မှန်ကန်သော ပုံစံ (EC-CUBE Standard Annotation Format)
/**
 * @Route("/%eccube_admin_route%/product/favorite", name="admin_product_favorite", methods={"GET", "POST"})
 * @Route("/%eccube_admin_route%/product/favorite/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_favorite_page", methods={"GET", "POST"})
 * @Template("@admin/Product/favorite.twig")
 */
public function index(Request $request, PaginatorInterface $paginator, $page_no = null)
{
    // ...
}

// ❌ မသုံးရသော ပုံစံ (PHP 8 Attribute Format)
// #[Route('/admin/product/favorite', name: 'admin_product_favorite', methods: ['GET', 'POST'])]
```

---

## ၃။ မူရင်း Core ဖိုင်များ စာရင်း (Origin Core File List)

> [!CAUTION]
> **EC-CUBE Core Rules:** `src/Eccube/` အောက်ရှိ မူရင်း Core ဖိုင်များကို **တိုက်ရိုက် ပြင်ဆင်ခြင်း မပြုလုပ်ရပါ**။

| စဉ် | မူရင်း Core ဖိုင်လမ်းကြောင်း (Origin Core File Path) | မူရင်း တာဝန် (Default Role) |
| :---: | :--- | :--- |
| ၁ | `src/Eccube/Entity/CustomerFavoriteProduct.php` | Favorite Database Table (`dtb_customer_favorite_product`) နှင့် ချိတ်ဆက်ထားသော Entity။ |
| ၂ | `src/Eccube/Repository/CustomerFavoriteProductRepository.php` | Favorite Database Query များကို ကိုင်တွယ်သည့် Repository။ |
| ၃ | `src/Eccube/Resource/template/admin/default_frame.twig` | Admin Panel ၏ အခြေခံ ပင်မ Layout Frame Template။ |
| ၄ | `src/Eccube/Resource/template/admin/nav.twig` | Admin Panel ဘယ်ဘက် Sidebar Menu Render ပြုလုပ်သည့် Template။ |

---

## ၄။ ပြင်ဆင် / အသစ်ဖန်တီးထားသော ဖိုင်များ စာရင်း (Updated / Created File List)

EC-CUBE Customization စည်းမျဉ်းများနှင့်အညီ `app/Customize/` နှင့် `app/template/` အောက်တွင် အောက်ပါအတိုင်း အသစ်ဖန်တီး/ပြင်ဆင်ထားပါသည်:

| စဉ် | ပြင်ဆင်/အသစ်ဖန်တီးထားသော ဖိုင်လမ်းကြောင်း (File Path) | အမျိုးအစား | တာဝန်နှင့် လုပ်ဆောင်ချက် (Role & Description) |
| :---: | :--- | :---: | :--- |
| ၁ | [`FavoriteController.php`](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/Customize/Controller/Admin/Product/FavoriteController.php) | **အသစ်ဖန်တီး (NEW)** | Admin Favorite List စာမျက်နှာနှင့် Delete လုပ်ဆောင်ချက်ကို ကိုင်တွယ်သည့် Controller (Route Annotation ပုံစံဖြင့် ရေးသားထားသည်)။ |
| ၂ | [`SearchFavoriteProductType.php`](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/Customize/Form/Type/Admin/SearchFavoriteProductType.php) | **အသစ်ဖန်တီး (NEW)** | Admin Search Form (Multi Keyword, Date Start/End) အတွက် Symfony Form Type Class။ |
| ၃ | [`favorite.twig`](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/template/admin/Product/favorite.twig) | **အသစ်ဖန်တီး (NEW)** | Admin Favorite List UI Template (Search Card, Summary Cards, Data Table, Delete Modal, Paginator)။ |
| ၄ | [`eccube_nav.yaml`](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/config/eccube/packages/eccube_nav.yaml) | **ပြင်ဆင် (UPDATED)** | Admin Sidebar Menu ရှိ 商品管理 အောက်တွင် `お気に入り商品一覧` Menu Link ထည့်သွင်းထားခြင်း။ |
| ၅ | [`messages.ja.yaml`](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/app/Customize/Resource/locale/messages.ja.yaml) | **အသစ်ဖန်တီး (NEW)** | Admin Favorite စနစ်အတွက် ဂျပန်ဘာသာ Translation String များ။ |

---

## ၅။ အဆင့်ဆင့် အကောင်အထည်ဖော်မှု လမ်းညွှန် (Step-by-Step Implementation Details)

### အဆင့် ၁: Search Form Type ဖန်တီးခြင်း
**ဖိုင်တည်နေရာ:** `app/Customize/Form/Type/Admin/SearchFavoriteProductType.php`
* **ရှာဖွေနိုင်သော Fields များ:**
  * `multi`: Product Name, Product Code, Customer Name, Email, Customer ID
  * `create_date_start` & `create_date_end`: Favorite ထည့်သွင်းခဲ့သည့် နေ့စွဲ အပိုင်းအခြား

---

### အဆင့် ၂: Admin Favorite Controller ဖန်တီးခြင်း
**ဖိုင်တည်နေရာ:** `app/Customize/Controller/Admin/Product/FavoriteController.php`
* **Annotation Routes:**
  ```php
  /**
   * @Route("/%eccube_admin_route%/product/favorite", name="admin_product_favorite", methods={"GET", "POST"})
   * @Route("/%eccube_admin_route%/product/favorite/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_favorite_page", methods={"GET", "POST"})
   * @Template("@admin/Product/favorite.twig")
   */
  ```
* **လုပ်ဆောင်ချက်များ:**
  1. Search Form မှ Data ကို လက်ခံပြီး QueryBuilder ဖြင့် Join Query တည်ဆောက်ခြင်း။
  2. KNP Paginator ဖြင့် စာမျက်နှာ ခွဲထုတ်ခြင်း။
  3. Total Favorites, Unique Products, Unique Customers စာရင်းအင်းများ တွက်ချက်ခြင်း။
  4. Delete Action (`admin_product_favorite_delete`) တွင် CSRF Token စစ်ဆေးပြီး ဒေတာဖျက်ခြင်း။

---

### အဆင့် ၃: Admin UI Template ဖန်တီးခြင်း
**ဖိုင်တည်နေရာ:** `app/template/admin/Product/favorite.twig`
* **ပါဝင်သော အစိတ်အပိုင်းများ:**
  * Search Filter Card (ကလစ်နှိပ်၍ အဖွင့်/အပိတ် ပြုလုပ်နိုင်သော ရှာဖွေရေး အကွက်)
  * Stat Cards (စုစုပေါင်း အချက်အလက် ကတ် ၃ ခု)
  * Table View (Product ပုံ၊ အမည်၊ Customer အချက်အလက်၊ နေ့စွဲ၊ Delete ခလုတ်)
  * Confirmation Modal (ဖျက်ရန် အတည်ပြုချက် မေးမြန်းသော Modal)
  * Pagination Footer (စာမျက်နှာ ကူးပြောင်းရန် ခလုတ်များ)

---

### အဆင့် ၄: Admin Navigation Menu ထည့်သွင်းခြင်း
**ဖိုင်တည်နေရာ:** `app/config/eccube/packages/eccube_nav.yaml`
```yaml
    eccube_nav:
        product:
            name: admin.product.product_management
            icon: fa-cube
            children:
                product_master:
                    name: admin.product.product_list
                    url: admin_product
                product_favorite:
                    name: admin.product.favorite_list
                    url: admin_product_favorite
```

---

## ၆။ စစ်ဆေးအတည်ပြုရန် Console Commands များ

```bash
# ၁။ Symfony Cache ရှင်းလင်းခြင်း
docker compose -f docker-compose.yml -f docker-compose.mysql.yml exec ec-cube bin/console cache:clear --no-warmup

# ၂။ Admin Favorite Routes များ မှန်ကန်စွာ စာရင်းဝင်ခြင်း ရှိ/မရှိ စစ်ဆေးခြင်း
docker compose -f docker-compose.yml -f docker-compose.mysql.yml exec ec-cube bin/console debug:router admin_product_favorite
docker compose -f docker-compose.yml -f docker-compose.mysql.yml exec ec-cube bin/console debug:router admin_product_favorite_delete
```

---

## ၇။ ဝင်ရောက်ကြည့်ရှု စမ်းသပ်နိုင်သော Admin URL

* **Admin Panel URL:** `http://localhost:8080/admin/product/favorite`
* **Admin Menu လမ်းကြောင်း:** ဘယ်ဘက် Sidebar -> **商品管理** -> **お気に入り商品一覧**
