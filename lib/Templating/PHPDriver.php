<?php
namespace Ma27\SilexExtension\Templating;

class PHPDriver implements DriverInterface
{
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