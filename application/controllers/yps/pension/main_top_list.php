<?php

class Main_top_list extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->config('yps/_constants');
        $this->load->library('pension_lib');
        $this->load->model('_yps/pension/pension_model');
        $this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
    }

    function index() {
        checkMethod('get'); // 접근 메서드를 제한

        $idx = $this->input->get('idx');
        if( !$idx ) $this->error->getError('0006'); // Key가 없을경우

        
        $page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
        $limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $result = $this->pension_model->topBannerList(array(
                                                            'idx'=>$idx,
                                                            'page'=>$page,
                                                            'limit'=>$limit,
                                                            'offset'=>$offset
                                                        ));



        if(!$result['count'])
            $this->error->getError('0005'); // 정보가 없을경우



        $banner = $this->pension_model->topBannerBanner($idx);

        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['tnt_cnt'] = $result['count']."";
        
        $ret['image'] = "http://img.yapen.co.kr/pension/mobile/".$banner['amtbFilename'];
        
        $ret['imgWidth'] = $banner['amtbWidth'];
        $ret['imgHeight'] = $banner['amtbHeight'];

        // ******************************************** 임시정보 *************************************************************
        // ******************************************** 임시정보 *************************************************************
        // ******************************************** 임시정보 *************************************************************

        $no = 0;
        foreach ($result['query'] as $row) {
            //주소를 시/구/군 으로 자름
            $addr = @explode(' ',$row['mpsAddr1']);
            if( isset( $addr[0] ) && isset( $addr[1] ) ) $addr = $addr[0].' '.$addr[1];
            else $addr = $row['mpsAddr1'];

            $ret['lists'][$no]["idx"] = $row['mpIdx'];          // 펜션키
            $ret['lists'][$no]["image"] = 'http://img.yapen.co.kr/pension/etc/'.$row['mpIdx'].'/'.$row['ppbImage'];     // 이미지경로
            $ret['lists'][$no]["image_cnt"] = "0";
            $ret['lists'][$no]["location"] = $addr; // 지역정보
            $ret['lists'][$no]["name"] = $row['mpsName'];       // 펜션명
            //$ret['lists'][$no]["content"] = $this->pension_lib->themeInfo($row['mpsIdx']);    // 테마정보
            $ret['lists'][$no]["content"] = "";   // 테마정보
            if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $row['ppbReserve'] == "R") ||
            	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
                $ret['lists'][$no]["price"] = number_format(floor(($row['price']-($row['price']*0.02))/10)*10)."";
                $percent = 100-floor(($row['price']-($row['price']*0.02))/$row['basicPrice']*100);
                $ret['lists'][$no]["sales"] = $percent."";
            }else{
                $ret['lists'][$no]["price"] = number_format($row['price'])."";
                $ret['lists'][$no]["sales"] = round(100-($row['price']/$row['basicPrice']*100),0)."";;
            }
            
            $ret['lists'][$no]["review"] = $row['ppbWantCnt']."";                    // 리뷰
            $ret['lists'][$no]["reserve"] = $row['ppbReserve'];         // 예약방식
            $ret['lists'][$no]["eventFlag"] = $row['ppbEventFlag'];
            if($row['ptsSale'] > 0){
            	$ret['lists'][$no]["todaySale"]   = "Y";   //당일특가 여부
            }else{
            	$ret['lists'][$no]["todaySale"]   = "N";   //당일특가 여부
            }

            $no++;
        }

        echo json_encode( $ret );
    }

    function web() {
        checkMethod('get'); // 접근 메서드를 제한


        $idx = $this->input->get('idx');
        if( !$idx ) $this->error->getError('0006'); // Key가 없을경우

        
        $page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
        $limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $result = $this->pension_model->topBannerList(array(
                                                            'idx'=>$idx,
                                                            'page'=>$page,
                                                            'limit'=>$limit,
                                                            'offset'=>$offset
                                                        ));



        if(!$result['count'])
            $this->error->getError('0005'); // 정보가 없을경우



        $banner = $this->pension_model->topBannerBanner($idx);

        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['tnt_cnt'] = $result['count']."";
        $ret['image'] = "http://img.yapen.co.kr/pension/mobile/".$banner['amtbFilename'];

        // ******************************************** 임시정보 *************************************************************
        // ******************************************** 임시정보 *************************************************************
        // ******************************************** 임시정보 *************************************************************

        $no = 0;
        foreach ($result['query'] as $row) {
            //주소를 시/구/군 으로 자름
            $addr = @explode(' ',$row['mpsAddr1']);
            if( isset( $addr[0] ) && isset( $addr[1] ) ) $addr = $addr[0].' '.$addr[1];
            else $addr = $row['mpsAddr1'];

            $pensionPriceInfo = $this->pension_model->pensionMinPrice($row['mpIdx']);       

            $ret['lists'][$no]["idx"] = $row['mpIdx'];          // 펜션키
            $ret['lists'][$no]["image"] = 'http://img.yapen.co.kr/pension/etc/'.$row['mpIdx'].'/'.$row['ppbImage'];     // 이미지경로
            $ret['lists'][$no]["image_cnt"] = $this->pension_model->pensionImageCount( $row['mpIdx'] );
            $ret['lists'][$no]["location"] = $addr; // 지역정보
            $ret['lists'][$no]["name"] = $row['mpsName'];       // 펜션명
            $ret['lists'][$no]["content"] = $this->pension_lib->themeInfo($row['mpsIdx']);  // 테마정보
            $ret['lists'][$no]["price"] = $pensionPriceInfo->minPrice;  // 이용요금
            $ret['lists'][$no]["review"] = $this->basket_model->getPensionBasketCountByMpIdx($row['mpIdx']);                    // 리뷰
            $ret['lists'][$no]["sales"] = $pensionPriceInfo->maxSalePercent;            // 세일요금
            $ret['lists'][$no]["reserve"] = $row['ppbReserve'];         // 예약방식
            $ret['lists'][$no]["eventFlag"] = $row['ppbEventFlag'];
            

            $no++;
        }

        echo var_dump( $ret );
    }
}
?>