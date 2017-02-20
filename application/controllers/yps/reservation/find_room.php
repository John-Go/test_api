<?php

class Find_room extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/reservation/reservation_model');
		$this->load->model('_yps/pension/room_model');
        $this->load->model('_yps/pension/pension_model');
		$this->config->load('yps/_code');
	}
	

	function index() {
		checkMethod('get');	// 접근 메서드를 제한

		$idx = $this->input->get('idx');
        $test = $this->input->get('test');
        $infoResult = $this->pension_model->pensionGetInfo($idx); // 펜션정보
        $infoRow = $infoResult->row_array();
        
		if(!$idx)
			$this->error->getError('0006');	// Key가 없을경우

		$searchDate = $this->input->get('searchDate');				// 입실일
		
		if(substr($searchDate,0,7) == "2016-12" && date('Y') == "2015"){
		    $searchDate = "2015-12".substr($searchDate,7);
		}
		$searchDateNum = $this->input->get('searchDateNum');		// 입실박수
        
        $last_day = date("t", mktime(0,0,0,date('m')+3,date('d'),date('Y')));
        $limitDate = date("Y-m-d", mktime(0,0,0,date('m')+3,$last_day,date('Y')));

		//$searchDate	= '2014-02-05';		// 입실일
		//$searchDateNum = 2;			// 입실박수

		
		// 투숙일 날짜정보
		$arrayDate = $this->pension_lib->reservationDate($searchDate, $searchDateNum);
		
	    $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['lists'] = array();
	    $lists = $this->room_model->getRevRoom($idx, $arrayDate);
        if(count($lists) > 0){
            $i=0;
            foreach($lists as $lists){
                $ret['lists'][$i]['room_key'] = $lists['pprIdx'];     // 객실키
                $ret['lists'][$i]['room_name'] = rawurlencode($lists['pprName']);       // 객실명
                $ret['lists'][$i]['room_in_min'] = $lists['pprInMin'];    // 최소인원
                $ret['lists'][$i]['room_in_max'] = $lists['pprInMax'];    // 최대인원
                $ret['lists'][$i]['room_size'] = $lists['pprSize'];       // 객실크기
                $ret['lists'][$i]['room_type'] = rawurlencode($this->config->item('pprShape')[$lists['pprShape']]); // 객실구조    
                $price = $this->room_model->realTimePrice( $lists['pprIdx'], $searchDate, $searchDateNum, $idx );                
                $ret['lists'][$i]['seasonPrice'] = $price['basicPrice'];          // 시즌요금
                $ret['lists'][$i]['resultPrice'] = $price['salePrice'];           // 할인요금                
                $ret['lists'][$i]['room_image'] = "http://img.yapen.co.kr/pension/room/".$idx."/800x0/".$lists['pprpFilename'];          // 객실이미지
                if($lists['ppbIdx']){
                    $ret['lists'][$i]['room_block'] = '1';
                }else{
                    $ret['lists'][$i]['room_block'] = '0';
                }
                $i++;
            }
        }
		
		 
		echo json_encode( $ret );
	}

    function web() {
        checkMethod('get'); // 접근 메서드를 제한

        $idx = $this->input->get('idx');
        
        $infoResult = $this->pension_model->pensionGetInfo($idx); // 펜션정보
        $infoRow = $infoResult->row_array();
        
        if(!$idx)
            $this->error->getError('0006'); // Key가 없을경우

        $searchDate = $this->input->get('searchDate');              // 입실일
        $searchDateNum = $this->input->get('searchDateNum');        // 입실박수


        //$searchDate   = '2014-02-05';     // 입실일
        //$searchDateNum = 2;           // 입실박수

        
        // 투숙일 날짜정보
        $arrayDate = $this->pension_lib->reservationDate($searchDate, $searchDateNum);
        
        // 블록된 펜션키 리스트
        $arrayBlockPensionKey = $this->reservation_model->blockPentionList($arrayDate);

        $result = $this->room_model->lists($idx);
        
        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        //$ret['tnt_cnt'] = $result['count']."";
        $openArray = array();
        $blockArray = array();
        $i=0;
        $j=0;
        foreach ($result as $key => $value){
            if ( in_array($value['pprIdx'], $arrayBlockPensionKey) ){                
                $blockArray[$i]['room_key'] = $value['pprIdx'];     // 객실키
                $blockArray[$i]['room_name'] = $value['pprName'];       // 객실명
                $blockArray[$i]['room_in_min'] = $value['pprInMin'];    // 최소인원
                $blockArray[$i]['room_in_max'] = $value['pprInMax'];    // 최대인원
                $blockArray[$i]['room_size'] = $value['pprSize'];       // 객실크기
                $blockArray[$i]['room_type'] = rawurlencode($this->config->item('pprShape')[$value['pprShape']]); // 객실구조    
                $price = $this->room_model->realTimePrice( $value['pprIdx'], $searchDate, $searchDateNum, $idx );                
                $blockArray[$i]['seasonPrice'] = $price['basicPrice'];          // 시즌요금
                $blockArray[$i]['resultPrice'] = $price['salePrice'];           // 할인요금                
                $blockArray[$i]['room_image'] = $value['pprpFileUrl'];          // 객실이미지
                $blockArray[$i]['room_block'] = '1';
                $i++;
            }else{
                $openArray[$j]['room_key'] = $value['pprIdx'];     // 객실키
                $openArray[$j]['room_name'] = $value['pprName'];       // 객실명
                $openArray[$j]['room_in_min'] = $value['pprInMin'];    // 최소인원
                $openArray[$j]['room_in_max'] = $value['pprInMax'];    // 최대인원
                $openArray[$j]['room_size'] = $value['pprSize'];       // 객실크기
                $openArray[$j]['room_type'] = rawurlencode($this->config->item('pprShape')[$value['pprShape']]); // 객실구조    
                $price = $this->room_model->realTimePrice( $value['pprIdx'], $searchDate, $searchDateNum, $idx );                
                $openArray[$j]['seasonPrice'] = $price['basicPrice'];          // 시즌요금
                $openArray[$j]['resultPrice'] = $price['salePrice'];           // 할인요금                
                $openArray[$j]['room_image'] = $value['pprpFileUrl'];          // 객실이미지
                $openArray[$j]['room_block'] = '0';
                $j++;
            }   
        }
        $ret['lists'] = array_merge($openArray,$blockArray);
        
        echo json_encode( $ret );
    }
}
?>