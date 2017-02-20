<?php
class Partner_rev_model extends CI_Model {
    function __construct() {
        parent::__construct();
		$CI =& get_instance();
        $CI->SV102 =& $this->load->database('YP', TRUE);
		$this->load->library('pension_lib');
    }
	
	function getParnerRoomIndex($key, $roomIndex){
		$this->SV102->where($key, $roomIndex);
		$result = $this->SV102->get('placePensionConnect')->row_array();
		
		return $result;
	}
	
	function getInfo($pprIdx){
		$schQuery = "	SELECT *
						FROM pensionDB.placePensionRoom AS PPR
						LEFT JOIN pensionDB.placePensionBasic AS PPB ON PPB.mpIdx = PPR.mpIdx
						LEFT JOIN pensionDB.mergePlaceSite AS MPS ON MPS.mpIdx = PPR.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'
						WHERE PPR.pprIdx = '".$pprIdx."'
						GROUP BY PPR.pprIdx";
		$info = $this->SV102->query($schQuery)->row_array();
		
		$today = date('Y-m-d');
        $weekDate = date('W', strtotime($today));
		
		$payFee = 15;
        $feeQuery = "   SELECT PPU.ppuExternalFlag, PPB.ppbGrade, IFNULL(top.amtbIdx,'') AS amtbIdx, IFNULL(ARB.arbIdx,'') AS arbIdx, PPB.ppbOnline, PPB.ppbFeeOnline,
                        IFNULL(PB.pbIdx,'') AS pbIdx, PPB.ppbFeeReservation, PPB.ppbFeeApi, PPB.ppbStartDate, PPB.ppbEndDate, PPB.ppbExternalSetFlag, PPB.ppbExternalRegDate,
                        IFNULL(PN.pnIdx,'') AS pnIdx, IFNULL(ATB.altrbIdx,'') AS atbIdx, PPB.ppbMainPension, PPB.ppbSubPension, PT.ptStart, PT.ptEnd, PT.ptOpen
                        FROM pensionDB.placePensionBasic AS PPB
                        LEFT JOIN pensionDB.placePensionUse AS PPU ON PPB.mpIdx = PPU.mpIdx
                        LEFT JOIN pensionDB.pensionTop AS PT ON PT.mpIdx = PPB.mpIdx AND '".$today."' BETWEEN PT.ptStart AND PT.ptEnd AND PT.ptOpen = 'Y'
                        LEFT JOIN (SELECT AMTBJ.mpIdx, AMTBJ.amtbIdx
                        FROM pensionDB.appMainTopBanner AS AMTB
                        LEFT JOIN pensionDB.appMainTopBannerJoin AS AMTBJ ON AMTB.amtbIdx = AMTBJ.amtbIdx
                        WHERE '".$today."' BETWEEN amtbStart AND amtbEnd AND AMTB.amtbOpen = '1' AND AMTBJ.amtbAd = 'Y') AS top ON top.mpIdx = PPB.mpIdx
                        LEFT JOIN pensionDB.appRandomBanner AS ARB ON ARB.mpIdx = PPB.mpIdx AND '".$today."' BETWEEN ARB.arbStartDate AND ARB.arbEndDate AND arbOpen = '1'
                        LEFT JOIN pensionDB.pensionBest AS PB ON PB.mpIdx = PPB.mpIdx AND '".$today."' BETWEEN PB.pbStart AND PB.pbEnd AND PB.pbOpen = 'Y'
                        LEFT JOIN pensionDB.pensionNew AS PN ON PN.mpIdx = PPB.mpIdx AND '".$today."' BETWEEN PN.pnStart AND PN.pnEnd AND PN.pnOpen = 'Y' AND PN.pnAd = 'Y'
                        LEFT JOIN pensionDB.appLocTopRollingBanner AS ATB ON ATB.mpIdx = PPB.mpIdx AND '".$today."' BETWEEN ATB.altrbStartDate AND ATB.altrbEndDate AND ATB.altrbOpen = '1' AND ATB.altrbAd = 'Y'
                        WHERE PPB.mpIdx = '".$info['mpIdx']."'
                        GROUP BY PPB.mpIdx";
        $feeInfo = $this->SV102->query($feeQuery)->row_array();
        
        if(isset($feeInfo['ppbSubPension'])){
            $rAffIndex = $feeInfo['ppbSubPension'];
        }else{
            $rAffIndex = "";
        }
        
        $connetArray = array('19','27');
        
        if($feeInfo['ptEnd'] >= $today && $feeInfo['ppbGrade'] == "10" && $feeInfo['ptOpen'] == "Y"){
            $grade = "10";
        }else{
            $grade = "1";
        }
        
        $payFee = $feeInfo['ppbFeeReservation'];
        
        if($grade == "10" || $feeInfo['amtbIdx'] != "" || $feeInfo['arbIdx'] != "" || $feeInfo['pbIdx'] != "" || $feeInfo['pnIdx'] != "" || $feeInfo['atbIdx'] != ""){
            $adFlag = 'Y';
        }else{
            $adFlag = 'N';
        }
        
        if(!in_array($feeInfo['ppbMainPension'],$connetArray)){
            if($adFlag == "Y" && $payFee >= 13){
                $payFee = 13;
            }
            // 2016.07.04 기존 YBS 사용업체만 수수료 우대 적용, 그 외 YBS 관련 수수료 우대 적용 해제
            if($feeInfo['ppbExternalSetFlag'] == "Y" && $feeInfo['ppbExternalRegDate'] < '2017-01-01'){
            	if($feeInfo['ppbExternalSetFlag'] == "Y" && $payFee >= 12){
                    $payFee = 12;
                }
                if($feeInfo['ppbExternalSetFlag'] == "Y" && $adFlag == "Y" && $payFee >= 10){
                    $payFee = 10;
                }
            }
        }
		
		if($feeInfo['ppbOnline'] == "1"){
			$payFee = $feeInfo['ppbFeeOnline'];
		}
		
		$info['payFee'] = $payFee;
		
		return $info;
	}

