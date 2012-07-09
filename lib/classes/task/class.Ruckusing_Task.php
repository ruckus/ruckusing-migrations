<?php

class Ruckusing_Task {
  private $framework;
  private $adapter;
  
  function __construct($adapter) {
    $this->adapter = $adapter;
	}
  
  public function get_framework() {
    return($this->framework);
  }
  
  public function set_framework($fw) {
    $this->framework = $fw;
  }

  public function get_adapter() {
    return($this->adapter);
  }

}

?>