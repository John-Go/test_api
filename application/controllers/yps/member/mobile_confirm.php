<?php

class Mobile_confirm extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		//$this->load->model('_yps/member/member_model');
		$this->load->model('_yps/sms_model');
		$this->load->model('smsnew_model');
	}

	function index() {

/*
<form name="form" method="post" action="/yps/member/mobile_confirm?key=a3b1a551515fb16937f16fcb47e99b4f">
아이디 <input type="text" name="idx"><br />
mbMobile <input type="text" name="mbMobile"><br />

<input type="submit" value="로그인">
</form>
*/
		log_message('error','Mobile_confirm Post data => '.print_r($this->input->post(),true) );
		$idx = $this->input->post('idx');
		$mbMobile = $this->input->post('mbMobile');

		if(!$idx || !$mbMobile)
			$this->error->getError('0006');	// Key가 없을경우

		//인증번호 전송수 체크
		//$session_id = $this->session->userdata('session_id');
		//$data = $this->sms_model->getCertifyCount($session_id);
		$data = $this->sms_model->getCertifyCount($idx);

		//인증번호 발송이 일일 최대 5회를 넘었을경우
		if( $data['count']['total'] >= 5 )	{
			$ret['status'] = '0';
			$ret['failed_message'] = '일일 전송수(5회)를 초과';
			echo json_encode( $ret );
			exit;
		}
		
		//회원 인증키 생성
		$certifyKey	= random_string('numeric', 6);
		$result = $this->sms_model->setCertify(array(
			'idx' => $idx,
			'certifyKey' => $certifyKey
		));

		//SMS발송

		$curCfg	= $this->smsnew_model->getPensionMsgTemplateInfo('YP_MCN_1');
		
		$chArray	= array();
		$chArray['certifyKey']		= '[' . $certifyKey . ']';

		$chKeyArray		= array_keys($chArray);
		$chValArray		= array_values($chArray);

		array_walk($chKeyArray, array($this, 'changeKeyFormat'));

		$msg	= str_replace($chKeyArray, $chValArray, $curCfg['pmtUser']);

		$result	= $this->smsnew_model->sendSMS($msg, preg_replace('/[^0-9]/', '', $mbMobile), 'K', $curCfg['pmtCode']);


//		$result = $this->sms_model->send(array(
//			'mobile'	=> $mbMobile,		// 수신자
//			//'mobile'	=> '01062045432',		// 수신자
//			'content'	=> '야놀자펜션 인증번호 ['.$certifyKey.']를 정확히 입력해 주세요.',	// 내용
//			'type'		=> 'S',					// M (mms) , S (sms) 
//			'sender' 	=> '16444816'	// 발송인
//		));

		/*$row = $this->member_model->mobileUpdate($idx, array(
								'mbMobile'=>$mbMobile,
								'mbMobileCertify'=>'Y'
								));*/
		if( $result ) {
			$ret['status'] = '1';
			$ret['failed_message'] = '';
		} else {
			$ret['status'] = '0';
			$ret['failed_message'] = '인증번호 발송 실패';
		}

		//print_r( $ret );
		log_message('error','Mobile_confirm Post data => '.print_r($ret,true) );
		echo json_encode( $ret );

//		$this->output->enable_profiler();
	}

	function changeKeyFormat(&$val, $key)
	{
		$val	= '#{' . $val . '}';
	}
}
?>
