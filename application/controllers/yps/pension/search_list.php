<?php

class Search_list extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->config('yps/_constants');
        $this->load->library('pension_lib');
        $this->load->model('_yps/pension/pension_model');
        $this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
    }
    /*
    function test() {
        $locCode        = urldecode($_REQUEST['locCode']);
        //$themeCode    = $this->pension_lib->paramNummCheck($this->input->get('themeCode'), 0, array("1"=>1,"2"=>1,"3"=>1,"4"=>1));
        $themeCode      = urldecode($_REQUEST['themeCode']);
        $sale           = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['sale']), 0, NULL);
        $search         = urldecode($_REQUEST['search']);   //검색시 이용
        //$orderby      = $this->pension_lib->paramNummCheck($this->input->get('orderby'), 1, NULL);
        $orderby        = urldecode($_REQUEST['orderby']);
        $idxStrings     = urldecode($_REQUEST['idxStrings']);
        $random         = urldecode($_REQUEST['random']);
        $page           = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['page']), 1, NULL);
        $limit          = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['limit']), 20, NULL);
        $latitude       = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['mapY']), NULL, NULL); // 위도
        $longitude      = $this->pension_lib->paramNummCheck(urldecode($_REQUEST['mapX']), NULL, NULL); // 경도
        //$limit = 300;
        $page = 1;
            
        $offset = ($page - 1) * $limit;
        
        if( $locCode ) {
            $getLocSite = $this->pension_model->getLocMapSite($locCode);

            if(isset($getLocSite['pptsIdx'])){
                $getMapX = $getLocSite['pptsMapX'];
                $getMapY = $getLocSite['pptsMapY'];
            }else{
                $getLocNameArray = $this->pension_model->getLocCodeName($locCode);
                $locCodeName = $getLocNameArray['mtName'];
                $mapUrl = "http://openapi.map.naver.com/api/geocode.php?key=?ver=2.0&key=4333cac45625c5f2f10c78b88828a273&query=".urlencode($locCodeName)."&encoding=UTF-8&coord=LatLng";
                $xmlData = file_get_contents($mapUrl);
                $xData = simplexml_load_string($xmlData);
                
                $getMapX = $xData->item->point->x;
                $getMapY = $xData->item->point->y;
            }
        }else{
            $getMapX    = '';
            $getMapY    = '';
        }

        //검색어가 있을경우 검색어를 기준으로 테마코드값을 가져온다
        if( $search ) {
            $result = $this->pension_model->getThemeCode( $search );

            $themeCode = '';
            foreach( $result as $o ) {
                if($themeCode){
                    $themeCode .= ','.$o->mtCode;
                }else{
                    $themeCode .= $o->mtCode;
                }
            }

            //if( empty( $themeCode ) ) $this->error->getError('0005');
        }

        //random 시 제외할 업체 key
        if( $idxStrings != "" || $idxStrings != ","){
            $idxStrings = explode(',', $idxStrings );
        }else
            $idxStrings = array();

        if( $themeCode ) $themeCode = $themeCode;
        $result = $this->pension_model->pensionSearchList(array(
            'code'          => '2.',
            'locCode'       => $locCode,
            'themeCode' => $themeCode,
            'search'        => $search,
            'latitude'      => $latitude,
            'longitude'     => $longitude,
            'orderby'       => $orderby,
            'sale'          => $sale,
            'page'          => $page,
            'limit'         => $limit,
            'offset'        => $offset,
            'random'        => $random,
            'idxStrings'=> $idxStrings
        ));

        $no = 0;

        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['tnt_cnt'] = $result['count']."";
        $ret['mapX']        = $getMapX."";
        $ret['mapY']        = $getMapY."";
        $ret['idxStrings'] = $idxStrings;

        foreach( $result['obj'] as $k => $o ) {
            //주소를 시/구/군 으로 자름
            $addr = @explode(' ',$o->mpsAddr1);
            if( isset( $addr[0] ) && isset( $addr[1] ) ) $addr = $addr[0].' '.$addr[1];
            else $addr = $o->mpsAddr1;
            
            $ret['lists'][$k]["idx"]        = $o->mpIdx;            // 펜션키
            $ret['lists'][$k]["image"]      = 'http://img.yapen.co.kr/pension/basic/'.$o->mpIdx.'/480x0/'.$o->ppbImage;     // 이미지경로
            $ret['lists'][$k]["image_cnt"]  = $this->pension_model->pensionImageCount( $o->mpIdx );
            $ret['lists'][$k]["location"]   = $addr;    // 지역정보
            $ret['lists'][$k]["name"]       = $o->mpsName;      // 펜션명
            $ret['lists'][$k]["content"]    = $this->pension_lib->themeInfo($o->mpsIdx);    // 테마정보
            $ret['lists'][$k]["price"]      = number_format($o->price); // 오늘의 펜션 최저가 요금
            $ret['lists'][$k]["review"]     = $this->basket_model->getPensionBasketCountByMpIdx($o->mpIdx);                 // 리뷰   
            $ret['lists'][$k]["sales"]      = $o->ppbRoomSales;                 // 세일요금
            $ret['lists'][$k]["reserve"]    = $o->ppbReserve;                   // 예약방식
            if($o->ppbGrade >= 10){
                $ret['lists'][$k]["grade"] = "1";                   // 상위노출여부
            }else{
                $ret['lists'][$k]["grade"] = "0";                   // 상위노출여부
            }
            $ret['lists'][$k]["eventFlag"]   = $o->ppbEventFlag;   //이벤트여부
            
            array_push($ret['idxStrings'],$o->mpIdx);
        }

        $ret['idxStrings'] = implode(',', $ret['idxStrings'] );

        //print_r( $ret );

        //$this->output->enable_profiler();
        
        echo json_encode( $ret );
    }
    */
    function index(){
        $locCode        = $this->input->get_post('locCode');
        $themeCode      = $this->input->get_post('themeCode');        
        $search         = $this->input->get_post('search');
        $orderby        = $this->input->get_post('orderby');
        $idxStrings     = $this->input->get_post('idxStrings');
		$todaySale		= $this->input->get_post('todaySale');
		
        $idxStrings     = str_replace("%2C",",",$idxStrings);
        if($locCode != ""){
            $listArray = $this->pension_model->getPensionSearchLists($locCode, $orderby, 'location', $idxStrings, $todaySale); 
        }else if($themeCode != ""){
            $listArray = $this->pension_model->getPensionSearchLists($themeCode, $orderby, 'theme', $idxStrings, $todaySale);
        }else if($search != ""){
            $listArray = $this->pension_model->getPensionSearchLists($search, $orderby, 'text', $idxStrings, $todaySale);
        }else{
            $this->error->getError('0005');
            return;
            exit;
        }
        
        if( $locCode ) {
            $getLocSite = $this->pension_model->getLocMapSite($locCode);
            if(isset($getLocSite['pptsIdx'])){
                $getMapX = $getLocSite['pptsMapX'];
                $getMapY = $getLocSite['pptsMapY'];
            }else{
                $getLocNameArray = $this->pension_model->getLocCodeName($locCode);
                $locCodeName = $getLocNameArray['mtName'];
                $mapUrl = "http://openapi.map.naver.com/api/geocode.php?key=?ver=2.0&key=4333cac45625c5f2f10c78b88828a273&query=".urlencode($locCodeName)."&encoding=UTF-8&coord=LatLng";
                $xmlData = file_get_contents($mapUrl);
                $xData = simplexml_load_string($xmlData);
                
                $getMapX = $xData->item->point->x;
                $getMapY = $xData->item->point->y;
            }
        }else{
            $getMapX    = '';
            $getMapY    = '';
        }
        
        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['tnt_cnt'] = $listArray['count']."";
        $ret['idxStrings'] = $idxStrings;
        $idxArray = "";
        $ret['mapX']        = $getMapX."";
        $ret['mapY']        = $getMapY."";
        
        $i=0;
        foreach($listArray['lists'] as $lists){
            //주소를 시/구/군 으로 자름
            $addressArray = @explode(' ',$lists['mpsAddr1']);
            if( isset( $addressArray[0] ) && isset( $addressArray[1] )){
                $address = $addressArray[0].' '.$addressArray[1];
            }else{
                $address = $lists['mpsAddr1'];
            }
            
            $ret['lists'][$i]["idx"]        = $lists['mpIdx'];
            $ret['lists'][$i]["image"]      = 'http://img.yapen.co.kr/pension/basic/'.$lists['mpIdx'].'/480x0/'.$lists['ppbImage'];     // 이미지경로
            //$ret['lists'][$i]["image_cnt"]  = $this->pension_model->pensionImageCount($lists['mpIdx']);
            $ret['lists'][$i]["image_cnt"]  = "0";
            $ret['lists'][$i]["location"]   = $address;    // 지역정보
            $ret['lists'][$i]["name"]       = $lists['mpsName'];      // 펜션명
            $ret['lists'][$i]["content"]    = "";// $this->pension_lib->themeInfo($o->mpsIdx);    // 테마정보
            if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $lists['ppbReserve'] == "R") ||
            	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
                $ret['lists'][$i]["price"]      = number_format(floor(($lists['price']-($lists['price']*0.02))/10)*10);
                $percent = 100-floor(($lists['price']-($lists['price']*0.02))/$lists['basicPrice']*100);
                $ret['lists'][$i]["sales"]      = $percent."";                 // 세일요금
            }else{
                $ret['lists'][$i]["price"]      = number_format($lists['price']).""; // 오늘의 펜션 최저가 요금
                $ret['lists'][$i]["sales"]      = round(100-($lists['price']/$lists['basicPrice']*100),0)."";                 // 세일요금
            }
            $ret['lists'][$i]["review"]     = $lists['ppbWantCnt'];
            $ret['lists'][$i]["reserve"]    = $lists['ppbReserve'];                   // 예약방식
            if($lists['ppbGrade'] >= 10){
                $ret['lists'][$i]["grade"] = "1";                   // 상위노출여부
            }else{
                $ret['lists'][$i]["grade"] = "0";                   // 상위노출여부
            }
            $ret['lists'][$i]["eventFlag"]   = $lists['ppbEventFlag'];   //이벤트여부
            if($lists['ptsSale'] > 0){
            	$ret['lists'][$i]["todaySale"]   = "Y";   //당일특가 여부
            }else{
            	$ret['lists'][$i]["todaySale"]   = "N";   //당일특가 여부
            }
            $idxArray .= ",".$lists['mpIdx'];
            $i++;
        }
        
        if($ret['idxStrings'] == ""){
            $ret['idxStrings'] = substr($idxArray,1);
        }else{
            $ret['idxStrings'] = $ret['idxStrings'].$idxArray;
        }
        
        echo json_encode( $ret );
    }

    function total(){
        $locCode        = $this->input->get_post('locCode');
        $themeCode      = $this->input->get_post('themeCode');        
        $search         = $this->input->get_post('search');
        $orderby        = $this->input->get_post('orderby');
        $idxStrings     = $this->input->get_post('idxStrings');
        $idxStrings     = str_replace("%2C",",",$idxStrings);
        if($locCode != ""){
            $listArray = $this->pension_model->getPensionSearchLists($locCode, $orderby, 'location', $idxStrings);
        }else if($themeCode != ""){
            $listArray = $this->pension_model->getPensionSearchLists($themeCode, $orderby, 'theme', $idxStrings);
        }else if($search != ""){
            $listArray = $this->pension_model->getPensionSearchLists($search, $orderby, 'text', $idxStrings);
        }else{
            $this->error->getError('0005');
            return;
            exit;
        }
        
        if( $locCode ) {
            $getLocSite = $this->pension_model->getLocMapSite($locCode);
            if(isset($getLocSite['pptsIdx'])){
                $getMapX = $getLocSite['pptsMapX'];
                $getMapY = $getLocSite['pptsMapY'];
            }else{
                $getLocNameArray = $this->pension_model->getLocCodeName($locCode);
                $locCodeName = $getLocNameArray['mtName'];
                $mapUrl = "http://openapi.map.naver.com/api/geocode.php?key=?ver=2.0&key=4333cac45625c5f2f10c78b88828a273&query=".urlencode($locCodeName)."&encoding=UTF-8&coord=LatLng";
                $xmlData = file_get_contents($mapUrl);
                $xData = simplexml_load_string($xmlData);
                
                $getMapX = $xData->item->point->x;
                $getMapY = $xData->item->point->y;
            }
        }else{
            $getMapX    = '';
            $getMapY    = '';
        }
        
        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['tnt_cnt'] = $listArray['count']."";
        $ret['idxStrings'] = $idxStrings;
        $idxArray = "";
        $ret['mapX']        = $getMapX."";
        $ret['mapY']        = $getMapY."";
        
        $i=0;
        foreach($listArray['lists'] as $lists){
            //주소를 시/구/군 으로 자름
            $addressArray = @explode(' ',$lists['mpsAddr1']);
            if( isset( $addressArray[0] ) && isset( $addressArray[1] )){
                $address = $addressArray[0].' '.$addressArray[1];
            }else{
                $address = $lists['mpsAddr1'];
            }
            
            $ret['lists'][$i]["idx"]        = $lists['mpIdx'];
            $ret['lists'][$i]["image"]      = 'http://img.yapen.co.kr/pension/basic/'.$lists['mpIdx'].'/480x0/'.$lists['ppbImage'];     // 이미지경로
            $ret['lists'][$i]["image_cnt"]  = '0';
            $ret['lists'][$i]["location"]   = $address;    // 지역정보
            $ret['lists'][$i]["name"]       = $lists['mpsName'];      // 펜션명
            $ret['lists'][$i]["content"]    = "";// $this->pension_lib->themeInfo($o->mpsIdx);    // 테마정보
            $ret['lists'][$i]["price"]      = number_format($lists['price']).""; // 오늘의 펜션 최저가 요금
            $ret['lists'][$i]["review"]     = $lists['ppbWantCnt']; //$this->basket_model->getPensionBasketCountByMpIdx($o->mpIdx);                 // 리뷰   
            $ret['lists'][$i]["sales"]      = $lists['percent']."";                 // 세일요금
            $ret['lists'][$i]["reserve"]    = $lists['ppbReserve'];                   // 예약방식
            if($lists['ppbGrade'] >= 10){
                $ret['lists'][$i]["grade"] = "1";                   // 상위노출여부
            }else{
                $ret['lists'][$i]["grade"] = "0";                   // 상위노출여부
            }
            $ret['lists'][$i]["eventFlag"]   = $lists['ppbEventFlag'];   //이벤트여부
            
            $idxArray .= ",".$lists['mpIdx'];
            $i++;
        }
        
        if($ret['idxStrings'] == ""){
            $ret['idxStrings'] = substr($idxArray,1);
        }else{
            $ret['idxStrings'] = $ret['idxStrings'].$idxArray;
        }
        
        echo json_encode( $ret );
    }
}
?>