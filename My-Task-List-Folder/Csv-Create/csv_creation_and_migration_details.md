# EC-CUBE 4.3.1 - CSV Creation, Architecture & Migration Deep-Dive Guide
## (CSV စနစ် တည်ဆောက်ခြင်း၊ Architecture မေးခွန်းများ၏ အဖြေ၊ Migration ဖိုင် ဖန်တီးပုံနှင့် တစ်ကြောင်းချင်း အသေးစိတ် ရှင်းလင်းချက် လက်စွဲ)

---

## ၁။ အမေးများသော နည်းပညာ မေးခွန်းများနှင့် အဖြေများ (Core Architectural Q&A)

---

### မေးခွန်း (၁) - Repository File မှ Data ရယူခြင်းသည် အမှားလော?
#### (Is getting data from the repository file a mistake?)

> **တိုတိုနှင့် လိုရင်း အဖြေ:**  
> **လုံးဝ (လုံးဝ) အမှားမဟုတ်ပါ။** ၎င်းသည် Symfony နှင့် EC-CUBE ၏ အကောင်းဆုံး စံနှုန်းဖြစ်သော **Repository Design Pattern (Separation of Concerns)** ဖြစ်ပါသည်။

#### အဘယ်ကြောင့် Repository ကို သုံးရသနည်း?
1. **တာဝန်ခွဲဝေမှု (Separation of Responsibilities):**
   * **Controller ၏ တာဝန်:** User ဆီမှ HTTP Request ကို လက်ခံခြင်း၊ Response (HTML/CSV) ပြန်ပို့ခြင်းကိုသာ အဓိက လုပ်ဆောင်ရပါမည်။ Controller ထဲတွင် ရှုပ်ထွေးသော Database SQL / DQL Query များကို မရေးရပါ။
   * **Repository ၏ တာဝန်:** Database မှ Data ဆွဲထုတ်ခြင်း၊ QueryBuilder တည်ဆောက်ခြင်း၊ JOINs နှင့် Aggregations (COUNT, GROUP BY) များကို သီးသန့် တာဝန်ယူရပါမည်။

2. **`CsvExportService` နှင့် ချိတ်ဆက်ရာတွင် အလွန်အရေးကြီးသော အချက်:**
   * အကယ်၍ သင်သည် Repository ထဲတွင် `$qb->getQuery()->getResult()` ဟုရေးပြီး Product Entity Object ထောင်ပေါင်းများစွာကို Array အနေဖြင့် Controller ဆီသို့ return ပေးလိုက်ပါက **PHP Memory Exhausted (Memory ပြည့်ပြီး Server Crash ဖြစ်သည့် Error)** တက်သွားပါမည်။
   * ထို့ကြောင့် Repository သည် Array ဒေတာကို မပေးဘဲ **`QueryBuilder ($qb)` ကိုသာ return ပေးရပါသည်**။
   * Controller က ထို `$qb` ကို `CsvExportService::setExportQueryBuilder($qb)` ထဲသို့ ထည့်ပေးလိုက်သောအခါ `CsvExportService` သည်:
     * ဒေတာ အခု (၁၀၀) စီ ခွဲယူခြင်း (Chunking/Pagination)
     * တစ်သုတ်ပြီးတိုင်း Doctrine Cache ကို ရှင်းလင်းပေးခြင်း (`$em->clear()`)
     တို့ကို နောက်ကွယ်မှ အလိုအလျောက် ပြုလုပ်ပေးသဖြင့် Record သောင်းချီရှိသော်လည်း Memory အနည်းငယ်သာ သုံးစွဲပြီး လျင်မြန်စွာ Download ဆွဲနိုင်ခြင်း ဖြစ်ပါသည်။

---

### မေးခွန်း (၂) - EC-CUBE တွင် အဘယ်ကြောင့် CSV Type Format ကို အသုံးပြုရသနည်း?
#### (Why do we get from CSV Type in this format in EC-CUBE?)

