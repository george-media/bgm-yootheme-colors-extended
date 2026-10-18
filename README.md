# BGM YOOTheme Colors Extended

An original Joomla system plugin adding **Tertiary, Quaternary, Quinary and Senary** colour families to YOOtheme Pro's style editor and applicable native Builder controls.

**0.1.1 is released.** Native feature/lifecycle qualification, anonymous GitHub downloads, Stable-filter updating and exact-package fresh installation pass within the tested scope below.

[Installer ZIP](https://github.com/george-media/bgm-yootheme-colors-extended/releases/download/v0.1.1/bgm-yootheme-colors-extended-0.1.1.zip) · [SHA-256 checksum](https://github.com/george-media/bgm-yootheme-colors-extended/releases/download/v0.1.1/bgm-yootheme-colors-extended-0.1.1.zip.sha256) · [Qualification record](docs/003-qualification.md)

The native installer is **19,592 bytes**, with fourteen allowlisted files. GitHub's source archive is not the installer.

## Install

1. Upload the installer ZIP through Joomla **System → Install → Extensions**.
2. Enable **BGM YOOTheme Colors Extended** in the plugin manager.
3. In YOOtheme's style editor, configure **BGM Colours** and save the style.
4. Select the BGM colours in the applicable native Builder controls, save and reopen.

**Use a native YOOtheme Style Save after installation or update to compile styles.** Automatic regeneration of existing compiled CSS is not provided. Joomla's extension updater uses the real public feed and versioned installer assets without a GitHub credential.

## Verified scope

- Joomla **6.2.0**, YOOtheme Pro **5.0.52**, PHP **8.4.17**, MySQL **8.3.0**: all **100** colour choices across **19** fields, native saved data/classes and browser colours, nested controls, inverse/preserve modes, states, responsive widths, inherited child/non-default style and removal/recovery.
- PHP **8.3.30**: separate native install, browser Style Save/reopen, compiled CSS and frontend smoke on the same disposable Joomla/YOOtheme lane.
- Real anonymous GitHub downloads, native beta1 → beta2 → 0.1.1 updating, Stable filtering, exact final Install from URL, installed member hashes and settings persistence.
- **191** vendor API/lifecycle checks on both PHP versions, **62** actual UIkit LESS checks and **16** distribution regressions. These are separate from native browser acceptance.

Bulk matrix layouts used the native YOOtheme page endpoint. Representative Builder saves/reopening and installer/update actions used the browser. CSS state controls include synthetic buttons inside a native Text element, with native button-item placement tested separately.

YOOtheme staging used reconstructed manifest-listed installed source, rather than a pristine vendor distribution. Joomla 5, other PHP versions, arbitrary Builder-template overrides and general full-page-cache configurations have not been qualified. File-cache clearing was tested with global Joomla caching disabled. See [the complete evidence and limits](docs/003-qualification.md).

## Implementation and builds

The plugin uses Joomla dependency injection, a finite native schema map, a scoped rendering adapter and original LESS. Custom options/classes/assets use `bgm-` names. Native structural/behaviour classes remain intact. No legacy implementation, vendor templates, registration API or telemetry is packaged.

```bash
python3 -B bgm-scripts/bgm-build.py --expect-version 0.1.1
python3 -B bgm-tests/bgm-distribution.py
```

The deterministic builder writes the ZIP, checksum and member-hash receipt into ignored `dist/`. The manifest supplies the version. The feed generator verifies the actual anonymous versioned GitHub download before writing XML.

- [Installation and removal](docs/bgm-installation.md)
- [Distribution and updates](docs/bgm-release-plan.md)
- [Architecture and integration limits](docs/bgm-architecture.md)
- [Native field contract](docs/bgm-integration-contract.md)
- [Review findings and dispositions](docs/bgm-review-record.md)
- [Goals and acceptance gates](docs/bgm-work-plan.md)

## Next steps

Install on the intended site when authorised, enable the plugin and save the YOOtheme style. No further engineering or release action is required for 0.1.1.
