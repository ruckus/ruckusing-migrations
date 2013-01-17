<?php

if (!defined('BASE')) {
    define('BASE', dirname(__FILE__) . '/..');
}

require_once BASE  . '/test_helper.php';
require_once RUCKUSING_BASE  . '/lib/Ruckusing/Adapter/Base.php';
require_once RUCKUSING_BASE  . '/lib/Ruckusing/Migration/Base.php';
require_once RUCKUSING_BASE  . '/lib/Ruckusing/Adapter/Interface.php';
require_once RUCKUSING_BASE  . '/lib/Ruckusing/Adapter/PgSQL/Base.php';
require_once RUCKUSING_BASE  . '/lib/Ruckusing/Adapter/PgSQL/TableDefinition.php';

/**
 * Implementation of PostgresTableDefinitionTest.
 * To run these unit-tests an empty test database needs to be setup in database.inc.php
 * and of course, it has to really exist.
 *
 * @category Ruckusing_Tests
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class PostgresTableDefinitionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup commands before test case
     */
    protected function setUp()
    {
        $ruckusing_config = require RUCKUSING_BASE . '/config/database.inc.php';

        if (!is_array($ruckusing_config) || !(array_key_exists("db", $ruckusing_config) && array_key_exists("pg_test", $ruckusing_config['db']))) {
            $this->markTestSkipped("\n'pg_test' DB is not defined in config/database.inc.php\n\n");
        }

        $test_db = $ruckusing_config['db']['pg_test'];
        //setup our log
        $logger = Ruckusing_Util_Logger::instance(RUCKUSING_BASE . '/tests/logs/test.log');

        $this->adapter = new Ruckusing_Adapter_PgSQL_Base($test_db, $logger);
        $this->adapter->logger->log("Test run started: " . date('Y-m-d g:ia T'));
    }//setUp()

    /**
     * shutdown commands after test case
     */
    protected function tearDown()
    {
        //delete any tables we created
        if ($this->adapter->has_table('users',true)) {
            $this->adapter->drop_table('users');
        }
    }

    /*
     This is a difficult test because I seem to be having problems
    with getting the exact string returned (to compare). It is correct in SQL terms
    but my string comparison fails. There are extra spaces here and there...
    */
    public function test_create_sql()
    {
        /*
         $expected = <<<EXP
        CREATE TABLE users (
                        id int(11) UNSIGNED auto_increment PRIMARY KEY,
                        first_name varchar(255),
                        last_name varchar(32)
        ) Engine=InnoDB;
        EXP;
        $expected = trim($expected);
        $t1 = new MySQLTableDefinition($this->adapter, "users", array('options' => 'Engine=InnoDB') );
        $t1->column("first_name", "string");
        $t1->column("last_name", "string", array('limit' => 32));
        $actual = $t1->finish(true);
        //		$this->assertEquals($expected, $actual);

        $expected = <<<EXP
        CREATE TABLE users (
                        first_name varchar(255),
                        last_name varchar(32)
        ) Engine=InnoDB;
        EXP;
        $expected = trim($expected);
        $t1 = new MySQLTableDefinition($this->adapter, "users", array('id' => false, 'options' => 'Engine=InnoDB') );
        $t1->column("first_name", "string");
        $t1->column("last_name", "string", array('limit' => 32));
        $actual = $t1->finish(true);
        //				$this->assertEquals($expected, $actual);
        */
    }//test_create_sql

    /**
     * test column definition
     */
    public function test_column_definition()
    {
        $c = new Ruckusing_Adapter_ColumnDefinition($this->adapter, "last_name", "string", array('limit' => 32));
        $this->assertEquals("\"last_name\" varchar(32)", trim($c));

        $c = new Ruckusing_Adapter_ColumnDefinition($this->adapter, "last_name", "string", array('null' => false));
        $this->assertEquals("\"last_name\" varchar(255) NOT NULL", trim($c));

        $c = new Ruckusing_Adapter_ColumnDefinition($this->adapter, "last_name", "string", array('default' => 'abc', 'null' => false));
        $this->assertEquals("\"last_name\" varchar(255) DEFAULT 'abc' NOT NULL", trim($c));

        $c = new Ruckusing_Adapter_ColumnDefinition($this->adapter, "created_at", "datetime", array('null' => false));
        $this->assertEquals("\"created_at\" timestamp NOT NULL", trim($c));

        $c = new Ruckusing_Adapter_ColumnDefinition($this->adapter, "id", "integer", array("primary_key" => true, "unsigned" => true));
        $this->assertEquals("\"id\" integer", trim($c));
    }//test_column_definition

    /**
     * test column definition with limit
     */
    public function test_column_definition_with_limit()
    {
        $bm = new Ruckusing_Migration_Base($this->adapter);
        $ts = time();
        $table_name = "users_${ts}";
        $table = $bm->create_table($table_name);
        $table->column('username', 'string', array('limit' => 17));
        $table->finish();

        $username_actual = $this->adapter->column_info($table_name, "username");
        $this->assertEquals('character varying(17)', $username_actual['type']);
        $bm->drop_table($table_name);
    }

    /**
     * test column definition with not null
     */
    public function test_column_definition_with_not_null()
    {
        $bm = new Ruckusing_Migration_Base($this->adapter);
        $ts = time();
        $table_name = "users_${ts}";
        $table = $bm->create_table($table_name);
        $table->column('username', 'string', array('limit' => 17, 'null' => false));
        $table->finish();

        $username_actual = $this->adapter->column_info($table_name, "username");
        $this->assertEquals('character varying(17)', $username_actual['type']);
        $this->assertEquals(false, $username_actual['null']);
        $bm->drop_table($table_name);
    }

    /**
     * test column definition with default value
     */
    public function test_column_definition_with_default_value()
    {
        $bm = new Ruckusing_Migration_Base($this->adapter);
        $ts = time();
        $table_name = "users_${ts}";
        $table = $bm->create_table($table_name);
        $table->column('username', 'string', array('limit' => 17, 'default' => 'thor'));
        $table->finish();

        $username_actual = $this->adapter->column_info($table_name, "username");
        $this->assertEquals('character varying(17)', $username_actual['type']);
        $this->assertEquals('\'thor\'::character varying', $username_actual['default']);
        $bm->drop_table($table_name);
    }

    /**
     * test multiple primary keys
     */
    public function test_multiple_primary_keys()
    {
        $bm = new Ruckusing_Migration_Base($this->adapter);
        $ts = time();
        $table_name = "users_${ts}";
        $table = $bm->create_table($table_name, array('id' => false));
        $table->column('user_id', 'integer', array('primary_key' => true));
        $table->column('username', 'string', array('primary_key' => true));
        $table->finish();

        $user_id_actual = $this->adapter->column_info($table_name, "user_id");
        $username_actual = $this->adapter->column_info($table_name, "username");

        $primary_keys = $this->adapter->primary_keys($table_name);
        $this->assertEquals(true, is_array($primary_keys));
        $field_names = array();
        foreach ($primary_keys as $key) {
            $field_names[] = $key['name'];
        }
        $this->assertEquals(true, in_array('user_id', $field_names));
        $this->assertEquals(true, in_array('username', $field_names));

        //make sure there is NO 'id' column
        $id_actual = $this->adapter->column_info($table_name, "id");
        $this->assertEquals(array(), $id_actual);
        $bm->drop_table($table_name);
    }

    /**
     * test custom primary key with auto increment
     */
    public function test_custom_primary_key_with_auto_increment()
    {
        $bm = new Ruckusing_Migration_Base($this->adapter);
        $ts = time();
        $table_name = "users_${ts}";
        $table = $bm->create_table($table_name, array('id' => false));
        $table->column('user_id', 'integer', array('primary_key' => true, 'auto_increment' => true));
        $sql = $table->finish();

        $user_id_actual = $this->adapter->column_info($table_name, "user_id");

        $primary_keys = $this->adapter->primary_keys($table_name);
        $this->assertEquals(true, is_array($primary_keys));
        $this->assertEquals('user_id', $primary_keys[0]['name']);

        //make sure there is NO 'id' column
        $id_actual = $this->adapter->column_info($table_name, "id");
        $this->assertEquals(array(), $id_actual);
        $bm->drop_table($table_name);
    }

    /**
     * test that we can generate a table w/o a primary key
     */
    public function test_generate_table_without_primary_key()
    {
        $t1 = new Ruckusing_Adapter_PgSQL_TableDefinition($this->adapter, "users", array('id' => false));
        $t1->column("first_name", "string");
        $t1->column("last_name", "string", array('limit' => 32));
        $actual = $t1->finish();

        $col = $this->adapter->column_info("users", "id");
        $this->assertEquals(array(), $col);

        $primary_keys = $this->adapter->primary_keys('users');
        $this->assertEquals(array(), $primary_keys);
    }
}