	function getPriceInfo($pprIdx, $revDate){
		$result = array();
		    
        $dayNum = date('N', strtotime($revDate));
        if($dayNum < 5){
            $dayNum = "1";
        }
            
        $schQuery = " SELECT
                            PPR.*,
                            CASE WHEN peIdx THEN
                                CASE peDay
                                    WHEN '1' THEN ppdpDay1
                                    WHEN '5' THEN ppdpDay5
                                    WHEN '6' THEN ppdpDay6
                                    WHEN '7' THEN ppdpDay7
                                ELSE
                                    ppdpDay".$dayNum."
                                END
                            ELSE
                                ppdpDay".$dayNum."
                            END AS basicPrice,
                            CASE WHEN peIdx THEN
                                CASE peDay
                                    WHEN '1' THEN ppdpSaleDay1
                                    WHEN '5' THEN ppdpSaleDay5
                                    WHEN '6' THEN ppdpSaleDay6
                                    WHEN '7' THEN ppdpSaleDay7
                                ELSE
                                    ppdpSaleDay".$dayNum."
                                END
                            ELSE
                                ppdpSaleDay".$dayNum."
                            END AS resultPrice
                        FROM pensionDB.placePensionRoom AS PPR
                        LEFT JOIN pensionDB.pensionPrice AS PP ON PP.pprIdx = PPR.pprIdx AND '".$revDate."' BETWEEN PP.ppdpStart AND PP.ppdpEnd
                        LEFT JOIN pensionDB.pensionException AS PE ON PE.mpIdx = PPR.mpIdx AND PE.peSetDate = '".$revDate."' AND PE.peUseFlag = 'Y'
                        WHERE PPR.pprIdx = '".$pprIdx."'
                        AND (PPR.pprOpen = '1' OR PPR.pprEOpen = '1')
                        GROUP BY PPR.pprIdx";
        $roomInfo = $this->SV102->query($schQuery)->row_array();
        
        
        $result['basicPrice'] = $roomInfo['basicPrice'];
        $result['salePrice'] = $roomInfo['basicPrice']-$roomInfo['resultPrice'];
        $result['payPrice'] = $roomInfo['resultPrice'];
        
        return $result;
	}
	
	function getRoomCheck($pprIdx, $revDate){
		$this->SV102->where('pprIdx', $pprIdx);
		$this->SV102->where('ppbDate', $revDate);
		$result = $this->SV102->count_all_results('pensionDB.placePensionBlock');
		
		return $result;
	}
	
