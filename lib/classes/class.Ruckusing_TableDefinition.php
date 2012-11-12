<?php

class Ruckusing_TableDefinition {

  private $columns = array();
  private $adapter;

  function __construct($adapter) {
    $this->adapter = $adapter;
  }

  /*
  Determine whether or not the given column already exists in our 
  table definition.

  This method is lax enough that it can take either a string column name
  or a Ruckusing_ColumnDefinition object.
  */
  public function included($column) {
    $k = count($this->columns);
    for($i = 0; $i < $k; $i++) {
      $col = $this->columns[$i];
      if(is_string($column) && $col->name == $column) {
        return true;
      }
      if(($column instanceof Ruckusing_ColumnDefinition) && $col->name == $column->name) {
        return true;
      }
    }
    return false;
  }

  public function to_sql() {
    return join(",", $this->columns);
  }
}

?>