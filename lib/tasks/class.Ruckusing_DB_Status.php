<?php

require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_Task.php';
require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_iTask.php';
require_once RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_MigratorUtil.php';

/**
 * Implementation of the Ruckusing_DB_Status.
 * Prints out a list of migrations that have and haven't been applied
 *
 * @category Ruckusing_Tasks
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_DB_Status extends Ruckusing_Task implements Ruckusing_iTask
{
    private $create_ddl = "";

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
        echo "[db:status]: \n";
        $util = new Ruckusing_MigratorUtil($this->get_adapter());
        $migrations = $util->get_executed_migrations();
        $files = $util->get_migration_files($this->get_framework()->migrations_directory(), 'up');
        $applied = array();
        $not_applied = array();
        foreach ($files as $file) {
            if (in_array($file['version'], $migrations)) {
                $applied[] = $file['class'] . ' [ ' . $file['version'] . ' ]';
            } else {
                $not_applied[] = $file['class'] . ' [ ' . $file['version'] . ' ]';
            }
        }
        echo "\n\n===================== APPLIED ======================= \n";
        foreach ($applied as $a) {
            echo "\t" . $a . "\n";
        }
        echo "\n\n===================== NOT APPLIED ======================= \n";
        foreach ($not_applied as $na) {
            echo "\t" . $na . "\n";
        }

        echo "\n\nFinished: " . date('Y-m-d g:ia T') . "\n\n";
    }
}
