# BGM YOOTheme Colors Extended

Use Australian English. Work silently except for genuine blockers and completion reports. Every completion or handoff ends with a `Next steps` section.

## Project identity

- Canonical repository: `/Users/damian/Sites/localhost/bgm-yootheme-colors-extended`.
- Codex project context: `/Users/damian/Documents/Codex/PROJECTS/BGM YOOTHEME COLORS EXTENDED`.
- Git remote: `git@github.com:george-media/bgm-yootheme-colors-extended.git`.
- Product name: **BGM YOOTheme Colors Extended**.
- Rebuild from a behavioural specification. Do not copy the old plugin's implementation or redistribute YOOtheme source.

## Task numbering and continuity

Every top-level chat in this project must have a unique three-digit prefix assigned when the task is created, starting at `001`. Consult `TASK-NUMBERING.json`, supported active/archived chat listings and project context before reserving the next number. Preserve existing prefixes during renames and never reuse archived numbers. Use supported `set_thread_title` and `create_thread` tools, with a readback. Internal workers do not consume top-level numbers. Do not edit Codex databases or indexes.

Each goal needs a concrete acceptance gate. On completion, commit and push reviewed work with a detailed description, verify the remote SHA, run the requested hindsight review, then create and start the next numbered chat when a further required goal remains. Do not create an endless chain after all requested acceptance gates pass. Carry the user's authority and constraints in the successor prompt. Use the most suitable available model and effort for its work.

## Roles and ownership

Keep one controller, one engineer and one staging operator. The controller integrates evidence, owns Git/release state and communicates. The engineer owns application source. The staging operator alone owns staging processes, databases and UI sessions. Use separate read-only adversarial reviewers and the requested Claude advisor skill. All workers remain silent. Do not give overlapping write ownership. No self-approval of material implementation changes.

Bound repairs to three meaningful attempts per blocker, each with a changed hypothesis or evidence. Stop repeating unchanged failing steps. Record the remaining blocker accurately and continue independent work.

## Naming and implementation

Use `bgm-` prefixed kebab-case names for custom CSS classes, custom options, assets, public identifiers and custom directories. Preserve Joomla/YOOtheme native API identifiers when using their APIs. PHP namespaces/classes, PSR autoload filenames, interface methods and Joomla language keys require syntax/convention exceptions, document them explicitly and use a `Bgm`/`BGM` prefix wherever the identifier is ours. Do not generate invalid hyphenated PHP class names or invent prefixed replacements for native API keys.

Use current Joomla dependency injection, service providers, event subscribers, manifests, language files and supported install/update interfaces. Avoid legacy global class aliases and source patches to Joomla/YOOtheme. Preserve unknown and third-party Builder options. Prove install, enable, render, save/reopen, update, disable and uninstall using Joomla itself before calling a build release-ready.

## Authority and secrets

The user authorised access/use of required logins in 1Password and upload/download of required files. Do not expose secrets in chat, shell arguments, logs, commits or evidence. Use secure injection or private file inputs. Authority to access credentials is not authority for unrelated production mutations. Use new disposable local staging and synthetic data. Existing customer sites are read-only reference sources. Do not change repository visibility, publish a public release or deploy to a customer site without explicit scope for that action.

The repository was private at goal 001 discovery. During goal 002 the user confirmed making it public, and the controller verified GitHub API `private:false` and `visibility:public`. Continue the requested GitHub distribution/update workflow after qualification. Keep distribution status explicit, verify feed and installer access anonymously, and do not ship dead update URLs or embedded GitHub credentials. Do not change repository visibility again.

## Browser QA

Use this exact approved command prefix for screenshot/browser QA:

```bash
/Users/damian/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/bin/node /Users/damian/.codex/automations/tools/playwright_capture.mjs --url <url> --output <png> --wait-until load
```

Request unsandboxed execution through the normal escalation path if required. Do not try raw Playwright first or wrap the prefix in a shell launcher. Credentials must use `valueFromFile` through `--steps`, with cookies/storage state kept in ignored private storage.

## Evidence

Distinguish static legacy observations, current vendor source contracts, isolated tests, native Joomla installer results, browser behaviour and live distribution readback. A unit test or source inspection is not installation or compatibility proof. Never commit credentials, customer content, local databases, licensed vendor source, original plugin ZIPs or browser sessions. Keep source hashes and concise original findings instead.
