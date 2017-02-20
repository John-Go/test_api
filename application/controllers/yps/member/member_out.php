<?php

class Member_out extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		$this->load->config('yps/_constants');
		$this->load->library('pension_lib');
		$this->load->model('_yps/member/member_model');
	}

	function index() {

/*
<form name="form" method="post" action="/yps/member/member_out?key=a3b1a551515fb16937f16fcb47e99b4f">
아이디 <input type="text" name="idx"><br />
mbOutReason <input type="text" name="mbOutReason" value="MO01"><br />
mbPassword <input type="text" name="mbPassword" ><br />

<input type="submit" value="로그인">
</form>
*/
		$idx = $this->input->post('idx');
		$root = rawurldecode(trim($this->input->post('root')));
		$mbOutReason = rawurldecode($this->input->post('mbOutReason'));
		$mbPassword = rawurldecode($this->input->post('mbPassword'));
		$mbOutReasonDescription = rawurldecode($this->input->post('mbOutReasonDescription'));
		
		if( $root == 'YP' && !$mbPassword )
			$this->error->getError('0006');	// Key가 없을경우

		if( !$idx || !$mbOutReason )
			$this->error->getError('0006');	// Key가 없을경우


		//$row = $this->member_model->passwordConfirm(array(
		//		'mbIdx'=>$idx,
		//		'mbPassword'=>$mbPassword
		//	));
		$row = '';
		

		$ret = array();

		if( $idx || $mbOutReason ){
			$ret['status'] = '1';
			$ret['failed_message'] = '';
	
			$this->member_model->mypageUpdate($idx, $mbPassword, array(
									'mbOutReason'=>$mbOutReason,
									'mbOutReasonDescription'=>$mbOutReasonDescription,
									'mbOut'=>'Y',
									'mbOutDate'=>date('Y-m-d H:i:s')
									),
								$root
								);

			/* 메일 발송 START */
			$replaceData = array();
			
			$this->load->library(YANOLJA_PENSION_LIB_PATH.'template_email');
			$this->template_email
				->set_to( $idx )
				->set_data( $replaceData )
				->template_send('member_out_success');
			/* 메일 발송 END */
			
		}else{
			$ret['status'] = '0';
			$ret['failed_message'] = '입력정보가 올바르지 않습니다.';
		}


		echo json_encode( $ret );

//		$this->output->enable_profiler();
	}
}
?>