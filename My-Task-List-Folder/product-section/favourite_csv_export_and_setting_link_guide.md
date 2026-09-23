# EC-CUBE 4.3.1 - Favourite Page CSV Export & CSV Output Settings Link Guide
## (ဝယ်ယူသူ အကြိုက်ဆုံး ကုန်ပစ္စည်း စာမျက်နှာတွင် CSV Export ပြုလုပ်ခြင်း၊ Store CSV Settings နှင့် ချိတ်ဆက်ခြင်း၊ ဖိုင်များ ဖန်တီး/ပြင်ဆင်/Overwrite လုပ်ခြင်းဆိုင်ရာ အသေးစိတ် လက်စွဲ)

ဤစာရွက်စာတမ်းသည် EC-CUBE 4.3.1 (Symfony 5.4 Base) တွင် **(၁) ဝယ်ယူသူ အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာမျက်နှာ (Favourite Product Page) တွင် CSV Download စနစ် ဖန်တီးပုံ**၊ **(၂) အဆိုပါ CSV စနစ်အား Admin Panel ရှိ Store CSV Output Settings (`/admin/setting/shop/csv`) နှင့် ချိတ်ဆက်ပုံ**၊ နှင့် **(၃) စနစ်တစ်ခုလုံးအတွက် မည်သည့် ဖိုင်များကို အသစ်ဖန်တီးရမည် (New)၊ မည်သည့်ဖိုင်များကို ပြင်ဆင်ရမည် (Update)၊ Template Overwrite စည်းမျဉ်းများကို မည်သို့လိုက်နာရမည်** တို့ကို လုပ်ငန်းခွင် အတွေ့အကြုံ (၆) လရှိ Junior Developer များ အလွယ်တကူ လိုက်နာနားလည်နိုင်စေရန် မြန်မာဘာသာဖြင့် အသေးစိတ် ရေးသားထားသော လမ်းညွှန်စာတမ်း ဖြစ်ပါသည်။

---

## ၁။ စနစ်တစ်ခုလုံး၏ ချိတ်ဆက်ပုံ ဖွဲ့စည်းပုံ (Architecture & Workflow Diagram)

အောက်ပါပုံသည် Favourite Page မှ CSV Download ထုတ်ယူခြင်းနှင့် CSV Setting သို့ ချိတ်ဆက်၍ Columns များ ထိန်းချုပ်ခြင်း စနစ်တစ်ခုလုံး၏ ချိတ်ဆက်ပုံ ဖြစ်ပါသည်:

```mermaid
flowchart TD
    subgraph UI["Admin UI Layer (Twig Template)"]
        FavPage["Favourite Product Page<br/>(product_favourite.twig)"]
        CsvBtn["CSV Download Button<br/>(admin_product_favourite_export)"]
        SettingBtn["CSV Settings Button<br/>(admin_setting_shop_csv, id: 20)"]
        CsvSettingUI["CSV Output Settings UI<br/>(/admin/setting/shop/csv/20)"]
    end

    subgraph Backend["Controller & Service Layer"]
        Ctrl["FavouriteProductController::export()"]
        Sess["Session Filter Storage<br/>(eccube.admin.product.favourite.search)"]
        Repo["FavouriteProductRepository::getFavouriteDb()"]
        CsvService["Eccube\\Service\\CsvExportService"]
    end

    subgraph Database["Database Layer (MySQL)"]
        Mtb["mtb_csv_type<br/>(id: 20, name: 'お気に入り商品CSV')"]
        Dtb["dtb_csv<br/>(Columns: id, name, price, status, fav_count)"]
        Prod["dtb_product &<br/>dtb_customer_favorite_product"]
    end

    FavPage --> CsvBtn
    FavPage --> SettingBtn
    SettingBtn -->|Redirect with ID: 20| CsvSettingUI
    CsvSettingUI <-->|Manage Columns & Order| Dtb
    CsvSettingUI <-->|Read CSV Type Name| Mtb

    CsvBtn -->|Trigger GET Request| Ctrl
    Ctrl -->|Read Current Filters| Sess
    Ctrl -->|Build Query with Filters| Repo
    Repo -->|Query Data| Prod
    Ctrl -->|initCsvType(20)| CsvService
    CsvService -->|Read Enabled Columns| Dtb
    CsvService -->|Stream UTF-8 BOM CSV| FavPage
```

---

