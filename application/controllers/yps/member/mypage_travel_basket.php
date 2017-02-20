<?php

class Mypage_travel_basket extends CI_Controller {
	function __construct() {
		parent::__construct();

//		$CI =& get_instance();
//		$CI->dbHTS = $this->load->database('hts', TRUE);

		$this->load->config('yps/_constants');
		$this->load->library('pension_lib');
		$this->load->model('_yps/member/member_model');
		$this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
		define('IMG_PATH', 'http://img.yapen.co.kr');
	}

	function index() {
		checkMethod('get');

		$mbIdx = $this->input->get('mbIdx');
		$page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 10000, NULL);



		if(!$mbIdx)
			$this->error->getError('0006');

		$offset = ($page - 1) * $limit;
		$result = $this->member_model->travelBasketLists($mbIdx,$limit,$offset);

		$no = 0;

		$ret = array();
		$ret['status'] = 1;
		$ret['failed_message'] = "";
		$ret['tnt_cnt'] = $result['count']."";

		foreach ($result['query'] as $row) {
			$imageNum = 0;
			for($i=1;$i<=100;$i++){
				if(strcmp($row['dniiFileName'.$i],'NoFile') && $row['dniiFileName'.$i])
					$imageNum++;
			}

			$basket_cnt	= $this->basket_model->getTravelBasketCountByMpIdx( $row['dniIdx'] );
			$ret['lists'][$no]['idx'] = $row['dniIdx'];
			$ret['lists'][$no]['name'] = $row['dniTitle'];
			$ret['lists'][$no]['address'] = $row['dniSi'].' '.$row['dniGugun'];//.' '.$row['dniAdress'];
			$ret['lists'][$no]['theme'] = $this->pension_lib->travelThemeInfo($row['dniIdx']); // �׸�
			$ret['lists'][$no]['basket_cnt']	= $basket_cnt;
			$ret['lists'][$no]['filename'] = $row['dniFileName'];
			$ret['lists'][$no]['imageNum'] = $imageNum.'';

			$ret['lists'][$no]['readnum'] = $row['dniReadnum'];

			$no++;
		}
		
		echo json_encode( $ret );
		//$this->output->enable_profiler();

	}

	function delBasket() {
		$dnIdx	= $this->input->post('dnIdx');
		$mbIdx	= $this->input->post('mbIdx');
		if( !$dnIdx || !$mbIdx ) $this->error->getError('0006');	// Key�� �������

		$result	= $this->member_model->travelBasketDelete(array(
			'mpIdx'	=> $dnIdx,
			'mbIdx'	=> $mbIdx
					)
		);

		
		$ret['status'] = '1';
		$ret['failed_message'] = '';

		echo json_encode( $ret );

	}
}
?>