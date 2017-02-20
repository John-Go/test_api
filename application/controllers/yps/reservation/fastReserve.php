<?php

class FastReserve extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->config('yps/_constants');
        $this->load->library('pension_lib');
        $this->load->model('_yps/reservation/reservation_model');
        $this->load->model('_yps/reservation/reservation_model2');
        $this->load->model('_yps/pension/pension_model');
        $this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
        $this->load->model('_yps/pension/room_model');
        $this->config->load('yps/_code');
        
        
    }
    
    function index() {
        $page = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['page']), 1, NULL);
        $limit = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['limit']), 20, NULL);
        
        $iPod = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
        $iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
        $iPad = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
        $Android = stripos($_SERVER['HTTP_USER_AGENT'], "Android");
        
        $searchLoc = urldecode($_REQUEST['searchLoc']);             // 지역테마키
        $searchDate = urldecode($_REQUEST['searchDate']);               // 입실일
        
        //2015-12-28 이유진 수정
        if(substr($searchDate,0,7) == "2016-12" && date('Y') == "2015"){
            $searchDate = "2015-12".substr($searchDate,7);
        }
        
        $searchDateNum = urldecode($_REQUEST['searchDateNum']);     // 입실박수
        $searchAdultNum = $this->input->get_post('searchAdultNum');       // 성인인원수
        //$searchChild = $this->input->get_post('searchChild');         // 유아인원수
        //$searchBaby = $this->input->get_post('searchBaby');               // 아이인원수
        $searchChild = 0;
        $searchBaby = 0;
        $searchPriceMin = urldecode($_REQUEST['searchPriceMin']);       // 최저가
        if(!$searchPriceMin){
            $searchPriceMin = 0;
        }
        $searchPriceMax = urldecode($_REQUEST['searchPriceMax']);       // 최고가
        $searchTheme = urldecode($_REQUEST['searchTheme']);         // 테마키
        $idxStrings     = urldecode($_REQUEST['idxStrings']);         //제외할 Idx 값
        $searchOrderby     = urldecode($_REQUEST['searchOrderby']);         //제외할 Idx 값
        if($searchOrderby == ""){
            $searchOrderby = "1";
        }
		$todaySale = $this->input->get_post('todaySale');		//당일특가
		
		
        $offset = ($page - 1) * $limit;
        if(substr($idxStrings,0,1) == ","){
            $idxStrings = substr($idxStrings, 1);
            
        }
        $schPeople = (int)$searchAdultNum+(int)$searchChild+(int)$searchBaby;
        $result = $this->reservation_model->getEmptyPensionLists($searchLoc, $searchDate, $searchDateNum, $schPeople, $searchPriceMin, $searchPriceMax, $searchTheme, $idxStrings, $searchOrderby, $limit, $todaySale);

        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['tnt_cnt'] = $result['count']."";
        $ret['room_cnt'] = '';
        
        
        $no = 0;
        
        if( isset($idxStrings)){
            $idxStrings = explode(',', $idxStrings );
        }else{
            $idxStrings = array();
        }
        
        foreach ($result['lists'] as $lists) {
            $pensionSalePercent = 0;
            $addr_change = explode(" ",$lists['mpsAddr1']);
            $ret['lists'][$no]['idx'] = $lists['mpIdx'];          // 펜션키
            $ret['lists'][$no]['image'] = 'http://img.yapen.co.kr/pension/etc/'.$lists['mpIdx'].'/'.$lists['ppbImage'];     // 이미지경로
            $ret['lists'][$no]['location'] = $addr_change[0]." ".$addr_change[1];   // 지역정보
            $ret['lists'][$no]['name'] = $lists['mpsName'];       // 펜션명
            $ret['lists'][$no]['states'] = $lists['ppbReserve'];   // 실시간여부
            if($lists['ppbGrade'] >= 10){
                $ret['lists'][$no]['grade'] = "1";   //상위노출여부
            }else{
                $ret['lists'][$no]['grade'] = "0";   //상위노출여부
            }
            $ret['lists'][$no]['basket_cnt'] = $lists['ppbWantCnt'];
            $ret['lists'][$no]['basicPrice'] = number_format($lists['basicPrice']);  // 오늘의 펜션 최저가 요금
            if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && $searchDate >= YAPEN_SALE_EVENT_START && $searchDate <= YAPEN_SALE_EVENT_END) ||
            	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
                $ret['lists'][$no]['price'] = number_format(floor(($lists['price']-($lists['price']*0.02))/10*10));
            }else{
                $ret['lists'][$no]['price'] = number_format($lists['price']);  // 오늘의 펜션 최저가 요금
            }
            $ret['lists'][$no]['content'] = "";
            /*
            if($iPod || $iPhone || $iPad ){
                
            }else{
                $ret['lists'][$no]['price'] = number_format(min($lists['']));  // 오늘의 펜션 최저가 요금
            }
            */ 
            $roomLists = $this->reservation_model->getEmptyRoomLists($searchDate, $searchDateNum, $schPeople, $searchPriceMin, $searchPriceMax, $lists['mpIdx'], $todaySale);
            
            $roomNum = 0;
			$pensionTodaySale = "N";
            foreach ($roomLists as $roomLists) {                
                $ret['lists'][$no]['lists'][$roomNum]['pension_idx'] = $lists['mpIdx'];             // 펜션키 (안드로이드에서 사용)
                $ret['lists'][$no]['lists'][$roomNum]['room_key'] = $roomLists['pprIdx'];             // 객실키
                $ret['lists'][$no]['lists'][$roomNum]['room_name'] = $roomLists['pprName'];           // 객실명
                $ret['lists'][$no]['lists'][$roomNum]['room_in_min'] = $roomLists['pprInMin'];            // 최소인원
                $ret['lists'][$no]['lists'][$roomNum]['room_in_max'] = $roomLists['pprInMax'];            // 최대인원                
                
                $ret['lists'][$no]['lists'][$roomNum]['seasonPrice'] = (string)number_format($roomLists['basicPrice']);          // 기본요금
                
                if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && $searchDate >= YAPEN_SALE_EVENT_START && $searchDate <= YAPEN_SALE_EVENT_END) ||
                	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
                    $ret['lists'][$no]['lists'][$roomNum]['resultPrice'] = (string)number_format(floor(($roomLists['price']-($roomLists['price']*0.02))/10*10));
                    $salePercent = (100-floor(($roomLists['price']-($roomLists['price']*0.02))/$roomLists['basicPrice']*100));
                }else{
                    $ret['lists'][$no]['lists'][$roomNum]['resultPrice'] = (string)number_format($roomLists['price']);          // 최종요금
                    $salePercent = (100-round(($roomLists['price']/$roomLists['basicPrice']*100),0));
                }
                $ret['lists'][$no]['lists'][$roomNum]['salePercent'] = $salePercent;
				if($roomLists['ptsSale'] > 0){
					$pensionTodaySale = "Y";
					$ret['lists'][$no]['lists'][$roomNum]['todaySale'] = "Y"; //당일특가 여부
				}else{
					$ret['lists'][$no]['lists'][$roomNum]['todaySale'] = "N"; //당일특가 여부
				}
                $ret['lists'][$no]['lists'][$roomNum]['todaySpecial'] = "N"; //당일특가 여부 - 삭제
                if($pensionSalePercent < $salePercent){
                    $pensionSalePercent = $salePercent;
                }
                $roomNum++;
            }
            $ret['lists'][$no]['salePercent'] = $pensionSalePercent;
            $ret['lists'][$no]['todaySpecial'] = "N"; //당일특가 여부 - 삭제
            $ret['lists'][$no]['todaySale'] = $pensionTodaySale; //당일특가 여부
            array_push($idxStrings,$lists['mpIdx']);
            
            $no++;
        }
        
        $ret['idxStrings'] = implode(',', $idxStrings );
        
        echo json_encode( $ret );
    }
}
?>