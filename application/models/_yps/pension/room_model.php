<?php
class Room_model extends CI_Model {

    function __construct() {
        parent::__construct();
        
        $CI =& get_instance();
        $CI->db =& $this->load->database('yps', TRUE);
    }
    
    
    
    /**
     * db에 입력되는 data room 정보를 
     * 실제 뿌려주는 글씨들로 변환
     */
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
        /*
        switch ( $result['pprFloor'] )
        {
            case 'S' :
                $result['pprFloor2Str'] = '독채형';
            break;
                
            case 'M' :
            default : 
                $result['pprFloor2Str'] = '복층형';
            break;  
            
        }
        */
        
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

    
    /**
     * 편션의 객실 리스트
     * 
     * @param int pprIdx : 펜션 idx
     * @return array BasicData : 펜션 basic테이블 정보
     * 
     */
    public function lists( $mpIdx ) 
    {
            
        $this->db->select(array(
        '*'
        ));
        
        $this->db->where( 'ppr.mpIdx', $mpIdx );
        $this->db->where('ppr.pprOpen','1');
        $this->db->where('pprpFileName is not null','',false);
        $this->db->join('placePensionBasic AS ppb', 'ppb.mpIdx = ppr.mpIdx', 'left');
        $this->db->join('placePensionRoomPhoto AS pprp', 'pprp.pprIdx = ppr.pprIdx AND pprp.pprpRepr = 1', 'left');
        $this->db->group_by('ppr.pprIdx');
        $this->db->order_by('ppr.pprNo', 'desc');        
        $result = $this->db->get('placePensionRoom AS ppr')->result_array();
        
        foreach ( $result as $key => $value )
        {
            $result[$key] = $this->roomData2String( $value );
        }
        
        //if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
           //echo "<pre>".$this->db->last_query()."</pre>";
        //}
       
        return $result;
    }
    
    function getRevRoom($mpIdx, $dateArray){
        $this->db->select('PPR.*, PPRP.pprpFilename, PPB.ppbIdx');
        $this->db->where('PPR.mpIdx', $mpIdx);
        $this->db->group_by('PPR.pprIdx');
        $this->db->order_by('PPB.ppbIdx','ASC');
        $this->db->order_by('PPR.pprNo','DESC');
        $this->db->where('PPR.pprOpen','1');
        $this->db->join('placePensionRoomPhoto AS PPRP',"PPRP.pprIdx = PPR.pprIdx AND PPRP.pprpRepr = '1'",'LEFT');
        $this->db->join('placePensionBlock AS PPB',"PPB.pprIdx = PPR.pprIdx AND PPB.ppbDate IN ('".implode("','",$dateArray)."')",'LEFT');
        $result = $this->db->get('placePensionRoom AS PPR')->result_array();
        
        return $result;
    }
    
    
    
    /**
     * 객실정보
     * 
     * @param int pprIdx : 객실방 idx
     * @return array BasicData : 펜션 basic테이블 정보
     * 
     */
    public function view( $pprIdx ) 
    {
        $this->db->select(array(
        '*'
        ));
        
        $this->db->where( 'pprIdx', $pprIdx );
        $this->db->from( 'placePensionRoom AS ppr');
        $this->db->join('placePensionBasic AS ppb', 'ppb.mpIdx = ppr.mpIdx', 'left');
        $result = $this->db->get()->row_array();
        
        $result = $this->roomData2String( $result );
        
        return $result;
    }
    
    
    
    /**
     * 객실 가격정보
     * 
     * @param int pprIdx : 객실방 idx
     * @return array BasicData : 펜션 basic테이블 정보
     * 
     */
    public function price( $pprIdx ) 
    {
        $this->db->select(array(
        '*'
        ));
        
        
        $this->db->where( 'ppp.pprIdx', $pprIdx );
        $this->db->where( 'ppd.ppdOpen', '1' );
        $this->db->from( 'placePensionDate AS ppd');
        $this->db->join('placePensionPrice AS ppp', 'ppp.mpIdx = ppd.mpIdx AND ppp.pppType = ppd.ppdType', 'left');
        $result = $this->db->get()->result_array();
        
        
        
        return $result;
    }
    
    //펜션 pprIdx 로 최저가 구함
    function minPrice( $pprIdx, $type='DS' ){
        /* mysql query start */
        $sql = "SELECT  A.mpIdx, A.pprIdx, A.pppType , A.pppDay1, A.pppDay5, A.pppDay6,
                        B.ppraDay1, B.ppraDay5, B.ppraDay6,
                        ROUND((((A.pppDay1-ppraDay1)/A.pppDay1)*100),0) AS percent1,
                        ROUND((((A.pppDay5-ppraDay5)/A.pppDay5)*100),0) AS percent5,
                        ROUND((((A.pppDay6-ppraDay6)/A.pppDay6)*100),0) AS percent6
                        FROM placePensionPrice A
                        LEFT JOIN placePensionRoomAuto B ON A.mpIdx = B.mpIdx AND A.pprIdx = B.pprIdx AND A.pppType = B.ppraType
                        WHERE B.pprIdx = '".$pprIdx."'
                        AND '".date('Y-m-d')."' BETWEEN B.ppraStart AND B.ppraEnd
                        ";
        $row = $this->db->query($sql)->result_array();
        /* mysql query End */
        
        /* Basic data setting Start */
        $price = 0;
        $basic_price = array();
        $sale_price = array();
        $sale_percent = array();
        $bpri = array();
        $spri = array();
        $sper = array();
        /* Basic data setting End */
        
        /* minPrice , maxPercent Setting Start */
        if(count($row) > 0){
            foreach($row as $row){
                $bpri[] = $row['pppDay1'];
                $bpri[] = $row['pppDay5'];
                $bpri[] = $row['pppDay6'];
                $spri[] = $row['ppraDay1'];
                $spri[] = $row['ppraDay5'];
                $spri[] = $row['ppraDay6'];
                $sper[] = $row['percent1'];
                $sper[] = $row['percent5'];
                $sper[] = $row['percent6'];
            }
        }
        
        //echo var_dump($basic_price);
        for($i=0; $i< count($bpri); $i++){
            if($bpri[$i] > 0){
                $basic_price[] = $bpri[$i];
            }
        }
        for($i=0; $i< count($spri); $i++){
            if($spri[$i] > 0){
                $sale_price[] = $spri[$i];
            }
        }
        for($i=0; $i< count($sper); $i++){
            if($sper[$i] != 100){
                $sale_percent[] = $sper[$i];
            }
        }
        
        /* minPrice , maxPercent Setting End */
        
        if(count($sale_price) == 0){
            $price = min($basic_price);
            $sale = 0;
        }else{
            $price = min($sale_price);
            if($price > min($basic_price)){
                $price = min($basic_price);
            }
            $sale = max($sale_percent);
            if($sale == "100"){
                $sale = 0;
            }
        }
            
        
        return $price;
    }
    
