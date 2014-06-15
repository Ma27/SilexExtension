<?php
namespace Ma27\SilexExtension;

use Silex\Application;
use Symfony\Component\HttpKernel\KernelEvents;

abstract class Kernel extends Application
{
    private $env;
    private $bundles = array();
    
    public function __construct($env, $debug = true)
    {
        parent::__construct(['debug' => (bool)$debug]);
        $this->env = (string)$env;
        
        $this->setUpApplication();
        $this->generateView();
        $this->loadConfiguration();
        
        $this->init();
    }
    
    public function getEnv()
    {
        return $this->env;
    }

    public function getBundles()
    {
        return $this->bundles;
    }
    
    protected function loadConfiguration()
    {
        $containerConfig = $this->getApplicationConfigPath() . '/app_' . $this->getEnv() . '.php';
        if (!file_exists($containerConfig)) {
            throw new \RuntimeException(sprintf('Please create required config '
                . 'config file %s!', $containerConfig));
        }
        
        foreach (require $containerConfig as $propertyList) {
            $filePath = $this->generatePropertyFile($propertyList);
            if (!file_exists($filePath)) {
                throw new \RuntimeException(sprintf('Given property file (%s) cannot '
                    . 'be found!', $filePath));
            }
            
            $this->register(new \Igorw\Silex\ConfigServiceProvider($filePath));
        }
    }
    
    private function generatePropertyFile($fileName)
    {
        return $this->getApplicationConfigPath() . '/' . $fileName;
    }
    
    protected function setUpApplication()
    {
        // setup bundles
        $bundles = (array)$this->registerBundles();
        foreach ($bundles as $bundle) {
            if (!$bundle instanceof BundleInterface) {
                throw new \LogicException(sprintf('Bundle %s must be type of '
                    . '%s!', get_class($bundle), BundleInterface::class));
            }
            
            $bundle->onSetUp($this);
            $bundle->attachRoutes($this);
            $bundle->createDependencies($this);
            
            $this->bundles[] = $bundle;
        }
        
        // setup handlers
        $handlers = $this->generatePropertyFile('handlers.php');
        if (!file_exists($handlers)) {
            throw new \RuntimeException(sprintf('Handler file (%s) not found!', 
                $handlers));
        }
        
        $this[Parameters::HANDLER_STACK] = array();
        foreach (require $handlers as $actionHandler) {
            if (!$actionHandler instanceof FilterInterface) {
                throw new \LogicException(sprintf('Filter object %s must be '
                    . 'type of %s', get_class($actionHandler), FilterInterface::class));
            }
            
            $this[Parameters::HANDLER_STACK][] = $actionHandler;
        }
        
        // change resolver
        $this->extend('resolver', function($prevResolver, $app) {
            return new ControllerResolver($app, $app['logger']);
        });
    }
    
    protected function generateView()
    {
        $app = &$this;
        $this->on(KernelEvents::TERMINATE, function($event) use($app) {
            foreach ($app->getBundles() as $bundle) {
                $bundle->onShutDown($app);
            }
        });
        
        $this->on(KernelEvents::VIEW, function($event) use($app) {
            $actionId = $app[Parameters::CURRENT_ACTION_ID];
            foreach ($app[Parameters::HANDLER_STACK] as $handler) {
                if (in_array($actionId, $handler->getSubscribedControllers())) {
                    $handler->filterResponse($event);
                }
            }
            
            if ($event->hasResponse()) {
                return;
            }
            
            
        });
    }
    
    protected function init()
    {
    }
    
    public function createResponse($output, array $additionHeaders = [])
    {
        
    }
    
    abstract public function registerBundles();
    abstract public function getApplicationConfigPath();
    
    public static function generateControllerActionId($controllerName, $controllerMethod)
    {
        return md5(sprintf('%s|%s', $controllerName, $controllerMethod));
    }
}