> **တိုတိုနှင့် လိုရင်း အဖြေ:**  
> EC-CUBE သည် CSV Columns များကို PHP Code ထဲတွင် Hardcode မရေးဘဲ **Admin UI မှ စိတ်ကြိုက် ပြင်ဆင်နိုင်စေရန် Database-Driven Architecture** ဖြင့် တည်ဆောက်ထားသောကြောင့် ဖြစ်ပါသည်။

#### EC-CUBE ၏ Standard CSV Types စာရင်း:
EC-CUBE Core (`Eccube\Entity\Master\CsvType`) တွင် အောက်ပါ ID များကို မူရင်းအတိုင်း သတ်မှတ်ထားပါသည်:
* **ID: 1** -> Product CSV (`CSV_TYPE_PRODUCT`)
* **ID: 2** -> Customer CSV (`CSV_TYPE_CUSTOMER`)
* **ID: 3** -> Order CSV (`CSV_TYPE_ORDER`)
* **ID: 4** -> Shipping CSV (`CSV_TYPE_SHIPPING`)
* **ID: 5** -> Category CSV (`CSV_TYPE_CATEGORY`)

ကျွန်ုပ်တို့သည် ဝယ်ယူသူ အကြိုက်ဆုံး ကုန်ပစ္စည်းများအတွက်:
* **ID: 20** -> Favourite Product CSV (`CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT = 20`)

#### ဤ Format ၏ အကျိုးကျေးဇူး:
Controller တွင် `$this->csvExportService->initCsvType(20);` ဟု ရေးလိုက်သည်နှင့် တစ်ပြိုင်နက်:
1. `CsvExportService` သည် Database ၏ `dtb_csv` table ထဲမှ `csv_type_id = 20` ဖြစ်ပြီး `enabled = 1` ဖြစ်သော columns များကိုသာ `sort_no` အစဉ်အတိုင်း အလိုအလျောက် query ဆွဲထုတ်ပေးသည်။
2. ဆိုင်ပိုင်ရှင် Admin သည် Code ပြင်စရာမလိုဘဲ Admin Panel ရှိ **設定 (Settings) -> 店舗設定 (Shop Settings) -> CSV設定 (CSV Settings)** (`/admin/setting/shop/csv/20`) မှနေ၍ ကော်လံအမည် ပြောင်းခြင်း၊ မလိုချင်သော ကော်လံကို မျက်စိမှိတ်ပိတ်ခြင်း၊ အစီအစဉ် နေရာရွှေ့ခြင်းတို့ကို ပြုလုပ်နိုင်သွားမည် ဖြစ်ပါသည်။

---

### မေးခွန်း (၃) - အဘယ်ကြောင့် Route (၂) ခု ခွဲခြား ရေးသားရသနည်း?
#### (Why does it create two routes for the Favourite Products List Page?)

Controller တွင် အောက်ပါအတိုင်း Route (၂) ခုကို တွေ့မြင်ရပါမည်:
```php
/**
 * @Route("/%eccube_admin_route%/product/favourite", name="admin_product_favourite", methods={"GET"})
 * @Route("/%eccube_admin_route%/product/favourite/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_favourite_page", methods={"GET"})
 */
```

#### အကြောင်းရင်း ရှင်းလင်းချက်:
1. **Route (၁) - `admin_product_favourite` (`/admin/product/favourite`):**
   * Sidebar Menu မှ စတင်နှိပ်လိုက်ချိန်တွင် ပထမဆုံး စာမျက်နှာ (Page 1) ကို ဖွင့်လှစ်ပေးသည့် Base URL ဖြစ်ပါသည်။ ဤ Route တွင် `page_no` ပါဝင်ခြင်း မရှိပါ။
2. **Route (၂) - `admin_product_favourite_page` (`/admin/product/favourite/page/{page_no}`):**
   * စာမျက်နှာ အောက်ခြေရှိ Pagination ခလုတ်များ (Page 2, Page 3, ...) ကို နှိပ်သည့်အခါ အသုံးပြုသည့် URL ဖြစ်ပါသည်။
   * `requirements={"page_no" = "\d+"}` သည် `page_no` နေရာတွင် ဂဏန်းသီးသန့် (0-9) သာ လက်ခံမည်ဟု ကန့်သတ်ထားခြင်း ဖြစ်ပါသည်။

