<?php
class Account_model extends CI_Model {
    function __construct() {
        parent::__construct();
        $CI =& get_instance();
        $CI->dbYPS =& $this->load->database('yps', TRUE);
    }
    
    function getLimitLists(){
        $sch_sql = "SELECT R.rIdx, R.rPersonMobile, R.rRegDate, PRI.rRevDate, PRAL.ipgm_date, ROUND(((ipgm_date-app_time)/10000),0) AS limitTime
                    FROM pensionDB.reservation AS R
                    LEFT JOIN pensionDB.pensionRevAccountLog AS PRAL ON R.rPgCode = PRAL.tno
                    LEFT JOIN pensionDB.pensionRevInfo AS PRI ON PRI.rIdx = R.rIdx
                    WHERE R.rPayFlag = 'Y'
                    AND R.rVer = '1'
                    AND R.rRoot = 'RO01'
                    AND R.rPaymentMethod = 'PM03'
                    AND R.rPaymentState = 'PS01'
                    AND PRAL.ipgm_date IS NOT NULL
                    GROUP BY R.rIdx
                    HAVING limitTime < 50000";
        $result = $this->db->query($sch_sql)->result_array();
        
        return $result;
    }
    
    function getSendSmsCheck($rIdx){
        $this->db->where('rIdx', $rIdx);
        $this->db->where('pscType','1');
        $result = $this->db->count_all_results('pensionDB.pensionSmsCheck');
        
        return $result;
    }
    
    function insSmsCheck($rIdx, $receiver, $sender, $sendDate){
        $this->db->set('rIdx', $rIdx);
        $this->db->set('sender', $sender);
        $this->db->set('receiver', $receiver);
        $this->db->set('pscSendFlag','N');
        $this->db->set('pscSendDate', $sendDate);
        $this->db->set('pscType','1');
        $this->db->insert('pensionDB.pensionSmsCheck');
    }
    
    function getSmsCheckLists(){
        $this->db->where('pscSendFlag','N');
        $this->db->where('pscType','1');
        $this->db->where('pscSendDate <= ',date('Y-m-d H:i:s'));
        $result = $this->db->get('pensionDB.pensionSmsCheck')->result_array();
        
        return $result;
    }
    
    function getRevInfo($rIdx){
        $this->db->where('rIdx', $rIdx);
        $this->db->where('rPayFlag','Y');
        $result = $this->db->get('pensionDB.reservation')->row_array();
        
        return $result;
    }
    
    function uptSmsCheckInfo($pscIdx){
        $this->db->set('pscSendFlag','Y');
        $this->db->where('pscIdx', $pscIdx);
        $this->db->where('pscSendFlag','N');
        $this->db->update('pensionDB.pensionSmsCheck');
    }

    function getList(){
        $sch_sql = "SELECT *
                    FROM pensionDB.reservation AS R
                    LEFT JOIN pensionDB.pensionRevAccountLog AS PRAL ON PRAL.tno = R.rPgCode
                    LEFT JOIN pensionDB.placePensionBasic AS PPB ON PPB.mpIdx = R.mpIdx
                    WHERE R.rPaymentMethod = 'PM03'
                    AND R.rRoot = 'RO01'
                    AND R.rPayFlag = 'Y'
                    AND R.rPaymentState = 'PS01'
                    AND R.rVer = '1'
                    AND PRAL.ipgm_date < '".date('YmdHis')."'
                    GROUP BY R.rIdx";
        $result = $this->db->query($sch_sql)->result_array();
        return $result;
    }
	
	function getArsLists(){
		$sch_sql = "SELECT R.*, PPB.*
                    FROM pensionDB.reservation AS R
                    LEFT JOIN pensionDB.pensionRevArsLog AS PRARS ON PRARS.ordr_idxx = R.rCode
                    LEFT JOIN pensionDB.placePensionBasic AS PPB ON PPB.mpIdx = R.mpIdx
                    WHERE R.rPaymentMethod = 'PM10'
                    AND R.rRoot = 'RO01'
                    AND R.rPayFlag = 'Y'
                    AND R.rPaymentState = 'PS01'
                    AND R.rVer = '1'
                    AND PRARS.expr_dt < '".date('Y-m-d H:i:s')."'
                    GROUP BY R.rIdx";
        $result = $this->db->query($sch_sql)->result_array();
        
        return $result;
	}
    
    function getRevInfoLists($rIdx){
        $schQuery = "   SELECT *
                        FROM pensionDB.pensionRevInfo AS PRI
                        LEFT JOIN pensionDB.placePensionBasic AS PPB ON PRI.mpIdx = PPB.mpIdx
                        WHERE PRI.rIdx = '".$rIdx."'";
        $result = $this->db->query($schQuery)->result_array();
        
        return $result;
    }
    
