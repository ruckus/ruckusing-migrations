<?php

/*
	This task retrieves the current version of the schema.
*/

require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_iTask.php';
require_once RUCKUSING_BASE . '/config/config.inc.php';


class Ruckusing_DB_Version implements Ruckusing_iTask {
	
	private $adapter = null;
	private $create_ddl = ""; 
	
	function __construct($adapter) {
		$this->adapter = $adapter;
	}
	
	/* Primary task entry point */
	public function execute($args) {
		echo "Started: " . date('Y-m-d g:ia T') . "\n\n";		
		echo "[db:version]: \n";
		if( ! $this->adapter->table_exists(RUCKUSING_TS_SCHEMA_TBL_NAME) ) {
			//it doesnt exist, create it
			echo "\tSchema version table (" . RUCKUSING_TS_SCHEMA_TBL_NAME . ") does not exist. Do you need to run 'db:setup'?";
		} else {
			//it exists, read the version from it
      // We only want one row but we cannot assume that we are using MySQL and use a LIMIT statement
      // as it is not part of the SQL standard. Thus we have to select all rows and use PHP to return
      // the record we need
      $versions_nested = $this->adapter->select_all(sprintf("SELECT version FROM %s", RUCKUSING_TS_SCHEMA_TBL_NAME));
      $versions = array();
      foreach($versions_nested as $v) {
        $versions[] = $v['version'];
      }
      $num_versions = count($versions);
      if($num_versions > 0) {
        sort($versions); //sorts lowest-to-highest (ascending)
        $version = (string)$versions[$num_versions-1];
  			printf("\tCurrent version: %s", $version);
      } else {
        printf("\tNo migrations have been executed.");  			
      }
		}
		echo "\n\nFinished: " . date('Y-m-d g:ia T') . "\n\n";		
	}
	
	
}

?>