<?php
namespace Ma27\Kernel\Event;

/**
 * Event object of the event which registers the app configuration
 *
 * @package Ma27\Kernel\Event
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
class RegisterConfigurationEvent extends AbstractEvent
{
    /**
     * List of the configuration
     * @var string[]
     */
    private $configuration;

    /**
     * Stores a list with configuration parameters and attaches it
     * on the configuration stack
     *
     * @param string[] $config              Configuration parameters
     * @param boolean  $stopDispatchProcess If this value is true, the dispatching process will be stopped immediately
     *
     * @return void
     *
     * @api
     */
    public function storeConfiguration(array $config, $stopDispatchProcess = true)
    {
        $this->configuration = $config;
        if ($stopDispatchProcess) {
            $this->stopPropagation();
        }
    }

    /**
     * Merges a new configuration list with the
     * current configuration stack
     *
     * @param string[] $config              Configuration list to merge
     * @param boolean  $stopDispatchProcess If this value is true, the dispatching process will be stopped immediately
     *
     * @return void
     *
     * @api
     */
    public function addConfiguration(array $config, $stopDispatchProcess = true)
    {
        if (!is_array($this->configuration)) {
            $this->storeConfiguration($config, $stopDispatchProcess);
        }

        $this->configuration = array_replace($this->configuration, $config);
        if ($stopDispatchProcess) {
            $this->stopPropagation();
        }
    }

    /**
     * Returns the stored configuration stack
     *
     * @return string[]
     *
     * @api
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Checks whether a configuration stack exists
     *
     * @return boolean
     *
     * @api
     */
    public function hasConfiguration()
    {
        return $this->configuration !== null;
    }
}