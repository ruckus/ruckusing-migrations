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
    public function execute($args);
}
