<?php

require_once RUCKUSING_BASE . '/lib/Ruckusing/Task/Base.php';
require_once RUCKUSING_BASE . '/lib/Ruckusing/Task/Interface.php';

/**
 * Implementation of the Ruckusing_DB_Schema which is a generic task which dumps the schema of the DB
 * as a text file.
 *
 * @category Ruckusing_Tasks
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_DB_Schema extends Ruckusing_Task_Base implements Ruckusing_Task_Interface
{
    /**
     * Creates an instance of Ruckusing_DB_Schema
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
        try {
            echo "Started: " . date('Y-m-d g:ia T') . "\n\n";
            echo "[db:schema]: \n";

            $db_directory = $this->db_dir();

            //write to disk
            $schema_file = $db_directory . '/schema.txt';
            $schema = $this->get_adapter()->schema($schema_file);
            echo "\tSchema written to: $schema_file\n\n";
            echo "\n\nFinished: " . date('Y-m-d g:ia T') . "\n\n";
        } catch (Exception $ex) {
            throw $ex; //re-throw
        }
    }//execute

    /**
     * Get the db dir, check and create the db dir if it doesn't exists
     *
     * @return string
     */
    private function db_dir()
    {
        // create the db directory if it doesnt exist
        $db_directory = $this->get_framework()->db_directory();
        if (!is_dir($db_directory)) {
            printf("\n\tDB Schema directory (%s doesn't exist, attempting to create.\n", $db_directory);
            if (mkdir($db_directory, 0755, true) === FALSE) {
                printf("\n\tUnable to create migrations directory at %s, check permissions?\n", $db_directory);
            } else {
                printf("\n\tCreated OK\n\n");
            }
        }

        //check to make sure our destination directory is writable
        if (!is_writable($db_directory)) {
            throw new Exception("ERROR: DB Schema directory '" . $db_directory . "' is not writable by the current user. Check permissions and try again.\n");
        }

        return $db_directory;
    }

    /**
     * Return the usage of the task
     *
     * @return string
     */
    public function help()
    {
        $output =<<<USAGE

\tTask: db:schema

\tIt can be beneficial to get a dump of the DB in raw SQL format which represents
\tthe current version.

\tNote: This dump only contains the actual schema (e.g. the DML needed to
\treconstruct the DB), but not any actual data.

\tIn MySQL terms, this task would not be the same as running the mysqldump command
\t(which by defaults does include any data in the tables).

USAGE;

        return $output;
    }

}//class
