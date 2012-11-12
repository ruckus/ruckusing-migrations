<?php

define('SQL_UNKNOWN_QUERY_TYPE', 1);
define('SQL_SELECT', 2);
define('SQL_INSERT', 4);
define('SQL_UPDATE', 8);
define('SQL_DELETE', 16);
define('SQL_ALTER', 32);
define('SQL_DROP', 64);
define('SQL_CREATE', 128);
define('SQL_SHOW', 256);
define('SQL_RENAME', 512);
define('SQL_SET', 1024);

class Ruckusing_BaseAdapter {
	private $dsn;
	private $db;
	private $conn;
	
	function __construct($dsn) {
		$this->set_dsn($dsn);
	}
	
	public function set_dsn($dsn) { 
		$this->dsn = $dsn;
	}
	public function get_dsn() {
		return $this->dsn;
	}	

	public function set_db($db) { 
		$this->db = $db;
	}
	public function get_db() {
		return $this->db;
	}	
	
	public function set_logger($logger) {
		$this->logger = $logger;
	}

	public function get_logger($logger) {
		return $this->logger;
	}
	
	//alias
	public function has_table($tbl) {
		return $this->table_exists($tbl);
	}
	
}
?>
