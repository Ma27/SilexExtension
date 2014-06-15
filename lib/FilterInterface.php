<?php
namespace Ma27\SilexExtension;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

interface FilterInterface
{
    public function filterResponse(GetResponseForControllerResultEvent &$result);
    public function getSubscribedControllers();
}