<?php

/*
	This is a generic task which dumps the schema of the DB
	as a text file.	
*/

require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_iTask.php';
require_once RUCKUSING_BASE . '/config/config.inc.php';

class Ruckusing_DB_Schema implements Ruckusing_iTask {
	
	/**
	 * @var Ruckusing_BaseAdapter
	 */
	private $adapter = null;
	
	function __construct($adapter) {
		$this->adapter = $adapter;
	}
	
	/* Primary task entry point */
	public function execute($args) {
		try {
			echo "Started: " . date('Y-m-d g:ia T') . "\n\n";		
			echo "[db:schema]: \n";
			$schema = $this->adapter->schema();
			//write to disk
			
			if(isset($args['FILENAME']))
			{ // Checking if a custom filename is specified
				$filename = $args['FILENAME'];
			}
			else
			{
				$dbName = $this->adapter->getDbName();
				$filename = 'schema_'.$dbName.'.txt';
			}
			
			$schema_file = RUCKUSING_DB_DIR . '/'.$filename;
			
			if(is_file($schema_file))
			{
				// Dont overwrite file if already exists
				die("\tCan't write schema because file already exists: ".$schema_file."\n");
			}
			else
			{
				file_put_contents($schema_file, $schema, LOCK_EX);
			}
			
			echo "\tSchema written to: $schema_file\n\n";
			echo "\n\nFinished: " . date('Y-m-d g:ia T') . "\n\n";							
		}catch(Exception $ex) {
			throw $ex; //re-throw
		}
	}//execute
	
}//class

?>