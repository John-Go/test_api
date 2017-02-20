<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hotelnow extends CI_Controller {
    
    public function __construct(){
        parent::__construct();		
        $this->load->model('connect/partner_model');		
		$this->load->library('pension_lib');		
		$this->connectUrl = "http://cms.api.yanolja.com:8099";
		$this->passIP = array('52.197.176.69','54.64.90.137','211.119.136.118','211.119.136.108','211.119.136.101','211.119.136.74','211.119.136.97','211.119.165.88');
		if(!in_array($_SERVER['REMOTE_ADDR'], $this->passIP)){
			exit; return;
		}
		$this->reVal = array();
		$this->reVal['result'] = false;
		$this->reVal['fail_message'] = "";
    }
	
	function check(){
		$mpIdx = $this->input->get_post('pension_id');
		$pprIdx = $this->input->get_post('room_id');
		$startDate = $this->input->get_post('checkin_date');
		$endDate = $this->input->get_post('checkout_date');
		
		
		if(!$mpIdx || !$pprIdx || !$startDate || !$endDate){
        	$this->reVal['result'] = false;
			$this->reVal['fail_message'] = "필수값 누락";
            $this->reVal['data'] = array();
			echo json_encode($this->reVal);
            return; exit;
        }else{
            $this->reVal['result'] = true;
			$this->reVal['fail_message'] = "";
			$this->reVal['pension_id'] = $mpIdx;
			$this->reVal['room_id'] = $pprIdx;
            $this->reVal['data'] = array();
        }
		
		$checkRoom = $this->partner_model->getCheckPensionRoom($mpIdx, $pprIdx);
		
		if($checkRoom == 0){
			$this->reVal['result'] = false;
			$this->reVal['fail_message'] = "객실정보 없음";
            $this->reVal['data'] = array();
			echo json_encode($this->reVal);
            return; exit;
		}
		
		$startDateArray = explode('-', $startDate);
		$dayFor = round(abs(strtotime($endDate)-strtotime($startDate))/86400);
		
		$partnerArray = array('27');
			
		for($i=0; $i< $dayFor; $i++){
			$setDate = date('Y-m-d', mktime(0, 0, 0, $startDateArray[1], $startDateArray[2]+$i, $startDateArray[0]));
			$info = $this->partner_model->getCheckRoom($mpIdx, $pprIdx, $setDate);
			
			$this->reVal['data'][$i]['room_id'] = $info['pprIdx'];
			$this->reVal['data'][$i]['checkin_date'] = $setDate;
			
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
					            
                $roomIdx = $this->partner_model->getConnectRoom($pprIdx, 'gpKey');
				
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
                        $this->partner_model->roomConnect($mpIdx, $pprIdx, $setDate, 'G펜션 미 매칭', 'C');
                    }
                }
			}
			
			$this->reVal['data'][$i]['items_quantity'] = $roomCheck;
			
			if($info['pprOpen'] == "1" && $info['mpsOpen'] == "1"){
				$this->reVal['data'][$i]['status'] = "ok";
			}else{
				$this->reVal['data'][$i]['status'] = "stop";
			}
		}
		
		echo json_encode($this->reVal);
	}

	function cancel_price(){
		$rCode = $this->input->get('id');
		$rIdx = $this->input->get('chid');
		
		if(!$rCode || !$rIdx){
        	$this->reVal['result'] = false;
			$this->reVal['fail_message'] = "필수값 누락";
			echo json_encode($this->reVal);
            return; exit;
        }
		
		$this->reVal['data'] = $this->cancelPriceUpdate($rIdx);
		$this->reVal['result'] = true;
		
		echo json_encode($this->reVal);
	}
	
	function cancelPriceUpdate($rIdx){
        $info = $this->partner_model->getReserveInfo($rIdx);
        $revInfo = $this->partner_model->getReserveLists($rIdx);
        
        $penaltyPay = "";
        $optionPenalty = 0;
		
		$return = array();
		
        if(isset($info['rIdx'])){
            if(count($revInfo) > 0){
                $revNo = 0;
				$return['cancel_price'] = 0;
                foreach($revInfo as $revInfo){
                    if($info['rDuplicateFlag'] == 1){
                        continue;
                    }
                    
                    // 재갱신 돌리지 않아아할 경우
                    if(($revInfo['rCancelDate'] != "0000-00-00 00:00:00") || ($revInfo['rState'] != "PS01" && $revInfo['rState'] != "PS02")){
                        continue;
                    }
                    $DeferDay = (strtotime($revInfo['rRevDate'])-strtotime(date('Y-m-d')))/86400;
                    /* 추가인원 관련 설정 Start */
                    if($revInfo['rAddType'] == "1"){
                        $addPrice = $revInfo['rAdultPrice']+$revInfo['rYoungPrice']+$revInfo['rBabyPrice'];
                    }else{
                        $addPrice = 0;
                    }
                    /* 추가인원 관련 설정 End */
                   
                    $resultPrice = $revInfo['rBasicPrice']-$revInfo['rSalePrice']-$revInfo['rTodayPrice']-$revInfo['rSerialPrice']+$addPrice;
                    
                    $cancelRevDay = round((strtotime(date('Y-m-d'))-strtotime(substr($info['rRegDate'],0,10)))/86400);
                    
                    if($cancelRevDay < 0){
                        $cancelRevDay = 0;
                    }
                    if($info['rRoot'] == "RO01" || $info['rRoot'] == "RO04"){
                        $penaltyIdx = "1";
                    }else{
                        $penaltyIdx = $info['mpIdx'];                    
                    }
                    $penalty = $this->partner_model->getPenaltyInfo($penaltyIdx, $revInfo['rRevDate'], $cancelRevDay);
                    if($revNo == 0){
                        $optionPenalty = $penalty;
                    }
                    
                    $cancelPrice = $resultPrice/100*$penalty;
                    if($penaltyPay == ""){
                        $penaltyPay = $penalty;
                    }
                    if($revInfo['rNotCancelFlag'] == '1' && $revInfo['rCancelPrice'] >= $cancelPrice){
                        continue;
                    }
                    $this->partner_model->uptReserveInfo($revInfo['priIdx'], $cancelPrice);
					$return['cancel_price'] = $return['cancel_price'] + $cancelPrice;
					$return['cancel_penalty'] = (int)$penaltyPay;
                    $revNo++;
                }
            }
            
            
            $optionLists = $this->partner_model->getRevOptionLists($rIdx);
            $optionPrice = 0;
            if(count($optionLists) > 0){
                foreach($optionLists as $optionLists){
                    // 재갱신 돌리지 않아야할 경우
                    if(($optionLists['proCancelDate'] != "0000-00-00 00:00:00") || ($optionLists['proState'] != "PS01" && $optionLists['proState'] != "PS02") || $optionLists['proCancelCheck'] == "N"){
                        continue;
                    }
                    
                    $cancelPrice = 0;
                    if($optionLists['proType'] == "1"){
                        $optionPrice = $optionLists['proBasicPrice']*$optionLists['proNumber'];
                        if($optionPrice > 0){
                            $cancelPrice = $optionPrice/100*$optionPenalty;
                        }
                        if($optionLists['proNotCancelFlag'] == '1' && $optionLists['proCancelPrice'] >= $cancelPrice){
                            continue;
                        }
                        $this->partner_model->uptOption($optionLists['proIdx'], $cancelPrice);
						
						$return['cancel_price'] = $return['cancel_price'] + $cancelPrice;
                    }
                }
            }

			$return['price'] = ($resultPrice+$optionPrice);
			
        }else{
			$return['fail_message'] = "예약정보 없음";
        }
		
		return $return;
    }	

	function success(){
		$data = $this->input->post('data');
		
		if(!$data){
			$this->cancelLog('필수정보 누락');
		}
        
		$data = json_decode($data, TRUE);
		
		if(!isset($data['id'])){
			$this->cancelLog('예약조회 실패');
		}
		
		if(isset($data['id'])){
			$checkCount = $this->partner_model->checkRevData($data['id']);
			if($checkCount > 0){
				$this->cancelLog('이미 예약된 예약번호');
			}
			
			if(!isset($data['pension_id']) || !isset($data['room_id'])){
				$this->cancelLog('필요정보 없음');
			}
			
			if(isset($data['booking_from'])){
				if($data['booking_from'] == "hn"){
					$channelText = "호텔나우";
					$channel = "2";
				}else if($data['booking_from'] == "elevenl"){
					$channelText = "11번가 레저";
					$channel = "3";
				}else if($data['booking_from'] == "hn"){
					$channelText = "11번가 당일";
					$channel = "3";
				}else{
					$channelText = "호텔나우";
					$channel = "2";
				}
			}else{
				$channelText = "호텔나우";
				$channel = "2";
			}
			
			
			$connectRoom = array();
			$connectDate= array();
			$connectMemo = array();
			$connectEtc= array();
			$revData = array();
			
			$startDate = $data['checkin_date'];
			$startDateArray = explode('-', $startDate);
			$endDate = $data['checkout_date'];
			$dayFor = round(abs(strtotime($endDate)-strtotime($startDate))/86400);
			
			for($i=0; $i< $dayFor; $i++){
				$connectSetDate = date('Y-m-d', mktime(0, 0, 0, $startDateArray[1], $startDateArray[2]+$i, $startDateArray[0]));
				$connectRoom[$i] = $data['room_id'];
				$connectDate[$i] = $connectSetDate;
				$connectMemo[$i] = $connectSetDate." ".$channelText." 예약";
				$connectEtc[$i] = $data['id'];
				$revData['rRevDate'][$i] = $connectSetDate;
			}
			
			$connectData = array(
				'room' => $connectRoom,
				'setDate' => $connectDate,
				'memo' => $connectMemo,
				'etc' => $connectEtc
			);
			
			$connectJson = json_decode($this->connectRoom($connectData, 'S'));
			
			if($connectJson->state == "1"){
				$revData['rCode'] = $data['id'];
				$revData['rPersonName'] = trim($data['user_name']);
				$revData['rPersonMobile'] = trim(str_replace('-','',$data['user_phone']));
				$revData['rPaymentMethod'] = "PM11";
				if($data['status'] == "booked"){
					$revData['rPaymentState'] = "PS02";
				}else{
					$revData['rPaymentState'] = "PS07";
				}
				$revData['rRequestInfo'] = $data['msg'];
				$revData['mpIdx'] = $data['pension_id'];
				$revData['pprIdx'] = $data['room_id'];
				$revData['rPrice'] = $data['price'];
				$revData['rPersonBrithday'] = "1989-01-01";
				$revData['rChannel'] = $channel;
				if(isset($data['exoid'])){
					$revData['rPayStep1'] = $data['exid'];
					$revData['rPayStep2'] = $data['exoid'];
				}else{
					$revData['rPayStep1'] = "";
					$revData['rPayStep2'] = "";
				}
				
				$rIdx = $this->partner_model->insRevData($revData, '42');
				
				$returnData = array("result" => true,
									"data" => array('reserve_id' => $rIdx));
									
				
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
				
				echo json_encode($returnData);
			}else{
				$this->cancelLog('이미 판매된 객실');
			}
		}else{
			$this->cancelLog('예약조회 실패');
		}
	}

	function connectRoom($connectData, $type){
		if($type == "S"){
			$url = "http://api.yapen.co.kr/connect/room/block";
		}else if($type == "C"){
			$url = "http://api.yapen.co.kr/connect/room/open";
		}
		$data = array(
			'data' => json_encode($connectData),
			'key' => 'YA20161226114923'
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
	
	function cancel(){
		$data = $this->input->post('data');
		
		if(!$data){
			$this->cancelLog('필수정보 누락');
		}
		
		$data = json_decode($data, TRUE);
		
		$revData = array();
		$revData['rCode'] = $data['id'];
		$revData['cancelPrice'] = $data['cancel_amount'];
		$revData['cancelInfo'] = $data['cancel_msg'];
		
		$rIdx = $this->partner_model->cancelRevInfo($revData);
		if($rIdx == ""){
			$this->cancelLog('예약번호 존재하지 않음');
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
	
	function cancelLog($text){
		$this->logCreate(date('Y-m-d H:i:s')." ".$text);
		
		$data = array(	"result" => false,
						"message" => $text);
		echo json_encode($data);
		exit;
	}
	
	function logCreate($message){
		$message = $message."
";
		$filename = "/home/site/yanoljaTravel_api/application/logs/partner/hotelnow/".date("Y-m-d").".log";
        $fp = fopen($filename,"a+");
        fputs($fp,$message);
        fclose($fp);
	}
}
