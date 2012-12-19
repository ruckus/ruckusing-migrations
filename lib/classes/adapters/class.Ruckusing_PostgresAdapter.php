<?php

require_once RUCKUSING_BASE . '/lib/classes/class.Ruckusing_BaseAdapter.php';
require_once RUCKUSING_BASE . '/lib/classes/class.Ruckusing_iAdapter.php';
require_once RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_NamingUtil.php';
require_once RUCKUSING_BASE . '/lib/classes/class.Ruckusing_TableDefinition.php';
require_once RUCKUSING_BASE . '/lib/classes/adapters/class.Ruckusing_PostgresTableDefinition.php';
require_once RUCKUSING_BASE . '/lib/classes/class.Ruckusing_ColumnDefinition.php';

define('PG_MAX_IDENTIFIER_LENGTH', 64); // max length of an identifier like a column or index name


class Ruckusing_PostgresAdapter extends Ruckusing_BaseAdapter implements Ruckusing_iAdapter {

	private $name = "Postgres";
	private $tables = array();
	private $tables_loaded = false;
	private $version = '1.0';
	private $in_trx = false;

	function __construct($dsn, $logger) {
		parent::__construct($dsn);
		$this->connect($dsn);
		$this->set_logger($logger);
	}

	public function get_database_name() {
	  return($this->db_info['database']);
  }

	public function supports_migrations() {
	 return true;
  }

  public function native_database_types() {
    $types = array(
      'primary_key'   => array('name' => 'serial'),
      'string'        => array('name' => 'varchar', 'limit' => 255),
      'text'          => array('name' => 'text'),
      'mediumtext'    => array('name' => 'text'),
      'integer'       => array('name' => 'integer'),
      'smallinteger'  => array('name' => 'smallint'),
      'biginteger'    => array('name' => 'bigint'),
      'float'         => array('name' => 'float'),
      'decimal'       => array('name' => 'decimal', 'scale' => 10, 'precision' => 0),
      'datetime'      => array('name' => 'timestamp'),
      'timestamp'     => array('name' => 'timestamp'),
      'time'          => array('name' => 'time'),
      'date'          => array('name' => 'date'),
      'binary'        => array('name' => 'bytea'),
      'boolean'       => array('name' => 'boolean'),
      'tsvector'      => array('name' => 'tsvector')
    );
    return $types;
  }

  //-----------------------------------
  // PUBLIC METHODS
  //-----------------------------------

  /* Create the schema table, if necessary */
  public function create_schema_version_table() {
    if(!$this->has_table(RUCKUSING_TS_SCHEMA_TBL_NAME)) {
      $t = $this->create_table(RUCKUSING_TS_SCHEMA_TBL_NAME, array('id' => false));
      $t->column('version', 'string');
      $t->finish();
      $this->add_index(RUCKUSING_TS_SCHEMA_TBL_NAME, 'version', array('unique' => true));
    }
  }

  //transaction methods
  public function start_transaction() {
    try {
      if($this->inTransaction() === false) {
        $this->beginTransaction();
      }
    }catch(Exception $e) {
      trigger_error($e->getMessage());
    }
  }

  public function commit_transaction() {
    try {
      if($this->inTransaction()) {
        $this->commit();
      }
    }catch(Exception $e) {
      trigger_error($e->getMessage());
    }
  }

  public function rollback_transaction() {
    try {
      if($this->inTransaction()) {
        $this->rollback();
      }
  }catch(Exception $e) {
    trigger_error($e->getMessage());
    }
  }

  public function column_definition($column_name, $type, $options = null) {
    $col = new Ruckusing_ColumnDefinition($this, $column_name, $type, $options);
    return $col->__toString();
  }

