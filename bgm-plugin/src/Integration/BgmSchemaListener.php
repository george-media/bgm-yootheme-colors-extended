<?php
/** Original BGM implementation. GPL-2.0-or-later. */
namespace Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration;

final class BgmSchemaListener
{
    private static bool $bgmEnabled = true;

    public static function bgmDisable(): void
    {
        self::$bgmEnabled = false;
    }

    public static function filter(array $type): array
    {
        if (!self::$bgmEnabled) {
            return $type;
        }
        foreach (BgmColourRegistry::FIELDS[$type['name'] ?? ''] ?? [] as $field => $kind) {
            if (!isset($type['fields'][$field]['options']) || !is_array($type['fields'][$field]['options'])) {
                continue;
            }
            // Do not remove stock options, duplicate values, or overwrite third-party labels.
            foreach (BgmColourRegistry::options($kind) as $label => $value) {
                if (!in_array($value, $type['fields'][$field]['options'], true)) {
                    $candidate = $label;
                    $index = 2;
                    while (array_key_exists($candidate, $type['fields'][$field]['options'])) {
                        $candidate = $label . ' (' . $index++ . ')';
                    }
                    $type['fields'][$field]['options'][$candidate] = $value;
                }
            }
        }
        // Extend this verified native condition only. Preserve any third-party replacement.
        $prefixes = ['panel' => 'link', 'grid' => 'show_link', 'panel-slider' => 'show_link'];
        $prefix = $prefixes[$type['name'] ?? ''] ?? null;
        $native = $prefix . ' && panel_link && $match(panel_style, \'card-(default|primary|secondary|overlay)|tile-\')';
        if ($prefix && ($type['fields']['panel_link_hover']['enable'] ?? '') === $native) {
            $type['fields']['panel_link_hover']['enable'] = $prefix . ' && panel_link && ($match(panel_style, \'card-(default|primary|secondary|overlay)|tile-\') || $match(panel_style, \'^bgm-(card|tile)-(tertiary|quaternary|quinary|senary)$\'))';
        }
        return $type;
    }
}
