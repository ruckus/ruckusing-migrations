<?php

/**
 * Ruckusing
 *
 * @category  Ruckusing
 * @package   Ruckusing_Adapter
 * @subpackage Sqlite3
 * @author    Andrzej Oczkowicz <andrzejoczkowicz % gmail . com>
 * @author    Piotr Olaszewski <piotroo89 % gmail dot com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */

define('SQLITE3_MAX_IDENTIFIER_LENGTH', 64);

/**
 * Ruckusing_Adapter_Sqlite3_Base
 *
 * @category Ruckusing
 * @package  Ruckusing_Adapter
 * @subpackage   Sqlite3
 * @author    Piotr Olaszewski <piotroo89 % gmail dot com>
 * @author    Andrzej Oczkowicz <andrzejoczkowicz % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */
class Ruckusing_Adapter_Sqlite3_Base extends Ruckusing_Adapter_Base implements Ruckusing_Adapter_Interface
{
    /**
     * @var SQLite3
     */
    private $sqlite3;
    private $db_info;

    public function __construct($dsn, $logger)
    {
        parent::__construct($dsn);
        $this->connect($dsn);
        $this->set_logger($logger);
    }

    private function connect($dsn)
    {
        $this->db_connect($dsn);
    }

    private function db_connect($dsn)
    {
        if (!class_exists('SQLite3')) {
            throw new Ruckusing_Exception("\nIt appears you have not compiled PHP with SQLite3 support: missing class SQLite3",
                Ruckusing_Exception::INVALID_CONFIG
            );
        }
        $db_info = $this->get_dsn();
        if ($db_info) {
            $this->db_info = $db_info;
            try {
                $this->sqlite3 = new SQLite3($db_info['database']);
            } catch (Exception $e) {
                throw new Ruckusing_Exception("\n\nCould not connect to the DB, check database name\n\n",
                    Ruckusing_Exception::INVALID_CONFIG, $e->getCode(), $e);
            }
            return true;
        } else {
            throw new Ruckusing_Exception("\n\nCould not extract DB connection information from: {$dsn}\n\n",
                Ruckusing_Exception::INVALID_CONFIG
            );
        }
    }

    public function create_schema_version_table()
    {
        if (!$this->has_table(RUCKUSING_TS_SCHEMA_TBL_NAME)) {
            $t = $this->create_table(RUCKUSING_TS_SCHEMA_TBL_NAME, array('id' => false));
            $t->column('version', 'string');
            $t->finish();
            $this->add_index(RUCKUSING_TS_SCHEMA_TBL_NAME, 'version', array('unique' => true));
        }
    }

    public function get_database_name()
    {
        return $this->db_info['database'];
    }

    public function identifier($string)
    {
        return '"' . $string . '"';
    }

    public function quote($value, $column = null)
    {
        return ("'{$value}'");
    }

    public function query($query)
    {
        $this->logger->log($query);
        $query_type = $this->determine_query_type($query);
        $data = array();
        if ($query_type == SQL_SELECT || $query_type == SQL_SHOW) {
            $SqliteResult = $this->executeQuery($query);
            while ($row = $SqliteResult->fetchArray(SQLITE3_ASSOC)) {
                $data[] = $row;
            }
            return $data;
        } else {
            $this->executeQuery($query);
            if ($query_type == SQL_INSERT) {
                return $this->sqlite3->lastInsertRowID();
            }
            return true;
        }
    }

    private function executeQuery($query)
    {
        $SqliteResult = $this->sqlite3->query($query);
        if ($this->isError($SqliteResult)) {
            throw new Ruckusing_Exception(sprintf("Error executing 'query' with:\n%s\n\nReason: %s\n\n", $query, $this->lastErrorMsg()),
                Ruckusing_Exception::QUERY_ERROR
            );
        }
        return $SqliteResult;
    }

    private function determine_query_type($query)
    {
        $query = strtolower(trim($query));
        $match = array();
        preg_match('/^(\w)*/i', $query, $match);
        $type = $match[0];
        switch ($type) {
            case 'select':
                return SQL_SELECT;
            case 'update':
                return SQL_UPDATE;
            case 'delete':
                return SQL_DELETE;
            case 'insert':
                return SQL_INSERT;
            case 'alter':
                return SQL_ALTER;
            case 'drop':
                return SQL_DROP;
            case 'create':
                return SQL_CREATE;
            case 'pragma':
                return SQL_SHOW;
            default:
                return SQL_UNKNOWN_QUERY_TYPE;
        }
    }

