<?php
define('RUCKUSING_BASE', dirname(__FILE__) );
require RUCKUSING_BASE.'/bootstrap.php';

//requirements
require RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_Logger.php';
require getConfigFile($argv);
require RUCKUSING_BASE . '/lib/classes/class.Ruckusing_FrameworkRunner.php';

$main = new Ruckusing_FrameworkRunner($ruckusing_db_config, $argv);
$main->execute();

?>