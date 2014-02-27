<?php

class Sqlite3TableDefinitionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Ruckusing_Adapter_Sqlite3_Base
     */
    public $adapter;

    protected function setUp()
    {
        $ruckusing_config = require RUCKUSING_BASE . '/config/database.inc.php';

        if (!is_array($ruckusing_config) || !(array_key_exists("db", $ruckusing_config) && array_key_exists("sqlite_test", $ruckusing_config['db']))) {
            $this->markTestSkipped("\n'mysql_test' DB is not defined in config/database.inc.php\n\n");
        }

        $test_db = $ruckusing_config['db']['mysql_test'];
        //setup our log
        $logger = Ruckusing_Util_Logger::instance(RUCKUSING_BASE . '/tests/logs/test.log');

        $this->adapter = new Ruckusing_Adapter_Sqlite3_Base($test_db, $logger);
        $this->adapter->logger->log("Test run started: " . date('Y-m-d g:ia T'));
    }

    /**
     * shutdown commands after test case
     */
    protected function tearDown()
    {
        //delete any tables we created
        if ($this->adapter->has_table('users', true)) {
            $this->adapter->drop_table('users');
        }
    }

    /**
     * test column definition
     */
    public function test_column_definition()
    {
        $this->markTestIncomplete('wait for endrju');
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
    }

    /**
     * test column definition with limit
     */
    public function test_column_definition_with_limit()
    {
        $bm = new Ruckusing_Migration_Base($this->adapter);
        $ts = time();
        $table_name = "users_$ts";
        $table = $bm->create_table($table_name);
        $table->column('username', 'string', array('limit' => 17));
        $table->finish();

        $username_actual = $this->adapter->column_info($table_name, "username");
        $this->assertEquals('character varying(17)', $username_actual['type']);
        $bm->drop_table($table_name);
    }
}