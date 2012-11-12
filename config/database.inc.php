<?php

//----------------------------
// DATABASE CONFIGURATION
//----------------------------
return array(
  'db' => array(
    'development' => array(
      'type'      => 'pgsql',
      'host'      => 'localhost',
      'port'      => 3306,
      'database'  => 'ruckusing_migrations',
      'user'      => 'postgres',
      'password'  => ''
    ),
    
    'pg_test'  => array(
      'type'  => 'pgsql',
      'host'  => 'localhost',
      'port'  => 5432,
      'database'  => 'ruckusing_migrations_test',
      'user'  => 'postgres',
      'password'  => ''
    ),
    
    'mysql_test'  => array(
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