<?php

class Device_id_ins extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->model('_yps/member/member_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한
        
        /* parameter setting Start */
		$devID = $this->input->get('devID');
        $devOS = $this->input->get('devOS');
        /* parameter setting End */
        
        /* return parameter setting Start */
		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";
        /* return parameter setting End */
        if(!$devID || $devID == "" || strlen($devID) < 5){
            $ret['status'] = "2";
            $ret['failed_message'] = "Device ID Error";
        }else{
            $return = $this->member_model->InsDeviceID($devID, $devOS);
            if($return != "O"){
                $ret['status'] = "2";
                $ret['failed_message'] = "DataBase Insert Fail";            
            }
        }		
		echo json_encode( $ret );
	}
}
?>