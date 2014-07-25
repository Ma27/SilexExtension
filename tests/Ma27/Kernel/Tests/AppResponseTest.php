<?php
namespace Ma27\Kernel\Tests;
use Ma27\Kernel\AppResponse;


/**
 * @package Ma27\Kernel\Tests
 * @coversDefaultClass \Ma27\Kernel\AppResponse
 */
class AppResponseTest extends \PHPUnit_Framework_TestCase
{
    private $response;

    /**
     * @covers ::__construct
     * @api
     */
    public function testResponseDefaults()
    {
        $this->createResponse($failed = false, $errors = array());

        $this->assertSame('twig', $this->response->engine);
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\ParameterBag', $this->response->attributes);
        $this->assertSame($failed, $this->response->isFailed());
        $this->assertSame($errors, $this->response->getErrors());
    }

    /**
     * @covers ::render
     */
    public function testRenderObj()
    {
        $this->createResponse(false, array());
        $template   = 'landing.html';
        $attributes = array('foo' => 'bar');
        $this->response->render($template, $attributes);

        $this->assertSame($template, $this->response->getTemplate());
        $this->assertSame($attributes, $this->response->getViewAttributes());
    }

    public function createResponse($failed, $errors)
    {
        $this->response = new AppResponse($failed, $errors);
    }

    /**
     * @after
     */
    public function tearDown()
    {
        $this->response = null;
    }
}