<?php
/**
 * Testfile for the db:setup task
 * 
 * @package Ruckusing-Migrations/Tests
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */

/**
 * Testclass for the db:setup task.
 * 
 * @package Ruckusing-Migrations/Tests
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */
class DbMigrateTest extends PHPUnit_Framework_TestCase
{
	protected function tearDown()
	{
		if($this->adapter->has_table(RUCKUSING_TS_SCHEMA_TBL_NAME)) {
			$this->adapter->drop_table(RUCKUSING_TS_SCHEMA_TBL_NAME);
		}
	}

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
	
	/**
	 * Tests the db:migrate task with various arguments when migration 'up'.
	 * 
	 * @dataProvider provide_migrate_up_args_results
	 * @param mixed[] $args
	 * @param mixed[] $expectedMigrations 
	 */
	public function test_db_migrate_up($args, $expectedMigrations) {
		$setup = new Ruckusing_DB_Setup($this->adapter);
		$setup->execute(array('ENV' => 'test'));
		$migrate = new Ruckusing_DB_Migrate($this->adapter);
		$migrate->execute($args);
		$sql = 'SELECT * FROM '.RUCKUSING_TS_SCHEMA_TBL_NAME;
		$actualMigrations = $this->adapter->query($sql);
		$this->assertEquals($expectedMigrations, $actualMigrations);
	}

	/**
	 * Provides arguments and the expected results for 'up' migrations.
	 * 
	 * @return mixed[]
	 */
	public function provide_migrate_up_args_results() {
		return array(
				array(
					array('ENV' => 'test'),
					array(
						array(
							'version' => '1',
							'template' => 'testtpl'
						),
						array(
							'version' => '3',
							'template' => 'testtpl'
						),
						array(
							'version' => '20090122193325',
							'template' => 'testtpl'
						)
					)
				),
				array(
					array('ENV' => 'test', 'VERSION' => '+1'),
					array(
						array(
							'version' => '1',
							'template' => 'testtpl'
						)
					)
				),
				array(
					array('ENV' => 'test', 'VERSION' => '+5'),
					array(
						array(
							'version' => '1',
							'template' => 'testtpl'
						),
						array(
							'version' => '3',
							'template' => 'testtpl'
						),
						array(
							'version' => '20090122193325',
							'template' => 'testtpl'
						)
					)
				),
				array(
					array('ENV' => 'test', 'VERSION' => '20090122193325'),
					array(
						array(
							'version' => '1',
							'template' => 'testtpl'
						),
						array(
							'version' => '3',
							'template' => 'testtpl'
						),
						array(
							'version' => '20090122193325',
							'template' => 'testtpl'
						)
					)
				),
				array(
					array('ENV' => 'test', 'VERSION' => '+2'),
					array(
						array(
							'version' => '1',
							'template' => 'testtpl'
						),
						array(
							'version' => '3',
							'template' => 'testtpl'
						)
					)
				),
				array(
					array('ENV' => 'test', 'VERSION' => '+2', 'FLAVOUR' => 'flavour'),
					array(
						array(
							'version' => '1',
							'template' => 'testtpl'
						),
						array(
							'version' => '2',
							'template' => 'flavour'
						)
					)
				),
				array(
					array('ENV' => 'test', 'FLAVOUR' => 'flavour'),
					array(
						array(
							'version' => '1',
							'template' => 'testtpl'
						),
						array(
							'version' => '2',
							'template' => 'flavour'
						),
						array(
							'version' => '3',
							'template' => 'testtpl'
						),
						array(
							'version' => '20090122193325',
							'template' => 'testtpl'
						)
					)
				)
			);
	}

	/**
	 * Tests the db:migrate task with various arguments when migrating 'down'.
	 * 
	 * @dataProvider provide_migrate_down_args_results
	 * @param mixed[] $args
	 * @param mixed[] $expectedMigrations 
	 * @param mixed[] $upArgs The arguments for the 'up' migration before migrating down from it.
	 */
	public function test_db_migrate_down($args, $expectedMigrations, $upArgs) {
		$setup = new Ruckusing_DB_Setup($this->adapter);
		$setup->execute(array('ENV' => 'test'));
		$migrate = new Ruckusing_DB_Migrate($this->adapter);
		$migrate->execute($upArgs);
		$migrate->execute($args);
		$sql = 'SELECT * FROM '.RUCKUSING_TS_SCHEMA_TBL_NAME;
		$actualMigrations = $this->adapter->query($sql);
		$this->assertEquals($expectedMigrations, $actualMigrations);
	}

	/**
	 * Provides arguments and the expected results for 'down' migrations.
	 * 
	 * @return mixed[]
	 */
	public function provide_migrate_down_args_results() {
		return array(
				array(
					array('ENV' => 'test', 'VERSION' => '-3'),
					array(),
					array('ENV' => 'test'),
				),
				array(
					array('ENV' => 'test', 'VERSION' => '-2'),
					array(
						array(
							'version' => '1',
							'template' => 'testtpl'
						)
					),
					array('ENV' => 'test'),
				),
				array(
					array('ENV' => 'test', 'VERSION' => '-1'),
					array(
						array(
							'version' => '1',
							'template' => 'testtpl'
						)
					),
					array('ENV' => 'test', 'VERSION' => '+2'),
				),
				array(
					array('ENV' => 'test', 'VERSION' => '-3'),
					array(),
					array('ENV' => 'test', 'VERSION' => '+1'),
				),
				array(
					array('ENV' => 'test', 'VERSION' => '-5'),
					array(),
					array('ENV' => 'test'),
				),
				array(
					array('ENV' => 'test', 'VERSION' => '-2'),
					array(
						array(
							'version' => '1',
							'template' => 'testtpl'
						),
						array(
							'version' => '2',
							'template' => 'flavour'
						)
					),
					array('ENV' => 'test', 'FLAVOUR' => 'flavour'),
				),
				array(
					array('ENV' => 'test', 'VERSION' => '0'),
					array(),
					array('ENV' => 'test', 'FLAVOUR' => 'flavour', 'VERSION' => '+2'),
				),
				array(
					array('ENV' => 'test', 'VERSION' => '-2'),
					array(),
					array('ENV' => 'test', 'FLAVOUR' => 'flavour', 'VERSION' => '+2'),
				)
			);
	}
}

?>
