<?php
if(!defined('BASE')) {
  define('BASE', dirname(__FILE__) . '/..');
}
require_once BASE  . '/test_helper.php';
require_once RUCKUSING_BASE  . '/lib/classes/class.Ruckusing_BaseAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/class.Ruckusing_BaseMigration.php';
require_once RUCKUSING_BASE  . '/lib/classes/class.Ruckusing_iAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/adapters/class.Ruckusing_PostgresAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/Ruckusing_exceptions.php';

/*
	To run these unit-tests an empty test database needs to be setup in database.inc.php
	and of course, it has to really exist.
*/

class PostgresAdapterTest extends PHPUnit_Framework_TestCase {

  protected function setUp() {
    $ruckusing_config = require RUCKUSING_BASE . '/config/database.inc.php';

    if(!is_array($ruckusing_config) || !(array_key_exists("db", $ruckusing_config) && array_key_exists("test", $ruckusing_config['db']))) {
      die("\n'test' DB is not defined in config/database.inc.php\n\n");
    }

    $test_db = $ruckusing_config['db']['test'];

    //setup our log
    $logger = Ruckusing_Logger::instance(RUCKUSING_BASE . '/tests/logs/test.log');

    $this->adapter = new Ruckusing_PostgresAdapter($test_db, $logger);
    $this->adapter->logger->log("Test run started: " . date('Y-m-d g:ia T') );
  }//setUp()

  protected function tearDown() {
    //delete any tables we created
    if($this->adapter->has_table('users',true)) {
      $this->adapter->drop_table('users');
    }

    if($this->adapter->has_table(RUCKUSING_TS_SCHEMA_TBL_NAME,true)) {
      $this->adapter->drop_table(RUCKUSING_TS_SCHEMA_TBL_NAME);
    }

    $db = "test_db";
    //delete any databases we created
    if($this->adapter->database_exists($db)) {
      $this->adapter->drop_database($db);
    }
  }

  public function test_can_list_indexes() {
    $this->adapter->execute_ddl('DROP TABLE IF EXISTS animals');
    $this->adapter->execute_ddl("CREATE TABLE animals (id serial primary key, name varchar(32))");
    $this->adapter->execute_ddl("CREATE INDEX idx_animals_on_name ON animals(name)");
    $indexes = $this->adapter->indexes('animals');
    $length = count($indexes);
    $this->assertEquals(1, $length);
    $this->adapter->execute_ddl('DROP TABLE IF EXISTS animals');
  }
  
  public function test_create_schema_version_table() {
    //force drop, start from a clean slate
    if($this->adapter->has_table(RUCKUSING_TS_SCHEMA_TBL_NAME,true)) {
      $this->adapter->drop_table(RUCKUSING_TS_SCHEMA_TBL_NAME);
    }
    $this->adapter->create_schema_version_table();
    $this->assertEquals(true, $this->adapter->has_table(RUCKUSING_TS_SCHEMA_TBL_NAME,true) );
  }
  
  public function test_can_dump_schema() {
    $this->adapter->execute_ddl('DROP TABLE IF EXISTS animals');
    $this->adapter->execute_ddl("CREATE TABLE animals (id serial primary key, name varchar(32))");
    $this->adapter->execute_ddl("CREATE INDEX idx_animals_on_name ON animals(name)");
    $file = RUCKUSING_BASE . '/tests/logs/schema.txt';
    $this->adapter->schema($file);
    $this->assertFileExists($file);
    if(file_exists($file)) {
      unlink($file);
    }
  }

  public function test_ensure_table_does_not_exist() {
    $this->assertEquals(false, $this->adapter->has_table('unknown_table') );
  }

  public function test_ensure_table_does_exist() {
    //first make sure the table does not exist
    $users = $this->adapter->has_table('users', true);
    $this->assertEquals(false, $users);
    $t1 = new Ruckusing_PostgresTableDefinition($this->adapter, "users");
    $t1->column("email", "string", array('limit' => 20));
    $sql = $t1->finish();

    $users = $this->adapter->table_exists('users', true);
    $this->assertEquals(true, $users);
    $this->remove_table('users');
  }

  private function remove_table($table) {
    if($this->adapter->has_table($table,true)) {
      $this->adapter->drop_table($table);
    }
  }

  public function test_database_creation() {
    $db = "test_db";
    $this->assertEquals(true, $this->adapter->create_database($db) );
    $this->assertEquals(true, $this->adapter->database_exists($db) );

    $db = "db_does_not_exist";
    $this->assertEquals(false, $this->adapter->database_exists($db) );
  }

