<?php
if(!defined('BASE')) {
  define('BASE', dirname(__FILE__) . '/..');
}
require_once BASE  . '/test_helper.php';
require_once RUCKUSING_BASE  . '/lib/classes/class.Ruckusing_BaseAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/class.Ruckusing_BaseMigration.php';
require_once RUCKUSING_BASE  . '/lib/classes/class.Ruckusing_iAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/adapters/class.Ruckusing_MySQLAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/Ruckusing_exceptions.php';

/*
	To run these unit-tests an empty test database needs to be setup in database.inc.php
	and of course, it has to really exist.
*/

class MySQLAdapterTest extends PHPUnit_Framework_TestCase {
		
		protected function setUp() {
			require RUCKUSING_BASE . '/config/database.inc.php';

			if( !is_array($ruckusing_db_config) || !array_key_exists("test", $ruckusing_db_config)) {
				die("\n'test' DB is not defined in config/database.inc.php\n\n");
			}

			$test_db = $ruckusing_db_config['test'];

			//setup our log
			$logger = Ruckusing_Logger::instance(RUCKUSING_BASE . '/tests/logs/test.log');

			$this->adapter = new Ruckusing_MySQLAdapter($test_db, $logger);
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
		
		public function test_create_schema_version_table() {
		  //force drop, start from a clean slate
			if($this->adapter->has_table(RUCKUSING_TS_SCHEMA_TBL_NAME,true)) {
				$this->adapter->drop_table(RUCKUSING_TS_SCHEMA_TBL_NAME);
			}
			$this->adapter->create_schema_version_table();
			$this->assertEquals(true, $this->adapter->has_table(RUCKUSING_TS_SCHEMA_TBL_NAME,true) );
	  }
		
		public function test_ensure_table_does_not_exist() {
			$this->assertEquals(false, $this->adapter->has_table('unknown_table') );
		}

		public function test_ensure_table_does_exist() {
			//first make sure the table does not exist
			$users = $this->adapter->has_table('users',true);
			$this->assertEquals(false, $users);
			
			//create it
			//$this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20) );");
			
			$t1 = new Ruckusing_MySQLTableDefinition($this->adapter, "users", array('options' => 'Engine=InnoDB') );
			$t1->column("name", "string", array('limit' => 20));
			$sql = $t1->finish();
			
			//now make sure it does exist
			$users = $this->adapter->table_exists('users',true);
			$this->assertEquals(true, $users);			
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
      $ts = time();
      $table_name = "users_${ts}";
      $table = $bm->create_table($table_name, array('id' => false));
      $table->column('somecolumnthatiscrazylong', 'integer');
      $table->column('anothercolumnthatiscrazylongrodeclown', 'integer');
      $sql = $table->finish();
      $bm->add_index($table_name, array('somecolumnthatiscrazylong', 'anothercolumnthatiscrazylongrodeclown'));      
	  }

		public function test_custom_primary_key_1() {
		  $t1 = new Ruckusing_MySQLTableDefinition($this->adapter, "users", array('id' => true, 'options' => 'Engine=InnoDB') );
  		$t1->column("user_id", "integer", array("primary_key" => true));
  		$actual = $t1->finish(true);
	  }

		public function test_column_definition() {			
			$expected = "`age` varchar(255)";
			$this->assertEquals($expected, $this->adapter->column_definition("age", "string"));

			$expected = "`age` varchar(32)";
			$this->assertEquals($expected, $this->adapter->column_definition("age", "string", array('limit' => 32)));

			$expected = "`age` varchar(32) NOT NULL";
			$this->assertEquals($expected, $this->adapter->column_definition("age", "string", 
														array('limit' => 32, 'null' => false)));

			$expected = "`age` varchar(32) DEFAULT 'abc' NOT NULL";
			$this->assertEquals($expected, $this->adapter->column_definition("age", "string", 
														array('limit' => 32, 'default' => 'abc', 'null' => false)));

			$expected = "`age` varchar(32) DEFAULT 'abc'";
			$this->assertEquals($expected, $this->adapter->column_definition("age", "string", 
														array('limit' => 32, 'default' => 'abc')));

			$expected = "`age` int(11)";
			$this->assertEquals($expected, $this->adapter->column_definition("age", "integer"));

			$expected = "`active` tinyint(1)";
			$this->assertEquals($expected, $this->adapter->column_definition("active", "boolean"));	
			
			$expected = "`weight` bigint(20)";
			$this->assertEquals($expected, $this->adapter->column_definition("weight", "biginteger", array('limit' => 20)));
			
			$expected = "`age` int(11) AFTER `height`";
			$this->assertEquals($expected, $this->adapter->column_definition("age", "integer", array("after" => "height")));	
		}//test_column_definition

		public function test_column_info() {			
			//create it
			$this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20) );");	
	
