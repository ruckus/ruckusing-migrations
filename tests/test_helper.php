<?php

//set up some preliminary defaults, this is so all of our framework includes work

define('RUCKUSING_WORKING_BASE', dirname(__FILE__) . '/dummy/db');

$ruckusing_config = require dirname(__FILE__) . '/../config/database.inc.php';
if (isset($ruckusing_config['ruckusing_base'])) {
    define('RUCKUSING_BASE', $ruckusing_config['ruckusing_base']);
} else {
    define('RUCKUSING_BASE', dirname(__FILE__) . '/..');
}

define('RUCKUSING_TS_SCHEMA_TBL_NAME', 'schema_migrations');

require_once RUCKUSING_BASE . '/lib/Ruckusing/Util/Logger.php';
