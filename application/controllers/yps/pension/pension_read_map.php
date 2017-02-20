<?php

class Pension_read_map extends CI_Controller {
	function __construct() {
		parent::__construct();

//		$CI =& get_instance();
//		$CI->dbHTS = $this->load->database('hts', TRUE);

		$this->load->library('pension_lib');
		$this->load->model('_yps/travel/travel_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$latitude = $this->input->get('latitude');
		$longitude = $this->input->get('longitude');
		if( !$latitude || !$longitude) $this->error->getError('0006');	// Key가 없을경우

		$result = $this->travel_model->travelMap($latitude,$longitude);
		//$result = $this->travel_model->travelMapLists($latitude,$longitude);

		$no = 0;

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";
		$ret['tnt_cnt'] = $result['count']."";

		foreach ($result['query'] as $row) {
			
			$ret['lists'][$no]['idx'] = $row['dniIdx'];
			$ret['lists'][$no]['name'] = $row['dniTitle']; // 여행지명
			$ret['lists'][$no]['address'] = $row['dniSi'].' '.$row['dniGugun'].' '.$row['dniAdress']; // 여행지주소
			$ret['lists'][$no]['latitude'] = $row['dniGoogleY'];	// 여행지경도
			$ret['lists'][$no]['longitude'] = $row['dniGoogleX']; // 여행지위도
			/*
			$ret['lists'][$no]['idx'] = $row['mpIdx'];
			$ret['lists'][$no]['name'] = $row['mpsName']; // 여행지명
			$ret['lists'][$no]['address'] = $row['mpsAddr1']; // 여행지주소
			$ret['lists'][$no]['latitude'] = $row['mpsMapX']; // 여행지위도
			$ret['lists'][$no]['longitude'] = $row['mpsMapY'];	// 여행지경도
			*/
			$no++;
		}
		
		echo json_encode( $ret );

	}
}
?>