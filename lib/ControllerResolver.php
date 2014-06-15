<?php
namespace Ma27\SilexExtension;

use Silex\ControllerResolver as BaseResolver;
use Symfony\Component\HttpFoundation\Request;

class ControllerResolver extends BaseResolver
{
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