<?php
namespace Ma27\Kernel\Tests\Controller;

use Ma27\Kernel\Controller\ControllerServiceInjectionProvider;
use Ma27\Kernel\Tests\Fixtures\Controller\MockObjectController;
use Pimple\Container;

/**
 * @package Ma27\Kernel\Tests\Controller
 *
 * @coversDefaultClass \Ma27\Kernel\Controller\ControllerServiceInjectionProvider
 */
class ControllerServiceInjectionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Container
     *
     * @api
     */
    private function createPimple()
    {
        return new Container(array('test' => 'value'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     *
     * @api
     */
    private function createMockController()
    {
        return $this->getMockForAbstractClass('\Ma27\Kernel\Controller\Controller');
    }

    /**
     * @covers ::__construct
     * @expectedException \LogicException
     * @expectedExceptionMessage Controller \any\invalid\class cannot be found!
     *
     * @api
     */
    public function testInjectionWithInvalidClass()
    {
        $class = '\any\invalid\class';
        $this->assertFalse(class_exists($class));

        new ControllerServiceInjectionProvider($class, 'ModuleName');
    }

    /**
     * @covers ::register
     *
     * @api
     */
    public function testInjectionWithValidClass()
    {
        $class = get_class($this->createMockController());
        $this->assertTrue(class_exists($class));

        $container = $this->createPimple();
        $injection = new ControllerServiceInjectionProvider($class, $module = 'modulename');

        $injection->register($container);
        $this->assertTrue(isset($container[$alias = 'controller.' . $module . '.' . mb_strtolower($class)]));

        $this->assertInstanceOf($class, $container[$alias]);
    }

    /**
     * @covers ::register
     *
     * @api
     */
    public function testInjectionWithClassAndControllerSuffix()
    {
        $ctrl  = new MockObjectController();
        $class = get_class($ctrl);

        $container = $this->createPimple();
        $injection = new ControllerServiceInjectionProvider($class, $module = 'modulename');

        $injection->register($container);
        $alias = substr(end(explode('\\', $class)), 0, -10);

        $this->assertTrue(isset($container[$alias = 'controller.' . $module . '.' . mb_strtolower($alias)]));
    }
}
 