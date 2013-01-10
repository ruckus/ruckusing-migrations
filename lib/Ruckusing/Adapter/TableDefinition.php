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
 * Ruckusing_Adapter_TableDefinition
 *
 * @category Ruckusing
 * @package  Ruckusing_Adapter
 * @author   Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */
class Ruckusing_Adapter_TableDefinition
{
    /**
     * columns
     *
     * @var array
     */
    private $columns = array();

    /**
     * adapter
     *
     * @var Ruckusing_Adapter_Base
     */
    private $adapter;

    /**
     * Creates an instance of Ruckusing_Adapter_TableDefinition
     *
     * @param Ruckusing_Adapter_Base $adapter the current adapter
     *
     * @return Ruckusing_Adapter_TableDefinition
     */
    public function __construct($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Determine whether or not the given column already exists in our
     * table definition.
     *
     * This method is lax enough that it can take either a string column name
     * or a Ruckusing_Adapters_ColumnDefinition object.
     *
     * @param string $column the name of the column
     *
     * @return boolean
     */
    public function included($column)
    {
        $k = count($this->columns);
        for ($i = 0; $i < $k; $i++) {
            $col = $this->columns[$i];
            if (is_string($column) && $col->name == $column) {
                return true;
            }
            if (($column instanceof Ruckusing_Adapter_ColumnDefinition) && $col->name == $column->name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get list of columns
     *
     * @return string
     */
    public function to_sql()
    {
        return join(",", $this->columns);
    }
}
