<?php

class Login extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/member/member_model');
	}

	function index() {
		$root = rawurldecode(trim($this->input->get_post('root')));
		$mbID = rawurldecode($this->input->get_post('mbID'));
		$mbPassword = rawurldecode($this->input->get_post('mbPassword'));
		$mbRegId = $this->input->get_post('mbRegId');
		$mbEmail = rawurldecode(trim($this->input->get_post('mbEmail')));
        $mbDevID = $this->input->get_post('mbDevID');
		
		if( $root == 'YP' && !$mbPassword )
			$this->error->getError('0006');	// Key가 없을경우


		if(!$root || !$mbID )
			$this->error->getError('0006');	// Key가 없을경우
			
		if( $root == 'YA'){
		    $ret['status'] = '2';
            $ret['failed_message'] = "야놀자 아이디로 로그인 서비스가 종료되었습니다.\n야놀자펜션 회원가입 혹은 페이스북 로그인을 통해 이용 부탁드립니다.";		    
		}else{    		
    		$row = $this->member_model->yanoljaPensionLogin(array(
    								"root"=>$root,
    								"mbID"=>$mbID,
    								"mbPassword"=>$mbPassword,
    								"mbRegId"=>$mbRegId,
    								"mbEmail"=>$mbEmail,
    								"mbDevID"=>$mbDevID
    								));
    
    		$ret = array();
    
    		if($row){
    			$ret['status'] = '1';
    			$ret['failed_message'] = "";
    
    			$ret['info']['mem_idx'] = $row['mbIdx'];	// 회원 idx
    			$ret['info']['mem_id'] = $row['mbID'];	// 아이디
    			$ret['info']['mem_nick'] = $row['mbNick'];	// 닉네임
    			$ret['info']['mem_email'] = $row['mbEmail'];	// 이메일
    			$ret['info']['mem_emailAgree'] = $row['mbEmailAgree'];	// 이메일
    			$ret['info']['mem_mobile'] = $row['mbMobile'];	// 폰번호
    			$ret['info']['mem_birthday'] = str_replace('-', '', $row['mbBirthday']);	// 생년월일
    			$ret['info']['mem_regId'] = $row['mbRegId'];	// Device ID
    			if($row['mbPoint']){
    			    $ret['info']['mem_point'] = $row['mbPoint']; // 포인트
    			}else{
    			    $ret['info']['mem_point'] = "0"; // 포인트
    			}
    			
    			$ret['info']['mem_grade'] = $row['mbGrade'];    
    		}else{
    			/*if(!strcmp($root,"YA")){ // 야놀자로그인
    				$ret['status'] = '2';
    				$ret['failed_message'] = "아이디 또는 비밀번호가 맞지 않습니다";		
    			}else*/ 
    			if(!strcmp($root,"FB")){ // 페이스북 로그인
                    $ret['status'] = '3';
                    $ret['failed_message'] = "회원정보를 찾을 수 없습니다";     
                }else if(!strcmp($root,"KA")){ // 카카오톡 로그인
                    $ret['status'] = '3';
                    $ret['failed_message'] = "회원정보를 찾을 수 없습니다";     
                }else{
    				$ret['status'] = '0';
    				$ret['failed_message'] = "아이디 또는 비밀번호가 맞지 않습니다";			
    			}
    		}
		}

		echo json_encode( $ret );

//		$this->output->enable_profiler();

	}
}
?>