## ၂။ Favourite Page တွင် CSV Export စနစ် ဖန်တီးတည်ဆောက်ပုံ (The Process of Creating CSV in Favourite Page)

Admin Favourite Page တွင် CSV Export ပြုလုပ်ရာတွင် EC-CUBE Core ၏ စံနှုန်းဖြစ်သော `Eccube\Service\CsvExportService` နှင့် Symfony ၏ `StreamedResponse` ကို ပေါင်းစပ်အသုံးပြုရပါသည်။ 

### အဆင့် (က) - Controller တွင် CSV Export Action ရေးသားခြင်း

Controller ၏ `export()` method သည် အောက်ပါ အဓိက တာဝန် (၆) ချက်ကို လုပ်ဆောင်ပါသည်:

1. **Memory & Time Optimization:** 
   * `set_time_limit(0)` ဖြင့် PHP Script Execution Timeout မဖြစ်စေရန် ပြုလုပ်သည်။
   * `$this->entityManager->getConfiguration()->setSQLLogger(null)` ဖြင့် Doctrine SQL query log များ RAM ထဲ စုပုံမလာစေရန် ပိတ်ထားသည်။
2. **CSV Type Initialization:**
   * `$this->csvExportService->initCsvType(CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT)` ကို ခေါ်ယူခြင်းဖြင့် Database (`dtb_csv`) ထဲမှ ID: 20 နှင့် သက်ဆိုင်သော Active Columns များကို Memory ထဲသို့ Load လုပ်သည်။
3. **Session-based Filter Sync:**
   * Favourite List တွင် Admin အသုံးပြုသူ ရှာဖွေထားသော Criteria (ဥပမာ- Product ID, Product Name, Customer Name, Favorite Count Range) များကို Session `eccube.admin.product.favourite.search` မှ ပြန်လည်ဆွဲယူသည်။
   * အဆိုပါ Filter များအတိုင်း `FavouriteProductRepository` မှ QueryBuilder တည်ဆောက်ပြီး `setExportQueryBuilder($qb)` သို့ လွှဲပေးသည်။
4. **UTF-8 with BOM Handling (Excel Safe):**
   * Microsoft Excel (Windows/Japanese) တွင် မြန်မာစာ သို့မဟုတ် ဂျပန်စာလုံးများ မပျက်စီး (Garbled text/Mojibake မဖြစ်) စေရန် `\xEF\xBB\xBF` (UTF-8 BOM Header) ကို output stream ထဲသို့ ဦးစွာ ရေးသွင်းသည်။
5. **Dynamic Column & Row Data Extraction:**
   * `exportHeader()` ဖြင့် `dtb_csv` ရှိ `disp_name` ခေါင်းစဉ်တန်းများကို ရေးထုတ်သည်။
   * `exportData()` callback ထဲတွင် Product တစ်ခုချင်းစီအတွက် `dtb_csv` ကော်လံများကို Loop ပတ်ကာ သက်ဆိုင်ရာ တန်ဖိုးများကို `ExportCsvRow` ထဲသို့ ဖြည့်သွင်းသည်။
   * Custom field များဖြစ်သော `favorite_count` (စုစုပေါင်း အကြိုက်တွေ့မှု အရေအတွက်) နှင့် `Status` (Display Status အမည်) တို့ကို စိတ်ကြိုက် တွက်ချက်ထည့်သွင်းပေးသည်။
6. **Streaming Response:**
   * ဖိုင်တစ်ခုလုံးကို Server Hard Disk ထဲ သိမ်းဆည်းစရာမလိုဘဲ `php://output` မှတစ်ဆင့် Browser သို့ Memory သက်သာစွာ ချက်ချင်း Stream ပို့ပေးသည်။

#### 📁 Controller Code နမူနာ (`FavouriteProductController.php` မှ export method):

