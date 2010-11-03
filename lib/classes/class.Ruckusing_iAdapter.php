<?php

interface Ruckusing_iAdapter {

	public function quote($value, $column);
	public function supports_migrations();
	public function native_database_types();
	public function schema();
	
	public function execute($query);
	public function quote_string($str);
	
	//database level operations
	public function database_exists($db);
	public function create_table($table_name, $options = array());
	public function drop_database($db);
	
	//table level opertions
	public function show_fields_from($tbl);
	public function table_exists($tbl);
	public function drop_table($tbl);
	public function rename_table($name, $new_name);

	//column level operations
	public function rename_column($table_name, $column_name, $new_column_name);
	public function add_column($table_name, $column_name, $type, $options = array());
	public function remove_column($table_name, $column_name);
	public function change_column($table_name, $column_name, $type, $options = array());
	public function remove_index($table_name, $column_name);
	public function add_index($table_name, $column_name, $options = array());

}


?>