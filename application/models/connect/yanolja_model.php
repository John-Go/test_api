<?php
class Yanolja_model extends CI_Model {
    function __construct() {
        parent::__construct();
		$this->stateArray = array(	'예약준비' => 'PS01',
									'예약완료' => 'PS02',
									'입금대기' => 'PS01',
									'입금취소' => 'PS08',
									'취소완료' => 'PS07',
									'고객취소' => 'PS05',
									'관리자취소' => 'PS07',
									'현장취소' => 'PS07',
									'포인트환불취소' => 'PS07',
									'계좌이체환불취소' => 'PS07');
		$CI =& get_instance();
        $CI->SV102 =& $this->load->database('YP', TRUE);
    }
    
	function getCheckRoom($mpIdx, $pprIdx, $setDate){
		$schQuery = "	SELECT PPR.pprIdx, IFNULL(PPB.ppbIdx,'') AS ppbIdx, PPR.pprOpen, PB.ppbReserve, PB.ppbMainPension, MPS.mpsOpen
						FROM placePensionRoom AS PPR
						LEFT JOIN placePensionBlock AS PPB ON PPB.pprIdx = PPR.pprIdx AND PPB.ppbDate = '".$setDate."'
						LEFT JOIN placePensionBasic AS PB ON PB.mpIdx = PPR.mpIdx
						LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPR.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'
						WHERE PPR.mpIdx = '".$mpIdx."'
						AND PPR.pprIdx = '".$pprIdx."'
						GROUP BY PPR.pprIdx";
		$result = $this->SV102->query($schQuery)->row_array();
		
		return $result;
	}
	
	function insPartnerData($code, $data, $type){
		$this->db->set('prpName','yanolja');
		$this->db->set('prpCode', $code);
		$this->db->set('prpMsg', '');
		$this->db->set('prpType', $type);
		$this->db->set('prpContent', $data);
		$this->db->insert('pensionRevPartner');
	}
	
	function checkRevData($revCode){
		$this->db->where('rCode', $revCode);
		$this->db->where('rPayFlag','Y');
		$result = $this->db->count_all_results('reservation');
		
		return $result;
	}
	
