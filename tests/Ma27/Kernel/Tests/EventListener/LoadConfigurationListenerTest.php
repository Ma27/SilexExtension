<?php
namespace Ma27\Kernel\Tests\EventListener;

use Ma27\Kernel\Event\RegisterConfigurationEvent;
use Ma27\Kernel\EventListener\LoadConfigurationListener;
use Ma27\Kernel\Value\KernelKeys;
use Silex\Application;

/**
 * @package Ma27\Kernel\Tests\EventListener
 * @coversDefaultClass \Ma27\Kernel\EventListener\LoadConfigurationListener
 */
class LoadConfigurationListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RegisterConfigurationEvent
     */
    private $event;
    private $expectedConfigurationResult = array(
        'key1' => 'value1',
        'key2' => 'value2'
    );

    public function setUp()
    {
        $env       = 'test';
        $configDir = __DIR__ . '/../Fixtures/Config';

        $app = new Application(
            [
                KernelKeys::CONFIG_DIR  => $configDir,
                KernelKeys::ENVIRONMENT => $env
            ]
        );
        $this->event = new RegisterConfigurationEvent($app);
    }

    public function tearDown()
    {
        $this->event = null;
    }

    /**
     * @covers ::onRegisterConfiguration
     * @api
     */
    public function testConfigurationListenerResult()
    {
        $listener = new LoadConfigurationListener();
        $listener->onRegisterConfiguration($this->event);

        $this->assertTrue($this->event->hasConfiguration());
        $this->assertEquals($this->event->getConfiguration(), $this->expectedConfigurationResult);
    }
}