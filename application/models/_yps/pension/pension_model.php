<?php
class Pension_model extends CI_Model {
    function __construct() {
        parent::__construct();
        $CI =& get_instance();
        $CI->SV102 =& $this->load->database('YP', TRUE);
    }

    // ****************************************************** 메인 *********************************************************
    public function mainEventBanner(){  //  메인 이벤트 배너
        $this->SV102->select('amebIdx,amebFilename,amebContent');
        $this->SV102->from('pensionDB.appMainEventBanner');
        $this->SV102->where('amebOpen','1');        
        $this->SV102->order_by('', 'random');
        $this->SV102->limit(1);
        return $this->SV102->get();
    }

    public function mainTopBanner(){   // 기획전 배너
        $schQuery = "   SELECT amtbIdx,amtbTitle,amtbFilename1 as amtbFilename, amtbBannerFlag, amtbReturnVal, amtbWidth, amtbHeight
                        FROM pensionDB.appMainTopBanner
                        WHERE 1=1 AND amtbOpen = '1' ";
        if(isset($_SERVER['HTTP_USER_AGENT'])){
            if(preg_match('/(iPod|iPhone|iPad)/', $_SERVER['HTTP_USER_AGENT'])){
                $schQuery .= " AND amtbIdx != '157'";
            }
        }
        $schQuery .= "  
                        AND '".date('Y-m-d')."' BETWEEN amtbStartDate AND amtbEndDate
                        ORDER BY amtbSort ASC, rand()
                        ";
        $result = $this->SV102->query($schQuery)->result_array();
        return $result;
    }
    
    public function mainLolBanner(){    // 기획전 배너
        $schQuery = "   SELECT amlbIdx,amlbTitle,amlbFilename1 as amlbFilename, amlbReturnVal, amlbBannerFlag
                        FROM pensionDB.appMainLolBanner
                        WHERE amlbOpen = '1'";
        if($_SERVER['REMOTE_ADDR'] != YAPEN_SALE_EVENT_TEST_IP){
        	$schQuery .= "AND '".date('Y-m-d')."' BETWEEN amlbStartDate AND amlbEndDate";
		}	
                        
        $schQuery .= "            
                        ORDER BY amlbSort ASC, rand()
                        ";
        $result = $this->SV102->query($schQuery)->result_array();
        return $result;
    }

    public function mainLocBanner(){    // 인기지역 추천 펜션
        $schQuery = "   SELECT amlbIdx,amlbName,amlbContent,amlbColor,amlbColorF
                        FROM pensionDB.appMainLocBanner
                        WHERE amlbOpen = '1'
                        AND '".date('Y-m-d')."' BETWEEN amlbStartDate AND amlbEndDate
                        ORDER BY amlbSort ASC, rand()
                        ";
        $result = $this->SV102->query($schQuery)->result_array();
        /*
        $this->SV102->select('amlbIdx,amlbName,amlbContent,amlbColor,amlbColorF');
        $this->SV102->where('amlbOpen !=',0);
        $this->SV102->order_by('amlbSort asc, rand()');

        $result = $this->SV102->get('pensionDB.appMainLocBanner')->result_array();
        */
        return $result;
    }

    public function mainLocBannerTitle(){   // 인기지역 추천 펜션

        return $this->SV102->get('pensionDB.appMainLocBannerTitle')->row()->amlbtTitle;
    }
    // ****************************************************** 메인 *********************************************************


    // ****************************************************** 인기배너 리스트 *********************************************************

    public function topBannerBanner($idx){  // 메인 인기지역 배너
        return $this->SV102->select('amtbFilename2 as amtbFilename, amtbWidth, amtbHeight')->where('amtbIdx', $idx)->get('pensionDB.appMainTopBanner')->row_array();
    }
    
    public function lolBannerBanner($idx){ // 메인 인기지역 배너
        return $this->SV102->select('amlbFilename2 as amlbFilename')->where('amlbIdx', $idx)->get('pensionDB.appMainLolBanner')->row_array();
    }

    public function topBannerList($data){  // 메인 인기지역 리스트   //기획전 밑의 배너 클릭시 list
        $date = date('Y-m-d');
            
        $dateObj = new DateTime($date);
        $dayNum = $dateObj->format('N');
        if($dayNum < 5){
            $dayNum = "1";
        }
        
        $schQuery = "   SELECT COUNT(*) AS totalCnt FROM appMainTopBannerJoin WHERE amtbIdx = '".$data['idx']."'";
        $cntArray = $this->SV102->query($schQuery)->row_array();
        if($cntArray['totalCnt'] == 0){
            $result['count'] = NULL;
        }else{
            $result['count'] = $cntArray['totalCnt'];
        }
        
        /*
        $schQuery = "   SELECT MPS.mpIdx, MPS.mpsAddr1, PPB.ppbImage, MPS.mpsName, PPB.ppbWantCnt, PPB.ppbReserve, PPB.ppbEventFlag, MIN(PPDP.ppdpSaleDay".$dayNum.") AS price, MAX(PPDP.ppdpPercent".$dayNum.") AS percent, AMTB.amtbWidth, AMTB.amtbHeight
                        FROM appMainTopBannerJoin AS MTB
                        LEFT JOIN appMainTopBanner AS AMTB ON MTB.amtbIdx = AMTB.amtbIdx
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = MTB.mpIdx AND MPS.mmType ='YPS' AND MPS.mpType = 'PS'
                        LEFT JOIN placePensionBasic AS PPB ON MTB.mpIdx = PPB.mpIdx
                        LEFT JOIN pensionPrice AS PPDP ON PPDP.mpIdx = MTB.mpIdx AND PPDP.ppdpSaleDay".$dayNum." > 0 AND PPDP.ppdpPercent".$dayNum." < 100 AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                        WHERE MTB.amtbIdx = '".$data['idx']."'
                        AND MPS.mpsOpen = '1'
                        GROUP BY PPDP.mpIdx
                        ORDER BY MTB.amtbSort ASC, RAND()";
        $result['query'] = $this->SV102->query($schQuery)->result_array();
        
       	*/
        $schQuery = "   SELECT TT.*
                        FROM (
                            SELECT MPS.mpIdx, MPS.mpsAddr1, PPB.ppbImage, MPS.mpsName, PPB.ppbWantCnt, PPB.ppbReserve, PPB.ppbEventFlag, AMTB.amtbWidth, AMTB.amtbHeight, AMTB.amtbSort,
							IFNULL(PTS.ptsSale,0) AS ptsSale,
                            CASE WHEN peIdx THEN
                                CASE peDay
                                    WHEN '1' THEN ppdpSaleDay1/100*(100-IFNULL(PTS.ptsSale,0))
                                    WHEN '5' THEN ppdpSaleDay5/100*(100-IFNULL(PTS.ptsSale,0))
                                    WHEN '6' THEN ppdpSaleDay6/100*(100-IFNULL(PTS.ptsSale,0))
                                    WHEN '7' THEN ppdpSaleDay7/100*(100-IFNULL(PTS.ptsSale,0))
                                ELSE
                                    ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0))
                                END
                            ELSE
                                ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0))
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
                            FROM appMainTopBannerJoin AS MTB
                            LEFT JOIN appMainTopBanner AS AMTB ON MTB.amtbIdx = AMTB.amtbIdx
                            LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = MTB.mpIdx AND MPS.mmType ='YPS' AND MPS.mpType = 'PS'
                            LEFT JOIN placePensionBasic AS PPB ON MTB.mpIdx = PPB.mpIdx
                            LEFT JOIN placePensionRoom AS PPR ON PPR.mpIdx = PPB.mpIdx AND PPR.pprOpen = '1'
                            LEFT JOIN pensionPrice AS PPDP ON PPDP.pprIdx = PPR.pprIdx AND PPDP.ppdpSaleDay".$dayNum." > 0 AND PPDP.ppdpPercent".$dayNum." < 100 AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                            LEFT JOIN pensionException AS PE ON PE.mpIdx = PPDP.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                            LEFT JOIN pensionTodaySale AS PTS ON PTS.mpIdx = PPR.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PPR.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$dayNum." = '1'
                            WHERE MTB.amtbIdx = '".$data['idx']."'
                            AND MPS.mpsOpen = '1'
                            AND '".date('Y-m-d')."' BETWEEN MTB.amtbStart AND MTB.amtbEnd 
                            ORDER BY price ASC
                        ) AS TT
                        GROUP BY TT.mpIdx
                        ORDER BY TT.amtbSort ASC, RAND()
                ";
                
        $result['query'] = $this->SV102->query($schQuery)->result_array();
        if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
            //echo "<pre>".$this->SV102->last_query()."</pre>";
        }
        /*
        $this->SV102->start_cache();
        $this->SV102->where('MTB.amtbIdx', $data['idx']);
        $this->SV102->where('PS.mmType', 'YPS');   // 타입
        $this->SV102->where('PS.mpType', 'PS');    // 타입
        $this->SV102->join('pensionDB.mergePlaceSite PS', 'MTB.mpIdx = PS.mpIdx');
        $this->SV102->join('pensionDB.placePensionBasic PB', 'MTB.mpIdx = PB.mpIdx');
        $this->SV102->where('PS.mpsOpen > ', '0'); // 게시
        $this->SV102->stop_cache();

        $result['count'] = $this->SV102->count_all_results('pensionDB.appMainTopBannerJoin MTB');

        $this->SV102->order_by('MTB.amtbSort', 'asc');
        $this->SV102->order_by('rand()');
        $this->SV102->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PB.ppbImage,PB.ppbRoomMin,PB.ppbReserve, PB.ppbEventFlag');
        $result['query'] = $this->SV102->get('pensionDB.appMainTopBannerJoin MTB', $data['limit'], $data['offset'])->result_array();
        if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
            //echo $this->SV102->last_query();
        }
            
        
        foreach ( $result['query'] as $key => &$value )
        {
            // R:실시간예약,G:일반예약,T:전화문의
            switch ( $value['ppbReserve'] )
            {
                case 'R' :
                    $value['ppbReserve'] = 'R';
                break;
                case 'G' :
                    $value['ppbReserve'] = 'G';
                break;
                case 'T' :
                default :
                    $value['ppbReserve'] = 'T';
                break;
            }
        }
        
        

        $this->SV102->flush_cache();
        */
        return $result;
    }

    public function lolBannerList($data){   // 메인 인기지역 리스트
        $this->SV102->start_cache();
        $this->SV102->where('MLB.amlbIdx', $data['idx']);
        $this->SV102->where('PS.mmType', 'YPS');   // 타입
        $this->SV102->where('PS.mpType', 'PS');    // 타입
        $this->SV102->join('pensionDB.mergePlaceSite PS', 'MLB.mpIdx = PS.mpIdx');
        $this->SV102->join('pensionDB.placePensionBasic PB', 'MLB.mpIdx = PB.mpIdx');
        $this->SV102->where('PS.mpsOpen > ', '0'); // 게시
        $this->SV102->stop_cache();

        $result['count'] = $this->SV102->count_all_results('pensionDB.appMainLolBannerJoin MLB');

        $this->SV102->order_by('MLB.amlbSort', 'asc');
        $this->SV102->order_by('PS.mpIdx', 'desc');
        $this->SV102->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PB.ppbImage,PB.ppbRoomMin,PB.ppbReserve, PB.ppbEventFlag');
        $result['query'] = $this->SV102->get('pensionDB.appMainLolBannerJoin MLB', $data['limit'], $data['offset'])->result_array();
        
            
        
        foreach ( $result['query'] as $key => &$value )
        {
            // R:실시간예약,G:일반예약,T:전화문의
            switch ( $value['ppbReserve'] )
            {
                case 'R' :
                    $value['ppbReserve'] = 'R';
                break;
                case 'G' :
                    $value['ppbReserve'] = 'G';
                break;
                case 'T' :
                default :
                    $value['ppbReserve'] = 'T';
                break;
            }
        }
        
        

        $this->SV102->flush_cache();
        return $result;
    }

    // ****************************************************** 인기배너 리스트 *********************************************************


    // ****************************************************** 인기지역 리스트 *********************************************************

    public function locBannerList($data){   // 메인 인기지역 리스트
        $this->SV102->start_cache();
        $this->SV102->where('MLB.amlbIdx', $data['idx']);
        $this->SV102->where('PS.mmType LIKE "%YPS%"');
        $this->SV102->where('PS.mpType', 'PS');
        $this->SV102->where('PS.mpsOpen > ', '0');
        $this->SV102->join('pensionDB.mergePlaceSite PS', 'MLB.mpIdx = PS.mpIdx');
        $this->SV102->join('pensionDB.placePensionBasic PB', 'MLB.mpIdx = PB.mpIdx');
        $this->SV102->stop_cache();
        $result['count']    = $this->SV102->count_all_results('pensionDB.appMainLocBannerJoin MLB');

        $this->SV102->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PB.ppbImage,PB.ppbRoomMin, PB.ppbReserve');
        $this->SV102->order_by('MLB.viewSort', 'asc');
        $this->SV102->order_by('rand()');
        $result['obj']      = $this->SV102->get('pensionDB.appMainLocBannerJoin MLB', $data['limit'], $data['offset'] );
        $this->SV102->flush_cache();
        
        return $result;
    }

    // ****************************************************** 인기지역 리스트 *********************************************************
    
    

    // ************************************************** 펜션>지역>인기/일반지역 *****************************************************
    public function getThemePlaceCategory() {
        $popLists = array();
        $popIdx = 0;
        /* 지역별 장소통계 START */
        $this->SV102->select('PT.mtIdx, count(MPS.mpIdx) AS cnt');
        $this->SV102->from('pensionDB.placeTheme PT');
        $this->SV102->join('pensionDB.mergePlaceSite MPS', 'PT.mpsIdx = MPS.mpsIdx', 'left');
        $this->SV102->where('MPS.mpsOpen', 1);
        $this->SV102->where('MPS.mmType','YPS');
        $this->SV102->where('MPS.mpType','PS');
        //테스트펜션 제외
        $this->SV102->where('MPS.mpsIdx !=','167538');
        $this->SV102->where('INSTR(PT.mtCode, "1.")');
        $this->SV102->group_by('PT.mtIdx');
        //$this->SV102->order_by('MT.mtSort','ASC');
        $result['cnt'] = $this->SV102->get()->result_array();
        
        $themePlaceCnt = array();
        foreach ( $result['cnt'] as $key => $value )
        {
            $themePlaceCnt[$value['mtIdx']] = $value['cnt'];
        }
        /* 지역별 장소통계 END */
        
        
        
        
        $themePlaceList = array();
        
        /* 공통 쿼리 START */
        $this->SV102->start_cache();
        $this->SV102->from('pensionDB.mergeTheme MT');
        $this->SV102->where('INSTR(MT.mtCode, "1.")');
        $this->SV102->where('INSTR(MT.mtSite, "YPS")');
        $this->SV102->where('MT.mtType','PS');
        $this->SV102->where('MT.mtOpen', 1);
        //테스트펜션 제외
        //$this->SV102->where('MT.mpsIdx !=','167538');
        $this->SV102->stop_cache();
        $this->SV102->order_by('MT.mtSort','asc');
        /* 공통 쿼리 END */       
        
        
        /* 전체지역 START */
        $this->SV102->where('MT.mtDepth', 2);
        $result['lists2Dep'] = $this->SV102->get()->result_array();
        //echo $this->SV102->last_query();
        
        foreach ( $result['lists2Dep'] as $key => $value )
        {
            $lists3Dep = array();
            $lists3DepCnt[$key] = 0;
            $this->SV102->where('INSTR(MT.mtCode, "'.$value['mtCode'].'")');
            $this->SV102->where('MT.mtDepth', 3);
            $this->SV102->order_by('MT.mtSort','ASC');
            $result['lists3Dep'] = $this->SV102->get()->result_array();
            
            foreach ( $result['lists3Dep'] as $k => $v )
            {
                //등록된 펜션이 0개면 아예 뿌려주지 않음
                if ( ! isset($themePlaceCnt[$v['mtIdx']]) )
                {
                    continue;
                    //$themePlaceCnt[$v['mtIdx']] = '0';
                }
                
                $lists3DepCnt[$key] += $themePlaceCnt[$v['mtIdx']];
            
                $lists3Dep[] = array(
                    'code' => $v['mtCode'],
                    'name' => $v['mtName'],
                    'count' => (string)$themePlaceCnt[$v['mtIdx']],
                    'popularity' => ( $v['mtFavorite'] == 1 ) ? '1' : '0'
                );
            }

            //하위뎁스에 있는 지역에 펜션이 한개도 없으면 목록에 뿌려주지 않음
            if( $lists3DepCnt[$key] == 0 ) continue;
            
            $themePlaceList[] = array(
                'code' => $value['mtCode'], 
                'locname' => $value['mtName'], 
                'tntcnt' => (string)$lists3DepCnt[$key],
                'popularity' => ( $value['mtFavorite'] == 1 ) ? '1' : '0',
                'lists' => $lists3Dep
            );
        }
        /* 전체지역 END */
        
        $this->SV102->flush_cache();
        
        return $themePlaceList;
    }

    // public function getLocationTheme_1() {
        // $result = array();
        // $popLists = array();
        // $countNotArray = array('1.009003');
