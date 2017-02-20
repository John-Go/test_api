<?php
class Connect_model extends CI_Model {
    function __construct() {
        parent::__construct();
		$CI =& get_instance();
        $CI->SV102 =& $this->load->database('YP', TRUE);
    }
    
    function getMatchKey($key){
        $this->db->where('ppcnPensionMatch', $key);
        $this->db->select(array('ppcnPensionName','ppcnPensionKey'));
        $result = $this->db->get('pensionDB.placePensionConnectName')->row_array();
        
        return $result;
    }
    
    function getPensionInfo($column, $code){
        $this->db->where_in($column, $code);
        $this->db->select(array('mpIdx','pprIdx',$column));
        $result = $this->db->get('pensionDB.placePensionConnect')->result_array();
        
        if($column == "gpKey" && !isset($result[0][$column])){
            $this->db->where_in('tourKey', $code);
            $this->db->select(array('mpIdx','pprIdx'));
            $result = $this->db->get('pensionDB.placePensionConnect')->result_array();
        }
        
        return $result;
    }
    
    function roomBlockCheck($pprIdx, $setDate){
        $this->db->where('pprIdx', $pprIdx);
        $this->db->where('ppbDate', $setDate);
        $result = $this->db->count_all_results('pensionDB.placePensionBlock');
        if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
        	//echo $this->db->last_query();
        }
        return $result;
    }
    
    function roomOpenCheck($pprIdx, $setDate){
        $this->db->where('pprIdx', $pprIdx);
        $this->db->where('ppbDate', $setDate);
        $result = $this->db->get('pensionDB.placePensionBlock')->row_array();
        
        return $result['rIdx'];
    }
    
    function setRoomBlock($mpIdx, $pprIdx, $setDate, $affName, $memo, $column, $etc = null){
        /* Pension Block Start */
        $this->db->set('mpIdx', $mpIdx);
        $this->db->set('pprIdx', $pprIdx);
        $this->db->set('ppbDate', $setDate);
        $this->db->set('ppbRegDate', date('Y-m-d H:i:s'));
        $this->db->insert('pensionDB.placePensionBlock');
        /* Pension Block End */
        
        /* Pension Block Log Start */
        $this->db->set('mpIdx', $mpIdx);
        $this->db->set('pprIdx', $pprIdx);
        $this->db->set('ppbDate', $setDate);
        $this->db->set('ppblMemo', "[".$affName." 자동방막기]".$memo);
        $this->db->set('ppbBlock', 'Y');
        $this->db->set('ppbRegID', $column);
        $this->db->set('ppblRegGrop', 'SYS');
        $this->db->set('ppblIP', $this->input->server('REMOTE_ADDR'));
        $this->db->set('ppblRegDate', date('Y-m-d H:i:s'));
        if($etc){
            $this->db->set('ppblEtcCode', $etc);
        }
        $this->db->insert('pensionDB.placePensionBlockLog');
        /* Pension Block Log End */
    }
    
    function setRoomOpen($mpIdx, $pprIdx, $setDate, $affName, $memo, $column, $etc = null){
        $this->db->where('mpIdx', $mpIdx);
        $this->db->where('pprIdx', $pprIdx);
        $this->db->where('ppbDate', $setDate);
        $this->db->delete('pensionDB.placePensionBlock');
        /* Pension Block End */
        
        /* Pension Block Log Start */
        $this->db->set('mpIdx', $mpIdx);
        $this->db->set('pprIdx', $pprIdx);
        $this->db->set('ppbDate', $setDate);
        $this->db->set('ppblMemo', "[".$affName." 자동방풀기]".$memo);
        $this->db->set('ppbBlock', 'N');
        $this->db->set('ppbRegID', $column);
        $this->db->set('ppblRegGrop', 'SYS');
        $this->db->set('ppblIP', $this->input->server('REMOTE_ADDR'));
        $this->db->set('ppblRegDate', date('Y-m-d H:i:s'));
        if($etc){
            $this->db->set('ppblEtcCode', $etc);
        }
        $this->db->insert('pensionDB.placePensionBlockLog');
    }
    
		// 객실 정보
    function getRoomConnectInfo($pprIdx){
			$query = "
				SELECT ppc.gpKey, ppc.naraKey, ppc.tourKey, ppr.pprName, ppb.ppbMainPension 
				FROM placePensionConnect AS ppc
				LEFT JOIN placePensionRoom AS ppr ON ppc.pprIdx = ppr.pprIdx
				LEFT JOIN placePensionBasic AS ppb ON ppb.mpIdx = ppc.mpIdx 
				WHERE ppc.pprIdx = ?
			";
			$result = $this->db->query($query, array($pprIdx))->row_array();
			
			return $result;
    }

		// 제휴사(G펜션) 연동 예약 정보
		function getPartnerRevInfo($rIdx, $pprIdx, $setDate){
			$query = "
				SELECT 
					r.rPersonMobile, r.rPickupCheck, r.rRequestInfo, r.rPersonName
					, r.rPriceMileage, r.rPersonEmail, r.rPersonBrithday, r.rPaymentMethod
					, pr.rState, pr.rRevDate, pr.rBasicPrice, pr.rSerialPrice, pr.rTodayPrice, pr.rSalePrice
					, pr.rCouponPrice, pr.rEtcPrice, pr.rAdult, pr.pprInMin, pr.rYoung, pr.rBaby, pr.priIdx
					, pe.prepAffIdx
				FROM reservation AS r
				INNER JOIN pensionRevInfo AS pr ON r.rIdx = pr.rIdx
				LEFT JOIN pensionRevEtcPoint AS pe ON r.rIdx = pe.rIdx AND pr.priIdx = pe.priIdx
				WHERE r.rIdx = ?
				AND pr.pprIdx = ?
				AND pr.rRevDate = ?
				AND r.rPayFlag = 'Y'
				ORDER BY pe.prepIdx DESC
			";
			$result = $this->db->query($query, array($rIdx, $pprIdx, $setDate))->row_array();
			
			return $result;
		}
		
		// 제휴사 연동 로그
		function setPartnerRevLog($rIdx, $memo){
        $this->db->set('rIdx', $rIdx);
        $this->db->set('mbID','kimyw4');
        $this->db->set('rlRegDate', date('Y-m-d H:i:s'));
        $this->db->set('rlMemo', $memo);
        $this->db->set('rlIP', $_SERVER['REMOTE_ADDR']);
        $this->db->insert('pensionDB.reservation_Log');
    }
		
		// G펜션 예약시 예약번호 입력
		function setRevEtcPoint($rIdx, $priIdx, $userName, $cancelFlag, $calFlag, $repCode){
        $this->db->set('rIdx', $rIdx);
        $this->db->set('userName', $userName);
        $this->db->set('prepCode','gPension');
        $this->db->set('prepName', 'G펜션');
        $this->db->set('prepPoint','0');
        $this->db->set('prepCancelFlag',$cancelFlag);
        $this->db->set('prepCalFlag', $calFlag);
        $this->db->set('prepAffIdx', $repCode);
        $this->db->set('prepRegDate', date('Y-m-d H:i:s'));
        $this->db->set('priIdx', $priIdx);
        $this->db->insert('pensionRevEtcPoint');
    }
		
	function getRoomLists($mpIdx, $parnerKey){
		$this->SV102->select('PPR.*, PPC.'.$parnerKey.'Key', FALSE);
		$this->SV102->where('PPR.mpIdx', $mpIdx);
		$this->SV102->where('PPR.pprOpen','1');
		$this->SV102->order_by('pprNo','DESC');
		$this->SV102->join('placePensionConnect AS PPC','PPC.pprIdx = PPR.pprIdx','LEFT');
		$result = $this->SV102->get('placePensionRoom AS PPR')->result_array();
		 
		return $result;
	}
	
	function getRoomInfo($pprIdx, $parnerKey){
		$this->SV102->select('PPR.*, PPC.'.$parnerKey.'Key', FALSE);
		$this->SV102->where('PPR.pprIdx', $pprIdx);
		$this->SV102->where('PPR.pprOpen','1');
		$this->SV102->order_by('pprNo','DESC');
		$this->SV102->join('placePensionConnect AS PPC','PPC.pprIdx = PPR.pprIdx','LEFT');
		$result = $this->SV102->get('placePensionRoom AS PPR')->row_array();
		
		return $result;
	}
	
	function getRoomBlockLists($pprIdx, $startDate, $endDate){
		$schQuery = "	SELECT *
						FROM placePensionBlock
						WHERE pprIdx = '".$pprIdx."'
						AND ppbDate BETWEEN '".$startDate."' AND '".$endDate."'
						GROUP BY ppbDate
						ORDER BY ppbDate ASC";
		$lists = $this->SV102->query($schQuery)->result_array();
		
		$result = array();
		if(count($lists) > 0){
			foreach($lists as $lists){
				array_push($result, $lists['ppbDate']);
			}
		}
		
		return $result;
	}

	// 싱크 채널
	function syncChannel($pprIdx){
		$query = "
			SELECT 
				PPR.pprIdx, PCI.pciIdx
			FROM placePensionRoom AS PPR
			LEFT JOIN pensionChannel AS PC ON PC.mpIdx = PPR.mpIdx AND PC.pciIdx != '1'
			LEFT JOIN pensionChannelInfo AS PCI ON PCI.pciIdx = PC.pciIdx
			WHERE PPR.pprIdx = ?
			AND PCI.pciSync = '1'
		";
		$result = $this->db->query($query, array($pprIdx))->result_array();
		
		return $result;
	}
	
	function getRevPenalty($mpIdx, $revDate, $cancelRevDay){
        $penaltyDay = round((strtotime($revDate)-strtotime(date('Y-m-d')))/86400);
        if($penaltyDay < 0){
            $penaltyDay = 0;
        }        
        
        $this->SV102->where('mpIdx', $mpIdx);
        $this->SV102->order_by('pppnDay','DESC');
        $result = $this->SV102->get('placePensionPenalty')->row_array();
    
        $penaltyMaxDay = $result['pppnDay'];
        if($penaltyDay > $penaltyMaxDay){
            $penaltyDay = $penaltyMaxDay;
        }
        
        if($cancelRevDay >= 8){
            $this->SV102->order_by('pppnRevDay','DESC');
        }
        
        $this->SV102->where('mpIdx', $mpIdx);
        $penaltyFlag = $this->SV102->count_all_results('pensionDB.placePensionPenalty');
        if($penaltyFlag == 0){
            $mpIdx = "1";
        }
        
        $this->SV102->where('mpIdx', $mpIdx);
        $this->SV102->where('pppnDay >=', $penaltyDay);
        $this->SV102->order_by('pppnDay','ASC');
        $dayInfo = $this->SV102->get('pensionDB.placePensionPenalty')->row_array();
        if($cancelRevDay != '0'){
            $this->SV102->where('mpIdx', $mpIdx);
            $this->SV102->where('pppnRevDay <= ',$cancelRevDay);
            $this->SV102->order_by('pppnRevDay','DESC');
            $revDayInfo = $this->SV102->get('pensionDB.placePensionPenalty')->row_array();
        }else{
            $revDayInfo = array();
        }
        
        if(isset($revDayInfo['pppnPay'])){
            if($revDayInfo['pppnPay'] >= $dayInfo['pppnPay']){
                $cancelPercent = $revDayInfo['pppnPay'];
            }else{
                $cancelPercent = $dayInfo['pppnPay'];
            }
        }else{
            $cancelPercent = $dayInfo['pppnPay'];
        }            
        
        return $cancelPercent;
    }
}