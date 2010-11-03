<?php

require_once RUCKUSING_BASE . '/lib/classes/class.Ruckusing_iAdapter.php';

class Ruckusing_BaseMigration {
	
	private $adapter;
	
	public function set_adapter($a) {
		$this->adapter = $a;
	}
	
	public function get_adapter() {
		return $this->adapter;
	}
	
	public function create_database($name, $options = null) {
		return $this->adapter->create_database($name, $options);
	}
	
	public function drop_database($name) {
		return $this->adapter->drop_database($name);		
	}
	
	public function drop_table($tbl) {
		return $this->adapter->drop_table($tbl);				
	}
	
	public function rename_table($name, $new_name) {
		return $this->adapter->rename_table($name, $new_name);						
	}
		
	public function rename_column($tbl_name, $column_name, $new_column_name) {
		return $this->adapter->rename_column($tbl_name, $column_name, $new_column_name);
	}

	public function add_column($table_name, $column_name, $type, $options = array()) {
		return $this->adapter->add_column($table_name, $column_name, $type, $options);
	}
	
	public function remove_column($table_name, $column_name) {
		return $this->adapter->remove_column($table_name, $column_name);
	}

	public function change_column($table_name, $column_name, $type, $options = array()) {
		return $this->adapter->change_column($table_name, $column_name, $type, $options);	
	}
	
	public function add_index($table_name, $column_name, $options = array()) {
		return $this->adapter->add_index($table_name, $column_name, $options);			
	}
	
	public function remove_index($table_name, $column_name) {
		return $this->adapter->remove_index($table_name, $column_name);					
	}
	
	public function create_table($table_name, $options = array()) {
		return $this->adapter->create_table($table_name, $options);
	}
	
	public function execute($query) {
		return $this->adapter->query($query);
	}
	
	public function select_one($sql) {
		return $this->adapter->select_one($sql);
	}

	public function select_all($sql) {
		return $this->adapter->select_all($sql);
		
	}
	public function query($sql) {
		return $this->adapter->query($sql);		
	}
	
	public function quote_string($str) {
	 return $this->adapter->quote_string($str); 
  }
	
}//Ruckusing_BaseMigration

?>