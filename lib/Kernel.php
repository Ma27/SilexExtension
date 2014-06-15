<?php
namespace Ma27\SilexExtension;

use Silex\Application;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            $actionId   = $app[Parameters::CURRENT_ACTION_ID];
            $attributes = array();
            foreach ($app[Parameters::HANDLER_STACK] as $handler) {
                if (in_array($actionId, $handler->getSubscribedControllers())) {
                    $list = $handler->filterResponse($event);
                    
                    $attributes = array_merge($attributes, (array)$list);
                }
            }
            
            if ($event->hasResponse()) {
                $event->setResponse($app->createResponse($event->getResponse()));
                return;
            }
            
            $template = $event->getRequest()->attributes->get(Parameters::TEMPLATE_KEY);
            if (null === $template) {
                $template = $event->getControllerResult();
            }
            
            try {
                if (!isset($app[Parameters::TEMPLATE_ENGINE])) {
                    throw new \Exception(null);
                }
                
                $event->setResponse($app->createResponse(
                    $app[Parameters::TEMPLATE_ENGINE]->render($template, $attributes)));
                return;
            } catch (\Exception $ex) {
                if (null !== $app['logger'] && null === $ex->getMessage()) {
                    $app['logger']->critical('Render exception occurred: ' . $ex->getMessage());
                }
                
                $event->setResponse($app->createResponse($template, $attributes));
            }
        });
    }
    
    protected function init()
    {
    }
    
    public function createResponse($output, array $additionHeaders = [])
    {
        if ($output instanceof Response) {
            foreach ($additionHeaders as $key => $value) {
                $output->headers->set($key, $value);
            }
            return $output;
        }
        if ($output instanceof \SplFileInfo) {
            return new BinaryFileResponse($output, $additionHeaders);
        }
        if (is_object($output) || is_array($output)) {
            return new JsonResponse(json_encode($output, $additionHeaders));
        }
        
        json_decode($output);
        if (json_last_error() === JSON_ERROR_NONE && !empty($output)) {
            return new JsonResponse($output, $additionHeaders);
        }
        return new Response($output, $additionHeaders);
    }
    
    abstract public function registerBundles();
    abstract public function getApplicationConfigPath();
    
    public static function generateControllerActionId($controllerName, $controllerMethod)
    {
        return md5(sprintf('%s|%s', $controllerName, $controllerMethod));
    }
}