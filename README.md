# Introduction

Ruckusing is a framework written in PHP5 for generating and managing a set of "database migrations". Database migrations are declarative files which represent the state of a DB (its tables, columns, indexes, etc) at a particular state of time. By using database migrations, multiple developers can work on the same application and be guaranteed that the application is in a consistent state across all remote developer machines.

The idea of the framework was borrowed from the migration system built into Ruby on Rails. Any one who is familiar with Migrations in RoR will be immediately at home.

## Features


* Portability: the migration files, which describe the tables, columns, indexes, etc to be created are themselves written in pure PHP5 which is then translated to the appropriate SQL at run-time. This allows one to transparently support any RDBMS with a single set of migration files (assuming there is an adapter for it, see below).

* Extensibility: the framework is written with extensibility in mind and it is very modular. Support for new RDMBSs should be as easy as creating the appropriate adapter and implementing a single interface.

* "rake" like support for basic tasks. The framework has a concept of "tasks" (in fact the primary focus of the framework, migrations, is just a plain task) which are just basic PHP5 classes which implement an interface. Tasks can be freely written and as long as they adhere to a specific naming convention and implement a specific interface, the framework will automatically register them and allow them to be executed.

* The ability to go UP or DOWN to a specific migration state.

* Code generator for generating skeleton migration files.

* Out-of-the-box support for basic tasks like initializing the DB schema info table (db:setup), asking for the current version (db:version) and dumping the current schema (db:schema).

# Limitations

* PHP5 is a hard requirement. The framework employes extensive use of object-oriented features of PHP5. There are no plans to make the framework backwards compatible.

* As of August 2007, only the MySQL RDBMS is supported.