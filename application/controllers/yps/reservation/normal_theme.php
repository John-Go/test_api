<?php

class Normal_theme extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
	}

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$ret['status'] = '1';
		$ret['failed_message'] = '';

		//테마
		$ret['lists'] = $this->pension_model->getThemePlaceCategoryNormal();
		echo json_encode( $ret );
	}
}

?>