<?php
/** Original BGM implementation. GPL-2.0-or-later. */
namespace Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration;

final class BgmColourRegistry
{
    public const FAMILIES = ['tertiary' => 'Tertiary', 'quaternary' => 'Quaternary', 'quinary' => 'Quinary', 'senary' => 'Senary'];

    /** Native field names and element type names are external API identifiers. */
    public const FIELDS = [
        'section' => ['style' => 'section'],
        'column' => ['style' => 'panel'],
        'panel' => ['panel_style' => 'panel', 'title_color' => 'text'],
        'grid' => ['panel_style' => 'panel', 'title_color' => 'text'],
        'grid_item' => ['panel_style' => 'panel'],
        'panel-slider' => ['panel_style' => 'panel', 'title_color' => 'text'],
        'panel-slider_item' => ['panel_style' => 'panel'],
        'popover' => ['card_style' => 'card', 'title_color' => 'text'],
        'gallery' => ['title_color' => 'text'],
        'description_list' => ['title_color' => 'text'],
        'map' => ['title_color' => 'text'],
        'table' => ['title_color' => 'text'],
        'headline' => ['title_color' => 'text'],
        'text' => ['text_color' => 'text'],
        'button_item' => ['button_style' => 'button'],
    ];

    public static function options(string $kind): array
    {
        $options = [];
        foreach (self::FAMILIES as $family => $label) {
            foreach ($kind === 'panel' ? ['card', 'tile'] : [''] as $shape) {
                $options['BGM ' . ($shape ? ucfirst($shape) . ' ' : '') . $label] = 'bgm-' . ($shape ? "$shape-" : '') . $family;
            }
        }
        return $options;
    }

    /** Returns [native-shaped prefix, CSS component, family], or null. */
    public static function choice(string $value, string $kind): ?array
    {
        foreach (self::FAMILIES as $family => $_) {
            if ($kind === 'panel') {
                foreach (['card', 'tile'] as $shape) {
                    if ($value === "bgm-$shape-$family") {
                        return ["$shape-", $shape, $family];
                    }
                }
            } elseif ($value === "bgm-$family") {
                return ['', $kind, $family];
            }
        }
        return null;
    }
}
