<?php

require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_Task.php';
require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_iTask.php';
require_once RUCKUSING_BASE . '/config/config.inc.php';

/**
 * Implementation of the Ruckusing_DB_Setup.
 * This is a generic task which initializes a table to hold migration version information.
 * This task is non-destructive and will only create the table if it does not already exist, otherwise
 * no other actions are performed.
 *
 * @category Ruckusing_Tasks
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_DB_Setup extends Ruckusing_Task implements Ruckusing_iTask
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
        echo "Started: " . date('Y-m-d g:ia T') . "\n\n";
        echo "[db:setup]: \n";
        //it doesnt exist, create it
        if (!$this->get_adapter()->table_exists(RUCKUSING_TS_SCHEMA_TBL_NAME)) {
            echo sprintf("\tCreating table: %s", RUCKUSING_TS_SCHEMA_TBL_NAME);
            $this->get_adapter()->create_schema_version_table();
            echo "\n\tDone.\n";
        } else {
            echo sprintf("\tNOTICE: table '%s' already exists. Nothing to do.", RUCKUSING_TS_SCHEMA_TBL_NAME);
        }
        echo "\n\nFinished: " . date('Y-m-d g:ia T') . "\n\n";
    }
}
