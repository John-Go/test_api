<?php

class Mypage_Info extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/member/member_model');
		$this->config->load('yps/_code');
	}

	function index() {
		checkMethod('get');

		$mbIdx = $this->input->get('mbIdx');
		if( !$mbIdx ) $this->error->getError('0006');

		$mbInfo = $this->member_model->getUserInfo($mbIdx);
        
        $reVal                    = array();
        $reVal['status']          = '1';
        $reVal['failed_message']  = '';
        if(isset($mbInfo['mbIdx'])){
            $reVal['mbEmail'] = $mbInfo['mbEmail'];
            $reVal['mbNick'] = $mbInfo['mbNick'];
            if($mbInfo['mbBirthday'] != ""){
                $reVal['mbBirthday'] = str_replace("-", "", substr($mbInfo['mbBirthday'],2));
            }else{
                $reVal['mbBirthday'] = "";
            }            
            $reVal['mbMobile'] = $mbInfo['mbMobile'];
            if($mbInfo['mbEmailAgree']){
                $reVal['mbEmailAgree'] = $mbInfo['mbEmailAgree'];
            }else{
                $reVal['mbEmailAgree'] = "N";
            }
            
            $reVal['mbGrade'] = $mbInfo['mbGrade'];
        }else{
            $reVal['mbEmail'] = "";
            $reVal['mbNick'] = "";
            $reVal['mbBirthday'] = "";
            $reVal['mbMobile'] = "";
            $reVal['mbEmailAgree'] = 'N';
            $reVal['mbGrade'] = "0";
        }        
         
		echo json_encode( $reVal );		
	}
}
?>