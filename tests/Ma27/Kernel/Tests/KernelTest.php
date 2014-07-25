<?php
namespace Ma27\Kernel\Tests;

use Ma27\Kernel\AppResponse;
use Ma27\Kernel\Event\Events;
use Ma27\Kernel\Event\GenerateKernelPathsEvent;
use Ma27\Kernel\Value\KernelKeys;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @package Ma27\Kernel\Tests
 * @coversDefaultClass \Ma27\Kernel\Kernel
 */
class KernelTest extends \PHPUnit_Framework_TestCase
{
    const KERNEL_ENV = 'test';

    private $kernel;

    public function setUp()
    {
        $class = '\Ma27\Kernel\Kernel';
        $this->assertTrue(class_exists($class));

        $mock = $this->getMockForAbstractClass($class, array(static::KERNEL_ENV, false));

        $mock->on(Events::PATH_CREATION, function (GenerateKernelPathsEvent $event) {
            $event->setPaths(array(KernelKeys::CONFIG_DIR => __DIR__ . '/Fixtures/Config'));
            $event->stopPropagation();
        });
        $this->kernel = $mock;
    }

    /**
     * @covers ::__construct
     * @api
     */
    public function testKernelParameters()
    {
        $kernel = $this->kernel;

        $this->assertSame(static::KERNEL_ENV, $kernel[KernelKeys::ENVIRONMENT]);
        $this->assertFalse($kernel['debug']);
    }

    /**
     * @covers ::registerResponseHandler
     * @api
     */
    public function testResponseHandler()
    {
        $kernel = $this->kernel;
        $kernel['engine.mock'] = $this->createDummyEngine();
        $kernel->registerResponseHandler();

        $dispatcher  = $kernel['dispatcher'];
        $event       = $this->createControllerResultEvent();
        $resultEvent = $dispatcher->dispatch(KernelEvents::VIEW, $event);
        $ctrlResult  = $event->getControllerResult();
        $content     = $resultEvent->getResponse();

        $this->assertSame($ctrlResult->getTemplate(), $content->getContent());

        $session = $resultEvent->getRequest()->getSession();
        $this->assertTrue($session->has('asdf'));
        $this->assertSame('test', $session->get('asdf'));
    }

    /**
     * @covers ::registerResponseHander
     * @api
     */
    public function testResponseHandlerWithXMLHttpRequest()
    {
        $kernel = $this->kernel;
        $kernel['engine.mock'] = $this->createDummyEngine();
        $kernel->registerResponseHandler();

        // setup event
        $dispatcher = $kernel['dispatcher'];
        $event      = $this->createControllerResultEvent();
        $request    = $event->getRequest();

        // mark request as ajax request
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $event      = new GetResponseForControllerResultEvent(
            $event->getKernel(), $request, $event->getRequestType(), $event->getControllerResult()
        );

        // dispatch event
        $result     = $dispatcher->dispatch(KernelEvents::VIEW, $event);
        $response   = $result->getResponse();

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);

    }

    /**
     * @covers ::createModuleSetup
     * @api
     */
    public function testModuleSystem()
    {
        $kernel = $this->createKernelWithModuleList();
        $kernel->loadAppModules();

        $this->assertSame($kernel->registerModules(), $kernel->getModules(true));
        $module = $kernel->getModules(true)[0];

        $this->assertInstanceOf('\Ma27\Kernel\Module\ModuleInterface', $module);
    }

    private function createKernelWithModuleList()
    {
        $mock = $this->kernel;

        $class = '\Ma27\Kernel\Module\Module';
        $this->assertTrue(class_exists($class));
        $module = $this->getMock($class);

        $module->expects($this->any())
               ->method('getName')
               ->will($this->returnValue('MockModule'));
        $module->expects($this->any())
               ->method('getNamespace')
               ->will($this->returnValue(''));

        $mock->expects($this->any())
            ->method('registerModules')
            ->will($this->returnValue(array($module)));

        return $this->kernel = $mock;
    }

    private function createControllerResultEvent()
    {
        $httpKernel = $this->kernel->offsetGet('kernel');
        $request    = Request::create('/');
        $type       = HttpKernelInterface::MASTER_REQUEST;
        $result     = $this->createDummyAppResponse();

        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $request->attributes->set(
            KernelKeys::RESPONSE_LAMBDA,
            function($appResponse) use($session)
            {
                $session->set('asdf', 'test');
            }
        );

        return new GetResponseForControllerResultEvent($httpKernel, $request, $type, $result);
    }

    private function createDummyEngine()
    {
        $mock = $this->getMock('\Ma27\Kernel\Tests\Fixtures\AppResponseRenderEngineInterface');
        $mock->expects($this->any())
             ->method('render')
             ->will($this->returnArgument(0));

        return $mock;
    }

    private function createDummyAppResponse()
    {
        $response = new AppResponse();
        $response->render('test', array(), 'engine.mock');

        return $response;
    }

    public function tearDown()
    {
        $this->kernel = null;
    }
}