<?php
namespace Ma27\Kernel\Module;

use Ma27\Kernel\Controller\Container;
use Ma27\Kernel\Controller\ControllerServiceInjectionProvider;
use Silex\Application as Kernel;

/**
 * Concrete implementation of a module which
 * is able to load controllers via an iterator
 *
 * @package Ma27\Kernel\Module
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
abstract class Module implements ModuleInterface
{
    /**
     * Default path of the module
     * @var string
     */
    private $path;

    /**
     * Name of the module
     * @var string
     */
    private $name;

    /**
     * Returns the namespace of the concrete module
     * which is required to load classes from their sub-directories in the
     * module directory as PSR-0/PSR-4
     *
     * @return string
     *
     * @api
     */
    public function getNamespace()
    {
        $class = get_class($this);
        return substr($class, 0, strrpos($class, '\\'));
    }

    /**
     * Returns the class name (without namespace) of the
     * concrete module. It will be used to search a module by
     * its name
     *
     * @return string
     *
     * @api
     */
    public function getName()
    {
        if (null !== $this->name) {
            return $this->name;
        }

        $fullName = get_class($this);
        $split    = explode('\\', $fullName);
        return $this->name = $split[count($split) - 1];
    }

    /**
     * Returns the sub-path of the module path which contains the
     * controllers to inject those via a GlobIterator
     *
     * @return string
     *
     * @api
     */
    public function getControllerPath()
    {
        return 'Controller';
    }

    /**
     * This event will be triggered before handling the main
     * HTTP Request and dispatch process
     *
     * @param Kernel $kernel Instance of the application
     *
     * @return void
     *
     * @api
     */
    public function boot(Kernel &$kernel)
    {
    }

    /**
     * Initializes the module. This event will be triggered after
     * the registration of services, providers and controllers to
     * enable a custom initialization.
     *
     * @param Kernel $kernel Instance of the application
     *
     * @return void
     *
     * @api
     */
    public function initialize(Kernel &$kernel)
    {
    }

    /**
     * Stops the module. This event will be triggered after the sending of the
     * response
     *
     * @param Kernel $kernel Instance of the application
     *
     * @return void
     *
     * @api
     */
    public function shutdown(Kernel &$kernel)
    {
    }

    /**
     * Returns the main path of the module. In this case the
     * path of the module class will be returned.
     *
     * @return string
     *
     * @api
     */
    public function getPath()
    {
        if (null !== $this->path) {
            return $this->path;
        }

        $object = new \ReflectionObject($this);
        return $this->path = dirname($object->getFileName());
    }

    /**
     * Registers all services of this module. In this case it's a
     * best practise to register the service providers which
     * are responsible for the service initialization.
     *
     * @param Kernel $kernel Instance of the app
     *
     * @return void
     *
     * @api
     */
    public function registerServices(Kernel &$kernel)
    {
    }

    /**
     * Registers the module routes or mounts controller providers with
     * their route definitions on the module
     *
     * @param Kernel $kernel Instance of the app
     *
     * @return void
     *
     * @api
     */
    public function registerControllerProviders(Kernel &$kernel)
    {
    }

    /**
     * This method uses an iterator to get all controllers
     * from the defined controller sub-path with an InjectionProvider.
     *
     * @param Kernel $kernel Instance of the kernel
     *
     * @return void
     *
     * @api
     */
    public function injectControllers(Kernel &$kernel)
    {
        $controllerContainer = iterator_to_array(
            new Container(
                $this->getPath(),
                $this->getControllerPath()
            )
        );

        foreach ($controllerContainer as $controller) {
            $kernel->register(
                new ControllerServiceInjectionProvider(
                    $this->getNamespace() . '\\' . $this->getControllerPath() . '\\' . $controller,
                    $this->getName()
                )
            );
        }
    }
}