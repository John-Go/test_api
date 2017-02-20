<?php
class RoomInfo extends CI_Controller {    
    public function __construct(){
        parent::__construct();
        $this->load->config('yps/_constants');
        $this->load->library('pension_lib');
        $this->load->model('_ypv/pension_model','pension_model');
        $this->config->load('yps/_code');
    }
    
    function index(){
        $mpIdx = $this->input->get('mpIdx');
        
        $reVal = array();
        
        $reVal['status'] = "1";
        $reVal['failed_message'] = "";
        $reVal['mpIdx'] = $mpIdx;
        $info = $this->pension_model->getRoomInfo($mpIdx);
        
        $i = 0;
        foreach($info as $info){
            $reVal['roomList'][$i]['roomIdx'] = $info['pprIdx'];
            $reVal['roomList'][$i]['roomName'] = rawurlencode($info['pprName']);
            
            $imageListArray = $this->pension_model->pensionRoomImageLists($info['pprIdx'],0,9999);
            $arrImage = $imageListArray['count'];
            for($j=0; $j< count($imageListArray['query']); $j++){
                if($imageListArray['query'][$j]['pprpRepr'] > 0){
                    $imagesRepr = 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$imageListArray['query'][$j]['pprpFileName'];
                }
                $reVal['roomList'][$i]['imageList'][$j]['imageUrl'] = 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$imageListArray['query'][$j]['pprpFileName'];
            }

            if(isset($imagesRepr)){
                $reVal['roomList'][$i]["image"] = (sizeof($arrImage)) ? $imagesRepr : ''; // 객실사진
            }else{
                $reVal['roomList'][$i]["image"] = (sizeof($arrImage)) ? $reVal['roomList'][$i]['imageList'][0]['imageUrl'] : ''; // 객실사진
            }           
            $reVal['roomList'][$i]["space"] = $info['pprSize']; // 평수
            $reVal['roomList'][$i]["type"] = rawurlencode( $this->config->item('pprShape')[$info['pprShape']] ); // 객실구조
            $reVal['roomList'][$i]["inMin"] = $info['pprInMin']; // 최소수용인원
            $reVal['roomList'][$i]["inMax"] = $info['pprInMax']; // 최대수용인원
            $reVal['roomList'][$i]["price"] = number_format($info['resultPrice']);  // 이용요금
            
            $i++;
        }

        echo json_encode($reVal);
    }

    function test(){
        checkMethod('get'); // 접근 메서드를 제한
        
        $mpIdx = $this->input->get('mpIdx');
        if( !$mpIdx ) $this->error->getError('0006'); // Key가 없을경우
        
        $result = $this->pension_model->getPensionRoomLists($mpIdx);
        
        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        $ret['mpIdx'] = $mpIdx;
        
        $no = 0;
        foreach ($result as $row) {
            $arrImage = array();
            $resultImages = $this->pension_model->pensionRoomImageLists($row['pprIdx'], 0, 9999);

            $imageNo = 0;
            $imagesRepr = "";
            foreach ($resultImages['query'] as $imageRow) {
                if($imageRow['pprpRepr'] > 0){
                    $imagesRepr = 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$imageRow['pprpFileName'];
                }
                $arrImage[$imageNo] = 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$imageRow['pprpFileName'];
                $imageNo++;
            }


            $ret['roomList'][$no]["roomIdx"] = $row['pprIdx']; // 객실키
            
            $roomType = $this->config->item('pprShape')[$row['pprShape']];
            if($row['pprFloorS'] == "1"){
                $roomType = $roomType.', 독채형';
            }
            if($row['pprFloorM'] == "1"){
                $roomType = $roomType.', 복층형';
            }
            $roomType = rawurlencode($roomType);
              
            $ret['roomList'][$no]["roomName"] = rawurlencode($row['pprName']); // 객실명
            if($imagesRepr != "" || $imagesRepr){
                $ret['roomList'][$no]["image"] = (sizeof($arrImage)) ? $imagesRepr : ''; // 객실사진
            }else{
                $ret['roomList'][$no]["image"] = (sizeof($arrImage)) ? $arrImage[0] : ''; // 객실사진
            }           
            $ret['roomList'][$no]["space"] = $row['pprSize']; // 평수
            $ret['roomList'][$no]["type"] =  $roomType;// 객실구조
            $ret['roomList'][$no]["inMin"] = $row['pprInMin']; // 최소수용인원
            $ret['roomList'][$no]["inMax"] = $row['pprInMax']; // 최대수용인원
            $ret['roomList'][$no]["price"] = number_format($row['price'])."";  // 이용요금
            
            $nImgCnt = sizeof($arrImage);
            for ($z = 0; $z < $nImgCnt; $z++) {
                $ret['roomList'][$no]['image'][$z]["imageUrl"] = $arrImage[$z];
            }

            $no++;
        }       
        echo json_encode( $ret );
    }
}

?>