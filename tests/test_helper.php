<?php

//set up some preliminary defaults, this is so all of our
//framework includes work!
if(!defined('RUCKUSING_BASE')) {
	define('RUCKUSING_BASE', dirname(__FILE__) . '/..');
}

//Parent of migrations directory.
if(!defined('RUCKUSING_DB_DIR')) {
	define('RUCKUSING_DB_DIR', RUCKUSING_BASE . '/tests/dummy/db');
}

// DB name which is used as the standard template for all DBs
if(!defined('RUCKUSING_STANDARD_TEMPLATE'))
{
	define('RUCKUSING_STANDARD_TEMPLATE', 'testtpl');
}

if(!defined('RUCKUSING_TS_SCHEMA_TBL_NAME')) {
	define('RUCKUSING_TS_SCHEMA_TBL_NAME', 'schema_migrations');
}

//Where the dummy migrations reside
if(!defined('RUCKUSING_MIGRATION_DIR')) {
	define('RUCKUSING_MIGRATION_DIR', RUCKUSING_DB_DIR . '/migrate');
}

require RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_Logger.php';
require RUCKUSING_BASE . '/lib/tasks/class.Ruckusing_DB_Deploy.php';
require RUCKUSING_BASE . '/lib/tasks/class.Ruckusing_DB_Migrate.php';
require RUCKUSING_BASE . '/lib/tasks/class.Ruckusing_DB_Schema.php';
require RUCKUSING_BASE . '/lib/tasks/class.Ruckusing_DB_Setup.php';
require RUCKUSING_BASE . '/lib/tasks/class.Ruckusing_DB_Status.php';
require RUCKUSING_BASE . '/lib/tasks/class.Ruckusing_DB_Version.php';

?>