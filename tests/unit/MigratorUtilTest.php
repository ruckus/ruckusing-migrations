<?php

if(!defined('BASE')) {
  define('BASE', dirname(__FILE__) . '/..');
}
require_once BASE  . '/test_helper.php';
require_once RUCKUSING_BASE  . '/lib/classes/class.Ruckusing_FrameworkRunner.php';
require_once RUCKUSING_BASE  . '/lib/classes/util/class.Ruckusing_MigratorUtil.php';
require_once RUCKUSING_BASE  . '/lib/classes/class.Ruckusing_BaseAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/class.Ruckusing_iAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/adapters/class.Ruckusing_MySQLAdapter.php';
require_once RUCKUSING_BASE  . '/config/database.inc.php';
require_once RUCKUSING_BASE  . '/config/config.inc.php';

define('RUCKUSING_TEST_HOME', RUCKUSING_BASE . '/tests');

class MigratorUtilTest extends PHPUnit_Framework_TestCase {

  protected function setUp() {
    $ruckusing_config = require RUCKUSING_BASE . '/config/database.inc.php';

    if(!is_array($ruckusing_config) || !(array_key_exists("db", $ruckusing_config) && array_key_exists("test", $ruckusing_config['db']))) {
      die("\n'test' DB is not defined in config/database.inc.php\n\n");
    }

    $test_db = $ruckusing_config['db']['test'];

    //setup our log
    $logger = Ruckusing_Logger::instance(RUCKUSING_BASE . '/tests/logs/test.log');

    $this->adapter = new Ruckusing_MySQLAdapter($test_db, $logger);
    $this->adapter->logger->log("Test run started: " . date('Y-m-d g:ia T') );

    //create the schema table if necessary
    $this->adapter->create_schema_version_table();
    
    $framework = new Ruckusing_FrameworkRunner($ruckusing_config, null);
    $this->migrations_dir = $framework->migrations_directory();
    if(!is_dir($this->migrations_dir)) {
      mkdir($this->migrations_dir);
    }
  }//setUp()
  
  protected function tearDown() {
    //clear out any tables we populated
    $this->adapter->query('DELETE FROM ' . RUCKUSING_TS_SCHEMA_TBL_NAME);
  }

  private function insert_dummy_version_data($data) {
    foreach($data as $version) {
      $insert_sql = sprintf("INSERT INTO %s (version) VALUES ('%s')", RUCKUSING_TS_SCHEMA_TBL_NAME, $version);
      $this->adapter->query($insert_sql);
    }
  }
  
  private function clear_dummy_data() {
    $this->adapter->query('DELETE FROM ' . RUCKUSING_TS_SCHEMA_TBL_NAME);
  }
  
  public function test_get_max_version() {
    $migrator_util = new Ruckusing_MigratorUtil($this->adapter);

    $this->clear_dummy_data();
    $this->assertEquals(null, $migrator_util->get_max_version() );
    
    $this->insert_dummy_version_data(array(3));
    $this->assertEquals("3", $migrator_util->get_max_version() );
    $this->clear_dummy_data();
  }

  public function test_resolve_current_version_going_up() {
    $this->clear_dummy_data();
    $this->insert_dummy_version_data( array(1) );
    
    $migrator_util = new Ruckusing_MigratorUtil($this->adapter);
    $migrator_util->resolve_current_version(3, 'up');
    
    $executed = $migrator_util->get_executed_migrations();
    $this->assertEquals(true, in_array(3, $executed) );
    $this->assertEquals(true, in_array(1, $executed) );
    $this->assertEquals(false, in_array(2, $executed) );
  }

  public function test_resolve_current_version_going_down() {
    $this->clear_dummy_data();
    $this->insert_dummy_version_data( array(1,2,3) );
    
    $migrator_util = new Ruckusing_MigratorUtil($this->adapter);
    $migrator_util->resolve_current_version(3, 'down');
    
    $executed = $migrator_util->get_executed_migrations();
    $this->assertEquals(false, in_array(3, $executed) );
    $this->assertEquals(true, in_array(1, $executed) );
    $this->assertEquals(true, in_array(2, $executed) );
  }