```php
    /**
     * Admin Favourite Product CSV Export (Search Filtered)
     *
     * @Route("/%eccube_admin_route%/product/favourite/export", name="admin_product_favourite_export", methods={"GET"})
     * @Route("/%eccube_admin_route%/product/favorite/export", name="admin_product_favorite_export", methods={"GET"})
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function export(Request $request): StreamedResponse
    {
        // ၁။ Memory နှင့် Timeout ကန့်သတ်ချက်များကို ဖြေလျှော့ခြင်း
        set_time_limit(0);
        $this->entityManager->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($request) {
            // ၂။ Custom CSV Type (ID: 20) ဖြင့် CsvExportService ကို စတင်ခြင်း
            $this->csvExportService->initCsvType(CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT);

            // ၃။ Session မှ လက်ရှိ Search Filter များကို ရယူ၍ QueryBuilder တည်ဆောက်ခြင်း
            $searchForm = $this->createForm(SearchFavoriteProductType::class);
            $viewData = $this->session->get('eccube.admin.product.favourite.search', []);
            $searchData = FormUtil::submitAndGetData($searchForm, $viewData);

            $qb = $this->favouriteProductRepository->getFavouriteDb($searchData);
            $this->csvExportService->setExportQueryBuilder($qb);

            // ၄။ MS Excel အတွက် UTF-8 BOM ထည့်သွင်းခြင်း
            $fp = fopen('php://output', 'w');
            fwrite($fp, "\xEF\xBB\xBF");
            fclose($fp);

            // ၅။ CSV Header ခေါင်းစဉ်များ ထုတ်ပေးခြင်း
            $this->csvExportService->exportHeader();

            // ၆။ Data Rows များကို Memory Leak မဖြစ်စေဘဲ တစ်တန်းချင်းစီ ထုတ်ယူခြင်း
            $this->csvExportService->exportData(function (Product $Product, CsvExportService $csvService) use ($request) {
                $Csvs = $csvService->getCsvs();
                $ExportCsvRow = new ExportCsvRow();

                foreach ($Csvs as $Csv) {
                    $fieldName = $Csv->getFieldName();

                    // Computed Field များကို သီးခြား ကိုင်တွယ်ခြင်း
                    if ($fieldName === 'favorite_count') {
                        $favoriteCount = count($Product->getCustomerFavoriteProducts());
                        $ExportCsvRow->setData($favoriteCount);
                    } elseif ($fieldName === 'Status') {
                        $statusName = $Product->getStatus() ? $Product->getStatus()->getName() : '';
                        $ExportCsvRow->setData($statusName);
                    } else {
                        // Standard Entity Fields များကို CsvExportService မှ အလိုအလျောက် ရယူခြင်း
                        $ExportCsvRow->setData($csvService->getData($Csv, $Product));
                    }

                    $ExportCsvRow->pushData();
                }

                $csvService->fputcsv($ExportCsvRow->getRow());
            });
        });

        // Response Headers (File Name & Content Type)
        $now = new \DateTime();
        $filename = 'favourite_products_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        log_info('Favourite CSV Export Completed', [$filename]);

        return $response;
    }
```

---

### အဆင့် (ခ) - Favourite Twig Template တွင် CSV Download Button ထည့်သွင်းခြင်း

Favourite List UI (`app/template/admin/Product/product_favourite.twig`) ၏ ခေါင်းစဉ် Action Bar တွင် CSV Download ခလုတ်ကို အောက်ပါအတိုင်း ထည့်သွင်းထားပါသည်:

```twig
<!-- CSV Download ခလုတ် -->
<a href="{{ url('admin_product_favourite_export') }}" class="btn btn-ec-regular">
    <i class="fa fa-cloud-download me-1 text-secondary"></i>
    <span>{{ 'admin.common.csv_download'|trans }}</span>
</a>
```

> [!NOTE]
> အသုံးပြုသူသည် Filter Card တွင် ရှာဖွေထားပါက Controller ၏ `export()` action သည် Session `eccube.admin.product.favourite.search` မှ criteria များကို အလိုအလျောက် ဖတ်ယူသဖြင့် ရှာထားသော ရလဒ်များသာ CSV ထဲတွင် စစ်ထုတ် ပါဝင်လာမည် ဖြစ်ပါသည်။

---

## ၃။ CSV Output Settings နှင့် ချိတ်ဆက်ပုံ (How to Link to Setting CSV Output Type)

EC-CUBE တွင် CSV ထုတ်ယူရာ၌ မည်သည့် Columns များ ပါဝင်ရမည်ကို Admin Panel ရှိ **設定 (Settings) -> 店舗設定 (Shop Settings) -> CSV設定 (CSV Settings)** (`/admin/setting/shop/csv`) တွင် စိတ်ကြိုက် ပြင်ဆင်နိုင်သော စနစ် ပါရှိပါသည်။

Favourite Product CSV ကို အဆိုပါ System နှင့် ချိတ်ဆက်ရန် အောက်ပါ အဆင့်များကို လုပ်ဆောင်ထားပါသည်:

