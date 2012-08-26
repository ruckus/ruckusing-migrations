<?php

define('RUCKUSING_BASE', dirname(__FILE__) );
define('WORKING_BASE', getcwd());

$config_filename = WORKING_BASE . '/ruckusing.conf';
if (file_exists($config_filename)) {
    $db_config = include $config_filename;
} else {
    $db_config = include RUCKUSING_BASE . '/config/database.inc.php';
}

//requirements
require RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_Logger.php';
require RUCKUSING_BASE . '/lib/classes/class.Ruckusing_FrameworkRunner.php';

$main = new Ruckusing_FrameworkRunner($db_config, $argv);
$main->execute();

?>