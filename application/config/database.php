<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
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
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'yps';
$active_record = TRUE;


//공통DB
$db['ps']['hostname'] = 'yapen-testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
$db['ps']['username'] = 'admin';
$db['ps']['password'] = 'yapen1010';
$db['ps']['database'] = 'pensionDB';
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

$db['sms']['hostname'] = 'yapen-testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
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
$db['html']['hostname'] = 'yapen-testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
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
$db['info']['hostname'] = 'yapen-testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
$db['info']['username'] = 'admin';
$db['info']['password'] = 'yapen1010';
$db['info']['database'] = 'pensionDB';
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

$db['em']['hostname'] = 'yapen-testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
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
$db['yps']['hostname'] = 'yapen-testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
$db['yps']['username'] = 'admin';
$db['yps']['password'] = 'yapen1010';
$db['yps']['database'] = 'pensionDB';
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

$db['YP']['hostname'] = 'yapen-testdb.ckmics7jujs6.ap-northeast-2.rds.amazonaws.com';
$db['YP']['username'] = 'admin';
$db['YP']['password'] = 'yapen1010';
$db['YP']['database'] = 'pensionDB';
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


/* End of file database.php */
/* Location: ./application/config/database.php */