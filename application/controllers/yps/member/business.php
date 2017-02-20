<?php

class Business extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->library('pension_lib');
        $this->load->model('_yps/member/business_model');
		$this->load->model('smsnew_model');
        $this->returnVal = array();
        $this->returnVal['status'] = "1";
        $this->returnVal['failed_message'] = "";
    }

    function index() {
    	$pensionName = $this->input->post('pensionName');
		$location = $this->input->post('location');
		$mobile = $this->input->post('mobile');
		$homepage = $this->input->post('homepage');
		$calendar = $this->input->post('calendar');
		
		$this->business_model->insBusiness($pensionName, $location, $mobile, $homepage, $calendar);
		
		echo json_encode($this->returnVal);
		
    }
}
        