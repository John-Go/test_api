<?php

class Test extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('cron/rev_model');
    }
	function index(){
		echo 'aa';
	}
	public function getRevListsTest($offset = '0'){
		$rIdx = $this->input->get('rIdx');
	
           
        //$setDate = '2016-01-16';
        echo $setDate."\n";
        $schQuery = "   SELECT R.rIdx, R.rPension, R.rPersonName, R.rRoot, PPU.ppuExternalFlag, PPB.ppbGrade, IFNULL(top.amtbIdx,'') AS amtbIdx, IFNULL(ARB.arbIdx,'') AS arbIdx,
                        IFNULL(PB.pbIdx,'') AS pbIdx, PPB.ppbFeeReservation, PPB.ppbFeeApi, PPB.ppbStartDate, PPB.ppbEndDate, R.rRegDate,
                        IFNULL(PN.pnIdx,'') AS pnIdx, IFNULL(ATB.altrbIdx,'') AS atbIdx, PPB.ppbMainPension, PT.ptStart, PT.ptEnd, PT.ptOpen
                        FROM pensionDB.reservation AS R
                        LEFT JOIN pensionDB.placePensionUse AS PPU ON R.mpIdx = PPU.mpIdx
                        LEFT JOIN pensionDB.pensionTop AS PT ON PT.mpIdx AND R.mpIdx AND SUBSTR(R.rRegDate,1,10) BETWEEN PT.ptStart AND PT.ptEnd AND PT.ptOpen = 'Y'
                        LEFT JOIN pensionDB.placePensionBasic AS PPB ON PPB.mpIdx = R.mpIdx
                        LEFT JOIN (SELECT AMTBJ.mpIdx, AMTBJ.amtbIdx
                        FROM pensionDB.appMainTopBanner AS AMTB
                        LEFT JOIN pensionDB.appMainTopBannerJoin AS AMTBJ ON AMTB.amtbIdx = AMTBJ.amtbIdx
                        WHERE '".$setDate."' BETWEEN amtbStart AND amtbEnd AND AMTB.amtbOpen = '1' AND AMTBJ.amtbAd = 'Y') AS top ON top.mpIdx = R.mpIdx
                        LEFT JOIN pensionDB.appRandomBanner AS ARB ON ARB.mpIdx = R.mpIdx AND SUBSTR(R.rRegDate,1,10) BETWEEN ARB.arbStartDate AND ARB.arbEndDate AND arbOpen = '1'
                        LEFT JOIN pensionDB.pensionBest AS PB ON PB.mpIdx = R.mpIdx AND SUBSTR(R.rRegDate,1,10) BETWEEN PB.pbStart AND PB.pbEnd AND PB.pbOpen = 'Y'
                        LEFT JOIN pensionDB.pensionNew AS PN ON PN.mpIdx = R.mpIdx AND SUBSTR(R.rRegDate,1,10) BETWEEN PN.pnStart AND PN.pnEnd AND PN.pnOpen = 'Y' AND PN.pnAd = 'Y'
                        LEFT JOIN pensionDB.appLocTopRollingBanner AS ATB ON ATB.mpIdx = R.mpIdx AND SUBSTR(R.rRegDate,1,10) BETWEEN ATB.altrbStartDate AND ATB.altrbEndDate AND ATB.altrbOpen = '1' AND ATB.altrbAd = 'Y'
                        WHERE R.rPayFlag = 'Y' 
                        AND R.rAdminFeeFlag != 'Y' 
                    	AND R.rIdx = '".$rIdx."' 
                        AND R.rRoot != 'RO03'
                        GROUP BY R.rIdx
                        ORDER BY R.rIdx ASC
                        LIMIT 500 OFFSET ".$offset;
        
        $lists = $this->db->query($schQuery)->result_array();
        
        /* 
            예약대행 = 15%
            예약대행 + 광고 = 13% (광고 기간동안 2% 할인, 종료시 혜택 종료)
            예약대행 + 예약달력 = 12% (YBS 달력 사용시 3% 할인)
            예약대행 + 광고 + 예약달력 = 10% (광고 기간에만 최대 5% 할인)
         */
        echo count($lists)."건 실행중\n";
        $connetArray = array('19','27','25','29','30');
        if(count($lists) > 0){
            foreach($lists as $lists){
                //병국님 무료숙박권 수주로 인한, 지역상단 1순위 1달간 거제도 스타마린풀빌라 펜션 수수료 조정 예외처리 - 2016-04-04
                if($lists['mpIdx'] == "22493" && (substr($lists['rRegDate'],0,10) >= '2016-04-04' && substr($lists['rRegDate'],0,10) <= '2016-05-03')){
                    continue;
                }
                echo $lists['mpIdx'];
                if($lists['ptEnd'] >= $lists['rRegDate'] && $lists['ppbGrade'] == "10" && $lists['ptOpen'] == "Y"){
                    $grade = "10";
                }else{
                    $grade = "1";
                }
                if($lists['rRoot'] == "RO01"){
                    $fee = $lists['ppbFeeReservation'];
                }else{
                    $fee = $lists['ppbFeeApi'];
                }
                if($grade == "10" || $lists['amtbIdx'] != "" || $lists['arbIdx'] != "" || $lists['pbIdx'] != "" || $lists['pnIdx'] != "" || $lists['atbIdx'] != ""){
                    $adFlag = 'Y';
                }else{
                    $adFlag = 'N';
                }
                if($lists['rRoot'] == "RO01" && !in_array($lists['ppbMainPension'],$connetArray)){
                    if($adFlag == "Y"){
                        $fee = "13";
                    }
                    if($lists['ppbExternalSetFlag'] == "Y"){
                        $fee = "12";
                    }
                    if($lists['ppbExternalSetFlag'] == "Y" && $adFlag == "Y"){
                        $fee = "10";
                    }
                }
				echo 'ptEnd : '.$lists['ptEnd']. ' >= '.$lists['rRegDate']."\n" ;
				echo 'rIdx : '.$lists['rIdx']."\n"; 
				echo '펜션명 : '.$lists['rPension']."\n";
				echo 'ppbMainPension : '.$lists['ppbMainPension']."\n";
				echo 'rRoot : '.$lists['rRoot']."\n";
				echo 'ppbFeeReservation : '.$lists['ppbFeeReservation']."\n";
				echo 'ppbFeeApi : '.$lists['ppbFeeApi']."\n";
				echo 'YBS : '.$lists['ppuExternalFlag']."\n";
				echo 'adFlag : '.$adFlag."\n\n\n";
				echo '최종 수수료 : '.$fee."\n\n\n";
            }
        }
        echo "실행 종료\n"; 
    }

    function talkTest(){
        $this->load->model('em_model');
        $this->em_model->testTalk();
    }
}