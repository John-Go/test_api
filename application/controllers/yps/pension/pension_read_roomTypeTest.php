<?php
class Pension_read_roomTypeTest extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/pension/pension_model');
		$this->load->model('_yps/pension/room_model');
		$this->config->load('yps/_code');
	}
    

	function index() {
        
        checkMethod('get'); // 접근 메서드를 제한
        
        $idx = '20107';
        if( !$idx ) $this->error->getError('0006'); // Key가 없을경우
        
        
        $typeResult = $this->pension_model->getPensionRoomType($idx);
		if($typeResult['ppbRoomType'] == "T"){
        	$result = $this->pension_model->getPensionRoomTypeLists($idx);
		}else{
        	$result = $this->pension_model->getPensionRoomLists($idx);
		}
        $ret = array();
        $ret['status'] = "1";
        $ret['failed_message'] = "";
        
        $no = 0;
        foreach ($result as $row) {
            $arrImage = array();
            $resultImages = $this->pension_model->pensionRoomImageLists($row['pprIdx'], 0, 9999);

            $imageNo = 0;
            $imagesRepr = "";
            foreach ($resultImages['query'] as $imageRow) {
                if($imageRow['pprpRepr'] > 0){
                    $imagesRepr = 'http://img.yapen.co.kr/pension/room/'.$idx.'/800x0/'.$imageRow['pprpFileName'];
                }
                $arrImage[$imageNo] = 'http://img.yapen.co.kr/pension/room/'.$idx.'/800x0/'.$imageRow['pprpFileName'];
                $imageNo++;
            }


            $ret['lists'][$no]["idx"] = $row['pprIdx']; // 객실키
            
            $roomType = $this->config->item('pprShape')[$row['pprShape']];
            if($row['pprFloorS'] == "1"){
                $roomType = $roomType.', 독채형';
            }
            if($row['pprFloorM'] == "1"){
                $roomType = $roomType.', 복층형';
            }
            $roomType = rawurlencode($roomType);
              
            $ret['lists'][$no]["name"] = rawurlencode($row['prtName']); // 객실명
            if($imagesRepr != "" || $imagesRepr){
                $ret['lists'][$no]["image"] = (sizeof($arrImage)) ? $imagesRepr : ''; // 객실사진
            }else{
                $ret['lists'][$no]["image"] = (sizeof($arrImage)) ? $arrImage[0] : ''; // 객실사진
            }           
            $ret['lists'][$no]["space"] = $row['pprSize']; // 평수
            $ret['lists'][$no]["type"] =  $roomType;// 객실구조
            $ret['lists'][$no]["inMin"] = $row['pprInMin']; // 최소수용인원
            $ret['lists'][$no]["inMax"] = $row['pprInMax']; // 최대수용인원
            $ret['lists'][$no]["price"] = number_format($row['price'])."";  // 이용요금

            // $subNum = 0;
            // for($i=1;$i<sizeof($arrImage);$i++){
            //     $ret['lists'][$no]['lists'][$subNum]["image"] = $arrImage[$subNum];
            //     $subNum++;
            // }
            
            // 201406101300 pyh : 불필요하게 구문이 복잡해서 정리, 결정적으로 위처럼 코딩하면 1부터 시작해서 이미지 1개가 빠진다.
            $nImgCnt = sizeof($arrImage);
            for ($z = 0; $z < $nImgCnt; $z++) {
                $ret['lists'][$no]['lists'][$z]["image"] = $arrImage[$z];
            }

            $no++;
        }       
        echo json_encode( $ret );
    }
}

?>