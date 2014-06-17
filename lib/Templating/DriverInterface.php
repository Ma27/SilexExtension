<?php
namespace Ma27\SilexExtension\Templating;

/**
 * Interface of any system driver which the kernel could use to render 
 * templates
 * 
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright (c) 2014 - 2018, Maximilian Bosch
 */
interface DriverInterface
{
    /**
     * Renders a raw template by any template engine
     * 
     * @param string   $template   Template to render
     * @param string[] $attributes Attributes being used by the render process
     * 
     * @return string
     * 
     * @api
     */
    public function render($template, array $attributes = array());
}