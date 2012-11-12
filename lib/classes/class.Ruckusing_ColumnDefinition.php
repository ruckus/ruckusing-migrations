<?php

class Ruckusing_ColumnDefinition {
  private $adapter;
  public $name;
  public $type;
  public $properties;
  private $options = array();

  function __construct($adapter, $name, $type, $options = array()) {
    $this->adapter = $adapter;
    $this->name = $name;
    $this->type = $type;
    $this->options = $options;
  }

  public function to_sql() {
    $column_sql = sprintf("%s %s", $this->adapter->identifier($this->name), $this->sql_type());
    $column_sql .= $this->adapter->add_column_options($this->type, $this->options);
    return $column_sql;
  }

  public function __toString() {
    return $this->to_sql();
  }

  private function sql_type() {
    return $this->adapter->type_to_sql($this->type, $this->options);
  }
}

?>