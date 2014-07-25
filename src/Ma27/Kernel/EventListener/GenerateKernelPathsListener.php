<?php
namespace Ma27\Kernel\EventListener;

use Ma27\Kernel\Event\GenerateKernelPathsEvent;
use Ma27\Kernel\Value\KernelKeys;

/**
 * Listener of the "silex.kernel.paths" event
 *
 * @package Ma27\Kernel\EventListener
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
class GenerateKernelPathsListener
{
    /**
     * Generates the locale, config and cache path of the application
     * and stores them in the event object
     *
     * @param GenerateKernelPathsEvent $event Event object
     *
     * @return void
     *
     * @api
     */
    public function onGeneration(GenerateKernelPathsEvent $event)
    {
        $rootDir = $event->getApp()->offsetGet(KernelKeys::ROOT_DIR);

        $locales = $rootDir . '/locale';
        $config  = $rootDir . '/config';
        $cache   = $rootDir . '/cache';

        $event->setPaths(
            [
                KernelKeys::CACHE_DIR  => $cache,
                KernelKeys::CONFIG_DIR => $config,
                KernelKeys::LOCALE_DIR => $locales
            ]
        );
    }
} 