  // Returns a table's primary key and belonging sequence.
  public function pk_and_sequence_for($table) {
    $sql = <<<SQL
      SELECT attr.attname, seq.relname
      FROM pg_class      seq,
           pg_attribute  attr,
           pg_depend     dep,
           pg_namespace  name,
           pg_constraint cons
      WHERE seq.oid           = dep.objid
        AND seq.relkind       = 'S'
        AND attr.attrelid     = dep.refobjid
        AND attr.attnum       = dep.refobjsubid
        AND attr.attrelid     = cons.conrelid
        AND attr.attnum       = cons.conkey[1]
        AND cons.contype      = 'p'
        AND dep.refobjid      = '%s'::regclass
SQL;
    $sql = sprintf($sql, $table);
    $result = $this->select_one($sql);
    if($result) {
      return(array($result['attname'], $result['relname']));
    } else {
      return array();
    }
  }

  //-------- DATABASE LEVEL OPERATIONS

  /* Create database cannot run in a transaction block so if we're in a transaction
  than commit it, do our thing and then re-invoke the transaction
  */
  public function create_database($db, $options = array()) {

    $was_in_transaction = false;
    if($this->inTransaction()) {
      $this->commit_transaction();
      $was_in_transaction = true;
    }

    if(!array_key_exists('encoding', $options)) {
      $options['encoding'] = 'utf8';
    }
    $ddl = sprintf("CREATE DATABASE %s", $this->identifier($db));
    if(array_key_exists('owner', $options)) {
      $ddl .= " OWNER = \"{$options['owner']}\"";
    }
    if(array_key_exists('template', $options)) {
      $ddl .= " TEMPLATE = \"{$options['template']}\"";
    }
    if(array_key_exists('encoding', $options)) {
      $ddl .= " ENCODING = '{$options['encoding']}'";
    }
    if(array_key_exists('tablespace', $options)) {
      $ddl .= " TABLESPACE = \"{$options['tablespace']}\"";
    }
    if(array_key_exists('connection_limit', $options)) {
      $connlimit = intval($options['connection_limit']);
      $ddl .= " CONNECTION LIMIT = {$connlimit}";
    }
    $result = $this->query($ddl);

    if($was_in_transaction) {
      $this->start_transaction();
      $was_in_transaction = false;
    }
    return($result === true);
  }

  public function database_exists($db) {
    $sql = sprintf("SELECT datname FROM pg_database WHERE datname = '%s'", $db);
    $result = $this->select_one($sql);
    return(count($result) == 1 && $result['datname'] == $db);
  }

  public function drop_database($db) {
    if(!$this->database_exists($db)) {
      return false;
    }
    $ddl = sprintf("DROP DATABASE IF EXISTS %s", $this->quote_table_name($db));
    $result = $this->query($ddl);
    return($result === true);
  }

  /*
  Dump the complete schema of the DB. This is really just all of the
  CREATE TABLE statements for all of the tables in the DB.

  NOTE: this does NOT include any INSERT statements or the actual data
  (that is, this method is NOT a replacement for mysqldump)
  */
  public function schema($output_file) {
    $command = sprintf("pg_dump -U %s -Fp -s -f %s %s",
      $this->db_info['user'],
      $output_file,
      $this->db_info['database']
    );
    return system($command);
  }

  public function table_exists($tbl, $reload_tables = false) {
    $this->load_tables($reload_tables);
    return array_key_exists($tbl, $this->tables);
  }

  public function execute($query) {
    return $this->query($query);
  }

  public function query($query) {
    $this->logger->log($query);
    $query_type = $this->determine_query_type($query);
    $data = array();
    if($query_type == SQL_SELECT || $query_type == SQL_SHOW) {
      $res = pg_query($this->conn, $query);
      if($this->isError($res)) {
        trigger_error(sprintf("Error executing 'query' with:\n%s\n\nReason: %s\n\n", $query, pg_last_error($this->conn)));
      }
      while($row = pg_fetch_assoc($res)) {
        $data[] = $row;
      }
      return $data;
    } else {
      // INSERT, DELETE, etc...
      $res = pg_query($this->conn, $query);
      if($this->isError($res)) {
        trigger_error(sprintf("Error executing 'query' with:\n%s\n\nReason: %s\n\n", $query, pg_last_error($this->conn)));
      }
      // if the query contained a 'RETURNING' class then grab its value
      $returning_regex = '/ RETURNING \"(.+)\"$/';
      $matches = array();
      if(preg_match($returning_regex, $query, $matches)) {
        if(count($matches) == 2) {
          $returning_column_value = pg_fetch_result($res, 0, $matches[1]);
          return($returning_column_value);
        }
      }
      return true;
    }
  }

