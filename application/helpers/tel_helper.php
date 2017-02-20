<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function exportPhone( $PhoneArr , $phoneOpen){ 
	$mpsTel		= '';
	$mpsTelArr = array();
	$mpsTelArr = @explode(',', $PhoneArr);
	foreach( $mpsTelArr as $val ){
		if( trim($mpsTel) ) break;

		//mpsTelOpen 이 0이명 050이외의 번호는 제외시킴
		if( $phoneOpen ){
			$mpsTel = $val;
		}else{
			if( preg_match('/050-/', $val) ) $mpsTel = $val;
		}
	}

	return $mpsTel;
}
?>