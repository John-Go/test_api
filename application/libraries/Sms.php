<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//
// version 1.0.0
// 


class Sms {
	private $CI;

	function __construct(){
		//모델을 로드함
		$this->CI =& get_instance();
		$this->CI->load->model('Sms_model','sms_model');
	}

	//문자발송
	function send( $msg, $receiver, $sender='16444816' ){
		if( !$msg ) return false;

		$set = array(
			'Sender'	=> $sender,
			'receiver'	=> str_replace('-', '', $receiver),
			'msg'		=> $msg,
			'ReserveDT' => TIME_YMDHIS,
			'CreateDT'	=> TIME_YMDHIS
		);

		return $this->CI->sms_model->sendSMS( $set );
	}
}
