# BGM YOOtheme integration contract

Goal 001 discovery, 18 October 2026. Proposed original design, grounded in static local YOOtheme source. No compatibility certification or runtime acceptance is implied by this document.

## Observed target

Inspected source root: `/Users/damian/Sites/localhost/edengateway.com.au/templates/yootheme`.

`templateDetails.xml` declares **5.0.52**. This was the highest version found in the bounded inventory of `*/templates/yootheme/templateDetails.xml` beneath `/Users/damian/Sites/localhost`. The controller separately verified 5.0.52 against the [official changelog](https://yootheme.com/changelog), dated 14 October 2026 and checked 18 October 2026. Client site files were read only. Installed source has not been compared to a pristine vendor package.

All paths below are relative to this source root unless otherwise stated.

## Verified static hook surface

| Concern | Observed contract | Evidence |
| --- | --- | --- |
| Joomla boot order | YOOtheme's system plugin subscribes to `onAfterInitialise` with HIGH priority, then loads template bootstrap files. | Adjacent site `plugins/system/yootheme/src/YOOtheme.php:15-26` |
| Context | YOOtheme boots for site requests and selected administrator component/view contexts, including AJAX, content, template and module editing. | `template_bootstrap.php:8-35` |
| Application | Singleton Application and `load(string $files)` remain available. Module config loaders are processed by named keys. | `packages/application/src/Application.php:49-53,102-123` |
| Builder schema | `ElementType::jsonSerialize()` resolves the type to an array and emits `builder.type|filter`. | `packages/builder/src/Builder/ElementType.php:77-95` |
| Style UI | StylerConfig is registered through module `config`; its service extends ConfigObject. | `packages/styler/bootstrap.php:12-14,36-38`, `packages/styler/src/StylerConfig.php:10-29` |
| Config merge | Config keys shaped as class names ending in Config are collected for that service; the resolved ConfigObject receives a recursive merge. | `packages/application/src/Application/ConfigLoader.php:51-77` |
| Component LESS | `theme.styles.components` files are resolved into the style import map and appended to the selected theme's LESS imports. | `packages/styler/src/Listener/LoadStylerImports.php:65-74` |
| View wrapper | `View::addLoader` without a filter adds a loader receiving template name, parameters and the next loader. `render` merges inherited parameters and calls the loader stack. | `packages/view/src/View.php:163-174,217-236` |
| Structured HTML hook | HtmlHelper supports per-tag transforms. HtmlElement passes the structured element and render params to that transform before tag rendering. Class expressions can be evaluated with the native public `HtmlElement::expr`. | `packages/view/src/View/HtmlHelper.php:32-39,118-140`, `packages/view/src/View/HtmlElement.php:79-91,176-235` |
| Builder rendering | Builder renders a registered type's template with the node's parameters, including node and props, through View. | `packages/builder/src/Builder.php:116-143` |
| Alternative node transform | `addTransform` accepts context/callable/offset. `render` transforms run after children. This mutates the in-memory render node. | `packages/builder/src/Builder.php:246-255,262-305` |

## Preferred original design

Build a modern Joomla system plugin named **BGM YOOTheme Colors Extended**. Use a DI service provider and namespaced plugin class. Subscribe after YOOtheme's HIGH-priority initialiser, and load a BGM module only when its actual Application/Styler/Builder integration is available. Missing YOOtheme must not break unrelated Joomla requests. Do not load another site's template or assume a fixed template directory merely to discover its version.

Use one explicit schema map for supported native type/field combinations. Preserve every stock option and append BGM options once. Do not recursively alter arbitrary fields or third-party element schemas. Initial parity scope is the legacy element list, with each current field checked before it is changed. Different fields called `text_color` are not necessarily colour palettes: section, column, gallery and map controls mean forced light/dark text, so they must be excluded from palette appending despite the legacy filter's broad behaviour. Gallery explicitly describes light/dark colour mode (`gallery/element.php:719-727`), while map describes light/dark text under a transparent navbar (`map/element.php:291-300`), beneath `packages/builder/elements`.

All newly persisted palette choices should begin `bgm-`, such as `bgm-tertiary`, `bgm-card-tertiary` and `bgm-tile-tertiary`. All custom CSS selectors, LESS variables, module keys, asset handles and package basenames should begin `bgm-`. PHP class names and namespaces must follow Joomla/PSR requirements because PHP identifiers cannot contain hyphens. Native API keys, UIkit classes/properties and Joomla language identifiers retain their required external spelling. These are documented integration exceptions, not justification for arbitrary unprefixed BGM identifiers.

Keep original LESS limited to the four extra slots, defining BGM globals plus component values for background, text, title, hover, active, disabled and colour mode. Expose them through a BGM-named StylerConfig group. Use a small, original mixin/slot strategy rather than reproducing the legacy repeated component files. Stock style variables remain owned by YOOtheme. No legacy licence API, download-key field, telemetry or vendor endpoint belongs in the BGM plugin.

### Literal BGM class prefix and native templates

Native templates construct classes by concatenating UIkit prefixes and prop values. Directly appending a value such as `bgm-tertiary` would otherwise produce `uk-section-bgm-tertiary`, which fails the requested literal BGM class prefix.

Prototype a **scoped render adapter** using the verified View loader plus HtmlHelper structured element transform. The HtmlHelper hook is global per tag and is not safe merely because it works on structured attributes. Its use is conditional on the explicit narrowing and coexistence gates below:

1. Wrap every View invocation with a context-stack entry. Mark it active only for exact, verified native Builder template paths, explicitly supported field names and exact BGM option values from a finite table. Every other invocation pushes an inactive entry, including unrelated templates nested beneath an active one. Pop the entry with `try/finally`. The structured callback must not mutate attributes or replace markup unless the top context is active and an exact known sentinel generated by that invocation is present. This guard narrows a shared per-tag hook; per-tag registration alone is insufficient.
2. Work on the local render parameter array, not saved builder data or the original node. Translate BGM values into temporary native-shaped sentinel values where structural template branches require them. For example, a BGM card option can become a `card-bgm-*` sentinel only inside the native template invocation. Explicitly handle both the node's `props` and the parent's inherited `element` parameter where the verified template uses that key. Never recursively transform arbitrary parameter arrays.
3. Call the native next loader with the adapted render parameters. Reuse native rendering, media, grid, accessibility and link logic without copying templates.
4. At the structured HtmlHelper transform hook, resolve only the class expression with native `HtmlElement::expr`, then map exact allowlisted class tokens generated from those unique sentinels into `bgm-*` tokens. Never replace normal native `uk-card-primary`/`uk-button-primary` classes, because nested normal elements must remain intact. Do not parse or rewrite raw HTML, JavaScript, text nodes or arbitrary attributes.
5. Pass all other templates, props and output through unchanged. Preserve native structural classes such as `uk-card`, `uk-card-body`, `uk-button`, and native attributes. Unknown future structures fail closed to ordinary rendering and a bounded administrator diagnostic rather than a fatal error or speculative rewrite.

This is a design proposal, not yet a verified implementation. It improves on an output HTML parser by using native structured attrs and native class-expression evaluation, but the following constraints must be settled in the prototype:

- Register each needed tag transform before native `el()` creation, because `HtmlHelper::el` selects whether the element gets a transform at creation time (`HtmlHelper.php:32-39`). There is no wildcard tag fallback in that method. Cover the verified configurable native output tags, including heading levels and custom section/column tags, rather than assuming only `div` and `a`.
- Prove inactive/no-sentinel callbacks make no changes to non-Builder output, unrelated nested templates and stock controls. Test context cleanup after a thrown exception and after nested render calls. All those outputs must remain byte-identical to native output under the same inputs.
- Return null immediately when no exact BGM-generated token exists.
- Do not store an evaluated class string back on a reusable HtmlElement and silently discard its original expression. Native templates reuse element objects across item loops. A second render with different props must recompute its original expressions.
- Returning a fully rendered replacement from a transform short-circuits subsequent transforms (`HtmlHelper.php:134-137`). If a local attrs copy plus `HtmlElement::tag` is used for safe BGM rendering, explicitly test and document transform coexistence rather than claiming transparent third-party compatibility. A mutation-based alternative must preserve expression reuse and downstream transforms.
- Preserve all non-class attributes and content. Avoid a DOM serialise/reparse pass over rendered markup.

### Component-specific semantics

| Component | Native behaviour to preserve | Evidence and requirement |
| --- | --- | --- |
| Section | Preserve media, overlap, sticky and preserve-colour behaviours. | `packages/builder/elements/section/templates/template.php:22-49` generates `uk-section-{style}` and conditional inverse/preserve classes. |
| Column | The style belongs on an internal panel, not necessarily the outer column. Card/tile shape controls layout and media treatment. | `packages/builder/elements/column/templates/template.php:25-79,244-247`. A wrapper marker alone on the outer column is insufficient. |
| Panel | Card/tile classes may belong on a link container inside the root. | `packages/builder/elements/panel/templates/template.php:58-78`. Rewrite the generated unique token at its native position. |
| Linked card hover | Native schema and template whitelist only certain native card styles. | `packages/builder/elements/panel/element.php:179-183` and `templates/template.php:62-63`. Extend only the known enable condition, and add/preserve the native `uk-card-hover` trigger when the BGM card and saved hover flag require it. The custom colour class is BGM-prefixed; the native behaviour trigger is not renamed. Original LESS can combine `.bgm-card-tertiary.uk-card-hover:hover`. Test linked and unlinked panels. |
| Button | Keep link/dialog semantics, sizing, base button class and disabled behaviour. | `packages/builder/elements/button_item/templates/template.php:6-34`. Custom buttons need hover, active, focus-visible and disabled acceptance. |
| Title/text | Apply the BGM colour where native title/text classes are generated, including inner title content. | `headline/templates/template.php:10-35`, `panel/templates/template-content.php:9-24`, `popover_item/templates/template-content.php:12-24`, `text/templates/template.php:8`, all beneath `packages/builder/elements`. |
| Inverse | Set native `--uk-inverse` and integrate light/dark rules at the correct container. Respect preserve-colour. | `vendor/assets/uikit/src/less/components/inverse.less:32-58`, `section.less:183-190`, `card.less:248-276`, `tile.less:165-176`. The legacy section implementation omits the modern inverse custom property. |

Do not assume a title marker on an element root can safely colour every descendant heading. Native templates put the title class on a specific heading and can nest content with independent colours. Exact generated-class replacement preserves that placement.

For inverse styles, setting `--uk-inverse` alone is insufficient: current native section/card/tile LESS also extends the applicable `.uk-light` or `.uk-dark` rules. BGM LESS must use the corresponding native inverse extension mechanism at the matching container, including the card-body/media boundaries and preserve-colour exclusions. Import order and compiled nested selectors require actual LESS compilation evidence.

### Further verified schema and inheritance facts

Paths in this paragraph are beneath `packages/builder/elements`. Additional confirmed selectors are `description_list.title_color` (`description_list/element.php:211-228`), `gallery.title_color` (`gallery/element.php:988-1004`), `grid.panel_style` and `grid.title_color` (`grid/element.php:619-636,778-794`), `grid_item.panel_style` (`grid_item/element.php:149-165`), `map.title_color` (`map/element.php:430-447`), `panel-slider_item.panel_style` (`panel-slider_item/element.php:143-159`), and `table.title_color` (`table/element.php:260-277`). These facts cover source selectors, not rendered feature acceptance.

Popover requires a deliberate correction to the legacy type list: its **parent** `popover` schema owns `card_style` (`popover/element.php:234-244`) and `title_color` (`popover/element.php:328-345`). The child `popover_item` owns content fields rather than those style selectors. The BGM schema map must target the parent and render its inherited values in the child. Listing only `popover_item`, as the legacy filter does, cannot expose those parent selectors on the inspected 5.0.52 source. Gallery owns overlay style (`gallery/element.php:648`) and title colour, but no panel/card style selector was found in that element file. Initial gallery scope is title colour only, not card/tile variants or a new overlay family.

Grid items take panel style from either their own props or the parent element, then use that merged `element` value in their structural rendering (`grid_item/templates/template.php:4,72-83`). Panel slider items follow the same pattern (`panel-slider_item/templates/template.php:4,57-69`). Parent types pass `element => props` when rendering children, including grid (`grid/templates/template.php:115,128`), gallery (`gallery/templates/template.php:126,139`), description list (`description_list/templates/template.php:22`) and table (`table/templates/template.php:104`). Popover item rendering applies `card_style` and `title_color` from its inherited element (`popover_item/templates/template.php:33,72-74`, `template-content.php:12-24`). Schema interpolation and inherited child templates must therefore be included in the prototype, not inferred from main element props alone.

## Acceptance matrix for implementation and staging

| Gate | Required evidence |
| --- | --- |
| Installer | Native Joomla ZIP installation, plugin registration/name/namespace, enable/disable, upgrade retaining settings, uninstall, and no fatal error with YOOtheme unavailable. |
| Style editor | Four BGM backgrounds and component values appear, save, survive reload and compile into the selected theme CSS. Check a non-default YOOtheme style and a child theme. |
| Schemas | All supported current type/field pairs show exactly four additional choices, eight for card/tile selectors. Stock choices remain. Unrelated and forced light/dark selectors remain unchanged. |
| Rendering | Section, column card/tile, panel/grid card/tile, gallery title colour, headline, text, button and popover use custom `bgm-*` class tokens at the correct native DOM element. No sentinel UIkit classes remain. |
| Placement | Nested stock elements retain native classes. Title colours affect the title only. Button groups and item templates keep their own colours. |
| States | Card/tile hover, button hover/active/focus-visible/disabled, light/dark inverse, media preserve-colour and nested inverse surfaces have expected computed styles. |
| Persistence | Editor preview and frontend agree after save/reload. BGM values survive in saved data and temporary render adaptations never persist. |
| Isolation | Plugin disabled returns ordinary native behaviour. No vendor template file changes, legacy endpoint calls, copied legacy code or unrelated data mutations. |
| Updates, if enabled | Approved accessible Joomla update XML references a reproducible release ZIP and matching extension identity, version and SHA-256. Joomla discovers and installs a staged version bump through its native updater. Otherwise document automatic updates as unconfigured, with manual ZIP delivery. |

## Remaining uncertainty and bounded follow-up

- Vendor-current 5.0.52 and the proposed Joomla/PHP staging lanes are recorded. Runtime execution of the pinned matrix is still required before implementation claims compatibility.
- The View/HtmlHelper adapter needs an actual native render test before being selected conclusively. Verify expression reuse, transform coexistence, configurable tags, title/subtemplate inheritance and customizer preview. If it cannot preserve these, use a bounded prototype comparison against the verified Builder transform surface, rather than repeated unbounded patching.
- The additional verified selectors above still need their complete field interpolation/subtemplate inheritance mapped before end-to-end support is claimed. Popover schema interpolation and card placement also need runtime verification.
- A separately approved, opt-in migration can later map legacy unprefixed choices and style variables. Automatic site-wide content/style rewriting is outside this proposed first release.
- GitHub updates require public release assets/feed visibility. A private repository by itself cannot serve unauthenticated Joomla updates. Do not embed a GitHub credential in an installer ZIP.

## Current source identity ledger

| Relative path | SHA-256 |
| --- | --- |
| `templateDetails.xml` | `56ed717ecb7201ef1a6cb8935ca1a2204f6aabf97f66a270c7c5da18fbac6a4b` |
| `template_bootstrap.php` | `44484e45d8507254ca8e4ebce8f0c774f52318057246858049d63cee8b5b68a4` |
| `packages/styler/src/Listener/LoadStylerImports.php` | `f417cf2cf21482a24cb59e2dfa0321a736944b4d16ac3bec4666b3cd0a266cad` |
| `packages/styler/bootstrap.php` | `99a5a3b7380ba242ea2361a47e881ac0753bdb99bec2a26a6e2d6591034421b2` |
| `packages/styler/config/styler.php` | `809bce7e29699de4a49899c09bb61c78c3e637a07fb1289981b12d59ed3c2fec` |
| `packages/styler/src/StylerConfig.php` | `01276aa884818362397fe0c7eaab493f00ec2ba5d741b28b0f167b5f834849f7` |
| `packages/builder/src/Builder/ElementType.php` | `0dc1a5b87654de4ebebea7e83bd46deadae05992f180714a4cd50b4be33cc8b8` |
| `packages/builder/src/Builder.php` | `a3eccace9c9da4a8e267ad79f464c9a8eefaf191d073110b9663d4c10c0963fa` |
| `packages/builder/bootstrap.php` | `7bdd6ae26579ebdba4ba6278913e7213e9887fddaea8cfe2e6d3dbc4d65f3854` |
| `packages/view/src/View.php` | `dd3ae7faa302f635852c24245ca232ba95f876549bb0867d3ab5143af8616dc8` |
| `packages/view/src/View/HtmlHelper.php` | `dc666c1897efb398621a8e4dc8bef30bdce60ab00cefd6ea81322deca42e61de` |
| `packages/view/src/View/HtmlElement.php` | `288b0575daa692830c5233073aa8416cc3924baebfbe65b64effa7a22f1cdac8` |
| `packages/view/bootstrap.php` | `f61e42372520b1f76566bfe7c347f9142fd89a143bf01fadd33c7041460b0b94` |
| `packages/application/src/Application.php` | `15b42093a5b2e0eeaba7a5759c962d6c781bc8c81d3ca26974826f70668742aa` |
| `packages/application/src/Application/ConfigLoader.php` | `851f9b09952c560951fdbd10737b2214a7d9a3dd24e8de782896cff24b590e2f` |
| `packages/application/src/Application/EventLoader.php` | `475f598bcdd6f6e2eb1d17c50399ad969e4cf4a9b1c3dae36d47d7504343b706` |
| `vendor/assets/uikit/src/less/components/inverse.less` | `6a7271406752a77309eb5c5cd6762698c7641f2d4e3461d2cd9638924985dc3c` |
| `vendor/assets/uikit/src/less/components/section.less` | `a33c957359a0af5103b8da45d57d2196e4b13390620dc90f8bbe648d9f00aa83` |
| `vendor/assets/uikit/src/less/components/card.less` | `9cb0eac9d0c6d774380f3f892ec47d72a63eb498195415ba91cec74b30a172c8` |
| `vendor/assets/uikit/src/less/components/tile.less` | `912c76933b350fa4b2fee40968733d57e14360f4dc9f51599a941b409c4200ec` |

## Next steps

Complete the Claude/adversarial design gate, then implement and test one bounded View/HtmlHelper/LESS integration prototype on the pinned staging lanes before building the release.
