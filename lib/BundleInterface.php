<?php
namespace Ma27\SilexExtension;

/**
 * Interface of an application adapter
 * 
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @version 0.1
 */
interface BundleInterface
{
    /**
     * Method to create a setup of the module
     * 
     * @param \Ma27\SilexExtension\Kernel $kernel Application core
     * 
     * @return void
     * 
     * @api
     */
    public function onSetUp(Kernel &$kernel);
    
    /**
     * Method to register routes of the module
     * 
     * @param \Ma27\SilexExtension\Kernel $kernel Application core
     * 
     * @return void
     * 
     * @api
     */
    public function attachRoutes(Kernel &$kernel);
    
    /**
     * Method to create, register and setup the dependencies of the module
     * 
     * @param \Ma27\SilexExtension\Kernel $kernel Application core
     * 
     * @return void
     * 
     * @api
     */
    public function createDependencies(Kernel &$kernel);
    
    /**
     * Method which will be triggered after the sending of the response
     * 
     * @param \Ma27\SilexExtension\Kernel $kernel Application core
     * 
     * @return void
     * 
     * @api
     */
    public function onShutDown(Kernel &$kernel);
}