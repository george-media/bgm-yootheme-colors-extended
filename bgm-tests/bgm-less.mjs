/** Compile original BGM rules with an external licensed UIkit/YOOtheme source tree. */
import { createRequire } from 'node:module';
import path from 'node:path';
import assert from 'node:assert/strict';
import { fileURLToPath } from 'node:url';

const bgmRequire = createRequire(import.meta.url);
const bgmLess = bgmRequire(process.env.BGM_LESS_MODULE || 'less');
const bgmVendor = process.argv[2];
if (!bgmVendor || !path.isAbsolute(bgmVendor)) throw new Error('Pass an absolute YOOtheme source root.');
const bgmSource = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../bgm-plugin/bgm-assets/bgm-less/bgm-colours.less');
const bgmNative = path.join(bgmVendor, 'vendor/assets/uikit/src/less/uikit.less');
const bgmInput = `@import "${bgmNative}";\n@import "${bgmSource}";\n`;
const bgmResult = await bgmLess.render(bgmInput, { filename: path.resolve('bgm-less-test.less') });
const bgmCss = bgmResult.css;
let bgmChecks = 0;
for (const bgmFamily of ['tertiary', 'quaternary', 'quinary', 'senary']) {
    for (const bgmComponent of ['section', 'tile', 'card', 'button', 'text']) {
        assert.ok(bgmCss.includes(`.bgm-${bgmComponent}-${bgmFamily}`), `${bgmFamily}/${bgmComponent}`);
        bgmChecks++;
    }
    for (const bgmState of [':hover', ':focus-visible', ':active', ':disabled', '[aria-disabled="true"]']) {
        assert.ok(bgmCss.includes(`.bgm-button-${bgmFamily}${bgmState}`), `${bgmFamily}/${bgmState}`);
        bgmChecks++;
    }
    assert.ok(bgmCss.includes(`.bgm-card-${bgmFamily}.uk-card-hover:hover`));
    assert.ok(bgmCss.includes(`.bgm-tile-${bgmFamily}.uk-tile-hover:hover`));
    bgmChecks += 2;
}
for (const bgmFamily of ['quaternary', 'quinary', 'senary']) {
    for (const bgmContainer of [
        `.bgm-section-${bgmFamily}:not(.uk-preserve-color)`,
        `.bgm-tile-${bgmFamily}:not(.uk-preserve-color)`,
        `.bgm-card-${bgmFamily}.uk-card-body`,
        `.bgm-card-${bgmFamily} > :not([class*="uk-card-media"])`,
    ]) {
        assert.ok(bgmCss.includes(`${bgmContainer} .uk-button-default`), `${bgmContainer} native inverse extension`);
        bgmChecks++;
    }
}
const bgmChanged = await bgmLess.render(bgmInput + '@bgm-tertiary-background: #123456;\n@bgm-senary-section-color-mode: dark;\n@inverse-global-color-mode: dark;', { filename: path.resolve('bgm-less-test.less') });
assert.match(bgmChanged.css, /\.bgm-section-tertiary\s*\{[^}]*background:\s*#123456;/);
assert.ok(bgmChanged.css.includes('.bgm-section-senary:not(.uk-preserve-color) .uk-button-default'));
bgmChecks += 2;
console.log(JSON.stringify({ status: 'pass', checks: bgmChecks, compiler: bgmLess.version.join('.'), compiledBytes: bgmCss.length, customVariableOverride: true, darkModeOverride: true }, null, 2));
