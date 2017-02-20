<?php

class MsgException extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('em_model');
    }
    
    function index(){
        $cid = $this->input->get_post('cid');
        $rejectnumber = $this->input->get_post('rejectnumber');
        $trannumber = $this->input->get_post('trannumber');
        $regdate = $this->input->get_post('regdate');
        
        $reVal = $this->em_model->setException($cid, $rejectnumber, $trannumber, $regdate);
        
        echo $reVal;
    }
}