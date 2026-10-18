# BGM staging plan

Goal 001 discovery, inspected 18 October 2026, Australia/Sydney. This is a plan, not evidence that the new extension has been installed or works. The staging operator made no changes to existing sites, databases, service configuration or credentials.

## Verified local inputs

The read-only source at `/Users/damian/Sites/localhost/edengateway.com.au/templates/yootheme/templateDetails.xml` declares YOOtheme Pro **5.0.52**, creation date October 2026. The adjacent Joomla manifest and `libraries/src/Version.php` declare **6.2.0**. The template tree contains 3,426 files. These are installed-source observations, not a claim that every file matches an untouched vendor distribution.

| File relative to that YOOtheme directory | SHA-256 |
| --- | --- |
| `templateDetails.xml` | `56ed717ecb7201ef1a6cb8935ca1a2204f6aabf97f66a270c7c5da18fbac6a4b` |
| `packages/theme-joomla/bootstrap.php` | `508609e6fb20aa7ff66632479fcf25adb5b1b796e804675d82c731a061f3a1d4` |
| `vendor/autoload.php` | `a0be1b623f15d4cc56b43d913d5e5990003eab0305cf2dd6d60682950f9dd82b` |

Other inspected local manifest pairs include EAC Joomla 6.1.4 / YOOtheme 5.0.51, base-repo Joomla 6.1.3 / YOOtheme 5.0.43, and alexnoble.com.au-j5 Joomla 5.4.1 / YOOtheme 4.5.32. They are source references only. Do not use a customer site as the extension test target or clone its configuration or database.

| Runtime | Observation |
| --- | --- |
| `/Applications/MAMP/bin/php/php8.3.30/bin/php` | PHP 8.3.30, tested with `--version` and `-m` |
| `/Applications/MAMP/bin/php/php8.4.17/bin/php` | PHP 8.4.17, tested with `--version` and required module inventory |
| `/Applications/MAMP/bin/php/php8.5.2/bin/php` | PHP 8.5.2, version observed only |
| `/Applications/ServBay/package/php/8.2/current/bin/php` | PHP 8.2.23, version observed only |
| `/Applications/ServBay/bin/php` | PHP 8.3.11, tested with `--version` and `-m` |
| `/Applications/ServBay/package/mysql/8.3/current/bin/mysqld` | MySQL 8.3.0, `--version` and `--no-defaults --verbose --help` succeeded |
| Docker | CLI 29.6.2 installed. No daemon socket at `/Users/damian/.docker/run/docker.sock` |

MAMP PHP 8.4.17 has curl, dom, gd, intl, json, mbstring, mysqli, openssl, PDO, pdo_mysql, SimpleXML, xml, zip and zlib. Existing ServBay MySQL PID 960 listens on 3306/33060. Do not restart, reconfigure or connect the test to that instance. `/Applications/ServBay/package/bin/mysqld` is a broken MariaDB symlink, so use the explicit working MySQL path above. Ports 13317 and 18762 had no listeners during this inspection. Recheck immediately before use.

## Compatibility matrix

| Lane | Joomla | PHP | YOOtheme | Qualification |
| --- | --- | --- | --- | --- |
| Required primary | 6.2.0 | 8.4.17 | 5.0.52 | Native ZIP installer and complete builder/browser acceptance |
| Required minimum Joomla 6 PHP | 6.2.0 | 8.3.30 | 5.0.52 | Native install, save/reopen, compiled CSS and frontend smoke |
| Required if Joomla 5 support is advertised | Latest stable 5.4, freshly verified | 8.3.30 | 5.0.52 | Native install and same integration checks |
| Optional | Latest stable 5.4 | 8.2.23 | 5.0.52 | Only if this PHP version is advertised |
| Optional future coverage | 6.2.0 | 8.5.2 | 5.0.52 | Do not imply tested support from installed executable alone |

