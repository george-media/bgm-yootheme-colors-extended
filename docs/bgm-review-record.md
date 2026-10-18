# Review record

The opening sections record goal 001 discovery/design reviews only. Goal 002 implementation and native staging results are recorded separately below. Neither section certifies the unfinished full qualification matrix.

## Independent Codex adversarial review

Read-only reviewer, separate from controller, engineer and staging operator. It inspected the design and matching installed YOOtheme 5.0.52 source.

| Finding | Evidence | Disposition |
| --- | --- | --- |
| A raw HTML output-rewrite adapter is needlessly broad when a structured hook exists. | `packages/view/src/View/HtmlHelper.php:118-140` and `HtmlElement.php:79-93`. | Prefer structured class handling before serialisation. No raw HTML parser/rewrite in the selected design. |
| Native card hover whitelists stock style values and misses BGM sentinels. | `packages/builder/elements/panel/templates/template.php:65-66`. Reviewer evaluated the actual native expression: stock `card-primary` included `uk-card-hover`; `card-bgm-tertiary` did not. | Explicit hover adaptation and state acceptance required. Expression evaluation proves this narrow behaviour only. |
| Inverse colour requires the native inverse LESS extension, beyond a custom property. | Current UIkit section/card/tile LESS inverse blocks. | Require `.uk-light`/`.uk-dark` extensions and preserve-colour tests. |
| Popover selectors are on the parent type, so the legacy child-only map is ineffective. | `popover/element.php:234,328`; child template reads inherited `element`. | Add explicit parent schema selectors and child inheritance acceptance. |
| Gallery has no native card/tile selector. | `gallery/element.php:648,988`, `gallery_item/templates/template.php:99,122`. | Limit initial gallery extension to title colour; remove unsupported card/tile scope. |
| Structured transforms can freeze reused expressions or short-circuit other transforms if implemented naively. | HtmlElement reuse and `HtmlHelper::applyTransform` return behaviour. | Repeated rendering, configurable-tag coverage and downstream-transform coexistence are mandatory prototype stop/go gates. |

The final review message identified no additional severe installer/update design issue. The two source-map corrections it still listed were subsequently incorporated in the integration contract and checked by the controller. A narrow follow-up reviewer readback returned PASS for those corrections and the accuracy of this summary. The actual adapter algorithm remains an implementation risk to resolve early in goal 002.

## Claude advisor

Bundled companion readiness passed in this process. Review job `advise-mvdcyyp6-ikwo9x` completed successfully with an authoritative provider-JSON result, verified cleanup and canonical session `664ce6a6-9d2a-423a-8d8f-c0df3d1bdaba`. It used read-only `--effort xhigh`, local Read/Glob/Grep scope, strict unattended MCP restrictions and no write/web permission. Progress/status output was not treated as review approval.

Claude identified two concrete design defects:

- **High:** HtmlHelper transforms are registered globally per tag, so structured attributes alone do not provide narrow Builder scope. The design now requires an explicit View context entry for every invocation, inactive entries for unrelated nested templates, exact invocation-specific BGM sentinels, `try/finally` cleanup and out-of-scope byte-equality tests. The candidate is conditional on proving these gates, rather than described as inherently safer by API choice.
- **Medium:** Native `uk-card-hover` is a behaviour trigger and should not be renamed to `bgm-card-hover`. The corrected contract adds/preserves the native trigger beside the BGM colour class and uses original compound CSS rules.

Claude confirmed the native inverse and panel whitelist observations. Its verdict allowed a bounded prototype after the text defects were resolved, without compatibility certification. Narrow correction-verification job `advise-mvdd43gt-nj6spz` then completed with an authoritative provider-JSON result, verified cleanup and canonical session `2bb00108-6600-428b-b272-d6ed907d6f64`. It returned a **conditional pass to start a bounded prototype**, confirming all seven scope, cleanup, sentinel, native-hover and prototype-gate corrections were consistent. This is text/design acceptance only, not implementation certification.

## Controller checks