//         
        // $schQuery = "   SELECT MT.mtCode, MT.mtName, MT.mtSort, MT.mtOpen, MT.mtFavorite
                        // FROM mergeTheme AS MT
                        // WHERE MT.mtSite LIKE '%YPS%'
                        // AND MT.mtType = 'PS'
                        // AND LENGTH(MT.mtCode) = 5
                        // AND MT.mtCode LIKE '1%'
                        // AND MT.mtOpen = '1'
                        // ORDER BY MT.mtSort ASC";
        // $mainLists = $this->SV102->query($schQuery)->result_array();
        // $i=0;
        // $p=0;
        // /*
          // * (2016-03-30)
          // * 지역개수 불일치 수정
          // * 수정자 : 이유진
         // * 
         // * 2016-04-15
         // * 잘 되는거 이유진이 잘못 바궈서 버그생김. 그래서다시 원복함
         // * 수정자 : 김영웅
        // */
        // foreach($mainLists as $mainLists){
            // /*
            // $subQuery = "   SELECT *, COUNT(mpIdx) AS cnt FROM ( 
                                // SELECT MT.mtCode, MT.mtName, MT.mtSort, MT.mtOpen, MT.mtFavorite, MPS.mpIdx
                                // FROM mergeTheme AS MT 
                                // LEFT JOIN placeTheme AS PT ON MT.mtCode = PT.mtCode 
                                // LEFT JOIN mergePlaceSite AS MPS ON MPS.mpsIdx = PT.mpsIdx AND MPS.mmType ='YPS' AND mtType LIKE '%PS%' AND MPS.mpsOpen = '1'
                                // WHERE MT.mtSite LIKE '%YPS%' 
                                // AND MT.mtType LIKE '%PS%' 
                                // AND MT.mtCode LIKE '1.%' 
                                // AND LENGTH(MT.mtCode) = '8' 
                                // AND MT.mtOpen = '1' 
                                // AND SUBSTR(MT.mtCode,1,5) = '".$mainLists['mtCode']."'
                                // AND MPS.mpsOpen = '1' ";
            // if($mainLists['mtCode'] == '1.009'){ 
                // $subQuery .=    "GROUP BY PT.mpsIdx";
            // }                       
            // $subQuery .=    ") AS AA
                            // GROUP BY AA.mtCode
                            // ORDER BY AA.mtSort ASC";
            // */
//             
            // $subQuery = "   SELECT TT.mtCode, TT.mtName, TT.mtSort, TT.mtOpen, TT.mtFavorite, COUNT(TT.mpIdx) AS cnt FROM (
                                // SELECT PT.mtCode, MT.mtName, MT.mtSort, MT.mtOpen, MT.mtFavorite, MPS.mpIdx, MPS.mpsIdx
                                // FROM placeTheme AS PT
                                // LEFT JOIN mergePlaceSite AS MPS ON MPS.mpsIdx = PT.mpsIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'
                                // LEFT JOIN mergeTheme AS MT ON MT.mtType = 'PS' AND MT.mtSite LIKE '%YPS%' AND PT.mtCode = MT.mtCode
                                // WHERE MT.mtOpen = '1'
                                // AND PT.mtCode LIKE '".$mainLists['mtCode']."%'
                                // AND MPS.mpsOpen = '1'
                                // AND LENGTH(PT.mtCode) = 8
                            // ) AS TT
                            // GROUP BY TT.mtCode
                            // ORDER BY TT.mtSort ASC";
            // $subLists = $this->SV102->query($subQuery)->result_array();
//             
            // $result['lists'][$i]['lists'] = array();
            // $j=0;            
            // $subCount = 0;
            // foreach($subLists as $subLists){
                // if($subLists['cnt'] == 0){
                    // continue;
                // }
                // $result['lists'][$i]['lists'][$j]['code'] = $subLists['mtCode'];
                // $result['lists'][$i]['lists'][$j]['name'] = $subLists['mtName'];
                // $result['lists'][$i]['lists'][$j]['count'] = $subLists['cnt']."";
                // $result['lists'][$i]['lists'][$j]['popularity'] = ( $subLists['mtFavorite'] == 1 ) ? '1' : '0';
                // if(!in_array($subLists['mtCode'],$countNotArray)){
                    // $subCount = $subCount+$subLists['cnt'];
                // }
                // if($result['lists'][$i]['lists'][$j]['popularity'] == "1"){
                    // $result['popLists'][$p]['code'] = $subLists['mtCode'];
                    // $result['popLists'][$p]['name'] = $subLists['mtName'];
                    // $result['popLists'][$p]['count'] = $subLists['cnt']."";
                    // $result['popLists'][$p]['popularity'] = $result['lists'][$i]['lists'][$j]['popularity'];
                    // $p++;
                // }
                // $j++;
            // }
            // if($subCount > 0){
                // $result['lists'][$i]['code'] = $mainLists['mtCode'];
                // $result['lists'][$i]['locname'] = $mainLists['mtName'];
                // $result['lists'][$i]['tntcnt'] = $subCount."";
                // $result['lists'][$i]['popularity'] = ( $mainLists['mtFavorite'] == 1 ) ? '1' : '0';
                // $i++;
            // }
        // }