	function insReserve($revData){
		$this->SV102->where('pciIdx', $revData['reservation']['rChannel']);
		$channel = $this->SV102->get('pensionChannelInfo')->row_array();
		$channelName = "REV-API";
		if(isset($channel['pciName'])){
			$channelName = $channel['pciName'];
		}
		
		foreach($revData['reservation'] as $key => $val){
			$this->db->set($key, $val);
		}
        $this->db->insert('pensionDB.reservation');
        
        $rIdx = $this->db->insert_id();
		
		$priIdx = 0;
		if(isset($revData['pensionRevInfo'])){
			for($i=0; $i< count($revData['pensionRevInfo']); $i++){
				$this->db->set('rIdx', $rIdx);
				foreach($revData['pensionRevInfo'][$i] as $key => $val){
					$this->db->set($key, $val);
				}
				$this->db->insert('pensionDB.pensionRevInfo');
				
				$pensionRevInfoIndex = $this->db->insert_id();
				
				if($i == 0){
					$priIdx = $pensionRevInfoIndex;
				}
				
				$this->db->set('rIdx', $rIdx);
	            $this->db->set('typeIdx', $pensionRevInfoIndex);
	            $this->db->set('type', 'REV');
	            $this->db->insert('pensionDB.pensionRevMsg');
				
				$this->roomConnect($revData['pensionRevInfo'][$i]['mpIdx'], $revData['pensionRevInfo'][$i]['pprIdx'], $revData['pensionRevInfo'][$i]['rRevDate'], $channelName, 'C');
				
				$this->pension_lib->partner_sync_lib($revData['rChannel'], $revData['pensionRevInfo'][$i]['pprIdx'], $revData['pensionRevInfo']['rRevDate'], 'C', $rIdx);
			}
		}
		
		if(isset($revData['pensionRevOption'])){
			for($i=0; $i< count($revData['pensionRevOption']); $i++){
				$this->db->set('rIdx', $rIdx);
				$this->db->set('priIdx', $priIdx);
				foreach($revData['pensionRevOption'][$i] as $key => $val){
					$this->db->set($key, $val);
				}
				$this->db->insert('pensionDB.pensionRevOption');
			}
		}
		
		return $rIdx;
	}

	function roomConnect($mpIdx, $pprIdx, $revDate, $channelName, $type, $rIdx = ''){
		if($type == "C"){
			$this->db->set('mpIdx',		$mpIdx);
	        $this->db->set('pprIdx',	$pprIdx);
	        $this->db->set('ppbDate',	$revDate);
	        $this->db->set('ppbRegDate', date('Y-m-d H:i:s'));
			if($rIdx != ""){
				$this->db->set('rIdx', $rIdx);
			}
	        $this->db->insert('pensionDB.placePensionBlock');
			
			$this->db->set('ppbBlock', 'Y');
		}else{
			if($rIdx != ""){
				$this->db->where('rIdx', $rIdx);
			}
			$this->db->where('mpIdx',		$mpIdx);
	        $this->db->where('pprIdx',	$pprIdx);
	        $this->db->where('ppbDate',	$revDate);
	        $this->db->delete('pensionDB.placePensionBlock');
			
			$this->db->set('ppbBlock', 'N');
		}
		
		$this->db->set('mpIdx', $mpIdx);
        $this->db->set('pprIdx', $pprIdx);
        $this->db->set('ppbDate', $revDate);
        $this->db->set('ppblMemo', $channelName." SERVER BLOCK");
        $this->db->set('ppbRegID', $channelName);
        $this->db->set('ppblRegGrop', 'SYS');
        $this->db->set('ppblIP', $_SERVER['REMOTE_ADDR']);
        $this->db->set('ppblRegDate', date('Y-m-d H:i:s'));
        $this->db->insert('pensionDB.placePensionBlockLog');
		
		return;
	}

