<?php
class LocationInfo extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->config('yps/_constants');
        $this->load->library('pension_lib');
        $this->load->model('_ypv/info_model','info_model');
    }
    
    function index(){
        $reVal = array();
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        
        $lists = $this->info_model->getLocationInfo();
        
        $i = 0;
        $totalCount = 0;
        foreach($lists as $lists){
            $count = $this->info_model->getPensionCount($lists['mtCode']);
            if($count > 0){
                $reVal['lists'][$i]['code'] = $lists['mtCode'];
                $reVal['lists'][$i]['codeName'] = rawurlencode($lists['mtName']);
                $reVal['lists'][$i]['count'] = $count;
                $i++;
            }
            $totalCount = $totalCount + $count;
        }
        $reVal['totalCount'] = $totalCount;
        echo json_encode($reVal);
    }
    
}
?>