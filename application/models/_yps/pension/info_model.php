<?php
class Info_model extends CI_Model {
    function __construct() {
        parent::__construct();
        $CI =& get_instance();
        $CI->SV102 =& $this->load->database('YP', TRUE);
    }

	function getPensionPhotoCount($mpIdx, $type){
		$this->SV102->where('mpIdx', $mpIdx);
		$this->SV102->where('ppOpen','1');
		$this->SV102->where('ppType', $type);
		$result = $this->SV102->count_all_results('pensionPhoto');

		return $result;
	}

	function getPensionBasicInfo($mpIdx){
		$this->SV102->where('PPB.mpIdx', $mpIdx);
		$result = $this->SV102->get('placePensionBasic AS PPB')->row_array();

		return $result;
	}

	function getPensionInfo($mpIdx){
		$todayDay = date('N', strtotime(date('Y-m-d')));
		if($todayDay < 5){
			$todayDay = 1;
		}
		$schQuery = "SELECT
						MPS.*,
						PPB.*,
						PT.ptPension, PT.ptGuest, PT.ptVilla, PT.ptResort, PT.ptGlamping, PT.ptCaravan, PT.ptAuthAmenity, PT.ptAuthPrice, PT.ptAuthPhoto, PT.ptAuthReserve, PT.ptAuthBed,
						IFNULL(PTS.ptsSale, 0) AS ptsSale,
					    CASE WHEN peIdx THEN
							CASE peDay
							    WHEN '1' THEN ppdpSaleDay1/100*(100-IFNULL(PTS.ptsSale,0))
							    WHEN '5' THEN ppdpSaleDay5/100*(100-IFNULL(PTS.ptsSale,0))
							    WHEN '6' THEN ppdpSaleDay6/100*(100-IFNULL(PTS.ptsSale,0))
							    WHEN '7' THEN ppdpSaleDay7/100*(100-IFNULL(PTS.ptsSale,0))
							ELSE
							    ppdpSaleDay".$todayDay."/100*(100-IFNULL(PTS.ptsSale,0))
							END
					    ELSE
							ppdpSaleDay".$todayDay."/100*(100-IFNULL(PTS.ptsSale,0))
					    END AS resultPrice,
					    CASE WHEN peIdx THEN
							CASE peDay
							    WHEN '1' THEN ppdpDay1
							    WHEN '5' THEN ppdpDay5
							    WHEN '6' THEN ppdpDay6
							    WHEN '7' THEN ppdpDay7
							ELSE
							    ppdpDay".$todayDay."
							END
					    ELSE
							ppdpDay".$todayDay."
					    END AS basicPrice,
					    IFNULL(PN.pnIdx,'') AS pnIdx, IFNULL(PNI.pniImage,'') AS pnImage,
					    IFNULL(PBT.pbIdx,'') AS pbIdx, IFNULL(PBT.pbMainImage,'') AS pbImage,
					    IFNULL(PFS.pfsIdx,'') AS pfsIdx
						FROM mergePlaceSite AS MPS
						LEFT JOIN placePensionBasic AS PPB ON PPB.mpIdx = MPS.mpIdx
						LEFT JOIN pensionType AS PT ON PT.mpIdx = MPS.mpIdx
						LEFT JOIN placePensionRoom AS PPR ON PPR.mpIdx = MPS.mpIdx AND PPR.pprOpen = '1'
						LEFT JOIN pensionPrice AS PPDP ON PPDP.pprIdx = PPR.pprIdx AND PPDP.ppdpSaleDay1 > 0 AND PPDP.ppdpPercent1 < 100 AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
						LEFT JOIN pensionException AS PE ON PE.mpIdx = PPDP.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
						LEFT JOIN pensionNew AS PN ON PN.mpIdx = MPS.mpIdx AND '".date('Y-m-d')."' BETWEEN PN.pnStart AND PN.pnEnd AND PN.pnOpen = 'Y'
						LEFT JOIN pensionNewImage AS PNI ON PNI.pnIdx = PN.pnIdx AND PNI.pniRepr = '1'
						LEFT JOIN pensionBest AS PBT ON PBT.mpIdx = MPS.mpIdx AND '".date('Y-m-d')."' BETWEEN PBT.pbStart AND PBT.pbEnd AND PBT.pbOpen = 'Y'
						LEFT JOIN pensionFreeStay AS PFS ON PFS.mpIdx = MPS.mpIdx AND '".date('Y-m-d')."' BETWEEN PFS.pfsStart AND PFS.pfsEnd AND PFS.pfsOpen = '1' AND PFS.pfsRevDate <= '".date('Y-m-d H:i:s')."'
						LEFT JOIN pensionTodaySale AS PTS ON PTS.mpIdx = MPS.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PPDP.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$todayDay." = '1'
						WHERE MPS.mmType = 'YPS'
						AND MPS.mpType = 'PS'
						AND MPS.mpsOpen = '1'
						AND MPS.mpIdx = '".$mpIdx."'
						HAVING resultPrice > 0
						ORDER BY resultPrice
						LIMIT 1";
		$result = $this->SV102->query($schQuery)->row_array();
		//echo "<pre>".$this->SV102->last_query()."</pre>";
		return $result;
	}

