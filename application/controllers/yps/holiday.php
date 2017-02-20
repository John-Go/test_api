<?php

class Holiday extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('holiday_model');
    }
    
    function index() {
        $return = array();
        
        $endLastDay = date("t", mktime(0,0,0,date('m')+3,date('d'),date('Y')));
        $endDate = date("Y-m-d", mktime(0,0,0,date('m')+3,$endLastDay,date('Y')));
		
		$iPod = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
    	$iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
	    $iPad = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
	    $Android = stripos($_SERVER['HTTP_USER_AGENT'], "Android");
		
        $return['startDate'] = date('Y-m-d');
		$return['endDate'] = $endDate;
        
        $return['holidayLists'] = array();
        
        $lists = $this->holiday_model->getHoliday(date('Y-m-d'), $endDate);
        $i=0;
        if(count($lists) > 0){
            foreach($lists as $lists){
                $return['holidayLists'][$i]['holiName'] = $lists['hdName'];
                $return['holidayLists'][$i]['holiDate'] = $lists['hdDate'];
                $i++;
            }
        }
        
        echo json_encode( $return );
    }
}
?>