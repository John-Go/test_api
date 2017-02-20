<?php

class SmsSend extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->config->load('_msg');
        $this->load->model('em_model');
        $this->load->library('pension_lib');
    }
    
    /*
     * index : 고정 템플릿 문자
     * happyCall : 해피콜 문자
     * account : 현장결제 안내 문자
     * coupon : 중복예약 쿠폰문자
     * rev : 예약 문자
     */
    
    function index(){
        $type = $this->input->get('type');
        $receiver = $this->input->get('receiver');
        
        $ceoArray = array('YP_CALL1','YP_CALL2','YP_CALL3','YP_CALL4','YP_CALL5','YP_CALL6');        
        
        $msgTypeArray = $this->config->item('msgType');
        $msg = $msgTypeArray[$type];            
        
        if(in_array($type, $ceoArray)){
            $this->em_model->setTalk($type, $msg, str_replace('-','',$receiver),'C');            
        }else{
            $this->em_model->setTalk($type, $msg, str_replace('-','',$receiver),'U');
        }
    }
    
    function happyCall(){
        $rIdx = $this->input->get('rIdx');
        
        $rData = $this->em_model->getRevInfo($rIdx);
        
        $dayNameArray = array('일','월','화','수','목','금','토');
        $dayName = $dayNameArray[date('w', strtotime($rData['rStartDate']))];
        $totalPeople = $rData['pprInMin']+$rData['rNumAdult']+$rData['rNumYoung']+$rData['rNumBaby'];
        
        $msg = "[야놀자펜션] 고객님께서 예약하신 ".$rData['rPension']." ".$rData['rPensionRoom']." ".$rData['rStartDate']."(".$dayName.") / ".$totalPeople."명 에 대한 예약 건에 대해 펜션주와 예약확인 해피콜이 완료 되었습니다.
안심하시고, 즐거운 여행 되세요!";

        $this->em_model->setTalk('YP_H', $msg, str_replace('-','',$rData['rPersonMobile']),'U');
    }
    
    function account(){
        $rIdx = $this->input->get('rIdx');
        
        $rData = $this->em_model->getRevInfo($rIdx);
        
        $totalPeople = $rData['pprInMin']+$rData['rNumAdult']+$rData['rNumYoung']+$rData['rNumBaby'];
        
        $msg = "[야놀자펜션-안내]
".$rData['rPension']." 인원추가 ".$totalPeople."명
".number_format((int)$rData['rPriceAdult']+(int)$rData['rPriceYoung']+(int)$rData['rPriceBaby'])."원 현장결제 부탁드립니다.";

        $this->em_model->setTalk('YP_CALL10', $msg, str_replace('-','',$rData['rPersonMobile']),'U');
    }
    
    function coupon(){
        $receiver = $this->input->get('receiver');
        $coupon = $this->input->get('coupon');
        
        $msg = "[야놀자펜션]
중복예약 발생 쿠폰지급
".$coupon."
이용에 불편을드려 죄송합니다.
감사합니다.";

        $this->em_model->setTalk('YP_CALL11', $msg, str_replace('-','',$receiver),'U');
    }

    function rev(){
        $rIdx = $this->input->get('rIdx');
        
        if(!$rIdx){
            return; exit;
        }
        
        $setTypeArray = array('YP_RS','YP_RC','YP_RW','YP_RCW','YPC_RSS','YPC_RC','YPC_RW','YPC_RCW','YP_H','YP_CALL10');
        $ceoArray = array('YPC_RSS','YPC_RC','YPC_RW','YPC_RCW');        
        
        $rData = $this->em_model->getRevInfo($rIdx);
        if(!isset($rData['rIdx'])){
            return; exit;
        }
        $dayNameArray = array('일','월','화','수','목','금','토');
        $dayName = $dayNameArray[date('w', strtotime($rData['rStartDate']))];
        $totalPeople = $rData['pprInMin']+$rData['rNumAdult']+$rData['rNumYoung']+$rData['rNumBaby'];
        $btDay = round(abs(strtotime($rData['rEndDate'])-strtotime($rData['rStartDate']))/86400);
        $pensionTel = "";
        
        if($rData['ppbTel1']){
            $pensionTel = $this->pension_lib->replacePhone(str_replace('-','',$rData['ppbTel1']));
        }else{
            $pensionTel = $this->pension_lib->replacePhone(str_replace('-','',$rData['mpsTel']));
        }
        
        if($rData['mpsAddrFlag'] && $rData['mpsAddrFlag'] == "1"){
            $address = $rData['mpsAddr1New'];
        }else{
            $address = $rData['mpsAddr1']." ".$rData['mpsAddr2'];
        }         
        if($rData['rPickupCheck'] == "1"){
            $pickup = "신청";
        }else{
            $pickup = "미신청";
        }
        if($rData['rPriceAddType'] == "2"){
            $ceoPrice = (int)$rData['rBasicPrice']-(int)$rData['rPriceRoomDiscount'];
        }else{
            $ceoPrice = (int)$rData['rBasicPrice']-(int)$rData['rPriceRoomDiscount']+(int)$rData['rPriceAdult']+(int)$rData['rPriceYoung']+(int)$rData['rPriceBaby'];
        }
        
        if($rData['rOptionPrice'] != str_replace("|","",$rData['rOptionPrice'])){
            $optionArray		= explode("|", $rData['rOptionPrice']);
            $optionNumArray		= explode("|", $rData['rOptionNum']);
            $optionTypeArray	= explode("|", $rData['rOptionType']);
            for($i=0; $i< count($optionArray); $i++){
				if($optionTypeArray[$i] == '1')
				{
					$ceoPrice	+= ($optionArray[$i] * $optionNumArray[$i]);
				}
            }
        }else{
        	if($rData['rOptionType'] == '1')
			{
				$ceoPrice = $ceoPrice+(int)$rData['rOptionPrice'];
			}
        }
        
        $msgArray = array(
            'YP_RS' => "[야놀자펜션-펜션예약 완료]
펜션명: ".$rData['rPension']."
객실명: ".$rData['rPensionRoom']."
이용일: ".$rData['rStartDate']."(".$dayName.") ".$btDay."박
예약자명: ".$rData['rPersonName']."
인원: 총 ".number_format($totalPeople)."명
결제금액: ".number_format($rData['rPrice'])."원

펜션주소: ".$address."
펜션연락처: ".$pensionTel."

예약번호: ".$rData['rCode']."
(예약내역 조회 시 필요한 번호입니다)

반드시 출발 전 펜션과 통화하여 예약확정여부 확인부탁드립니다.",            
            'YP_RC' => "[야놀자펜션-예약취소]
펜션명: ".$rData['rPension']."
객실명: ".$rData['rPensionRoom']."
이용일: ".$rData['rStartDate']."(".$dayName.") ".$btDay."박
예약자명: ".$rData['rPersonName']."

예약이 취소되었습니다.
고객센터 : 1644-4816",                
            'YP_RW' => "[야놀자펜션-입금대기]
펜션명: ".$rData['rPension']."
객실명: ".$rData['rPensionRoom']."
이용일: ".$rData['rStartDate']."(".$dayName.") ".$btDay."박
예약자명: ".$rData['rPersonName']."
인원: 총 ".number_format($totalPeople)."명
결제금액: ".number_format($rData['rPrice'])."원
입금기한: ".substr($rData['LGD_LimitDate'],0,4)."년".substr($rData['LGD_LimitDate'],4,2)."월".substr($rData['LGD_LimitDate'],6,2)."일 ".substr($rData['LGD_LimitDate'],8,2)."시".substr($rData['LGD_LimitDate'],10,2)."분".substr($rData['LGD_LimitDate'],12,2)."초 까지",
                
            'YP_RCW' => "[야놀자펜션-예약취소]
펜션명: ".$rData['rPension']."
객실명: ".$rData['rPensionRoom']."
이용일: ".$rData['rStartDate']."(".$dayName.") ".$btDay."박
예약자명: ".$rData['rPersonName']."

취소 접수가 완료되었습니다
고객센터 : 1644-4816",
                
            'YPC_RSS' => "[야놀자펜션-예약완료] 

객실명 : ".$rData['rPensionRoom']."
입실일 : ".$rData['rStartDate']."(".$dayName.") / ".$btDay."박 
인원 : 성인".number_format($rData['pprInMin']+$rData['rNumAdult'])."명/소아".number_format($rData['rNumYoung'])."명/유아".$rData['rNumBaby']."명 

예약자명 : ".$rData['rPersonName']."
휴대폰 : ".$this->pension_lib->replacePhone($rData['rPersonMobile'])."
생년월일 : ".$rData['rPersonBrithday']."
입실예정 : ".str_replace(':','시 ',$rData['rRoomingTime'])."분
픽업신청 : ".$pickup."
요청사항 : ".str_replace("\n",' ',strip_tags($rData['rRequestInfo']))."

예약접수: ".$rData['rRegDate']."
판매금액 : ".number_format($ceoPrice)."원

[사장님 페이지 바로가기] 
http://ceo.yapen.co.kr 
[카카오톡 사장님 고객센터] 
http://goo.gl/scHffC",
                
            'YPC_RC' => "[야놀자펜션-예약취소]
".$rData['rPensionRoom']." ".$rData['rStartDate']."(".$dayName.")/".$btDay."박
".$rData['rPersonName']." 예약취소되었습니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",
            'YPC_RW' => "[야놀자펜션-입금대기]
".$rData['rPensionRoom']." ".$rData['rStartDate']."(".$dayName.")/".$btDay."박
".$rData['rPersonName']."/".$totalPeople."명 ".number_format($ceoPrice)."원 입금대기중입니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",
            'YPC_RCW' => "[야놀자펜션-미입금취소]
".$rData['rPensionRoom']." ".$rData['rStartDate']."(".$dayName.")/".$btDay."박
".$rData['rPersonName']." 예약취소되었습니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",
            'YP_H' => "[야놀자펜션] 고객님께서 예약하신 ".$rData['rPension']." ".$rData['rPensionRoom']." ".$rData['rStartDate']."(".$dayName.") / ".$totalPeople."명 에 대한 예약 건에 대해 펜션주와 예약확인 해피콜이 완료 되었습니다.
안심하시고, 즐거운 여행 되세요!"
        );
        
        if($rData['rPaymentState'] == "PS01"){
            $ceoMsg = $msgArray['YPC_RW'];            
            $userMsg = $msgArray['YP_RW'];
            $ceoCode = "YPC_RW";
            $userCode = "YP_RW";
        }else if($rData['rPaymentState'] == "PS02"){
            $ceoMsg = $msgArray['YPC_RSS'];
            $userMsg = $msgArray['YP_RS'];
            $ceoCode = "YPC_RSS";
            $userCode = "YP_RS";
        }else if($rData['rPaymentState'] == "PS08"){
            $ceoMsg = $msgArray['YPC_RCW'];
            $userMsg = $msgArray['YP_RCW'];
            $ceoCode = "YPC_RCW";
            $userCode = "YP_RCW";
        }else{
            $ceoMsg = $msgArray['YPC_RC'];
            $userMsg = $msgArray['YP_RC'];
            $ceoCode = "YPC_RC";
            $userCode = "YP_RC";
        }
        
        
        $countFlag = $this->em_model->checkReMsg($ceoMsg, str_replace('-','',$rData['ppbTel1']));
        $countUserFlag = $this->em_model->checkReMsgUser($ceoMsg, str_replace('-','',$rData['ppbTel1']));
        
        if(($countFlag+$countUserFlag) > 0){
            return;
            exit;
        }        
        
        
        if($rData['ppbTel1'] != "" && substr($rData['ppbTel1'],0,2) == "01"){
            $this->em_model->setTalk($ceoCode, $ceoMsg, str_replace('-','',$rData['ppbTel1']),'C');
        }
        if($rData['ppbTel2'] != "" && substr($rData['ppbTel2'],0,2) == "01"){
            $this->em_model->setTalk($ceoCode, $ceoMsg, str_replace('-','',$rData['ppbTel2']),'C');
        }
        if($rData['ppbTel3'] != "" && substr($rData['ppbTel3'],0,2) == "01"){
            $this->em_model->setTalk($ceoCode, $ceoMsg, str_replace('-','',$rData['ppbTel3']),'C');
        }            
    
        $this->em_model->setTalk($userCode, $userMsg, str_replace('-','',$rData['rPersonMobile']),'U');
              
    }

    function revCeo(){
        $rIdx = $this->input->get('rIdx');
        
        if(!$rIdx){
            return; exit;
        }
        
        $setTypeArray = array('YP_RS','YP_RC','YP_RW','YP_RCW','YPC_RSS','YPC_RC','YPC_RW','YPC_RCW','YP_H','YP_CALL10');
        $ceoArray = array('YPC_RSS','YPC_RC','YPC_RW','YPC_RCW');        
        
        $rData = $this->em_model->getRevInfo($rIdx);
        if(!isset($rData['rIdx'])){
            return; exit;
        }
        $dayNameArray = array('일','월','화','수','목','금','토');
        $dayName = $dayNameArray[date('w', strtotime($rData['rStartDate']))];
        $totalPeople = $rData['pprInMin']+$rData['rNumAdult']+$rData['rNumYoung']+$rData['rNumBaby'];
        $btDay = round(abs(strtotime($rData['rEndDate'])-strtotime($rData['rStartDate']))/86400);
        $pensionTel = "";
        
        if($rData['ppbTel1']){
            $pensionTel = $this->pension_lib->replacePhone(str_replace('-','',$rData['ppbTel1']));
        }else{
            $pensionTel = $this->pension_lib->replacePhone(str_replace('-','',$rData['mpsTel']));
        }
        
        if($rData['mpsAddrFlag'] && $rData['mpsAddrFlag'] == "1"){
            $address = $rData['mpsAddr1New'];
        }else{
            $address = $rData['mpsAddr1']." ".$rData['mpsAddr2'];
        }         
        if($rData['rPickupCheck'] == "1"){
            $pickup = "신청";
        }else{
            $pickup = "미신청";
        } 

		if($rData['rPriceAddType'] == "2"){
            $ceoPrice = (int)$rData['rBasicPrice']-(int)$rData['rPriceRoomDiscount'];
        }else{
            $ceoPrice = (int)$rData['rBasicPrice']-(int)$rData['rPriceRoomDiscount']+(int)$rData['rPriceAdult']+(int)$rData['rPriceYoung']+(int)$rData['rPriceBaby'];
        }

        if($rData['rOptionPrice'] != str_replace("|","",$rData['rOptionPrice'])){
            $optionArray = explode("|", $rData['rOptionPrice']);
            for($i=0; $i< count($optionArray); $i++){
                $ceoPrice = $ceoPrice+$optionArray[$i];
            }
        }else{
            $ceoPrice = $ceoPrice+(int)$rData['rOptionPrice'];
        }
        
        $msgArray = array(
            'YP_RS' => "[야놀자펜션-펜션예약 완료]
펜션명: ".$rData['rPension']."
객실명: ".$rData['rPensionRoom']."
이용일: ".$rData['rStartDate']."(".$dayName.") ".$btDay."박
예약자명: ".$rData['rPersonName']."
인원: 총 ".number_format($totalPeople)."명
결제금액: ".number_format($rData['rPrice'])."원

펜션주소: ".$address."
펜션연락처: ".$pensionTel."

예약번호: ".$rData['rCode']."
(예약내역 조회 시 필요한 번호입니다)

반드시 출발 전 펜션과 통화하여 예약확정여부 확인부탁드립니다.",            
            'YP_RC' => "[야놀자펜션-예약취소]
펜션명: ".$rData['rPension']."
객실명: ".$rData['rPensionRoom']."
이용일: ".$rData['rStartDate']."(".$dayName.") ".$btDay."박
예약자명: ".$rData['rPersonName']."

예약이 취소되었습니다.
고객센터 : 1644-4816",                
            'YP_RW' => "[야놀자펜션-입금대기]
펜션명: ".$rData['rPension']."
객실명: ".$rData['rPensionRoom']."
이용일: ".$rData['rStartDate']."(".$dayName.") ".$btDay."박
예약자명: ".$rData['rPersonName']."
인원: 총 ".number_format($totalPeople)."명
결제금액: ".number_format($rData['rPrice'])."원
입금기한: ".substr($rData['LGD_LimitDate'],0,4)."년".substr($rData['LGD_LimitDate'],4,2)."월".substr($rData['LGD_LimitDate'],6,2)."일 ".substr($rData['LGD_LimitDate'],8,2)."시".substr($rData['LGD_LimitDate'],10,2)."분".substr($rData['LGD_LimitDate'],12,2)."초 까지",
                
            'YP_RCW' => "[야놀자펜션-예약취소]
펜션명: ".$rData['rPension']."
객실명: ".$rData['rPensionRoom']."
이용일: ".$rData['rStartDate']."(".$dayName.") ".$btDay."박
예약자명: ".$rData['rPersonName']."

취소 접수가 완료되었습니다
고객센터 : 1644-4816",
                
            'YPC_RSS' => "[야놀자펜션-예약완료] 

객실명 : ".$rData['rPensionRoom']."
입실일 : ".$rData['rStartDate']."(".$dayName.") / ".$btDay."박 
인원 : 성인".number_format($rData['pprInMin']+$rData['rNumAdult'])."명/소아".number_format($rData['rNumYoung'])."명/유아".$rData['rNumBaby']."명 

예약자명 : ".$rData['rPersonName']."
휴대폰 : ".$this->pension_lib->replacePhone($rData['rPersonMobile'])."
생년월일 : ".$rData['rPersonBrithday']."
입실예정 : ".str_replace(':','시 ',$rData['rRoomingTime'])."분
픽업신청 : ".$pickup."
요청사항 : ".str_replace("\n",' ',strip_tags($rData['rRequestInfo']))."

예약접수: ".$rData['rRegDate']."
판매금액 : ".number_format($ceoPrice)."원

[사장님 페이지 바로가기] 
http://ceo.yapen.co.kr 
[카카오톡 사장님 고객센터] 
http://goo.gl/scHffC",
                
            'YPC_RC' => "[야놀자펜션-예약취소]
".$rData['rPensionRoom']." ".$rData['rStartDate']."(".$dayName.")/".$btDay."박
".$rData['rPersonName']." 예약취소되었습니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",
            'YPC_RW' => "[야놀자펜션-입금대기]
".$rData['rPensionRoom']." ".$rData['rStartDate']."(".$dayName.")/".$btDay."박 ".$totalPeople."명 ".$ceoPrice."원 입금대기중입니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",
            'YPC_RCW' => "[야놀자펜션-예약취소]
".$rData['rPensionRoom']." ".$rData['rStartDate']."(".$dayName.")/".$btDay."박
".$rData['rPersonName']." 예약취소되었습니다

[사장님페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",
            'YP_H' => "[야놀자펜션] 고객님께서 예약하신 ".$rData['rPension']." ".$rData['rPensionRoom']." ".$rData['rStartDate']."(".$dayName.") / ".$totalPeople."명 에 대한 예약 건에 대해 펜션주와 예약확인 해피콜이 완료 되었습니다.
안심하시고, 즐거운 여행 되세요!"
        );
        
        if($rData['rPaymentState'] == "PS01"){
            $ceoMsg = $msgArray['YPC_RW'];            
            $userMsg = $msgArray['YP_RW'];
            $ceoCode = "YPC_RW";
            $userCode = "YP_RW";
        }else if($rData['rPaymentState'] == "PS02"){
            $ceoMsg = $msgArray['YPC_RSS'];
            $userMsg = $msgArray['YP_RS'];
            $ceoCode = "YPC_RSS";
            $userCode = "YP_RS";
        }else if($rData['rPaymentState'] == "PS08"){
            $ceoMsg = $msgArray['YPC_RCW'];
            $userMsg = $msgArray['YP_RCW'];
            $ceoCode = "YPC_RCW";
            $userCode = "YP_RCW";
        }else{
            $ceoMsg = $msgArray['YPC_RC'];
            $userMsg = $msgArray['YP_RC'];
            $ceoCode = "YPC_RC";
            $userCode = "YP_RC";
        }
        
        if($rData['ppbTel1'] != "" && substr($rData['ppbTel1'],0,2) == "01"){
            $this->em_model->setTalk($ceoCode, $ceoMsg, str_replace('-','',$rData['ppbTel1']),'C');
        }
        if($rData['ppbTel2'] != "" && substr($rData['ppbTel2'],0,2) == "01"){
            $this->em_model->setTalk($ceoCode, $ceoMsg, str_replace('-','',$rData['ppbTel2']),'C');
        }
        if($rData['ppbTel3'] != "" && substr($rData['ppbTel3'],0,2) == "01"){
            $this->em_model->setTalk($ceoCode, $ceoMsg, str_replace('-','',$rData['ppbTel3']),'C');
        }                          
    }

    function freeMsg(){
        //사장님페이지에서 118번 서버로 문자 보내는 함수
        $msg = $this->input->post('msg');
        $receiver = $this->input->post('receiver');
        
        $this->em_model->setTalk('', $msg, $receiver,'C');
    }
}