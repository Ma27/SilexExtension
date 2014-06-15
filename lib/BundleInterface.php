<?php
namespace Ma27\SilexExtension;

interface BundleInterface
{
    public function onSetUp(Kernel &$kernel);
    public function attachRoutes(Kernel &$kernel);
    public function createDependencies(Kernel &$kernel);
    public function onShutDown(Kernel &$kernel);
}