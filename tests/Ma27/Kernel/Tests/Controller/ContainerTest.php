<?php
namespace Ma27\Kernel\Tests\Controller;

use Ma27\Kernel\Controller\Container;

/**
 * @package Ma27\Kernel\Tests\Controller
 * @coversDefaultClass \Ma27\Kernel\Controller\Container
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Controller path (/any/invalid/path/subDir) does not exist!
     *
     * @api
     */
    public function testContainerWithInvalidControllerPath()
    {
        new Container('/any/invalid/path', 'subDir');
    }

    /**
     * @dataProvider getMockControllerDir
     * @covers ::__construct
     *
     * @api
     */
    public function testContainerWithClassContainerList($controllerDir, $subPath)
    {
        $container = new Container($controllerDir, $subPath);
        foreach ($container as $file) {
            $this->assertSame($file, 'MockObjectController');
        }

        $clear = iterator_to_array($container);
        $this->assertContains('MockObjectController', $clear);
        $this->assertCount(1, $clear);
    }

    public  function getMockControllerDir()
    {
        return array(array(__DIR__ . '/../Fixtures', 'Controller'));
    }
}