<?php
class FreeStay extends CI_Controller {
    function __construct() {
        parent::__construct();
        //$this->load->library('pension_lib');
        $this->load->model('_yps/content/freestay_model');
    }

    function lists() {
        $reVal = array();
		$reValNew	= array();
        $lists = $this->freestay_model->getFreeStayLists();
        $dateNameArray = array('일','월','화','수','목','금','토');

        $i=0;
        $reVal['totalCount'] = '10';
        $reVal['status'] = '1';
        $reVal['failed_message'] = '';
        $reVal['lists'] = array();

        $reValNew['lists'] = array();

        foreach($lists as $lists){
            $user = "";
            if(isset($lists['userName'])){
                if($lists['userName'] != str_replace(",","",$lists['userName'])){
                    $userNameArray = explode(",",$lists['userName']);
                    $userMobileArray = explode(",",$lists['userMobile']);
                    for($j=0; $j< count($userNameArray); $j++){
                        if( trim($userNameArray[$j]) != '') {
                            $user .= ", ".mb_substr($userNameArray[$j],0,1)."*".mb_substr($userNameArray[$j],2)."(".substr($userMobileArray[$j],7,1)."*".substr($userMobileArray[$j],9).")";
                        }
                        // $user .= ", ".mb_substr($userNameArray[$j],0,1)."*".mb_substr($userNameArray[$j],2)."(".substr($userMobileArray[$j],7,1)."*".substr($userMobileArray[$j],9).")";
                    }
                }else{
                    $user .= ", ".mb_substr($lists['userName'],0,1)."*".mb_substr($lists['userName'],2)."(".substr($lists['userMobile'],7,1)."*".substr($lists['userMobile'],9).")";
                }
            }
            if($user != ""){
                $user = substr($user,2);
            }
            $reVal['lists'][$i]['idx'] = $lists['pfsIdx'];
            $reVal['lists'][$i]['pensionName'] = rawurlencode($lists['mpsName']);
            $reVal['lists'][$i]['eventDate'] = substr(date('Y.m.d',strtotime($lists['pfsStart'])),2)."(".$dateNameArray[date('w',strtotime($lists['pfsStart']))].") ~ ".substr(date('Y.m.d',strtotime($lists['pfsEnd'])),2)."(".$dateNameArray[date('w',strtotime($lists['pfsEnd']))].")";
            $reVal['lists'][$i]['pensionTag'] = explode(',',$lists['pfsTag']);
            $reVal['lists'][$i]['dDay'] = round((strtotime($lists['pfsEnd'])-strtotime(date('Y-m-d')))/86400);
            $reVal['lists'][$i]['imageUrl'] = "http://img.yapen.co.kr/pension/freestay/".$lists['pfsIdx']."/".$lists['pfsImage'];
            $reVal['lists'][$i]['winner'] = rawurlencode($user);
            $reVal['lists'][$i]['type'] = $lists['pfsChannel'];
            if($lists['pfsChannel'] == "3"){
                $reVal['lists'][$i]['icon'] = "http://img.yapen.co.kr/pension/freestay/images/kakaostory_event_new.png";
                if($lists['pfsLink']){
                    $reVal['lists'][$i]['link'] = $lists['pfsLink'];
                }else{
                    $reVal['lists'][$i]['link'] = "";
                }
            }else{
                $summerEventArray = array('203','204','205','206','207');
                $summerEventArray2 = array('208','209','210','211','212','213','214','215','216','217','218');
                $summerEventArray3 = array('220','224','225','226','227','228','229','230','231','232');
				$summerEventArray4 = array('243','242','241','240','239','238','237','236','235','234');
				$newPensionEventArray = array('247','248','249','250','251');
				$xmasEventArray = array('298','299','300','301','302');
				$newYearEventArray = array('304','305','306','307','308');

                if(in_array($lists['pfsIdx'], $summerEventArray)){
                    $reVal['lists'][$i]['icon'] = "http://img.yapen.co.kr/pension/freestay/images/summerEvent1.png";
                }else if(in_array($lists['pfsIdx'], $summerEventArray2)){
                    $reVal['lists'][$i]['icon'] = "http://img.yapen.co.kr/pension/freestay/images/summerEvent2.png";
                }else if(in_array($lists['pfsIdx'], $summerEventArray3)){
                    $reVal['lists'][$i]['icon'] = "http://img.yapen.co.kr/pension/freestay/images/summerEvent3.png";
                }else if(in_array($lists['pfsIdx'], $summerEventArray4)){
                    $reVal['lists'][$i]['icon'] = "http://img.yapen.co.kr/pension/freestay/images/summerEvent4.png";
                }else if(in_array($lists['pfsIdx'], $newPensionEventArray)){
                    $reVal['lists'][$i]['icon'] = "http://img.yapen.co.kr/pension/freestay/images/newPensionEvent.png";
                }else if(in_array($lists['pfsIdx'], $xmasEventArray)){
                    $reVal['lists'][$i]['icon'] = "http://img.yapen.co.kr/pension/event/winterEvent/2016-11-30/xmasBtn.png";
                }else if(in_array($lists['pfsIdx'], $newYearEventArray)){
                    $reVal['lists'][$i]['icon'] = "http://img.yapen.co.kr/pension/freestay/images/newYearEvent.png";
                }else{
                    $reVal['lists'][$i]['icon'] = "";
                }
                $reVal['lists'][$i]['link'] = "";
            }

			if($user == '')
			{
				$reValNew['lists'][$i]	= $reVal['lists'][$i];
			}

            $i++;
        }

		shuffle($reValNew['lists']);
		foreach($reValNew['lists'] as $k => $arr)
		{
			$reVal['lists'][$k]	= $arr;
		}

        echo json_encode($reVal);
    }

