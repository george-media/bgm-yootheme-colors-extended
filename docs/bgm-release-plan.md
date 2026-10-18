# Distribution and Joomla updates

## Verified repository state

On 18 October 2026, `gh repo view` and the repository API identified `george-media/bgm-yootheme-colors-extended` as **private**, empty, unarchived and writable by the current account. Keep that visibility. The user's upload/download and commit/push authority does not require exposing a private source repository.

Joomla's standard updater fetches an extension XML feed and a downloadable package. A signed-in developer's ability to download a GitHub asset does not establish access from a Joomla server. Private GitHub content needs authentication. Do not put a GitHub token into extension source, a public URL, update metadata, logs or package settings as a shortcut.

## Build first

Create `dist/bgm-yootheme-colors-extended-<version>.zip`, a SHA-256 checksum and a validated XML update manifest generated from the same release metadata. Keep `dist/` ignored locally. Attach a built ZIP as a release asset when publication is authorised. GitHub's automatically generated source ZIP is not the native Joomla installer package.

An XML schema/parse check is necessary but insufficient. Test actual native Joomla extension installation and an upgrade from the previous fixture version, then read back installed version/state/files and saved settings.

## Feasible distribution choices

1. Keep source private and make a separate approved distribution repository or host public update XML and release ZIPs. This is the simplest anonymous Joomla path, once the public destination is explicitly selected.
2. Keep source and distribution private and use a deliberately designed authenticated distribution service. This adds infrastructure and credential lifecycle work outside the first plugin build.
3. Deliver an installer ZIP manually while the optional automatic-update destination remains unresolved. Clearly state that automatic updates are not active.

Do not change repository visibility or publish a public feed to resolve the question implicitly. Continue plugin engineering and local staging independently. If public distribution is later authorised, prefer GitHub versioned release assets plus a stable feed URL. Verify both with an unauthenticated request from outside the developer's logged-in session. Publishing a feed without accessible ZIPs is not a completed updater.

## Update metadata contract

The feed must match the installed element `bgm-yootheme-colors-extended`, type `plugin`, folder `system` and client `site`. Each update entry needs its actual version, selected stability and supported Joomla/PHP constraints. Use a single-line HTTPS download URL and a checksum computed from the exact uploaded ZIP. Generate a changelog URL only if its destination is available. The native plugin manifest should register an update server only after a real accessible feed exists, or clearly leave automatic updates unconfigured.

These requirements follow the [Joomla update-server documentation](https://manual.joomla.org/docs/building-extensions/install-update/update-server/). GitHub documents [release assets](https://docs.github.com/en/rest/releases/assets) and [repository content access](https://docs.github.com/en/rest/repos/contents). No live update feed exists at goal 001.

## Release gate

Before marking a version stable, confirm native fresh install, upgrade, enable/disable, uninstall, customizer persistence, compiled frontend rendering, third-party option preservation and absence of PHP/browser errors on the declared matrix. Then run both independent reviews, address material findings, create a detailed commit, push and verify matching remote SHA. Hindsight follows the goal's completed commit. Do not publish an unqualified package as stable solely to finish a goal.
