# BGM YOOTheme Colors Extended

An original Joomla system plugin adding **Tertiary, Quaternary, Quinary and Senary** colour families to YOOtheme Pro's native style editor and applicable Builder controls.

**Status: 0.1.0 prototype implemented, with native installation and a minimal save/reopen/compiled-frontend slice verified. Goal 002 remains open because the required final Claude review returned no usable result after three bounded attempts. Full qualification and stable GitHub delivery remain goal 003.**

The plugin uses BGM-prefixed options/classes/LESS, a finite native schema map, Joomla dependency injection and an original scoped rendering adapter. It preserves native structural classes, inverse modes and card hover. It includes no legacy plugin implementation, vendor templates, registration API or telemetry.

## Verified scope

- Native Joomla **6.2.0**, PHP **8.4.17**, YOOtheme **5.0.52** staging, with synthetic content and a new isolated database.
- Exact deterministic installer ZIP passed native browser upload/enable and all 14 installed member hashes matched.
- Native style saving/reopening and a saved Builder section produced the configured frontend background through actual generated CSS.
- **189** focused vendor API/lifecycle checks pass on PHP **8.3.30** and **8.4.17**. These do not substitute for the pending native PHP 8.3 lane.
- **62** actual UIkit LESS checks pass, covering families, states, inverse selectors and overrides.

YOOtheme staging used reconstructed manifest-listed installed source, with generated/customer assets excluded. It was not compared to a pristine vendor distribution. Full component/state, responsive, child-theme, cache, version-upgrade and removal qualification remains open. See [goal 002 evidence](docs/002-implementation.md) and [the review record](docs/bgm-review-record.md).

## Build and install

```bash
python3 bgm-scripts/bgm-build.py
```

This writes the root-manifest native installer ZIP and SHA-256 sidecar into ignored `dist/`. GitHub's source archive is not the installer package. Follow [the installation guide](docs/bgm-installation.md). Version 0.1.0 requires an explicit YOOtheme Style SAVE after installation or update to compile existing styles.

The [source repository](https://github.com/george-media/bgm-yootheme-colors-extended) is now public following the user's change during goal 002. A real GitHub release-asset/update-feed workflow is planned in goal 003. **Automatic updates are not active in 0.1.0.**

- [Legacy behaviour](docs/bgm-legacy-behaviour.md)
- [Integration evidence](docs/bgm-integration-contract.md)
- [Selected architecture and compatibility limits](docs/bgm-architecture.md)
- [Staging and qualification gates](docs/bgm-staging-plan.md)
- [GitHub distribution and updates](docs/bgm-release-plan.md)
- [Goals and acceptance gates](docs/bgm-work-plan.md)

## Next steps

Resolve the final Claude reviewer-output blocker and close goal 002, then complete goal 003 qualification, publish the qualified installer/checksum, and verify a real anonymous native Joomla update before stable delivery.