    function uptReservation($rIdx){
        $this->db->set('rPaymentState','PS08');
        $this->db->set('rCancelCheck','1');
        $this->db->set('rCancelDate',date('Y-m-d H:i:s'));
        $this->db->set('rCancelInfo','가상계좌 시간만료');
        $this->db->where('rPaymentState','PS01');
        $this->db->where_in('rPaymentMethod',array('PM03','PM10'));
        $this->db->where('rIdx',$rIdx);
        $this->db->update('pensionDB.reservation');
        
        $this->db->set('rState','PS08');
        $this->db->set('rCancelDate', date('Y-m-d H:i:s'));
        $this->db->set('rCancelInfo','가상계좌 시간만료');
        $this->db->where('rIdx', $rIdx);
        $this->db->update('pensionDB.pensionRevInfo');
        
        $this->db->set('proState','C');
        $this->db->where('rIdx', $rIdx);
        $this->db->update('pensionDB.pensionRevOption');
        
        return;
    }
    
    function getCeoInfo($mpIdx) {
         $this->db->select(array('mps.mpIdx','mpsName','mpsDelegate','mpsTel','mpsMobile','mpsAddr1','mpsAddr1New','mpsAddr2','ppbTel1','ppbTel2','ppbTel3','ppbTelSMS'));
        $this->db->where('mps.mpType','PS');
        $this->db->where('mps.mpIdx',$mpIdx);
        $this->db->where('mps.mmType','YPS');
        $this->db->join('pensionDB.placePensionBasic ppb','mps.mpIdx = ppb.mpIdx');
        $result = $this->db->get('pensionDB.mergePlaceSite mps')->row_array();
        return $result;
    }
    
    function uptPensionBlock($mpIdx, $pprIdx, $ppbDate, $etcCode = null, $rIdx){
        $this->db->where('mpIdx',$mpIdx);
        $this->db->where('pprIdx',$pprIdx);
        $this->db->where('ppbDate',$ppbDate);
        $this->db->where('rIdx', $rIdx);
        $this->db->delete('pensionDB.placePensionBlock');
        
        $log_sql = "INSERT INTO pensionDB.placePensionBlockLog(mpIdx, pprIdx, ppbDate, ppblMemo, ppbBlock, ppbRegID, ppblRegGrop, ppblIP, ppblRegDate, ppblEtcCode)
                    VALUES('".$mpIdx."','".$pprIdx."','".$ppbDate."','미입금 자동 방풀기','N','Cron','SYS',
                    '".$_SERVER['REMOTE_ADDR']."','".date('Y-m-d H:i:s')."','".$etcCode."')";
        $this->db->query($log_sql);
        return;
    }
    
    function getEtcCode($mpIdx, $pprIdx, $ppbDate){
        $this->db->where('mpIdx', $mpIdx);
        $this->db->where('ppridx', $pprIdx);
        $this->db->where('ppbDate', $ppbDate);
        $this->db->select('ppblEtcCode');
        $result = $this->db->get('pensionDB.placePensionBlockLog')->row_array();
        
        return $result;
    }
    
    function MileageReturn($rIdx){
        $this->db->where('mprUseRIdx', $rIdx);
        $point_arr = $this->db->get('pensionDB.memberPointRaw')->result_array();
        
        foreach($point_arr as $point_arr){
           if($point_arr['mprPlusMinus'] == "P"){
               $this->db->set('mbIdx', $point_arr['mbIdx']);
               $this->db->set('mpID', $point_arr['mpID']);
               $this->db->set('mprResvCode', $point_arr['mprResvCode']);
               $this->db->set('mprPointCode', $point_arr['mprPointCode']);
               $this->db->set('mprPlusMinus', 'M');
               $this->db->set('mprPoint', $point_arr['mprPoint']);
               $this->db->set('mprPointDate', $point_arr['mprPointDate']);
               $this->db->set('mplExpirationDate', $point_arr['mplExpirationDate']);
               $this->db->set('mprCronFlag', $point_arr['mprCronFlag']);
               $this->db->set('mprUseRIdx', '0');
               $this->db->set('mprUseYn', 'Y');
               $this->db->insert('pensionDB.memberPointRaw');
           }else{
               $this->db->set('mbIdx', $point_arr['mbIdx']);
               $this->db->set('mpID', $point_arr['mpID']);
               $this->db->set('mprResvCode', $point_arr['mprResvCode']);
               $this->db->set('mprPointCode', $point_arr['mprPointCode']);
               $this->db->set('mprPlusMinus', 'P');
               $this->db->set('mprPoint', $point_arr['mprPoint']);
               $this->db->set('mprPointDate', $point_arr['mprPointDate']);
               $this->db->set('mplExpirationDate', $point_arr['mplExpirationDate']);
               $this->db->set('mprCronFlag', $point_arr['mprCronFlag']);
               $this->db->set('mprUseRIdx', '0');
               $this->db->set('mprUseYn', 'N');
               $this->db->insert('pensionDB.memberPointRaw');
           }
        }

        $this->db->set('rPriceMileage','0');
        $this->db->where('rIdx', $rIdx);
        $this->db->update('pensionDB.reservation');
    }

