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

The filter concept can be used to generate http responses