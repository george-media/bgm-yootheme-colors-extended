<?php
/** Original BGM implementation. GPL-2.0-or-later. */
defined('_JEXEC') or die;
use Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration\BgmColourRegistry;

$bgmGroups = [];
foreach (BgmColourRegistry::FAMILIES as $bgmFamily => $bgmLabel) {
    $bgmGroups[$bgmLabel] = '@bgm-' . $bgmFamily . '-*';
}
return ['components' => ['bgm-colours' => [
    'name' => 'BGM Colours',
    'general' => true,
    'groups' => $bgmGroups,
    'hover' => '[class*="bgm-section-"], [class*="bgm-card-"], [class*="bgm-tile-"], [class*="bgm-button-"], [class*="bgm-text-"]',
    'inspect' => '[class*="bgm-section-"], [class*="bgm-card-"], [class*="bgm-tile-"], [class*="bgm-button-"], [class*="bgm-text-"]',
]]];
