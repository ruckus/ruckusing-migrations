<?php

/*
    Prints out a list of migrations that have and haven't been applied
*/

require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_iTask.php';
require_once RUCKUSING_BASE . '/config/config.inc.php';
require_once RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_MigratorUtil.php';


class Ruckusing_DB_Status implements Ruckusing_iTask {
	
	private $adapter = null;
	private $create_ddl = ""; 
	
	function __construct($adapter) {
		$this->adapter = $adapter;
	}
	
	/* Primary task entry point */
	public function execute($args) {
		echo "Started: " . date('Y-m-d g:ia T') . "\n\n";		
		echo "[db:status]: \n";
		$util = new Ruckusing_MigratorUtil($this->adapter);
		$migrations = $util->get_executed_migrations();
		$files = $util->get_migration_files(RUCKUSING_MIGRATION_DIR, 'up');
		$applied = array();
		$not_applied = array();
		foreach($files as $file) {
		  if(in_array($file['version'], $migrations)) {
		    $applied[] = $file['class'] . ' [ ' . $file['version'] . ' ]';
		  } else {
		    $not_applied[] = $file['class'] . ' [ ' . $file['version'] . ' ]';
	    }
	  }
    echo "\n\n===================== APPLIED ======================= \n";
	  foreach($applied as $a) {
	    echo "\t" . $a . "\n";
    }
    echo "\n\n===================== NOT APPLIED ======================= \n";
	  foreach($not_applied as $na) {
	    echo "\t" . $na . "\n";
    }

		echo "\n\nFinished: " . date('Y-m-d g:ia T') . "\n\n";		
	}
	
	
}

?>