> [!WARNING]
> **အကယ်၍ Route (၂) ကို မထည့်သွင်းပါက ဘာဖြစ်မည်နည်း?**  
> Twig Template ထဲရှိ Pagination Pager Component (`@admin/pager.twig`) သည် ဒုတိယ စာမျက်နှာသို့ သွားရန် Link ဆောက်သည့်အခါ `admin_product_favourite_page` route ကို အသုံးပြုပါသည်။ ထိုအခါ Route မရှိသဖြင့် **`404 Route Not Found` Error** တက်သွားမည် ဖြစ်ပါသည်။ ထို့ကြောင့် Route နှစ်ခု ခွဲရေးခြင်းသည် Symfony/EC-CUBE ၏ စံနည်းလမ်း ဖြစ်ပါသည်။

---

### မေးခွန်း (၄) - Search Filter နှင့် Customer Name Search များကို အဘယ်ကြောင့် ဖြုတ်ပယ်လိုက်သနည်း?
#### (Why were Search Filters removed?)

* မူလက Admin List Page များတွင် Search Filter ထည့်သွင်းလေ့ရှိသောကြောင့် ထည့်သွင်းထားခဲ့သော်လည်း User ၏ တိုက်ရိုက် လိုအပ်ချက်မှာ **"Search Filter မပါဘဲ Favorite Product List နှင့် CSV Export + CSV Setting သီးသန့်သာ လိုအပ်သည်"** ဟု ညွှန်ကြားထားသောကြောင့် ဖြစ်ပါသည်။
* ယခုအခါ ရှုပ်ထွေးသော Search Form (`SearchFavoriteProductType`)၊ Customer Name Search နှင့် Like filter များကို **လုံးဝ ဖယ်ရှား (Remove) သန့်စင်ပြီး ဖြစ်ပါသည်**။
* Controller, Repository နှင့် Twig UI တို့တွင် Favorite Products စာရင်းပြသခြင်း၊ Pagination နှင့် CSV Download/Settings ခလုတ်များသာ ပါဝင်သော **အလွန်ရိုးရှင်းပြီး သန့်ရှင်းသော Core Standard Code** အဖြစ် ပြင်ဆင်ပြီး ဖြစ်ပါသည်။

---

## ၂။ Doctrine Migration ဖိုင်ကို Command ဖြင့် မည်သို့ ဖန်တီးရမည်နည်း?
### (How to create migration file by command?)

Migration ဖိုင်တစ်ခုကို လက်ဖြင့် နာမည်ပေး ဖန်တီးမည့်အစား Terminal တွင် အောက်ပါ Symfony/Doctrine command ဖြင့် အလွယ်တကူ generate လုပ်နိုင်ပါသည်:

```bash
docker compose exec ec-cube php bin/console doctrine:migrations:generate
```

#### ဖိုင်အမည် ဖွဲ့စည်းပုံ သဘောတရား:
အထက်ပါ command ကို run လိုက်ပါက `app/DoctrineMigrations/` အောက်တွင် ဖိုင်အသစ်တစ်ခု အလိုအလျောက် ထွက်ပေါ်လာမည် ဖြစ်သည်:
* ဥပမာ ဖိုင်အမည် - **`Version20260917083632.php`**
* `Version` + **`2026`** (ခုနှစ်) + **`09`** (လ) + **`17`** (ရက်) + **`08`** (နာရီ) + **`36`** (မိနစ်) + **`32`** (စက္ကန့်)
* Doctrine သည် ဤ Timestamp ကို ကြည့်၍ မည်သည့် Migration ကို အရင် run ရမည်၊ မည်သည့် Migration ကို နောက်မှ run ရမည်ဟူသော အစဉ်အတိုင်း (Execution Order) ကို ဆုံးဖြတ်ပါသည်။

---

## ၃။ `Version20260917083632.php` ၏ အလုပ်လုပ်ပုံကို တစ်ကြောင်းချင်း အသေးစိတ် ရှင်းလင်းချက်
### (Line-by-Line Code Breakdown of Migration File)

