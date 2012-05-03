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
	
	public function getDbName()
	{
		$dbName = $this->dsn['database'];
		return $dbName;
	}
	
	/**
	 * Returns whether the adapter is using templates
	 * 
	 * @return boolean False if the adapter is not using templates, true otherwise
	 */
	public function hasTemplates()
	{
		$templates = $this->getTemplates();
		
		if(empty($templates))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * Returns whether the adapter is for a master db or not
	 * 
	 * Standard behaviour when no config option was delivered is no master
	 * 
	 * @return boolean True if the adapter is for a master db, false if not
	 */
	public function isMaster()
	{
		$dsn = $this->dsn;
		
		if(isset($dsn['master']))
		{
			return $dsn['master'];
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Returns whether this adapter is used for a template db
	 * 
	 * @return boolean True if the adapter is used for a template, otherwise false
	 */
	public function isTemplate()
	{
		$dsn = $this->dsn;
		return $dsn['isTemplate'];
	}
	
}
?>
