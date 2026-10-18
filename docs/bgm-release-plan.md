# Distribution and Joomla updates

## Historical repository state at goal 001

At the initial 18 October 2026 discovery, `gh repo view` and the repository API identified `george-media/bgm-yootheme-colors-extended` as **private**, empty, unarchived and writable. That historical restriction was superseded by the user's public-repository decision below.

Joomla's standard updater fetches an extension XML feed and a downloadable package. A signed-in developer's ability to download a GitHub asset does not establish access from a Joomla server. Private GitHub content needs authentication. Do not put a GitHub token into extension source, a public URL, update metadata, logs or package settings as a shortcut.

## Public repository confirmed in goal 002

During implementation on 18 October 2026 the user stated that the GitHub repository was now public. A fresh repository API read confirmed `private:false`, `visibility:public`, default branch `main` and `archived:false`. The private-repository blocker is resolved. The requested GitHub installer/update workflow can proceed after qualification. This does not make the prototype a stable release or prove any package/feed endpoint exists.

Use versioned GitHub release assets for installer ZIPs and checksums, with an HTTPS XML feed committed to the public repository and fetched through its raw URL. Test anonymous reads and a real native Joomla version upgrade before announcing updates as active. Never substitute GitHub source archives for installer packages.

## Build first

Create `dist/bgm-yootheme-colors-extended-<version>.zip`, a SHA-256 checksum and an XML update manifest generated from the same release metadata only when its real package endpoint exists. Keep `dist/` ignored locally. Publish the qualified built ZIP as a versioned release asset for the requested GitHub delivery. Clearly label any updater-test fixtures as prereleases. GitHub's automatically generated source ZIP is not the native Joomla installer package.

An XML schema/parse check is necessary but insufficient. Test actual native Joomla extension installation and an upgrade from the previous fixture version, then read back installed version/state/files and saved settings.

## Distribution boundary

Public GitHub delivery is now the selected feasible route. Complete local qualification first, then publish the actual installer asset and a matching feed. Keep the source repository public as the user selected. No GitHub credential belongs in Joomla or its package.

The goal 002 prototype has no update server. Goal 003 must add and test the public feed and release assets with two distinct versions, saved settings readback, SHA-256 verification and a native update result. An accessible feed without an accessible installer ZIP does not satisfy the updater gate.

## Update metadata contract

The feed must match the installed element `bgm-yootheme-colors-extended`, type `plugin`, folder `system` and client `site`. Each update entry needs its actual version, selected stability and supported Joomla/PHP constraints. Use a single-line HTTPS download URL and a checksum computed from the exact uploaded ZIP. Generate a changelog URL only if its destination is available. The native plugin manifest should register an update server only after a real accessible feed exists, or clearly leave automatic updates unconfigured.

These requirements follow the [Joomla update-server documentation](https://manual.joomla.org/docs/building-extensions/install-update/update-server/). GitHub documents [release assets](https://docs.github.com/en/rest/releases/assets) and [repository content access](https://docs.github.com/en/rest/repos/contents). No live update feed exists at goal 001.

## Release gate

Before marking a version stable, confirm native fresh install, upgrade, enable/disable, uninstall, customizer persistence, compiled frontend rendering, third-party option preservation and absence of PHP/browser errors on the declared matrix. Then run both independent reviews, address material findings, create a detailed commit, push and verify matching remote SHA. Hindsight follows the goal's completed commit. Do not publish an unqualified package as stable solely to finish a goal.
