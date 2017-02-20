<?php

class Find_pension extends CI_Controller {
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
        $searchAdultNum = urldecode($_REQUEST['searchAdultNum']);       // 성인인원수
        $searchChild = urldecode($_REQUEST['searchChild']);         // 유아인원수
        $searchBaby = urldecode($_REQUEST['searchBaby']);               // 아이인원수
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
        $offset = ($page - 1) * $limit;
        if(substr($idxStrings,0,1) == ","){
            $idxStrings = substr($idxStrings, 1);
            
        }
        $schPeople = (int)$searchAdultNum+(int)$searchChild+(int)$searchBaby;
        $result = $this->reservation_model->getEmptyPensionLists($searchLoc, $searchDate, $searchDateNum, $schPeople, $searchPriceMin, $searchPriceMax, $searchTheme, $idxStrings, $searchOrderby, $limit);

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
            $addr_change = explode(" ",$lists['mpsAddr1']);
            $ret['lists'][$no]['idx'] = $lists['mpIdx'];          // 펜션키
            $ret['lists'][$no]['image'] = 'http://img.yapen.co.kr/pension/etc/'.$lists['mpIdx'].'/'.$lists['ppbImage'];     // 이미지경로
            $ret['lists'][$no]['location'] = $addr_change[0]." ".$addr_change[1];   // 지역정보
            $ret['lists'][$no]['name'] = $lists['mpsName'];       // 펜션명
            $ret['lists'][$no]['states'] = $lists['ppbReserve'];   // 실시간여부
            if($row['ppbGrade'] >= 10){
                $ret['lists'][$no]['grade'] = "1";   //상위노출여부
            }else{
                $ret['lists'][$no]['grade'] = "0";   //상위노출여부
            }
            $ret['lists'][$no]['basket_cnt'] = $lists['ppbWantCnt'];
            $ret['lists'][$no]['price'] = number_format($lists['price']);  // 오늘의 펜션 최저가 요금
            $ret['lists'][$no]['content'] = "";
            /*
            if($iPod || $iPhone || $iPad ){
                
            }else{
                $ret['lists'][$no]['price'] = number_format(min($lists['']));  // 오늘의 펜션 최저가 요금
            }
            */ 
            $roomLists = $this->reservation_model->getEmptyRoomLists($searchDate, $searchDateNum, $schPeople, $searchPriceMin, $searchPriceMax, $lists['mpIdx']);
            
            $roomNum = 0;
            foreach ($roomLists as $roomLists) {                
                $ret['lists'][$no]['lists'][$roomNum]['room_key'] = $roomLists['pprIdx'];             // 객실키
                $ret['lists'][$no]['lists'][$roomNum]['room_name'] = $roomLists['pprName'];           // 객실명
                $ret['lists'][$no]['lists'][$roomNum]['room_in_min'] = $roomLists['pprInMin'];            // 최소인원
                $ret['lists'][$no]['lists'][$roomNum]['room_in_max'] = $roomLists['pprInMax'];            // 최대인원                
                
                $ret['lists'][$no]['lists'][$roomNum]['seasonPrice'] = (string)number_format($roomLists['basicPrice']);          // 시즌요금
                $ret['lists'][$no]['lists'][$roomNum]['resultPrice'] = (string)number_format($roomLists['price']);          // 할인요금
                
                if($_SERVER['REMOTE_ADDR'] == "211.119.165.87"){
                    $ret['lists'][$no]['lists'][$roomNum]['lists'][0]['image'] = '';          // 객실사진
                }else{
                    $roomImageResult = $this->pension_model->pensionRoomImageLists($roomLists['pprIdx'], 0, 5);

                    $imageNum = 0;
                    foreach ($roomImageResult['query'] as $imageRow) {
                        $ret['lists'][$no]['lists'][$roomNum]['lists'][$imageNum]['image'] = 'http://img.yapen.co.kr/pension/room/'.$lists['mpIdx'].'/800x0/'.$imageRow['pprpFileName'];          // 객실사진
    
                        $imageNum++;
                    }
                }
                
                
                $roomNum++;
            }
            
            array_push($idxStrings,$lists['mpIdx']);
            
