<?php

require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_TaskManager.php';

/**
 * Implementation of Ruckusing_FrameworkRunner.
 * Primary work-horse class. This class bootstraps the framework by loading
 * all adapters and tasks.
 *
 * @category Ruckusing_Classes
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_FrameworkRunner
{
    //reference to our DB connection
    private $db = null;
    //the currently active config
    private $active_db_config;
    //all available DB configs (e.g. test,development, production)
    private $config = array();
    private $task_mgr = null;
    private $adapter = null;
    private $cur_task_name = "";
    private $task_options = "";
    //default (can also be one 'test', 'production')
    private $ENV = "development";

    //set up some defaults
    private $opt_map = array(
                    'ENV' => 'development'
    );

    /**
     * Flag to display help of task
     * @see Ruckusing_FrameworkRunner::parse_args
     *
     * @var boolean
    */
    private $_showhelp = false;

    /**
     * Creates an instance of Ruckusing_BaseAdapter
     *
     * @param array $config The current config
     * @param array $argv   the supplied command line arguments
     */
    public function __construct($config, $argv)
    {
        try {
            set_error_handler(array("Ruckusing_FrameworkRunner", "scr_error_handler"), E_ALL);

            //parse arguments
            $this->parse_args($argv);

            //set config variables
            $this->config = $config;

            //verify config array
            $this->verify_db_config();

            //initialize logger
            $this->initialize_logger();

            //include all adapters
            $this->load_all_adapters(RUCKUSING_BASE . '/lib/classes/adapters');
            $this->initialize_db();
            $this->init_tasks();
        } catch (Exception $e) {
            trigger_error($e->getMessage());
        }
    }//constructor

    /**
     * Destructor
     */
    public function __destruct()
    {
    }

    //-------------------------
    // PUBLIC METHODS
    //-------------------------
    /**
    * Execute the current task
    */
    public function execute()
    {
        if (empty($this->cur_task_name)) {
            if (isset($_SERVER["argv"][1])) {
                echo sprintf("\n\tWrong Task format: %s\n", $_SERVER["argv"][1]);
            }
            echo $this->help();
        } else {
            if ($this->task_mgr->has_task($this->cur_task_name)) {
                if ($this->_showhelp) {
                    echo $this->task_mgr->help($this->cur_task_name);
                } else {
                    $output = $this->task_mgr->execute($this, $this->cur_task_name, $this->task_options);
                    //$this->display_results($output);

                }
                //return 0; // 0 is success
            } else {
                echo sprintf("\n\tTask not found: %s\n", $this->cur_task_name);
                echo $this->help();
                //return 1;
            }
        }

        if ($this->logger) {
            $this->logger->close();
        }
    }

    /**
     * Get the current adapter
     *
     * @return object
     */
    public function get_adapter()
    {
        return $this->adapter;
    }

    /**
     * Initialize the task
     */
    public function init_tasks()
    {
        $this->task_mgr = new Ruckusing_TaskManager($this->adapter);
    }

    /**
     * Get the current migration dir
     *
     * @return string
     */
    public function migrations_directory()
    {
        return $this->config['migrations_dir'] . DIRECTORY_SEPARATOR . $this->config['db'][$this->ENV]['database'];
    }

    /**
     * Get the current db schema dir
     *
     * @return string
     */
    public function db_directory()
    {
        return $this->config['db_dir'] . DIRECTORY_SEPARATOR . $this->config['db'][$this->ENV]['database'];
    }

    /**
     * Initialize the db
     */
    public function initialize_db()
    {
        try {
            $db = $this->config['db'][$this->ENV];
            $adapter = $this->get_adapter_class($db['type']);

            if ($adapter === null) {
                trigger_error(sprintf("No adapter available for DB type: %s", $db['type']));
            }
            //construct our adapter
            $this->adapter = new $adapter($db, $this->logger);
        } catch (Exception $ex) {
            trigger_error(sprintf("\n%s\n",$ex->getMessage()));
        }
    }

    /**
     * Initialize the logger
     */
    public function initialize_logger()
    {
        if (is_dir($this->config['log_dir']) && !is_writable($this->config['log_dir'])) {
            die("\n\nCannot write to log directory: " . $this->config['log_dir'] . "\n\nCheck permissions.\n\n");
        } elseif (!is_dir($this->config['log_dir'])) {
            //try and create the log directory
            mkdir($this->config['log_dir'], 0755, true);
        }
        $log_name = sprintf("%s.log", $this->ENV);
        $this->logger = Ruckusing_Logger::instance($this->config['log_dir'] . "/" . $log_name);
    }

    /**
     * $argv is our complete command line argument set.
     * PHP gives us:
     * [0] = the actual file name we're executing
     * [1..N] = all other arguments
     *
     * Our task name should be at slot [1]
     * Anything else are additional parameters that we can pass
     * to our task and they can deal with them as they see fit.
     *
     * @param array $argv the current command line arguments
     */
    private function parse_args($argv)
    {
        $num_args = count($argv);

        $options = array();
        for ($i = 0; $i < $num_args; $i++) {
            $arg = $argv[$i];
            if (stripos($arg, ':') !== false) {
                $this->cur_task_name = $arg;
            } elseif ($arg == 'help') {
                $this->_showhelp = true;
                continue;
            } elseif (stripos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg);
                $key = strtolower($key); // Allow both upper and lower case parameters
                $options[$key] = $value;
                if ($key == 'env') {
                    $this->ENV = $value;
                }
            }
        }
        $this->task_options = $options;

    }//parse_args()

    /**
     * Global error handler to process all errors
     * during script execution
     *
     * @param integer $errno   the error number
     * @param string  $errstr  the error message
     * @param string  $errfile the file that generated the error
     * @param integer $errline the line that generated the error
     *
     */
    public static function scr_error_handler($errno, $errstr, $errfile, $errline)
    {
        echo(sprintf("\n\n(%s:%d) %s\n\n", basename($errfile), $errline, $errstr));
        exit(1); // exit with error
    }

    /**
     * Update the local schema to handle multiple records versus the prior architecture
     * of storing a single version. In addition take all existing migration files
     * and register them in our new table, as they have already been executed.
     */
    public function update_schema_for_timestamps()
    {
        //only create the table if it doesnt already exist
        $this->adapter->create_schema_version_table();
        //insert all existing records into our new table
        $migrator_util = new Ruckusing_MigratorUtil($this->adapter);
        $files = $migrator_util->get_migration_files($this->migrations_directory(), 'up');
        foreach ($files as $file) {
            if ( (int) $file['version'] >= PHP_INT_MAX) {
                //its new style like '20081010170207' so its not a candidate
                continue;
            }
            //query old table, if it less than or equal to our max version, then its a candidate for insertion
            $query_sql = sprintf("SELECT version FROM %s WHERE version >= %d", RUCKUSING_SCHEMA_TBL_NAME, $file['version']);
            $existing_version_old_style = $this->adapter->select_one($query_sql);
            if (count($existing_version_old_style) > 0) {
                //make sure it doesnt exist in our new table, who knows how it got inserted?
                $new_vers_sql = sprintf("SELECT version FROM %s WHERE version = %d", RUCKUSING_TS_SCHEMA_TBL_NAME, $file['version']);
                $existing_version_new_style = $this->adapter->select_one($new_vers_sql);
                if (empty($existing_version_new_style)) {
                    // use printf & %d to force it to be stripped of any leading zeros, we *know* this represents an old version style
                    // so we dont have to worry about PHP and integer overflow
                    $insert_sql = sprintf("INSERT INTO %s (version) VALUES (%d)", RUCKUSING_TS_SCHEMA_TBL_NAME, $file['version']);
                    $this->adapter->query($insert_sql);
                }
            }
        }//foreach
    } // update_schema_for_timestamps()

    //-------------------------
    // PRIVATE METHODS
    //-------------------------
    /**
    * Update the local schema to handle multiple records versus the prior architecture
    * of storing a single version. In addition take all existing migration files
    * and register them in our new table, as they have already been executed.
    *
    * @param string $output the message to output
    */
    private function display_results($output)
    {
        return;
        //deprecated
        echo "\nStarted: " . date('Y-m-d g:ia T') . "\n\n";
        echo "\n\n";
        echo "\n$output\n";
        echo "\nFinished: " . date('Y-m-d g:ia T') . "\n\n";
    }

    /**
     * Set an option
     *
     * @param string $key   the key to set
     * @param string $value the value to set
     */
    private function set_opt($key, $value)
    {
        if (!$key) {
            return;
        }
        $this->opt_map[$key] = $value;
    }

    /**
     * Verify db config
     */
    private function verify_db_config()
    {
        if ( !array_key_exists($this->ENV, $this->config['db'])) {
            throw new Exception(sprintf("Error: '%s' DB is not configured", $this->ENV));
        }
        $env = $this->ENV;
        $this->active_db_config = $this->config['db'][$this->ENV];
        if (!array_key_exists("type",$this->active_db_config)) {
            throw new Exception(sprintf("Error: 'type' is not set for '%s' DB", $this->ENV));
        }
        if (!array_key_exists("host",$this->active_db_config)) {
            throw new Exception(sprintf("Error: 'host' is not set for '%s' DB", $this->ENV));
        }
        if (!array_key_exists("database",$this->active_db_config)) {
            throw new Exception(sprintf("Error: 'database' is not set for '%s' DB", $this->ENV));
        }
        if (!array_key_exists("user",$this->active_db_config)) {
            throw new Exception(sprintf("Error: 'user' is not set for '%s' DB", $this->ENV));
        }
        if (!array_key_exists("password",$this->active_db_config)) {
            throw new Exception(sprintf("Error: 'password' is not set for '%s' DB", $this->ENV));
        }
        if (empty($this->config['migrations_dir'])) {
            throw new Exception("Error: 'migrations_dir' is not set in config.");
        }
        if (empty($this->config['db_dir'])) {
            throw new Exception("Error: 'db_dir' is not set in config.");
        }
        if (empty($this->config['log_dir'])) {
            throw new Exception("Error: 'log_dir' is not set in config.");
        }
    }//verify_db_config

    /**
     * Get the adapter class
     *
     * @param string $db_type the database type
     */
    private function get_adapter_class($db_type)
    {
        $adapter_class = null;
        switch ($db_type) {
            case 'mysql':
                $adapter_class = "Ruckusing_MySQLAdapter";
                break;
            case 'mssql':
                $adapter_class = "Ruckusing_MSSQLAdapter";
                break;
            case 'pgsql':
                $adapter_class = "Ruckusing_PostgresAdapter";
                break;
        }

        return $adapter_class;
    }

    /**
     * DB adapters are classes in lib/classes/adapters
     * and they follow the file name syntax of "class.<DB Name>Adapter.php".
     *
     * See the function "get_adapter_class" in this class for examples.
     *
     * @param string $adapter_dir the adapter dir
     */
    private function load_all_adapters($adapter_dir)
    {
        if (!is_dir($adapter_dir)) {
            trigger_error(sprintf("Adapter dir: %s does not exist", $adapter_dir));

            return false;
        }
        $files = scandir($adapter_dir);
        $regex = '/^class\.(\w+)Adapter\.php$/';
        foreach ($files as $f) {
            //skip over invalid files
            if ($f == '.' || $f == ".." || !preg_match($regex,$f) ) {
                continue;
            }
            require_once $adapter_dir . '/' . $f;
        }
    }

    /**
     * Return the usage of the task
     *
     * @return string
     */
    public function help()
    {
        // TODO: dynamically list all available tasks
        $output =<<<USAGE

\tUsage: php {$_SERVER['argv'][0]} <task> [help] [task parameters] [ENV=environment]

\thelp: Display this message

\tENV: The ENV command line parameter can be used to specify a different
\tdatabase to run against, as specific in the configuration file
\t(config/database.inc.php).
\tBy default, ENV is "development"

\ttask: In a nutshell, task names are pseudo-namespaced. The tasks that come
\twith the framework are namespaced to "db" (e.g. the tasks are "db:migrate",
\t"db:setup", etc).
\tAll tasks available actually :

\t- db:setup : A basic task to initialize your DB for migrations is
\tavailable. One should always run this task when first starting out.

\t- db:generate : A generic task which acts as a Generator for migrations.

\t- db:migrate : The primary purpose of the framework is to run migrations,
\tand the execution of migrations is all handled by just a regular ol' task.

\t- db:version : It is always possible to ask the framework (really the DB)
\twhat version it is currently at.

\t- db:status : With this taks you'll get an overview of the already
\texecuted migrations and which will be executed when running db:migrate

\t- db:schema : It can be beneficial to get a dump of the DB in raw SQL
\tformat which represents the current version.

USAGE;
        return $output;
    }

}//class
