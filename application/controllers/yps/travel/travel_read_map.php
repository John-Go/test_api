<?php

class Travel_read_map extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');

		$this->load->model('_yps/pension/pension_model');
		$this->load->model('_yps/travel/travel_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$latitude = $this->input->get('latitude');
		$longitude = $this->input->get('longitude');
		if( !$latitude || !$longitude ) $this->error->getError('0006');	// Key가 없을경우


		$prnsionResult = $this->pension_model->pensionMap($latitude,$longitude);
		$travelResult = $this->travel_model->travelMap($latitude,$longitude);

		if(!$prnsionResult['count'] && !$travelResult['count'])
//			$this->error->getError('0005');	// 정보가 없을경우


		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";


		$pensionNo = 0;
		$ret['pensions']['count'] = $prnsionResult['count']."";
		foreach ($prnsionResult['query'] as $row) {

			$ret['pensions']['lists'][$pensionNo]['idx'] = $row['mpIdx'];
			$ret['pensions']['lists'][$pensionNo]['name'] = $row['mpsName']; // 여행지명
			$ret['pensions']['lists'][$pensionNo]['address'] = $row['mpsAddr1']; // 여행지주소
			$ret['pensions']['lists'][$pensionNo]['latitude'] = $row['mpsMapY']; // 여행지위도
			$ret['pensions']['lists'][$pensionNo]['longitude'] = $row['mpsMapX'];	// 여행지경도

			$pensionNo++;
		}

		$travelNo = 0;
		$ret['travels']['count'] = $travelResult['count'];
		foreach ($travelResult['query'] as $row) {

			$ret['travels']['lists'][$travelNo]['idx'] = $row['dniIdx'];
			$ret['travels']['lists'][$travelNo]['name'] = $row['dniTitle']; // 여행지명
			$ret['travels']['lists'][$travelNo]['address'] = $row['dniSi'].' '.$row['dniGugun'].' '.$row['dniAdress']; // 여행지주소
			$ret['travels']['lists'][$travelNo]['latitude'] = $row['dniGoogleY']; // 여행지위도
			$ret['travels']['lists'][$travelNo]['longitude'] = $row['dniGoogleX'];	// 여행지경도
			$travelNo++;
		}

		echo json_encode( $ret );

//		$this->output->enable_profiler();

	}
}
?>