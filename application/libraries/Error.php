<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Error {
	private $CI;
	private $err;


	public function __construct($param=array()) {
		$this->CI =& get_instance();

		//error 코드들을 가져옴
		$this->CI->config->load('_code', TRUE);
		$this->err = $this->CI->config->item('errorcode', '_code');
	}


	/* 에류코드로 정보를 반환 */
	public function getError( $code = NULL ){
		$ret = array();

		//오류코드가 없으면 알수없는 오류 표시
		if( !$code || !isset($this->err[$code]) ) $code = '9999';

		//반환값
		$ret['status']			= '0';
		$ret['failed_code']		= $code;
		$ret['failed_message']	= $this->err[$code];

		echo json_encode( $ret );
		exit;
	}
}

?>