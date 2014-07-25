<?php
namespace Ma27\Kernel\Tests\Fixtures;

interface AppResponseRenderEngineInterface
{
    public function render($template, array $attributes = array());
} 