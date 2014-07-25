<?php
namespace Ma27\Kernel\Tests\Module;

use Ma27\Kernel\Tests\Fixtures\Bundle;
use Silex\Application;

/**
 * @package Ma27\Kernel\Tests\Module
 * @coversDefaultClass \Ma27\Kernel\Module\Module
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Bundle
     */
    private $module;

    public function setUp()
    {
        $this->module = new Bundle();
    }

    public function tearDown()
    {
        $this->module = null;
    }

    /**
     * @covers ::injectControllers
     * @api
     */
    public function testContainerLoad()
    {
        $c      = $this->createMockKernel();
        $module = $this->module;

        $module->injectControllers($c);

        $name   = mb_strtolower($module->getName());
        $alias  = 'controller.' . $name . '.mockobject';

        $this->assertTrue(isset($c[$alias]));
        $this->assertInstanceOf('\Ma27\Kernel\Tests\Fixtures\Controller\MockObjectController', $c[$alias]);
    }

    private function createMockKernel()
    {
        return new Application();
    }
}