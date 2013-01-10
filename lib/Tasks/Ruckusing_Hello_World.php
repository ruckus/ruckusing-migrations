<?php

/**
 * Implementation of the Ruckusing_Hello_World.
 *
 * @category Ruckusing_Tasks
 * @package  Ruckusing_Migrations
 * @author   Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_Hello_World extends Ruckusing_Task_Base implements Ruckusing_Task_Interface
{
    /**
     * Creates an instance of Ruckusing_DB_Migrate
     *
     * @param object $adapter The current adapter being used
     */
    public function __construct($adapter)
    {
        parent::__construct($adapter);
    }

    /**
     * Primary task entry point
     *
     * @param array $args The current supplied options.
     */
    public function execute($args)
    {
        echo "\nHello, World\n";
    }

    /**
     * Return the usage of the task
     *
     * @return string
     */
    public function help()
    {
        $output =<<<USAGE

\tTask: hello:world

\tHello World.

\tThis task does not take arguments.

USAGE;

        return $output;
    }
}
