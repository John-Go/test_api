<?php

class Travel_main extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->model('_yps/travel/travel_model');
	}

	function index() {
		checkMethod('get');	// ���� �޼��带 ����

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";

		// ******************************* ���� �̺�Ʈ *******************************************
		$eventResult = $this->travel_model->mainEventBanner();
		$eventRow = $eventResult->row();
		if( isset( $eventRow->amebIdx ) ){
			$ret['info']['main_event']['idx'] = $eventRow->amebIdx;
			$ret['info']['main_event']['url'] = $eventRow->amebContent;
			$ret['info']['main_event']['filename'] = 'http://img.yapen.co.kr/pension/mobile/'.$eventRow->amebFilename;
		}

		// ******************************* ���� �̺�Ʈ *******************************************

		// ******************************* ��� ��� *******************************************
		$topResult = $this->travel_model->mainTopBanner();
		$topNum = 0;		
		foreach ($topResult as $row) {
			$ret['info']['main_top']['lists'][$topNum]['idx'] = $row['amtbIdx'];
			$ret['info']['main_top']['lists'][$topNum]['code'] = $row['mpIdx'];
			$ret['info']['main_top']['lists'][$topNum]['title'] = $row['amtbTitle'];
			$ret['info']['main_top']['lists'][$topNum]['filename'] = 'http://img.yapen.co.kr/pension/mobile/'.$row['amtbFilename'];
			$topNum++;
		}
		// ******************************* ��� ��� *******************************************

		// ******************************* �α�������� ��� *******************************************
		$locResult = $this->travel_model->mainLocBanner();
		$locNum = 0;		
		foreach ($locResult as $row) {
			$ret['info']['main_loc']['lists'][$locNum]['idx'] = $row['amlbIdx'];
			$ret['info']['main_loc']['lists'][$locNum]['name'] = $row['amlbName'];
			$ret['info']['main_loc']['lists'][$locNum]['code'] = $row['amlbCode'];
			$ret['info']['main_loc']['lists'][$locNum]['content'] = $row['amlbContent'];
			$ret['info']['main_loc']['lists'][$locNum]['color'] = $row['amlbColor'];
			$ret['info']['main_loc']['lists'][$locNum]['fcolor'] = $row['amlbColorF'];
			$locNum++;
		}
		// ******************************* �α�������� ��� *******************************************



		echo json_encode( $ret );


//		$this->output->enable_profiler();

	}
}
?>