	function getPensionMainPhoto($mpIdx){
        $schQuery = "   SELECT PPEP.ppepFileName AS imageUrl, PPEP.ppepMainSort AS sort, 'E' AS photoType
                        FROM placePensionEtcPhoto AS PPEP
                        LEFT JOIN placePensionEtc AS PPE ON PPE.ppeIdx = PPEP.ppeIdx
                        WHERE PPEP.mpIdx = '".$mpIdx."'
                        AND PPEP.ppepOpen = '1'
                        AND PPEP.ppepRepr = '1'
                        UNION ALL
                        SELECT pprpFileName AS imageUrl, pprpMainSort AS sort, 'R' AS photoType
                        FROM placePensionRoomPhoto
                        WHERE mpIdx = '".$mpIdx."'
                        AND pprpOpen = '1'
                        AND pprpRepr = '1'
                        ORDER BY sort ASC";
        $result = $this->SV102->query($schQuery)->result_array();

        return $result;
    }

	function getPensionTipCount($mpIdx){
		$this->SV102->where('mpIdx', $mpIdx);
		//$this->SV102->where('ptBlindFlag','0');
		$this->SV102->where('ptFlag','0');
		$result = $this->SV102->count_all_results('pensionTip');

		return $result;
	}

	function getPensionService($mpIdx, $mpsIdx){
		$schQuery = "	SELECT PPT.pptIdx, PPT.pptContent, MT.mtCode, MT.mtName, MT.mtIdx
						FROM placeTheme AS PT
						LEFT JOIN mergeTheme AS MT ON MT.mtIdx = PT.mtIdx AND MT.mtSite = 'YPS' AND MT.mtType = 'PS'
						LEFT JOIN mergeTheme AS basicMT ON basicMT.mtCode = SUBSTR(MT.mtCode,1,5) AND MT.mtSite = 'YPS' AND MT.mtType = 'PS'
						LEFT JOIN placePensionTheme AS PPT ON PT.mtIdx = PPT.mtIdx AND PPT.pptOpen = '1' AND PPT.mpIdx = '".$mpIdx."'
						WHERE PT.mpsIdx = '".$mpsIdx."'
						AND PT.mtCode LIKE '2%'
						AND LENGTH(PT.mtCode) = '8'
						AND MT.mtOpen = '1'
						AND PT.mtCode != '2.001005'
						ORDER BY basicMT.mtSort ASC, MT.mtSort ASC";
		$result = $this->SV102->query($schQuery)->result_array();

		return $result;
	}

	function getPensionPartner($partner){
		$this->SV102->where_in('ppcnIdx', $partner);
		$result = $this->SV102->get('placePensionConnectName')->result_array();

		return $result;
	}

	function getPensionNoticeLists($mpIdx, $limit = 9999){
		$schQuery = "	SELECT *
						FROM pensionEvent
						WHERE mpIdx = '".$mpIdx."'
						AND peOpen = '1'
						AND '".date('Y-m-d H:i:s')."' BETWEEN peStartDate AND peEndDate
						ORDER BY peType DESC, peRegDate DESC
						LIMIT ".$limit;
		$result = $this->SV102->query($schQuery)->result_array();

		return $result;
	}

	function getPensionNoticeInfo($ptIdx){
		$this->SV102->where('ptIdx', $ptIdx);
		$result = $this->SV102->get('pensionEvent')->row_array();

		return $result;
	}

