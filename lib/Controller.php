<?php
namespace Ma27\SilexExtension;

use ArrayAccess;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class Controller
{
    private $container;
    
    public function setContainer(ArrayAccess $container)
    {
        $this->container = $container;
    }
    
    protected function get($ident, $default = null)
    {
        return (isset($this->container[$ident]))
            ? $this->container[$ident]
            : $default;
    }
    
    protected function getRequest()
    {
        $request = $this->get('request');
        if (null === $request) {
            throw new \LogicException(sprintf('Request object must be type of %s!', 
                'Symfony\Component\HttpFoundation\Request'));
        }
        
        return $request;
    }
    
    protected function getKernel()
    {
        $kernel = $this->get('kernel');
        if (null === $kernel) {
            throw new \LogicException(sprintf('Kernel instance must be type of %s!', 
                'Symfony\Component\HttpKernel\HttpKernelInterface'));
        }
        
        return clone $kernel;
    }
    
    protected function forward($controller, array $parameters = [])
    {
        $request = clone $this->getRequest();
        foreach ($parameters as $key => $value) {
            $request->attributes->set($key, $value);
        }
        
        return $this->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST);
    }
}