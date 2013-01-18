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

require_once RUCKUSING_BASE . '/lib/Ruckusing/Task/Base.php';
require_once RUCKUSING_BASE . '/lib/Ruckusing/Task/Interface.php';
require_once RUCKUSING_BASE . '/lib/Ruckusing/Exception.php';
require_once RUCKUSING_BASE . '/lib/Ruckusing/Util/Migrator.php';
require_once RUCKUSING_BASE . '/lib/Ruckusing/Migration/Base.php';

define('STYLE_REGULAR', 1);
define('STYLE_OFFSET', 2);

/**
 * Task_DB_Migrate.
 * This is the primary work-horse method, it runs all migrations available,
 * up to the current version.
 *
 * @category Ruckusing
 * @package  Task
 * @subpackage Db
 * @author   Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
*/
class Task_Db_Migrate extends Ruckusing_Task_Base implements Ruckusing_Task_Interface
{
    /**
     * migrator util
     *
     * @var Ruckusing_Util_Migrator
     */
    private $_migrator_util = null;

    /**
     * Current Adapter
     *
     * @var Ruckusing_Adapter_Base
     */
    private $_adapter = null;

    /**
     * migrator directory
     *
     * @var string
     */
    private $_migratorDir = null;

    /**
     * The task arguments
     *
     * @var array
     */
    private $_task_args = array();

    /**
     * debug
     *
     * @var boolean
    */
    private $_debug = false;

    /**
     * Creates an instance of Task_DB_Migrate
     *
     * @param Ruckusing_Adapter_Base $adapter The current adapter being used
     *
     * @return Task_DB_Migrate
     */
    public function __construct($adapter)
    {
        parent::__construct($adapter);
        $this->_adapter = $adapter;
        $this->_migrator_util = new Ruckusing_Util_Migrator($this->_adapter);
    }

    /**
     * Primary task entry point
     *
     * @param array $args The current supplied options.
     */
    public function execute($args)
    {
        $output = "";
        if (!$this->_adapter->supports_migrations()) {
            throw new Ruckusing_Exception(
                            "This database does not support migrations.",
                            Ruckusing_Exception::MIGRATION_NOT_SUPPORTED
            );
        }
        $this->_task_args = $args;
        echo "Started: " . date('Y-m-d g:ia T') . "\n\n";
        echo "[db:migrate]: \n";
        try {
            // Check that the schema_version table exists, and if not, automatically create it
            $this->verify_environment();

            $target_version = null;
            $style = STYLE_REGULAR;

            //did the user specify an explicit version?
            if (array_key_exists('version', $this->_task_args)) {
                $target_version = trim($this->_task_args['version']);
            }

            // did the user specify a relative offset, e.g. "-2" or "+3" ?
            if ($target_version !== null) {
                if (preg_match('/^([\-\+])(\d+)$/', $target_version, $matches)) {
                    if (count($matches) == 3) {
                        $direction = $matches[1] == '-' ? 'down' : 'up';
                        $offset = intval($matches[2]);
                        $style = STYLE_OFFSET;
                    }
                }
            }
            //determine our direction and target version
            $current_version = $this->_migrator_util->get_max_version();
            if ($style == STYLE_REGULAR) {
                if (is_null($target_version)) {
                    $this->prepare_to_migrate($target_version, 'up');
                } elseif ($current_version > $target_version) {
                    $this->prepare_to_migrate($target_version, 'down');
                } else {
                    $this->prepare_to_migrate($target_version, 'up');
                }
            }

            if ($style == STYLE_OFFSET) {
                $this->migrate_from_offset($offset, $current_version, $direction);
            }

            // Completed - display accumulated output
            if (!empty($output)) {
                echo $output . "\n\n";
            }
        } catch (Ruckusing_Exception $ex) {
            if ($ex->getCode() == Ruckusing_Exception::MISSING_SCHEMA_INFO_TABLE) {
                echo "\tSchema info table does not exist. I tried creating it but failed. Check permissions.";
            } else {
                throw $ex;
            }
        }
        echo "\n\nFinished: " . date('Y-m-d g:ia T') . "\n\n";
    }

