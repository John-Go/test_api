<?php
class Pension_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    function getMainPensionList($schLocation, $idxStrings){
        $this->db->join('placePensionUse AS PPU','PVB.mpIdx = PPU.mpIdx','LEFT');
        $this->db->join('mergePlaceSite AS MPS',"PVB.mpIdx = MPS.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->db->join('placeTheme AS PT','MPS.mpsIdx = PT.mpsIdx');
        if($schLocation != ""){
            $this->db->where("PT.mtCode LIKE '".$schLocation."%'");
        }
        $this->db->where('PPU.ppuPullFlag','1');
        $this->db->where('MPS.mpsOpen','1');
        $this->db->group_by('PVB.mpIdx');
        $countArray = $this->db->get('placeVillaBasic AS PVB')->result_array();
        $result['totCount'] = count($countArray);
        
        $this->db->join('placePensionUse AS PPU','PVB.mpIdx = PPU.mpIdx','LEFT');
        $this->db->join('mergePlaceSite AS MPS',"PVB.mpIdx = MPS.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        
        $this->db->join('placeTheme AS PT','MPS.mpsIdx = PT.mpsIdx');
        $this->db->where('PPU.ppuPullFlag','1');
        $this->db->where('MPS.mpsOpen','1');
        $this->db->order_by('PVB.pvbGrade','DESC');
        
        $this->db->order_by('rand()');
        $this->db->group_by('PVB.mpIdx');
        
        if($schLocation != ""){
            $this->db->where("PT.mtCode LIKE '".$schLocation."%'");
        }

        if(count($idxStrings) > 0){
            $this->db->where_not_in('PVB.mpIdx',$idxStrings);
        }
        
        $result['list'] = $this->db->get('placeVillaBasic AS PVB','10')->result_array();
        
        return $result;
    }
    
    function getSchPensionList($schText, $themeCode, $idxStrings){
        $holyQuery = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday WHERE hDate-INTERVAL 1 DAY = '".date('Y-m-d')."'";
        $holyRow = $this->db->query($holyQuery)->row_array();
        
        $holiDayCheck = $this->holidayCheck(date('Y-m-d'));
        
        $date = date('Y-m-d');
        $dateObj = new DateTime($date);
        $numOfWeek = $dateObj->format('N');
        
        $toNumOfWeek = $numOfWeek;
        if($toNumOfWeek < 5){
            $toNumOfWeek = "1";
        }
        if(isset($holiDayCheck[$date])){    // 공휴일 날짜가 있을경우
            $numOfWeek = "6";
        }else{
            if($numOfWeek < 5){
                $numOfWeek = "1";
            }
        }
    
        if(!isset($holyRow['hIdx'])){
            $holyRow['hIdx'] = "";
        }
            
            
        $this->db->join('placePensionUse AS PPU','PVB.mpIdx = PPU.mpIdx','LEFT');
        $this->db->join('mergePlaceSite AS MPS',"PVB.mpIdx = MPS.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->db->join('placePensionBasic AS PPB','PVB.mpIdx = PPB.mpIdx','LEFT');
        $this->db->join('placeTheme AS PT','MPS.mpsIdx = PT.mpsIdx');
        $this->db->where('PPU.ppuPullFlag','1');
        if(count($themeCode) > 0){
            $schCode = array();
            foreach($themeCode as $themeCode){
                $schCode[] = $themeCode['mtCode'];
            }
            $this->db->where_in('PT.mtCode', $schCode);
        }
        if($schText != "" && count($themeCode) == 0){
            $this->db->where("MPS.mpsName LIKE '%".$schText."%'");
        }        
        $this->db->group_by('PVB.mpIdx');
        $totCount = $this->db->get('placeVillaBasic AS PVB')->result_array();

        $result['totCount'] = count($totCount);
        
        $this->db->join('placePensionUse AS PPU','PVB.mpIdx = PPU.mpIdx','LEFT');
        $this->db->join('mergePlaceSite AS MPS',"PVB.mpIdx = MPS.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->db->join('placePensionBasic AS PPB','PVB.mpIdx = PPB.mpIdx','LEFT');
        $this->db->join('placeTheme AS PT','MPS.mpsIdx = PT.mpsIdx','LEFT');
        $this->db->where('PPU.ppuPullFlag','1');
        $this->db->order_by('PVB.pvbGrade','DESC');
        $this->db->order_by('PPB.ppbReserve','ASC');
        $this->db->order_by('rand()');
        $this->db->group_by('PVB.mpIdx');
        $this->db->select('*');
        $this->db->select("(SELECT CASE WHEN HE.heIdx THEN CONCAT(ppdpSaleDay".$toNumOfWeek.",'|',ppdpPercent".$toNumOfWeek.") ELSE CONCAT(ppdpSaleDay".$numOfWeek.",'|',ppdpPercent".$numOfWeek.") END AS price
        
                FROM pensionPrice AS SPPDP
                LEFT JOIN holidayExclude AS HE ON SPPDP.mpIdx = HE.mpIdx AND HE.hIdx = '".$holyRow['hIdx']."'
                WHERE SPPDP.mpIdx = PVB.mpIdx AND '".$date."' BETWEEN SPPDP.ppdpStart AND SPPDP.ppdpEnd
                HAVING price > 0 ORDER BY price ASC LIMIT 1) AS price", false);
        
        if(count($themeCode) > 0){
            $this->db->where_in('PT.mtCode', $schCode);
        }
        if($schText != "" && count($themeCode) == 0){
            $this->db->where("MPS.mpsName LIKE '%".$schText."%'");
        }
        if(count($idxStrings) > 0){
            $this->db->where_not_in('PVB.mpIdx',$idxStrings);
        }
        
        $result['list'] = $this->db->get('placeVillaBasic AS PVB',10)->result_array();
        
        return $result;
    }
    
    function getThemeCode($schText){
        $this->db->like('mtSite','YPS');
        $this->db->like('mtType','PS');
        $this->db->like('mtName',$schText);
        $result = $this->db->get('mergeTheme')->result_array();
        
        return $result;
    }
    
    function holidayCheck($date){
        $this->db->select("(hDate + INTERVAL -1 DAY) as ageDate");
        $this->db->where('(hDate + INTERVAL -1 DAY) >=', $date);
        $this->db->where('(hDate + INTERVAL -1 DAY) <=', $date);
        $result = $this->db->get('holiday', 1, 0)->result_array();
        
        $arrayResult = array();

        foreach ($result as $row) {
            $arrayResult[$row['ageDate']] = 1;
        }
        
        return $arrayResult;
    }
    
    function getPensionInfo($mpIdx){
        $date = date('Y-m-d');
            
        $dateObj = new DateTime($date);
        $dayNum = $dateObj->format('N');
        
        $holyQuery = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday WHERE hDate-INTERVAL 1 DAY = '".$date."'";
        $holyRow = $this->db->query($holyQuery)->row_array();
        
        
        if(isset($holyRow['hIdx'])){    // 공휴일 날짜가 있을경우
            $flag_sql = "SELECT COUNT(*) AS cnt FROM holidayExclude WHERE hIdx = '".$holyRow['hIdx']."' AND mpIdx = '".$mpIdx."'";
            $flag_arr = $this->db->query($flag_sql)->row_array();
            if($flag_arr['cnt'] == 0){
                $dayNum = "6";
            }else if($dayNum < 5){
                $dayNum = "1";
            }
        }else{
            if($dayNum < 5){
                $dayNum = "1";
            }
        }
        
        $schQuery = "   SELECT
                            MPS.mpsMapX, MPS.mpsMapY, MPS.mpsName, MPS.mpsAddr1, MPS.mpsAddr2, MPS.mpsAddr1New, MPS.mpsTelService, MPS.mpsIdx, 
                            PPB.ppbWantCnt, PPB.ppbReserve, PPB.ppbTel1, PPB.ppbTel2, PPB.ppbTel3, ppbEventFlag, PPB.mpIdx,
                            MIN(PPDP.ppdpSaleDay".$dayNum.") AS price, MAX(PPDP.ppdpPercent".$dayNum.") AS percent,
                            PE.peIdx, PE.peTitle, PE.peStartDate, PE.peENdDate,
                            PPU.ppuPullFlag,
                            PT.theme, PVB.pvbPageUrl, PVB.pvbGrade
                        FROM placePensionBasic AS PPB
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPB.mpIdx AND MPS.mmType LIKE '%YPS%' AND MPS.mpType = 'PS'
                        LEFT JOIN pensionPrice AS PPDP ON PPDP.mpIdx = PPB.mpIdx AND PPDP.ppdpSaleDay".$dayNum." > 0 AND PPDP.ppdpPercent".$dayNum." < 100 AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                        LEFT JOIN placePensionUse AS PPU ON PPB.mpIdx = PPU.mpIdx
                        LEFT JOIN placeVillaBasic AS PVB ON PPB.mpIdx = PVB.mpIdx
                        LEFT JOIN (
                            SELECT peIdx,peTitle,peIntro,peStartDate,peEndDate,mpIdx
                            FROM pensionEvent AS PE
                            WHERE PE.peOpen > 0
                            AND PE.mpIdx = '".$mpIdx."'     
                            AND PE.peEndDate >= '".date('Y-m-d H:i:s')."'
                            ORDER BY PE.peIdx DESC
                            LIMIT 1
                        ) AS PE ON PE.mpIdx = PPB.mpIdx
                        LEFT JOIN (
                            SELECT GROUP_CONCAT(MT.mtName) AS theme, PT.mpsIdx
                            FROM mergeTheme AS MT 
                            LEFT JOIN placeTheme AS PT ON MT.mtCode = PT.mtCode
                            WHERE MT.mtOpen = '1'
                            AND MT.mtType = 'PS'
                            AND MT.mtSite LIKE '%YPS%'
                            AND MT.mtCode LIKE '2%'
                            GROUP BY PT.mpsIdx
                        ) AS PT ON PT.mpsIdx = MPS.mpsIdx
                        WHERE PPB.mpIdx = '".$mpIdx."'
                        AND MPS.mpsOpen = '1'
                        GROUP BY PPDP.mpIdx";
        $result = $this->db->query($schQuery)->row_array();
        
        return $result;
    }
    
    public function pensionMinPrice( $mpIdx )
    {
        $date = date('Y-m-d');
        $dateObj = new DateTime($date);
        $numOfWeek = $dateObj->format('N');
        
        $holyQuery = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday WHERE hDate-INTERVAL 1 DAY = '".$date."'";
        $holyRow = $this->db->query($holyQuery)->row_array();
        
        
        if(isset($holyRow['hIdx'])){    // 공휴일 날짜가 있을경우
            $flag_sql = "SELECT COUNT(*) AS cnt FROM holidayExclude WHERE hIdx = '".$holyRow['hIdx']."' AND mpIdx = '".$mpIdx."'";
            $flag_arr = $this->db->query($flag_sql)->row_array();
            if($flag_arr['cnt'] == 0){
                $numOfWeek = "6";
            }else if($numOfWeek < 5){
                $numOfWeek = "1";
            }
        }else{
            if($numOfWeek < 5){
                $numOfWeek = "1";
            }
        }
        
        $arrayResult = array();

        foreach ($result as $row) {
            $arrayResult[$row['ageDate']] = 1;
        }
        
        $query = "SELECT ppdpSaleDay".$numOfWeek.", ppdpDay".$numOfWeek."
                FROM pensionPrice
                WHERE mpIdx = '".$mpIdx."' AND '".$date."' BETWEEN ppdpStart AND ppdpEnd
                AND ppdpSaleDay".$numOfWeek." > 0
                ORDER BY ppdpSaleDay".$numOfWeek." ASC";
        $priceRow = $this->db->query($query)->result_array();
        $saleArray = array();
        $priceArray = array();
        foreach($priceRow as $priceRow){
            $saleArray[] = round(100-($priceRow['ppdpSaleDay'.$numOfWeek]/$priceRow['ppdpDay'.$numOfWeek]*100),0);
            $priceArray[] = $priceRow['ppdpSaleDay'.$numOfWeek];
        }
        
        $price = min($priceArray);
        $sale = max($saleArray);
        
        $result = array(
            'minPrice' => (string)number_format($price),
            'maxSalePercent' => (string)$sale
        );
        
        return $price;
    }

    function pensionReprEtcImageLists($mpIdx){
        $this->db->start_cache();
        $this->db->where('PPEP.mpIdx', $mpIdx);
        $this->db->where('PPEP.ppepOpen > ', 0);
        $this->db->where('PPEP.ppepRepr > ', 0);
        $this->db->stop_cache();

        $result['count'] = $this->db->count_all_results('placePensionEtcPhoto AS PPEP');

        $this->db->select("PPEP.ppepFileName");
        $this->db->join('placePensionEtc AS PPE','PPE.ppeIdx = PPEP.ppeIdx','LEFT');
        $this->db->order_by("PPE.ppeNo ASC");
        $this->db->order_by("PPEP.ppepIdx DESC");
        $result['query'] = $this->db->get('placePensionEtcPhoto AS PPEP')->result_array();
        
        $this->db->flush_cache();
        return $result;
    }
    
    function pensionImageLists($mpIdx){

        $this->db->start_cache();
        $this->db->where('mpIdx', $mpIdx);
        $this->db->where('pprpOpen > ', 0);
        $this->db->where('pprpRepr > ', 0);
        $this->db->stop_cache();

        $result['count'] = $this->db->count_all_results('placePensionRoomPhoto');

        $this->db->select("pprpFileName");
        $this->db->order_by("pprpNo desc");
        $result['query'] = $this->db->get('placePensionRoomPhoto')->result_array();

        $this->db->flush_cache();
        return $result;
    }

    function getRoomInfo($mpIdx){
        $dayNum = date('N', strtotime(date('Y-m-d')));
        if($dayNum < 5){
            $dayNum = 1;
        }
        
        $schQuery = "   SELECT PPR.*, PPB.*, PPRP.pprpFileName, 
                        CASE WHEN peIdx THEN
                            CASE peDay
                                WHEN '1' THEN MAX(ppdpPercent1)
                                WHEN '5' THEN MAX(ppdpPercent5)
                                WHEN '6' THEN MAX(ppdpPercent6)
                                WHEN '7' THEN MAX(ppdpPercent7)
                            ELSE
                                MAX(ppdpPercent".$dayNum.")
                            END
                        ELSE
                            MAX(ppdpPercent".$dayNum.")
                        END AS percent,
                        CASE WHEN peIdx THEN
                            CASE peDay
                                WHEN '1' THEN MIN(ppdpSaleDay1)
                                WHEN '5' THEN MIN(ppdpSaleDay5)
                                WHEN '6' THEN MIN(ppdpSaleDay6)
                                WHEN '7' THEN MIN(ppdpSaleDay7)
                            ELSE
                                MIN(ppdpSaleDay".$dayNum.")
                            END
                        ELSE
                            MIN(ppdpSaleDay".$dayNum.")
                        END AS resultPrice,
                        CASE WHEN peIdx THEN
                            CASE peDay
                                WHEN '1' THEN MIN(ppdpDay1)
                                WHEN '5' THEN MIN(ppdpDay5)
                                WHEN '6' THEN MIN(ppdpDay6)
                                WHEN '7' THEN MIN(ppdpDay7)
                            ELSE
                                MIN(ppdpDay".$dayNum.")
                            END
                        ELSE
                            MIN(ppdpDay".$dayNum.")
                        END AS basicPrice
                        FROM placePensionRoom AS PPR
                        LEFT JOIN placePensionBasic AS PPB ON PPB.mpIdx = PPR.mpIdx
                        LEFT JOIN placePensionRoomPhoto AS PPRP ON PPRP.pprIDx = PPR.pprIdx AND PPRP.pprpRepr = '1'
                        LEFT JOIN pensionPrice AS PP ON PP.pprIdx = PPR.pprIdx AND '".date('Y-m-d')."' BETWEEN PP.ppdpStart AND PP.ppdpEnd
                        LEFT JOIN pensionException AS PE ON PE.mpIdx = PPR.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                        WHERE PPR.mpIdx = '".$mpIdx."'
                        AND PPR.pprOpen = '1'
                        GROUP BY PPR.pprIdx
                        ORDER BY PPR.pprNo DESC";
        $result = $this->db->query($schQuery)->result_array();
        
        foreach ( $result as $key => $value )
        {
            $result[$key] = $this->roomData2String( $value );
        }
        
        return $result;
    }
    
    public function pensionRoomImageLists($idx, $limit, $offset){
        $this->db->start_cache();
        $this->db->where('pprIdx', $idx);
        $this->db->where('pprpOpen > ', 0);
        $this->db->where('pprpFileName is not null','',false);
        
        $this->db->stop_cache();
        
        $result['count'] = $this->db->count_all_results('pensionDB.placePensionRoomPhoto');
        $this->db->select('mpIdx,pprpFileName, pprpRepr');
        $this->db->where('pprpFileName is not null','',false);      
        $this->db->order_by('pprpNo', 'asc');
        $result['query'] = $this->db->get('pensionDB.placePensionRoomPhoto', $offset, $limit)->result_array();

        $this->db->flush_cache();
        return $result;
    }
    
    function getRoomPrice($mpIdx, $pprIdx){
        $date = date('Y-m-d');
        $dateObj = new DateTime($date);
        $numOfWeek = $dateObj->format('N');
        
        $holyQuery = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday WHERE hDate-INTERVAL 1 DAY = '".$date."'";
        $holyRow = $this->db->query($holyQuery)->row_array();
        
        
        if(isset($holyRow['hIdx'])){    // 공휴일 날짜가 있을경우
            $flag_sql = "SELECT COUNT(*) AS cnt FROM holidayExclude WHERE hIdx = '".$holyRow['hIdx']."' AND mpIdx = '".$mpIdx."'";
            $flag_arr = $this->db->query($flag_sql)->row_array();
            if($flag_arr['cnt'] == 0){
                $numOfWeek = "6";
            }else if($numOfWeek < 5){
                $numOfWeek = "1";
            }
        }else{
            if($numOfWeek < 5){
                $numOfWeek = "1";
            }
        }
        
        $query = "SELECT ppdpSaleDay".$numOfWeek.", ppdpDay".$numOfWeek."
                FROM pensionPrice
                WHERE pprIdx = '".$pprIdx."' AND '".$date."' BETWEEN ppdpStart AND ppdpEnd
                AND ppdpSaleDay".$numOfWeek." > 0
                ORDER BY ppdpSaleDay".$numOfWeek." ASC";
        $priceArray = $this->db->query($query)->row_array();
        
        $price = $priceArray['ppdpSaleDay'.$numOfWeek];
        
        return $price;
    }

    public function roomData2String( $result )
    {
        switch ( $result['pprShape'] )
        {
            case 'S' :
                $result['pprShape2Str'] = '원룸'; 
            break;
                
            case 'M' :
            default : 
                $result['pprShape2Str'] = '거실+방';
            break;  
            
        }
        if($result['pprFloorS'] == "1"){
            $result['pprFloor2Str'] = '독채형';
        }else if($result['pprFloorM'] == "1"){
            $result['pprFloor2Str'] = '복층형';
        }else{
            $result['pprFloor2Str'] = '복층형';
        }
        
        $result['pprCnt2Str'] = array();
        if ( isset($result['pprBed']) && $result['pprBed'] > 0 )
        {
            $result['pprCnt2Str'][] = '침대룸' . $result['pprBed'];
        }
        if ( isset($result['pprOndol']) && $result['pprOndol'] > 0 )
        {
            $result['pprCnt2Str'][] = '온돌룸' . $result['pprOndol'];
        }
        if ( isset($result['pprToilet']) && $result['pprToilet'] > 0 )
        {
            $result['pprCnt2Str'][] = '화장실' . $result['pprToilet'];
        }
        
        if ( count($result['pprCnt2Str']) > 0 )
        {
            $result['pprCnt2Str'] = '(' . implode( '+', $result['pprCnt2Str'] ) . ')';
        }
        else
        {
            $result['pprCnt2Str'] = '';
        }
        
        // 평수를 제곱미터로 변환
        $result['pprSize2m2'] = round($result['pprSize'] / 0.3025 * 10) / 10;
        
        $this->load->config('_pension');
        $useful = $this->config->item('pprUseful');
        
        $result['pprUseful'] = explode(',', $result['pprUseful']);
        
        
        // 구비시설
        $pprUseful = array();
        foreach ( $result['pprUseful'] as $key => $pprUsefulIdx ) 
        {
            if ( isset($useful[$pprUsefulIdx]) ) 
            {
                $pprUseful[] = $useful[$pprUsefulIdx];
            }
        }
        
        // 구비시설 직적입력
        $pprUseful = array_merge( $pprUseful, explode( ',', $result['pprUsefulText'] ) );
        
        $result['pprUseful'] = implode( ', ', $pprUseful );
        
        $result['pprpFileUrl'] = 'http://img.yapen.co.kr/pension/room/'.$result['mpIdx'].'/800x0/'.$result['pprpFileName'];
        
        return $result;
    }

    function getEtcInfo($mpIdx){
        $this->db->where('mpIdx', $mpIdx);
        $this->db->where('ppeOpen > ', '0');
        $this->db->select('ppeIdx, ppeNo, ppeName');
        $this->db->order_by('ppeIdx asc');
        
        $result = $this->db->get('pensionDB.placePensionEtc')->result_array();
        
        return $result;
    }
    
    function pensionEtcImageLists($idx, $limit, $offset){
        $this->db->start_cache();
        $this->db->where('ppeIdx', $idx);
        $this->db->where('ppepOpen > ', 0);
        $this->db->stop_cache();

        $result['count'] = $this->db->count_all_results('pensionDB.placePensionEtcPhoto');

        $this->db->select('mpIdx, ppepFileName');       
        $this->db->order_by('ppepNo', 'asc');
        $result['query'] = $this->db->get('pensionDB.placePensionEtcPhoto', $offset, $limit)->result_array();

        $this->db->flush_cache();
        return $result;
    }
    
    function getPensionThemeName($mpIdx){
        $schQuery = "SELECT GROUP_CONCAT(MT.mtName) AS codeName
                    FROM mergePlaceSite AS MPS
                    LEFT JOIN placeTheme AS PT ON MPS.mpsIdx = PT.mpsIdx
                    LEFT JOIN mergeTheme MT ON PT.mtCode = MT.mtCode AND MT.mtSite LIKE '%YPS%' AND MT.mtType LIKE '%PS%'
                    WHERE MPS.mmType = 'YPS'
                    AND MPS.mpType = 'PS'
                    AND MPS.mpIdx = '".$mpIdx."'";
                    
        $result = $this->db->query($schQuery)->row_array();
        $codeName = $result['codeName'];
        
        $reNameReplace = str_replace(",",", ",$codeName);
        
        return $reNameReplace;
    }
    
    function pensionAllPhotoLists($mpIdx){
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
        $result = $this->db->query($schQuery)->result_array();
        
        return $result;
    }
    
    function getPensionRoom($pprIdx){
        $this->db->where('pprIdx', $pprIdx);
        $result = $this->db->get('placePensionRoom')->row_array();
        
        return $result;
    }
    
    function getPensionRoomLists($mpIdx){
        $date = date('Y-m-d');
            
        $dateObj = new DateTime($date);
        $dayNum = $dateObj->format('N');
        
        if($dayNum < 5){
            $dayNum = "1";
        }
        
        $schQuery = "   SELECT PPR.pprIdx, PPR.pprName, PPR.pprSize, PPR.pprInMin, PPR.pprInMax, PPR.pprShape, PPR.pprFloorM, PPR.pprFloorS, PPB.ppbReserve, 
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
                        END AS price,
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
                        END AS basicPrice
                        FROM placePensionRoom AS PPR
                        LEFT JOIN pensionPrice AS PPDP ON PPDP.pprIdx = PPR.pprIdx AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                        LEFT JOIN pensionException AS PE ON PE.mpIdx = PPR.mpIdx AND PE.peSetDate = '".$date."' AND PE.peUseFlag = 'Y'
                        LEFT JOIN placePensionBasic AS PPB ON PPB.mpIdx = PPR.mpIdx
                        WHERE PPR.mpIdx = '".$mpIdx."' AND PPR.pprOpen > 0
                        GROUP BY PPR.pprIdx
                        ORDER BY PPR.pprNo DESC";
        
        $result = $this->SV102->query($schQuery)->result_array();
    
        return $result;
    }

}
?>