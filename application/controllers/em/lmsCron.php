<?php

class LmsCron extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('em_model');
    }
    
    function index(){
        $this->em_model->resendLMS();
    }
}