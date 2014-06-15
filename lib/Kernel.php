<?php
namespace Ma27\SilexExtension;

use Silex\Application;

abstract class Kernel extends Application
{
    private $env;
    
    public function __construct($env, $debug = true)
    {
        parent::__construct(['debug' => (bool)$debug]);
        $this->env = (string)$env;
        
        $this->init();
    }
    
    protected function init()
    {
    }
}