Validated archive SHA-256 and 27 documented source/member digests against actual current inputs. Parsed JSON and checked relative Markdown links. Retained the REA inventory and closed its target. No legacy or vendor source, secrets, site databases, session state or executable staging processes were added to Git. [Verification output](../evidence/bgm-discovery-verification.json) describes this limited scope.

## Next steps

Commit/push the reviewed foundation. Goal 002 begins with the scoped structured adapter and native-installer prototype before broader feature implementation.

## Goal 002 implementation review

The engineer owned source, a separate reviewer remained read-only, and the staging operator owned all disposable state. The controller integrated evidence and owns the package/Git decision.

### Independent adversarial findings and repairs

The reviewer identified and verified repairs for missing nested title templates, an incorrect native hover enable-condition comparison, missing `hgroup`, third-party option-label collisions and ancestor sentinel leakage into unrelated nested views. Actual filtered-loader short-circuit tests then disproved the loader-only scope boundary, including filters registered after BGM. The implemented `BgmView` render/evaluate subclass boundary passed those tests, plus later unfiltered redirects, postprocessing and real native exception cleanup.

A native staging consent 500 exposed the early root-theme configuration error. Moving to the top-level native theme queue preserved defaults. Further native lifecycle tests exposed numeric component merging, repaired with a BGM-owned associative key. Actual native ThemeLoader initialisation and LoadStylerImports checks now cover native identity/consent, prior/later numeric imports and repeat registration. Capability/timing tests also cover an already initialised theme and already resolved View/StylerConfig services. Existing service extenders are preserved, and a competing View factory fails closed.

The final reviewer verdict is **PASS for source/package `0785c6dd02056e4ed5ce674508ae637e24618efa58e0f419c4538d94aacfe6ea`**, superseding earlier candidate verdicts. The reviewer independently ran **189** native API/lifecycle checks on both PHP **8.3.30** and **8.4.17**, the **62** LESS checks, and verified all **14** archive entries against source. Its verdict explicitly excludes native browser/full-matrix certification. Persisted original-element mutations, callback-list changes during dispatch and arbitrary competing factories are not represented as universally compatible.

### Claude evidence and execution limit

Architecture job `advise-mvddbwwt-uwfxxo` completed with authoritative provider JSON, verified cleanup and canonical session `6a6b28df-9b0c-48bd-9641-ffe489b12d8f`. It found the reachable `hgroup` gap and flattening of class-array shape before downstream callbacks. Both are repaired and covered by focused tests. Its references to production effects were source-based predictions, not observed customer/production failures.

Final-review job `do-mvddq8ri-iecbat` was cancelled when staging invalidated that source candidate. Two later file-reading jobs, `do-mvddub7u-vxhrd5` and `advise-mvde2kta-h3zfkk`, exited non-zero with no authoritative result. Readiness checks passed, but were not treated as completed reviews. The controller changed to a single-turn inline review of the exact frozen runtime bytes, without tool-reading ambiguity. The final changed approach, foreground single-turn inline job `advise-mvdeba0z-jum5zp`, also failed before returning a supported result. Its structured status is failed, with no available authoritative assessment. The controller stopped after these three materially changed final-review attempts. The earlier architecture review remains valid evidence of its findings, but is not being relabelled as a final-source approval. No root cause is claimed from the opaque failure classification.

### Native staging readback

The separate staging operator accepted the exact final ZIP through native browser upload, verified the unchanged checksum before/after and all fourteen installed members, then used native Style SAVE/reopen and a saved native Builder section. The configured final colour **#245A7E** appeared as **rgb(36, 90, 126)** on the frontend through actual generated/served CSS. There were no final frontend console errors or new stage-root PHP warnings/fatals/deprecations. The [implementation record](002-implementation.md) links hashes, readbacks and screenshots and explicitly limits the result to one section on Joomla 6.2.0/PHP 8.4.17/installed-source YOOtheme 5.0.52.

## Next steps

Goal 002 remains open at the final Claude review gate. Preserve the verified work, resolve reviewer-result execution, then complete the review before starting goal 003. Full component/state/lifecycle qualification and real public GitHub update delivery belong to goal 003.
