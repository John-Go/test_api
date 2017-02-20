<?php

class Travel_basket extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/travel/travel_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$sector = $this->input->get('sector');
		$mpIdx = $this->input->get('mpIdx'); // 업체키
		$mbIdx = $this->input->get('mbIdx'); // 회원키


		if(!$sector || !$mpIdx || !$mbIdx)
			$this->error->getError('0006');	// Key가 없을경우

		$result = $this->travel_model->travelBasket($sector, $mpIdx, $mbIdx);

		$ret = array();

		if($result == "1"){
			$ret['status'] = "1";
			$ret['failed_message'] = "";		
		}else{
			$ret['status'] = "0";
			$ret['failed_message'] = $result;		
		}



		echo json_encode( $ret );

//		$this->output->enable_profiler();

	}
}
?>