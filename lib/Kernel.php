<?php
namespace Ma27\SilexExtension;

use Silex\Application;

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
        
    }
    
    protected function setUpApplication()
    {
        
    }
    
    protected function generateView()
    {
        
    }
    
    protected function init()
    {
    }
    
    abstract public function registerBundles();
    abstract public function getApplicationConfigPath();
}