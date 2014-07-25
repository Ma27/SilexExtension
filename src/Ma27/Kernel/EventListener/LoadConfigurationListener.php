<?php
namespace Ma27\Kernel\EventListener;

use Ma27\Kernel\Event\RegisterConfigurationEvent;
use Ma27\Kernel\Value\KernelKeys;

/**
 * Listener of the "silex.kernel.config" event
 *
 * @package Ma27\Kernel\EventListener
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
class LoadConfigurationListener
{
    /**
     * Loads the configuration from a given directory depending
     * on the current execution environment
     *
     * @param RegisterConfigurationEvent $event Event object
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the container file which contains all names of configuration lists cannot be found
     * @throws \InvalidArgumentException If one of the configuration lists cannot be found
     *
     * @api
     */
    public function onRegisterConfiguration(RegisterConfigurationEvent $event)
    {
        $app = $event->getApp();

        $configPath = $app->offsetGet(KernelKeys::CONFIG_DIR);
        $container  = $configPath . '/container.php';
        if (!file_exists($container)) {
            throw new \InvalidArgumentException(sprintf('Container file %s not found!', $container));
        }

        $configRoot = $configPath . '/' . $app->offsetGet(KernelKeys::ENVIRONMENT);
        $config     = array();
        foreach (require $container as $item) {
            $file = $configRoot . '/' . $item . '.php';
            if (!file_exists($file)) {
                throw new \InvalidArgumentException(sprintf('Parameter list (%s) not found!', $file));
            }

            $config = array_merge($config, require $file);
        }

        $event->storeConfiguration($config);
    }
} 