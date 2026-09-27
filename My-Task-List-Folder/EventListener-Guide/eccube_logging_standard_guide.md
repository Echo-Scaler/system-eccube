# EC-CUBE 4.3 Logging System & Standard Guide (EC-CUBE Log ရေးသားခြင်း စံလမ်းညွှန်)

EC-CUBE တွင် Log ရေးသားခြင်းသည် စနစ်တစ်ခုလုံး၏ ဖြစ်စဉ်များ (Events)၊ User များ၏ လုပ်ဆောင်ချက်များ၊ စနစ်ချွတ်ယွင်းမှု (Errors) များကို စောင့်ကြည့်စစ်ဆေးရာတွင် အဓိကအရေးပါသော အစိတ်အပိုင်းတစ်ခု ဖြစ်သည်။ ဤလမ်းညွှန်သည် အတွေ့အကြုံ (၆) လ ခန့်ရှိသော Junior Developer များအနေဖြင့် EC-CUBE Standard နှင့်အညီ စနစ်တကျ Log ရေးသားအသုံးပြုနိုင်စေရန် ရည်ရွယ်ပါသည်။

---

## မာတိကာ (Table of Contents)
1. [EC-CUBE Logging အကြောင်း အခြေခံသိထားသင့်သည့် အချက်များ (What Must Be Known)](#၁-eccube-logging-အကြောင်း-အခြေခံသိထားသင့်သည့်-အချက်များ)
2. [Log Levels သတ်မှတ်ချက်များ (PSR-3 RFC 5424)](#၂-log-levels-သတ်မှတ်ချက်များ)
3. [EC-CUBE Log Format ၏ ဖွဲ့စည်းပုံ (Log Formatter)](#၃-eccube-log-format-၏-ဖွဲ့စည်းပုံ)
4. [EC-CUBE Standard အတိုင်း Log ရေးသားနည်းများ (Implementation Methods)](#၄-eccube-standard-အတိုင်း-log-ရေးသားနည်းများ)
   - နည်းလမ်း (၁): Controller နှင့် Service များတွင် Constructor Injection ဖြင့် Log ရေးသားခြင်း (Standard & Recommended)
   - နည်းလမ်း (၂): Event Subscriber တွင် Hook လုပ်၍ Log မှတ်သားခြင်း
   - နည်းလမ်း (၃): Feature သီးသန့်အတွက် Custom Log File & Channel ခွဲထုတ်ခြင်း (ဥပမာ - `pos.log`, `payment.log`)
5. [Exception နှင့် Error များကို စနစ်တကျ Log ရေးသားနည်း](#၅-exception-နှင့်-error-များကို-စနစ်တကျ-log-ရေးသားနည်း)
6. [လိုက်နာရမည့် စည်းမျဉ်းများနှင့် ရှောင်ကြဉ်ရမည့်အချက်များ (Dos & Don'ts)](#၆-လိုက်နာရမည့်-စည်းမျဉ်းများနှင့်-ရှောင်ကြဉ်ရမည့်အချက်များ)
7. [Terminal Commands များဖြင့် Log စစ်ဆေးနည်း](#၇-terminal-commands-များဖြင့်-log-စစ်ဆေးနည်း)

---

## ၁. EC-CUBE Logging အကြောင်း အခြေခံသိထားသင့်သည့် အချက်များ

### ၁.၁ Symfony & Monolog (PSR-3) အခြေခံခြင်း
- EC-CUBE 4.3 သည် PHP အဆင့်မီ စံသတ်မှတ်ချက်ဖြစ်သော **PSR-3 Logger Interface (`Psr\Log\LoggerInterface`)** နှင့် Symfony Monolog Bundle ကို အသုံးပြုထားသည်။
- Core PHP standard ဖြစ်သော `file_put_contents()`, `error_log()` သို့မဟုတ် `var_dump()` / `die()` များကို Production code တွင် မည်သည့်အခါမျှ အသုံးမပြုရပါ။

### ၁.၂ Log ဖိုင်များ သိမ်းဆည်းရာ တည်နေရာ (Log Channels & Storage)
Log ဖိုင်များကို `var/log/<environment>/` အောက်တွင် နေ့စွဲအလိုက် အလိုအလျောက် ခွဲထုတ်သိမ်းဆည်းပေးပါသည်-

| Log ဖိုင်အမည် | သက်ဆိုင်သည့် ဧရိယာ (Channel) | အသုံးပြုသည့် ရည်ရွယ်ချက် |
| :--- | :--- | :--- |
| `front-YYYY-MM-DD.log` | `front` | Front-end မျက်နှာပြင် (Customer များ၏ ဝင်ရောက်မှု၊ ဈေးဝယ်မှု၊ Cart စသည်) |
| `admin-YYYY-MM-DD.log` | `admin` | Admin Panel (စီမံခန့်ခွဲသူများ၏ ဝင်ရောက်မှု၊ Product/Order ပြင်ဆင်မှု စသည်) |
| `site-YYYY-MM-DD.log` | `site` / `app` | Console commands, Batch jobs, Cron tasks နှင့် General fallback |

### ၁.၃ Environment အလိုက် အလုပ်လုပ်ပုံ ကွာခြားချက် (`dev` vs `prod`)
- **Development Environment (`dev`):**
  - Developer များ အလွယ်တကူ Debug လုပ်နိုင်ရန် Log Level အားလုံး (`DEBUG` အဆင့်အထိ) ကို `var/log/dev/site.log` တွင် တိုက်ရိုက် ထုတ်ပေးသည်။
- **Production Environment (`prod`):**
  - စနစ်စွမ်းဆောင်ရည် (Performance) နှင့် Disk Space မကုန်စေရန် **`fingers_crossed` handler** ကို အသုံးပြုထားသည်။
  - ပုံမှန်အခြေအနေတွင် `INFO` နှင့် အထက် Level သာ မှတ်သားပြီး၊ `ERROR` သို့မဟုတ် `CRITICAL` ဖြစ်ပေါ်မှသာ ထို Request မတိုင်မီ buffer လုပ်ထားသော debug log များကို တစ်ပြိုင်နက် ဖိုင်ထဲသို့ ချရေးပေးသည်။

---

## ၂. Log Levels သတ်မှတ်ချက်များ

PSR-3 စံနှုန်းအရ အခြေအနေပေါ်မူတည်၍ အောက်ပါ Log Levels များကို ခွဲခြားအသုံးပြုရမည်-

```
EMERGENCY (800) -> စနစ်တစ်ခုလုံး လုံးဝပြိုလဲသွားခြင်း
ALERT     (700) -> ချက်ချင်းအရေးယူ ဖြေရှင်းရမည့် အခြေအနေ
CRITICAL  (600) -> စနစ်အစိတ်အပိုင်း ပြတ်တောက်သွားခြင်း (ဥပမာ - Payment Gateway မရတော့ခြင်း)
ERROR     (400) -> Runtime error များ (မဖြစ်မနေ စစ်ဆေးပြင်ဆင်ရမည့် error များ)
WARNING   (300) -> ပုံမှန်မဟုတ်သော သတိပေးချက်များ (ဥပမာ - Deprecated function သုံးထားခြင်း)
NOTICE    (250) -> သာမန်ဖြစ်သော်လည်း မှတ်သားထိုက်သော အခြေအနေများ
INFO      (200) -> စနစ်၏ အရေးပါသော ပုံမှန်ဖြစ်စဉ်များ (User Login, Order Placement)
DEBUG     (100) -> Developer စမ်းသပ်စဉ်သာ လိုအပ်သော အသေးစိတ် Data များ
```

---

## ၃. EC-CUBE Log Format ၏ ဖွဲ့စည်းပုံ

EC-CUBE တွင် `eccube.log.formatter.line` ဖြင့် Log တစ်ကြောင်းချင်းစီကို အောက်ပါအတိုင်း ရှင်းလင်းစွာ အလိုအလျောက် Format ချပေးထားသည်-

```text
[datetime] channel.level_name [session_id] [uid] [user_id] [class:function:line] - message context [http_method, url, ip, referrer, user_agent]
```

### အလိုအလျောက် ပါဝင်လာသော အချက်အလက်များ (Processors):
1. **`SessionProcessor`**: လက်ရှိ Request ၏ Session ID
2. **`TokenProcessor`**: Login ဝင်ထားသော Customer/Admin User ID
3. **`UidProcessor`**: ထို Request တစ်ခုလုံးကို ကိုယ်စားပြုသည့် သီးသန့် Unique Request ID
4. **`IntrospectionProcessor`**: Log ထွက်လာသော Class အမည်၊ Function အမည်နှင့် Line Number
5. **`WebProcessor`**: HTTP Method (GET/POST), URL လမ်းကြောင်း, Client IP, Referrer, User-Agent

ထို့ကြောင့် Developer ဘက်မှ IP၊ URL၊ Line Number များကို ကိုယ်တိုင် String ရေးထည့်ရန် မလိုအပ်ပါ။

---

## ၄. EC-CUBE Standard အတိုင်း Log ရေးသားနည်းများ

> **စည်းမျဉ်းသတိပေးချက်:**  
> Core files (`src/Eccube/`) များကို တိုက်ရိုက် မပြင်ရပါ။ အားလုံးကို `app/Customize/` သို့မဟုတ် `app/Plugin/<PluginCode>/` အောက်တွင်သာ ရေးသားရပါမည်။

---

### နည်းလမ်း (၁) - Controller နှင့် Service များတွင် Constructor Injection ဖြင့် ရေးသားခြင်း (Standard & Recommended)

Symfony ၏ Dependency Injection စနစ်ဖြင့် `Psr\Log\LoggerInterface` ကို Inject လုပ်ပြီး အသုံးပြုသော စံနည်းလမ်း ဖြစ်သည်။

#### ဖိုင်တည်နေရာ: `app/Customize/Controller/ProductHistoryController.php`

```php
<?php

namespace Customize\Controller;

use Eccube\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductHistoryController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor Injection ဖြင့် LoggerInterface ကို ချိတ်ဆက်ယူခြင်း
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/custom/product/history", name="custom_product_history")
     */
    public function index(): Response
    {
        // 1. Info Level Log ရေးသားခြင်း (Context Array ဖြင့် Data များ ပို့ပေးရပါမည်)
        $this->logger->info('Viewed product history page.', [
            'action' => 'index',
            'customer_id' => $this->getUser() ? $this->getUser()->getId() : 'guest',
        ]);

        // 2. Debug Level Log ရေးသားခြင်း (စမ်းသပ်စဉ်သာ လိုအပ်သော အသေးစိတ် Data)
        $this->logger->debug('Product history query parameters initialized.', [
            'limit' => 10,
            'page' => 1,
        ]);

        return new Response('Product history loaded.');
    }
}
```

---

### နည်းလမ်း (၂) - Event Subscriber တွင် Hook လုပ်၍ Log မှတ်သားခြင်း

EC-CUBE ၏ အဖြစ်အပျက်တစ်ခုခု (ဥပမာ - Customer မှ Order အပြီးသတ် ချေယူသွားခြင်း) ကို စောင့်ကြည့်ပြီး သီးခြား Log မှတ်လိုပါက Event Subscriber ကို အသုံးပြုရသည်။

#### ဖိုင်တည်နေရာ: `app/Customize/EventListener/OrderLogSubscriber.php`

```php
<?php

namespace Customize\EventListener;

use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderLogSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * ဘယ် Event ကို စောင့်နားထောင်မည်ကို သတ်မှတ်ခြင်း
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EccubeEvents::FRONT_SHOPPING_COMPLETE_INITIALIZE => 'onOrderComplete',
        ];
    }

    /**
     * Order Complete ဖြစ်ချိန်တွင် အလုပ်လုပ်မည့် Function
     */
    public function onOrderComplete(EventArgs $event): void
    {
        /** @var \Eccube\Entity\Order $Order */
        $Order = $event->getArgument('Order');

        if ($Order) {
            // အရေးကြီးသော အရောင်းအဝယ်ဖြစ်စဉ်ကို INFO အဆင့်ဖြင့် မှတ်သားခြင်း
            $this->logger->info('Shopping complete successfully.', [
                'order_id'   => $Order->getId(),
                'order_no'   => $Order->getOrderNo(),
                'total'      => $Order->getPaymentTotal(),
                'customer_id'=> $Order->getCustomer() ? $Order->getCustomer()->getId() : null,
            ]);
        }
    }
}
```

---

### နည်းလမ်း (၃) - Feature သီးသန့်အတွက် Custom Log File & Channel ခွဲထုတ်ခြင်း

ဥပမာ - မိမိဖန်တီးထားသော POS System သို့မဟုတ် Payment Module အတွက် `var/log/dev/pos.log` သို့မဟုတ် `var/log/prod/payment.log` ဟု သီးသန့် ဖိုင်ခွဲထုတ်သိမ်းဆည်းလိုပါက အောက်ပါအတိုင်း ပြင်ဆင်နိုင်သည်။

#### အဆင့် (၁): Monolog Configuration တွင် Channel အသစ် ထည့်သွင်းခြင်း
ဖိုင်တည်နေရာ: `app/config/eccube/packages/dev/monolog.yml` (သို့မဟုတ် Plugin ရေးသားပါက Plugin ၏ config တွင် ထည့်နိုင်သည်)

```yaml
monolog:
    channels: ['pos']
    handlers:
        pos_rotating_file:
            type: rotating_file
            max_files: 30
            path: '%kernel.logs_dir%/%kernel.environment%/pos.log'
            level: debug
            channels: ['pos']
            formatter: eccube.log.formatter.line
```

#### အဆင့် (၂): Service တွင် သက်ဆိုင်ရာ Channel Logger ကို Inject ပြုလုပ်ခြင်း
ဖိုင်တည်နေရာ: `app/Customize/Service/PosIntegrationService.php`

```php
<?php

namespace Customize\Service;

use Psr\Log\LoggerInterface;

class PosIntegrationService
{
    /**
     * @var LoggerInterface
     */
    private $posLogger;

    /**
     * Symfony သည် monolog.logger.<channel_name> အမည်ဖြင့် autowire လုပ်ပေးနိုင်သည်
     */
    public function __construct(LoggerInterface $posLogger)
    {
        $this->posLogger = $posLogger;
    }

    public function syncTransactions(): void
    {
        // ဤ Log သည် pos.log ထဲသို့သာ သီးသန့် ရောက်ရှိသွားမည်ဖြစ်သည်
        $this->posLogger->info('POS transaction sync started.', [
            'timestamp' => time(),
        ]);
    }
}
```

---

## ၅. Exception နှင့် Error များကို စနစ်တကျ Log ရေးသားနည်း

Error တစ်ခုဖြစ်ပေါ်လာပါက `$e->getMessage()` သက်သက်ကို String အဖြစ် ရေးထည့်ခြင်းထက် Monolog standard အတိုင်း `['exception' => $e]` key ဖြင့် ပေးပို့ရမည်။ သို့မှသာ Stack Trace တစ်ခုလုံးကို Log တွင် သေသပ်စွာ တွေ့မြင်နိုင်မည် ဖြစ်သည်။

```php
try {
    // အန္တရာယ်ရှိသော သို့မဟုတ် ပြင်ပ API ခေါ်ယူသည့် လုပ်ဆောင်ချက်
    $result = $this->externalApiService->sendPaymentData($payload);

} catch (\Throwable $e) {
    // စနစ်တကျ Error Log ရေးသားခြင်း
    $this->logger->error('External API payment request failed.', [
        'exception' => $e,                       // Monolog မှ Stack trace ကို အလိုအလျောက် ထုတ်ပေးမည်
        'error_code' => $e->getCode(),
        'order_id' => $payload['order_id'] ?? null,
    ]);

    // အသုံးပြုသူထံ User-friendly Error message ပြသခြင်း
    $this->addError('ငွေပေးချေမှု စနစ်နှင့် ချိတ်ဆက်ရာတွင် ချို့ယွင်းချက် ဖြစ်ပေါ်နေပါသည်။');
}
```

---

## ၆. လိုက်နာရမည့် စည်းမျဉ်းများနှင့် ရှောင်ကြဉ်ရမည့်အချက်များ (Dos & Don'ts)

| အလေ့အကျင့်ကောင်းများ (DOs) | ရှောင်ကြဉ်ရမည့်အချက်များ (DON'Ts) |
| :--- | :--- |
| **Context Array ဖြင့်သာ Data ပို့ပါ:** <br>`$this->logger->info('User logged in', ['id' => $id]);` | **String Concatenation ဖြင့် မရေးပါနှင့်:** <br>`$this->logger->info('User logged in with id ' . $id);` (Log Parser များ ရှာဖွေရခက်ခဲစေသည်) |
| **Exception Object ကို ပေးပို့ပါ:** <br>`['exception' => $e]` ထည့်ပေးခြင်းဖြင့် ဘယ်ဖိုင် ဘယ်လိုင်းတွင် အမှားဖြစ်သည်ကို တိကျစွာ သိနိုင်သည်။ | **Message သာ ထည့်ပြီး Trace မထည့်ခြင်း:** <br>`$this->logger->error($e->getMessage());` (Root Cause ရှာရခက်စေသည်) |
| **Dependency Injection (DI) ကို သုံးပါ:** <br>Class Constructor မှ `LoggerInterface` ကို တောင်းယူသုံးစွဲပါ။ | **`new Logger()` သို့မဟုတ် Global Object ဆွဲမထုတ်ပါနှင့်။** |
| **Log Levels များကို ခွဲခြားအသုံးပြုပါ:** <br>Debug အတွက် `debug()`, အရေးကြီးဖြစ်စဉ်အတွက် `info()`, ပြဿနာအတွက် `error()`။ | **Log အားလုံးကို `info()` သို့မဟုတ် `error()` တစ်မျိုးတည်း မသုံးပါနှင့်။** |

### လုံခြုံရေးဆိုင်ရာ အထူးသတိပေးချက် (Security & Privacy Warning)
> [!CAUTION]
> Customer များ၏ **Password (စကားဝှက်)**, **Credit Card Number**, **CVV**, **Personal Secret Tokens / API Keys** များကို Log Message ထဲတွင် သော်လည်းကောင်း၊ Context Array ထဲတွင် သော်လည်းကောင်း **လုံးဝ (လုံးဝ) ထည့်သွင်းမှတ်သားခြင်း မပြုရပါ**။ ၎င်းသည် GDPR နှင့် လုံခြုံရေးဥပဒေများကို ချိုးဖောက်ရာ ရောက်ပါသည်။

---

## ၇. Terminal Commands များဖြင့် Log စစ်ဆေးနည်း

### ၇.၁ Cache ရှင်းလင်းခြင်း (Clear Cache)
Configuration များ သို့မဟုတ် Service အသစ်များ ရေးသားပြီးပါက Cache ရှင်းပေးရမည်-

```bash
bin/console cache:clear --no-warmup
```

### ၇.၂ Real-time Log စောင့်ကြည့်ခြင်း (Live Monitoring)
Terminal တွင် Request များ ဝင်ရောက်လာချိန် Log များကို တိုက်ရိုက် ကြည့်ရှုလိုပါက `tail -f` command ကို အသုံးပြုနိုင်သည်-

```bash
# Development site log ကို တိုက်ရိုက် စောင့်ကြည့်ခြင်း
tail -f var/log/dev/site.log

# လိုအပ်ပါက အောက်ပါအတိုင်း လိုင်းရေ သတ်မှတ်ကြည့်ရှုနိုင်သည် (ဥပမာ - နောက်ဆုံးလိုင်း ၁၀၀)
tail -n 100 -f var/log/dev/site.log
```

### ၇.၃ သီးသန့် အမှား (Error) များကိုသာ ရှာဖွေခြင်း
Log ဖိုင်အတွင်းမှ `ERROR` သို့မဟုတ် `CRITICAL` များကိုသာ သီးသန့် ထုတ်ယူကြည့်လိုပါက-

```bash
grep -iE "CRITICAL|ERROR" var/log/dev/site.log
```
