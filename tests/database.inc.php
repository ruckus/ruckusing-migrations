<?php
/**
 * Config for the databases. Uses the master db to generate a configarray
 * 
 * @package Ruckusing-Migrations
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */
$ruckusing_db_config = array(); // Array which holds db config
$masterDb = array(
	'type'      => 'mysql',
	'host'      => 'mongo',
	'port'      => 3306,
	'database'  => 'one_2001_test',
	'user'      => 'root',
	'password'  => 'laTonerossi'
);
$ruckusing_db_config['test'] = $masterDb;
?>
