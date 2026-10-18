# Goal 002 implementation evidence

Controller: `01a14d69-a15d-72b2-8577-08e3bc2a6c93`. Started 18 October 2026, Australia/Sydney. Supported chat inventory confirmed **002 Build the original BGM colour extension**. Goal 001 foundation is commit `3ce4fad90acf54a8afc87c5188dfa0d2ea94efc1`.

## Original implementation

Version **0.1.0** adds Tertiary, Quaternary, Quinary and Senary to explicit native Builder palettes and a BGM Colours style-editor group. It uses a Joomla DI service provider, namespaced event subscriber, native theme configuration queue, original LESS and a finite colour registry. Forced light/dark controls and third-party choices remain intact. No vendor template, original plugin implementation, telemetry or registration endpoint is included.

Actual API tests rejected the initial loader-only adapter. Native filtered loaders can bypass it. The selected `BgmView` service subclass inherits native rendering while guarding every render invocation and activating only at the final verified native evaluation path. Original expressions survive repeated rendering. Owned ancestor sentinels are restored for unrelated nested views. Real exceptions restore native frames and buffers. See [the selected architecture](bgm-architecture.md) for the boundary and the deliberately limited downstream-transform compatibility claim.

Native staging also caught a real bootstrap error: setting root `config.theme` early skipped native defaults and produced a consent-rendering 500. BGM now uses the top-level theme loader. A BGM-owned associative component key preserves earlier/later numeric imports. Actual native ThemeLoader regression tests cover identity, defaults, consent, import preservation and repeated registration. The rejected packages are not final deliverables.

## Package and source checks

- Installer: `dist/bgm-yootheme-colors-extended-0.1.0.zip`, **19,481 bytes**, **14 allowlisted files**.
- SHA-256: `0785c6dd02056e4ed5ce674508ae637e24618efa58e0f419c4538d94aacfe6ea`.
- Root manifest and metadata agree on element `bgm-yootheme-colors-extended`, group `system`, version `0.1.0` and the BGM namespace.
- Two controller builds from a separate source snapshot produced identical bytes. All archive entries match source. No credentials, site database, licensed source, test helpers or sessions are packaged.
- **189** actual vendor API/lifecycle checks pass independently on PHP **8.3.30** and **8.4.17**, including stock/unrelated byte equality, later filtered/unfiltered loaders, inheritance, configurable tags, exceptions, reusable expressions, ordinary downstream callbacks, schema collisions and timing/factory fail-closed cases.
- **62** LESS checks pass with Less **4.4.2** and actual 5.0.52 UIkit input. The isolated compilation is **625,764 bytes** and verifies four families, button states, native inverse descendants, preserve-colour/media exclusions and variable overrides. This is separate from the actual browser style compilation below.
- The engineer linted all ten runtime/test PHP files on both PHP executables.

[Source/package verification](../evidence/bgm-002-source-verification.json) binds these checks to the exact archive. Independent adversarial review passed this final source/hash. Chat 003 recovered the final authoritative Claude assessment through the supported native review route. Independent adjudication rejected its MAJOR parameter-inheritance claim because the implementation matches the actual native fallback, and documented four bounded integration points. Two additional native parity regressions pass on both PHP runtimes, bringing the current API checks to **191**. The runtime/package bytes remain unchanged. See the final section of [the review record](bgm-review-record.md).

## Isolated native staging

The operator created a new private MySQL 8.3.0 datadir, socket and port **13317**, plus PHP **8.4.17** on loopback **18762**. Existing services and customer configurations/databases were untouched. Joomla **6.2.0** came from its official full package, with the published SHA-1 matched and SHA-256 recorded. The clean site contains synthetic users/content only, with mail disabled.

Pristine YOOtheme account download was unavailable. The operator used the authorised fallback, reconstructing manifest-listed template and framework **5.0.52** packages from read-only installed source. Generated css/fonts, child themes, caches and customer configuration/content were excluded. Synthetic blank files retained the required empty folders through Joomla extraction. This is explicitly **installed-source provenance, not a pristine vendor distribution comparison**. See [input identities](../evidence/bgm-002-staging/bgm-inputs.json), [environment](../evidence/bgm-002-staging/bgm-environment.json) and [YOOtheme provenance](../evidence/bgm-002-staging/bgm-yootheme-source.json).

Native browser upload/enable proved the hyphenated plugin element, service provider, namespace map and language resolution. With YOOtheme absent, enabled Joomla frontend loaded HTTP 200. On the repaired intermediate package, all four BGM style families appeared, native Style SAVE persisted `@bgm-tertiary-background = #245A7D`, and reopening the colour picker retained it. YOOtheme generated real theme CSS. A section saved through the native Builder stored `bgm-tertiary` without a sentinel and rendered `bgm-section-tertiary uk-section`, with browser background `rgb(36, 90, 125)` and native inverse mode `dark`.

The exact final `0785c6dd...` archive then passed native browser upload, with an unchanged checksum before/after and all **14 installed members matching source hashes**. The operator changed the colour to a distinct **#245A7E** under final code, used native Style SAVE, reloaded and reopened the picker. Stored Builder data retained `bgm-tertiary` with no sentinel. The final frontend returned HTTP **200** with background **rgb(36, 90, 126)** and `--uk-inverse: dark`, using served `theme.13.css`, **746,464 bytes**, SHA-256 `7b3c82ee22d7194d51bf78adfb137fa046bf10366a31873cca602375c8730a20`. Frontend console errors were empty. A stage-root-filtered PHP log check for the final acceptance window found no new warnings, fatal errors or deprecations, separated from historical rejected-candidate diagnostics.

[Native installation and member hashes](../evidence/bgm-002-staging/bgm-final-native-install.json), [browser/persistence/CSS proof](../evidence/bgm-002-staging/bgm-final-browser-proof.json), [saved synthetic layout](../evidence/bgm-002-staging/bgm-native-synthetic-layout.json) and [reopened picker](../evidence/bgm-002-staging/bgm-final-bgm-style-saved-reopened.png) bind this minimal acceptance to the final archive. [Final frontend](../evidence/bgm-002-staging/bgm-final-frontend-tertiary.png), [enabled plugin](../evidence/bgm-002-staging/bgm-final-plugin-enabled.png) and [final PHP log check](../evidence/bgm-002-staging/bgm-final-error-log-check.json) supplement the readbacks. Same-version replacement is not a two-version update qualification.

## Distribution and remaining qualification

During this goal the user made the [GitHub repository](https://github.com/george-media/bgm-yootheme-colors-extended) public. Fresh API read confirmed `private:false`, `visibility:public`, default `main` and unarchived status. The official [YOOtheme changelog](https://yootheme.com/changelog) still led with **5.0.52, 14 October 2026**, on this date. The requested GitHub feed/release workflow is now feasible. Version 0.1.0 has no active update server.

This is a prototype acceptance slice. It does not certify the full responsive/inverse/component matrix, a child theme, native PHP 8.3 lane, Joomla 5, automatic cache regeneration, version upgrade, disable/uninstall or a GitHub-delivered update. Style SAVE remains required after installing/updating 0.1.0. Ordinary per-render transforms are tested, while persisted original-element mutations, dynamic callback-list mutation and arbitrary competing View factories are outside the claim.

## Next steps

Record the recovered final review and unchanged package with commit/push readback and hindsight. Goal 002 is complete at this prototype gate. Continue goal 003 in chat 003 to qualify the full native matrix and real GitHub installer/update feed before stable delivery.