	function insRevData($retData){
		$schQuery = "	SELECT *
						FROM placePensionRoom AS PPR
						LEFT JOIN placePensionBasic AS PPB ON PPB.mpIdx = PPR.mpIdx
						LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPR.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'
						WHERE PPR.mpIdx = '".$retData->channelPlaceNo."'
						AND PPR.pprIdx = '".$retData->channelRoomTypeNo."'
						GROUP BY PPR.pprIdx";
		$info = $this->SV102->query($schQuery)->row_array();
		
		$today = date('Y-m-d');
        $weekDate = date('W', strtotime($today));
		
		$payFee = 15;
        $feeQuery = "   SELECT PPU.ppuExternalFlag, PPB.ppbGrade, IFNULL(top.amtbIdx,'') AS amtbIdx, IFNULL(ARB.arbIdx,'') AS arbIdx,
                        IFNULL(PB.pbIdx,'') AS pbIdx, PPB.ppbFeeReservation, PPB.ppbFeeApi, PPB.ppbStartDate, PPB.ppbEndDate, PPB.ppbExternalSetFlag, PPB.ppbExternalRegDate, PPB.ppbOnline, PPB.ppbFeeOnline,
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
                        WHERE PPB.mpIdx = '".$retData->channelPlaceNo."'
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

		$trafficArray = array('자가' => 'PT01','대중교통' => 'PT03');
		
        if(isset($trafficArray[$retData->visitType])){
        	$traffic = $trafficArray[$retData->visitType];
        }else{
        	$traffic = 'PT01';
        }
		$penaltyIdx = "1";
        $this->db->set('mpIdx', $info['mpIdx']);
        $this->db->set('mbIdx', '');
        $this->db->set('mbID', '');
        $this->db->set('rCode', $retData->reservationNo);
        $this->db->set('rPension', $info['mpsName']);
        $this->db->set('rPersonName', trim($retData->memberName));
        $this->db->set('rPersonStayName', trim($retData->memberName));
        $this->db->set('rPersonMobile', trim(str_replace('-','',$retData->memberPhone)));
        $this->db->set('rPersonEmail', trim($retData->memberEmail));
        $this->db->set('rRoomingTime', $retData->appointmentTime);
        $this->db->set('rPersonBrithday', $retData->memberBirthDay);        
        $this->db->set('rPickupCheck', $retData->pickup);
        $this->db->set('rPickupTime', '');        
        $this->db->set('rPersonSi', '서울특별시');
        $this->db->set('rPersonTraffic', $traffic);
        $this->db->set('rRequestInfo', '');
        $this->db->set('rRegDate', date('Y-m-d H:i:s'));
        $this->db->set('rStatus', 'RS01'); //변경
        $this->db->set('rPaymentMethod','PM11');
        $this->db->set('rPaymentState',$this->stateArray[$retData->paymentStatus]); //변경
        $this->db->set('rPrice', $retData->roomPrice);
        $this->db->set('rPriceMileage', (int)($retData->roomPrice*0.075));
        $this->db->set('rPriceCoupon', 0);
        $this->db->set('rAdminFee', $payFee);
        $this->db->set('rVer','1');
        $this->db->set('rFee','R');
        $this->db->set('rWeekDate', $weekDate);
        $this->db->set('rPayFlag','Y');
        $this->db->set('rReserveFlag', $retData->appOs);
        $this->db->set('rRoot','RO01');
        $this->db->set('rMainPension','41');
        
        $this->db->insert('reservation');
        
        $rIdx = $this->db->insert_id();
        
        $priIdx = 0;
        $pensionRevInfoIdx  = 0;
		
		$startDate = substr($retData->checkInDateTime,0,10);
		$startDateArray = explode('-', $startDate);
		$endDate = substr($retData->checkOutDateTime,0,10);
		$dayFor = round(abs(strtotime($endDate)-strtotime($startDate))/86400);
		
        for($i=0; $i< $dayFor; $i++){
        	$setDate = date('Y-m-d', mktime(0, 0, 0, $startDateArray[1], $startDateArray[2]+$i, $startDateArray[0]));
            //객실 요금 다시 불러오기
            $rPrice = 0;
            $rSitePrice = 0;
            $addType = "1";
            $commPrice = 0;
            $peopleCommPrice = 0;
            
            $dayNum = date('N', strtotime($setDate));
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
                            FROM placePensionRoom AS PPR
                            LEFT JOIN pensionPrice AS PP ON PP.pprIdx = PPR.pprIdx AND '".$setDate."' BETWEEN PP.ppdpStart AND PP.ppdpEnd
                            LEFT JOIN pensionException AS PE ON PE.mpIdx = PPR.mpIdx AND PE.peSetDate = '".$setDate."' AND PE.peUseFlag = 'Y'
                            WHERE PPR.pprIdx = '".$retData->channelRoomTypeNo."'
                            AND (PPR.pprOpen = '1' OR PPR.pprEOpen = '1')
                            GROUP BY PPR.pprIdx";
            $roomInfo = $this->SV102->query($schQuery)->row_array();
            
            //객실 요금 및 인원요금 재설정
            $basicPrice = $roomInfo['basicPrice'];
            $salePrice = $roomInfo['basicPrice']-$roomInfo['resultPrice'];
            $addPeopleFlag = $roomInfo['pprInAddPay'];
            
            //결제요금 설정
            $commPrice = $basicPrice-$salePrice;
            
            $rPrice = $basicPrice-$salePrice;
            $rSitePrice = 0;
            $addType = "2";
            $peopleCommPrice = 0;
            
            
            //계좌 설정
            $this->SV102->select('pprAccount');
            $this->SV102->where('pprIdx', $info['pprIdx']);
            $roomAccount = $this->SV102->get('placePensionRoom')->row_array();
            
            if(isset($roomAccount['pprAccount'])){
                $ppaIdx = $roomAccount['pprAccount'];
            }else{
                $this->SV102->select('ppaIdx');
                $this->SV102->where('mpIdx', $info['mpIdx']);
                $this->SV102->where('ppaRepr','1');
                $this->SV102->order_by('ppaIdx','DESC');
                $roomAccount = $this->SV102->get('placePensionAccount')->row_array();
                
                if(isset($roomAccount['ppaIdx'])){
                    $ppaIdx = $ppaIdx_arr['ppaIdx'];
                }else{
                    $ppaIdx = "";
                }
            }
            
            //위약금 설정                       
            $penaltyDay = round(abs(strtotime($setDate)-strtotime(date('Y-m-d')))/86400);        
        
            $this->SV102->where('mpIdx', $penaltyIdx);
            $this->SV102->order_by('pppnDay','DESC');
            $result = $this->SV102->get('placePensionPenalty')->row_array();
        
            $penaltyMaxDay = $result['pppnDay'];
            if($penaltyDay > $penaltyMaxDay){
                $penaltyDay = $penaltyMaxDay;
            }
            
            $this->SV102->where('mpIdx', $penaltyIdx);
            $penaltyFlag = $this->SV102->count_all_results('pensionDB.placePensionPenalty');
            if($penaltyFlag == 0){
                $penaltyIdx = "1";
            }
            
            $this->SV102->where('mpIdx', $penaltyIdx);
            $this->SV102->where('pppnDay <=', $penaltyDay);
            $this->SV102->order_by('pppnDay','DESC');
            $dayInfo = $this->SV102->get('pensionDB.placePensionPenalty')->row_array();
            
            $cancelPercent = $dayInfo['pppnPay'];
            
            $cancelPrice = $rPrice/100*$cancelPercent;
            
            $this->db->set('rIdx', $rIdx);
            $this->db->set('rCode', $retData->reservationNo);
            $this->db->set('mpIdx', $info['mpIdx']);
            $this->db->set('pprIdx', $info['pprIdx']);
            $this->db->set('rPensionRoom', $info['pprName']);
            $this->db->set('rBasicPrice', $basicPrice);
            $this->db->set('rSalePrice', $salePrice);
            $this->db->set('rCouponPrice', 0);
            $this->db->set('rSerialPrice', 0);
            $this->db->set('rTodayPrice', 0);
            $this->db->set('rEtcPrice', 0);
            $this->db->set('rEventPrice', 0);
            $this->db->set('rPrice', $rPrice);
            $this->db->set('rSitePrice', $rSitePrice);
            $this->db->set('rState', $this->stateArray[$retData->paymentStatus]);
            $this->db->set('rRevDate', $setDate);
            $this->db->set('pprInMin', $info['pprInMin']);
            $this->db->set('rAddType', $addType);
            $this->db->set('rAdult', 0);
            $this->db->set('rYoung', 0);
            $this->db->set('rBaby', 0);
            $this->db->set('rAdultPrice', 0);
            $this->db->set('rYoungPrice', 0);
            $this->db->set('rBabyPrice', 0);
            $this->db->set('ppaIdx', $ppaIdx);
            $this->db->set('rComm', $payFee);
            $this->db->set('rPeopleComm', $payFee);
            $this->db->set('rCommPrice', ($commPrice*(100-$payFee)/100));
            $this->db->set('rPeopleCommPrice', ($peopleCommPrice*(100-$payFee)/100));
            $this->db->set('rRegDate', date('Y-m-d H:i:s'));
            $this->db->set('rCancelPrice', $cancelPrice);
            $this->db->set('rCancelDate', '');
            $this->db->set('rCancelInfo', '');
            $this->db->set('rAff', $rAffIndex);
            $this->db->insert('pensionRevInfo');
            
            if($i == 0){
                $priIdx = $this->db->insert_id();
                $pensionRevInfoIdx  = $priIdx;
            }else{
                $pensionRevInfoIdx  = $this->db->insert_id();
            }
			
			$this->db->set('rIdx', $rIdx);
            $this->db->set('typeIdx', $pensionRevInfoIdx);
            $this->db->set('type', 'REV');
            $this->db->insert('pensionRevMsg');
			
			$this->db->where('pprIdx', $info['pprIdx']);
			$this->db->where('ppbDate', $setDate);
			$this->db->set('rIdx', $rIdx);
			$this->db->update('placePensionBlock');
        }

		return $rIdx;
	}

