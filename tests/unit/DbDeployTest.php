<?php
/**
 * Testfile for the db:deploy task
 * 
 * @package Ruckusing-Migrations/Tests
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */
if(!defined('BASE')) {
  define('BASE', dirname(__FILE__) . '/..');
}
require_once BASE  . '/test_helper.php';
require_once RUCKUSING_BASE  . '/lib/classes/adapters/class.Ruckusing_MySQLAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/task/class.Ruckusing_TaskManager.php';
require_once RUCKUSING_BASE  . '/lib/tasks/class.Ruckusing_DB_Schema.php';
/**
 * Testclass for the db:deploy task.
 * 
 * @package Ruckusing-Migrations/Tests
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */
class DbDeployTest extends PHPUnit_Framework_TestCase
{
	/**
	 * The name of a deployed db, if any got deployed.
	 * 
	 * @var string
	 */
	protected $deployedDb = null;
	
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
		
		if($this->deployedDb !== null) {
			if($this->adapter->database_exists($this->deployedDb)) {
				$this->adapter->drop_database($this->deployedDb);
			}
		}
		if(file_exists($schemafile)) {
			unlink($schemafile);
		}

		if(file_exists($schemafileCustom)) {
			unlink($schemafileCustom);
		}
	}
	/**
	 * Tests the db:deploy task when no schema file is there.
	 * 
	 * @expectedException Exception
	 */
	public function test_deploy_no_existing_schema() {
		require RUCKUSING_BASE . '/tests/config/database.inc.php';
		if(!defined('RUCKUSING_CURRENT_TASK')) {
			define('RUCKUSING_CURRENT_TASK', 'db:deploy');
		}
		$logger = Ruckusing_Logger::instance(RUCKUSING_BASE . '/tests/logs/testdeploy.log');
		$adapter = new Ruckusing_MySQLAdapter($ruckusing_db_config['testdeploy'], $logger);
		$this->deployedDb = $adapter->getDbName();
		$deploy = new Ruckusing_DB_Deploy($adapter);
		$deploy->execute(array('ENV' => 'testdeploy', 'TEMPLATE' => 'somenotexistingtemplate'));
	}

	/**
	 * Tests the regular db:deploy task process.
	 */
	public function test_deploy() {
		require RUCKUSING_BASE . '/tests/config/database.inc.php';
		if(!defined('RUCKUSING_CURRENT_TASK')) {
			define('RUCKUSING_CURRENT_TASK', 'db:deploy');
		}
		$logger = Ruckusing_Logger::instance(RUCKUSING_BASE . '/tests/logs/testdeploy.log');
		$adapter = new Ruckusing_MySQLAdapter($ruckusing_db_config['testdeploy'], $logger);
		$this->deployedDb = $adapter->getDbName();
		$deploy = new Ruckusing_DB_Deploy($adapter);
		$deploy->execute(array('ENV' => 'testdeploy'));
	}
}

?>
