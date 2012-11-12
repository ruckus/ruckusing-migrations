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