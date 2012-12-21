<?php

/**
 * Implementation of Ruckusing_ColumnDefinition.
 *
 * @category Ruckusing_Classes
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_ColumnDefinition
{
    private $adapter;
    public $name;
    public $type;
    public $properties;
    private $options = array();

    /**
     * Creates an instance of Ruckusing_ColumnDefinition
     *
     * @param object $adapter The current adapter
     * @param string $name    the name of the column
     * @param string the type of the column
     * @param array $options
    */
    public function __construct($adapter, $name, $type, $options = array())
    {
        $this->adapter = $adapter;
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * sql version
     *
     * @return string
     */
    public function to_sql()
    {
        $column_sql = sprintf("%s %s", $this->adapter->identifier($this->name), $this->sql_type());
        $column_sql .= $this->adapter->add_column_options($this->type, $this->options);

        return $column_sql;
    }

    /**
     * sql string version
     *
     * @return string
     */
    public function __toString()
    {
        return $this->to_sql();
    }

    /**
     * sql version
     *
     * @return string
     */
    private function sql_type()
    {
        return $this->adapter->type_to_sql($this->type, $this->options);
    }
}
