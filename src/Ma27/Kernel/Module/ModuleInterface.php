<?php
namespace Ma27\Kernel\Module;

use Silex\Application;

/**
 * Interface with the method of every silex
 * module used by this kernel
 *
 * @package Ma27\Kernel\Module
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
interface ModuleInterface
{
    /**
     * Returns the default class namespace of the module
     *
     * @return string
     *
     * @api
     */
    public function getNamespace();


    /**
     * Returns the root path of the module
     *
     * @return string
     *
     * @api
     */
    public function getPath();

    /**
     * Returns the name of the module
     *
     * @return string
     *
     * @api
     */
    public function getName();

    /**
     * Boots the application before request handling
     *
     * @param Application $kernel Application core
     *
     * @return void
     *
     * @api
     */
    public function boot(Application &$kernel);

    /**
     * Shuts the module off. This method will be triggered after
     * that the response was sent
     *
     * @param Application $kernel Application core
     *
     * @return void
     *
     * @api
     */
    public function shutdown(Application &$kernel);

    /**
     * Initializes the application. This method will be triggered after the
     * registration of the app services, providers and controllers.
     *
     * @param Application $kernel Application core
     *
     * @return void
     *
     * @api
     */
    public function initialize(Application &$kernel);

    /**
     * Injects the controllers into the app. You could iterate a directory
     * or register those statically
     *
     * @param Application $kernel Application core
     *
     * @return void
     *
     * @api
     */
    public function injectControllers(Application &$kernel);

    /**
     * Registers and initializes the app services. Actually it's better to register
     * in this method providers only which are responsible for the registration
     * of the services
     *
     * @param Application $kernel Application core
     *
     * @return void
     *
     * @api
     */
    public function registerServices(Application &$kernel);

    /**
     * Registers all providers which associate controllers with routes. In simple
     * modules you can use this method for plain route definitions
     *
     * @param Application $kernel Application core
     *
     * @return void
     *
     * @api
     */
    public function registerControllerProviders(Application &$kernel);
} 