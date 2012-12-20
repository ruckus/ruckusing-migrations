<?php

/**
 * Implementation of Ruckusing_Task
 *
 * @category Ruckusing_Tasks
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_Task
{
    private $framework;
    private $adapter;

    /**
     * Creates an instance of Ruckusing_Task
     *
     * @param object $adapter The current adapter being used
     */
    public function __construct($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Get the current framework
     *
     * @return object
     */
    public function get_framework()
    {
        return($this->framework);
    }

    /**
     * Set the current framework
     *
     * @param object $fw the framework being set
     */
    public function set_framework($fw)
    {
        $this->framework = $fw;
    }

    /**
     * Get the current adapter
     *
     * @return object
     */
    public function get_adapter()
    {
        return($this->adapter);
    }

}
