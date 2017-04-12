<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['dsn']      The full DSN string describe a connection to the database.
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database driver. e.g.: mysqli.
|			Currently supported:
|				 cubrid, ibase, mssql, mysql, mysqli, oci8,
|				 odbc, pdo, postgre, sqlite, sqlite3, sqlsrv
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Query Builder class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['encrypt']  Whether or not to use an encrypted connection.
|
|			'mysql' (deprecated), 'sqlsrv' and 'pdo/sqlsrv' drivers accept TRUE/FALSE
|			'mysqli' and 'pdo/mysql' drivers accept an array with the following options:
|
|				'ssl_key'    - Path to the private key file
|				'ssl_cert'   - Path to the public key certificate file
|				'ssl_ca'     - Path to the certificate authority file
|				'ssl_capath' - Path to a directory containing trusted CA certificats in PEM format
|				'ssl_cipher' - List of *allowed* ciphers to be used for the encryption, separated by colons (':')
|				'ssl_verify' - TRUE/FALSE; Whether verify the server certificate or not ('mysqli' only)
|
|	['compress'] Whether or not to use client compression (MySQL only)
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|	['ssl_options']	Used to set various SSL options that can be used when making SSL connections.
|	['failover'] array - A array with 0 or more data for connections if the main should fail.
|	['save_queries'] TRUE/FALSE - Whether to "save" all executed queries.
| 				NOTE: Disabling this will also effectively disable both
| 				$this->db->last_query() and profiling of DB queries.
| 				When you run a query, with this setting set to TRUE (default),
| 				CodeIgniter will store the SQL statement for debugging purposes.
| 				However, this may cause high memory usage, especially if you run
| 				a lot of SQL queries ... disable this to avoid that problem.
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $query_builder variables lets you determine whether or not to load
| the query builder class.
*/
$active_group = 'default';
$query_builder = TRUE;


$active_group = 'yps';
$active_record = TRUE;


//공통DB
$db['ps']['hostname'] = 'testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
$db['ps']['username'] = 'admin';
$db['ps']['password'] = 'yapen1010';
$db['ps']['database'] = 'test';
$db['ps']['dbdriver'] = 'mysqli';
$db['ps']['dbprefix'] = '';
$db['ps']['pconnect'] = FALSE;
$db['ps']['db_debug'] = TRUE;
$db['ps']['cache_on'] = FALSE;
$db['ps']['cachedir'] = APPPATH.'cache/yps';
$db['ps']['char_set'] = 'utf8';
$db['ps']['dbcollat'] = 'utf8_general_ci';
$db['ps']['swap_pre'] = '';
$db['ps']['autoinit'] = TRUE;
$db['ps']['stricton'] = FALSE;

//야놀자(102번, SMS)
/*20151102
$db['sms']['hostname'] = '14.49.38.228';
$db['sms']['port']     = "3310";
$db['sms']['username'] = 'smsUser';
$db['sms']['password'] = 'sms16441440';
$db['sms']['database'] = 'sms80';
$db['sms']['dbdriver'] = 'mysqli';
$db['sms']['dbprefix'] = '';
$db['sms']['pconnect'] = FALSE;
$db['sms']['db_debug'] = TRUE;
$db['sms']['cache_on'] = FALSE;
$db['sms']['cachedir'] = APPPATH.'cache/sms';
$db['sms']['char_set'] = 'utf8';
$db['sms']['dbcollat'] = 'utf8_general_ci';
$db['sms']['swap_pre'] = '';
$db['sms']['autoinit'] = TRUE;
$db['sms']['stricton'] = FALSE;
 */

$db['sms']['hostname'] = 'testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
$db['sms']['username'] = 'admin';
$db['sms']['password'] = 'yapen1010';
$db['sms']['database'] = 'emma';
$db['sms']['dbdriver'] = 'mysqli';
$db['sms']['dbprefix'] = '';
$db['sms']['pconnect'] = FALSE;
$db['sms']['db_debug'] = TRUE;
$db['sms']['cache_on'] = FALSE;
$db['sms']['cachedir'] = APPPATH.'cache/bbs/';
$db['sms']['char_set'] = 'utf8';
$db['sms']['dbcollat'] = 'utf8_general_ci';
$db['sms']['swap_pre'] = '';
$db['sms']['autoinit'] = TRUE;
$db['sms']['stricton'] = FALSE;