    /**
     * 펜션 기간 정보
     * 
     * @param int pensionIdx : 객실방 idx
     * @return array BasicData : 펜션 basic테이블 정보
     * 
     */
    public function period( $pensionIdx ) 
    {
        $this->db->select(array(
        'ppd.ppdIdx',
        'ppd.mpIdx',
        'ppd.ppdNo',
        'ppd.ppdName',
        'ppdt.ppdtStart',
        'ppdt.ppdtEnd'
        ));
        
        $this->db->where( 'ppd.mpIdx', $pensionIdx );
        $this->db->where( 'ppd.ppdOpen', '1' );
        $this->db->from( 'placePensionDate AS ppd'); 
        $this->db->join('placePensionDateTime AS ppdt', 'ppdt.ppdIdx = ppd.ppdIdx', 'left');
        $result = $this->db->get()->result_array();
        
        $pensionPeriod = array();
        foreach ( $result as $key => $result )
        {
            $pensionPeriod[$result['ppdName']][] = $result['ppdtStart'] . ' ~ ' . $result['ppdtEnd'];
        }
        
        
        return $pensionPeriod;
    }
    
    
    
    /**
     * 시작과 끝의 일자를 받아 시작과 끝을 포함하는 사이의 일자를 출력
     * days에 요일들을 변수로 넣는 경우 해당 요일들의 일자만 출려함
     * 
     * @param date startDate : 시작일자
     * @param date endDate : 끝일자
     * @param array days : 가져오려는 요일들
     * @return array dateRange: 일자배열
     */
    function getDateRange( $startDate, $endDate, $days = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') ) 
    {
        $dateRange = array();
        
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        
        for( $i=0; $i <= $interval->days; $i++ )
        {
            if ( $i > 0 )
            {
                $datetime1->modify('+1 day');
            }

            if ( in_array($datetime1->format('l'), $days))
            {
                //$dateRange[] = $datetime1->format('Y-m-d l');
                $dateRange[] = $datetime1->format('Y-m-d');
            }
        }
        
        
        return $dateRange;
    }
    
    
    
    /**
     * 시작일자와 박수를 받아 종료일자를 반환
     * 
     * @param date startDate : 시작일자
     * @param int nights : 박수
     * @return date endDate : 끝일자
     */
    function getEndDate( $startDate, $nights ) 
    {
        $endDate = NULL;
        
        if ( $nights > 0 )
        {
            $datetime1 = new DateTime($startDate);
            $datetime1->modify('+'.$nights.' day');
            $endDate = $datetime1->format('Y-m-d');
        }
        
        return $endDate;
    }
    
    
    
    /**
     * 객실 예약 가격 정보 
     * 
     * @param int pprIdx : 객실방 idx
     * @param date startDate : 투숙 시작일
     * @param int nights : 박수
     * @return array priceData : 객실 예약 가격 정보
     * 
     */
    public function totalPrice( $pprIdx, $startDate=TIME_YMD, $nights=1 ) 
    {
        $result = array();
        $result = array(
            'byRoom' => array(
                'seasonPrice' => '0',
                'salePrice' => '0',
                'resultPrice' => '0' 
            ),
            'byDate' => array()
        );
        
        $endDate = $this->getEndDate( $startDate, $nights );
        
        if ( $endDate != NULL )
        {
            $dateRange = $this->getDateRange( $startDate, $endDate );
            array_pop($dateRange);
            
            foreach ( $dateRange as $key => $date )
            {
                $dateObj = new DateTime($date);
                $date = $dateObj->format('Y-m-d');
                $dayOfWeek = $dateObj->format('l');
                $numOfWeek = $dateObj->format('N'); // 오늘의 요일 숫자 - 월은1 부터 일은7
                
                $seasonAndSaleData = $this->getSeasonAndSaleForDate($pprIdx, $date);
                //echo var_dump($seasonAndSaleData);
                $byRoom =& $result['byRoom'];
                $byDate =& $result['byDate'];
                
                $byDate[$key]['date'] = $date;
                $byDate[$key]['dayOfWeek'] = $dayOfWeek;
                //$byDate[$key]['season'] = $seasonAndSaleData['season']['name'];
                $byDate[$key]['seasonPrice'] = $seasonAndSaleData['season']['price'];
                $byDate[$key]['salePrice'] = $seasonAndSaleData['sale']['price'];
                $byDate[$key]['resultPrice'] = $seasonAndSaleData['sale']['price'];
                
                $byRoom['seasonPrice'] += $byDate[$key]['seasonPrice'];
                $byRoom['salePrice'] += $byDate[$key]['salePrice'];
                $byRoom['resultPrice'] += $byDate[$key]['resultPrice'];
            }
            
            $result['byRoom']['seasonPrice'] = (string)$result['byRoom']['seasonPrice'];
            $result['byRoom']['salePrice'] = (string)$result['byRoom']['salePrice'];
            $result['byRoom']['resultPrice'] = (string)$result['byRoom']['resultPrice'];
            
        }

        return $result;
    }

    public function DirectTotalPrice( $pprIdx, $startDate=TIME_YMD, $nights=1 ) 
    {
        $result = array();
        $result = array(
            'byRoom' => array(
                'seasonPrice' => '0',
                'salePrice' => '0',
                'resultPrice' => '0' 
            ),
            'byDate' => array()
        );
        
        $endDate = $this->getEndDate( $startDate, $nights );
        
        if ( $endDate != NULL )
        {
            $dateRange = $this->getDateRange( $startDate, $endDate );
            array_pop($dateRange);
            
            foreach ( $dateRange as $key => $date )
            {
                $dateObj = new DateTime($date);
                $date = $dateObj->format('Y-m-d');
                $dayOfWeek = $dateObj->format('l');
                $numOfWeek = $dateObj->format('N'); // 오늘의 요일 숫자 - 월은1 부터 일은7
                
                $seasonAndSaleData = $this->getDirectSeasonAndSaleForDate($pprIdx, $date);
                //echo var_dump($seasonAndSaleData);
                $byRoom =& $result['byRoom'];
                $byDate =& $result['byDate'];
                
                $byDate[$key]['date'] = $date;
                $byDate[$key]['dayOfWeek'] = $dayOfWeek;
                //$byDate[$key]['season'] = $seasonAndSaleData['season']['name'];
                $byDate[$key]['seasonPrice'] = $seasonAndSaleData['season']['price'];
                $byDate[$key]['salePrice'] = $seasonAndSaleData['sale']['price'];
                $byDate[$key]['resultPrice'] = $seasonAndSaleData['season']['price']-$seasonAndSaleData['sale']['price'];
                
                $byRoom['seasonPrice'] += $byDate[$key]['seasonPrice'];
                $byRoom['salePrice'] += $byDate[$key]['salePrice'];
                $byRoom['resultPrice'] += $byDate[$key]['resultPrice'];
                
                
            }
            
            $result['byRoom']['seasonPrice'] = (string)$byRoom['seasonPrice'];
            $result['byRoom']['salePrice'] = (string)$byRoom['salePrice'];
            $result['byRoom']['resultPrice'] = (string)$byRoom['resultPrice'];
            
        }

        return $result;
    }

    public function realTimePrice( $pprIdx, $startDate=TIME_YMD, $nights=1, $mpIdx ) 
    {
        $basicPrice = 0;
        $salePrice = 0;
        $setDateArray = explode("-",$startDate);
        
        for($i=0; $i< $nights; $i++){
            $date = date("Y-m-d", mktime(0,0,0,$setDateArray[1], $setDateArray[2]+$i, $setDateArray[0]));
            
            $dateObj = new DateTime($date);
            $numOfWeek = $dateObj->format('N');
            if($numOfWeek == 0){
                $numOfWeek = "7";
            }else if($numOfWeek < 5){
                $numOfWeek = "1";
            }
            
            $query = "SELECT PP.ppdpType, PD.pdName ,
                        CASE WHEN peIdx THEN
                            CASE peDay
                                WHEN '1' THEN ppdpDay1
                                WHEN '5' THEN ppdpDay5
                                WHEN '6' THEN ppdpDay6
                                WHEN '7' THEN ppdpDay7
                            ELSE
                                ppdpDay".$numOfWeek."
                            END
                        ELSE
                            ppdpDay".$numOfWeek."
                        END AS basicPrice,
                        CASE WHEN peIdx THEN
                            CASE peDay
                                WHEN '1' THEN ppdpSaleDay1
                                WHEN '5' THEN ppdpSaleDay5
                                WHEN '6' THEN ppdpSaleDay6
                                WHEN '7' THEN ppdpSaleDay7
                            ELSE
                                ppdpSaleDay".$numOfWeek."
                            END
                        ELSE
                            ppdpSaleDay".$numOfWeek."
                        END AS resultPrice
                FROM pensionPrice AS PP
                LEFT JOIN pensionDate AS PD ON PD.mpIdx = '".$mpIdx."' AND '".$date."' BETWEEN pdStart AND pdEnd
                LEFT JOIN pensionException AS PE ON PE.mpIdx = PP.mpIdx AND PE.peSetDate = '".$date."' AND PE.peUseFlag = 'Y'
                WHERE PP.pprIdx = '".$pprIdx."' AND '".$date."' BETWEEN PP.ppdpStart AND PP.ppdpEnd 
                HAVING resultPrice > 0
                ORDER BY resultPrice ASC";
            //echo "<pre>".$query."</pre>";
            $priceArray = $this->db->query($query)->row_array();
        
            $basicPrice = $basicPrice + $priceArray['basicPrice'];
            $salePrice = $salePrice + $priceArray['resultPrice'];

            
            $dateName = ", ".$priceArray['pdName'];
        }
        
        $iPod = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
        $iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
        $iPad = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
        
        if($iPod || $iPhone || $iPad ){
            $basicPrice = number_format($basicPrice);
            $salePrice = number_format($salePrice);
        }
        
        $result = array(
            'basicPrice' => $basicPrice,
            'salePrice' => $salePrice
        );
        
        return $result;
    }


    public function roomPrice( $pprIdx, $startDate=TIME_YMD, $nights=1 ) 
    {
        $result = array();
        $result = array(
            'byRoom' => array(
                'seasonPrice' => '0',
                'salePrice' => '0',
                'resultPrice' => '0' 
            ),
            'byDate' => array()
        );
        
        $endDate = $this->getEndDate( $startDate, $nights );
        
        if ( $endDate != NULL )
        {
            $dateRange = $this->getDateRange( $startDate, $endDate );
            array_pop($dateRange);
            
            foreach ( $dateRange as $key => $date )
            {
                $dateObj = new DateTime($date);
                $date = $dateObj->format('Y-m-d');
                $dayOfWeek = $dateObj->format('l');
                $numOfWeek = $dateObj->format('N'); // 오늘의 요일 숫자 - 월은1 부터 일은7
                
                $seasonAndSaleData = $this->getPriceData($pprIdx, $date);
                //echo var_dump($seasonAndSaleData);
                $byRoom =& $result['byRoom'];
                $byDate =& $result['byDate'];
                
                $byDate[$key]['date'] = $date;
                $byDate[$key]['dayOfWeek'] = $dayOfWeek;
                //$byDate[$key]['season'] = $seasonAndSaleData['season']['name'];
                $byDate[$key]['seasonPrice'] = $seasonAndSaleData['season']['price'];
                $byDate[$key]['salePrice'] = $seasonAndSaleData['sale']['price'];
                $byDate[$key]['resultPrice'] = $seasonAndSaleData['season']['price']-$seasonAndSaleData['sale']['price'];
                
                $byRoom['seasonPrice'] += $byDate[$key]['seasonPrice'];
                $byRoom['salePrice'] += $byDate[$key]['salePrice'];
                $byRoom['resultPrice'] += $byDate[$key]['resultPrice'];
            }
            
            $result['byRoom']['seasonPrice'] = (string)$result['byRoom']['seasonPrice'];
            $result['byRoom']['salePrice'] = (string)$result['byRoom']['salePrice'];
            $result['byRoom']['resultPrice'] = (string)$result['byRoom']['resultPrice'];
            
        }
           
        return $result;
    }



    private $seasonData = array();
    private $saleData = array();
    
    /**
     * 입력받은 일자 객실의 시즌정보
     * 
     * @param int pprIdx : 객실방 idx
     * @param date startDate : 투숙 시작일
     * @param int nights : 박수
     * @return array priceData : 객실 예약 가격 정보
     * 
     */
    function getSeasonAndSaleForDate( $pprIdx, $date )
    {
        $dateObj = new DateTime($date);
        $date = $dateObj->format('Y-m-d');
        $numOfWeek = $dateObj->format('N'); // 오늘의 요일 숫자 - 월은1 부터 일은7
        $dateTime = strtotime($date);
        
        
        
        
        /* 시즌 정보 START */
        
        $this->db->select(array(
            'ppp.mpIdx', 
            'ppp.pprIdx', 
            'ppp.pppType', 
            'ppp.pppDay1', 
            'ppp.pppDay2', 
            'ppp.pppDay3', 
            'ppp.pppDay4', 
            'ppp.pppDay5', 
            'ppp.pppDay6', 
            'ppp.pppDay7',
            'ppd.ppdName',
            'ppdt.ppdtStart', 
            'ppdt.ppdtEnd'
        ));
        $this->db->where( 'ppp.pprIdx', $pprIdx );
        $this->db->where( 'ppd.ppdOpen','1');
        $this->db->where("'".date('Y-m-d')."' BETWEEN ppdt.ppdtStart AND ppdt.ppdtEnd", '',false);
        $this->db->from( 'placePensionPrice AS ppp');
        $this->db->join('placePensionDate AS ppd', 'ppp.mpIdx = ppd.mpIdx AND ppp.pppType = ppd.ppdType', 'left');
        $this->db->join('placePensionDateTime AS ppdt', 'ppdt.ppdIdx = ppd.ppdIdx', 'left');        
        $this->seasonData[$pprIdx] = $this->db->get()->result_array();
        //echo $this->db->last_query();
        $mpIdx = $this->seasonData[$pprIdx][0]['mpIdx'];
        
        
        $holi_sql = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday";
        $holiday_arr = $this->db->query($holi_sql)->result_array();
        $holiday = array();
        $holiday_title = array();
        foreach($holiday_arr as $holiday_arr){
            $flag_sql = "SELECT COUNT(*) AS cnt FROM holidayExclude WHERE hIdx = '".$holiday_arr['hIdx']."' AND mpIdx = '".$mpIdx."'";
            $flag_arr = $this->db->query($flag_sql)->row_array();
            if($flag_arr['cnt'] == 0){
                $holiday[] = $holiday_arr['hDate'];
                $holiday_title[$holiday_arr['hDate']] = $holiday_arr['hTitle'];
            }
        }
        $seasonData = array(
            'name' => NULL,
            'price' => NULL
        );
        /*
         * if (($ppdtStart <= $dateTime && $ppdtEnd >= $dateTime)|| ($ppdtStart == NULL && $value['pppDay'.$numOfWeek] != 0)
         *  ppdTStart가 nul 이고 pppDay의 값이 있다면, 비수기의 값인데, 비수기가 안걸리고 다른 날짜정보가 잡혀, Type이 DS라는 것을 강제로 추가
         *  2014.06.03 김영웅
         * 
         */
        foreach ( $this->seasonData[$pprIdx] as $key => $value )
        {
            $ppdtStart = strtotime($value['ppdtStart']);
            $ppdtEnd = strtotime($value['ppdtEnd']);
            
            if (($ppdtStart <= $dateTime && $ppdtEnd >= $dateTime)|| ($ppdtStart == NULL && $value['pppDay'.$numOfWeek] != 0 && $value['pppType'] == "DS"))
            {
                $seasonData['name'] = $value['ppdName'];
                $seasonData['price'] = $value['pppDay'.$numOfWeek];
                $seasonData['type'] = $value['pppType'];
                
                if(in_array($date, $holiday)){
                    $seasonData['name']  = $holiday_title[$date];
                    $seasonData['price'] = $value['pppDay6'];
                    $seasonData['type'] = $value['pppType'];                    
                }
            }
        }
        /* 시즌 정보 END */
        
        
        /* 할인 정보 START */
        /* mysql query start */
        $sql = "SELECT  A.mpIdx, A.pprIdx, A.pppType , A.pppDay1, A.pppDay5, A.pppDay6,
                        B.ppraDay1, B.ppraDay5, B.ppraDay6,
                        ROUND((((A.pppDay1-ppraDay1)/A.pppDay1)*100),0) AS percent1,
                        ROUND((((A.pppDay5-ppraDay5)/A.pppDay5)*100),0) AS percent5,
                        ROUND((((A.pppDay6-ppraDay6)/A.pppDay6)*100),0) AS percent6
                        FROM placePensionPrice A
                        LEFT JOIN placePensionRoomAuto B ON A.mpIdx = B.mpIdx AND A.pprIdx = B.pprIdx AND A.pppType = B.ppraType
                        WHERE A.mpIdx = '".$mpIdx."'
                        AND A.pprIdx = '".$pprIdx."'
                        AND A.pppType = '".$seasonData['type']."'";
        $row = $this->db->query($sql)->result_array();
        /* mysql query End */
        
        /* Basic data setting Start */
        $price = 0;
        $basic_price = array();
        $sale_price = array();
        $sale_percent = array();
        $bpri = array();
        $spri = array();
        $sper = array();
        /* Basic data setting End */
        
        /* minPrice , maxPercent Setting Start */
        if(count($row) > 0){
            foreach($row as $row){
                $bpri[] = $row['pppDay1'];
                $bpri[] = $row['pppDay5'];
                $bpri[] = $row['pppDay6'];
                $spri[] = $row['ppraDay1'];
                $spri[] = $row['ppraDay5'];
                $spri[] = $row['ppraDay6'];
                $sper[] = $row['percent1'];
                $sper[] = $row['percent5'];
                $sper[] = $row['percent6'];
            }
        }
        
        //echo var_dump($basic_price);
        for($i=0; $i< count($bpri); $i++){
            if($bpri[$i] > 0){
                $basic_price[] = $bpri[$i];
            }
        }
        for($i=0; $i< count($spri); $i++){
            if($spri[$i] > 0){
                $sale_price[] = $spri[$i];
            }
        }
        for($i=0; $i< count($sper); $i++){
            if($sper[$i] > 0){
                $sale_percent[] = $sper[$i];
            }
        }
        
        /* minPrice , maxPercent Setting End */
        
        if(count($sale_price) == 0){
            $price = $seasonData['price'];
            $sale = 0;
        }else{
            $price = min($sale_price);
            if($price > $seasonData['price']){
                $price = $seasonData['price'];
            }
            $sale = max($sale_percent);
            if($sale == "100"){
                $sale = 0;
            }
        }
        //echo "price : ".$price."// sale : ".$sale;
        //echo var_dump($price);

        $salePrice = array( 
                           'percent' => $sale,
                           'price' => $price);
        /* 할인 정보 END */
        
        return array(
            'season' => $seasonData,
            'sale' => $salePrice
        );
    }

    function getDirectSeasonAndSaleForDateTest( $pprIdx, $date)
    {
        
        $dateObj = new DateTime($date);
        $date = $dateObj->format('Y-m-d');
        $numOfWeek = $dateObj->format('N'); // 오늘의 요일 숫자 - 월은1 부터 일은7
        $dateTime = strtotime($date);
        
        
        
        
        /* 시즌 정보 START */
        
        $this->db->select(array(
            'ppp.mpIdx', 
            'ppp.pprIdx', 
            'ppp.pppType', 
            'ppp.pppDay1', 
            'ppp.pppDay2', 
            'ppp.pppDay3', 
            'ppp.pppDay4', 
            'ppp.pppDay5', 
            'ppp.pppDay6', 
            'ppp.pppDay7',
            'ppd.ppdName',
            'ppdt.ppdtStart', 
            'ppdt.ppdtEnd'
        ));
        $this->db->where( 'ppp.pprIdx', $pprIdx );
        $this->db->where( '(ppd.ppdOpen =  \'1\' OR pppType = \'DS\')' );
        $this->db->from( 'placePensionPrice AS ppp');
        $this->db->join('placePensionDate AS ppd', 'ppp.mpIdx = ppd.mpIdx AND ppp.pppType = ppd.ppdType', 'left');
        $this->db->join('placePensionDateTime AS ppdt', 'ppdt.ppdIdx = ppd.ppdIdx', 'left');
        $this->seasonData[$pprIdx] = $this->db->get()->result_array();
        
        $mpIdx = $this->seasonData[$pprIdx][0]['mpIdx'];
        
        
        $holi_sql = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday";
        $holiday_arr = $this->db->query($holi_sql)->result_array();
        $holiday = array();
        $holiday_title = array();
        foreach($holiday_arr as $holiday_arr){
            $flag_sql = "SELECT COUNT(*) AS cnt FROM holidayExclude WHERE hIdx = '".$holiday_arr['hIdx']."' AND mpIdx = '".$mpIdx."'";
            $flag_arr = $this->db->query($flag_sql)->row_array();
            if($flag_arr['cnt'] == 0){
                $holiday[] = $holiday_arr['hDate'];
                $holiday_title[$holiday_arr['hDate']] = $holiday_arr['hTitle'];
            }
        }
        $seasonData = array(
            'name' => NULL,
            'price' => NULL
        );
        /*
         * if (($ppdtStart <= $dateTime && $ppdtEnd >= $dateTime)|| ($ppdtStart == NULL && $value['pppDay'.$numOfWeek] != 0)
         *  ppdTStart가 nul 이고 pppDay의 값이 있다면, 비수기의 값인데, 비수기가 안걸리고 다른 날짜정보가 잡혀, Type이 DS라는 것을 강제로 추가
         *  2014.06.03 김영웅
         * 
         */
        foreach ( $this->seasonData[$pprIdx] as $key => $value )
        {
            $ppdtStart = strtotime($value['ppdtStart']);
            $ppdtEnd = strtotime($value['ppdtEnd']);
            
            if (($ppdtStart <= $dateTime && $ppdtEnd >= $dateTime)|| ($ppdtStart == NULL && $value['pppDay'.$numOfWeek] != 0 && $value['pppType'] == "DS"))
            {
                $seasonData['name'] = $value['ppdName'];
                $seasonData['price'] = $value['pppDay'.$numOfWeek];
                
                if(in_array($date, $holiday)){
                    $seasonData['name']  = $holiday_title[$date];
                    $seasonData['price'] = $value['pppDay6'];
                    
                }
            }
        }
        /* 시즌 정보 END */
        
        
        /* 할인 정보 START */
        /* mysql query start */
        $row = "";
        $sql = "SELECT  A.mpIdx, A.pprIdx, A.pppType , A.pppDay1, A.pppDay5, A.pppDay6,
                        B.ppraDay1, B.ppraDay5, B.ppraDay6,
                        ROUND((((A.pppDay1-ppraDay1)/A.pppDay1)*100),0) AS percent1,
                        ROUND((((A.pppDay5-ppraDay5)/A.pppDay5)*100),0) AS percent5,
                        ROUND((((A.pppDay6-ppraDay6)/A.pppDay6)*100),0) AS percent6
                        FROM placePensionPrice A
                        LEFT JOIN placePensionRoomAuto B ON A.mpIdx = B.mpIdx AND A.pprIdx = B.pprIdx AND A.pppType = B.ppraType
                        WHERE A.mpIdx = '".$mpIdx."'
                        AND A.pprIdx = '".$pprIdx."'
                        AND '".$date."' BETWEEN B.ppraStart AND B.ppraEnd";
        
        $row = $this->db->query($sql)->result_array();
        
        /* mysql query End */
        
        /* Basic data setting Start */
        $sale = "";
        $price = "";
        $basic_price = array();
        $sale_price = array();
        $sale_percent = array();
        $bpri = array();
        $spri = array();
        $sper = array();
        /* Basic data setting End */
        if($numOfWeek == "7" || $numOfWeek < 5){
            $numOfWeek = "1";
        }
        /* minPrice , maxPercent Setting Start */
        if(count($row) > 0){
            foreach($row as $row){
                $bpri[] = $row['pppDay'.$numOfWeek];
                $spri[] = $row['ppraDay'.$numOfWeek];
                $sper[] = $row['percent'.$numOfWeek];
            }
        }
        
        //echo var_dump($bpri);
        for($i=0; $i< count($bpri); $i++){
            if($bpri[$i] > 0){
                $basic_price[] = $bpri[$i];
            }
        }
        for($i=0; $i< count($spri); $i++){
            if($spri[$i] > 0){
                $sale_price[] = $spri[$i];
            }
        }
        for($i=0; $i< count($sper); $i++){
            if($sper[$i] > 0){
                $sale_percent[] = $sper[$i];
            }
        }
        
        /* minPrice , maxPercent Setting End */
        //echo var_dump($basic_price);
        if(count($sale_price) == 0){
            $price = $seasonData['price'];
            $sale = 0;
        }else{
            $price = min($sale_price);
            if($price > $seasonData['price']){
                $price = $seasonData['price'];
            }
            $sale = max($sale_percent);
            if($sale == "100"){
                $sale = 0;
            }
        }
        //echo "price : ".$price."// sale : ".$sale;
        //echo var_dump($price);

        $salePrice = array( 
                           'percent' => $sale,
                           'price' => $price);
        /* 할인 정보 END */
        //echo var_dump($salePrice);
        return array(
            'season' => $seasonData,
            'sale' => $salePrice
        );
    }

    function getDirectSeasonAndSaleForDate( $pprIdx, $date)
    {
        $dateObj = new DateTime($date);
        $date = $dateObj->format('Y-m-d');
        $numOfWeek = $dateObj->format('N'); // 오늘의 요일 숫자 - 월은1 부터 일은7
        $dateTime = strtotime($date);
        
        
        
        
        /* 시즌 정보 START */
        
        $this->db->select(array(
            'ppp.mpIdx', 
            'ppp.pprIdx', 
            'ppp.pppType', 
            'ppp.pppDay1', 
            'ppp.pppDay2', 
            'ppp.pppDay3', 
            'ppp.pppDay4', 
            'ppp.pppDay5', 
            'ppp.pppDay6', 
            'ppp.pppDay7',
            'ppd.ppdName',
            'ppdt.ppdtStart', 
            'ppdt.ppdtEnd'
        ));
        $this->db->where( 'ppp.pprIdx', $pprIdx );
        $this->db->where( '(ppd.ppdOpen =  \'1\' OR pppType = \'DS\')' );
        $this->db->from( 'placePensionPrice AS ppp');
        $this->db->join('placePensionDate AS ppd', 'ppp.mpIdx = ppd.mpIdx AND ppp.pppType = ppd.ppdType', 'left');
        $this->db->join('placePensionDateTime AS ppdt', 'ppdt.ppdIdx = ppd.ppdIdx', 'left');
        $this->seasonData[$pprIdx] = $this->db->get()->result_array();
        
        $mpIdx = $this->seasonData[$pprIdx][0]['mpIdx'];
        
        
        $holi_sql = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday";
        $holiday_arr = $this->db->query($holi_sql)->result_array();
        $holiday = array();
        $holiday_title = array();
        foreach($holiday_arr as $holiday_arr){
            $flag_sql = "SELECT COUNT(*) AS cnt FROM holidayExclude WHERE hIdx = '".$holiday_arr['hIdx']."' AND mpIdx = '".$mpIdx."'";
            $flag_arr = $this->db->query($flag_sql)->row_array();
            if($flag_arr['cnt'] == 0){
                $holiday[] = $holiday_arr['hDate'];
                $holiday_title[$holiday_arr['hDate']] = $holiday_arr['hTitle'];
            }
        }
        $seasonData = array(
            'name' => NULL,
            'price' => NULL
        );
        /*
         * if (($ppdtStart <= $dateTime && $ppdtEnd >= $dateTime)|| ($ppdtStart == NULL && $value['pppDay'.$numOfWeek] != 0)
         *  ppdTStart가 nul 이고 pppDay의 값이 있다면, 비수기의 값인데, 비수기가 안걸리고 다른 날짜정보가 잡혀, Type이 DS라는 것을 강제로 추가
         *  2014.06.03 김영웅
         * 
         */
        foreach ( $this->seasonData[$pprIdx] as $key => $value )
        {
            $ppdtStart = strtotime($value['ppdtStart']);
            $ppdtEnd = strtotime($value['ppdtEnd']);
            
            if (($ppdtStart <= $dateTime && $ppdtEnd >= $dateTime)|| ($ppdtStart == NULL && $value['pppDay'.$numOfWeek] != 0 && $value['pppType'] == "DS"))
            {
                $seasonData['name'] = $value['ppdName'];
                $seasonData['price'] = $value['pppDay'.$numOfWeek];
                $seasonData['type'] = $value['pppType'];
                if(in_array($date, $holiday)){
                    $seasonData['name']  = $holiday_title[$date];
                    $seasonData['price'] = $value['pppDay6'];
                    $seasonData['type'] = $value['pppType'];
                    $numOfWeek = "6";
                }
            }
        }
        /* 시즌 정보 END */
        
        
        /* 할인 정보 START */
        if ( ! isset($this->saleData[$pprIdx]) )
        {
            $this->db->select(array(
                'pps.mpIdx', 
                'pps.pprIdx', 
                'pps.ppsStartDate', 
                'pps.ppsEndDate', 
                'pps.ppsType', 
                'pps.ppsSalePrice',
                'pps.ppsType', 
                'pps.ppsDay1', 
                'pps.ppsDay2', 
                'pps.ppsDay3', 
                'pps.ppsDay4', 
                'pps.ppsDay5', 
                'pps.ppsDay6', 
                'pps.ppsDay7'
            ));
            $this->db->where( 'pps.pprIdx like', '%'.$pprIdx.'%' );
            $this->db->where('pps.mpIdx', $mpIdx);
            $this->db->from( 'placePensionSale AS pps');
            $this->saleData[$pprIdx] = $this->db->get()->result_array();
        }
        
        $saleData = array(
            'name' => NULL,
            'percent' => 0,
            'price' => 0
        );
        foreach ( $this->saleData[$pprIdx] as $key => $value )
        {
            $ppsStartDate = strtotime($value['ppsStartDate']);
            $ppsEndDate = strtotime($value['ppsEndDate']);
            
            if ( $ppsStartDate <= $dateTime && $ppsEndDate >= $dateTime  && $value['ppsDay'.$numOfWeek] == '1' && $seasonData['price'] > 0 )
            {
                switch ( $value['ppsType'] )
                {
                    case 'CS' : // 금액할인
                    
                        $saleData['name'] = '금액할인';
                        $saleData['percent'] = $value['ppsSalePrice'] / $seasonData['price'] * 100;
                        $salePrice = $value['ppsSalePrice'];
                    
                    
                    break;
                    case 'RS' : // 정률할인
                    
                        $saleData['name'] = '정률할인';
                        $saleData['percent'] = $value['ppsSalePrice'];
                        $salePrice = $seasonData['price'] * $value['ppsSalePrice'] / 100;
                        
                    break;
                    default;
                    
                    
                    
                    break;
                }
                
                
                if ( $saleData['price'] < $salePrice )
                {
                    $saleData['price'] = $salePrice;
                }
            }
        }
        /* 할인 정보 END */
        
        
        return array(
            'season' => $seasonData,
            'sale' => $saleData
        );
    }

    function getPriceData( $pprIdx, $date )
    {
        $dateObj = new DateTime($date);
        $date = $dateObj->format('Y-m-d');
        $numOfWeek = $dateObj->format('N'); // 오늘의 요일 숫자 - 월은1 부터 일은7
        $dateTime = strtotime($date);
        
        
        
        
        /* 시즌 정보 START */
        
        $this->db->select(array(
            'ppp.mpIdx', 
            'ppp.pprIdx', 
            'ppp.pppType', 
            'ppp.pppDay1', 
            'ppp.pppDay2', 
            'ppp.pppDay3', 
            'ppp.pppDay4', 
            'ppp.pppDay5', 
            'ppp.pppDay6', 
            'ppp.pppDay7',
            'ppd.ppdName',
            'ppdt.ppdtStart', 
            'ppdt.ppdtEnd'
        ));
        $this->db->where( 'ppp.pprIdx', $pprIdx );
        $this->db->where( '(ppd.ppdOpen =  \'1\' OR pppType = \'DS\')' );
        $this->db->from( 'placePensionPrice AS ppp');
        $this->db->join('placePensionDate AS ppd', 'ppp.mpIdx = ppd.mpIdx AND ppp.pppType = ppd.ppdType', 'left');
        $this->db->join('placePensionDateTime AS ppdt', 'ppdt.ppdIdx = ppd.ppdIdx', 'left');
        $this->seasonData[$pprIdx] = $this->db->get()->result_array();
        
        $mpIdx = $this->seasonData[$pprIdx][0]['mpIdx'];
        
        
        $holi_sql = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday";
        $holiday_arr = $this->db->query($holi_sql)->result_array();
        $holiday = array();
        $holiday_title = array();
        foreach($holiday_arr as $holiday_arr){
            $flag_sql = "SELECT COUNT(*) AS cnt FROM holidayExclude WHERE hIdx = '".$holiday_arr['hIdx']."' AND mpIdx = '".$mpIdx."'";
            $flag_arr = $this->db->query($flag_sql)->row_array();
            if($flag_arr['cnt'] == 0){
                $holiday[] = $holiday_arr['hDate'];
                $holiday_title[$holiday_arr['hDate']] = $holiday_arr['hTitle'];
            }
        }
        $seasonData = array(
            'name' => NULL,
            'price' => NULL
        );
        /*
         * if (($ppdtStart <= $dateTime && $ppdtEnd >= $dateTime)|| ($ppdtStart == NULL && $value['pppDay'.$numOfWeek] != 0)
         *  ppdTStart가 nul 이고 pppDay의 값이 있다면, 비수기의 값인데, 비수기가 안걸리고 다른 날짜정보가 잡혀, Type이 DS라는 것을 강제로 추가
         *  2014.06.03 김영웅
         * 
         */
        foreach ( $this->seasonData[$pprIdx] as $key => $value )
        {
            $ppdtStart = strtotime($value['ppdtStart']);
            $ppdtEnd = strtotime($value['ppdtEnd']);
            
            if (($ppdtStart <= $dateTime && $ppdtEnd >= $dateTime)|| ($ppdtStart == NULL && $value['pppDay'.$numOfWeek] != 0 && $value['pppType'] == "DS"))
            {
                $seasonData['name'] = $value['ppdName'];
                $seasonData['price'] = $value['pppDay'.$numOfWeek];
                $seasonData['type'] = $value['pppType'];
                if(in_array($date, $holiday)){
                    $seasonData['name']  = $holiday_title[$date];
                    $seasonData['price'] = $value['pppDay6'];
                    $seasonData['type'] = $value['pppType'];
                    $numOfWeek = "6";
                }
            }
        }
        /* 시즌 정보 END */
        
        
        /* 할인 정보 START */
        if ( ! isset($this->saleData[$pprIdx]) )
        {
            $this->db->select(array(
                'pps.mpIdx', 
                'pps.pprIdx', 
                'pps.ppsStartDate', 
                'pps.ppsEndDate', 
                'pps.ppsType', 
                'pps.ppsSalePrice',
                'pps.ppsType', 
                'pps.ppsDay1', 
                'pps.ppsDay2', 
                'pps.ppsDay3', 
                'pps.ppsDay4', 
                'pps.ppsDay5', 
                'pps.ppsDay6', 
                'pps.ppsDay7'
            ));
            $this->db->where( 'pps.pprIdx like', '%'.$pprIdx.'%' );
            $this->db->where('pps.mpIdx', $mpIdx);
            $this->db->from( 'placePensionSale AS pps');
            $this->saleData[$pprIdx] = $this->db->get()->result_array();
        }
        
        $saleData = array(
            'name' => NULL,
            'percent' => 0,
            'price' => 0
        );
        foreach ( $this->saleData[$pprIdx] as $key => $value )
        {
            $ppsStartDate = strtotime($value['ppsStartDate']);
            $ppsEndDate = strtotime($value['ppsEndDate']);
            
            if ( $ppsStartDate <= $dateTime && $ppsEndDate >= $dateTime  && $value['ppsDay'.$numOfWeek] == '1' && $seasonData['price'] > 0 )
            {
                switch ( $value['ppsType'] )
                {
                    case 'CS' : // 금액할인
                    
                        $saleData['name'] = '금액할인';
                        $saleData['percent'] = $value['ppsSalePrice'] / $seasonData['price'] * 100;
                        $salePrice = $value['ppsSalePrice'];
                    
                    
                    break;
                    case 'RS' : // 정률할인
                    
                        $saleData['name'] = '정률할인';
                        $saleData['percent'] = $value['ppsSalePrice'];
                        $salePrice = $seasonData['price'] * $value['ppsSalePrice'] / 100;
                        
                    break;
                    default;
                    
                    
                    
                    break;
                }
                
                
                if ( $saleData['price'] < $salePrice )
                {
                    $saleData['price'] = $salePrice;
                }
            }
        }
        /* 할인 정보 END */
        
        
        return array(
            'season' => $seasonData,
            'sale' => $saleData
        );
    }

    function getRoomCalInfo($pprIdx){
        $this->db->select(array('pprName','pprInMin','pprInMax','pprSize'));
        $this->db->where('pprIdx', $pprIdx);
        $result = $this->db->get('placePensionRoom')->row_array();
        
        return $result;
    }
    
    function getRoomPrice($mpIdx, $pprIdx){
        $dayNum = date('N', strtotime(date('Y-m-d')));
        if($dayNum < 5){
            $dayNum = 1;
        }
        
        $query = "  SELECT 
                    CASE WHEN peIdx THEN
                        CASE peDay
                            WHEN '1' THEN ppdpPercent1
                            WHEN '5' THEN ppdpPercent5
                            WHEN '6' THEN ppdpPercent6
                            WHEN '7' THEN ppdpPercent7
                        ELSE
                            ppdpPercent".$dayNum."
                        END
                    ELSE
                        ppdpPercent".$dayNum."
                    END AS percent,
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
                    END AS resultPrice,
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
                    END AS basicPrice, PPB.ppbReserve
                    FROM pensionPrice AS PP
                    LEFT JOIN pensionException AS PE ON PE.mpIdx = PP.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                    LEFT JOIN placePensionBasic AS PPB ON PPB.mpIdx = PP.mpIdx
                    WHERE PP.pprIdx = '".$pprIdx."' AND '".date('Y-m-d')."' BETWEEN PP.ppdpStart AND PP.ppdpEnd
                    AND PP.pprIdx IS NOT NULL
                    AND PP.ppdpSaleDay".$dayNum." > 0
                    AND PP.ppdpPercent".$dayNum." < 100";
                    
        $priceArray = $this->db->query($query)->row_array();
        if(	(date('Y-m-d') >= YAPEN_SALE_EVENT_START && date('Y-m-d') <= YAPEN_SALE_EVENT_END && $priceArray['ppbReserve'] == "R") ||
        	($_SERVER['REMOTE_ADDR'] == YAPEN_SALE_EVENT_TEST_IP && YAPEN_SALE_EVENT_TEST == "Y")){
            return (floor(($priceArray['resultPrice']-($priceArray['resultPrice']*0.02))/10)*10);
        }else{
            return $priceArray['resultPrice'];
        }
        
    }

    function getDateRoomPrice($mpIdx, $pprIdx, $revDate, $revDay){
        $basicPrice = 0;
        $resultPrice = 0;
        
        $revDateArray = explode("-", $revDate);
        for($i=0; $i< $revDay; $i++){
            $date = date('Y-m-d', mktime(0, 0, 0, $revDateArray[1], $revDateArray[2]+$i, $revDateArray[0]));
            
            $dateObj = new DateTime($date);
            $numOfWeek = $dateObj->format('N');
            if($numOfWeek < 5){
                $numOfWeek = "1";
            }
            
            $query = "SELECT
                            CASE WHEN peIdx THEN
                                CASE peDay
                                    WHEN '1' THEN ppdpDay1
                                    WHEN '5' THEN ppdpDay5
                                    WHEN '6' THEN ppdpDay6
                                    WHEN '7' THEN ppdpDay7
                                ELSE
                                    ppdpDay".$numOfWeek."
                                END
                            ELSE
                                ppdpDay".$numOfWeek."
                            END AS basicPrice,
                            CASE WHEN peIdx THEN
                                CASE peDay
                                    WHEN '1' THEN ppdpSaleDay1
                                    WHEN '5' THEN ppdpSaleDay5
                                    WHEN '6' THEN ppdpSaleDay6
                                    WHEN '7' THEN ppdpSaleDay7
                                ELSE
                                    ppdpSaleDay".$numOfWeek."
                                END
                            ELSE
                                ppdpSaleDay".$numOfWeek."
                            END AS resultPrice
                    FROM pensionPrice AS PP
                    LEFT JOIN pensionException AS PE ON PE.mpIdx = PP.mpIdx AND PE.peSetDate = '".$date."' AND PE.peUseFlag = 'Y'
                    WHERE PP.pprIdx = '".$pprIdx."' AND '".$date."' BETWEEN PP.ppdpStart AND PP.ppdpEnd
                    HAVING resultPrice > 0
                    ORDER BY resultPrice ASC";
            $priceArray = $this->db->query($query)->row_array();
            
            $basicPrice = $basicPrice + $priceArray['basicPrice'];
            $resultPrice = $resultPrice + $priceArray['resultPrice'];
            
        }
        
        $result = array('basicPrice' => $basicPrice, 'resultPrice' => $resultPrice);
        
        return $result;
    }
}

?>