  public function test_get_runnable_migrations_going_up_no_target_version() {
    $migrator_util = new Ruckusing_MigratorUtil($this->adapter);
    $actual_up_files = $migrator_util->get_runnable_migrations($this->migrations_dir, 'up', false);
    $expect_up_files = array(
      array(
        'version' => 1,
        'class' => 'CreateUsers',
        'file' => '001_CreateUsers.php'
      ),
      array(
        'version' => 3,
        'class' => 'AddIndexToBlogs',
        'file' => '003_AddIndexToBlogs.php'
      ),
      array(
        'version' => '20090122193325',
        'class'   => 'AddNewTable',
        'file'    => '20090122193325_AddNewTable.php'
      )
    );
    $this->assertEquals($expect_up_files, $actual_up_files);
  }
  
  public function test_get_runnable_migrations_going_down_no_target_version() {
    $migrator_util = new Ruckusing_MigratorUtil($this->adapter);
    $actual_down_files  = $migrator_util->get_runnable_migrations($this->migrations_dir, 'down', false);
    $this->assertEquals( array() , $actual_down_files);
  }

  public function test_get_runnable_migrations_going_up_with_target_version_no_current() {
    $migrator_util = new Ruckusing_MigratorUtil($this->adapter);
    $actual_up_files = $migrator_util->get_runnable_migrations($this->migrations_dir, 'up', 3, false);
    $expect_up_files = array(
      array(
        'version' => 1,
        'class' => 'CreateUsers',
        'file'  => '001_CreateUsers.php'
      ),
      array(
        'version' => 3,
        'class' => 'AddIndexToBlogs',
        'file'  => '003_AddIndexToBlogs.php'
      )
    );
    $this->assertEquals($expect_up_files, $actual_up_files);
  }

  public function test_get_runnable_migrations_going_up_with_target_version_with_current() {
    $migrator_util = new Ruckusing_MigratorUtil($this->adapter);
    //pretend we already executed version 1
    $this->insert_dummy_version_data( array(1) );
    $actual_up_files = $migrator_util->get_runnable_migrations($this->migrations_dir, 'up', 3, false);
    $expect_up_files = array(
      array(
        'version' => 3,
        'class' => 'AddIndexToBlogs',
        'file'  => '003_AddIndexToBlogs.php'
      )
    );
    $this->assertEquals($expect_up_files, $actual_up_files);
    $this->clear_dummy_data();

    //now pre-register some migrations that we have already executed
    $this->insert_dummy_version_data( array(1,3) );
    $actual_up_files = $migrator_util->get_runnable_migrations($this->migrations_dir, 'up', 3, false);
    $this->assertEquals(array(), $actual_up_files);
  }
  
  public function test_get_runnable_migrations_going_down_with_target_version_no_current() {
    $migrator_util = new Ruckusing_MigratorUtil($this->adapter);
    $this->insert_dummy_version_data( array(3, '20090122193325') );
    $actual_down_files = $migrator_util->get_runnable_migrations($this->migrations_dir, 'down', 1, false);
    $expect_down_files = array(
      array(
        'version' => '20090122193325',
        'class'   => 'AddNewTable',
        'file'    => '20090122193325_AddNewTable.php'
      ),
      array(
        'version' => 3,
        'class' => 'AddIndexToBlogs',
        'file' => '003_AddIndexToBlogs.php'
      )
    );
    $this->assertEquals($expect_down_files, $actual_down_files);
    $this->clear_dummy_data();

    $this->insert_dummy_version_data( array(3) );
    $actual_down_files = $migrator_util->get_runnable_migrations($this->migrations_dir, 'down', 1, false);
    $expect_down_files = array(
      array(
        'version' => 3,
        'class' => 'AddIndexToBlogs',
        'file' => '003_AddIndexToBlogs.php'
      )
    );
    $this->assertEquals($expect_down_files, $actual_down_files);

    //go all the way down!
    $this->clear_dummy_data();
    $this->insert_dummy_version_data( array(1, 3, '20090122193325') );
    $actual_down_files = $migrator_util->get_runnable_migrations($this->migrations_dir, 'down', 0, false);
    $expect_down_files = array(
      array(
        'version' => '20090122193325',
        'class'   => 'AddNewTable',
        'file'    => '20090122193325_AddNewTable.php'
      ),
      array(
        'version' => 3,
        'class' => 'AddIndexToBlogs',
        'file' => '003_AddIndexToBlogs.php'
      ),
      array(
        'version' => 1,
        'class' => 'CreateUsers',
        'file' => '001_CreateUsers.php'
      )
    );
    $this->assertEquals($expect_down_files, $actual_down_files);
  } //test_get_runnable_migrations_going_down_with_target_version_no_current

} // class MigratorUtilTest
?>