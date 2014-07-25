<?php
namespace Ma27\Kernel\Controller;

use Pimple\Container as PimpleContainer;
use Pimple\ServiceProviderInterface;

/**
 * Service provider to inject a controller in the app container
 *
 * @package Ma27\Kernel\Controller
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
class ControllerServiceInjectionProvider implements ServiceProviderInterface
{
    /**
     * Name of the class to inject
     * @var string
     */
    private $className;

    /**
     * Name of the module containing the class
     * @var string
     */
    private $moduleName;

    /**
     * Sets the class and the module to validate the before the
     * injection
     *
     * @param string $className  Name of the class to inject
     * @param string $moduleName Name of the module containing the class
     *
     * @throws \LogicException If the class does not exist
     *
     * @api
     */
    public function __construct($className, $moduleName)
    {
        $this->className = (string)$className;
        if (!class_exists($this->className)) {
            throw new \LogicException(sprintf('Controller %s cannot be found!', $this->className));
        }

        $this->moduleName = (string)$moduleName;
    }

    /**
     * Register event of the injection provider. By triggering this event
     * the currently stored class and the module will be injected
     * into the app container
     *
     * @param PimpleContainer $app
     *
     * @return void
     *
     * @api
     */
    public function register(PimpleContainer $app)
    {
        $classSplit = explode('\\', $this->className);
        $class      = end($classSplit);
        $classAlias = ((bool)preg_match('/^.*controller$/i', $class)) ? substr($class, 0, -10) : $class;
        $alias      = sprintf('controller.%s.%s', mb_strtolower($this->moduleName), mb_strtolower($classAlias));
        $class      = $this->className;

        $app[$alias] = function ($container) use ($class) {
            $instance = new $class;
            if ($instance instanceof Controller) {
                $instance->setContainer($container);
            }

            return $instance;
        };
    }
}