အောက်ပါ code သည် ကျွန်ုပ်တို့၏ Favorite Product CSV Type နှင့် Columns များကို Database သို့ ထည့်သွင်းပေးသော Migration ဖိုင် ဖြစ်ပါသည်။ တစ်ကြောင်းချင်းစီ၏ အဓိပ္ပာယ်ကို အသေးစိတ် လေ့လာနိုင်ပါသည်:

```php
1:  <?php
2:  
3:  declare(strict_types=1);
4:  
5:  namespace DoctrineMigrations;
6:  
7:  use Customize\Constant\CustomCsvType;
8:  use Doctrine\DBAL\Schema\Schema;
9:  use Doctrine\Migrations\AbstractMigration;
10: 
11: final class Version20260917083632 extends AbstractMigration
12: {
13:     public function getDescription(): string
14:     {
15:         return 'Add Favorite Product CSV type (ID: 20) and default columns into mtb_csv_type and dtb_csv';
16:     }
17: 
18:     public function up(Schema $schema): void
19:     {
20:         $csvTypeId = CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT;
21: 
22:         // ၁။ mtb_csv_type တွင် Custom CSV Type (ID: 20) ထည့်သွင်းခြင်း
23:         if ($schema->hasTable('mtb_csv_type')) {
24:             $typeExists = (int) $this->connection->fetchOne(
25:                 "SELECT COUNT(*) FROM mtb_csv_type WHERE id = ?",
26:                 [$csvTypeId]
27:             );
28: 
29:             if ($typeExists === 0) {
30:                 $this->addSql(
31:                     "INSERT INTO mtb_csv_type (id, name, sort_no, discriminator_type) VALUES (?, 'お気に入り商品CSV', (SELECT COALESCE(MAX(t.sort_no), 0) + 1 FROM (SELECT sort_no FROM mtb_csv_type) AS t), 'csvtype')",
32:                     [$csvTypeId]
33:                 );
34:             }
35:         }
36: 
37:         // ၂။ dtb_csv တွင် Default Output Columns (၆) ခု ထည့်သွင်းခြင်း
38:         if ($schema->hasTable('dtb_csv')) {
39:             $csvColsExists = (int) $this->connection->fetchOne(
40:                 "SELECT COUNT(*) FROM dtb_csv WHERE csv_type_id = ?",
41:                 [$csvTypeId]
42:             );
43: 
44:             if ($csvColsExists === 0) {
45:                 $items = [
46:                     ['id',             '商品ID',         1],
47:                     ['name',           '商品名',         2],
48:                     ['price02_min',    '販売価格(下限)', 3],
49:                     ['price02_max',    '販売価格(上限)', 4],
50:                     ['Status',         '公開状態',       5],
51:                     ['favorite_count', 'お気に入り数',   6],
52:                 ];
53: 
54:                 foreach ($items as [$fieldName, $dispName, $sortNo]) {
55:                     $this->addSql(
56:                         "INSERT INTO dtb_csv (csv_type_id, entity_name, field_name, reference_field_name, disp_name, sort_no, enabled, create_date, update_date, discriminator_type) VALUES (?, 'Eccube\\\\Entity\\\\Product', ?, NULL, ?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'csv')",
57:                         [$csvTypeId, $fieldName, $dispName, $sortNo]
58:                     );
59:                 }
60:             }
61:         }
62:     }
63: 
64:     public function down(Schema $schema): void
65:     {
66:         $csvTypeId = CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT;
67: 
68:         if ($schema->hasTable('dtb_csv')) {
69:             $this->addSql("DELETE FROM dtb_csv WHERE csv_type_id = ?", [$csvTypeId]);
70:         }
71: 
72:         if ($schema->hasTable('mtb_csv_type')) {
73:             $this->addSql("DELETE FROM mtb_csv_type WHERE id = ?", [$csvTypeId]);
74:         }
75:     }
76: }
```

---

### တစ်ကြောင်းချင်း ရှင်းလင်းချက် (Line-by-Line Explanation):