	function cancelRevData($rCode){
		$this->db->where('rCode', $rCode);
		$info = $this->db->get('reservation')->row_array();
		
		$this->db->where('rIdx', $info['rIdx']);
		$lists = $this->db->get('pensionRevInfo')->result_array();
		
		$penaltyPay = "";
        if(isset($info['rIdx'])){
            if($info['rVer'] == "1"){
                if(count($lists) > 0){
                    foreach($lists as $lists){
                        $DeferDay = (strtotime($lists['rRevDate'])-strtotime(date('Y-m-d')))/86400;
                        /* 추가인원 관련 설정 Start */
                        if($lists['rAddType'] == "1"){
                            $addPrice = $lists['rAdultPrice']+$lists['rYoungPrice']+$lists['rBabyPrice'];
                        }else{
                            $addPrice = 0;
                        }
                        /* 추가인원 관련 설정 End */
                       
                        $resultPrice = $lists['rBasicPrice']-$lists['rSalePrice']-$lists['rTodayPrice']-$lists['rSerialPrice']+$addPrice;
                        
                        $cancelRevDay = round((strtotime(date('Y-m-d'))-strtotime(substr($info['rRegDate'],0,10)))/86400);
                        
                        if($cancelRevDay < 0){
                            $cancelRevDay = 0;
                        }
                        if($info['rRoot'] == "RO01" || $info['rRoot'] == "RO04"){
                            $penaltyIdx = "1";
                        }else{
                            $penaltyIdx = $info['mpIdx'];                    
                        }
						
                        $penalty = $this->getPenaltyInfo('1', $lists['rRevDate'], $cancelRevDay);
                        
                        $cancelPrice = $resultPrice/100*$penalty;
                        if($penaltyPay == ""){
                            $penaltyPay = $penalty;
                        }
						$this->db->where('priIdx', $lists['priIdx']);
						$this->db->set('rState','PS07');
						$this->db->set('rCancelDate', date('Y-m-d H:i:s'));
						$this->db->set('rCancelInfo','야놀자 취소');
				        $this->db->set('rCancelPrice', $cancelPrice);
				        $this->db->update('pensionRevInfo');
						
						$this->db->where('rIdx', $info['rIdx']);
						$this->db->where('pprIdx', $lists['pprIdx']);
						$this->db->where('ppbDate', $lists['rRevDate']);
						$this->db->delete('placePensionBlock');
						
						$this->db->set('mpIdx', $info['mpIdx']);
						$this->db->set('pprIdx', $lists['pprIdx']);
						$this->db->set('ppbDate', $lists['rRevDate']);
						$this->db->set('ppblMemo','[야놀자 자동방풀기]');
						$this->db->set('ppbBlock','N');
						$this->db->set('ppbRegID','yanoljaKey');
						$this->db->set('ppblRegGrop','SYS');
						$this->db->set('ppblIP',$_SERVER['REMOTE_ADDR']);
						$this->db->set('ppblRegDate', date('Y-m-d H:i:s'));
						$this->db->insert('placePensionBlockLog');
                    }
                }
               
                $resultPrice = $info['rBasicPrice']-$info['rPriceRoomDiscount']+$addPrice;
                $cancelRevDay = round(abs(strtotime(date('Y-m-d'))-strtotime(substr($info['rRegDate'],0,10)))/86400);
                if($info['rRoot'] == "RO01" || $info['rRoot'] == "RO04"){
                    $penaltyIdx = "1";
                }else{
                    $penaltyIdx = $info['mpIdx'];                    
                }
                $penalty = $this->getPenaltyInfo($penaltyIdx, $info['rStartDate'], $cancelRevDay);
                
                $cancelPrice = $resultPrice/100*$penalty;
				
				$this->db->where('rIdx', $info['rIdx']);
				$this->db->set('rPaymentState','PS07');
				$this->db->set('rCancelCheck','1');
				$this->db->set('rCancelDate', date('Y-m-d H:i:s'));
				$this->db->set('rCancelInfo','야놀자 취소');
		        $this->db->set('rPriceCancel', $cancelPrice);
		        $this->db->update('reservation');
            }

			return $info['rIdx'];
        }else{
        	return "";
        }
	}

