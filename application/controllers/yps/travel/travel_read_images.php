<?php

class Travel_read_images extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/travel/travel_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');	// Key가 없을경우

		$result = $this->travel_model->travelImageLists($idx, 0, 100); // 사진정보

		$arrayImages = array();
		$checkNum = 0;
		for($i=1;$i<=100;$i++){
			if(strcmp($result['dniiFileName'.$i],'NoFile') && $result['dniiFileName'.$i]){
				$arrayImages[$checkNum] = $result['dniiFileName'.$i];
				$checkNum++;
			}
		}

		if(!$checkNum)
			$this->error->getError('0005');	// 정보가 없을경우

		$ret = array();
		$ret['status'] = '1';
		$ret['failed_message'] = "";
		$ret['tnt_cnt'] = $checkNum.'';
		
		for($i=0; $i<sizeof($arrayImages); $i++)
			$ret['lists'][$i]['image'] = $arrayImages[$i];

		echo json_encode( $ret );

//		$this->output->enable_profiler();

	}
}

?>