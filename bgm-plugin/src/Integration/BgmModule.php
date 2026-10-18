<?php
/** Original BGM implementation. GPL-2.0-or-later. */
namespace Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration;

use YOOtheme\Application;
use YOOtheme\Builder;
use YOOtheme\Config;
use YOOtheme\Theme\Styler\StylerConfig;
use YOOtheme\View;

final class BgmModule
{
    public static function load(Application $app, callable $diagnostic): bool
    {
        if (!$app->has(View::class) || !$app->has(Builder::class) || !$app->has(StylerConfig::class)) {
            return false;
        }
        // extend() invokes immediately for an existing instance, without resolving a new one.
        // Never replace a View already held by the theme or another extension.
        if ($app->get(Config::class)->get('theme') || self::resolved($app, View::class)
            || self::resolved($app, StylerConfig::class)) {
            $diagnostic();
            return false;
        }
        $app->load(dirname(__DIR__, 2) . '/bgm-bootstrap.php');
        // Extenders survive service factory changes. A later competing factory fails closed.
        $app->extend(View::class, static function (View $view) use ($diagnostic): void {
            if (!$view instanceof BgmView) {
                BgmSchemaListener::bgmDisable();
                $diagnostic();
            }
        });
        return true;
    }

    private static function resolved(Application $app, string $id): bool
    {
        $resolved = false;
        $app->extend($id, static function () use (&$resolved): void { $resolved = true; });
        return $resolved;
    }
}
