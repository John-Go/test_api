<?php
class Pension_read extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->config('yps/_constants');
        $this->load->library('pension_lib');
        $this->load->model('_yps/pension/pension_model');
        $this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
    }
    
    function index(){
        checkMethod('get'); // 접근 메서드를 제한

        $mpIdx = $this->input->get('idx');
        if( !$mpIdx ) $this->error->getError('0006'); // Key가 없을경우

        $info = $this->pension_model->getPensionInfo($mpIdx); // 펜션정보
        
        if(!isset($info['mpIdx'])){
            $this->error->getError('0005'); // 정보가 없을경우
        }
        
        if($info['ppbSubPension']){
            $connectName = $this->pension_model->getSubPensionLists(explode("|",$info['ppbSubPension']));
        }else{
            $connectName = "";
        }

        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['mapX']    = $info['mpsMapX'];
        $ret['mapY']    = $info['mpsMapY'];
        $ret['info']['name'] = $info['mpsName'];             // 펜션명
        $ret['connectName'] = $connectName;
        $ret['revEventText'] = "";
        /* promise Start */
        if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $info['ppbReserve'] == "R") ||
        	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
            $ret['promiseImage'] = "http://img.yapen.co.kr/pension/event/winterEvent/2016-11-25/appPensionDetail.png";
            $ret['promiseLink'] = "http://web.yapen.co.kr/yps/yapen/winterSale";
            $ret['promiseTitle'] = "겨울 특별 이벤트";
            $ret['promiseImageWidth'] = 750;
            $ret['promiseImageHeight'] = 217;
            $ret['revEventText'] = rawurlencode("최저가＋2% 특가할인!");
        }else if(date('Ym') == "201611"){
			$ret['promiseImage'] = "http://img.yapen.co.kr/pension/event/201611EventPensionBanner.png";
            $ret['promiseLink'] = "http://web.yapen.co.kr/yps/yapen/newYearEvent";
            $ret['promiseTitle'] = "단풍하나 추억하나";
            $ret['promiseImageWidth'] = 750;
            $ret['promiseImageHeight'] = 217;
			$ret['revEventText'] = rawurlencode("마일리지 혜택으로 예약하기!");
		}else{
            $ret['promiseImage'] = "http://img.yapen.co.kr/pension/event/banner_mobile_promise.png";
            $ret['promiseLink'] = "http://web.yapen.co.kr/event/yapenPromise";
            $ret['promiseTitle'] = "고객과의 약속";
            $ret['promiseImageWidth'] = 1080;
            $ret['promiseImageHeight'] = 312;
        }
		
        
        /* promise End */
        $ret['info']['review'] = $info['ppbWantCnt'];
        if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $info['ppbReserve'] == "R") ||
        	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
            $ret['info']['basicPrice'] = number_format($info['basicPrice']);            // 이용요금
            $ret['info']['price'] = number_format(floor(($info['price']-($info['price']*0.02))/10)*10)."";
            $percent = 100-floor(($info['price']-($info['price']*0.02))/$info['basicPrice']*100);
            $ret['info']['percent'] = $percent;
        }else{
            $ret['info']['price'] = number_format($info['price'])."";            // 이용요금
            $ret['info']['basicPrice'] = number_format($info['basicPrice']);            // 이용요금
            $ret['info']['percent'] = $info['percent'];            // 이용요금
        }
        
        if($info['peIdx'] != ""){
            $ret['info']['event_key'] = $info['peIdx'];      // 이벤트키
            $ret['info']['event_title'] = $info['peTitle'];    // 이벤트명
            if(substr($info['peEndDate'],0,10) == "9999-12-31"){
                $ret['info']['event_date'] = ""; // 이벤트기간
            }else{
                $ret['info']['event_date'] = substr($info['peStartDate'],0,10)."~".substr($info['peEndDate'],0,10); // 이벤트기간
            }
        }else{
            $ret['info']['event_key'] = "";
            $ret['info']['event_title'] = "";
            $ret['info']['event_date'] = "";
        }
        
        if(!$info['theme']){
            $themeText = "";
        }else{
            $themeArray = explode(",", $info['theme']);
            $themeText = "";
            for($i=0; $i< count($themeArray); $i++){
                $themeText .= ", ".$themeArray[$i];
                if($i == 6){
                    break;
                }
            }
            
            if($themeText != ""){
                $themeText = substr($themeText, 2);
            }
        }

        $ret['info']['service'] = $themeText;    // 부대시설 및 서비스        
        $ret['info']['address'] = $info['mpsAddr1'] . ' ' . $info['mpsAddr2'];
        $ret['info']['reservation'] = $info['ppbReserve'];                   // 예약여부
        if(!$info['mpsTelService']){
            $ret['info']['tel_service'] = "16444816";                    // 서비스번호
        }else{
            $ret['info']['tel_service'] = $info['mpsTelService'];                    // 서비스번호
        }
        $ret['info']['eventFlag'] = $info['ppbEventFlag'];        
        $ret['info']['pensionTel'] = trim(str_replace("-","",$info['ppbTel1']));
        
        if(isset($info['ppuPullFlag'])){
            if($info['ppuPullFlag'] == "1"){
                $ret['info']['poolFlag'] = "Y";
            }else{
                $ret['info']['poolFlag'] = "N";
            }
        }else{
            $ret['info']['poolFlag'] = "N";
        }
                
        $photoLists = $this->pension_model->pensionAllPhotoLists($mpIdx);
        $ret['info']["images"]['count'] = count($photoLists);
        $ret['info']['images']['lists'] = array();
        if(count($photoLists) > 0){
            $i = 0;
            foreach($photoLists as $photoLists){
                if($photoLists['photoType'] == "E"){
                    $ret['info']['images']['lists'][$i]["image"] = 'http://img.yapen.co.kr/pension/etc/'.$mpIdx.'/800x0/'.$photoLists['imageUrl'];
                }else{
                    $ret['info']['images']['lists'][$i]["image"] = 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$photoLists['imageUrl'];
                }
                $i++;
            }
        }

        $tipListResult = $this->pension_model->tipLists($mpIdx, 0, 2); // 팁
        $ret['info']["tip"]['count'] = $tipListResult['count'];

        $tipNum = 0;
        foreach ($tipListResult['query'] as $row) {
            $ret['info']["tip"]['lists'][$tipNum]['tip_idx'] = $row['ptIdx'];
            $ret['info']["tip"]['lists'][$tipNum]['tip_name'] = $row['ptName'];
            $ret['info']["tip"]['lists'][$tipNum]['tip_date'] = $row['ptRegDate'];
            $ret['info']["tip"]['lists'][$tipNum]['tip_content'] = urldecode($row['ptContent']);
            $tipNum++;
        }
        echo json_encode( $ret );
    }

    function before() {
        checkMethod('get'); // 접근 메서드를 제한

        $idx = $this->input->get('idx');
        if( !$idx ) $this->error->getError('0006'); // Key가 없을경우

        $infoResult = $this->pension_model->pensionGetInfo($idx); // 펜션정보

        if( !$infoResult->num_rows() )
            $this->error->getError('0005'); // 정보가 없을경우

        $infoRow = $infoResult->row_array();
        
        $pensionPriceInfo = $this->pension_model->pensionMinPrice($idx);

        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['mapX']    = $infoRow['mpsMapX'];
        $ret['mapY']    = $infoRow['mpsMapY'];
        $ret['info']['name'] = $infoRow['mpsName'];             // 펜션명
        $ret['info']['review'] = $this->basket_model->getPensionBasketCountByMpIdx($idx);                           // 가보고싶어요
        $ret['info']['price'] = $pensionPriceInfo->minPrice;            // 이용요금
        $ret['info']['event_key'] = (is_null($infoRow['peIdx'])) ? '' : $infoRow['peIdx'];      // 이벤트키
        $ret['info']['event_title'] = ($infoRow['peTitle']);    // 이벤트명
        if(substr($infoRow['peEndDate'],0,10) == "9999-12-31"){
            $ret['info']['event_date'] = ""; // 이벤트기간
        }else{
            $ret['info']['event_date'] = substr($infoRow['peStartDate'],0,10)."~".substr($infoRow['peEndDate'],0,10); // 이벤트기간
        }
        
        $ret['info']['service'] = $this->pension_lib->themeInfo($infoRow['mpsIdx']);    // 부대시설 및 서비스
        
        $ret['info']['address'] = $infoRow['mpsAddr1'] . ' ' . $infoRow['mpsAddr2']."\n".$infoRow['mpsAddr1New'];
        
        
        $ret['info']['reservation'] = $infoRow['ppbReserve'];                   // 예약여부 
        $ret['info']['tel_service'] = $infoRow['mpsTelService'];                    // 서비스번호 
        $ret['info']['eventFlag'] = $infoRow['ppbEventFlag'];        
        $ret['info']['pensionTel'] = trim(str_replace("-","",$infoRow['ppbTel1']));
                
        $photoLists = $this->pension_model->pensionAllPhotoLists($idx);
        $ret['info']["images"]['count'] = count($photoLists);
        $ret['info']['images']['lists'] = array();
        if(count($photoLists) > 0){
            $i = 0;
            foreach($photoLists as $photoLists){
                if($photoLists['photoType'] == "E"){
                    $ret['info']['images']['lists'][$i]["image"] = 'http://img.yapen.co.kr/pension/etc/'.$idx.'/800x0/'.$photoLists['imageUrl'];                    
                }else{
                    $ret['info']['images']['lists'][$i]["image"] = 'http://img.yapen.co.kr/pension/room/'.$idx.'/800x0/'.$photoLists['imageUrl'];
                }
                $i++;
            }
        }

        // ********************************************* 팁정보 **************************************************
        $tipListResult = $this->pension_model->tipLists($idx, 0, 2); // 팁
        $ret['info']["tip"]['count'] = $tipListResult['count'];

        $tipNum = 0;
        foreach ($tipListResult['query'] as $row) {
            $ret['info']["tip"]['lists'][$tipNum]['tip_idx'] = $row['ptIdx'];
            $ret['info']["tip"]['lists'][$tipNum]['tip_name'] = $row['ptName'];
            $ret['info']["tip"]['lists'][$tipNum]['tip_date'] = $row['ptRegDate'];
            $ret['info']["tip"]['lists'][$tipNum]['tip_content'] = urldecode($row['ptContent']);
            $tipNum++;
        }
        // ********************************************* 팁정보 **************************************************
        //print_re( $ret );
        
        /* 풀빌라 유무 Start */
        $poolFlag = $this->pension_model->getPoolFlag($idx);
        $ret['info']['poolFlag'] = $poolFlag;
        /* 풀빌라 유무 End */
        echo json_encode( $ret );
        //$this->output->enable_profiler();
    }
}
?>