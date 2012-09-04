<?php

//set up some preliminary defaults, this is so all of our framework includes work
if(!defined('RUCKUSING_BASE')) {
	define('RUCKUSING_BASE', dirname(__FILE__) . '/..');
}
if(!defined('RUCKUSING_WORKING_BASE')) {
  define('RUCKUSING_WORKING_BASE', dirname(__FILE__) . '/dummy/db');
}

//Parent of migrations directory.
if(!defined('RUCKUSING_DB_DIR')) {
	define('RUCKUSING_DB_DIR', RUCKUSING_BASE . '/tests/dummy/db');
}

if(!defined('RUCKUSING_TS_SCHEMA_TBL_NAME')) {
	define('RUCKUSING_TS_SCHEMA_TBL_NAME', 'schema_migrations');
}

require RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_Logger.php';

?>