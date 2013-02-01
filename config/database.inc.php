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
                        'password'  => ''
                ),

                'pg_test'  => array(
                        'type'  => 'pgsql',
                        'host'  => 'localhost',
                        'port'  => 5432,
                        'database'  => 'ruckusing_migrations_test',
                        'user'  => 'postgres',
                        'password'  => ''
                ),

                'mysql_test'  => array(
                        'type'  => 'mysql',
                        'host'  => 'localhost',
                        'port'  => 3306,
                        'database'  => 'ruckusing_migrations_test',
                        'user'  => 'root',
                        'password'  => ''
                )

        ),

        'migrations_dir' => array('default' => RUCKUSING_WORKING_BASE . '/migrations'),

        'db_dir' => RUCKUSING_WORKING_BASE . '/db',

        'log_dir' => RUCKUSING_WORKING_BASE . '/logs',

        'ruckusing_base' => dirname(__FILE__) . '/..'

);
