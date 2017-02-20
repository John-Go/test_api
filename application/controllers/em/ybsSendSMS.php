<?php

class YbsSendSMS extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->config->load('_ybsMsg');
		$this->load->model('ybs_model');
		$this->load->model('em_model');
	}
	
	function index()
	{
		// init
		$infoUrl		= 'https://goo.gl/1afjlG';
		$userCode		= '';	// 고객 code
		$userMsg		= '';	// 고객 메세지
		$ceoCode		= '';	// 사장님 code
		$ceoMsg			= '';	// 사장님 메세지
		$dateNameArray	= array('일','월','화','수','목','금','토');
		$rIdx			= $this->input->get('rIdx');
		$msgTypeArray	= $this->config->item('ybsMsgType');
		if(!$rIdx)
		{
			exit;
		}
		
		// 주문 정보
		$resArr	= $this->ybs_model->getRevInfo($rIdx);
		
		// 결제수단
		if($resArr['rPaymentMethod'] == 'PM03')
		{
			$accArr	= $this->ybs_model->getAccountInfo($resArr['rCode']);
		}

		// 총인원
		$totalPeople	= '총 ' . (intval($resArr['pprInMin']) + intval($resArr['rNumAdult']) + intval($resArr['rNumYoung']) + intval($resArr['rNumBaby'])) . '명';

		$dateName		= $dateNameArray[date('w', strtotime($resArr['rStartDate']))];
		$btDay			= round((strtotime($resArr['rEndDate'])-strtotime($resArr['rStartDate']))/86400);

		if($resArr['mpsAddrFlag'] == "1"){
			$address	= $resArr['mpsAddr1New'];
		}else{
			$address	= $resArr['mpsAddr1'] . " " . $resArr['mpsAddr2'];
		}

		// 옵션
		$noPrice		= 0;		// 현장결제 금액
		$options		= @explode('|',$resArr['rOptionName']);
		$optionNum		= @explode('|',$resArr['rOptionNum']);
		$optionPrice	= @explode('|',$resArr['rOptionPrice']);
		$optionType		= @explode('|',$resArr['rOptionType']);
		foreach( $options as $key => $row ){
			$obj	=& $resArr['options'];
			$obj[]	= $row . ' ' . $optionNum[$key] . '개';
			
			// 현장 결제일 경우 현장결제 금액에 +
			if($optionType[$key] == '0')
			{
				$noPrice	+= ($optionPrice[$key] * $optionNum[$key]);
			}
		}

		$obj	= implode(', ', $obj);


		// 주문서 상태 확인
		switch($resArr['rPaymentState'])
		{
			// 입금대기
			case 'PS01':
				$userCode	= 'YBS_W';
				$ceoCode	= 'YBS_CW';

				$userMsg	= $msgTypeArray['U'][$userCode];
				$userMsg	= str_replace('#{pensionName}',	$resArr['rPension'],				$userMsg);
				$userMsg	= str_replace('#{roomName}',	$resArr['rPensionRoom'],			$userMsg);
				$userMsg	= str_replace('#{startDate}',	$resArr['rStartDate'],				$userMsg);
				$userMsg	= str_replace('#{dayName}',		$dateName,							$userMsg);
				$userMsg	= str_replace('#{day}',			$btDay,								$userMsg);
				$userMsg	= str_replace('#{user}',		$resArr['rPersonName'],				$userMsg);
				$userMsg	= str_replace('#{people}',		$totalPeople,						$userMsg);
				$userMsg	= str_replace('#{options}',		$resArr['options'],					$userMsg);
				$userMsg	= str_replace('#{price}',		number_format($resArr['rPrice']),	$userMsg);
				$userMsg	= str_replace('#{account}',		$accArr['ppaAccountInfo'],			$userMsg);
				$userMsg	= str_replace('#{limit}',		$accArr['ppaLimitDate'],			$userMsg);
				$userMsg	= str_replace('#{URL}',			$infoUrl,							$userMsg);

				$ceoMsg		= $msgTypeArray['C'][$ceoCode];
				$ceoMsg	= str_replace('#{roomName}',	$resArr['rPensionRoom'],				$ceoMsg);
				$ceoMsg	= str_replace('#{startDate}',	$resArr['rStartDate'],					$ceoMsg);
				$ceoMsg	= str_replace('#{dayName}',		$dateName,								$ceoMsg);
				$ceoMsg	= str_replace('#{day}',			$btDay,									$ceoMsg);
				$ceoMsg	= str_replace('#{user}',		$resArr['rPersonName'],					$ceoMsg);
				$ceoMsg	= str_replace('#{phoneNumber}',	$resArr['rPersonMobile'],				$ceoMsg);
				$ceoMsg	= str_replace('#{people}',		$totalPeople,							$ceoMsg);
				$ceoMsg	= str_replace('#{options}',		$resArr['options'],						$ceoMsg);
				$ceoMsg	= str_replace('#{regDate}',		$resArr['rRegDate'],					$ceoMsg);
				$ceoMsg	= str_replace('#{price}',		number_format($resArr['rPrice']),		$ceoMsg);
				break;

			// 예약완료
			case 'PS02':
				$userCode	= 'YBS_S';
				$ceoCode	= 'YBS_CS';

				if($resArr['pprInAdd'] == '1' && $resArr['pprInAddPay'] == 'P')
				{
					$noPrice	+= (($resArr['pprInAddPrice']*$resArr['rNumAdult'])*$btDay);
					$noPrice	+= (($resArr['pprInAddChild']*$resArr['rNumYoung'])*$btDay);
					$noPrice	+= (($resArr['pprInAddBaby']*$resArr['rNumBaby'])*$btDay);
				}

				$userMsg	= $msgTypeArray['U'][$userCode];
				$userMsg	= str_replace('#{revCode}',		$resArr['rCode'],					$userMsg);
				$userMsg	= str_replace('#{pensionName}',	$resArr['rPension'],				$userMsg);
				$userMsg	= str_replace('#{roomName}',	$resArr['rPensionRoom'],			$userMsg);
				$userMsg	= str_replace('#{startDate}',	$resArr['rStartDate'],				$userMsg);
				$userMsg	= str_replace('#{dayName}',		$dateName,							$userMsg);
				$userMsg	= str_replace('#{day}',			$btDay,								$userMsg);

				$userMsg	= str_replace('#{address}',		$address,							$userMsg);
				$userMsg	= str_replace('#{phoneNumber}',	$resArr['ppbTel1'],					$userMsg);

				$userMsg	= str_replace('#{user}',		$resArr['rPersonName'],				$userMsg);
				$userMsg	= str_replace('#{people}',		$totalPeople,						$userMsg);
				$userMsg	= str_replace('#{options}',		$resArr['options'],					$userMsg);
				$userMsg	= str_replace('#{price}',		number_format($resArr['rPrice']),	$userMsg);
				$userMsg	= str_replace('#{noPrice}',		number_format($noPrice),			$userMsg);
				$userMsg	= str_replace('#{URL}',			$infoUrl,							$userMsg);


				$ceoMsg		= $msgTypeArray['C'][$ceoCode];
				$ceoMsg	= str_replace('#{roomName}',	$resArr['rPensionRoom'],				$ceoMsg);
				$ceoMsg	= str_replace('#{startDate}',	$resArr['rStartDate'],					$ceoMsg);
				$ceoMsg	= str_replace('#{dayName}',		$dateName,								$ceoMsg);
				$ceoMsg	= str_replace('#{day}',			$btDay,									$ceoMsg);
				$ceoMsg	= str_replace('#{user}',		$resArr['rPersonName'],					$ceoMsg);
				$ceoMsg	= str_replace('#{phoneNumber}',	$resArr['rPersonMobile'],				$ceoMsg);
				$ceoMsg	= str_replace('#{people}',		$totalPeople,							$ceoMsg);
				$ceoMsg	= str_replace('#{options}',		$resArr['options'],						$ceoMsg);
				$ceoMsg	= str_replace('#{pickup}',		($resArr['rPickupCheck'] ? '신청' : '신청안함'),	$ceoMsg);
				$ceoMsg	= str_replace('#{inTime}',		$resArr['rRoomingTime'],				$ceoMsg);
				$ceoMsg	= str_replace('#{request}',		$resArr['rRequestInfo'],				$ceoMsg);
				$ceoMsg	= str_replace('#{regDate}',		$resArr['rRegDate'],					$ceoMsg);
				$ceoMsg	= str_replace('#{price}',		number_format($resArr['rPrice']),		$ceoMsg);
				$ceoMsg	= str_replace('#{noPrice}',		number_format($noPrice),				$ceoMsg);
				break;

			// 예약취소
			case 'PS04':
				$userCode	= 'YBS_C';
				$ceoCode	= 'YBS_CC';

				$userMsg	= $msgTypeArray['U'][$userCode];
				$userMsg	= str_replace('#{pensionName}',	$resArr['rPension'],				$userMsg);
				$userMsg	= str_replace('#{roomName}',	$resArr['rPensionRoom'],			$userMsg);
				$userMsg	= str_replace('#{startDate}',	$resArr['rStartDate'],				$userMsg);
				$userMsg	= str_replace('#{dayName}',		$dateName,							$userMsg);
				$userMsg	= str_replace('#{day}',			$btDay,								$userMsg);
				$userMsg	= str_replace('#{user}',		$resArr['rPersonName'],				$userMsg);
				$userMsg	= str_replace('#{URL}',			$infoUrl,							$userMsg);

				$ceoMsg		= $msgTypeArray['C'][$ceoCode];
				$ceoMsg	= str_replace('#{roomName}',	$resArr['rPensionRoom'],				$ceoMsg);
				$ceoMsg	= str_replace('#{startDate}',	$resArr['rStartDate'],					$ceoMsg);
				$ceoMsg	= str_replace('#{dayName}',		$dateName,								$ceoMsg);
				$ceoMsg	= str_replace('#{day}',			$btDay,									$ceoMsg);
				$ceoMsg	= str_replace('#{user}',		$resArr['rPersonName'],					$ceoMsg);
				break;


			// 미입금취소
			case 'PS08':
				$userCode	= 'YBS_C';
				$ceoCode	= 'YBS_CWC';

				$userMsg	= $msgTypeArray['U'][$userCode];
				$userMsg	= str_replace('#{pensionName}',	$resArr['rPension'],				$userMsg);
				$userMsg	= str_replace('#{roomName}',	$resArr['rPensionRoom'],			$userMsg);
				$userMsg	= str_replace('#{startDate}',	$resArr['rStartDate'],				$userMsg);
				$userMsg	= str_replace('#{dayName}',		$dateName,							$userMsg);
				$userMsg	= str_replace('#{day}',			$btDay,								$userMsg);
				$userMsg	= str_replace('#{user}',		$resArr['rPersonName'],				$userMsg);
				$userMsg	= str_replace('#{URL}',			$infoUrl,							$userMsg);


				$ceoMsg		= $msgTypeArray['C'][$ceoCode];
				$ceoMsg		= str_replace('#{roomName}',	$resArr['rPensionRoom'],				$ceoMsg);
				$ceoMsg		= str_replace('#{startDate}',	$resArr['rStartDate'],					$ceoMsg);
				$ceoMsg		= str_replace('#{dayName}',		$dateName,								$ceoMsg);
				$ceoMsg		= str_replace('#{day}',			$btDay,									$ceoMsg);
				$ceoMsg		= str_replace('#{user}',		$resArr['rPersonName'],					$ceoMsg);
				break;

		}

		
		// 고객
//		if(is_numeric($resArr['rPersonMobile']) && substr($resArr['rPersonMobile'],0,2) == "01")
//		{
//			$this->em_model->setTalk($userCode, $userMsg, str_replace('-','',$resArr['rPersonMobile']), 'U');
//		}
//		
//		// 사장님
//		if($resArr['ppbTel1'] != "" && substr($resArr['ppbTel1'],0,2) == "01")
//		{
//			$this->em_model->setTalk($ceoCode, $ceoMsg, str_replace('-', '', $resArr['ppbTel1']), 'C');
//		}
//		if($resArr['ppbTel2'] != "" && substr($resArr['ppbTel2'],0,2) == "01")
//		{
//			$this->em_model->setTalk($ceoCode, $ceoMsg, str_replace('-','',$resArr['ppbTel1']), 'C');
//		}
//		if($resArr['ppbTel3'] != "" && substr($resArr['ppbTel3'],0,2) == "01")
//		{
//			$this->em_model->setTalk($ceoCode, $ceoMsg, str_replace('-','',$resArr['ppbTel1']), 'C');
//		}
	}




    
}