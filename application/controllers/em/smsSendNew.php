<?php

class SmsSendNew extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->config->load('_msg_config');
		$this->load->model('_yps/reservation/reservation_model');
		$this->load->model('smsnew_model');
		$this->load->library('pension_lib');
	}
	
	// 고정템플릿
	function index()
	{
		// param
		$type			= $this->input->get('type');
		$receiver		= $this->input->get('receiver');
		$receiver		= preg_replace('/[^0-9]/', '', $receiver);
		
		// 사장님 문자
		$ceoTypeArrs	= array('YP_CALL1','YP_CALL2','YP_CALL3','YP_CALL4','YP_CALL5','YP_CALL6');
		
		// 메세지 config
		$msgCfg			= $this->config->item('msgType');
		$curCfg			= $msgCfg[array_shift(explode('_', $type))]; // EX) YP_CALL1
		
		if($curCfg && $curCfg[$type])
		{
			// msg 
			$msg	= $curCfg[$type];

			// 사장님께 (LMS)
			if(in_array($type, $ceoTypeArrs))
			{
//				$this->smsnew_model->sendSMS($msg, $receiver, 'L');
			}
			// 고객 ( 알림톡 )
			else
			{
//				$this->smsnew_model->sendSMS($msg, $receiver, 'K', $type);
			}
		}
	}

	// 해피콜
	function happyCall()
	{
		$rIdx = $this->input->get('rIdx');

		if(!$rIdx)
		{
			return;
			exit;
		}

		// 주문 정보
		$resArr	= $this->smsnew_model->getRevInfo($rIdx);
		if(!isset($resArr['rIdx']))
		{
			return;
			exit;
		}
		$personMobile	= preg_replace('/[^0-9]/', '', $resArr['rPersonMobile']);
		
		// 날짜 관련 정보
		$dayNameArray	= array('일','월','화','수','목','금','토');
		$dayName		= $dayNameArray[date('w', strtotime($resArr['rStartDate']))];
		$btDay			= round(abs(strtotime($resArr['rEndDate'])-strtotime($resArr['rStartDate']))/86400);

		// 메세지 config
		$type	= 'YP_H_1';
		$msgCfg	= $this->config->item('msgType');
		$curCfg	= $msgCfg['YP'][$type];

		// 메세지 setting
		$chUserArray				= array();
		$chUserArray['pensionName']	= $resArr['rPension'];
		$chUserArray['roomName']	= $resArr['rPensionRoom'];
		$chUserArray['startDate']	= $resArr['rStartDate'];
		$chUserArray['dayName']		= $dayName;
		$chUserArray['day']			= $btDay;
		$chUserArray['url']			= '';

		$chKeyArray		= array_keys($chUserArray);
		$chValArray		= array_values($chUserArray);

		array_walk($chKeyArray, array($this, 'changeKeyFormat'));

		$msg	= str_replace($chKeyArray, $chValArray, $curCfg);
		
		// 고객 ( 알림톡 )
//		$this->smsnew_model->sendSMS($msg, $personMobile, 'K', $type);
	}
	
	// 현장결제 안내 문자
	function account()
	{
		$rIdx = $this->input->get('rIdx');

		if(!$rIdx)
		{
			return;
			exit;
		}

		// 주문 정보
		$resArr	= $this->smsnew_model->getRevInfo($rIdx);
		if(!isset($resArr['rIdx']))
		{
			return;
			exit;
		}
		
		// 바로 결제
		if($resArr['rPriceAddType'] == '1')
		{
			return;
			exit;
		}

		// 추가 정보
		$personMobile	= preg_replace('/[^0-9]/', '', $resArr['rPersonMobile']);
		$addPerson		= $resArr['rNumAdult'] + $resArr['rNumYoung'] + $resArr['rNumBaby'];
		$addPrice		= intval($resArr['rPriceAdult']) + intval($resArr['rPriceYoung']) + intval($resArr['rPriceBaby']);

		// 메세지 config
		$type		= 'YP_CALL10';
		$msgCfg		= $this->config->item('msgType');
		$curCfg		= $msgCfg[array_shift(explode('_', $type))][$type];

		// 메세지 setting
		$chUserArray				= array();
		$chUserArray['pension']		= $resArr['rPension'];
		$chUserArray['people']		= $addPerson;
		$chUserArray['price']		= number_format($addPrice);

		$chKeyArray		= array_keys($chUserArray);
		$chValArray		= array_values($chUserArray);

		array_walk($chKeyArray, array($this, 'changeKeyFormat'));

		$msg	= str_replace($chKeyArray, $chValArray, $curCfg);

		// 고객 ( 알림톡 )
//		$this->smsnew_model->sendSMS($msg, '01068991678', 'K', $type);
//		$this->smsnew_model->sendSMS($msg, $personMobile, 'K', $type);
	}

	// 쿠폰
	function coupon()
	{
		$receiver	= $this->input->get('receiver');
		$coupon		= $this->input->get('coupon');
		$receiver	= preg_replace('/[^0-9]/', '', $receiver);

		// 메세지 config
		$type		= 'YP_CALL11';
		$msgCfg		= $this->config->item('msgType');
		$curCfg		= $msgCfg[array_shift(explode('_', $type))][$type];

		// 메세지 setting
		$chUserArray				= array();
		$chUserArray['code']		= $coupon;

		$chKeyArray		= array_keys($chUserArray);
		$chValArray		= array_values($chUserArray);

		array_walk($chKeyArray, array($this, 'changeKeyFormat'));

		$msg	= str_replace($chKeyArray, $chValArray, $curCfg);

		// 고객 ( 알림톡 )
//		$this->smsnew_model->sendSMS($msg, '01068991678', 'K', $type);
//		$this->smsnew_model->sendSMS($msg, $personMobile, 'K', $type);
	}
	
	// 예약관련
	function rev()
	{
		// 파라미터
		$rIdx			= $this->input->post('rIdx');
		$rUserSend		= isset($_POST['rUserSend']) ? $this->input->post('rUserSend') : true;		// 고객 전송 여부 1 or 0		default 1
		$rCeoSend		= isset($_POST['rCeoSend']) ? $this->input->post('rCeoSend') : true;			// 대표 전송 여부 1 or 0		default 1

		$rIdx			= intval($rIdx);

		$pensionName	= '';
		$ceoPensionName	= '';
		$noPrice		= 0;

		if(!$rIdx)
		{
			return;
			exit;
		}

		// 메세지 config
		$msgCfg	= $this->config->item('msgType');
		$curCfg	= null;
		
		// 주문 정보
		$resArr	= $this->smsnew_model->getRevInfo($rIdx);
		
		if(!isset($resArr['rIdx']))
		{
			return;
			exit;
		}

		// 기본
		$rBasicPrice	= $resArr['rBasicPrice'] - $resArr['rPriceRoomDiscount'];
		
		// 인원
		$totalPeople	= $resArr['pprInMin'] + $resArr['rNumAdult'] + $resArr['rNumYoung'] + $resArr['rNumBaby'];
		$noPrice		+= ($resArr['rPriceAddType'] == '2' ? ($resArr['rPriceAdult'] + $resArr['rPriceYoung'] + $resArr['rPriceBaby']) : 0);
		$rBasicPrice	+= ($resArr['rPriceAddType'] == '1' ? ($resArr['rPriceAdult'] + $resArr['rPriceYoung'] + $resArr['rPriceBaby']) : 0);

		// 날짜 관련 정보
		$dayNameArray	= array('일','월','화','수','목','금','토');
		$dayName		= $dayNameArray[date('w', strtotime($resArr['rStartDate']))];
		$btDay			= round(abs(strtotime($resArr['rEndDate'])-strtotime($resArr['rStartDate']))/86400);
		
		// 펜션 주소
		$address		= '';
		if($resArr['mpsAddrFlag'] && $resArr['mpsAddrFlag'] == "1")
		{
			$address	= $resArr['mpsAddr1New'];
		}
		else
		{
			$address	= $resArr['mpsAddr1'] . " " . $resArr['mpsAddr2'];
		}
		
		// 펜션 연락처
		$pensionTel	= '';
		if($resArr['ppbTel1'])
		{
			$pensionTel		= $this->pension_lib->replacePhone(str_replace('-','',$resArr['ppbTel1']));
		}
		else
		{
			$pensionTel		= $this->pension_lib->replacePhone(str_replace('-','',$resArr['mpsTel']));
		}

		// 추가 옵션
		$optText	= '';
		if($resArr['rOptionName'])
		{
			$optArray	= array();
			if(str_replace('|', '', $resArr['rOptionName']) == $resArr['rOptionName'])
			{
				$optArray[]	= $resArr['rOptionName'] . ' - ' . $resArr['rOptionNum']. '개';

				if($resArr['rOptionType'] == '0')
				{
					$noPrice		+= $resArr['rOptionPrice'] * $resArr['rOptionNum'];
				}
				else
				{
					$rBasicPrice	+= $resArr['rOptionPrice'] * $resArr['rOptionNum'];
				}
			}
			else
			{
				$optNameArray	= explode('|', $resArr['rOptionName']);
				$optNumArray	= explode('|', $resArr['rOptionNum']);
				$optPriceArray	= explode('|', $resArr['rOptionPrice']);
				$optTypeArray	= explode('|', $resArr['rOptionType']);
				$optCnt			= count($optNameArray);

				for($i=0; $i<$optCnt; $i++)
				{
					$optArray[]	= $optNameArray[$i] . ' - ' . $optNumArray[$i];

					if($optTypeArray[$i] == '0')
					{
						$noPrice		+= $optPriceArray[$i] * $optNumArray[$i];
					}
					else
					{
						$rBasicPrice	+= $optPriceArray[$i] * $optNumArray[$i];
					}
				}
			}
			$optText	= implode(', ', $optArray);
		}


		// root 에 따라 config 설정
		switch($resArr['rRoot'])
		{
			case 'RO01':	// 예약대행 (야펜)
				$curCfg			= $this->config->item('YP');
				$pensionName	= '야놀자펜션';
				$ceoPensionName	= '야놀자펜션';
				break;
			case 'RO02':	// YBS
				$curCfg			= $this->config->item('YBS');
				$pensionName	= $resArr['rPension'];
				$ceoPensionName	= '홈페이지 예약';
				break;
			case 'RO03':	// 사장님페이지
				$curCfg			= $this->config->item('CEO');
				$pensionName	= $resArr['rPension'];
				$ceoPensionName	= '홈페이지 예약';
				break;

			default:
				die('');
				break;
		}

		if(!$curCfg[$resArr['rPaymentState']])
		{
			die('NO_SETTING_STATE');
		}

		// 입금 대기 일경우
		if($resArr['rPaymentState'] == 'PS01')
		{
			// 예약대행 (야펜)
			if($resArr['rRoot'] == 'RO01')
			{
				$accArr			= $this->smsnew_model->getAccountInfo($resArr['rCode']);
				$account		= $accArr['LGD_FINANCENAME'] . ' ' . $accArr['LGD_ACCOUNTNUM'] . ' ' . $accArr['LGD_PAYER'];
				$accountLimit	= date('Y-m-d H:i:s', strtotime($accArr['LGD_LimitDate']));
			}
			// YBS
			else if($resArr['rRoot'] == 'RO02')
			{
				$accArr			= $this->smsnew_model->getCeoAccountInfo($resArr['rCode']);
				$account		= $accArr['ppaAccountInfo'];
				$accountLimit	= $accArr['ppaLimitDate'];
			}
			// 사장님페이지
			else if($resArr['rRoot'] == 'RO03')
			{
				$account		= $resArr['ppaBank'] . ' ' . $resArr['ppaNumber'] . ' ' . $resArr['ppaOwner'];
			}
		}
		
		$cancelArrs	= $this->cancelPriceUpdate($rIdx);
		if($resArr['rDuplicateFlag'] == '1')
		{
			$cancelArrs['cancelPrice']	= '0';
		}


		// 상태에 따라 설정된 config
		$curCfg			= $curCfg[$resArr['rPaymentState']];
		$msg['user']	= $msgCfg[array_shift(explode('_', $curCfg['user']))][$curCfg['user']];
		$msg['ceo']		= $msgCfg[array_shift(explode('_', $curCfg['ceo']))][$curCfg['ceo']];

		// user 메시지 정보
		if($rUserSend)
		{
			$chUserArray				= array();
			$chUserArray['pensionName']	= $pensionName;
			$chUserArray['revCode']		= $resArr['rCode'];
			$chUserArray['pension']		= $resArr['rPension'];
			$chUserArray['roomName']	= $resArr['rPensionRoom'];
			$chUserArray['startDate']	= $resArr['rStartDate'];
			$chUserArray['dayName']		= $dayName;
			$chUserArray['day']			= $btDay;
			$chUserArray['address']		= $address;
			$chUserArray['phoneNumber']	= $pensionTel;
			$chUserArray['user']		= $resArr['rPersonName'];
			$chUserArray['adult']		= $resArr['pprInMin'] + $resArr['rNumAdult'];
			$chUserArray['young']		= $resArr['rNumYoung'];
			$chUserArray['baby']		= $resArr['rNumBaby'];
			$chUserArray['people']		= '총 ' . $totalPeople;
			$chUserArray['options']		= $optText;
			$chUserArray['price']		= number_format($rBasicPrice);
			$chUserArray['noPrice']		= number_format($noPrice);
			$chUserArray['penalty']		= $cancelArrs ? ($cancelArrs['cancelPrice'] != '0' ? number_format($cancelArrs['cancelPrice']) . '원 (수수료 '  . $cancelArrs['penalty'] . '%) | 이용 ' . $cancelArrs['penaltyDay'] . '일 전 (' . date('m월 d일부터') . ')' : $cancelArrs['cancelPrice'] . '원') : '';
			$chUserArray['account']		= $account;
			$chUserArray['limit']		= $accountLimit;
			$chUserArray['URL']			= 'https://goo.gl/1afjlG';
			$chUserArray['url']			= 'https://goo.gl/1afjlG';
			
			$chKeyArray		= array_keys($chUserArray);
			$chValArray		= array_values($chUserArray);

			array_walk($chKeyArray, array($this, 'changeKeyFormat'));

			$msg['user']	= str_replace($chKeyArray, $chValArray, $msg['user']);
		}

		
		if($rCeoSend)
		{
			// ceo 메시지 정보
			$chCeoArray	= array();
            $chCeoArray['revNo']        = $rIdx;
			$chCeoArray['pensionName']	= $ceoPensionName;
			$chCeoArray['room']			= $resArr['rPensionRoom'];
			$chCeoArray['roomName']		= $resArr['rPensionRoom'];
			$chCeoArray['startArea']	= $resArr['rPersonSi'];
			$chCeoArray['startDate']	= $resArr['rStartDate'];
			$chCeoArray['dayName']		= $dayName;
			$chCeoArray['day']			= $btDay;
			$chCeoArray['adult']		= $resArr['pprInMin'] + $resArr['rNumAdult'];
			$chCeoArray['young']		= $resArr['rNumYoung'];
			$chCeoArray['baby']			= $resArr['rNumBaby'];
			$chCeoArray['people']		= '총 ' . $totalPeople;
			$chCeoArray['options']		= $optText;
			$chCeoArray['user']			= $resArr['rPersonName'];
			$chCeoArray['phoneNumber']	= $this->pension_lib->replacePhone($resArr['rPersonMobile']);
			$chCeoArray['birthday']		= $resArr['rPersonBrithday'] ? $resArr['rPersonBrithday'] : '-';
			$chCeoArray['inTime']		= $resArr['rRoomingTime'] ? $resArr['rRoomingTime'] : '-';
			$chCeoArray['pickup']		= $resArr['rPickupCheck'] == '1' ? '신청' : '미신청';
			$chCeoArray['memo']			= str_replace("\n",' ',strip_tags($resArr['rRequestInfo']));
			$chCeoArray['request']		= str_replace("\n",' ',strip_tags($resArr['rRequestInfo']));
			$chCeoArray['regDate']		= $resArr['rRegDate'];
			$chCeoArray['price']		= number_format($rBasicPrice);
			$chCeoArray['noPrice']		= number_format($noPrice);
			$chCeoArray['penalty']		= $cancelArrs ? ($cancelArrs['cancelPrice'] != '0' ? number_format($cancelArrs['cancelPrice']) . '원 (수수료 '  . $cancelArrs['penalty'] . '%) | 이용 ' . $cancelArrs['penaltyDay'] . '일 전 (' . date('m월 d일부터') . ')' : $cancelArrs['cancelPrice'] . '원') : '';

			$chKeyArray		= array_keys($chCeoArray);
			$chValArray		= array_values($chCeoArray);

			array_walk($chKeyArray, array($this, 'changeKeyFormat'));

			$msg['ceo']	= str_replace($chKeyArray, $chValArray, $msg['ceo']);
		}

		if($rUserSend)
		{
			$this->smsnew_model->sendSMS($msg['user'], preg_replace('/[^0-9]/', '', $resArr['rPersonMobile']), 'K', $curCfg['user']);
//			$this->smsnew_model->sendSMS($msg['user'], '01068991678', 'K', $curCfg['user']);
		}

		if($rCeoSend)
		{
			for($i=1; $i<=3; $i++)
			{
				if($resArr['ppbTel' . $i] && substr($resArr['ppbTel' . $i], 0, 2) == "01")
				{
					$ppbTel		= 'ppbTel' . $i;
					$this->smsnew_model->sendSMS($msg['ceo'], preg_replace('/[^0-9]/', '', $resArr[$ppbTel]), 'L');
//					$this->smsnew_model->sendSMS($msg['ceo'], '01068991678', 'L');
				}
			}
		}

	}

	function cancelPriceUpdate($rIdx){
		$resultArrs	= array();

		$rData = $this->reservation_model->getCancelRevList($rIdx);
		
		if(isset($rData['rIdx']) > 0){
			$DeferDay = (strtotime($rData['rStartDate'])-strtotime(date('Y-m-d')))/86400;
			/* 추가인원 관련 설정 Start */
			if($rData['rPriceAddType'] == "1"){
				$addPrice = $rData['rPriceAdult']+$rData['rPriceYoung']+$rData['rPriceBaby'];
			}else{
				$addPrice = 0;
			}
			/* 추가인원 관련 설정 End */
		   
		   /* 옵션추가 관련 설정 Start */
			$addOption = 0;
			if($rData['rOptionType'] != ""){
				if($rData['rOptionType'] == str_replace("|","",$rData['rOptionType'])){
					if($rData['rOptionType'] == "1"){
						$addOption = $addOption + ($rData['rOptionPrice']*$rData['rOptionNum']);
					}
				}else{
					$optionArray = explode("|",$rData['rOptionType']);
					$optionPriceArray = explode("|",$rData['rOptionPrice']);
					$optionNumArray = explode("|", $rData['rOptionNum']);
					for($i=0; $i< count($optionArray); $i++){
						if($optionArray[$i] == "1"){
							$addOption = $addOption + ($optionPriceArray[$i]*$optionNumArray[$i]);
						}
					}
				}
			}
			/* 옵션추가 관련 설정 End */
		   
			$resultPrice = $rData['rBasicPrice']-$rData['rPriceRoomDiscount']+$addPrice+$addOption;
			
			$cancelRevDay = round(abs(strtotime(date('Y-m-d'))-strtotime(substr($rData['rRegDate'],0,10)))/86400);
			
			if($rData['rRoot'] == "RO01"){
				$penaltyIdx = "1";
			}else{
				$penaltyIdx = $rData['mpIdx'];                    
			}
			$penaltyArrs = $this->reservation_model->getPenaltyInfo($penaltyIdx, $rData['rStartDate'], $cancelRevDay);
			
			$resultArrs['penalty']		= $penaltyArrs['cancelPercent'];
			$resultArrs['penaltyDay']	= $penaltyArrs['penaltyDay'];
			$resultArrs['cancelPrice']	= $resultPrice/100*$penaltyArrs['cancelPercent'];

			return $resultArrs;
		}
	}

	

	function changeKeyFormat(&$val, $key)
	{
		$val	= '#{' . $val . '}';
	}


}