<?php

/*
	This is a generic task which dumps the schema of the DB
	as a text file.	
*/

require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_Task.php';
require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_iTask.php';
require_once RUCKUSING_BASE . '/config/config.inc.php';

class Ruckusing_DB_Schema extends Ruckusing_Task implements Ruckusing_iTask {

  function __construct($adapter) {
    parent::__construct($adapter);
  }

  /* Primary task entry point */
  public function execute($args) {
    try {
      echo "Started: " . date('Y-m-d g:ia T') . "\n\n";
      echo "[db:schema]: \n";

      if (!is_dir(RUCKUSING_DB_DIR)) {
          echo "\n\tDB Schema directory (".RUCKUSING_DB_DIR." doesn't exist, attempting to create.\n";
          if (mkdir(RUCKUSING_DB_DIR) === FALSE) {
              echo "\n\tUnable to create migrations directory at ".RUCKUSING_DB_DIR.", check permissions?\n";
          } else {
              echo "\n\tCreated OK\n\n";
          }
      }

      //check to make sure our destination directory is writable
      if (!is_writable(RUCKUSING_DB_DIR)) {
          throw new Exception("ERROR: migration directory '" . RUCKUSING_DB_DIR . "' is not writable by the current user. Check permissions and try again.\n");
      }

      //write to disk
      $schema_file = RUCKUSING_DB_DIR . '/schema.txt';
      $schema = $this->get_adapter()->schema($schema_file);
      echo "\tSchema written to: $schema_file\n\n";
      echo "\n\nFinished: " . date('Y-m-d g:ia T') . "\n\n";
    }catch(Exception $ex) {
      throw $ex; //re-throw
    }
  }//execute

}//class

?>