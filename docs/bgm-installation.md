# Installation and local builds

The plugin element is `bgm-yootheme-colors-extended` in Joomla's `system` group. The product name is **BGM YOOTheme Colors Extended**. This is an original implementation, with no legacy plugin or licensed YOOtheme source in its installer ZIP.

## Package

Use the generated `dist/bgm-yootheme-colors-extended-<version>.zip` and its SHA-256 sidecar. The manifest belongs at the ZIP root. GitHub's source download is not the installer package. The user made the source repository public during goal 002, enabling the requested GitHub distribution workflow. This prototype does not yet register an automatic update feed.

Version-specific test results and limitations belong in [goal 002's evidence](002-implementation.md) and the subsequent qualification record. An available ZIP is not by itself a stable-release or compatibility claim.

## Joomla

1. Back up the target site before a real installation. Goal 002's native proof uses disposable local staging only.
2. In Joomla administration, open **System → Install → Extensions → Upload Package File**, select the exact generated ZIP and install it.
3. Open **System → Manage → Plugins**, find **BGM YOOTheme Colors Extended** and enable it. Installation does not silently enable the plugin.
4. Open YOOtheme's style editor and find **BGM Colours**. Configure the four families, then save the style so YOOtheme compiles the additional LESS. Version 0.1.0 also requires an explicit style save after updating the plugin. It does not automatically regenerate existing compiled CSS.
5. In the applicable native Builder controls, select the BGM colour or BGM card/tile choice, save the layout and reopen it. Confirm the frontend after compilation.

Keep the original `bgm-*` saved values. Temporary rendering sentinels must never be saved in Builder data. The plugin does not automatically migrate legacy colour values or rewrite existing content.

## Naming boundary

BGM colour options, CSS classes, LESS variables, custom assets and package names use the `bgm-` prefix with kebab case. Joomla/YOOtheme native API keys and UIkit structural/behaviour classes retain their native names, including `uk-card-hover`. PHP namespaces and class names use `Bgm` because hyphens are invalid PHP identifiers. PSR source filenames, Joomla's `services/provider.php` entry point, native element/field names and Joomla language-file/key conventions are deliberate framework syntax exceptions.

## Removal and saved content

Disabling or uninstalling removes the active integration. It does not convert saved BGM choices into native colours or delete saved style/layout data. The exact disable/uninstall fallback, update persistence, cache lifecycle and broader version matrix must be qualified before stable delivery. See [the staging gates](bgm-staging-plan.md).

## Next steps

Use the exact package and tested-version notes supplied with the qualified release. Automatic updates require a separately selected accessible distribution endpoint and native updater testing.
