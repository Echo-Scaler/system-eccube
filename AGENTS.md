# EC-CUBE Development & Customization Rules (EC-CUBE စည်းမျဉ်းများနှင့် လမ်းညွှန်ချက်များ)

## 1. Core Principles & EC-CUBE Law (EC-CUBE အဓိက လိုက်နာရမည့် ဥပဒေသများ)

1. **Never Modify Core Files Directly (Core ဖိုင်များကို တိုက်ရိုက် မပြင်ရ):**
   - Core files located under `src/Eccube/` and `vendor/` must NEVER be edited directly.
   - All modifications, extensions, and new features must be implemented strictly within `app/Customize/` or as a standalone Plugin in `app/Plugin/<PluginCode>/`.

2. **Entity Extension Standard (Entity ချဲ့ထွင်ခြင်း စည်းမျဉ်း):**
   - To add fields/relations to existing EC-CUBE entities (e.g., `Customer`, `Product`), use **Entity Traits** under `app/Customize/Entity/` (or `app/Plugin/<PluginCode>/Entity/`) with `@Eccube\EntityExtension("Eccube\Entity\<EntityName>")` annotation.
   - Custom standalone entities must be placed in `app/Customize/Entity/` or `app/Plugin/<PluginCode>/Entity/`.
   - After entity modifications, always generate proxy classes and update the schema:
     ```bash
     bin/console eccube:generate:proxies
     bin/console doctrine:schema:update --dump-sql
     bin/console doctrine:schema:update --force
     ```

3. **Template & UI Customization (Twig Template ပြင်ဆင်ခြင်း စည်းမျဉ်း):**
   - Never modify `src/Eccube/Resource/template/` directly.
   - Override templates by placing custom Twig templates in `app/template/<template_code>/` (e.g., `app/template/default/...`).
   - Use Event Listeners with `EccubeEvents::FRONT_..._RENDER` / `EccubeEvents::ADMIN_..._RENDER` or Twig template hook points for dynamic injection.

4. **Form & Validation Extension (Form Extension စည်းမျဉ်း):**
   - Extend existing forms using `Symfony\Component\Form\AbstractTypeExtension` under `app/Customize/Form/Extension/` or `app/Plugin/<PluginCode>/Form/Extension/`.
   - Implement `getExtendedTypes()` returning the target form class.

5. **Event Listeners & Hook Points (Event Listener စည်းမျဉ်း):**
   - Use Symfony `EventSubscriberInterface` or EC-CUBE Hook points for business logic injection instead of modifying controllers or services directly.
   - Place subscribers under `app/Customize/EventListener/` or `app/Plugin/<PluginCode>/EventListener/`.

6. **Service Customization & Dependency Injection (Service စည်းမျဉ်း):**
   - Custom services belong in `app/Customize/Service/` or `app/Plugin/<PluginCode>/Service/`.
   - If overriding existing core services, use Symfony Service Decoration via `app/Customize/Resource/config/services.yaml`.

7. **Coding Standards & Cache Management:**
   - Adhere to PSR-12 and EC-CUBE PHP CS Fixer rules.
   - Always clear cache after configuration, entity, or routing changes:
     ```bash
     bin/console cache:clear --no-warmup
     ```

---

## 2. Beginner-Friendly & Simplicity Principle (ရိုးရှင်းလွယ်ကူပြီး အတွေ့အကြုံ ၆ လ အဆင့် နားလည်နိုင်စေရန် ရေးသားခြင်း)

1. **Keep Customizations Simple & Practical (ရိုးရှင်းလွယ်ကူသော နည်းလမ်းကို ဦးစားပေးရန်):**
   - ရှုပ်ထွေးလွန်းသော Architecture (Over-engineering) များကို ရှောင်ရှားပြီး standard ဖြစ်သည့် EC-CUBE နည်းလမ်းအတိုင်း ရိုးရိုးရှင်းရှင်းနှင့် ထိရောက်စွာ ရေးသားရပါမည်။
   - Junior developer များ နားလည်လွယ်စေရန် Code တွင် လိုအပ်သော မှတ်ချက်များ (Comments) ကို မြန်မာ/အင်္ဂလိပ် ရောနှော၍ ရှင်းလင်းစွာ ထည့်သွင်းပေးရပါမည်။

2. **Junior-Level Accessible Explanations (အတွေ့အကြုံ နုနယ်သူ နားလည်လွယ်သော ရှင်းလင်းချက်များ):**
   - လုပ်ငန်းအတွေ့အကြုံ (၆) လ ခန့်ရှိသော Junior Developer များ အလွယ်တကူ လိုက်လုပ်နိုင်ရန် ရေးသားပေးရပါမည်။
   - အဆင့်တိုင်းတွင် **ဘာကြောင့် ဒီလိုလုပ်ရသလဲ (Why)**၊ **ဘယ်ဖိုင်တည်နေရာမှာ ရေးရမလဲ (File Path)**၊ **ဘယ် Command ကို run ရမလဲ (Terminal Commands)** များကို ရှင်းပြရပါမည်။
   - အပိုင်းအစများ မဟုတ်ဘဲ ပြည့်စုံပြီး copy-paste လုပ်ရုံဖြင့် အသုံးပြုနိုင်သော Code Snippets များကို ပံ့ပိုးပေးရပါမည်။

---

## 3. Documentation Language Rule (မြန်မာဘာသာဖြင့် မှတ်တမ်းတင်ခြင်း စည်းမျဉ်း)

1. **Implementation Documentation in Burmese (လုပ်ဆောင်ချက် အဆင့်ဆင့် လမ်းညွှန်များကို မြန်မာဘာသာဖြင့် ရေးသားရန်):**
   - All step-by-step implementation guides, walkthroughs, task list documents (e.g., in `Task-List-Folder/`), and developer documentation MUST be written in **Burmese (မြန်မာဘာသာ)**.
   - Technical terms (such as Entity, Repository, Controller, Service, Migration, Twig, Doctrine, EventSubscriber, Hook Point, Docker, Composer, console commands) must be retained in English alongside clear, beginner-friendly Burmese explanations.
   - Include clear step-by-step checklists, directory structures, code examples, and command explanations in Burmese so that developers can easily follow and verify.
