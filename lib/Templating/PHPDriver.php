<?php
namespace Ma27\SilexExtension\Templating;

/**
 * PHP driver of a templating engine
 * 
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @version 0.1
 */
class PHPDriver implements DriverInterface
{
    /**
     * Renders a raw template
     * 
     * @param string   $template   Template to render
     * @param string[] $attributes List of placeholders for the template
     * 
     * @return string
     * 
     * @throws \RuntimeException If the given template is inexistent
     * 
     * @api
     */
    public function render($template, array $attributes = array())
    {
        if (!file_exists($template)) {
            throw new \RuntimeException(sprintf('Template file %s cannot be found!', $template));
        }
        
        ob_start();
        
        extract($attributes);
        require_once $template;
        
        return ob_get_clean();
    }
}