<?php

class Find_main extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한
		
		$ret = array();
		$ret['status'] = '1';
		$ret['failed_message'] = "";
		
		

		$result	= $this->pension_model->getThemePlaceList( $array = array(
			'code' => '1.',
			'favorite' => 1,
			'depth' => 3
		) );
		//print_re($result['obj']->result_array());
		
		
		foreach( $result['obj']->result() as $k => $o ) {
			$ret['lists'][$k]['code'] = $o->mtCode;
			$ret['lists'][$k]['name'] = $o->mtName;
			$ret['lists'][$k]['count'] = $o->sCnt;
		}
		
		// 이번주 토요일 구하기 START
		$toDay = time();
		$dayOfWeekNum = date('w', $toDay);
		$diffNum = 6 - (int)$dayOfWeekNum;
		date('Y-m-d', strtotime('+'.$diffNum.'days',$toDay));
		// 이번주 토요일 구하기 END
		
		echo json_encode( $ret );

//		$this->output->enable_profiler();

	}
}
?>