    public function supports_migrations()
    {
        return true;
    }

    public function native_database_types()
    {
        $types = array(
            'primary_key' => array('name' => 'integer'),
            'string' => array('name' => 'varchar', 'limit' => 255),
            'text' => array('name' => 'text'),
            'tinytext' => array('name' => 'text'),
            'mediumtext' => array('name' => 'text'),
            'integer' => array('name' => 'integer'),
            'tinyinteger' => array('name' => 'smallint'),
            'smallinteger' => array('name' => 'smallint'),
            'mediuminteger' => array('name' => 'integer'),
            'biginteger' => array('name' => 'bigint'),
            'float' => array('name' => 'float'),
            'decimal' => array('name' => 'decimal', 'scale' => 0, 'precision' => 10),
            'datetime' => array('name' => 'datetime'),
            'timestamp' => array('name' => 'datetime'),
            'time' => array('name' => 'time'),
            'date' => array('name' => 'date'),
            'binary' => array('name' => 'blob'),
            'tinybinary' => array('name' => "blob"),
            'mediumbinary' => array('name' => "blob"),
            'longbinary' => array('name' => "blob"),
            'boolean' => array('name' => 'boolean')
        );

        return $types;
    }

    public function schema($output_file)
    {
        $command = sprintf("sqlite3 '%s' .schema > '%s'", $this->db_info['database'], $output_file);
        return system($command);
    }

    public function create_database($db, $options = array())
    {
        $this->log_unsupported_feature(__FUNCTION__);
        return true;
    }

    public function execute($query)
    {
        return $this->query($query);
    }

    /**
     * Quote a raw string.
     *
     * @param string $str Raw string
     *
     * @return string
     */
    public function quote_string($str)
    {

    }

    public function database_exists($db)
    {
        $this->log_unsupported_feature(__FUNCTION__);
        return true;
    }

    public function create_table($table_name, $options = array())
    {
        return new Ruckusing_Adapter_Sqlite3_TableDefinition($this, $table_name, $options);
    }

    public function drop_database($databaseName)
    {
        $this->log_unsupported_feature(__FUNCTION__);
        return true;
    }

    public function log_unsupported_feature($feature)
    {
        $this->logger->log(sprintf("WARNING: Unsupported SQLite3 feature: %s", $feature));
    }

    public function table_exists($tbl, $reload_tables = false)
    {
        $query = sprintf("SELECT tbl_name FROM sqlite_master WHERE type='table' AND tbl_name=%s;", $this->quote_column_name($tbl));
        $table = $this->select_one($query);
        return is_array($table) && sizeof($table) > 0;
    }

    public function drop_table($table_name)
    {
        $ddl = sprintf("DROP TABLE IF EXISTS %s", $this->quote_table_name($table_name));
        $this->execute_ddl($ddl);
        return true;
    }

    public function quote_table_name($string)
    {
        return '"' . $string . '"';
    }

