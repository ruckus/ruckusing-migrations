<?php
require_once 'PHPUnit/Framework.php';
 
require_once '../test_helper.php';
require_once RUCKUSING_BASE  . '/lib/classes/adapters/class.Ruckusing_MySQLAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/task/class.Ruckusing_TaskManager.php';
require_once RUCKUSING_BASE  . '/lib/tasks/class.Ruckusing_DB_Schema.php';

class TaskManagerTest extends PHPUnit_Framework_TestCase {

  protected function setUp() {
    require RUCKUSING_BASE . '/config/database.inc.php';

		if( !is_array($ruckusing_db_config) || !array_key_exists("test", $ruckusing_db_config)) {
			die("\n'test' DB is not defined in config/database.inc.php\n\n");
		}

		$test_db = $ruckusing_db_config['test'];
		//setup our log
		$logger = &Ruckusing_Logger::instance(RUCKUSING_BASE . '/tests/logs/test.log');

		$this->adapter = new Ruckusing_MySQLAdapter($test_db, $logger);
		$this->adapter->logger->log("Test run started: " . date('Y-m-d g:ia T') );
		
  } //setUp()
		
  public function test_db_schema_creation() {
    $schema = new Ruckusing_DB_Schema($this->adapter);
    $schema->execute(array());
    $this->assertEquals(true, file_exists(RUCKUSING_DB_DIR . '/schema.txt') );
  }
  
}
?>