  public function test_database_droppage() {
    $db = "test_db";
    //create it
    $this->assertEquals(true, $this->adapter->create_database($db) );
    $this->assertEquals(true, $this->adapter->database_exists($db) );

    //drop it
    $this->assertEquals(true, $this->adapter->drop_database($db) );
    $this->assertEquals(false, $this->adapter->database_exists($db) );
  }

  public function test_index_name_too_long_throws_exception() {
    $this->setExpectedException('Ruckusing_InvalidIndexNameException');
    $bm = new Ruckusing_BaseMigration();
    $bm->set_adapter($this->adapter);
    srand();
    $table_name = "users_" . rand(0, 1000000);
    $table = $bm->create_table($table_name, array('id' => false));
    $table->column('somecolumnthatiscrazylong', 'integer');
    $table->column('anothercolumnthatiscrazylongrodeclown', 'integer');
    $sql = $table->finish();
    $bm->add_index($table_name, array('somecolumnthatiscrazylong', 'anothercolumnthatiscrazylongrodeclown'));
    $this->remove_table($table_name);
  }

  public function test_custom_primary_key_1() {
    $this->remove_table('users');
    $t1 = new Ruckusing_PostgresTableDefinition($this->adapter, "users", array('id' => true) );
    $t1->column("user_id", "integer", array("primary_key" => true));
    $table_create_sql = $t1->finish(true);
    //echo $table_create_sql;
    $this->remove_table('users');
  }

  public function test_column_definition() {
    $expected = '"age" varchar(255)';
    $this->assertEquals($expected, $this->adapter->column_definition("age", "string"));

    $expected = '"age" varchar(32)';
    $this->assertEquals($expected, $this->adapter->column_definition("age", "string", array('limit' => 32)));

    $expected = '"age" varchar(32) NOT NULL';
    $this->assertEquals($expected, $this->adapter->column_definition("age", "string", array('limit' => 32, 'null' => false)));

    $expected = '"age" varchar(32) DEFAULT \'abc\' NOT NULL';
    $this->assertEquals($expected, $this->adapter->column_definition("age", "string", array('limit' => 32, 'default' => 'abc', 'null' => false)));

    $expected = '"age" varchar(32) DEFAULT \'abc\'';
    $this->assertEquals($expected, $this->adapter->column_definition("age", "string", array('limit' => 32, 'default' => 'abc')));

    $expected = '"age" integer';
    $this->assertEquals($expected, $this->adapter->column_definition("age", "integer"));

    $expected = '"active" boolean';
    $this->assertEquals($expected, $this->adapter->column_definition("active", "boolean"));

    $expected = '"weight" bigint';
    $this->assertEquals($expected, $this->adapter->column_definition("weight", "biginteger"));
  }

  public function test_column_info() {
    $this->adapter->execute_ddl("CREATE TABLE \"users\" ( name varchar(20) );");

    $expected = array();
    $actual = $this->adapter->column_info("users", "name");
    $this->assertEquals('character varying(20)', $actual['type'] );
    $this->assertEquals('name', $actual['field'] );
    $this->remove_table('users');
  }

  public function test_rename_table() {
    $this->adapter->drop_table('users');
    $this->adapter->drop_table('users_new');
    //create it
    $this->adapter->execute_ddl("CREATE TABLE users ( name varchar(20) );");
    $this->assertEquals(true, $this->adapter->has_table('users') );
    $this->assertEquals(false, $this->adapter->has_table('users_new') );
    //rename it
    $this->adapter->rename_table('users', 'users_new');
    $this->assertEquals(false, $this->adapter->has_table('users') );
    $this->assertEquals(true, $this->adapter->has_table('users_new') );
    //clean up
    $this->adapter->drop_table('users');
    $this->adapter->drop_table('users_new');
  }

  public function test_rename_column() {
    $this->adapter->drop_table('users');
    //create it
    $this->adapter->execute_ddl("CREATE TABLE users ( name varchar(20) );");

    $before = $this->adapter->column_info("users", "name");
    $this->assertEquals('character varying(20)', $before['type'] );
    $this->assertEquals('name', $before['field'] );

    //rename the name column
    $this->adapter->rename_column('users', 'name', 'new_name');

    $after = $this->adapter->column_info("users", "new_name");
    $this->assertEquals('character varying(20)', $after['type'] );
    $this->assertEquals('new_name', $after['field'] );
    $this->remove_table('users');
  }

