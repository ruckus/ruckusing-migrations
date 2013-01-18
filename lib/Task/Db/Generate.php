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
require_once RUCKUSING_BASE . '/lib/Ruckusing/Util/Migrator.php';

/**
 * Task_DB_Generate
 * generic task which acts as a Generator for migrations.
 *
 * @category Ruckusing
 * @package  Task
 * @subpackage Db
 * @author   Cody Caughlan <codycaughlan % gmail . com>
 * @author   Salimane Adjao Moustapha <me@salimane.com>
 */
class Task_Db_Generate extends Ruckusing_Task_Base implements Ruckusing_Task_Interface
{
    /**
     * Current Adapter
     *
     * @var Ruckusing_Adapter_Base
     */
    private $_adapter = null;

    /**
     * Creates an instance of Task_DB_Generate
     *
     * @param Ruckusing_Adapter_Base $adapter The current adapter being used
     *
     * @return Task_DB_Generate
     */
    public function __construct($adapter)
    {
        parent::__construct($adapter);
        $this->_adapter = $adapter;
    }

    /**
     * Primary task entry point
     *
     * @param array $args The current supplied options.
     */
    public function execute($args)
    {
        // Add support for old migration style
        if (!is_array($args) || !array_key_exists('name', $args)) {
            $cargs = $this->parse_args($_SERVER['argv']);
            //input sanity check
            if (!is_array($cargs) || !array_key_exists('name', $cargs)) {
                echo $this->help();

                return;
            }
            $migration_name = $cargs['name'];
        }
        // Add NAME= parameter for db:generate
        else {
            $migration_name = $args['name'];
        }

        //clear any filesystem stats cache
        clearstatcache();

        //generate a complete migration file
        $next_version = Ruckusing_Util_Migrator::generate_timestamp();
        $class = Ruckusing_Util_Naming::camelcase($migration_name);
        $file_name = $next_version . '_' . $class . '.php';

        $framework = $this->get_framework();
        $migrations_dir = $framework->migrations_directory();

        if (!is_dir($migrations_dir)) {
            echo "\n\tMigrations directory (" . $migrations_dir . " doesn't exist, attempting to create.\n";
            if (mkdir($migrations_dir, 0755, true) === FALSE) {
                echo "\n\tUnable to create migrations directory at " . $migrations_dir . ", check permissions?\n";
            } else {
                echo "\n\tCreated OK\n";
            }
        }

        //check to make sure our destination directory is writable
        if (!is_writable($migrations_dir)) {
            throw new Ruckusing_Exception(
                            "ERROR: migration directory '"
                            . $migrations_dir
                            . "' is not writable by the current user. Check permissions and try again.",
                            Ruckusing_Exception::INVALID_MIGRATION_DIR
            );
        }

        //write it out!
        $full_path = $migrations_dir . '/' . $file_name;
        $template_str = self::get_template($class);
        $file_result = file_put_contents($full_path, $template_str);
        if ($file_result === FALSE) {
            throw new Ruckusing_Exception(
                            "Error writing to migrations directory/file. Do you have sufficient privileges?",
                            Ruckusing_Exception::INVALID_MIGRATION_DIR
            );
        } else {
            echo "\n\tCreated migration: {$file_name}\n\n";
        }
    }

    /**
     * Parse command line arguments.
     *
     * @param array $argv The current supplied command line arguments.
     *
     * @return array ('name' => 'name')
     */
    public function parse_args($argv)
    {
        foreach ($argv as $i => $arg) {
            if (strpos($arg, '=') !== FALSE) {
                unset($argv[$i]);
            }
        }
        $num_args = count($argv);
        if ($num_args < 3) {
            echo $this->help();

            return;
        }
        $migration_name = $argv[2];

        return array('name' => $migration_name);
    }

    /**
     * generate a migration template string
     *
     * @param  string $klass class name to create
     * @return string
     */
    public static function get_template($klass)
    {
        $template = <<<TPL
<?php

class $klass extends Ruckusing_Migration_Base
{
    public function up()
    {
    }//up()

    public function down()
    {
    }//down()
}

TPL;

        return $template;
    }

    /**
     * Return the usage of the task
     *
     * @return string
     */
    public function help()
    {
        $output =<<<USAGE

\tTask: db:generate <migration name>

\tGenerator for migrations.

\t<migration name> is a descriptive name of the migration,
\tjoined with underscores. e.g.: add_index_to_users | create_users_table

\tExample :

\t\tphp {$_SERVER['argv'][0]} db:generate add_index_to_users

USAGE;

        return $output;
    }

}
