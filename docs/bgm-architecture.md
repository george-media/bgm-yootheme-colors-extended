# Original implementation design

Decision date: 18 October 2026. Status: original 0.1.0 implementation, with bounded source and native staging evidence recorded in [goal 002](002-implementation.md). Full qualification remains goal 003. The initial loader-only candidate was rejected after actual API tests exposed its scope gap.

## Behaviour and scope

Build a new Joomla system plugin called **BGM YOOTheme Colors Extended**. Provide four additional semantic colour families, Tertiary, Quaternary, Quinary and Senary, editable in YOOtheme's style editor and selectable in applicable native Builder controls. Derive the exact controls and states from [the behavioural specification](bgm-legacy-behaviour.md), then verify them against [5.0.52's integration contract](bgm-integration-contract.md).

The feature set is based on observed behaviour, not on the original file structure or copied source. Do not carry across licensing callbacks, original vendor registration, automatic editing of another plugin's state or blind filesystem cache deletion. Do not import or rename old saved layouts automatically. A future migration would need a separate reversible operation and explicit acceptance evidence.

## Joomla boundary

Use element `bgm-yootheme-colors-extended` in system group `system`, with a matching root manifest `bgm-yootheme-colors-extended.xml`. Use a current `services/provider.php` provider that registers Joomla's native `PluginInterface` and returns the namespaced extension class through dependency injection. Register namespaced source through the manifest. The provider path and API identifiers are Joomla conventions.

Proposed namespace: `Bgm\Plugin\System\BgmYoothemeColorsExtended`. Our concrete PHP classes start with `Bgm`, for example `BgmYoothemeColorsExtended`, `BgmColourRegistry`, `BgmBuilderAdapter` and `BgmStyleListener`. Use PSR-matching filenames and native interface method signatures. PHP cannot parse kebab-case class or variable names. Joomla language constants use `PLG_SYSTEM_BGM_...` and framework-defined entry points retain their native spelling. These are explicit syntax/convention exceptions. Custom assets, custom settings, custom colour values, CSS classes and LESS variables use a `bgm-` prefix and kebab case.

Use Joomla's current installer-script interface/service-provider mechanism if a preflight script is necessary. Do not use the legacy convention which constructs a global installer class from a hyphenated element name. Prove the element, namespace registration and language resolution through the native installer early.

Load the BGM YOOtheme module after the native YOOtheme bootstrap is available, using supported event ordering and a capability check. Register once per request. In contexts where YOOtheme is absent or does not bootstrap, do nothing safely. Installation may remain possible while YOOtheme is absent, but display an actionable administrator status before enabling features. Do not fake compatibility by bootstrapping a customer template manually from an arbitrary path.

## Colour registry and Builder integration

Keep one small typed registry for the four families. It drives the option values, labels and expected custom CSS selectors so the server output and Builder choices cannot drift. Persist values such as `bgm-tertiary`, `bgm-card-tertiary` and `bgm-tile-quaternary` according to field semantics. Declare explicit element/field pairs from the current schema, and extend their option maps without replacing existing values, third-party additions or custom labels. Unknown element types and unrelated colour fields pass through.

Never walk and rewrite every arbitrary field which happens to contain the word `color`. Native inverse choices such as `light` and `dark` keep their native meaning. Extend the UI's relevant visibility/enable conditions as well as its option list where native controls restrict particular states to known styles.

Native templates construct `uk-*` colour class names. Simply passing a custom value through produces a custom class that does not begin with `bgm-`. The selected implementation must preserve native structure while emitting BGM-owned custom classes such as `bgm-section-tertiary`, `bgm-card-quaternary`, `bgm-button-tertiary` and `bgm-text-quaternary`.

The selected implementation uses YOOtheme's supported `services` loader to supply a `BgmView` subclass. It inherits the native render/evaluate implementation and preserves the standard View service extenders. `BgmModule` checks capability and timing before registration. It declines integration if the theme has already initialised or the View/StylerConfig service was already resolved, retaining the existing service and issuing a bounded administrator diagnostic. A later competing View factory suspends schema additions. Arbitrary replacement factories are outside the compatibility guarantee.

`BgmView::render` provides an unconditional inactive frame around every invocation, including filtered loaders which short-circuit, redirect, throw or postprocess. It merges inherited/native global parameters and restores only exact owned ancestor sentinels in the direct known `props`/`element` fields. `BgmView::evaluate` then activates only exact verified native Builder files after all loaders have selected the final path. Both frames use `try/finally`. On a native evaluation exception, the wrapper restores the entry template/parameter stacks and only buffers opened within that evaluation, then rethrows.

