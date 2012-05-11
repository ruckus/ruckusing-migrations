<?php
/**
 * Testfile for the db:schema task
 * 
 * @package Ruckusing-Migrations/Tests
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */

/**
 * Testclass for the db:schema task.
 * 
 * @package Ruckusing-Migrations/Tests
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */
class DbSchemaTest extends PHPUnit_Framework_TestCase
{
	protected function setUp() {
		require RUCKUSING_BASE . '/tests/config/database.inc.php';

		if( !is_array($ruckusing_db_config) || !array_key_exists("test", $ruckusing_db_config)) {
			die("\n'test' DB is not defined in tests/config/database.inc.php\n\n");
		}

		if(!defined('RUCKUSING_STANDARD_TEMPLATE')) {
			define('RUCKUSING_STANDARD_TEMPLATE', 'testtpl');
		}

		$test_db = $ruckusing_db_config['test'];

		//setup our log
		$logger = Ruckusing_Logger::instance(RUCKUSING_BASE . '/tests/logs/test.log');

		$this->adapter = new Ruckusing_MySQLAdapter($test_db, $logger);
		$this->adapter->logger->log("Test run started: " . date('Y-m-d g:ia T') );

	} //setUp()

	protected function tearDown()
	{
		$schemafile = RUCKUSING_DB_DIR . '/schema_' . $this->adapter->getDbName().'.txt';
		$schemafileCustom = RUCKUSING_DB_DIR . '/schema_custom.txt';
		
		if(file_exists($schemafile)) {
			unlink($schemafile);
		}

		if(file_exists($schemafileCustom)) {
			unlink($schemafileCustom);
		}
	}
	
	public function test_db_schema_creation() {
		$schema = new Ruckusing_DB_Schema($this->adapter);
		$schema->execute(array('ENV' => 'test'));
		$this->assertEquals(true, file_exists(RUCKUSING_DB_DIR . '/schema_' . $this->adapter->getDbName().'.txt') );
	}

	public function test_db_schema_creation_custom_filename() {
		$schema = new Ruckusing_DB_Schema($this->adapter);
		$schema->execute(array('ENV' => 'test', 'FILENAME' => 'schema_custom.txt'));
		$this->assertEquals(true, file_exists(RUCKUSING_DB_DIR . '/schema_custom.txt') );
	}
}

?>
