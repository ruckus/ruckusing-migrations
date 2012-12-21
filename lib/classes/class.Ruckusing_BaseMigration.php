<?php

require_once RUCKUSING_BASE . '/lib/classes/class.Ruckusing_iAdapter.php';

/**
 * Implementation of Ruckusing_BaseMigration.
 *
 * @category Ruckusing_Classes
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_BaseMigration
{
    private $adapter;

    /**
     * Set an adapter
     *
     * @param object $a the adapater
     */
    public function set_adapter($a)
    {
        $this->adapter = $a;
    }

    /**
     * Get the current adapter
     *
     * @return object
     */
    public function get_adapter()
    {
        return $this->adapter;
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
        return $this->adapter->create_database($name, $options);
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
        return $this->adapter->drop_database($name);
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
        return $this->adapter->drop_table($tbl);
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
        return $this->adapter->rename_table($name, $new_name);
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
        return $this->adapter->rename_column($tbl_name, $column_name, $new_column_name);
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
        return $this->adapter->add_column($table_name, $column_name, $type, $options);
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
        return $this->adapter->remove_column($table_name, $column_name);
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
        return $this->adapter->change_column($table_name, $column_name, $type, $options);
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
        return $this->adapter->add_index($table_name, $column_name, $options);
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
        return $this->adapter->remove_index($table_name, $column_name, $options);
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
        return $this->adapter->create_table($table_name, $options);
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
        return $this->adapter->query($query);
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
        return $this->adapter->select_one($sql);
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
        return $this->adapter->select_all($sql);

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
        return $this->adapter->query($sql);
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
        return $this->adapter->quote_string($str);
    }

}//Ruckusing_BaseMigration
