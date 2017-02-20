<?php

class Aaaa extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->config->load('_msg_configNew');
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

		if($resArr['rVer'] == '1')
		{
			$revLists		= $this->smsnew_model->getRevInfoLists($resArr['rIdx']);
			$revCnt			= count($revLists);
			
			$roomArr	= array();
			$rStartArr	= array();
			foreach($revLists as $k => $arr)
			{
				$roomArr[]		= $arr['rPensionRoom'];
				$rStartArr[]	= $arr['rRevDate'] . '(' . $dayNameArray[date('w', strtotime($arr['rRevDate']))] . ')';
			}

			$resArr['rPensionRoom']	= implode(', ', $roomArr);
			$resArr['rStartDate']	= implode(', ', $rStartArr);

			$dayName	= '';
			$btDay		= '';
		}

		// 메세지 config
		$type	= 'YP_H_2';

		$curCfg	= $this->smsnew_model->getPensionMsgTemplateInfo($type);

		// 메세지 setting
		$chUserArray				= array();
		$chUserArray['pensionName']	= $resArr['rPension'];
		$chUserArray['roomName']	= $resArr['rPensionRoom'];
		$chUserArray['startDate']	= $resArr['rStartDate'];
		$chUserArray['dayName']		= $dayName;
		$chUserArray['day']			= $btDay;
		$chUserArray['url']			= 'http://goo.gl/M2rKOY';

		$chKeyArray		= array_keys($chUserArray);
		$chValArray		= array_values($chUserArray);

		array_walk($chKeyArray, array($this, 'changeKeyFormat'));

		$msg	= str_replace($chKeyArray, $chValArray, $curCfg['pmtUser']);

		// 고객 ( 알림톡 )
		$this->smsnew_model->sendSMS($msg, $personMobile, 'K', $curCfg['pmtCode']);
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
		$state			= $this->input->post('state');
		$auth			= $this->input->post('auth');
		$rUserSend		= isset($_POST['rUserSend']) ? $this->input->post('rUserSend') : true;		// 고객 전송 여부 1 or 0		default 1
		$rCeoSend		= isset($_POST['rCeoSend']) ? $this->input->post('rCeoSend') : true;			// 대표 전송 여부 1 or 0		default 1

		$rIdx			= intval($rIdx);
