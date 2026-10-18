# Goal 003 native qualification checkpoint

All implemented field and colour choices pass on Joomla 6.2.0 / YOOtheme 5.0.52 / PHP 8.4.17. The separate native PHP 8.3.30 install, Style SAVE/reopen and frontend smoke also passes. YOOtheme provenance remains the documented reconstructed installed-source fallback.

- 19 field mappings, 100 colour choices: actual native customizer schema, native page endpoint save and persisted readback, frontend classes and browser computed colours. Four lazy Leaflet popup cases were opened and computed separately. No saved or rendered sentinel.
- The actual native Builder UI changed the first section to BGM Senary, saved it, reloaded/reopened it, restored BGM Tertiary and saved again. Bulk matrix saves are labelled native endpoint saves.
- Four distinct backgrounds/text colours, native Style SAVE/compilation, inverse/preserve-colour, forced image-background light/dark, native nested primary/secondary controls, card-hover and card-media exclusion pass. Unknown synthetic third-party props survive.
- Synthetic buttons in a native Text element prove hover, keyboard focus-visible, active, disabled and aria-disabled CSS across four families. Native Builder button_item colours have their own matrix cases.
- Browser widths 390/768/1440 retain colours and show no horizontal overflow.
- A native child/non-default style14 saves and renders its own compiled theme.14.css and original custom.css marker. The first Joomla Create Child attempt retained the reconstructed manifest fixed parent element, so parent metadata was restored from its preserved archive and a corrected minimal original child ZIP installed natively. All3131 parent archive files match after restoration. This was staging provenance, not a BGM repair.
- Native disable/re-enable, uninstall (all14ownedmembers and extension record removed), reinstall/enable and StyleSAVE recovery preserve both style settings, saved layout and unrelated synthetic content. Disabled/absent integration uses Joomla/YOOtheme unknown-choice fallback and stays HTTP200.
- Actual public GitHub beta1 asset installed as baseline, Stable filter excludes beta2, Beta minimum discovers beta2, and actual browser Update installs beta2. All14installedmembers match its anonymously downloaded ZIP. Enabled state, both styles, layout and unrelated file hashes survive unchanged. Stable minimum is restored. CSS remains existing at update, with explicit StyleSAVE separately proven.
- Native file-cache clear preserves settings/frontend. Global Joomla caching was disabled, so this does not certify every fullpagecache configuration.

Current checkpoint is beta2, enabled, primary PHP8.4.17, owned PHP PID80711 / loopback18762 and isolated MySQL PID59962 / loopback13317. Latest native StyleSAVE/readback #245A85 computes rgb(36,90,133), with no browser warnings/errors or new PHP errors in configured8.3/8.4logs. Retain servers and private rollback/session inputs.

## Next steps

Await final stable0.1.1 identity from controller, then qualify native beta2 to stable through Stable minimum and exact finalZIP fresh plugin installation/enable/StyleSAVE recovery. No Joomla5, PHP8.2/8.5 or automatic CSSregeneration claim.
