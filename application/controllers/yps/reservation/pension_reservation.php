<?php

class Pension_reservation extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/reservation/reservation_model');
		$this->config->load('yps/_code');
	}
	

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$ptIdx = $this->input->get('ptIdx');
		
		// 각 펜션당 예약 제한 검색
		$LimitPensionDate = $this->reservation_model->getPensionLimitDate($ptIdx);
		$limitDate = "";
		if(count($LimitPensionDate) > 0){
			if($LimitPensionDate['rodLoofDays'] > 0){
				$last_day = date("t", mktime(0,0,0,date('m'),date('d')+$LimitPensionDate['rodLoofDays'],date('Y')));
                $limitDate = date("Y-m-d", mktime(0,0,0,date('m'),date('d')+$LimitPensionDate['rodLoofDays']+$last_day,date('Y')));
			}else{
			    if($LimitPensionDate['rodSetdate'] != ""){
			        $limitDate = $LimitPensionDate['rodSetdate'];
			    }else{
			        $last_day = date("t", mktime(0,0,0,date('m')+3,date('d'),date('Y')));
                    $limitDate = date("Y-m-d", mktime(0,0,0,date('m')+3,$last_day,date('Y')));
			    }
			}			
		}else{
			$last_day = date("t", mktime(0,0,0,date('m')+3,date('d'),date('Y')));
            $limitDate = date("Y-m-d", mktime(0,0,0,date('m')+3,$last_day,date('Y')));
		}        
        
		$ptLimitDate = $limitDate;
		
		$ret = array();
		$ret['ptLimitDate'] = $ptLimitDate;
		$ret['status'] = '1';
		$ret['failed_message'] = '';
		
		echo json_encode( $ret );
	}
}
?>