//		$rIdx			= 241852;
//		$state			= 'PS02';
//		$auth			= 'admin';
//		$rUserSend		= 1;
//		$rCeoSend		= 1;

		// S 입금대기, 완료 W 취소접수 C 취소

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

		$tempArr	= $this->smsnew_model->getPensionMsgTemplate($resArr['rRoot'], $state);
		
		$stateArr	= explode('||', trim($tempArr['stateArr'], '|'));

		$sendRevArr	= array();
		$prmIdxArr	= array();

		if($resArr['rVer'] == '1')
		{
			$sendFlagArr	= $this->smsnew_model->getRevMsgFlag($rIdx);
			$revLists		= $this->smsnew_model->getRevInfoLists($rIdx);
			$revCnt			= count($revLists);
			$optionLists	= $this->smsnew_model->getRevOptionLists($rIdx);
			$sendType		= '';

			foreach($revLists as $k => $arr)
			{
				if(in_array($arr['rState'], $stateArr))
				{
					$sendFlag	= true;

					if($auth != 'admin')
					{
						switch($arr['rState'])
						{
							case 'PS01':
								$sendFlag		= $sendFlagArr['REV'][$arr['priIdx']]['payWaitFlag'] > 0 ? false : true;
								$prmIdxArr[]	= $sendFlagArr['REV'][$arr['priIdx']]['prmIdx'];
								$sendType		= 'payWaitFlag';
								break;

							case 'PS02':
								$sendFlag		= $sendFlagArr['REV'][$arr['priIdx']]['paySuccessFlag'] > 0 ? false : true;
								$prmIdxArr[]	= $sendFlagArr['REV'][$arr['priIdx']]['prmIdx'];
								$sendType		= 'paySuccessFlag';
								break;

							case 'PS03':
							case 'PS04':
							case 'PS05':
							case 'PS07':
								$sendFlag		= $sendFlagArr['REV'][$arr['priIdx']]['cancelSuccessFlag'] > 0 ? false : true;
								$prmIdxArr[]	= $sendFlagArr['REV'][$arr['priIdx']]['prmIdx'];
								$sendType		= 'cancelSuccessFlag';
								break;

							case 'PS06':
								$sendFlag		= $sendFlagArr['REV'][$arr['priIdx']]['cancelWaitFlag'] > 0 ? false : true;
								$prmIdxArr[]	= $sendFlagArr['REV'][$arr['priIdx']]['prmIdx'];
								$sendType		= 'cancelWaitFlag';
								break;

							case 'PS08':
								$sendFlag		= $sendFlagArr['REV'][$arr['priIdx']]['noPayCancelFlag'] > 0 ? false : true;
								$prmIdxArr[]	= $sendFlagArr['REV'][$arr['priIdx']]['prmIdx'];
								$sendType		= 'noPayCancelFlag';
								break;
						}
					}

					if($sendFlag)
					{
						$sendRevArr[]	= $arr;
					}
				}
			}
		}
		else
		{
			$sendRevArr[]	= $resArr;
		}

		$sendRevCnt	= count($sendRevArr);
		
		if($sendRevCnt == 0)
		{
			return;
			exit;
		}

		$rBasicPrice		= 0;
		$noPrice		= 0;
		$cancelPrice	= 0;
		$remainPrice	= 0;

		$totalPeople	= 0;

		$optArray		= array();
		$optText		= '';
		

		// 날짜 관련 정보
		$dayNameArray	= array('일','월','화','수','목','금','토');

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


		if($resArr['rVer'] == '1')
		{
			$btDay			= 1;

			foreach($sendRevArr as $k => $arr)
			{
				$dayName		= $dayNameArray[date('w', strtotime($arr['rRevDate']))];
				$roomName		= $arr['rPensionRoom'];
				$revDate		= $arr['rRevDate'];
				$totalPeople	+= intval($arr['pprInMin']) + intval($arr['rAdult']) + intval($arr['rYoung']) + intval($arr['rBaby']);
				$adult			= $arr['pprInMin'] + $arr['rAdult'];
				$young			= $arr['rYoung'];
				$baby			= $arr['rBaby'];

				$rBasicPrice	+=	$arr['rBasicPrice'] - $arr['rSalePrice'];
				$rBasicPrice	+= ($arr['rAddType'] == '1' ? ($arr['rAdultPrice'] + $arr['rYoungPrice'] + $arr['rBabyPrice']) : 0);
				
				$noPrice	+= ($arr['rAddType'] == '2' ? ($arr['rAdultPrice'] + $arr['rYoungPrice'] + $arr['rBabyPrice']) : 0);

				$remainPrice	+= $arr['rPrice'];

				$sendRevInfoArr[]	= array(
					'room'		=> $roomName,
					'startDate'	=> $revDate,
					'dayName'	=> $dayName,
					'day'		=> '1',
					'adult'		=> $adult,
					'young'		=> $young,
					'baby'		=> $baby
				);
			}
		
			$samOptArr	= array();
			foreach($optionLists as $k => $arr)
			{
				if(($state == 'PS01' || $state == 'PS02') || ( ($state != 'PS01' && $state != 'PS02') && $revCnt == $sendRevCnt) )
				{
					$rBasicPrice		+= ($arr['proType'] == '1' ? $arr['proBasicPrice'] * $arr['proNumber'] : 0);
					$noPrice		+= ($arr['proType'] == '2' ? $arr['proBasicPrice'] * $arr['proNumber'] : 0);

					$remainPrice	+= $arr['proType'] == '1' ? $arr['proPrice'] : 0;
				}
				
				$samOptArr[$arr['ppoIdx']]['proName']		= $arr['proName'];
				$samOptArr[$arr['ppoIdx']]['proUnit']		= $arr['proUnit'];
				$samOptArr[$arr['ppoIdx']]['proNumber']		+= $arr['proNumber'];
			}

			foreach($samOptArr as $k => $arr)
			{
				$optArray[]	= $arr['proName'] . ' - ' . $arr['proNumber']. $resArr['proUnit'];
			}
			
			$cancelPrice	+= $rBasicPrice - $remainPrice;
		}
		else
		{
			$roomName		= $resArr['rPensionRoom'];
			$revDate		= $resArr['rStartDate'];

			// 기본
			$rBasicPrice	= $resArr['rBasicPrice'] - $resArr['rPriceRoomDiscount'];

			// 인원
			$totalPeople	+= $resArr['pprInMin'] + $resArr['rNumAdult'] + $resArr['rNumYoung'] + $resArr['rNumBaby'];
			$adult			= $resArr['pprInMin'] + $resArr['rNumAdult'];
			$young			= $resArr['rNumYoung'];
			$baby			= $resArr['rNumBaby'];

			$noPrice		+= ($resArr['rPriceAddType'] == '2' ? ($resArr['rPriceAdult'] + $resArr['rPriceYoung'] + $resArr['rPriceBaby']) : 0);
			$rBasicPrice	+= ($resArr['rPriceAddType'] == '1' ? ($resArr['rPriceAdult'] + $resArr['rPriceYoung'] + $resArr['rPriceBaby']) : 0);
			
			// 날짜 관련 정보
			$dayName		= $dayNameArray[date('w', strtotime($resArr['rStartDate']))];
			$btDay			= round(abs(strtotime($resArr['rEndDate'])-strtotime($resArr['rStartDate']))/86400);
			
			$sendRevInfoArr[]	= array(
				'room'		=> $roomName,
				'startDate'	=> $revDate,
				'dayName'	=> $dayName,
				'day'		=> $btDay,
				'adult'		=> $adult,
				'young'		=> $young,
				'baby'		=> $baby
			);

			// 추가 옵션
			if($resArr['rOptionName'])
			{
				$optArray	= array();
				if(str_replace('|', '', $resArr['rOptionName']) == $resArr['rOptionName'])
				{
					$optArray[]	= $resArr['rOptionName'] . ' - ' . $resArr['rOptionNum']. $resArr['rOptionUnit'];

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
					$optUnitArray	= explode('|', $resArr['rOptionUnit']);
					$optCnt			= count($optNameArray);

					for($i=0; $i<$optCnt; $i++)
					{
						$optArray[]	= $optNameArray[$i] . ' - ' . $optNumArray[$i] . $optUnitArray[$i];

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
			}
		}
//		$rBasicPrice	-= ($resArr['rPriceMileage'] > '0' ? $resArr['rPriceMileage'] : 0);
		 
		$optText	= implode(', ', $optArray);
		
		$linkUrl	= 'https://goo.gl/1afjlG';

		// root 에 따라 config 설정
		switch($resArr['rRoot'])
		{
			case 'RO01':	// 예약대행 (야펜)
				$pensionName	= '야놀자펜션';
				$ceoPensionName	= '야놀자펜션';
				$linkUrl		= 'http://goo.gl/M2rKOY';
				break;
			case 'RO02':	// YBS
				$pensionName	= $resArr['rPension'];
				$ceoPensionName	= '홈페이지 예약';
				break;
			case 'RO03':	// 사장님페이지
				$pensionName	= $resArr['rPension'];
				$ceoPensionName	= '홈페이지 예약';
				break;

			default:
				die('');
				break;
		}
	
		if($sendRevCnt == 1)
		{
			$curCfg	= $tempArr['lists']['0'];
		}
		else
		{
			$curCfg	= $tempArr['lists']['1'];
		}

		if(!$curCfg)
		{
			die('NO_SETTING_STATE');
		}

		// 입금 대기 일경우
		if($resArr['rPaymentState'] == 'PS01')
		{
			// 예약대행 (야펜)
			if($resArr['rRoot'] == 'RO01')
			{
				$accArr			= $this->smsnew_model->getAccountInfoNew($resArr['rCode']);
				
				$account		= $accArr['bankname'] . ' ' . $accArr['account'] . ' ' . $accArr['ipgm_name'];
				$accountLimit	= date('Y-m-d H:i:s', strtotime($accArr['ipgm_date']));

				if($resArr['rPaymentMethod'] == 'PM05')
				{
					$account		= '국민은행 445701-01-243880 (주)야놀자트래블';
					$accountLimit	= '-';
				}
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
		$cancelArrs	= $this->cancelPriceUpdateNew($rIdx);

		if($resArr['rDuplicateFlag'] == '1')
		{
			$cancelArrs['cancelPrice']	= '0';
		}


		// 상태에 따라 설정된 config
		$msg['user']	= $curCfg['pmtUser'];
		$msg['ceo']		= $curCfg['pmtCeo'];

		// user 메시지 정보
		if($rUserSend)
		{
			$chUserArray				= array();
			$chUserArray['pensionName']	= $pensionName;
			$chUserArray['revCode']		= $resArr['rCode'];
			$chUserArray['pension']		= $resArr['rPension'];
			$chUserArray['roomName']	= $roomName;
			$chUserArray['startDate']	= $revDate;
			$chUserArray['dayName']		= $dayName;
			$chUserArray['day']			= $btDay;
			$chUserArray['address']		= $address;
			$chUserArray['phoneNumber']	= $pensionTel;
			$chUserArray['user']		= $resArr['rPersonName'];
			$chUserArray['adult']		= $adult;
			$chUserArray['young']		= $young;
			$chUserArray['baby']		= $baby;
			$chUserArray['people']		= '총 ' . $totalPeople;
			$chUserArray['options']		= $optText;
			$chUserArray['price']		= number_format($rBasicPrice - $resArr['rPriceMileage']);
			$chUserArray['noPrice']		= number_format($noPrice);

			$chUserArray['penalty']		= $cancelArrs['cancelPrice'] > 0 ? number_format($cancelArrs['cancelPrice']) . '원' : '-';
			

			$chUserArray['account']		= $account;
			$chUserArray['limit']		= $accountLimit;
			$chUserArray['URL']			= $linkUrl;
			$chUserArray['url']			= $linkUrl;
			
			$chKeyArray		= array_keys($chUserArray);
			$chValArray		= array_values($chUserArray);

			array_walk($chKeyArray, array($this, 'changeKeyFormat'));

			$msg['user']	= str_replace($chKeyArray, $chValArray, $msg['user']);
		}

		if($rCeoSend)
		{
			// ceo 메시지 정보
			$chCeoArray	= array();
			$chCeoArray['pensionName']	= $ceoPensionName;
			$chCeoArray['rCode']		= $resArr['rCode'];
			$chCeoArray['roomName']		= $roomName;
			$chCeoArray['startArea']	= $resArr['rPersonSi'];

			$revInfoListText	= array();
			foreach($sendRevInfoArr as $k => $arr)
			{	
				$revInfoListText[]	= '객실명 : ' . $arr['room'] . '
입실일 : ' . $arr['startDate'] . '(' . $arr['dayName'] . ')' . ($arr['day'] > '1' ? (' / ' . $arr['day'] . '박') : '') . '
인원 : 성인' . $arr['adult'] . '명 / 아동' . $arr['young'] . '명 / 유아' . $arr['baby'] . '명';
			}
			
			$chCeoArray['revInfoLists']	= implode("\r\n\r\n", $revInfoListText);
			
			$chCeoArray['room']			= $roomName;
			$chCeoArray['startDate']	= $revDate;
			$chCeoArray['dayName']		= $dayName;
			$chCeoArray['day']			= $btDay;
			$chCeoArray['adult']		= $adult;
			$chCeoArray['young']		= $young;
			$chCeoArray['baby']			= $baby;


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
			//$chCeoArray['penalty']		= $cancelArrs ? ($cancelArrs['cancelPrice'] != '0' ? number_format($cancelArrs['cancelPrice']) . '원 (수수료 '  . $cancelArrs['penalty'] . '%) | 이용 ' . $cancelArrs['penaltyDay'] . '일 전 (' . date('m월 d일부터') . ')' : $cancelArrs['cancelPrice'] . '원') : '';
			
			$chCeoArray['penalty']		= $cancelArrs['cancelPrice'] > 0 ? number_format($cancelArrs['cancelPrice']) . '원' : '-';
			

			$chKeyArray		= array_keys($chCeoArray);
			$chValArray		= array_values($chCeoArray);

			array_walk($chKeyArray, array($this, 'changeKeyFormat'));

			$msg['ceo']	= str_replace($chKeyArray, $chValArray, $msg['ceo']);
		}

		
		if(count($prmIdxArr) > 0)
		{
			$this->smsnew_model->uptRevMsgFlag($prmIdxArr, $sendType);
		}


		if($rUserSend)
		{
			$this->smsnew_model->sendSMS($msg['user'], preg_replace('/[^0-9]/', '', $resArr['rPersonMobile']), 'K', $curCfg['pmtCode']);
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


	function cancelPriceUpdateNew($rIdx){
        $rData		= $this->reservation_model->getCancelRevList($rIdx);
        $revInfo	= $this->reservation_model->getRevLists($rIdx);
		$totalCancelPrice	= 0;
	
        $penaltyPay = "";
        if(isset($rData['rIdx']) > 0){
				
			if($rData['rDuplicateFlag'] == '1')
			{
				$resultArrs['cancelPrice']	= 0;
				return $resultArrs;
			}

			if($rData['rVer'] == "1"){
                if(count($revInfo) > 0){
                    foreach($revInfo as $revInfo){
						
						if(($revInfo['rCancelDate'] && $revInfo['rCancelDate'] != "0000-00-00 00:00:00") && ($revInfo['rState'] != "PS01" && $revInfo['rState'] != "PS02"))
						{
							$totalCancelPrice	+= $revInfo['rCancelPrice'];
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
                       
                        $resultPrice = $revInfo['rBasicPrice']-$revInfo['rSalePrice']+$addPrice;
                        
                        $cancelRevDay = round((strtotime(date('Y-m-d'))-strtotime(substr($rData['rRegDate'],0,10)))/86400);
                        
                        if($cancelRevDay < 0){
                            $cancelRevDay = 0;
                        }
                        if($rData['rRoot'] == "RO01"){
                            $penaltyIdx = "1";
                        }else{
                            $penaltyIdx = $rData['mpIdx'];
                        }
                        $penalty = $this->reservation_model->getPenaltyInfo($penaltyIdx, $revInfo['rRevDate'], $cancelRevDay);

						$resultArrs['penalty']		= $penalty['cancelPercent'];
						$resultArrs['penaltyDay']	= $penalty['penaltyDay'];
						$resultArrs['cancelPrice']	= $resultPrice/100*$penalty['cancelPercent'];

                        $cancelPrice		= $resultPrice/100*$resultArrs['penalty'];
						$totalCancelPrice	+= $cancelPrice;
                        if($penaltyPay == ""){
                            $penaltyPay = $resultArrs['penalty'];
                        }
                        
                        $this->reservation_model->uptReserveInfo($revInfo['priIdx'], $cancelPrice);
                    }
                }
                
                
                $optionLists = $this->reservation_model->getRevOptionLists($rIdx);
                
                if(count($optionLists) > 0){
                    foreach($optionLists as $optionLists){
						
						if($optionLists['proCancelDate'] && $optionLists['proCancelDate'] != "0000-00-00 00:00:00"){
							$totalCancelPrice	+= $optionLists['proCancelPrice'];
							continue;
						}

                        $cancelPrice = 0;
                        if($optionLists['proType'] == "1"){
                            $optionPrice = $optionLists['proPrice'];
                            if($optionPrice > 0){

								if($rData['rDuplicateFlag'] == '1')
								{
									$cancelPrice	= 0;
								}
								else
								{
									$cancelPrice = $optionPrice/100*$penaltyPay;
								}

								$totalCancelPrice	+= $cancelPrice;
                            }
                            
                            $this->reservation_model->uptOption($optionLists['proIdx'], $cancelPrice);
                        }
                    }
                }

				//$resultArrs['penalty']		= $penaltyArrs['cancelPercent'];
				//$resultArrs['penaltyDay']	= $penaltyArrs['penaltyDay'];
				$resultArrs['cancelPrice']	= $totalCancelPrice;

				return $resultArrs;

            }
			else
			{

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
				$penalty = $this->reservation_model->getPenaltyInfo($penaltyIdx, $rData['rStartDate'], $cancelRevDay);
				
				$cancelPrice = $resultPrice/100*$penalty;

				$resultArrs['penalty']		= $penalty['cancelPercent'];
				$resultArrs['penaltyDay']	= $penalty['penaltyDay'];
				$resultArrs['cancelPrice']	= $resultPrice/100*$penalty['cancelPercent'];

				$this->reservation_model->uptReserve($rData['rIdx'], $cancelPrice);

				return $resultArrs;
			}

        }
    }

	

	function changeKeyFormat(&$val, $key)
	{
		$val	= '#{' . $val . '}';
	}

	function sendAuthCode()
	{
		$curCfg	= $this->smsnew_model->getPensionMsgTemplateInfo('YP_MCN_1');

		$authCode	= $this->input->post('authCode');
		$receiver	= $this->input->post('receiver');
		
		$chArray	= array();
		$chArray['certifyKey']		= '[' . $authCode . ']';

		$chKeyArray		= array_keys($chArray);
		$chValArray		= array_values($chArray);

		array_walk($chKeyArray, array($this, 'changeKeyFormat'));

		$msg	= str_replace($chKeyArray, $chValArray, $curCfg['pmtUser']);

		$this->smsnew_model->sendSMS($msg, preg_replace('/[^0-9]/', '', $receiver), 'K', $curCfg['pmtCode']);
	}


}