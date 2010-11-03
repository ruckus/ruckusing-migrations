<?php

define('RUCKUSING_BASE', dirname(__FILE__) );

//requirements
require RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_Logger.php';
require RUCKUSING_BASE . '/config/database.inc.php';
require RUCKUSING_BASE . '/lib/classes/class.Ruckusing_FrameworkRunner.php';

$main = new Ruckusing_FrameworkRunner($ruckusing_db_config, $argv);
$main->execute();

?>