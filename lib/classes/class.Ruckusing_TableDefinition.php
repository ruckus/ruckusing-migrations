<?php

/**
 * Implementation of Ruckusing_TableDefinition
 *
 * @category Ruckusing_Classes
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_TableDefinition
{
    private $columns = array();
    private $adapter;

    /**
     * Creates an instance of Ruckusing_TableDefinition
     *
     * @param object $adapter The current adapter
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
     * or a Ruckusing_ColumnDefinition object.
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
            if (($column instanceof Ruckusing_ColumnDefinition) && $col->name == $column->name) {
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
