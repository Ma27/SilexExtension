<?php
namespace Ma27\Kernel;

use Ma27\Kernel\Event\Events;
use Ma27\Kernel\Event\GenerateKernelPathsEvent;
use Ma27\Kernel\Event\InitSilexKernelEvent;
use Ma27\Kernel\Event\RegisterConfigurationEvent;
use Ma27\Kernel\EventListener\GenerateKernelPathsListener;
use Ma27\Kernel\EventListener\LoadConfigurationListener;
use Ma27\Kernel\Module\ModuleInterface;
use Ma27\Kernel\Value\KernelKeys;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Abstract kernel which extends the silex container. This kernel
 * provides better structures, environment-depended configuration and
 * a configuration loader which loads parameters from config files dynamically
 * into this container and an advanced response handler.
 *
 * @package Ma27\Kernel
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
abstract class Kernel extends Application
{
    /**
     * List of all registered modules
     * @var \SplObjectStorage
     */
    private $modules;

    /**
     * Sets important parameters (execution environment and debugging mode)
     * of the app and creates the object storage for the modules
     *
     * @param string  $env     Current execution environment
     * @param boolean $debug   Debugging mode
     * @param string  $rootDir Root directory of the application
     *
     * @api
     */
    public function __construct($env, $debug = false, $rootDir = null)
    {
        $rootDir = null !== $rootDir ? $rootDir : dirname((new \ReflectionObject($this))->getFileName());
        parent::__construct(
            [
                KernelKeys::ENVIRONMENT => (string)$env,
                KernelKeys::ROOT_DIR    => (string)$rootDir,
                'debug'                 => (bool)$debug
            ]
        );

        $this->modules = new \SplObjectStorage();
    }

    /**
     * Returns a list of all registered modules
     *
     * @param boolean $toArray Is this variable true, the object storage will be transformed to an array
     *
     * @return \Ma27\Kernel\Module\ModuleInterface[]|\SplObjectStorage
     *
     * @api
     */
    public function getModules($toArray = false)
    {
        if (true === $toArray) {
            return iterator_to_array($this->modules);
        }
        return $this->modules;
    }

    /**
     * Creates a setup of the application. It loads the modules, handlers and
     * the configuration
     *
     * @throws \RuntimeException If no configuration exists after triggering the config event
     *
     * @return void
     *
     * @api
     */
    public function loadAppModules()
    {
        // kernel path generation
        $this->on(
            Events::PATH_CREATION,
            array(new GenerateKernelPathsListener(), 'onGeneration'),
            static::LATE_EVENT
        );

        $this->extend(
            'dispatcher',
            function (EventDispatcher $dispatcher, Application $app)
            {
                $dispatcher->dispatch(Events::PATH_CREATION, $event = new GenerateKernelPathsEvent($app));
                if (!array_key_exists(KernelKeys::CONFIG_DIR, $paths = $event->getPaths())) {
                    throw new \LogicException(sprintf('A config directory is required in '
                        . 'this environment and should be registered with alias %s!', KernelKeys::CONFIG_DIR));
                }

                foreach ($paths as $alias => $path) {
                    if (!file_exists($path)) {
                        throw new \LogicException(sprintf('Path %s not found!', $path));
                    }
                    
                    $app->offsetSet($alias, $path);
                }

                return $dispatcher;
            }
        );

        // application setup
        $this->createModuleSetup();
        $this->registerResponseHandler();

        // custom setup
        $this->extend(
            'dispatcher',
            function (EventDispatcher $dispatcher, Application &$app)
            {
                $dispatcher->dispatch(Events::INITIALIZATION, new InitSilexKernelEvent($app));

                return $dispatcher;
            }
        );

        // configuration
        $this->on(
            Events::CONFIGURE,
            array(
                new LoadConfigurationListener(),
                'onRegisterConfiguration'
            ),
            static::LATE_EVENT
        );
        $this['dispatcher']->dispatch(Events::CONFIGURE, $configEvent = new RegisterConfigurationEvent($this));

        if ($configEvent->hasConfiguration()) {
            foreach ($configEvent->getConfiguration() as $key => $value) {
                $this->offsetSet($key, $value);
            }
            return;
        }

        throw new \RuntimeException('No configuration found!');
    }

    /**
     * Creates a setup of all registered modules. This method injects the
     * routes, providers, services and controllers of all modules into the app container
     * and maintains them
     *
     * @throws \UnexpectedValueException If one of the modules is not instance of \Ma27\Kernel\Module\ModuleInterface
     *
     * @return void
     *
     * @api
     */
    protected function createModuleSetup()
    {
        $app = &$this;

        foreach ($this->registerModules() as $module) {
            if (!$module instanceof ModuleInterface) {
                throw new \UnexpectedValueException(sprintf(
                    'Class %s must be an instance of ModuleInterface!',
                    get_class($module)
                ));
            }

            $module->injectControllers($this);
            $module->registerControllerProviders($this);
            $module->registerServices($this);
            $module->initialize($this);

            $this->after(
                function () use ($app, $module)
                {
                    $module->shutdown($app);
                }
            );

            $this->modules->attach($module);
        }
    }

    /**
     * Boots the application. The boot-event of the modules
     * will be triggered. After that this method calls the boot method
     * of the silex container
     *
     * @return void
     *
     * @api
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->modules as $module) {
            $module->boot($this);
        }

        parent::boot();
        $this->booted = true;
    }

    /**
     * Registers a handler which creates responses
     * by the AppResponse
     *
     * @return void
     *
     * @api
     */
    public function registerResponseHandler()
    {
        $app = &$this;
        $this->on(
            KernelEvents::VIEW,
            function (GetResponseForControllerResultEvent $event) use ($app)
            {
                $appResponse = $event->getControllerResult();
                $request     = $event->getRequest();
                if (!$appResponse instanceof AppResponse) {
                    return;
                }

                /**
                 * @var $handler \Closure
                 */
                if (null !== $handler = $request->attributes->get(KernelKeys::RESPONSE_LAMBDA)) {
                    if (
                        is_object($handler) &&
                        method_exists($handler, '__invoke')
                    ) {
                        $handlerResponse = $handler($appResponse);
                        if ($handlerResponse instanceof Response) {
                            $event->setResponse($handlerResponse);
                            return;
                        }

                        if ($handlerResponse instanceof AppResponse) {
                            $appResponse = $handlerResponse;
                        }
                    } else {
                        throw new \LogicException('Given app response handler must be an instance ' .
                            'of "Closure"!');
                    }
                }

                if (true === $request->isXmlHttpRequest()) {
                    $values = $appResponse->attributes->all();
                    $event->setResponse(
                        new JsonResponse(json_encode($values))
                    );

                    return;
                }

                if (!isset($app[$alias = $appResponse->engine])) {
                    throw new \LogicException(sprintf('Engine "%s" to render templates not exist!', $alias));
                }
                $engine = $app[$alias];

                $result   = $engine->render($appResponse->getTemplate(), $appResponse->getViewAttributes());
                $response = new Response($result);

                $event->setResponse($response);
            }
        );
    }

    /**
     * Creates and returns a list of all modules. You can create a list or load them
     * from a configuration file
     *
     * @return \Ma27\Kernel\Module\ModuleInterface[]
     *
     * @abstract
     *
     * @api
     */
    abstract public function registerModules();
} 