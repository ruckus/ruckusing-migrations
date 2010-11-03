<?php

/*
	This is a generic task which initializes a table to hold migration version information. 
	This task is non-destructive and will only create the table if it does not already exist, otherwise
	no other actions are performed.	
*/

require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_iTask.php';
require_once RUCKUSING_BASE . '/config/config.inc.php';

class Ruckusing_DB_Setup implements Ruckusing_iTask {
	
	private $adapter = null;
	
	function __construct($adapter) {
		$this->adapter = $adapter;
	}
	
	/* Primary task entry point */
	public function execute($args) {
		echo "Started: " . date('Y-m-d g:ia T') . "\n\n";		
		echo "[db:setup]: \n";
		//it doesnt exist, create it
		if( !$this->adapter->table_exists(RUCKUSING_TS_SCHEMA_TBL_NAME) ) {
			echo sprintf("\tCreating table: %s", RUCKUSING_TS_SCHEMA_TBL_NAME);
      $this->adapter->create_schema_version_table();
			echo "\n\tDone.\n";
		} else {
			echo sprintf("\tNOTICE: table '%s' already exists. Nothing to do.", RUCKUSING_TS_SCHEMA_TBL_NAME);
		}
		echo "\n\nFinished: " . date('Y-m-d g:ia T') . "\n\n";		
	}
	
	
}

?>