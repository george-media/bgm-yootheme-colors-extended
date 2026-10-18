# 001 Discovery findings

Checked 18 October 2026, Australia/Sydney. This goal establishes the specification and implementation gates. The requested plugin itself is not built yet.

## Findings and evidence

| Question | Finding | Evidence type and boundary |
| --- | --- | --- |
| What does the old plugin add? | Four colour families across selected native Builder controls and component styles. | Static ZIP source inspection, documented in [legacy behaviour](bgm-legacy-behaviour.md). No legacy execution. |
| Why can it reject current YOOtheme? | Its installation helper explicitly caps YOOtheme at 4.5.99. | Archive `helper.php:17-20,51-70` and installer `script.php:32-45`. A proven installation gate, not proof that every runtime API broke. |
| What is the current target? | Vendor changelog reports YOOtheme Pro 5.0.52, dated 14 October 2026. Matching installed source is available locally. | [Official changelog](https://yootheme.com/changelog), read this session, plus read-only `templateDetails.xml` identity/hash. Installed files have not been proven pristine against a vendor package. |
| Are the old integration concepts still available? | Builder type filter, module configuration, style-component imports and render extension surfaces remain in matching current source. | [Current source contract](bgm-integration-contract.md). Static API evidence only. |
| Can the new identifiers be kebab-case? | Public custom keys, values, assets and classes can. PHP classes/namespaces and framework contracts have mandatory spelling/syntax exceptions. | PHP/Joomla current loader and service-provider source; [architecture](bgm-architecture.md). Native installation must still verify the chosen hyphenated element. |
| Can GitHub provide direct Joomla updates now? | The source repository is private. A public feed/download or a separate authenticated service is needed. | Repository API readback and [release plan](bgm-release-plan.md). No update feed claimed. |
| Is safe staging possible? | Working PHP/MySQL executables and current YOOtheme source exist. A clean separate Joomla/MySQL instance is planned. | [Staging plan](bgm-staging-plan.md), no customer site/database mutation and no staging execution yet. |

The [official module documentation](https://yootheme.com/docs/joomla/developers-modules) supports extending YOOtheme through modules and style components. The design uses a current Joomla plugin boundary and original implementation, rather than cloning the legacy archive or generating a second collection of copied vendor templates.

## REA identity

- Supplied archive SHA-256: `966885e6edd5a3f76ab298e0532d341e31c0cce9b33b2b78524e3514afce6f35`.
- REA `inspect_artifact` evidence: `ev_178f0200b085b5867c711a17cf5a838360380f72723c6f8fa840bbedbadcdab7`.
- Nested inventory evidence: `ev_208e69e700819bc084544d71dc11b57c48533e4d0dda59e011ccb4d19e5a56e9`.
- Graph manifest: `agm_2736b0087836bfc16c28ba5863467c02552f7f7967da02e2522bdf96e39c6cc8`.
- Graph: 28 nodes/occurrences including the ZIP root, 27 archive members, 27 edges.
- [Retained complete returned inventory](../evidence/bgm-legacy-inventory.json) contains metadata and digests, not extracted legacy source.

REA's ZIP graph describes central-directory entries. It does not execute code, establish PHP semantics or describe bytes outside that inventory. Detailed behaviour is attributed separately to ordinary read-only member inspection. The REA target was closed after retaining evidence.

## Review gate

An independent Codex adversarial reviewer and the Claude advisor inspect the integration plan before this foundation is committed. Record their material findings and dispositions in `docs/bgm-review-record.md`. Goal 002 must execute the rendering prototype and native Joomla installation gates. The discovery review does not qualify the final implementation.

## Next steps

After the reviewed foundation commit is pushed and read back, run hindsight and start `002 Build the original BGM colour extension`. Carry forward the four-family scope, structured rendering-adapter prototype, private-distribution limit and clean staging acceptance matrix.
