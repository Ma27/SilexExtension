<?php
namespace Ma27\Kernel\Tests\EventListener;

use Ma27\Kernel\Event\GenerateKernelPathsEvent;
use Ma27\Kernel\EventListener\GenerateKernelPathsListener;
use Ma27\Kernel\Value\KernelKeys;
use Silex\Application;

/**
 * @package Ma27\Kernel\Tests\EventListener
 * @coversDefaultClass \Ma27\Kernel\EventListener\GenerateKernelPathsListener
 */
class GenerateKernelPathsListenerTest extends \PHPUnit_Framework_TestCase
{
    private $event;
    private $rootDir;
    private $requiredPaths = array('config', 'cache', 'locale');

    public function setUp()
    {
        $app = new Application(array(KernelKeys::ROOT_DIR => $this->rootDir = ''));
        $this->event = new GenerateKernelPathsEvent($app);
    }

    public function tearDown()
    {
        $this->event   = null;
        $this->rootDir = null;
    }

    /**
     * @covers ::onGeneration
     * @api
     */
    public function testGenerationBehavior()
    {
        $event = &$this->event;

        $listener = new GenerateKernelPathsListener();
        $listener->onGeneration($event);

        $this->assertSame(count($this->requiredPaths), count($paths = $event->getPaths()));
        foreach ($this->requiredPaths as $requirement) {
            $this->assertContains($this->generatePath($requirement), $paths);
        }
    }

    private function generatePath($subPath)
    {
        return $this->rootDir . '/' . (string)$subPath;
    }
}