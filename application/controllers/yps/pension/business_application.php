<?php

class Business_application extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('_yps/pension/pension_model');
        $this->load->model('_yps/sms_model');
	}

	function index() {
		$baPensionName = $this->input->post('baPensionName');
		$baPicCheck = $this->input->post('baPicCheck');
		$baMobile = $this->input->post('baMobile');
        $baSponsor = $this->input->post('baSponsor');
        if(!isset($baSponsor)){
            $baSponsor = "N";
        }

		if(!$baPensionName || !$baPicCheck || !$baMobile){
			$this->error->getError('0006');	// Key가 없을경우
		}


		$result = $this->pension_model->businessApplicationInsert(array(
												'baPensionName'=>urldecode($baPensionName),
												'baPicCheck'=>$baPicCheck,
												'baMobile'=>$baMobile,
												'baSponsor' => $baSponsor
					));


		$ret = array();

		if($result){
			$ret['status'] = "1";
			$ret['failed_message'] = '';
            
            $msg = array();
            $msg['mobile'] = $baMobile;
            $msg['content'] = "[야놀자펜션] 무료등록 신청이 완료되었습니다. 담당자가 확인하여 연락드리겠습니다.";
            $this->sms_model->send($msg);
		}else{
			$ret['status'] = "0";
			$ret['failed_message'] = '등록실패';
		}


		$ret['status'] = "1";
		$ret['failed_message'] = '';

		echo json_encode( $ret );
	}
}
?>