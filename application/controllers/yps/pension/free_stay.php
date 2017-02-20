<?php
class Free_stay extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('pension_lib');
        $this->load->model('_yps/pension/pension_model');
    }

    function index() {
        $ret['status'] = '1';
        $ret['failed_message'] = '';
        $ret['moveUrl'] = "http://yapen.kr/event_freestay";
        $ret['moveUrl'] = "http://m.yapen.co.kr/freeStay/lists?appFlag=Y";

        echo json_encode( $ret );
    }
}
?>