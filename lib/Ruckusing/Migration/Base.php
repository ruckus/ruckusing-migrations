<?php

/**
 * Ruckusing
 *
 * @category  Ruckusing
 * @package   Ruckusing_Migration
 * @author    Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */

require_once RUCKUSING_BASE . '/lib/Ruckusing/Adapter/Interface.php';

/**
 * Ruckusing_Migration_Base
 *
 * @category Ruckusing
 * @package  Ruckusing_Migration
 * @author   Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */
class Ruckusing_Migration_Base
{
    /**
     * adapter
     *
     * @var Ruckusing_Adapter_Base
     */
    private $_adapter;

    /**
     * Set an adapter
     *
     * @param Ruckusing_Adapter_Base $adapter the adapter to set
     */
    public function set_adapter($adapter)
    {
        if (!($adapter instanceof Ruckusing_Adapter_Base)) {
            throw new Ruckusing_Exception(
                    'Adapter must be implement Ruckusing_Adapter_Base!',
                    Ruckusing_Exception::INVALID_ADAPTER
            );
        }
        $this->_adapter = $adapter;
    }

    /**
     * Get the current adapter
     *
     * @return object
     */
    public function get_adapter()
    {
        return $this->_adapter;
    }

    /**
     * Create a database
     *
     * @param string $name    the name of the database
     * @param array  $options
     *
     * @return boolean
     */
    public function create_database($name, $options = null)
    {
        return $this->_adapter->create_database($name, $options);
    }

    /**
     * Drop a database
     *
     * @param string $name the name of the database
     *
     * @return boolean
     */
    public function drop_database($name)
    {
        return $this->_adapter->drop_database($name);
    }

    /**
     * Drop a table
     *
     * @param string $tbl the name of the table
     *
     * @return boolean
     */
    public function drop_table($tbl)
    {
        return $this->_adapter->drop_table($tbl);
    }

    /**
     * Rename a table
     *
     * @param string $name     the name of the table
     * @param string $new_name the new name of the table
     *
     * @return boolean
     */
    public function rename_table($name, $new_name)
    {
        return $this->_adapter->rename_table($name, $new_name);
    }

    /**
     * Rename a column
     *
     * @param string $tbl_name        the name of the table
     * @param string $column_name     the column name
     * @param string $new_column_name the new column name
     *
     * @return boolean
     */
    public function rename_column($tbl_name, $column_name, $new_column_name)
    {
        return $this->_adapter->rename_column($tbl_name, $column_name, $new_column_name);
    }

    /**
     * Add a column
     *
     * @param string $table_name  the name of the table
     * @param string $column_name the column name
     * @param string $type        the column type
     * @param string $options
     *
     * @return boolean
     */
    public function add_column($table_name, $column_name, $type, $options = array())
    {
        return $this->_adapter->add_column($table_name, $column_name, $type, $options);
    }

    /**
     * Remove a column
     *
     * @param string $table_name  the name of the table
     * @param string $column_name the column name
     *
     * @return boolean
     */
    public function remove_column($table_name, $column_name)
    {
        return $this->_adapter->remove_column($table_name, $column_name);
    }

    /**
     * Change a column
     *
     * @param string $table_name  the name of the table
     * @param string $column_name the column name
     * @param string $type        the column type
     * @param string $options
     *
     * @return boolean
     */
    public function change_column($table_name, $column_name, $type, $options = array())
    {
        return $this->_adapter->change_column($table_name, $column_name, $type, $options);
    }

    /**
     * Add an index
     *
     * @param string $table_name  the name of the table
     * @param string $column_name the column name
     * @param string $options
     *
     * @return boolean
     */
    public function add_index($table_name, $column_name, $options = array())
    {
        return $this->_adapter->add_index($table_name, $column_name, $options);
    }

    /**
     * Remove an index
     *
     * @param string $table_name  the name of the table
     * @param string $column_name the column name
     * @param string $options
     *
     * @return boolean
     */
    public function remove_index($table_name, $column_name, $options = array())
    {
        return $this->_adapter->remove_index($table_name, $column_name, $options);
    }

    /**
     * Create a table
     *
     * @param string $table_name the name of the table
     * @param string $options
     *
     * @return boolean
     */
    public function create_table($table_name, $options = array())
    {
        return $this->_adapter->create_table($table_name, $options);
    }

    /**
     * Execute a query
     *
     * @param string $query the query to run
     *
     * @return boolean
     */
    public function execute($query)
    {
        return $this->_adapter->query($query);
    }

    /**
     * Select one query
     *
     * @param string $sql the query to run
     *
     * @return array
     */
    public function select_one($sql)
    {
        return $this->_adapter->select_one($sql);
    }

    /**
     * Select all query
     *
     * @param string $sql the query to run
     *
     * @return array
     */
    public function select_all($sql)
    {
        return $this->_adapter->select_all($sql);

    }

    /**
     * Execute a query
     *
     * @param string $sql the query to run
     *
     * @return boolean
     */
    public function query($sql)
    {
        return $this->_adapter->query($sql);
    }

    /**
     * Quote a string
     *
     * @param string $str the string to quote
     *
     * @return string
     */
    public function quote_string($str)
    {
        return $this->_adapter->quote_string($str);
    }

}

/**
 * Implementation of Ruckusing_BaseMigration.
 * Fix for backward compatibility, take care of old migrations files
 * before switch to new structure
 *
 * @category Ruckusing
 * @package  Ruckusing_Migration
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_BaseMigration extends Ruckusing_Migration_Base
{
}
