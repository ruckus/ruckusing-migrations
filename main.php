<?php

define('RUCKUSING_BASE', dirname(__FILE__));
define('RUCKUSING_WORKING_BASE', getcwd());

$db_config = require RUCKUSING_WORKING_BASE . '/ruckusing.conf.php';

require_once RUCKUSING_BASE . '/config/config.inc.php';
require_once RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_Logger.php';
require_once RUCKUSING_BASE . '/lib/classes/class.Ruckusing_FrameworkRunner.php';

$main = new Ruckusing_FrameworkRunner($db_config, $argv);
$main->execute();