The [Joomla 5.4 requirements](https://manual.joomla.org/docs/5.4/get-started/technical-requirements/) specify PHP 8.1 minimum and MySQL 8.0.13 minimum. The [Joomla 6 requirements](https://manual.joomla.org/docs/next/get-started/technical-requirements/) specify PHP 8.3 minimum, recommend PHP 8.4, and require MySQL 8.0.13 or a supported MariaDB/PostgreSQL alternative. Their documentation header still called 6.2 upcoming when inspected, while the [official release announcement](https://www.joomla.org/announcements/release-news/joomla-6-2-is-here.html) records its 13 October 2026 release. Use the released package as the version authority. Joomla does not offer SQLite as a supported CMS installation database.

[YOOtheme's current introduction](https://yootheme.com/docs/joomla/introduction?view=docs) requires PHP 7.4+ with GD and refers to Joomla's requirements. Its [official support response](https://yootheme.com/support/question/175654) says current YOOtheme Pro requires Joomla 5+ and supports Joomla 6. These are prerequisites, not proof that this extension supports every possible combination.

## Disposable environment recipe

1. Create a new private directory outside Git, for example a new `mktemp -d /private/tmp/bgm-yootheme-staging.XXXXXX` result. Keep logs, database files, sessions, downloads and test website in that directory. Record its canonical path and owned child PIDs privately for cleanup. The staging operator is the sole owner of these processes.
2. Download the [official Joomla 6.2.0 full package](https://downloads.joomla.org/cms/joomla6/6-2-0/Joomla_6-2-0-Stable-Full_Package.zip?format=zip). The official [download page](https://downloads.joomla.org/cms/joomla6/6-2-0) lists ZIP SHA-1 `67a107c83b3084701a1a4acb151cd0452e5deaaf`. Check that listed digest and calculate SHA-256 for the evidence manifest. Do not substitute the downloaded update packages for a clean installation. No full Joomla package was found in the bounded Downloads inventory.
3. Unpack into a new `site` subdirectory. Initialise a completely new MySQL datadir with the explicit binary and `--no-defaults --initialize-insecure --datadir=<private-dir>/mysql --log-error=<private-dir>/mysql-init.log`. This creates a new disposable local database, not a change to the existing ServBay instance. Initialise permissions before exposing it. Start it with `--no-defaults`, the same private datadir, a private socket and PID/error files, `--port=13317 --bind-address=127.0.0.1 --mysqlx=OFF`. Confirm the new process datadir, socket and PID before any SQL. Fail closed if a directory or port is already occupied. This startup is proposed, not yet executed.
4. Create a uniquely named test schema and a schema-scoped disposable account through that private socket. Keep generated authentication material out of shell arguments, terminal output, Git, screenshots and committed evidence. Pass secrets through protected process input or the platform's supported credential mechanism. Do not reuse a customer database login or an existing Joomla configuration.
5. Install clean Joomla using its native web installer, or `/Applications/MAMP/bin/php/php8.4.17/bin/php installation/joomla.php install` from the new site root. The [official CLI installation guide](https://guide.joomla.org/user-guide/command-line-interface/command-line-interface-joomla-cli-installation) describes the prompts. Use secure input for passwords, not its example command-line password flags. Run `help install` against the actual downloaded package before scripting it. Disable mail sending and scheduled tasks in this disposable site. Use synthetic users/content only.
6. Obtain a pristine licensed YOOtheme 5.0.52 Joomla package using the authorised vendor account if available. Alternatively, reconstruct an install package from the local template's manifest-listed files only and record that provenance limitation. Never include customer configuration, content, caches, images or database material. Install through Joomla's native extension installer, select YOOtheme as the default template and confirm the installed version. Keep vendor code outside this project's Git history and release ZIP.
7. Bind the disposable PHP server only to loopback, for example `/Applications/MAMP/bin/php/php8.4.17/bin/php -d memory_limit=512M -d upload_max_filesize=64M -d post_max_size=64M -S 127.0.0.1:18762 -t <private-dir>/site`. The PHP development server is a local test convenience. Do not advertise Apache/Nginx deployment coverage from it. Use the `index.php` URLs below and avoid relying on rewritten SEF URLs.
8. Install the built extension ZIP through the acceptance flow below. Repeat lanes using separate clean sites/datadirs or a documented reset of this disposable environment. Never point a compatibility lane at existing customer tables.

## Native Joomla installation and acceptance

Open `http://127.0.0.1:18762/administrator/index.php?option=com_installer&view=install`. The native UI path is **System → Install → Extensions → Upload Package File**. Select the release ZIP and install. The current Joomla source confirms upload field `install_package`, standard installer task `install.install`, and `installtype`; use the live form's CSRF token. Browser file upload is preferred. A scripted multipart POST to this same native form may supplement it, but must not be reported as a browser click test.

Verify a native success message, the plugin's extension-list record, version, group `system`, manifest cache, files and supported namespace autoloading. Enable it through **System → Manage → Plugins**, then confirm the saved state. Supplementary CLI testing is available via `php cli/joomla.php extension:install --path=<zip>` in the disposable site, but does not replace the requested upload-package acceptance.

| Check | Required evidence |
| --- | --- |
| Clean installation | Exact release ZIP SHA-256, native installer success, saved extension record and file inventory |
| Disabled or absent YOOtheme | Joomla admin/frontend continue to load without a fatal error |
| Builder integration | Controls appear in their intended YOOtheme panels and existing colour options remain available |
| Save/reopen | Synthetic configured values survive save, browser reload and reopening the builder |
| CSS generation | Actual generated CSS contains the configured colour values/classes and frontend computed styles use them |
| Component coverage | Exercise the implemented scope, including backgrounds, text, overlays, buttons, cards, tiles and section styles where supported |
| Input boundaries | Invalid/empty colour inputs, bounded number of rows and unsafe labels cannot break CSS or execute markup/script |
| Isolation | Loading unrelated templates, administration pages or disabled plugin causes no unintended assets or errors |
| Upgrade | Install a later test version over an earlier one via the native installer, retain settings and prove new files/version |
| Uninstall/reinstall | Remove through native extension management, check extension-owned files are removed and unrelated files/content remain |
| GitHub update, if configured | Verify approved accessible update XML and ZIP URLs, targetplatform/php constraints, checksum and native update discovery/installation using two test versions; otherwise document manual ZIP delivery and no active automatic feed |
| Error review | Relevant PHP logs, browser console and network failures inspected for the actual tests |

Use the approved screenshot helper exactly, with private authentication/session input where necessary:

```bash
/Users/damian/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/bin/node /Users/damian/.codex/automations/tools/playwright_capture.mjs --url http://127.0.0.1:18762/administrator/index.php --output <absolute-evidence-png> --wait-until load
```

Its supported `--storage-state`, `--cookies`, `--save-storage-state` and allowlisted `--steps` inputs may support login/setup. Credentials must use `valueFromFile`, never literal command arguments. Capture installer outcome, enabled plugin, builder configuration and frontend output. Screenshots alone do not prove stored settings or compiled CSS, so pair them with readback and served-asset evidence.

## Bounded blockers and cleanup

- No extension implementation or release ZIP existed during this discovery. No staging installation or end-to-end compatibility claim is justified yet.
- Docker cannot be used without starting its daemon. The existing MySQL binary permits a proposed isolated instance without touching existing services, but that isolated startup remains untested.
- A pristine 5.0.52 vendor archive has not been downloaded. Installed-source contracts are available immediately. A reconstructed archive must retain its provenance limitation.
- Joomla 5's latest patch and any YOOtheme update after 5.0.52 must be refreshed at execution time. Never substitute an old local manifest for a current-release check.
- Two failed attempts at the same environment step require diagnosis and one changed approach. Stop after three materially different failed approaches and hand over the exact blocker. Do not alter a customer site to bypass a staging blocker.
- Stop only recorded, verified child PIDs. Shut down the private database via its private socket. Remove only the canonical private staging directory after checks are complete and evidence has been copied without secrets. Do not use broad process kills or service-manager restarts.

## Next steps

Goal 002 should build the extension, create this isolated clean environment, execute the required matrix, and record actual results before a compatibility or release claim.
