<?php
namespace Ma27\SilexExtension;

use Silex\ControllerResolver as BaseResolver;
use Symfony\Component\HttpFoundation\Request;

/**
 * Custom resolver to get a controller by a string.<br />
 * Usage:
 * <p><code>
 * // at the controller
 * 
 * class Controller
 * {
 *    public function doSthAction()
 *    {
 *       return 'hello world';
 *    }
 * }
 * 
 * // register controller
 * 
 * $kernel['controller.default'] = new Controller();
 * 
 * 
 * // create route
 * 
 * $kernel->match('/', 'controller.default:doSth')->method('GET|POST');
 * </code></p>
 * 
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright (c) 2014 - 2018, Maximilian Bosch
 */
class ControllerResolver extends BaseResolver
{
    /**
     * Creates a controller by the current http request.
     * It creates the action id and initializes the controller
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Current http request
     * 
     * @return callable
     * 
     * @throws \InvalidArgumentException If the controller alias is empty
     * @throws \LogicException           If the controller alias is inexistent in the app contaienr
     * @throws \UnexpectedValueException If the controller is not an object
     * @throws \BadMethodCallException   If the controller-action does not exist
     * 
     * @api
     */
    public function getController(Request $request)
    {
        try {
            return parent::getController($request);
        } catch (\InvalidArgumentException $ex) {
            $controller = $request->attributes->get('_controller');
            if (null === $controller) {
                throw $ex;
            }
            
            list($alias, $method) = explode(':', $controller, 2);
            if (!isset($this->app[$alias])) {
                throw new \LogicException(sprintf('Controller with alias %s does '
                    . 'not exist in application!', $alias));
            }
            
            $controllerAction = sprintf('%sAction', $method);
            $controller = $this->app[$alias];
            
            if (!is_object($controller)) {
                throw new \UnexpectedValueException(sprintf('Controller (%s) must '
                    . 'be an object!', print_r($controller, true)));
            }
            if (!method_exists($controller, $controllerAction)) {
                throw new \BadMethodCallException(sprintf('Controller action %s not found '
                    . 'on object %s', $controllerAction, get_class($controller)));
            }
            
            if ($controller instanceof Controller) {
                $controller->setContainer($this->app);
            }
            
            $this->app[Parameters::CURRENT_ACTION_ID] = Kernel::generateControllerActionId(
                    get_class($controller), $controllerAction);
            return [$controller, $controllerAction];
        }
    }
}