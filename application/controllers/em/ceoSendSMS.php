<?php
class CeoSendSMS extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('cron/ceosms_model');
    }

    function index(){
		$rIdx		= $this->input->get('rIdx');
		$type		= $this->input->get('type');
		$rUserSend	= $this->input->get('rUserSend');		// 고객에게 알림서비스 1 - 사용  0 - 미사용
		$rCeoSend	= $this->input->get('rCeoSend');		// 업주에게 알림서비스 1 - 사용  0 - 미사용
		$msgCode	= "";

		if(!$rIdx){
			exit;
		}
		if(!$type){
			$type = "K";
		}
		$userMsg		= "";
		$ceoMsg			= "";	
		$dateNameArray	= array('일','월','화','수','목','금','토');
		
		// 관련 정보
		$info			= $this->ceosms_model->getRevInfo($rIdx);
		$dateName		= $dateNameArray[date('w', strtotime($info['rStartDate']))];

		$btDay			= round((strtotime($info['rEndDate'])-strtotime($info['rStartDate']))/86400);

		if($info['mpsAddrFlag'] == "1"){
			$address	= $info['mpsAddr1New'];
		}else{
			$address	= $info['mpsAddr1'] . " " . $info['mpsAddr2'];
		}
		
		// 예약완료시
		if($info['rPaymentState'] == "PS02")
		{
			$msgCode	= "YBS_DS";

			// 고객
			$userMsg	= "[예약완료]
펜션명 : " . $info['rPension'] . "
객실명 : " . $info['rPensionRoom'] . "
입실일 : " . $info['rStartDate'] . "(" . $dateName . ") / " . $btDay . "박

주소 : " . $address . "
연락처 : " . $info['ppbTel1'] . "

예약이 완료되었습니다. 즐거운 여행 되세요.";
			
			// 업주
			$ceoMsg	= "[홈페이지 예약-예약완료] 

객실명 : " . $info['rPensionRoom'] . "
입실일 : " . $info['rStartDate'] . "(" . $dateName . ") / " . $btDay . "박

예약자명 : " . $info['rPersonName'] . "
휴대폰 : " . $info['rPersonMobile'] . "

예약접수: " . $info['rRegDate'] . "
판매금액 : " . number_format($info['rPrice']) . "원

[사장님 페이지 바로가기] 
http://ceo.yapen.co.kr 
[카카오톡 사장님 고객센터] 
http://goo.gl/scHffC";
		}
		// 입금대기
		else if($info['rPaymentState'] == "PS01")
		{
			$msgCode	= "YBS_DW";

			// 고객
			$userMsg	= "[입금대기]
펜션명 : " . $info['rPension'] . "
객실명 : " . $info['rPensionRoom'] . "
입실일 : " . $info['rStartDate'] . "(" . $dateName . ") / " . $btDay . "박

예약자 : " . $info['rPersonName'] . "

결제금액 : " . $info['rPrice'] . "원
입금계좌 : " . $info['ppaBank'] . " " . $info['ppaNumber'] . " " . $info['ppaOwner'];
			
			// 업주
			$ceoMsg		= "[홈페이지 예약-입금대기]
" . $info['rPensionRoom'] . " " . $info['rStartDate'] . "(" . $dateName . ")/" . $btDay . "박
" . $info['rPersonName'] . "/" . number_format($info['rPrice']) . "원 입금대기중입니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC";
		}
		// 예약취소
		else if($info['rPaymentState'] == 'PS04')
		{
			$msgCode	= "YBS_DC";

			// 고객
			$userMsg	= "[예약취소]
펜션명 : " . $info['rPension'] . "
객실명 : " . $info['rPensionRoom'] . "
입실일 : " . $info['rStartDate'] . "(" . $dateNameArray[date('w', strtotime($info['rStartDate']))] . ") / " . $btDay . "박
예약자 : " . $info['rPersonName'] . "

취소가 완료되었습니다";
			
			// 업주
			$ceoMsg		= "[홈페이지 예약-예약취소]
" . $info['rPensionRoom'] . " " . $info['rStartDate'] . "(" . $dateName . ")/" . $btDay . "박
" . $info['rPersonName'] . " 예약취소되었습니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC";
		}
		// 미입금취소
		else
		{
			$msgCode	= "YBS_DC";

			// 고객
			$userMsg	= "[예약취소]
펜션명 : " . $info['rPension'] . "
객실명 : " . $info['rPensionRoom'] . "
입실일 : " . $info['rStartDate'] . "(" . $dateNameArray[date('w', strtotime($info['rStartDate']))] . ") / " . $btDay . "박
예약자 : " . $info['rPersonName'] . "

취소가 완료되었습니다";
			
			// 업주
			$ceoMsg		= "[홈페이지 예약-미입금취소]
" . $info['rPensionRoom'] . " " . $info['rStartDate'] . "(" . $dateName . ")/" . $btDay . "박
" . $info['rPersonName'] . " 예약취소되었습니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC";
		}

		// 고객
		if($rUserSend == 1 && is_numeric($info['rPersonMobile']) && substr($info['rPersonMobile'],0,2) == "01")
		{
			$this->ceosms_model->insSendSMS($userMsg, $info['rPersonMobile'], $type, $msgCode, $info['rIdx'], $info['mpIdx']);
		}
		
		// 대표
		if($rCeoSend)
		{
			if($info['ppbTel1'] != "" && substr($info['ppbTel1'],0,2) == "01")
			{
				$this->ceosms_model->insSendSMS($ceoMsg, $info['ppbTel1'], "", "", $info['rIdx'], $info['mpIdx']);
			}
			if($info['ppbTel2'] != "" && substr($info['ppbTel2'],0,2) == "01")
			{
				$this->ceosms_model->insSendSMS($ceoMsg, $info['ppbTel2'], "", "", $info['rIdx'], $info['mpIdx']);
			}
			if($info['ppbTel3'] != "" && substr($info['ppbTel3'],0,2) == "01")
			{
				$this->ceosms_model->insSendSMS($ceoMsg, $info['ppbTel3'], "", "", $info['rIdx'], $info['mpIdx']);
			}
		}
	}


}