<?php
namespace Ma27\SilexExtension\Templating;

interface DriverInterface
{
    public function render($template, array $attributes = []);
}