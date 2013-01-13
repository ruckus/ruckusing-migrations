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
 * Ruckusing_Task_Interface
 * Interface that all tasks must implement.
 *
 * @category Ruckusing
 * @package  Ruckusing_Task
 * @author   Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */
interface Ruckusing_Task_Interface
{
    /**
     * execute the task
     *
     * @param array $args Argument to the task
     *
     * @return string
     */
    public function execute($args);

    /**
     * Return the usage of the task
     *
     * @return string
     */
    public function help();
}
