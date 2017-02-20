<?php

class Search_location extends CI_Controller {
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
		
		$data = $this->pension_model->getLocationTheme(); 
		
	    $ret['lists'] = $data['lists'];
        $ret['popLists'] = $data['popLists'];
		
		echo json_encode( $ret );
	}
    
    function web(){
        checkMethod('get'); // 접근 메서드를 제한
        $ret['status'] = '1';
        $ret['failed_message'] = '';
        //테마
        $ret['lists'] = $this->pension_model->getThemePlaceCategory();
        echo var_dump( $ret );
    }
}

?>