    public function rename_table($name, $new_name)
    {
        if (empty($name)) {
            throw new Ruckusing_Exception("Missing original column name parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }
        if (empty($new_name)) {
            throw new Ruckusing_Exception("Missing new column name parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }
        $sql = sprintf("ALTER TABLE %s RENAME TO %s", $this->identifier($name), $this->identifier($new_name));
        return $this->execute_ddl($sql);
    }

    public function rename_column($table_name, $column_name, $new_column_name)
    {
        $this->log_unsupported_feature(__FUNCTION__);
        return true;
    }

    public function quote_column_name($string)
    {
        return '"' . $string . '"';
    }

    public function add_column($table_name, $column_name, $type, $options = array())
    {
        if (empty($table_name)) {
            throw new Ruckusing_Exception("Missing table name parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }
        if (empty($column_name)) {
            throw new Ruckusing_Exception("Missing column name parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }
        if (empty($type)) {
            throw new Ruckusing_Exception("Missing type parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }
        $defaultOptions = array(
            'limit' => null,
            'precision' => null,
            'scale' => null
        );
        $options = array_merge($defaultOptions, $options);
        $sql = sprintf("ALTER TABLE %s ADD COLUMN %s %s",
            $this->quote_table_name($table_name),
            $this->quote_column_name($column_name),
            $this->type_to_sql($type, $options)
        );
        $sql .= $this->add_column_options($type, $options);

        return $this->execute_ddl($sql);
    }

    public function remove_column($table_name, $column_name)
    {
        $this->log_unsupported_feature(__FUNCTION__);
    }

    public function change_column($table_name, $column_name, $type, $options = array())
    {
        $this->log_unsupported_feature(__FUNCTION__);
    }

    public function remove_index($table_name, $column_name, $options = array())
    {
        if (empty($table_name)) {
            throw new Ruckusing_Exception("Missing table name parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }
        if (empty($column_name)) {
            throw new Ruckusing_Exception("Missing column name parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }
        //did the user specify an index name?
        if (is_array($options) && array_key_exists('name', $options)) {
            $index_name = $options['name'];
        } else {
            $index_name = Ruckusing_Util_Naming::index_name($table_name, $column_name);
        }
        $sql = sprintf("DROP INDEX %s", $this->quote_column_name($index_name));

        return $this->execute_ddl($sql);
    }

    public function add_index($table_name, $column_name, $options = array())
    {
        if (empty($table_name)) {
            throw new Ruckusing_Exception("Missing table name parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }
        if (empty($column_name)) {
            throw new Ruckusing_Exception("Missing column name parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }
        //unique index?
        if (is_array($options) && array_key_exists('unique', $options) && $options['unique'] === true) {
            $unique = true;
        } else {
            $unique = false;
        }

        //did the user specify an index name?
        if (is_array($options) && array_key_exists('name', $options)) {
            $index_name = $options['name'];
        } else {
            $index_name = Ruckusing_Util_Naming::index_name($table_name, $column_name);
        }

        if (strlen($index_name) > SQLITE3_MAX_IDENTIFIER_LENGTH) {
            $msg = "The auto-generated index name is too long for Postgres (max is 64 chars). ";
            $msg .= "Considering using 'name' option parameter to specify a custom name for this index.";
            $msg .= " Note: you will also need to specify";
            $msg .= " this custom name in a drop_index() - if you have one.";
            throw new Ruckusing_Exception($msg, Ruckusing_Exception::INVALID_INDEX_NAME);
        }

        if (!is_array($column_name)) {
            $column_names = array($column_name);
        } else {
            $column_names = $column_name;
        }
        $cols = array();
        foreach ($column_names as $name) {
            $cols[] = $this->quote_column_name($name);
        }
        $sql = sprintf("CREATE %sINDEX %s ON %s(%s)",
            $unique ? "UNIQUE " : "",
            $this->quote_column_name($index_name),
            $this->quote_column_name($table_name),
            join(", ", $cols)
        );

        return $this->execute_ddl($sql);
    }

    public function add_column_options($type, $options, $performing_change = false)
    {
        if (!is_array($options)) {
            return '';
        }

        $sql = "";
        if (!$performing_change) {
            if (array_key_exists('default', $options) && $options['default'] !== null) {
                if (is_int($options['default'])) {
                    $default_format = '%d';
                } elseif (is_bool($options['default'])) {
                    $default_format = "'%d'";
                } else {
                    $default_format = "'%s'";
                }
                $default_value = sprintf($default_format, $options['default']);
                $sql .= sprintf(" DEFAULT %s", $default_value);
            }

            if (array_key_exists('null', $options) && $options['null'] === false) {
                $sql .= " NOT NULL";
            }
        }
        return $sql;
    }

    public function type_to_sql($type, $options = array())
    {
        $natives = $this->native_database_types();
        if (!array_key_exists($type, $natives)) {
            $error = sprintf("Error: I don't know what column type of '%s' maps to for SQLite3.", $type);
            $error .= "\nYou provided: {$type}\n";
            $error .= "Valid types are: \n";
            $error .= implode(', ', array_diff(array_keys($natives), array('primary_key')));
            throw new Ruckusing_Exception($error, Ruckusing_Exception::INVALID_ARGUMENT);
        }

        $native_type = $natives[$type];
        $column_type_sql = $native_type['name'];

        $optionsLimit = isset($options['limit']) ? $options['limit'] : null;
        $nativeLimit = isset($native_type['limit']) ? $native_type['limit'] : null;
        $limit = $optionsLimit ? $optionsLimit : $nativeLimit;

        if ($limit !== null) {
            $column_type_sql .= sprintf("(%d)", $limit);
        }
        return $column_type_sql;
    }

    public function column_info($table, $column)
    {
        if (empty($table)) {
            throw new Ruckusing_Exception("Missing table name parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }
        if (empty($column)) {
            throw new Ruckusing_Exception("Missing original column name parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }

        try {
            $pragmaTable = $this->query('pragma table_info(' . $table . ')');
            $data = array();

            $pragmaTable = array_values(array_filter($pragmaTable, function ($element) use ($column) {
                return $element['name'] == $column ? $element : false;
            }));

            if (isset($pragmaTable[0]) && is_array($pragmaTable[0])) {
                $data['type'] = $pragmaTable[0]['type'];
                $data['name'] = $column;
                $data['field'] = $column;
                $data['null'] = $pragmaTable[0]['notnull'] == 0;
                $data['default'] = $pragmaTable[0]['dflt_value'];
            }
            return $data;
        } catch (Exception $e) {
            return null;
        }
    }

    public function execute_ddl($ddl)
    {
        $this->query($ddl);
        return true;
    }

    public function indexes($table_name)
    {
        $sql = sprintf("PRAGMA INDEX_LIST(%s);", $this->quote_table_name($table_name));
        $result = $this->select_all($sql);

        $indexes = array();
        foreach ($result as $row) {
            $indexes[] = array(
                'name' => $row['name'],
                'unique' => $row['unique'] ? true : false
            );
        }

        return $indexes;
    }

    private function isError($SQLite3Result)
    {
        return ($SQLite3Result === FALSE);
    }

    private function lastErrorMsg()
    {
        return $this->sqlite3->lastErrorMsg();
    }

    public function primary_keys($table)
    {
        $result = $this->query('pragma table_info(' . $table . ')');
        $primary_keys = array();
        foreach ($result as $row) {
            if ($row['pk']) {
                $primary_keys[] = array(
                    'name' => $row['name'],
                    'type' => $row['type']
                );
            }
        }
        return $primary_keys;
    }

    public function select_one($query)
    {
        $this->logger->log($query);
        $query_type = $this->determine_query_type($query);

        if ($query_type == SQL_SELECT || $query_type == SQL_SHOW) {
            $res = $this->executeQuery($query);
            if ($this->isError($res)) {
                throw new Ruckusing_Exception(sprintf("Error executing 'query' with:\n%s\n\nReason: %s\n\n", $query, $this->lastErrorMsg()), Ruckusing_Exception::QUERY_ERROR);
            }
            return $res->fetchArray(SQLITE3_ASSOC);
        } else {
            throw new Ruckusing_Exception("Query for select_one() is not one of SELECT or SHOW: $query", Ruckusing_Exception::QUERY_ERROR);
        }
    }

    public function select_all($query)
    {
        return $this->query($query);
    }

    public function column_definition($column_name, $type, $options = null)
    {
        $col = new Ruckusing_Adapter_ColumnDefinition($this, $column_name, $type, $options);
        return $col->__toString();
    }

    public function has_index($table_name, $column_name, $options = array())
    {
        if (empty($table_name)) {
            throw new Ruckusing_Exception("Missing table name parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }
        if (empty($column_name)) {
            throw new Ruckusing_Exception("Missing column name parameter", Ruckusing_Exception::INVALID_ARGUMENT);
        }

        if (is_array($options) && array_key_exists('name', $options)) {
            $index_name = $options['name'];
        } else {
            $index_name = Ruckusing_Util_Naming::index_name($table_name, $column_name);
        }
        $indexes = $this->indexes($table_name);
        foreach ($indexes as $idx) {
            if ($idx['name'] == $index_name) {
                return true;
            }
        }
        return false;
    }
}