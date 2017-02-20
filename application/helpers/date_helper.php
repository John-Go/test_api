<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function dateDiff($val, $val2){ 
	$tmp1['arr1'] = explode(" ",$val); 
	$tmp1['arr2'] = explode(" ",$val2); 

	$tmp1['date1'] = explode("-",$tmp1['arr1'][0]);
	$tmp1['date2'] = explode("-",$tmp1['arr2'][0]);
	
	$tmp1['time1'] = explode(":",$tmp1['arr1'][1]);
	$tmp1['time2'] = explode(":",$tmp1['arr2'][1]);

	$tmp2	= mktime($tmp1['time1'][0],$tmp1['time1'][1],$tmp1['time1'][2],$tmp1['date1'][1],$tmp1['date1'][2],$tmp1['date1'][0]); 
	$tmp3	= mktime($tmp1['time2'][0],$tmp1['time2'][1],$tmp1['time2'][2],$tmp1['date2'][1],$tmp1['date2'][2],$tmp1['date2'][0]); 

	$t = $tmp3 - $tmp2; // 지정한 날과의 시간 차이 
	$d = floor($t/86400); $t-= $d*86400; // 일 
	$h = floor($t/3600); $t-= $h*3600; // 시간 
	$i = floor($t/60); $t-= $i*60; // 분 

	if($d>0){
		$result=$tmp1['arr1'][0];
	}else if($h>0){
		$result=$h."시간 전";
	}else if($i>0){
		$result=$i."분전";
	}else{
		$result="방금전";
	}

	return $result;
}

// 모텔앱용
function dateForString($date_ymdhis=TIME_YMD){ 
	$string = '';

	//당일작성일 경우
	if( date('Y-m-d',strtotime($date_ymdhis)) == TIME_YMD ){
		$date_hi = date('H:i', strtotime($date_ymdhis));
		//오전
		if( 'AM' == date('A', strtotime($date_ymdhis)) )
			$string = '오전 '.$date_hi;
		//오후
		else $string = '오후 '.$date_hi;

	//당일작성이 아닐경우
	}else $string = date('Y.m.d', strtotime($date_ymdhis));

	return $string;
}