### ၁။ Database Architecture (`mtb_csv_type` & `dtb_csv`)

* **`mtb_csv_type`:** CSV အမျိုးအစား Master Table ဖြစ်သည်။ မူရင်း EC-CUBE တွင် Product (1), Customer (2), Order (3), Shipping (4), Category (5), ClassName (6), ClassCategory (7) စသည်တို့ ရှိသည်။ ကျွန်ုပ်တို့ စနစ်အတွက် Favorite Product CSV အား **ID: 20** အဖြစ် သတ်မှတ်သည်။
* **`dtb_csv`:** CSV အမျိုးအစားတစ်ခုစီအတွက် ထွက်လာမည့် ကော်လံများ စာရင်းဖြစ်သည်။
  * `csv_type_id`: သက်ဆိုင်ရာ CSV Type ID (ID: 20)
  * `entity_name`: Target Entity (`Eccube\Entity\Product`)
  * `field_name`: Property အမည် (`id`, `name`, `price02_min`, `Status`, `favorite_count`)
  * `disp_name`: CSV Header တွင် ပေါ်မည့် အမည် ('商品ID', '商品名', '販売価格(下限)', စသည်)
  * `sort_no`: ကော်လံ အစီအစဉ်
  * `enabled`: ၁ ဖြစ်လျှင် CSV တွင် ပါဝင်ပြီး၊ ၀ ဖြစ်လျှင် ဖျောက်ထားမည်။

---

### ၂။ Custom CSV Type Constant သတ်မှတ်ခြင်း

Code ထဲတွင် ID နံပါတ်ကို Hardcode မရေးဘဲ Constant အဖြစ် သတ်မှတ်ပါသည်:

📁 **ဖိုင်လမ်းကြောင်း:** `app/Customize/Constant/CustomCsvType.php`
```php
namespace Customize\Constant;

class CustomCsvType
{
    public const CSV_TYPE_FAVOURITE_PRODUCT = 20;
    public const CSV_TYPE_FAVORITE_PRODUCT = 20;
}
```

---

### ၃။ Doctrine Migration ဖြင့် Master Data နှင့် Columns သွင်းခြင်း

Migration run လိုက်သည်နှင့် `mtb_csv_type` ထဲသို့ ID: 20 အသစ် ဝင်ရောက်သွားပြီး၊ `dtb_csv` ထဲသို့ အောက်ပါ Default Columns (၆) ခု အလိုအလျောက် သွင်းပေးပါသည်:

📁 **ဖိုင်လမ်းကြောင်း:** `app/DoctrineMigrations/Version20260917083632.php`
```php
// ၁။ mtb_csv_type ထဲသို့ Type အသစ်သွင်းခြင်း
$this->addSql(
    "INSERT INTO mtb_csv_type (id, name, sort_no, discriminator_type) VALUES (?, 'お気に入り商品CSV', (SELECT COALESCE(MAX(t.sort_no), 0) + 1 FROM (SELECT sort_no FROM mtb_csv_type) AS t), 'csvtype')",
    [20]
);

// ၂။ dtb_csv ထဲသို့ Default Columns (၆) ခု သွင်းခြင်း
$items = [
    ['id',             '商品ID',         1],
    ['name',           '商品名',         2],
    ['price02_min',    '販売価格(下限)', 3],
    ['price02_max',    '販売価格(上限)', 4],
    ['Status',         '公開状態',       5],
    ['favorite_count', 'お気に入り数',   6],
];

foreach ($items as [$fieldName, $dispName, $sortNo]) {
    $this->addSql(
        "INSERT INTO dtb_csv (csv_type_id, entity_name, field_name, reference_field_name, disp_name, sort_no, enabled, create_date, update_date, discriminator_type) VALUES (20, 'Eccube\\\\Entity\\\\Product', ?, NULL, ?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'csv')",
        [$fieldName, $dispName, $sortNo]
    );
}
```

---

### ၄။ Favourite Page UI မှ CSV Setting သို့ တိုက်ရိုက် ချိတ်ဆက်ခြင်း (Gear Icon Link)

Admin User သည် ကော်လံများ အစီအစဉ် ပြောင်းလိုလျှင် သို့မဟုတ် မလိုချင်သော ကော်လံကို ပိတ်လိုလျှင် Favourite Page မှ Setting စာမျက်နှာသို့ တစ်ချက်တည်းဖြင့် ရောက်ရှိစေရန် Gear Button ကို ထည့်သွင်းထားပါသည်:

