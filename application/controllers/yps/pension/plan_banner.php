<?php

class Plan_banner extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->model('_yps/pension/pension_model');
    }

    function index() {
        checkMethod('get'); // 접근 메서드를 제한

        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        
        $topResult = $this->pension_model->mainTopBanner();
        $topNum = 0;        
        foreach ($topResult as $row) {
            $ret['info']['main_plan']['lists'][$topNum]['idx'] = $row['amtbIdx'];
            $ret['info']['main_plan']['lists'][$topNum]['title'] = $row['amtbTitle'];
            $ret['info']['main_plan']['lists'][$topNum]['filename'] = 'http://img.yapen.co.kr/pension/mobile/'.$row['amtbFilename'];
            $ret['info']['main_plan']['lists'][$topNum]['flag'] = $row['amtbBannerFlag'];
            $ret['info']['main_plan']['lists'][$topNum]['eventUrl'] = $row['amtbReturnVal'];
            $ret['info']['main_plan']['lists'][$topNum]['imgWidth'] = $row['amtbWidth'];
            $ret['info']['main_plan']['lists'][$topNum]['imgHeight'] = $row['amtbHeight'];
            $topNum++;
        }
        echo json_encode( $ret );
    }
}
?>