	function cancelReserve($cancelData){
		$this->SV102->where('rChannel', $cancelData['rChannel']);
		$this->SV102->where('rCode', $cancelData['rCode']);
		$info = $this->SV102->get('reservation')->row_array();
		
		$this->SV102->where('pciIdx', $info['rChannel']);
		$channel = $this->SV102->get('pensionChannelInfo')->row_array();
		$channelName = "REV-API";
		if(isset($channel['pciName'])){
			$channelName = $channel['pciName'];
		}
		
		if(!isset($info['rIdx'])){
			return "Reservation search fail";
		}else{
			if($info['rPaymentState'] == "PS07"){
				return "Already canceled";
			}
			$this->SV102->where('rIdx', $info['rIdx']);
			$lists = $this->SV102->get('pensionRevInfo')->result_array();
			
			if(count($lists) > 0){
				$totalCancelPrice = 0;
				foreach($lists as $lists){
					$dateFor = (strtotime($lists['rRevDate'])-strtotime(date('Y-m-d')))/86400;
					
					if($lists['rAddType'] == "1"){
                        $addPrice = $lists['rAdultPrice']+$lists['rYoungPrice']+$lists['rBabyPrice'];
                    }else{
                        $addPrice = 0;
                    }
					
					$resultPrice = $lists['rBasicPrice']-$lists['rSalePrice']-$lists['rTodayPrice']-$lists['rSerialPrice']+$addPrice;
					
					$cancelRevDay = round((strtotime(date('Y-m-d'))-strtotime(substr($info['rRegDate'],0,10)))/86400);
                        
                    if($cancelRevDay < 0){
                        $cancelRevDay = 0;
                    }
					
					$penalty = $this->pension_lib->revPenalty('1', $lists['rRevDate'], $cancelRevDay);
					
					$cancelPrice = $resultPrice/100*$penalty;
					$totalCancelPrice += $cancelPrice;
					//떠나요는 위약금 규정을 다르게 설정
					$penaltyExceptionArray = array('8');
					
					$this->db->where('priIdx', $lists['priIdx']);
					if($cancelData['rCancelPrice'] != ""){
						$this->db->set('rCancelPrice', $cancelData['rCancelPrice']);
					}else if(!in_array($info['rChannel'], $penaltyExceptionArray)){
						$this->db->set('rCancelPrice', '100000000');
					}else{
						$this->db->set('rCancelPrice', $cancelPrice);
					}
					$this->db->set('rState','PS07');
					$this->db->set('rCancelDate', $cancelData['rCancelDate']);
					$this->db->set('rCancelInfo',$cancelData['rCancelInfo']);
			        
			        $this->db->update('pensionRevInfo');
					
					$this->roomConnect($lists['mpIdx'], $lists['pprIdx'], $lists['rRevDate'], $channelName, 'O', $info['rIdx']);
				}
				
				//제휴사 에서 중복예약 파악 시 or 제휴사와 위약금이 다를 시
				if($cancelPrice > 0 && $cancelData['rCancelPrice'] != $cancelPrice){
					$this->db->set('rIdx', $info['rIdx']);
	                $this->db->set('mbID', 'system');
	                $this->db->set('rlRegDate', date('Y-m-d H:i:s'));
	                $this->db->set('rlMemo', $channelName.' 관리자 직접취소 / 위약금 : '.number_format($cancelData['rCancelPrice'])."원 변경");
	                $this->db->set('rlIP', $_SERVER['REMOTE_ADDR']);
	                $this->db->insert('reservation_Log');
					
					$this->db->where('rIdx', $info['rIdx']);
					if($cancelData['cancelPrice'] == "0"){
						$this->db->set('rCancelPrice', $cancelData['rCancelPrice']);
					}
					$this->db->set('rNotCancelFlag','1');
					$this->db->set('rCancelDate', date('Y-m-d H:i:s', strtotime('-1 month')));
					$this->db->update('pensionRevInfo');
					
					$this->db->set('rPriceCancel', $cancelData['rCancelPrice']);					
				}else{
					$this->db->set('rPriceCancel', $totalCancelPrice);
				}
				$this->db->where('rIdx', $info['rIdx']);
				$this->db->set('rPaymentState','PS07');
				$this->db->set('rCancelCheck','1');
				$this->db->set('rCancelDate', date('Y-m-d H:i:s'));
				$this->db->set('rCancelInfo', $channelName.' 취소');
		        
		        $this->db->update('reservation');
			}
			return $info['rIdx'];
		}
	}

	function insPartnerDuplicateLog($duplicateData){
		$this->db->insert('pensionRevPartnerDuplicate', $duplicateData);
	}
}