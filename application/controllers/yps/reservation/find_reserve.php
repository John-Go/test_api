<?php

class Find_reserve extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/reservation/reservation_model');
	}

	function index() {
	    
		checkMethod('get');	// 접근 메서드를 제한
		
		$reVal = array();
		$reVal['status'] = '1';
		$reVal['failed_message'] = "";
		
        $userRev = $this->input->get('userRev');
        $userName = $this->input->get('userName');
        
        $sData = $this->reservation_model->getRevInfo($userRev, $userName);
        
        if(isset($sData['rIdx'])){
            $reVal['mpIdx'] = $sData['mpIdx'];
            $reVal['pensionName'] = $sData['rPension'];
            $reVal['revIdx'] = $sData['rIdx'];
        }else{
            $reVal['mpIdx'] = "";
            $reVal['pensionName'] = "";
            $reVal['revIdx'] = "";
            $reVal['failed_message'] = "예약 내역이 없습니다";
        }
		
		echo json_encode( $reVal );
	}
}
?>