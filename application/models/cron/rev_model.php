<?php
class Rev_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }
    
    function getRevLists($offset){
        $setDate = $this->input->get('setDate');
        if($setDate == "" || !$setDate){
            $setDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-1, date('Y')));
        }       
        //$setDate = '2016-01-16';
        echo $setDate."\n";
        $schQuery = "   SELECT R.rIdx, R.rPension, R.rPersonName, R.rRoot, PPU.ppuExternalFlag, PPB.ppbGrade, IFNULL(top.amtbIdx,'') AS amtbIdx, IFNULL(ARB.arbIdx,'') AS arbIdx,
                        IFNULL(PB.pbIdx,'') AS pbIdx, PPB.ppbFeeReservation, PPB.ppbFeeApi, PPB.ppbStartDate, PPB.ppbEndDate, R.rRegDate, PPB.ppbExternalSetFlag,
                        IFNULL(PN.pnIdx,'') AS pnIdx, IFNULL(ATB.altrbIdx,'') AS atbIdx, PPB.ppbMainPension, PT.ptStart, PT.ptEnd, PT.ptOpen
                        FROM pensionDB.reservation AS R
                        LEFT JOIN pensionDB.placePensionUse AS PPU ON R.mpIdx = PPU.mpIdx
                        LEFT JOIN pensionDB.pensionTop AS PT ON PT.mpIdx = R.mpIdx AND SUBSTR(R.rRegDate,1,10) BETWEEN PT.ptStart AND PT.ptEnd AND PT.ptOpen = 'Y'
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
                        AND R.rVer = '0'
                        AND SUBSTR(R.rRegDate,1,10) = '".$setDate."'
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
                $this->db->where('rIdx', $lists['rIdx']);
                $this->db->set('rAdminFee', $fee);
                $this->db->update('pensionDB.reservation');
            }
        }
        echo "실행 종료\n"; 
    }

    function getReserveState(){
        $schQuery = "
			SELECT 
				R.*, RCA.*, PPB.*, YP.*, YL.ylCancelFlag, YL.ylCancelStart, YL.ylCancelEnd
			FROM 
				pensionDB.reservation AS R 
				LEFT JOIN pensionDB.reservationCeoAccount AS RCA ON R.rIdx = RCA.rIdx
				LEFT JOIN pensionDB.placePensionBasic AS PPB ON PPB.mpIdx = R.mpIdx 
				LEFT JOIN pensionDB.ybsPension AS YP ON YP.mpIdx = R.mpIdx 
				LEFT JOIN pensionDB.ybsLimit AS YL ON YL.mpIdx = R.mpIdx
			WHERE 
				(
					(
						R.rRoot = 'RO02' 
						AND R.rPaymentMethod = 'PM03'
					) 
					OR
					(
						R.rRoot = 'RO03' 
						AND R.rPaymentMethod = 'PM08'
						AND R.rVer = '1'
					)
				)
				AND R.rPaymentState = 'PS01' 
				AND R.rRegDate >= '2016-04-28 00:00:00' 
				AND RCA.ppaLimitDate <= '".date('Y-m-d H:i:s')."' 
				AND YP.ypRevLimitCron = 'Y'  
				AND R.rPayFlag = 'Y'
		"; 
		$lists = $this->db->query($schQuery)->result_array();

		return $lists;
    }

}