    /**
     * Migrate to a specific offset
     *
     * @param integer $offset          version to migrate to
     * @param string  $current_version current version
     * @param $string $direction direction to migrate to 'up'/'down'
     */
    private function migrate_from_offset($offset, $current_version, $direction)
    {
        $migrations = $this->_migrator_util->get_migration_files($this->_migratorDir, $direction);
        $versions = array();
        $current_index = -1;
        for ($i = 0; $i < count($migrations); $i++) {
            $migration = $migrations[$i];
            $versions[] = $migration['version'];
            if ($migration['version'] === $current_version) {
                $current_index = $i;
            }
        }
        if ($this->_debug == true) {
            print_r($migrations);
            echo "\ncurrent_index: " . $current_index . "\n";
            echo "\ncurrent_version: " . $current_version . "\n";
            echo "\noffset: " . $offset . "\n";
        }

        // If we are not at the bottom then adjust our index (to satisfy array_slice)
        if ($current_index == -1) {
            $current_index = 0;
        } else {
            $current_index += 1;
        }

        // check to see if we have enough migrations to run - the user
        // might have asked to run more than we have available
        $available = array_slice($migrations, $current_index, $offset);
        if (count($available) != $offset) {
            $names = array();
            foreach ($available as $a) {
                $names[] = $a['file'];
            }
            $num_available = count($names);
            $prefix = $direction == 'down' ? '-' : '+';
            echo "\n\nCannot migrate " . strtoupper($direction) . " via offset \"{$prefix}{$offset}\": not enough migrations exist to execute.\n";
            echo "You asked for ({$offset}) but only available are ({$num_available}): " . implode(", ", $names) . "\n\n";
        } else {
            // run em
            $target = end($available);
            if ($this->_debug == true) {
                echo "\n------------- TARGET ------------------\n";
                print_r($target);
            }
            $this->prepare_to_migrate($target['version'], $direction);
        }
    }

    /**
     * Prepare to do a migration
     *
     * @param string $destination version to migrate to
     * @param $string $direction direction to migrate to 'up'/'down'
     */
    private function prepare_to_migrate($destination, $direction)
    {
        try {
            echo "\tMigrating " . strtoupper($direction);
            if (!is_null($destination)) {
                echo " to: {$destination}\n";
            } else {
                echo ":\n";
            }
            $migrations = $this->_migrator_util->get_runnable_migrations(
                            $this->_migratorDir,
                            $direction,
                            $destination
            );
            if (count($migrations) == 0) {
                return "\nNo relevant migrations to run. Exiting...\n";
            }
            $result = $this->run_migrations($migrations, $direction, $destination);
        } catch (Exception $ex) {
            throw $ex;
        }

    }

    /**
     * Run migrations
     *
     * @param array $migrations nigrations to run
     * @param $string $target_method direction to migrate to 'up'/'down'
     * @param string $destination version to migrate to
     *
     * @return array
     */
    private function run_migrations($migrations, $target_method, $destination)
    {
        $last_version = -1;
        foreach ($migrations as $file) {
            $full_path = $this->_migratorDir  . '/' . $file['file'];
            if (is_file($full_path) && is_readable($full_path) ) {
                require_once $full_path;
                $klass = Ruckusing_Util_Naming::class_from_migration_file($file['file']);
                $obj = new $klass($this->_adapter);
                $start = $this->start_timer();
                try {
                    //start transaction
                    $this->_adapter->start_transaction();
                    $result =  $obj->$target_method();
                    //successfully ran migration, update our version and commit
                    $this->_migrator_util->resolve_current_version($file['version'], $target_method);
                    $this->_adapter->commit_transaction();
                } catch (Ruckusing_Exception $e) {
                    $this->_adapter->rollback_transaction();
                    //wrap the caught exception in our own
                    throw new Ruckusing_Exception(
                                    sprintf("%s - %s", $file['class'], $e->getMessage()),
                                    Ruckusing_Exception::MIGRATION_FAILED
                    );
                }
                $end = $this->end_timer();
                $diff = $this->diff_timer($start, $end);
                printf("========= %s ======== (%.2f)\n", $file['class'], $diff);
                $last_version = $file['version'];
                $exec = true;
            }
        }

        //update the schema info
        $result = array('last_version' => $last_version);

        return $result;
    }

