<?php
//INI file can be used for any customization of the config
$cfg = parse_ini_file(__DIR__.'/'.RUCKUSING_INI_CONFIG);

//----------------------------
// DATABASE CONFIGURATION
//----------------------------
$ruckusing_db_config = array(

    'development' => array(
        'type' => 'mysql',
        'host' => $cfg['dbHost'],
        'port' => 3306,
        'database' => 'ruckusing_migrations',
        'user' => $cfg['dbUser'],
        'password' => $cfg['dbPass'],
		'dbType'	=> 'standard',
		'isTemplate' => false
    ),

	'test' => array(
		'type' => 'mysql',
		'host' => $cfg['dbHost'],
		'port' => 3306,
		'database' => 'ruckusing_migrations_test',
		'user' => $cfg['dbUser'],
		'password' => $cfg['dbPass'],
		'dbType'	=> 'standard',
		'isTemplate' => false
	)

);
?>
