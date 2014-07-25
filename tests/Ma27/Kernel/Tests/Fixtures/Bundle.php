<?php
namespace Ma27\Kernel\Tests\Fixtures;

use Ma27\Kernel\Module\Module;

class Bundle extends Module
{
    public function getControllerPath()
    {
        return 'Controller';
    }
}