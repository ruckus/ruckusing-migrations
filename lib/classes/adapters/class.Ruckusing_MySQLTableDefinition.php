<?php

class Ruckusing_MySQLTableDefinition {
	
	private $adapter;
	private $name;
	private $options;
	private $sql = "";
	private $initialized = false;
	private $columns = array();
	private $table_def;
	private $primary_keys = array();
	private $auto_generate_id = true;
	
	function __construct($adapter, $name, $options = array()) {
		//sanity check
		if( !($adapter instanceof Ruckusing_BaseAdapter)) {
			throw new Ruckusing_MissingAdapterException("Invalid MySQL Adapter instance.");
		}
		if(!$name) {
			throw new Ruckusing_ArgumentException("Invalid 'name' parameter");
		}

		$this->adapter = $adapter;
		$this->name = $name;
		$this->options = $options;		
		$this->init_sql($name, $options);
		$this->table_def = new Ruckusing_TableDefinition($this->adapter, $this->options);

		if(array_key_exists('id', $options)) {
			if(is_bool($options['id']) && $options['id'] == false) {
			  $this->auto_generate_id = false;
			}
			//if its a string then we want to auto-generate an integer-based
			//primary key with this name
			if(is_string($options['id'])) {
			  $this->auto_generate_id = true;
			  $this->primary_keys[] = $options['id'];
		  }
    }
	}//__construct
	
	/*
	public function primary_key($name, $auto_increment) {
	  $options = array('auto_increment' => $auto_increment);
		$this->column($name, "primary_key", $options);
	}
	*/
	
	public function column($column_name, $type, $options = array()) {		
		//if there is already a column by the same name then silently fail 
		//and continue
		if($this->table_def->included($column_name) == true) {
			return;
		}
		
		$column_options = array();
		
		if(array_key_exists('primary_key', $options)) {
		  if($options['primary_key'] == true) {
		    $this->primary_keys[] = $column_name;
	    }
	  }
	  
		if(array_key_exists('auto_increment', $options)) {
		  if($options['auto_increment'] == true) {
		    $column_options['auto_increment'] = true;
	    }
	  }
        $column_options = array_merge($column_options, $options);
        $column = new Ruckusing_ColumnDefinition($this->adapter, $column_name, $type, $column_options);
        
        $this->columns[] = $column;
	}//column
	
	private function keys() {
	  if(count($this->primary_keys) > 0) {
  	  $lead = ' PRIMARY KEY (';
  	  $quoted = array();
	    foreach($this->primary_keys as $key) {
	      $quoted[] = sprintf("%s", $this->adapter->identifier($key));
      }
      $primary_key_sql = ",\n" . $lead . implode(",", $quoted) . ")";
      return($primary_key_sql);
    } else {
      return '';
    }
  }
	
	public function finish($wants_sql = false) {
		if($this->initialized == false) {
			throw new Ruckusing_InvalidTableDefinitionException(sprintf("Table Definition: '%s' has not been initialized", $this->name));
		}
		if(is_array($this->options) && array_key_exists('options', $this->options)) {
			$opt_str = $this->options['options'];
		} else {
			$opt_str = null;			
		}
		
		$close_sql = sprintf(") %s;",$opt_str);
		$create_table_sql = $this->sql;
		
		if($this->auto_generate_id === true) {
            $this->primary_keys[] = 'id';
            $primary_id = new Ruckusing_ColumnDefinition($this->adapter, 'id', 'integer', 
            array('unsigned' => true, 'null' => false, 'auto_increment' => true));

            $create_table_sql .= $primary_id->to_sql() . ",\n";
	    }
	    
	    $create_table_sql .= $this->columns_to_str();
	    $create_table_sql .= $this->keys() . $close_sql;
		
		if($wants_sql) {
			return $create_table_sql;
		} else {
			return $this->adapter->execute_ddl($create_table_sql);			
		}
	}//finish
	
	private function columns_to_str() {
		$str = "";
		$fields = array();
		$len = count($this->columns);
		for($i = 0; $i < $len; $i++) {
			$c = $this->columns[$i];
			$fields[] = $c->__toString();
		}
		return join(",\n", $fields);
	}
	
	private function init_sql($name, $options) {
		//are we forcing table creation? If so, drop it first
		if(array_key_exists('force', $options) && $options['force'] == true) {
			try {
				$this->adapter->drop_table($name);
			}catch(Ruckusing_MissingTableException $e) {
				//do nothing
			}
		}
		$temp = "";
		if(array_key_exists('temporary', $options)) {
			$temp = " TEMPORARY";
		}
		$create_sql = sprintf("CREATE%s TABLE ", $temp);
        $create_sql .= sprintf("%s (\n", $this->adapter->identifier($name));
		$this->sql .= $create_sql;
		$this->initialized = true;
	}//init_sql	
}

?>