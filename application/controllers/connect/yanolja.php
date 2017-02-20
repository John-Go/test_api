<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Yanolja extends CI_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model('connect/yanolja_model');
		$this->load->library('pension_lib');
		//$this->connectUrl = "http://stage.cms.api.yanolja.com:8099";
		$this->connectUrl = "http://cms.api.yanolja.com:8099";
    }
	
	function check(){
		$mpIdx = $this->input->get_post('pension_id');
		$pprIdx = $this->input->get_post('room_id');
		$startDate = $this->input->get_post('checkin_date');
		$endDate = $this->input->get_post('checkout_date');
		
		$reVal = array();
		
		
		if(!$mpIdx || !$pprIdx || !$startDate || !$endDate){
        	$reVal['result'] = false;
            $reVal['data'] = array();
			echo json_encode($reVal);
            return; exit;
        }else{
            $reVal['result'] = true;
			$reVal['pension_id'] = $mpIdx;
			$reVal['room_id'] = $pprIdx;
            $reVal['data'] = array();
        }
		
		$startDateArray = explode('-', $startDate);
		$dayFor = round(abs(strtotime($endDate)-strtotime($startDate))/86400);
		
		$partnerArray = array('29');
			
		for($i=0; $i< $dayFor; $i++){
			$setDate = date('Y-m-d', mktime(0, 0, 0, $startDateArray[1], $startDateArray[2]+$i, $startDateArray[0]));
			$info = $this->yanolja_model->getCheckRoom($mpIdx, $pprIdx, $setDate);
			
			$reVal['data'][$i]['room_id'] = $info['pprIdx'];
			$reVal['data'][$i]['checkin_date'] = $setDate;

			$roomCheck = 0;
			
			if($info['ppbReserve'] != "R" || in_array($info['ppbMainPension'], $partnerArray) || $info['mpsOpen'] == "0"){
				$roomCheck = 0;
			}else{
				if($info['ppbIndex'] == ""){
					$roomCheck = 1;
				}else{
					$roomCheck = 0;
				}
			}
			
			//G펜션인 경우,
			if($info['ppbMainPension'] == "19"){
				$url = "http://reservation1.gpension.kr/_API/YP/search_room.php";
					            
                $roomIdx = $this->yanolja_model->getConnectRoom($pprIdx, 'gpKey');
				
                if($roomIdx){
                    $url = "http://reservation1.gpension.kr/_API/YP/search_room.php";

                    $sendData = array(
                                    'partner_id' => 'yapen',
                                    'room_id' => $roomIdx,
                                    'startdate' => $setDate,
                                    'daytype' => 1
                    );

					$ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $sendData);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $returnText = curl_exec($ch);
                    $returnData = explode('::',$returnText);
					curl_close($ch);
					
                    if($returnData[0] != "S"){
                        $roomCheck = 0;
                        $this->yanolja_model->roomConnect($mpIdx, $pprIdx, $setDate, 'G펜션 미 매칭', 'C');
                    }
                }
			}
			
			$reVal['data'][$i]['items_quantity'] = $roomCheck;
			
			if($info['pprOpen'] == "1" && $info['mpsOpen'] == "1"){
				$reVal['data'][$i]['status'] = "ok";
			}else{
				$reVal['data'][$i]['status'] = "stop";
			}
		}
		
		echo json_encode($reVal);
	}

	function call(){
		$type = $this->input->get('type');
		$index = $this->input->get('index');
		if(!$index || !$type){
			$this->cancel('필수정보 누락');
		}

		$logData = date("Y-m-d H:i:s")." / index : ".$index." / type : ".$type."
";
        $filename = "/home/site/yanoljaTravel_api/application/logs/partner/yanolja/".date("Y-m-d").".log";
        $fp = fopen($filename,"a+");
        fputs($fp,$logData);
        fclose($fp);
		if($type == 'S'){
			$url = $this->connectUrl."/reservations/".$index."?channelNo=6";
			
            $ch = curl_init();    
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $retData = curl_exec($ch);
            curl_close($ch);
			
			$this->revSuccess($retData, $type);
		}else if($type == 'C'){
			$this->yanoljaCancel($index, $type);
		}
	}

	function yanoljaRoomConnect($connectData, $type){
		if($type == "S"){
			$url = "http://api.yapen.co.kr/connect/room/block";
		}else if($type == "C"){
			$url = "http://api.yapen.co.kr/connect/room/open";
		}
		$data = array(
			'data' => json_encode($connectData),
			'key' => 'YA20161116113212'
		);
		$ch= curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$returnData = curl_exec($ch);
		curl_close($ch);
		
		return $returnData;
	}
	
	
	
	function yanoljaCancel($index, $type){
		$url = $this->connectUrl."/reservations/".$index."?channelNo=6";
			
        $ch = curl_init();    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $retData = curl_exec($ch);
        curl_close($ch);
		
		$retData = json_decode($retData);
		
		if($retData->code != 200){
			$this->cancel('예약정보 누락');
			return; exit;
		}
		
		$rIdx = $this->yanolja_model->cancelRevInfo($index, $retData);
		
		if($rIdx == ""){
			$this->cancel('예약번호 존재하지 않음');
		}else{
			$url = "http://api.yapen.co.kr/em/send/rev";
		        
	        $smsData = array(   'rIdx' => $rIdx,
	                            'state' => 'PS07',
	                            'rUserSend' => 0,
	                            'rCeoSend' => 1,
	                            'auth' => 'admin');
	            
	        $ch = curl_init();    
	        curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $smsData);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_exec($ch);
	        curl_close($ch);
			
			$returnData = array('result' => true,
								'data' => array('booking_id' => $rIdx));
									
			echo json_encode($returnData);
		}
	}
	
	function revSuccess($retData, $type){
		$retData = json_decode($retData, TRUE);
		if(!isset($retData['code'])){
			$this->cancel('예약조회 실패');
			return; exit;
		}
		
		if($retData['code']== 200){
			$checkCount = $this->yanolja_model->checkRevData($retData['data']['reservationNo']);
			
			//$pensionInfo = $this->yanolja_model->getPensionInfo($retData['data']['channelPlaceNo'], $retData['data']['channelRoomTypeNo']);
			
			if($checkCount > 0){
				$this->cancel('이미 예약된 예약번호');
				return; exit;
			}
			
			if(!isset($retData['data']['channelPlaceNo']) || !isset($retData['data']['channelRoomTypeNo'])){
				$this->cancel('필수 정보 누락');
				return; exit;
			}
			
			$startDate = substr($retData['data']['checkInDateTime'],0,10);
			$startDateArray = explode('-', $startDate);
			$endDate = substr($retData['data']['checkOutDateTime'],0,10);
			$dayFor = round(abs(strtotime($endDate)-strtotime($startDate))/86400);
			
			$roomBlockCheck = 0;
	        for($i=0; $i< $dayFor; $i++){
	        	$setDate = date('Y-m-d', mktime(0, 0, 0, $startDateArray[1], $startDateArray[2]+$i, $startDateArray[0]));
				$roomBlockCheck += $this->yanolja_model->getRoomCheck($retData['data']['channelRoomTypeNo'], $setDate);
				
				//G펜션인 경우
				/*
				if($pensionInfo['ppbMainPension'] == "19" && $pensionInfo['gpKey']){
                    $url = "http://reservation1.gpension.kr/_API/YP/search_room.php";

                    $sendData = array(
                                    'partner_id' => 'yapen',
                                    'room_id' => $pensionInfo['gpKey'],
                                    'startdate' => $setDate,
                                    'daytype' => 1
                    );

					$ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $sendData);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $returnText = curl_exec($ch);
                    $returnData = explode('::',$returnText);
					curl_close($ch);
					
                    if($returnData[0] != "S"){
                        $roomBlockCheck++;
                        $this->yanolja_model->roomConnect($retData['data']['channelPlaceNo'], $retData['data']['channelRoomTypeNo'], $setDate, 'G펜션 미 매칭', 'C');
                    }
                }
				*/
			}
			
			if($roomBlockCheck == 0){
				$rIdx = $this->yanolja_model->insRevInfo($retData['data']);
				
				$returnData = array("result" => true,
									"data" => array('booking_id' => $rIdx));
				if($retData['data']['channelPlaceNo'] != "20107"){
		            $url = "http://api.yapen.co.kr/em/send/rev";
			        
			        $smsData = array(   'rIdx' => $rIdx,
			                            'state' => 'PS02',
			                            'rUserSend' => 0,
			                            'rCeoSend' => 1,
			                            'auth' => 'admin');
			            
			        $ch = curl_init();    
			        curl_setopt($ch, CURLOPT_URL, $url);
			        curl_setopt($ch, CURLOPT_POST, true);
			        curl_setopt($ch, CURLOPT_POSTFIELDS, $smsData);
			        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			        curl_exec($ch);
			        curl_close($ch);
				}
				echo json_encode($returnData);
			}else{
				$this->cancel('이미 판매된 객실');
			}
		}else{
			$this->cancel('예약조회 실패');
		}
	}
	
	
	
	function cancel($text){
		$data = array(	"result" => false,
						"message" => $text);
		echo json_encode($data);
	}
	
	/*
	function yanoljaCancel20161228($index, $type){
		$rIdx = $this->yanolja_model->cancelRevData($index);
		if($rIdx == ""){
			$this->cancel('예약번호 존재하지 않음');
		}else{
			
			$url = "http://api.yapen.co.kr/em/send/rev";
		        
	        $smsData = array(   'rIdx' => $rIdx,
	                            'state' => 'PS07',
	                            'rUserSend' => 0,
	                            'rCeoSend' => 1,
	                            'auth' => 'admin');
	            
	        $ch = curl_init();    
	        curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $smsData);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_exec($ch);
	        curl_close($ch);
			
			$returnData = array('result' => true,
								'data' => array('booking_id' => $rIdx));
									
			echo json_encode($returnData);
		}
	}
	
	function success($retData, $type){
		$retData = json_decode($retData);
		if(!isset($retData->code)){
			$this->cancel('예약조회 실패');
			return; exit;
		}
		
		if($retData->code == 200){
			$checkCount = $this->yanolja_model->checkRevData($retData->data->reservationNo);
			if($checkCount > 0){
				$this->cancel('이미 예약된 예약번호');
				return; exit;
			}
			
			if(!isset($retData->data->channelPlaceNo) || !isset($retData->data->channelRoomTypeNo)){
				$this->cancel('필수 정보 누락');
				return; exit;
			}
			$this->yanolja_model->insPartnerData($retData->code, $this->pension_lib->encrypt(json_encode($retData->data)), $type);
			
			$connectRoom = array();
			$connectDate= array();
			$connectMemo = array();
			$connectEtc= array();
			
			$startDate = substr($retData->data->checkInDateTime,0,10);
			$startDateArray = explode('-', $startDate);
			$endDate = substr($retData->data->checkOutDateTime,0,10);
			$dayFor = round(abs(strtotime($endDate)-strtotime($startDate))/86400);
			
			for($i=0; $i< $dayFor; $i++){
				$connectSetDate = date('Y-m-d', mktime(0, 0, 0, $startDateArray[1], $startDateArray[2]+$i, $startDateArray[0]));
				$connectRoom[$i] = $retData->data->channelRoomTypeNo;
				$connectDate[$i] = $connectSetDate;
				$connectMemo[$i] = $connectSetDate." 야놀자 예약";
				$connectEtc[$i] = $retData->data->reservationNo;
			}
			
			$connectData = array(
				'room' => $connectRoom,
				'setDate' => $connectDate,
				'memo' => $connectMemo,
				'etc' => $connectEtc
			);
			
			$connectJson = json_decode($this->yanoljaRoomConnect($connectData, $type));
			
			if($connectJson->state == "1"){
				$rIdx = $this->yanolja_model->insRevData($retData->data);
				
				$returnData = array("result" => true,
									"data" => array('booking_id' => $rIdx));
				if($retData->data->channelPlaceNo != "20107"){
		            $url = "http://api.yapen.co.kr/em/send/rev";
			        
			        $smsData = array(   'rIdx' => $rIdx,
			                            'state' => 'PS02',
			                            'rUserSend' => 0,
			                            'rCeoSend' => 1,
			                            'auth' => 'admin');
			            
			        $ch = curl_init();    
			        curl_setopt($ch, CURLOPT_URL, $url);
			        curl_setopt($ch, CURLOPT_POST, true);
			        curl_setopt($ch, CURLOPT_POSTFIELDS, $smsData);
			        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			        curl_exec($ch);
			        curl_close($ch);
				}
				echo json_encode($returnData);
			}else{
				$this->cancel('이미 판매된 객실');
			}
		}else{
			$this->cancel('예약조회 실패');
		}
	}
	
	function cancelTest(){
		$index = $this->input->get('index');
		$url = $this->connectUrl."/reservations/".$index."?channelNo=6";
			
        $ch = curl_init();    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $retData = curl_exec($ch);
        curl_close($ch);
		
		$retData = json_decode($retData);
		
		if($retData->code != 200){
			$this->cancel('예약정보 누락');
			return; exit;
		}
		
		$rIdx = $this->yanolja_model->cancelRevInfoTest($index, $retData);
	}
	*/
}
