<?php

class Lolling_banner extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";
        $ret['imgWidth'] = "750";
        $ret['imgHeight'] = "217";

		$topResult = $this->pension_model->mainLolBanner();
		$topNum = 0;		
		foreach ($topResult as $row) {
			$ret['info']['main_lol']['lists'][$topNum]['idx'] = $row['amlbIdx'];
			$ret['info']['main_lol']['lists'][$topNum]['title'] = $row['amlbTitle'];
			$ret['info']['main_lol']['lists'][$topNum]['filename'] = 'http://img.yapen.co.kr/pension/mobile/'.$row['amlbFilename'];
            $ret['info']['main_lol']['lists'][$topNum]['flag'] = $row['amlbBannerFlag'];
            $ret['info']['main_lol']['lists'][$topNum]['eventUrl'] = $row['amlbReturnVal'];
			$topNum++;
		}
		echo json_encode( $ret );
	}
}
?>