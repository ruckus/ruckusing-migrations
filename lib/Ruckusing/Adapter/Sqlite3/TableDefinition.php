<?php

/**
 * Ruckusing
 *
 * @category  Ruckusing
 * @package   Ruckusing_Adapter
 * @subpackage   Sqlite3
 * @author    Piotr Olaszewski <piotroo89 % gmail dot com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */

/**
 * Ruckusing_Adapter_Sqlite3_TableDefinition
 *
 * @category Ruckusing
 * @package  Ruckusing_Adapter
 * @subpackage   Sqlite3
 * @author    Piotr Olaszewski <piotroo89 % gmail dot com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */
class Ruckusing_Adapter_Sqlite3_TableDefinition extends Ruckusing_Adapter_TableDefinition
{
    /**
     * adapter PgSQL
     *
     * @var Ruckusing_Adapter_Sqlite3_Base
     */
    private $_adapter;

    /**
     * Name
     *
     * @var string
     */
    private $_name;

    /**
     * options
     *
     * @var array
     */
    private $_options;

    /**
     * sql
     *
     * @var string
     */
    private $_sql = "";

    /**
     * initialized
     *
     * @var boolean
     */
    private $_initialized = false;

    /**
     * Columns
     *
     * @var array
     */
    private $_columns = array();

    /**
     * Table definition
     *
     * @var array
     */
    private $_table_def;

    /**
     * primary keys
     *
     * @var array
     */
    private $_primary_keys = array();

    /**
     * auto generate id
     *
     * @var boolean
     */
    private $_auto_generate_id = true;

    /**
     * Creates an instance of Ruckusing_Adapter_Sqlite3_TableDefinition
     *
     * @param Ruckusing_Adapter_Sqlite3_Base $adapter the current adapter
     * @param string $name the table name
     * @param array $options
     *
     * @throws Ruckusing_Exception
     * @return \Ruckusing_Adapter_Sqlite3_TableDefinition
     */
    public function __construct($adapter, $name, $options = array())
    {
        //sanity check
        if (!($adapter instanceof Ruckusing_Adapter_Sqlite3_Base)) {
            throw new Ruckusing_Exception("Invalid Postgres Adapter instance.", Ruckusing_Exception::INVALID_ADAPTER);
        }
        if (!$name) {
            throw new Ruckusing_Exception("Invalid 'name' parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }

        $this->_adapter = $adapter;
        $this->_name = $name;
        $this->_options = $options;
        $this->init_sql($name, $options);
        $this->_table_def = new Ruckusing_Adapter_TableDefinition($this->_adapter, $this->_options);

        if (array_key_exists('id', $options)) {
            if (is_bool($options['id']) && $options['id'] == false) {
                $this->_auto_generate_id = false;
            }
            //if its a string then we want to auto-generate an integer-based primary key with this name
            if (is_string($options['id'])) {
                $this->_auto_generate_id = true;
                $this->_primary_keys[] = $options['id'];
            }
        }
    }
}