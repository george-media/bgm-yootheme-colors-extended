<?php
/** Read-only contract tests against an external, licensed YOOtheme installation. */
declare(strict_types=1);
define('_JEXEC', 1);

$bgmVendor = $argv[1] ?? '';
if (!is_file($bgmVendor . '/vendor/autoload.php')) {
    fwrite(STDERR, "Usage: php bgm-tests/bgm-native-api.php /absolute/yootheme/root\n");
    exit(2);
}
require $bgmVendor . '/vendor/autoload.php';
YOOtheme\Url::setBase('https://bgm-synthetic.invalid/');
spl_autoload_register(static function ($class) {
    $prefix = 'Bgm\\Plugin\\System\\BgmYoothemeColorsExtended\\';
    if (str_starts_with($class, $prefix)) {
        require dirname(__DIR__) . '/bgm-plugin/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    }
});

use Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration\BgmColourRegistry;
use Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration\BgmRenderAdapter;
use Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration\BgmSchemaListener;
use Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration\BgmView;
use Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration\BgmModule;
use YOOtheme\View;
use YOOtheme\View\FileLoader;
use YOOtheme\View\HtmlElement;
use YOOtheme\View\HtmlHelper;
use YOOtheme\View\StrHelper;

$bgmChecks = 0;
function bgmAssert(bool $pass, string $label): void {
    global $bgmChecks;
    if (!$pass) { throw new RuntimeException($label); }
    $bgmChecks++;
}
function bgmView(?BgmRenderAdapter &$adapter, bool $enabled = true): View {
    global $bgmVendor;
    $view = $enabled ? new BgmView(new FileLoader()) : new View(new FileLoader());
    new HtmlHelper($view);
    new StrHelper($view);
    $view->addFunction('parallaxOptions', static fn() => []);
    $view->addFunction('uid', static fn() => 'bgm-synthetic');
    if ($enabled) {
        $adapter = new BgmRenderAdapter($bgmVendor . '/packages/builder/elements');
        $adapter->register($view);
    }
    return $view;
}
function bgmProps(string $type): array {
    global $bgmVendor;
    $dir = $bgmVendor . '/packages/builder/elements/' . $type;
    $typeData = require $dir . '/element.php';
    $props = $typeData['defaults'] ?? [];
    foreach (glob($dir . '/templates/*.php') as $file) {
        preg_match_all('/\$(?:props|element)\[\x27([^\x27]+)\x27\]/', file_get_contents($file), $matches);
        foreach ($matches[1] as $field) { $props[$field] ??= ''; }
    }
    return $props;
}

$view = bgmView($adapter);
$html = $view['html'];
$section = $bgmVendor . '/packages/builder/elements/section/templates/template.php';
$panel = $bgmVendor . '/packages/builder/elements/panel/templates/template.php';
$text = $bgmVendor . '/packages/builder/elements/text/templates/template.php';
$untouched = ['props' => ['style' => 'primary']];
bgmAssert($adapter->load($section, $untouched, fn($n, $p) => json_encode($p)) === json_encode($untouched), 'Stock parameters unchanged');
$malicious = ['props' => ['style' => 'bgm-tertiary onclick=x']];
bgmAssert($adapter->load($section, $malicious, fn($n, $p) => json_encode($p)) === json_encode($malicious), 'Unknown and malicious values unchanged');
$foreign = ['props' => ['style' => 'third-party-purple']];
bgmAssert($adapter->load($section, $foreign, fn($n, $p) => json_encode($p)) === json_encode($foreign), 'Third-party choice unchanged');
$el = $view->el('h2', ['class' => ['uk-text-{text_color}'], 'data-test' => ['{text_color}']], 'Content');
$expression = $el->attrs['class'];
$out = $adapter->load($text, ['props' => ['text_color' => 'bgm-tertiary']], function ($n, $p) use ($el) { return $el($p['props']); });
bgmAssert(str_contains($out, 'class="bgm-text-tertiary"'), 'Exact class mapped on dynamic heading');
bgmAssert($el->attrs['class'] === $expression, 'Original reusable expression preserved');
bgmAssert(str_contains($adapter->load($text, ['props' => ['text_color' => 'bgm-senary']], fn($n, $p) => $el($p['props'])), 'class="bgm-text-senary"'), 'Second BGM render recomputes another family');
bgmAssert(str_contains($el(['text_color' => 'primary']), 'class="uk-text-primary"'), 'Repeated expression recomputes');
bgmAssert(str_contains($out, 'data-test="bgm-render-'), 'Non-class attributes left unchanged');
$nested = $adapter->load($text, ['props' => ['text_color' => 'bgm-tertiary']], function ($n, $p) use ($adapter, $el) {
    return $adapter->load('/unrelated/template.php', $p, fn($n, $p) => $el($p['props']));
});
bgmAssert($nested === $el(['text_color' => 'bgm-tertiary']), 'Unrelated nested invocation byte-identical after exact owned sentinel restoration');
try { $adapter->load($text, ['props' => ['text_color' => 'bgm-tertiary']], static function () { throw new RuntimeException('synthetic'); }); } catch (RuntimeException $e) {}
bgmAssert($el(['text_color' => 'bgm-tertiary']) === '<h2 class="uk-text-bgm-tertiary" data-test="bgm-tertiary">Content</h2>', 'Exception cleans adapter context');
$calls = 0;
$html->addTransform('h2', function ($element, $params) use (&$calls) { $calls++; $element->attrs['data-downstream'] = 'kept'; $element->attrs['class'][] = 'bgm-downstream-class'; $element->attrs['data-expression'] = HtmlElement::expr($element->attrs['class'], $params); $element->contents .= '!'; return null; });
$out = $adapter->load($text, ['props' => ['text_color' => 'bgm-tertiary']], fn($n, $p) => $el($p['props']));
bgmAssert($calls === 1 && str_contains($out, 'data-downstream="kept"') && str_contains($out, 'Content!'), 'Downstream attrs/content transform runs exactly once');
bgmAssert(str_contains($out, 'bgm-text-tertiary bgm-downstream-class') && str_contains($out, 'data-expression="bgm-text-tertiary bgm-downstream-class"'), 'Downstream array append and native expression evaluation preserved');
$out = $adapter->load($text, ['props' => ['text_color' => 'bgm-senary']], fn($n, $p) => $el($p['props']));
bgmAssert($calls === 2 && str_contains($out, 'bgm-text-senary bgm-downstream-class') && substr_count($out, 'Content!') === 1 && $el->contents === 'Content', 'Repeated per-render downstream callback keeps reusable original intact');
$html->addTransform('h2', static fn() => 'short-circuit');
bgmAssert($adapter->load($text, ['props' => ['text_color' => 'bgm-tertiary']], fn($n, $p) => $el($p['props'])) === 'short-circuit', 'Downstream truthy short-circuit honoured');
$falseyView = bgmView($falseyAdapter);
$falseyCalls = [];
foreach (['', '0', null] as $result) {
    $falseyView['html']->addTransform('span', function () use (&$falseyCalls, $result) { $falseyCalls[] = $result; return $result; });
}
$falseyEl = $falseyView->el('span', ['class' => ['uk-text-{text_color}']], 'Synthetic');
$out = $falseyAdapter->load($text, ['props' => ['text_color' => 'bgm-quinary']], fn($n, $p) => $falseyEl($p['props']));
bgmAssert($falseyCalls === ['', '0', null] && str_contains($out, 'bgm-text-quinary'), 'Native falsey callback results continue in order');
$filtered = new BgmView(new FileLoader());
new HtmlHelper($filtered);
$unrelated = static function ($name, $params) use ($filtered) { return $filtered->el('p', ['class' => ['uk-text-{text_color}']], 'Unrelated')($params['props']); };
$filtered->addLoader($unrelated, '/unrelated/before.php');
$filteredAdapter = new BgmRenderAdapter($bgmVendor . '/packages/builder/elements');
$filteredAdapter->register($filtered);
$filtered->addLoader($unrelated, '/unrelated/after.php');
foreach (['before', 'after'] as $order) {
    $out = $filteredAdapter->load($text, ['props' => ['text_color' => 'bgm-tertiary']], fn($n, $p) => $filtered->render("/unrelated/$order.php", $p));
    bgmAssert($out === '<p class="uk-text-bgm-tertiary">Unrelated</p>', "$order-registered filtered short-circuit gets inactive invocation guard");
}
$post = bgmView($postAdapter);
$post->addLoader(function ($name, $params, $next) use ($post) {
    return $next($name, $params) . $post->el('p', ['class' => ['uk-text-{text_color}']], 'Postprocess')($params['props']);
}, $text);
$out = $post->render($text, ['props' => array_replace(bgmProps('text'), ['text_color' => 'bgm-tertiary', 'content' => 'Native']), 'attrs' => []]);
bgmAssert(str_contains($out, 'bgm-text-tertiary') && str_contains($out, '<p class="uk-text-bgm-tertiary">Postprocess</p>'), 'Filtered postprocessing inactive after active native inner evaluation');
$redirected = new BgmView(new FileLoader());
new HtmlHelper($redirected);
$redirected->addLoader(function ($name, $params, $next) use ($redirected) {
    if ($name === '/unrelated/destination.php') { return $redirected->el('p', ['class' => ['uk-text-{text_color}']], 'Redirected')($params['props']); }
    return $next($name, $params);
});
$redirectedAdapter = new BgmRenderAdapter($bgmVendor . '/packages/builder/elements');
$redirectedAdapter->register($redirected);
$redirected->addLoader(static fn($name, $params, $next) => $next('/unrelated/destination.php', $params), $text);
$out = $redirected->render($text, ['props' => ['text_color' => 'bgm-tertiary']]);
bgmAssert($out === '<p class="uk-text-bgm-tertiary">Redirected</p>', 'Filtered path change checked at actual forwarded path');
$filtered->addLoader(static function () { throw new RuntimeException('filtered synthetic'); }, '/unrelated/throws.php');
try { $filtered->render('/unrelated/throws.php', ['props' => ['text_color' => 'bgm-tertiary']]); } catch (RuntimeException $e) {}
bgmAssert($filtered->render('/unrelated/after.php', ['props' => ['text_color' => 'bgm-tertiary']]) === '<p class="uk-text-bgm-tertiary">Unrelated</p>', 'Filtered exception cleans universal invocation guard');
$redirected->addLoader(static fn($name, $params, $next) => $next('/unrelated/destination.php', $params));
bgmAssert($redirected->render($text, ['props' => ['text_color' => 'bgm-tertiary']]) === '<p class="uk-text-bgm-tertiary">Redirected</p>', 'Late unfiltered path change cannot activate a non-native evaluator');
$throwView = bgmView($throwAdapter);
$throwOnce = true;
$throwView['html']->addTransform('div', function () use (&$throwOnce) {
    if ($throwOnce) { $throwOnce = false; echo 'must not leak'; throw new RuntimeException('actual native evaluation synthetic'); }
    return null;
});
$bufferLevel = ob_get_level();
try { $throwView->render($text, ['props' => array_replace(bgmProps('text'), ['text_color' => 'bgm-tertiary', 'content' => 'Throws']), 'attrs' => []]); } catch (RuntimeException $e) {}
$rootFlag = false;
$throwView->addLoader(function ($name, $params, $next) use (&$rootFlag) { $rootFlag = $params['_root']; return $next($name, $params); }, $text);
$out = $throwView->render($text, ['props' => array_replace(bgmProps('text'), ['text_color' => 'bgm-senary', 'content' => 'Sibling']), 'attrs' => []]);
bgmAssert(ob_get_level() === $bufferLevel && $rootFlag && str_contains($out, 'bgm-text-senary') && !str_contains($out, 'must not leak') && !str_contains($out, 'bgm-render-'), 'Actual native evaluation throw cleans output buffers, native frames and BGM contexts before sibling');

// Actual native templates, not copied render fixtures.
$view = bgmView($adapter);
$nativeView = bgmView($unused, false);
$builder = new class { public function render($nodes): string { return ''; } };
$props = bgmProps('section');
$props['html_element'] = 'section';
$props['style'] = 'primary';
$params = ['props' => $props, 'attrs' => [], 'children' => [], 'builder' => $builder];
bgmAssert($view->render($section, $params) === $nativeView->render($section, $params), 'Native section output byte-identical for stock choice');
$params['props']['style'] = 'bgm-tertiary';
$out = $view->render($section, $params);
bgmAssert(str_contains($out, '<section class="bgm-section-tertiary uk-section"'), 'Actual native section custom tag and BGM class');
bgmAssert(!str_contains($out, 'bgm-render-'), 'Section sentinel fully consumed');
$params['props']['html_element'] = 'bgm-custom-element';
$out = $view->render($section, $params);
bgmAssert(str_contains($out, 'uk-section-bgm-tertiary') && !str_contains($out, 'bgm-render-'), 'Unknown custom HTML tag fails closed without issuing a sentinel');
$props = bgmProps('panel');
$props = array_replace($props, ['panel_style' => 'bgm-card-tertiary', 'panel_link' => true, 'panel_link_hover' => true, 'link' => '/synthetic', 'title' => 'Synthetic title', 'title_element' => 'h3', 'title_color' => 'bgm-quaternary']);
$out = $view->render($panel, ['props' => $props, 'attrs' => []]);
bgmAssert(str_contains($out, 'bgm-card-tertiary uk-card-hover'), 'Actual native linked card keeps hover trigger');
bgmAssert(str_contains($out, 'bgm-text-quaternary'), 'Native nested content title re-tokenises inherited props');
bgmAssert(!str_contains($out, 'bgm-render-'), 'Panel and subtemplate sentinels fully consumed');
$builder = new YOOtheme\Builder(static fn($file) => require $file, [$view, 'render'], ['attrs' => []]);
foreach (['grid', 'grid_item', 'column', 'button', 'button_item'] as $type) {
    $builder->addType($type, require $bgmVendor . "/packages/builder/elements/$type/element.php");
}
$gridProps = array_replace(bgmProps('grid'), ['panel_style' => 'bgm-card-tertiary', 'title_color' => 'bgm-senary', 'title_element' => 'h3', 'panel_link' => true, 'panel_link_hover' => true]);
$childProps = array_replace(bgmProps('grid_item'), ['title' => 'Synthetic first', 'link' => '/synthetic']);
$grid = (object) ['type' => 'grid', 'props' => $gridProps, 'children' => [
    (object) ['type' => 'grid_item', 'props' => $childProps],
    (object) ['type' => 'grid_item', 'props' => array_replace($childProps, ['title' => 'Synthetic second', 'panel_style' => 'card-primary'])],
]];
$before = serialize($grid);
$out = $builder->render($grid);
bgmAssert(substr_count($out, 'bgm-card-tertiary') === 1 && str_contains($out, 'uk-card-primary') && substr_count($out, 'bgm-text-senary') === 2, 'Actual multi-item grid inheritance and stock child override');
bgmAssert(!str_contains($out, 'bgm-render-') && serialize($grid) === $before, 'Multi-item rendering consumes sentinels without node mutation');
$column = (object) ['type' => 'column', 'props' => array_replace(bgmProps('column'), ['html_element' => 'hgroup', 'style' => 'bgm-card-quinary']), 'children' => []];
$out = $builder->render($column);
bgmAssert(str_contains($out, '<hgroup') && str_contains($out, 'bgm-card-quinary') && !str_contains($out, 'bgm-render-'), 'Actual native column hgroup with inner custom card');
$button = (object) ['type' => 'button', 'props' => bgmProps('button'), 'children' => [
    (object) ['type' => 'button_item', 'props' => array_replace(bgmProps('button_item'), ['button_style' => 'bgm-quaternary', 'content' => 'Synthetic button', 'link' => '/synthetic'])],
]];
$out = $builder->render($button);
bgmAssert(str_contains($out, 'uk-button bgm-button-quaternary') && str_contains($out, 'href="/synthetic"') && !str_contains($out, 'bgm-render-'), 'Actual native button group retains link and structure');
foreach (['description_list_item' => 'description_list', 'table_item' => 'table'] as $child => $parent) {
    $props = bgmProps($child);
    $props['title'] = 'Synthetic title';
    $element = bgmProps($parent);
    $element['title_color'] = 'bgm-senary';
    $element['title_element'] = 'h4';
    $out = $view->render($bgmVendor . "/packages/builder/elements/$child/templates/template-title.php", compact('props', 'element'));
    bgmAssert(str_contains($out, 'bgm-text-senary') && !str_contains($out, 'bgm-render-'), "Actual $child inherited title template");
}
foreach (BgmColourRegistry::FIELDS as $name => $fields) {
    $type = require $bgmVendor . "/packages/builder/elements/$name/element.php";
    // Some native palette fields are interpolation references. Resolve through actual config.
    $config = new YOOtheme\Configuration\Configuration();
    $config->addFilter('url', static fn($value) => $value);
    $config->addFile('builder', $bgmVendor . '/packages/builder/config/builder.php');
    $type = $config->resolve($type, ['file' => $bgmVendor . "/packages/builder/elements/$name/element.php"]);
    $updated = BgmSchemaListener::filter($type);
    foreach (['html_element', 'item_element', 'title_element'] as $tagField) {
        foreach ($type['fields'][$tagField]['options'] ?? [] as $tagOption) {
            if (is_string($tagOption) && $tagOption !== '') {
                bgmAssert(!empty($view['html']->transforms[$tagOption]), "$name.$tagField native tag $tagOption registered before element creation");
            }
        }
    }
    bgmAssert(BgmSchemaListener::filter($updated) === $updated, "$name schema is idempotent");
    foreach ($fields as $field => $kind) {
        bgmAssert(count($updated['fields'][$field]['options']) === count($type['fields'][$field]['options']) + ($kind === 'panel' ? 8 : 4), "$name.$field has exact expected additions");
    }
    if (in_array($name, ['panel', 'grid', 'panel-slider'], true)) {
        bgmAssert(str_contains($updated['fields']['panel_link_hover']['enable'], 'bgm-'), "$name native hover enable extended");
    }
    foreach (['text_color', 'image_text_color', 'lightbox_text_color'] as $field) {
        if (!isset($fields[$field]) && isset($type['fields'][$field])) {
            bgmAssert($type['fields'][$field] === $updated['fields'][$field], "$name.$field native inverse control unchanged");
        }
    }
}
$collision = ['name' => 'text', 'fields' => ['text_color' => ['options' => ['BGM Tertiary' => 'third-party-purple', 'Stock primary' => 'primary']]]];
$updated = BgmSchemaListener::filter($collision);
bgmAssert($updated['fields']['text_color']['options']['BGM Tertiary'] === 'third-party-purple'
    && in_array('bgm-tertiary', $updated['fields']['text_color']['options'], true)
    && BgmSchemaListener::filter($updated) === $updated, 'Third-party label collision preserves both options idempotently');
function bgmApp(): YOOtheme\Application {
    global $bgmVendor;
    $app = new YOOtheme\Application();
    foreach (['application', 'view', 'builder', 'styler'] as $package) { $app->load($bgmVendor . "/packages/$package/bootstrap.php"); }
    // Register the actual native queue without booting Joomla or touching a database.
    $queue = tempnam(sys_get_temp_dir(), 'bgm-native-queue-');
    file_put_contents($queue, '<?php return ["loaders" => ["theme" => [YOOtheme\\Theme\\Joomla\\ThemeLoader::class, "load"]]];');
    try { $app->load($queue); } finally { unlink($queue); }
    return $app;
}
$partial = new YOOtheme\Application();
$partialDiagnostic = 0;
bgmAssert(!BgmModule::load($partial, function () use (&$partialDiagnostic) { $partialDiagnostic++; }) && $partialDiagnostic === 0, 'Partially loaded application fails capability gate without resolving services');
$app = bgmApp();
$extended = 0;
$app->extend(View::class, function ($view) use (&$extended): void { $extended++; $view->addGlobal('bgm-test-marker', 'kept'); });
$diagnostics = 0;
bgmAssert(BgmModule::load($app, function () use (&$diagnostics): void { $diagnostics++; }), 'Actual application accepts unresolved native View factory');
bgmAssert($app->get(YOOtheme\Config::class)->get('theme') === null, 'BGM module leaves native theme initialisation sentinel unset');
$queue = (new ReflectionClass(YOOtheme\Theme\Joomla\ThemeLoader::class))->getProperty('configs')->getValue();
$queued = end($queue);
bgmAssert(($queued['styles']['components']['bgm-colours'] ?? '') === dirname(__DIR__) . '/bgm-plugin/bgm-assets/bgm-less/bgm-colours.less', 'BGM components queued through actual native ThemeLoader');
$resolved = $app->get(View::class);
bgmAssert($resolved instanceof BgmView && $extended === 1 && $resolved->getGlobals()['bgm-test-marker'] === 'kept' && $diagnostics === 0, 'Native application extenders retained on BGM subclass resolution');
$earlyApp = bgmApp();
$early = $earlyApp->get(View::class);
bgmAssert(!BgmModule::load($earlyApp, function () use (&$diagnostics): void { $diagnostics++; }) && $earlyApp->get(View::class) === $early && $diagnostics === 1, 'Actual application early resolved View fails closed and keeps same instance');
$earlyStyleApp = bgmApp();
$earlyStyle = $earlyStyleApp->get(YOOtheme\Theme\Styler\StylerConfig::class);
bgmAssert(!BgmModule::load($earlyStyleApp, function () use (&$diagnostics): void { $diagnostics++; }) && $earlyStyleApp->get(YOOtheme\Theme\Styler\StylerConfig::class) === $earlyStyle && $diagnostics === 2, 'Actual application early resolved StylerConfig fails closed and keeps same instance');
$earlyThemeApp = bgmApp();
$earlyThemeApp->get(YOOtheme\Config::class)->set('theme', ['id' => 99, 'active' => true]);
bgmAssert(!BgmModule::load($earlyThemeApp, function () use (&$diagnostics): void { $diagnostics++; }) && $earlyThemeApp->get(YOOtheme\Config::class)->get('theme.id') === 99 && $diagnostics === 3, 'Already initialised native theme fails closed before queue registration');
$lateApp = bgmApp();
BgmModule::load($lateApp, function () use (&$diagnostics): void { $diagnostics++; });
$lateApp->add(View::class, static fn() => new View(new FileLoader()));
bgmAssert(!$lateApp->get(View::class) instanceof BgmView && $diagnostics === 4 && BgmSchemaListener::filter($collision) === $collision, 'Actual application late competing factory suspends schema and keeps ordinary View');
final class BgmSyntheticThemeParams extends ArrayObject {
    public function get($key, $default = null) { return $this[$key] ?? $default; }
}
final class BgmThemeLifecycleProbe extends YOOtheme\Theme\Joomla\ThemeLoader {
    public static function run(object $template): void { parent::loadConfiguration($template); }
    protected static function loadChildTheme(object $template, array $config): ?string { return null; }
    protected static function updateConfig(object $template, array $config): array { return $config; }
}
$lifecycleApp = YOOtheme\Application::getInstance();
$lifecycleConfig = $lifecycleApp->get(YOOtheme\Config::class);
$bgmModule = require dirname(__DIR__) . '/bgm-plugin/bgm-bootstrap.php';
YOOtheme\Theme\Joomla\ThemeLoader::load($lifecycleApp, [
    require $bgmVendor . '/packages/theme/config/theme.php',
    require $bgmVendor . '/packages/theme-consent/config/theme.php',
    ['styles' => ['components' => ['/bgm-synthetic/third-party.less']]],
    $bgmModule['theme'],
    ['styles' => ['components' => [1 => '/bgm-synthetic/later-third-party.less']]],
    $bgmModule['theme'],
]);
bgmAssert($lifecycleConfig->get('theme') === null, 'Actual native lifecycle starts with unset theme root');
BgmThemeLifecycleProbe::run((object) ['id' => 99, 'home' => 1, 'template' => 'bgm-synthetic', 'parent' => false,
    'params' => new BgmSyntheticThemeParams(['config' => json_encode(['version' => '5.0.52'])])]);
bgmAssert($lifecycleConfig->get('theme.id') === 99 && $lifecycleConfig->get('theme.active') === true && $lifecycleConfig->get('~theme.consent.type') === 'none', 'Actual native theme initialisation preserves identity and consent defaults');
$components = $lifecycleConfig->get('theme.styles.components');
bgmAssert(in_array('/bgm-synthetic/third-party.less', $components, true) && in_array('/bgm-synthetic/later-third-party.less', $components, true)
    && count(array_filter($components, static fn($value) => $value === dirname(__DIR__) . '/bgm-plugin/bgm-assets/bgm-less/bgm-colours.less')) === 1, 'Actual native theme merge preserves prior/later third-party LESS imports and repeated BGM configuration is idempotent');
$numericMerge = new YOOtheme\Configuration\Configuration();
$numericMerge->add('', $bgmModule['theme']);
$numericMerge->add('', ['styles' => ['components' => ['/bgm-synthetic/later-zero.less']]]);
bgmAssert(isset($numericMerge->get('styles.components')['bgm-colours']) && in_array('/bgm-synthetic/later-zero.less', $numericMerge->get('styles.components'), true), 'Actual native repository keeps named BGM import beside later numeric index zero');
YOOtheme\Path::setAlias('~theme', $bgmVendor);
$styler = new YOOtheme\Theme\Styler\Styler($lifecycleConfig);
$importListener = new YOOtheme\Theme\Styler\Listener\LoadStylerImports($lifecycleConfig, $styler);
$imports = $importListener->handle([], 'balou');
$bgmLess = dirname(__DIR__) . '/bgm-plugin/bgm-assets/bgm-less/bgm-colours.less';
bgmAssert(isset($imports[YOOtheme\Url::to($bgmLess)]), 'Actual native style import listener consumes named BGM component entry');
$nativeTheme = $bgmVendor . '/less/theme.balou.less';
bgmAssert(str_contains($imports[YOOtheme\Url::to($nativeTheme)], YOOtheme\Path::relative(dirname($nativeTheme), $bgmLess)), 'Actual native style import listener appends BGM LESS to selected theme source');
echo json_encode(['status' => 'pass', 'checks' => $bgmChecks, 'vendor' => trim((string) simplexml_load_file($bgmVendor . '/templateDetails.xml')->version)], JSON_PRETTY_PRINT) . "\n";