  public function select_one($query) {
    $this->logger->log($query);
    $query_type = $this->determine_query_type($query);
    if($query_type == SQL_SELECT || $query_type == SQL_SHOW) {
      $res = pg_query($this->conn, $query);
      if($this->isError($res)) {
        trigger_error(sprintf("Error executing 'query' with:\n%s\n\nReason: %s\n\n", $query, pg_last_error($this->conn)));
      }
      return pg_fetch_assoc($res);
    } else {
      trigger_error("Query for select_one() is not one of SELECT or SHOW: $query");
    }
  }

  public function select_all($query) {
    return $this->query($query);
  }

  /*
  Use this method for non-SELECT queries
  Or anything where you dont necessarily expect a result string, e.g. DROPs, CREATEs, etc.
  */
  public function execute_ddl($ddl) {
    $result = $this->query($ddl);
    return true;
  }

  public function drop_table($tbl) {
    $ddl = sprintf("DROP TABLE IF EXISTS %s", $this->quote_table_name($tbl));
    $result = $this->query($ddl);
    return true;
  }

  public function create_table($table_name, $options = array()) {
    return new Ruckusing_PostgresTableDefinition($this, $table_name, $options);
  }

  public function quote_string($string) {
    return pg_escape_string($string);
  }

  public function identifier($string) {
    return '"' . $string . '"';
  }

  public function quote_table_name($string) {
    return '"' . $string . '"';
  }

  public function quote_column_name($string) {
    return '"' . $string . '"';
  }

  public function quote($value, $column = null) {
    $type = gettype($value);
    if($type == "double") {
      return("'{$value}'");
    } elseif($type == "integer") {
      return("'{$value}'");
    } else {
      // TODO: this global else is probably going to be problematic.
      // I think eventually we'll need to do more introspection and handle all possible types
      return("'{$value}'");
    }
    /*
    "boolean"
    "integer"
    "double" (for historical reasons "double" is returned in case of a float, and not simply "float")
    "string"
    "array"
    "object"
    "resource"
    "NULL"
    "unknown type"
    */
  }

  /*
    Renames a table.
    Also renames a table's primary key sequence if the sequence name matches the Ruckusing Migrations default.
  */
  public function rename_table($name, $new_name) {
    if(empty($name)) {
      throw new Ruckusing_ArgumentException("Missing original column name parameter");
    }
    if(empty($new_name)) {
      throw new Ruckusing_ArgumentException("Missing new column name parameter");
    }
    $sql = sprintf("ALTER TABLE %s RENAME TO %s", $this->identifier($name), $this->identifier($new_name));
    $this->execute_ddl($sql);
    $pk_and_sequence_for = $this->pk_and_sequence_for($new_name);
    if(!empty($pk_and_sequence_for)) {
      list($pk, $seq) = $pk_and_sequence_for;
      if($seq == "{$name}_{$pk}_seq") {
        $new_seq = "{$new_name}_{$pk}_seq";
        $this->execute_ddl("ALTER TABLE $seq RENAME TO $new_seq");
      }
    }
  }

  public function add_column($table_name, $column_name, $type, $options = array()) {
    if(empty($table_name)) {
      throw new Ruckusing_ArgumentException("Missing table name parameter");
    }
    if(empty($column_name)) {
      throw new Ruckusing_ArgumentException("Missing column name parameter");
    }
    if(empty($type)) {
      throw new Ruckusing_ArgumentException("Missing type parameter");
    }
    //default types
    if(!array_key_exists('limit', $options)) {
      $options['limit'] = null;
    }
    if(!array_key_exists('precision', $options)) {
      $options['precision'] = null;
    }
    if(!array_key_exists('scale', $options)) {
      $options['scale'] = null;
    }
    $sql = sprintf("ALTER TABLE %s ADD COLUMN %s %s",
      $this->quote_table_name($table_name),
      $this->quote_column_name($column_name),
      $this->type_to_sql($type, $options)
    );
    $sql .= $this->add_column_options($type, $options);
    return $this->execute_ddl($sql);
  }

