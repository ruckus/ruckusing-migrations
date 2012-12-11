<?php

class Ruckusing_Hello_World extends Ruckusing_Task implements Ruckusing_iTask {

  function __construct($adapter) {
    parent::__construct($adapter);
  }

  /* Primary task entry point */
  public function execute($args) {
      echo "\nHello, World\n";
  }

}

?>