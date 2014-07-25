<?php
namespace Ma27\Kernel\Event;

use Silex\Application;
use Symfony\Component\EventDispatcher\Event;

/**
 * Abstract event of any silex kernel event which
 * contains the application to handle the events of the kernel
 *
 * @package Ma27\Kernel\Event
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
abstract class AbstractEvent extends Event
{
    /**
     * App instance
     * @var \Silex\Application
     */
    private $app;

    /**
     * Sets the application
     *
     * @param Application $app
     *
     * @api
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Returns the application
     *
     * @return Application
     *
     * @api
     */
    public function getApp()
    {
        return $this->app;
    }
} 