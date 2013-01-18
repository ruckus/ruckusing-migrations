#!/usr/bin/env php
<?php

// Find and initialize Composer
$composer_found = false;
if (version_compare(PHP_VERSION, '5.3.2', '>=')) {
    $files = array(
                    __DIR__ . '/vendor/autoload.php',
                    __DIR__ . '/../vendor/autoload.php',
                    __DIR__ . '/../../vendor/autoload.php',
                    __DIR__ . '/../../../vendor/autoload.php',
                    __DIR__ . '/../../../../autoload.php',
    );

    foreach ($files as $file) {
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }

    if (class_exists('Composer\Autoload\ClassLoader', false)) {
        $composer_found = true;
    }
}

define('RUCKUSING_WORKING_BASE', getcwd());
$db_config = require RUCKUSING_WORKING_BASE . '/ruckusing.conf.php';

if (isset($db_config['ruckusing_base'])) {
    define('RUCKUSING_BASE', $db_config['ruckusing_base']);
} else {
    define('RUCKUSING_BASE', dirname(__FILE__));
}

require_once RUCKUSING_BASE . '/config/config.inc.php';

if (!$composer_found) {
    require_once RUCKUSING_BASE . '/lib/Ruckusing/Util/Logger.php';
    require_once RUCKUSING_BASE . '/lib/Ruckusing/FrameworkRunner.php';
}

$main = new Ruckusing_FrameworkRunner($db_config, $argv);
$main->execute();
