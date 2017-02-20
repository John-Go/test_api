<?php
class Reservation_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }
    
    // ********************************* 블럭 날짜 배열 *********************************************
    public function blockPentionList($date){
        $this->db->select('pprIdx');
        $this->db->where_in('ppbDate', $date);
        $this->db->group_by("pprIdx");

        $result = $this->db->get('pensionDB.placePensionBlock')->result_array();
        
        $arrayResult = array();
        foreach ($result as $row)
           $arrayResult[] = $row['pprIdx'];
        
        return $arrayResult;
    }
    // ********************************* 빈방 검색 *********************************************

    private function dateRoomPriceCheck($date){ // pppType 체크
    /*
        return "                    
                (if(
                    (
                        select PD.ppdType 
                        from pensionDB.placePensionDate PD, pensionDB.placePensionDateTime PDT 
                        where PS.mpIdx=PD.mpIdx and PD.ppdIdx=PDT.ppdIdx and PD.ppdOpen > '0'
                        and ppdtStart <= '".$date."' and ppdtEnd >= '".$date."'
                        order by PD.ppdNo desc limit 0,1
                    ) is not null
                , 
                    (
                        select PD.ppdType 
                        from pensionDB.placePensionDate PD, pensionDB.placePensionDateTime PDT 
                        where PS.mpIdx=PD.mpIdx and PD.ppdIdx=PDT.ppdIdx and PD.ppdOpen > '0'
                        and ppdtStart <= '".$date."' and ppdtEnd >= '".$date."'
                        order by PD.ppdNo desc limit 0,1
                    )                                           
                , 'DS'))
            ";       
     
     */
     return "                   
                (SELECT PD.ppdType FROM placePensionDate PD
                 LEFT JOIN placePensionDateTime PDT ON PD.mpIdx = PDT.mpIdx AND ppdtStart <= '".$date."' AND ppdtEnd >= '".$date."'
                 WHERE PS.mpIdx=PD.mpIdx
                 AND PD.ppdIdx=PDT.ppdIdx
                 AND PD.ppdOpen > '0'
                 ORDER BY PD.ppdNo DESC
                 LIMIT 0, 1)
            ";   
    }

    private function dateWeekCheck($date){ // pppType 체크
        switch(date("w", strtotime($date))){
            case 0: return "7";
            case 1: return "1";
            case 2: return "2";
            case 3: return "3";
            case 4: return "4";
            case 5: return "5";
            case 6: return "6";
        }
    }

    private function findPensionRoomPriceQuery($date, $num, $priceMin, $priceMax, $arrayHolidayDate){

        $field = 'pppDay'.$this->dateWeekCheck($date);

        $str = " ( ";

        $str .= " 
        select 
            (
            ";


        if(isset($arrayHolidayDate[$date])){    // 공휴일 날짜가 있을경우
            $str .= " 
                PP.pppDay6
                 ";

            $str .= "           
                    -
                    if(         
                        (   
                        select if(PPS.ppsType='CS', PPS.ppsSalePrice , PP.pppDay6 * (PPS.ppsSalePrice * 0.01) ) as salePrice
                        from placePensionSale PPS 
                        where PPS.pprIdx != REPLACE(pprIdx,ROOM.pprIdx,'') AND PPS.pprIdx != '' and PPS.ppsDay".$this->dateWeekCheck($date)." > 0 and PPS.ppsStartDate <= '".$date."' and PPS.ppsEndDate >= '".$date."'
                        having salePrice > 0 limit 1
                        ) is not null,
                        (   
                        select if(PPS.ppsType='CS', PPS.ppsSalePrice , PP.pppDay6 * (PPS.ppsSalePrice * 0.01) ) as salePrice
                        from placePensionSale PPS 
                        where PPS.pprIdx != REPLACE(pprIdx,ROOM.pprIdx,'') AND PPS.pprIdx != '' and PPS.ppsDay".$this->dateWeekCheck($date)." > 0 and PPS.ppsStartDate <= '".$date."' and PPS.ppsEndDate >= '".$date."' limit 1
                        ),
                        0
                    )
                )
                as price
            ";
        }else{
            $str .= " 
                PP.".$field." ";

            $str .= "           
                    -
                    if(         
                        (   
                        select if(PPS.ppsType='CS', PPS.ppsSalePrice , PP.".$field." * (PPS.ppsSalePrice * 0.01) ) as salePrice
                        from placePensionSale PPS 
                        where PPS.pprIdx != REPLACE(pprIdx,ROOM.pprIdx,'') AND PPS.pprIdx != '' and PPS.ppsDay".$this->dateWeekCheck($date)." > 0 and PPS.ppsStartDate <= '".$date."' and PPS.ppsEndDate >= '".$date."'
                        having salePrice > 0 limit 1
                        ) is not null,
                        (   
                        select if(PPS.ppsType='CS', PPS.ppsSalePrice , PP.".$field." * (PPS.ppsSalePrice * 0.01) ) as salePrice
                        from placePensionSale PPS 
                        where PPS.pprIdx != REPLACE(pprIdx,ROOM.pprIdx,'') AND PPS.pprIdx != '' and PPS.ppsDay".$this->dateWeekCheck($date)." > 0 and PPS.ppsStartDate <= '".$date."' and PPS.ppsEndDate >= '".$date."' limit 1
                        ),
                        0
                    )
                )
                as price    
            ";
        
        }

        $str .= " from pensionDB.placePensionPrice PP where ROOM.pprIdx = PP.pprIdx ";
        
        $str .= " and PP.pppType= ";
        $str .= $this->dateRoomPriceCheck($date);

        $str .= " having price > 0 ";

        if(trim($priceMin) != '' )
            $str .= " and price >= '".$priceMin."' ";
        
        if(trim($priceMax) != '' )
            $str .= " and  price <= '".$priceMax."' ";

        $str .= "
            LIMIT 1)    
            as price_".$num." 
            ";
        
        return $str;
    } 
    
    private function findPensionRoomPriceQueryOrderBy($date, $num, $priceMin, $priceMax, $arrayHolidayDate, $cnt, $tot, $arrRoomKey){
        
        $schQuery = "0";
        
            
        $dateObj = new DateTime($date);
        $numOfWeek = $dateObj->format('N');
        
        $holyQuery = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday WHERE hDate-INTERVAL 1 DAY = '".$date."'";
        $holyRow = $this->db->query($holyQuery)->row_array();
        
        
        if(isset($holyRow['hIdx'])){                
            $numOfWeek = "6";
        }else{
            if($numOfWeek < 5){
                $numOfWeek = "1";
            }
        }
        
        $schQuery .= "+(SELECT MIN(ppdpSaleDay".$numOfWeek.") AS price FROM pensionPrice AS PPDP WHERE ppdpSaleDay".$numOfWeek." > 0 AND PPDP.mpIdx = PS.mpIdx AND '".$date."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd AND PPDP.pprIdx NOT IN ('".$arrRoomKey."') )";
        
        if(($cnt+1) == $tot){
            $schQuery .= " AS price";
        }
        
        return $schQuery;
        
        
        
        
        $field = $this->dateWeekCheck($date);

        $str = "0";

        if(isset($arrayHolidayDate[$date])){    // 공휴일 날짜가 있을경우
            $WeekNum = "6";
        }else{
            if($field < 5){
                $WeekNum = "1";
            }else{
                $WeekNum = $field;
            }        
        }
        
        $str .= "+ (
                        SELECT CASE ppra.ppraDay".$WeekNum." WHEN 0 THEN ppp.pppDay".$WeekNum." ELSE ppra.ppraDay".$WeekNum." END AS price
                        FROM placePensionPrice AS ppp
                        LEFT JOIN placePensionRoomAuto AS ppra ON ppp.mpIdx = ppra.mpIdx AND ppp.pppType = ppra.ppraType AND ppp.pprIdx = ppra.pprIdx
                        LEFT JOIN placePensionDateTime AS ppdt ON ppp.mpIdx = ppdt.mpIdx
                        WHERE ppp.mpIdx = PS.mpIdx
                        AND ppp.pppDay1 > '0'
                        AND ppp.pppDay5 > '0'
                        AND ppp.pppDay6 > '0'
                        AND ppdt.ppdtStart <= '".$date."'
                        AND ppdt.ppdtEnd >= '".$date."'
                        AND ppra.ppraStart <= '".$date."'
                        AND ppra.ppraEnd >= '".$date."'       
                     ";
        if (count($arrRoomKey) > 2) {
            $arrRoomKey = implode(",",$arrRoomKey);
            $arrRoomKey = str_replace(",","','",$arrRoomKey);
            $str .= " AND ppra.pprIdx NOT IN ('".$arrRoomKey."')";
        }
        $str .= "ORDER BY price ASC LIMIT 1 )";
        if(($cnt+1) == $tot){
            $str .= " AS price";
        }
        return $str;
        
    } 

    public function findReservationPensionList(
         $arrRoomKey
        ,$personNum
        ,$priceMin
        ,$priceMax
        ,$arrayDate
        ,$searchLoc
        ,$searchTheme
        ,$arrayHolidayDate
        ,$searchOrderby
        ,$offset
        ,$limit
        ,$idxStrings
    ) {
        
        // **************************************************** 카운트 쿼리 **************************************************************
        $this->db->where('PS.mpsOpen >', '0');
        $this->db->where('PS.mmType', 'YPS');   // 타입
        $this->db->where('PS.mpType', 'PS');    // 타입
        $this->db->where('ROOM.pprInMax >=', $personNum);  // 최대인원 이상, = 추가, 20140610 pyh
        $this->db->where('PB.ppbReserve', 'R');

        if($searchLoc || $searchTheme){ // 테마쿼리 추가

            $arrTheme = array();

            if($searchLoc){
                $this->db->join('pensionDB.placeTheme MT', "PS.mpsIdx = MT.mpsIdx");
                $this->db->where('MT.mtCode', $searchLoc);
            }                

            if($searchTheme != ""){
                //$arrTheme = array_merge($arrTheme, explode(',',$searchTheme));
                
                $searchThemeCode = array();
                if(substr($searchTheme,0,1) != "1"){
                    $searchThemeCode  = explode(",",$searchTheme);                    
                    for($i=0; $i< count($searchThemeCode); $i++){
                        $this->db->where('PPTP.PS'.str_replace(".","",$searchThemeCode[$i]).' > ', 0);
                    }
                }
                $this->db->join('pensionDB.placePensionThemeFlag AS PPTP','PPTP.mpIdx = PS.mpIdx','LEFT');
                 
            }
        }

        if(sizeof($arrRoomKey))
            $this->db->where_not_in('ROOM.pprIdx', $arrRoomKey);

        $this->db->join('pensionDB.placePensionRoom ROOM', "PS.mpIdx = ROOM.mpIdx and ROOM.pprOpen > '0'");
        $this->db->join('pensionDB.placePensionBasic PB', "PS.mpIdx = PB.mpIdx ");

        for($i=0; $i<sizeof($arrayDate); $i++)
            $this->db->select($this->findPensionRoomPriceQuery($arrayDate[$i], $i, $priceMin, $priceMax, $arrayHolidayDate), FALSE);

        for($i=0; $i<sizeof($arrayDate); $i++)
            $this->db->having("price_".$i." > 0"); 

        $this->db->select('PS.mpIdx');
        $this->db->group_by('PS.mpIdx');
        $result['count'] = $this->db->get('pensionDB.mergePlaceSite PS')->num_rows();

        // **************************************************** 카운트 쿼리 **************************************************************



        // **************************************************** 펜션리스트 쿼리 **************************************************************
        $this->db->where('PS.mpsOpen >', '0');
        $this->db->where('PS.mmType', 'YPS');   // 타입
        $this->db->where('PS.mpType', 'PS');    // 타입
        $this->db->where('ROOM.pprInMax >=', $personNum);  // 최대인원 이상, = 추가, 20140610 pyh
        $this->db->where('PB.ppbReserve', 'R');
        if($searchLoc || $searchTheme){ // 테마쿼리 추가

            $arrTheme = array();

            if($searchLoc){
                $this->db->join('pensionDB.placeTheme MT', "PS.mpsIdx = MT.mpsIdx");
                $this->db->where('MT.mtCode', $searchLoc);
            }                

            if($searchTheme != ""){
                //$arrTheme = array_merge($arrTheme, explode(',',$searchTheme));
                
                $searchThemeCode = array();
                if(substr($searchTheme,0,1) != "1"){
                    $searchThemeCode  = explode(",",$searchTheme);                    
                    for($i=0; $i< count($searchThemeCode); $i++){
                        $this->db->where('PPTP.PS'.str_replace(".","",$searchThemeCode[$i]).' > ', 0);
                    }
                }
                $this->db->join('pensionDB.placePensionThemeFlag AS PPTP','PPTP.mpIdx = PS.mpIdx','LEFT');
                 
            }
        }

        $this->db->join('pensionDB.placePensionRoom ROOM', "PS.mpIdx = ROOM.mpIdx and ROOM.pprOpen > '0'");
        $this->db->join('pensionDB.placePensionBasic PB', "PS.mpIdx = PB.mpIdx ");
    
        for($i=0; $i<sizeof($arrayDate); $i++)
            $this->db->select($this->findPensionRoomPriceQuery($arrayDate[$i], $i, $priceMin, $priceMax, $arrayHolidayDate), FALSE);

        for($i=0; $i<sizeof($arrayDate); $i++)
            $this->db->having("price_".$i." > 0"); 

        $this->db->select('PS.mpsIdx,PS.mpIdx,PS.mpsAddr1,PS.mpsName');
        $this->db->select('PB.ppbImage,PB.ppbRoomMin, PB.ppbReserve, PB.ppbGrade');
        
        // 201406100935 pyh : 무조건 카운트는 1이다. 값을 체크
        // if(count($idxStrings) > 0 ){
        if (count($idxStrings) > 2) {
            $this->db->where_not_in('PS.mpIdx', $idxStrings);
        }
        if(sizeof($arrRoomKey)){
            $this->db->where_not_in('ROOM.pprIdx', $arrRoomKey);
        }
        if($searchOrderby == "" || !$searchOrderby){
            $searchOrderby = "1";
        }
        if($searchOrderby == "1"){
            $this->db->order_by('PB.ppbGrade DESC, Rand()');
        }else if($searchOrderby == "2"){
            $this->db->order_by('PB.ppbWantCnt DESC, Rand()');
        }else if($searchOrderby == "4"){
            $priceOrderby = "";
            for($i=0; $i<sizeof($arrayDate); $i++){
                if($i==0){
                    $priceOrderby .= 'price_'.$i;
                }else{
                    $priceOrderby .= '+price_'.$i;
                }
            }   
            $this->db->order_by('('.$priceOrderby.') ASC');
            $this->db->order_by('Rand()');
        }else if($searchOrderby == "5"){
            $priceOrderby = "";
            for($i=0; $i<sizeof($arrayDate); $i++){
                if($i==0){
                    $priceOrderby .= 'price_'.$i;
                }else{
                    $priceOrderby .= '+price_'.$i;
                }
            }   
            $this->db->order_by('('.$priceOrderby.') DESC');
            $this->db->order_by('Rand()');
        }
        
        $this->db->group_by('PS.mpIdx');
        
        $result['query'] = $this->db->get('pensionDB.mergePlaceSite PS', $limit)->result_array();
        //echo "<pre>".$this->db->last_query()."</pre>";
        // **************************************************** 펜션리스트 쿼리 **************************************************************

        return $result;
    }

    public function findReservationPensionListWeb(
         $arrRoomKey
        ,$personNum
        ,$priceMin
        ,$priceMax
        ,$arrayDate
        ,$searchLoc
        ,$searchTheme
        ,$arrayHolidayDate
        ,$searchOrderby
        ,$offset
        ,$limit
        ,$idxStrings
    ) {
        
        // **************************************************** 카운트 쿼리 **************************************************************
        $this->db->where('PS.mpsOpen >', '0');
        $this->db->where('PS.mmType', 'YPS');   // 타입
        $this->db->where('PS.mpType', 'PS');    // 타입
        $this->db->where('ROOM.pprInMax >=', $personNum);  // 최대인원 이상, = 추가, 20140610 pyh
        $this->db->where('PB.ppbReserve', 'R');

        if($searchLoc || $searchTheme){ // 테마쿼리 추가

            $arrTheme = array();

            if($searchLoc){
                $this->db->join('pensionDB.placeTheme MT', "PS.mpsIdx = MT.mpsIdx");
                $this->db->where('MT.mtCode', $searchLoc);
            }                

            if($searchTheme != ""){
                //$arrTheme = array_merge($arrTheme, explode(',',$searchTheme));
                
                $searchThemeCode = array();
                if(substr($searchTheme,0,1) != "1"){
                    $searchThemeCode  = explode(",",$searchTheme);                    
                    for($i=0; $i< count($searchThemeCode); $i++){
                        $this->db->where('PPTP.PS'.str_replace(".","",$searchThemeCode[$i]).' > ', 0);
                    }
                }
                $this->db->join('pensionDB.placePensionThemeFlag AS PPTP','PPTP.mpIdx = PS.mpIdx','LEFT');
                 
            }
        }

        if(sizeof($arrRoomKey))
            $this->db->where_not_in('ROOM.pprIdx', $arrRoomKey);

        $this->db->join('pensionDB.placePensionRoom ROOM', "PS.mpIdx = ROOM.mpIdx and ROOM.pprOpen > '0'");
        $this->db->join('pensionDB.placePensionBasic PB', "PS.mpIdx = PB.mpIdx ");

        for($i=0; $i<sizeof($arrayDate); $i++)
            $this->db->select($this->findPensionRoomPriceQueryTest($arrayDate[$i], $i, $priceMin, $priceMax, $arrayHolidayDate, $i, count($arrayDate), $arrRoomKey), FALSE);

        

        $this->db->select('PS.mpIdx');
        $this->db->group_by('PS.mpIdx');
        $result['count'] = $this->db->get('pensionDB.mergePlaceSite PS')->num_rows();

        // **************************************************** 카운트 쿼리 **************************************************************



        // **************************************************** 펜션리스트 쿼리 **************************************************************
        $this->db->where('PS.mpsOpen >', '0');
        $this->db->where('PS.mmType', 'YPS');   // 타입
        $this->db->where('PS.mpType', 'PS');    // 타입
        $this->db->where('ROOM.pprInMax >=', $personNum);  // 최대인원 이상, = 추가, 20140610 pyh
        $this->db->where('PB.ppbReserve', 'R');
        if($searchLoc || $searchTheme){ // 테마쿼리 추가

            $arrTheme = array();

            if($searchLoc){
                $this->db->join('pensionDB.placeTheme MT', "PS.mpsIdx = MT.mpsIdx");
                $this->db->where('MT.mtCode', $searchLoc);
            }                

            if($searchTheme != ""){
                //$arrTheme = array_merge($arrTheme, explode(',',$searchTheme));
                
                $searchThemeCode = array();
                if(substr($searchTheme,0,1) != "1"){
                    $searchThemeCode  = explode(",",$searchTheme);                    
                    for($i=0; $i< count($searchThemeCode); $i++){
                        $this->db->where('PPTP.PS'.str_replace(".","",$searchThemeCode[$i]).' > ', 0);
                    }
                }
                $this->db->join('pensionDB.placePensionThemeFlag AS PPTP','PPTP.mpIdx = PS.mpIdx','LEFT');
                 
            }
        }

        $this->db->join('pensionDB.placePensionRoom ROOM', "PS.mpIdx = ROOM.mpIdx and ROOM.pprOpen > '0'");
        $this->db->join('pensionDB.placePensionBasic PB', "PS.mpIdx = PB.mpIdx ");
    
        for($i=0; $i<sizeof($arrayDate); $i++)
            $this->db->select($this->findPensionRoomPriceQueryTest($arrayDate[$i], $i, $priceMin, $priceMax, $arrayHolidayDate, $i, count($arrayDate), $arrRoomKey), FALSE);


        $this->db->select('PS.mpsIdx,PS.mpIdx,PS.mpsAddr1,PS.mpsName');
        $this->db->select('PB.ppbImage,PB.ppbRoomMin, PB.ppbReserve, PB.ppbGrade');
        
        // 201406100935 pyh : 무조건 카운트는 1이다. 값을 체크
        // if(count($idxStrings) > 0 ){
        if (count($idxStrings) > 2) {
            $this->db->where_not_in('PS.mpIdx', $idxStrings);
        }
        if(sizeof($arrRoomKey)){
            $this->db->where_not_in('ROOM.pprIdx', $arrRoomKey);
        }
        if($searchOrderby == "" || !$searchOrderby){
            $searchOrderby = "1";
        }
        if($searchOrderby == "1"){
            $this->db->order_by('PB.ppbGrade DESC, Rand()');
        }else if($searchOrderby == "2"){
            $this->db->order_by('PB.ppbWantCnt DESC, Rand()');
        }else if($searchOrderby == "4"){
            $this->db->order_by('price', 'ASC');
            $this->db->order_by('Rand()');
        }else if($searchOrderby == "5"){
            $this->db->order_by('price', 'DESC');
            $this->db->order_by('Rand()');
        }
        
        $this->db->group_by('PS.mpIdx');
        
        $result['query'] = $this->db->get('pensionDB.mergePlaceSite PS', $limit)->result_array();
        //echo "<pre>".$this->db->last_query()."</pre>";
        // **************************************************** 펜션리스트 쿼리 **************************************************************

        return $result;
    }

    public function findReservationPensionListOrderby(
         $arrRoomKey
        ,$personNum
        ,$priceMin
        ,$priceMax
        ,$arrayDate
        ,$searchLoc
        ,$searchTheme
        ,$arrayHolidayDate
        ,$searchOrderby
        ,$offset
        ,$limit
        ,$idxStrings
    ) {
        
        // **************************************************** 카운트 쿼리 **************************************************************
        $this->db->where('PS.mpsOpen >', '0');
        $this->db->where('PS.mmType', 'YPS');   // 타입
        $this->db->where('PS.mpType', 'PS');    // 타입
        $this->db->where('ROOM.pprInMax >=', $personNum);  // 최대인원 이상, = 추가, 20140610 pyh
        $this->db->where('PB.ppbReserve', 'R');

        if($searchLoc || $searchTheme){ // 테마쿼리 추가

            $arrTheme = array();

            if($searchLoc){
                $this->db->join('pensionDB.placeTheme MT', "PS.mpsIdx = MT.mpsIdx");
                $this->db->where('MT.mtCode', $searchLoc);
            }                

            if($searchTheme != ""){
                //$arrTheme = array_merge($arrTheme, explode(',',$searchTheme));
                
                $searchThemeCode = array();
                if(substr($searchTheme,0,1) != "1"){
                    $searchThemeCode  = explode(",",$searchTheme);                    
                    for($i=0; $i< count($searchThemeCode); $i++){
                        $this->db->where('PPTP.PS'.str_replace(".","",$searchThemeCode[$i]).' > ', 0);
                    }
                }
                $this->db->join('pensionDB.placePensionThemeFlag AS PPTP','PPTP.mpIdx = PS.mpIdx','LEFT');
                 
            }
        }

        if(sizeof($arrRoomKey))
            $this->db->where_not_in('ROOM.pprIdx', $arrRoomKey);

        $this->db->join('pensionDB.placePensionRoom ROOM', "PS.mpIdx = ROOM.mpIdx and ROOM.pprOpen > '0'");
        $this->db->join('pensionDB.placePensionBasic PB', "PS.mpIdx = PB.mpIdx ");
        if($arrayDate[0] >= date('Y').'-07-01' && $arrayDate[0] <= date('Y').'-08-31'){
            $this->db->where('PB.ppbDateCheck','1');
        }
         
        for($i=0; $i<sizeof($arrayDate); $i++)
            $this->db->select($this->findPensionRoomPriceQueryOrderby($arrayDate[$i], $i, $priceMin, $priceMax, $arrayHolidayDate, $i, count($arrayDate), $arrRoomKey), FALSE);

        
        if(trim($priceMin) != ""){
            $priceMin = str_replace(",","",$priceMin);
            $this->db->having('price >= '.$priceMin);
        }
         
         
        if(trim($priceMax) != ""){
            $priceMax = str_replace(",","",$priceMax);
            $this->db->having('price <= '.$priceMax);
        }
        $this->db->select('PS.mpIdx');
        $this->db->group_by('PS.mpIdx');
        
        
        
        $result['count'] = $this->db->get('pensionDB.mergePlaceSite PS')->num_rows();

        // **************************************************** 카운트 쿼리 **************************************************************



        // **************************************************** 펜션리스트 쿼리 **************************************************************
        $this->db->where('PS.mpsOpen >', '0');
        $this->db->where('PS.mmType', 'YPS');   // 타입
        $this->db->where('PS.mpType', 'PS');    // 타입
        $this->db->where('ROOM.pprInMax >=', $personNum);  // 최대인원 이상, = 추가, 20140610 pyh
        $this->db->where('PB.ppbReserve', 'R');
        if($searchLoc || $searchTheme){ // 테마쿼리 추가

            $arrTheme = array();

            if($searchLoc){
                $this->db->join('pensionDB.placeTheme MT', "PS.mpsIdx = MT.mpsIdx");
                $this->db->where('MT.mtCode', $searchLoc);
            }                

            if($searchTheme != ""){
                //$arrTheme = array_merge($arrTheme, explode(',',$searchTheme));
                
                $searchThemeCode = array();
                if(substr($searchTheme,0,1) != "1"){
                    $searchThemeCode  = explode(",",$searchTheme);                    
                    for($i=0; $i< count($searchThemeCode); $i++){
                        $this->db->where('PPTP.PS'.str_replace(".","",$searchThemeCode[$i]).' > ', 0);
                    }
                }
                $this->db->join('pensionDB.placePensionThemeFlag AS PPTP','PPTP.mpIdx = PS.mpIdx','LEFT');
                 
            }
        }

        $this->db->join('pensionDB.placePensionRoom ROOM', "PS.mpIdx = ROOM.mpIdx and ROOM.pprOpen > '0'");
        $this->db->join('pensionDB.placePensionBasic PB', "PS.mpIdx = PB.mpIdx ");
        
        if($arrayDate[0] >= date('Y').'-07-01' && $arrayDate[0] <= date('Y').'-08-31'){
            $this->db->where('PB.ppbDateCheck','1');
        }
        
        for($i=0; $i<sizeof($arrayDate); $i++)
            $this->db->select($this->findPensionRoomPriceQueryOrderby($arrayDate[$i], $i, $priceMin, $priceMax, $arrayHolidayDate, $i, count($arrayDate), $arrRoomKey), FALSE);


        $this->db->select('PS.mpsIdx,PS.mpIdx,PS.mpsAddr1,PS.mpsName');
        $this->db->select('PB.ppbImage,PB.ppbRoomMin, PB.ppbReserve, PB.ppbGrade');
        
        // 201406100935 pyh : 무조건 카운트는 1이다. 값을 체크
        // if(count($idxStrings) > 0 ){
        if (count($idxStrings) > 2) {
            $this->db->where_not_in('PS.mpIdx', $idxStrings);
        }
        if(sizeof($arrRoomKey)){
            $this->db->where_not_in('ROOM.pprIdx', $arrRoomKey);
        }
        
        if(trim($priceMin) != ""){
            $priceMin = str_replace(",","",$priceMin);
            $this->db->having('price >= '.$priceMin);
        }
         
         
        if(trim($priceMax) != ""){
            $priceMax = str_replace(",","",$priceMax);
            $this->db->having('price <= '.$priceMax);
        }
        
        if($searchOrderby == "" || !$searchOrderby){
            $searchOrderby = "1";
        }
        if($searchOrderby == "1"){
            $this->db->order_by('PB.ppbGrade DESC, Rand()');
        }else if($searchOrderby == "2"){
            $this->db->order_by('PB.ppbWantCnt DESC, Rand()');
        }else if($searchOrderby == "4"){
            $this->db->order_by('price', 'ASC');
            $this->db->order_by('Rand()');
        }else if($searchOrderby == "5"){
            $this->db->order_by('price', 'DESC');
            $this->db->order_by('Rand()');
        }
        
        $this->db->group_by('PS.mpIdx');
        
        $result['query'] = $this->db->get('pensionDB.mergePlaceSite PS', $limit)->result_array();
       
        
        // **************************************************** 펜션리스트 쿼리 **************************************************************

        return $result;
    }

    public function findReservationPensionListTest(
         $arrRoomKey
        ,$personNum
        ,$priceMin
        ,$priceMax
        ,$arrayDate
        ,$searchLoc
        ,$searchTheme
        ,$arrayHolidayDate
        ,$offset
        ,$limit
        ,$idxStrings
    ){
        $this->db->start_cache();
        $this->db->select(array('mps.mpsIdx','mps.mpIdx','mps.mpsAddr1','mps.mpsName','ppb.ppbRoomMin','ppb.ppbReserve','ppb.ppbGrade','ppb.ppbImage'));
        $this->db->join('placeTheme AS mt','mps.mpsIdx = mt.mpsIdx','left');
        $this->db->join('placePensionBasic AS ppb','mps.mpIdx = ppb.mpIdx');
        $this->db->join('placePensionRoom AS ppr','mps.mpIdx = ppr.mpidx AND ppr.pprOpen = \'1\'');
        $this->db->where('mps.mpType','PS');
        $this->db->where('mps.mmType','YPS');
        $this->db->where('ppb.ppbReserve','R');
        $this->db->where('mps.mpsOpen','1');
        $this->db->where('ppr.pprInMin >=', $personNum);
        $this->db->where('mt.mtCode',$searchLoc);
        if (count($idxStrings) > 2) {
            $this->db->where_not_in('mps.mpIdx', $idxStrings);
        }
        if(sizeof($arrRoomKey)){
            $this->db->where_not_in('ppr.pprIdx', $arrRoomKey);
        }
        $this->db->group_by('mps.mpIdx');
        $this->db->order_by('ppb.ppbGrade','DESC');
        $this->db->order_by('rand()');
        $result['count'] = $this->db->get('mergePlaceSite mps')->num_rows();
        $this->db->stop_cache();
        $result['query'] = $this->db->get('mergePlaceSite mps', $limit)->result_array();
        $this->db->flush_cache();
        
        for($i=0; $i< count($result['query']); $i++){
            $price_0 = 0;
            for($j=0; $j< count($arrayDate); $j++){
                $dateObj = new DateTime($arrayDate[$j]);
                $date = $dateObj->format('Y-m-d');
                $dayOfWeek = $dateObj->format('l');
                $numOfWeek = $dateObj->format('N'); // 오늘의 요일 숫자 - 월은1 부터 일은7
                if($numOfWeek < 5 || $numOfWeek == 7){
                    $numOfWeek = 1;
                }
                
                $price_sql = "SELECT MIN(A.price) AS price FROM (
                                SELECT 
                                    (CASE WHEN PPRA.ppraDay".$numOfWeek." = 0 THEN PPP.pppDay".$numOfWeek."
                                    ELSE    PPRA.ppraDay".$numOfWeek." END) AS price 
                                FROM placePensionPrice PPP
                                LEFT JOIN placePensionDate PPD ON PPP.mpIdx = PPD.mpIdx AND PPD.ppdType = PPP.pppType
                                LEFT JOIN placePensionDateTime PPDT ON PPD.ppdIdx = PPDT.ppdIdx
                                LEFT JOIN placePensionRoomAuto PPRA ON PPP.mpIdx = PPRA.mpIdx AND PPP.pprIdx = PPRA.pprIdx AND PPD.ppdType = PPRA.ppraType
                                WHERE PPP.mpIdx = '".$result['query'][$i]['mpIdx']."'
                                AND (ppdtStart <= '".$arrayDate[$j]."' AND ppdtEnd >= '".$arrayDate[$j]."')
                                AND PPP.pppDay1 > 0
                                AND PPP.pppDay5 > 0
                                AND PPP.pppDay6 > 0)A
                              WHERE A.price IS NOT NULL
                              AND A.price BETWEEN ".$priceMin." AND ".$priceMax;
                $price_arr = $this->db->query($price_sql)->row_array();
                $price = $price_arr['price'];
                $price_0 = $price_0 + $price;
            }
            $result['query'][$i]['price_0'] = $price_0;
        }
        return $result;
    }


    // **************************************************** 객실리스트 쿼리 **************************************************************
    public function findReservationPensionRoomList($arrRoomKey, $personNum, $priceMin, $priceMax, $arrayDate, $arrayHolidayDate, $mpIdx){
        $this->db->where('ROOM.mpIdx', $mpIdx);
        $this->db->where('ROOM.pprOpen >', '0');    // 타입
        $this->db->where('ROOM.pprInMax >=', $personNum);
        $this->db->where('PS.mpsOpen >', '0');
        $this->db->where('PS.mmType', 'YPS');   // 타입
        $this->db->where('PS.mpType', 'PS');    // 타입

        $this->db->join('pensionDB.mergePlaceSite PS', "PS.mpIdx = ROOM.mpIdx ");

        if(sizeof($arrRoomKey))
            $this->db->where_not_in('ROOM.pprIdx', $arrRoomKey);

        for($i=0; $i<sizeof($arrayDate); $i++)
            $this->db->select($this->findPensionRoomPriceQuery($arrayDate[$i], $i, $priceMin, $priceMax, $arrayHolidayDate), FALSE);

        $this->db->select('ROOM.*');
        
        $this->db->order_by('ROOM.pprNo', 'desc');

        $return = $this->db->get('pensionDB.placePensionRoom ROOM')->result_array();
        return $return;
        
    }
    // **************************************************** 객실리스트 쿼리 **************************************************************


    // **************************************************** My 예약리스트 **************************************************************
    public function myReservation($mbIdx, $limit, $offset, $gName, $gBirth, $rMobile){
        
        if($gBirth){
            if(substr($gBirth,0,2) > substr(date('Y'),2,2)){
                $gBirth = "19".$gBirth;
            }else{
                $gBirth = "20".$gBirth;
            }
            $gBirth = substr($gBirth,0,4)."-".substr($gBirth,4,2)."-".substr($gBirth,6,2);
        }
        
        $this->db->start_cache();
        if($mbIdx || $mbIdx != ""){
            $this->db->where('mbIdx', $mbIdx);
        }else{
            $this->db->where('rPersonStayName like "%'.$gName.'%"');
            $this->db->where('rPersonMobile', $rMobile);
            $this->db->where('rPersonBrithday', $gBirth);
        }
        $this->db->where('rPayFlag','Y');
        $this->db->stop_cache();
        
        $result['count'] = $this->db->count_all_results('reservation');

        $this->db->select("rIdx,rPension,rPensionRoom,rRegDate,rPaymentState,rStartDate,rEndDate, mpIdx");
        $this->db->order_by("rIdx desc");
        $result['query'] = $this->db->get('reservation', $offset, $limit)->result_array();

        $this->db->flush_cache();
        return $result;
    }
    // **************************************************** My 예약리스트 **************************************************************

    public function findRoom($arrRoomKey, $idx, $arrayDate, $arrayHolidayDate){
        $this->db->where('ROOM.mpIdx', $idx);
        $this->db->where('ROOM.pprOpen >', '0');    // 타입
        $this->db->where('PS.mpsOpen >', '0');
        $this->db->where('PS.mmType', 'YPS');   // 타입
        $this->db->where('PS.mpType', 'PS');    // 타입

        $this->db->join('pensionDB.mergePlaceSite PS', "PS.mpIdx = ROOM.mpIdx ");

        if(sizeof($arrRoomKey))
            $this->db->where_not_in('ROOM.pprIdx', $arrRoomKey);

        for($i=0; $i<sizeof($arrayDate); $i++)
            $this->db->select($this->findPensionRoomPriceQuery($arrayDate[$i], $i, 0, 0, $arrayHolidayDate), FALSE);

        $this->db->select('ROOM.*');
        $this->db->order_by('ROOM.pprIdx', 'desc');

        $row = $this->db->get('pensionDB.placePensionRoom ROOM');
        $result['count'] = $row->num_rows(); 
        $result['query'] = $row->result_array();

        return $result;
    }

    //예약 정보
    function getReservationInfo( $mbIdx, $mpIdx) {
        $this->db->select('*');
        $this->db->where('mbIdx', $mbIdx);
        $this->db->where('mpIdx', $mpIdx);
        $this->db->order_by('rIdx','DESC');
        $result = $this->db->get('pensionDB.reservation')->row_array();
        return $result;
    }

    //펜션당 예약 날짜 제한
    function getPensionLimitDate($mpIdx){
        $this->db->select(array('rodLoofDays','rodSetdate'));
        $this->db->where('mpIdx',$mpIdx);
        $result = $this->db->get('pensionDB.reservationOpenDate')->row_array();

        return $result;
    }
    function uptPensionBlock($mpIdx, $pprIdx, $ppbDate){
        $this->db->where('mpIdx',$mpIdx);
        $this->db->where('pprIdx',$pprIdx);
        $this->db->where('ppbDate',$ppbDate);
        $this->db->delete('placePensionBlock');
        return;
    }

    function getRevInfo($userRev, $userName){
        
        $this->db->where('rCode', $userRev);
        $this->db->like('rPersonName', $userName);        
        $result = $this->db->get('reservation')->row_array();
        
        return $result;
    }
    
    function getKeywordPensionLists($keyword, $searchDate, $searchDateNum, $schPeople, $searchPriceMin, $searchPriceMax, $mapX, $mapY, $limit, $offset){
        $setDate = explode("-", $searchDate);
        $basicPriceQuery = "(0";
        $priceQuery = "(0";
        $percentQuery = "(0";
        $joinQuery = "";
        $dateBt = "";
        for($i=0; $i< $searchDateNum; $i++){
            $date = date('Y-m-d', mktime(0, 0, 0, $setDate[1], $setDate[2]+$i, $setDate[0]));
            $dateBt .= "','".$date;
            $dateObj = new DateTime($date);
            $numOfWeek = $dateObj->format('N');
            
            if($numOfWeek < 5){
                $numOfWeek = "1";
            }
            
            $joinQuery .= " LEFT JOIN pensionPrice AS PP".$i." ON PPR.pprIdx = PP".$i.".pprIdx AND '".$date."' BETWEEN PP".$i.".ppdpStart AND PP".$i.".ppdpEnd 
                            LEFT JOIN pensionException AS PE".$i." ON PE".$i.".mpIdx = MPS.mpIdx AND PE".$i.".peSetDate = '".$date."' AND PE".$i.".peUseFlag = 'Y' ";
            $basicPriceQuery .= "+ ( CASE WHEN PE".$i.".peIdx THEN CASE PE".$i.".peDay WHEN '1' THEN MIN(PP".$i.".ppdpDay1)  WHEN '5' THEN MIN(PP".$i.".ppdpDay5)  WHEN '6' THEN MIN(PP".$i.".ppdpDay6)  WHEN '7' THEN MIN(PP".$i.".ppdpDay7) ELSE PP".$i.".ppdpDay".$numOfWeek." END ELSE PP".$i.".ppdpDay".$numOfWeek." END )";
            $priceQuery .= "+ ( CASE WHEN PE".$i.".peIdx THEN CASE PE".$i.".peDay WHEN '1' THEN MIN(PP".$i.".ppdpSaleDay1)  WHEN '5' THEN MIN(PP".$i.".ppdpSaleDay5)  WHEN '6' THEN MIN(PP".$i.".ppdpSaleDay6)  WHEN '7' THEN MIN(PP".$i.".ppdpSaleDay7) ELSE MIN(PP".$i.".ppdpSaleDay".$numOfWeek.") END ELSE MIN(PP".$i.".ppdpSaleDay".$numOfWeek.") END )";
            $percentQuery .= "+ ( CASE WHEN PE".$i.".peIdx THEN CASE PE".$i.".peDay WHEN '1' THEN MIN(PP".$i.".ppdpPercent1)  WHEN '5' THEN MIN(PP".$i.".ppdpPercent5)  WHEN '6' THEN MIN(PP".$i.".ppdpPercent6)  WHEN '7' THEN MIN(PP".$i.".ppdpPercent7) ELSE PP".$i.".ppdpPercent".$numOfWeek." END ELSE PP".$i.".ppdpPercent".$numOfWeek." END )";
        }
        $basicPriceQuery .= ")";
        $priceQuery .= ")";
        $percentQuery .= ")";
        
        $orderbyQuery = " ORDER BY PB.ppbGrade DESC, PPU.ppuExternalFlag DESC, PB.ppbMainPension DESC, MPS.mpIdx DESC";
        
        if($dateBt != ""){
            $dateBt = substr($dateBt,3);
        }
        if($searchPriceMax == ""){
            $priceLimitQuery = " price > 0";
        }else{
            $priceLimitQuery = " price > ".$searchPriceMin." AND price <= ".$searchPriceMax;
        }
        
        $schQuery = "   SELECT MPS.mpsName, MPS.mpsIdx, MPS.mpsAddr1, MPS.mpsAddr2, MPS.mpsAddr1New, MPS.mpsAddrFlag, MPS.mpsMapX, MPS.mpsMapY, MPS.mpIdx,
                        PB.ppbImage, PB.ppbGrade, PB.ppbWantCnt, PB.ppbReserve,
                        ".$basicPriceQuery." AS basicPrice, ".$priceQuery." AS price, ".$percentQuery." AS percent,
                        (6371*ACOS(COS(RADIANS('".$mapY."'))*COS(RADIANS(MPS.mpsMapY))*COS(RADIANS(MPS.mpsMapX)-RADIANS('".$mapX."'))+SIN(RADIANS('".$mapY."'))*SIN(RADIANS(MPS.mpsMapY)))) AS distance
                        FROM mergePlaceSite AS MPS
                        LEFT JOIN placePensionUse AS PPU ON PPU.mpIdx = MPS.mpIdx
                        LEFT JOIN placePensionBasic AS PB ON PB.mpIdx = MPS.mpIdx
                        LEFT JOIN placePensionRoom AS PPR ON PPR.mpIdx = MPS.mpIdx AND PPR.pprInMax >= ".$schPeople." AND PPR.pprOpen = '1'
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
                        ".$joinQuery."
                        WHERE MPS.mpsOpen = '1'
                        AND MPS.mmType = 'YPS'
                        AND PB.ppbReserve = 'R'
                        AND MPS.mpType = 'PS'
                        AND CONCAT(MPS.mpsAddr1, MPS.mpsAddr2, MPS.mpsName, PT.theme) LIKE '%".$keyword."%'
                        AND PPR.pprIdx IS NOT NULL";
        if($themeQuery != ""){
            $schQuery .= $themeQuery;
        }
        $schQuery .= "  GROUP BY MPS.mpIdx
                        HAVING distance < 10 AND ".$priceLimitQuery."                        
                        ".$orderbyQuery."
                        LIMIT ".$limit." OFFSET ".$offset;
        $result['lists'] = $this->db->query($schQuery)->result_array();
        if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
            //echo "<pre>".$this->db->last_query()."</pre>";
        }
        return $result;
    }
    
    function getAroundPensionLists($searchLoc, $searchDate, $searchDateNum, $schPeople, $searchPriceMin, $searchPriceMax, $mapX, $mapY, $limit, $offset){
        $setDate = explode("-", $searchDate);
        $basicPriceQuery = "(0";
        $priceQuery = "(0";
        $percentQuery = "(0";
        $joinQuery = "";
        $dateBt = "";
        for($i=0; $i< $searchDateNum; $i++){
            $date = date('Y-m-d', mktime(0, 0, 0, $setDate[1], $setDate[2]+$i, $setDate[0]));
            $dateBt .= "','".$date;
            $dateObj = new DateTime($date);
            $numOfWeek = $dateObj->format('N');
            
            if($numOfWeek < 5){
                $numOfWeek = "1";
            }
            
            $joinQuery .= " LEFT JOIN pensionPrice AS PP".$i." ON PPR.pprIdx = PP".$i.".pprIdx AND '".$date."' BETWEEN PP".$i.".ppdpStart AND PP".$i.".ppdpEnd 
                            LEFT JOIN pensionException AS PE".$i." ON PE".$i.".mpIdx = MPS.mpIdx AND PE".$i.".peSetDate = '".$date."' AND PE".$i.".peUseFlag = 'Y' ";
            $basicPriceQuery .= "+ ( CASE WHEN PE".$i.".peIdx THEN CASE PE".$i.".peDay WHEN '1' THEN MIN(PP".$i.".ppdpDay1)  WHEN '5' THEN MIN(PP".$i.".ppdpDay5)  WHEN '6' THEN MIN(PP".$i.".ppdpDay6)  WHEN '7' THEN MIN(PP".$i.".ppdpDay7) ELSE PP".$i.".ppdpDay".$numOfWeek." END ELSE PP".$i.".ppdpDay".$numOfWeek." END )";
            $priceQuery .= "+ ( CASE WHEN PE".$i.".peIdx THEN CASE PE".$i.".peDay WHEN '1' THEN MIN(PP".$i.".ppdpSaleDay1)  WHEN '5' THEN MIN(PP".$i.".ppdpSaleDay5)  WHEN '6' THEN MIN(PP".$i.".ppdpSaleDay6)  WHEN '7' THEN MIN(PP".$i.".ppdpSaleDay7) ELSE MIN(PP".$i.".ppdpSaleDay".$numOfWeek.") END ELSE MIN(PP".$i.".ppdpSaleDay".$numOfWeek.") END )";
            $percentQuery .= "+ ( CASE WHEN PE".$i.".peIdx THEN CASE PE".$i.".peDay WHEN '1' THEN MIN(PP".$i.".ppdpPercent1)  WHEN '5' THEN MIN(PP".$i.".ppdpPercent5)  WHEN '6' THEN MIN(PP".$i.".ppdpPercent6)  WHEN '7' THEN MIN(PP".$i.".ppdpPercent7) ELSE PP".$i.".ppdpPercent".$numOfWeek." END ELSE PP".$i.".ppdpPercent".$numOfWeek." END )";
        }
        $basicPriceQuery .= ")";
        $priceQuery .= ")";
        $percentQuery .= ")";
        
        $orderbyQuery = " ORDER BY distance ASC";
        
        if($dateBt != ""){
            $dateBt = substr($dateBt,3);
        }
        if($searchPriceMax == ""){
            $priceLimitQuery = " price > 0";
        }else{
            $priceLimitQuery = " price > ".$searchPriceMin." AND price <= ".$searchPriceMax;
        }
        
        $schQuery = "   SELECT MPS.mpsName, MPS.mpsIdx, MPS.mpsAddr1, MPS.mpsAddr2, MPS.mpsAddr1New, MPS.mpsAddrFlag, MPS.mpsMapX, MPS.mpsMapY, MPS.mpIdx,
                        PB.ppbImage, PB.ppbGrade, PB.ppbWantCnt, PB.ppbReserve,
                        CASE WHEN ATRB.altrbIdx IS NULL THEN 0 ELSE 1 END AS topIdx,
                        ".$basicPriceQuery." AS basicPrice, ".$priceQuery." AS price, ".$percentQuery." AS percent,
                        (6371*ACOS(COS(RADIANS('".$mapY."'))*COS(RADIANS(MPS.mpsMapY))*COS(RADIANS(MPS.mpsMapX)-RADIANS('".$mapX."'))+SIN(RADIANS('".$mapY."'))*SIN(RADIANS(MPS.mpsMapY)))) AS distance
                        FROM mergePlaceSite AS MPS
                        LEFT JOIN placeTheme AS PT ON MPS.mpsIdx = PT.mpsIdx AND PT.mtCode LIKE '1%' AND LENGTH(PT.mtCode) = 8
                        LEFT JOIN placePensionUse AS PPU ON PPU.mpIdx = MPS.mpIdx
                        LEFT JOIN placePensionBasic AS PB ON PB.mpIdx = MPS.mpIdx
                        LEFT JOIN placePensionRoom AS PPR ON PPR.mpIdx = MPS.mpIdx AND PPR.pprInMax >= ".$schPeople." AND PPR.pprOpen = '1'
                        LEFT JOIN appLocTopRollingBanner AS ATRB ON '".date('Y-m-d')."' BETWEEN ATRB.altrbStartDate AND ATRB.altrbEndDate AND ATRB.altrbOpen = '1' AND ATRB.altrbLocal IN ('".implode("','",$searchLoc)."') AND ATRB.mpIdx = MPS.mpIdx
                        ".$joinQuery."
                        WHERE MPS.mpsOpen = '1'
                        AND MPS.mmType = 'YPS'
                        AND PB.ppbReserve = 'R'
                        AND MPS.mpType = 'PS'
                        AND PT.mtCode IN ('".implode("','",$searchLoc)."')
                        AND PPR.pprIdx IS NOT NULL";
        if($themeQuery != ""){
            $schQuery .= $themeQuery;
        }
        $schQuery .= "  GROUP BY MPS.mpIdx
                        HAVING distance < 10 AND ".$priceLimitQuery."                        
                        ".$orderbyQuery."
                        LIMIT ".$limit." OFFSET ".$offset;
        $result['lists'] = $this->db->query($schQuery)->result_array();
        if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
            //echo "<pre>".$this->db->last_query()."</pre>";
        }
        return $result;
    }
    
    function getPensionLists($searchLoc, $searchDate, $searchDateNum, $schPeople, $searchPriceMin, $searchPriceMax, $mapX, $mapY, $limit, $offset){
        $setDate = explode("-", $searchDate);
        $basicPriceQuery = "(0";
        $priceQuery = "(0";
        $percentQuery = "(0";
        $joinQuery = "";
        $dateBt = "";
        for($i=0; $i< $searchDateNum; $i++){
            $date = date('Y-m-d', mktime(0, 0, 0, $setDate[1], $setDate[2]+$i, $setDate[0]));
            $dateBt .= "','".$date;
            $dateObj = new DateTime($date);
            $numOfWeek = $dateObj->format('N');
            
            if($numOfWeek < 5){
                $numOfWeek = "1";
            }
            
            $joinQuery .= " LEFT JOIN pensionPrice AS PP".$i." ON PPR.pprIdx = PP".$i.".pprIdx AND '".$date."' BETWEEN PP".$i.".ppdpStart AND PP".$i.".ppdpEnd 
                            LEFT JOIN pensionException AS PE".$i." ON PE".$i.".mpIdx = MPS.mpIdx AND PE".$i.".peSetDate = '".$date."' AND PE".$i.".peUseFlag = 'Y' ";
            $basicPriceQuery .= "+ ( CASE WHEN PE".$i.".peIdx THEN CASE PE".$i.".peDay WHEN '1' THEN MIN(PP".$i.".ppdpDay1)  WHEN '5' THEN MIN(PP".$i.".ppdpDay5)  WHEN '6' THEN MIN(PP".$i.".ppdpDay6)  WHEN '7' THEN MIN(PP".$i.".ppdpDay7) ELSE PP".$i.".ppdpDay".$numOfWeek." END ELSE PP".$i.".ppdpDay".$numOfWeek." END )";
            $priceQuery .= "+ ( CASE WHEN PE".$i.".peIdx THEN CASE PE".$i.".peDay WHEN '1' THEN MIN(PP".$i.".ppdpSaleDay1)  WHEN '5' THEN MIN(PP".$i.".ppdpSaleDay5)  WHEN '6' THEN MIN(PP".$i.".ppdpSaleDay6)  WHEN '7' THEN MIN(PP".$i.".ppdpSaleDay7) ELSE MIN(PP".$i.".ppdpSaleDay".$numOfWeek.") END ELSE MIN(PP".$i.".ppdpSaleDay".$numOfWeek.") END )";
            $percentQuery .= "+ ( CASE WHEN PE".$i.".peIdx THEN CASE PE".$i.".peDay WHEN '1' THEN MIN(PP".$i.".ppdpPercent1)  WHEN '5' THEN MIN(PP".$i.".ppdpPercent5)  WHEN '6' THEN MIN(PP".$i.".ppdpPercent6)  WHEN '7' THEN MIN(PP".$i.".ppdpPercent7) ELSE PP".$i.".ppdpPercent".$numOfWeek." END ELSE PP".$i.".ppdpPercent".$numOfWeek." END )";
        }
        $basicPriceQuery .= ")";
        $priceQuery .= ")";
        $percentQuery .= ")";
        
        $orderbyQuery = " ORDER BY topIdx DESC, PB.ppbGrade DESC, PPU.ppuExternalFlag DESC, PB.ppbMainPension DESC, MPS.mpIdx DESC";
        
        if($dateBt != ""){
            $dateBt = substr($dateBt,3);
        }
        if($searchPriceMax == ""){
            $priceLimitQuery = " price > 0";
        }else{
            $priceLimitQuery = " price > ".$searchPriceMin." AND price <= ".$searchPriceMax;
        }
        
        $schQuery = "   SELECT MPS.mpsName, MPS.mpsIdx, MPS.mpsAddr1, MPS.mpsAddr2, MPS.mpsAddr1New, MPS.mpsAddrFlag, MPS.mpsMapX, MPS.mpsMapY, MPS.mpIdx,
                        PB.ppbImage, PB.ppbGrade, PB.ppbWantCnt, PB.ppbReserve,
                        CASE WHEN ATRB.altrbIdx IS NULL THEN 0 ELSE 1 END AS topIdx,
                        ".$basicPriceQuery." AS basicPrice, ".$priceQuery." AS price, ".$percentQuery." AS percent,
                        (6371*ACOS(COS(RADIANS('".$mapY."'))*COS(RADIANS(MPS.mpsMapY))*COS(RADIANS(MPS.mpsMapX)-RADIANS('".$mapX."'))+SIN(RADIANS('".$mapY."'))*SIN(RADIANS(MPS.mpsMapY)))) AS distance
                        FROM mergePlaceSite AS MPS
                        LEFT JOIN placeTheme AS PT ON MPS.mpsIdx = PT.mpsIdx AND PT.mtCode LIKE '1%' AND LENGTH(PT.mtCode) = 8
                        LEFT JOIN placePensionUse AS PPU ON PPU.mpIdx = MPS.mpIdx
                        LEFT JOIN placePensionBasic AS PB ON PB.mpIdx = MPS.mpIdx
                        LEFT JOIN placePensionRoom AS PPR ON PPR.mpIdx = MPS.mpIdx AND PPR.pprInMax >= ".$schPeople." AND PPR.pprOpen = '1'
                        LEFT JOIN appLocTopRollingBanner AS ATRB ON '".date('Y-m-d')."' BETWEEN ATRB.altrbStartDate AND ATRB.altrbEndDate AND ATRB.altrbOpen = '1' AND ATRB.altrbLocal IN ('".implode("','",$searchLoc)."') AND ATRB.mpIdx = MPS.mpIdx
                        ".$joinQuery."
                        WHERE MPS.mpsOpen = '1'
                        AND MPS.mmType = 'YPS'
                        AND PB.ppbReserve = 'R'
                        AND MPS.mpType = 'PS'
                        AND PT.mtCode IN ('".implode("','",$searchLoc)."')
                        AND PPR.pprIdx IS NOT NULL";
        if($themeQuery != ""){
            $schQuery .= $themeQuery;
        }
        $schQuery .= "  GROUP BY MPS.mpIdx
                        HAVING ".$priceLimitQuery."                        
                        ".$orderbyQuery."
                        LIMIT ".$limit." OFFSET ".$offset;
        $result['lists'] = $this->db->query($schQuery)->result_array();
        if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
            //echo "<pre>".$this->db->last_query()."</pre>";
        }
        return $result;
    }
    
    function getPensionRoomLists($mpIdx, $searchDate, $searchDateNum){
        $setDate = explode("-", $searchDate);
        $basicPriceQuery = "(0";
        $priceQuery = "(0";
        $percentQuery = "(0";
        $joinQuery = "";
        
        for($i=0; $i< $searchDateNum; $i++){
            $date = date('Y-m-d', mktime(0, 0, 0, $setDate[1], $setDate[2]+$i, $setDate[0]));
            $dateObj = new DateTime($date);
            $numOfWeek = $dateObj->format('N');
            if($numOfWeek < 5){
                $numOfWeek = "1";
            }
            
            $joinQuery .= " LEFT JOIN pensionPrice AS PP".$i." ON PPR.pprIdx = PP".$i.".pprIdx AND '".$date."' BETWEEN PP".$i.".ppdpStart AND PP".$i.".ppdpEnd 
                            LEFT JOIN pensionException AS PE".$i." ON PE".$i.".mpIdx = PPR.mpIdx AND PE".$i.".peSetDate = '".$date."' AND PE".$i.".peUseFlag = 'Y' ";
            $basicPriceQuery .= "+ ( CASE WHEN PE".$i.".peIdx THEN CASE PE".$i.".peDay WHEN '1' THEN MIN(PP".$i.".ppdpDay1)  WHEN '5' THEN MIN(PP".$i.".ppdpDay5)  WHEN '6' THEN MIN(PP".$i.".ppdpDay6)  WHEN '7' THEN MIN(PP".$i.".ppdpDay7) ELSE PP".$i.".ppdpDay".$numOfWeek." END ELSE PP".$i.".ppdpDay".$numOfWeek." END )";
            $priceQuery .= "+ ( CASE WHEN PE".$i.".peIdx THEN CASE PE".$i.".peDay WHEN '1' THEN MIN(PP".$i.".ppdpSaleDay1)  WHEN '5' THEN MIN(PP".$i.".ppdpSaleDay5)  WHEN '6' THEN MIN(PP".$i.".ppdpSaleDay6)  WHEN '7' THEN MIN(PP".$i.".ppdpSaleDay7) ELSE PP".$i.".ppdpSaleDay".$numOfWeek." END ELSE PP".$i.".ppdpSaleDay".$numOfWeek." END )";
            $percentQuery .= "+ ( CASE WHEN PE".$i.".peIdx THEN CASE PE".$i.".peDay WHEN '1' THEN MIN(PP".$i.".ppdpPercent1)  WHEN '5' THEN MIN(PP".$i.".ppdpPercent5)  WHEN '6' THEN MIN(PP".$i.".ppdpPercent6)  WHEN '7' THEN MIN(PP".$i.".ppdpPercent7) ELSE PP".$i.".ppdpPercent".$numOfWeek." END ELSE PP".$i.".ppdpPercent".$numOfWeek." END )";
        }
        
        $basicPriceQuery .= ")";
        $priceQuery .= ")";
        $percentQuery .= ")";
        
        $schQuery = "   SELECT PPR.*, ".$basicPriceQuery." AS basicPrice, ".$priceQuery." AS price, ".$percentQuery." AS percent, PPRP.pprpIdx, PPRP.pprpFileName
                        FROM placePensionRoom AS PPR
                        LEFT JOIN placePensionRoomPhoto AS PPRP ON PPRP.pprIdx = PPR.pprIdx AND PPRP.pprpRepr = '1'
                        ".$joinQuery."
                        WHERE PPR.pprOpen = '1'
                        AND PPR.mpIdx = '".$mpIdx."'
                        AND PPRP.pprpIdx IS NOT NULL
                        GROUP BY PPR.pprIdx
                        ORDER BY PPR.pprNo DESC
                        ";
        $result = $this->db->query($schQuery)->result_array();
        
        return $result;
    }
    
    function getRoomLists($mpIdx){
        $this->db->where('mpIdx', $mpIdx);
        $this->db->where('pprOpen','1');
        $this->db->order_by('pprNo','DESC');
        $result = $this->db->get('placePensionRoom')->result_array();
        
        return $result;
    }

    function getCancelRevList($rIdx){
        $sch_sql = "SELECT *
                    FROM pensionDB.reservation
                    WHERE rIdx = '".$rIdx."'
                    AND rPriceCancelFlag = '0'";
        $result = $this->db->query($sch_sql)->row_array();
        return $result;
    }

    function getPenaltyInfo($mpIdx, $revDate, $cancelRevDay){
        $resArrs    = array();
        
        $penaltyDay = round(abs(strtotime($revDate)-strtotime(date('Y-m-d')))/86400);        
        
        $this->db->where('mpIdx', $mpIdx);
        $this->db->order_by('pppnDay','DESC');
        $result = $this->db->get('pensionDB.placePensionPenalty')->row_array();
    
        $penaltyMaxDay = $result['pppnDay'];
        if($penaltyDay > $penaltyMaxDay){
            $penaltyDay = $penaltyMaxDay;
        }

        $resArrs['penaltyDay']  = $penaltyDay;
        
        $this->db->where('mpIdx', $mpIdx);
        $penaltyFlag = $this->db->count_all_results('pensionDB.placePensionPenalty');
        if($penaltyFlag == 0){
            $mpIdx = "1";
        }
        
        $this->db->where('mpIdx', $mpIdx);
        $this->db->where('pppnDay <=', $penaltyDay);
        $this->db->order_by('pppnDay','DESC');
        $dayInfo = $this->db->get('pensionDB.placePensionPenalty')->row_array();
        if($cancelRevDay != '0'){
            $this->db->where('mpIdx', $mpIdx);
            $this->db->where('pppnRevDay <= ',$cancelRevDay);
            $this->db->order_by('pppnRevDay','DESC');
            $revDayInfo = $this->db->get('pensionDB.placePensionPenalty')->row_array();
        }else{
            $revDayInfo = array();
        }
        if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
            //echo var_dump($revDayInfo);
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

        $resArrs['cancelPercent']   = $cancelPercent;
        
        return $resArrs;
    }

    function getLocationCode($keyword){
        $locArray = explode("/", $keyword);
        $this->db->like('mtSite', 'YPS');
        $this->db->where('mtType', 'PS');
        $this->db->where("mtCode LIKE '1%'",'', FALSE);
        $this->db->where('LENGTH(mtCode)','8');
        $this->db->where_in('mtName', $locArray);
        $result = $this->db->get('mergeTheme')->result_array();
        
        return $result;
    }
    
}


?>