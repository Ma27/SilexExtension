<?php
namespace Ma27\SilexExtension;

use Silex\Application;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Igorw\Silex\ConfigServiceProvider;

/**
 * Extension of the silex framework
 * 
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright (c) 2014 - 2018, Maximilian Bosch
 */
abstract class Kernel extends Application
{
    /**
     * Value of the current execution environment
     * @var string
     */
    private $env;
    
    /**
     * List of all registered bundles
     * @var \Ma27\SilexExtension\BundleInterface[]
     */
    private $bundles = array();
    
    /**
     * Creates a setup of the application
     * 
     * @param string  $env   Sets the current execution environment
     * @param boolean $debug Value of the debugging mode
     * 
     * @api
     */
    public function __construct($env, $debug = true)
    {
        parent::__construct(['debug' => (bool)$debug]);
        $this->env = (string)$env;
        
        $this->setUpApplication();
        $this->generateView();
        $this->loadConfiguration();
        
        $this->init();
    }
    
    /**
     * Returns the value of the current 
     * execution environment
     * 
     * @return string
     * 
     * @api
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * Returns a list of all bundles
     * 
     * @return \Ma27\SilexExtension\BundleInterface[]
     * 
     * @api
     */
    public function getBundles()
    {
        return $this->bundles;
    }
    
    /**
     * Loads a configuration file which loads  
     * other files containing configuration parameters
     * 
     * @throws \RuntimeException If the configuration file cannot be found
     * @throws \RuntimeException If one defined file with parameters is inexistent
     * 
     * @return void
     * 
     * @api
     */
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
            
            $this->register(new ConfigServiceProvider($filePath));
        }
    }
    
    /**
     * Generates the file path of a configuration file with 
     * extension
     * 
     * @param string $fileName Name of the file
     * 
     * @return string
     * 
     * @api
     */
    private function generatePropertyFile($fileName)
    {
        return $this->getApplicationConfigPath() . '/' . $fileName;
    }
    
    /**
     * Creates a setup of the application:<br />
     * <ul>
     *   <li>Registers the given bundles</li>
     *   <li>Stores the configured output filters</li>
     * </ul>
     * 
     * @throws \LogicException   If one given bundle is not type of \Ma27\SilexExtension\BundleInterface
     * @throws \LogicException   If a filter is not type of \Ma27\SilexExtension\FilterInterface
     * @throws \RuntimeException If the file containing the required filters cannot be found
     * 
     * @return void
     * 
     * @api
     */
    protected function setUpApplication()
    {
        // setup bundles
        $bundles = (array)$this->registerBundles();
        foreach ($bundles as $bundle) {
            if (!$bundle instanceof BundleInterface) {
                throw new \LogicException(sprintf('Bundle %s must be type of '
                    . '%s!', get_class($bundle), 'Ma27\SilexExtension\BundleInterface'));
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
                    . 'type of %s', get_class($actionHandler), 'Ma27\SilexExtension\FilterInterface'));
            }
            
            $this[Parameters::HANDLER_STACK] = array_merge($this[Parameters::HANDLER_STACK], [$actionHandler]);
        }
        
        // change resolver
        $this->extend('resolver', function($prevResolver, $app) {
            return new ControllerResolver($app, $app['logger']);
        });
    }
    
    /**
     * Generates the view by result of the controllers:<br />
     * <ul>
     *   <li>Executes the output filter</li>
     *   <li>If there's no valid response, a response will be generated</li>
     * </ul>
     * 
     * @return void
     * 
     * @api
     */
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
            
            $response = $app->createResponse($template);
            if (get_class($response) !== '\Symfony\Component\HttpFoundation\Response') {
                $event->setResponse($response);
                return;
            }
            
            try {
                if (!isset($app[Parameters::TEMPLATE_ENGINE])) {
                    $report = null;
                    throw new \LogicException($report);
                }
                
                $content = $response->getContent();
                $newResponse = $app->createResponse(
                    $app[Parameters::TEMPLATE_ENGINE]->render($content, $attributes));
            } catch (\Exception $ex) {
                if (null !== $ex->getMessage()) {
                    throw $ex;
                }
                
                $response->headers->add($attributes);
                $newResponse = clone $response;
            }
            $event->setResponse($newResponse);
       });
    }
    
    /**
     * Empty method to override for a custom 
     * initialization
     * 
     * @return void
     * 
     * @api
     */
    protected function init()
    {
    }
    
    /**
     * Creates a response by its output
     * 
     * @param mixed    $output          Output of the controller
     * @param string[] $additionHeaders Additional headers to send with the response
     * 
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * 
     * @api
     */
    public function createResponse($output, array $additionHeaders = [])
    {
        if ($output instanceof Response) {
            foreach ($additionHeaders as $key => $value) {
                $output->headers->set($key, $value);
            }
            return $output;
        }
        if ($output instanceof \SplFileInfo) {
            return new BinaryFileResponse($output, 200, $additionHeaders);
        }
        if (is_object($output) || is_array($output)) {
            return new JsonResponse(json_encode($output, 200, $additionHeaders));
        }
        
        json_decode($output);
        if (json_last_error() === JSON_ERROR_NONE && !empty($output)) {
            return new JsonResponse($output, 200, $additionHeaders);
        }
        return new Response($output, 200, $additionHeaders);
    }
    
    /**
     * Abstract method to register custom app 
     * bundles
     * 
     * @return \Ma27\SilexExtension\BundleInterface[]
     * 
     * @abstract
     * 
     * @api
     */
    abstract public function registerBundles();
    
    /**
     * Returns the configuration path of all config files
     * 
     * @return string
     * 
     * @abstract
     * 
     * @api
     */
    abstract public function getApplicationConfigPath();
    
    /**
     * Creates a unique id of a controller action
     * 
     * @param string $controllerName   Name of the controller
     * @param string $controllerMethod Name of the action
     * 
     * @return string
     * 
     * @static
     * 
     * @api
     */
    public static function generateControllerActionId($controllerName, $controllerMethod)
    {
        return md5(sprintf('%s|%s', $controllerName, $controllerMethod));
    }
}