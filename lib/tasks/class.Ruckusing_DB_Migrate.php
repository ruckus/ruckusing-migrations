<?php

require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_Task.php';
require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_iTask.php';
require_once RUCKUSING_BASE . '/lib/classes/Ruckusing_exceptions.php';
require_once RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_MigratorUtil.php';
require_once RUCKUSING_BASE . '/lib/classes/class.Ruckusing_BaseMigration.php';

define('STYLE_REGULAR', 1);
define('STYLE_OFFSET', 2);

/**
 * Implementation of the Ruckusing_DB_Migrate.
 * This is the primary work-horse method, it runs all migrations available,
 * up to the current version.
 *
 * @category Ruckusing_Tasks
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
*/
class Ruckusing_DB_Migrate extends Ruckusing_Task implements Ruckusing_iTask
{
    private $migrator_util = null;
    private $task_args = array();
    private $regexp = '/^(\d+)\_/';
    private $debug = false;
    private $migrations_directory;
    private $framework;

    /**
     * Creates an instance of Ruckusing_DB_Migrate
     *
     * @param object $adapter The current adapter being used
     */
    public function __construct($adapter)
    {
        parent::__construct($adapter);
        $this->migrator_util = new Ruckusing_MigratorUtil($adapter);
    }

    /**
     * Primary task entry point
     *
     * @param array $args The current supplied options.
     */
    public function execute($args)
    {
        $output = "";
        if (!$this->get_adapter()->supports_migrations()) {
            die("This database does not support migrations.");
        }
        $this->task_args = $args;
        echo "Started: " . date('Y-m-d g:ia T') . "\n\n";
        echo "[db:migrate]: \n";
        try {
            // Check that the schema_version table exists, and if not, automatically create it
            $this->verify_environment();

            $target_version = null;
            $style = STYLE_REGULAR;

            //did the user specify an explicit version?
            if (array_key_exists('VERSION', $this->task_args)) {
                $target_version = trim($this->task_args['VERSION']);
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
            $current_version = $this->migrator_util->get_max_version();
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
        } catch (Ruckusing_MissingSchemaInfoTableException $ex) {
            echo "\tSchema info table does not exist. I tried creating it but failed. Check permissions.";
        } catch (Ruckusing_Exception $ex) {
            die("\n\n" . $ex->getMessage() . "\n\n");
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
        $migrations = $this->migrator_util->get_migration_files($this->get_framework()->migrations_directory(), $direction);
        $versions = array();
        $current_index = -1;
        for ($i = 0; $i < count($migrations); $i++) {
            $migration = $migrations[$i];
            $versions[] = $migration['version'];
            if ($migration['version'] === $current_version) {
                $current_index = $i;
            }
        }
        if ($this->debug == true) {
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
            if ($this->debug == true) {
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
            $migrations = $this->migrator_util->get_runnable_migrations($this->get_framework()->migrations_directory(), $direction, $destination);
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
            $full_path = $this->get_framework()->migrations_directory()  . '/' . $file['file'];
            if (is_file($full_path) && is_readable($full_path) ) {
                require_once $full_path;
                $klass = Ruckusing_NamingUtil::class_from_migration_file($file['file']);
                $obj = new $klass();
                $refl = new ReflectionObject($obj);
                if ($refl->hasMethod($target_method)) {
                    $obj->set_adapter($this->get_adapter());
                    $start = $this->start_timer();
                    try {
                        //start transaction
                        $this->get_adapter()->start_transaction();
                        $result =  $obj->$target_method();
                        //successfully ran migration, update our version and commit
                        $this->migrator_util->resolve_current_version($file['version'], $target_method);
                        $this->get_adapter()->commit_transaction();
                    } catch (Exception $e) {
                        $this->get_adapter()->rollback_transaction();
                        //wrap the caught exception in our own
                        $ex = new Exception(sprintf("%s - %s", $file['class'], $e->getMessage()));
                        throw $ex;
                    }
                    $end = $this->end_timer();
                    $diff = $this->diff_timer($start, $end);
                    printf("========= %s ======== (%.2f)\n", $file['class'], $diff);
                    $last_version = $file['version'];
                    $exec = true;
                } else {
                    trigger_error("ERROR: {$klass} does not have a '{$target_method}' method defined!");
                }
            }//is_file
        }//foreach
        //update the schema info
        $result = array('last_version' => $last_version);

        return $result;
    }//run_migrations

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
        if (!$this->get_adapter()->table_exists(RUCKUSING_TS_SCHEMA_TBL_NAME) ) {
            echo "\n\tSchema version table does not exist. Auto-creating.";
            $this->auto_create_schema_info_table();
        }
        // create the migrations directory if it doesnt exist
        $migrations_directory = $this->get_framework()->migrations_directory();
        if (!is_dir($migrations_directory)) {
            printf("\n\tMigrations directory (%s doesn't exist, attempting to create.", $migrations_directory);
            if (mkdir($migrations_directory, 0755, true) === FALSE) {
                printf("\n\tUnable to create migrations directory at %s, check permissions?", $migrations_directory);
            } else {
                printf("\n\tCreated OK");
            }
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
            $this->get_adapter()->create_schema_version_table();

            return true;
        } catch (Exception $e) {
            die("\nError auto-creating 'schema_info' table: " . $e->getMessage() . "\n\n");
        }
    }

}//class