This boundary replaced the proposed `View::addLoader` wrapper. Actual `View::render` wraps unfiltered loaders with its filtered loaders, and later registered filters can bypass an outer wildcard as well. A loader alone could not satisfy the every-invocation gate. No vendor render method or template body is copied into the subclass.

The active evaluator adapts only finite registry choices into invocation-specific native-shaped sentinels. The structured HtmlHelper callback acts only when the top context is active and an exact generated class token matches. It clones the element, evaluates/maps the class while preserving its array/string shape, executes the remaining ordinary callbacks in order with native truthy short-circuit semantics, then serialises through native `HtmlElement::tag`. The original reusable expression remains intact. Other attributes/content and native structural classes keep their native rendering. Unrelated output is checked against native byte-for-byte controls.

Configurable native tags, including `hgroup`, are registered before element creation. Unknown selected tags suppress adaptation rather than leak internal sentinels. Native linked-card behaviour remains `uk-card-hover` beside the BGM colour class. Item inheritance, nested title subtemplates, repeated renders, later filtered/unfiltered loaders and actual evaluation exceptions have focused tests against 5.0.52.

The transform compatibility claim is bounded to ordinary per-render callbacks. Callbacks expecting their mutations to persist on the original reusable HtmlElement, changing the callback list during dispatch, or substituting a custom View factory are not advertised as transparently compatible. No raw HTML search/replace, saved-node mutation or vendor-template copy is used.

The parameter inheritance fallback deliberately matches native 5.0.52, including a direct empty evaluation frame falling back to globals. The service-resolution probe uses native `extend()`: an unresolved service retains an inert probe closure until it resolves. The extension's per-request load guard bounds registration. An unknown configurable tag suppresses adaptation for the complete evaluation invocation, including otherwise valid colour fields.

Every proposed YOOtheme upgrade must requalify all offered configurable tags against the fixed tag allowlist and recheck HtmlHelper's public `transforms` storage shape/visibility plus callback ordering, truthy short-circuit and replay semantics. These are version-specific integration contracts, not promises about future vendor internals.

## Styling and cache lifecycle

Register original `bgm-*` LESS components and style-editor configuration through YOOtheme's supported module APIs. Write original styles from required properties and states. Do not copy the old plugin's component files or UIkit/YOOtheme component bodies. Integrate native style variables by reference where appropriate, while BGM-owned variables begin `@bgm-`.

Cover component background/text colours, buttons' hover/active/focus states, card hover, links, borders and inverse contexts where the legacy feature supported them. Accessible focus must survive. Do not set stock primary/secondary variables globally to implement the extra families. Follow the native compilation/import path, including frontend use after saving and reopening the customizer.

Version 0.1.0 requires an explicit native style save after installation or update to compile BGM styles into existing CSS. It does not claim automatic cache invalidation or regeneration. BGM components are queued through the top-level native theme loader with a BGM-owned associative key, preserving native theme initialisation and numeric component entries from other extensions. Writing `config.theme` early was a confirmed prototype defect because it skipped native defaults, including consent configuration.

Do not recursively delete template caches, purge unrelated content or force a rebuild on every request. Complete automatic cache-lifecycle, disable/uninstall and saved-data fallback qualification in goal 003 before stable delivery.

## Packaging and release

Build a deterministic ZIP with the manifest at its root, no outer GitHub source-directory wrapper, and only required runtime files. Keep build/release metadata in one source of truth. Validate file inventory, XML, version consistency, namespaces and checksums before native installation. Provide an explicit installation/download link only when the build actually exists.

Use a GPL-compatible licence for original Joomla plugin code, with BGM attribution. Keep proprietary/vendor reference files and original archives outside the distribution. Distribution/update decisions are described in [the release plan](bgm-release-plan.md).

## Acceptance boundary

The initial source target is YOOtheme Pro 5.0.52. The local reference site reports Joomla 6.2.0, which is an installed snapshot rather than proof of the latest supported Joomla release. PHP CLI is 8.3.11. Do not equate those source versions with a tested compatibility matrix. Determine the supported minimums and actual executable matrix in the staging goal before writing manifest/update claims.

Release readiness requires the [staging acceptance gates](bgm-staging-plan.md), an independent adversarial pass, Claude review, bounded fixes and verified commit/push. Source-contract discovery alone satisfies none of those runtime gates.
