<?php
namespace Ma27\Kernel\Controller;

use Ma27\Kernel\AppResponse;
use Pimple\Container as PimpleContainer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Abstract class with basic functionality for application
 * controllers to handle view
 *
 * @package Ma27\Kernel\Controller
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
abstract class Controller
{
    /**
     * Container which contains all the properties of the application
     * @var \Pimple\Container
     */
    private $container;

    /**
     * Http kernel of the app
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    private $kernel;

    /**
     * Current app request
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * Sets the container with the data
     *
     * @param PimpleContainer $container Container to store
     *
     * @return $this
     *
     * @api
     */
    public function setContainer(PimpleContainer $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Returns a dependency from the given controller by its identifier.
     * If the dependency does not exist, the value of $default will be returned
     * (default is void)
     *
     * @param string|int $name    Identifier of the dependency to load
     * @param mixed      $default Default value to return if the dependency doesn't exist
     *
     * @return mixed
     *
     * @api
     */
    public function get($name, $default = null)
    {
        return isset($this->container[$name])
            ? $this->container[$name]
            : $default;
    }

    /**
     * Redirects the client to another page or application by
     * generating a response with target url, http status code and some
     * additional headers
     *
     * @param string   $url     Target url of the redirect
     * @param int      $status  Http status code of the redirect (default is 302)
     * @param string[] $headers List of additional headers
     *
     * @return RedirectResponse
     *
     * @api
     */
    public function redirect($url, $status = 302, array $headers = [])
    {
        return new RedirectResponse($url, $status, $headers);
    }

    /**
     * Creates a json stream and sends a json encoded dataset
     * to the user to enable clean interfaces
     *
     * @param string   $data    Json encoded dataset to send
     * @param int      $status  Status of the transaction (default is 200)
     * @param string[] $headers List of additional headers
     *
     * @return JsonResponse
     *
     * @api
     */
    public function json($data, $status = 200, array $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Shortcut to access the current app request
     * comfortably. If there's no request an exception of type
     * \OutOfBoundsException will be thrown
     *
     * @return \Symfony\Component\HttpFoundation\Request
     *
     * @throws \OutOfBoundsException If there's no request registered
     *
     * @api
     */
    public function getRequest()
    {
        if (null !== $this->request) {
            return $this->request;
        }
        if (null === $request = $this->get('request_stack')) {
            throw new \OutOfBoundsException(sprintf('Request stack must be registered in app container!'));
        }

        return $this->request = $request->getCurrentRequest();
    }

    /**
     * Shortcut to access a clone of the http kernel
     * comfortably. The kernel will be cloned to disable the possibility
     * of kernel-manipulation in a controller
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     *
     * @throws \OutOfBoundsException If there's no kernel registered
     *
     * @api
     */
    public function getKernel()
    {
        if (null !== $this->kernel) {
            return $this->kernel;
        }
        if (null === $kernel = $this->get('kernel')) {
            throw new \OutOfBoundsException(sprintf('Kernel must be registered in app container!'));
        }

        return $this->kernel = clone $kernel;
    }

    /**
     * Sends a sub-request from the current request to any sub-controller
     * with a controller and some attributes which will be stored in the request object
     *
     * @param mixed    $controller Sub-controller to call
     * @param string[] $attributes List of additional attributes
     * @param string[] $query      Additional query parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @api
     */
    public function forward($controller, array $attributes = array(), array $query = array())
    {
        $request = $this->getRequest()->duplicate(array(), null, $query);
        $request->attributes->add($attributes);
        $request->attributes->set('_controller', $controller);

        return $this->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Creates an app response with the result of any  before
     * executed business operation
     *
     * @param boolean  $failed Is business operation failed
     * @param string[] $errors List of all occurred errors
     * @param mixed[]  §values List of additional attributes
     *
     * @return AppResponse
     *
     * @api
     */
    public function createAppResponse($failed = false, array $errors = array(), array $values = array())
    {
        $response = new AppResponse($failed, $errors);
        $response->attributes->add($values);

        return $response;
    }
} 