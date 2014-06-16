Silex Extension
===============


Philosophy:
-----------
This extension is developed to create silex applications with 
better structures

Usage:
------

###Using bundles:
The kernel class is abstract so you it is required to inherit this class:

    <?php
    class CustomKernel extends \Ma27\SilexExtension\Kernel
    {
        public function registerBundles()
        {
            return [
                new FooBundle
            ];
        }

        // implementation of other abstract methods
    }

A bundle is something like an adapter of any module for your application.
It contains 4 methods.
Sample implementation of FooBundle:

    <?php
    use Ma27\SilexExtension\Kernel;

    class FooBundle extends \Ma27\SilexExtension\BundleInterface
    {
        public function onSetUp(Kernel &$kernel)
        {
            // create a setup of your bundle
        }

        public function attachRoutes(Kernel &$kernel)
        {
            // register your routes
        }

        public function createDependencies(Kernel &$kernel)
        {
            // register providers and services of your module
        }

        public function onShutDown(Kernel &$kernel)
        {
            // do something after sending response headers
        }
    }

#### Hint:
It would be better if you use in attachRoutes() $kernel->mount()

#### Meaning of the methods
 * onSetUp: In this method you can create a custom setup of your module or register configuration parameters
 * attachRoutes: Here you can create routes of your modules
 * createDependencies: In this method you can register provider and custom services of your module
 * onShutDown: All commands containing this method will be executed after the response has been sended


### Filter concept

The filter concept can be used to generate http responses. Use concrete filters to create a 
view for a specific controllers.

    <?php
    namespace Foo;

    class Controller
    {
        public function currentAction()
        {
            return 'any execution which is executed currently';
        }
    }

    // at the filter
    use Foo\Controller;
    use Symfony\Component\HttpFoundation\Response;

    class CustomFilter implements \Ma27\SilexExtension\FilterInterface
    {
        public function filterResponse(\Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent &$result)
        {
            $result->setResponse(new Response(strtoupper($result->getControllerResult())));
        }

        public function getSubscribedActions()
        {
            return [\Ma27\SilexExtension\Kernel::generateControllerActionId(Controller::class, 'currentAction')];
        }
    }

    // output:
    ANY EXECUTION WHICH IS EXECUTED CURRENTLY


### Configuration

This extension has also a configuration concept. You can register files containing parameters which will be 
injected into the application container.