📁 **ဖိုင်လမ်းကြောင်း:** `app/template/admin/Product/product_favourite.twig`
```twig
<!-- CSV Output Setting သို့ သွားမည့် ခလုတ် (ID: 20 ဖြင့် တိုက်ရိုက်ဖွင့်ပေးခြင်း) -->
<a href="{{ url('admin_setting_shop_csv', { id: constant('\\Customize\\Constant\\CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT') }) }}" class="btn btn-ec-regular">
    <i class="fa fa-cog me-1 text-secondary"></i>
    <span>{{ 'admin.setting.shop.csv_setting'|trans }}</span>
</a>
```

---

### ၅။ Admin Panel Setting UI တွင် ပြောင်းလဲမှုများ အလုပ်လုပ်ပုံ (Dynamic Binding)

1. Admin သည် Favourite Page ရှိ **「CSV設定」** ခလုတ်ကို နှိပ်လိုက်သောအခါ `/admin/setting/shop/csv/20` သို့ ရောက်ရှိသွားမည်။
2. အဆိုပါ စာမျက်နှာတွင် ကော်လံများကို Drag & Drop ပြုလုပ်၍ နေရာရွှေ့ခြင်း၊ အမည် (`disp_name`) ပြင်ဆင်ခြင်း၊ Enable/Disable အမှန်ခြစ် ဖြုတ်ခြင်းများ ပြုလုပ်နိုင်ပါသည်။
3. Admin မှ သိမ်းဆည်း (Save) လိုက်သည်နှင့် `dtb_csv` ထဲတွင် update ဖြစ်သွားမည်။
4. အဘယ်ကြောင့် Code ထပ်ပြင်ရန် မလိုသနည်း?
   * အဘယ်ကြောင့်ဆိုသော် FavouriteProductController တွင် `$this->csvExportService->initCsvType(20)` ကို ခေါ်ယူထားသောကြောင့် `CsvExportService` သည် Database ၏ `dtb_csv` ထဲမှ လက်ရှိ `enabled = 1` ဖြစ်နေပြီး `sort_no` အစဉ်အတိုင်း စီထားသော ကော်လံများကို dynamic ဖတ်ယူထုတ်ပေးသောကြောင့် ဖြစ်ပါသည်။

---

## ၄။ ဖိုင်များ အမျိုးအစားခွဲခြားမှုနှင့် အသေးစိတ် စာရင်း (Updated and Overwrite Files Breakdown)

> [!IMPORTANT]
> ### 🚨 EC-CUBE Core Rule (အဓိက လိုက်နာရမည့် ဥပဒေသ)
> * **Never Modify Core Files Directly:** `src/Eccube/` နှင့် `vendor/` အောက်ရှိ ဖိုင်များကို **လုံးဝ (လုံးဝ) တိုက်ရိုက် မပြင်ရပါ**။
> * **Template Overwriting Rule:** Core Twig template တစ်ခုခုကို ပြင်ဆင်လိုပါက `src/Eccube/Resource/template/...` ကို တိုက်ရိုက် မ overwrite ဘဲ `app/template/<template_code>/...` အောက်တွင် တူညီသော ဖိုင်လမ်းကြောင်း (Mirror Path) တည်ဆောက်၍ Override လုပ်ရပါသည်။
> * **New Feature Strategy:** Favourite Product Feature ကဲ့သို့သော လုပ်ဆောင်ချက်အသစ်များအတွက် Core ဖိုင်များကို မထိခိုက်စေဘဲ `app/Customize/` နှင့် `app/template/admin/Product/` တွင် သီးသန့် အသစ်ဖန်တီး (New Files) တည်ဆောက်ခြင်းသည် အကောင်းဆုံး Standard နည်းလမ်း ဖြစ်ပါသည်။

---

### အသေးစိတ် ဖိုင်များ စာရင်းနှင့် တာဝန်များ ဇယား (Detailed File Manifest)

