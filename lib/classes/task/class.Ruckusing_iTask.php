<?php

/**
 * Implementation of Ruckusing_iTask
 * Interface that all tasks must implement.
 *
 * @category Ruckusing_Tasks
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
interface Ruckusing_iTask
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
