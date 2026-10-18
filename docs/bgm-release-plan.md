# Distribution and Joomla updates

## Current delivery checkpoint

The selected distribution destination is the public [george-media/bgm-yootheme-colors-extended repository](https://github.com/george-media/bgm-yootheme-colors-extended). Versioned native ZIP/checksum assets and a real raw HTTPS update feed are now available anonymously:

- [0.1.1 installer ZIP](https://github.com/george-media/bgm-yootheme-colors-extended/releases/download/v0.1.1/bgm-yootheme-colors-extended-0.1.1.zip)
- [0.1.1 checksum](https://github.com/george-media/bgm-yootheme-colors-extended/releases/download/v0.1.1/bgm-yootheme-colors-extended-0.1.1.zip.sha256)
- [0.1.1 release](https://github.com/george-media/bgm-yootheme-colors-extended/releases/tag/v0.1.1)
- [Live Joomla update feed](https://raw.githubusercontent.com/george-media/bgm-yootheme-colors-extended/main/bgm-updates/bgm-update.xml)

The final installer has fourteen allowlisted members, is **19,592 bytes**, and has SHA-256 `14e8924d5f5137d2823bc062fa1ccb166457d058aa69816f26df42ef0b2e9f98`. Anonymous downloads match the exact local ZIP/checksum/feed. It contains no vendor implementation or GitHub credentials.

GitHub marks `0.1.1` stable/latest after independent native acceptance. Beta1-to-beta2 updating and beta2-to-0.1.1 updating under Stable filtering passed, followed by exact final-package fresh Install from URL, explicit enablement and Style Save/reopen. All fourteen installed members match. Promotion changed release metadata only, preserving the source tag and asset bytes. [Final public readback](../evidence/bgm-003-final-distribution.json) verifies actual flags, tag and anonymous bytes. [Goal 003 qualification](003-qualification.md) records the full outcome.

## Deterministic package and receipt

The source manifest supplies the version. Build with an explicit expectation:

```bash
python3 -B bgm-scripts/bgm-build.py --expect-version 0.1.1
python3 -B bgm-tests/bgm-distribution.py
```

The builder validates the native plugin identity, exact `files`/`languages` declarations, their installation coverage and the fourteen-member package allowlist. It rejects source symlinks, unexpected members and a version mismatch. ZIP ordering, modes, compression and a documented fixed reproducibility epoch are deterministic. The epoch is not a build/release-date claim.

Ignored `dist/` receives the native ZIP, its `.zip.sha256` sidecar and a `.zip.json` receipt derived from the exact archive. The receipt records version, stability, byte count, archive hash, update-server URL and every member hash. Compare two independent builds before uploading. GitHub's automatically generated source archives are not installer packages.

The frozen `0.1.0` prototype remains a historical no-feed artifact. Its validator exception allows archived receipts to be checked without changing those bytes. New release manifests register the already verified live feed.

## Publish assets before generating the feed

1. Commit and verify the reviewed source and version tag. Publish the actual built ZIP and checksum as a clearly marked qualification prerelease.
2. Download both public URLs without an Authorization header, compare size/hash and exact bytes, and preserve the tag/source/package readback. Do not overwrite an already published version's assets.
3. Generate a local XML candidate from its receipt and its actual versioned installer URL:

```bash
python3 -B bgm-scripts/bgm-feed.py \
  --receipt dist/bgm-yootheme-colors-extended-0.1.1.zip.json \
  --asset-url https://github.com/george-media/bgm-yootheme-colors-extended/releases/download/v0.1.1/bgm-yootheme-colors-extended-0.1.1.zip \
  --output dist/bgm-update-0.1.1.xml
```

The generator revalidates the local ZIP/receipt, permits only the exact canonical versioned asset URL and downloads it anonymously. Every redirect must stay on HTTPS and approved GitHub asset hosts, without URL credentials. It verifies public byte count and SHA-256 before atomically writing the XML candidate with public-readable mode `0644`. It does not publish the feed itself.

4. Review the candidate, commit it to `bgm-updates/bgm-update.xml`, push and verify the raw public URL anonymously against local bytes. The fixed feed URL is independent of the ZIP hash, so the package never embeds its own checksum or a circular feed dependency.
5. Run the native Joomla discovery/update and fresh-install gates below. Promote the same release assets only after acceptance. Re-read GitHub release metadata, public bytes and final feed afterwards.

## Native updater contract

The feed matches element `bgm-yootheme-colors-extended`, type `plugin`, folder `system` and client `site`. It supplies the actual version, a single-line full ZIP download URL, explicit stability and exact SHA-256. The target is Joomla **6.2.0**, with PHP minimum **8.3.0**. Both pinned Joomla XML adapters prepend a start anchor to the native `6\.2\.0$` regex fragment. The PHP minimum does not enforce an upper bound.

Beta fixtures use versions `0.1.1-beta1` and `0.1.1-beta2`, explicit beta tags and GitHub prerelease flags. Native PHP comparison orders them before `0.1.1`. Stable minimum excludes the beta update; Beta filtering discovers it. That actual beta1-to-beta2 browser update passed, with all installed hashes, enabled state, saved styles/layout and unrelated synthetic data retained. Restore Stable filtering after beta qualification. GitHub release flags and Joomla feed stability are separate controls.

The final gate checks native discovery/install under Stable filtering, all fourteen installed member hashes, version and enabled state, preserved settings, exact-package fresh Install from URL/enable, explicit Style Save/reopen, compiled frontend output and new PHP/browser diagnostics. XML parsing or an available asset alone does not establish native acceptance.

No GitHub token, cookie or signed developer session belongs in extension source, feed URLs, packages or Joomla settings. Anonymous package/feed access is the distribution requirement.

## Qualified scope and limits

The full feature/lifecycle lane is Joomla **6.2.0**, YOOtheme **5.0.52**, PHP **8.4.17** and MySQL **8.3.0**. The native PHP **8.3.30** lane covers installer, browser Style Save/reopen and changed-CSS/frontend smoke. Staging uses synthetic data and authorised manifest-reconstructed YOOtheme installed source, not a pristine vendor distribution. [Goal 003 qualification](003-qualification.md) distinguishes each lane and acceptance stage.

An explicit native **YOOtheme Style Save is required after every installation or update**. Existing compiled CSS remains until that save. Native disable/uninstall/reinstall recovery preserves saved BGM values and unrelated data, with native unknown-choice fallback while integration is absent. File-cache clearing was tested with global Joomla caching disabled. Automatic CSS regeneration, Joomla 5, other vendor/PHP versions, arbitrary Builder-template overrides and all full-page-cache configurations are not qualified.

## Next steps

No further release action is required for 0.1.1. Install on the intended site when authorised and perform the required native Style Save. Repeat qualification before a new release or support target.
