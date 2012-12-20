<?php

if (!defined('BASE')) {
    define('BASE', dirname(__FILE__) . '/..');
}

require_once BASE  . '/test_helper.php';
require_once RUCKUSING_BASE  . '/lib/classes/class.Ruckusing_BaseAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/class.Ruckusing_BaseMigration.php';
require_once RUCKUSING_BASE  . '/lib/classes/class.Ruckusing_iAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/adapters/class.Ruckusing_MySQLAdapter.php';
require_once RUCKUSING_BASE  . '/lib/classes/Ruckusing_exceptions.php';

/**
 * Implementation of BaseMigrationTest.
 * To run these unit-tests an empty test database needs to be setup in database.inc.php
 * and of course, it has to really exist.
 *
 * @category Ruckusing_Tests
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class BaseMigrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup commands before test case
     */
    protected function setUp()
    {
        $ruckusing_config = require RUCKUSING_BASE . '/config/database.inc.php';

        if (!is_array($ruckusing_config) || !(array_key_exists("db", $ruckusing_config) && array_key_exists("mysql_test", $ruckusing_config['db']))) {
            die("\n'mysql_test' DB is not defined in config/database.inc.php\n\n");
            //$this->markTestSkipped
        }

        $test_db = $ruckusing_config['db']['mysql_test'];

        //setup our log
        $logger = Ruckusing_Logger::instance(RUCKUSING_BASE . '/tests/logs/test.log');

        $this->adapter = new Ruckusing_MySQLAdapter($test_db, $logger);
        $this->adapter->logger->log("Test run started: " . date('Y-m-d g:ia T') );
    }//setUp()

    /**
     * shutdown commands after test case
     */
    protected function tearDown()
    {
        //delete any tables we created
        if ($this->adapter->has_table('users', true)) {
            $this->adapter->drop_table('users');
        }

        if ($this->adapter->has_table(RUCKUSING_TS_SCHEMA_TBL_NAME, true)) {
            $this->adapter->drop_table(RUCKUSING_TS_SCHEMA_TBL_NAME);
        }
    }

    /**
     * test case for creating an index with a custom name
     */
    public function test_can_create_index_with_custom_name()
    {
        //create it
        $this->adapter->execute_ddl("CREATE TABLE `users` ( name varchar(20), age int(3) );");
        $base = new Ruckusing_BaseMigration();
        $base->set_adapter($this->adapter);
        $base->add_index("users", "name", array('name' => 'my_special_index'));

        //ensure it exists
        $this->assertEquals(true, $this->adapter->has_index("users", "name", array('name' => 'my_special_index')));

        //drop it
        $base->remove_index("users", "name", array('name' => 'my_special_index'));
        $this->assertEquals(false, $this->adapter->has_index("users", "my_special_index"));
    }
}
