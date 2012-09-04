<?php


//----------------------------
// DATABASE CONFIGURATION
//----------------------------
return array(
  'db' => array(
    'development' => array(
      'type'      => 'mysql',
      'host'      => 'localhost',
      'port'      => 3306,
      'database'  => 'ruckusing_migrations',
      'user'      => 'root',
      'password'  => ''
    ),
    
    'test'  => array(
      'type'  => 'mysql',
      'host'  => 'localhost',
      'port'  => 3306,
      'database'  => 'ruckusing_migrations_test',
      'user'  => 'root',
      'password'  => ''
    )
  ),
  'migrations_dir' => RUCKUSING_WORKING_BASE . '/migrations'
);

?>