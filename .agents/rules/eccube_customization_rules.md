# EC-CUBE Customization & Development Standards

## 1. EC-CUBE Law & Architecture Rules

### Rule 1.1: Core Integrity Protection
- **STRICT PROHIBITION:** Do NOT modify any file inside `src/Eccube/` or `vendor/`.
- All custom code must reside within:
  - `app/Customize/` (for site-specific customizations)
  - `app/Plugin/<PluginCode>/` (for reusable plugins)

### Rule 1.2: Entity & Database Extension Rules
- Extend existing EC-CUBE entities using Entity Traits with `@Eccube\EntityExtension("Eccube\Entity\<TargetEntity>")`.
  - Traits location: `app/Customize/Entity/...Trait.php` or `app/Plugin/<PluginCode>/Entity/...Trait.php`.
- Create new custom entities inside `app/Customize/Entity/` or `app/Plugin/<PluginCode>/Entity/`.
- Repositories must extend `Eccube\Repository\AbstractRepository` or `Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository`.
- Database update procedure:
  ```bash
  bin/console eccube:generate:proxies
  bin/console doctrine:schema:update --dump-sql
  bin/console doctrine:schema:update --force
  ```

### Rule 1.3: Controller & Route Extension Rules
- Custom controllers must be placed in `app/Customize/Controller/` or `app/Plugin/<PluginCode>/Controller/`.
- Use Route annotations or attributes with unique route names and paths.

### Rule 1.4: Form Extension Rules
- Extend forms via `AbstractTypeExtension` placed in `app/Customize/Form/Extension/` or `app/Plugin/<PluginCode>/Form/Extension/`.
- Use `buildForm()` and declare targets via `getExtendedTypes()`.

### Rule 1.5: Event Subscribers & Hook Points
- Inject business logic using Symfony `EventSubscriberInterface` placed in `app/Customize/EventListener/` or `app/Plugin/<PluginCode>/EventListener/`.
- Use EC-CUBE render events (`EccubeEvents::FRONT_..._RENDER`, etc.) or lifecycle events.

### Rule 1.6: Twig Template Overrides
- Never modify `src/Eccube/Resource/template/`.
- Place override templates into `app/template/<template_code>/` (e.g. `app/template/default/`).

### Rule 1.7: Cache Management
- Clear cache whenever adding or modifying services, routes, entities, or event listeners:
  ```bash
  bin/console cache:clear --no-warmup
  ```

---

## 2. Beginner-Friendly & Simplicity Principle (ရိုးရှင်းလွယ်ကူပြီး အတွေ့အကြုံ ၆ လ အဆင့် နားလည်နိုင်စေရန် ရေးသားခြင်း)

### Rule 2.1: Keep It Simple & Avoid Over-Engineering
- Use the simplest, most standard EC-CUBE approach to solve the problem.
- Avoid unnecessary complex abstractions, multi-layered interfaces, or esoteric tricks that confuse junior developers.
- Add clear explanatory code comments for any logic that needs context.

### Rule 2.2: 6-Month Junior Developer Accessibility
- All step-by-step guides, instructions, and code samples must be easily understandable and executable by a developer with approximately 6 months of web development experience.
- Provide full, copy-paste-ready code snippets with precise file paths.
- Explicitly explain **Why (အဘယ်ကြောင့်)**, **Where (ဘယ်နေရာတွင်)**, and **How (မည်သို့)** for every action.

---

## 3. Documentation & Workflow Rules (မြန်မာဘာသာ သတ်မှတ်ချက်)

### Rule 3.1: Burmese Language Documentation (မြန်မာဘာသာဖြင့် ရေးသားရန်)
- All implementation plans, technical documentation, step-by-step installation/configuration guides, and task checklists (including files in `Task-List-Folder/` and `walkthrough.md`) MUST be created and written in **Burmese (မြန်မာဘာသာ)**.
- English technical terms (e.g. Entity, Repository, Controller, Service, Doctrine, Twig, Migration, Proxy, EventListener) should be kept in English alongside clear, beginner-friendly Burmese descriptions.
- Each implementation step must clearly list:
  1. **ဖိုင်တည်နေရာ (File Paths)**
  2. **ကုဒ်နမူနာနှင့် ရှင်းလင်းချက် (Code Snippets & Explanations)**
  3. **Run ရမည့် Command များ (Terminal Commands)**
  4. **စစ်ဆေးရမည့် အချက်များ (Verification Checklist)**
