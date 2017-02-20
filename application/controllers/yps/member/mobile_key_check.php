<?php

class Mobile_key_check extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->config('yps/_constants');
		$this->load->library('pension_lib');
		//$this->load->model('_yps/member/member_model');
		$this->load->model('_yps/sms_model');
	}

	function index() {
		log_message('error','Mobile_key_check Post Dats => '.print_r($this->input->post(),true) );
		$idx = $this->input->post('idx');
		$mbMobile = $this->input->post('mbMobile');
		$certifyKey	= $this->input->post('certifyKey');

		if(!$idx || !$mbMobile || !$certifyKey)
			$this->error->getError('0006');	// Key가 없을경우		

		//인증번호가 유효한지 확인
		if( $certifyKey ) {
			//$session_id = $this->session->userdata('session_id');
			//$data = $this->sms_model->getCertifyCount($session_id);
			$data = $this->sms_model->getCertifyCount($idx);

			//인증번호가 없거나 유효시간이 지났을경우
			if( $data['count']['able'] < 1 ) {				
				$ret['status'] = '0';
				$ret['failed_message'] = '제한시간 초과';
				echo json_encode( $ret );
				exit;
			}
			//인증번호가 맞는지 확인
			if( $data['array']['certifyKey'] != $certifyKey ) {
					
				$ret['status'] = '0';
				$ret['failed_message'] = '인증번호 불일치';
				echo json_encode( $ret );
				exit;
			}
		}		
		$ret['status'] = '1';
		$ret['failed_message'] = '';
		
		$this->load->model(YANOLJA_PENSION_MODEL_PATH.'member_model');
		$new_pass_key = $this->member_model->set_password_key( $idx );
		$ret['new_pass_key'] = $new_pass_key;
		log_message('error','Mobile_key_check => '.print_r($ret,true) );	
		echo json_encode( $ret );
	}
}
?>
