<?php

//--------------------------------------------
//Overall file system configuration paths
//--------------------------------------------

//These might already be defined, so wrap them in checks

if(!defined('RUCKUSING_DB_CONFIG'))
{
	define('RUCKUSING_DB_CONFIG', 'database.inc.php');
}

// DB name which is used as the standard template for all DBs
if(!defined('RUCKUSING_STANDARD_TEMPLATE'))
{
	define('RUCKUSING_STANDARD_TEMPLATE', 'templateDbName');
}

// DB table where the version info is stored
if(!defined('RUCKUSING_SCHEMA_TBL_NAME')) {
	define('RUCKUSING_SCHEMA_TBL_NAME', 'schema_info');
}

if(!defined('RUCKUSING_TS_SCHEMA_TBL_NAME')) {
	define('RUCKUSING_TS_SCHEMA_TBL_NAME', 'schema_migrations');
}

//Parent of migrations directory.
//Where schema.txt will be placed when 'db:schema' is executed
if(!defined('RUCKUSING_DB_DIR')) {
	define('RUCKUSING_DB_DIR', RUCKUSING_BASE . '/db');
}

//Where the actual migrations reside
if(!defined('RUCKUSING_MIGRATION_DIR')) {
	define('RUCKUSING_MIGRATION_DIR', RUCKUSING_DB_DIR . '/migrate');
}

?>