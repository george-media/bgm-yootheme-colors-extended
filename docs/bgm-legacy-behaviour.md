# BGM legacy behaviour evidence

Goal 001 discovery, 18 October 2026. This document describes the supplied package by static inspection. It does not claim a browser execution or installation test.

## Target and boundary

- Supplied archive: `/Users/damian/Downloads/plg_system_kickyoocolors_1.2.2.zip`.
- SHA-256: `966885e6edd5a3f76ab298e0532d341e31c0cce9b33b2b78524e3514afce6f35`.
- Inventory: 27 ZIP members, including directories, totalling 52,972 uncompressed bytes.
- Declared product/version: Kick Yoo Colors 1.2.2, manifest date 15 October 2024.
- Inspected directly from the ZIP. No legacy source was copied into this repository.
- Evidence notation below identifies archive member and one-based source line.

## Observed feature specification

The package adds four fixed colour slots named Tertiary, Quaternary, Quinary and Senary. It is a style extension to YOOtheme's existing elements, rather than an independent page builder or collection of new elements.

| Concern | Observed behaviour | Evidence |
| --- | --- | --- |
| Bootstrap | After Joomla initialisation, do nothing unless YOOtheme Application is already loaded. Otherwise register its namespace and load its YOOtheme module. | `kickyoocolors.php:60-81` |
| LESS integration | Register seven extra LESS component files for global variables, inverse, buttons, cards, sections, text and tiles. | `bootstrap.php:18-29` |
| Style editor | Merge extra global background, inverse button, section, card, tile and button groups into StylerConfig. | `bootstrap.php:32-34`, `config/styler.json:2-64` |
| Builder integration | Filter serialised element schemas through `builder.type`. | `bootstrap.php:36-39`, `src/FormListener.php:39-123` |
| Covered type names | `button_item`, `column`, `description_list`, `gallery`, `grid`, `grid_item`, `headline`, `map`, `panel`, `panel-slider_item`, `popover_item`, `section`, `table`, `text`. | `src/FormListener.php:22-37` |
| Simple style choices | If a listed type has `style`, `title_color`, `text_color`, `button_style` or `card_style` options, append the four slot values. | `src/FormListener.php:46-80,95-102` |
| Card/tile choices | Append eight `card-*` / `tile-*` choices to an existing `panel_style` selector. For `column.style`, append these eight instead of simple slot choices. | `src/FormListener.php:82-93,104-120` |
| Global defaults | Backgrounds default to `#f8f8f8`, `#1e87f0`, `#222`, `#222`, respectively. | `assets/less/variables.less:15-18` |
| Sections | Extra backgrounds plus light/dark inverse extensions, with preserve-colour support. | `assets/less/section.less:19-29,42-85` |
| Cards | Background, foreground, title colour, hover background and colour mode per slot. | `assets/less/card.less:19-41,50-148` |
| Buttons | Background/text, hover and active states per slot, shared disabled styling, plus inverse button styles. | `assets/less/button.less:17-43,52-159,179-300` |
| Tiles | Background, hover-background variable and colour mode per slot, with preserve-colour support. Actual hover rules have a defect described below. | `assets/less/tile.less:19-33,43-105,118-132` |
| Text | Four extra text utility colours, all defaulting to global muted colour. | `assets/less/text.less:11-23` |
| Installation | A Joomla system plugin, upgrade installation method. Installation enables the plugin by updating the extensions table. | `kickyoocolors.xml:2,16-24`, `script.php:53-55` |

The plugin does not provide colour naming, slot count configuration, migration, or an explicit export/import facility in its own manifest fields. YOOtheme owns the style customisation and builder data.

## Proven compatibility gate and other fragility

1. **Installer rejects YOOtheme versions above 4.5.99.** `helper.php:17-20` declares PHP 7.4+, Joomla 4.0+, YOOtheme 4.0.0–4.5.99. `helper.php:51-70` reads the fixed `templates/yootheme/templateDetails.xml` path and throws above the cap. `script.php:32-45` calls this validation for install/update. This proves a 5.x installation blocker without proving that every runtime hook has broken.
2. **The fixed theme path is fragile.** It assumes the template is installed under `templates/yootheme`, and reads its XML without first checking whether the file exists. The explicit failure branch tests null after dereferencing the result. Evidence: `helper.php:51-54`.
3. **A declared theme preinstall guard is not shown wired to an event.** `helper.php:73-93` defines a helper that rejects unsupported YOOtheme package versions. No call to it was observed in the supplied PHP members. Do not claim it blocks a YOOtheme upgrade in execution.
4. **Bootstrap depends on event ordering.** `kickyoocolors.php:63-65` returns when YOOtheme Application has not yet been loaded. The legacy plugin has no explicit event priority.
5. **Senary section dark mode has a copy/paste defect.** The final dark extension targets the tertiary selector and checks the tertiary variable, where the paired light extension uses senary. Evidence: `assets/less/section.less:84-85`.
6. **Tile hover background is not applied by the invoked hooks.** The selector calls `hook-tile-*-hover`, whose defaults are empty, but the background declarations are in separately named `hook-tile-*-hover-hover` mixins. Evidence: `assets/less/tile.less:48-50,109-132`. Static observation, not a computed-style test.
7. **Custom selectors and variables occupy unprefixed UIkit-style names.** Examples include `uk-card-tertiary` and `@global-tertiary-background`. A new BGM implementation should use BGM identifiers rather than retain this shared namespace.
8. **Listed element names do not guarantee all of their sub-elements are covered.** The filter operates only on fields of each allowlisted type. It does not recursively discover arbitrary nested controls.

## Commercial update and registration behaviour

These are properties of the legacy vendor's distribution, not required colour features. They should not be reproduced for BGM.

- Manifest update URL points to the legacy shop and declares a download key prefix (`kickyoocolors.xml:3,61-65`).
- Plugin edit/save handlers read and write the update site's extra query download key (`kickyoocolors.php:94-180`).
- An AJAX handler posts a website domain to the vendor API with a supplied Joomla token. It has no visible local authorisation or CSRF check, uses an unlimited cURL timeout, and renders returned error titles directly (`kickyoocolors.php:188-234`). This is a static concern, not a demonstrated exploit.
- An installer download hook appends the site's domain, Joomla version and PHP version to matching vendor package URLs (`kickyoocolors.php:241-256`).

## Member identity ledger

| Archive member | SHA-256 |
| --- | --- |
| `kickyoocolors.php` | `4de3036359c4f5e930a40a9de7e440bb004b54a065682ff502fdb18eff5f7b63` |
| `bootstrap.php` | `98cf5652c68d762f6b4b2acb88a5144fdb71dfb99d17aebe829c8e6a9aca76f2` |
| `helper.php` | `2a0f80d597a028bc597856c194a9342ca2e2dc17f341bab1194542d0226f73a8` |
| `src/FormListener.php` | `550b37ccfffb6c5e5f74be0a3d9458c2e9900259d975c63a31e9c291d4aab681` |
| `config/styler.json` | `225ff0b551ce8167871e179598176403399f89f678b654ee2cd36f340b337cfe` |
| `script.php` | `4492dc43a54775095d19ac8a6a0c6d4a72bf6c224257779234ed61e2c69414ae` |
| `kickyoocolors.xml` | `12948b3d235d5bdbbe503e2aa139ec9c044ca31c7c71d2c5c5011c2d7fa2123f` |

## Next steps

Implement original BGM code against the separate integration contract, then verify the feature matrix through Joomla installation and YOOtheme style compilation. Legacy-content migration needs its own explicit decision and tests.
