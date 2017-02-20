<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);
if($_SERVER['REMOTE_ADDR'] != "211.119.165.88"){
    ini_set('display_errors', 0);
}
/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/* 사용자 정의 */
define('TIME_YMD', date('Y-m-d', time()));
define('TIME_HIS', date('H:i:s', time()));
define('TIME_YMDHIS', date('Y-m-d H:i:s', time()));

define('RT_PATH', ''); // ex) /test
define('SKIN_PATH', $_SERVER['DOCUMENT_ROOT'].RT_PATH.'/skin/');

//2% 할인 관련 이벤트 시작일, 종료일, 테스트 유무, 테스트 IP
define('YAPEN_SALE_EVENT_START', '2016-12-01');
define('YAPEN_SALE_EVENT_END', '2017-05-31');
define('YAPEN_SALE_EVENT_TEST', 'Y');
define('YAPEN_SALE_EVENT_TEST_IP', '211.119.165.87');

//야놀자 연동 URL
define('YANOLJA_CONNECT_URL', 'http://cms.api.yanolja.com:8099');

//호텔나우 연동 URL
define('HOTELNOW_CONNECT_URL', 'http://exapi.hotelnow.co.kr');

//공공데이터 API KEY
define('OPENAPI_DATA_KEY', '33vZbthX%2BUjG%2FclESJRzadu32X5K3rtrUvNADbMQ1CU2iNPqYPNJTteCDFUCBZ8tdRgbppEhkYfqpWiut44quA%3D%3D');

/* End of file constants.php */
/* Location: ./application/config/constants.php */