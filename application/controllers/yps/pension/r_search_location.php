<?php

class R_search_location extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');

		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한
		
		//if( !$idx ) $this->error->getError('0006');	// Key가 없을경우		 

		$ret['status'] = '1';
		$ret['failed_message'] = '';

		//테마
		$ret['lists'] = $this->pension_model->getRThemePlaceCategory();
		echo json_encode( $ret );
	}
}

?>