    /**
     * Start Timer
     *
     * @return int
     */
    private function start_timer()
    {
        return microtime(true);
    }

    /**
     * End Timer
     *
     * @return int
     */
    private function end_timer()
    {
        return microtime(true);
    }

    /**
     * Calculate the time difference
     *
     * @param int $s the start time
     * @param int $e the end time
     *
     * @return int
     */
    private function diff_timer($s, $e)
    {
        return $e - $s;
    }

    /**
     * Check the environment and create the migration dir if it doesn't exists
     */
    private function verify_environment()
    {
        if (!$this->_adapter->table_exists(RUCKUSING_TS_SCHEMA_TBL_NAME) ) {
            echo "\n\tSchema version table does not exist. Auto-creating.";
            $this->auto_create_schema_info_table();
        }

        $this->_migratorDir = $this->get_framework()->migrations_directory();

        // create the migrations directory if it doesnt exist
        if (!is_dir($this->_migratorDir)) {
            printf("\n\tMigrations directory (%s doesn't exist, attempting to create.", $this->_migratorDir);
            if (mkdir($this->_migratorDir, 0755, true) === FALSE) {
                printf("\n\tUnable to create migrations directory at %s, check permissions?", $this->_migratorDir);
            } else {
                printf("\n\tCreated OK");
            }
        }

        //check to make sure our destination directory is writable
        if (!is_writable($this->_migratorDir)) {
            throw new Ruckusing_Exception(
                            "ERROR: Migrations directory '"
                            . $this->_migratorDir
                            . "' is not writable by the current user. Check permissions and try again.\n",
                            Ruckusing_Exception::INVALID_MIGRATION_DIR
            );
        }
    }

    /**
     * Create the schema
     *
     * @return boolean
     */
    private function auto_create_schema_info_table()
    {
        try {
            echo sprintf("\n\tCreating schema version table: %s", RUCKUSING_TS_SCHEMA_TBL_NAME . "\n\n");
            $this->_adapter->create_schema_version_table();

            return true;
        } catch (Exception $e) {
            throw new Ruckusing_Exception(
                            "\nError auto-creating 'schema_info' table: " . $e->getMessage() . "\n\n",
                            Ruckusing_Exception::MIGRATION_FAILED
            );
        }
    }

    /**
     * Return the usage of the task
     *
     * @return string
     */
    public function help()
    {
        $output =<<<USAGE

\tTask: db:migrate [VERSION]

\tThe primary purpose of the framework is to run migrations, and the
\texecution of migrations is all handled by just a regular ol' task.

\tVERSION can be specified to go up (or down) to a specific
\tversion, based on the current version. If not specified,
\tall migrations greater than the current database version
\twill be executed.

\tExample A: The database is fresh and empty, assuming there
\tare 5 actual migrations, but only the first two should be run.

\t\tphp {$_SERVER['argv'][0]} db:migrate VERSION=20101006114707

\tExample B: The current version of the DB is 20101006114707
\tand we want to go down to 20100921114643

\t\tphp {$_SERVER['argv'][0]} db:migrate VERSION=20100921114643

\tExample C: You can also use relative number of revisions
\t(positive migrate up, negative migrate down).

\t\tphp {$_SERVER['argv'][0]} db:migrate VERSION=-2

USAGE;

        return $output;
    }

}
