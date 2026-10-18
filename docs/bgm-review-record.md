# Goal 001 review record

Review scope: discovery, original design and delivery gates. No implementation, native installation or browser compatibility has been approved by these reviews.

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