    function view(){
        $pfsIdx = $this->input->get_post('idx');
        $dateNameArray = array('일','월','화','수','목','금','토');

        $reVal = array();
        $reVal['status'] = '1';
        $reVal['failed_message'] = '';

        $info = $this->freestay_model->getFreeStayInfo($pfsIdx);
        $imageLists = $this->freestay_model->getFreeStayImage($pfsIdx);
        $roomInfo = $this->freestay_model->getRoomInfo($info['pprIdx']);


        if($info['pfsWeekFlag'] == "W"){
            $reVal['eventUseDay'] = "주중 무료숙박권";
        }else if($info['pfsWeekFlag'] == "E"){
            $reVal['eventUseDay'] = "주말 무료숙박권";
        }else{
            $reVal['eventUseDay'] = "주중, 주말 무료숙박권";
        }
        $reVal['mpIdx'] = $info['mpIdx'];
        if($info['totalCount'] == 0){
            $reVal['eventCount'] = 1;
        }else{
            $reVal['eventCount'] = number_format($info['totalCount']);
        }

        $reVal['dDay'] = round((strtotime($info['pfsEnd'])-strtotime(date('Y-m-d')))/86400);
        $reVal['pensionName'] = $info['mpsName'];
        $reVal['eventDate'] = date('Y.m.d',strtotime($info['pfsStart']))."(".$dateNameArray[date('w',strtotime($info['pfsStart']))].") ~ ".date('Y.m.d',strtotime($info['pfsEnd']))."(".$dateNameArray[date('w',strtotime($info['pfsEnd']))].")";

        $roomLists = explode("|", $info['pprIdx']);
        $countLists = explode("|", $info['pfsCount']);
        $reVal['roomCount'] = count($roomLists);
        for($i=0; $i< count($roomLists); $i++){
            if($countLists[$i] == ""){
                $reVal['roomLists'][$i] = $roomInfo[$roomLists[$i]]." 객실".$countLists[$i];
            }else{
                $reVal['roomLists'][$i] = $roomInfo[$roomLists[$i]]." 객실 ".$countLists[$i]."매";
            }
        }
        $reVal['public'] = date('Y.m.d', strtotime($info['pfsPublic']))." (".$dateNameArray[date('w', strtotime($info['pfsPublic']))] .") 앱 내 공지사항";
        $reVal['tipText'] = "많이 응모할수록 당첨 확률이 높아 집니다.";
        $reVal['step1Text'] = "이벤트를 친구에게 공유해 주세요.";
        $reVal['step2Text'] = "공유 화면을 캡쳐 후 응모하기에 첨부해 주세요.";

        if($info['pfsPeriodStart'] != "" || $info['pfsPeriodStart'] != '0000-00-00'){
            $periodText = date('Y.m.d', strtotime($info['pfsPeriodStart']))."(".$dateNameArray[date('w', strtotime($info['pfsPeriodStart']))].") 부터 ".date('Y.m.d', strtotime($info['pfsPeriodEnd']))."(".$dateNameArray[date('w', strtotime($info['pfsPeriodEnd']))].") 까지";
        }else{
            $periodText = date('Y.m.d', strtotime($info['pfsPeriodEnd']))."(".$dateNameArray[date('w', strtotime($info['pfsPeriodEnd']))].") 까지";
        }

        if($info['pfsWeekFlag'] == "W"){
            $periodText .= "\n(주중 사용 / 주말 사용 불가)";
        }else if($info['pfsWeekFlag'] == "E"){;
            $periodText .= "\n(주말 사용 / 주중 사용 불가)";
        }else{
            $periodText .= "\n(주중, 주말 사용 가능)";
        }

        $reVal['noticeText'] = "- 이용기간 : ".$periodText."\n
- 숙박권은 양도가 불가합니다.\n
- 당첨된 숙박권은 안내된 기간 내에 사용해 주셔야 합니다.\n(사용기간 내 미사용 시 자동 취소됩니다.)\n
- 입실가능인원은 해당 객실의 기준인원이며, 인원 추가 시 요금이 발생합니다. (기준인원이 최대인원과 같은 경우 객실 인원추가 불가)\n
- 예약이 완료된 객실은 날짜 변경 및 취소가 되지 않습니다.\n
- 바비큐 이용 및 유료결제 이용 시 예약 전 알려주셔야 하며, 결제는 별도로 진행해 주셔야합니다.\n
- 픽업은 펜션 사정에 따라 운영됩니다.";

        $reVal['imageLists'] = array();
        $reVal['imageCount'] = 0;
        $i=0;
        if(count($imageLists) > 0){
            $reVal['imageCount'] = count($imageLists);
            foreach($imageLists as $imageLists){
                $reVal['imageLists'][$i] = "http://img.yapen.co.kr/pension/freestay/".$info['pfsIdx']."/".$imageLists['pfsImage'];
                $i++;
            }
        }

        $reVal['shareText'] = $info["mpsName"]." 무료숙박권 이벤트, 참여기간 : ".date("m월 d일", strtotime($info["pfsEnd"]))."까지";

        echo json_encode($reVal);
        //echo var_dump($reVal);
    }

    function insData(){
        $reVal = array();
        $reVal['status'] = '1';
        $reVal['failed_message'] = '';

        $mbIdx = $this->input->get_post('idx');
        if(!$mbIdx){
            $this->returnVal['status'] = "0";
            $this->returnVal['failed_message'] = rawurlencode('필수값 누락');

            echo json_encode($reVal);
            return;
        }
        $mbEmail = $this->input->get_post('id');
        $mbMobile = $this->input->get_post('mobile');
        $pfsIdx = $this->input->get_post('pfsIdx');
        $device = $this->input->get_post('device');

        $state = $this->freestay_model->insUseData($mbIdx, $mbEmail, $mbMobile, $pfsIdx, $device);

        if($state > 100){
            $reVal['msg'] = '일일 최대 응모 횟수를 초과하였습니다.';
            $reVal['status'] = '0';
            $reVal['failed_message'] = '일일 최대 응모 횟수를 초과하였습니다.';
        }else{
            $reVal['msg'] = '무료숙박권 이벤트에 응모되었습니다.';
        }



        echo json_encode($reVal);
    }
}
?>