			$expected = array();
			$actual = $this->adapter->column_info("users", "name");
			$this->assertEquals('varchar(20)', $actual['type'] );			
			$this->assertEquals('name', $actual['field'] );			
		}
		
		public function test_rename_table() {
			//create it
			$this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20) );");	
		  $this->assertEquals(true, $this->adapter->has_table('users') );
		  $this->assertEquals(false, $this->adapter->has_table('users_new') );
		  //rename it
		  $this->adapter->rename_table('users', 'users_new');
		  $this->assertEquals(false, $this->adapter->has_table('users') );
		  $this->assertEquals(true, $this->adapter->has_table('users_new') );
		  //clean up
		  $this->adapter->drop_table('users_new');
	  }

		public function test_rename_column() {			
			//create it
			$this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20) );");	

			$before = $this->adapter->column_info("users", "name");
			$this->assertEquals('varchar(20)', $before['type'] );			
			$this->assertEquals('name', $before['field'] );			
			
			//rename the name column
			$this->adapter->rename_column('users', 'name', 'new_name');

			$after = $this->adapter->column_info("users", "new_name");
			$this->assertEquals('varchar(20)', $after['type'] );			
			$this->assertEquals('new_name', $after['field'] );				
		}

		public function test_add_column() {			
			//create it
			$this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20) );");	

			$col = $this->adapter->column_info("users", "name");
			$this->assertEquals("name", $col['field']);			
			
			//add column
			$this->adapter->add_column("users", "fav_color", "string", array('limit' => 32));
			$col = $this->adapter->column_info("users", "fav_color");
			$this->assertEquals("fav_color", $col['field']);			
			$this->assertEquals('varchar(32)', $col['type'] );			

			//add column
			$this->adapter->add_column("users", "latitude", "decimal", array('precision' => 10, 'scale' => 2));
			$col = $this->adapter->column_info("users", "latitude");
			$this->assertEquals("latitude", $col['field']);			
			$this->assertEquals('decimal(10,2)', $col['type'] );			
			
			//add column with unsigned parameter
			$this->adapter->add_column("users", "age", "integer", array('unsigned' => true));
			$col = $this->adapter->column_info("users", "age");
			$this->assertEquals("age", $col['field']);			
			$this->assertEquals('int(11) unsigned', $col['type'] );
			
			//add column with biginteger datatype
			$this->adapter->add_column("users", "weight", "biginteger", array('limit' => 20));
			$col = $this->adapter->column_info("users", "weight");
			$this->assertEquals("weight", $col['field']);			
			$this->assertEquals('bigint(20)', $col['type'] );
		}

		public function test_remove_column() {			
			//create it
			$this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20), age int(3) );");	

			//verify it exists
			$col = $this->adapter->column_info("users", "name");
			$this->assertEquals("name", $col['field']);			
			
			//drop it
			$this->adapter->remove_column("users", "name");

			//verify it does not exist
			$col = $this->adapter->column_info("users", "name");
			$this->assertEquals(null, $col);			
		}


		public function test_change_column() {			
			//create it
			$this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20), age int(3) );");	

			//verify its type
			$col = $this->adapter->column_info("users", "name");
			$this->assertEquals('varchar(20)', $col['type'] );			
			$this->assertEquals('', $col['default'] );			
			
			//change it, add a default too!
			$this->adapter->change_column("users", "name", "string", array('default' => 'abc', 'limit' => 128));
			
			$col = $this->adapter->column_info("users", "name");
			$this->assertEquals('varchar(128)', $col['type'] );						
			$this->assertEquals('abc', $col['default'] );			
		}
		
		public function test_add_index() {
			//create it
			$this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20), age int(3), title varchar(20) );");	
			$this->adapter->add_index("users", "name");
			
			$this->assertEquals(true, $this->adapter->has_index("users", "name") );						
			$this->assertEquals(false, $this->adapter->has_index("users", "age") );								
			
			$this->adapter->add_index("users", "age", array('unique' => true));
			$this->assertEquals(true, $this->adapter->has_index("users", "age") );								
			
			$this->adapter->add_index("users", "title", array('name' => 'index_on_super_title'));
			$this->assertEquals(true, $this->adapter->has_index("users", "title", array('name' => 'index_on_super_title')));								
		}
		
		public function test_multi_column_index() {
			//create it
			$this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20), age int(3) );");	
			$this->adapter->add_index("users", array("name", "age"));
			
			$this->assertEquals(true, $this->adapter->has_index("users", array("name", "age") ));						
			
			//drop it
			$this->adapter->remove_index("users", array("name", "age"));
			$this->assertEquals(false, $this->adapter->has_index("users", array("name", "age") ));
	    }

		public function test_remove_index_with_default_index_name() {
			//create it
			$this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20), age int(3) );");	
			$this->adapter->add_index("users", "name");
			
			$this->assertEquals(true, $this->adapter->has_index("users", "name") );						
			
			//drop it
			$this->adapter->remove_index("users", "name");
			$this->assertEquals(false, $this->adapter->has_index("users", "name") );						
		}

		public function test_remove_index_with_custom_index_name() {
			//create it
			$this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20), age int(3) );");	
			$this->adapter->add_index("users", "name", array('name' => 'my_special_index'));
			
			$this->assertEquals(true, $this->adapter->has_index("users", "name", array('name' => 'my_special_index')) );						
			
			//drop it
			$this->adapter->remove_index("users", "name", array('name' => 'my_special_index'));
			$this->assertEquals(false, $this->adapter->has_index("users", "name", array('name' => 'my_special_index')) );						
		}

		
		
		/*
		public function test_determine_query_type() {
			$q = "SELECT * from users";
			$this->assertEquals(SELECT, $this->adapter->determine_query_type($q) );						

			$q = "select * from users";
			$this->assertEquals(SELECT, $this->adapter->determine_query_type($q) );						

			$q = "INSERT INTO foo (name, age) VALUES ('foo bar', 28)";
			$this->assertEquals(INSERT, $this->adapter->determine_query_type($q) );						

			$q = "UPDATE foo SET name = 'bar'";
			$this->assertEquals(UPDATE, $this->adapter->determine_query_type($q) );						

			$q = "DELETE FROM foo WHERE age > 100";
			$this->assertEquals(DELETE, $this->adapter->determine_query_type($q) );						

			$q = "ALTER TABLE foo ADD COLUMN bar int(11)";
			$this->assertEquals(ALTER, $this->adapter->determine_query_type($q) );						

			$q = "CREATE INDEX idx_foo ON foo(users)";
			$this->assertEquals(CREATE, $this->adapter->determine_query_type($q) );						

			$q = "DROP TABLE foo";
			$this->assertEquals(DROP, $this->adapter->determine_query_type($q) );						
		}
		*/
		
		public function test_string_quoting() {
		  $unquoted = "Hello Sam's";
		  $quoted = "Hello Sam\'s";
		  $this->assertEquals($quoted, $this->adapter->quote_string($unquoted));
	  }
		
}//class

?>