//HTML DB
$db['html']['hostname'] = 'testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
$db['html']['username'] = 'admin';
$db['html']['password'] = 'yapen1010';
$db['html']['database'] = 'htmlDB';
$db['html']['dbdriver'] = 'mysqli';
$db['html']['dbprefix'] = '';
$db['html']['pconnect'] = FALSE;
$db['html']['db_debug'] = TRUE;
$db['html']['cache_on'] = FALSE;
$db['html']['cachedir'] = APPPATH.'cache/common';
$db['html']['char_set'] = 'utf8';
$db['html']['dbcollat'] = 'utf8_general_ci';
$db['html']['swap_pre'] = '';
$db['html']['autoinit'] = TRUE;
$db['html']['stricton'] = FALSE;

//호텔365 통합DB
$db['info']['hostname'] = 'testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
$db['info']['username'] = 'admin';
$db['info']['password'] = 'yapen1010';
$db['info']['database'] = 'test';
$db['info']['dbdriver'] = 'mysqli';
$db['info']['dbprefix'] = '';
$db['info']['pconnect'] = FALSE;
$db['info']['db_debug'] = TRUE;
$db['info']['cache_on'] = FALSE;
$db['info']['cachedir'] = APPPATH.'cache/hts';
$db['info']['char_set'] = 'utf8';
$db['info']['dbcollat'] = 'utf8_general_ci';
$db['info']['swap_pre'] = '';
$db['info']['autoinit'] = TRUE;
$db['info']['stricton'] = FALSE;

$db['em']['hostname'] = 'testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
$db['em']['username'] = 'admin';
$db['em']['password'] = 'yapen1010';
$db['em']['database'] = 'imds';
$db['em']['dbdriver'] = 'mysqli';
$db['em']['dbprefix'] = '';
$db['em']['pconnect'] = FALSE;
$db['em']['db_debug'] = TRUE;
$db['em']['cache_on'] = FALSE;
$db['em']['cachedir'] = APPPATH.'cache/yps';
$db['em']['char_set'] = 'utf8';
$db['em']['dbcollat'] = 'utf8_general_ci';
$db['em']['swap_pre'] = '';
$db['em']['autoinit'] = TRUE;
$db['em']['stricton'] = FALSE;


//야놀자 펜션
$db['yps']['hostname'] = 'testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
$db['yps']['username'] = 'admin';
$db['yps']['password'] = 'yapen1010';
$db['yps']['database'] = 'test';
$db['yps']['dbdriver'] = 'mysqli';
$db['yps']['dbprefix'] = '';
$db['yps']['pconnect'] = FALSE;
$db['yps']['db_debug'] = TRUE;
$db['yps']['cache_on'] = FALSE;
$db['yps']['cachedir'] = APPPATH.'cache/yps';
$db['yps']['char_set'] = 'utf8';
$db['yps']['dbcollat'] = 'utf8_general_ci';
$db['yps']['swap_pre'] = '';
$db['yps']['autoinit'] = TRUE;
$db['yps']['stricton'] = FALSE;

$db['YP']['hostname'] = 'testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
$db['YP']['username'] = 'admin';
$db['YP']['password'] = 'yapen1010';
$db['YP']['database'] = 'test';
$db['YP']['dbdriver'] = 'mysqli';
$db['YP']['dbprefix'] = '';
$db['YP']['pconnect'] = FALSE;
$db['YP']['db_debug'] = TRUE;
$db['YP']['cache_on'] = FALSE;
$db['YP']['cachedir'] = APPPATH.'cache/yps';
$db['YP']['char_set'] = 'utf8';
$db['YP']['dbcollat'] = 'utf8_general_ci';
$db['YP']['swap_pre'] = '';
$db['YP']['autoinit'] = TRUE;
$db['YP']['stricton'] = FALSE;

