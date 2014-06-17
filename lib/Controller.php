<?php
namespace Ma27\SilexExtension;

use ArrayAccess;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Optional controller for any application controller
 * 
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @version 0.1
 */
abstract class Controller
{
    /**
     * Simple container containing the application parameters and 
     * services
     * @var \ArrayAccess
     */
    private $container;
    
    /**
     * Sets the dependency cotainer
     * 
     * @param ArrayAccess $container Container to store
     * 
     * @return \Ma27\SilexExtension\Controller
     * 
     * @api
     */
    public function setContainer(ArrayAccess &$container)
    {
        $this->container = $container;
        return $this;
    }
    
    /**
     * Returns a value or dependency or the value of $default, if the value is 
     * inexistent
     * 
     * @param string $ident   Identifier of the dependency
     * @param mixed  $default Default value to return
     * 
     * @return mixed
     * 
     * @api
     */
    protected function get($ident, $default = null)
    {
        return (isset($this->container[$ident]))
            ? $this->container[$ident]
            : $default;
    }
    
    /**
     * Returns the current http request
     * 
     * @return \Symfony\Component\HttpFoundation\Request
     * 
     * @throws \LogicException If the request is inexistent
     * 
     * @api
     */
    protected function getRequest()
    {
        $request = $this->get('request');
        if (null === $request) {
            throw new \LogicException(sprintf('Request object must be type of %s!', 
                'Symfony\Component\HttpFoundation\Request'));
        }
        
        return $request;
    }
    
    /**
     * Returns the http kernel of the application
     * 
     * @return \Symfony\Component\HttpKernel\HttpKernel
     * 
     * @throws \LogicException If the kernel is inexistent
     * 
     * @api
     */
    protected function getKernel()
    {
        $kernel = $this->get('kernel');
        if (null === $kernel) {
            throw new \LogicException(sprintf('Kernel instance must be type of %s!', 
                'Symfony\Component\HttpKernel\HttpKernelInterface'));
        }
        
        return clone $kernel;
    }
    
    /**
     * Forwards a request to a sub-controller
     * 
     * @param string   $controller Identifier of the sub-action
     * @param string[] $parameters List of the parameters
     * 
     * @return mixed
     * 
     * @api
     */
    protected function forward($controller, array $parameters = [])
    {
        $request = clone $this->getRequest();
        foreach ($parameters as $key => $value) {
            $request->attributes->set($key, $value);
        }
        $currentId = $this->get(Parameters::CURRENT_ACTION_ID);
        
        $request->attributes->set('_controller', (string)$controller);
        $response = $this->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST);
        
        $this->container[Parameters::CURRENT_ACTION_ID] = $currentId;
        return $response;
    }
}