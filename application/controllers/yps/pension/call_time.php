<?php

class Call_time extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->library('pension_lib');

        $this->load->model('_yps/pension/pension_model');
    }

    function index() {
        /*
            $ret['status'] = '1';
            $ret['failed_message'] = '';
            $ret['mainText'] = "고객센터 1644-4816";
            $ret['firstDate'] = "월~토 09:00~18:00 | 점심 12:00~13:00";
            $ret['secDate'] = "(일요일, 공휴일 휴무)";
            $ret['lunchText'] = "";
        */
            $ret['status'] = '1';
            $ret['failed_message'] = '';
            $ret['mainText'] = "고객센터 1644-4816";
            $ret['firstDate'] = "월~토 09:00~22:00 | 일,공휴일 휴무";
            $ret['secDate'] = "(점심 12:00~13:00)";
            $ret['lunchText'] = "";
        
        echo json_encode($ret);
    }
}
?>