            $no++;
        }
        
        $ret['idxStrings'] = implode(',', $idxStrings );
        
        echo json_encode( $ret );
    }

    function testK() {
        $page = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['page']), 1, NULL);
        $limit = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['limit']), 20, NULL);
        
        $iPod = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
        $iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
        $iPad = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
        $Android = stripos($_SERVER['HTTP_USER_AGENT'], "Android");
        
        $searchLoc = urldecode($_REQUEST['searchLoc']);             // 지역테마키
        $searchDate = urldecode($_REQUEST['searchDate']);               // 입실일
        $searchDateNum = urldecode($_REQUEST['searchDateNum']);     // 입실박수
        $searchAdultNum = urldecode($_REQUEST['searchAdultNum']);       // 성인인원수
        $searchChild = urldecode($_REQUEST['searchChild']);         // 유아인원수
        $searchBaby = urldecode($_REQUEST['searchBaby']);               // 아이인원수
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
        
        
        
        $offset = ($page - 1) * $limit;
        
        //random 시 제외할 업체 key
        if( isset($idxStrings)){
            $idxStrings = explode(',', $idxStrings );
        }else{
            $idxStrings = array();
        }
        // 투숙일 날짜정보
        $arrayDate = $this->pension_lib->reservationDate($searchDate, $searchDateNum);
        
        // 공휴일 전일 날짜
        $arrayHolidayDate = $this->pension_lib->reservationHolidayDate($arrayDate);

        // 블록된 객실키 리스트
        $arrayBlockPensionKey = $this->reservation_model->blockPentionList($arrayDate);

        $result = $this->reservation_model->findReservationPensionListOrderby(
             $arrayBlockPensionKey
            ,($searchAdultNum + $searchChild + $searchBaby)
            ,$searchPriceMin
            ,$searchPriceMax
            ,$arrayDate
            ,$searchLoc
            ,$searchTheme
            ,$arrayHolidayDate
            ,$searchOrderby
            ,$offset
            ,$limit
            ,$idxStrings
        );

        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['tnt_cnt'] = $result['count']."";
        $ret['room_cnt'] = '';
        
        //echo var_dump($idxStrings);
        $no = 0;
        foreach ($result['query'] as $row) {
            $addr_change = explode(" ",$row['mpsAddr1']);
            $ret['lists'][$no]['idx'] = $row['mpIdx'];          // 펜션키
            $ret['lists'][$no]['image'] = 'http://img.yapen.co.kr/pension/etc/'.$row['mpIdx'].'/'.$row['ppbImage'];     // 이미지경로
            $ret['lists'][$no]['location'] = $addr_change[0]." ".$addr_change[1];   // 지역정보
            $ret['lists'][$no]['name'] = $row['mpsName'];       // 펜션명
            $ret['lists'][$no]['content'] = $this->pension_lib->themeInfo($row['mpsIdx']);  // 테마정보
            $ret['lists'][$no]['states'] = $row['ppbReserve'];   // 실시간여부
            if($row['ppbGrade'] >= 10){
                $ret['lists'][$no]['grade'] = "1";   //상위노출여부
            }else{
                $ret['lists'][$no]['grade'] = "0";   //상위노출여부
            }
            
            
            // 201405141134 pyh : reservation_model.php 참조하여 펜션 최저가 다시 계산
            // $ret['lists'][$no]['price'] = number_format($row['ppbRoomMin']); // 1st : 이용요금
            // $pensionPriceInfo = $this->pension_model->pensionMinPrice($row['mpIdx']);
            // $ret['lists'][$no]['price'] = $pensionPriceInfo->minPrice;  // 2nd : 오늘의 펜션 최저가 요금
            
            $ret['lists'][$no]['basket_cnt'] = $this->basket_model->getPensionBasketCountByMpIdx($row['mpIdx']);    // 가보고싶어요 수

            $roomResult = $this->reservation_model->findReservationPensionRoomList(
                 $arrayBlockPensionKey
                ,($searchAdultNum+$searchChild+$searchBaby)
                ,$searchPriceMin
                ,$searchPriceMax
                ,$arrayDate
                ,$arrayHolidayDate
                ,$row['mpIdx']
            );

            $roomNum = 0;
            $aUseAbleMinPrice = array();
            $aUseAbleMinPriceSale = array();
            $pensionPriceArray = array();
            foreach ($roomResult as $roomRow) {
                
                $tntPrice = 0;
                for($i=0; $i<sizeof($arrayDate); $i++)
                    $tntPrice += $roomRow['price_'.$i];

                $ret['lists'][$no]['lists'][$roomNum]['room_key'] = $roomRow['pprIdx'];             // 객실키
                $ret['lists'][$no]['lists'][$roomNum]['room_name'] = $roomRow['pprName'];           // 객실명
                $ret['lists'][$no]['lists'][$roomNum]['room_in_min'] = $roomRow['pprInMin'];            // 최소인원
                $ret['lists'][$no]['lists'][$roomNum]['room_in_max'] = $roomRow['pprInMax'];            // 최대인원
                
                $price  = $this->room_model->realTimePrice( $roomRow['pprIdx'], $searchDate, $searchDateNum, $row['mpIdx'] );
                
                if($iPod || $iPhone || $iPad ){
                    $ret['lists'][$no]['lists'][$roomNum]['seasonPrice'] = (string)$price['basicPrice'];          // 시즌요금
                    $ret['lists'][$no]['lists'][$roomNum]['resultPrice'] = (string)$price['salePrice'];          // 할인요금
                }else{
                    $ret['lists'][$no]['lists'][$roomNum]['seasonPrice'] = (string)number_format($price['basicPrice']);          // 시즌요금
                    $ret['lists'][$no]['lists'][$roomNum]['resultPrice'] = (string)number_format($price['salePrice']);          // 할인요금
                }
                                
                $roomImageResult = $this->pension_model->pensionRoomImageLists($roomRow['pprIdx'], 0, 5);

                $imageNum = 0;
                foreach ($roomImageResult['query'] as $imageRow) {
                    $ret['lists'][$no]['lists'][$roomNum]['lists'][$imageNum]['image'] = 'http://img.yapen.co.kr/pension/room/'.$row['mpIdx'].'/800x0/'.$imageRow['pprpFileName'];          // 객실사진

                    $imageNum++;
                }
                
                // 201405141134
                
                $aUseAbleMinPrice[$roomNum] = $price['basicPrice'];
                $aUseAbleMinPriceSale[$roomNum] = $price['salePrice'];
                $pensionPriceArray[] = (int)$price['salePrice'];
                
                $roomNum++;
            }
            // 201405141134
            if($iPod || $iPhone || $iPad ){
                $ret['lists'][$no]['price'] = number_format($row['price']);  // 오늘의 펜션 최저가 요금
            }else{
                $ret['lists'][$no]['price'] = number_format(min($aUseAbleMinPriceSale));  // 오늘의 펜션 최저가 요금
            }
            
            
            array_push($idxStrings,$row['mpIdx']);
            
            $no++;
        }
        $ret['idxStrings'] = implode(',', $idxStrings );

        echo json_encode( $ret );
    }

    

    function web() {
        
        $page = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['page']), 1, NULL);
        $limit = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['limit']), 20, NULL);
        $limit = 10;
        $searchLoc = urldecode($_REQUEST['searchLoc']);             // 지역테마키
        $searchDate = urldecode($_REQUEST['searchDate']);               // 입실일
        $searchDateNum = urldecode($_REQUEST['searchDateNum']);     // 입실박수
        $searchAdultNum = urldecode($_REQUEST['searchAdultNum']);       // 성인인원수
        $searchChild = urldecode($_REQUEST['searchChild']);         // 유아인원수
        $searchBaby = urldecode($_REQUEST['searchBaby']);               // 아이인원수
        $searchPriceMin = urldecode($_REQUEST['searchPriceMin']);       // 최저가
        $searchPriceMax = urldecode($_REQUEST['searchPriceMax']);       // 최고가
        $searchTheme = urldecode($_REQUEST['searchTheme']);         // 테마키
        $idxStrings     = urldecode($_REQUEST['idxStrings']);         //제외할 Idx 값
        $searchOrderby     = urldecode($_REQUEST['searchOrderby']);         //제외할 Idx 값
        if($searchOrderby == ""){
            $searchOrderby = "1";
        }
        
        
        
        $offset = ($page - 1) * $limit;
        
        //random 시 제외할 업체 key
        if( isset($idxStrings)){
            $idxStrings = explode(',', $idxStrings );
        }else{
            $idxStrings = array();
        }
        // 투숙일 날짜정보
        $arrayDate = $this->pension_lib->reservationDate($searchDate, $searchDateNum);
        
        // 공휴일 전일 날짜
        $arrayHolidayDate = $this->pension_lib->reservationHolidayDate($arrayDate);

        // 블록된 객실키 리스트
        $arrayBlockPensionKey = $this->reservation_model->blockPentionList($arrayDate);

        $result = $this->reservation_model->findReservationPensionListOrderby(
             $arrayBlockPensionKey
            ,($searchAdultNum + $searchChild + $searchBaby)
            ,$searchPriceMin
            ,$searchPriceMax
            ,$arrayDate
            ,$searchLoc
            ,$searchTheme
            ,$arrayHolidayDate
            ,$searchOrderby
            ,$offset
            ,$limit
            ,$idxStrings
        );

        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['tnt_cnt'] = $result['count']."";
        $ret['room_cnt'] = '';
        
        //echo var_dump($idxStrings);
        $no = 0;
        foreach ($result['query'] as $row) {
            $addr_change = explode(" ",$row['mpsAddr1']);
            $ret['lists'][$no]['idx'] = $row['mpIdx'];          // 펜션키
            $ret['lists'][$no]['image'] = 'http://img.yapen.co.kr/pension/etc/'.$row['mpIdx'].'/'.$row['ppbImage'];     // 이미지경로
            $ret['lists'][$no]['location'] = $addr_change[0]." ".$addr_change[1];   // 지역정보
            $ret['lists'][$no]['name'] = $row['mpsName'];       // 펜션명
            $ret['lists'][$no]['content'] = $this->pension_lib->themeInfo($row['mpsIdx']);  // 테마정보
            $ret['lists'][$no]['states'] = $row['ppbReserve'];   // 실시간여부
            if($row['ppbGrade'] >= 10){
                $ret['lists'][$no]['grade'] = "1";   //상위노출여부
            }else{
                $ret['lists'][$no]['grade'] = "0";   //상위노출여부
            }
            
            
            // 201405141134 pyh : reservation_model.php 참조하여 펜션 최저가 다시 계산
            // $ret['lists'][$no]['price'] = number_format($row['ppbRoomMin']); // 1st : 이용요금
            // $pensionPriceInfo = $this->pension_model->pensionMinPrice($row['mpIdx']);
            // $ret['lists'][$no]['price'] = $pensionPriceInfo->minPrice;  // 2nd : 오늘의 펜션 최저가 요금
            
            $ret['lists'][$no]['basket_cnt'] = $this->basket_model->getPensionBasketCountByMpIdx($row['mpIdx']);    // 가보고싶어요 수

            $roomResult = $this->reservation_model->findReservationPensionRoomList(
                 $arrayBlockPensionKey
                ,($searchAdultNum+$searchChild+$searchBaby)
                ,$searchPriceMin
                ,$searchPriceMax
                ,$arrayDate
                ,$arrayHolidayDate
                ,$row['mpIdx']
            );

            $roomNum = 0;
            $aUseAbleMinPrice = array();
            $aUseAbleMinPriceSale = array();
            
            foreach ($roomResult as $roomRow) {
                
                $tntPrice = 0;
                for($i=0; $i<sizeof($arrayDate); $i++)
                    $tntPrice += $roomRow['price_'.$i];

                $ret['lists'][$no]['lists'][$roomNum]['room_key'] = $roomRow['pprIdx'];             // 객실키
                $ret['lists'][$no]['lists'][$roomNum]['room_name'] = $roomRow['pprName'];           // 객실명
                $ret['lists'][$no]['lists'][$roomNum]['room_in_min'] = $roomRow['pprInMin'];            // 최소인원
                $ret['lists'][$no]['lists'][$roomNum]['room_in_max'] = $roomRow['pprInMax'];            // 최대인원
                
                $price  = $this->room_model->DirectTotalPriceTest( $roomRow['pprIdx'], $searchDate, $searchDateNum );
                $ret['lists'][$no]['lists'][$roomNum]['seasonPrice'] = number_format($price['basicPrice']);          // 시즌요금
                $ret['lists'][$no]['lists'][$roomNum]['resultPrice'] = number_format($price['salePrice']);          // 할인요금
                
                                
                $roomImageResult = $this->pension_model->pensionRoomImageLists($roomRow['pprIdx'], 0, 5);

                $imageNum = 0;
                foreach ($roomImageResult['query'] as $imageRow) {
                    $ret['lists'][$no]['lists'][$roomNum]['lists'][$imageNum]['image'] = 'http://img.yapen.co.kr/pension/room/'.$row['mpIdx'].'/800x0/'.$imageRow['pprpFileName'];          // 객실사진

                    $imageNum++;
                }
                
                // 201405141134
                $aUseAbleMinPrice[$roomNum] = $price['basicPrice'];
                $aUseAbleMinPriceSale[$roomNum] = $price['salePrice'];
                
                $roomNum++;
            }
            
            // 201405141134
            $ret['lists'][$no]['price'] = number_format(min($aUseAbleMinPriceSale));  // 오늘의 펜션 최저가 요금
            
            array_push($idxStrings,$row['mpIdx']);
            
            $no++;
        }
        $ret['idxStrings'] = implode(',', $idxStrings );
        
        echo var_dump($ret);
    }
	
	function test() {
		/*$time_start=array_sum(explode(' ',microtime()));
		echo date('Y /m / d / H: i :s').microtime(); 
		echo '<br>';*/
        $page = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['page']), 1, NULL);
        $limit = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['limit']), 20, NULL);
        
        $iPod = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
        $iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
        $iPad = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
        $Android = stripos($_SERVER['HTTP_USER_AGENT'], "Android");
        
        $searchLoc = urldecode($_REQUEST['searchLoc']);             // 지역테마키
        $searchDate = urldecode($_REQUEST['searchDate']);               // 입실일
        $searchDateNum = urldecode($_REQUEST['searchDateNum']);     // 입실박수
        $searchAdultNum = urldecode($_REQUEST['searchAdultNum']);       // 성인인원수
        $searchChild = urldecode($_REQUEST['searchChild']);         // 유아인원수
        $searchBaby = urldecode($_REQUEST['searchBaby']);               // 아이인원수
        $searchPriceMin = urldecode($_REQUEST['searchPriceMin']);       // 최저가
        $searchPriceMax = urldecode($_REQUEST['searchPriceMax']);       // 최고가
        $searchTheme = urldecode($_REQUEST['searchTheme']);         // 테마키
        $idxStrings     = urldecode($_REQUEST['idxStrings']);         //제외할 Idx 값
        $searchOrderby     = urldecode($_REQUEST['searchOrderby']);         //제외할 Idx 값
        if($searchOrderby == ""){
            $searchOrderby = "1";
        }
  
        $offset = ($page - 1) * $limit;
		
		if($searchDateNum <1){
			$searchDateNum = 1;
		}
		
		$endCount = 0;
		$revDate = array();
		
		for($i=0; $i<$searchDateNum; $i++){
			$revDate[$i] = strftime("%Y-%m-%d" ,strtotime("+".$i." days", strtotime($searchDate))).'<br>';  
			$endCount++;
		}
		$endCount = $endCount-1;
		
	
		$holliday =  $this->reservation_model2->holidayLists($revDate[0], $revDate[$endCount]);
		
		
		$personNum = $searchAdultNum + $searchChild + $searchBaby;
		if($personNum < '1' || !$personNum){
			$personNum = '2';
		}
		$pensionList = $this->reservation_model2->getPensionList($searchLoc, $personNum, $searchPriceMin, $searchPriceMax, $searchTheme, $searchOrderby, $limit);
		
		
		for($i=0; $i<count($pensionList); $i++){
			$roomList = $this->reservation_model2->getRoomList($pensionList[$i]['mpIdx'], $revDate, $endCount,  $personNum, $searchPriceMin, $searchPriceMax,  $limit);
		}
		
		 
		
		
		
        

    }

}
?>