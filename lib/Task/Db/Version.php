<?php

/**
 * Ruckusing
 *
 * @category  Ruckusing
 * @package   Task
 * @subpackage Db
 * @author    Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */

require_once RUCKUSING_BASE . '/lib/Ruckusing/Task/Interface.php';

/**
 * Task_DB_Version.
 * This task retrieves the current version of the schema.
 *
 * @category Ruckusing
 * @package  Task
 * @subpackage Db
 * @author   Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */
class Task_Db_Version extends Ruckusing_Task_Base implements Ruckusing_Task_Interface
{
    /**
     * Creates an instance of Task_DB_Version
     *
     * @param Ruckusing_Adapter_Base $adapter The current adapter being used
     *
     * @return Task_DB_Version
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
        echo "[db:version]: \n";
        if (!$this->get_adapter()->table_exists(RUCKUSING_TS_SCHEMA_TBL_NAME)) {
            //it doesnt exist, create it
            echo "\tSchema version table (" . RUCKUSING_TS_SCHEMA_TBL_NAME . ") does not exist. Do you need to run 'db:setup'?";
        } else {
            //it exists, read the version from it
            // We only want one row but we cannot assume that we are using MySQL and use a LIMIT statement
            // as it is not part of the SQL standard. Thus we have to select all rows and use PHP to return
            // the record we need
            $versions_nested = $this->get_adapter()->select_all(sprintf("SELECT version FROM %s", RUCKUSING_TS_SCHEMA_TBL_NAME));
            $versions = array();
            foreach ($versions_nested as $v) {
                $versions[] = $v['version'];
            }
            $num_versions = count($versions);
            if ($num_versions > 0) {
                sort($versions); //sorts lowest-to-highest (ascending)
                $version = (string) $versions[$num_versions-1];
                printf("\tCurrent version: %s", $version);
            } else {
                printf("\tNo migrations have been executed.");
            }
        }
        echo "\n\nFinished: " . date('Y-m-d g:ia T') . "\n\n";
    }

    /**
     * Return the usage of the task
     *
     * @return string
     */
    public function help()
    {
        $output =<<<USAGE

\tTask: db:version

\tIt is always possible to ask the framework (really the DB) what version it is
\tcurrently at.

\tThis task does not take arguments.

USAGE;

        return $output;
    }
}
