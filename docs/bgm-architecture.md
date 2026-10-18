# Original implementation design

Decision date: 18 October 2026. Status: design for goal 002, not implemented or runtime-qualified.

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

The current source exposes `HtmlHelper::addTransform` and `HtmlElement` attributes as a structured class boundary, but its tag registration is global and is not inherently narrower than a View loader. The prototype candidate combines it with a path-scoped `View::addLoader` wrapper. Every View call pushes a context entry: an exact allowlisted native template plus finite BGM values, or an explicit inactive entry for every other template. Use `try/finally` to pop the context on success or failure. A structured callback must return without changing anything unless the top context is active and an exact BGM sentinel token from that invocation is present. Nested unrelated templates must push an inactive entry, rather than inherit the parent's active scope.

The scoped wrapper adapts only verified BGM values in render-local parameters where native card/tile branches need native-shaped sentinels. The structured hook may then remap their exact generated colour class tokens before HTML serialisation. Register callbacks before elements are constructed and verify every actual native tag, including dynamic headings and linked containers. Never perform global string replacements over HTML, user text, URLs or script/style bodies. Preserve every native structural class and every stock nested primary/secondary control. Do not mutate saved nodes during rendering or copy vendor element templates. Prove out-of-scope byte equality, inactive nested context, exception cleanup, repeated rendering and transform coexistence before selecting this candidate.

Goal 002 must first prove this adapter on section, panel/card, button and a nested multi-item control. Explicitly handle native linked-card hover conditions, which whitelist stock colour values, and inverse LESS extensions at the actual surface. Add/preserve native `uk-card-hover` as the behaviour trigger alongside the custom BGM colour class; do not rename or replace this native trigger. The structured adapter remains a prototype requiring adversarial and runtime proof. If the hooks cannot meet all invariants safely, record the specific conflict rather than quietly relaxing the prefix requirement.

## Styling and cache lifecycle

Register original `bgm-*` LESS components and style-editor configuration through YOOtheme's supported module APIs. Write original styles from required properties and states. Do not copy the old plugin's component files or UIkit/YOOtheme component bodies. Integrate native style variables by reference where appropriate, while BGM-owned variables begin `@bgm-`.

Cover component background/text colours, buttons' hover/active/focus states, card hover, links, borders and inverse contexts where the legacy feature supported them. Accessible focus must survive. Do not set stock primary/secondary variables globally to implement the extra families. Follow the native compilation/import path, including frontend use after saving and reopening the customizer.

Use a version/config fingerprint to request recompilation only when needed through supported style configuration. Do not recursively delete template caches, purge unrelated content or force a rebuild on every request. Disable/uninstall must leave stock pages functional and remove only BGM's registered integration/assets. Saved BGM choices must be preserved rather than destructively rewritten, with their post-disable fallback documented from actual behaviour.

## Packaging and release

Build a deterministic ZIP with the manifest at its root, no outer GitHub source-directory wrapper, and only required runtime files. Keep build/release metadata in one source of truth. Validate file inventory, XML, version consistency, namespaces and checksums before native installation. Provide an explicit installation/download link only when the build actually exists.

Use a GPL-compatible licence for original Joomla plugin code, with BGM attribution. Keep proprietary/vendor reference files and original archives outside the distribution. Distribution/update decisions are described in [the release plan](bgm-release-plan.md).

## Acceptance boundary

The initial source target is YOOtheme Pro 5.0.52. The local reference site reports Joomla 6.2.0, which is an installed snapshot rather than proof of the latest supported Joomla release. PHP CLI is 8.3.11. Do not equate those source versions with a tested compatibility matrix. Determine the supported minimums and actual executable matrix in the staging goal before writing manifest/update claims.

Release readiness requires the [staging acceptance gates](bgm-staging-plan.md), an independent adversarial pass, Claude review, bounded fixes and verified commit/push. Source-contract discovery alone satisfies none of those runtime gates.
