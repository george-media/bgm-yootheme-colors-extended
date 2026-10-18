<?php
/** Original BGM implementation. GPL-2.0-or-later. */
defined('_JEXEC') or die;
use Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration\BgmRenderAdapter;
use Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration\BgmSchemaListener;
use Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration\BgmView;
use YOOtheme\Builder;
use YOOtheme\Theme\Styler\StylerConfig;
use YOOtheme\View;
use YOOtheme\View\FileLoader;
use YOOtheme\View\HtmlHelper;
use YOOtheme\View\SectionHelper;
use YOOtheme\View\StrHelper;

return [
    // Queue through the platform's theme loader. Populating config('theme') before
    // native theme initialisation would make ThemeLoader skip all native defaults.
    // Named entries survive native array_replace_recursive merges with other extensions'
    // numeric component lists. The native import listener consumes values, not indexes.
    'theme' => ['styles' => ['components' => ['bgm-colours' => __DIR__ . '/bgm-assets/bgm-less/bgm-colours.less']]],
    'config' => [
        StylerConfig::class => __DIR__ . '/bgm-styler.php',
    ],
    'events' => ['builder.type' => [BgmSchemaListener::class => 'filter']],
    'services' => [
        View::class => static function (FileLoader $loader): View {
            $view = new BgmView($loader);
            $view->addGlobal('view', $view);
            foreach ([StrHelper::class, HtmlHelper::class, SectionHelper::class] as $helper) {
                $view->addHelper($helper);
            }
            $file = (new ReflectionClass(Builder::class))->getFileName();
            (new BgmRenderAdapter(dirname($file, 2) . '/elements'))->register($view);
            return $view;
        },
    ],
];