	function cancelRevInfo($rCode, $data){
		$this->db->where('rCode', $rCode);
		$info = $this->db->get('reservation')->row_array();
		
		$this->db->where('rIdx', $info['rIdx']);
		$lists = $this->db->get('pensionRevInfo')->result_array();
		
		$penaltyPay = "";
        if(isset($info['rIdx'])){
        	$resultPrice = 0;
            if($info['rVer'] == "1"){
                if(count($lists) > 0){
                    foreach($lists as $lists){
                        $DeferDay = (strtotime($lists['rRevDate'])-strtotime(date('Y-m-d')))/86400;
                        /* 추가인원 관련 설정 Start */
                        if($lists['rAddType'] == "1"){
                            $addPrice = $lists['rAdultPrice']+$lists['rYoungPrice']+$lists['rBabyPrice'];
                        }else{
                            $addPrice = 0;
                        }
                        /* 추가인원 관련 설정 End */
                       
                        $resultPrice = $lists['rBasicPrice']-$lists['rSalePrice']-$lists['rTodayPrice']-$lists['rSerialPrice']+$addPrice;
                        
                        $cancelRevDay = round((strtotime(date('Y-m-d'))-strtotime(substr($info['rRegDate'],0,10)))/86400);
                        
                        if($cancelRevDay < 0){
                            $cancelRevDay = 0;
                        }
                        if($info['rRoot'] == "RO01" || $info['rRoot'] == "RO04"){
                            $penaltyIdx = "1";
                        }else{
                            $penaltyIdx = $info['mpIdx'];                    
                        }
						
                        $penalty = $this->getPenaltyInfo('1', $lists['rRevDate'], $cancelRevDay);
                        
                        $cancelPrice = $resultPrice/100*$penalty;
                        if($penaltyPay == ""){
                            $penaltyPay = $penalty;
                        }
						
						$this->db->where('priIdx', $lists['priIdx']);
						$this->db->set('rState','PS07');
						$this->db->set('rCancelDate', date('Y-m-d H:i:s'));
						$this->db->set('rCancelInfo','야놀자 취소');
				        $this->db->set('rCancelPrice', $cancelPrice);
				        $this->db->update('pensionRevInfo');
						
						$this->db->where('rIdx', $info['rIdx']);
						$this->db->where('pprIdx', $lists['pprIdx']);
						$this->db->where('ppbDate', $lists['rRevDate']);
						$this->db->delete('placePensionBlock');
						
						$this->db->set('mpIdx', $info['mpIdx']);
						$this->db->set('pprIdx', $lists['pprIdx']);
						$this->db->set('ppbDate', $lists['rRevDate']);
						$this->db->set('ppblMemo','[야놀자 자동방풀기]');
						$this->db->set('ppbBlock','N');
						$this->db->set('ppbRegID','yanoljaKey');
						$this->db->set('ppblRegGrop','SYS');
						$this->db->set('ppblIP',$_SERVER['REMOTE_ADDR']);
						$this->db->set('ppblRegDate', date('Y-m-d H:i:s'));
						$this->db->insert('placePensionBlockLog');
                    }
                }
               	
                $cancelRevDay = round(abs(strtotime(date('Y-m-d'))-strtotime(substr($info['rRegDate'],0,10)))/86400);
                if($info['rRoot'] == "RO01" || $info['rRoot'] == "RO04"){
                    $penaltyIdx = "1";
                }else{
                    $penaltyIdx = $info['mpIdx'];                    
                }
                
                $cancelPrice = $resultPrice/100*$penalty;
				
				if($cancelPrice > 0 && $data->data->cancelAmount != $cancelPrice){
					$this->db->set('rIdx', $info['rIdx']);
	                $this->db->set('mbID', 'system');
	                $this->db->set('rlRegDate', date('Y-m-d H:i:s'));
	                $this->db->set('rlMemo', '야놀자 관리자 직접취소 / 위약금 : '.number_format($data->data->cancelAmount)."원 변경");
	                $this->db->set('rlIP', $_SERVER['REMOTE_ADDR']);
	                $this->db->insert('reservation_Log');
					
					$this->db->where('rIdx', $info['rIdx']);
					if($data->data->cancelAmount == "0"){
						$this->db->set('rCancelPrice', $data->data->cancelAmount);
					}
					$this->db->set('rNotCancelFlag','1');
					$this->db->set('rCancelDate', date('Y-m-d H:i:s', strtotime('-1 month')));
					$this->db->update('pensionRevInfo');
					
					$this->db->set('rPriceCancel', $data->data->cancelAmount);					
				}else{
					$this->db->set('rPriceCancel', $cancelPrice);
				}
				$this->db->where('rIdx', $info['rIdx']);
				$this->db->set('rPaymentState','PS07');
				$this->db->set('rCancelCheck','1');
				$this->db->set('rCancelDate', date('Y-m-d H:i:s'));
				$this->db->set('rCancelInfo','야놀자 취소');
		        
		        $this->db->update('reservation');
            }

			return $info['rIdx'];
        }else{
        	return "";
        }
	}

