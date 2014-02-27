<?php

/**
 * @category Ruckusing_Tests
 * @package  Ruckusing_Migrations
 * @author   (c) Andrzej Oczkowicz <andrzejoczkowicz % gmail . com>
 */
class PostgresAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var $adapter Ruckusing_Adapter_Sqlite3_Base
     */
    private $adapter;

    protected function setUp()
    {
        $ruckusing_config = require RUCKUSING_BASE . '/config/database.inc.php';

        if (!is_array($ruckusing_config) || !(array_key_exists("db", $ruckusing_config) && array_key_exists("sqlite_test", $ruckusing_config['db']))) {
            $this->markTestSkipped("\n'sqlite_test' DB is not defined in config/database.inc.php\n\n");
        }

        $test_db = $ruckusing_config['db']['sqlite_test'];

        //setup our log
        $logger = Ruckusing_Util_Logger::instance(RUCKUSING_BASE . '/tests/logs/test.log');

        $this->adapter = new Ruckusing_Adapter_Sqlite3_Base($test_db, $logger);
        $this->adapter->logger->log("Test run started: " . date('Y-m-d g:ia T'));
    }

    protected function tearDown()
    {
        if ($this->adapter->has_table('users', true)) {
            $this->adapter->drop_table('users');
        }

        if ($this->adapter->has_table(RUCKUSING_TS_SCHEMA_TBL_NAME, true)) {
            $this->adapter->drop_table(RUCKUSING_TS_SCHEMA_TBL_NAME);
        }

        $db = "test_db";
        if ($this->adapter->database_exists($db)) {
            $this->adapter->drop_database($db);
        }
    }

    public function test_select_one()
    {
        $table = $this->adapter->create_table('users');
        $table->column('name', 'string', array('limit' => 20));
        $table->column('age', 'integer');
        $table->finish();

        $id1 = $this->adapter->query(sprintf("INSERT INTO users (name, age) VALUES ('%s', %d) RETURNING \"id\"", 'Taco', 32));
        $this->assertEquals(1, $id1);

        $result = $this->adapter->select_one(sprintf("SELECT * FROM users WHERE name = '%s'", 'Taco'));
        $this->assertEquals(true, is_array($result));
        $this->assertEquals('Taco', $result['name']);
        $this->assertEquals(32, $result['age']);

        $this->drop_table('users');
    }

}//class
