<?php

require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_Task.php';
require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_iTask.php';
require_once RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_MigratorUtil.php';

/**
 * Implementation of the Ruckusing_DB_Generate generic task which acts as a Generator for migrations.
 *
 * @category Ruckusing_Tasks
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 * @author   (c) Salimane Adjao Moustapha <me@salimane.com>
 */
class Ruckusing_DB_Generate extends Ruckusing_Task implements Ruckusing_iTask
{
    /**
     * Creates an instance of Ruckusing_DB_Generate
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
        // Add support for old migration style
        if (!is_array($args) || !array_key_exists('name', $args)) {
            $cargs = $this->parse_args($_SERVER['argv']);
            //input sanity check
            if (!is_array($cargs) || !array_key_exists('name', $cargs)) {
                $this->print_help(true);
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
        $next_version = Ruckusing_MigratorUtil::generate_timestamp();
        $class = Ruckusing_NamingUtil::camelcase($migration_name);
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
            self::die_with_error("ERROR: migration directory '" . $migrations_dir . "' is not writable by the current user. Check permissions and try again.");
        }

        //write it out!
        $full_path = $migrations_dir . '/' . $file_name;
        $template_str = self::get_template($class);
        $file_result = file_put_contents($full_path, $template_str);
        if ($file_result === FALSE) {
            self::die_with_error("Error writing to migrations directory/file. Do you have sufficient privileges?");
        } else {
            echo "\n\tCreated migration: {$file_name}\n\n";
        }
    }

    /**
     * Parse command line arguments.
     *
     * @param  array $argv The current supplied command line arguments.
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
            $this->print_help(true);
        }
        $migration_name = $argv[2];

        return array('name' => $migration_name);
    }

    /**
     * Print a usage scenario for this script.
     * Optionally take a boolean on whether to immediately die or not.
     *
     * @param boolean $exit should die after or not
     */
    public function print_help($exit = false)
    {
        echo $this->help();
        if ($exit) {
            die;
        }
    }

    /**
     * Print an error and die.
     *
     * @param string $str message to print
     */
    public static function die_with_error($str)
    {
        die("\n{$str}\n");
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

class $klass extends Ruckusing_BaseMigration
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
