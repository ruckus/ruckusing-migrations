<?php

/**
 * Ruckusing
 *
 * @category  Ruckusing
 * @package   Ruckusing_Adapter
 * @author    Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */

/**
 * Ruckusing_Adapter_ColumnDefinition
 *
 * @category Ruckusing
 * @package  Ruckusing_Adapter
 * @author   Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */
class Ruckusing_Adapter_ColumnDefinition
{
    private $adapter;
    /**
     * name
     *
     * @var string
     */
    public $name;

    /**
     * type
     *
     * @var mixed
     */
    public $type;

    /**
     * properties
     *
     * @var mixed
     */
    public $properties;

    /**
     * options
     *
     * @var array
     */
    private $options = array();

    /**
     * Creates an instance of Ruckusing_Adapter_ColumnDefinition
     *
     * @param Ruckusing_Adapter_Base $adapter The current adapter
     * @param string                 $name    the name of the column
     * @param string                 $type    the type of the column
     * @param array                  $options the column options
     *
     * @return Ruckusing_Adapter_ColumnDefinition
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
