<?php
class Mypage extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('_yps/member/member_model');
		//$this->output->enable_profiler();
	}

	function index() {

		$ret['status'] = '1';
		$ret['failed_message'] = '';

		$mbIdx = $this->input->get('mbIdx');
		if( !$mbIdx ) $this->error->getError('0006');	// Key가 없을경우

		$mbIdx_arr = array($mbIdx);
		$result	= $this->member_model->getMypageInfo( $mbIdx );
		$UserPoint = $this->member_model->getPointSch($mbIdx_arr);
		if(count($UserPoint) > 0){
			$UserNowPoint = $UserPoint['mpNowPoint'];
		}else{
			$UserNowPoint = 0;
		}		
		
		$tip		= $result['ptCnt']+$result['ttCnt'];
		$review	= $result['tbCnt']+$result['pbCnt'];

		$ret['info']['reservation'] = $result['rvCnt'];	// 예약내역
		$ret['info']['point'] = $UserNowPoint;	// 마일리지
		$ret['info']['coupon'] = '0';	// 할인쿠폰
		$ret['info']['tip'] = (string)$tip;	// 내가쓴팁
		$ret['info']['review'] = (string)$review;	// 가고싶어요
		$ret['info']['inquirie'] = $result['arCnt']; // 가고싶어요

		echo json_encode( $ret );
	}
}
?>