//         
        // return $result;
    // }

    public function getLocationTheme() {
        $result = array();
        $popLists = array();
        $countNotArray = array('1.009003','1.015001','1.009001','1.009002');
        
        $schQuery = "   SELECT MT.mtCode, MT.mtName, MT.mtSort, MT.mtOpen, MT.mtFavorite
                        FROM mergeTheme AS MT
                        WHERE MT.mtSite = 'YPS'
                        AND MT.mtType = 'PS'
                        AND LENGTH(MT.mtCode) = 5
                        AND MT.mtCode LIKE '1%'
                        AND MT.mtOpen = '1'
                        ORDER BY MT.mtSort ASC";
        $mainLists = $this->SV102->query($schQuery)->result_array();
        $i=0;
        $p=0;
        /*
          * (2016-03-30)
          * 지역개수 불일치 수정
          * 수정자 : 이유진
         * 
         * 2016-04-15
         * 잘 되는거 이유진이 잘못 바꿔서 버그생김. 그래서다시 원복함
         * 수정자 : 김영웅
         * 
         * 2016-06-15
         * 여전히 안맞아서 다시수정
         * 원인 : 금액값 0일때 제외 안함
         * 수정자 : 이유진
         * 
         * 2016-08-11
         * 세부 지역에 대해 카운팅값 신경 X
         * 대표님 승인하에 강제로 다 불러오도록 변경
         * 금액은 0원 이상 반드시로 다시 변경
         * 무조껀 카운팅 (지역 x 펜션수)
         * 수정자 : 김영웅
        */
        $result['popLists'] = array();
        foreach($mainLists as $mainLists){
            $subQuery = "   SELECT TT.mtCode, TT.mtName, TT.mtSort, TT.mtOpen, TT.mtFavorite, COUNT(TT.mpIdx) AS cnt
                            FROM (
                                SELECT PT.mtCode, MT.mtName, MT.mtSort, MT.mtOpen, MT.mtFavorite, MPS.mpIdx, MPS.mpsIdx ,MAX(PP.ppdpSaleDay1) AS salePrice
                                FROM placeTheme AS PT 
                                LEFT JOIN mergePlaceSite AS MPS ON MPS.mpsIdx = PT.mpsIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'
                                LEFT JOIN mergeTheme AS MT ON MT.mtType = 'PS' AND MT.mtSite = 'YPS' AND PT.mtCode = MT.mtCode
                                LEFT JOIN pensionPrice AS PP ON PP.mpIdx = MPS.mpIdx AND '".date('Y-m-d')."' BETWEEN PP.ppdpStart AND PP.ppdpEnd
                                WHERE MT.mtOpen = '1'
                                AND PT.mtCode LIKE '".$mainLists['mtCode']."%'
                                AND MPS.mpsOpen = '1'
                                AND LENGTH(PT.mtCode) = 8
                                GROUP BY PT.mtCode, MPS.mpIdx
                            ) AS TT
                            WHERE TT.salePrice > 0
                            GROUP BY TT.mtCode
                            ORDER BY TT.mtSort ASC";
                                
            $subLists = $this->SV102->query($subQuery)->result_array();
            
            if(count($subLists) > 0){
            	$result['lists'][$i]['lists'] = array();
	            $j=0;            
	            $subCount = 0;
	            foreach($subLists as $subLists){
	                if($subLists['cnt'] == 0){
	                    continue;
	                }
	                $result['lists'][$i]['lists'][$j]['code'] = $subLists['mtCode'];
	                $result['lists'][$i]['lists'][$j]['name'] = $subLists['mtName'];
	                $result['lists'][$i]['lists'][$j]['count'] = $subLists['cnt']."";
	                $result['lists'][$i]['lists'][$j]['popularity'] = ( $subLists['mtFavorite'] == 1 ) ? '1' : '0';
	                if(!in_array($subLists['mtCode'],$countNotArray)){
	                    $subCount = $subCount+$subLists['cnt'];
	                }
	                if($result['lists'][$i]['lists'][$j]['popularity'] == "1"){
	                    $result['popLists'][$p]['code'] = $subLists['mtCode'];
	                    $result['popLists'][$p]['name'] = $subLists['mtName'];
	                    $result['popLists'][$p]['count'] = $subLists['cnt']."";
	                    $result['popLists'][$p]['popularity'] = $result['lists'][$i]['lists'][$j]['popularity'];
	                    $p++;
	                }
	                $j++;
	            }
	            if($subCount > 0){
	                $result['lists'][$i]['code'] = $mainLists['mtCode'];
	                $result['lists'][$i]['locname'] = $mainLists['mtName'];
	                $result['lists'][$i]['tntcnt'] = $subCount."";
	                $result['lists'][$i]['popularity'] = ( $mainLists['mtFavorite'] == 1 ) ? '1' : '0';
	                $i++;
	            }
            }
            
        }
        
        return $result;
    }

    // ************************************************** 펜션>일반예약/지역정보 *****************************************************
    public function getThemePlaceCategoryNormal() {
            
        /* 지역별 장소통계 START */
        $this->SV102->select('PT.mtIdx, count(MPS.mpIdx) AS cnt');
        $this->SV102->from('pensionDB.placeTheme PT');
        $this->SV102->join('pensionDB.mergePlaceSite MPS', 'PT.mpsIdx = MPS.mpsIdx', 'left');
        $this->SV102->join('pensionDB.placePensionBasic PPB', 'MPS.mpIdx = PPB.mpIdx', 'left');
        $this->SV102->where('MPS.mpsOpen', 1);
        $this->SV102->where('MPS.mmType','YPS');
        $this->SV102->where('MPS.mpType','PS');
        $this->SV102->where('PPB.ppbReserve','G');
        $this->SV102->where('INSTR(PT.mtCode, "1.")');
        $this->SV102->group_by('PT.mtIdx');
        $result['cnt'] = $this->SV102->get()->result_array();
        
        $themePlaceCnt = array();
        foreach ( $result['cnt'] as $key => $value )
        {
            $themePlaceCnt[$value['mtIdx']] = $value['cnt'];
        }
        /* 지역별 장소통계 END */
        
        
        $themePlaceList = array();
        
        /* 공통 쿼리 START */
        $this->SV102->start_cache();
        $this->SV102->from('pensionDB.mergeTheme MT');
        $this->SV102->where('INSTR(MT.mtCode, "1.")');
        $this->SV102->where('INSTR(MT.mtSite, "YPS")');
        $this->SV102->where('MT.mtType','PS');
        $this->SV102->where('MT.mtOpen', 1);
        $this->SV102->stop_cache();
        $this->SV102->order_by('MT.mtSort','asc');
        /* 공통 쿼리 END */

        /* 전체지역 START */
        $this->SV102->where('MT.mtDepth', 2);
        $result['lists2Dep'] = $this->SV102->get()->result_array();
        
        foreach ( $result['lists2Dep'] as $key => $value )
        {
            $lists3Dep = array();
            $lists3DepCnt[$key] = 0;
            $this->SV102->where('INSTR(MT.mtCode, "'.$value['mtCode'].'")');
            $this->SV102->where('MT.mtDepth', 3);
            $result['lists3Dep'] = $this->SV102->get()->result_array();
            
            foreach ( $result['lists3Dep'] as $k => $v )
            {
                //등록된 펜션이 0개면 아예 뿌려주지 않음
                if ( ! isset($themePlaceCnt[$v['mtIdx']]) )
                {
                    continue;
                    //$themePlaceCnt[$v['mtIdx']] = '0';
                }
                
                $lists3DepCnt[$key] += $themePlaceCnt[$v['mtIdx']];
            
                $lists3Dep[] = array(
                    'code' => $v['mtCode'],
                    'name' => $v['mtName'],
                    'count' => (string)$themePlaceCnt[$v['mtIdx']],
                    'popularity' => ( $v['mtFavorite'] == 1 ) ? '1' : '0'
                );
            }

            //하위뎁스에 있는 지역에 펜션이 한개도 없으면 목록에 뿌려주지 않음
            if( $lists3DepCnt[$key] == 0 ) continue;
                
            $themePlaceList[] = array(
                'code' => $value['mtCode'], 
                'locname' => $value['mtName'], 
                'tntcnt' => (string)$lists3DepCnt[$key],
                'popularity' => ( $value['mtFavorite'] == 1 ) ? '1' : '0',
                'lists' => $lists3Dep
            );
            
            
        }
        /* 전체지역 END */
        
        $this->SV102->flush_cache();
        
        
        return $themePlaceList;
    }

    // ************************************************** 펜션>실시간조회>일반지역 *****************************************************
    public function getRThemePlaceCategory() {
            
        /* 지역별 장소통계 START */
        $this->SV102->select('PT.mtIdx, count(MPS.mpIdx) AS cnt');
        $this->SV102->from('pensionDB.placeTheme PT');
        $this->SV102->join('pensionDB.mergePlaceSite MPS', 'PT.mpsIdx = MPS.mpsIdx', 'left');
        $this->SV102->join('pensionDB.placePensionBasic PPB', 'MPS.mpIdx = PPB.mpIdx', 'left');
        $this->SV102->where('MPS.mpsOpen', 1);
        $this->SV102->where('MPS.mmType','YPS');
        $this->SV102->where('MPS.mpType','PS');
        $this->SV102->where('INSTR(PT.mtCode, "1.")');
        $this->SV102->where('PPB.ppbReserve','R');
        $this->SV102->group_by('PT.mtIdx');
        $result['cnt'] = $this->SV102->get()->result_array();

        $themePlaceCnt = array();
        foreach ( $result['cnt'] as $key => $value )
        {
            $themePlaceCnt[$value['mtIdx']] = $value['cnt'];
        }
        /* 지역별 장소통계 END */
        
        
        
        
        $themePlaceList = array();
        
        /* 공통 쿼리 START */
        $this->SV102->start_cache();
        $this->SV102->from('pensionDB.mergeTheme MT');
        $this->SV102->where('INSTR(MT.mtCode, "1.")');
        $this->SV102->where('INSTR(MT.mtSite, "YPS")');
        $this->SV102->where('MT.mtType','PS');
        $this->SV102->where('MT.mtOpen', 1);
        $this->SV102->stop_cache();
        $this->SV102->order_by('MT.mtSort','asc');
        /* 공통 쿼리 END */
        
        /* 전체지역 START */
        $this->SV102->where('MT.mtDepth', 2);
        $result['lists2Dep'] = $this->SV102->get()->result_array();
        
        
        foreach ( $result['lists2Dep'] as $key => $value )
        {
            $lists3Dep = array();
            $lists3DepCnt[$key] = 0;
            $this->SV102->where('INSTR(MT.mtCode, "'.$value['mtCode'].'")');
            $this->SV102->where('MT.mtDepth', 3);
            $this->SV102->order_by('MT.mtSort','ASC');
            $result['lists3Dep'] = $this->SV102->get()->result_array();
            
            foreach ( $result['lists3Dep'] as $k => $v )
            {
                //등록된 펜션이 0개면 아예 뿌려주지 않음
                if ( ! isset($themePlaceCnt[$v['mtIdx']]) )
                {
                    continue;
                    //$themePlaceCnt[$v['mtIdx']] = '0';
                }
                
                $lists3DepCnt[$key] += $themePlaceCnt[$v['mtIdx']];
            
                $lists3Dep[] = array(
                    'code' => $v['mtCode'],
                    'name' => $v['mtName'],
                    'count' => (string)$themePlaceCnt[$v['mtIdx']],
                    'popularity' => ( $v['mtFavorite'] == 1 ) ? '1' : '0'
                );
            }

            //하위뎁스에 있는 지역에 펜션이 한개도 없으면 목록에 뿌려주지 않음
            if( $lists3DepCnt[$key] == 0 ) continue;
                
            $themePlaceList[] = array(
                'code' => $value['mtCode'], 
                'locname' => $value['mtName'], 
                'tntcnt' => (string)$lists3DepCnt[$key],
                'popularity' => ( $value['mtFavorite'] == 1 ) ? '1' : '0',
                'lists' => $lists3Dep
            );
            
            
        }
        /* 전체지역 END */
        
        $this->SV102->flush_cache();
        
        
        return $themePlaceList;
    }

    // 201405141450 pyh : mergeTheme 순서 컬럼을 이용하여 진행
    public function getThemePlaceList( $param ) {
        extract( $param ); 
        $this->SV102->start_cache();
        if( $param['favorite'] ) $this->SV102->where('MT.mtFavorite',(string)$param['favorite']);
        $this->SV102->where('INSTR(MT.mtCode, "'.$param['code'].'")');
        $this->SV102->where('INSTR(MT.mtSite, "YPS")');
        $this->SV102->where('MT.mtType','PS');
        $this->SV102->where('MT.mtDepth',$param['depth']);
        
        // 201405231435 pyh : 테마 재정리를 위해 추가, DB로 관리
        $this->SV102->where('MT.mtOpen','1');
        
        if( $param['depth'] == 3 ) $this->SV102->join('pensionDB.placeTheme PT', 'MT.mtIdx = PT.mtIdx');
        $this->SV102->stop_cache();

        if( $param['depth'] == 3 ) $this->SV102->join('pensionDB.mergePlaceSite MPS', 'PT.mpsIdx = MPS.mpsIdx');
        $result['count']    = $this->SV102->count_all_results('pensionDB.mergeTheme MT');

        $this->SV102->select('MT.*, COUNT(*) as sCnt');
        $this->SV102->group_by('MT.mtIdx');
        $this->SV102->order_by('MT.mtSort');
        $result['obj']      = $this->SV102->get('pensionDB.mergeTheme MT');

        $this->SV102->flush_cache();
        return $result;
    }
    // ************************************************** 펜션>지역>인기/일반지역 *****************************************************


    // ********************************************************* 펜션>테마 ************************************************************
    public function getThemeEtcList( $param ) {
        extract( $param ); 
        $this->SV102->start_cache();
        $this->SV102->where('MT.mtFavorite',(string)$param['favorite']);
        $this->SV102->where('MT.mtSite','YPS');
        $this->SV102->where('MT.mtType','DC');
        $this->SV102->where('MT.mtDepth','2');
        $this->SV102->join('pensionDB.placeTheme PT', 'MT.mtIdx = PT.mtIdx');
        $this->SV102->stop_cache();

        $this->SV102->join('pensionDB.mergePlaceSite MPS', 'PT.mpsIdx = MPS.mpsIdx');
        $result['count']    = $this->SV102->count_all_results('pensionDB.mergeTheme MT');

        $this->SV102->select('MT.*, COUNT(*) as sCnt');
        $this->SV102->group_by('MT.mtIdx');
        $result['obj']      = $this->SV102->get('pensionDB.mergeTheme MT');

        $this->SV102->flush_cache();
        return $result;
    }
    // ********************************************************* 펜션>테마 ************************************************************


    // ****************************************************** 렌덤배너 리스트 *********************************************************

    public function randomBannerList($data){
        $dayNum = date('N', strtotime(date('Y-m-d')));
        if($dayNum < 5){
            $dayNum = 1;
        }
        $countQuery = "  SELECT COUNT(*) AS cnt
                       FROM appRandomBanner AS MTB
                       LEFT JOIN pensionDB.mergePlaceSite PS ON MTB.mpIdx = PS.mpIdx AND PS.mmType ='YPS' AND PS.mpType LIKE '%PS%'
                       WHERE MTB.arbOpen = '1'
                       ORDER BY MTB.arbOrder DESC, RAND()";
        $countArray = $this->SV102->query($countQuery)->row_array();
        $result['count'] = $countArray['cnt'];
        
        
        $schQuery = "   SELECT PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PS.mpsAddrSi,PS.mpsAddrGun,MTB.arbFilename,MTB.arbTitle, MTB.arbWidth, MTB.arbHeight, PPB.ppbWantCnt, MTB.arbTag, PPB.ppbReserve, PPB.ppbOnline, 
                        IFNULL(PTS.ptsSale,0) AS ptsSale,
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
                        END AS basicPrice,
                        CASE WHEN peIdx THEN
                            CASE peDay
                                WHEN '1' THEN MIN(ppdpSaleDay1/100*(100-IFNULL(PTS.ptsSale,0)))
                                WHEN '5' THEN MIN(ppdpSaleDay5/100*(100-IFNULL(PTS.ptsSale,0)))
                                WHEN '6' THEN MIN(ppdpSaleDay6/100*(100-IFNULL(PTS.ptsSale,0)))
                                WHEN '7' THEN MIN(ppdpSaleDay7/100*(100-IFNULL(PTS.ptsSale,0)))
                            ELSE
                                MIN(ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
                            END
                        ELSE
                            MIN(ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
                        END AS resultPrice
                        FROM appRandomBanner AS MTB
                        LEFT JOIN pensionDB.mergePlaceSite PS ON MTB.mpIdx = PS.mpIdx AND PS.mmType = 'YPS' AND PS.mpType = 'PS'
                        LEFT JOIN pensionDB.placePensionBasic AS PPB ON PPB.mpIdx = MTB.mpIdx
                        LEFT JOIN pensionDB.pensionPrice AS PP ON PP.mpIdx = MTB.mpIdx AND '".date('Y-m-d')."' BETWEEN PP.ppdpStart AND PP.ppdpEnd
                        LEFT JOIN pensionDB.pensionException AS PE ON PE.mpIdx = MTB.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                        LEFT JOIN pensionTodaySale AS PTS ON PTS.mpIdx = PS.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PP.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$dayNum." = '1'
                        WHERE '".date('Y-m-d')."' BETWEEN MTB.arbStartDate AND MTB.arbEndDate
                        AND MTB.arbOpen = '1'
                        GROUP BY MTB.arbIdx
                        ORDER BY MTB.arbOrder DESC, RAND()";
        
        
        $result['query'] = $this->SV102->query($schQuery)->result_array();
        /*
        $this->SV102->start_cache();
        $this->SV102->where('PS.mmType', 'YPS');   // 타입
        $this->SV102->where('PS.mpType', 'PS');    // 타입
        $this->SV102->join('pensionDB.mergePlaceSite PS', 'MTB.mpIdx = PS.mpIdx', 'left');
        
        $this->SV102->where('MTB.arbOpen > ', '0');    // 게시
        $this->SV102->stop_cache();

        $result['count'] = $this->SV102->count_all_results('pensionDB.appRandomBanner MTB');

        $this->SV102->order_by('MTB.arbOrder', 'desc');
        $this->SV102->order_by('rand()');
        
        $this->SV102->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PS.mpsAddrSi,PS.mpsAddrGun,MTB.arbFilename,MTB.arbTitle');
        $result['query'] = $this->SV102->get('pensionDB.appRandomBanner MTB', $data['limit'], $data['offset'])->result_array();

        $this->SV102->flush_cache();
        */
        return $result;
    }

    // ****************************************************** 렌덤배너 리스트 *********************************************************



    public function reportInsert($data){ // 펜션 신고하기 등록

        $result = $this->db->insert('pensionDB.pensionReport', array(
            'mpIdx' => $data["mpIdx"],
            'mbIdx' => $data["mbIdx"],
            'prName' => urldecode($data["prName"]),
            'prPensionName' => urldecode($data["prPensionName"]),
            'prPensionAddress' => urldecode($data["prPensionAddress"]),
            'prContent' => urldecode($data["prContent"]),
            'prRegDate' => date("Y-m-d H:i:s")
        ));
    
        return $this->db->insert_id();
    }

    public function businessApplicationInsert($data){ // 펜션 무료등록
        if($data["baPensionName"] == str_replace(" ","", $data["baPensionName"])){
            $locData = "";
            $pensionName = $data['baPensionName'];
        }else{
            $locArray = explode(" ", $data['baPensionName']);
            $locData = $locArray[0];
            $pensionName = str_replace($locArray[0]." ","", $data['baPensionName']);
        }
         
        $result = $this->db->insert('pensionDB.businessApplication', array(
            'baLocation' => $locData,
            'baPensionName' => $pensionName,
            'baPicCheck' => $data["baPicCheck"],
            'baMobile' => $data["baMobile"],
            'baRegDate' => date("Y-m-d H:i:s"),
            'baSponsor' => $data['baSponsor']
        ));

        return $this->db->insert_id();
    }

    
    // *************************************************** 펜션(검색) 리스트 ******************************************************
    public function pensionSearchList($data){
            $holyQuery = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday WHERE hDate-INTERVAL 1 DAY = '".date('Y-m-d')."'";
            $holyRow = $this->SV102->query($holyQuery)->row_array();
            // 세일 펜션
            $salePensionSaleIdxs = NULL;
            if( isset($data['sale']) && $data['sale'] == 1 ) 
            {
                $salePensionSaleIdxs = $this->salePensionSaleIdxs();
            }
            
            $holiDayCheck = $this->holidayCheck(date('Y-m-d'));

            $this->SV102->start_cache();
            
            /* 위도, 경도 검색 START */
            $distance = FALSE;
            if ( isset($data['latitude']) && isset($data['longitude']) )
            {
                $latitude = $data['latitude'];
                $longitude = $data['longitude'];
                $this->SV102->select("
                    PS.mpIdx,PS.mpsName,PS.mpsAddr1,PS.mpsMapX,PS.mpsMapY, 
                    ( 6371 * acos( cos( radians($latitude) ) * cos( radians(mpsMapY) ) * cos( radians(mpsMapX) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians(mpsMapY) ) ) ) AS distance "
                );
                $this->SV102->having('distance <=', 5);
                $distance = TRUE;
            }
            /* 위도, 경도 검색 END */
            /*
            $qWhere = array();
            //if( empty( $data['themeCode'] ) && empty( $data['locCode'] ) ) $qWhere[] = 'MT.mtCode > "' .$data['code'] .'"';
            if( !empty( $data['locCode'] ) ) $qWhere[]  = 'MT.mtCode LIKE "'.$data['locCode'].'%"';
            if( !empty( $data['themeCode'] ) ){
                //$data['themeCode'] = str_replace(",","','",$data['themeCode']);
                $qWhere[]    = 'PT.mtCode IN ('.$data['themeCode'].')';
            }
            if( $data['search'] ) $qWhere[] = 'PS.mpsName LIKE "%'.$data['search'].'%"';
            
            if ( $distance == FALSE )
            {
                $qWhere[] = '1=2';
            }
            
            if ( count($qWhere) > 0 )
            {
                $qWhere = '(' . implode(' or ', $qWhere) . ')'; 
                $this->SV102->where( $qWhere );
            }           
*/
            //테스트펜션 제외
            if( !empty( $data['locCode'] ) ) $this->SV102->like('MT.mtCode',$data['locCode']);
            if( !empty( $data['themeCode'] )){
                if(substr($data['themeCode'],0,1) != "1"){
                    $data['themeCode'] = explode(",",$data['themeCode']);
                    //$this->SV102->where_in('PT.mtCode', $data['themeCode']);
                    
                    for($i=0; $i< count($data['themeCode']); $i++){
                        $this->SV102->where('PPTP.PS'.str_replace(".","",$data['themeCode'][$i]), 1);
                    }
                }                
            }
            
            $this->SV102->join('placeTheme PT', 'MT.mtIdx = PT.mtIdx');
            $this->SV102->join('mergePlaceSite PS', 'PT.mpsIdx = PS.mpsIdx');
            $this->SV102->join('placePensionBasic PB', 'PS.mpIdx = PB.mpIdx');
            $this->SV102->join('placePensionThemeFlag AS PPTP','PPTP.mpIdx = PB.mpIdx','LEFT');
            
            // 세일 펜션
            if ( is_array($salePensionSaleIdxs) && count($salePensionSaleIdxs) > 0 )
            {
                $this->SV102->where_in( 'PS.mpIdx', $salePensionSaleIdxs );
            }            
            $this->SV102->where('MT.mtSite','YPS');
            $this->SV102->where('MT.mtType','PS');
            $this->SV102->where('PS.mpsOpen > ', '0');
            
            if( $data['search'] && empty( $data['locCode'] )){
                $data['search'] = str_replace(" ","%",$data['search']);
                $this->SV102->where("(PS.mpsName LIKE '%".$data['search']."%' OR CONCAT(PS.mpsAddr1,' ',mpsAddr2) LIKE '%".$data['search']."%')","",false);
            }
            //random 처리시 예외 시킬 idx들
            if( count($data['idxStrings']) > 3 ){
                    $this->SV102->where_not_in('PS.mpIdx', $data['idxStrings']);
            }

            $this->SV102->group_by('PS.mpIdx');
            
            $this->SV102->stop_cache();

            $result['count']    = $this->SV102->get('mergeTheme MT')->num_rows();
            

            $this->SV102->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PB.ppbImage,PB.ppbRoomMin,PB.ppbReserve, PB.ppbWantCnt, PB.ppbEventFlag');
            $this->SV102->select('PB.ppbGrade');
            
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

            $this->SV102->select("(SELECT CASE WHEN HE.heIdx THEN ppdpSaleDay".$toNumOfWeek." ELSE ppdpSaleDay".$numOfWeek." END AS price
                FROM pensionPrice AS SPPDP
                LEFT JOIN holidayExclude AS HE ON SPPDP.mpIdx = HE.mpIdx AND HE.hIdx = '".$holyRow['hIdx']."'
                WHERE SPPDP.mpIdx = PS.mpIdx AND '".$date."' BETWEEN SPPDP.ppdpStart AND SPPDP.ppdpEnd
                HAVING price > 0 ORDER BY price ASC LIMIT 1) AS price", false);
            
            
            // 1 -> 조회 , 2 -> 가보고싶아요, 3 -> 신규, 4 -> 낮은가격순, 5 -> 높은가격순
            if($data['orderby'] == ""){
                $data['orderby'] = "1";
            } 
            if( $data['orderby'] ){
                switch ( $data['orderby'] )
                {
                    case ('1') :
                        $this->SV102->order_by('PB.ppbGrade', 'DESC');
                        $this->SV102->order_by('rand()');
                        break;
                    case ('2') :
                        $this->SV102->order_by('PB.ppbWantCnt', 'DESC');
                        $this->SV102->order_by('rand()');
                        break;
                    case ('4') :
                        $this->SV102->order_by('price', 'ASC');
                        $this->SV102->order_by('rand()');
                        break;
                    case ('5') :
                        $this->SV102->order_by('price', 'DESC');
                        $this->SV102->order_by('rand()');
                        break;
                    default :
                        $this->SV102->order_by('PB.ppbGrade', 'DESC');
                        $this->SV102->order_by('rand()');
                        break;
                }                
            }
            $this->SV102->order_by('PB.ppbGrade DESC, PB.ppbReserve', 'ASC');
            
            $this->SV102->having('price IS NOT NULL');
            
            $result['obj']      = $this->SV102->get('mergeTheme MT', $data['limit'], $data['offset'])->result();
            
            $this->SV102->flush_cache();
            //echo "<pre>".$this->SV102->last_query()."</pre>";
            
            foreach( $result['obj'] as $k => $o ) {
                $pensionPriceInfo = $this->pensionMinPrice($o->mpIdx);
                $result['obj'][$k]->ppbRoomMin  = $pensionPriceInfo->minPrice;  // 오늘의 펜션 최저가 요금
                $result['obj'][$k]->ppbRoomSales    = $pensionPriceInfo->maxSalePercent;    // 오늘의 펜션 최고 할인율
                
                
                // R:실시간예약,G:일반예약,T:전화문의
                switch ( $o->ppbReserve )
                {
                    case 'R' :
                        $result['obj'][$k]->ppbReserve = 'R';
                    break;
                    case 'G' :
                        $result['obj'][$k]->ppbReserve = 'G';
                    break;
                    case 'T' :
                    default :
                        $result['obj'][$k]->ppbReserve = 'T';
                    break;
                }
                
            }
            
            return $result;
    }
    // *************************************************** 펜션(검색) 리스트 ******************************************************
    private function findPensionRoomPriceQueryOrderBy($holiDayCheck){
        $date = date('Y-m-d');
        $dateObj = new DateTime($date);
        $numOfWeek = $dateObj->format('N');
        
        if(isset($holiDayCheck[$date])){    // 공휴일 날짜가 있을경우
            $numOfWeek = "6";
        }else{
            if($numOfWeek < 5){
                $numOfWeek = "1";
            }        
        }
        
        $str = "(SELECT ppdpSaleDay".$numOfWeek."
                FROM pensionPrice
                WHERE mpIdx = PS.mpIdx AND '".$date."' BETWEEN ppdpStart AND ppdpEnd
                AND ppdpSaleDay".$numOfWeek." > 0 ORDER BY ppdpSaleDay".$numOfWeek." ASC LIMIT 1) AS price";
        
        return $str;
    } 
    // *************************************************** 바로예약 리스트 ******************************************************
    public function getDirectList($data){
            //$this->output->enable_profiler(true);
            // 세일 펜션
            //echo var_dump($data);
            $salePensionSaleIdxs = NULL;
            if( isset($data['sale']) && $data['sale'] == 1 ) 
            {
                $salePensionSaleIdxs = $this->salePensionSaleIdxs();
            }
            

            $this->SV102->start_cache();
            
            $qWhere = array();
            //if( empty( $data['themeCode'] ) && empty( $data['locCode'] ) ) $qWhere[] = 'MT.mtCode > "' .$data['code'] .'"';
            if( !empty( $data['locCode'] ) ) $qWhere[]  = 'MT.mtCode LIKE "%'.$data['locCode'].'%"';
            /*
            if ( $distance == FALSE )
            {
                $qWhere[] = '1=1';
            }
            */
            if ( count($qWhere) > 0 )
            {
                $qWhere = '(' . implode(' or ', $qWhere) . ')'; 
                $this->SV102->where( $qWhere );
            }           

            $this->SV102->join('placeTheme PT', 'MT.mtIdx = PT.mtIdx');
            $this->SV102->join('mergePlaceSite PS', 'PT.mpsIdx = PS.mpsIdx AND PS.mmType = \'YPS\'');
            $this->SV102->join('placePensionBasic PB', 'PS.mpIdx = PB.mpIdx');
            
            // 세일 펜션
            if ( is_array($salePensionSaleIdxs) && count($salePensionSaleIdxs) > 0 )
            {
                $this->SV102->where_in( 'PS.mpIdx', $salePensionSaleIdxs );
            }
            //echo var_dump($data['idxStrings']);
            //random 처리시 예외 시킬 idx들
            if( $data['random'] > 0 && count($data['idxStrings']) > 0 ){
                $this->SV102->where_not_in('PS.mpIdx',$data['idxStrings']);
            }
            
            $this->SV102->where('MT.mtSite LIKE "%YPS%"');
            $this->SV102->where('MT.mtType','PS');
            $this->SV102->where('PS.mpsOpen > ', '0');
            $this->SV102->group_by('PS.mpIdx');
            $reserve_arr = array('R','G');
            $this->SV102->where_in('PB.ppbReserve',$reserve_arr);
            $this->SV102->stop_cache();
            
            $result['count']    = $this->SV102->get('mergeTheme MT')->num_rows();
            //echo var_dump($result);
            //echo $this->SV102->last_query();
            $this->SV102->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PB.ppbImage,PB.ppbRoomMin,PB.ppbReserve');
            // 1 -> 조회 , 2 -> 가보고싶아요, 3 -> 신규, 4 -> 낮은가격순, 5 -> 높은가격순
            if( $data['orderby'] ){
                switch ( $data['orderby'] )
                {
                    case ('R') :
                        $orderBy = 'ASC';
                    case ('G') :
                        $orderBy = 'DESC';
                    default :
                        $orderBy = 'ASC';
                    break;
                }


                $this->SV102->order_by('PB.ppbGrade DESC, PB.ppbReserve',$orderBy);
            }
            
            //랜덤정렬
            if( $data['random'] ){
                $this->SV102->order_by('rand()');
            }else
                if( !$data['orderby'] ) $this->SV102->order_by('PS.mpsIdx', 'desc');

            //echo var_dump($data);
            $result['obj']      = $this->SV102->get('mergeTheme MT', $data['limit'])->result();
            $this->SV102->flush_cache();
            //echo $this->SV102->last_query();
            
            foreach( $result['obj'] as $k => $o ) {
                $pensionPriceInfo = $this->pensionMinPrice($o->mpIdx);
                $result['obj'][$k]->ppbRoomMin  = $pensionPriceInfo->minPrice;  // 오늘의 펜션 최저가 요금
                $result['obj'][$k]->ppbRoomSales    = $pensionPriceInfo->maxSalePercent;    // 오늘의 펜션 최고 할인율
                
                
                // R:실시간예약,G:일반예약,T:전화문의
                switch ( $o->ppbReserve )
                {
                    case 'R' :
                        $result['obj'][$k]->ppbReserve = 'R';
                    break;
                    case 'G' :
                        $result['obj'][$k]->ppbReserve = 'G';
                    break;
                    case 'T' :
                    default :
                        $result['obj'][$k]->ppbReserve = 'T';
                    break;
                }
                
            }
            
            return $result;
    }
    // *************************************************** 바로예약 리스트 ******************************************************
    
    // *************************************************** 일반예약 리스트 ******************************************************
    public function getNormalList($data){
            $holyQuery = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday WHERE hDate-INTERVAL 1 DAY = '".date('Y-m-d')."'";
            $holyRow = $this->SV102->query($holyQuery)->row_array();
            
            // 세일 펜션
            $salePensionSaleIdxs = NULL;
            if( isset($data['sale']) && $data['sale'] == 1 ) 
            {
                $salePensionSaleIdxs = $this->salePensionSaleIdxs();
            }
            
            $holiDayCheck = $this->holidayCheck(date('Y-m-d'));
            
            $this->SV102->start_cache();
            
            /* 위도, 경도 검색 START */
            $distance = FALSE;
            if ( isset($data['latitude']) && isset($data['longitude']) )
            {
                $latitude = $data['latitude'];
                $longitude = $data['longitude'];
                $this->SV102->select("
                    PS.mpIdx,PS.mpsName,PS.mpsAddr1,PS.mpsMapX,PS.mpsMapY, 
                    ( 6371 * acos( cos( radians($latitude) ) * cos( radians(mpsMapY) ) * cos( radians(mpsMapX) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians(mpsMapY) ) ) ) AS distance "
                );
                $this->SV102->having('distance <=', 5);
                $distance = TRUE;
            }
            //테스트펜션 제외
            if( !empty( $data['locCode'] ) ) $this->SV102->like('MT.mtCode',$data['locCode']);
            if( !empty( $data['themeCode'] )){
                if(substr($data['themeCode'],0,1) != "1"){
                    $data['themeCode'] = explode(",",$data['themeCode']);
                    //$this->SV102->where_in('PT.mtCode', $data['themeCode']);
                    
                    for($i=0; $i< count($data['themeCode']); $i++){
                        $this->SV102->where('PPTP.PS'.str_replace(".","",$data['themeCode'][$i]), 1);
                    }
                }                
            }
            if( $data['search'] ) $this->SV102->like('PS.mpsName',$data['search']);
            
            
            $this->SV102->join('placeTheme PT', 'MT.mtIdx = PT.mtIdx');
            $this->SV102->join('mergePlaceSite PS', 'PT.mpsIdx = PS.mpsIdx');
            $this->SV102->join('placePensionBasic PB', 'PS.mpIdx = PB.mpIdx');
            $this->SV102->join('placePensionThemeFlag AS PPTP','PPTP.mpIdx = PB.mpIdx','LEFT');
            
            // 세일 펜션
            if ( is_array($salePensionSaleIdxs) && count($salePensionSaleIdxs) > 0 )
            {
                $this->SV102->where_in( 'PS.mpIdx', $salePensionSaleIdxs );
            }

            //random 처리시 예외 시킬 idx들
            if( count($data['idxStrings']) > 3 ){
                    $this->SV102->where_not_in('PS.mpIdx', $data['idxStrings']);
            }
            
            $this->SV102->where('MT.mtSite','YPS');
            $this->SV102->where('MT.mtType','PS');
            $this->SV102->where('PS.mpsOpen > ', '0');
            $this->SV102->where('PB.ppbReserve','G');
            $this->SV102->group_by('PS.mpIdx');
            $this->SV102->stop_cache();

            $result['count']    = $this->SV102->get('mergeTheme MT')->num_rows();
            

            $this->SV102->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PB.ppbImage,PB.ppbRoomMin,PB.ppbReserve, PB.ppbWantCnt');
            
            $date = date("Y-m-d");            
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
        
            $this->SV102->select("(SELECT CASE WHEN HE.heIdx THEN ppdpSaleDay".$toNumOfWeek." ELSE ppdpSaleDay".$numOfWeek." END AS price
                FROM pensionPrice AS SPPDP
                LEFT JOIN holidayExclude AS HE ON SPPDP.mpIdx = HE.mpIdx AND HE.hIdx = '".$holyRow['hIdx']."'
                WHERE SPPDP.mpIdx = PS.mpIdx AND '".$date."' BETWEEN SPPDP.ppdpStart AND SPPDP.ppdpEnd
                HAVING price > 0 ORDER BY price ASC LIMIT 1) AS price", false);
                
            // 1 -> 조회 , 2 -> 가보고싶아요, 3 -> 신규, 4 -> 낮은가격순, 5 -> 높은가격순
            if($data['orderby'] == ""){
                $data['orderby'] = "1";
            } 
            if( $data['orderby'] ){
                switch ( $data['orderby'] )
                {
                    case ('1') :
                        $this->SV102->order_by('PB.ppbGrade', 'DESC');
                        $this->SV102->order_by('rand()');
                        break;
                    case ('2') :
                        $this->SV102->order_by('PB.ppbWantCnt', 'DESC');
                        $this->SV102->order_by('rand()');
                        break;
                    case ('4') :
                        $this->SV102->order_by('price', 'ASC');
                        $this->SV102->order_by('rand()');
                        break;
                    case ('5') :
                        $this->SV102->order_by('price', 'DESC');
                        $this->SV102->order_by('rand()');
                        break;
                    default :
                        $this->SV102->order_by('PB.ppbGrade', 'DESC');
                        $this->SV102->order_by('rand()');
                        break;
                }                
            }
            $this->SV102->order_by('PB.ppbGrade DESC, PB.ppbReserve', 'ASC');
            $this->SV102->select('PB.ppbGrade');

            $result['obj']      = $this->SV102->get('mergeTheme MT', $data['limit'], $data['offset'])->result();
            
            $this->SV102->flush_cache();
            //echo "<pre>".$this->SV102->last_query()."</pre>";
            
            foreach( $result['obj'] as $k => $o ) {
                $pensionPriceInfo = $this->pensionMinPrice($o->mpIdx);
                $result['obj'][$k]->ppbRoomMin  = $pensionPriceInfo->minPrice;  // 오늘의 펜션 최저가 요금
                $result['obj'][$k]->ppbRoomSales    = $pensionPriceInfo->maxSalePercent;    // 오늘의 펜션 최고 할인율
                
                
                // R:실시간예약,G:일반예약,T:전화문의
                switch ( $o->ppbReserve )
                {
                    case 'R' :
                        $result['obj'][$k]->ppbReserve = 'R';
                    break;
                    case 'G' :
                        $result['obj'][$k]->ppbReserve = 'G';
                    break;
                    case 'T' :
                    default :
                        $result['obj'][$k]->ppbReserve = 'T';
                    break;
                }
                
            }
            
            return $result;
    }
    // *************************************************** 바로예약 리스트 ******************************************************


    // 펜션 최저가 구하기
    public function pensionMinPrice( $mpIdx )
    {
        $date = date('Y-m-d');
        $dateObj = new DateTime($date);
        $numOfWeek = $dateObj->format('N');
        
        $holyQuery = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday WHERE hDate-INTERVAL 1 DAY = '".$date."'";
        $holyRow = $this->SV102->query($holyQuery)->row_array();
        
        
        if(isset($holyRow['hIdx'])){    // 공휴일 날짜가 있을경우
            $flag_sql = "SELECT COUNT(*) AS cnt FROM holidayExclude WHERE hIdx = '".$holyRow['hIdx']."' AND mpIdx = '".$mpIdx."'";
            $flag_arr = $this->SV102->query($flag_sql)->row_array();
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
        $priceRow = $this->SV102->query($query)->result_array();
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
        
        return (object)$result;
    }


    // 세일펜션들의 idx를 배열로 가져오기
    public function salePensionSaleIdxs()
    {
        $salePensionSaleIdxs = array();
        $pensionSaleList = $this->pensionSaleList();
        
        foreach ( $pensionSaleList as $key => $value )
        {
            array_push( $salePensionSaleIdxs, $value['mpIdx'] );
        }
        
        return $salePensionSaleIdxs;
    }

    // 세일펜션 가져오기
    public function pensionSaleList()
    {
        $result = array();
        $dateTime = date("Y-m-d");
        
        $this->SV102->where('ppsStartDate <= ', $dateTime);
        $this->SV102->where('ppsEndDate >= ', $dateTime);
        $result = $this->SV102->from('placePensionSale AS pps')->get()->result_array();
        
        return $result;
    }

    //검색어가 있는 테마코드를 가져온다
    function getThemeCode( $keyword ) {
        $this->SV102->where("mtSite LIKE '%YPS%'");
        $this->SV102->where('mtType', 'PS');
        $this->SV102->where('mtOpen > ', '0');
        $this->SV102->where("mtName LIKE '%".$keyword."%'");
        $this->SV102->where("mtCode LIKE '2.%'");
        $this->SV102->select('mtCode');
        $result = $this->SV102->get('mergeTheme')->result();
        
        return $result;
    }

    //펜션 이미지 카운트
    public function pensionImageCount( $mpIdx ) {       
        $this->SV102->select('(A.cnt+B.cnt) as cnt');
        $result = $this->SV102->from('(select count(mpIdx) as cnt from placePensionRoomPhoto where mpIdx='.$mpIdx.') A, (select count(mpIdx) as cnt from placePensionEtcPhoto where mpIdx='.$mpIdx.') B', FALSE)->get()->row_array();
        return $result['cnt'];
    }

    public function pensionMap($latitude, $longitude){ // 펜션지도 좌표

        $this->SV102->start_cache();
        $this->SV102->where('MPS.mmType LIKE "%YPS%"');    // 타입
        $this->SV102->where('MPS.mpType', 'PS');   // 타입
        $this->SV102->where('MPS.mpsOpen > ', '0');    // 게시
        $this->SV102->having('distance <=', 15); 
        $this->SV102->group_by('MPS.mpIdx');

        $this->SV102->stop_cache();

        $this->SV102->select("
            MPS.mpIdx,MPS.mpsName,MPS.mpsAddr1,MPS.mpsMapX,MPS.mpsMapY, PPB.ppbImage,
            ( 6371 * acos( cos( radians($latitude) ) * cos( radians(mpsMapY) ) * cos( radians(mpsMapX) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians(mpsMapY) ) ) ) AS distance "
        );
        $this->SV102->order_by("MPS.mpsIdx asc");
        $this->SV102->join('placePensionBasic AS PPB','PPB.mpIdx = MPS.mpIdx');
        $row =  $this->SV102->get('pensionDB.mergePlaceSite AS MPS');

        $result['count'] = $row->num_rows();
        $result['query'] = $row->result_array();

        $this->SV102->flush_cache();
        return $result;

    }

    public function pensionTheme(){
        $this->SV102->select('mtDepth,mtCode,mtName');
        $this->SV102->where('mtType','PS');
        $this->SV102->where('mtType > ', '2');
        $this->SV102->where('mtDepth > ', '1');

        $this->SV102->order_by('mtCode', 'asc');

        return $this->SV102->get('pensionDB.mergeTheme')->result_array();
    }

    // ****************************************************** 펜션 상세정보 *********************************************************

    public function pensionGetInfo($idx){
        $dateTime = date("Y-m-d H:i:s");

        // $this->SV102->select('
        //  PS.mpsIdx,PS.mpsName,PS.mpsAddr1,PS.mpsAddr2,PS.mpsMapX,PS.mpsMapY,mpsTelService,
        //  PB.ppbRoomMin,ppbReserve,
        //  PE.peIdx,PE.peTitle,PE.peIntro,PE.peStartDate,PE.peEndDate');
        // $this->SV102->where('PS.mpIdx ', $idx);
        // $this->SV102->where('PS.mpsOpen > ', 0);
        // $this->SV102->where('PS.mmType', 'YPS');    // 타입
        // $this->SV102->where('PS.mpType', 'PS'); // 타입
        // $this->SV102->group_by('PS.mpIdx');
        // 
        // $this->SV102->join('pensionDB.placePensionBasic PB', 'PS.mpIdx = PB.mpIdx'); // 기본정보 테이블
        // $this->SV102->join('pensionDB.pensionEvent PE', "PS.mpIdx = PE.mpIdx and PE.peOpen > 0 and peStartDate <= '".$dateTime."' and peEndDate >= '".$dateTime."' ",'left');  // 이벤트 테이블
        // 
        // $this->SV102->from('pensionDB.mergePlaceSite PS');
        //
        // return $this->SV102->get();
        
        $nSql = "
            SELECT
                PS.mpsIdx,PS.mpsName,PS.mpsAddr1,PS.mpsAddr2,PS.mpsMapX,PS.mpsMapY,mpsTelService, PS.mpsAddrFlag, PS.mpsAddr1New, PS.mpsTel,
                PB.ppbRoomMin,ppbReserve,PB.ppbEventFlag, PB.ppbTel1, PB.ppbDateCheck,
                PE.peIdx,PE.peTitle,PE.peIntro,PE.peStartDate,PE.peEndDate
            FROM 
                 pensionDB.mergePlaceSite PS
                    LEFT JOIN (
                        SELECT 
                            peIdx,peTitle,peIntro,peStartDate,peEndDate,mpIdx
                        FROM 
                            pensionDB.pensionEvent
                        WHERE
                            peOpen > 0
                        AND mpIdx = '$idx'     
            /*AND peStartDate <= '$dateTime' 20140624 이벤트 시작일과 상관없이 이벤트 출력함 modified by 박재한 */
                        AND peEndDate >= '$dateTime'
                        ORDER BY 
                            peIdx DESC
                        LIMIT 1
                    ) PE ON PS.mpIdx = PE.mpIdx
                ,pensionDB.placePensionBasic PB
            WHERE 
                PS.mpIdx = PB.mpIdx
            AND PS.mpIdx = '$idx'
            AND PS.mpsOpen > '0'
            AND PS.mmType = 'YPS'
            AND PS.mpType = 'PS'
            GROUP BY
                PS.mpIdx
        ";
        
        return $this->SV102->query($nSql);
    }

    // ****************************************************** 펜션 상세정보 *********************************************************


    // ****************************************************** 펜션 객실정보 *********************************************************
    public function pensionRoomLists($idx){
        $this->SV102->select('PR.pprIdx,PR.pprName,PR.pprSize,PR.pprInMin, PR.pprInMax,PR.pprShape, PR.pprFloorM, PR.pprFloorS');
        $this->SV102->where('PR.mpIdx', $idx);
        $this->SV102->where('PR.pprOpen > ', 0);
        $this->SV102->order_by('PR.pprNo', 'desc');
        $this->SV102->join('placePensionRoomPhoto AS PPRP','PPRP.pprIdx = PR.pprIdx', 'left');
        $this->SV102->join('placePensionPrice AS PPP','PR.pprIdx = PPP.pprIdx AND PPP.pppType = \'DS\'');
        $this->SV102->group_by('PR.pprIdx');
        $this->SV102->where('PPRP.pprpFileName is not null','',false);
        $this->SV102->where('PPP.pppDay1 > 0');
        $return = $this->SV102->get('pensionDB.placePensionRoom PR')->result_array();
        if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
            //echo $this->SV102->last_query();
        }
        
        return $return;
    }
    // ****************************************************** 펜션 객실정보 *********************************************************


    // *************************************************** 펜션 객실사진 리스트 *****************************************************

    public function pensionRoomImageLists($idx, $limit, $offset){
        $this->SV102->start_cache();
        $this->SV102->where('pprIdx', $idx);
        $this->SV102->where('pprpOpen > ', 0);
        $this->SV102->where('pprpFileName is not null','',false);
        //$this->SV102->where('pprpRepr > ', 0);
        
        $this->SV102->stop_cache();
        
        $result['count'] = $this->SV102->count_all_results('pensionDB.placePensionRoomPhoto');
//echo $this->SV102->last_query();
        $this->SV102->select('mpIdx,pprpFileName, pprpRepr');
        $this->SV102->where('pprpFileName is not null','',false);      
        $this->SV102->order_by('pprpNo', 'asc');
        $result['query'] = $this->SV102->get('pensionDB.placePensionRoomPhoto', $offset, $limit)->result_array();

        $this->SV102->flush_cache();
        return $result;
    }
    // *************************************************** 펜션 객실사진 리스트 *****************************************************


    // ************************************************* 펜션 기타사진 리스트 **************************************************

    public function pensionEtcImageLists($idx, $limit, $offset){
        $this->SV102->start_cache();
        $this->SV102->where('ppeIdx', $idx);
        $this->SV102->where('ppepOpen > ', 0);
        $this->SV102->stop_cache();

        $result['count'] = $this->SV102->count_all_results('pensionDB.placePensionEtcPhoto');

        $this->SV102->select('mpIdx, ppepFileName');       
        $this->SV102->order_by('ppepNo', 'asc');
        $result['query'] = $this->SV102->get('pensionDB.placePensionEtcPhoto', $offset, $limit)->result_array();
		
        $this->SV102->flush_cache();
        return $result;
    }
    // ************************************************* 펜션 기타사진 리스트 **************************************************


    // ****************************************************** 펜션 객실요금 *********************************************************
    public function pensionRoomPrice($idx){
        $this->SV102->select('PD.ppdName,PD.ppdType');
        $this->SV102->where('PD.mpIdx', $idx);
        $this->SV102->where('PDT.ppdtStart <=', date('Y-m-d'));
        $this->SV102->where('PDT.ppdtEnd >=', date('Y-m-d'));
        $this->SV102->order_by('PD.ppdNo', 'desc');

        $this->SV102->join('pensionDB.placePensionDateTime PDT', 'PD.ppdIdx = PDT.ppdIdx');
        $result = $this->SV102->get('pensionDB.placePensionDate PD', 1, 0)->row_array();

        if($result)
            return $result['ppdType'];

        return 'DS';
    }

    // ****************************************************** 펜션 객실요금 *********************************************************

    function pensionImageLists($idx, $limit, $offset) { // 펜션 사진 리스트

        $this->SV102->start_cache();
        $this->SV102->where('mpIdx', $idx);
        $this->SV102->where('pprpOpen > ', 0);
        $this->SV102->where('pprpRepr > ', 0);
        $this->SV102->stop_cache();

        $result['count'] = $this->SV102->count_all_results('pensionDB.placePensionRoomPhoto');

        $this->SV102->select("pprpFileName");
        $this->SV102->order_by("pprpNo desc");
        $result['query'] = $this->SV102->get('pensionDB.placePensionRoomPhoto', $offset, $limit)->result_array();

        $this->SV102->flush_cache();
        return $result;
    }

    function pensionReprEtcImageLists($idx, $limit, $offset) {  // 펜션 사진 리스트

        $this->SV102->start_cache();
        $this->SV102->where('PPEP.mpIdx', $idx);
        $this->SV102->where('PPEP.ppepOpen > ', 0);
        $this->SV102->where('PPEP.ppepRepr > ', 0);
        $this->SV102->stop_cache();

        $result['count'] = $this->SV102->count_all_results('pensionDB.placePensionEtcPhoto AS PPEP');

        $this->SV102->select("PPEP.ppepFileName");
        $this->SV102->join('placePensionEtc AS PPE','PPE.ppeIdx = PPEP.ppeIdx','LEFT');
        $this->SV102->order_by("PPE.ppeNo ASC");
        $this->SV102->order_by("PPEP.ppepIdx DESC");
        $result['query'] = $this->SV102->get('pensionDB.placePensionEtcPhoto AS PPEP', $offset, $limit)->result_array();
        
        $this->SV102->flush_cache();
        return $result;
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
        $result = $this->SV102->query($schQuery)->result_array();
        
        return $result;
    }

    // ****************************************************** 펜션 가고싶어요 *********************************************************
    public function pensionBasket($sector, $mpIdx, $mbIdx){ // 가고싶어요 등록 , 삭제
        if(!strcmp($sector,"INSERT")){ // 등록
            $this->db->select('mpIdx');
            $this->db->where('mpIdx ', $mpIdx);
            $this->db->where('mbIdx ', $mbIdx);
            $this->db->from('pensionDB.pensionBasket');

            if($this->db->get()->row()){
                return "이미 가고싶어요한 업체입니다. MY>가고싶어요에서 삭제하실 수 있습니다.";
            }else{
                $this->db->insert('pensionDB.pensionBasket', array(
                    'mpIdx' => $mpIdx,
                    'mbIdx' => $mbIdx,
                    'pbDate' => date("Y-m-d")
                ));

                return 1;
            }
        }else{  // 삭제
            $this->db->where('mpIdx ', $mpIdx);
            $this->db->where('mbIdx ', $mbIdx);
            $this->db->delete('pensionDB.pensionBasket'); 

            return 1;
        }
    }
    
    public function pensionBasketToggle($sector, $mpIdx, $mbIdx){ // 가고싶어요 등록 , 삭제
        if(!strcmp($sector,"INSERT")){ // 등록

            $this->db->select('mpIdx');
            $this->db->where('mpIdx ', $mpIdx);
            $this->db->where('mbIdx ', $mbIdx);
            $this->db->from('pensionDB.pensionBasket');

            if($this->db->get()->row()){
                $this->db->where('mpIdx ', $mpIdx);
                $this->db->where('mbIdx ', $mbIdx);
                $this->db->delete('pensionDB.pensionBasket');
                return 3;
            }else{
                $this->db->insert('pensionDB.pensionBasket', array(
                    'mpIdx' => $mpIdx,
                    'mbIdx' => $mbIdx,
                    'pbDate' => date("Y-m-d")
                ));
                return 2;
            }
        }else{  // 삭제
            $this->db->where('mpIdx ', $mpIdx);
            $this->db->where('mbIdx ', $mbIdx);
            $this->db->delete('pensionDB.pensionBasket'); 

            return 1;
        }
    }

    function pensionBasketCount($mpIdx){
        $this->db->where('mpIdx', $mpIdx);
        $result = $this->db->count_all_results('pensionDB.pensionBasket');
        
        return $result;
    }

    public function pensionBasketSch($mpIdx, $mbIdx){ // 가고싶어요 등록 , 삭제
        $this->SV102->select('mpIdx');
        $this->SV102->where('mpIdx ', $mpIdx);
        $this->SV102->where('mbIdx ', $mbIdx);
        $this->SV102->from('pensionDB.pensionBasket');

        if($this->SV102->get()->row()){
            return 1;
        }else{
            return 2;
        }
    }
    // ****************************************************** 펜션 가고싶어요 *********************************************************

    // ***************************************************** 펜션 팁 리스트 *******************************************************
    function tipLists($idx, $limit, $offset) {

        $this->SV102->start_cache();
        $this->SV102->where('mpIdx', $idx);
        $this->SV102->where('ptFlag','0');
        $this->SV102->stop_cache();

        $result['count'] = $this->SV102->count_all_results('pensionDB.pensionTip');

        $this->SV102->select("*");
        $this->SV102->order_by("ptIdx desc");
        $result['query'] = $this->SV102->get('pensionDB.pensionTip', $offset, $limit)->result_array();
        
        $this->SV102->flush_cache();
        return $result;
    }
    // ***************************************************** 펜션 팁 리스트 *******************************************************

    // ***************************************************** 펜션 팁 등록 *********************************************************
    public function tipInsert_old($data){
        $this->db->select(array('rIdx','rCode','round((rPrice*0.02),0) as savePoint'));
        $this->db->where('mpIdx', $data['mpIdx']);
        $this->db->where('mbIdx', $data['mbIdx']);
        $this->db->where('rPointSaveCheck', '0');
        $this->db->where('rEndDate <= ', date('Y-m-d'));
        $this->db->where('rPayFlag','Y'); 
        $this->db->where('rEndDate + INTERVAL 1 MONTH >= ', date('Y-m-d'));
        $this->db->where('rPaymentState','PS02');
        $this->db->order_by('rRegDate','DESC');

        $reservation = $this->db->get('pensionDB.reservation', 1, 0)->row_array();
        
        $this->db->select('mbID');
        $this->db->where('mbIdx',$data['mbIdx']);
        $mbID_arr = $this->db->get('pensionDB.member')->row_array();
        $data['mbID'] = $mbID_arr['mbID'];

        $this->db->select('rIdx');
        $this->db->where('mpIdx', $data['mpIdx']);
        $this->db->where('mbIdx', $data['mbIdx']);
        $this->db->where('ptPointSave', '1');
        $tip_check_arr = $this->db->get('pensionDB.pensionTip')->result_array();

        $tip_check = array();
        if(count($tip_check_arr) > 0){
            foreach($tip_check_arr as $tip_check_arr){
                $tip_check[] = $tip_check_arr['rIdx'];
            }
            $this->db->select('mbIdx');
            $this->db->where_in('rIdx',$tip_check);
            $this->db->where('mpIdx', $data['mpIdx']);
            $this->db->where('mbIdx', $data['mbIdx']);
            $check_num = $this->db->get('pensionDB.reservation')->num_rows();
        }else{
            $check_num = 0;
        }
        

        if($check_num <= 0){
            if(count($reservation) > 0){
                $this->db->set('rPointSaveCheck', '1')->where('rIdx', $reservation['rIdx'])->update('pensionDB.reservation');
                //$this->db->set('mbPoint', 'mbPoint+'.(int)$reservation['savePoint'], FALSE)->where('mbIdx', $data['mbIdx'])->update('pensionDB.member');
                $this->db->insert('pensionDB.memberPointRaw', array(
                                                                'mbIdx' => $data['mbIdx'],
                                                                'mpID'  => $data['mbID'],
                                                                'mprResvCode' => $reservation['rCode'],
                                                                'mprPointCode' => 'MP001',
                                                                'mprPlusMinus' => 'P',
                                                                'mprPoint' => (int)$reservation['savePoint'],
                                                                'mprPointDate' => date('Y-m-d H:i:s'),
                                                                'mplExpirationDate' => date("Y-m-d",strtotime("+2 year"))
                                                                ));
                $ptPointSave = '1';
            }else{
                $ptPointSave = '0';
            }
            
        }else{
            $ptPointSave = '0';
        }
        
        
        if(isset($data['rIdx'])){
            $Tip_arr = array(
                'mpIdx' => $data['mpIdx'],
                'mbIdx' => $data['mbIdx'],
                'ptSector' => $data['ptSector'],
                'ptName' => $data['ptName'],
                'ptPensionName' => $data['ptPensionName'],
                'ptTravelName' => $data['ptTravelName'],
                'ptContent' => $data['ptContent'],
                'ptRegDate' => date('Y-m-d H:i:s'),
                'ptRecommend' => '0',
                'ptPointSave' => $ptPointSave,
                'rIdx' => $data['rIdx']
            );
        }else{
            $Tip_arr = array(
                'mpIdx' => $data['mpIdx'],
                'mbIdx' => $data['mbIdx'],
                'ptSector' => $data['ptSector'],
                'ptName' => $data['ptName'],
                'ptPensionName' => $data['ptPensionName'],
                'ptTravelName' => $data['ptTravelName'],
                'ptContent' => $data['ptContent'],
                'ptRegDate' => date('Y-m-d H:i:s'),
                'ptRecommend' => '0',
                'ptPointSave' => $ptPointSave
            );
        }
        
        return $this->db->insert('pensionDB.pensionTip', $Tip_arr);

//      return $this->db->insert_id();
    }
    
    public function tipInsert($data){
        //reservation 예약건 찾기
        $this->db->select(array('rIdx','rCode'));
        $this->db->where('mpIdx', $data['mpIdx']);
        $this->db->where('mbIdx', $data['mbIdx']);
        $this->db->where('rPayFlag','Y'); 
        $this->db->where('rPaymentState','PS02');
        $this->db->order_by('rRegDate','DESC');
        $reservationInfo = $this->db->get('pensionDB.reservation', 1, 0)->row_array();

        //pensionRevInfo에서 해당예약건 마지막날짜 찾기
        $this->db->where('rIdx',$reservationInfo['rIdx']);
        $this->db->order_by('rRevDate','DESC');
        $revInfo = $this->db->get('pensionDB.pensionRevInfo', 1, 0)->row_array();
        
        //reservation 예약 마지막날짜가 조건에 만족하는 포인트 저장 안된 예약건 찾기
        $this->db->select(array('rIdx','rCode','round((rPrice*0.02),0) as savePoint'));
        $this->db->where(rIdx , $reservationInfo['rIdx']);
        $this->db->where("'".$revInfo['rRevDate']."' <= ", "'".date('Y-m-d')."'", false);
        $this->db->where("'".date('Y-m-d', strtotime('+1 day', strtotime($revInfo['rRevDate'])))."' + INTERVAL 1 MONTH >= ","'".date('Y-m-d')."'",false);
        $this->db->where('rPointSaveCheck', '0');
        $reservation = $this->db->get('pensionDB.reservation', 1, 0)->row_array();
        
        $this->db->select('mbID');
        $this->db->where('mbIdx',$data['mbIdx']);
        $mbID_arr = $this->db->get('pensionDB.member')->row_array();
        $data['mbID'] = $mbID_arr['mbID'];

        $this->db->select('rIdx');
        $this->db->where('mpIdx', $data['mpIdx']);
        $this->db->where('mbIdx', $data['mbIdx']);
        $this->db->where('ptPointSave', '1');
        $this->db->where('rIdx', $reservation['rIdx']);
        $tip_check_arr = $this->db->get('pensionDB.pensionTip')->result_array();

        $tip_check = array();
        if(count($tip_check_arr) > 0){
            foreach($tip_check_arr as $tip_check_arr){
                $tip_check[] = $tip_check_arr['rIdx'];
            }
            $this->db->select('mbIdx');
            $this->db->where_in('rIdx',$tip_check);
            $this->db->where('mpIdx', $data['mpIdx']);
            $this->db->where('mbIdx', $data['mbIdx']);
            $check_num = $this->db->get('pensionDB.reservation')->num_rows();
        }else{
            $check_num = 0;
        }
        

        if($check_num <= 0){
            if(count($reservation) > 0){
                $this->db->set('rPointSaveCheck', '1')->where('rIdx', $reservation['rIdx'])->update('pensionDB.reservation');
                //$this->db->set('mbPoint', 'mbPoint+'.(int)$reservation['savePoint'], FALSE)->where('mbIdx', $data['mbIdx'])->update('pensionDB.member');
                $this->db->insert('pensionDB.memberPointRaw', array(
                                                                'mbIdx' => $data['mbIdx'],
                                                                'mpID'  => $data['mbID'],
                                                                'mprResvCode' => $reservation['rCode'],
                                                                'mprPointCode' => 'MP001',
                                                                'mprPlusMinus' => 'P',
                                                                'mprPoint' => (int)$reservation['savePoint'],
                                                                'mprPointDate' => date('Y-m-d H:i:s'),
                                                                'mplExpirationDate' => date("Y-m-d",strtotime("+2 year"))
                                                                ));
                $ptPointSave = '1';
            }else{
                $ptPointSave = '0';
            }
            
        }else{
            $ptPointSave = '0';
        }
        
        
        if(isset($data['rIdx'])){
            $Tip_arr = array(
                'mpIdx' => $data['mpIdx'],
                'mbIdx' => $data['mbIdx'],
                'ptSector' => $data['ptSector'],
                'ptName' => $data['ptName'],
                'ptPensionName' => $data['ptPensionName'],
                'ptTravelName' => $data['ptTravelName'],
                'ptContent' => $data['ptContent'],
                'ptRegDate' => date('Y-m-d H:i:s'),
                'ptRecommend' => '0',
                'ptPointSave' => $ptPointSave,
                'rIdx' => $data['rIdx']
            );
        }else{
            $Tip_arr = array(
                'mpIdx' => $data['mpIdx'],
                'mbIdx' => $data['mbIdx'],
                'ptSector' => $data['ptSector'],
                'ptName' => $data['ptName'],
                'ptPensionName' => $data['ptPensionName'],
                'ptTravelName' => $data['ptTravelName'],
                'ptContent' => $data['ptContent'],
                'ptRegDate' => date('Y-m-d H:i:s'),
                'ptRecommend' => '0',
                'ptPointSave' => $ptPointSave
            );
        }
        
        return $this->db->insert('pensionDB.pensionTip', $Tip_arr);

//      return $this->db->insert_id();
    }

    
    // ***************************************************** 펜션 팁 등록 *******************************************************


    // ***************************************************** 펜션 팁 수정 *******************************************************
    function tipUpdate($data) {
        $this->db->where('ptIdx', $data['ptIdx']);
        $this->db->where('mpIdx', $data['mpIdx']);
        $this->db->where('mbIdx', $data['mbIdx']);
        return $this->db->update('pensionDB.pensionTip', array(
            'ptSector' => $data['ptSector'],
            'ptTravelName' => $data['ptTravelName'],
            'ptContent' => $data['ptContent']
        ));
    }
    // ***************************************************** 펜션 팁 수정 *******************************************************


    // ***************************************************** 펜션 팁 삭제 *******************************************************
    function tipDelete($data) {
        return $this->db->delete('pensionDB.pensionTip', $data);
    }
    // ***************************************************** 펜션 팁 삭제 *******************************************************


    // ***************************************************** 펜션 팁 추천 *******************************************************
    public function tipRecommend($data){
        $this->db->select('ptIdx');
        $this->db->where('ptIdx', $data['ptIdx']);
        $this->db->where('mbIdx', $data['mbIdx']);
        $info = $this->db->get('pensionDB.pensionTip')->row_array();
        
        if(isset($info['ptIdx'])){
            return 1;
        }else{
            $this->db->where('ptIdx', $data['ptIdx']);
            $this->db->where('mbIdx', $data['mbIdx']);
            $recommInfo = $this->db->get('pensionTipRecommend')->row_array();
            
            $this->db->where('ptIdx', $data['ptIdx']);
            $tipInfo = $this->db->get('pensionDB.pensionTip')->row_array();
            
            if(isset($recommInfo['ptrIdx'])){
                $this->db->where('ptIdx', $data['ptIdx']);
                $this->db->where('mbIdx', $data['mbIdx']);
                $this->db->delete('pensionDB.pensionTipRecommend');
                
                $this->db->where('ptIdx', $data['ptIdx']);
                $this->db->set('ptRecommend', ($tipInfo['ptRecommend']-1));
                $this->db->update('pensionDB.pensionTip');
                
                return 2;
            }else{
                $this->db->set('ptIdx', $data['ptIdx']);
                $this->db->set('mbIdx', $data['mbIdx']);
                $this->db->set('ptRegDate', date('Y-m-d H:i:s'));
                $this->db->insert('pensionDB.pensionTipRecommend');
                
                $this->db->where('ptIdx', $data['ptIdx']);
                $this->db->set('ptRecommend', ($tipInfo['ptRecommend']+1));
                $this->db->update('pensionDB.pensionTip');
                
                return 3;
            }
        }
/*
        $this->db->select('ptIdx')->where('ptIdx', $data['ptIdx'])->where('mbIdx', $data['mbIdx'])->from('pensionDB.pensionTip');

        if($this->db->get()->row()){ // 내가쓴팁
            return 1;
        }else{
            $this->db->select('ptIdx');
            $this->db->where('ptIdx', $data['ptIdx']);
            $this->db->where('mbIdx', $data['mbIdx']);
            $this->db->from('pensionDB.pensionTipRecommend');
            if($this->db->get()->row()){ // 이미추천하였음.
                $this->db->where('ptIdx', $data['ptIdx']);
                $this->db->where('mbIdx', $data['mbIdx']);
                $this->db->delete('pensionDB.pensionTipRecommend');
                
                $this->db->set('ptRecommend', 'ptRecommend-1', FALSE);
                $this->db->where('ptIdx', $data['ptIdx']);
                $this->db->update('pensionDB.pensionTip');
                
                return 2;
            }else{
                $this->db->insert('pensionDB.pensionTipRecommend', array('ptIdx' => $data['ptIdx'],'mbIdx' => $data['mbIdx'],'ptRegDate' => date('Y-m-d H:i:s')));

                $this->db->set('ptRecommend', 'ptRecommend+1', FALSE);
                $this->db->where('ptIdx', $data['ptIdx']);
                $this->db->update('pensionDB.pensionTip');
                
                return 3;
            }
        }
 * */
    }
    // ***************************************************** 펜션 팁 추천 *******************************************************


    // ***************************************************** 펜션 팁 신고 *******************************************************
    function tipComplaintCheck($param) {
        extract( $param ); 
        $this->db->select('pcIdx');
        $this->db->where('mpIdx', $param['mpIdx']);
        $this->db->where('ptIdx', $param['ptIdx']);
        $this->db->where('mbIdx', $param['mbIdx']);
        return $this->db->get('pensionTipComplaint')->num_rows();
    }

    function tipComplaintIns($param) {
        extract( $param ); 
        $this->db->set('mpIdx', $param['mpIdx']);
        $this->db->set('ptIdx', $param['ptIdx']);
        $this->db->set('mbIdx', $param['mbIdx']);
        $this->db->set('regDate', date('Y-m-d H:i:s'));
        return $this->db->insert('pensionTipComplaint');
    }
    // ***************************************************** 펜션 팁 신고 *******************************************************
    
    // ***************************************************** 펜션 검색 *******************************************************
    function getTopSch($mbIdx, $mpIdx, $rIdx = null) {
        $this->db->select('mbIdx');
        $this->db->where('mbIdx', $mpIdx);
        $this->db->where('mpIdx', $ptIdx);
        if($rIdx){
            $this->db->where('rIdx', $mbIdx);
        }       
        return $this->db->get('pensionTip')->num_rows();
    }
    // ***************************************************** 펜션 검색 *******************************************************


    // ***************************************************** 펜션 지역명 *******************************************************
    function getLocName($data) {
        $this->SV102->where('mtCode', $data);
        $this->SV102->where('mtSite LIKE "%YPS%"');
        $this->SV102->where('mtType', 'PS');
        $this->SV102->select('mtName');
        return $this->SV102->get('pensionDB.mergeTheme')->row();
    }
    
    function getLocCodeName($data) {
        $this->SV102->where('mtCode', $data);
        $this->SV102->where('mtSite LIKE "%YPS%"');
        $this->SV102->where('mtType', 'PS');
        $this->SV102->select('mtName');
        $result = $this->SV102->get('pensionDB.mergeTheme')->row_array();
        return $result;
    }
    
    function getLocMapSite($data){
        $this->SV102->where('mtCode', $data);
        $result = $this->SV102->get('placePensionThemeSite')->row_array();
        
        return $result;
    }
    // ***************************************************** 펜션 지역명 *******************************************************


    // **************************************************** 펜션 객실 키 *******************************************************
    function getRoomKey($data) {
        $this->SV102->where('PR.mpIdx', $data['ptIdx']);
        if( $data['prIdx'] ) $this->SV102->where('PR.pprIdx', $data['prIdx']);
        $this->SV102->where('PR.pprOpen > ', '0');
        $this->SV102->join('pensionDB.placePensionBasic PB', 'PR.mpIdx = PB.mpIdx');
        $this->SV102->select('PR.pprIdx, PR.pprNo, PR.pprName, PR.pprInMin, PR.pprInMax, PR.pprSize, PB.ppbRoomMin');
        // $this->SV102->order_by('PR.pprIdx asc');
        $this->SV102->order_by('PR.pprNo desc');
        $result = $this->SV102->get('pensionDB.placePensionRoom PR')->result();
		
		if($_SERVER['REMOTE_ADDR'] == '211.119.165.88'){
			//echo $this->SV102->last_query();
		}
        
        return $result;
    }
    // **************************************************** 펜션 객실 키 *******************************************************


    // ************************************************** 펜션 기타 그룹 키 ****************************************************
    function getEtcKey($data) {
        $this->SV102->where('mpIdx', $data['ptIdx']);
        $this->SV102->where('ppeOpen > ', '0');
        $this->SV102->select('ppeIdx, ppeNo, ppeName');
        $this->SV102->order_by('ppeNo ASC');
        return $this->SV102->get('pensionDB.placePensionEtc')->result();
    }
    // ************************************************** 펜션 기타 그룹 키 ****************************************************
    
    // ************************************************** 펜션 외부 URL ****************************************************
    function getOutUrl($mpIdx) {
        $this->SV102->where('mpIdx', $mpIdx);
        $this->SV102->select(array('ppbOutUrl'));
        return $this->SV102->get('pensionDB.placePensionBasic')->row_array();
    }
    // ************************************************** 펜션 외부 URL ****************************************************
    
    /**
     * pensionEventCount
     * 
     * @author pyh, 201405231755
     * @desc 펜션 이벤트 카운트
     */
    public function pensionEventCount($idx) {
        $dateTime = date("Y-m-d H:i:s");
        
        $nSql = "
            SELECT
                COUNT(*) AS event_count
            FROM 
                pensionDB.pensionEvent
            WHERE
                peOpen > 0 
            AND mpIdx = '$idx'    
            /*AND peStartDate <= '$dateTime' 20140624 이벤트 시작일과 상관없이 이벤트 출력함 modified by 박재한 */
            AND peEndDate >= '$dateTime' 
        ";
        
        return $this->SV102->query($nSql);
    }
    
    function holidayCheck($date){
        $this->SV102->select("(hDate + INTERVAL -1 DAY) as ageDate");
        $this->SV102->where('(hDate + INTERVAL -1 DAY) >=', $date);
        $this->SV102->where('(hDate + INTERVAL -1 DAY) <=', $date);
        $result = $this->SV102->get('holiday', 1, 0)->result_array();
        
        $arrayResult = array();

        foreach ($result as $row) {
            $arrayResult[$row['ageDate']] = 1;
        }
        
        return $arrayResult;
    }
    
    function todayPriceFlag($mpIdx){
        $date = date("Y-m-d");
            
        $dateObj = new DateTime($date);
        $numOfWeek = $dateObj->format('N');
            
        $holyQuery = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday WHERE hDate-INTERVAL 1 DAY = '".$date."'";
        $holyRow = $this->SV102->query($holyQuery)->row_array();
        
        
        if(isset($holyRow['hIdx'])){    // 공휴일 날짜가 있을경우
            $flag_sql = "SELECT COUNT(*) AS cnt FROM holidayExclude WHERE hIdx = '".$holyRow['hIdx']."' AND mpIdx = '".$mpIdx."'";
            $flag_arr = $this->SV102->query($flag_sql)->row_array();
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
        
        switch ($numOfWeek) {
            case '1':
                $flag = "0";
                break;
                
            case '5':
                $flag = "1";
                break;
                
            case '6':
                $flag = "2";
                break;
                
            case '7':
                $flag = "3";
                break;
            
            default:
                $flag = "0";
                break;
        }
        
        return $flag;
    }

    function getPoolFlag($mpIdx){
        $this->SV102->where('mpIdx', $mpIdx);
        $this->SV102->where('ppuPullFlag','1');
        $flag = $this->SV102->count_all_results('placePensionUse');
        if($flag > 0){
            return "Y";
        }else{
            return "N";
        }
    }
    
    function getPensionNormalSearchLists($schVal, $schSort, $schFlag, $idxStrings){
        $date = date('Y-m-d');
            
        $dateObj = new DateTime($date);
        $dayNum = $dateObj->format('N');
        
        $holyQuery = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday WHERE hDate-INTERVAL 1 DAY = '".$date."'";
        $holyRow = $this->SV102->query($holyQuery)->row_array();
        
        
        if(isset($holyRow['hIdx'])){                
            $dayNum = "6";
        }else{
            if($dayNum < 5){
                $dayNum = "1";
            }
        }
        
        $schQuery = "   SELECT COUNT(tot.mpIdx) AS totalCount FROM (SELECT PPDP.mpIdx
                        FROM pensionPrice AS PPDP
                        LEFT JOIN placePensionBasic AS PPB ON PPDP.mpIdx = PPB.mpIdx
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPDP.mpIdx AND mmType ='YPS' AND mpType = 'PS'
                        LEFT JOIN placeTheme AS PT ON MPS.mpsIdx = PT.mpsIdx AND PT.mtCode LIKE '1%'
                        WHERE '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                        AND PPB.ppbReserve = 'G'
                        AND PPDP.ppdpSaleDay".$dayNum." > 0
                        AND PPDP.ppdpPercent".$dayNum." < 100
                        AND mpsOpen = '1'
                        AND PT.mtCode = '".$schVal."'
                        GROUP BY PPDP.mpIdx) AS tot";
        $count = $this->SV102->query($schQuery)->row_array();
    
        $result['count'] = $count['totalCount'];
        
        $schQuery = "   SELECT PPDP.mpIdx, MPS.mpsAddr1, MPS.mpsName, MIN(PPDP.ppdpSaleDay".$dayNum.") AS price, MAX(PPDP.ppdpPercent".$dayNum.") AS percent, PPB.ppbReserve, PPB.ppbGrade, PPB.ppbImage, PPB.ppbWantCnt, PPB.ppbEventFlag
                        FROM pensionPrice AS PPDP
                        LEFT JOIN placePensionBasic AS PPB ON PPDP.mpIdx = PPB.mpIdx
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPDP.mpIdx AND mmType ='YPS' AND mpType = 'PS'
                        LEFT JOIN placeTheme AS PT ON MPS.mpsIdx = PT.mpsIdx AND PT.mtCode LIKE '1%'
                        WHERE '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                        AND PPB.ppbReserve = 'G'
                        AND PPDP.ppdpSaleDay".$dayNum." > 0
                        AND PPDP.ppdpPercent".$dayNum." < 100";
        if($idxStrings != ""){
            $schQuery .= " AND PPDP.mpIdx NOT IN (".$idxStrings.")";
        }            
        $schQuery .= "  AND mpsOpen = '1'
                        AND PT.mtCode = '".$schVal."'
                        GROUP BY PPDP.mpIdx";
        if($schSort == "1"){
            $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND()";
        }else if($schSort == "2"){
            $schQuery .= "  ORDER BY PPB.ppbWantCnt DESC, RAND()";                
        }else if($schSort == "4"){
            $schQuery .= "  ORDER BY price ASC, RAND()";
        }else if($schSort == "5"){
            $schQuery .= "  ORDER BY price DESC, RAND()";                
        }else{
            $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND()";
        }
        
        $schQuery .= "  LIMIT 20";
        
        $result['lists'] = $this->SV102->query($schQuery)->result_array();
        
        return $result;
    }
    function getPensionSearchLists($schVal, $schSort, $schFlag, $idxStrings, $todaySale = 'N'){
        $iPod = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
        $iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
        $iPad = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
        $Android = stripos($_SERVER['HTTP_USER_AGENT'], "Android");
        
        $date = date('Y-m-d');
            
        $dateObj = new DateTime($date);
        $dayNum = $dateObj->format('N');
        
        if($dayNum < 5){
            $dayNum = "1";
        }
		
        if($schFlag == "text"){
            $schQuery = "   SELECT COUNT(tot.mpIdx) AS totalCount FROM (SELECT PPDP.mpIdx
                            FROM placePensionBasic AS PPB
                            LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPB.mpIdx AND mmType ='YPS' AND mpType = 'PS'
                            LEFT JOIN pensionPrice AS PPDP ON PPDP.mpIdx = PPB.mpIdx
                            LEFT JOIN pensionTodaySale AS PTS ON PTS.mpIdx = PPDP.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PPDP.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$dayNum." = '1'
                            LEFT JOIN (
                                SELECT GROUP_CONCAT(MT.mtName) AS theme, PT.mpsIdx
                                FROM mergeTheme AS MT 
                                LEFT JOIN placeTheme AS PT ON MT.mtCode = PT.mtCode
                                WHERE MT.mtOpen = '1'
                                AND MT.mtType = 'PS'
                                AND MT.mtSite = 'YPS'
                                AND MT.mtCode LIKE '2%'
                                GROUP BY PT.mpsIdx
                            ) AS PT ON PT.mpsIdx = MPS.mpsIdx
                            WHERE '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                            AND PPDP.ppdpSaleDay".$dayNum." > 0
                            AND PPDP.ppdpPercent".$dayNum." < 100
                            AND mpsOpen = '1'
                            AND CONCAT(REPLACE(MPS.mpsAddr1,' ',''),REPLACE(MPS.mpsAddr2,' ',''),'|',REPLACE(MPS.mpsName,' ',''),'|',REPLACE(PT.theme,' ','')) LIKE '%".$schVal."%'";
            if($schSort == "2" || $schSort == "4" || $schSort == "5"){
                $schQuery .= "  AND PPB.ppbReserve != 'N'";
            }
            if($todaySale == "Y"){
            	$schQuery .= "  AND PTS.ptsSale > 0";
            }
            $schQuery .= "  GROUP BY PPDP.mpIdx) AS tot";
            $count = $this->SV102->query($schQuery)->row_array();
        
            $result['count'] = $count['totalCount'];
            
            
            $schQuery = "   SELECT
                            PPDP.mpIdx, MPS.mpsAddr1, MPS.mpsName,
                            IFNULL(PTS.ptsSale,0) AS ptsSale,
                            CASE WHEN peIdx THEN
                                CASE peDay
                                    WHEN '1' THEN MIN(ppdpSaleDay1/100*(100-IFNULL(PTS.ptsSale,0)))
                                    WHEN '5' THEN MIN(ppdpSaleDay5/100*(100-IFNULL(PTS.ptsSale,0)))
                                    WHEN '6' THEN MIN(ppdpSaleDay6/100*(100-IFNULL(PTS.ptsSale,0)))
                                    WHEN '7' THEN MIN(ppdpSaleDay7/100*(100-IFNULL(PTS.ptsSale,0)))
                                ELSE
                                    MIN(ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
                                END
                            ELSE
                                MIN(ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
                            END AS price,
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
                            END AS basicPrice,
                            PT.theme, PPB.ppbReserve, PPB.ppbGrade, PPB.ppbImage, PPB.ppbWantCnt, PPB.ppbEventFlag
                        FROM placePensionBasic AS PPB
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPB.mpIdx AND MPS.mmType ='YPS' AND MPS.mpType = 'PS'
                        LEFT JOIN placePensionRoom AS PPR ON PPR.mpIdx = PPB.mpIdx AND PPR.pprOpen = '1'
                        LEFT JOIN pensionPrice AS PPDP ON PPDP.mpIdx = PPB.mpIdx AND PPDP.pprIdx = PPR.pprIdx AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                        LEFT JOIN pensionException AS PE ON PE.mpIdx = PPB.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                        LEFT JOIN pensionTodaySale AS PTS ON PTS.mpIdx = PPDP.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PPDP.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$dayNum." = '1'
                        LEFT JOIN placePensionUse AS PPU ON PPU.mpIdx = PPB.mpIdx
                        LEFT JOIN (
                            SELECT GROUP_CONCAT(MT.mtName) AS theme, PT.mpsIdx
                            FROM mergeTheme AS MT 
                            LEFT JOIN placeTheme AS PT ON MT.mtCode = PT.mtCode
                            WHERE MT.mtOpen = '1'
                            AND MT.mtType = 'PS'
                            AND MT.mtSite = 'YPS'
                            AND MT.mtCode LIKE '2%'
                            GROUP BY PT.mpsIdx
                        ) AS PT ON PT.mpsIdx = MPS.mpsIdx
                        WHERE PPR.pprIdx IS NOT NULL
                        AND PPDP.ppdpSaleDay".$dayNum." > 0
                        AND PPDP.ppdpPercent".$dayNum." < 100";
            
            if($idxStrings != ""){
                $schQuery .= " AND PPDP.mpIdx NOT IN (".$idxStrings.")";
            }
            if($schSort == "2" || $schSort == "4" || $schSort == "5"){
                $schQuery .= "  AND PPB.ppbReserve != 'N'";
            }
            if($todaySale == "Y"){
            	$schQuery .= "  AND PTS.ptsSale > 0";
            }
            $schQuery .= "  AND mpsOpen = '1'
                            AND CONCAT(REPLACE(MPS.mpsAddr1,' ',''),REPLACE(MPS.mpsAddr2,' ',''),'|',REPLACE(MPS.mpsName,' ',''),'|',REPLACE(PT.theme,' ','')) LIKE '%".$schVal."%'
                            GROUP BY PPDP.mpIdx";
            if($schSort == "1"){
                $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND(), PPB.ppbMainPension DESC, RAND()";
            }else if($schSort == "2"){
                $schQuery .= "  ORDER BY PPB.ppbWantCnt DESC, RAND()";                
            }else if($schSort == "4"){
                $schQuery .= "  ORDER BY price ASC, RAND()";
            }else if($schSort == "5"){
                $schQuery .= "  ORDER BY price DESC, RAND()";                
            }else{
                $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND(), PPB.ppbMainPension DESC, RAND()";
            }
            if($iPod || $iPhone || $iPad ){
                
            }else{
                $schQuery .= "  LIMIT 20";
            }
            $result['lists'] = $this->SV102->query($schQuery)->result_array();
            if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
            	//echo "<pre>".$this->SV102->last_query()."</pre>";
            }
            
        }else if($schFlag == "location"){
            $schQuery = "   SELECT COUNT(tot.mpIdx) AS totalCount FROM (SELECT PPDP.mpIdx
                            FROM placePensionBasic AS PPB
                            LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPB.mpIdx AND mmType ='YPS' AND mpType = 'PS'
                            LEFT JOIN pensionPrice AS PPDP ON PPDP.mpIdx = PPB.mpIdx AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                            LEFT JOIN pensionTodaySale AS PTS ON PTS.mpIdx = PPDP.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PPDP.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$dayNum." = '1'
                            LEFT JOIN placeTheme AS PT ON MPS.mpsIdx = PT.mpsIdx AND PT.mtCode LIKE '1%'                            
                            WHERE PPDP.ppdpSaleDay".$dayNum." > 0
                            AND PPDP.ppdpPercent".$dayNum." < 100
                            AND mpsOpen = '1'
                            AND PT.mtCode = '".$schVal."'";
            if($schSort == "2" || $schSort == "4" || $schSort == "5"){
                $schQuery .= "  AND PPB.ppbReserve != 'N'";
            }
            if($todaySale == "Y"){
            	$schQuery .= "  AND PTS.ptsSale > 0";
            }
            $schQuery .= "  GROUP BY PPDP.mpIdx) AS tot";
            $count = $this->SV102->query($schQuery)->row_array();
        
            $result['count'] = $count['totalCount'];
            
            $schQuery = "   SELECT
                                PPDP.mpIdx, MPS.mpsAddr1, MPS.mpsName,
                                IFNULL(PTS.ptsSale,0) AS ptsSale,
                                CASE WHEN peIdx THEN
                                    CASE peDay
                                        WHEN '1' THEN MIN(ppdpSaleDay1/100*(100-IFNULL(PTS.ptsSale,0)))
                                        WHEN '5' THEN MIN(ppdpSaleDay5/100*(100-IFNULL(PTS.ptsSale,0)))
                                        WHEN '6' THEN MIN(ppdpSaleDay6/100*(100-IFNULL(PTS.ptsSale,0)))
                                        WHEN '7' THEN MIN(ppdpSaleDay7/100*(100-IFNULL(PTS.ptsSale,0)))
                                    ELSE
                                        MIN(ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
                                    END
                                ELSE
                                    MIN(ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
                                END AS price,
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
                                END AS basicPrice,
                                PPB.ppbReserve, PPB.ppbGrade, PPB.ppbImage, PPB.ppbWantCnt, PPB.ppbEventFlag
                            FROM placePensionBasic AS PPB
                            LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPB.mpIdx AND mmType ='YPS' AND mpType = 'PS'
                            LEFT JOIN placePensionRoom AS PPR ON PPB.mpIdx = PPR.mpIdx AND PPR.pprOpen = '1'
                            LEFT JOIN pensionPrice AS PPDP ON PPDP.mpIdx = PPB.mpIdx AND PPR.pprIdx = PPDP.pprIdx AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                            LEFT JOIN pensionException AS PE ON PE.mpIdx = PPB.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                            LEFT JOIN pensionTodaySale AS PTS ON PTS.mpIdx = PPDP.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PPDP.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$dayNum." = '1'
                            LEFT JOIN placePensionUse AS PPU ON PPU.mpIdx = PPB.mpIdx
                            LEFT JOIN placeTheme AS PT ON MPS.mpsIdx = PT.mpsIdx AND PT.mtCode LIKE '1%'
                            WHERE PPR.pprIdx IS NOT NULL                            
                            AND PPDP.ppdpSaleDay".$dayNum." > 0
                            AND PPDP.ppdpPercent".$dayNum." < 100";
            if($idxStrings != ""){
                $schQuery .= " AND PPDP.mpIdx NOT IN (".$idxStrings.")";
            }
            if($schSort == "2" || $schSort == "4" || $schSort == "5"){
                $schQuery .= "  AND PPB.ppbReserve != 'N'";
            }
            if($todaySale == "Y"){
            	$schQuery .= "  AND PTS.ptsSale > 0";
            }
            $schQuery .= "  AND mpsOpen = '1'
                            AND PT.mtCode = '".$schVal."'
                            GROUP BY PPDP.mpIdx";
            if($schSort == "1"){
                $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND(), RAND(), PPB.ppbMainPension DESC, RAND()";
            }else if($schSort == "2"){
                $schQuery .= "  ORDER BY PPB.ppbWantCnt DESC, RAND()";                
            }else if($schSort == "4"){
                $schQuery .= "  ORDER BY price ASC, RAND()";
            }else if($schSort == "5"){
                $schQuery .= "  ORDER BY price DESC, RAND()";                
            }else{
                $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND(), RAND(), PPB.ppbMainPension DESC, RAND()";
            }
            if($iPod || $iPhone || $iPad ){
                
            }else{
                $schQuery .= "  LIMIT 20";
            }
            
            $result['lists'] = $this->SV102->query($schQuery)->result_array();
            if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
                //echo "<pre>".$this->SV102->last_query()."</pre>";
            }
            
        }else if($schFlag == "theme"){
            $schQuery = "   SELECT COUNT(tot.mpIdx) AS totalCount
            				FROM (
            					SELECT PPDP.mpIdx
                            	FROM placePensionBasic AS PPB
                            	LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPB.mpIdx AND mmType ='YPS' AND mpType = 'PS'
                            	LEFT JOIN pensionPrice AS PPDP ON PPDP.mpIdx = PPB.mpIdx
                            	LEFT JOIN pensionTodaySale AS PTS ON PTS.mpIdx = PPDP.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PPDP.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$dayNum." = '1'
                            	LEFT JOIN placePensionThemeFlag AS PPTF ON PPDP.mpIdx = PPTF.mpIdx
                            	WHERE '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                            	AND PPDP.ppdpSaleDay".$dayNum." > 0
                            	AND PPDP.ppdpPercent".$dayNum." < 100
                            	AND mpsOpen = '1'";
            if(strlen($schVal) > 10){
                $themeArray = explode(",", $schVal);
                for($i=0; $i< count($themeArray); $i++){
                    $schQuery .= "  AND PPTF.PS".str_replace(".","",$themeArray[$i])." = '1'";
                }
            }else{
                $schQuery .= "  AND PPTF.PS".str_replace(".","",$schVal)." = '1'";
            }
            if($schSort == "2" || $schSort == "4" || $schSort == "5"){
                $schQuery .= "  AND PPB.ppbReserve != 'N'";
            }
            if($todaySale == "Y"){
            	$schQuery .= "  AND PTS.ptsSale > 0";
            }
            $schQuery .= "  GROUP BY PPDP.mpIdx) AS tot";
            $count = $this->SV102->query($schQuery)->row_array();
        
            $result['count'] = $count['totalCount'];
            
                $schQuery = "   SELECT
                                    PPDP.mpIdx, MPS.mpsAddr1, MPS.mpsName,
                                    IFNULL(PTS.ptsSale,0) AS ptsSale,
                                    CASE WHEN peIdx THEN
                                        CASE peDay
                                            WHEN '1' THEN MIN(ppdpSaleDay1/100*(100-IFNULL(PTS.ptsSale,0)))
                                            WHEN '5' THEN MIN(ppdpSaleDay5/100*(100-IFNULL(PTS.ptsSale,0)))
                                            WHEN '6' THEN MIN(ppdpSaleDay6/100*(100-IFNULL(PTS.ptsSale,0)))
                                            WHEN '7' THEN MIN(ppdpSaleDay7/100*(100-IFNULL(PTS.ptsSale,0)))
                                        ELSE
                                            MIN(ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
                                        END
                                    ELSE
                                        MIN(ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
                                    END AS price,
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
                                    END AS basicPrice,
                                    PPB.ppbReserve, PPB.ppbGrade, PPB.ppbImage, PPB.ppbWantCnt, PPB.ppbEventFlag
                                FROM mergePlaceSite AS MPS
                                LEFT JOIN placePensionBasic AS PPB ON MPS.mpIdx = PPB.mpIdx
                                LEFT JOIN placePensionRoom AS PPR ON PPR.mpIdx = MPS.mpIdx AND PPR.pprOpen = '1'
                                LEFT JOIN pensionPrice AS PPDP ON PPDP.mpIdx = MPS.mpIdx AND PPDP.pprIdx = PPR.pprIdx AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                                LEFT JOIN pensionException AS PE ON PE.mpIdx = PPDP.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                                LEFT JOIN pensionTodaySale AS PTS ON PTS.mpIdx = PPDP.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PPDP.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$dayNum." = '1'
                                LEFT JOIN placePensionUse AS PPU ON PPU.mpIdx = PPB.mpIdx
                                LEFT JOIN placePensionThemeFlag AS PPTF ON PPDP.mpIdx = PPTF.mpIdx
                                WHERE mmType ='YPS' AND mpType = 'PS'
                                AND PPR.pprIdx IS NOT NULL
                                AND PPDP.ppdpSaleDay".$dayNum." > 0
                                AND PPDP.ppdpPercent".$dayNum." < 100";
            
            if($idxStrings != ""){
                $schQuery .= " AND PPDP.mpIdx NOT IN (".$idxStrings.")";
            }
            
            $schQuery .= "  AND mpsOpen = '1'";
            if(strlen($schVal) > 10){
                $themeArray = explode(",", $schVal);
                for($i=0; $i< count($themeArray); $i++){
                    $schQuery .= "  AND PPTF.PS".str_replace(".","",$themeArray[$i])." = '1'";
                }
            }else{
                $schQuery .= "  AND PPTF.PS".str_replace(".","",$schVal)." = '1'";
            }
            if($schSort == "2" || $schSort == "4" || $schSort == "5"){
                $schQuery .= "  AND PPB.ppbReserve != 'N'";
            }
            if($todaySale == "Y"){
            	$schQuery .= "  AND PTS.ptsSale > 0";
            }
            $schQuery .= "  GROUP BY PPDP.mpIdx";
            if($schSort == "1"){
                $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND(), RAND(), PPB.ppbMainPension DESC, RAND()";
            }else if($schSort == "2"){
                $schQuery .= "  ORDER BY PPB.ppbWantCnt DESC, RAND()";                
            }else if($schSort == "4"){
                $schQuery .= "  ORDER BY price ASC, RAND()";
            }else if($schSort == "5"){
                $schQuery .= "  ORDER BY price DESC, RAND()";                
            }else{
                $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND(), RAND(), PPB.ppbMainPension DESC, RAND()";
            }
            
            if($iPod || $iPhone || $iPad ){
                
            }else{
                $schQuery .= "  LIMIT 20";
            }
                        
            $result['lists'] = $this->SV102->query($schQuery)->result_array();
			
			if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
				//echo "<pre>".$this->SV102->last_query()."</pre>";
			}
        }    
        
        return $result;
    }

    function getPensionInfo($mpIdx){
        $date = date('Y-m-d');
            
        $dateObj = new DateTime($date);
        $dayNum = $dateObj->format('N');
        
        if($dayNum < 5){
            $dayNum = "1";
        }
        
        $schQuery = "   SELECT
                            MPS.mpsMapX, MPS.mpsMapY, MPS.mpsName, MPS.mpsAddr1, MPS.mpsAddr2, MPS.mpsAddr1New, MPS.mpsTelService, MPS.mpsIdx, 
                            PPB.ppbWantCnt, PPB.ppbReserve, PPB.ppbTel1, PPB.ppbTel2, PPB.ppbTel3, ppbEventFlag, PPB.mpIdx,
                            CASE WHEN PEN.peIdx THEN
                                CASE PEN.peDay
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
                            CASE WHEN PEN.peIdx THEN
                                CASE PEN.peDay
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
                            CASE WHEN PEN.peIdx THEN
                                CASE PEN.peDay
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
                            IFNULL(PE.peIdx,'') as peIdx, PE.peTitle, PE.peStartDate, PE.peEndDate,
                            PPU.ppuPullFlag,
                            PT.theme, PPB.ppbImage, PPB.ppbSubPension
                        FROM placePensionBasic AS PPB
                        LEFT JOIN pensionException AS PEN ON PEN.mpIdx = PPB.mpIdx AND PEN.peSetDate = '".date('Y-m-d')."' AND PEN.peUseFlag = 'Y'
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPB.mpIdx AND MPS.mmType ='YPS' AND MPS.mpType = 'PS'
                        LEFT JOIN pensionPrice AS PPDP ON PPDP.mpIdx = PPB.mpIdx AND PPDP.ppdpSaleDay".$dayNum." > 0 AND PPDP.ppdpPercent".$dayNum." < 100 AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd 
                        LEFT JOIN placePensionRoom AS PPR ON PPDP.pprIdx = PPR.pprIdx AND PPDP.mpIdx = PPR.mpIdx  
                        LEFT JOIN placePensionUse AS PPU ON PPB.mpIdx = PPU.mpIdx
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
                            AND MT.mtSite = 'YPS'
                            AND MT.mtCode LIKE '2%'
                            GROUP BY PT.mpsIdx
                        ) AS PT ON PT.mpsIdx = MPS.mpsIdx
                        WHERE PPB.mpIdx = '".$mpIdx."'
                        AND MPS.mpsOpen = '1' 
                        AND PPR.pprOpen = '1' 
                        ORDER BY price ASC 
                        LIMIT 1";
        $result = $this->SV102->query($schQuery)->row_array();
        
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
    
    function getPensionRoomTypeLists($mpIdx){
        $date = date('Y-m-d');
            
        $dateObj = new DateTime($date);
        $dayNum = $dateObj->format('N');
        
        $holyQuery = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday WHERE hDate-INTERVAL 1 DAY = '".$date."'";
        $holyRow = $this->SV102->query($holyQuery)->row_array();
        
        
        if(isset($holyRow['hIdx'])){    // 공휴일 날짜가 있을경우
            $flag_sql = "SELECT COUNT(*) AS cnt FROM holidayExclude WHERE hIdx = '".$holyRow['hIdx']."' AND mpIdx = '".$mpIdx."'";
            $flag_arr = $this->SV102->query($flag_sql)->row_array();
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
                        
        $schQuery = "   SELECT PRT.prtIdx, PRT.prtName, PPR.pprSize, PPR.pprInMin, PPR.pprInMax, PPR.pprShape, PPR.pprFloorM, PPR.pprFloorS, PPDP.ppdpSaleDay".$dayNum." AS price, PRT.prtSort, PRT.pprIdxRepr, PPDP.* , PPR.pprIdx
                        FROM pensionRoomType AS PRT
                        LEFT JOIN placePensionRoom AS PPR ON PPR.pprIdx = PRT.pprIdxRepr AND PPR.mpIdx = PRT.mpIdx
                        LEFT JOIN pensionPrice AS PPDP ON PPDP.pprIdx = PRT.pprIdxRepr AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                        WHERE PRT.mpIdx = '".$mpIdx."' 
                        AND PPR.pprOpen > 0
                        AND PRT.pprIdxRepr > 0
                        GROUP BY PRT.prtIdx
                        ORDER BY PRT.prtSort DESC ";                
        $result = $this->SV102->query($schQuery)->result_array();
    
        return $result;
    }

    function getPensionRoom($pprIdx){
        $this->SV102->where('pprIdx', $pprIdx);
        $result = $this->SV102->get('placePensionRoom')->row_array();
        
        return $result;
    }
    
    function getPensionRoomType($mpIdx){
        $typeRowQuery = "SELECT * FROM `placePensionBasic` WHERE mpIdx = '".$mpIdx."' ";
        $typeRow = $this->SV102->query($typeRowQuery)->row_array();
        return $typeRow;
    }
    
    // ****************************************************** 지역상단 롤링배너 리스트 *********************************************************
    
    function topLocRolBanner($idx){ // 메인 인기지역 리스트\
        $setDate = date('Y-m-d');
        $this->SV102->start_cache();
        $this->SV102->like('altrbLocal', $idx);
        if($_SERVER['REMOTE_ADDR'] != YAPEN_SALE_EVENT_TEST_IP){
        	$this->SV102->where("'$setDate' BETWEEN altrbStartDate AND altrbEndDate",'',false);
		}
    	$this->SV102->where('altrbOpen', '1');
        
        $this->SV102->stop_cache();
        $result['count']    = $this->SV102->count_all_results('pensionDB.appLocTopRollingBanner');

        $this->SV102->select('altrbIdx,altrbTitle,altrbFilename1 AS altrbFilename, mpIdx, pensionName, altrbLocal');
        $this->SV102->order_by('altrbSort', 'asc');
        $this->SV102->order_by('rand()');
        $result['lists'] = $this->SV102->get('pensionDB.appLocTopRollingBanner')->result_array();
        
        $this->SV102->flush_cache();
       
        return $result;
    }
    // ****************************************************** 지역상단 롤링배너 리스트 *********************************************************
    
    function getVillaLists($idxString){        
        $this->SV102->join('placePensionBasic AS PPB','PPB.mpIdx = PPU.mpIdx','LEFT');
        $this->SV102->join('mergePlaceSite AS MPS',"MPS.mpIdx = PPU.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->SV102->where('PPB.ppbReserve','R');
        $this->SV102->where('MPS.mpsOpen','1');
        $this->SV102->where('PPU.ppuPullFlag','1');
        $result['count'] = $this->SV102->count_all_results('placePensionUse AS PPU');
        
        $this->SV102->select('MPS.mpsName, MPS.mpsAddr1, PPB.mpIdx, PPB.ppbImage');
        $this->SV102->join('placePensionBasic AS PPB','PPB.mpIdx = PPU.mpIdx','LEFT');
        $this->SV102->join('mergePlaceSite AS MPS',"MPS.mpIdx = PPU.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->SV102->where('PPB.ppbReserve','R');
        $this->SV102->where('MPS.mpsOpen','1');
        $this->SV102->where('PPU.ppuPullFlag','1');
        if(count($idxString) > 0){
            $this->SV102->where_not_in('PPU.mpIdx', $idxString);
        }
        $this->SV102->order_by('rand()');
        $result['lists'] = $this->SV102->get('placePensionUse AS PPU')->result_array();
        
        return $result;
    }
    
    function getNewLists($idxString){
        $setDate = date('Y-m-d');
        
        $this->SV102->join('placePensionBasic AS PPB','PPB.mpIdx = PN.mpIdx','LEFT');
        $this->SV102->join('mergePlaceSite AS MPS',"MPS.mpIdx = PN.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->SV102->where('MPS.mpsOpen','1');
        $this->SV102->where('PPB.ppbOpenDate != 0000-00-00');
        $this->SV102->where("'$setDate' BETWEEN PN.pnStart AND PN.pnEnd",'',false);
        $count = $this->SV102->count_all_results('pensionNew AS PN');
        $result['count'] = 30-$count; 
        
        $this->SV102->select('MPS.mpsName, MPS.mpsAddr1, PPB.mpIdx, PPB.ppbImage, PPB.ppbOpenDate');
        $this->SV102->join('mergePlaceSite AS MPS',"MPS.mpIdx = PPB.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        //$this->SV102->where('PPB.ppbReserve','R');
        $this->SV102->where('PPB.ppbOpenDate >= ','date_add(now(), interval -12 month)', false);
        $this->SV102->where('PPB.ppbOpenDate != 0000-00-00');
        $this->SV102->where('MPS.mpsOpen','1');
        if(count($idxString) > 0){
            $this->SV102->where_not_in('PPB.mpIdx', $idxString);
        }
        $this->SV102->order_by('rand()');
        $result['lists'] = $this->SV102->get('placePensionBasic AS PPB', $result['count'])->result_array();
        
        return $result;
    }

    function getNewAdLists(){
        $setDate = date('Y-m-d');
        $this->SV102->join('placePensionBasic AS PPB','PPB.mpIdx = PN.mpIdx','LEFT');
        $this->SV102->join('mergePlaceSite AS MPS',"MPS.mpIdx = PN.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->SV102->where('MPS.mpsOpen','1');
        $this->SV102->where('PN.pnOpen','Y');
        $this->SV102->where('PPB.ppbOpenDate != 0000-00-00');
        $this->SV102->where("'$setDate' BETWEEN PN.pnStart AND PN.pnEnd",'',false);
        $result['count'] = $this->SV102->count_all_results('pensionNew AS PN');
        
        $dayNum = date('N', strtotime(date('Y-m-d')));
        if($dayNum < 5){
            $dayNum = 1;
        }
        $schQuery = "   SELECT MPS.mpsName, MPS.mpsAddr1, PPB.mpIdx, PPB.ppbImage, PPB.ppbOpenDate, PN.pnImage, PN.pnIdx, PN.pnTag, PN.pnIdx, IFNULL(PNI.pniImage,'') AS pniImage, PPB.ppbReserve, PPB.ppbOnline, 
                        IFNULL(PTS.ptsSale,0) AS ptsSale,
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
                        END AS basicPrice,
                        CASE WHEN peIdx THEN
                            CASE peDay
                                WHEN '1' THEN MIN(ppdpSaleDay1/100*(100-IFNULL(PTS.ptsSale,0)))
                                WHEN '5' THEN MIN(ppdpSaleDay5/100*(100-IFNULL(PTS.ptsSale,0)))
                                WHEN '6' THEN MIN(ppdpSaleDay6/100*(100-IFNULL(PTS.ptsSale,0)))
                                WHEN '7' THEN MIN(ppdpSaleDay7/100*(100-IFNULL(PTS.ptsSale,0)))
                            ELSE
                                MIN(ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
                            END
                        ELSE
                            MIN(ppdpSaleDay".$dayNum."/100*(100-IFNULL(PTS.ptsSale,0)))
                        END AS resultPrice
                        FROM (pensionNew AS PN)
                        LEFT JOIN pensionNewImage AS PNI ON PNI.pnIdx = PN.pnIdx AND PNI.pniRepr = '1'
                        LEFT JOIN placePensionBasic AS PPB ON PPB.mpIdx = PN.mpIdx
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PN.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'
                        LEFT JOIN pensionDB.pensionPrice AS PP ON PP.mpIdx = PN.mpIdx AND '".date('Y-m-d')."' BETWEEN ppdpStart AND ppdpEnd
                        LEFT JOIN pensionDB.pensionException AS PE ON PE.mpIdx = PN.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y' 
                        LEFT JOIN placePensionRoom AS PPR ON PPR.pprIdx = PP.pprIdx AND PPR.mpIdx = PN.mpIdx 
                        LEFT JOIN pensionTodaySale AS PTS ON PTS.mpIdx = PPR.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PPR.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$dayNum." = '1'
                        WHERE MPS.mpsOpen =  '1'
                        AND PPB.ppbOpenDate != 0000-00-00
                        AND '".date('Y-m-d')."' BETWEEN PN.pnStart AND PN.pnEnd
                        AND PN.pnOpen = 'Y' 
                        AND PPR.pprOpen != '0' 
                        GROUP BY PN.pnIdx
                        ORDER BY pnSort DESC, RAND()";
        
        $result['lists'] = $this->SV102->query($schQuery)->result_array();
        
        
        return $result;
    }
    
    function getJejuLists($idxString){
        $result['count'] = $this->SV102->count_all_results('pensionJeju');
        
        $this->SV102->join('mergePlaceSite AS MPS',"MPS.mpIdx = PJ.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->SV102->join('placePensionBasic AS PPB','PPB.mpIdx = PJ.mpIdx','LEFT');
        $this->SV102->where('MPS.mpsOpen','1');
        $this->SV102->order_by('PJ.pjSort','DESC');
        $this->SV102->order_by('rand()');
        if(count($idxString) > 0){
            $this->SV102->where_not_in('PJ.mpIdx', $idxString);
        }
        $result['lists'] = $this->SV102->get('pensionJeju AS PJ')->result_array();
        
        return $result;
    }
    
    function getSubPensionLists($connectIdx){
        $connectIdx = array_filter($connectIdx);
        $result = "";
        if(count($connectIdx) > 0){
            $this->SV102->where_in('ppcnIdx', $connectIdx);
            $lists = $this->SV102->get('placePensionConnectName')->result_array();
            
            
            if(count($lists) > 0){
                foreach($lists as $lists){
                    $result.= ",".$lists['ppcnPensionName'];
                }
                if($result != ""){
                    $result = substr($result,1);
                }
            }
        }
        
        
        return $result;
    }

    function getNewAdInfo($pnIdx){
        $schQuery = "   SELECT MPS.mpsName, MPS.mpsAddr1, PPB.mpIdx, PPB.ppbImage, PPB.ppbOpenDate, PN.pnImage, PN.pnIdx, PN.pnTag, PN.pnIdx, PPB.ppbReserve, 
                        CASE WHEN peIdx THEN
                            CASE peDay
                                WHEN '1' THEN MAX(ppdpPercent1)
                                WHEN '5' THEN MAX(ppdpPercent5)
                                WHEN '6' THEN MAX(ppdpPercent6)
                                WHEN '7' THEN MAX(ppdpPercent7)
                            ELSE
                                MAX(ppdpPercent1)
                            END
                        ELSE
                            MAX(ppdpPercent1)
                        END AS percent
                        FROM (pensionNew AS PN)
                        LEFT JOIN placePensionBasic AS PPB ON PPB.mpIdx = PN.mpIdx
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PN.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'
                        LEFT JOIN pensionDB.pensionPrice AS PP ON PP.mpIdx = PN.mpIdx AND '".date('Y-m-d')."' BETWEEN ppdpStart AND ppdpEnd
                        LEFT JOIN pensionDB.pensionException AS PE ON PE.mpIdx = PN.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                        WHERE PN.pnIdx = '".$pnIdx."' 
                        GROUP BY PN.pnIdx
                        ORDER BY pnSort DESC, RAND()";
                        
        $result = $this->SV102->query($schQuery)->row_array();
        
        return $result;
    }
    
    function getNewAdImageLists($pnIdx){
        $this->SV102->where('pnIdx', $pnIdx);
        $this->SV102->where('pniRepr','0');
        $this->SV102->order_by('pniSort','ASC');
        $result = $this->SV102->get('pensionNewImage')->result_array();
        
        return $result;
    }
    
    function getMainBanner(){
        $date = date('Y-m-d');
        $this->db->where("'$date' BETWEEN ambStart AND ambEnd",'',FALSE);
        $this->db->where('ambFlag','1');
        $this->db->order_by('ambSort','DESC');
        $result = $this->db->get('appMainBanner')->result_array();
        
        return $result;
    }
}
?>