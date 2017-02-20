<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//ajax인지 체크함
function checkAjax(){
	$CI =& get_instance();
	
	if( !$CI->input->is_ajax_request() ) $CI->error->getError('0001');
}

//method 형태를 체크함
function checkMethod($method='post'){
	$CI =& get_instance();

	$method = @strtolower($method);
	$requestMethod = @strtolower($CI->input->server('REQUEST_METHOD'));

	if( $requestMethod != $method ) $CI->error->getError('0002');
}

// 최근 팁에서 글자수를 2줄로 제한
function cut_summary($str, $line)
{
	$strArray = explode('<br />', nl2br($str) );
	$string = '';
	$no = 1;
	foreach( $strArray as $key => $val ){
		if( $no > $line ){
			$string .= ' ...';
			break;
		}

		$string .= $val;

		$no++;
	}

	return $string;
}

//팁의 동행을 글자로 바꿈
function getTipWith($code,$gubun){
	switch ($code) {
		default:
		case '0': $ret['long'] = '연인과 함께'; $ret['short'] = '연인';  break;
		case '1': $ret['long'] = '친구와 함께'; $ret['short'] = '친구';  break;
		case '2': $ret['long'] = '가족과 함께'; $ret['short'] = '가족';  break;
		case '3': $ret['long'] = '나홀로'; 		$ret['short'] = '나홀로';break;
	}
	return ($gubun=='short')?$ret['short']:$ret['long'];
}

// print_r 개조
function print_re($data) {
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}