  public function remove_column($table_name, $column_name) {
    $sql = sprintf("ALTER TABLE %s DROP COLUMN %s",
      $this->quote_table_name($table_name),
      $this->quote_column_name($column_name)
    );
    return $this->execute_ddl($sql);
  }

  public function rename_column($table_name, $column_name, $new_column_name) {
    if(empty($table_name)) {
      throw new Ruckusing_ArgumentException("Missing table name parameter");
    }
    if(empty($column_name)) {
      throw new Ruckusing_ArgumentException("Missing original column name parameter");
    }
    if(empty($new_column_name)) {
      throw new Ruckusing_ArgumentException("Missing new column name parameter");
    }
    $column_info = $this->column_info($table_name, $column_name);
    $current_type = $column_info['type'];
    $sql =  sprintf("ALTER TABLE %s RENAME COLUMN %s TO %s",
      $this->quote_table_name($table_name),
      $this->quote_column_name($column_name),
      $this->quote_column_name($new_column_name)
    );
    return $this->execute_ddl($sql);
  }

  public function change_column($table_name, $column_name, $type, $options = array()) {
    if(empty($table_name)) {
      throw new Ruckusing_ArgumentException("Missing table name parameter");
    }
    if(empty($column_name)) {
      throw new Ruckusing_ArgumentException("Missing original column name parameter");
    }
    if(empty($type)) {
      throw new Ruckusing_ArgumentException("Missing type parameter");
    }
    $column_info = $this->column_info($table_name, $column_name);
    //default types
    if(!array_key_exists('limit', $options)) {
      $options['limit'] = null;
    }
    if(!array_key_exists('precision', $options)) {
      $options['precision'] = null;
    }
    if(!array_key_exists('scale', $options)) {
      $options['scale'] = null;
    }
    $sql = sprintf("ALTER TABLE %s ALTER COLUMN %s TYPE %s",
      $this->quote_table_name($table_name),
      $this->quote_column_name($column_name),
      $this->type_to_sql($type,$options)
    );
    $sql .= $this->add_column_options($type, $options, true);

    if(array_key_exists('default', $options)) {
      $this->change_column_default($table_name, $column_name, $options['default']);
    }
    if(array_key_exists('null', $options)) {
      $default = array_key_exists('default', $options) ? $options['default'] : null;
      $this->change_column_null($table_name, $column_name, $options['null'], $default);
    }
    return $this->execute_ddl($sql);
  }

  private function change_column_default($table_name, $column_name, $default) {
    $sql = sprintf("ALTER TABLE %s ALTER COLUMN %s SET DEFAULT %s",
      $this->quote_table_name($table_name),
      $this->quote_column_name($column_name),
      $this->quote($default)
    );
    $this->execute_ddl($sql);
  }

  private function change_column_null($table_name, $column_name, $null, $default = null) {
    if(($null !== false) || ($default !== null)) {
      $sql = sprintf("UPDATE %s SET %s=%s WHERE %s IS NULL",
        $this->quote_table_name($table_name),
        $this->quote_column_name($column_name),
        $this->quote($default),
        $this->quote_column_name($column_name)
      );
      $this->query($sql);
    }
    $sql = sprintf("ALTER TABLE %s ALTER %s %s NOT NULL",
      $this->quote_table_name($table_name),
      $this->quote_column_name($column_name),
      ($null ? 'DROP' : 'SET')
    );
    $this->query($sql);
  }

