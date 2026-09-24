# EC-CUBE 4.3.1 - Favourite History (အကြိုက်ဆုံး ကုန်ပစ္စည်း မှတ်တမ်း) တည်ဆောက်ခြင်း အဆင့်ဆင့် လမ်းညွှန်
## (Favourite Product History Step-by-Step Implementation Guide)

ဤလက်စွဲစာအုပ်သည် EC-CUBE 4.3.1 တွင် ဝယ်ယူသူ (Customer) များ ကုန်ပစ္စည်းတစ်ခုအား အကြိုက်ဆုံး (Favorite) အဖြစ် ထည့်သွင်းခြင်း (Register) နှင့် ပြန်လည်ဖျက်ထုတ်ခြင်း (Remove) ပြုလုပ်ခဲ့သည့် မှတ်တမ်းများကို Database ဇယားအသစ် (`dtb_customer_favorite_product_history`) ဖြင့် သိမ်းဆည်းခြင်း၊ Admin Panel ရှိ Favorites Management စာရင်းမှတစ်ဆင့် ကုန်ပစ္စည်းတစ်ခုချင်းစီ၏ History ကို ဝင်ရောက်ကြည့်ရှုနိုင်သော Link ထည့်သွင်းခြင်းနှင့် Member ID, Member Name, Action (Register/Remove), Date/Time တို့ကို ရှင်းလင်းလှပစွာ ပြသနိုင်သော History List Screen တည်ဆောက်ခြင်းတို့ကို လုပ်ငန်းအတွေ့အကြုံ (၆) လခန့်ရှိသော Junior Developer များ အလွယ်တကူ လိုက်ပါလုပ်ဆောင်နိုင်စေရန် **မြန်မာဘာသာ** ဖြင့် ပြည့်စုံစွာ ရေးသားထားသော လမ်းညွှန်ဖြစ်ပါသည်။

---

