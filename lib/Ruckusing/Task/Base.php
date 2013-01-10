<?php

/**
 * Ruckusing
 *
 * @category  Ruckusing
 * @package   Ruckusing_Task
 * @author    Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */

/**
 * Ruckusing_Task_Base
 *
 * @category Ruckusing
 * @package  Ruckusing_Task
 * @author   Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */
class Ruckusing_Task_Base
{
    /**
     * the framework
     *
     * @var Ruckusing_FrameworkRunner
     */
    private $framework;

    /**
     * the adapter
     *
     * @var Ruckusing_Adapter_Base
     */
    private $adapter;

    /**
     * Creates an instance of Ruckusing_Task_Base
     *
     * @param Ruckusing_Adapter_Base $adapter The current adapter being used
     *
     * @return Ruckusing_Task_Base
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
