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

if (!defined('BASE')) {
    define('BASE', dirname(__FILE__));
}

if (!defined('RUCKUSING_TEST_HOME')) {
    define('RUCKUSING_TEST_HOME', RUCKUSING_BASE . '/tests');
}

spl_autoload_register('loader', true, true);

set_include_path(
        implode(
                PATH_SEPARATOR,
                array(
                        RUCKUSING_BASE . '/lib',
                        get_include_path(),
                )
        )
);

function loader($classname)
{
    $filename = str_replace('_', '/', $classname) . '.php';
    if (is_file(RUCKUSING_BASE . '/lib/' . $filename)) {
        include RUCKUSING_BASE . '/lib/' . $filename;
    }
}