## မာတိကာ (Table of Contents)
1. [လုပ်ဆောင်ချက် အနှစ်ချုပ်နှင့် အဓိက လိုအပ်ချက်များ (Overview & Requirements)](#၁-လုပ်ဆောင်ချက်-အနှစ်ချုပ်နှင့်-အဓိက-လိုအပ်ချက်များ-overview--requirements)
2. [ဖန်တီး/ပြင်ဆင်ရမည့် ဖိုင်များ စာရင်းအကျဉ်း (List of Required Files)](#၂-ဖန်တီးပြင်ဆင်ရမည့်-ဖိုင်များ-စာရင်းအကျဉ်း-list-of-required-files)
3. [ဖိုင်တည်ဆောက်ပုံ ဇယား (Directory & File Tree)](#၃-ဖိုင်တည်ဆောက်ပုံ-ဇယား-directory--file-tree)
4. [Database Table ဖွဲ့စည်းပုံ ဒီဇိုင်း (Table Schema Design)](#၄-database-table-ဖွဲ့စည်းပုံ-ဒီဇိုင်း-table-schema-design)
5. [အဆင့်ဆင့် ရေးသားတည်ဆောက်ပုံ (Step-by-Step Implementation)](#၅-အဆင့်ဆင့်-ရေးသားတည်ဆောက်ပုံ-step-by-step-implementation)
   - [အဆင့် (၁) - Entity အသစ် ဖန်တီးခြင်း (`CustomerFavoriteProductHistory.php`)](#အဆင့်-၁---entity-အသစ်-ဖန်တီးခြင်း-customerfavoriteproducthistoryphp)
     - [အဘယ်ကြောင့် `ACTION_REGISTER` နှင့် `ACTION_REMOVE` ကို `public const` သတ်မှတ်ရသလဲ?](#အဘယ်ကြောင့်-action_register-နှင့်-action_remove-ကို-public-const-class-constants-အဖြစ်-သတ်မှတ်ရသလဲ-why-use-class-constants)
     - [ရေးသားထားသော Entity ရှိ အမှားများ သုံးသပ်ချက်နှင့် const မဖြစ်မနေ လို/မလို](#ခ-ရေးသားထားသော-favouritehistory-entity-ရှိ-အမှားများ-သုံးသပ်ချက်နှင့်-const-မဖြစ်မနေ-လိုမလို-ရှင်းလင်းချက်)
     - [Entity ရေးသားရာတွင် တွေ့ကြုံရသော အမေးများဆုံး မေးခွန်း (၄) ခုနှင့် Core File သက်သေများ](#ဂ-entity-ရေးသားရာတွင်-တွေ့ကြုံရသော-အမေးများဆုံး-မေးခွန်း-၄-ခုနှင့်-core-file-သက်သေများ-entity-deep-dive-qa)
     - [Property ကြေညာပုံ (`private $action` vs `private ?string $action`) နှင့် `create_date` စံနှုန်း နှိုင်းယှဉ်ချက်](#ဃ-property-ကြေညာပုံ-private-action-vs-private-string-action-နှင့်-create_date-ရေးသားပုံ-စံနှုန်း-နှိုင်းယှဉ်ချက်-property-types--date-handling-standard)
   - [အဆင့် (၂) - Repository ရေးသားခြင်း (`CustomerFavoriteProductHistoryRepository.php`)](#အဆင့်-၂---repository-ရေးသားခြင်း-customerfavoriteproducthistoryrepositoryphp)
   - [အဆင့် (၃) - Database Table တည်ဆောက်ခြင်း (Doctrine Schema Commands)](#အဆင့်-၃---database-table-တည်ဆောက်ခြင်း-doctrine-schema-commands)
   - [အဆင့် (၄) - Action များကို အလိုအလျောက် မှတ်တမ်းတင်ခြင်း (`FavoriteEventListener.php`)](#အဆင့်-၄---action-များကို-အလိုအလျောက်-မှတ်တမ်းတင်ခြင်း-favoriteeventlistenerphp)
     - [EventListener နှင့် Repository နည်းပညာဆိုင်ရာ အသေးစိတ် ရှင်းလင်းချက်](#eventlistener-နှင့်-repository-နည်းပညာဆိုင်ရာ-အသေးစိတ်-ရှင်းလင်းချက်-deep-dive-guide-on-eventlistener--repository)
   - [အဆင့် (၅) - Admin History Controller ရေးသားခြင်း (`FavouriteProductHistoryController.php`)](#အဆင့်-၅---admin-history-controller-ရေးသားခြင်း-favouriteproducthistorycontrollerphp)
   - [အဆင့် (၆) - Favorites Management List တွင် History Link ထည့်သွင်းခြင်း (`product_favourite.twig`)](#အဆင့်-၆---favorites-management-list-တွင်-history-link-ထည့်သွင်းခြင်း-product_favouritetwig)
   - [အဆင့် (၇) - History List UI Twig Template ဖန်တီးခြင်း (`product_favourite_history.twig`)](#အဆင့်-၇---history-list-ui-twig-template-ဖန်တီးခြင်း-product_favourite_historytwig)
6. [Terminal Commands & Cache Management](#၆-terminal-commands--cache-management)
7. [စနစ်စမ်းသပ် စစ်ဆေးခြင်း (Testing & Verification Guide)](#၇-စနစ်စမ်းသပ်-စစ်ဆေးခြင်း-testing--verification-guide)

---

## ၁။ လုပ်ဆောင်ချက် အနှစ်ချုပ်နှင့် အဓိက လိုအပ်ချက်များ (Overview & Requirements)

### ပြဿနာနှင့် နောက်ခံအကြောင်းအရာ (Background):
* EC-CUBE မူရင်းတွင် `dtb_customer_favorite_product` ဇယားသာ ရှိပြီး Customer တစ်ဦးသည် ကုန်ပစ္စည်းကို Favorite လုပ်ထားခြင်း ရှိ/မရှိ (Current State) ကိုသာ သိမ်းဆည်းပါသည်။
* အကယ်၍ Customer က Favorite မှ ပြန်ဖျက်လိုက်ပါက မူရင်း table ထဲမှ record မှာ `DELETE` ဖြစ်သွားပြီး မည်သည့် Customer က မည်သည့်အချိန်တွင် Register လုပ်ခဲ့သည် သို့မဟုတ် Remove လုပ်ခဲ့သည်ဟူသော **သမိုင်းမှတ်တမ်း (Action History)** လုံးဝ ကျန်ရှိခြင်း မရှိပါ။

### လိုအပ်သော အဓိက အင်္ဂါရပ်များ (Key Requirements):
1. **New Database Table (ဇယားအသစ်):**
   - Customer က Favorite ပြုလုပ်သည့်အခါ (Register) နှင့် Favorite မှ ပယ်ဖျက်သည့်အခါ (Remove) တိုင်းတွင် အချိန်နှင့်တကွ မှတ်တမ်းတင်မည့် `dtb_customer_favorite_product_history` ဇယားအသစ် ထည့်သွင်းရန်။
2. **Link from Favorites Management (သမိုင်းမှတ်တမ်းကြည့်ရန် Link ချိတ်ဆက်ခြင်း):**
   - လက်ရှိ ရှိပြီးသားဖြစ်သော Favorites Management မျက်နှာပြင် (`/admin/product/favourite`) ရှိ ကုန်ပစ္စည်းတစ်ခုချင်းစီ၏ row တွင် **"履歴 (History)"** ခလုတ်/link ထည့်သွင်းပေးရန်။
3. **History List Screen (မှတ်တမ်းစာရင်း မျက်နှာပြင် ပြသခြင်း):**
   - History link ကို နှိပ်လိုက်ပါက သက်ဆိုင်ရာ ကုန်ပစ္စည်း၏ Favorite လှုပ်ရှားမှု မှတ်တမ်းများကို သီးသန့် screen ဖြင့် ပြသပေးရန်။
4. **Display Columns (ပြသရမည့် ဒေတာ အချက်အလက်များ):**
   - **Member ID (会員ID):** ဝယ်ယူသူ၏ Customer ID
   - **Member Name (会員名):** ဝယ်ယူသူ၏ အမည် (ဥပမာ- `山田 太郎`)
   - **Action (アクション):** လုပ်ဆောင်ချက်အမျိုးအစား - `登録 (Register)` သို့မဟုတ် `解除 (Remove)`
   - **Date/Time (日時):** အဆိုပါ Action ပြုလုပ်ခဲ့သည့် ရက်စွဲနှင့် အချိန်တိကျမှု (ဥပမာ- `2026/09/24 23:15:30`)

---

## ၂။ ဖန်တီး/ပြင်ဆင်ရမည့် ဖိုင်များ စာရင်းအကျဉ်း (List of Required Files)

EC-CUBE Core စည်းမျဉ်း (`Never Modify Core Files Directly`) အတိုင်း `src/` ဖိုင်များကို လုံးဝ မထိခိုက်စေဘဲ အောက်ပါဖိုင်များကို တည်ဆောက်/ပြင်ဆင်ရမည် ဖြစ်ပါသည်:

| No | ဖိုင်အမျိုးအစား | ဖိုင်လမ်းကြောင်း (File Path) | ရည်ရွယ်ချက်နှင့် အခန်းကဏ္ဍ (Role & Purpose) | အသစ်/ပြင်ဆင် |
|:---|:---|:---|:---|:---:|
| 1 | **Entity** | `app/Customize/Entity/CustomerFavoriteProductHistory.php` | `dtb_customer_favorite_product_history` table အတွက် Doctrine ORM Entity Model | အသစ် (New) |
| 2 | **Repository** | `app/Customize/Repository/CustomerFavoriteProductHistoryRepository.php` | History data များကို သိမ်းဆည်းရန်နှင့် QueryBuilder ဆွဲထုတ်ရန် | အသစ် (New) |
| 3 | **EventListener** | `app/Customize/EventListener/FavoriteEventListener.php` | Front-end တွင် Favorite Add / Delete ဖြစ်သည့် Event များကို ဖမ်းယူ၍ History အလိုအလျောက် သွင်းပေးခြင်း | ပြင်ဆင် (Update) |
| 4 | **Admin Controller** | `app/Customize/Controller/Admin/Product/FavouriteProductHistoryController.php` | Admin History စာရင်း Request များကို လက်ခံပြီး Paginator ဖြင့် Twig သို့ ပေးပို့ခြင်း | အသစ် (New) |
| 5 | **Admin Twig** | `app/template/admin/Product/product_favourite.twig` | Favorites စာရင်းတွင် History စာမျက်နှာသို့ သွားရောက်နိုင်မည့် ခလုတ် (Link) ထည့်သွင်းခြင်း | ပြင်ဆင် (Update) |
| 6 | **Admin Twig** | `app/template/admin/Product/product_favourite_history.twig` | Member ID, Name, Action, Date/Time စာရင်းများကို Pagination ဖြင့် ပြသပေးမည့် UI View | အသစ် (New) |

---

## ၃။ ဖိုင်တည်ဆောက်ပုံ ဇယား (Directory & File Tree)

```text
eccube-4.3.1/
├── app/
│   ├── Customize/
│   │   ├── Controller/
│   │   │   └── Admin/
│   │   │       └── Product/
│   │   │           ├── FavouriteProductController.php
│   │   │           └── FavouriteProductHistoryController.php  <-- [အသစ်] Admin History Controller
│   │   ├── Entity/
│   │   │   └── CustomerFavoriteProductHistory.php             <-- [အသစ်] History Database Entity
│   │   ├── EventListener/
│   │   │   └── FavoriteEventListener.php                      <-- [ပြင်ဆင်] Action များ အလိုအလျောက် မှတ်တမ်းတင်ခြင်း
│   │   └── Repository/
│   │       ├── FavouriteProductRepository.php
│   │       └── CustomerFavoriteProductHistoryRepository.php   <-- [အသစ်] History Repository
│   └── template/
│       └── admin/
│           └── Product/
│               ├── product_favourite.twig                     <-- [ပြင်ဆင်] History Link ခလုတ် ထည့်သွင်းခြင်း
│               └── product_favourite_history.twig             <-- [အသစ်] History List View Template
└── My-Task-List-Folder/
    └── Favourite-Product-List/
        └── favourite_product_history_step_by_step_guide.md    <-- [ယခုဖိုင်] မြန်မာဘာသာ လမ်းညွှန်ချက်
```

---

## ၄။ Database Table ဖွဲ့စည်းပုံ ဒီဇိုင်း (Table Schema Design)

Table အမည်: `dtb_customer_favorite_product_history`

| Column အမည် | Data Type | Nullable | ရှင်းလင်းချက် (Description) |
|:---|:---|:---:|:---|
| `id` | INT (Auto Increment) | NO | Primary Key ID |
| `customer_id` | INT | YES | Foreign Key ချိတ်ဆက်ထားသော ဝယ်ယူသူ Member ID (`dtb_customer.id`) |
| `product_id` | INT | NO | Foreign Key ချိတ်ဆက်ထားသော ကုန်ပစ္စည်း ID (`dtb_product.id`) |
| `action` | VARCHAR(20) | NO | လုပ်ဆောင်ချက် အမျိုးအစား (`register` သို့မဟုတ် `remove`) |
| `create_date` | DATETIME | NO | Action ပြုလုပ်ခဲ့သည့် ရက်စွဲနှင့် အချိန် |

> **မှတ်ချက်:** `customer_id` ကို `nullable = true` ထားရှိရခြင်းမှာ နောင်တွင် Customer တစ်ဦးက အကောင့်ဖျက်သိမ်း (Withdrawal/Delete) သွားခဲ့သော်လည်း History Data များ ပျက်မသွားဘဲ သမိုင်းမှတ်တမ်းအဖြစ် ဆက်လက်ကျန်ရှိစေရန် (ON DELETE SET NULL) ဖြစ်ပါသည်။

---

## ၅။ အဆင့်ဆင့် ရေးသားတည်ဆောက်ပုံ (Step-by-Step Implementation)

---

### အဆင့် (၁) - Entity အသစ် ဖန်တီးခြင်း (`CustomerFavoriteProductHistory.php`)

📁 **ဖိုင်လမ်းကြောင်း:** `app/Customize/Entity/CustomerFavoriteProductHistory.php`

#### ဘာကြောင့် ဒီဖိုင်ကို ရေးရသလဲ? (Why?)
EC-CUBE တွင် Database Table တစ်ခု တည်ဆောက်လိုပါက Doctrine ORM ၏ Entity Class အနေဖြင့် ကြေညာပေးရပါသည်။ ဤဖိုင်တွင် Table အမည်၊ Column အမျိုးအစားများနှင့် အခြား Entity များ (`Customer`, `Product`) နှင့် ချိတ်ဆက်ပုံ (ManyToOne Relationship) များကို သတ်မှတ်ပေးရပါသည်။

#### Code အပြည့်အစုံ:
```php
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

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Customer;
use Eccube\Entity\Product;

/**
 * CustomerFavoriteProductHistory
 *
 * အကြိုက်ဆုံးပစ္စည်း ထည့်သွင်းခြင်း/ဖယ်ရှားခြင်း သမိုင်းမှတ်တမ်း Entity
 *
 * @ORM\Table(name="dtb_customer_favorite_product_history")
 * @ORM\InheritanceType("NONE")
 * @ORM\Entity(repositoryClass="Customize\Repository\CustomerFavoriteProductHistoryRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CustomerFavoriteProductHistory extends AbstractEntity
{
    // Action Constants
    public const ACTION_REGISTER = 'register'; // အကြိုက်ဆုံးအဖြစ် ထည့်သွင်းခြင်း (Add Favorite)
    public const ACTION_REMOVE = 'remove';     // အကြိုက်ဆုံးမှ ဖျက်ထုတ်ခြင်း (Remove Favorite)

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Customer|null
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     */
    private $Customer;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Product")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $Product;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=20)
     */
    private $action;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime")
     */
    private $create_date;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->create_date = new \DateTime();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Customer.
     *
     * @param Customer|null $customer
     * @return CustomerFavoriteProductHistory
     */
    public function setCustomer(?Customer $customer = null)
    {
        $this->Customer = $customer;

        return $this;
    }

    /**
     * Get Customer.
     *
     * @return Customer|null
     */
    public function getCustomer()
    {
        return $this->Customer;
    }

    /**
     * Set Product.
     *
     * @param Product $product
     * @return CustomerFavoriteProductHistory
     */
    public function setProduct(Product $product)
    {
        $this->Product = $product;

        return $this;
    }

    /**
     * Get Product.
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * Set action.
     *
     * @param string $action
     * @return CustomerFavoriteProductHistory
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     * @return CustomerFavoriteProductHistory
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }
}
```

#### အဘယ်ကြောင့် `ACTION_REGISTER` နှင့် `ACTION_REMOVE` ကို `public const` (Class Constants) အဖြစ် သတ်မှတ်ရသလဲ? (Why use Class Constants?)

Junior Developer များ မကြာခဏ မေးလေ့ရှိသည့် မေးခွန်းမှာ *"Code ထဲမှာ တိုက်ရိုက် `'register'` သို့မဟုတ် `'remove'` လို့ ရေးလိုက်ရင် ရနေတာပဲ၊ ဘာကြောင့် `public const` ဆိုပြီး သီးသန့် သတ်မှတ်ရတာလဲ?"* ဟူ၍ ဖြစ်ပါသည်။ အဓိက အကြောင်းရင်း (၅) ချက် ရှိပါသည်:

1. **Magic Strings (Hardcoding) ကြောင့် ဖြစ်ပေါ်တတ်သော Typo Bug များကို တားဆီးခြင်း (Preventing Magic Strings & Typos):**
   * အကယ်၍ Constant မသုံးဘဲ EventListener, Controller သို့မဟုတ် Twig Template တွေမှာ `'register'`, `'remove'` ဟု စာသား တိုက်ရိုက် ရိုက်ထည့်ပါက developer တစ်ဦးဦးက စာလုံးပေါင်းမှား ရိုက်မိခြင်း (ဥပမာ- `'regisster'`, `'remov'`) ဖြစ်သွားနိုင်ပါသည်။
   * String (စာသား) မှားရိုက်မိပါက PHP က syntax error အနေဖြင့် မပြသနိုင်ဘဲ စနစ်ထဲတွင် Action မမှန်ကန်သော **Silent Bug (အသံတိတ် အမှား)** ဖြစ်ပေါ်သွားတတ်ပါသည်။
   * `CustomerFavoriteProductHistory::ACTION_REGISTER` ဟု သုံးထားပါက စာလုံးပေါင်းမှားခဲ့လျှင် PHP က **Fatal Error (Undefined class constant)** အဖြစ် ချက်ချင်း အသိပေးသဖြင့် အမှားကို ချက်ချင်း ရှာဖွေပြင်ဆင်နိုင်ပါသည်။

2. **Single Source of Truth (ဗဟိုမှ တစ်နေရာတည်း စီမံခန့်ခွဲနိုင်ခြင်း - Maintainability):**
   * နောင်တစ်ချိန်တွင် Action တန်ဖိုးကို `'register'` အစား အခြားတစ်ခုခု (ဥပမာ- `'add'` သို့မဟုတ် database ID တစ်ခုခု) ပြောင်းလဲသတ်မှတ်ရန် လိုအပ်လာပါက Entity ရှိ Constant ဖိုင် **တစ်နေရာတည်းတွင်သာ** ပြင်ဆင်လိုက်ရုံဖြင့် စနစ်တစ်ခုလုံး (EventListener, Controller, Repository, Twig) တွင် အလိုအလျောက် လိုက်ပါပြောင်းလဲသွားမည် ဖြစ်ပါသည်။ တစ်ဖိုင်ချင်းစီ လိုက်ရှာပြင်စရာ မလိုတော့ပါ။

3. **IDE Code Completion & Developer Productivity (အလိုအလျောက် စာလုံးဖြည့်ပေးခြင်း):**
   * VS Code သို့မဟုတ် PhpStorm တွင် Developer က `CustomerFavoriteProductHistory::` ဟု ရိုက်လိုက်သည်နှင့် ရွေးချယ်နိုင်သော Action အားလုံးကို Dropdown စာရင်းဖြင့် အလိုအလျောက် ပြသပေးပါသည်။ ထို့ကြောင့် Junior Developer များအတွက် မှားယွင်းမှု မရှိဘဲ လျင်မြန်စွာ Code ရေးသားနိုင်စေပါသည်။

4. **Twig Template တွင် `constant(...)` ဖြင့် EC-CUBE Standard အတိုင်း စစ်ဆေးနိုင်ခြင်း:**
   * Twig Template ထဲတွင် Action ကို စစ်ဆေးသည့်အခါ:
     ```twig
     {% if History.action == constant('Customize\\Entity\\CustomerFavoriteProductHistory::ACTION_REGISTER') %}
         <span class="badge bg-success">登録 (Register)</span>
     {% endif %}
     ```
     ဟု ရေးသားနိုင်ပါသည်။ EC-CUBE ၏ မူရင်း Core ကုဒ်များ (ဥပမာ- `OrderStatus::PAID`, `ProductStatus::DISPLAY_SHOW`, `CsvType::CSV_TYPE_PRODUCT`) တွင်လည်း ဤကဲ့သို့ Class Constant များကိုသာ အသုံးပြုသောကြောင့် EC-CUBE စံနှုန်းနှင့် ၁၀၀% ကိုက်ညီပါသည်။

5. **Self-Documenting Code (ဖတ်ရုံဖြင့် အဓိပ္ပာယ် ရှင်းလင်းစွာ သိရှိနိုင်ခြင်း):**
   * အခြား Developer တစ်ဦးက ဤ Entity ဖိုင်ကို ဖွင့်ကြည့်လိုက်ရုံဖြင့် အဆိုပါ History Table ထဲတွင် မည်သည့် Action အမျိုးအစားများ ရှိသလဲ (Allowed Actions) ဆိုသည်ကို ချက်ချင်း အလွယ်တကူ သိရှိနိုင်စေပါသည်။

---

#### (ခ) ရေးသားထားသော `FavouriteHistory` Entity ရှိ အမှားများ သုံးသပ်ချက်နှင့် `const` မဖြစ်မနေ လို/မလို ရှင်းလင်းချက်

အောက်ပါ Code သည် သင်ရေးသားထားသော Entity ဖြစ်ပါသည်:
```php
class FavouriteHistory extends AbstractEntity
{
   private ?int $id = null;
   private ?Customer $Customer = null;
   private ?string $customerName = null;
   private ?string $action = null;
   private ?\DateTimeInterface $createDate = null;
   ...
}
```

ဤ Code ကို စစ်ဆေးကြည့်ရာတွင် အောက်ပါ အရေးကြီးသော **အမှားများနှင့် တိုးတက်ရန် လိုအပ်ချက်များ (Mistakes & Improvements)** ကို တွေ့ရှိရပါသည်:

##### ၁။ အဓိက အကြီးမားဆုံး အမှား (Fatal Architectural Mistake): `Product` ချိတ်ဆက်မှု မပါဝင်ခြင်း
* **ပြဿနာ:** အထက်ပါ Entity ထဲတွင် `Customer`, `customerName`, `action`, `createDate` သာ ပါရှိပြီး **မည်သည့် ကုန်ပစ္စည်း (`Product` သို့မဟုတ် `product_id`) အတွက် Action ဖြစ်သလဲ** ဆိုသည့် ကုန်ပစ္စည်း Foreign Key ချိတ်ဆက်မှု လုံးဝ မပါဝင်ပါ။
* **အဘယ်ကြောင့် မှားယွင်းသနည်း:** 
  * ကျွန်ုပ်တို့၏ Requirement အရ Favorites Management (အကြိုက်ဆုံး ကုန်ပစ္စည်း စီမံခန့်ခွဲမှု) မျက်နှာပြင်တွင် ကုန်ပစ္စည်းတစ်ခုချင်းစီ၏ row တွင် **"履歴 (History)"** ခလုတ် ပါဝင်ရမည် ဖြစ်ပါသည်။
  * Admin က ထိုခလုတ်ကို နှိပ်လိုက်သည့်အခါ Database ထဲမှ `WHERE product_id = :product_id` ဖြင့် ထိုကုန်ပစ္စည်းနှင့် သက်ဆိုင်သော History ကိုသာ ဆွဲထုတ်ပြသရမည် ဖြစ်သည်။
  * အကယ်၍ `Product` relationship မရှိပါက ဤ Favorite History သည် မည်သည့် ကုန်ပစ္စည်း၏ သမိုင်းမှတ်တမ်း ဖြစ်သည်ကို မည်သို့မျှ ရှာဖွေနိုင်မည် မဟုတ်ပါ။
* **ပြင်ဆင်ရန် နည်းလမ်း:**
  Entity ထဲတွင် `Product` relationship ကို မဖြစ်မနေ ထည့်သွင်းပေးရပါမည်:
  ```php
  use Eccube\Entity\Product;

  /**
   * @ORM\ManyToOne(targetEntity="Eccube\Entity\Product")
   * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
   */
  private ?Product $Product = null;
  ```

##### ၂။ Foreign Key ရှိ `onDelete` Attribute မပါဝင်ခြင်း
* `customer_id` အတွက် `@ORM\JoinColumn` တွင် `onDelete="SET NULL"` မထည့်ထားပါ။
* အကယ်၍ နောင်တစ်ချိန်တွင် ဝယ်ယူသူ Customer တစ်ဦးက အကောင့်ဖျက်သိမ်း (Delete/Withdraw) သွားပါက Database တွင် Foreign Key Constraint Error တက်နိုင်ပါသည်။ `onDelete="SET NULL"` ထည့်ထားပါက Customer ဖျက်လိုက်သော်လည်း History Data မပျက်ဘဲ `customer_id` သာ NULL ဖြစ်သွားမည် ဖြစ်ပါသည်။

##### ၃။ `create_date` အတွက် Constructor တွင် Default တန်ဖိုး မသတ်မှတ်ထားခြင်း
* Constructor (`__construct()`) မရေးထားပါက EventListener မှ History အသစ် save သည့်အခါ `$history->setCreateDate(new \DateTime())` ကို မေ့ကျန်ခဲ့လျှင် Database ထဲသို့ NULL ဝင်သွားပြီး SQL Not Null Violation Error တက်စေနိုင်ပါသည်။
* Constructor ထဲတွင် `$this->createDate = new \DateTime();` ထည့်သွင်းထားပါက ဘယ်တော့မှ အမှားအယွင်း မရှိနိုင်တော့ပါ။

##### ၄။ `customer_name` Column ထည့်သွင်းခြင်းနှင့် ပတ်သက်သည့် သဘောတရား
* `customerName` ထည့်သွင်းထားခြင်းသည် **အမှား မဟုတ်ပါ၊ အလွန်ကောင်းမွန်သော အလေ့အကျင့် (Good Snapshot Pattern)** ဖြစ်ပါသည်။
* အကြောင်းရင်းမှာ Customer က အကောင့်ဖျက်သွားသည့်အခါ `Customer` object က null ဖြစ်သွားသော်လည်း `customer_name` ထဲတွင် အဆိုပါအချိန်က ဝယ်ယူသူအမည် snapshot ကျန်ရှိနေမည် ဖြစ်သောကြောင့် ဖြစ်ပါသည်။
* သို့သော် EventListener တွင် save သည့်အခါ အဆိုပါ column ထဲသို့ တန်ဖိုး မဖြစ်မနေ ထည့်ပေးရပါမည်:
  `$history->setCustomerName($Customer->getName01() . ' ' . $Customer->getName02());`

---

#### မေးခွန်း - History list တည်ဆောက်တိုင်း `const` ကို မဖြစ်မနေ ကြေညာသုံးရမလား? (Must we declare const in every history structure?)

> **တိုတိုနှင့် ရှင်းရှင်း အဖြေ:**  
> **PHP Syntax အရ မဖြစ်မနေ မဟုတ်ပါ (Optional)။ သို့သော် EC-CUBE နှင့် Professional Project များတွင် မဖြစ်မနေ သုံးသင့်သော အကောင်းဆုံး စံနှုန်း (Must-Have Best Practice) ဖြစ်ပါသည်။**

##### ကွာခြားချက် နှိုင်းယှဉ်ချက် (Raw String vs Declared Const):

| အချက်အလက် | Constant မသုံးဘဲ Raw String ရေးခြင်း (`'register'`) | `public const` ဖြင့် ကြေညာသုံးခြင်း |
|:---|:---|:---|
| **PHP Syntax Error** | Error မတက်ပါ (အလုပ်လုပ်ပါသည်)။ | Error မတက်ပါ (အလုပ်လုပ်ပါသည်)။ |
| **စာလုံးပေါင်းမှားခြင်း (Typo)** | `'regisster'` ဟု မှားရိုက်မိပါက PHP က error မပြဘဲ **Bug ဖြစ်သွားပါသည်**။ | စာလုံးမှားပါက PHP က **Fatal Error ချက်ချင်းပြပြီး ကာကွယ်ပေးသည်**။ |
| **IDE Auto-complete** | ကိုယ်တိုင် လက်ဖြင့် စာလုံးအပြည့် ရိုက်ရပါမည်။ | `Entity::` ရိုက်ရုံဖြင့် စာရင်းအလိုအလျောက် ပေါ်လာပါသည်။ |
| **တန်ဖိုး ပြောင်းလဲပြင်ဆင်ခြင်း** | ဖိုင်ပေါင်းများစွာ (Listener, Twig, Controller) တွင် လိုက်ရှာပြင်ရပါမည်။ | Entity ဖိုင် **တစ်နေရာတည်းတွင် ပြင်ရုံဖြင့်** အားလုံး ပြောင်းသွားပါသည်။ |
| **Twig စစ်ဆေးခြင်း** | `{% if h.action == 'register' %}` (Hardcoded) | `{% if h.action == constant('...::ACTION_REGISTER') %}` (EC-CUBE Standard) |

**နိဂုံးချုပ် အကြံပြုချက်:**  
History တွင် `action` (ဥပမာ- 登録/解除၊ အောင်မြင်/ကျရှုံး) စသည့် ရွေးချယ်စရာ သတ်မှတ်ချက်များ ရှိနေပါက `public const ACTION_...` ဖြင့် ကြေညာရေးသားခြင်းသည် Junior မှ Senior အဆင့်သို့ တက်လှမ်းမည့် Developer တိုင်း လိုက်နာရမည့် **Enterprise Standard Architecture** ဖြစ်ပါသည်။

---

#### (ဂ) Entity ရေးသားရာတွင် တွေ့ကြုံရသော အမေးများဆုံး မေးခွန်း (၄) ခုနှင့် Core File သက်သေများ (Entity Deep-Dive Q&A)

Junior Developer များ Entity တည်ဆောက်ရာတွင် မကြာခဏ ဇဝေဇဝါ ဖြစ်တတ်သော မေးခွန်း (၄) ခုကို EC-CUBE မူရင်း Core ကုဒ်များနှင့် နှိုင်းယှဉ်၍ အသေးစိတ် ရှင်းပြပေးထားပါသည်:

---

##### မေးခွန်း (၁) - `options={"unsigned":true}` ကို မဖြစ်မနေ ထည့်သွင်း ကြေညာရန် လိုအပ်ပါသလား?

> **အဖြေ:**  
> **PHP/Doctrine Syntax အရ မဖြစ်မနေ မဟုတ်သော်လည်း EC-CUBE Core Standard အရ မဖြစ်မနေ ထည့်သွင်းသင့်ပါသည်။**

1. **`unsigned` ၏ အဓိပ္ပာယ်:**
   * MySQL Database တွင် `INT` တန်ဖိုးအား အနှုတ်လက္ခဏာ (-1, -2, ...) လက်မခံဘဲ အပေါင်းကိန်း `0` မှ စတင်ရေတွက်စေရန် သတ်မှတ်ခြင်း ဖြစ်ပါသည်။
2. **အဘယ်ကြောင့် ထည့်သွင်းရသနည်း (Why):**
   * **Storage ပမာဏ ၂ ဆ တိုးတက်စေခြင်း:** သာမန် `signed int` သည် အများဆုံး `2,147,483,647` (၂ ဘီလီယံကျော်) သာ ဆံ့သော်လည်း `unsigned int` သုံးလိုက်ပါက အများဆုံး `4,294,967,295` (၄ ဘီလီယံကျော်) အထိ ၂ ဆ ပိုမိုဆံ့ပါသည်။ Database Primary Key ID များသည် ဘယ်တော့မှ အနှုတ်ကိန်း မဖြစ်နိုင်သောကြောင့် `unsigned` သုံးခြင်းသည် အကောင်းဆုံး ဖြစ်ပါသည်။
   * **Foreign Key Data Type ကိုက်ညီစေခြင်း:** EC-CUBE Core ၏ Table အားလုံး (Customer, Product) ရှိ Primary Key များသည် `unsigned` ဖြင့် တည်ဆောက်ထားသောကြောင့် Foreign Key ချိတ်ဆက်ရာတွင် Type တူညီစေရန် ဖြစ်ပါသည်။
3. **EC-CUBE Core File သက်သေ:**
   * ဖိုင်လမ်းကြောင်း: `src/Eccube/Entity/CustomerFavoriteProduct.php` (Line 33)
   ```php
   /**
    * @ORM\Column(name="id", type="integer", options={"unsigned":true})
    */
   private $id;
   ```

---

##### မေးခွန်း (၂) - Customer နှင့် Product တွင် အဘယ်ကြောင့် `ManyToOne` ကို သုံးရသလဲ? `onDelete="CASCADE"` နှင့် `onDelete="SET NULL"` ကွာခြားချက်က အဘယ်နည်း?

> **အဖြေ:**  
> **ဒေတာ ဆက်စပ်မှု သဘောတရား (Cardinality) နှင့် မိခင် Record ပျက်စီးသွားသည့်အခါ သမိုင်းမှတ်တမ်း (History) မည်သို့ ဖြစ်သွားမည်ကို ထိန်းချုပ်ရန် ဖြစ်ပါသည်။**

1. **အဘယ်ကြောင့် `ManyToOne` ဖြစ်ရသနည်း:**
   * **Customer ဘက်မှ ကြည့်လျှင်:** ဝယ်ယူသူ **တစ်ဦး (One Customer)** သည် ကုန်ပစ္စည်းများကို အကြိမ်ပေါင်း **များစွာ (Many Times)** Favorite ပြုလုပ်နိုင်သဖြင့် History Records ပေါင်းများစွာ ဖြစ်ပေါ်နိုင်ပါသည်။ ထို့ကြောင့် History ဘက်မှ ပြန်ကြည့်လျှင် `ManyToOne` ဖြစ်ပါသည်။
   * **Product ဘက်မှ ကြည့်လျှင်:** ကုန်ပစ္စည်း **တစ်ခု (One Product)** ကို Customer ပေါင်းများစွာက အကြိမ်ပေါင်း **များစွာ (Many Times)** Favorite ပြုလုပ်နိုင်သဖြင့် History ဘက်မှ `ManyToOne` ဖြစ်ပါသည်။

2. **`onDelete="CASCADE"` နှင့် `onDelete="SET NULL"` ကွာခြားချက် ရှင်းလင်းချက်:**

| သတ်မှတ်ချက် | အသုံးပြုသည့် နေရာ | အလုပ်လုပ်ပုံ (Behavior) | ဘာကြောင့် ဒီလို သုံးရသလဲ (Rationale) |
|:---|:---|:---|:---|
| **`onDelete="CASCADE"`** | **Product** (`product_id`) | အကယ်၍ Admin က Product တစ်ခုကို Database မှ အပြီးတိုင် ဖျက်လိုက်ပါက (Product Delete) ၎င်းနှင့် သက်ဆိုင်သော History row အားလုံးကို **တစ်ပါတည်း အလိုအလျောက် ဖျက်ပေးသည်**။ | ကုန်ပစ္စည်း မရှိတော့လျှင် ၎င်း၏ Favorite သမိုင်းမှတ်တမ်းကို ဆက်သိမ်းထားစရာ အကြောင်းမရှိတော့သောကြောင့် ဖြစ်သည်။ |
| **`onDelete="SET NULL"`** | **Customer** (`customer_id`) | အကယ်၍ ဝယ်ယူသူ (Customer) တစ်ဦးက အကောင့်ဖျက်သိမ်း (Withdrawal/Delete) သွားပါက History record တစ်ခုလုံးကို မဖျက်ဘဲ **`customer_id` ကိုသာ `NULL` ပြောင်းလိုက်သည်**။ | ဆိုင်ရှင်အတွက် ထိုပစ္စည်းကို လူကြိုက်များခဲ့သည့် သမိုင်းအချက်အလက် (Audit/Analytics) များ မပျက်စီးစေရန် ထိန်းသိမ်းထားလိုသောကြောင့် ဖြစ်သည်။ |

* **EC-CUBE Core File သက်သေ:**
  * EC-CUBE မူရင်း Login History ဇယားဖြစ်သော `src/Eccube/Entity/LoginHistory.php` (Line 79) တွင် User အကောင့်ဖျက်သွားသော်လည်း Login History မပျက်စေရန် အောက်ပါအတိုင်း ရေးထားပါသည်:
    ```php
    /**
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $LoginUser;
    ```
  * Cart Item ဇယားဖြစ်သော `src/Eccube/Entity/CartItem.php` (Line 70) တွင် Cart ပျက်လျှင် Item အလိုအလျောက် ပျက်စေရန်:
    ```php
    /**
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id", onDelete="CASCADE")
     */
    ```

---

##### မေးခွန်း (၃) - `@var string` ဟု ရေးသားခြင်းသည် EC-CUBE Standard ဟုတ်ပါသလား? Core File တွင် မည်သည့်နေရာ၌ ရေးထားပါသလဲ?

> **အဖြေ:**  
> **ဟုတ်ကဲ့၊ ၁၀၀% EC-CUBE Standard ဖြစ်ပါသည်။ EC-CUBE မူရင်း Core Entity ဖိုင်တိုင်းတွင် ခြွင်းချက်မရှိ အတိအကျ ဤပုံစံအတိုင်း ရေးသားထားပါသည်။**

1. **Core File တည်နေရာများ:**
   * `src/Eccube/Entity/CustomerFavoriteProduct.php`:
     - Line 31: `* @var int`
     - Line 40: `* @var \DateTime`
     - Line 54: `* @var \Eccube\Entity\Customer`
     - Line 64: `* @var \Eccube\Entity\Product`
   * `src/Eccube/Entity/Product.php` နှင့် `src/Eccube/Entity/Customer.php` ဖိုင်ပေါင်းများစွာတွင်လည်း ကိန်းရှင်တိုင်း၏ အပေါ်တွင် `@var string`, `@var int`, `@var \DateTime` စသည်ဖြင့် ရေးသားထားပါသည်။
2. **အဘယ်ကြောင့် ထည့်သွင်းရသနည်း (Why):**
   * **PHPDoc စံနှုန်း (PSR-5):** PHP သည် Dynamically Typed ဘာသာစကား ဖြစ်သဖြင့် Variable တစ်ခုချင်းစီ မည်သည့် Data Type ဖြစ်သည်ကို IDE (PhpStorm, VSCode) က သိရှိစေရန် ဖြစ်သည်။
   * **Doctrine ORM Proxy Generation:** Doctrine က Proxy Class များနှင့် Metadata Cache များ တည်ဆောက်ရာတွင် PHPDoc မှ Type Information များကို အသုံးပြုပါသည်။

---

##### မေးခွန်း (၄) - Entity တိုင်းတွင် `@param Customer|null $customer` နှင့် `@return CustomerFavoriteProductHistory` တို့ကို မဖြစ်မနေ ထည့်သွင်းရန် လိုအပ်ပါသလား?

> **အဖြေ:**  
> **PHP Runtime အရ မထည့်လည်း အလုပ်လုပ်နိုင်သော်လည်း၊ Enterprise Architecture နှင့် EC-CUBE Best Practice အရ မဖြစ်မနေ ထည့်သွင်းပေးရပါမည်။**

1. **ဘာကြောင့် မဖြစ်မနေ ရေးသင့်သလဲ:**
   * **IDE Auto-Completion & Code Navigation:**  
     Developer တစ်ဦးက Controller သို့မဟုတ် Service ထဲတွင်:
     ```php
     $Customer = $History->getCustomer();
     ```
     ဟု ခေါ်ယူလိုက်ပါက `@return Customer|null` ရေးထားမှသာ IDE က `$Customer->getId()`, `$Customer->getName01()` စသည့် method များကို Dropdown စာရင်းဖြင့် အလိုအလျောက် ဖော်ပြပေးနိုင်မည် ဖြစ်ပါသည်။ မရေးထားပါက IDE သည် မည်သည့် Object ပြန်လာသည်ကို မသိနိုင်တော့ပါ။
   * **Fluent Interface (Method Chaining) အထောက်အကူပြုခြင်း:**  
     Setter method များတွင် `return $this;` ပြန်ပေးပြီး `@return CustomerFavoriteProductHistory` ဟု ထည့်ထားခြင်းဖြင့် အောက်ပါအတိုင်း တစ်ဆက်တည်း ခေါ်ယူနိုင်ပါသည်:
     ```php
     $history->setProduct($Product)
             ->setCustomer($Customer)
             ->setAction('register');
     ```
   * **Static Analysis Tools (PHPStan, Psalm, Linter):**  
     EC-CUBE Core တွင် အသုံးပြုထားသော PHP CS Fixer နှင့် PHPStan စစ်ဆေးမှုများတွင် Method DocBlock (`@param`, `@return`) မပါဝင်ပါက Code Quality Warning ထွက်ပေါ်စေတတ်ပါသည်။ ထို့ကြောင့် Core ဖိုင်များ အားလုံးတွင် မပျက်မကွက် ရေးသားထားခြင်း ဖြစ်ပါသည်။

---

#### (ဃ) Property ကြေညာပုံ (`private $action` vs `private ?string $action`) နှင့် `create_date` ရေးသားပုံ စံနှုန်း နှိုင်းယှဉ်ချက် (Property Types & Date Handling Standard)

ဤမေးခွန်းသည် EC-CUBE ၏ အတွင်းပိုင်း Core Architecture ကို နားလည်စေရန် အလွန် အရေးကြီးသော မေးခွန်း ဖြစ်ပါသည်။ မေးခွန်းတစ်ခုချင်းစီကို Core File သက်သေများနှင့်တကွ ရှင်းပြပေးထားပါသည်:

---

##### မေးခွန်း (၁) - `private ?string $action = null;` ဟု မရေးဘဲ `private $action;` ဟု ရေးသားခြင်းသည် EC-CUBE Core Standard ဟုတ်ပါသလား? Core File တွင် မည်သို့ ရေးထားပါသလဲ? (Proof)

> **အဖြေ:**  
> **ဟုတ်ကဲ့၊ ၁၀၀% EC-CUBE Core Standard အစစ်အမှန် ဖြစ်ပါသည်။ EC-CUBE မူရင်း Core ဖိုင်ပေါင်း (၈၀) ကျော်တွင် Typed Property (`?string`) ကို လုံးဝ မသုံးဘဲ `@var string` နှင့် `private $property;` ဖြင့်သာ ခြွင်းချက်မရှိ ရေးသားထားပါသည်။**

1. **EC-CUBE Core Files များမှ ခိုင်လုံသော သက်သေများ (Concrete Core File Proofs):**
   * **သမိုင်းမှတ်တမ်း Core ဖိုင် အစစ်အမှန်:** [src/Eccube/Entity/LoginHistory.php](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/src/Eccube/Entity/LoginHistory.php) (Line 34-51)
     ```php
     /**
      * @var int
      *
      * @ORM\Column(name="id", type="integer", options={"unsigned":true})
      * @ORM\Id
      * @ORM\GeneratedValue(strategy="IDENTITY")
      */
     private $id;

     /**
      * @var string
      * @ORM\Column(type="text",nullable=true)
      */
     private $user_name;

     /**
      * @var string
      * @ORM\Column(type="text",nullable=true)
      */
     private $client_ip;
     ```

     **`LoginHistory.php` (Line 34-51) ပါ ကုဒ်တစ်ကြောင်းချင်းစီ၏ အသေးစိတ် ရှင်းလင်းချက်:**
     - **`@ORM\Column(name="id", type="integer", options={"unsigned":true})`**: Primary Key ID ကို အနှုတ်ကိန်း လက်မခံဘဲ အပေါင်းကိန်း သီးသန့် `0` မှ စတင်ရေတွက်စေရန် `unsigned` သတ်မှတ်ခြင်း ဖြစ်သည်။
     - **`@ORM\Id`**: ဤ Column သည် Database Table ၏ ပင်တိုင် Primary Key ဖြစ်ကြောင်း Doctrine အား ညွှန်ကြားခြင်း ဖြစ်သည်။
     - **`@ORM\GeneratedValue(strategy="IDENTITY")`**: MySQL တွင် Record အသစ် တိုးလာတိုင်း ID နံပါတ်ကို `1, 2, 3, ...` ဟု အလိုအလျောက် ၁ တိုးပေးသည့် **Auto Increment** စနစ် ဖြစ်သည်။
     - **`private $id;`**: Primary Key နံပါတ်ကို သိုလှောင်မည့် Untyped Property ဖြစ်သည်။
     - **`private $user_name;`**:
       - `@var string`: Property သည် String အမျိုးအစား ဖြစ်ကြောင်း IDE နှင့် Linter များ သိရှိစေရန် PHPDoc ထည့်ထားခြင်း ဖြစ်သည်။
       - `@ORM\Column(type="text", nullable=true)`: `name` မထည့်ထားပါက Doctrine သည် Property Name ဖြစ်သော `user_name` ကို Column Name အဖြစ် အလိုအလျောက် သုံးပေးသည်။ Email အရှည်ကြီး ဖြစ်နိုင်သဖြင့် `type="text"` သုံးထားပြီး၊ မှတ်ပုံမတင်ရသေးသော Anonymous ဧည့်သည်များ Login ကြိုးစားမှုတွင် နာမည်မရှိနိုင်သဖြင့် `nullable=true` ထားခြင်း ဖြစ်သည်။
     - **`private $client_ip;`**: Login ကြိုးစားခဲ့သော စက်၏ IP Address ကို သိမ်းဆည်းရန် ဖြစ်ပြီး၊ IPv4/IPv6 နှင့် Proxy Headers များ ပါဝင်နိုင်သဖြင့် `type="text", nullable=true` ထားရှိခြင်း ဖြစ်သည်။
   * **Favorite မူရင်း Core ဖိုင်:** [src/Eccube/Entity/CustomerFavoriteProduct.php](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/src/Eccube/Entity/CustomerFavoriteProduct.php) (Line 31-37)
     ```php
     /**
      * @var int
      *
      * @ORM\Column(name="id", type="integer", options={"unsigned":true})
      * @ORM\Id
      * @ORM\GeneratedValue(strategy="IDENTITY")
      */
     private $id;
     ```
   * `src/Eccube/Entity/Product.php`, `Customer.php`, `Order.php` စသည့် မည်သည့် Core ဖိုင်ကိုမဆို ဖွင့်ကြည့်ပါက `private ?string $name = null;` ဟု ရေးသားထားခြင်း လုံးဝ မရှိပါ။

2. **အဘယ်ကြောင့် EC-CUBE Core က `private ?string $action = null;` ကို မသုံးဘဲ `private $action;` ကို သုံးသနည်း?**
   * **Doctrine ORM Proxy Generation ပြဿနာကို ကာကွယ်ရန်:**  
     Doctrine ORM သည် Database မှ data များကို Lazy Loading ဖြင့် ဆွဲတင်သည့်အခါ Proxy Class များကို Auto-generate ပြုလုပ်ပါသည်။ PHP Typed Property (`?string`) ကို သုံးထားပါက Doctrine ၏ Reflection နှင့် Lazy Loading က access လုပ်သည့်အခါ မတော်တဆ uninitialized ဖြစ်နေပါက PHP Fatal Error (`Typed property must not be accessed before initialization`) ဖြစ်ပွားတတ်ပါသည်။ Untyped property (`private $action;`) သည် Doctrine ORM Proxy များနှင့် ၁၀၀% အဆင်ပြေပြေ သဟဇာတ ဖြစ်ပါသည်။
   * **PHP Version Compatibility:** EC-CUBE ၏ Architecture သည် PHP CS Fixer နှင့် Doctrine 2.x Standard ကို အခြေခံထားသောကြောင့် အစဉ်အလာအရ ဤနည်းလမ်းကို Standard အဖြစ် အသုံးပြုထားခြင်း ဖြစ်ပါသည်။
   * *(မှတ်ချက်- PHP 8.1+ အရ မိမိ Customize Code တွင် `private ?string $action = null;` ဟု ရေးလျှင်လည်း run နိုင်သော်လည်း EC-CUBE Core စံနှုန်းအတိုင်း ရေးလိုပါက `private $action;` နှင့် `@var string` သည် အမှန်ကန်ဆုံး ဖြစ်ပါသည်)*

3. **`@ORM\Column(name="action", type="string", length=20)` နှင့် `private ?string $action = null;` တစ်လုံးချင်းစီ၏ အသေးစိတ် ရှင်းလင်းချက်:**

```php
/**
 * @ORM\Column(name="action", type="string", length=20)
 */
private ?string $action = null;
```

ဤကုဒ် (၄) ကြောင်းတွင် ပါဝင်သော အစိတ်အပိုင်း တစ်ခုချင်းစီ၏ နည်းပညာဆိုင်ရာ အဓိပ္ပာယ်နှင့် သဘောတရားများမှာ အောက်ပါအတိုင်း ဖြစ်ပါသည်:

| အစိတ်အပိုင်း (Part) | အမျိုးအစား (Category) | အသေးစိတ် နည်းပညာ ရှင်းလင်းချက် (Technical Explanation) |
|:---|:---|:---|
| `/** ... */` | **DocBlock Annotation** | Doctrine ORM သည် PHP Class ထဲမှ မည်သည့် Property သည် Database Column ဖြစ်သည်ကို ဖတ်ရှုနိုင်ရန် ရေးသားရသော Annotation မှတ်ချက် ဖြစ်ပါသည်။ |
| `@ORM\Column` | **Doctrine Mapping** | ဤ Property သည် Database Table ထဲတွင် တကယ့် Column အဖြစ် တည်ရှိရမည်ဟု Doctrine အား ညွှန်ကြားခြင်း ဖြစ်ပါသည် (Relationship မဟုတ်သော သာမန် data column ဖြစ်သည်)။ |
| `name="action"` | **DB Column Name** | MySQL Database Table ထဲတွင် အမှန်တကယ် ပေါ်လာမည့် Column အမည် ဖြစ်ပါသည် (`dtb_customer_favorite_product_history.action`)။ |
| `type="string"` | **Doctrine Type** | Doctrine ၏ Data Type ဖြစ်ပြီး MySQL တွင် **`VARCHAR`** ဟူသော Data Type အဖြစ် အလိုအလျောက် ပြောင်းလဲတည်ဆောက်ပေးပါသည်။ |
| `length=20` | **Column Length** | MySQL ရှိ `VARCHAR(20)` ၏ အရှည်ဆုံး စာလုံးရေ (Max Length) ကို သတ်မှတ်ခြင်း ဖြစ်ပါသည်။<br>• ကျွန်ုပ်တို့၏ Action များသည် `'register'` (၈ လုံး) နှင့် `'remove'` (၆ လုံး) သာ ရှိသဖြင့် အရှည် ၂၀ သည် အလွန်လုံလောက်ပါသည်။<br>• အကယ်၍ `length` မထည့်ပါက Doctrine သည် Default အနေဖြင့် `VARCHAR(255)` ဟု အကျယ်ကြီး ဆောက်ပေးသဖြင့် Database Storage နှင့် Index Memory နေရာပိုယူစေပါသည်။ `length=20` သတ်မှတ်ခြင်းသည် Database Performance အတွက် အလွန်ကောင်းမွန်သော အလေ့အကျင့် ဖြစ်ပါသည်။ |
| `private` | **Access Modifier (OOP)** | Class ၏ ပြင်ပမှနေ၍ `$history->action = '...'` ဟု တိုက်ရိုက် ပြင်ဆင်ခွင့် မပြုဘဲ Getter/Setter (`getAction()`, `setAction()`) မှတစ်ဆင့်သာ ထိန်းချုပ်ဝင်ရောက်စေသည့် OOP Encapsulation သဘောတရား ဖြစ်ပါသည်။ |
| `?string` | **Nullable Type Hint** | PHP 7.1+ တွင် ပါဝင်လာသော Type Hinting ဖြစ်ပါသည်။<br>• **`string`**: ဤကိန်းရှင်ထဲသို့ စာသား (string) သာ ထည့်ခွင့်ရှိသည်။<br>• **`?` (Question Mark)**: ဤကိန်းရှင်သည် string အပြင် **`null` တန်ဖိုးလည်း ဖြစ်ခွင့်ရှိသည်** (Nullable) ဟု အဓိပ္ပာယ်ရပါသည်။ အကယ်၍ `?` မပါဘဲ `private string $action;` ဟု ရေးပါက `null` ထည့်၍ မရတော့ပါ။ |
| `$action` | **Property Name** | PHP Class အတွင်း အသုံးပြုမည့် Variable အမည် ဖြစ်ပါသည်။ |
| `= null;` | **Default Value Initialization** | Class Object အသစ် စတင်ဆောက်လိုက်သည့်အခါ ကနဦးတန်ဖိုးကို `null` အဖြစ် ကြိုတင်သတ်မှတ်ထားခြင်း ဖြစ်ပါသည်။<br>• **အရေးကြီးသော အချက်:** PHP 7.4+ တွင် Type Hint ပါသော Property (ဥပမာ- `?string`) အား `= null;` မထည့်ဘဲ ထားပါက ၎င်းသည် "Uninitialized State" ဖြစ်နေတတ်ပြီး တန်ဖိုးမထည့်ရသေးမီ လှမ်းခေါ်မိပါက PHP Fatal Error (`Typed property must not be accessed before initialization`) ဖြစ်ပွားတတ်ပါသည်။ `= null;` ထည့်ထားခြင်းဖြင့် ထို Fatal Error ကို ၁၀၀% ကြိုတင်ကာကွယ်ပေးပါသည်။ |

4. **`LoginHistory.php` တွင် `type="text"` သုံးထားသော်လည်း ကျွန်ုပ်တို့၏ `action` တွင် အဘယ်ကြောင့် `type="string", length=20` သုံးရသလဲ? ဘာကြောင့် မတူရသလဲ? (Why are we not the same as LoginHistory?):**

Junior Developer များ နှိုင်းယှဉ်ကြည့်မိသည့်အခါ *"LoginHistory.php မှာတုန်းက `@ORM\Column(type="text",nullable=true)` လို့ ရေးထားတယ်၊ ကျွန်ုပ်တို့ကျတော့ ဘာကြောင့် `@ORM\Column(name="action", type="string", length=20)` လို့ ရေးရတာလဲ? ဘာကြောင့် မတူရသလဲ?"* ဟု မေးတတ်ကြပါသည်။

အဓိက ကွာခြားရသည့် နည်းပညာ အကြောင်းရင်း (၃) ချက် ရှိပါသည်:

| နှိုင်းယှဉ်ချက် | `LoginHistory.php` (`user_name`, `client_ip`) | ကျွန်ုပ်တို့၏ `FavouriteHistory.php` (`action`) |
|:---|:---|:---|
| **Data Type** | `type="text"` (`TEXT` in MySQL) | `type="string", length=20` (`VARCHAR(20)` in MySQL) |
| **သိမ်းဆည်းရမည့် ဒေတာ သဘောသဘာဝ** | • User Name သည် Email အရှည်ကြီး ဖြစ်နိုင်ခြင်း<br>• Client IP သည် IPv6 လိပ်စာ အရှည်ကြီး သို့မဟုတ် Proxy Header များ ပါဝင်နိုင်ခြင်းကြောင့် အလျားအကန့်အသတ် မထားလို၍ `TEXT` ကို သုံးခြင်း ဖြစ်သည်။ | • Action သည် `'register'` (၈ လုံး) နှင့် `'remove'` (၆ လုံး) ဟူသော **Fixed Keywords (သတ်မှတ်ချက်တို)** သာ ဖြစ်သည်။ စာလုံး ၂၀ ထက် မည်သည့်အခါမျှ ပိုမိုမလာနိုင်ပါ။ |
| **Database Performance & Memory** | `TEXT` သည် MySQL တွင် Off-page LOB Storage ဖြင့် သိမ်းဆည်းရပြီး `ORDER BY` / `GROUP BY` ပြုလုပ်သည့်အခါ Disk Temporary Table သုံးရသဖြင့် Memory ပိုစားသည်။ | `VARCHAR(20)` သည် MySQL Inline Row Memory ပေါ်တွင် တိုက်ရိုက် သိမ်းဆည်းသဖြင့် Memory အလွန်ပေါ့ပါးပြီး Query Execution Speed အလွန်မြန်ဆန်ပါသည်။ |
| **Indexing & Search Speed** | `TEXT` column များကို MySQL တွင် တိုက်ရိုက် Index ပေး၍ မရပါ (Prefix length မဖြစ်မနေ ထည့်ပေးရသည်)။ | `VARCHAR(20)` သည် B-Tree Index ကို လွယ်ကူလျင်မြန်စွာ တည်ဆောက်နိုင်သဖြင့် Action အလိုက် ရှာဖွေရာတွင် စွမ်းဆောင်ရည် အလွန်မြင့်မားပါသည်။ |
| **Nullable သတ်မှတ်ချက်** | `nullable=true` (Login မအောင်မြင်သော Anonymous User များအတွက် User Name သည် null ဖြစ်နိုင်သည်)။ | `nullable=false` (Favorite History တွင် Action မရှိဘဲ Record ဖြစ်ပေါ်၍ မရပါ၊ Action သည် မဖြစ်မနေ ပါရမည်)။ |

> **အနှစ်ချုပ်:** Core ဖိုင်ဖြစ်သော `LoginHistory.php` တွင် Data အရှည် မသိနိုင်သောကြောင့် `type="text"` ကို သုံးခဲ့ခြင်း ဖြစ်သော်လည်း၊ ကျွန်ုပ်တို့၏ `action` သည် စာလုံးတိုတိုသာ ဖြစ်သောကြောင့် `VARCHAR(20)` (`type="string", length=20`) ကို သုံးခြင်းသည် **Database Design Best Practice အတိုင်း အမှန်ကန်ဆုံးနှင့် အထိရောက်ဆုံး ဖြစ်ပါသည်**။

---

##### ၅။ `create_date` Property ရေးသားပုံ အသေးစိတ် ခွဲခြမ်းစိတ်ဖြာချက်:

```php
/**
 * @ORM\Column(name="create_date", type="datetime")
 */
private ?\DateTimeInterface $createDate = null;
```

ဤကုဒ်တွင် ပါဝင်သော အစိတ်အပိုင်း တစ်ခုချင်းစီ၏ နည်းပညာ သဘောတရားမှာ အောက်ပါအတိုင်း ဖြစ်ပါသည်:

| အစိတ်အပိုင်း | အဓိပ္ပာယ်နှင့် နည်းပညာ သဘောတရား |
|:---|:---|
| `name="create_date"` | MySQL Database Table ထဲတွင် အမှန်တကယ် ပေါ်လာမည့် Column အမည် ဖြစ်ပါသည်။ Database တွင် **snake_case** (`create_date`) စံနှုန်းအတိုင်း သိမ်းဆည်းပါသည်။ |
| `type="datetime"` | Doctrine ORM Mapping Type ဖြစ်ပြီး MySQL Database ထဲတွင် **`DATETIME`** column type အဖြစ် အလိုအလျောက် တည်ဆောက်ပေးပါသည်။ |
| `private` | OOP Encapsulation အရ ပြင်ပမှ တိုက်ရိုက် မပြင်နိုင်စေရန် ကာကွယ်ခြင်း ဖြစ်ပါသည်။ |
| `?\DateTimeInterface` | • **`\DateTimeInterface`**: PHP တွင် `\DateTime` နှင့် `\DateTimeImmutable` Class နှစ်ခုစလုံး Implement လုပ်ထားသော Parent Interface ဖြစ်သဖြင့် Type ပိုမိုပြည့်စုံပြီး Flexible ဖြစ်စေပါသည်။<br>• **`?` (Nullable)**: Class Object စတင်ဆောက်ချိန်တွင် `null` ဖြစ်ခွင့်ပြုထားခြင်း ဖြစ်ပါသည်။ |
| `$createDate` | PHP Code အတွင်း၌ အသုံးပြုမည့် Property အမည် ဖြစ်ပါသည်။ PHP Standard အရ **camelCase** (`$createDate`) ဖြင့် ရေးသားထားပြီး၊ Database ကော်လံ `create_date` နှင့် အလိုအလျောက် ချိတ်ဆက်ပေးပါသည်။ |
| `= null;` | PHP 7.4+ တွင် Type Hint ပါသော Property ကို အစပျိုး (Initialize) လုပ်ပေးထားခြင်း ဖြစ်ပြီး `Uninitialized Fatal Error` ဖြစ်ပွားခြင်းမှ ကာကွယ်ပေးပါသည်။ |

---

##### ၆။ `private $Product;` နှင့် Relationship ရေးသားပုံ အသေးစိတ် ခွဲခြမ်းစိတ်ဖြာချက်:

```php
/**
 * @var Product
 *
 * @ORM\ManyToOne(targetEntity="Eccube\Entity\Product")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
 * })
 */
private $Product;
```

Junior Developer များ သိရှိရမည့် အလွန်အရေးကြီးသော OOP & Doctrine သဘောတရားများ ဖြစ်ပါသည်:

1. **အဘယ်ကြောင့် စာလုံးကြီး `$Product` (Capital Letter) ဖြင့် ရေးရသနည်း?**
   * EC-CUBE Core ၏ Coding Standard တွင် သာမန် Primitive Data (ဥပမာ- `$id`, `$action`, `$create_date`) များကို စာလုံးသေး (`camelCase` သို့မဟုတ် `snake_case`) ဖြင့် ရေးသားပြီး၊ **အခြား Entity နှင့် ချိတ်ဆက်ထားသော Entity Object ကိန်းရှင်များ** (ဥပမာ- `$Customer`, `$Product`, `$Status`, `$Creator`) ကို **စာလုံးကြီး (PascalCase / Capitalize)** ဖြင့် ရေးသားရသည့် စံနှုန်းသတ်မှတ်ချက် ရှိပါသည်။
   * ဤသို့ ရေးသားခြင်းဖြင့် ကုဒ်ဖတ်သူသည် ဤကိန်းရှင်သည် သာမန် နံပါတ် integer မဟုတ်ဘဲ **Entity Object အစစ်အမှန် ဖြစ်သည်** ကို ချက်ချင်း ခွဲခြားသိမြင်နိုင်စေပါသည်။

2. **`private $Product;` သည် Database ထဲတွင် မည်သို့ သိမ်းဆည်းသနည်း?**
   * Database Table ထဲတွင် `$Product` ဟူသော Column မရှိပါ။ ၎င်းအစား JoinColumn တွင် သတ်မှတ်ထားသော **`product_id` (INT)** အနေဖြင့်သာ သိမ်းဆည်းပါသည်။
   * သို့သော် PHP Code ထဲတွင် Doctrine က အဆိုပါ `product_id` ကို ဖတ်ပြီး `Eccube\Entity\Product` Object အဖြစ် အလိုအလျောက် ပြောင်းလဲပေးထားသဖြင့် Controller သို့မဟုတ် Twig ထဲတွင်:
     ```php
     // Product ရဲ့ အချက်အလက်အားလုံးကို တိုက်ရိုက် ဆွဲယူနိုင်ပါသည်
     $History->getProduct()->getId();           // Product ID
     $History->getProduct()->getName();         // Product အမည်
     $History->getProduct()->getMainFileName(); // Product ဓာတ်ပုံ
     ```
     စသည်ဖြင့် အလွယ်တကူ ခေါ်ယူအသုံးပြုနိုင်စေပါသည်။

3. **Doctrine Mapping အစိတ်အပိုင်းများ ရှင်းလင်းချက်:**
   * **`targetEntity="Eccube\Entity\Product"`**: ချိတ်ဆက်မည့် မူရင်း Entity Class အမည် ဖြစ်ပါသည်။
   * **`name="product_id"`**: MySQL တွင် ပေါ်လာမည့် Foreign Key ကော်လံ အမည် ဖြစ်ပါသည်။
   * **`referencedColumnName="id"`**: Product Entity ၏ မည်သည့် column နှင့် ချိတ်မလဲ (Product ၏ Primary Key `id` နှင့် ချိတ်သည်)။
   * **`nullable=false`**: Favorite History တွင် ကုန်ပစ္စည်း မပါဘဲ Record ဖြစ်ပေါ်ခွင့် မရှိပါ (Product ID သည် မဖြစ်မနေ လိုအပ်သည်)။
   * **`onDelete="CASCADE"`**: အကယ်၍ ကုန်ပစ္စည်း ပျက်သွားလျှင် ၎င်း၏ History စာရင်းများကိုပါ တစ်ပါတည်း အလိုအလျောက် ရှင်းထုတ်ပစ်ရန် ဖြစ်ပါသည်။

---

##### မေးခွန်း (၂) - `create_date` အတွက် `setCreateDate()` နှင့် `getCreateDate()` ကို အဘယ်ကြောင့် ဤ Structure အတိုင်း ရေးရသလဲ? အခြား Structure သုံး၍ မရဘူးလား?

> **အဖြေ:**  
> **အလွန်အရေးကြီးသော အကြောင်းရင်း ရှိပါသည်: EC-CUBE Core ၏ Auto-Save စနစ် (`SaveEventSubscriber`) သည် `setCreateDate` ဟူသော method အမည်ကိုသာ စောင့်ကြည့်၍ အလိုအလျောက် Date သွင်းပေးသောကြောင့် ဖြစ်ပါသည်။**

1. **EC-CUBE Core ၏ လျှို့ဝှက်ချက် - `SaveEventSubscriber.php` သက်သေ:**
   * ဖိုင်လမ်းကြောင်း: [src/Eccube/Doctrine/EventSubscriber/SaveEventSubscriber.php](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/src/Eccube/Doctrine/EventSubscriber/SaveEventSubscriber.php) (Line 62-67)
   ```php
   public function prePersist(LifecycleEventArgs $args)
   {
       $entity = $args->getObject();

       // ဤနေရာတွင် EC-CUBE Core က 'setCreateDate' method ရှိ/မရှိ စစ်ဆေးပြီး အလိုအလျောက် ရက်စွဲ သွင်းပေးပါသည်
       if (method_exists($entity, 'setCreateDate')) {
           $entity->setCreateDate(new \DateTime());
       }
       if (method_exists($entity, 'setUpdateDate')) {
           $entity->setUpdateDate(new \DateTime());
       }
       ...
   }
   ```
2. **ဤ Structure အတိုင်း အတိအကျ ရေးရသည့် အကြောင်းရင်း (Why):**
   * အကယ်၍ သင်သည် အခြား structure ဥပမာ `setCreatedAt()`, `setCreated()` သို့မဟုတ် `set_create_date()` ဟု ရေးလိုက်ပါက EC-CUBE Core ၏ `SaveEventSubscriber` က ထို method ကို ရှာမတွေ့တော့ဘဲ Database ထဲသို့ Save သည့်အခါ ရက်စွဲ မဝင်တော့ဘဲ ဖြစ်သွားပါမည်။
   * ထို့ကြောင့် EC-CUBE Core နှင့် ချိတ်ဆက် အလုပ်လုပ်နိုင်ရန် Method Name ကို **`setCreateDate($createDate)`** ဟု မဖြစ်မနေ အတိအကျ ပေးရခြင်း ဖြစ်ပါသည်။

3. **Core Entity ရှိ Getter/Setter သက်သေ:**
   * ဖိုင်လမ်းကြောင်း: [src/Eccube/Entity/CustomerFavoriteProduct.php](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/src/Eccube/Entity/CustomerFavoriteProduct.php) (Line 84-105)
   ```php
   /**
    * Set create_date
    *
    * @param \DateTime $createDate
    * @return CustomerFavoriteProduct
    */
   public function setCreateDate($createDate)
   {
       $this->create_date = $createDate;

       return $this;
   }

   /**
    * Get create_date
    *
    * @return \DateTime
    */
   public function getCreateDate()
   {
       return $this->create_date;
   }
   ```
   * Login History ဖိုင် [src/Eccube/Entity/LoginHistory.php](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/src/Eccube/Entity/LoginHistory.php) (Line 163-180) တွင်လည်း အတိအကျ ဤအတိုင်း ရေးသားထားပါသည်။

4. **Constructor ထဲတွင် `$this->create_date = new \DateTime();` ထည့်သွင်းရသည့် အကြောင်းရင်း:**
   * Database ထဲသို့ `persist()` မလုပ်မီ PHP Memory ထဲတွင် Entity Object စတင်ဆောက်လိုက်သည်နှင့် `$entity->getCreateDate()` ကို လှမ်းခေါ်ပါက `null` မဖြစ်ဘဲ တိကျသော DateTime Object တစ်ခု အဆင်သင့် ရှိနေစေရန် (Defensive Programming) အတွက် ဖြစ်ပါသည်။

---

### အဆင့် (၂) - Repository ရေးသားခြင်း (`CustomerFavoriteProductHistoryRepository.php`)

📁 **ဖိုင်လမ်းကြောင်း:** `app/Customize/Repository/CustomerFavoriteProductHistoryRepository.php`

#### ဘာကြောင့် ဒီဖိုင်ကို ရေးရသလဲ? (Why?)
Database ထဲသို့ Record အသစ် ထည့်သွင်းခြင်း (`addHistory`) နှင့် သက်ဆိုင်ရာ Product အလိုက် History စာရင်းများကို ရက်စွဲအသစ်ဆုံးမှ အဟောင်းသို့ စီတန်းထုတ်ယူမည့် QueryBuilder (`getQueryBuilderByProduct`) တို့ကို စုစည်းရေးသားထားရန် ဖြစ်ပါသည်။

#### Code အပြည့်အစုံ:
```php
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

namespace Customize\Repository;

use Customize\Entity\CustomerFavoriteProductHistory;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Eccube\Entity\Customer;
use Eccube\Entity\Product;
use Eccube\Repository\AbstractRepository;

/**
 * Class CustomerFavoriteProductHistoryRepository
 */
class CustomerFavoriteProductHistoryRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerFavoriteProductHistory::class);
    }

    /**
     * သမိုင်းမှတ်တမ်းအသစ် ထည့်သွင်းသိမ်းဆည်းခြင်း
     *
     * @param Customer $Customer
     * @param Product $Product
     * @param string $action ('register' သို့မဟုတ် 'remove')
     * @return CustomerFavoriteProductHistory
     */
    public function addHistory(Customer $Customer, Product $Product, string $action): CustomerFavoriteProductHistory
    {
        $history = new CustomerFavoriteProductHistory();
        $history->setCustomer($Customer);
        $history->setProduct($Product);
        $history->setAction($action);
        $history->setCreateDate(new \DateTime());

        $em = $this->getEntityManager();
        $em->persist($history);
        $em->flush();

        return $history;
    }

    /**
     * ကုန်ပစ္စည်းတစ်ခု၏ သမိုင်းမှတ်တမ်းများအတွက် QueryBuilder ရယူခြင်း
     * (ရက်စွဲ နောက်ဆုံးဖြစ်ပေါ်ခဲ့သည်များကို ထိပ်ဆုံးမှ ပြသရန် DESC ဖြင့် စီထားပါသည်)
     *
     * @param Product $Product
     * @return QueryBuilder
     */
    public function getQueryBuilderByProduct(Product $Product): QueryBuilder
    {
        $qb = $this->createQueryBuilder('h')
            ->leftJoin('h.Customer', 'c')
            ->addSelect('c')
            ->where('h.Product = :Product')
            ->setParameter('Product', $Product)
            ->orderBy('h.create_date', 'DESC')
            ->addOrderBy('h.id', 'DESC');

        return $qb;
    }
}
```

---

### အဆင့် (၃) - Database Table တည်ဆောက်ခြင်း (Doctrine Schema Commands)

Entity ရေးသားပြီးပါက Doctrine ORM မှတစ်ဆင့် Database ထဲတွင် `dtb_customer_favorite_product_history` table အမှန်တကယ် ပေါ်လာစေရန် Terminal တွင် အောက်ပါ Command များကို အစဉ်လိုက် Run ပေးရပါမည်:

```bash
# ၁။ Entity Proxy Classes များကို Generate လုပ်ခြင်း
docker compose exec ec-cube bin/console eccube:generate:proxies

# ၂။ ထွက်ပေါ်လာမည့် SQL Query ကို စစ်ဆေးကြည့်ရှုခြင်း (Dump SQL)
docker compose exec ec-cube bin/console doctrine:schema:update --dump-sql

# ၃။ Database ထဲသို့ Table အသစ် အမှန်တကယ် ဆောက်လုပ်ခြင်း (Force Update)
docker compose exec ec-cube bin/console doctrine:schema:update --force
```

> **စစ်ဆေးရန်:** `--force` run ပြီးပါက Terminal တွင် `[OK] Database schema updated successfully!` ဟူသော စာသား ပေါ်လာပါမည်။

---

### အဆင့် (၄) - Action များကို အလိုအလျောက် မှတ်တမ်းတင်ခြင်း (`FavoriteEventListener.php`)

📁 **ဖိုင်လမ်းကြောင်း:** `app/Customize/EventListener/FavoriteEventListener.php`

#### ဘာကြောင့် ဒီဖိုင်ကို ပြင်ဆင်ရသလဲ? (Why?)
ဝယ်ယူသူ Customer များသည် Front Store ကုန်ပစ္စည်း Detail စာမျက်နှာမှ Favorite ခလုတ်နှိပ်သည့်အခါ (`FRONT_PRODUCT_FAVORITE_ADD_COMPLETE`) နှင့် Mypage မှ Favorite ဖျက်သည့်အခါ (`FRONT_MYPAGE_MYPAGE_DELETE_COMPLETE`) အလိုအလျောက် Event စောင့်ကြည့်ပြီး သမိုင်းမှတ်တမ်း (History) ထဲသို့ Database row အသစ် ထည့်ပေးရန် ဖြစ်ပါသည်။

> [!TIP]
> **EventListener အသေးစိတ် လေ့လာရန် သီးသန့် လက်စွဲစာအုပ်:**  
> EventListener ကို မည်သည့်အချိန်တွင် သုံးရမည်၊ မည်သို့ အလုပ်လုပ်သည်၊ Logger နှင့် Security အဘယ်ကြောင့် သုံးရသည် စသည့် ပြည့်စုံသော အသေးစိတ် လမ်းညွှန်ချက်ကို [eccube_event_listener_complete_guide.md](file:///Users/kyawwaiyan/Documents/my-Home-tech/eccube-4.3.1/My-Task-List-Folder/EventListener-Guide/eccube_event_listener_complete_guide.md) တွင် သီးသန့် ဖတ်ရှုလေ့လာနိုင်ပါသည်။

#### ပြင်ဆင်ရမည့် အချက်များ:
1. `CustomerFavoriteProductHistory` နှင့် `CustomerFavoriteProductHistoryRepository` တို့ကို Import လုပ်ခြင်း။
2. Constructor တွင် `CustomerFavoriteProductHistoryRepository` နှင့် Symfony `Security` (လက်ရှိ Login ဝင်ထားသော Customer ကို ရယူရန်) တို့ကို Inject လုပ်ခြင်း။
3. `onFavoriteAddComplete` method တွင် `ACTION_REGISTER` မှတ်တမ်းတင်ခြင်း။
4. `onMypageDeleteComplete` method တွင် `ACTION_REMOVE` မှတ်တမ်းတင်ခြင်း။

#### Code အပြည့်အစုံ:
```php
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

namespace Customize\EventListener;

use Customize\Entity\CustomerFavoriteProductHistory;
use Customize\Repository\CustomerFavoriteProductHistoryRepository;
use Eccube\Entity\Customer;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class FavoriteEventListener
 *
 * အကြိုက်ဆုံးပစ္စည်း အပြောင်းအလဲများကို စောင့်ကြည့်ဖမ်းယူပြီး History Table ထဲသို့ မှတ်တမ်းတင်ပေးသော Event Subscriber
 */
class FavoriteEventListener implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerFavoriteProductHistoryRepository
     */
    protected $historyRepository;

    /**
     * @var Security
     */
    protected $security;

    /**
     * FavoriteEventListener constructor.
     *
     * @param LoggerInterface $logger
     * @param CustomerFavoriteProductHistoryRepository $historyRepository
     * @param Security $security
     */
    public function __construct(
        LoggerInterface $logger,
        CustomerFavoriteProductHistoryRepository $historyRepository,
        Security $security
    ) {
        $this->logger = $logger;
        $this->historyRepository = $historyRepository;
        $this->security = $security;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EccubeEvents::FRONT_PRODUCT_FAVORITE_ADD_INITIALIZE => 'onFavoriteAddInitialize',
            EccubeEvents::FRONT_PRODUCT_FAVORITE_ADD_COMPLETE => 'onFavoriteAddComplete',
            EccubeEvents::FRONT_MYPAGE_MYPAGE_DELETE_COMPLETE => 'onMypageDeleteComplete',
        ];
    }

    /**
     * Favorite အဖြစ် စတင်မထည့်သွင်းမီ လုပ်ဆောင်ချက်
     *
     * @param EventArgs $event
     */
    public function onFavoriteAddInitialize(EventArgs $event)
    {
        $Product = $event->getArgument('Product');
        if ($Product) {
            $this->logger->info(sprintf('[Favorite] Product Favorite Add Initialized: Product ID=%d, Name=%s', $Product->getId(), $Product->getName()));
        }
    }

    /**
     * Favorite အဖြစ် အောင်မြင်စွာ ထည့်သွင်းပြီးချိန် လုပ်ဆောင်ချက်
     * Action = 'register' (အကြိုက်ဆုံး မှတ်ပုံတင်ခြင်း) အဖြစ် မှတ်တမ်းတင်ပါသည်
     *
     * @param EventArgs $event
     */
    public function onFavoriteAddComplete(EventArgs $event)
    {
        $Product = $event->getArgument('Product');
        $Customer = $this->security->getUser();

        if ($Product && $Customer instanceof Customer) {
            // History Record အသစ် ထည့်သွင်းခြင်း
            $this->historyRepository->addHistory(
                $Customer,
                $Product,
                CustomerFavoriteProductHistory::ACTION_REGISTER
            );

            $this->logger->info(sprintf(
                '[Favorite History] Registered: Customer ID=%d, Product ID=%d',
                $Customer->getId(),
                $Product->getId()
            ));
        }
    }

    /**
     * Mypage မှ Favorite ပစ္စည်းအား ဖျက်လိုက်သည့်အခါ လုပ်ဆောင်ချက်
     * Action = 'remove' (အကြိုက်ဆုံးမှ ပြန်လည်ဖယ်ရှားခြင်း) အဖြစ် မှတ်တမ်းတင်ပါသည်
     *
     * @param EventArgs $event
     */
    public function onMypageDeleteComplete(EventArgs $event)
    {
        $Customer = $event->getArgument('Customer');
        $CustomerFavoriteProduct = $event->getArgument('CustomerFavoriteProduct');

        if ($Customer instanceof Customer && $CustomerFavoriteProduct && $CustomerFavoriteProduct->getProduct()) {
            $Product = $CustomerFavoriteProduct->getProduct();

            // History Record အသစ် ထည့်သွင်းခြင်း
            $this->historyRepository->addHistory(
                $Customer,
                $Product,
                CustomerFavoriteProductHistory::ACTION_REMOVE
            );

            $this->logger->info(sprintf(
                '[Favorite History] Removed: Customer ID=%d, Product ID=%d',
                $Customer->getId(),
                $Product->getId()
            ));
        }
    }
}
```

---

#### EventListener နှင့် Repository နည်းပညာဆိုင်ရာ အသေးစိတ် ရှင်းလင်းချက် (Deep-Dive Guide on EventListener & Repository)

Junior Developer များ နေ့စဉ် လုပ်ငန်းခွင်တွင် ရှင်းလင်းစွာ သိရှိနားလည်ထားရမည့် EventListener နှင့် Repository ဆိုင်ရာ အဓိက သဘောတရားများ ဖြစ်ပါသည်:

---

##### ၁။ Repository ရှိ Method DocBlock (`@param`, `@return`) အသုံးပြုပုံနှင့် အားသာချက်များ

```php
/**
 * သမိုင်းမှတ်တမ်းအသစ် ထည့်သွင်းသိမ်းဆည်းခြင်း
 *
 * @param Customer $Customer
 * @param Product $Product
 * @param string $action ('register' သို့မဟုတ် 'remove')
 * @return CustomerFavoriteProductHistory
 */
public function addHistory(Customer $Customer, Product $Product, string $action): CustomerFavoriteProductHistory
```

* **အသုံးပြုပုံ (Usage):**  
  Method တစ်ခုကို အခြားဖိုင်များ (ဥပမာ- EventListener သို့မဟုတ် Controller) မှ လှမ်းခေါ်သည့်အခါ:
  1. မည်သည့် Parameter အမျိုးအစားများကို ထည့်ပေးရမည် (`$Customer` သည် Customer Entity ဖြစ်ရမည်၊ `$Product` သည် Product Entity ဖြစ်ရမည်၊ `$action` သည် string ဖြစ်ရမည်)
  2. ဤ Method ပြီးဆုံးသွားသည့်အခါ မည်သည့် Object ပြန်ပေးမည် (`CustomerFavoriteProductHistory` Entity Object ပြန်ပေးမည်)
  ဆိုသည်ကို ကြေညာပေးခြင်း ဖြစ်ပါသည်။
* **အဓိက အားသာချက်များ (Advantages):**
  1. **IDE Auto-completion:** အခြားဖိုင်တွင် `$history = $this->historyRepository->addHistory(...)` ဟု ခေါ်ပြီးနောက် `$history->` ဟု ရိုက်လိုက်သည်နှင့် IDE (PhpStorm/VSCode) က ဤ Entity ၏ Getter/Setter များကို Dropdown စာရင်းဖြင့် အလိုအလျောက် ပြသပေးနိုင်ပါသည်။
  2. **Static Analysis & Bug Prevention:** အကယ်၍ Developer က `$Product` နေရာတွင် မှားယွင်းစွာ integer ID သွားထည့်မိပါက PHPStan သို့မဟုတ် IDE က Code မ Run မီကတည်းက Error အနီရောင်မျဉ်းတား၍ ချက်ချင်း သတိပေးပါသည်။

---

##### ၂။ `use` Statement (Namespace Imports) များသည် Format လား၊ EC-CUBE Standard လား? Listener တိုင်း ထည့်ရမလား?

* **သဘောတရား:** PHP တွင် အခြား Folder/Namespace ထဲရှိ Class များကို ခေါ်ယူအသုံးပြုရန် `use` ဖြင့် Import လုပ်ရခြင်းသည် **PHP Standard (PSR-4)** ဖြစ်ပါသည်။
* **Listener တိုင်း အားလုံး ထည့်ရမလား?**
  * **Listener တိုင်း မဖြစ်မနေ တူညီစွာ ပါဝင်ရမည့် အခြေခံ Core (၃) ခု:**
    1. `use Symfony\Component\EventDispatcher\EventSubscriberInterface;` (Listener အားလုံး Implement လုပ်ရမည့် Interface ဖြစ်သည်)
    2. `use Eccube\Event\EccubeEvents;` (EC-CUBE Core ၏ Event အမည်များ ယူသုံးရန်)
    3. `use Eccube\Event\EventArgs;` (Event မှ ပေးပို့လိုက်သော Data များကို ဆွဲထုတ်ရန်)
  * **လုပ်ငန်းလိုအပ်ချက်အရ ထပ်မံထည့်ရသော အရာများ (On-Demand Imports):**
    - Database ထဲ Data သွင်းလိုပါက သက်ဆိုင်ရာ Entity/Repository ကို `use` လုပ်ရသည် (`CustomerFavoriteProductHistory`, `CustomerFavoriteProductHistoryRepository`)။
    - လက်ရှိ Login ဝင်ထားသော Customer ကို ရယူလိုပါက `Security` ကို `use` လုပ်ရသည် (`use Symfony\Component\Security\Core\Security;`)။
    - Log မှတ်တမ်း ရေးသားလိုပါက `LoggerInterface` ကို `use` လုပ်ရသည် (`use Psr\Log\LoggerInterface;`)။

---

##### ၃။ `protected $logger; protected $historyRepository; protected $security;` အဘယ်ကြောင့် `protected` သုံးသနည်း? ၎င်းတို့သည် မည်သည့်နေရာမှ ရောက်လာသနည်း?

* **၎င်းတို့သည် မည်သည့်နေရာမှ ရောက်လာသနည်း (Where do they come from?):**
  * Symfony / EC-CUBE ၏ **Dependency Injection (DI) Container (အလိုအလျောက် ဝန်ဆောင်မှု ပေးဝေရေးစနစ်)** မှတစ်ဆင့် ရောက်ရှိလာခြင်း ဖြစ်ပါသည်။
  * Developer က `new Logger()` သို့မဟုတ် `new Repository()` ဟု ကိုယ်တိုင် ကုဒ်ရေးစရာ မလိုဘဲ Constructor ၏ Parameter တွင် Type Hinting ပေးထားရုံဖြင့် Symfony က စနစ်နောက်ကွယ်မှ အဆင်သင့် Object များကို အလိုအလျောက် ထည့်သွင်း (Auto-wiring) ပေးပါသည်။
* **အဘယ်ကြောင့် `protected` အဖြစ် ကြေညာသနည်း:**
  * `private` ဟု သတ်မှတ်ပါက အဆိုပါ Class တစ်ခုတည်းတွင်သာ သုံးနိုင်ပါသည်။
  * `protected` ဟု သတ်မှတ်ထားခြင်းဖြင့် နောင်တစ်ချိန်တွင် အခြား Developer တစ်ဦးဦးက ဤ Listener ကို Class Inheritance (Extend) လုပ်၍ Override ချဲ့ထွင်ရေးသားလိုပါက အဆိုပါ Service များကို အလွယ်တကူ ဆက်လက် သုံးစွဲနိုင်စေရန် ဖြစ်ပါသည်။ EC-CUBE Core ၏ စံနှုန်းအတိုင်း ရေးသားထားခြင်း ဖြစ်ပါသည်။

---

##### ၄။ `public static function getSubscribedEvents()` ဆိုသည်မှာ အဘယ်နည်း? အခြား Event များအတွက်ရော ဤသို့ပင် ရေးရမည်လား?

* **အဓိပ္ပာယ်:** Symfony Event Dispatcher အား *"ငါ့ Listener သည် မည်သည့် စနစ်လှုပ်ရှားမှု (Events) များကို စောင့်ကြည့်ချင်သလဲ၊ ထို Event ဖြစ်လာပါက မည်သည့် Method ကို အလုပ်လုပ်ပေးပါ"* ဟု စာရင်းသွင်း (Subscribe) ပေးသော နေရာ ဖြစ်ပါသည်။
* **ပုံစံ (Format):** `[ 'စောင့်ကြည့်မည့် Event အမည်' => 'ခေါ်ယူရမည့် Method အမည်' ]`
* **အခြား မတူညီသော Event များအတွက်ရော ဤသို့ပင် ရေးရမည်လား?**
  * **ဟုတ်ကဲ့၊ ၁၀၀% အတိအကျ ဤပုံစံအတိုင်းပင် ရေးသားရပါမည်။**
  * ဥပမာ- Order တင်ပြီးချိန်ကို စောင့်ကြည့်လိုပါက:
    ```php
    public static function getSubscribedEvents()
    {
        return [
            EccubeEvents::FRONT_SHOPPING_CONFIRM_COMPLETE => 'onShoppingComplete',
        ];
    }
    ```
    ဟု ရေးသားပြီး အောက်တွင် `public function onShoppingComplete(EventArgs $event)` method ကို ရေးပေးရုံ ဖြစ်ပါသည်။

---

##### ၅။ `(EventArgs $event)`, `$event->getArgument()` နှင့် `$this->logger->info()` တို့၏ အသုံးပြုပုံ ရှင်းလင်းချက်

1. **`(EventArgs $event)` ဆိုသည်မှာ အဘယ်နည်း:**
   * Event တစ်ခု စတင်ဖြစ်ပေါ်သည့်အခါ EC-CUBE Core Controller က ထို Event နှင့် သက်ဆိုင်သော အချက်အလက်များ (ဥပမာ- Product, Customer, Request) ကို ထည့်သွင်း ပေးပို့လိုက်သည့် **သတင်းအချက်အလက် ထုပ်ပိုးထားသော Container Object** ဖြစ်ပါသည်။
2. **`$event->getArgument('Product')` အသုံးပြုပုံ:**
   * Core Controller က ထုပ်ပိုးပေးပို့လိုက်သော Data ထဲမှ `'Product'` ဟူသော နာမည်ဖြင့် ကုန်ပစ္စည်း Entity Object ကို ဆွဲထုတ်ရယူခြင်း ဖြစ်ပါသည်။
3. **`$this->logger->info(...)` အသုံးပြုပုံ:**
   * စနစ်၏ Log ဖိုင် (`var/log/prod/site_yyyy-mm-dd.log`) ထဲသို့ စနစ်၏ လှုပ်ရှားမှု မှတ်တမ်းများကို ရေးသားသိမ်းဆည်းပေးခြင်း ဖြစ်ပါသည်။ နောင်တွင် Error ရှာဖွေရာ၌ အလွန် အသုံးဝင်ပါသည်။

---

##### ၆။ `onFavoriteAddComplete` နှင့် `onMypageDeleteComplete` Method များ အလုပ်လုပ်ပုံ အသေးစိတ်

###### (က) Favorite Add အခိုက်အတန့် (`onFavoriteAddComplete`):
```php
public function onFavoriteAddComplete(EventArgs $event)
{
    // ၁။ Event ထဲမှ ကုန်ပစ္စည်းကို ရယူသည်
    $Product = $event->getArgument('Product');

    // ၂။ လက်ရှိ Login ဝင်ထားသော ဝယ်ယူသူ (Customer) ကို Security မှ ရယူသည်
    $Customer = $this->security->getUser();

    // ၃။ ကုန်ပစ္စည်းရော Customer ပါ အမှန်တကယ် ရှိမှသာ History ထဲသို့ 'register' အဖြစ် သိမ်းဆည်းသည်
    if ($Product && $Customer instanceof Customer) {
        $this->historyRepository->addHistory(
            $Customer,
            $Product,
            CustomerFavoriteProductHistory::ACTION_REGISTER
        );

        // ၄။ Log ရေးမှတ်သည်
        $this->logger->info(sprintf('[Favorite History] Registered: Customer ID=%d, Product ID=%d', $Customer->getId(), $Product->getId()));
    }
}
```

###### (ခ) Favorite Delete အခိုက်အတန့် (`onMypageDeleteComplete`):
```php
public function onMypageDeleteComplete(EventArgs $event)
{
    // ၁။ Event ထဲမှ Customer နှင့် CustomerFavoriteProduct ကို ရယူသည်
    $Customer = $event->getArgument('Customer');
    $CustomerFavoriteProduct = $event->getArgument('CustomerFavoriteProduct');

    // ၂။ ဖျက်လိုက်သော အရာထဲမှ Product ကို ဆွဲထုတ်သည်
    if ($Customer instanceof Customer && $CustomerFavoriteProduct && $CustomerFavoriteProduct->getProduct()) {
        $Product = $CustomerFavoriteProduct->getProduct();

        // ၃။ History ထဲသို့ 'remove' အဖြစ် သိမ်းဆည်းသည်
        $this->historyRepository->addHistory(
            $Customer,
            $Product,
            CustomerFavoriteProductHistory::ACTION_REMOVE
        );

        // ၄။ Log ရေးမှတ်သည်
        $this->logger->info(sprintf('[Favorite History] Removed: Customer ID=%d, Product ID=%d', $Customer->getId(), $Product->getId()));
    }
}
```

---

##### ၇။ နောင်တွင် အလားတူ History Feature များ တည်ဆောက်ပါက ဤပုံစံအတိုင်း အတူတူ သုံးရမည်လား?

> **အဖြေ: ဟုတ်ကဲ့၊ ၁၀၀% အတိအကျ ဤပုံစံအတိုင်းပင် ရေးသားရပါမည်။**

* ဥပမာ- နောင်တွင် **Order Status ပြောင်းလဲမှု History**, **Login History**, **Cart Item အပြောင်းအလဲ History**, **Point အတိုးအလျော့ History** စသည်တို့ကို တည်ဆောက်လိုပါကလည်း:
  1. History Database Entity & Repository တစ်ခု တည်ဆောက်မည်။
  2. သက်ဆိုင်ရာ Core Action ၏ Event ကို စောင့်ကြည့်မည့် EventSubscriber တစ်ခု တည်ဆောက်မည်။
  3. ထို Event ဖြစ်ချိန်တိုင်း Repository ၏ `addHistory()` ကို လှမ်းခေါ်ပြီး History Table ထဲသို့ Record အသစ် ထည့်သွင်းမည်။
* ဤနည်းလမ်းသည် **EC-CUBE Core File များကို လုံးဝ မထိခိုက်စေဘဲ စနစ်ကို သန့်ရှင်းလုံခြုံစွာ တည်ဆောက်နိုင်သော အကောင်းဆုံး Enterprise Industry Standard Pattern** ဖြစ်ပါသည်။

---

### အဆင့် (၅) - Admin History Controller ရေးသားခြင်း (`FavouriteProductHistoryController.php`)

📁 **ဖိုင်လမ်းကြောင်း:** `app/Customize/Controller/Admin/Product/FavouriteProductHistoryController.php`

#### ဘာကြောင့် ဒီဖိုင်ကို ရေးရသလဲ? (Why?)
Admin Panel မှ `/admin/product/favourite/{id}/history` URL သို့ ဝင်ရောက်လာသောအခါ သက်ဆိုင်ရာ Product ၏ History Data များကို Repository မှ ဆွဲထုတ်ပြီး Pagination ပြုလုပ်ကာ Twig Template သို့ ပေးပို့ပေးမည့် Controller ဖြစ်ပါသည်။

#### Code အပြည့်အစုံ:
```php
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

use Customize\Repository\CustomerFavoriteProductHistoryRepository;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Product;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FavouriteProductHistoryController
 *
 * ကုန်ပစ္စည်းတစ်ခုချင်းစီ၏ Favorite Action History (Register/Remove) များကို ပြသသော Admin Controller
 */
class FavouriteProductHistoryController extends AbstractController
{
    /**
     * @var CustomerFavoriteProductHistoryRepository
     */
    protected $historyRepository;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * FavouriteProductHistoryController constructor.
     *
     * @param CustomerFavoriteProductHistoryRepository $historyRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(
        CustomerFavoriteProductHistoryRepository $historyRepository,
        PaginatorInterface $paginator
    ) {
        $this->historyRepository = $historyRepository;
        $this->paginator = $paginator;
    }

    /**
     * ကုန်ပစ္စည်း၏ Favorite History စာရင်း ပြသခြင်း
     *
     * @Route("/%eccube_admin_route%/product/favourite/{id}/history", name="admin_product_favourite_history", requirements={"id" = "\d+"}, methods={"GET"})
     * @Route("/%eccube_admin_route%/product/favourite/{id}/history/page/{page_no}", name="admin_product_favourite_history_page", requirements={"id" = "\d+", "page_no" = "\d+"}, methods={"GET"})
     * @Template("@admin/Product/product_favourite_history.twig")
     *
     * @param Request $request
     * @param Product $Product
     * @param int|null $page_no
     * @return array
     */
    public function index(Request $request, Product $Product, $page_no = null): array
    {
        if (null !== $page_no) {
            $this->session->set('eccube.admin.product.favourite.history.page_no', (int) $page_no);
        } else {
            $page_no = $this->session->get('eccube.admin.product.favourite.history.page_no', 1);
        }

        $page_count = $this->eccubeConfig->get('eccube_default_page_count');

        // Product အလိုက် History QueryBuilder ရယူခြင်း
        $qb = $this->historyRepository->getQueryBuilderByProduct($Product);

        // Pagination ပြုလုပ်ခြင်း
        $pagination = $this->paginator->paginate($qb, $page_no, $page_count, ['wrap-queries' => true]);

        return [
            'Product' => $Product,
            'pagination' => $pagination,
            'page_no' => $page_no,
        ];
    }
}
```

---

### အဆင့် (၆) - Favorites Management List တွင် History Link ထည့်သွင်းခြင်း (`product_favourite.twig`)

📁 **ဖိုင်လမ်းကြောင်း:** `app/template/admin/Product/product_favourite.twig`

#### ပြင်ဆင်ရန် လိုအပ်ချက်:
လက်ရှိ Favourite Product စာရင်း Table ၏ Header တွင် **"Action (操作)"** သို့မဟုတ် **"History (履歴)"** Column တစ်ခု ထပ်တိုးပြီး၊ အောက်ပါအတိုင်း Button Link ထည့်သွင်းပေးရပါမည်:

#### နမူနာ Code ပြင်ဆင်ပုံ:
Table Thead တွင်:
```twig
<th class="py-3 text-center" style="width: 140px;">{{ 'admin.product.favorite_count'|trans }}</th>
<th class="py-3 text-center" style="width: 120px;">{{ 'admin.product.display_status__short'|trans }}</th>
<!-- အသစ်ထည့်သွင်းမည့် History Column -->
<th class="pe-3 py-3 text-center" style="width: 130px;">{{ '履歴' }}</th>
```

Table Tbody loop အတွင်း ကုန်ပစ္စည်းတစ်ခုချင်းစီအတွက်:
```twig
<!-- Status -->
<td class="text-center">
    {% if Product.Status and Product.Status.id == constant('Eccube\\Entity\\Master\\ProductStatus::DISPLAY_SHOW') %}
        <span class="badge bg-success">{{ Product.Status.name }}</span>
    {% elseif Product.Status and Product.Status.id == constant('Eccube\\Entity\\Master\\ProductStatus::DISPLAY_HIDE') %}
        <span class="badge bg-secondary">{{ Product.Status.name }}</span>
    {% else %}
        <span class="badge bg-light text-dark">-</span>
    {% endif %}
</td>

<!-- အသစ်ထည့်သွင်းမည့် History Button Link -->
<td class="pe-3 text-center">
    <a href="{{ url('admin_product_favourite_history', { id : Product.id }) }}" class="btn btn-sm btn-ec-regular text-primary shadow-sm" title="履歴一覧を見る">
        <i class="fa fa-history me-1"></i><span>履歴</span>
    </a>
</td>
```

---

### အဆင့် (၇) - History List UI Twig Template ဖန်တီးခြင်း (`product_favourite_history.twig`)

📁 **ဖိုင်လမ်းကြောင်း:** `app/template/admin/Product/product_favourite_history.twig`

#### ဘာကြောင့် ဒီဖိုင်ကို ရေးရသလဲ? (Why?)
အသုံးပြုသူက "履歴" Link ကို နှိပ်လိုက်သည့်အခါ သက်ဆိုင်ရာ ကုန်ပစ္စည်း၏ အချက်အလက်များနှင့်တကွ:
1. **Member ID (会員ID)**
2. **Member Name (会員名)**
3. **Action (登録 / 解除)**
4. **Date/Time (日時)**
တို့ကို ဇယားဖြင့် သပ်ရပ်လှပစွာ ပြသပေးမည့် Template ဖြစ်ပါသည်။

#### Code အပြည့်အစုံ:
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

{% block title %}お気に入り履歴一覧 - {{ Product.name }}{% endblock %}
{% block sub_title %}{{ 'admin.product.product_management'|trans }}{% endblock %}

{% block stylesheet %}
    <style>
        .fav-history-badge-register {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
        }
        .fav-history-badge-remove {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
        }
        .product-mini-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
    </style>
{% endblock %}

{% block main %}
    <div class="c-contentsArea__cols">
        <div class="c-contentsArea__primaryCol">
            <div class="c-primaryCol">

                <!-- Header & Navigation -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="{{ url('admin_product_favourite') }}" class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="fa fa-arrow-left me-1"></i>お気に入り商品一覧へ戻る
                        </a>
                        <h4 class="mb-1 fw-bold text-dark">
                            <i class="fa fa-history text-primary me-2"></i>お気に入り履歴 (Favorite History)
                        </h4>
                        <p class="text-muted small mb-0">この商品の登録・解除アクション履歴を確認できます。</p>
                    </div>
                </div>

                <!-- Product Summary Card -->
                <div class="card rounded border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <img class="product-mini-thumb me-3" src="{{ asset(Product.mainFileName|no_image_product, 'save_image') }}" alt="{{ Product.name }}">
                            <div class="flex-grow-1">
                                <span class="badge bg-light text-dark border font-monospace mb-1">ID: {{ Product.id }}</span>
                                <h6 class="mb-0 fw-bold">
                                    <a href="{{ url('admin_product_product_edit', { id: Product.id }) }}" class="text-dark text-decoration-none" target="_blank">
                                        {{ Product.name }} <i class="fa fa-external-link small text-muted ms-1"></i>
                                    </a>
                                </h6>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small d-block">現在のお気に入り数</span>
                                <span class="badge bg-danger fs-6 px-3 py-1">
                                    <i class="fa fa-heart me-1"></i>{{ Product.CustomerFavoriteProducts|length|number_format }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Table Card -->
                <div class="card rounded border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="fa fa-list-ul me-2 text-primary"></i>アクション履歴一覧 (Action History List)
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
                                        <th class="ps-4 py-3" style="width: 120px;">会員ID (Member ID)</th>
                                        <th class="py-3" style="min-width: 220px;">会員名 (Member Name)</th>
                                        <th class="py-3 text-center" style="width: 180px;">アクション (Action)</th>
                                        <th class="pe-4 py-3 text-end" style="width: 200px;">日時 (Date/Time)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {% for History in pagination %}
                                        <tr>
                                            <!-- 1. Member ID -->
                                            <td class="ps-4 fw-bold">
                                                {% if History.Customer %}
                                                    <a href="{{ url('admin_customer_customer_edit', { id: History.Customer.id }) }}" class="text-decoration-none font-monospace" target="_blank">
                                                        #{{ History.Customer.id }} <i class="fa fa-external-link small text-muted"></i>
                                                    </a>
                                                {% else %}
                                                    <span class="text-muted font-monospace">-</span>
                                                {% endif %}
                                            </td>

                                            <!-- 2. Member Name -->
                                            <td>
                                                {% if History.Customer %}
                                                    <span class="fw-bold text-dark">
                                                        {{ History.Customer.name01 }} {{ History.Customer.name02 }}
                                                    </span>
                                                    <span class="text-muted small ms-1">({{ History.Customer.kana01 }} {{ History.Customer.kana02 }})</span>
                                                {% else %}
                                                    <span class="text-muted fst-italic">退会済み会員 (Deleted Customer)</span>
                                                {% endif %}
                                            </td>

                                            <!-- 3. Action (Register / Remove) -->
                                            <td class="text-center">
                                                {% if History.action == constant('Customize\\Entity\\CustomerFavoriteProductHistory::ACTION_REGISTER') %}
                                                    <span class="fav-history-badge-register">
                                                        <i class="fa fa-plus-circle me-1"></i>登録 (Register)
                                                    </span>
                                                {% elseif History.action == constant('Customize\\Entity\\CustomerFavoriteProductHistory::ACTION_REMOVE') %}
                                                    <span class="fav-history-badge-remove">
                                                        <i class="fa fa-minus-circle me-1"></i>解除 (Remove)
                                                    </span>
                                                {% else %}
                                                    <span class="badge bg-secondary">{{ History.action }}</span>
                                                {% endif %}
                                            </td>

                                            <!-- 4. Date / Time -->
                                            <td class="pe-4 text-end font-monospace text-muted">
                                                <i class="fa fa-clock-o me-1"></i>{{ History.create_date|date_format('', 'Y/m/d H:i:s') }}
                                            </td>
                                        </tr>
                                    {% endfor %}
                                    </tbody>
                                </table>
                            </div>
                        {% else %}
                            <!-- Empty State Card -->
                            <div class="text-center py-5">
                                <i class="fa fa-history fa-3x text-muted mb-3 d-block"></i>
                                <h6 class="text-muted fw-bold">まだこの商品のアクション履歴はありません</h6>
                                <p class="text-muted small">お気に入り登録または解除が行われると、ここに履歴が記録されます。</p>
                            </div>
                        {% endif %}
                    </div>
                </div>

                <!-- Pagination Footer -->
                {% if pagination and pagination.totalItemCount > 0 %}
                    <div class="row justify-content-md-center mb-4">
                        {% include "@admin/pager.twig" with {'pages': pagination.paginationData, 'routes': 'admin_product_favourite_history_page', 'params': {'id': Product.id}} %}
                    </div>
                {% endif %}

            </div>
        </div>
    </div>
{% endblock %}
```

---

## ၆။ Terminal Commands & Cache Management

ဖိုင်များအားလုံး ရေးသားပြီးစီးပါက EC-CUBE စနစ်ထဲတွင် အလုပ်လုပ်နိုင်စေရန် အောက်ပါ Command များကို အဆင့်လိုက် Run ပေးရပါမည်:

```bash
# အဆင့် (၁) - Entity Proxies ပြန်လည်ထုတ်လုပ်ခြင်း
docker compose exec ec-cube bin/console eccube:generate:proxies

# အဆင့် (၂) - Database Table အသစ် ထည့်သွင်းခြင်း
docker compose exec ec-cube bin/console doctrine:schema:update --force

# အဆင့် (၃) - Symfony Cache အားလုံးကို ရှင်းလင်းခြင်း
docker compose exec ec-cube bin/console cache:clear --no-warmup
```

---

## ၇။ စနစ်စမ်းသပ် စစ်ဆေးခြင်း (Testing & Verification Guide)

### စစ်ဆေးရမည့် အဆင့်များ:
1. **Front Store မှ Favorite ပြုလုပ်ခြင်း စမ်းသပ်ခြင်း:**
   - Front Store တွင် Customer အကောင့်တစ်ခုဖြင့် Login ဝင်ပါ။
   - ကုန်ပစ္စည်းတစ်ခု၏ Detail စာမျက်နှာ (`/products/detail/{id}`) သို့ သွားရောက်ပြီး **"お気に入りに追加 (Add to Favorite)"** ခလုတ်ကို နှိပ်ပါ။
2. **Front Store မှ Favorite ဖယ်ရှားခြင်း စမ်းသပ်ခြင်း:**
   - Mypage (`/mypage/favorite`) သို့ သွားပြီး အဆိုပါ ကုန်ပစ္စည်းအား **"削除 (Delete)"** ခလုတ်နှိပ်၍ ဖယ်ထုတ်ပါ။
3. **Database ထဲတွင် Data ဝင်မဝင် စစ်ဆေးခြင်း:**
   - Database ဇယား `dtb_customer_favorite_product_history` ကို ကြည့်ရှုပါ:
     - `action = 'register'` ဖြင့် record တစ်ကြောင်း ဝင်နေမည်။
     - `action = 'remove'` ဖြင့် record တစ်ကြောင်း ဝင်နေမည်။
4. **Admin Panel တွင် ဝင်ရောက်စစ်ဆေးခြင်း:**
   - Admin Panel ရှိ `Product (商品管理)` -> `Favourite Product (အကြိုက်ဆုံး ကုန်ပစ္စည်းများ စာရင်း)` သို့ သွားပါ။
   - သက်ဆိုင်ရာ ကုန်ပစ္စည်း၏ ညာဘက်ရှိ **"履歴 (History)"** ခလုတ်ကို နှိပ်ပါ။
   - ပေါ်လာသော History List Screen တွင်:
     - **Member ID** (နှိပ်ပါက အဆိုပါ Customer Edit မျက်နှာပြင်သို့ သွားနိုင်သော Link)
     - **Member Name**
     - **Action** (`登録 (Register)` - အစိမ်းရောင် badge / `解除 (Remove)` - အနီရောင် badge)
     - **Date/Time** (တိကျသော အချိန်နှင့် ရက်စွဲ)
     တို့ သပ်ရပ်လှပစွာ ပေါ်နေသည်ကို စစ်ဆေးအတည်ပြုနိုင်ပါသည်။
