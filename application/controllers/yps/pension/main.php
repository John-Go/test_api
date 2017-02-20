<?php

class Main extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";

		// ******************************* 메인 이벤트 *******************************************
		$eventResult = $this->pension_model->mainEventBanner();
		$eventRow = $eventResult->row();
		if( isset($eventRow->amebIdx) ){
			$ret['info']['main_event']['idx'] = $eventRow->amebIdx;
			$ret['info']['main_event']['url'] = $eventRow->amebContent;
			$ret['info']['main_event']['filename'] = 'http://img.yapen.co.kr/pension/mobile/'.$eventRow->amebFilename;
		}

		// ******************************* 메인 이벤트 *******************************************

		// ******************************* 기획전 배너 *******************************************
		$topResult = $this->pension_model->mainTopBanner();
		$topNum = 0;		
		foreach ($topResult as $row) {
			$ret['info']['main_top']['lists'][$topNum]['idx'] = $row['amtbIdx'];
			$ret['info']['main_top']['lists'][$topNum]['title'] = $row['amtbTitle'];
            if(preg_match( '/(iPod|iPhone|iPad)/', $_SERVER[ 'HTTP_USER_AGENT' ]) && $row['amtbIdx'] == "229"){
                $ret['info']['main_top']['lists'][$topNum]['filename'] = 'http://image2.yanolja.com/pension/event/rainbowiOS.png';
            }else{
                $ret['info']['main_top']['lists'][$topNum]['filename'] = 'http://img.yapen.co.kr/pension/mobile/'.$row['amtbFilename'];
            }
            
            if(preg_match( '/(iPod|iPhone|iPad)/', $_SERVER[ 'HTTP_USER_AGENT' ]) && $row['amtbIdx'] == "231"){
                $ret['info']['main_top']['lists'][$topNum]['filename'] = 'http://image2.yanolja.com/pension/event/yapenTen/iosBanner.png';
            }else{
                $ret['info']['main_top']['lists'][$topNum]['filename'] = 'http://img.yapen.co.kr/pension/mobile/'.$row['amtbFilename'];
            }
			
            $ret['info']['main_top']['lists'][$topNum]['flag'] = $row['amtbBannerFlag'];
            $ret['info']['main_top']['lists'][$topNum]['eventUrl'] = $row['amtbReturnVal'];
            $ret['info']['main_top']['lists'][$topNum]['imgWidth'] = $row['amtbWidth'];
            $ret['info']['main_top']['lists'][$topNum]['imgHeight'] = $row['amtbHeight'];
			$topNum++;
		}
		// ******************************* 기획전 배너 *******************************************

		// ******************************* 인기지역 추천 펜션 *******************************************
		$locResult = $this->pension_model->mainLocBanner();
		$locNum = 0;
		$ret['info']['main_loc']['title'] = $this->pension_model->mainLocBannerTitle();
		foreach ($locResult as $row) {
			$ret['info']['main_loc']['lists'][$locNum]['idx'] = $row['amlbIdx'];
			$ret['info']['main_loc']['lists'][$locNum]['name'] = $row['amlbName'];
			$ret['info']['main_loc']['lists'][$locNum]['content'] = $row['amlbContent'];
			$ret['info']['main_loc']['lists'][$locNum]['color'] = $row['amlbColor'];
			$ret['info']['main_loc']['lists'][$locNum]['fcolor'] = $row['amlbColorF'];
			$locNum++;
		}
		// ******************************* 인기지역 추천 펜션 *******************************************



		echo json_encode( $ret );


//		$this->output->enable_profiler();

	}
}
?>