<?php

class Find_pension_pyh extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->config('yps/_constants');
		$this->load->library('pension_lib');
		$this->load->model('_yps/reservation/reservation_model_pyh', 'reservation_model');
		// $this->load->model('_yps/reservation/reservation_model');
		$this->load->model('_yps/pension/pension_model');
		$this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
		$this->load->model('_yps/pension/room_model');
		$this->config->load('yps/_code');
	}

	function index() {

		$page = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['page']), 1, NULL);
		$limit = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['limit']), 20, NULL);

		$searchLoc = urldecode($_REQUEST['searchLoc']);				// 지역테마키
		$searchDate = urldecode($_REQUEST['searchDate']);				// 입실일
		$searchDateNum = urldecode($_REQUEST['searchDateNum']);		// 입실박수
		$searchAdultNum = urldecode($_REQUEST['searchAdultNum']);		// 성인인원수
		$searchChild = urldecode($_REQUEST['searchChild']);			// 유아인원수
		$searchBaby = urldecode($_REQUEST['searchBaby']);				// 아이인원수
		$searchPriceMin = urldecode($_REQUEST['searchPriceMin']);		// 최저가
		$searchPriceMax = urldecode($_REQUEST['searchPriceMax']);		// 최고가
		$searchTheme = urldecode($_REQUEST['searchTheme']);			// 테마키
		$idxStrings     = urldecode($_REQUEST['idxStrings']);         //제외할 Idx 값

		$offset = ($page - 1) * $limit;
        
        //random 시 제외할 업체 key
        if( isset($idxStrings) ){
            $idxStrings = explode(',', $idxStrings );
        }else
            $idxStrings = array();

		// 투숙일 날짜정보
		$arrayDate = $this->pension_lib->reservationDate($searchDate, $searchDateNum);

		// 공휴일 전일 날짜
		$arrayHolidayDate = $this->pension_lib->reservationHolidayDate($arrayDate);

		// 블록된 객실키 리스트
		$arrayBlockPensionKey = $this->reservation_model->blockPentionList($arrayDate);

		$result = $this->reservation_model->findReservationPensionList(
             $arrayBlockPensionKey
            ,($searchAdultNum+$searchChild+$searchBaby)
            ,$searchPriceMin
            ,$searchPriceMax
            ,$arrayDate
            ,$searchLoc
            ,$searchTheme
            ,$arrayHolidayDate
            ,$offset
            ,$limit
            ,$idxStrings
        );

		$ret = array();
		$ret['status'] = "1";
		$ret['failed_message'] = "";
		$ret['tnt_cnt'] = $result['count']."";
		$ret['room_cnt'] = '';
        $ret['idxStrings'] = array();
        
		

		$no = 0;
		foreach ($result['query'] as $row) {
            $addr_change = explode(" ",$row['mpsAddr1']);
			$ret['lists'][$no]['idx'] = $row['mpIdx'];			// 펜션키
			$ret['lists'][$no]['image'] = 'http://img.yapen.co.kr/pension/etc/'.$row['mpIdx'].'/'.$row['ppbImage'];		// 이미지경로
			$ret['lists'][$no]['location'] = $addr_change[0]." ".$addr_change[1];	// 지역정보
			$ret['lists'][$no]['name'] = $row['mpsName'];		// 펜션명
			$ret['lists'][$no]['content'] = $this->pension_lib->themeInfo($row['mpsIdx']);	// 테마정보
			$ret['lists'][$no]['states'] = $row['ppbReserve'];   // 실시간여부
			$ret['lists'][$no]['grade'] = $row['ppbGrade'];   //상위노출여부
			
			// 201405141134 pyh : reservation_model.php 참조하여 펜션 최저가 다시 계산
			// $ret['lists'][$no]['price'] = number_format($row['ppbRoomMin']);	// 1st : 이용요금
			// $pensionPriceInfo = $this->pension_model->pensionMinPrice($row['mpIdx']);
            // $ret['lists'][$no]['price'] = $pensionPriceInfo->minPrice;  // 2nd : 오늘의 펜션 최저가 요금
            
			$ret['lists'][$no]['basket_cnt'] = $this->basket_model->getPensionBasketCountByMpIdx($row['mpIdx']);	// 가보고싶어요 수

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

				$ret['lists'][$no]['lists'][$roomNum]['room_key'] = $roomRow['pprIdx'];				// 객실키
				$ret['lists'][$no]['lists'][$roomNum]['room_name'] = $roomRow['pprName'];			// 객실명
				$ret['lists'][$no]['lists'][$roomNum]['room_in_min'] = $roomRow['pprInMin'];			// 최소인원
				$ret['lists'][$no]['lists'][$roomNum]['room_in_max'] = $roomRow['pprInMax'];			// 최대인원
				
				$price	= $this->room_model->totalPrice( $roomRow['pprIdx'], $searchDate, $searchDateNum );
				$ret['lists'][$no]['lists'][$roomNum]['seasonPrice'] = number_format($price['byRoom']['seasonPrice']);			// 시즌요금
				$ret['lists'][$no]['lists'][$roomNum]['resultPrice'] = number_format($price['byRoom']['resultPrice']);			// 할인요금
				
								
				$roomImageResult = $this->pension_model->pensionRoomImageLists($roomRow['pprIdx'], 0, 5);

				$imageNum = 0;
				foreach ($roomImageResult['query'] as $imageRow) {
					$ret['lists'][$no]['lists'][$roomNum]['lists'][$imageNum]['image'] = 'http://img.yapen.co.kr/pension/room/'.$row['mpIdx'].'/800x0/'.$imageRow['pprpFileName'];			// 객실사진

					$imageNum++;
				}
                
                // 201405141134
                $aUseAbleMinPrice[$roomNum] = $price['byRoom']['seasonPrice'];
                $aUseAbleMinPriceSale[$roomNum] = $price['byRoom']['resultPrice'];
                
				$roomNum++;
			}
            
            // 201405141134
            $ret['lists'][$no]['price'] = number_format(min($aUseAbleMinPriceSale));  // 오늘의 펜션 최저가 요금
            
            array_push($ret['idxStrings'],$row['mpIdx']);
            
			$no++;
		}
        $ret['idxStrings'] = implode(',', $ret['idxStrings'] );

		echo json_encode( $ret );
	}
}
?>