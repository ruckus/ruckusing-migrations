<?php
//----------------------------
// DATABASE CONFIGURATION
//----------------------------
$ruckusing_db_config = array(

    'development' => array(
        'type' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'ruckusing_migrations',
        'user' => 'root',
        'password' => '',
		'dbType'	=> 'standard',
		'isTemplate' => false
    ),

	'test' => array(
		'type' => 'mysql',
		'host' => 'localhost',
		'port' => 3306,
		'database' => 'ruckusing_migrations_test',
		'user' => 'root',
		'password' => '',
		'dbType'	=> 'standard',
		'isTemplate' => false
	)

);
?>
