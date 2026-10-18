<?php
/** Original BGM implementation. GPL-2.0-or-later. */
defined('_JEXEC') or die;

use Bgm\Plugin\System\BgmYoothemeColorsExtended\Extension\BgmYoothemeColorsExtended;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(PluginInterface::class, static function (Container $container): PluginInterface {
            $plugin = new BgmYoothemeColorsExtended($container->get(DispatcherInterface::class),
                (array) PluginHelper::getPlugin('system', 'bgm-yootheme-colors-extended'));
            $plugin->setApplication(Factory::getApplication());
            return $plugin;
        });
    }
};
