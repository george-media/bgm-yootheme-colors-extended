<?php
/** Original BGM implementation. GPL-2.0-or-later. */
namespace Bgm\Plugin\System\BgmYoothemeColorsExtended\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;
use YOOtheme\Application;
use YOOtheme\Builder;
use YOOtheme\View;
use Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration\BgmModule;

final class BgmYoothemeColorsExtended extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;
    private bool $bgmLoaded = false;

    public static function getSubscribedEvents(): array
    {
        return ['onAfterInitialise' => ['onAfterInitialise', Priority::LOW]];
    }

    public function onAfterInitialise(): void
    {
        if ($this->bgmLoaded || !class_exists(Application::class, false)
            || !class_exists(Builder::class) || !class_exists(View::class)) {
            return;
        }
        $this->bgmLoaded = true;
        BgmModule::load(Application::getInstance(), function (): void {
            if ($this->getApplication()->isClient('administrator')) {
                $this->getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_BGM_YOOTHEME_COLORS_EXTENDED_CONFLICT'), 'warning');
            }
        });
    }
}
