<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	function br2nl( $str ){
		return preg_replace('/\<br(\s*)?\/?\>/i', '\n', $str);
	}

	//어플용 문자열(태그가 빠지고, <br>, <p>이 \n 으로 대체 됨
	function strtoapp( $str ){
		$str = nl2br( $str );
		$str = preg_replace('/\<br(\s*)?\/?\>/i', '{BR}', $str);
		$str = preg_replace('/\<\/p(\s*)?\>/i', '{BR}', $str);
		$str = strip_tags( $str ); // br 태그 빼고 태그 제거
		$str = str_replace('{BR}', '\n', $str);


		return br2nl( $str );
	}
?>