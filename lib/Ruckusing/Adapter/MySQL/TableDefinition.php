<?php

/**
 * Ruckusing
 *
 * @category  Ruckusing
 * @package   Ruckusing_Adapter
 * @subpackage   MySQL
 * @author    Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */

/**
 * Ruckusing_Adapter_MySQL_TableDefinition
 *
 * @category Ruckusing
 * @package  Ruckusing_Adapter
 * @subpackage   MySQL
 * @author   Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */
class Ruckusing_Adapter_MySQL_TableDefinition
{
    /**
     * adapter MySQL
     *
     * @var Phigrate_Adapter_Mysql_Adapter
     */
    private $adapter;

    /**
     * Name
     *
     * @var string
     */
    private $name;

    /**
     * options
     *
     * @var array
     */
    private $options;

    /**
     * sql
     *
     * @var string
     */
    private $sql = "";

    /**
     * initialized
     *
     * @var boolean
     */
    private $initialized = false;

    /**
     * Columns
     *
     * @var array
     */
    private $columns = array();

    /**
     * Table definition
     *
     * @var array
     */
    private $table_def;

    /**
     * primary keys
     *
     * @var array
     */
    private $primary_keys = array();

    /**
     * auto generate id
     *
     * @var boolean
     */
    private $auto_generate_id = true;

    /**
     * Creates an instance of Ruckusing_Adapters_MySQL_Adapter
     *
     * @param Ruckusing_Adapter_Base $adapter the current adapter
     * @param string                 $name    the table name
     * @param array                  $options the options
     *
     * @return Ruckusing_Adapter_MySQL_TableDefinition
     */
    public function __construct($adapter, $name, $options = array())
    {
        //sanity check
        if ( !($adapter instanceof Ruckusing_Adapter_MySQL_Base)) {
            throw new Ruckusing_MissingAdapterException("Invalid MySQL Adapter instance.");
        }
        if (!$name) {
            throw new Ruckusing_ArgumentException("Invalid 'name' parameter");
        }

        $this->adapter = $adapter;
        $this->name = $name;
        $this->options = $options;
        $this->init_sql($name, $options);
        $this->table_def = new Ruckusing_Adapter_TableDefinition($this->adapter, $this->options);

        if (array_key_exists('id', $options)) {
            if (is_bool($options['id']) && $options['id'] == false) {
                $this->auto_generate_id = false;
            }
            //if its a string then we want to auto-generate an integer-based
            //primary key with this name
            if (is_string($options['id'])) {
                $this->auto_generate_id = true;
                $this->primary_keys[] = $options['id'];
            }
        }
    }//__construct

    /*
     public function primary_key($name, $auto_increment)
     {
    $options = array('auto_increment' => $auto_increment);
    $this->column($name, "primary_key", $options);
    }
    */

    /**
     * Create a column
     *
     * @param string $column_name the column name
     * @param string $type        the column type
     * @param array  $options
     */
    public function column($column_name, $type, $options = array())
    {
        //if there is already a column by the same name then silently fail
        //and continue
        if ($this->table_def->included($column_name) == true) {
            return;
        }

        $column_options = array();

        if (array_key_exists('primary_key', $options)) {
            if ($options['primary_key'] == true) {
                $this->primary_keys[] = $column_name;
            }
        }

        if (array_key_exists('auto_increment', $options)) {
            if ($options['auto_increment'] == true) {
                $column_options['auto_increment'] = true;
            }
        }
        $column_options = array_merge($column_options, $options);
        $column = new Ruckusing_Adapter_ColumnDefinition($this->adapter, $column_name, $type, $column_options);

        $this->columns[] = $column;
    }//column

    /**
     * Get all primary keys
     *
     * @return string
     */
    private function keys()
    {
        if (count($this->primary_keys) > 0) {
            $lead = ' PRIMARY KEY (';
            $quoted = array();
            foreach ($this->primary_keys as $key) {
                $quoted[] = sprintf("%s", $this->adapter->identifier($key));
            }
            $primary_key_sql = ",\n" . $lead . implode(",", $quoted) . ")";

            return($primary_key_sql);
        } else {
            return '';
        }
    }

    /**
     * Table definition
     *
     * @param boolean $wants_sql
     *
     * @return boolean | string
     */
    public function finish($wants_sql = false)
    {
        if ($this->initialized == false) {
            throw new Ruckusing_InvalidTableDefinitionException(sprintf("Table Definition: '%s' has not been initialized", $this->name));
        }
        if (is_array($this->options) && array_key_exists('options', $this->options)) {
            $opt_str = $this->options['options'];
        } else {
            $opt_str = null;
        }

        $close_sql = sprintf(") %s;",$opt_str);
        $create_table_sql = $this->sql;

        if ($this->auto_generate_id === true) {
            $this->primary_keys[] = 'id';
            $primary_id = new Ruckusing_Adapter_ColumnDefinition($this->adapter, 'id', 'integer',
                            array('unsigned' => true, 'null' => false, 'auto_increment' => true));

            $create_table_sql .= $primary_id->to_sql() . ",\n";
        }

        $create_table_sql .= $this->columns_to_str();
        $create_table_sql .= $this->keys() . $close_sql;

        if ($wants_sql) {
            return $create_table_sql;
        } else {
            return $this->adapter->execute_ddl($create_table_sql);
        }
    }//finish

    /**
     * get all columns
     *
     * @return string
     */
    private function columns_to_str()
    {
        $str = "";
        $fields = array();
        $len = count($this->columns);
        for ($i = 0; $i < $len; $i++) {
            $c = $this->columns[$i];
            $fields[] = $c->__toString();
        }

        return join(",\n", $fields);
    }

    /**
     * Init create sql
     *
     * @param string $name
     * @param array  $options
     */
    private function init_sql($name, $options)
    {
        //are we forcing table creation? If so, drop it first
        if (array_key_exists('force', $options) && $options['force'] == true) {
            try {
                $this->adapter->drop_table($name);
            } catch (Ruckusing_MissingTableException $e) {
                //do nothing
            }
        }
        $temp = "";
        if (array_key_exists('temporary', $options)) {
            $temp = " TEMPORARY";
        }
        $create_sql = sprintf("CREATE%s TABLE ", $temp);
        $create_sql .= sprintf("%s (\n", $this->adapter->identifier($name));
        $this->sql .= $create_sql;
        $this->initialized = true;
    }//init_sql
}
