<?php

define('SQL_UNKNOWN_QUERY_TYPE', 1);
define('SQL_SELECT', 2);
define('SQL_INSERT', 4);
define('SQL_UPDATE', 8);
define('SQL_DELETE', 16);
define('SQL_ALTER', 32);
define('SQL_DROP', 64);
define('SQL_CREATE', 128);
define('SQL_SHOW', 256);
define('SQL_RENAME', 512);
define('SQL_SET', 1024);

/**
 * Implementation of Ruckusing_BaseAdapter.
 *
 * @category Ruckusing_Classes
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_BaseAdapter
{
    private $dsn;
    private $db;
    private $conn;

    /**
     * Creates an instance of Ruckusing_BaseAdapter
     *
     * @param object $dsn The current dsn
     */
    public function __construct($dsn)
    {
        $this->set_dsn($dsn);
    }

    /**
     * Set a dsn
     *
     * @param object $dsn The current dsn
     */
    public function set_dsn($dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * Get the current dsn
     *
     * @return object
     */
    public function get_dsn()
    {
        return $this->dsn;
    }

    /**
     * Set a db
     *
     * @param array $db The current db
     */
    public function set_db($db)
    {
        $this->db = $db;
    }

    /**
     * Get the current db
     *
     * @return array
     */
    public function get_db()
    {
        return $this->db;
    }

    /**
     * Set a logger
     *
     * @param object $logger The current logger
     */
    public function set_logger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the current logger
     *
     * @return object
     */
    public function get_logger($logger)
    {
        return $this->logger;
    }

    /**
     * Check table exists
     *
     * @return boolean
     */
    public function has_table($tbl)
    {
        return $this->table_exists($tbl);
    }

}
