<?php
/**
 * An example for a database config.
 * 
 * This should contain a 'test' and 'testdeploy' db configuration.
 * The 'test' db has to exist, the 'testdeploy' will be generated during the tests.
 * 
 * @package Ruckusing-Migrations
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */
$ruckusing_db_config = array(); // Array which holds db config
$testDb = array(
	'type'      => 'mysql',
	'host'      => 'localhost',
	'port'      => 3306,
	'database'  => 'test',
	'user'      => 'root',
	'password'  => ''
);
$deployDb = array(
	'type'      => 'mysql',
	'host'      => 'localhost',
	'port'      => 3306,
	'database'  => 'testdeploy',
	'user'      => 'root',
	'password'  => ''
);
$ruckusing_db_config['test'] = $testDb;
$ruckusing_db_config['testdeploy'] = $deployDb;
?>