	public function column_info($table, $column) {
    if(empty($table)) {
      throw new Ruckusing_ArgumentException("Missing table name parameter");
    }
    if(empty($column)) {
      throw new Ruckusing_ArgumentException("Missing original column name parameter");
    }
    try {
      $sql = <<<SQL
      SELECT a.attname, format_type(a.atttypid, a.atttypmod), d.adsrc, a.attnotnull
        FROM pg_attribute a LEFT JOIN pg_attrdef d
          ON a.attrelid = d.adrelid AND a.attnum = d.adnum
       WHERE a.attrelid = '%s'::regclass
         AND a.attname = '%s'
         AND a.attnum > 0 AND NOT a.attisdropped
       ORDER BY a.attnum
SQL;
      $sql = sprintf($sql, $this->quote_table_name($table), $column);
      $result = $this->select_one($sql);
      $data = array();
      if(is_array($result)) {
        $data['type'] = $result['format_type'];
        $data['name'] = $column;
        $data['field'] = $column;
        $data['null'] = $result['attnotnull'] == 'f';
        $data['default'] = $result['adsrc'];
      }
      return $data;
    }catch(Exception $e) {
      return null;
    }
  }

  public function add_index($table_name, $column_name, $options = array()) {
    if(empty($table_name)) {
      throw new Ruckusing_ArgumentException("Missing table name parameter");
    }
    if(empty($column_name)) {
      throw new Ruckusing_ArgumentException("Missing column name parameter");
    }
    //unique index?
    if(is_array($options) && array_key_exists('unique', $options) && $options['unique'] === true) {
      $unique = true;
    } else {
      $unique = false;
    }

    //did the user specify an index name?
    if(is_array($options) && array_key_exists('name', $options)) {
      $index_name = $options['name'];
    } else {
      $index_name = Ruckusing_NamingUtil::index_name($table_name, $column_name);
    }

    if(strlen($index_name) > PG_MAX_IDENTIFIER_LENGTH) {
      $msg = "The auto-generated index name is too long for Postgres (max is 64 chars). ";
      $msg .= "Considering using 'name' option parameter to specify a custom name for this index.";
      $msg .= " Note: you will also need to specify";
      $msg .= " this custom name in a drop_index() - if you have one.";
      throw new Ruckusing_InvalidIndexNameException($msg);
    }
    if(!is_array($column_name)) {
      $column_names = array($column_name);
    } else {
      $column_names = $column_name;
    }
    $cols = array();
    foreach($column_names as $name) {
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

  public function remove_index($table_name, $column_name, $options = array()) {
    if(empty($table_name)) {
      throw new Ruckusing_ArgumentException("Missing table name parameter");
    }
    if(empty($column_name)) {
      throw new Ruckusing_ArgumentException("Missing column name parameter");
    }
    //did the user specify an index name?
    if(is_array($options) && array_key_exists('name', $options)) {
      $index_name = $options['name'];
    } else {
      $index_name = Ruckusing_NamingUtil::index_name($table_name, $column_name);
    }
    $sql = sprintf("DROP INDEX %s", $this->quote_column_name($index_name));
    return $this->execute_ddl($sql);
  }

  public function has_index($table_name, $column_name, $options = array()) {
    if(empty($table_name)) {
      throw new Ruckusing_ArgumentException("Missing table name parameter");
    }
    if(empty($column_name)) {
      throw new Ruckusing_ArgumentException("Missing column name parameter");
    }
    //did the user specify an index name?
    if(is_array($options) && array_key_exists('name', $options)) {
      $index_name = $options['name'];
    } else {
      $index_name = Ruckusing_NamingUtil::index_name($table_name, $column_name);
    }
    $indexes = $this->indexes($table_name);
    foreach($indexes as $idx) {
      if($idx['name'] == $index_name) {
        return true;
      }
    }
    return false;
  }

  public function indexes($table_name) {
    $sql = <<<SQL
       SELECT distinct i.relname, d.indisunique, d.indkey, pg_get_indexdef(d.indexrelid), t.oid
       FROM pg_class t
       INNER JOIN pg_index d ON t.oid = d.indrelid
       INNER JOIN pg_class i ON d.indexrelid = i.oid
       WHERE i.relkind = 'i'
         AND d.indisprimary = 'f'
         AND t.relname = '%s'
         AND i.relnamespace IN (SELECT oid FROM pg_namespace WHERE nspname = ANY (current_schemas(false)) )
      ORDER BY i.relname
SQL;
    $sql = sprintf($sql, $table_name);
    $result = $this->select_all($sql);

    $indexes = array();
    foreach($result as $row) {
      $indexes[] = array(
        'name' => $row['relname'],
        'unique' => $row['indisunique'] == 't' ? true : false
      );
    }
    return $indexes;
  }

  public function primary_keys($table_name) {
    $sql = <<<SQL
      SELECT
        pg_attribute.attname,
        format_type(pg_attribute.atttypid, pg_attribute.atttypmod)
      FROM pg_index, pg_class, pg_attribute
      WHERE
        pg_class.oid = '%s'::regclass AND
        indrelid = pg_class.oid AND
        pg_attribute.attrelid = pg_class.oid AND
        pg_attribute.attnum = any(pg_index.indkey)
        AND indisprimary
SQL;
    $sql = sprintf($sql, $table_name);
    $result = $this->select_all($sql);

    $primary_keys = array();
    foreach($result as $row) {
      $primary_keys[] = array(
        'name' => $row['attname'],
        'type' => $row['format_type']
      );
    }
    return $primary_keys;
  }

  public function type_to_sql($type, $options = array()) {
    $natives = $this->native_database_types();
    if(!array_key_exists($type, $natives)) {
      $error = sprintf("Error: I dont know what column type of '%s' maps to for Postgres.", $type);
      $error .= "\nYou provided: {$type}\n";
      $error .= "Valid types are: \n";
      $types = array_keys($natives);
      foreach($types as $t) {
        if($t == 'primary_key') { continue; }
        $error .= "\t{$t}\n";
      }
      throw new Ruckusing_ArgumentException($error);
    }

    $scale = null;
    $precision = null;
    $limit = null;

    if(isset($options['precision'])) {
      $precision = $options['precision'];
    }
    if(isset($options['scale'])) {
      $scale = $options['scale'];
    }
    if(isset($options['limit'])) {
      $limit = $options['limit'];
    }

    $native_type = $natives[$type];
    if( is_array($native_type) && array_key_exists('name', $native_type)) {
      $column_type_sql = $native_type['name'];
    } else {
      return $native_type;
    }
    if($type == "decimal") {
      //ignore limit, use precison and scale
      if( $precision == null && array_key_exists('precision', $native_type)) {
        $precision = $native_type['precision'];
      }
      if( $scale == null && array_key_exists('scale', $native_type)) {
        $scale = $native_type['scale'];
      }
      if($precision != null) {
        if(is_int($scale)) {
          $column_type_sql .= sprintf("(%d, %d)", $precision, $scale);
        } else {
          $column_type_sql .= sprintf("(%d)", $precision);
        }//scale
      } else {
        if($scale) {
          throw new Ruckusing_ArgumentException("Error adding decimal column: precision cannot be empty if scale is specified");
        }
      }//pre
    }
    // integer columns dont support limit (sizing)
    if($native_type['name'] != "integer") {
      if($limit == null && array_key_exists('limit', $native_type)) {
        $limit = $native_type['limit'];
      }
      if($limit) {
        $column_type_sql .= sprintf("(%d)", $limit);
      }
    }
    return $column_type_sql;
  }//type_to_sql

  public function add_column_options($type, $options, $performing_change = false) {
    $sql = "";

    if(!is_array($options)) {
      return $sql;
    }
    if(!$performing_change) {
      if(array_key_exists('default', $options) && $options['default'] !== null) {
        if(is_int($options['default'])) {
          $default_format = '%d';
        } elseif(is_bool($options['default'])) {
          $default_format = "'%d'";
        } else {
          $default_format = "'%s'";
        }
        $default_value = sprintf($default_format, $options['default']);
        $sql .= sprintf(" DEFAULT %s", $default_value);
      }

      if(array_key_exists('null', $options) && $options['null'] === false) {
        $sql .= " NOT NULL";
      }
    }
    return $sql;
  }//add_column_options

  public function set_current_version($version) {
    $sql = sprintf("INSERT INTO %s (version) VALUES ('%s')", RUCKUSING_TS_SCHEMA_TBL_NAME, $version);
    return $this->execute_ddl($sql);
  }

  public function remove_version($version) {
    $sql = sprintf("DELETE FROM %s WHERE version = '%s'", RUCKUSING_TS_SCHEMA_TBL_NAME, $version);
    return $this->execute_ddl($sql);
  }

  public function __toString() {
    return "Ruckusing_PostgresAdapter, version " . $this->version;
  }

  //-----------------------------------
  // PRIVATE METHODS
  //-----------------------------------

  private function connect($dsn) {
    $this->db_connect($dsn);
  }

  private function db_connect($dsn) {
    if(!function_exists('pg_connect')) {
      die("\nIt appears you have not compiled PHP with Postgres support: missing function pg_connect()");
    }
    $db_info = $this->get_dsn();
    if($db_info) {
      $this->db_info = $db_info;
      $conninfo = sprintf('host=%s port=%s dbname=%s user=%s password=%s',
        $db_info['host'],
        (!empty($db_info['port']) ? $db_info['port'] : '5432'),
        $db_info['database'],
        $db_info['user'],
        $db_info['password']
      );
      $this->conn = pg_connect($conninfo);
      if($this->conn === FALSE) {
        die("\n\nCould not connect to the DB, check host / user / password\n\n");
      }
      return true;
    } else {
      die("\n\nCould not extract DB connection information from: {$dsn}\n\n");
    }
  }

  private function isError($o) {
    return $o === FALSE;
  }

  // Initialize an array of table names
  private function load_tables($reload = true) {
    if($this->tables_loaded == false || $reload) {
      $this->tables = array(); //clear existing structure
      $sql = "SELECT tablename FROM pg_tables WHERE schemaname = ANY (current_schemas(false))";

      $res = pg_query($this->conn, $sql);
      while($row = pg_fetch_row($res)) {
        $table = $row[0];
        $this->tables[$table] = true;
      }
    }
  }

  private function determine_query_type($query) {
    $query = strtolower(trim($query));
    if(preg_match('/^select/', $query)) {
      return SQL_SELECT;
    }
    if(preg_match('/^update/', $query)) {
      return SQL_UPDATE;
    }
    if(preg_match('/^delete/', $query)) {
      return SQL_DELETE;
    }
    if(preg_match('/^insert/', $query)) {
      return SQL_INSERT;
    }
    if(preg_match('/^alter/', $query)) {
      return SQL_ALTER;
    }
    if(preg_match('/^drop/', $query)) {
      return SQL_DROP;
    }
    if(preg_match('/^create/', $query)) {
      return SQL_CREATE;
    }
    if(preg_match('/^show/', $query)) {
      return SQL_SHOW;
    }
    if(preg_match('/^rename/', $query)) {
      return SQL_RENAME;
    }
    if(preg_match('/^set/', $query)) {
      return SQL_SET;
    }
    return SQL_UNKNOWN_QUERY_TYPE;
  }

  private function is_select($query_type) {
    return($query_type == SQL_SELECT);
  }

  /*
  Detect whether or not the string represents a function call and if so
  do not wrap it in single-quotes, otherwise do wrap in single quotes.
  */
  private function is_sql_method_call($str) {
    $str = trim($str);
    return(substr($str, -2, 2) == "()");
  }

  private function inTransaction() {
    return $this->in_trx;
  }

  private function beginTransaction() {
    pg_query($this->conn, "BEGIN");
    $this->in_trx = true;
  }

  private function commit() {
    if($this->in_trx === true) {
     pg_query($this->conn, "COMMIT");
     $this->in_trx = false;
    }
  }

  private function rollback() {
    if($this->in_trx === true) {
     pg_query($this->conn, "ROLLBACK");
     $this->in_trx = false;
    }
  }

}

?>
