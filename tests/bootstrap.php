<?php

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__) . '/vendor/autoload.php';

// add non-production test package namespaces
$loader->add('Ma27\\Kernel\\Tests', __DIR__);