| စဉ် | ဖိုင်လမ်းကြောင်း (File Path) | အမျိုးအစား (Type) | Module / Layer | အဓိက တာဝန်နှင့် ပြုလုပ်ခဲ့သော အပြောင်းအလဲများ |
| :---: | :--- | :---: | :---: | :--- |
| **၁** | `app/Customize/Constant/CustomCsvType.php` | **NEW** (အသစ်) | Constant | CSV Type ID ကို Hardcode မဖြစ်စေရန် `CSV_TYPE_FAVOURITE_PRODUCT = 20` ဟု ဗဟိုပြု သတ်မှတ်ပေးသော ဖိုင်။ |
| **၂** | `app/DoctrineMigrations/Version20260917083632.php` | **NEW** (အသစ်) | Database Migration | `mtb_csv_type` တွင် Favorite CSV Type (ID: 20) ထည့်သွင်းခြင်းနှင့် `dtb_csv` တွင် Default Output Columns (၆) ခု သွင်းပေးသော Migration ဖိုင်။ |
| **၃** | `app/Customize/Form/Type/Admin/SearchFavoriteProductType.php` | **NEW** (အသစ်) | Symfony Form | Product ID, Product Name, Customer Name, Favorite Count Range (`Min` ～ `Max`) Search Filters များအတွက် Form Fields တည်ဆောက်ထားသော ဖိုင်။ |
| **၄** | `app/Customize/Repository/FavouriteProductRepository.php` | **NEW** (အသစ်) | Doctrine Repository | Favorite အများဆုံး ကုန်ပစ္စည်းများကို MySQL 8 `ONLY_FULL_GROUP_BY` safe ဖြစ်အောင် Subquery ဖြင့် စီပေးပြီး Search Filter Queries များ ရေးသားထားသော ဖိုင်။ |
| **၅** | `app/Customize/Controller/Admin/Product/FavouriteProductController.php` | **NEW** (အသစ်) | Symfony Controller | List ပြသခြင်း (Pagination)၊ Search Session Handling နှင့် `CsvExportService` ကို အသုံးပြု၍ CSV Streamed Response ထုတ်ပေးသော Controller ဖိုင်။ |
| **၆** | `app/template/admin/Product/product_favourite.twig` | **NEW** (အသစ်) | Twig Template (UI) | Admin Panel တွင် Favorite Products စာရင်းပြသခြင်း၊ Search Form Card၊ Price Range ပြသခြင်းနှင့် CSV Download/Setting ခလုတ်များ ပါဝင်သော သီးသန့် Admin UI ဖိုင်။ |
| **၇** | `app/config/eccube/packages/eccube_nav.yaml` | **UPDATED** (မွမ်းမံ) | Configuration | Admin Panel ၏ ဘယ်ဘက် Sidebar Menu (商品管理) အောက်တွင် **「お気に入り商品」** (`admin_product_favourite`) navigation link ထည့်သွင်းပေးခြင်း။ |

---

### Template Overwrite နှင့် ပတ်သက်သော စိစစ်သုံးသပ်ချက် (Template Overwrite Analysis)

* **မေးခွန်း - Core ဖိုင် `src/Eccube/Resource/template/admin/Setting/Shop/csv.twig` ကို Overwrite လုပ်ရန် လိုအပ်ပါသလား?**
  * **အဖြေ - လုံးဝ မလိုအပ်ပါ။** အဘယ်ကြောင့်ဆိုသော် EC-CUBE ၏ CSV Setting Controller သည် Database ၏ `mtb_csv_type` ထဲရှိ ဒေတာများကို Dynamic query လုပ်ပြီး Dropdown ထဲသို့ ထည့်သွင်းပြသပေးသောကြောင့် ဖြစ်ပါသည်။ Migration ဖြင့် `mtb_csv_type` ထဲသို့ ID: 20 ထည့်သွင်းလိုက်ရုံဖြင့် Core CSV Setting Template သည် အလိုအလျောက် detect လုပ်ပြီး Dropdown တွင် **「お気に入り商品CSV」** အဖြစ် ဖော်ပြပေးပါသည်။

* **မေးခွန်း - Core ဖိုင် `src/Eccube/Resource/template/admin/Product/index.twig` ကို Overwrite လုပ်ရန် လိုအပ်ပါသလား?**
  * **အဖြေ - မလိုအပ်ပါ။** Product Master (`admin_product`) နှင့် Favourite Product (`admin_product_favourite`) သည် ရည်ရွယ်ချက်နှင့် Query Builder မတူညီသော Feature နှစ်ခု ဖြစ်သည်။ Core Product Master ကို မထိခိုက်စေဘဲ `app/template/admin/Product/product_favourite.twig` အဖြစ် သီးသန့် ခွဲထုတ်ရေးသားခြင်းသည် EC-CUBE ၏ Standard Best Practice ဖြစ်ပါသည်။

