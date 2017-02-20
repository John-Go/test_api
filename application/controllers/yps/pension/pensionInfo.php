<?php
class PensionInfo extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('pension_lib');
        $this->load->model('_yps/pension/info_model');
		$this->reVal = array();
		$this->reVal['status'] = "0";
		$this->reVal['failed_message'] = "실패";
		$this->ynConvert = array('1' => 'Y', '0' => 'N');
		$this->config->load('yps/_code');
    }
	
	function index(){
		checkMethod('get'); // 접근 메서드를 제한

        $mpIdx = $this->input->get('index');
        if(!$mpIdx){
        	$this->error->getError('0006');
		}
        
        $info = $this->info_model->getPensionInfo($mpIdx);
		if(!isset($info['mpIdx'])){
			$this->error->getError('0006');
		}
		
		$pensionType = "";
		if($info['ptPension'] == 1){
			$pensionType .= "·펜션";
		}
		if($info['ptGuest'] == 1){
			$pensionType .= "·게스트하우스";
		}
		if($info['ptVilla'] == 1){
			$pensionType .= "·풀빌라";
		}
		if($info['ptResort'] == 1){
			$pensionType .= "·리조트";
		}
		if($info['ptGlamping'] == 1){
			$pensionType .= "·글램핑";
		}
		if($info['ptCaravan'] == 1){
			$pensionType .= "·카라반";
		}
		if($pensionType != ""){
			$pensionType = mb_substr($pensionType, 1);
		}else{
			$pensionType = "펜션";
		}
		if(str_replace('|','',$info['ppbHashTag']) == ""){
			$hashTagArray = array();
		}else{
			$hashTagArray = explode('|', $info['ppbHashTag']);
			$hashTagArray = array_values(array_filter($hashTagArray));
		}
		
		$roomLocFlag = $this->info_model->getPensionPhotoCount($mpIdx, 'R');
		
		if((int)$roomLocFlag >= 1){
			$roomLocFlag = "Y";
		}else{
			$roomLocFlag = "N";
		}
        $vrFlag = $this->info_model->getPensionPhotoCount($mpIdx, 'V');
		if((int)$vrFlag >= 1){
			$vrFlag = "Y";
		}else{
			$vrFlag = "N";
		}
		
		$todaySaleInfo = $this->info_model->getTodaySaleInfo($mpIdx);
		
		$tipCount = $this->info_model->getPensionTipCount($mpIdx);
		$partnerText = "";
		
		if(str_replace('|','',$info['ppbSubPension']) != ""){
			$partner = explode('|', $info['ppbSubPension']);
			$partner = array_filter($partner);
			$partnerLists = $this->info_model->getPensionPartner($partner);
			
			if(count($partnerLists) > 0){
				foreach($partnerLists as $partnerLists){
					$partnerText .= ','.$partnerLists['ppcnPensionName'];
				}
			}
			if($partnerText != ""){
				$partnerText = substr($partnerText, 1);
			}
		}
		
		$pickPlace = explode(',',$info['ppbPickPlace']);
		$pickPlace = array_values(array_filter($pickPlace));
		$pickPlaceTime = explode(',',$info['ppbPickPlaceTime']);
		$pickPlaceTime = array_values(array_filter($pickPlaceTime));
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		$this->reVal['info'] = array();
		$this->reVal['info']['index'] = (int)$info['mpIdx'];	//펜션 Index
		$this->reVal['info']['pensionName'] = ($info['mpsName']);	//펜션명
		$this->reVal['info']['intro'] = ($this->pension_lib->htmlRemove($info['ppbIntro']));
		$this->reVal['info']['want'] = number_format($info['ppbWantCnt']);
		$this->reVal['info']['attention'] = ($this->pension_lib->htmlRemoveArray($info['ppbWayAttention']));
		$this->reVal['info']['type'] = ($pensionType);
		$this->reVal['info']['hashTag'] = ($hashTagArray);
		$this->reVal['info']['roomLayout'] = $roomLocFlag;
		$this->reVal['info']['vr'] = $vrFlag;
		$this->reVal['info']['tipCount'] = number_format($tipCount);
		$this->reVal['info']['roomCount'] = number_format($info['ppbRoom']);
		$this->reVal['info']['reserve'] = $info['ppbReserve'];
		if($info['mpsTelService']){
			$this->reVal['info']['serviceTel'] = $info['mpsTelService'];
		}else{
			$this->reVal['info']['serviceTel'] = "";
		}		
		$this->reVal['info']['tel'] = $info['ppbTel1'];
		$this->reVal['info']['partner'] = ($partnerText);
		
		$this->reVal['todaySale'] = array();
		if(isset($todaySaleInfo['mpIdx'])){
			$this->reVal['todaySale']['flag'] = "Y";
			$this->reVal['todaySale']['start'] = $todaySaleInfo['startTime'];
			$this->reVal['todaySale']['end'] = $todaySaleInfo['endTime'];
		}else{
			$this->reVal['todaySale']['flag'] = "N";
			$this->reVal['todaySale']['start'] = "00:00";
			$this->reVal['todaySale']['end'] = "00:00";
		}
		
		$this->reVal['address'] = array();
		$this->reVal['address']['address'] = ($info['mpsAddr1']." ".$info['mpsAddr2']);
		$this->reVal['address']['mapX'] = $info['mpsMapX'];
		$this->reVal['address']['mapY'] = $info['mpsMapY'];
		
		$this->reVal['time'] = array();
		$this->reVal['time']['checkIn'] = $info['ppbTimeIn'];
		$this->reVal['time']['checkOut'] = $info['ppbTimeOut'];
		$this->reVal['time']['info'] = ($this->pension_lib->htmlRemoveArray($info['ppbTimeText']));
		
		$pickupText = "";
		if($info['ppbPick'] == 1){
			$pickupText = "펜션 자체 픽업 가능";
		}else if($info['ppbPick'] == 2){
			$pickupText = "마트 픽업 가능";
		}else{
			$pickupText = "픽업 불가능";
		}
		$this->reVal['pickup'] = array();
		$this->reVal['pickup']['type'] = $info['ppbPick'];
		$this->reVal['pickup']['typeText'] = ($pickupText);
		$this->reVal['pickup']['info'] = ($this->pension_lib->htmlRemoveArray($info['ppbPickText']));
		if(count($this->reVal['pickup']['info']) < 1){
			if($info['ppbPick'] == 1){
				$this->reVal['pickup']['info'][0] = "픽업서비스 이용을 원하실 경우 펜션으로 연락하여 미리 신청해 주시기 바랍니다.";
			}else if($info['ppbPick'] == 2){
				$this->reVal['pickup']['info'][0] = "주변 마트에서 픽업이 가능하며, 자세한 내용은 펜션으로 문의해 주시기 바랍니다.";
			}else{
				$this->reVal['pickup']['info'][0] = "픽업서비스를 지원하지 않습니다.";
			}
		}
		$this->reVal['pickup']['place'] = implode(', ',$pickPlace);
		$this->reVal['pickup']['placeTime'] = implode(', ',$pickPlaceTime);
		
		/*
		$authArray = array('amenity','price','photo','reserve','bed');
		$authColumnArray = array('ptAuthAmenity','ptAuthPrice','ptAuthPhoto','ptAuthReserve','ptAuthBed');
		$authTitleArray = array(
								'호텔식 어메니티 서비스',
								'최저가 보상제',
								'인증된 사진',
								'안심 예약',
								'호텔식 침구류 제공');
	 	*/
		$authArray = array('amenity','bed');
		$authColumnArray = array('ptAuthAmenity','ptAuthBed');
		$authTitleArray = array(
								'호텔식 어메니티 서비스',
								'호텔식 침구류 제공');
		
		$this->reVal['auth'] = array();
		$authNo = 0;
		for($i=0; $i< count($authArray); $i++){
			if($info[$authColumnArray[$i]] == 1){
				$this->reVal['auth'][$authNo] = array();
				$this->reVal['auth'][$authNo]['type'] = $authArray[$i];
				$this->reVal['auth'][$authNo]['title'] = $authTitleArray[$i];
				$authNo++;
			}
		}
		
		$this->reVal['badge'] = array();
		//MD 추천 뱃지
		
		$this->reVal['badge'][0]['type'] = 'MD';
		$this->reVal['badge'][0]['title'] = "MD 추천";
		if($info['ppbOnline'] == "1"){
			$this->reVal['badge'][0]['flag'] = "Y";
		}else{
			$this->reVal['badge'][0]['flag'] = "N";
		}
		$this->reVal['badge'][0]['color'] = array();
		$this->reVal['badge'][0]['color']['R'] = "87";
		$this->reVal['badge'][0]['color']['G'] = "209";
		$this->reVal['badge'][0]['color']['B'] = "186";
		$this->reVal['badge'][0]['color']['A'] = "100";
		
		
		$this->reVal['price'] = array();
		$this->reVal['price']['basic'] = number_format($info['basicPrice']);
		
		if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $info['ppbReserve'] == "R") ||
        	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
            $this->reVal['price']['result'] = number_format(floor(($info['resultPrice']-($info['resultPrice']*0.02))/10)*10);
            $percent = 100-floor(($info['resultPrice']-($info['resultPrice']*0.02))/$info['basicPrice']*100);
            $this->reVal['price']['percent'] = number_format($percent);
        }else{
            $this->reVal['price']['result'] = number_format($info['resultPrice']);
            $this->reVal['price']['percent'] = number_format(round(100-($info['resultPrice']/$info['basicPrice']*100),0));
        }
		
		
		$photoLists = $this->info_model->getPensionMainPhoto($mpIdx);
		
		$this->reVal['photo'] = array();
		$this->reVal['photo']['main'] = "http://img.yapen.co.kr/pension/etc/".$mpIdx."/".$info['ppbImage'];
		$this->reVal['photo']['count'] = count($photoLists);
		$this->reVal['photo']['lists'] = array();
        if(count($photoLists) > 0){
            $i = 0;
            foreach($photoLists as $photoLists){
                if($photoLists['photoType'] == "E"){
                    $this->reVal['photo']['lists'][$i] = 'http://img.yapen.co.kr/pension/etc/'.$mpIdx.'/800x0/'.$photoLists['imageUrl'];
                }else{
                    $this->reVal['photo']['lists'][$i] = 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$photoLists['imageUrl'];
                }
                $i++;
            }
        }
		
		$this->reVal['popup'] = array();
		$this->reVal['popup']['premium'] = array();
		$this->reVal['popup']['new'] = array();
		$this->reVal['popup']['free'] = array();
		
		if($info['pbIdx'] != ""){
			$this->reVal['popup']['premium']['flag'] = "Y";
			$this->reVal['popup']['premium']['index'] = (int)$info['pbIdx'];
			$this->reVal['popup']['premium']['image'] = "http://img.yapen.co.kr/pension/best/".$info['pbIdx']."/".$info['pbImage'];
		}else{
			$this->reVal['popup']['premium']['flag'] = "N";
			$this->reVal['popup']['premium']['index'] = 0;
			$this->reVal['popup']['premium']['image'] = "";
		}
		
		if($info['pnIdx'] != ""){
			$this->reVal['popup']['new']['flag'] = "Y";
			$this->reVal['popup']['new']['index'] = (int)$info['pnIdx'];
			$this->reVal['popup']['new']['image'] = "http://img.yapen.co.kr/pension/newPension/".$info['pnIdx']."/".$info['pnImage'];
		}else{
			$this->reVal['popup']['new']['flag'] = "N";
			$this->reVal['popup']['new']['index'] = 0;
			$this->reVal['popup']['new']['image'] = "";
		}
		
		if($info['pfsIdx'] != ""){
			$this->reVal['popup']['free']['flag'] = "Y";
			$this->reVal['popup']['free']['index'] = (int)$info['pfsIdx'];
			$this->reVal['popup']['free']['image'] = "";
		}else{
			$this->reVal['popup']['free']['flag'] = "N";
			$this->reVal['popup']['free']['index'] = 0;
			$this->reVal['popup']['free']['image'] = "";
		}
		 
		$this->reVal['service'] = array();
		$i=0;
		$serviceLists = $this->info_model->getPensionService($mpIdx, $info['mpsIdx']);
		$roomLists = $this->info_model->getPensionRoomLists($mpIdx, date('Y-m-d'));
		$roomCount = count($roomLists);
		
		$this->reVal['info']['roomRealCount'] = $roomCount;
		
		$themeRoom = array();
		if(count($roomLists) > 0){
			foreach($roomLists as $roomLists){
				$useful = explode(',', $roomLists['pprUseful']);
				
				if (in_array(1, $useful)) // 스파/월풀
					$themeRoom['909'][] = $roomLists['pprName'];
	
				if (in_array(19, $useful)) // 빔프로젝트
					$themeRoom['923'][] = $roomLists['pprName'];
	
				if (in_array(15, $useful)) // 벽난로
					$themeRoom['931'][] = $roomLists['pprName'];
	
				if (in_array(6, $useful)) // 개별 바비큐
					$themeRoom['935'][] = $roomLists['pprName'];
				
				if ($roomLists['pprFloorS'] == '1')
					$themeRoom['910'][] = $roomLists['pprName'];
				
				if ($roomLists['pprFloorM'] == '1')
					$themeRoom['911'][] = $roomLists['pprName'];
			}
		}

		if(count($serviceLists) > 0){
			foreach($serviceLists as $serviceLists){
				$this->reVal['service'][$i] = array();
				$this->reVal['service'][$i]['index'] = (int)$serviceLists['mtIdx'];
				$this->reVal['service'][$i]['info'] = ($this->pension_lib->htmlRemove($serviceLists['pptContent']));
				$this->reVal['service'][$i]['code'] = (string)$serviceLists['mtCode'];
				$this->reVal['service'][$i]['codeName'] = ($serviceLists['mtName']);
				if(isset($themeRoom[$serviceLists['mtIdx']])){
					if(count($themeRoom[$serviceLists['mtIdx']]) == $roomCount){
						$this->reVal['service'][$i]['room'] = "전체 객실 해당";
					}else{
						$this->reVal['service'][$i]['room'] = implode(', ', $themeRoom[$serviceLists['mtIdx']]);
					}
				}else{
					$this->reVal['service'][$i]['room'] = "";
				}
				$i++;
			}
		}
		$pensionThemeText = $this->pension_lib->htmlRemove($info['ppbThemeText']);
		if($pensionThemeText != ""){
			$this->reVal['service'][$i] = array();
			$this->reVal['service'][$i]['index'] = 1;
			$this->reVal['service'][$i]['info'] = $pensionThemeText;
			$this->reVal['service'][$i]['code'] = 'enjoy';
			$this->reVal['service'][$i]['codeName'] = '펜션즐기기';
			$this->reVal['service'][$i]['room'] = "";
		}
		
		
		$noticeLists = $this->info_model->getPensionNoticeLists($mpIdx, 3);
		$noticeCount = count($noticeLists);
		
		$this->reVal['notice'] = array();
		if($noticeCount > 0){
			$i = 0;
			$noticeLimitFlag = "N";
			foreach($noticeLists as $noticeLists){
				if($i == 0 && $noticeLists['peType'] == "D"){
					$noticeLimitFlag = "Y";
				}
				if($noticeLimitFlag == "Y" && $noticeCount == ($i+1) && $i > 1){
					continue;
				}
				$this->reVal['notice'][$i] = array();
				$this->reVal['notice'][$i]['index'] = (int)$noticeLists['peIdx'];
				$this->reVal['notice'][$i]['type'] = $noticeLists['peType'];
				$this->reVal['notice'][$i]['title'] = ($noticeLists['peTitle']);
				$i++;
			}
		}
		
		$this->reVal['banner'] = array();
		$this->reVal['banner']['revText'] = "";
		if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $info['ppbReserve'] == "R") ||
        	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
        		$this->reVal['banner']['revText'] = "최저가＋2% 특가할인!";
				$this->reVal['banner']['lists'] = array();
				$this->reVal['banner']['lists'][0]['image'] = "http://img.yapen.co.kr/pension/event/winterEvent/2016-11-25/appPensionDetail.png";
				$this->reVal['banner']['lists'][0]['width'] = 750;
				$this->reVal['banner']['lists'][0]['height'] = 217;
				$this->reVal['banner']['lists'][0]['link'] = "http://web.yapen.co.kr/yps/yapen/winterSale";
				$this->reVal['banner']['lists'][0]['title'] = "겨울 특별 이벤트";
		}else{
			$this->reVal['banner']['lists'] = array();
			$this->reVal['banner']['lists'][0]['image'] = "http://img.yapen.co.kr/pension/event/banner_mobile_promise.png";
			$this->reVal['banner']['lists'][0]['width'] = 1080;
			$this->reVal['banner']['lists'][0]['height'] = 312;
			$this->reVal['banner']['lists'][0]['link'] = "http://web.yapen.co.kr/event/yapenPromise";
			$this->reVal['banner']['lists'][0]['title'] = "고객과의 약속";
		}
		
		
		if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
			echo json_encode($this->reVal);
		}else{
			echo json_encode($this->reVal);
		}
        
	}

	function traffic(){
		checkMethod('get'); // 접근 메서드를 제한

        $mpIdx = $this->input->get('index');
        if(!$mpIdx){
        	$this->error->getError('0006');
		}
		
		$info = $this->info_model->getPensionInfo($mpIdx);
		if(!isset($info['mpIdx'])){
			$this->error->getError('0006');
		}
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		
		$this->reVal['info'] = ("출발 전에 펜션 연락처를 확인하여 펜션 가는 길을 자세히 안내 받고, 장마철이나 눈이 많이 내리는 계절에는 출발 전에 날씨와 도로 상태 등을 꼭 확인해 주시기 바랍니다.\n\n(야놀자펜션앱을 통해 예약한 경우, 예약 완료 시 펜션 연락 처를 고객님의 휴대폰으로 발송해 드립니다.)");
		
		$this->reVal['lists'] = array();
		
		$wayTraffic = ($this->pension_lib->htmlRemove($info['ppbWayTraffic']));
		$wayCar = ($this->pension_lib->htmlRemove($info['ppbWayCar']));
		
		$i=0;
		
		if(str_replace('\n','',$wayTraffic) != ""){
			$this->reVal['lists'][$i]['title'] = "대중교통";
			$this->reVal['lists'][$i]['content'] = $wayTraffic;
			$i++;
		}
		
		if(str_replace('\n','',$wayCar) != ""){
			$this->reVal['lists'][$i]['title'] = "자가용";
			$this->reVal['lists'][$i]['content'] = $wayCar;
			$i++;
		}
		
		echo json_encode($this->reVal);
	}

	function notice(){
		checkMethod('get'); // 접근 메서드를 제한

        $mpIdx = $this->input->get('index');
        if(!$mpIdx){
        	$this->error->getError('0006');
		}
		
		$lists = $this->info_model->getPensionNoticeLists($mpIdx, 9999);
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		$this->reVal['count'] = count($lists);
		$this->reVal['lists'] = array();
		if(count($lists) > 0){
			$i=0;
			foreach($lists as $lists){
				$this->reVal['lists'][$i]['title'] = ($lists['peTitle']);
				$this->reVal['lists'][$i]['type'] = $lists['peType'];
				$this->reVal['lists'][$i]['event'] = array();
				$eventFlag = "N";
				if($lists['peEventStart'] != "0000-00-00" && $lists['peEventEnd'] != "0000-00-00"){
					$eventFlag = "Y";
				}
				$this->reVal['lists'][$i]['event']['flag'] = $eventFlag;
				if($eventFlag == "Y"){
					$this->reVal['lists'][$i]['event']['startDate'] = str_replace("-",".",$lists['peEventStart']);
					$this->reVal['lists'][$i]['event']['endDate'] = str_replace("-",".",$lists['peEventEnd']);
				}else{
					$this->reVal['lists'][$i]['event']['startDate'] = "";
					$this->reVal['lists'][$i]['event']['endDate'] = "";
				}
				$this->reVal['lists'][$i]['content'] = ($this->pension_lib->htmlRemove($lists['peIntro']));
				$i++;
			}
		}

		echo json_encode($this->reVal);
	}
	
	function raw(){
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		
		$raw = array();
		
		$raw[0] = "야놀자펜션은 펜션 예약대행서비스로 실시간예약과 같이 야놀자펜션이 펜션과 이용자 간의 예약을 중개하는 방식이 아닌, '전화문의 업체'에 대해서는 펜션과 이용자 간의 전화 를 통한 직접 문의 및 예약을 원칙으로 하며, 이로 인해 발생하는 모든 문제에 대해 별도 책임을 지지 않으며, 아래와 같이 고지합니다.";
		$raw[1] = "야놀자펜션은 모바일, PC 등을 통해 배포, 전송, 포함되는 서비스에 정보의 정확성, 신뢰성이 있는 콘텐츠를 제공하기 위해 노력하지만 그 과정에서 발생할 수 있는 콘텐츠의 오 류 가능성이 있음을 인정합니다.";
		$raw[2] = "상기 2항의 이유로 야놀자펜션은 야놀자펜션에서 제공하는 모든 콘텐츠의 정확성이나 신뢰성에 대해 어떠한 보증도 하지 않으며, 콘텐츠의 오류로 인해 발생하는 모든 직접, 간접, 파생적, 징벌적, 부수적인 손해에 대해 책임을 지지 않습니다. 또한 야놀자펜션에서 제공하는 콘텐츠(객실정보, 요금정보, 이미지 등)에 대한 신뢰 여부는 전적으로 이용자 본인의 책임이며, 자세한 상품 구성은 해당 사이트에서 이용자 본인이 반드시 확인해야하며, 객실 예약 및 환불의 의무와 책임은 해당 숙박업소에 있습니다.";
		$raw[3] = "야놀자펜션은 판매업체의 활동, 제휴사에서 제공되는 상품이나 서비스 또는 게재되는 내용, 제휴사로의 접속 또는 접속 불가능으로 인한 손해, 손실, 상해에 대해서는 명시적으 로 어떠한 책임이나 의무도 부담하지 아니합니다.";
		
		$this->reVal['count'] = count($raw);
		$this->reVal['lists'] = ($raw);
		
		echo json_encode($this->reVal);
	}
	
	function allPhoto(){
		checkMethod('get'); // 접근 메서드를 제한

        $mpIdx = $this->input->get('index');
        if(!$mpIdx){
        	$this->error->getError('0006');
		}
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		
		$setDate = date('Y-m-d');
		
		$roomLists = $this->info_model->getPensionRoomLists($mpIdx, $setDate);
		$etcLists = $this->info_model->getPensionEtcLists($mpIdx, $setDate);
		
		$this->reVal['count'] = (count($roomLists)+count($etcLists));
		$this->reVal['lists'] = array();
		
		if(count($roomLists) > 0){
			$i=0;
			foreach($roomLists as $roomLists){
				$this->reVal['lists'][$i]['type'] = "R";
				$this->reVal['lists'][$i]['info'] = array();
				$this->reVal['lists'][$i]['info']['index'] = (int)$roomLists['pprIdx'];
				$this->reVal['lists'][$i]['info']['name'] = $roomLists['pprName'];
				$this->reVal['lists'][$i]['info']['inMin'] = $roomLists['pprInMin'];
				$this->reVal['lists'][$i]['info']['inMax'] = $roomLists['pprInMax'];
				$this->reVal['lists'][$i]['info']['pyeong'] =  $roomLists['pprSize'];
				$this->reVal['lists'][$i]['info']['size'] = (round($roomLists['pprSize'] / 0.3025, 0)."m²");
				
				$roomTypeConfig = $this->config->item('pprShape');
				
				$roomType = $roomTypeConfig[$roomLists['pprShape']];
				
				$roomOption = "";
				if($roomLists['pprBed'] > 0){
					$roomOption .= "+"."침대룸".$roomLists['pprBed'];
				}
				if($roomLists['pprOndol'] > 0){
					$roomOption .= "+"."온돌룸".$roomLists['pprOndol'];
				}
				if($roomLists['pprToilet'] > 0){
					$roomOption .= "+"."화장실".$roomLists['pprToilet'];
				}
				if($roomOption != ""){
					$roomOption = mb_substr($roomOption, 1);
				}
				if($roomOption != ""){
					$roomType .= "(".$roomOption.")";
				}
	            if($roomLists['pprFloorS'] == "1"){
	                $roomType = $roomType.', 독채형';
	            }
	            if($roomLists['pprFloorM'] == "1"){
	                $roomType = $roomType.', 복층형';
	            }
				
				$useful = explode(',', $roomLists['pprUseful']);
				$useful = array_values(array_filter($useful));
				
				$usefulText = "";
				if(in_array('1', $useful)){
					$usefulText .= ", 스파/월풀";
				}
				if(in_array('6', $useful)){
					$usefulText .= ", 개별바비큐";
				}
				if($usefulText != ""){
					$usefulText = substr($usefulText, 2);
				}
				$this->reVal['lists'][$i]['info']['useful'] = ($usefulText);
				$this->reVal['lists'][$i]['info']['type'] = ($roomType);
				
				$this->reVal['lists'][$i]['info']['todaySale'] = array();
				if($roomLists['ptsSale'] > 0){
					$this->reVal['lists'][$i]['info']['todaySale']['flag'] = "Y";
				}else{
					$this->reVal['lists'][$i]['info']['todaySale']['flag'] = "N";
				}
				$this->reVal['lists'][$i]['info']['todaySale']['start'] = $roomLists['ptsStartTime'];
				$this->reVal['lists'][$i]['info']['todaySale']['end'] = $roomLists['ptsEndTime'];
				
				$this->reVal['lists'][$i]['info']['price'] = array();
				$this->reVal['lists'][$i]['info']['price']['basic'] = number_format($roomLists['basicPrice']);
				if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $roomLists['ppbReserve'] == "R") ||
		        	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
		            $this->reVal['lists'][$i]['info']['price']['result'] = number_format(floor(($roomLists['resultPrice']-($roomLists['resultPrice']*0.02))/10)*10);
		            $percent = 100-floor(($roomLists['resultPrice']-($roomLists['resultPrice']*0.02))/$roomLists['basicPrice']*100);
		            $this->reVal['lists'][$i]['info']['price']['percent'] = number_format($percent);
		        }else{
		            $this->reVal['lists'][$i]['info']['price']['result'] = number_format($roomLists['resultPrice']);
					$this->reVal['lists'][$i]['info']['price']['percent'] = number_format(round(100-($roomLists['resultPrice']/$roomLists['basicPrice']*100),0));
		        }
				
				$this->reVal['lists'][$i]['photo'] = array();
				$photoLists = $this->info_model->getPensionRoomPhoto($roomLists['pprIdx']);
				$this->reVal['lists'][$i]['photo']['count'] = count($photoLists);
				$this->reVal['lists'][$i]['photo']['lists'] = array();
				if(count($photoLists) > 0){
					$j=0;
					foreach($photoLists as $photoLists){
						$this->reVal['lists'][$i]['photo']['lists'][$j] = 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$photoLists['pprpFileName'];
						$j++;
					}
					$i++;
				}else{
					unset($this->reVal['lists'][$i]); 
				}
			}
		}
		
		if(count($etcLists) > 0){
			foreach($etcLists as $etcLists){
				$this->reVal['lists'][$i]['type'] = "E";
				$this->reVal['lists'][$i]['info'] = array();
				$this->reVal['lists'][$i]['info']['index'] = (int)$etcLists['ppeIdx'];
				$this->reVal['lists'][$i]['info']['name'] = ($etcLists['ppeName']);
				
				$photoLists = $this->info_model->getPensionEtcPhoto($mpIdx, $etcLists['ppeIdx']);
				$this->reVal['lists'][$i]['photo']['count'] = count($photoLists);
				$this->reVal['lists'][$i]['photo']['lists'] = array();
				if(count($photoLists) > 0){
					$j=0;
					foreach($photoLists as $photoLists){
						$this->reVal['lists'][$i]['photo']['lists'][$j] = 'http://img.yapen.co.kr/pension/etc/'.$mpIdx.'/800x0/'.$photoLists['ppepFileName'];
						$j++;
					}
					$i++;
				}else{
					unset($this->reVal['lists'][$i]);
				}
			}
		}

		echo json_encode($this->reVal);
	}

	function photo(){
		checkMethod('get'); // 접근 메서드를 제한

        $index = $this->input->get('index');
		$type = $this->input->get('type');
		
        if(!$index || !$type){
        	$this->error->getError('0006');
		}
		
		$photoDir = "800x0";
		$columnName = "ppFileName";
		$fileDir = "etc";
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		
		if($type == "R"){
			$setDate = date('Y-m-d');
			$info = $this->info_model->getPensionRoomInfo($index, $setDate);
			$photo = $this->info_model->getPensionRoomPhoto($index);
			
			$this->reVal['info'] = array();
			$this->reVal['info']['index'] = (int)$info['pprIdx'];
			$this->reVal['info']['pensionIndex'] = $info['mpIdx'];
			$this->reVal['info']['name'] = ($info['pprName']);
			$this->reVal['info']['inMin'] = $info['pprInMin'];
			$this->reVal['info']['inMax'] = $info['pprInMax'];
			$this->reVal['info']['pyeong'] =  $info['pprSize'];
			$this->reVal['info']['size'] = round($info['pprSize'] / 0.3025, 0)."m²";
			
			$roomTypeConfig = $this->config->item('pprShape');
			
			$roomType = $roomTypeConfig[$info['pprShape']];
			
			$roomOption = "";
			if($info['pprBed'] > 0){
				$roomOption .= "+"."침대룸".$info['pprBed'];
			}
			if($info['pprOndol'] > 0){
				$roomOption .= "+"."온돌룸".$info['pprOndol'];
			}
			if($info['pprToilet'] > 0){
				$roomOption .= "+"."화장실".$info['pprToilet'];
			}
			if($roomOption != ""){
				$roomOption = mb_substr($roomOption, 1);
			}
			if($roomOption != ""){
				$roomType .= "(".$roomOption.")";
			}
            if($info['pprFloorS'] == "1"){
                $roomType = $roomType.', 독채형';
            }
            if($info['pprFloorM'] == "1"){
                $roomType = $roomType.', 복층형';
            }
            
			$this->reVal['info']['type'] = ($roomType);
			
			$useful = explode(',', $info['pprUseful']);
			$useful = array_values(array_filter($useful));
			
			$usefulText = "";
			if(in_array('1', $useful)){
				$usefulText .= ", 스파/월풀";
			}
			if(in_array('6', $useful)){
				$usefulText .= ", 개별바비큐";
			}
			if($usefulText != ""){
				$usefulText = substr($usefulText, 2);
			}
			$this->reVal['info']['useful'] = ($usefulText);
			$this->reVal['info']['reserve'] = $info['ppbReserve'];
			
			$this->reVal['info']['todaySale'] = array();
			if($info['ptsSale'] > 0){
				$this->reVal['info']['todaySale']['flag'] = "Y";
			}else{
				$this->reVal['info']['todaySale']['flag'] = "N";
			}
			$this->reVal['info']['todaySale']['start'] = $info['ptsStartTime'];
			$this->reVal['info']['todaySale']['end'] = $info['ptsEndTime'];
			
			$this->reVal['info']['price'] = array();
			$this->reVal['info']['price']['basic'] = number_format($info['basicPrice']);
			if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $info['ppbReserve'] == "R") ||
	        	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
	            $this->reVal['info']['price']['result'] = number_format(floor(($info['resultPrice']-($info['resultPrice']*0.02))/10)*10);
	            $percent = 100-floor(($info['resultPrice']-($info['resultPrice']*0.02))/$info['basicPrice']*100);
	            $this->reVal['info']['price']['percent'] = number_format($percent);
	        }else{
	            $this->reVal['info']['price']['result'] = number_format($info['resultPrice']);
				$this->reVal['info']['price']['percent'] = number_format(round(100-($info['resultPrice']/$info['basicPrice']*100),0));
	        }
			
			$columnName = "pprpFileName";
			$fileDir = "room";
		}else if($type == "E"){
			$info = $this->info_model->getPensionEtcInfo($index);
			$photo = $this->info_model->getPensionEtcPhoto($info['mpIdx'], $index);
			
			$this->reVal['info'] = array();
			$this->reVal['info']['index'] = (int)($info['ppeIdx']);
			$this->reVal['info']['name'] = $info['ppeName'];
			$this->reVal['info']['reserve'] = $info['ppbReserve'];
			$this->reVal['info']['pensionIndex'] = $info['mpIdx'];
			
			$columnName = "ppepFileName";
		}else if($type == "V"){
			$photo = $this->info_model->getPensionPhoto($index, $type);
			$info = $this->info_model->getPensionBasicInfo($index);
			
			$this->reVal['info'] = array();
			$this->reVal['info']['reserve'] = $info['ppbReserve'];
			$this->reVal['info']['pensionIndex'] = $info['mpIdx'];
			$photoDir = "2400x0";
		}else if($type == "RL"){
			$photo = $this->info_model->getPensionPhoto($index, $type);
			$info = $this->info_model->getPensionBasicInfo($index);
			
			$this->reVal['info'] = array();
			$this->reVal['info']['reserve'] = $info['ppbReserve'];
			$this->reVal['info']['pensionIndex'] = $info['mpIdx'];
		}
		
		$this->reVal['photo'] = array();
		$this->reVal['photo']['count'] = count($photo);
		$this->reVal['photo']['lists'] = array();
		
		if(count($photo) > 0){
			$i=0;
			foreach($photo as $photo){
				$this->reVal['photo']['lists'][$i] = "http://img.yapen.co.kr/pension/".$fileDir."/".$photo['mpIdx']."/".$photoDir."/".$photo[$columnName];
				$i++;
			}
		}
		
		echo json_encode($this->reVal);
	}

	function room(){
		checkMethod('get'); // 접근 메서드를 제한

        $mpIdx = $this->input->get('index');
        if(!$mpIdx){
        	$this->error->getError('0006');
		}
		
		$today = date('Y-m-d');
        
        $lists = $this->info_model->getPensionRoomLists($mpIdx, $today);
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		$this->reVal['lists'] = array();
		
		if(count($lists) == 0){
			$this->error->getError('0006');
		}
		
		$i=0;
		foreach($lists as $lists){
			$this->reVal['lists'][$i]['info']['index'] = (int)$lists['pprIdx'];
			$this->reVal['lists'][$i]['info']['name'] = ($lists['pprName']);
			$this->reVal['lists'][$i]['info']['inMin'] = $lists['pprInMin'];
			$this->reVal['lists'][$i]['info']['inMax'] = $lists['pprInMax'];
			$this->reVal['lists'][$i]['info']['pyeong'] =  $lists['pprSize'];
			$this->reVal['lists'][$i]['info']['size'] = (round($lists['pprSize'] / 0.3025, 0)."m²");
			
			$roomTypeConfig = $this->config->item('pprShape');
			
			$roomType = $roomTypeConfig[$lists['pprShape']];
			
            if($lists['pprFloorS'] == "1"){
                $roomType = $roomType.', 독채형';
            }
            if($lists['pprFloorM'] == "1"){
                $roomType = $roomType.', 복층형';
            }
            
			$this->reVal['lists'][$i]['info']['type'] = ($roomType);
			
			$this->reVal['lists'][$i]['image'] = 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$lists['pprpFileName'];
			
			$this->reVal['lists'][$i]['todaySale'] = array();
			if($lists['ptsSale'] > 0){
				$this->reVal['lists'][$i]['todaySale']['flag'] = "Y";
			}else{
				$this->reVal['lists'][$i]['todaySale']['flag'] = "N";
			}
			$this->reVal['lists'][$i]['todaySale']['start'] = $lists['ptsStartTime'];
			$this->reVal['lists'][$i]['todaySale']['end'] = $lists['ptsEndTime'];
			
			$this->reVal['lists'][$i]['price'] = array();
			$this->reVal['lists'][$i]['price']['basic'] = number_format($lists['basicPrice']);
			if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $lists['ppbReserve'] == "R") ||
	        	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
	            $this->reVal['lists'][$i]['price']['result'] = number_format(floor(($lists['resultPrice']-($lists['resultPrice']*0.02))/10)*10);
	            $percent = 100-floor(($lists['resultPrice']-($lists['resultPrice']*0.02))/$lists['basicPrice']*100);
	            $this->reVal['lists'][$i]['price']['percent'] = number_format($percent);
	        }else{
	            $this->reVal['lists'][$i]['price']['result'] = number_format($lists['resultPrice']);
				$this->reVal['lists'][$i]['price']['percent'] = number_format(round(100-($lists['resultPrice']/$lists['basicPrice'])*100,0));
	        }
			
			
			$i++;
		}

		if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
			//echo var_dump($this->reVal);
			echo json_encode($this->reVal);
		}else{
			echo json_encode($this->reVal);
		}
	}

	function roomInfo(){
		checkMethod('get'); // 접근 메서드를 제한

        $pprIdx = $this->input->get('index');
        if(!$pprIdx){
        	$this->error->getError('0006');
		}
		
		$today = date('Y-m-d');
        
        $info = $this->info_model->getPensionRoomInfo($pprIdx, $today);
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		$this->reVal['info'] = array();
		$this->reVal['info']['pensionIndex'] = $info['mpIdx'];
		$this->reVal['info']['index'] = $info['pprIdx'];
		$this->reVal['info']['name'] = $info['pprName'];
		$this->reVal['info']['reserve'] = $info['ppbReserve'];
		$this->reVal['info']['inMin'] = $info['pprInMin'];
		$this->reVal['info']['inMax'] = $info['pprInMax'];
		$this->reVal['info']['pyeong'] =  $info['pprSize'];
		$this->reVal['info']['size'] = (round($info['pprSize'] / 0.3025, 0)."m²");
		
		$roomTypeConfig = $this->config->item('pprShape');
		
		$roomType = $roomTypeConfig[$info['pprShape']];
		
		$roomOption = "";
		
		if($info['pprBed'] > 0){
			$roomOption .= "+"."침대룸".$info['pprBed'];
		}
		if($info['pprOndol'] > 0){
			$roomOption .= "+"."온돌룸".$info['pprOndol'];
		}
		if($info['pprToilet'] > 0){
			$roomOption .= "+"."화장실".$info['pprToilet'];
		}
		if($roomOption != ""){
			$roomOption = mb_substr($roomOption, 1);
		} 
		 
		if($roomOption != ""){
			$roomType .= "(".$roomOption.")";
		}
		
        if($info['pprFloorS'] == "1"){
            $roomType = $roomType.', 독채형';
        }
        if($info['pprFloorM'] == "1"){
            $roomType = $roomType.', 복층형';
        }
        
		$this->reVal['info']['type'] = $roomType;
		
		$this->reVal['info']['todaySale'] = array();
		if($info['ptsSale'] > 0){
			$this->reVal['info']['todaySale']['flag'] = "Y";
		}else{
			$this->reVal['info']['todaySale']['flag'] = "N";
		}
		$this->reVal['info']['todaySale']['start'] = $info['ptsStartTime'];
		$this->reVal['info']['todaySale']['end'] = $info['ptsEndTime'];
		
		$this->reVal['info']['price'] = array();
		$this->reVal['info']['price']['basic'] = number_format($info['basicPrice']);
		if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $info['ppbReserve'] == "R") ||
        	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
            $this->reVal['info']['price']['result'] = number_format(floor(($info['resultPrice']-($info['resultPrice']*0.02))/10)*10);
            $percent = 100-floor(($info['resultPrice']-($info['resultPrice']*0.02))/$info['basicPrice']*100);
            $this->reVal['info']['price']['percent'] = number_format($percent);
        }else{
            $this->reVal['info']['price']['result'] = number_format($info['resultPrice']);
			$this->reVal['info']['price']['percent'] = number_format(round(100-($info['resultPrice']/$info['basicPrice']*100),0));
        }
		
		$photo = $this->info_model->getPensionRoomPhoto($pprIdx);
		
		$this->reVal['photo'] = array();
		$this->reVal['photo']['count'] = count($photo);
		$this->reVal['photo']['lists'] = array();
		
		if(count($photo) > 0){
			$i=0;
			foreach($photo as $photo){
				$this->reVal['photo']['lists'][$i] = "http://img.yapen.co.kr/pension/room/".$photo['mpIdx']."/800x0/".$photo['pprpFileName'];
				$i++;
			}
		}else{
			$this->reVal['photo']['lists'][0] = "http://img.yapen.co.kr/pension/images/app/empty_image.png";
		}
		
		//요금 정보 (실시간 예약만)
		$this->reVal['price'] = array();
		if($info['ppbReserve'] == "R"){
			$startDate = date('Y-m-d');
			
	        $endLastDay = date("t", mktime(0,0,0,date('m')+3,date('d'),date('Y')));
	        $endDate = date("Y-m-d", mktime(0,0,0,date('m')+3,$endLastDay,date('Y')));
	        $btDay = round(abs(strtotime($endDate)-strtotime($startDate))/86400);
			$this->reVal['price']['lists'] = $this->info_model->getPensionPriceLists($info['mpIdx'], $pprIdx, $startDate, $endDate, $btDay);
		}
		
		//상세 설명
		$this->reVal['content'] = array();
		
		//인원 및 추가요금
		$this->reVal['content']['people'] = array();
		$this->reVal['content']['people']['title'] = "인원 및 추가요금";
		$this->reVal['content']['people']['inText'] = "기준 ".number_format($info['pprInMin'])."명｜"."최대 ".number_format($info['pprInMax'])."명";
		$this->reVal['content']['people']['infoText'] = array();
		if($info['pprInAdd'] == "1" && $info['pprInMin'] < $info['pprInMax']){
			$this->reVal['content']['people']['infoText'][0] = "기준 인원 초과 시 추가요금이 발생합니다.";
			
			$peopleAddText = "";
			$peopleAddArray = array('pprAdultFlag','pprChildFlag','pprBabyFlag');
			$peopleAddTextArray = array('성인','아동','유아');
			$peopleAddPriceArray = array('pprInAddPrice','pprInAddChild','pprInAddBaby');
			
			for($i=0; $i< count($peopleAddArray); $i++){
				if($i != 0){
					$peopleAddText .= "｜";
				}
				$peopleAddText .= $peopleAddTextArray[$i];
				if($info[$peopleAddArray[$i]] == "1"){
					if($info[$peopleAddPriceArray[$i]] > 0){
						$peopleAddText .= " +".number_format($info[$peopleAddPriceArray[$i]])."원";
					}else{
						$peopleAddText .= " 무료";
					}
				}else{
					$peopleAddText .= " 추가 입실 불가";
				}
			}
			$this->reVal['content']['people']['infoText'][1] = $peopleAddText;
			$this->reVal['content']['people']['infoText'][2] = $info['pprInAddText'];
			
		}else{
			$this->reVal['content']['people']['infoText'][0] = "이 객실은 최대인원까지만 입실 가능합니다.";
		}
		
		//입.퇴실 시간 안내
		$this->reVal['content']['time'] = array();
		$this->reVal['content']['time']['title'] = "입실 · 퇴실 안내";
		
		if($info['ppbTimeFlag'] == "P"){
			$inTime = $info['ppbTimeIn'];
			$outTime = $info['ppbTimeOut'];
		}else{
			$inTime = $info['ppbTimeIn'];
			$outTime = $info['ppbTimeOut'];
			if(isset($info['pprTimeIn'])){
				if($info['pprTimeIn'] != ""){
					$inTime = $info['pprTimeIn'];
				}
			}
			if(isset($info['pprTimeOut'])){
				if($info['pprTimeOut'] != ""){
					$outTime = $info['pprTimeOut'];
				}
			}
		}
		$this->reVal['content']['time']['infoText'] = "입실 ".$inTime." 이후 / 퇴실 ".$outTime." 이전";
		
		//객실구조 및 크기
		$this->reVal['content']['type'] = array();
		$this->reVal['content']['type']['title'] = "객실구조 및 크기";
		if((int)$info['pprSize'] == 0){
			$this->reVal['content']['type']['infoText'] = $roomType;
		}else{
			$this->reVal['content']['type']['infoText'] = $roomType."｜".$info['pprSize']."평(".(round($info['pprSize'] / 0.3025, 0)."m²").")";
		}
		
		//구비시설
		$this->reVal['content']['useful'] = array();
		$this->reVal['content']['useful']['title'] = "구비시설";
		
		$usefulConfig = $this->config->item('pprUseful');
		$useful = "";
		$usefulArray = explode(',', $info['pprUseful']);
		$usefulArray = array_values(array_filter($usefulArray));
		
		for($i=0; $i< count($usefulArray); $i++){
			if(isset($usefulConfig[$usefulArray[$i]])){
				$useful .= ", ".$usefulConfig[$usefulArray[$i]];
			}
		}
		if($useful != ""){
			$useful = substr($useful, 2);
		}
		if($info['pprUsefulText'] != ""){
			if(substr($info['pprUsefulText'],0,1) == ','){
				$useful .= $info['pprUsefulText'];
			}else{
				$useful .= ", ".$info['pprUsefulText'];
			}
		}
		$this->reVal['content']['useful']['infoText'] = $useful;
		
		//객실소개
		$this->reVal['content']['info'] = array();
		$this->reVal['content']['info']['title'] = "객실소개";
		$this->reVal['content']['info']['infoText'] = $this->pension_lib->htmlRemove($info['pprContent']);
		
		if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
			//echo var_dump($this->reVal);
			echo json_encode($this->reVal);
		}else{
			echo json_encode($this->reVal);
		}
	}

	function publicData(){
		checkMethod('get'); // 접근 메서드를 제한

        $mpIdx = $this->input->get('index');
        if(!$mpIdx){
        	$this->error->getError('0006');
		}
        
        $info = $this->info_model->getPensionInfo($mpIdx);
		if(!isset($info['mpIdx'])){
			$this->error->getError('0006');
		}		
		
		$page = $this->input->get('page');
		if(!$page){
			$page = 1;
		}
		$limit = $this->input->get('limit');
		if(!$limit){
			$limit = 20;
		}
		$radius = $this->input->get('radius');
		if(!$radius){
			$radius = 20000;
		}
		$type = $this->input->get('type');
		/*
		 	콘텐츠 타입 코드
		  	관광지 = 12
			문화시설 = 14
			행사/공연/축제 = 15
			여행코스 = 25
			레포츠 = 28
			숙박 = 32
			쇼핑 = 38
			음식점 = 39
		*/
		
		$url = 'http://api.visitkorea.or.kr/openapi/service/rest/KorService/locationBasedList?ServiceKey='.OPENAPI_DATA_KEY;
        $url .= '&numOfRows='.$limit;			// 한 페이지 결과 수
		$url .= '&pageNo='.$page;				// 페이지 번호
		$url .= '&arrange=S';				// (O=제목순, P=조회순, Q=수정일순, R=생성일순, S=거리순)
		$url .= '&listYN=Y';				// Y=목록, N=개수
		$url .= '&mapX='.$info['mpsMapX'];		// X좌표
		$url .= '&mapY='.$info['mpsMapY'];			// Y좌표
		$url .= '&radius='.$radius;			// 반경 (m)
		$url .= '&contentTypeId='.$type;		// 코드
		$url .= '&MobileOS=ETC';			// OS 구분 (IOS (아이폰), AND (안드로이드),WIN (윈도우폰), ETC)
		$url .= '&MobileApp='.urlencode('야놀자펜션');	// 서비스명
		$url .= '&_type=json';
		
		$ch = curl_init();    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = json_decode(curl_exec($ch));
        curl_close($ch);
		
		
		$dataAddr1 = array();
		$dataAddr2 = array();
		$dataMapX = array();
		$dataMapY = array();
		$dataTel = array();
		$dataName = array();
		$dataImage = array();
		$dataEventStart = array();
		$dataEventEnd = array();
		$dataEventPlace = array();
		$dataEventPlaytime = array();
		
		if($result->response->header->resultCode == '0000'){
			$lists = $result->response->body->items;
		
			foreach($lists->item as $lists){
				$subUrl = 'http://api.visitkorea.or.kr/openapi/service/rest/KorService/detailIntro?ServiceKey='.OPENAPI_DATA_KEY;
				$subUrl .= '&contentId='.$lists->contentid;		// 코드
				$subUrl .= '&contentTypeId='.$type;		// 코드
				$subUrl .= '&MobileOS=ETC';			// OS 구분 (IOS (아이폰), AND (안드로이드),WIN (윈도우폰), ETC)
				$subUrl .= '&MobileApp='.urlencode('야놀자펜션');	// 서비스명
				$subUrl .= '&_type=json';
				
				$ch = curl_init();    
		        curl_setopt($ch, CURLOPT_URL, $subUrl);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		        $subResult = json_decode(curl_exec($ch));
		        curl_close($ch);
				
				$subLists = $subResult->response->body->items;
				
				if(isset($lists->addr1)){
					array_push($dataAddr1, $lists->addr1);
				}else{
					array_push($dataAddr1, '');
				}
				
				if(isset($lists->addr2)){
					array_push($dataAddr2, $lists->addr2);
				}else{
					array_push($dataAddr2, '');
				}
				
				if(isset($lists->mapX)){
					array_push($dataMapX, $lists->mapX);
				}else{
					array_push($dataMapX, '');
				}
				
				if(isset($lists->mapY)){
					array_push($dataMapY, $lists->mapY);
				}else{
					array_push($dataMapY, '');
				}
				
				if(isset($lists->tel)){
					array_push($dataTel, $lists->tel);
				}else{
					array_push($dataTel, '');
				}
				
				if(isset($lists->title)){
					array_push($dataName, $lists->title);
				}else{
					array_push($dataName, '');
				}
				
				if(isset($lists->firstimage)){
					array_push($dataImage, $lists->firstimage);
				}else{
					array_push($dataImage, '');
				}
				
				foreach($subLists as $subLists){
					if(isset($subLists->eventstartdate)){
						array_push($dataEventStart, $subLists->eventstartdate);
					}else{
						array_push($dataEventStart, '');
					}
					
					if(isset($subLists->eventenddate)){
						array_push($dataEventEnd, $subLists->eventenddate);
					}else{
						array_push($dataEventEnd, '');
					}
					
					if(isset($subLists->eventplace)){
						array_push($dataEventPlace, $subLists->eventplace);
					}else{
						array_push($dataEventPlace, '');
					}
					
					if(isset($subLists->playtime)){
						array_push($dataEventPlaytime, $subLists->playtime);
					}else{
						array_push($dataEventPlaytime, '');
					}
				}
			}
		}else{
			$this->reVal['status'] = "2";
			$this->reVal['failed_message'] = $result->response->header->resultMsg;
			$this->reVal['lists'] = array();
			echo json_encode($this->reVal);
			exit;
		}

		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		$this->reVal['lists'] = array();
		
		$dataNo = 0;
		for($i=0; $i< count($dataName); $i++){
			if($dataName[$i] == ""){
				continue;
			}
			$this->reVal['lists'][$dataNo]['info'] = array();
			$this->reVal['lists'][$dataNo]['info']['title'] = $dataName[$i];
			$this->reVal['lists'][$dataNo]['info']['tel'] = explode(',',trim($dataTel[$i]));
			$this->reVal['lists'][$dataNo]['info']['image'] = $dataImage[$i];
			
			$this->reVal['lists'][$dataNo]['address'] = array();
			$this->reVal['lists'][$dataNo]['address']['address'] = $dataAddr1[$i]." ".$dataAddr2[$i];
			$this->reVal['lists'][$dataNo]['address']['mapX'] = $dataMapX[$i];
			$this->reVal['lists'][$dataNo]['address']['mapY'] = $dataMapY[$i];
			
			$this->reVal['lists'][$dataNo]['event'] = array();
			$this->reVal['lists'][$dataNo]['event']['startDate'] = date('Y-m-d', strtotime($dataEventStart[$i]));
			$this->reVal['lists'][$dataNo]['event']['endDate'] = date('Y-m-d', strtotime($dataEventEnd[$i]));
			$this->reVal['lists'][$dataNo]['event']['place'] = $dataEventPlace[$i];
			$this->reVal['lists'][$dataNo]['event']['playTime'] = $this->pension_lib->htmlRemove($dataEventPlaytime[$i]);
			
			$dataNo++;
		}
		
		if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
			//echo var_dump($this->reVal);
			echo json_encode($this->reVal);
		}else{
			echo json_encode($this->reVal);
		}
	}

	function report(){
		checkMethod('post'); // 접근 메서드를 제한

        $mpIdx = $this->input->post('index');
		$mbIdx = $this->input->post('userIndex');
		$content = $this->input->post('content');
		
        if(!$mpIdx || !$content || !$mbIdx){
        	$this->error->getError('0006');
		}
		
		$this->info_model->insPensionReport($mpIdx, $mbIdx, $content);
		
		$this->reVal['status'] = "1";
		$this->reVal['failed_message'] = "";
		
		echo json_encode($this->reVal);
	}
		
}
?>