    function insBlockConnect($pprIdx, $date, $blockFlag){
        $this->db->where('ppcnUseYn','Y');
        $connectArr = $this->db->get('pensionDB.placePensionConnectName')->result_array();
        
        for($j=0; $j < count($connectArr); $j++){
            $this->db->where('pprIdx', $pprIdx);
            $this->db->select($connectArr[$j]['ppcnPensionKey']);
            $connectFlag = $this->db->get('pensionDB.placePensionConnect')->row_array();
            
            if($connectFlag[$connectArr[$j]['ppcnPensionKey']] != ""){
                $this->db->set('pprIdx', $pprIdx);
                $this->db->set('ppbDate', $date);
                $this->db->set('ppbcFlag', $blockFlag);
                $this->db->set('ppbcCnt','0');
                $this->db->set('ppcnPensionKey', $connectArr[$j]['ppcnPensionKey']);
                $this->db->insert('pensionDB.placePensionBlockConnect');
            }
        }   
    }
    
    function getPensionBasicInfo($mpIdx){
        $this->db->where('mpIdx', $mpIdx);
        $result = $this->db->get('pensionDB.placePensionBasic')->row_array();
        
        return $result;
    }

    function delSmsCheck($pscIdx){
        $this->dbYPS->where('pscIdx', $pscIdx);
        $this->dbYPS->delete('pensionDB.pensionSmsCheck');
    }
    
    function setRevError($rIdx, $type, $memo){
        $this->db->where('rIdx', $rIdx);
        $this->db->where('rlMemo', 'G펜션 로그 : '.$memo);
        $flag = $this->db->count_all_results('pensionDB.reservation_Log');
        
        if($flag == 0){
            $this->db->set('rIdx', $rIdx);
            $this->db->set('mbID','kimyw4');
            $this->db->set('rlRegDate', date('Y-m-d H:i:s'));
            $this->db->set('rlMemo', 'G펜션 로그 : '.$memo);
            $this->db->set('rlIP', $_SERVER['REMOTE_ADDR']);
            $this->db->insert('pensionDB.reservation_Log');
        }else if(substr($_SERVER['REMOTE_ADDR'],0,11) == "211.119.136"){
            $this->db->set('rIdx', $rIdx);
            $this->db->set('mbID','kimyw4');
            $this->db->set('rlRegDate', date('Y-m-d H:i:s'));
            $this->db->set('rlMemo', 'G펜션 로그 : '.$memo);
            $this->db->set('rlIP', $_SERVER['REMOTE_ADDR']);
            $this->db->insert('pensionDB.reservation_Log');
        }
    }
    
    function pensionRevEtcPoint($priIdx){
        $this->db->where('priIdx', $priIdx);
        $result = $this->db->get('pensionDB.pensionRevEtcPoint')->row_array();
        
        return $result;
    }
    
    function pensionNaraConnect($rIdx, $pprIdx, $setDate, $type){
        $this->db->where('PPC.pprIdx', $pprIdx);
        $this->db->join('pensionDB.placePensionBasic AS PPB','PPB.mpIdx = PPC.mpIdx');
        $connectInfo = $this->db->get('pensionDB.placePensionConnect AS PPC')->row_array();
        
        if($connectInfo['ppbMainPension'] == "24" && $connectInfo['naraKey']){
            $url = "http://www.pensionnara.co.kr/change/state.php?key=yapen&room_uid=".$connectInfo['naraKey']."&sdate=".$setDate."&edate=".$setDate."&state_view=O";
                    
            $ch = curl_init();    
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $resultData = curl_exec($ch);
            curl_close($ch);
            $resultData = trim(preg_replace("/[^0-9]*/s","",$resultData));
            
            $returnText = "인증코드 없음";
            
            if($resultData == "4"){
                $returnText = "객실열기 성공";
            }else if($resultData == "1"){
                $returnText = "시작일 또는 종료일이 오늘날짜보다 이전";
            }else if($resultData == "2"){
                $returnText = "객실번호가 없음";
            }else if($resultData == "3"){
                $returnText = "이미 예약완료";
            }else{
                $returnText = "인증불가(등록된 key값이 아닙니다)";
            }
            
            $this->setPensionNaraRevError($rIdx, '0', $returnText);
        }
    }

    function setPensionNaraRevError($rIdx, $type, $memo){
        $this->db->where('rIdx', $rIdx);
        $this->db->where('rlMemo', '펜션나라 로그 : '.$memo);
        $flag = $this->db->count_all_results('pensionDB.reservation_Log');
        
        if($flag == 0){
            $this->db->set('rIdx', $rIdx);
            $this->db->set('mbID','kimyw4');
            $this->db->set('rlRegDate', date('Y-m-d H:i:s'));
            $this->db->set('rlMemo', '펜션나라 로그 : '.$memo);
            $this->db->set('rlIP', $_SERVER['REMOTE_ADDR']);
            $this->db->insert('pensionDB.reservation_Log');
        }else if(substr($_SERVER['REMOTE_ADDR'],0,11) == "211.119.136"){
            $this->db->set('rIdx', $rIdx);
            $this->db->set('mbID','kimyw4');
            $this->db->set('rlRegDate', date('Y-m-d H:i:s'));
            $this->db->set('rlMemo', '펜션나라 로그 : '.$memo);
            $this->db->set('rlIP', $_SERVER['REMOTE_ADDR']);
            $this->db->insert('pensionDB.reservation_Log');
        }
    }
}
?>