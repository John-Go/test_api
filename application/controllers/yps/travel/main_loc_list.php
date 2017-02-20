<?php

class Main_loc_list extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->config('yps/_constants');
		$this->load->library('pension_lib');
		$this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
		$this->load->model('_yps/travel/travel_model');
	}

	function index() {
		checkMethod('get');	// ���� �޼��带 ����


		$idx = $this->input->get('idx');
		if( !$idx ) $this->error->getError('0006');	// Key�� �������

		// $this->error->getError('0005');	// ������ �������


		$page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);

		$offset = ($page - 1) * $limit;

		$result = $this->travel_model->locBannerList(array(
															'idx'=>$idx,
															'page'=>$page,
															'limit'=>$limit,
															'offset'=>$offset
														));

		if(!$result['count'])
			$this->error->getError('0005');	// ������ �������


		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";
		$ret['tnt_cnt'] = $result['count']."";



		$no = 0;
		foreach ($result['query'] as $row) {
			$basket_cnt	= $this->basket_model->getTravelBasketCountByMpIdx( $row['dniIdx'] );
			$ret['lists'][$no]['idx'] = $row['dniIdx'];
			$ret['lists'][$no]['name'] = $row['dniTitle'];
			$ret['lists'][$no]['address'] = $row['dniSi'].' '.$row['dniGugun'].' '.$row['dniAdress'];
			$ret['lists'][$no]['theme'] = '??????????????'; // �׸�
			$ret['lists'][$no]['basket_cnt']	= $basket_cnt;
			$ret['lists'][$no]['filename'] = $row['dniFileName'];

			$ret['lists'][$no]['readnum'] = $row['dniReadnum'];

			$no++;
		}


		echo json_encode( $ret );

	}
}
?>