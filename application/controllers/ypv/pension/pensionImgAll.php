<?php
class PensionImgAll extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->library('pension_lib');
		$this->load->model('_ypv/pension_model','pension_model');
	}

	function index() {
		$mpIdx		= $this->input->get('mpIdx');

		if(!$mpIdx){
            $this->error->getError('0006');
		}
        
        
        //객실 Data
		$roomData	= $this->pension_model->getRoomInfo($mpIdx);
        
        //기타 Data
		$etcData	= $this->pension_model->getEtcInfo($mpIdx);


		if(empty($roomData)){
		    $this->error->getError('0005');
        }

        $reVal = array();
        $i = 0;
        
		foreach($roomData as $roomData){
			$raVal['roomList'][$i]['pprIdx']	= $roomData['pprIdx'];
			$raVal['roomList'][$i]['roomName']	= $roomData['pprName'];
			$raVal['roomList'][$i]['inMin']		= $roomData['pprInMin'];
			$raVal['roomList'][$i]['inMax']		= $roomData['pprInMax'];
			$raVal['roomList'][$i]['size']		= $roomData['pprSize'];
            
			$minPrice = $this->pension_model->getRoomPrice($mpIdx, $roomData['pprIdx']);            
			$raVal['roomList'][$i]['roomPrice']	= number_format($minPrice);			
			
			$roomImgData	= $this->pension_model->pensionRoomImageLists($roomData['pprIdx'],0,1000);
            $roomImg = $roomImgData['query'];
			$raVal['roomList'][$i]['imageCount'] = $roomImgData['count'];
            $j = 0;
            
			foreach( $roomImg as $roomImg ) {
				$raVal['roomList'][$i]['lists'][$j]['images']	= 'http://img.yapen.co.kr/pension/room/'.$mpIdx.'/800x0/'.$roomImg['pprpFileName'];
                $j++;
			}
            $i++;
		}
        
        $i = 0;
		foreach($etcData as $etcData) {
			$raVal['etcList'][$i]['ppeIdx']				= $etcData['ppeIdx'];
			$raVal['etcList'][$i]['ppeName']			= $etcData['ppeName'];
			$etcImages	= $this->pension_model->pensionEtcImageLists($etcData['ppeIdx'],0,1000);
			$raVal['etcList'][$i]['imageCount']	= $etcImages['count'];
            
            $etcImaList = $etcImages['query'];
            $j=0;
			foreach( $etcImaList as $etcImaList) {
				$raVal['etcList'][$i]['lists'][$j]['images']	= 'http://img.yapen.co.kr/pension/etc/'.$mpIdx.'/800x0/'.$etcImaList['ppepFileName'];
                $j++;
			}
            $i++;
		}
        
		$raVal['status'] = "1";
		$raVal['failed_message'] = '';

		echo json_encode( $raVal );
	}
}
?>