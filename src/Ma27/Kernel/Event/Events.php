<?php
namespace Ma27\Kernel\Event;

/**
 * Enum which contains the events of the silex kernel as class
 * constants
 *
 * @package Ma27\Kernel\Event
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
final class Events
{
    /**
     * The initialization event gives the developer the possibility
     * to create a custom setup of the application kernel. It will be triggered
     * <b>after</b> the module setup
     *
     * @var string
     *
     * @api
     */
    const INITIALIZATION = 'silex.kernel.init';

    /**
     * This event will be triggered <b>after</b> the setup of the
     * services. The listeners of this event store configuration in the event
     * object
     *
     * @var string
     *
     * @api
     */
    const CONFIGURE      = 'silex.kernel.config';

    /**
     * This event will be triggered <b>before</b> the setup of the services
     * and application. Here will be generated the core paths (in the default listener the config, cache and locale
     * path) to avoid generating paths all the time. <i>Note:</i> the generation of a config path
     * is required, but not the generation of other paths
     *
     * @var string
     *
     * @api
     */
    const PATH_CREATION  = 'silex.kernel.paths';
} 