	function getPensionRoomInfo($pprIdx, $setDate){
		$dayNum = date('N', strtotime($setDate));
		if($dayNum < 5){
			$dayNum = 1;
		}
		$schQuery = "	SELECT
						    PPR.*,
						    PPB.ppbTimeFlag, PPB.ppbTimeIn, PPB.ppbTimeOut, PPB.ppbReserve,
						    IFNULL(PTS.ptsSale,0) AS ptsSale,
							IFNULL(PTS.ptsStartTime, '00:00') AS ptsStartTime,
							IFNULL(PTS.ptsEndTime, '00:00') AS ptsEndTime,
						    CASE WHEN PE.peIdx THEN
								CASE PE.peDay
								    WHEN '1' THEN MIN(PP.ppdpSaleDay1/100*(100-IFNULL(PTS.ptsSale,0)))
								    WHEN '5' THEN MIN(PP.ppdpSaleDay5/100*(100-IFNULL(PTS.ptsSale,0)))
								    WHEN '6' THEN MIN(PP.ppdpSaleDay6/100*(100-IFNULL(PTS.ptsSale,0)))
								    WHEN '7' THEN MIN(PP.ppdpSaleDay7/100*(100-IFNULL(PTS.ptsSale,0)))
								ELSE
								    MIN(PP.ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
								END
						    ELSE
								MIN(PP.ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
						    END AS resultPrice,
						    CASE WHEN PE.peIdx THEN
								CASE PE.peDay
								    WHEN '1' THEN MIN(PP.ppdpDay1)
								    WHEN '5' THEN MIN(PP.ppdpDay5)
								    WHEN '6' THEN MIN(PP.ppdpDay6)
								    WHEN '7' THEN MIN(PP.ppdpDay7)
								ELSE
								    MIN(PP.ppdpDay".$dayNum.")
								END
						    ELSE
								MIN(PP.ppdpDay".$dayNum.")
						    END AS basicPrice
						FROM placePensionRoom AS PPR
						LEFT JOIN pensionPrice AS PP ON PP.pprIdx = PPR.pprIdx AND '".$setDate."' BETWEEN PP.ppdpStart AND PP.ppdpEnd
						LEFT JOIN pensionException AS PE ON PE.mpIdx = PPR.mpIdx AND PE.peSetDate = '".$setDate."' AND PE.peUseFlag = 'Y'
						LEFT JOIN placePensionBasic AS PPB ON PPB.mpIdx = PPR.mpIdx
						LEFT JOIN pensionTodaySale AS PTS ON PTS.mpIdx = PPR.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PPR.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$dayNum." = '1'
						WHERE PPR.pprOpen = '1'
						AND PP.ppdpSaleDay1 > 0
						AND PP.ppdpPercent1 < 100
						AND PPR.pprIdx = '".$pprIdx."'
						GROUP BY PP.pprIdx";
		$result = $this->SV102->query($schQuery)->row_array();

		return $result;
	}

	function getPensionRoomLists($mpIdx, $setDate){
		$dayNum = date('N', strtotime($setDate));
		if($dayNum < 5){
			$dayNum = 1;
		}
			$schQuery = "	SELECT
								T.*,
								IFNULL(PTS.ptsSale,0) AS ptsSale,
								IFNULL(PTS.ptsStartTime, '00:00') AS ptsStartTime,
								IFNULL(PTS.ptsEndTime, '00:00') AS ptsEndTime,
							    CASE WHEN PE.peIdx THEN
									CASE PE.peDay
									    WHEN '1' THEN MIN(PP.ppdpSaleDay1/100*(100-IFNULL(PTS.ptsSale,0)))
									    WHEN '5' THEN MIN(PP.ppdpSaleDay5/100*(100-IFNULL(PTS.ptsSale,0)))
									    WHEN '6' THEN MIN(PP.ppdpSaleDay6/100*(100-IFNULL(PTS.ptsSale,0)))
									    WHEN '7' THEN MIN(PP.ppdpSaleDay7/100*(100-IFNULL(PTS.ptsSale,0)))
									ELSE
									    MIN(PP.ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
									END
							    ELSE
									MIN(PP.ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
							    END AS resultPrice,
							    CASE WHEN PE.peIdx THEN
									CASE PE.peDay
									    WHEN '1' THEN MIN(PP.ppdpDay1)
									    WHEN '5' THEN MIN(PP.ppdpDay5)
									    WHEN '6' THEN MIN(PP.ppdpDay6)
									    WHEN '7' THEN MIN(PP.ppdpDay7)
									ELSE
									    MIN(PP.ppdpDay".$dayNum.")
									END
							    ELSE
									MIN(PP.ppdpDay".$dayNum.")
							    END AS basicPrice,
							    PPB.ppbReserve
							FROM (
								SELECT
							    	PPR.*, PPRP.pprpFileName
								FROM placePensionRoom AS PPR
								LEFT JOIN placePensionRoomPhoto AS PPRP ON PPRP.pprIdx = PPR.pprIdx
								WHERE PPR.pprOpen = '1'
								AND PPR.mpIdx = '".$mpIdx."'
								ORDER BY PPRP.pprpRepr DESC, PPRP.pprpNo ASC
							) AS T
							LEFT JOIN pensionPrice AS PP ON PP.pprIdx = T.pprIdx AND '".$setDate."' BETWEEN PP.ppdpStart AND PP.ppdpEnd
							LEFT JOIN pensionException AS PE ON PE.mpIdx = T.mpIdx AND PE.peSetDate = '".$setDate."' AND PE.peUseFlag = 'Y'
							LEFT JOIN placePensionBasic AS PPB ON PPB.mpIdx = T.mpIdx
							LEFT JOIN pensionTodaySale AS PTS ON PTS.mpIdx = PP.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PP.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$dayNum." = '1'
							WHERE PP.ppdpSaleDay1 > 0
							AND PP.ppdpPercent1 < 100
							GROUP BY T.pprIdx
							ORDER BY T.pprNo DESC";
		$result = $this->SV102->query($schQuery)->result_array();

		return $result;
	}

	function getPensionEtcInfo($ppeIdx){
		$this->SV102->where('PPE.ppeIdx', $ppeIdx);
		$this->SV102->where('PPE.ppeOpen','1');
		$this->SV102->join('placePensionBasic AS PPB','PPB.mpIdx = PPE.mpIdx','LEFT');
		$result = $this->SV102->get('placePensionEtc AS PPE')->row_array();

		return $result;
	}

	function getPensionEtcLists($mpIdx){
		$this->SV102->where('PPE.mpIdx', $mpIdx);
		$this->SV102->where('PPE.ppeOpen','1');
		$this->SV102->order_by('PPE.ppeNo','ASC');
		$result = $this->SV102->get('placePensionEtc AS PPE')->result_array();

		return $result;
	}

	function getPensionRoomPhoto($pprIdx){
		$this->SV102->where('PPRP.pprIdx', $pprIdx);
		$this->SV102->where('PPRP.pprpOpen','1');
		$this->SV102->order_by('PPRP.pprpRepr','DESC');
		$this->SV102->order_by('PPRP.pprpNo','ASC');
		$result = $this->SV102->get('placePensionRoomPhoto AS PPRP')->result_array();

		return $result;
	}

	function getPensionEtcPhoto($mpIdx, $ppeIdx){
		$this->SV102->where('PPEP.mpIdx', $mpIdx);
		$this->SV102->where('PPEP.ppeIdx', $ppeIdx);
		$this->SV102->where('PPEP.ppepOpen','1');
		$this->SV102->order_by('PPEP.ppepRepr','DESC');
		$this->SV102->order_by('PPEP.ppepNo','ASC');
		$result = $this->SV102->get('placePensionEtcPhoto AS PPEP')->result_array();

		return $result;
	}

	function getPensionPhoto($mpIdx, $type){
		$this->SV102->where('PP.mpIdx', $mpIdx);

		if($type == "V"){
			$this->SV102->where('PP.ppType','V');
		}else if($type == "RL"){
			$this->SV102->where('PP.ppType','R');
		}

		$this->SV102->where('PP.ppOpen','1');
		$this->SV102->order_by('PP.ppRepr','DESC');
		$this->SV102->order_by('PP.ppSort','ASC');
		$result = $this->SV102->get('pensionPhoto AS PP')->result_array();
		//echo "<pre>".$this->SV102->last_query()."</pre>";
		return $result;
	}

	function getPensionPriceLists($mpIdx, $pprIdx, $startDate, $endDate, $btDay){
        $this->SV102->where('mpIdx', $mpIdx);
        $info = $this->SV102->get('placePensionBasic')->row_array();

        $schQuery = "   SELECT *
                        FROM pensionException
                        WHERE mpIdx = '".$mpIdx."'
                        AND peSetDate BETWEEN '".$startDate."' AND '".$endDate."'
                        AND peUseFlag = 'Y'
                        ORDER BY peSetDate ASC";
        $exceptionLists = $this->SV102->query($schQuery)->result_array();

        $exception = array();
        $exceptionName = array();
        if(count($exceptionLists) > 0){
            foreach($exceptionLists as $exceptionLists){
                $exception[$exceptionLists['peSetDate']] = $exceptionLists['peDay'];
                $exceptionName[$exceptionLists['peDate']] = $exceptionLists['peName'];
            }
        }

		$schQuery = "	SELECT *
						FROM holidayDate
						WHERE hdDate BETWEEN '".$startDate."' AND '".$endDate."'";
		$holidayLists = $this->SV102->query($schQuery)->result_array();

		$holiday = array();
		if(count($holidayLists) > 0){
			foreach($holidayLists as $holidayLists){
				array_push($holiday, $holidayLists['hdDate']);
			}
		}

        $schQuery = "   SELECT PPB.*, R.rPaymentState
                        FROM placePensionBlock AS PPB
                        LEFT JOIN reservation AS R ON R.rIdx = PPB.rIdx
                        WHERE PPB.pprIdx = '".$pprIdx."'
                        AND PPB.ppbDate BETWEEN '".$startDate."' AND '".$endDate."'
                        GROUP BY PPB.ppbIdx
                        ORDER BY PPB.ppbDate ASC
                        ";

        $blockLists = $this->SV102->query($schQuery)->result_array();
        $blockArray = array();
        if(count($blockLists) > 0){
            foreach($blockLists as $blockLists){
                if($blockLists['rPaymentState'] == "PS01"){
                    $blockArray[$blockLists['ppbDate']] = "S";
                }else{
                    $blockArray[$blockLists['ppbDate']] = "W";
                }

            }
        }
		
        $schQuery = "   SELECT *
                        FROM pensionPrice AS PP
                        WHERE PP.pprIdx = '".$pprIdx."'
                        AND PP.ppdpEnd >= '".$startDate."'
                        AND PP.ppdpStart <= '".$endDate."
                        AND PP.ppdpSaleDay1 > 0
                        ORDER BY PP.ppdpStart ASC'";
        $priceLists = $this->SV102->query($schQuery)->result_array();
		
		$todayDayNum = date('N', strtotime(date('Y-m-d')));
        if($todayDayNum < 5){
            $todayDayNum = 1;
        }
		
		$schQuery = "	SELECT *
						FROM pensionTodaySale AS PTS
						WHERE PTS.mpIdx = '".$mpIdx."'
						AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd
						AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime
						AND PTS.ptsOpen = '1'
						AND PTS.ptsDay".$todayDayNum." = '1'
						AND PTS.pprIdx LIKE '%".$pprIdx."%'
						GROUP BY PTS.mpIdx";
					
		$todaySaleInfo = $this->SV102->query($schQuery)->row_array();
		if(isset($todaySaleInfo['ptsIdx'])){
			$todaySale = $todaySaleInfo['ptsSale'];
		}else{
			$todaySale = 0;
		}
					
        $result = array();

        $setDateArray = explode("-", $startDate);
        $dayNameArray = array('일','월','화','수','목','금','토');

        $result = array();

        for($i=0; $i< $btDay; $i++){
            $setDate = date('Y-m-d', mktime(0, 0, 0, $setDateArray[1], $setDateArray[2]+$i, $setDateArray[0]));
            $dayNum = date('N', strtotime($setDate));
            if($dayNum < 5){
                $dayNum = 1;
            }
            $result[$i]['info']['date'] = $setDate;
            $result[$i]['info']['dateText'] = date('m/d', strtotime($setDate))." (".$dayNameArray[date('w', strtotime($setDate))].")";
            $result[$i]['info']['dayNum'] = (string)$dayNum;

            if($info['ppbDateCheck'] == 0 && $setDate >= date('Y').'-07-01' && $setDate <= date('Y').'-08-31'){
                $result[$i]['info']['block'] = "3";
            }else{
                if(isset($blockArray[$setDate])){
                    if($blockArray[$setDate] == "S"){
                        $result[$i]['info']['block'] = "2";
                    }else{
                        $result[$i]['info']['block'] = "1";
                    }
                }else{
                    $result[$i]['info']['block'] = "0";
                }
            }

            for($j=0; $j< count($priceLists); $j++){
                if($setDate >= $priceLists[$j]['ppdpStart'] && $setDate <= $priceLists[$j]['ppdpEnd']){
                    if(isset($exceptionName[$setDate])){
                        $result[$i]['info']['dayName'] = $exceptionName[$setDate];
                    }else{
                        $result[$i]['info']['dayName'] = $priceLists[$j]['ppdpName'];
                    }
					
                    if(isset($exception[$setDate])){
                        $dayNum = $exception[$setDate];
						$result[$i]['info']['dayNum'] = (string)$dayNum;
                    }
					
					if($result[$i]['info']['dayName'] == ""){
						$result[$i]['info']['dayName'] = "비수기";
					}
					
					$result[$i]['info']['price'] = array();
                    $result[$i]['info']['price']['basic'] = number_format($priceLists[$j]['ppdpDay'.$dayNum]);
					
                    if((date('Y-m-d') >= YAPEN_SALE_EVENT_START && $setDate >= YAPEN_SALE_EVENT_START && $setDate <= YAPEN_SALE_EVENT_END) ||
					   ($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
                        $resultPrice = (floor(($priceLists[$j]['ppdpSaleDay'.$dayNum]-($priceLists[$j]['ppdpSaleDay'.$dayNum]*0.02))/10)*10);
                    }else{
                        $resultPrice = ($priceLists[$j]['ppdpSaleDay'.$dayNum]);
                    }
					
					if($todaySale > 0 && $setDate == date('Y-m-d')){
						$resultPrice = floor(($resultPrice/100*(100-$todaySale))/10)*10;
					}
					
					$result[$i]['info']['price']['result'] = number_format($resultPrice);
					
					if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && $setDate >= YAPEN_SALE_EVENT_START && $setDate <= YAPEN_SALE_EVENT_END) ||
                    	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
                        if($priceLists[$j]['ppdpPercent'.$dayNum] == 0){
                            $salePercent = "2%";
                        }else{
                            $salePercent = round(100-($resultPrice/$priceLists[$j]['ppdpDay'.$dayNum]*100),0)."%+2%";
                        }
                    }else{
                    	if($priceLists[$j]['ppdpDay'.$dayNum] != 0){
                        	$salePercent = round(100-($resultPrice/$priceLists[$j]['ppdpDay'.$dayNum]*100),0)."%";
						}else{
							$salePercent = 0;
						}
                    }

                    $result[$i]['info']['price']['percent'] = $salePercent;
					if(in_array($setDate, $holiday)){
						$result[$i]['info']['dayNum'] = "7";
					}

                    break;
                }
            }
        }

        return $result;
    }

	function insPensionReport($mpIdx, $mbIdx, $content){
		$this->SV102->where('mpIdx', $mpIdx);
		$this->SV102->where('mmType','YPS');
		$this->SV102->where('mpType','PS');
		$info = $this->SV102->get('mergePlaceSite')->row_array();

		$this->SV102->where('mbIdx', $mbIdx);
		$userInfo = $this->SV102->get('member')->row_array();

		$this->db->set('mpIdx', $mpIdx);
		$this->db->set('mbIdx', $mbIdx);
		$this->db->set('prName', $userInfo['mbNick']);
		$this->db->set('prPensionName', $info['mpsName']);
		$this->db->set('prPensionAddress', ($info['mpsAddr1'].' '.$info['mpsAddr2']));
		$this->db->set('prContent', $content);
		$this->db->set('prRegDate', date('Y-m-d H:i:s'));
		$this->db->set('prCheckFlag', 'N');
		$this->db->insert('pensionReport');
	}

	function getTodaySaleInfo($mpIdx){
		$dayNum = date('N', strtotime(date('Y-m-d')));
		if($dayNum < 5){
			$dayNum = 1;
		}
		$schQuery = "	SELECT PTS.mpIdx, MIN(PTS.ptsStartTime) AS startTime, MAX(PTS.ptsEndTime) AS endTime
						FROM pensionTodaySale AS PTS
						WHERE PTS.mpIdx = '".$mpIdx."'
						AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd
						AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime
						AND PTS.ptsOpen = '1'
						AND PTS.ptsDay".$dayNum." = '1'
						GROUP BY PTS.mpIdx";
		$result = $this->SV102->query($schQuery)->row_array();
		
		return $result;
	}

}