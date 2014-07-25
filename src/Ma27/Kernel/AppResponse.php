<?php
namespace Ma27\Kernel;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Domain model which represents a response
 * which can be handled by the silex kernel.
 *
 * @package Ma27\Kernel
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
class AppResponse
{
    /**
     * Name of the template engine which is responsible for the render process of the template
     * @var string
     */
    public $engine = 'twig';

    /**
     * Bag which contains all response attributes
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $attributes;

    /**
     * Value of the template to render
     * @var string
     */
    private $template;

    /**
     * List of all view attributes
     * @var string[]
     */
    private $parameters = array();

    /**
     * Is business operation failed
     * @var boolean
     */
    private $failed = false;

    /**
     * List of occurred errors
     * @var string[]
     */
    private $errors = array();

    /**
     * Sets the values of the response
     *
     * @param boolean  $failed Is business operation failed
     * @param string[] $errors List of all errors
     *
     * @api
     */
    public function __construct($failed = false, array $errors = array())
    {
        $this->attributes = new ParameterBag();
        $this->failed     = (bool)$failed;
        $this->errors     = $errors;
    }

    /**
     * Sets the data of the template, attributes and
     * desired template engine
     *
     * @param string   $template   Template to render
     * @param string[] $attributes View attributes
     * @param string   $engine     Alias of the engine
     *
     * @return $this
     *
     * @api
     */
    public function render($template, array $attributes = array(), $engine = 'twig')
    {
        $this->template   = (string)$template;
        $this->parameters = $attributes;
        $this->engine     = (string)$engine;

        return $this;
    }

    /**
     * Returns the current template to render
     *
     * @return string
     *
     * @api
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Returns a list of all view attributes which will be used
     * by the render process
     *
     * @return string[]
     *
     * @api
     */
    public function getViewAttributes()
    {
        return $this->parameters;
    }

    /**
     * Is business operation failed?
     *
     * @return boolean
     *
     * @api
     */
    public function isFailed()
    {
        return $this->failed;
    }

    /**
     * Returns a list of all occurred
     * errors.
     *
     * @return string[]
     *
     * @api
     */
    public function getErrors()
    {
        return $this->errors;
    }
} 