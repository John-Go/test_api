<?php
class Holiday extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('pension_lib');
        $this->load->model('_yps/pension/calendar_model');
    }

    function index() {
        $mpIdx = $this->input->get('mpIdx');
        $date = $this->input->get('date');
        
        $reVal = array();
        $lists = $this->calendar_model->getHolidayLists($mpIdx, $date);
        
        $reVal['status'] = '1';
        $reVal['failed_message'] = '';
        
        if(count($lists) > 0){
            $i=0;
            foreach($lists as $lists){
                if(!isset($lists['mpIdx'])){
                    $reVal['lists'][$i]['date'] = $lists['hDate'];
                }                
                $i++;
            }
        }
        
        echo json_encode($reVal);
    }
}
?>