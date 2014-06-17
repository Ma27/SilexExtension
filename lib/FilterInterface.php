<?php
namespace Ma27\SilexExtension;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * Interface of an output filter
 * 
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright (c) 2014 - 2018, Maximilian Boschh
 */
interface FilterInterface
{
    /**
     * Filters the response and returns an array of view 
     * attributes or nothing
     * 
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $result Controller result object
     * 
     * @return string[]|void
     */
    public function filterResponse(GetResponseForControllerResultEvent &$result);
    
    /**
     * Returns a list of all controllers which result the filter 
     * shoud manipulate
     * 
     * @return string[]
     * 
     * @api
     */
    public function getSubscribedControllers();
}