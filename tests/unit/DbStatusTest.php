<?php
/**
 * Testfile for the db:status task
 * 
 * @package Ruckusing-Migrations/Tests
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */

/**
 * Testclass for the db:status task.
 * 
 * @package Ruckusing-Migrations/Tests
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */
class DbStatusTest extends PHPUnit_Framework_TestCase
{
	protected function setUp() {
		require RUCKUSING_BASE . '/tests/config/database.inc.php';

		if( !is_array($ruckusing_db_config) || !array_key_exists("test", $ruckusing_db_config)) {
			die("\n'test' DB is not defined in config/tests/database.inc.php\n\n");
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
		if($this->adapter->has_table(RUCKUSING_TS_SCHEMA_TBL_NAME)) {
			$this->adapter->drop_table(RUCKUSING_TS_SCHEMA_TBL_NAME);
		}
	}
	
	/**
	 * Tests the db:status Task. 
	 */
	public function test_status() {
		$setup = new Ruckusing_DB_Setup($this->adapter);
		$setup->execute(array('ENV' => 'test'));
		$migrate = new Ruckusing_DB_Migrate($this->adapter);
		$migrate->execute(array('ENV' => 'test', 'VERSION' => '+2'));
		$status = new Ruckusing_DB_Status($this->adapter);
		$status->execute(array('ENV' => 'test', 'FLAVOUR' => 'flavour'));
	}
}

?>
