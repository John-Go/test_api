<?php

class Password_confirm extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/member/member_model');
	}

	function index() {

/*
<form name="form" method="post" action="/yps/member/password_confirm?key=a3b1a551515fb16937f16fcb47e99b4f">
아이디 <input type="text" name="idx"><br />
비밀번호 <input type="text" name="mbPassword"><br />

<input type="submit" value="로그인">
</form>
*/
		$idx = $this->input->post('idx');
		$mbPassword = $this->input->post('mbPassword');

		if(!$idx || !$mbPassword)
			$this->error->getError('0006');	// Key가 없을경우


		$row = $this->member_model->passwordConfirm(array(
								'mbIdx'=>$idx,
								'mbPassword'=>$mbPassword
								));

		$ret = array();

		if($row){
			$ret['status'] = '1';
			$ret['failed_message'] = '';
		}else{
			$ret['status'] = '0';
			$ret['failed_message'] = '입력정보가 올바르지 않습니다.';
		}

		echo json_encode( $ret );

//		$this->output->enable_profiler();

	}
}
?>