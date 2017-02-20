<?php

class Mypage_update extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/member/member_model');
	}

	function index() {
		$idx		 = $this->input->get_post('idx');
		$root		 = rawurldecode(trim($this->input->get_post('root')));
		$mbNick		 = rawurldecode(trim($this->input->get_post('mbNick')));
		$mbEmail	 = rawurldecode(trim($this->input->get_post('mbEmail')));		
		$mbBirthday  = rawurldecode(trim($this->input->get_post('mbBirthday')));
		$mbPassword  = rawurldecode(trim($this->input->get_post('mbPassword')));
        $mbEmailAgree= rawurldecode(trim($this->input->get_post('mbEmailAgree')));

		/*
			네이티브 쪽 페이스북으로 로그인시 root 값이 안넘어오는 버그가 있어서 임시 처리.
			버전업데이트 되면서 해결되었으나, 구버전 사용자에 때문에 처리해놓음
		*/
		if( !$root ) $root = 'FB';

		// 필수 설정값 체크
		if(!$root || !$idx) $this->error->getError('0006');	

		// 닉네임 오류(2~10자)
		if($root != "KA"){
		  if( !$mbNick || ( mb_strlen($mbNick) < 2 || mb_strlen($mbNick) > 10 ) ) $this->error->getError('0301');
        }

		//특수문자 걸러냄
		if( preg_match('/[^\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}0-9a-zA-Z]/u', $mbNick) )
			$this->error->getError('0301');

		// 이메일 오류
		//if( !$mbEmail || !preg_match('/([0-9a-zA-Z_\.-]+)@([0-9a-zA-Z_-]+)(\.[0-9a-zA-Z_-]+)/', $mbEmail) ) $this->error->getError('0302');

		// 생년월일 오류
		if($root != "KA"){
		    if( !$mbBirthday || ( strlen($mbBirthday) != 6 ) ) $this->error->getError('0303');
		}
		
	
		//기존 회원 정보 가져옴
		$this->db->where('mbIdx', $idx);
		$member = $this->db->get('member')->row();

		//닉네임 중복체크
		/*
         * 2014-06-11 닉네임 중복체크 해제 [김영웅]
		if( $member->mbNick != $mbNick ){
			if( $this->member_model->checkDuplicated('mbNick',$mbNick) ) $this->error->getError('0304');
		}
         */

		//이메일 중복체크
	    if($member->mbEmail != "" && $member->mbEmail != $mbEmail ){
	        if($mbEmail != ""){
                if( $this->member_model->checkDuplicated('mbEmail',$mbEmail) ) $this->error->getError('0306');
            }
        }
				
		//db 쿼리
		//$this->output->enable_profiler(true);
		$this->member_model->mypageUpdate($idx, $mbPassword, array(
												'mbNick'	=>$mbNick,
												'mbEmail'	=>$mbEmail,
												'mbBirthday'=>$mbBirthday,
												'mbEmailAgree'	=>$mbEmailAgree
											),
											$root
										);

		$ret					= array();
		$ret['status']			= '1';
		$ret['failed_message']	= '';


		echo json_encode( $ret );

//		$this->output->enable_profiler();

	}
}
?>