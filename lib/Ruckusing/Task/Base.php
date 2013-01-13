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
    private $_framework;

    /**
     * the adapter
     *
     * @var Ruckusing_Adapter_Base
     */
    private $_adapter;

    /**
     * Creates an instance of Ruckusing_Task_Base
     *
     * @param Ruckusing_Adapter_Base $adapter The current adapter being used
     *
     * @return Ruckusing_Task_Base
     */
    public function __construct($adapter)
    {
        $this->setAdapter($adapter);
    }

    /**
     * Get the current framework
     *
     * @return object
     */
    public function get_framework()
    {
        return $this->_framework;
    }

    /**
     * Set the current framework
     *
     * @param Ruckusing_FrameworkRunner $fw the framework being set
     */
    public function set_framework($fw)
    {
        if (!($fw instanceof Ruckusing_FrameworkRunner)) {
            throw new Ruckusing_Exception('Framework must be instance of Ruckusing_FrameworkRunner!', Ruckusing_Exception::INVALID_FRAMEWORK);
        }
        $this->_framework = $fw;
    }

    /**
     * set adapter
     *
     * @param Ruckusing_Adapter_Base $adapter the current adapter
     *
     * @return Ruckusing_Util_Migrator
     */
    public function setAdapter($adapter)
    {
        if (!($adapter instanceof Ruckusing_Adapter_Base)) {
            throw new Ruckusing_Exception('Adapter must be implement Ruckusing_Adapter_Base!', Ruckusing_Exception::INVALID_ADAPTER);
        }
        $this->_adapter = $adapter;

        return $this;
    }

    /**
     * Get the current adapter
     *
     * @return object
     */
    public function get_adapter()
    {
        return $this->_adapter;
    }

}
