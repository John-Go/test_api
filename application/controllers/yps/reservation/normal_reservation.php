<?php

class Normal_reservation extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->config('yps/_constants');
        $this->load->library('pension_lib');
        // $this->load->model('_yps/pension/test_pension_model','pension_model');   // test용도
        $this->load->model('_yps/pension/pension_model','pension_model');
        $this->load->model(YANOLJA_PENSION_MODEL_PATH.'/basket_model');
    }
    
    function index(){
        $locCode        = $this->input->get_post('locCode');
        $themeCode      = $this->input->get_post('themeCode');        
        $search         = $this->input->get_post('search');
        $orderby        = $this->input->get_post('orderby');
        $idxStrings     = $this->input->get_post('idxStrings');
        $idxStrings     = str_replace("%2C",",",$idxStrings);
        
        $listArray = $this->pension_model->getPensionNormalSearchLists($locCode, $orderby, 'location', $idxStrings);        
        
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

    function basic() {
        
        $locCode        = $_REQUEST['locCode'];
        $sale_data = $_REQUEST['sale'];
        $sale           = $this->pension_lib->paramNummCheck($sale_data, 0, NULL);
        $orderby        = $_REQUEST['orderby'];
        $idxStrings     = urldecode($_REQUEST['idxStrings']);
        $random         = $_REQUEST['random'];
        $page_data      = $_REQUEST['page'];
        $page           = $this->pension_lib->paramNummCheck($page_data, 1, NULL);
        $limit_data     = $_REQUEST['limit'];
        $limit             = $this->pension_lib->paramNummCheck($limit_data, 25, NULL);
        
        $offset = ($page - 1) * $limit;

        //지역검색일때 지역 좌표값을 가져온다
        if( $locCode ) {
            $getLocName = $this->pension_model->getLocName($locCode);

            //naver API 는 주소체계에서 검색하지 지명에서 검색하는것이 아니므로 거제도란 주소는 없으므로 거제시로 예외처리
            if( $getLocName->mtName == '거제도' ) $getLocName->mtName = '거제시';
            elseif( $getLocName->mtName == '경주' ) $getLocName->mtName = '경주시';

            $mapXmlData = simplexml_load_file( "http://openapi.map.naver.com/api/geocode.php?key=439ae3d16767ead8d7e2fa556f471123&encoding=utf-8&coord=LatLng&query=".$getLocName->mtName );

            if( $mapXmlData->total > 0 ){
                $item       = $mapXmlData->item;
                $point      = $item[0]->point;
                $getMapX    = $point->x;
                $getMapY    = $point->y;
            }else{
                $getMapX    = '';
                $getMapY    = '';
            }
        }
        else
        {
            $getMapX    = '';
            $getMapY    = '';
        }

        //random 시 제외할 업체 key
        if( $random > 0 && isset($idxStrings) ){
            $idxStrings = explode(',', $idxStrings );
        }else
            $idxStrings = array();
        
        $result = $this->pension_model->getNormalList(array(
            'code'          => '2.',
            'locCode'       => $locCode,
            'orderby'       => $orderby,
            'sale'          => $sale,
            'page'          => $page,
            'limit'         => $limit,
            'offset'        => $offset,
            'random'        => $random,
            'idxStrings'    => $idxStrings
        ));
        //echo var_dump($result);
        $no = 0;

        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['tnt_cnt'] = $result['count']."";
        $ret['mapX']        = $getMapX."";
        $ret['mapY']        = $getMapY."";
        $ret['idxStrings'] = $idxStrings;
        //echo var_dump($result['obj']);
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
            $ret['lists'][$k]["price"]      = $o->ppbRoomMin;   // 오늘의 펜션 최저가 요금
            $ret['lists'][$k]["review"]     = $this->basket_model->getPensionBasketCountByMpIdx($o->mpIdx);                 // 리뷰   
            $ret['lists'][$k]["sales"]      = $o->ppbRoomSales;                 // 세일요금
            $ret['lists'][$k]["reserve"]    = $o->ppbReserve;                   // 예약방식
            
            if( $random > 0 ){
                array_push($ret['idxStrings'],$o->mpIdx);
            }
        }

        $ret['idxStrings'] = implode(',', $ret['idxStrings'] );

        //print_r( $ret );

        //$this->output->enable_profiler();
        
        echo json_encode( $ret );

    }
}
?>