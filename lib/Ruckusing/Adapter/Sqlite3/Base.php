<?php

class Ruckusing_Adapter_Sqlite3_Base extends Ruckusing_Adapter_Base implements Ruckusing_Adapter_Interface
{
    private $conn;

    /**
     * Creates an instance of Ruckusing_Adapter_PgSQL_Base
     *
     * @param array                 $dsn    The current dsn being used
     * @param Ruckusing_Util_Logger $logger the current logger
     *
     * @return Ruckusing_Adapter_Sqlite3_Base
     */
    public function __construct($dsn, $logger)
    {
        parent::__construct($dsn);
        $this->connect($dsn);
        $this->set_logger($logger);
    }

    /**
     * Connect to the db
     *
     * @param string $dsn the current dsn
     */
    private function connect($dsn)
    {
        $this->db_connect($dsn);
    }

    /**
     * Connect to the db
     *
     * @param string $dsn the current dsn
     *
     * @return boolean
     */
    private function db_connect($dsn)
    {
        if (!function_exists('sqlite_open')) {
            throw new Ruckusing_Exception(
                "\nIt appears you have not compiled PHP with SQLite support: missing function sqlite_open()",
                Ruckusing_Exception::INVALID_CONFIG
            );
        }
        $db_info = $this->get_dsn();
        if ($db_info) {
            $this->db_info = $db_info;
            $this->conn = sqlite_open($db_info['database']);
            if ($this->conn === FALSE) {
                throw new Ruckusing_Exception(
                    "\n\nCould not connect to the DB, check database name\n\n",
                    Ruckusing_Exception::INVALID_CONFIG
                );
            }
            return true;
        } else {
            throw new Ruckusing_Exception(
                "\n\nCould not extract DB connection information from: {$dsn}\n\n",
                Ruckusing_Exception::INVALID_CONFIG
            );
        }
    }

    /**
     * get the current database name
     *
     * @return string
     */
    public function get_database_name()
    {

    }

    /**
     * Quote a raw string.
     *
     * @param string $value Raw string
     * @param string $column the column name
     *
     * @return string
     */
    public function quote($value, $column = null)
    {

    }

    /**
     * Wrapper to execute a query
     *
     * @param string $query query to run
     *
     * @return boolean
     */
    public function query($query)
    {
        $this->logger->log($query);
        $query_type = $this->determine_query_type($query);
        $data = array();
        if ($query_type == SQL_SELECT || $query_type == SQL_SHOW) {
            $res = sqlite_query($this->conn, $query);
            if ($this->isError($res)) {
                throw new Ruckusing_Exception(
                    sprintf("Error executing 'query' with:\n%s\n\nReason: %s\n\n", $query, pg_last_error($this->conn)),
                    Ruckusing_Exception::QUERY_ERROR
                );
            }
            while ($row = pg_fetch_assoc($res)) {
                $data[] = $row;
            }

            return $data;
        } else {
            // INSERT, DELETE, etc...
            $res = pg_query($this->conn, $query);
            if ($this->isError($res)) {
                throw new Ruckusing_Exception(
                    sprintf("Error executing 'query' with:\n%s\n\nReason: %s\n\n", $query, pg_last_error($this->conn)),
                    Ruckusing_Exception::QUERY_ERROR
                );
            }
            // if the query contained a 'RETURNING' class then grab its value
            $returning_regex = '/ RETURNING \"(.+)\"$/';
            $matches = array();
            if (preg_match($returning_regex, $query, $matches)) {
                if (count($matches) == 2) {
                    $returning_column_value = pg_fetch_result($res, 0, $matches[1]);

                    return ($returning_column_value);
                }
            }

            return true;
        }
    }

    /**
     * Check query type
     *
     * @param string $query query to run
     *
     * @return int
     */
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
            default:
                return SQL_UNKNOWN_QUERY_TYPE;
        }
    }

    /**
     * supports migrations ?
     *
     * @return boolean
     */
    public function supports_migrations()
    {

    }

    /**
     * native database types
     *
     * @return array
     */
    public function native_database_types()
    {

    }

    /**
     * schema
     *
     * @return void
     */
    public function schema($output_file)
    {

    }

    /**
     * execute
     *
     * @param string $query Query SQL
     *
     * @return void
     */
    public function execute($query)
    {

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

    /**
     * database exists
     *
     * @param string $db The database name
     *
     * @return boolean
     */
    public function database_exists($db)
    {

    }

    /**
     * create table
     *
     * @param string $table_name The table name
     * @param array $options Options for definition table
     *
     * @return boolean
     */
    public function create_table($table_name, $options = array())
    {
        return new Ruckusing_Adapter_Sqlite3_TableDefinition($this, $table_name, $options);
    }

    /**
     * drop database
     *
     * @param string $db The database name
     *
     * @return boolean
     */
    public function drop_database($db)
    {

    }

    /**
     * table exists ?
     *
     * @param string $tbl Table name
     *
     * @return boolean
     */
    public function table_exists($tbl)
    {

    }

    /**
     * drop table
     *
     * @param string $tbl The table name
     *
     * @return boolean
     */
    public function drop_table($tbl)
    {

    }

    /**
     * rename table
     *
     * @param string $name The old name of table
     * @param string $new_name The new name
     *
     * @return boolean
     */
    public function rename_table($name, $new_name)
    {

    }

    /**
     * rename column
     *
     * @param string $table_name The table name where is the column
     * @param string $column_name The old column name
     * @param string $new_column_name The new column name
     *
     * @return boolean
     */
    public function rename_column($table_name, $column_name, $new_column_name)
    {

    }

    /**
     * add column
     *
     * @param string $table_name The table name
     * @param string $column_name The column name
     * @param string $type The type generic of the column
     * @param array $options The options definition of the column
     *
     * @return boolean
     */
    public function add_column($table_name, $column_name, $type, $options = array())
    {

    }

    /**
     * remove column
     *
     * @param string $table_name The table name
     * @param string $column_name The column name
     *
     * @return boolean
     */
    public function remove_column($table_name, $column_name)
    {

    }

    /**
     * change column
     *
     * @param string $table_name The table name
     * @param string $column_name The column name
     * @param string $type The type generic of the column
     * @param array $options The options definition of the column
     *
     * @return void
     */
    public function change_column($table_name, $column_name, $type, $options = array())
    {

    }

    /**
     * remove index
     *
     * @param string $table_name The table name
     * @param string $column_name The column name
     *
     * @return boolean
     */
    public function remove_index($table_name, $column_name)
    {

    }

    /**
     * add index
     *
     * @param string $table_name The table name
     * @param string $column_name The column name
     * @param array $options The options definition of the index
     *
     * @return boolean
     */
    public function add_index($table_name, $column_name, $options = array())
    {

    }
}