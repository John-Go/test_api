<?php

class AppMainBanner extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('_yps/pension/pension_model');
    }

    function index(){
        $return = array();
        
        $return['status'] = "1";
        $return['failed_message'] = "";
        
        $lists = $this->pension_model->getMainBanner();
        
        $i=0;
        if(count($lists) > 0){
            foreach($lists as $lists){
                $return['lists'][$i]['index'] = $lists['ambIdx'];
                $return['lists'][$i]['URL'] = $lists['ambURL'];
                $i++;
            }
        }
        
        echo json_encode( $return );
    }
}       