<?php
namespace Ma27\Kernel\Tests\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @package Ma27\Kernel\Tests\Controller
 * @covversDefaultClass \Ma27\Kernel\Controller\Controller
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
{
    public function provideMockContainer()
    {
        $container = new Application();
        $container['request_stack'] = new RequestStack();
        $container['request_stack']->push(Request::create('/'));
        $container['foo'] = 'bar';

        return array(array($container));
    }

    public function provideMockContainerWithInvalidRequestAndKernel()
    {
        $container = $this->provideMockContainer();
        $container[0][0]['request_stack'] = null;
        $container[0][0]['kernel']  = null;

        return $container;
    }

    public function mockController()
    {
        $class = '\Ma27\Kernel\Controller\Controller';
        $this->assertTrue(class_exists($class));

        return $this->getMockForAbstractClass($class);
    }

    /**
     * @covers ::redirect
     *
     * @api
     */
    public function testControllerRedirect()
    {
        $controller = $this->mockController();
        $url        = '/';
        $status     = 302;
        $headers    = array($headerName = 'test' => $headerValue = 'value');

        $response = $controller->redirect($url, $status, $headers);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);

        $this->assertSame($url, $response->getTargetUrl());
        $this->assertSame($status, $response->getStatusCode());
        $this->assertTrue($response->headers->has($headerName));
        $this->assertSame($headerValue, $response->headers->get($headerName));
    }

    /**
     * @covers ::json
     *
     * @api
     */
    public function testJsonStream()
    {
        $data = '{}';

        $status = 200;
        $headers = array($name = 'test' => $value = 'value');

        $result = $this->mockController()->json($data, $status, $headers);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $result);

        $this->assertSame($data, substr($result->getContent(), 1, -1));
        $this->assertSame($status, $result->getStatusCode());
        $this->assertTrue($result->headers->has($name));
        $this->assertSame($result->headers->get($name), $value);
        $this->assertTrue($result->headers->has('Content-Type'));
        $this->assertSame('application/json', $result->headers->get('Content-Type'));
    }

    /**
     * @covers ::getRequest
     * @dataProvider provideMockContainer
     *
     * @api
     */
    public function testRequestShortcut($app)
    {
        $controller = $this->mockController();
        $controller->setContainer($app);

        $request = $controller->getRequest();
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Request', $request);
        $this->assertSame('/', $request->server->get('REQUEST_URI'));
    }

    /**
     * @covers ::getRequest
     * @dataProvider provideMockContainerWithInvalidRequestAndKernel
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Request stack must be registered in app container!
     *
     * @api
     */
    public function testRequestShortcutWithoutRequestObject($app)
    {
        $controller = $this->mockController();
        $controller->setContainer($app);

        $controller->getRequest();
    }

    /**
     * @dataProvider provideMockContainer
     * @covers ::getKernel
     *
     * @api
     */
    public function testKernelShortcut($app)
    {
        $controller = $this->mockController();
        $controller->setContainer($app);

        $kernel = $controller->getKernel();
        $this->assertInstanceOf('\Symfony\Component\HttpKernel\HttpKernelInterface', $kernel);
    }

    /**
     * @covers ::getRequest
     * @dataProvider provideMockContainerWithInvalidRequestAndKernel
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Kernel must be registered in app container!
     *
     * @api
     */
    public function testKernelShortcutWithoutKernelObject($app)
    {
        $controller = $this->mockController();
        $controller->setContainer($app);

        $controller->getKernel();
    }

    /**
     * @dataProvider provideMockContainer
     * @covers ::forward
     *
     * @api
     */
    public function testSubRequestHandler($app)
    {
        $controller = $this->mockController();
        $controller->setContainer($app);

        $lambda = function(Request $request) {
            return 'content ' . $request->attributes->get('test_attr');
        };
        $subRequest = $controller->forward($lambda, array('test_attr' => $testResult = 'some value'));

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $subRequest);
        $this->assertSame('content ' . $testResult, $subRequest->getContent());
    }

    /**
     * @covers ::createAppResponse
     * @api
     */
    public function testAppResponseFactory()
    {
        $failed = false;
        $errors = array('success' => 'Success message');
        $values = array('dataset' => new \stdClass());

        $controller = $this->mockController();
        $response   = $controller->createAppResponse($failed, $errors, $values);

        $this->assertInstanceOf('\Ma27\Kernel\AppResponse', $response);
        $this->assertSame($failed, $response->isFailed());
        $this->assertSame($errors, $response->getErrors());
        $this->assertTrue($response->attributes->has('dataset'));
        $this->assertSame($values['dataset'], $response->attributes->get('dataset'));
    }
}
 