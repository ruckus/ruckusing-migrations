<?php
/**
 * Holds the testclass for the bootstrap.php
 * 
 * @package Ruckusing-Migrations/Test
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */
if(!defined('BASE')) {
  define('BASE', dirname(__FILE__) . '/../');
}
require_once BASE  . '/../bootstrap.php';
require_once BASE . '/test_helper.php';
/**
 * Tests the bootstrap.php
 * 
 * @package Ruckusing-Migrations/Test
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */
class BootstrapTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	    
	}
	
	public function test_parse_all_args() {
		$argv = array(
			'php',
			'main.php',
			'db:migrate',
			'ENV=somedb',
			'VERSION=+2'
		);
		$actualArgs = parseAllArgs($argv);
		$expectedArgs = array(
			'ENV' => 'somedb',
			'VERSION' => '+2'
		);
		$this->assertEquals($expectedArgs, $actualArgs);
	}
	
	/**
	 * Tests the generation of the config filename when a non existing filename is provided by the arguments.
	 * 
	 * @expectedException Exception 
	 */
	public function test_get_config_file_no_config_file() {
		$argv = array(
			'php',
			'main.php',
			'db:migrate',
			'ENV=somedb',
			'VERSION=+2',
			'CONFIG=nonexistingconfigfile'
		);
		getConfigFile($argv);
	}
	
	/**
	 * Tests the generation of the config filename
	 */
	public function test_get_config_file() {
		$argv = array(
			'php',
			'main.php',
			'db:migrate',
			'ENV=somedb',
			'VERSION=+2'
		);
		$actualConfigFile = getConfigFile($argv);
		$expectedConfigFile = realpath(BASE.'/../config/database.inc.php');
		$this->assertEquals($expectedConfigFile, $actualConfigFile);
	}
}

?>