	function getPenaltyInfo($mpIdx, $revDate, $cancelRevDay){
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
        if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
			echo var_dump($cancelPercent);
			
		}
        return $cancelPercent;
    }

	function insRevInfo($retData){
		$schQuery = "	SELECT *
						FROM placePensionRoom AS PPR
						LEFT JOIN placePensionBasic AS PPB ON PPB.mpIdx = PPR.mpIdx
						LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPR.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'
						WHERE PPR.mpIdx = '".$retData['channelPlaceNo']."'
						AND PPR.pprIdx = '".$retData['channelRoomTypeNo']."'
						GROUP BY PPR.pprIdx";
		$info = $this->SV102->query($schQuery)->row_array();
		
		$today = date('Y-m-d');
        $weekDate = date('W', strtotime($today));
		
		$payFee = 15;
        $feeQuery = "   SELECT PPU.ppuExternalFlag, PPB.ppbGrade, IFNULL(top.amtbIdx,'') AS amtbIdx, IFNULL(ARB.arbIdx,'') AS arbIdx,
                        IFNULL(PB.pbIdx,'') AS pbIdx, PPB.ppbFeeReservation, PPB.ppbFeeApi, PPB.ppbStartDate, PPB.ppbEndDate, PPB.ppbExternalSetFlag, PPB.ppbExternalRegDate, PPB.ppbOnline, PPB.ppbFeeOnline,
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
                        WHERE PPB.mpIdx = '".$retData['channelPlaceNo']."'
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

		$trafficArray = array('자가' => 'PT01','대중교통' => 'PT03');
		
        if(isset($trafficArray[$retData['visitType']])){
        	$traffic = $trafficArray[$retData['visitType']];
        }else{
        	$traffic = 'PT01';
        }
		$penaltyIdx = "1";
        $this->db->set('mpIdx', $info['mpIdx']);
        $this->db->set('mbIdx', '');
        $this->db->set('mbID', '');
        $this->db->set('rCode', $retData['reservationNo']);
        $this->db->set('rPension', $info['mpsName']);
        $this->db->set('rPersonName', trim($retData['memberName']));
        $this->db->set('rPersonStayName', trim($retData['memberName']));
        $this->db->set('rPersonMobile', trim(str_replace('-','',$retData['memberPhone'])));
        $this->db->set('rPersonEmail', trim($retData['memberEmail']));
        $this->db->set('rRoomingTime', $retData['appointmentTime']);
        $this->db->set('rPersonBrithday', $retData['memberBirthDay']);        
        $this->db->set('rPickupCheck', $retData['pickup']);
        $this->db->set('rPickupTime', '');        
        $this->db->set('rPersonSi', '서울특별시');
        $this->db->set('rPersonTraffic', $traffic);
        $this->db->set('rRequestInfo', '');
        $this->db->set('rRegDate', date('Y-m-d H:i:s'));
        $this->db->set('rStatus', 'RS01'); //변경
        $this->db->set('rPaymentMethod','PM11');
        $this->db->set('rPaymentState',$this->stateArray[$retData['paymentStatus']]); //변경
        $this->db->set('rPrice', $retData['roomPrice']);
        $this->db->set('rPriceMileage', (int)($retData['roomPrice']*0.075));
        $this->db->set('rPriceCoupon', 0);
        $this->db->set('rAdminFee', $payFee);
        $this->db->set('rVer','1');
        $this->db->set('rFee','R');
        $this->db->set('rWeekDate', $weekDate);
        $this->db->set('rPayFlag','Y');
        $this->db->set('rReserveFlag', $retData['appOs']);
        $this->db->set('rRoot','RO04');
        $this->db->set('rChannel','1');
        
        $this->db->insert('reservation');
        
        $rIdx = $this->db->insert_id();
        
        $priIdx = 0;
        $pensionRevInfoIdx  = 0;
		
		$startDate = substr($retData['checkInDateTime'],0,10);
		$startDateArray = explode('-', $startDate);
		$endDate = substr($retData['checkOutDateTime'],0,10);
		$dayFor = round(abs(strtotime($endDate)-strtotime($startDate))/86400);
		
        for($i=0; $i< $dayFor; $i++){
        	$setDate = date('Y-m-d', mktime(0, 0, 0, $startDateArray[1], $startDateArray[2]+$i, $startDateArray[0]));
            //객실 요금 다시 불러오기
            $rPrice = 0;
            $rSitePrice = 0;
            $addType = "1";
            $commPrice = 0;
            $peopleCommPrice = 0;
            
            $dayNum = date('N', strtotime($setDate));
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
                            FROM placePensionRoom AS PPR
                            LEFT JOIN pensionPrice AS PP ON PP.pprIdx = PPR.pprIdx AND '".$setDate."' BETWEEN PP.ppdpStart AND PP.ppdpEnd
                            LEFT JOIN pensionException AS PE ON PE.mpIdx = PPR.mpIdx AND PE.peSetDate = '".$setDate."' AND PE.peUseFlag = 'Y'
                            WHERE PPR.pprIdx = '".$retData['channelRoomTypeNo']."'
                            AND (PPR.pprOpen = '1' OR PPR.pprEOpen = '1')
                            GROUP BY PPR.pprIdx";
            $roomInfo = $this->SV102->query($schQuery)->row_array();
            
            //객실 요금 및 인원요금 재설정
            $basicPrice = $roomInfo['basicPrice'];
            $salePrice = $roomInfo['basicPrice']-$roomInfo['resultPrice'];
            $addPeopleFlag = $roomInfo['pprInAddPay'];
            
            //결제요금 설정
            $commPrice = $basicPrice-$salePrice;
            
            $rPrice = $basicPrice-$salePrice;
            $rSitePrice = 0;
            $addType = "2";
            $peopleCommPrice = 0;
            
            
            //계좌 설정
            $this->SV102->select('pprAccount');
            $this->SV102->where('pprIdx', $info['pprIdx']);
            $roomAccount = $this->SV102->get('placePensionRoom')->row_array();
            
            if(isset($roomAccount['pprAccount'])){
                $ppaIdx = $roomAccount['pprAccount'];
            }else{
                $this->SV102->select('ppaIdx');
                $this->SV102->where('mpIdx', $info['mpIdx']);
                $this->SV102->where('ppaRepr','1');
                $this->SV102->order_by('ppaIdx','DESC');
                $roomAccount = $this->SV102->get('placePensionAccount')->row_array();
                
                if(isset($roomAccount['ppaIdx'])){
                    $ppaIdx = $ppaIdx_arr['ppaIdx'];
                }else{
                    $ppaIdx = "";
                }
            }
            
            //위약금 설정                       
            $penaltyDay = round(abs(strtotime($setDate)-strtotime(date('Y-m-d')))/86400);        
        
            $this->SV102->where('mpIdx', $penaltyIdx);
            $this->SV102->order_by('pppnDay','DESC');
            $result = $this->SV102->get('placePensionPenalty')->row_array();
        
            $penaltyMaxDay = $result['pppnDay'];
            if($penaltyDay > $penaltyMaxDay){
                $penaltyDay = $penaltyMaxDay;
            }
            
            $this->SV102->where('mpIdx', $penaltyIdx);
            $penaltyFlag = $this->SV102->count_all_results('pensionDB.placePensionPenalty');
            if($penaltyFlag == 0){
                $penaltyIdx = "1";
            }
            
            $this->SV102->where('mpIdx', $penaltyIdx);
            $this->SV102->where('pppnDay <=', $penaltyDay);
            $this->SV102->order_by('pppnDay','DESC');
            $dayInfo = $this->SV102->get('pensionDB.placePensionPenalty')->row_array();
            
            $cancelPercent = $dayInfo['pppnPay'];
            
            $cancelPrice = $rPrice/100*$cancelPercent;
            
            $this->db->set('rIdx', $rIdx);
            $this->db->set('rCode', $retData['reservationNo']);
            $this->db->set('mpIdx', $info['mpIdx']);
            $this->db->set('pprIdx', $info['pprIdx']);
            $this->db->set('rPensionRoom', $info['pprName']);
            $this->db->set('rBasicPrice', $basicPrice);
            $this->db->set('rSalePrice', $salePrice);
            $this->db->set('rCouponPrice', 0);
            $this->db->set('rSerialPrice', 0);
            $this->db->set('rTodayPrice', 0);
            $this->db->set('rEtcPrice', 0);
            $this->db->set('rEventPrice', 0);
            $this->db->set('rPrice', $rPrice);
            $this->db->set('rSitePrice', $rSitePrice);
            //$this->db->set('rState', $this->stateArray[$retData['paymentStatus']]);
			$this->db->set('rState', 'PS02');
            $this->db->set('rRevDate', $setDate);
            $this->db->set('pprInMin', $info['pprInMin']);
            $this->db->set('rAddType', $addType);
            $this->db->set('rAdult', 0);
            $this->db->set('rYoung', 0);
            $this->db->set('rBaby', 0);
            $this->db->set('rAdultPrice', 0);
            $this->db->set('rYoungPrice', 0);
            $this->db->set('rBabyPrice', 0);
            $this->db->set('ppaIdx', $ppaIdx);
            $this->db->set('rComm', $payFee);
            $this->db->set('rPeopleComm', $payFee);
            $this->db->set('rCommPrice', ($commPrice*(100-$payFee)/100));
            $this->db->set('rPeopleCommPrice', ($peopleCommPrice*(100-$payFee)/100));
            $this->db->set('rRegDate', date('Y-m-d H:i:s'));
            $this->db->set('rCancelPrice', $cancelPrice);
            $this->db->set('rCancelDate', '');
            $this->db->set('rCancelInfo', '');
            $this->db->set('rAff', $rAffIndex);
            $this->db->insert('pensionRevInfo');
            
            if($i == 0){
                $priIdx = $this->db->insert_id();
                $pensionRevInfoIdx  = $priIdx;
            }else{
                $pensionRevInfoIdx  = $this->db->insert_id();
            }
			
			$this->db->set('rIdx', $rIdx);
            $this->db->set('typeIdx', $pensionRevInfoIdx);
            $this->db->set('type', 'REV');
            $this->db->insert('pensionRevMsg');
			
			$this->roomConnect($info['mpIdx'], $info['pprIdx'], $setDate, '야놀자', 'C', $rIdx);
        }

		return $rIdx;
	}

	function getRoomCheck($pprIdx, $revDate){
		$this->SV102->where('pprIdx', $pprIdx);
		$this->SV102->where('ppbDate', $revDate);
		$result = $this->SV102->count_all_results('pensionDB.placePensionBlock');
		
		return $result;
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
		
		$this->load->library('pension_lib');
		
		$this->pension_lib->partner_sync_lib('1', $pprIdx, $revDate, $type, $rIdx);
		
		return;
	}

	function getConnectRoom($pprIdx, $column){
        $this->SV102->where('pprIdx', $pprIdx);
        $this->SV102->select($column);
        $result = $this->SV102->get('placePensionConnect')->row_array();

        return $result[$column];
    }

	function getPensionInfo($mpIdx, $pprIdx){
		$schQuery = "	SELECT PPB.*, PPC.pprIdx, PPC.gpKey
						FROM placePensionBasic AS PPB
						LEFT JOIN placePensionConnect AS PPC ON PPC.mpIdx = PPB.mpIdx AND PPC.pprIdx = '".$pprIdx."'
						WHERE PPB.mpIdx = '".$mpIdx."'";
		$result = $this->SV102->query($schQuery)->row_array();
		
		return $result;
	}
}