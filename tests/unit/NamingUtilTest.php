<?php

require_once 'PHPUnit/Framework.php';

require_once '../test_helper.php';
require_once RUCKUSING_BASE  . '/lib/classes/util/class.Ruckusing_NamingUtil.php';

define('RUCKUSING_TEST_HOME', RUCKUSING_BASE . '/tests');

 
class NamingUtilTest extends PHPUnit_Framework_TestCase
{
    public function test_task_from_class_method() {
				$klass = "Ruckusing_DB_Schema";
        $this->assertEquals('db:schema', Ruckusing_NamingUtil::task_from_class_name($klass) );
    }

    public function test_task_to_class_method() {
				$task_name = "db:schema";
        $this->assertEquals('Ruckusing_DB_Schema', Ruckusing_NamingUtil::task_to_class_name($task_name) );
    }

		public function test_class_name_from_file_name() {
			$klass = RUCKUSING_TEST_HOME . '/dummy/class.Ruckusing_DB_Setup.php';
      $this->assertEquals('Ruckusing_DB_Setup', Ruckusing_NamingUtil::class_from_file_name($klass) );
		}

		public function test_class_name_from_string() {
			$klass = 'class.Ruckusing_DB_Schema.php';
      $this->assertEquals('Ruckusing_DB_Schema', Ruckusing_NamingUtil::class_from_file_name($klass) );
		}

		public function test_class_from_migration_file_name() {
			$klass = '001_CreateUsers.php';
      $this->assertEquals('CreateUsers', Ruckusing_NamingUtil::class_from_migration_file($klass) );

			$klass = '120_AddIndexToPeopleTable.php';
      $this->assertEquals('AddIndexToPeopleTable', Ruckusing_NamingUtil::class_from_migration_file($klass) );
		}

		public function test_camelcase() {
			$a = "add index to users";
      $this->assertEquals('AddIndexToUsers', Ruckusing_NamingUtil::camelcase($a) );

			$b = "add index to Users";
      $this->assertEquals('AddIndexToUsers', Ruckusing_NamingUtil::camelcase($b) );

			$c = "AddIndexToUsers";
      $this->assertEquals('AddIndexToUsers', Ruckusing_NamingUtil::camelcase($c) );
		}
		
		public function test_underscore() {
      $this->assertEquals("users_and_children", Ruckusing_NamingUtil::underscore("users and children") );
      $this->assertEquals("animals", Ruckusing_NamingUtil::underscore("animals") );
      $this->assertEquals("bobby_pins", Ruckusing_NamingUtil::underscore("bobby!pins") );			
		}
		
		public function test_index_name() {
			$column = "first_name";
      $this->assertEquals("idx_users_first_name", Ruckusing_NamingUtil::index_name("users", $column));

			$column = "age";
      $this->assertEquals("idx_users_age", Ruckusing_NamingUtil::index_name("users", $column));

			$column = array('listing_id', 'review_id');
      $this->assertEquals("idx_users_listing_id_and_review_id", Ruckusing_NamingUtil::index_name("users", $column));


		}
		

}
?>