* **Line 3 (`declare(strict_types=1);`):** PHP တွင် Data types များကို တင်းကြပ်စွာ စစ်ဆေးစေခြင်း (ဥပမာ- string နေရာတွင် int အမှားထည့်ပါက error ပြခြင်း)။
* **Line 5 (`namespace DoctrineMigrations;`):** Migration ဖိုင်အားလုံး ထားရှိရမည့် စံ Namespace ဖြစ်သည်။
* **Line 7-9 (`use ...;`):** အသုံးပြုမည့် Class များကို Import လုပ်ခြင်း:
  * `CustomCsvType`: ကျွန်ုပ်တို့ သတ်မှတ်ထားသော Constant Class (ID: 20)။
  * `Schema`: Database ဇယားများ၊ ကော်လံများ ရှိမရှိ စစ်ဆေးပေးသည့် Doctrine DBAL အရာဝတ္ထု။
  * `AbstractMigration`: Doctrine Migration တိုင်း အမွေဆက်ခံ (Inherit) ရမည့် Core Base Class။
* **Line 11 (`final class Version... extends AbstractMigration`):** Migration Class အမည် ဖြစ်သည်။ Class နာမည်သည် ဖိုင်အမည်နှင့် အတိအကျ တူညီရပါမည်။
* **Line 13-16 (`getDescription()`):** ဤ Migration ဖိုင်သည် ဘာအတွက် ရေးထားသနည်းဟူသော ဖော်ပြချက် (Description) ကို ပြန်ပေးသည်။ `bin/console doctrine:migrations:status` တွင် ဤစာသားကို မြင်တွေ့ရမည်။
* **Line 18 (`public function up(Schema $schema): void`):**
  * **`up()` Method သည် အဓိက အသက် ဖြစ်သည်!**
  * Terminal တွင် `bin/console doctrine:migrations:migrate` ဟု run သည့်အခါ ဤ `up()` method ထဲရှိ ကုဒ်များ အလုပ်လုပ်ပြီး Database ထဲသို့ Data သွင်းခြင်း/ဇယားဆောက်ခြင်း ပြုလုပ်သည်။
  * **`Schema $schema` ဆိုတာ ဘာလဲ?** လက်ရှိ Database ၏ ဖွဲ့စည်းပုံ (Structure) ကို ကိုယ်စားပြုသော Object ဖြစ်သည်။
* **Line 20 (`$csvTypeId = CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT;`):** ID နံပါတ် ၂၀ ကို variable ထဲ ထည့်ယူသည်။
* **Line 23 (`if ($schema->hasTable('mtb_csv_type'))`):** Database ထဲတွင် `mtb_csv_type` ဇယား အမှန်တကယ် ရှိမရှိ စစ်ဆေးခြင်း (ဇယားမရှိဘဲ SQL run ပါက Error တက်မည်ကို ကာကွယ်ခြင်း)။
* **Line 24-27 (`fetchOne(...)`):** ID: 20 ရှိပြီးသား ဟုတ်မဟုတ် စစ်ဆေးခြင်း (Duplicate Key Error မဖြစ်စေရန်)။
* **Line 30-33 (`$this->addSql(...)` for `mtb_csv_type`):**
  * `INSERT INTO mtb_csv_type (id, name, sort_no, discriminator_type) VALUES (?, 'お気に入り商品CSV', ..., 'csvtype')`
  * **`id`:** ၂၀ (Custom Type ID)
  * **`name`:** Admin Dropdown တွင် ပေါ်မည့် အမည် ('お気に入り商品CSV')
  * **`sort_no`:** နောက်ဆုံး နံပါတ်၏ နောက်တွင် အလိုအလျောက် +1 ပေါင်းထည့်ပေးခြင်း
  * **`discriminator_type`:** Doctrine Single Table Inheritance အတွက် EC-CUBE စံတန်ဖိုး `'csvtype'`။
* **Line 38 (`if ($schema->hasTable('dtb_csv'))`):** `dtb_csv` table ရှိမရှိ စစ်ဆေးခြင်း။
* **Line 39-42:** ID: 20 အတွက် Columns များ သွင်းပြီးသား ဟုတ်မဟုတ် စစ်ဆေးခြင်း။
* **Line 45-52 (`$items` Array):** CSV တွင် ပေါ်စေလိုသော မူလ Default Columns (၆) ခု:
  1. `id` -> '商品ID'
  2. `name` -> '商品名'
  3. `price02_min` -> '販売価格(下限)'
  4. `price02_max` -> '販売価格(上限)'
  5. `Status` -> '公開状態'
  6. `favorite_count` -> 'お気に入り数'
