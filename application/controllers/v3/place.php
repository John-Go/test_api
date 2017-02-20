<?php

class Place extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('pension_lib');
        $this->load->model('v3/reservation_model');
        $this->load->model('v3/pension_model');
        $this->pathArray = array('region','aroundMe','keyword');
        
        if(!in_array($this->uri->segment(3),$this->pathArray)){
            $this->info();
            exit;
        }
    }
    
    function info(){
        $mpIdx = $this->uri->segment(3);
        if(!is_numeric($mpIdx)){
            show_404();
            return;
        }
        
        $info = $this->pension_model->getPensionInfo($mpIdx); // 펜션정보
        
        if(!isset($info['mpIdx'])){
            show_404();
            return;
        }
        
        $ret = array();
        
        if($this->uri->segment(4) == "roomType" && $this->uri->segment(5) == "photos"){
            $lists = $this->reservation_model->getRoomLists($mpIdx);
            
            if(count($lists) > 0){
                $i=0;
                foreach($lists as $lists){
                    $roomArray = array();
                    $roomArray['roomTypeNo'] = (int)$lists['pprIdx'];
                    $roomArray['roomTypeName'] = $lists['pprName'];                    
                    $roomImages = $this->pension_model->pensionRoomImageLists($lists['pprIdx'],0,10);
                    $roomArray['roomPhotosCount'] = $roomImages['count'];
                    $photoArray = array();
                    foreach($roomImages['query'] as $j => $o ) {
                        array_push($photoArray, 'http://img.yapen.co.kr/pension/room/'.$ptIdx.'/800x0/'.$o['pprpFileName']);
                    }
                    $roomArray['roomImageList'] = $photoArray;                    
                    $ret['list'][$i] = $roomArray;
                    $i++;
                }
            }
            
        }else{
            $revDate = urldecode($_REQUEST['checkinDate']);
            if(!$searchDate){
                $searchDate = date('Y-m-d');
            }
            $searchEndDate = urldecode($_REQUEST['checkoutDate']);
            if(!$searchEndDate){
                $searchEndDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+1, date('Y')));
            }
            $revDay = round(abs(strtotime($searchEndDate)-strtotime($searchDate))/86400)+1;
            
            $roomLists = $this->reservation_model->getPensionRoomLists($mpIdx, $revDate, $revDay);
            $photoLists = $this->pension_model->pensionAllPhotoLists($mpIdx);
            
            $pensionPhoto = array();
            if(count($photoLists) > 0){
                foreach($photoLists as $photoLists){
                    if($photoLists['photoType'] == "E"){
                        array_push($pensionPhoto, 'http://img.yapen.co.kr/pension/etc/'.$mpIdx.'/800x0/'.$photoLists['imageUrl']);
                    }else{
                        array_push($pensionPhoto, 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$photoLists['imageUrl']);
                    }
                }
            }
            
            if(count($roomLists) == 0){
                $roomLists = array();
            }
            
            
            $ret['bookmarkType'] = 0;
            $ret['no'] = (int)$mpIdx;
            $ret['name'] = $info['mpsName'];
            $ret['address'] = $info['mpsAddr1']." ".$info['mpsAddr2'];
            $ret['latitude'] = $info['mpsMapY'];
            $ret['longitude'] = $info['mpsMapX'];
            $ret['contentsType'] = 3;
            $ret['reviewStar'] = 0;
            $ret['reviewCount'] = 0;
            $ret['selectedCount'] = 0;
            $ret['bestPrice'] = 0;
            $ret['franchiseType'] = 0;
            $ret['partnershipType'] = 0;
            $ret['balloonZoneType'] = 0;
            $ret['bestpick'] = 0;
            $ret['beskprice'] = 0;
            $ret['iot'] = 0;
            $ret['myRoom'] = 0;
            $ret['partnerNo'] = 0;
            $ret['areaPropertyNo'] = 0;
            $ret['tel'] = "1644-4816";
            $ret['savePercent'] = "";
            $ret['useTypeList'] = array();
            
            for($i=0; $i< count($roomLists); $i++){
                $ret['roomTypeList'][$i]['roomTypeNo'] = $roomLists[$i]['pprIdx'];
                $ret['roomTypeList'][$i]['roomTypeName'] = $roomLists[$i]['pprIdx'];
                $ret['roomTypeList'][$i]['mykitYN'] = FALSE;
                $ret['roomTypeList'][$i]['myRoom'] = 0;
                $ret['roomTypeList'][$i]['roomImage'] = 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$roomLists['pprpFileName'];
                $ret['roomTypeList'][$i]['roomImageThumb'] = 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$roomLists['pprpFileName'];
                $ret['roomTypeList'][$i]['rentSoldout'] = 0;
                $ret['roomTypeList'][$i]['staySoldout'] = 0;
                $ret['roomTypeList'][$i]['allDaySoldout'] = 0;
                $ret['roomTypeList'][$i]['daysSoldout'] = 0;
                $ret['roomTypeList'][$i]['shortDaysSoldout'] = 0;
                $ret['roomTypeList'][$i]['priceRent'] = 0;
                $ret['roomTypeList'][$i]['priceRentDiscount'] = 0;
                $ret['roomTypeList'][$i]['priceStay'] = (int)round($lists['basicPrice']/$searchDateNum,0);
                $ret['roomTypeList'][$i]['priceStayDiscount'] = (int)round(($lists['basicPrice']-$lists['price'])/$searchDateNum,0);
                $ret['roomTypeList'][$i]['priceAllDay'] = 0;
                $ret['roomTypeList'][$i]['priceAllDayDiscount'] = 0;
                $ret['roomTypeList'][$i]['priceDays'] = 0;
                $ret['roomTypeList'][$i]['priceDaysDiscount'] = 0;
                $ret['roomTypeList'][$i]['priceShortDays'] = 0;
                $ret['roomTypeList'][$i]['priceShortDaysDiscount'] = 0;
                $ret['roomTypeList'][$i]['roomBenefitList'] = array();
                $ret['roomTypeList'][$i]['roomEventList'] = array();
            }
            
            $ret['photoList'] = $pensionPhoto;
            $ret['themeList'] = array();
            $ret['reviewList'] = array();
            $ret['stayAuthList'] = array();
            $ret['guideList'] = array();
            $ret['benefit'] = "";
            $ret['notice'] = "최대 인원이 2명인 커플 객실은 영, 유아 동반 입실이 되지 않습니다.\n이용요금은 기준인원에 대한 요금이며, 예약 시 신청한 인원만 입실 가능합니다.\n픽업 가능한 펜션은 이용 전 반드시 펜션 업주와 통화 후 이용 바랍니다.\n보호자 동반 없는 미성년자는 펜션을 이용할 수 없습니다.\사전 동의 없이 애완동물의 입실은 되지 않습니다.";
            $ret['recommendList'] = array();
        }
        
        

        echo json_encode($ret);
        
        return;
    }
    
    function region() {
        $ret = array();
        $ret['countType'] = 9999;
        
        $page = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['page']), 1, NULL);
        $limit = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['limit']), 20, NULL);
        
        $keyword = urldecode($_REQUEST['keyword']);
        
        $locLists = $this->reservation_model->getLocationCode($keyword);
        
        $searchLoc = array();
        if(count($locLists) > 0){
            foreach($locLists as $locLists){
                array_push($searchLoc, $locLists['mtCode']);
            }
        }else{
            echo json_encode( $ret );
            return;
        }
        
        $searchDate = urldecode($_REQUEST['checkinDate']);
        if(!$searchDate){
            $searchDate = date('Y-m-d');
        }
        $searchEndDate = urldecode($_REQUEST['checkoutDate']);
        if(!$searchEndDate){
            $searchEndDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+1, date('Y')));
        }
        $searchDateNum = round(abs(strtotime($searchEndDate)-strtotime($searchDate))/86400)+1;
        $searchPriceMin = urldecode($_REQUEST['minPrice']);       // 최저가
        if(!$searchPriceMin){
            $searchPriceMin = 0;
        }
        $searchPriceMax = urldecode($_REQUEST['maxPrice']);       // 최고가        
        $mapX = urlencode($_REQUEST['longitude']);
        $mapY = urlencode($_REQUEST['latitude']);
        
        $offset = ($page - 1) * $limit;
        
        $schPeople = 1;
        $result = $this->reservation_model->getPensionLists($searchLoc, $searchDate, $searchDateNum, $schPeople, $searchPriceMin, $searchPriceMax, $mapX, $mapY, $limit, $offset);
        
        $no = 0;
        
        foreach ($result['lists'] as $lists) {
            $ret['list'][$no]['no'] = (int)$lists['mpIdx'];
            $ret['list'][$no]['name'] = $lists['mpsName'];
            $ret['list'][$no]['address'] = $lists['mpsAddr1'];
            $ret['list'][$no]['addressDetail'] = $lists['mpsAddr2'];
            $ret['list'][$no]['address2'] = $lists['mpsAddr1New'];
            $ret['list'][$no]['latitude'] = (float)$lists['mpsMapY'];
            $ret['list'][$no]['longitude'] = (float)$lists['mpsMapX'];
            $ret['list'][$no]['franchiseType'] = 0;
            $ret['list'][$no]['contentsType'] = 3;
            $ret['list'][$no]['bestPrice'] = 0;
            $ret['list'][$no]['authZoneType'] = 0;
            $ret['list'][$no]['myRoom'] = 0;
            $ret['list'][$no]['iot'] = 0;
            $ret['list'][$no]['bestPick'] = 0;
            $ret['list'][$no]['distance'] = number_format($lists['distance'],1)."km";
            $ret['list'][$no]['thumb'] = 'http://img.yapen.co.kr/pension/etc/'.$lists['mpIdx'].'/'.$lists['ppbImage'];
            $ret['list'][$no]['location'] = "";
            $ret['list'][$no]['rentOpen'] = 0;
            $ret['list'][$no]['stayOpen'] = 1;
            $ret['list'][$no]['allDayOpen'] = 0;
            $ret['list'][$no]['daysOpen'] = 0;
            $ret['list'][$no]['rentSoldout'] = 0;
            $ret['list'][$no]['staySoldout'] = 0;
            $ret['list'][$no]['allDaySoldout'] = 0;
            $ret['list'][$no]['daysSoldout'] = 0;
            $ret['list'][$no]['shortDaysSoldout'] = 0;
            $ret['list'][$no]['priceStay'] = (int)round($lists['basicPrice']/$searchDateNum,0);
            $ret['list'][$no]['priceStayDiscount'] = (int)round(($lists['basicPrice']-$lists['price'])/$searchDateNum,0);
            $ret['list'][$no]['priceRent'] = 0;
            $ret['list'][$no]['priceRentDiscount'] = 0;
            $ret['list'][$no]['priceAllDay'] = 0;
            $ret['list'][$no]['priceAllDayDiscount'] = 0;
            $ret['list'][$no]['priceDays'] = 0;
            $ret['list'][$no]['priceDaysDiscount'] = 0;
            $ret['list'][$no]['priceShortDays'] = 0;
            $ret['list'][$no]['savePointPer'] = 0;
            $ret['list'][$no]['benefitList'] = array();
            
            $no++;
        }
        
        echo json_encode( $ret );
    }

    function aroundMe() {
        $ret = array();
        $ret['countType'] = 9999;
        
        $page = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['page']), 1, NULL);
        $limit = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['limit']), 20, NULL);
        
        $keyword = urldecode($_REQUEST['keyword']);
        
        $locLists = $this->reservation_model->getLocationCode($keyword);
        
        $searchLoc = array();
        if(count($locLists) > 0){
            foreach($locLists as $locLists){
                array_push($searchLoc, $locLists['mtCode']);
            }
        }else{
            echo json_encode( $ret );
            return;
        }
        
        $searchDate = urldecode($_REQUEST['checkinDate']);
        if(!$searchDate){
            $searchDate = date('Y-m-d');
        }
        $searchEndDate = urldecode($_REQUEST['checkoutDate']);
        if(!$searchEndDate){
            $searchEndDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+1, date('Y')));
        }
        $searchDateNum = round(abs(strtotime($searchEndDate)-strtotime($searchDate))/86400);
        $searchPriceMin = urldecode($_REQUEST['minPrice']);       // 최저가
        if(!$searchPriceMin){
            $searchPriceMin = 0;
        }
        $searchPriceMax = urldecode($_REQUEST['maxPrice']);       // 최고가        
        $mapX = urlencode($_REQUEST['longitude']);
        $mapY = urlencode($_REQUEST['latitude']);
        
        $offset = ($page - 1) * $limit;
        
        $schPeople = 1;
        $result = $this->reservation_model->getAroundPensionLists($searchLoc, $searchDate, $searchDateNum, $schPeople, $searchPriceMin, $searchPriceMax, $mapX, $mapY, $limit, $offset);
        
        $no = 0;
        
        foreach ($result['lists'] as $lists) {
            $ret['list'][$no]['no'] = (int)$lists['mpIdx'];
            $ret['list'][$no]['name'] = $lists['mpsName'];
            $ret['list'][$no]['address'] = $lists['mpsAddr1'];
            $ret['list'][$no]['addressDetail'] = $lists['mpsAddr2'];
            $ret['list'][$no]['address2'] = $lists['mpsAddr1New'];
            $ret['list'][$no]['latitude'] = (float)$lists['mpsMapY'];
            $ret['list'][$no]['longitude'] = (float)$lists['mpsMapX'];
            $ret['list'][$no]['franchiseType'] = 0;
            $ret['list'][$no]['contentsType'] = 3;
            $ret['list'][$no]['bestPrice'] = 0;
            $ret['list'][$no]['authZoneType'] = 0;
            $ret['list'][$no]['myRoom'] = 0;
            $ret['list'][$no]['iot'] = 0;
            $ret['list'][$no]['bestPick'] = 0;
            $ret['list'][$no]['distance'] = number_format($lists['distance'],1)."km";
            $ret['list'][$no]['thumb'] = 'http://img.yapen.co.kr/pension/etc/'.$lists['mpIdx'].'/'.$lists['ppbImage'];
            $ret['list'][$no]['location'] = "";
            $ret['list'][$no]['rentOpen'] = 0;
            $ret['list'][$no]['stayOpen'] = 1;
            $ret['list'][$no]['allDayOpen'] = 0;
            $ret['list'][$no]['daysOpen'] = 0;
            $ret['list'][$no]['rentSoldout'] = 0;
            $ret['list'][$no]['staySoldout'] = 0;
            $ret['list'][$no]['allDaySoldout'] = 0;
            $ret['list'][$no]['daysSoldout'] = 0;
            $ret['list'][$no]['shortDaysSoldout'] = 0;
            $ret['list'][$no]['priceStay'] = (int)round($lists['basicPrice']/$searchDateNum,0);
            $ret['list'][$no]['priceStayDiscount'] = (int)round(($lists['basicPrice']-$lists['price'])/$searchDateNum,0);
            $ret['list'][$no]['priceRent'] = 0;
            $ret['list'][$no]['priceRentDiscount'] = 0;
            $ret['list'][$no]['priceAllDay'] = 0;
            $ret['list'][$no]['priceAllDayDiscount'] = 0;
            $ret['list'][$no]['priceDays'] = 0;
            $ret['list'][$no]['priceDaysDiscount'] = 0;
            $ret['list'][$no]['priceShortDays'] = 0;
            $ret['list'][$no]['savePointPer'] = 0;
            $ret['list'][$no]['benefitList'] = array();
            
            $no++;
        }
        
        echo json_encode( $ret );
    }

    function keyword() {
        $ret = array();
        $ret['countType'] = 9999;
        
        $page = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['page']), 1, NULL);
        $limit = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['limit']), 20, NULL);
        
        $keyword = urldecode($_REQUEST['keyword']);
        
        $searchDate = urldecode($_REQUEST['checkinDate']);
        if(!$searchDate){
            $searchDate = date('Y-m-d');
        }
        $searchEndDate = urldecode($_REQUEST['checkoutDate']);
        if(!$searchEndDate){
            $searchEndDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+1, date('Y')));
        }
        $searchDateNum = round(abs(strtotime($searchEndDate)-strtotime($searchDate))/86400);
        $searchPriceMin = urldecode($_REQUEST['minPrice']);       // 최저가
        if(!$searchPriceMin){
            $searchPriceMin = 0;
        }
        $searchPriceMax = urldecode($_REQUEST['maxPrice']);       // 최고가        
        $mapX = urlencode($_REQUEST['longitude']);
        $mapY = urlencode($_REQUEST['latitude']);
        
        $offset = ($page - 1) * $limit;
        
        $schPeople = 1;
        $result = $this->reservation_model->getKeywordPensionLists($keyword, $searchDate, $searchDateNum, $schPeople, $searchPriceMin, $searchPriceMax, $mapX, $mapY, $limit, $offset);
        
        $no = 0;
        
        foreach ($result['lists'] as $lists) {
            $ret['list'][$no]['no'] = (int)$lists['mpIdx'];
            $ret['list'][$no]['name'] = $lists['mpsName'];
            $ret['list'][$no]['address'] = $lists['mpsAddr1'];
            $ret['list'][$no]['addressDetail'] = $lists['mpsAddr2'];
            $ret['list'][$no]['address2'] = $lists['mpsAddr1New'];
            $ret['list'][$no]['latitude'] = (float)$lists['mpsMapY'];
            $ret['list'][$no]['longitude'] = (float)$lists['mpsMapX'];
            $ret['list'][$no]['franchiseType'] = 0;
            $ret['list'][$no]['contentsType'] = 3;
            $ret['list'][$no]['bestPrice'] = 0;
            $ret['list'][$no]['authZoneType'] = 0;
            $ret['list'][$no]['myRoom'] = 0;
            $ret['list'][$no]['iot'] = 0;
            $ret['list'][$no]['bestPick'] = 0;
            $ret['list'][$no]['distance'] = number_format($lists['distance'],1)."km";
            $ret['list'][$no]['thumb'] = 'http://img.yapen.co.kr/pension/etc/'.$lists['mpIdx'].'/'.$lists['ppbImage'];
            $ret['list'][$no]['location'] = "";
            $ret['list'][$no]['rentOpen'] = 0;
            $ret['list'][$no]['stayOpen'] = 1;
            $ret['list'][$no]['allDayOpen'] = 0;
            $ret['list'][$no]['daysOpen'] = 0;
            $ret['list'][$no]['rentSoldout'] = 0;
            $ret['list'][$no]['staySoldout'] = 0;
            $ret['list'][$no]['allDaySoldout'] = 0;
            $ret['list'][$no]['daysSoldout'] = 0;
            $ret['list'][$no]['shortDaysSoldout'] = 0;
            $ret['list'][$no]['priceStay'] = (int)round($lists['basicPrice']/$searchDateNum,0);
            $ret['list'][$no]['priceStayDiscount'] = (int)round(($lists['basicPrice']-$lists['price'])/$searchDateNum,0);
            $ret['list'][$no]['priceRent'] = 0;
            $ret['list'][$no]['priceRentDiscount'] = 0;
            $ret['list'][$no]['priceAllDay'] = 0;
            $ret['list'][$no]['priceAllDayDiscount'] = 0;
            $ret['list'][$no]['priceDays'] = 0;
            $ret['list'][$no]['priceDaysDiscount'] = 0;
            $ret['list'][$no]['priceShortDays'] = 0;
            $ret['list'][$no]['savePointPer'] = 0;
            $ret['list'][$no]['benefitList'] = array();
            
            $no++;
        }
        
        echo json_encode( $ret );
    }
}
        