---

## ၅။ Terminal Commands များ Run ခြင်း (Setup & Maintenance Commands)

ဖိုင်များ ဖန်တီး/ပြင်ဆင်ပြီးပါက အောက်ပါ Docker commands များကို Terminal တွင် အစဉ်အတိုင်း Run ပေးရပါမည်:

```bash
# ၁။ Database Migration အား Run ၍ mtb_csv_type နှင့် dtb_csv သို့ Data ထည့်သွင်းခြင်း
docker compose exec -T ec-cube php bin/console doctrine:migrations:migrate --no-interaction

# ၂။ Symfony Cache အား ရှင်းလင်းခြင်း (Routing နှင့် Navigation အသစ်များ ပေါ်လာစေရန်)
docker compose exec -T -u www-data ec-cube php bin/console cache:clear --no-warmup

# ၃။ Proxy Classes များ ပြန်လည် Generate လုပ်ခြင်း
docker compose exec -T -u root ec-cube php bin/console eccube:generate:proxies

# ၄။ Cache & Log ဖိုင်များ Permission Error မတက်စေရန် ခွင့်ပြုချက် သတ်မှတ်ပေးခြင်း
docker compose exec -T -u root ec-cube chown -R www-data:www-data var/cache var/log
docker compose exec -T -u root ec-cube chmod -R 777 var/cache var/log
```

---

## ၆။ စနစ်အား စစ်ဆေးအတည်ပြုခြင်း Checklist (Verification Checklist)

အောက်ပါ အချက်များကို Admin Panel တွင် တစ်ဆင့်ချင်း စမ်းသပ်စစ်ဆေးနိုင်ပါသည်:

1. **Sidebar Menu စစ်ဆေးခြင်း:**
   * Admin Panel သို့ ဝင်ရောက်ပြီး ဘယ်ဘက် **商品管理 (Product Management)** အောက်တွင် **「お気に入り商品」** menu ပေါ်နေပြီး နှိပ်ပါက Favourite Page သို့ ရောက်ရှိရပါမည်။
2. **CSV Output Setting သို့ ချိတ်ဆက်မှု စစ်ဆေးခြင်း:**
   * Favourite Page ညာဘက်အပေါ်ရှိ **「CSV設定」** ခလုတ်ကို နှိပ်ပါ။
   * URL သည် `/admin/setting/shop/csv/20` သို့ ရောက်ရှိပြီး CSV Type Dropdown တွင် **「お気に入り商品CSV」** ရွေးချယ်ထားလျက် Columns (၆) ခု ပေါ်နေရပါမည်။
3. **Columns စိတ်ကြိုက် ပြင်ဆင်စမ်းသပ်ခြင်း:**
   * ကော်လံတစ်ခုခု၏ နာမည်ကို ပြင်ကြည့်ပါ သို့မဟုတ် ကော်လံတစ်ခုအား Disable (အမှန်ခြစ်ဖြုတ်) ပြီး Save လုပ်ပါ။
4. **CSV Download စစ်ဆေးခြင်း:**
   * Favourite Page သို့ ပြန်သွားပြီး **「CSVダウンロード」** ခလုတ်ကို နှိပ်ပါ။
   * `.csv` ဖိုင် Download ကျလာမည်ဖြစ်ပြီး Excel ဖြင့် ဖွင့်ကြည့်ပါက စာလုံးမပျက်ဘဲ Setting တွင် ပြင်ဆင်ထားသည့် ကော်လံများအတိုင်း အတိအကျ ထွက်ပေါ်လာရပါမည်။
5. **Search Filter ဖြင့် စစ်ထုတ်၍ CSV Download ဆွဲခြင်း:**
   * Filter Box တွင် Product Name သို့မဟုတ် Favorite Count (Min ～ Max) ဖြင့် ရှာဖွေပြီး Search နှိပ်ပါ။
   * စာရင်းတွင် Filter လုပ်ထားသော ကုန်ပစ္စည်းများသာ ပေါ်နေချိန်တွင် CSV Download ပြုလုပ်ပါက ရှာဖွေထားသော ကုန်ပစ္စည်းများသာ CSV ထဲတွင် စစ်ထုတ် ပါဝင်လာရပါမည်။