* **Line 54-59 (`foreach ... addSql` for `dtb_csv`):**
  * ကော်လံ (၆) ခုကို Loop ပတ်၍ `dtb_csv` ထဲသို့ တန်းစီထည့်သွင်းပေးခြင်း:
    * `csv_type_id`: ၂၀
    * `entity_name`: `'Eccube\Entity\Product'` (မည်သည့် Entity ထဲမှ Data ယူမည်နည်း)
    * `field_name`: Entity ၏ Property အမည် (`id`, `name`, `Status`, `favorite_count`)
    * `disp_name`: CSV Header တန်းတွင် ဖော်ပြမည့် ဂျပန်အမည်
    * `sort_no`: ကော်လံ အစီအစဉ် (1, 2, 3, 4, 5, 6)
    * `enabled`: ၁ (Default အနေဖြင့် Output ထုတ်ပေးမည်)
    * `discriminator_type`: `'csv'`
* **Line 64 (`public function down(Schema $schema): void`):**
  * **`down()` Method:** အကယ်၍ migration ကို ပြန်ဖျက်လိုပါက (Rollback လုပ်လိုပါက) run မည့် ကုဒ် ဖြစ်သည်။
  * ထည့်ထားသော `dtb_csv` နှင့် `mtb_csv_type` မှ ID: 20 ဒေတာများကို သန့်ရှင်းစွာ ပြန်လည် `DELETE` လုပ်ပေးသည်။

---

## ၄။ နောက်ထပ် Custom CSV အသစ်များ ဖန်တီးလိုပါက မည်သည့်အချိန်တွင် DB Migration အသစ် ဖန်တီးရမည်နည်း?
### (When to create a new DB Migration file for other CSVs?)

အကယ်၍ သင်သည် အနာဂတ်တွင် အခြားသော Feature အသစ်များအတွက် Custom CSV ထပ်မံ ဖန်တီးလိုပါက (ဥပမာ- **Product Review CSV**, **Stock Alert CSV**, သို့မဟုတ် **Daily Sales CSV**) အောက်ပါ အခြေအနေတွင် Migration ဖိုင်အသစ် ဖန်တီးရပါမည်:

#### အခြေအနေ (When):
> **Admin Panel ရှိ `/admin/setting/shop/csv` တွင် CSV Type အသစ်တစ်ခု ထပ်တိုးချင်သည့် အခါတိုင်း** Migration ဖိုင်အသစ် တစ်ခု မဖြစ်မနေ ဖန်တီးရပါမည်။

#### အဆင့်ဆင့် ဖန်တီးနည်း (Beginner Guide to Replicate):
1. **Constant သတ်မှတ်ပါ:**  
   `CustomCsvType.php` တွင် မသုံးရသေးသော ID အသစ်တစ်ခု ပေးပါ (ဥပမာ- `CSV_TYPE_PRODUCT_REVIEW = 21;`)။
2. **Migration ဖိုင် Generate လုပ်ပါ:**  
   `docker compose exec ec-cube php bin/console doctrine:migrations:generate`
3. **`up()` method တွင် SQL ထည့်ပါ:**  
   အထက်ဖော်ပြပါ `Version20260917083632.php` မှ code ကို copy ကူးပြီး:
   * `id`: ၂၁
   * `name`: `'商品レビューCSV'`
   * `$items`: ထုတ်ပေးလိုသော Reviews ကော်လံများ စာရင်း ပြောင်းလဲပေးပါ။
4. **Migration အား Run ပါ:**  
   `docker compose exec ec-cube php bin/console doctrine:migrations:migrate --no-interaction`
5. **ပြီးစီးပါပြီ!**  
   Admin Panel ရှိ CSV Setting တွင် သင်ဖန်တီးလိုက်သော CSV Type အသစ်ချက်ချင်း ပေါ်လာမည် ဖြစ်ပါသည်။
