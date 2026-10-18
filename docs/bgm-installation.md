# Installation and local builds

BGM YOOTheme Colors Extended is an original Joomla system plugin. Its element is `bgm-yootheme-colors-extended`, its group is `system`, and its installer contains no legacy implementation or licensed YOOtheme source.

## Download and verify

- [Native 0.1.1 installer ZIP](https://github.com/george-media/bgm-yootheme-colors-extended/releases/download/v0.1.1/bgm-yootheme-colors-extended-0.1.1.zip)
- [SHA-256 checksum](https://github.com/george-media/bgm-yootheme-colors-extended/releases/download/v0.1.1/bgm-yootheme-colors-extended-0.1.1.zip.sha256)
- [Release record](https://github.com/george-media/bgm-yootheme-colors-extended/releases/tag/v0.1.1)
- [Joomla update feed](https://raw.githubusercontent.com/george-media/bgm-yootheme-colors-extended/main/bgm-updates/bgm-update.xml)

The installer is **19,592 bytes**, with fourteen members and SHA-256:

```text
14e8924d5f5137d2823bc062fa1ccb166457d058aa69816f26df42ef0b2e9f98
```

The ZIP, checksum and feed have been downloaded anonymously and matched to local bytes. GitHub's source archive is not the native installer. The `0.1.1` GitHub release is stable/latest after native Stable-filter updating and exact-package fresh-install acceptance. See [goal 003 qualification](003-qualification.md) for evidence and limits.

## Qualified environment

| Environment | Verified scope |
| --- | --- |
| Joomla 6.2.0, YOOtheme Pro 5.0.52, PHP 8.4.17, MySQL 8.3.0 | Full native component, state, responsive, placement, persistence, child-style and removal matrix, both real updater transitions and exact final fresh Install from URL. |
| Same disposable Joomla/YOOtheme/database, PHP 8.3.30 | Native installer, browser Style Save/reopen, changed compiled CSS and frontend smoke. |

All native tests use synthetic content and isolated local processes. The YOOtheme input is an authorised reconstruction from manifest-listed installed source, with customer/generated assets excluded. It is not a pristine vendor-distribution comparison. Joomla 5, other PHP/YOOtheme versions and arbitrary Builder-template overrides require separate qualification. The feed targets Joomla **6.2.0** and PHP minimum **8.3.0**. That minimum does not enforce a PHP upper bound.

## Install, enable and compile

1. Back up the target site and verify the installer checksum.
2. In Joomla administration, open **System → Install → Extensions → Upload Package File** and upload the native ZIP. Alternatively use **Install from URL** with the linked native installer URL, the method verified for the exact final 0.1.1 fresh installation.
3. Open **System → Manage → Plugins**, find **BGM YOOTheme Colors Extended** and enable it. Installation does not silently enable the plugin.
4. Open YOOtheme's Style editor, find **BGM Colours**, configure Tertiary, Quaternary, Quinary and Senary, then **save the style**. Repeat this native Style Save after every plugin installation or update, including each style that needs regenerated CSS.
5. In the applicable native Builder controls, select a BGM colour or BGM card/tile choice, save and reopen the layout, then check the frontend.

An explicit Style Save compiles the added LESS. Updating the plugin retains existing compiled CSS until that save. Automatic regeneration of existing styles is not provided.

## Joomla updates

The manifest registers the live HTTPS feed above. Use Joomla's **System → Update → Extensions** page to check for and install an available version. Joomla checks update metadata and the downloaded package checksum. No GitHub login or token is required on the Joomla server.

Keep Joomla's minimum stability at **Stable** for the `0.1.1` feed. The earlier beta-to-beta qualification used Beta filtering only in disposable staging and restored Stable afterwards. GitHub's prerelease flag and Joomla's feed stability tag are separate settings. The current feed uses the stable tag. Native beta2-to-0.1.1 discovery and browser Update passed under Stable filtering, and a final check found no pending BGM update.

## Saved values, removal and caches

Keep the saved `bgm-*` values. Temporary rendering sentinels must never be saved in Builder data. The plugin does not migrate legacy colour values or rewrite existing content.

Native disable/uninstall testing retained saved style/layout settings and unrelated synthetic content. Disabling removes BGM choices from the active schema and rendering integration. Uninstalling also removes the extension record and all fourteen owned members. The tested frontend remains HTTP 200 through native unknown-choice fallback, with BGM styling inactive. Existing content is not converted to stock colours. Reinstall, enable and Style Save restore the integration.

Native file-cache clearing retained settings and frontend behaviour with global Joomla caching disabled. Full-page caching and other cache configurations are outside that proof. The tested child style inherits parent Builder templates. Ordinary per-render callbacks are supported, while persistent mutation of reusable original elements and dynamic callback-list changes remain outside the adapter contract.

## Local build

```bash
python3 -B bgm-scripts/bgm-build.py --expect-version 0.1.1
python3 -B bgm-tests/bgm-distribution.py
```

The manifest is the version source. The deterministic builder writes the ZIP, `.zip.sha256` sidecar and `.zip.json` receipt into ignored `dist/`. It checks the exact native installation declarations and fourteen-member allowlist. The receipt binds the archive and every member by SHA-256. See [distribution and updates](bgm-release-plan.md) for public-asset verification and feed generation.

## Naming boundary

Custom colour options, CSS classes, LESS variables, assets and package names use the `bgm-` prefix with kebab case. Native Joomla/YOOtheme API keys and UIkit behaviour classes retain their names, including `uk-card-hover`. PHP namespaces/classes use `Bgm` because hyphens are invalid PHP identifiers. PSR filenames, `services/provider.php`, native element/field names and Joomla language conventions are deliberate framework exceptions.

## Next steps

Install on the intended site when authorised, enable the plugin and save each required YOOtheme style. No further release action is required for 0.1.1.
