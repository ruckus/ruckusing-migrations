<?php

/* 
  The table for keeping track of Ruckusing Migrations has changed so we need to alter the schema and migrate
  over existing migrations.
*/

if(!defined('RUCKUSING_BASE')) {
	define('RUCKUSING_BASE', __DIR__);
}

//requirements
require RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_Logger.php';
require RUCKUSING_BASE . '/config/config.inc.php';
require RUCKUSING_BASE . '/config/database.inc.php';
require RUCKUSING_BASE . '/lib/classes/class.Ruckusing_FrameworkRunner.php';


echo "\n\nStarting upgrade process.\n";
$main = new Ruckusing_FrameworkRunner($ruckusing_db_config, $argv);
$main->update_schema_for_timestamps();
echo "\n\nSuccesfully completed uprade!\n";
$notice = <<<NOTICE
Ruckusing Migrations now uses the table '%s' to keep track of migrations.
The old table '%s' can be removed at your leisure.
NOTICE;

printf("\n$notice\n\n", RUCKUSING_TS_SCHEMA_TBL_NAME, RUCKUSING_SCHEMA_TBL_NAME);

?>