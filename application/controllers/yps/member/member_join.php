<?php

class Member_join extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		$this->load->config('yps/_constants');
		$this->load->library('pension_lib');
		$this->load->model('_yps/member/member_model');
	}
	
	function test() {
		
		/* 메일 발송 START */
		$replaceData = array(
			'아이디' => substr('adfdaf', 0, 2).'****',
			'이메일' => 'cloudtea_@nate.com'
		);
		
		$this->load->library(YANOLJA_PENSION_LIB_PATH.'template_email');
		$this->template_email
			->set_to( 21 )
			->set_data( $replaceData )
			->template_send('member_join_success');
			
		
		/* 메일 발송 END */
		
		
	}

	function index() {
		$mbID = urldecode(trim($this->input->post('mbID')));
		$mbPassword = urldecode(trim($this->input->post('mbPassword')));
		$mbEmail = urldecode(trim($this->input->post('mbEmail')));
		$mbEmailAgree = urldecode(trim($this->input->post('mbEmailAgree')));
		
		if(!$mbEmailAgree) $mbEmailAgree = "NULL";

		if(!$mbID || !$mbPassword || !$mbEmail)
			$this->error->getError('0006');	// Key가 없을경우


		$rowID = $this->member_model->memberJoinCheckID(array(
                                'mbID'=>'YP.'.$mbID,
                                'mbEmail'=>$mbEmail
                                ));
        
        $rowEmail = $this->member_model->memberJoinCheckEmail(array(
                                'mbID'=>'YP.'.$mbID,
                                'mbEmail'=>$mbEmail
                                ));
        $ret = array();
        if(strlen($mbID) > 2){    		
            if($rowID){
    			$ret['status'] = '0';
    			$ret['failed_message'] = '동일한 아이디가 존재합니다';
    		}else if($rowEmail){
    		    $ret['status'] = '0';
                $ret['failed_message'] = '동일한 이메일이 존재합니다';		
    		}else{
    			$result = $this->member_model->memberJoin(array(
    											'mbID'=>'YP.'.$mbID,
    											'mbPassword'=>$mbPassword,
    											'mbEmail'=>$mbEmail,
    											'mbEmailAgree'=>$mbEmailAgree
    											));
    
    			if($result){
    				$ret['status'] = '1';
    				$ret['failed_message'] = '';
    				
    				/* 메일 발송 START */
    				$replaceData = array(
    					'아이디' => substr($mbID, 0, 2).'****',
    					'이메일' => $mbEmail
    				);
    				
    				$this->load->library(YANOLJA_PENSION_LIB_PATH.'template_email');
    				$this->template_email
    					->set_to( $mbEmail )
    					->set_data( $replaceData )
    					->template_send('member_join_success');
    				/* 메일 발송 END */
    				
    			}else{
    				$ret['status'] = '0';
    				$ret['failed_message'] = '가입처리가 되지 않았습니다';
    			}
    		}
    	}else{
    	    $ret['status'] = '0';
            $ret['failed_message'] = '아이디는 최소 2자 이상입니다';
    	}

		echo json_encode( $ret );

//		$this->output->enable_profiler();
	}
}
?>