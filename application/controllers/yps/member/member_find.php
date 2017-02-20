<?php
class Member_find extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->config('yps/_constants');
        $this->load->library('pension_lib');
    }

    public function mbId() {
        checkMethod('get');

        $data['keyword'] = $this->input->get('keyword');

        $this->load->model('_yps/member/member_model');
        $result = $this->member_model->findMbId( $data );
        

        $ret['status'] = '0';
        $ret['failed_message'] = '정보가 일치하지 않습니다.';
        if($result['mbEmail'] == ""){
            $ret['status'] = '0';
            if($result['type'] == "mobile"){
                $ret['failed_message'] = '고객님의 연락처 정보가 존재하지 않습니다.';
            }else{
                $ret['failed_message'] = '고객님의 이메일 정보가 존재하지 않습니다.';
            }            
        }else{
            if ( isset($result['match']) && $result['match'] == 'Y' ){
                $ret['status'] = '1';
                $ret['failed_message'] = '';
            }
        }
        
        
        $ret = array_merge( $ret, $result );

        echo json_encode( $ret );

    }
    
    
    /**
     * 비밀번호 분실하여 email로 비밀번호 변경하는 경우
     * 발송하는 email
     * 
     */
    public function reset_password_auth()
    {
        checkMethod('get');
        
        $mbIdx = $this->input->get('mbIdx');
        
        
        $this->load->model(YANOLJA_PENSION_MODEL_PATH.'member_model');
        $new_pass_key = $this->member_model->set_password_key( $mbIdx );
        $mbInfo = $this->member_model->getMemberInfo($mbIdx);
        
        $ret['status'] = '0';
        $ret['failed_message'] = '정보가 일치하지 않습니다.';
        
        if ( $new_pass_key ){
            $ret['status'] = '1';
            $ret['failed_message'] = '';
            $ret['new_password_key'] = $new_pass_key;
        }
        echo json_encode( $ret );
        
        if ( $new_pass_key ){
            /* 메일 발송 START */
        
            $replaceData = array(
                '회원키' => $mbIdx,
                '비밀번호변경주소' => 'http://web.yapen.co.kr/yps/member/find_password/reset_password?id='.$mbIdx.'&key='.$new_pass_key,
                '비밀번호변경신고주소' => 'http://web.yapen.co.kr/yps/member/find_password/reset_password_return',
                '이메일' => str_replace("YP.","",$mbInfo['mbID'])
				
            );

            $this->load->library(YANOLJA_PENSION_LIB_PATH.'template_email');
            $this->template_email
                ->set_to( $mbIdx )
                ->set_data( $replaceData )
                ->template_send('reset_password_auth_email');
            /* 메일 발송 END */
        }
    }
    
    
    /**
     * 비밀번호 분실하여 email로 비밀번호 변경하는 경우
     * 발송하는 email
     * 
     */
    public function reset_password_auth_email()
    {
        checkMethod('get');
	log_message('error','Email Sender =>');        
        $mbIdx = $this->input->get('mbIdx');
        
        
        $this->load->model(YANOLJA_PENSION_MODEL_PATH.'member_model');
        $new_pass_key = $this->member_model->set_password_key( $mbIdx );
        $mbInfo = $this->member_model->getMemberInfo($mbIdx);
        
        $ret['status'] = '0';
        $ret['failed_message'] = '정보가 일치하지 않습니다.';
        
        if ( $new_pass_key )
        {
            /* 메일 발송 START */
            $replaceData = array(
                '회원키' => $mbIdx,
                '비밀번호변경주소' => 'http://web.yapen.co.kr/yps/member/find_password/reset_password?id='.$mbIdx.'&key='.$new_pass_key,
                '비밀번호변경신고주소' => 'http://web.yapen.co.kr/yps/member/find_password/reset_password_return',
                '이메일' => str_replace("YP.","",$mbInfo['mbID'])
            );
            
            $this->load->library(YANOLJA_PENSION_LIB_PATH.'template_email');
            $this->template_email
                ->set_to( $mbIdx )
                ->set_data( $replaceData )
                ->template_send('reset_password_auth_email');
            /* 메일 발송 END */
            
            $ret['status'] = '1';
            $ret['failed_message'] = '';
            $ret['new_password_key'] = $new_pass_key;
        }
        
	log_message('error','Email Sender =>'.print_r($ret,true));        
        echo json_encode( $ret );
    }
    
    
    // 문자로 인증받고 패스워드 변경하기
    public function reset_password() {
        //$this->output->enable_profiler();
        
        $this->load->library('form_validation', array(
            array(
                'field'=>'mbIdx', 
                'label'=>'회원 고유코드', 
                'rules'=>'trim|required|xss_clean'
            ),
            array(
                'field'=>'new_password_key', 
                'label'=>'비밀번호 인증키', 
                'rules'=>'trim|required|xss_clean'
            ),
            array(
                'field'=>'new_password', 
                'label'=>'새 비밀번호', 
                'rules'=>'trim|required|xss_clean|matches[confirm_new_password]'
            ),
            array(
                'field'=>'confirm_new_password', 
                'label'=>'새 비밀번호 확인', 
                'rules'=>'trim|required|xss_clean'
            )
        ));
        
        $this->load->model('_yps/member/member_model');
        
        if ( $this->form_validation->run() == FALSE ) 
        {
            
        }
        else
        {
            $mbIdx = $this->input->post('mbIdx');
            $new_password_key = rawurldecode(trim($this->input->post('new_password_key')));
            $new_password = rawurldecode(trim($this->input->post('new_password')));
            $confirm_new_password = rawurldecode(trim($this->input->post('confirm_new_password')));
            
            $result = $this->member_model->reset_password( $mbIdx, $new_password, $new_password_key );
            
            if ( $result == TRUE )
            {
                $ret['status'] = '1';
                $ret['failed_message'] = '비밀번호가 변경되었습니다.';
                echo json_encode( $ret );
                exit;
            }
        }
        
        $ret['status'] = '0';
        $ret['failed_message'] = '정보가 올바르지 않습니다.';
        echo json_encode( $ret );
            
    }


}
?>
