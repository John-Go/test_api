<?php
class Account_remove_model extends CI_Model {
	function __construct() {
		parent::__construct();
        $CI =& get_instance();
        $CI->dbYPS =& $this->load->database('yps', TRUE);
	}

	
	function getList(){
		$sch_sql = "SELECT
							*
					FROM
							pensionDB.reservation A
					LEFT JOIN pensionDB.reservationXpayInfo B ON A.rCode = B.LGD_OID
					WHERE
							B.LGD_LimitDate < '".date('YmdHis')."'
				    AND A.rPaymentMethod = 'PM03'
				    AND A.rPaymentState = 'PS01'
				    AND A.rPayFlag = 'Y'
				    AND A.rRoot = 'RO01'
					GROUP BY B.LGD_OID";
		$result = $this->db->query($sch_sql)->result_array();
		return $result;
	}
	
	function uptReservation($rIdx){
		$this->db->set('rPaymentState','PS08');
		$this->db->set('rCancelCheck','1');
		$this->db->set('rCancelDate',date('Y-m-d H:i:s'));
		$this->db->set('rCancelInfo','가상계좌 시간만료');
		$this->db->where('rPaymentState','PS01');
        $this->db->where('rPaymentMethod','PM03');
		$this->db->where('rIdx',$rIdx);
		$this->db->update('pensionDB.reservation');
		return;
	}
	
	function getCeoInfo($mpIdx)	{
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
                    VALUES('".$mpIdx."','".$pprIdx."','".$ppbDate."','Cron 방풀기','N','Cron','SYS',
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
}
?>