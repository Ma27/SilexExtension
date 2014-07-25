<?php
namespace Ma27\Kernel\Event;

/**
 * Event object of the kernel path generation event
 *
 * @package Ma27\Kernel\Event
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
class GenerateKernelPathsEvent extends AbstractEvent
{
    /**
     * List of the paths
     * @var string[]
     */
    private $paths = array();

    /**
     * Sets a list of the paths
     *
     * @param string[] $paths Path list
     *
     * @return $this
     *
     * @api
     */
    public function setPaths(array $paths)
    {
        $this->paths = $paths;
        return $this;
    }

    /**
     * Returns a list of the paths
     *
     * @return string[]
     *
     * @api
     */
    public function getPaths()
    {
        return $this->paths;
    }
} 