  public function test_add_column() {
    //create it
    $this->adapter->execute_ddl("CREATE TABLE users ( name varchar(20) );");

    $col = $this->adapter->column_info("users", "name");
    $this->assertEquals("name", $col['field']);

    //add column
    $this->adapter->add_column("users", "fav_color", "string", array('limit' => 32));
    $col = $this->adapter->column_info("users", "fav_color");
    $this->assertEquals("fav_color", $col['field']);
    $this->assertEquals('character varying(32)', $col['type'] );

    //add column
    $this->adapter->add_column("users", "latitude", "decimal", array('precision' => 10, 'scale' => 2));
    $col = $this->adapter->column_info("users", "latitude");
    $this->assertEquals("latitude", $col['field']);
    $this->assertEquals('numeric(10,2)', $col['type'] );

    //add column with unsigned parameter
    $this->adapter->add_column("users", "age", "integer", array('limit' => 2)); // the limit will be ignored
    $col = $this->adapter->column_info("users", "age");
    $this->assertEquals("age", $col['field']);
    $this->assertEquals('integer', $col['type'] );

    //add column with biginteger datatype
    $this->adapter->add_column("users", "weight", "biginteger");
    $col = $this->adapter->column_info("users", "weight");
    $this->assertEquals("weight", $col['field']);
    $this->assertEquals('bigint', $col['type'] );
    $this->remove_table('users');
  }

  public function test_remove_column() {
    $this->remove_table('users');
    //create it
    $this->adapter->execute_ddl("CREATE TABLE users ( name varchar(20), age integer );");

    //verify it exists
    $col = $this->adapter->column_info("users", "name");
    $this->assertEquals("name", $col['field']);

    //drop it
    $this->adapter->remove_column("users", "name");

    //verify it does not exist
    $col = $this->adapter->column_info("users", "name");
    $this->assertEquals(array(), $col);
    $this->remove_table('users');
  }

  public function test_change_column() {
    //create it
    $this->adapter->execute_ddl("CREATE TABLE users ( name varchar(20), age integer );");

    //verify its type
    $col = $this->adapter->column_info("users", "name");
    $this->assertEquals('character varying(20)', $col['type'] );
    $this->assertEquals('', $col['default'] );

    //change it, add a default too!
    $this->adapter->change_column("users", "name", "string", array('default' => 'abc', 'limit' => 128));

    $col = $this->adapter->column_info("users", "name");
    $this->assertEquals('character varying(128)', $col['type'] );
    $this->assertEquals("'abc'::character varying", $col['default'] );
    $this->remove_table('users');
  }

  public function test_add_index() {
    //create it
    $this->adapter->execute_ddl("CREATE TABLE users ( name varchar(20), age integer, title varchar(20) );");
    $this->adapter->add_index("users", "name");

    $this->assertEquals(true, $this->adapter->has_index("users", "name") );
    $this->assertEquals(false, $this->adapter->has_index("users", "age") );

    $this->adapter->add_index("users", "age", array('unique' => true));
    $this->assertEquals(true, $this->adapter->has_index("users", "age") );

    $this->adapter->add_index("users", "title", array('name' => 'index_on_super_title'));
    $this->assertEquals(true, $this->adapter->has_index("users", "title", array('name' => 'index_on_super_title')));
    $this->remove_table('users');
  }

  public function test_multi_column_index() {
    //create it
    $this->adapter->execute_ddl("CREATE TABLE users ( name varchar(20), age integer );");
    $this->adapter->add_index("users", array("name", "age"));

    $this->assertEquals(true, $this->adapter->has_index("users", array("name", "age") ));

    //drop it
    $this->adapter->remove_index("users", array("name", "age"));
    $this->assertEquals(false, $this->adapter->has_index("users", array("name", "age") ));
    $this->remove_table('users');
  }

  /*
  public function test_remove_index_with_default_index_name() {
    //create it
    $this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20), age int(3) );");
    $this->adapter->add_index("users", "name");

    $this->assertEquals(true, $this->adapter->has_index("users", "name") );

    //drop it
    $this->adapter->remove_index("users", "name");
    $this->assertEquals(false, $this->adapter->has_index("users", "name") );
    $this->remove_table('users');
  }

  public function test_remove_index_with_custom_index_name() {
    //create it
    $this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20), age int(3) );");
    $this->adapter->add_index("users", "name", array('name' => 'my_special_index'));

    $this->assertEquals(true, $this->adapter->has_index("users", "name", array('name' => 'my_special_index')) );

    //drop it
    $this->adapter->remove_index("users", "name", array('name' => 'my_special_index'));
    $this->assertEquals(false, $this->adapter->has_index("users", "name", array('name' => 'my_special_index')) );
    $this->remove_table('users');
  }

  public function test_string_quoting() {
    $unquoted = "Hello Sam's";
    $quoted = "Hello Sam\'s";
    $this->assertEquals($quoted, $this->adapter->quote_string($unquoted));
  }
  */
}//class

?>