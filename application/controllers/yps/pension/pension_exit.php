<?php
class Pension_exit extends CI_Controller {
    function __construct() {
        parent::__construct();
       //$this->load->model('_yps/cast_model','cast_model');
		$this->load->model('_yps/pension_exit_model','pension_exit_model');
    }
    function index() {
        $reVal = array();
        $i=0;    
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
		$lists = $this->pension_exit_model->getPensionLists();
        
        if(count($lists) > 0){
            $randCount = count($lists)-1;
            $i = rand(0,$randCount);
            
            
            $reVal['mpsName'] = $lists[$i]['mpsName'];            
            $reVal['araFileName'] = "http://img.yapen.co.kr/pension/exitAd/".$lists[$i]['araFilename'];
            $reVal['araUrl'] = $lists[$i]['araUrl'];
        }else{
            $reVal['mpsName'] = "";            
            $reVal['araFileName'] = "";
            $reVal['araUrl'] = "";
        }
        
        
     
        echo json_encode($reVal);
    }
}
?>