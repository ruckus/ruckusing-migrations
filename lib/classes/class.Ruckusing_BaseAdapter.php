<?php

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
