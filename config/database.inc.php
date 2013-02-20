<?php

//----------------------------
// DATABASE CONFIGURATION
//----------------------------

/*

Valid types (adapters) are Postgres & MySQL:

'type' must be one of: 'pgsql' or 'mysql'

*/

return array(
        'db' => array(
                'development' => array(
                        'type'      => 'mysql',
                        'host'      => 'localhost',
                        'port'      => 3306,
                        'database'  => 'ruckusing_migrations',
                        'user'      => 'root',
                        'password'  => '',
                        //'directory' => 'custom_name',
                        //'socket' => '/var/run/mysqld/mysqld.sock'
                ),

                'pg_test'  => array(
                        'type'  => 'pgsql',
                        'host'  => 'localhost',
                        'port'  => 5432,
                        'database'  => 'ruckusing_migrations_test',
                        'user'  => 'postgres',
                        'password'  => '',
                        //'directory' => 'custom_name',

                ),

                'mysql_test'  => array(
                        'type'  => 'mysql',
                        'host'  => 'localhost',
                        'port'  => 3306,
                        'database'  => 'ruckusing_migrations_test',
                        'user'  => 'root',
                        'password'  => '',
                        //'directory' => 'custom_name',
                        //'socket' => '/var/run/mysqld/mysqld.sock'
                )

        ),

        'migrations_dir' => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'migrations',

        'db_dir' => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'db',

        'log_dir' => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'logs',

        'ruckusing_base' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'

);
