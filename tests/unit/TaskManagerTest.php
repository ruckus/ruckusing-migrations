<?php
if(!defined('BASE')) {
  define('BASE', dirname(__FILE__) . '/..');
}
require_once BASE  . '/test_helper.php';require_once RUCKUSING_BASE  . '/lib/classes/adapters/class.Ruckusing_MySQLAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/task/class.Ruckusing_TaskManager.php';
require_once RUCKUSING_BASE  . '/lib/tasks/class.Ruckusing_DB_Schema.php';

class TaskManagerTest extends PHPUnit_Framework_TestCase {

  protected function setUp() {
    $ruckusing_config = require RUCKUSING_BASE . '/config/database.inc.php';

    if(!is_array($ruckusing_config) || !(array_key_exists("db", $ruckusing_config) && array_key_exists("mysql_test", $ruckusing_config['db']))) {
      $this->markTestSkipped("\n'mysql_test' DB is not defined in config/database.inc.php\n\n");
    }

    $test_db = $ruckusing_config['db']['mysql_test'];
    //setup our log
    $logger = Ruckusing_Logger::instance(RUCKUSING_BASE . '/tests/logs/test.log');

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