<?php
class Pension_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    // ****************************************************** 메인 *********************************************************
    public function mainEventBanner(){  //  메인 이벤트 배너
        $this->db->select('amebIdx,amebFilename,amebContent');
        $this->db->from('pensionDB.appMainEventBanner');
        $this->db->where('amebOpen','1');        
        $this->db->order_by('', 'random');
        $this->db->limit(1);
        return $this->db->get();
    }

    public function mainTopBanner(){   // 기획전 배너
        $schQuery = "   SELECT amtbIdx,amtbTitle,amtbFilename1 as amtbFilename, amtbBannerFlag, amtbReturnVal, amtbWidth, amtbHeight
                        FROM pensionDB.appMainTopBanner
                        WHERE 1=1 AND amtbOpen = '1' ";
        if(preg_match( '/(iPod|iPhone|iPad)/', $_SERVER[ 'HTTP_USER_AGENT' ])){
            $schQuery .= " AND amtbIdx != '157'";
        }
        $schQuery .= "  
                        AND '".date('Y-m-d')."' BETWEEN amtbStartDate AND amtbEndDate
                        ORDER BY amtbSort ASC, rand()
                        ";
        $result = $this->db->query($schQuery)->result_array();
        return $result;
    }
    
    public function mainLolBanner(){    // 기획전 배너
        $schQuery = "   SELECT amlbIdx,amlbTitle,amlbFilename1 as amlbFilename, amlbReturnVal, amlbBannerFlag
                        FROM pensionDB.appMainLolBanner
                        WHERE '".date('Y-m-d')."' BETWEEN amlbStartDate AND amlbEndDate AND amlbOpen = '1' ";
        $schQuery .= "            
                        ORDER BY amlbSort ASC, rand()
                        ";
        $result = $this->db->query($schQuery)->result_array();
        return $result;
    }

    public function mainLocBanner(){    // 인기지역 추천 펜션
        $schQuery = "   SELECT amlbIdx,amlbName,amlbContent,amlbColor,amlbColorF
                        FROM pensionDB.appMainLocBanner
                        WHERE amlbOpen = '1'
                        AND '".date('Y-m-d')."' BETWEEN amlbStartDate AND amlbEndDate
                        ORDER BY amlbSort ASC, rand()
                        ";
        $result = $this->db->query($schQuery)->result_array();
        /*
        $this->db->select('amlbIdx,amlbName,amlbContent,amlbColor,amlbColorF');
        $this->db->where('amlbOpen !=',0);
        $this->db->order_by('amlbSort asc, rand()');

        $result = $this->db->get('pensionDB.appMainLocBanner')->result_array();
        */
        return $result;
    }

    public function mainLocBannerTitle(){   // 인기지역 추천 펜션

        return $this->db->get('pensionDB.appMainLocBannerTitle')->row()->amlbtTitle;
    }
    // ****************************************************** 메인 *********************************************************


    // ****************************************************** 인기배너 리스트 *********************************************************

    public function topBannerBanner($idx){  // 메인 인기지역 배너
        return $this->db->select('amtbFilename2 as amtbFilename, amtbWidth, amtbHeight')->where('amtbIdx', $idx)->get('pensionDB.appMainTopBanner')->row_array();
    }
    
    public function lolBannerBanner($idx){ // 메인 인기지역 배너
        return $this->db->select('amlbFilename2 as amlbFilename')->where('amlbIdx', $idx)->get('pensionDB.appMainLolBanner')->row_array();
    }

    public function topBannerList($data){  // 메인 인기지역 리스트   //기획전 밑의 배너 클릭시 list
        $date = date('Y-m-d');
            
        $dateObj = new DateTime($date);
        $dayNum = $dateObj->format('N');
        if($dayNum < 5){
            $dayNum = "1";
        }
        
        $schQuery = "   SELECT COUNT(*) AS totalCnt FROM appMainTopBannerJoin WHERE amtbIdx = '".$data['idx']."'";
        $cntArray = $this->db->query($schQuery)->row_array();
        if($cntArray['totalCnt'] == 0){
            $result['count'] = NULL;
        }else{
            $result['count'] = $cntArray['totalCnt'];
        }
        
        
        $schQuery = "   SELECT MPS.mpIdx, MPS.mpsAddr1, PPB.ppbImage, MPS.mpsName, PPB.ppbWantCnt, PPB.ppbReserve, PPB.ppbEventFlag, MIN(PPDP.ppdpSaleDay".$dayNum.") AS price, MAX(PPDP.ppdpPercent".$dayNum.") AS percent, AMTB.amtbWidth, AMTB.amtbHeight
                        FROM appMainTopBannerJoin AS MTB
                        LEFT JOIN appMainTopBanner AS AMTB ON MTB.amtbIdx = AMTB.amtbIdx
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = MTB.mpIdx AND MPS.mmType LIKE '%YPS%' AND MPS.mpType = 'PS'
                        LEFT JOIN placePensionBasic AS PPB ON MTB.mpIdx = PPB.mpIdx
                        LEFT JOIN pensionPrice AS PPDP ON PPDP.mpIdx = MTB.mpIdx AND PPDP.ppdpSaleDay".$dayNum." > 0 AND PPDP.ppdpPercent".$dayNum." < 100 AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                        WHERE MTB.amtbIdx = '".$data['idx']."'
                        AND MPS.mpsOpen = '1'
                        GROUP BY PPDP.mpIdx
                        ORDER BY MTB.amtbSort ASC, RAND()";
        $result['query'] = $this->db->query($schQuery)->result_array();
        
        $schQuery = "   SELECT MPS.mpIdx, MPS.mpsAddr1, PPB.ppbImage, MPS.mpsName, PPB.ppbWantCnt, PPB.ppbReserve, PPB.ppbEventFlag, AMTB.amtbWidth, AMTB.amtbHeight, 
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
                        END AS price
                        FROM appMainTopBannerJoin AS MTB
                        LEFT JOIN appMainTopBanner AS AMTB ON MTB.amtbIdx = AMTB.amtbIdx
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = MTB.mpIdx AND MPS.mmType LIKE '%YPS%' AND MPS.mpType = 'PS'
                        LEFT JOIN placePensionBasic AS PPB ON MTB.mpIdx = PPB.mpIdx
                        LEFT JOIN pensionPrice AS PPDP ON PPDP.mpIdx = MTB.mpIdx AND PPDP.ppdpSaleDay".$dayNum." > 0 AND PPDP.ppdpPercent".$dayNum." < 100 AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                        LEFT JOIN pensionException AS PE ON PE.mpIdx = PPDP.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                        WHERE MTB.amtbIdx = '".$data['idx']."'
                        AND MPS.mpsOpen = '1'
                        AND '".date('Y-m-d')."' BETWEEN MTB.amtbStart AND MTB.amtbEnd 
                        GROUP BY PPDP.mpIdx
                        ORDER BY MTB.amtbSort ASC, RAND()
                    ";
        $result['query'] = $this->db->query($schQuery)->result_array();
        /*
        $this->db->start_cache();
        $this->db->where('MTB.amtbIdx', $data['idx']);
        $this->db->where('PS.mmType', 'YPS');   // 타입
        $this->db->where('PS.mpType', 'PS');    // 타입
        $this->db->join('pensionDB.mergePlaceSite PS', 'MTB.mpIdx = PS.mpIdx');
        $this->db->join('pensionDB.placePensionBasic PB', 'MTB.mpIdx = PB.mpIdx');
        $this->db->where('PS.mpsOpen > ', '0'); // 게시
        $this->db->stop_cache();

        $result['count'] = $this->db->count_all_results('pensionDB.appMainTopBannerJoin MTB');

        $this->db->order_by('MTB.amtbSort', 'asc');
        $this->db->order_by('rand()');
        $this->db->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PB.ppbImage,PB.ppbRoomMin,PB.ppbReserve, PB.ppbEventFlag');
        $result['query'] = $this->db->get('pensionDB.appMainTopBannerJoin MTB', $data['limit'], $data['offset'])->result_array();
        if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
            //echo $this->db->last_query();
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
        
        

        $this->db->flush_cache();
        */
        return $result;
    }

    public function lolBannerList($data){   // 메인 인기지역 리스트
        $this->db->start_cache();
        $this->db->where('MLB.amlbIdx', $data['idx']);
        $this->db->where('PS.mmType', 'YPS');   // 타입
        $this->db->where('PS.mpType', 'PS');    // 타입
        $this->db->join('pensionDB.mergePlaceSite PS', 'MLB.mpIdx = PS.mpIdx');
        $this->db->join('pensionDB.placePensionBasic PB', 'MLB.mpIdx = PB.mpIdx');
        $this->db->where('PS.mpsOpen > ', '0'); // 게시
        $this->db->stop_cache();

        $result['count'] = $this->db->count_all_results('pensionDB.appMainLolBannerJoin MLB');

        $this->db->order_by('MLB.amlbSort', 'asc');
        $this->db->order_by('PS.mpIdx', 'desc');
        $this->db->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PB.ppbImage,PB.ppbRoomMin,PB.ppbReserve, PB.ppbEventFlag');
        $result['query'] = $this->db->get('pensionDB.appMainLolBannerJoin MLB', $data['limit'], $data['offset'])->result_array();
        
            
        
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
        
        

        $this->db->flush_cache();
        return $result;
    }

    // ****************************************************** 인기배너 리스트 *********************************************************


    // ****************************************************** 인기지역 리스트 *********************************************************

    public function locBannerList($data){   // 메인 인기지역 리스트
        $this->db->start_cache();
        $this->db->where('MLB.amlbIdx', $data['idx']);
        $this->db->where('PS.mmType LIKE "%YPS%"');
        $this->db->where('PS.mpType', 'PS');
        $this->db->where('PS.mpsOpen > ', '0');
        $this->db->join('pensionDB.mergePlaceSite PS', 'MLB.mpIdx = PS.mpIdx');
        $this->db->join('pensionDB.placePensionBasic PB', 'MLB.mpIdx = PB.mpIdx');
        $this->db->stop_cache();
        $result['count']    = $this->db->count_all_results('pensionDB.appMainLocBannerJoin MLB');

        $this->db->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PB.ppbImage,PB.ppbRoomMin, PB.ppbReserve');
        $this->db->order_by('MLB.viewSort', 'asc');
        $this->db->order_by('rand()');
        $result['obj']      = $this->db->get('pensionDB.appMainLocBannerJoin MLB', $data['limit'], $data['offset'] );
        $this->db->flush_cache();
        
        return $result;
    }

    // ****************************************************** 인기지역 리스트 *********************************************************
    
    

    // ************************************************** 펜션>지역>인기/일반지역 *****************************************************
    public function getThemePlaceCategory() {
        $popLists = array();
        $popIdx = 0;
        /* 지역별 장소통계 START */
        $this->db->select('PT.mtIdx, count(MPS.mpIdx) AS cnt');
        $this->db->from('pensionDB.placeTheme PT');
        $this->db->join('pensionDB.mergePlaceSite MPS', 'PT.mpsIdx = MPS.mpsIdx', 'left');
        $this->db->where('MPS.mpsOpen', 1);
        $this->db->where('MPS.mmType','YPS');
        $this->db->where('MPS.mpType','PS');
        //테스트펜션 제외
        $this->db->where('MPS.mpsIdx !=','167538');
        $this->db->where('INSTR(PT.mtCode, "1.")');
        $this->db->group_by('PT.mtIdx');
        //$this->db->order_by('MT.mtSort','ASC');
        $result['cnt'] = $this->db->get()->result_array();
        
        $themePlaceCnt = array();
        foreach ( $result['cnt'] as $key => $value )
        {
            $themePlaceCnt[$value['mtIdx']] = $value['cnt'];
        }
        /* 지역별 장소통계 END */
        
        
        
        
        $themePlaceList = array();
        
        /* 공통 쿼리 START */
        $this->db->start_cache();
        $this->db->from('pensionDB.mergeTheme MT');
        $this->db->where('INSTR(MT.mtCode, "1.")');
        $this->db->where('INSTR(MT.mtSite, "YPS")');
        $this->db->where('MT.mtType','PS');
        $this->db->where('MT.mtOpen', 1);
        //테스트펜션 제외
        //$this->db->where('MT.mpsIdx !=','167538');
        $this->db->stop_cache();
        $this->db->order_by('MT.mtSort','asc');
        /* 공통 쿼리 END */       
        
        
        /* 전체지역 START */
        $this->db->where('MT.mtDepth', 2);
        $result['lists2Dep'] = $this->db->get()->result_array();
        //echo $this->db->last_query();
        
        foreach ( $result['lists2Dep'] as $key => $value )
        {
            $lists3Dep = array();
            $lists3DepCnt[$key] = 0;
            $this->db->where('INSTR(MT.mtCode, "'.$value['mtCode'].'")');
            $this->db->where('MT.mtDepth', 3);
            $this->db->order_by('MT.mtSort','ASC');
            $result['lists3Dep'] = $this->db->get()->result_array();
            
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
        
        $this->db->flush_cache();
        
        return $themePlaceList;
    }

    public function getLocationTheme() {
        $result = array();
        $popLists = array();
        $countNotArray = array('1.009003');
        
        $schQuery = "   SELECT MT.mtCode, MT.mtName, MT.mtSort, MT.mtOpen, MT.mtFavorite
                        FROM mergeTheme AS MT
                        WHERE MT.mtSite LIKE '%YPS%'
                        AND MT.mtType = 'PS'
                        AND LENGTH(MT.mtCode) = 5
                        AND MT.mtCode LIKE '1%'
                        AND MT.mtOpen = '1'
                        ORDER BY MT.mtSort ASC";
        $mainLists = $this->db->query($schQuery)->result_array();
        $i=0;
        $p=0;
        /*
          * (2016-03-30)
          * 지역개수 불일치 수정
          * 수정자 : 이유진
         * 
         * 2016-04-15
         * 잘 되는거 이유진이 잘못 바궈서 버그생김. 그래서다시 원복함
         * 수정자 : 김영웅
        */
        foreach($mainLists as $mainLists){
            /*
            $subQuery = "   SELECT *, COUNT(mpIdx) AS cnt FROM ( 
                                SELECT MT.mtCode, MT.mtName, MT.mtSort, MT.mtOpen, MT.mtFavorite, MPS.mpIdx
                                FROM mergeTheme AS MT 
                                LEFT JOIN placeTheme AS PT ON MT.mtCode = PT.mtCode 
                                LEFT JOIN mergePlaceSite AS MPS ON MPS.mpsIdx = PT.mpsIdx AND MPS.mmType LIKE '%YPS%' AND mtType LIKE '%PS%' AND MPS.mpsOpen = '1'
                                WHERE MT.mtSite LIKE '%YPS%' 
                                AND MT.mtType LIKE '%PS%' 
                                AND MT.mtCode LIKE '1.%' 
                                AND LENGTH(MT.mtCode) = '8' 
                                AND MT.mtOpen = '1' 
                                AND SUBSTR(MT.mtCode,1,5) = '".$mainLists['mtCode']."'
                                AND MPS.mpsOpen = '1' ";
            if($mainLists['mtCode'] == '1.009'){ 
                $subQuery .=    "GROUP BY PT.mpsIdx";
            }                       
            $subQuery .=    ") AS AA
                            GROUP BY AA.mtCode
                            ORDER BY AA.mtSort ASC";
            */
            $subQuery = "   SELECT MT.mtCode, MT.mtName, MT.mtSort, MT.mtOpen, MT.mtFavorite, COUNT(MPS.mpIdx) AS cnt
                            FROM mergeTheme AS MT
                            LEFT JOIN placeTheme AS PT ON MT.mtCode = PT.mtCode
                            LEFT JOIN mergePlaceSite AS MPS ON PT.mpsIdx = MPS.mpsIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS' AND MPS.mpsOpen = '1' AND MPS.mpsIdx != '167538' AND MPS.mpsName NOT LIKE '%삭제%'
                            WHERE MT.mtSite LIKE '%YPS%'
                            AND MT.mtType = 'PS'
                            AND LENGTH(MT.mtCode) = 8
                            AND MT.mtCode LIKE '".$mainLists['mtCode']."%'
                            AND MT.mtOpen = '1'
                            GROUP BY MT.mtCode
                            ORDER BY MT.mtSort ASC";
            
            $subLists = $this->db->query($subQuery)->result_array();
            
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
        
        return $result;
    }

    // ************************************************** 펜션>일반예약/지역정보 *****************************************************
    public function getThemePlaceCategoryNormal() {
            
        /* 지역별 장소통계 START */
        $this->db->select('PT.mtIdx, count(MPS.mpIdx) AS cnt');
        $this->db->from('pensionDB.placeTheme PT');
        $this->db->join('pensionDB.mergePlaceSite MPS', 'PT.mpsIdx = MPS.mpsIdx', 'left');
        $this->db->join('pensionDB.placePensionBasic PPB', 'MPS.mpIdx = PPB.mpIdx', 'left');
        $this->db->where('MPS.mpsOpen', 1);
        $this->db->where('MPS.mmType','YPS');
        $this->db->where('MPS.mpType','PS');
        $this->db->where('PPB.ppbReserve','G');
        $this->db->where('INSTR(PT.mtCode, "1.")');
        $this->db->group_by('PT.mtIdx');
        $result['cnt'] = $this->db->get()->result_array();
        
        $themePlaceCnt = array();
        foreach ( $result['cnt'] as $key => $value )
        {
            $themePlaceCnt[$value['mtIdx']] = $value['cnt'];
        }
        /* 지역별 장소통계 END */
        
        
        $themePlaceList = array();
        
        /* 공통 쿼리 START */
        $this->db->start_cache();
        $this->db->from('pensionDB.mergeTheme MT');
        $this->db->where('INSTR(MT.mtCode, "1.")');
        $this->db->where('INSTR(MT.mtSite, "YPS")');
        $this->db->where('MT.mtType','PS');
        $this->db->where('MT.mtOpen', 1);
        $this->db->stop_cache();
        $this->db->order_by('MT.mtSort','asc');
        /* 공통 쿼리 END */

        /* 전체지역 START */
        $this->db->where('MT.mtDepth', 2);
        $result['lists2Dep'] = $this->db->get()->result_array();
        
        foreach ( $result['lists2Dep'] as $key => $value )
        {
            $lists3Dep = array();
            $lists3DepCnt[$key] = 0;
            $this->db->where('INSTR(MT.mtCode, "'.$value['mtCode'].'")');
            $this->db->where('MT.mtDepth', 3);
            $result['lists3Dep'] = $this->db->get()->result_array();
            
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
        
        $this->db->flush_cache();
        
        
        return $themePlaceList;
    }

    // ************************************************** 펜션>실시간조회>일반지역 *****************************************************
    public function getRThemePlaceCategory() {
            
        /* 지역별 장소통계 START */
        $this->db->select('PT.mtIdx, count(MPS.mpIdx) AS cnt');
        $this->db->from('pensionDB.placeTheme PT');
        $this->db->join('pensionDB.mergePlaceSite MPS', 'PT.mpsIdx = MPS.mpsIdx', 'left');
        $this->db->join('pensionDB.placePensionBasic PPB', 'MPS.mpIdx = PPB.mpIdx', 'left');
        $this->db->where('MPS.mpsOpen', 1);
        $this->db->where('MPS.mmType','YPS');
        $this->db->where('MPS.mpType','PS');
        $this->db->where('INSTR(PT.mtCode, "1.")');
        $this->db->where('PPB.ppbReserve','R');
        $this->db->group_by('PT.mtIdx');
        $result['cnt'] = $this->db->get()->result_array();

        $themePlaceCnt = array();
        foreach ( $result['cnt'] as $key => $value )
        {
            $themePlaceCnt[$value['mtIdx']] = $value['cnt'];
        }
        /* 지역별 장소통계 END */
        
        
        
        
        $themePlaceList = array();
        
        /* 공통 쿼리 START */
        $this->db->start_cache();
        $this->db->from('pensionDB.mergeTheme MT');
        $this->db->where('INSTR(MT.mtCode, "1.")');
        $this->db->where('INSTR(MT.mtSite, "YPS")');
        $this->db->where('MT.mtType','PS');
        $this->db->where('MT.mtOpen', 1);
        $this->db->stop_cache();
        $this->db->order_by('MT.mtSort','asc');
        /* 공통 쿼리 END */
        
        /* 전체지역 START */
        $this->db->where('MT.mtDepth', 2);
        $result['lists2Dep'] = $this->db->get()->result_array();
        
        
        foreach ( $result['lists2Dep'] as $key => $value )
        {
            $lists3Dep = array();
            $lists3DepCnt[$key] = 0;
            $this->db->where('INSTR(MT.mtCode, "'.$value['mtCode'].'")');
            $this->db->where('MT.mtDepth', 3);
            $this->db->order_by('MT.mtSort','ASC');
            $result['lists3Dep'] = $this->db->get()->result_array();
            
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
        
        $this->db->flush_cache();
        
        
        return $themePlaceList;
    }

    // 201405141450 pyh : mergeTheme 순서 컬럼을 이용하여 진행
    public function getThemePlaceList( $param ) {
        extract( $param ); 
        $this->db->start_cache();
        if( $param['favorite'] ) $this->db->where('MT.mtFavorite',(string)$param['favorite']);
        $this->db->where('INSTR(MT.mtCode, "'.$param['code'].'")');
        $this->db->where('INSTR(MT.mtSite, "YPS")');
        $this->db->where('MT.mtType','PS');
        $this->db->where('MT.mtDepth',$param['depth']);
        
        // 201405231435 pyh : 테마 재정리를 위해 추가, DB로 관리
        $this->db->where('MT.mtOpen','1');
        
        if( $param['depth'] == 3 ) $this->db->join('pensionDB.placeTheme PT', 'MT.mtIdx = PT.mtIdx');
        $this->db->stop_cache();

        if( $param['depth'] == 3 ) $this->db->join('pensionDB.mergePlaceSite MPS', 'PT.mpsIdx = MPS.mpsIdx');
        $result['count']    = $this->db->count_all_results('pensionDB.mergeTheme MT');

        $this->db->select('MT.*, COUNT(*) as sCnt');
        $this->db->group_by('MT.mtIdx');
        $this->db->order_by('MT.mtSort');
        $result['obj']      = $this->db->get('pensionDB.mergeTheme MT');

        $this->db->flush_cache();
        return $result;
    }
    // ************************************************** 펜션>지역>인기/일반지역 *****************************************************


    // ********************************************************* 펜션>테마 ************************************************************
    public function getThemeEtcList( $param ) {
        extract( $param ); 
        $this->db->start_cache();
        $this->db->where('MT.mtFavorite',(string)$param['favorite']);
        $this->db->where('MT.mtSite like "%YPS%"');
        $this->db->where('MT.mtType','DC');
        $this->db->where('MT.mtDepth','2');
        $this->db->join('pensionDB.placeTheme PT', 'MT.mtIdx = PT.mtIdx');
        $this->db->stop_cache();

        $this->db->join('pensionDB.mergePlaceSite MPS', 'PT.mpsIdx = MPS.mpsIdx');
        $result['count']    = $this->db->count_all_results('pensionDB.mergeTheme MT');

        $this->db->select('MT.*, COUNT(*) as sCnt');
        $this->db->group_by('MT.mtIdx');
        $result['obj']      = $this->db->get('pensionDB.mergeTheme MT');

        $this->db->flush_cache();
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
                       LEFT JOIN pensionDB.mergePlaceSite PS ON MTB.mpIdx = PS.mpIdx AND PS.mmType LIKE '%YPS%' AND PS.mpType LIKE '%PS%'
                       WHERE MTB.arbOpen = '1'
                       ORDER BY MTB.arbOrder DESC, RAND()";
        $countArray = $this->db->query($countQuery)->row_array();
        $result['count'] = $countArray['cnt'];
        
        $schQuery = "   SELECT PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PS.mpsAddrSi,PS.mpsAddrGu,MTB.arbFilename,MTB.arbTitle, MTB.arbWidth, MTB.arbHeight, PPB.ppbWantCnt, MTB.arbTag,
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
                                WHEN '1' THEN MIN(ppdpSaleDay1)
                                WHEN '5' THEN MIN(ppdpSaleDay5)
                                WHEN '6' THEN MIN(ppdpSaleDay6)
                                WHEN '7' THEN MIN(ppdpSaleDay7)
                            ELSE
                                MIN(ppdpSaleDay".$dayNum.")
                            END
                        ELSE
                            MIN(ppdpSaleDay".$dayNum.")
                        END AS resultPrice
                        FROM appRandomBanner AS MTB
                        LEFT JOIN pensionDB.mergePlaceSite PS ON MTB.mpIdx = PS.mpIdx AND PS.mmType = 'YPS' AND PS.mpType = 'PS'
                        LEFT JOIN pensionDB.placePensionBasic AS PPB ON PPB.mpIdx = MTB.mpIdx
                        LEFT JOIN pensionDB.pensionPrice AS PP ON PP.mpIdx = MTB.mpIdx AND '".date('Y-m-d')."' BETWEEN PP.ppdpStart AND PP.ppdpEnd
                        LEFT JOIN pensionDB.pensionException AS PE ON PE.mpIdx = MTB.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                        WHERE '".date('Y-m-d')."' BETWEEN MTB.arbStartDate AND MTB.arbEndDate
                        AND MTB.arbOpen = '1'
                        GROUP BY MTB.arbIdx
                        ORDER BY MTB.arbOrder DESC, RAND()";
        $result['query'] = $this->db->query($schQuery)->result_array();
        /*
        $this->db->start_cache();
        $this->db->where('PS.mmType', 'YPS');   // 타입
        $this->db->where('PS.mpType', 'PS');    // 타입
        $this->db->join('pensionDB.mergePlaceSite PS', 'MTB.mpIdx = PS.mpIdx', 'left');
        
        $this->db->where('MTB.arbOpen > ', '0');    // 게시
        $this->db->stop_cache();

        $result['count'] = $this->db->count_all_results('pensionDB.appRandomBanner MTB');

        $this->db->order_by('MTB.arbOrder', 'desc');
        $this->db->order_by('rand()');
        
        $this->db->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PS.mpsAddrSi,PS.mpsAddrGu,MTB.arbFilename,MTB.arbTitle');
        $result['query'] = $this->db->get('pensionDB.appRandomBanner MTB', $data['limit'], $data['offset'])->result_array();

        $this->db->flush_cache();
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
            $holyRow = $this->db->query($holyQuery)->row_array();
            // 세일 펜션
            $salePensionSaleIdxs = NULL;
            if( isset($data['sale']) && $data['sale'] == 1 ) 
            {
                $salePensionSaleIdxs = $this->salePensionSaleIdxs();
            }
            
            $holiDayCheck = $this->holidayCheck(date('Y-m-d'));

            $this->db->start_cache();
            
            /* 위도, 경도 검색 START */
            $distance = FALSE;
            if ( isset($data['latitude']) && isset($data['longitude']) )
            {
                $latitude = $data['latitude'];
                $longitude = $data['longitude'];
                $this->db->select("
                    PS.mpIdx,PS.mpsName,PS.mpsAddr1,PS.mpsMapX,PS.mpsMapY, 
                    ( 6371 * acos( cos( radians($latitude) ) * cos( radians(mpsMapY) ) * cos( radians(mpsMapX) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians(mpsMapY) ) ) ) AS distance "
                );
                $this->db->having('distance <=', 5);
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
                $this->db->where( $qWhere );
            }           
*/
            //테스트펜션 제외
            if( !empty( $data['locCode'] ) ) $this->db->like('MT.mtCode',$data['locCode']);
            if( !empty( $data['themeCode'] )){
                if(substr($data['themeCode'],0,1) != "1"){
                    $data['themeCode'] = explode(",",$data['themeCode']);
                    //$this->db->where_in('PT.mtCode', $data['themeCode']);
                    
                    for($i=0; $i< count($data['themeCode']); $i++){
                        $this->db->where('PPTP.PS'.str_replace(".","",$data['themeCode'][$i]), 1);
                    }
                }                
            }
            
            $this->db->join('placeTheme PT', 'MT.mtIdx = PT.mtIdx');
            $this->db->join('mergePlaceSite PS', 'PT.mpsIdx = PS.mpsIdx');
            $this->db->join('placePensionBasic PB', 'PS.mpIdx = PB.mpIdx');
            $this->db->join('placePensionThemeFlag AS PPTP','PPTP.mpIdx = PB.mpIdx','LEFT');
            
            // 세일 펜션
            if ( is_array($salePensionSaleIdxs) && count($salePensionSaleIdxs) > 0 )
            {
                $this->db->where_in( 'PS.mpIdx', $salePensionSaleIdxs );
            }            
            $this->db->like('MT.mtSite','YPS');
            $this->db->where('MT.mtType','PS');
            $this->db->where('PS.mpsOpen > ', '0');
            
            if( $data['search'] && empty( $data['locCode'] )){
                $data['search'] = str_replace(" ","%",$data['search']);
                $this->db->where("(PS.mpsName LIKE '%".$data['search']."%' OR CONCAT(PS.mpsAddr1,' ',mpsAddr2) LIKE '%".$data['search']."%')","",false);
            }
            //random 처리시 예외 시킬 idx들
            if( count($data['idxStrings']) > 3 ){
                    $this->db->where_not_in('PS.mpIdx', $data['idxStrings']);
            }

            $this->db->group_by('PS.mpIdx');
            
            $this->db->stop_cache();

            $result['count']    = $this->db->get('mergeTheme MT')->num_rows();
            

            $this->db->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PB.ppbImage,PB.ppbRoomMin,PB.ppbReserve, PB.ppbWantCnt, PB.ppbEventFlag');
            $this->db->select('PB.ppbGrade');
            
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

            $this->db->select("(SELECT CASE WHEN HE.heIdx THEN ppdpSaleDay".$toNumOfWeek." ELSE ppdpSaleDay".$numOfWeek." END AS price
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
                        $this->db->order_by('PB.ppbGrade', 'DESC');
                        $this->db->order_by('rand()');
                        break;
                    case ('2') :
                        $this->db->order_by('PB.ppbWantCnt', 'DESC');
                        $this->db->order_by('rand()');
                        break;
                    case ('4') :
                        $this->db->order_by('price', 'ASC');
                        $this->db->order_by('rand()');
                        break;
                    case ('5') :
                        $this->db->order_by('price', 'DESC');
                        $this->db->order_by('rand()');
                        break;
                    default :
                        $this->db->order_by('PB.ppbGrade', 'DESC');
                        $this->db->order_by('rand()');
                        break;
                }                
            }
            $this->db->order_by('PB.ppbGrade DESC, PB.ppbReserve', 'ASC');
            
            $this->db->having('price IS NOT NULL');
            
            $result['obj']      = $this->db->get('mergeTheme MT', $data['limit'], $data['offset'])->result();
            
            $this->db->flush_cache();
            //echo "<pre>".$this->db->last_query()."</pre>";
            
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
            

            $this->db->start_cache();
            
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
                $this->db->where( $qWhere );
            }           

            $this->db->join('placeTheme PT', 'MT.mtIdx = PT.mtIdx');
            $this->db->join('mergePlaceSite PS', 'PT.mpsIdx = PS.mpsIdx AND PS.mmType = \'YPS\'');
            $this->db->join('placePensionBasic PB', 'PS.mpIdx = PB.mpIdx');
            
            // 세일 펜션
            if ( is_array($salePensionSaleIdxs) && count($salePensionSaleIdxs) > 0 )
            {
                $this->db->where_in( 'PS.mpIdx', $salePensionSaleIdxs );
            }
            //echo var_dump($data['idxStrings']);
            //random 처리시 예외 시킬 idx들
            if( $data['random'] > 0 && count($data['idxStrings']) > 0 ){
                $this->db->where_not_in('PS.mpIdx',$data['idxStrings']);
            }
            
            $this->db->where('MT.mtSite LIKE "%YPS%"');
            $this->db->where('MT.mtType','PS');
            $this->db->where('PS.mpsOpen > ', '0');
            $this->db->group_by('PS.mpIdx');
            $reserve_arr = array('R','G');
            $this->db->where_in('PB.ppbReserve',$reserve_arr);
            $this->db->stop_cache();
            
            $result['count']    = $this->db->get('mergeTheme MT')->num_rows();
            //echo var_dump($result);
            //echo $this->db->last_query();
            $this->db->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PB.ppbImage,PB.ppbRoomMin,PB.ppbReserve');
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


                $this->db->order_by('PB.ppbGrade DESC, PB.ppbReserve',$orderBy);
            }
            
            //랜덤정렬
            if( $data['random'] ){
                $this->db->order_by('rand()');
            }else
                if( !$data['orderby'] ) $this->db->order_by('PS.mpsIdx', 'desc');

            //echo var_dump($data);
            $result['obj']      = $this->db->get('mergeTheme MT', $data['limit'])->result();
            $this->db->flush_cache();
            //echo $this->db->last_query();
            
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
            $holyRow = $this->db->query($holyQuery)->row_array();
            
            // 세일 펜션
            $salePensionSaleIdxs = NULL;
            if( isset($data['sale']) && $data['sale'] == 1 ) 
            {
                $salePensionSaleIdxs = $this->salePensionSaleIdxs();
            }
            
            $holiDayCheck = $this->holidayCheck(date('Y-m-d'));
            
            $this->db->start_cache();
            
            /* 위도, 경도 검색 START */
            $distance = FALSE;
            if ( isset($data['latitude']) && isset($data['longitude']) )
            {
                $latitude = $data['latitude'];
                $longitude = $data['longitude'];
                $this->db->select("
                    PS.mpIdx,PS.mpsName,PS.mpsAddr1,PS.mpsMapX,PS.mpsMapY, 
                    ( 6371 * acos( cos( radians($latitude) ) * cos( radians(mpsMapY) ) * cos( radians(mpsMapX) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians(mpsMapY) ) ) ) AS distance "
                );
                $this->db->having('distance <=', 5);
                $distance = TRUE;
            }
            //테스트펜션 제외
            if( !empty( $data['locCode'] ) ) $this->db->like('MT.mtCode',$data['locCode']);
            if( !empty( $data['themeCode'] )){
                if(substr($data['themeCode'],0,1) != "1"){
                    $data['themeCode'] = explode(",",$data['themeCode']);
                    //$this->db->where_in('PT.mtCode', $data['themeCode']);
                    
                    for($i=0; $i< count($data['themeCode']); $i++){
                        $this->db->where('PPTP.PS'.str_replace(".","",$data['themeCode'][$i]), 1);
                    }
                }                
            }
            if( $data['search'] ) $this->db->like('PS.mpsName',$data['search']);
            
            
            $this->db->join('placeTheme PT', 'MT.mtIdx = PT.mtIdx');
            $this->db->join('mergePlaceSite PS', 'PT.mpsIdx = PS.mpsIdx');
            $this->db->join('placePensionBasic PB', 'PS.mpIdx = PB.mpIdx');
            $this->db->join('placePensionThemeFlag AS PPTP','PPTP.mpIdx = PB.mpIdx','LEFT');
            
            // 세일 펜션
            if ( is_array($salePensionSaleIdxs) && count($salePensionSaleIdxs) > 0 )
            {
                $this->db->where_in( 'PS.mpIdx', $salePensionSaleIdxs );
            }

            //random 처리시 예외 시킬 idx들
            if( count($data['idxStrings']) > 3 ){
                    $this->db->where_not_in('PS.mpIdx', $data['idxStrings']);
            }
            
            $this->db->like('MT.mtSite','YPS');
            $this->db->where('MT.mtType','PS');
            $this->db->where('PS.mpsOpen > ', '0');
            $this->db->where('PB.ppbReserve','G');
            $this->db->group_by('PS.mpIdx');
            $this->db->stop_cache();

            $result['count']    = $this->db->get('mergeTheme MT')->num_rows();
            

            $this->db->select('PS.mpsIdx,PS.mpIdx,PS.mpsName,PS.mpsAddr1,PB.ppbImage,PB.ppbRoomMin,PB.ppbReserve, PB.ppbWantCnt');
            
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
        
            $this->db->select("(SELECT CASE WHEN HE.heIdx THEN ppdpSaleDay".$toNumOfWeek." ELSE ppdpSaleDay".$numOfWeek." END AS price
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
                        $this->db->order_by('PB.ppbGrade', 'DESC');
                        $this->db->order_by('rand()');
                        break;
                    case ('2') :
                        $this->db->order_by('PB.ppbWantCnt', 'DESC');
                        $this->db->order_by('rand()');
                        break;
                    case ('4') :
                        $this->db->order_by('price', 'ASC');
                        $this->db->order_by('rand()');
                        break;
                    case ('5') :
                        $this->db->order_by('price', 'DESC');
                        $this->db->order_by('rand()');
                        break;
                    default :
                        $this->db->order_by('PB.ppbGrade', 'DESC');
                        $this->db->order_by('rand()');
                        break;
                }                
            }
            $this->db->order_by('PB.ppbGrade DESC, PB.ppbReserve', 'ASC');
            $this->db->select('PB.ppbGrade');

            $result['obj']      = $this->db->get('mergeTheme MT', $data['limit'], $data['offset'])->result();
            
            $this->db->flush_cache();
            //echo "<pre>".$this->db->last_query()."</pre>";
            
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
        
        $this->db->where('ppsStartDate <= ', $dateTime);
        $this->db->where('ppsEndDate >= ', $dateTime);
        $result = $this->db->from('placePensionSale AS pps')->get()->result_array();
        
        return $result;
    }

    //검색어가 있는 테마코드를 가져온다
    function getThemeCode( $keyword ) {
        $this->db->where("mtSite LIKE '%YPS%'");
        $this->db->where('mtType', 'PS');
        $this->db->where('mtOpen > ', '0');
        $this->db->where("mtName LIKE '%".$keyword."%'");
        $this->db->where("mtCode LIKE '2.%'");
        $this->db->select('mtCode');
        $result = $this->db->get('mergeTheme')->result();
        
        return $result;
    }

    //펜션 이미지 카운트
    public function pensionImageCount( $mpIdx ) {       
        $this->db->select('(A.cnt+B.cnt) as cnt');
        $result = $this->db->from('(select count(mpIdx) as cnt from placePensionRoomPhoto where mpIdx='.$mpIdx.') A, (select count(mpIdx) as cnt from placePensionEtcPhoto where mpIdx='.$mpIdx.') B', FALSE)->get()->row_array();
        return $result['cnt'];
    }

    public function pensionMap($latitude, $longitude){ // 펜션지도 좌표

        $this->db->start_cache();
        $this->db->where('MPS.mmType LIKE "%YPS%"');    // 타입
        $this->db->where('MPS.mpType', 'PS');   // 타입
        $this->db->where('MPS.mpsOpen > ', '0');    // 게시
        $this->db->having('distance <=', 15); 
        $this->db->group_by('MPS.mpIdx');

        $this->db->stop_cache();

        $this->db->select("
            MPS.mpIdx,MPS.mpsName,MPS.mpsAddr1,MPS.mpsMapX,MPS.mpsMapY, PPB.ppbImage,
            ( 6371 * acos( cos( radians($latitude) ) * cos( radians(mpsMapY) ) * cos( radians(mpsMapX) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians(mpsMapY) ) ) ) AS distance "
        );
        $this->db->order_by("MPS.mpsIdx asc");
        $this->db->join('placePensionBasic AS PPB','PPB.mpIdx = MPS.mpIdx');
        $row =  $this->db->get('pensionDB.mergePlaceSite AS MPS');

        $result['count'] = $row->num_rows();
        $result['query'] = $row->result_array();

        $this->db->flush_cache();
        return $result;

    }

    public function pensionTheme(){
        $this->db->select('mtDepth,mtCode,mtName');
        $this->db->where('mtType','PS');
        $this->db->where('mtType > ', '2');
        $this->db->where('mtDepth > ', '1');

        $this->db->order_by('mtCode', 'asc');

        return $this->db->get('pensionDB.mergeTheme')->result_array();
    }

    // ****************************************************** 펜션 상세정보 *********************************************************

    public function pensionGetInfo($idx){
        $dateTime = date("Y-m-d H:i:s");

        // $this->db->select('
        //  PS.mpsIdx,PS.mpsName,PS.mpsAddr1,PS.mpsAddr2,PS.mpsMapX,PS.mpsMapY,mpsTelService,
        //  PB.ppbRoomMin,ppbReserve,
        //  PE.peIdx,PE.peTitle,PE.peIntro,PE.peStartDate,PE.peEndDate');
        // $this->db->where('PS.mpIdx ', $idx);
        // $this->db->where('PS.mpsOpen > ', 0);
        // $this->db->where('PS.mmType', 'YPS');    // 타입
        // $this->db->where('PS.mpType', 'PS'); // 타입
        // $this->db->group_by('PS.mpIdx');
        // 
        // $this->db->join('pensionDB.placePensionBasic PB', 'PS.mpIdx = PB.mpIdx'); // 기본정보 테이블
        // $this->db->join('pensionDB.pensionEvent PE', "PS.mpIdx = PE.mpIdx and PE.peOpen > 0 and peStartDate <= '".$dateTime."' and peEndDate >= '".$dateTime."' ",'left');  // 이벤트 테이블
        // 
        // $this->db->from('pensionDB.mergePlaceSite PS');
        //
        // return $this->db->get();
        
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
        
        return $this->db->query($nSql);
    }

    // ****************************************************** 펜션 상세정보 *********************************************************


    // ****************************************************** 펜션 객실정보 *********************************************************
    public function pensionRoomLists($idx){
        $this->db->select('PR.pprIdx,PR.pprName,PR.pprSize,PR.pprInMin, PR.pprInMax,PR.pprShape, PR.pprFloorM, PR.pprFloorS');
        $this->db->where('PR.mpIdx', $idx);
        $this->db->where('PR.pprOpen > ', 0);
        $this->db->order_by('PR.pprNo', 'desc');
        $this->db->join('placePensionRoomPhoto AS PPRP','PPRP.pprIdx = PR.pprIdx', 'left');
        $this->db->join('placePensionPrice AS PPP','PR.pprIdx = PPP.pprIdx AND PPP.pppType = \'DS\'');
        $this->db->group_by('PR.pprIdx');
        $this->db->where('PPRP.pprpFileName is not null','',false);
        $this->db->where('PPP.pppDay1 > 0');
        $return = $this->db->get('pensionDB.placePensionRoom PR')->result_array();
        if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
            //echo $this->db->last_query();
        }
        
        return $return;
    }
    // ****************************************************** 펜션 객실정보 *********************************************************


    // *************************************************** 펜션 객실사진 리스트 *****************************************************

    public function pensionRoomImageLists($idx, $limit, $offset){
        $this->db->start_cache();
        $this->db->where('pprIdx', $idx);
        $this->db->where('pprpOpen > ', 0);
        $this->db->where('pprpFileName is not null','',false);
        //$this->db->where('pprpRepr > ', 0);
        
        $this->db->stop_cache();
        
        $result['count'] = $this->db->count_all_results('pensionDB.placePensionRoomPhoto');
//echo $this->db->last_query();
        $this->db->select('mpIdx,pprpFileName, pprpRepr');
        $this->db->where('pprpFileName is not null','',false);      
        $this->db->order_by('pprpNo', 'asc');
        $result['query'] = $this->db->get('pensionDB.placePensionRoomPhoto', $offset, $limit)->result_array();

        $this->db->flush_cache();
        return $result;
    }
    // *************************************************** 펜션 객실사진 리스트 *****************************************************


    // ************************************************* 펜션 기타사진 리스트 **************************************************

    public function pensionEtcImageLists($idx, $limit, $offset){
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
    // ************************************************* 펜션 기타사진 리스트 **************************************************


    // ****************************************************** 펜션 객실요금 *********************************************************
    public function pensionRoomPrice($idx){
        $this->db->select('PD.ppdName,PD.ppdType');
        $this->db->where('PD.mpIdx', $idx);
        $this->db->where('PDT.ppdtStart <=', date('Y-m-d'));
        $this->db->where('PDT.ppdtEnd >=', date('Y-m-d'));
        $this->db->order_by('PD.ppdNo', 'desc');

        $this->db->join('pensionDB.placePensionDateTime PDT', 'PD.ppdIdx = PDT.ppdIdx');
        $result = $this->db->get('pensionDB.placePensionDate PD', 1, 0)->row_array();

        if($result)
            return $result['ppdType'];

        return 'DS';
    }

    // ****************************************************** 펜션 객실요금 *********************************************************

    function pensionImageLists($idx, $limit, $offset) { // 펜션 사진 리스트

        $this->db->start_cache();
        $this->db->where('mpIdx', $idx);
        $this->db->where('pprpOpen > ', 0);
        $this->db->where('pprpRepr > ', 0);
        $this->db->stop_cache();

        $result['count'] = $this->db->count_all_results('pensionDB.placePensionRoomPhoto');

        $this->db->select("pprpFileName");
        $this->db->order_by("pprpNo desc");
        $result['query'] = $this->db->get('pensionDB.placePensionRoomPhoto', $offset, $limit)->result_array();

        $this->db->flush_cache();
        return $result;
    }

    function pensionReprEtcImageLists($idx, $limit, $offset) {  // 펜션 사진 리스트

        $this->db->start_cache();
        $this->db->where('PPEP.mpIdx', $idx);
        $this->db->where('PPEP.ppepOpen > ', 0);
        $this->db->where('PPEP.ppepRepr > ', 0);
        $this->db->stop_cache();

        $result['count'] = $this->db->count_all_results('pensionDB.placePensionEtcPhoto AS PPEP');

        $this->db->select("PPEP.ppepFileName");
        $this->db->join('placePensionEtc AS PPE','PPE.ppeIdx = PPEP.ppeIdx','LEFT');
        $this->db->order_by("PPE.ppeNo ASC");
        $this->db->order_by("PPEP.ppepIdx DESC");
        $result['query'] = $this->db->get('pensionDB.placePensionEtcPhoto AS PPEP', $offset, $limit)->result_array();
        
        $this->db->flush_cache();
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
        $result = $this->db->query($schQuery)->result_array();
        
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
        $this->db->select('mpIdx');
        $this->db->where('mpIdx ', $mpIdx);
        $this->db->where('mbIdx ', $mbIdx);
        $this->db->from('pensionDB.pensionBasket');

        if($this->db->get()->row()){
            return 1;
        }else{
            return 2;
        }
    }
    // ****************************************************** 펜션 가고싶어요 *********************************************************

    // ***************************************************** 펜션 팁 리스트 *******************************************************
    function tipLists($idx, $limit, $offset) {

        $this->db->start_cache();
        $this->db->where('mpIdx', $idx);
        $this->db->where('ptFlag','0');
        $this->db->stop_cache();

        $result['count'] = $this->db->count_all_results('pensionDB.pensionTip');

        $this->db->select("*");
        $this->db->order_by("ptIdx desc");
        $result['query'] = $this->db->get('pensionDB.pensionTip', $offset, $limit)->result_array();
        
        $this->db->flush_cache();
        return $result;
    }
    // ***************************************************** 펜션 팁 리스트 *******************************************************

    // ***************************************************** 펜션 팁 등록 *******************************************************
    public function tipInsert($data){
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
        $this->db->where('mtCode', $data);
        $this->db->where('mtSite LIKE "%YPS%"');
        $this->db->where('mtType', 'PS');
        $this->db->select('mtName');
        return $this->db->get('pensionDB.mergeTheme')->row();
    }
    
    function getLocCodeName($data) {
        $this->db->where('mtCode', $data);
        $this->db->where('mtSite LIKE "%YPS%"');
        $this->db->where('mtType', 'PS');
        $this->db->select('mtName');
        $result = $this->db->get('pensionDB.mergeTheme')->row_array();
        return $result;
    }
    
    function getLocMapSite($data){
        $this->db->where('mtCode', $data);
        $result = $this->db->get('placePensionThemeSite')->row_array();
        
        return $result;
    }
    // ***************************************************** 펜션 지역명 *******************************************************


    // **************************************************** 펜션 객실 키 *******************************************************
    function getRoomKey($data) {
        $this->db->where('PR.mpIdx', $data['ptIdx']);
        if( $data['prIdx'] ) $this->db->where('PR.pprIdx', $data['prIdx']);
        $this->db->where('PR.pprOpen > ', '0');
        $this->db->join('pensionDB.placePensionBasic PB', 'PR.mpIdx = PB.mpIdx');
        $this->db->select('PR.pprIdx, PR.pprNo, PR.pprName, PR.pprInMin, PR.pprInMax, PR.pprSize, PB.ppbRoomMin');
        // $this->db->order_by('PR.pprIdx asc');
        $this->db->order_by('PR.pprNo desc');
        $result = $this->db->get('pensionDB.placePensionRoom PR')->result();
        //echo $this->db->last_query();
        return $result;
    }
    // **************************************************** 펜션 객실 키 *******************************************************


    // ************************************************** 펜션 기타 그룹 키 ****************************************************
    function getEtcKey($data) {
        $this->db->where('mpIdx', $data['ptIdx']);
        $this->db->where('ppeOpen > ', '0');
        $this->db->select('ppeIdx, ppeNo, ppeName');
        $this->db->order_by('ppeIdx asc');
        return $this->db->get('pensionDB.placePensionEtc')->result();
    }
    // ************************************************** 펜션 기타 그룹 키 ****************************************************
    
    // ************************************************** 펜션 외부 URL ****************************************************
    function getOutUrl($mpIdx) {
        $this->db->where('mpIdx', $mpIdx);
        $this->db->select(array('ppbOutUrl'));
        return $this->db->get('pensionDB.placePensionBasic')->row_array();
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
        
        return $this->db->query($nSql);
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
    
    function todayPriceFlag($mpIdx){
        $date = date("Y-m-d");
            
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
        $this->db->where('mpIdx', $mpIdx);
        $this->db->where('ppuPullFlag','1');
        $flag = $this->db->count_all_results('placePensionUse');
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
        $holyRow = $this->db->query($holyQuery)->row_array();
        
        
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
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPDP.mpIdx AND mmType LIKE '%YPS%' AND mpType = 'PS'
                        LEFT JOIN placeTheme AS PT ON MPS.mpsIdx = PT.mpsIdx AND PT.mtCode LIKE '1%'
                        WHERE '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                        AND PPB.ppbReserve = 'G'
                        AND PPDP.ppdpSaleDay".$dayNum." > 0
                        AND PPDP.ppdpPercent".$dayNum." < 100
                        AND mpsOpen = '1'
                        AND PT.mtCode = '".$schVal."'
                        GROUP BY PPDP.mpIdx) AS tot";
        $count = $this->db->query($schQuery)->row_array();
    
        $result['count'] = $count['totalCount'];
        
        $schQuery = "   SELECT PPDP.mpIdx, MPS.mpsAddr1, MPS.mpsName, MIN(PPDP.ppdpSaleDay".$dayNum.") AS price, MAX(PPDP.ppdpPercent".$dayNum.") AS percent, PPB.ppbReserve, PPB.ppbGrade, PPB.ppbImage, PPB.ppbWantCnt, PPB.ppbEventFlag
                        FROM pensionPrice AS PPDP
                        LEFT JOIN placePensionBasic AS PPB ON PPDP.mpIdx = PPB.mpIdx
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPDP.mpIdx AND mmType LIKE '%YPS%' AND mpType = 'PS'
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
        
        $result['lists'] = $this->db->query($schQuery)->result_array();
        
        return $result;
    }
    function getPensionSearchLists($schVal, $schSort, $schFlag, $idxStrings){
        $iPod = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
        $iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
        $iPad = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
        $Android = stripos($_SERVER['HTTP_USER_AGENT'], "Android");
        
        $date = date('Y-m-d');
            
        $dateObj = new DateTime($date);
        $dayNum = $dateObj->format('N');
        
        $holyQuery = "SELECT hDate-INTERVAL 1 DAY AS hDate, hTitle, hIdx FROM holiday WHERE hDate-INTERVAL 1 DAY = '".$date."'";
        $holyRow = $this->db->query($holyQuery)->row_array();
        
        
        if(isset($holyRow['hIdx'])){                
            $dayNum = "6";
        }else{
            if($dayNum < 5){
                $dayNum = "1";
            }
        }
        
        
        if($schFlag == "text"){
            $schQuery = "   SELECT COUNT(tot.mpIdx) AS totalCount FROM (SELECT PPDP.mpIdx
                            FROM pensionPrice AS PPDP
                            LEFT JOIN placePensionBasic AS PPB ON PPDP.mpIdx = PPB.mpIdx
                            LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPDP.mpIdx AND mmType LIKE '%YPS%' AND mpType = 'PS'
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
                            WHERE '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                            AND PPDP.ppdpSaleDay".$dayNum." > 0
                            AND PPDP.ppdpPercent".$dayNum." < 100
                            AND mpsOpen = '1'
                            AND CONCAT(MPS.mpsAddr1, MPS.mpsAddr2, MPS.mpsAddr1New, MPS.mpsName, PT.theme) LIKE '%".$schVal."%'
                            GROUP BY PPDP.mpIdx) AS tot";
            $count = $this->db->query($schQuery)->row_array();
        
            $result['count'] = $count['totalCount'];
            
            $schQuery = "   SELECT
                                PPDP.mpIdx, MPS.mpsAddr1, MPS.mpsName,
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
                                END AS price,
                                PT.theme, PPB.ppbReserve, PPB.ppbGrade, PPB.ppbImage, PPB.ppbWantCnt, PPB.ppbEventFlag
                            FROM pensionPrice AS PPDP
                            LEFT JOIN placePensionRoom AS PPR ON PPR.pprIdx = PPDP.pprIdx AND PPR.pprOpen = '1'
                            LEFT JOIN pensionException AS PE ON PE.mpIdx = PPDP.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                            LEFT JOIN placePensionBasic AS PPB ON PPDP.mpIdx = PPB.mpIdx
                            LEFT JOIN placePensionUse AS PPU ON PPU.mpIdx = PPB.mpIdx
                            LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPDP.mpIdx AND mmType LIKE '%YPS%' AND mpType = 'PS'
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
                            WHERE '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                            AND PPR.pprIdx IS NOT NULL
                            AND PPDP.ppdpSaleDay".$dayNum." > 0
                            AND PPDP.ppdpPercent".$dayNum." < 100";
            if($idxStrings != ""){
                $schQuery .= " AND PPDP.mpIdx NOT IN (".$idxStrings.")";
            }            
            $schQuery .= "  AND mpsOpen = '1'
                            AND CONCAT(MPS.mpsAddr1, MPS.mpsAddr2, MPS.mpsName, PT.theme) LIKE '%".$schVal."%'
                            GROUP BY PPDP.mpIdx";
            if($schSort == "1"){
                $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND(), PPU.ppuExternalFlag DESC, RAND(), PPB.ppbMainPension DESC, RAND()";
            }else if($schSort == "2"){
                $schQuery .= "  ORDER BY PPB.ppbWantCnt DESC, RAND()";                
            }else if($schSort == "4"){
                $schQuery .= "  ORDER BY price ASC, RAND()";
            }else if($schSort == "5"){
                $schQuery .= "  ORDER BY price DESC, RAND()";                
            }else{
                $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND(), PPU.ppuExternalFlag DESC, RAND(), PPB.ppbMainPension DESC, RAND()";
            }
            if($iPod || $iPhone || $iPad ){
                
            }else{
                $schQuery .= "  LIMIT 20";
            }
                        
            $result['lists'] = $this->db->query($schQuery)->result_array();
            
        }else if($schFlag == "location"){
            $schQuery = "   SELECT COUNT(tot.mpIdx) AS totalCount FROM (SELECT PPDP.mpIdx
                            FROM pensionPrice AS PPDP
                            LEFT JOIN placePensionBasic AS PPB ON PPDP.mpIdx = PPB.mpIdx
                            LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPDP.mpIdx AND mmType LIKE '%YPS%' AND mpType = 'PS'
                            LEFT JOIN placeTheme AS PT ON MPS.mpsIdx = PT.mpsIdx AND PT.mtCode LIKE '1%'                            
                            WHERE '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                            AND PPDP.ppdpSaleDay".$dayNum." > 0
                            AND PPDP.ppdpPercent".$dayNum." < 100
                            AND mpsOpen = '1'
                            AND PT.mtCode = '".$schVal."'
                            GROUP BY PPDP.mpIdx) AS tot";
            $count = $this->db->query($schQuery)->row_array();
        
            $result['count'] = $count['totalCount'];
            
            $schQuery = "   SELECT
                                PPDP.mpIdx, MPS.mpsAddr1, MPS.mpsName,
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
                                END AS price,
                                PPB.ppbReserve, PPB.ppbGrade, PPB.ppbImage, PPB.ppbWantCnt, PPB.ppbEventFlag
                            FROM pensionPrice AS PPDP
                            LEFT JOIN placePensionRoom AS PPR ON PPR.pprIdx = PPDP.pprIdx AND PPR.pprOpen = '1'
                            LEFT JOIN placePensionBasic AS PPB ON PPDP.mpIdx = PPB.mpIdx
                            LEFT JOIN pensionException AS PE ON PE.mpIdx = PPDP.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                            LEFT JOIN placePensionUse AS PPU ON PPU.mpIdx = PPB.mpIdx
                            LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPDP.mpIdx AND mmType LIKE '%YPS%' AND mpType = 'PS'
                            LEFT JOIN placeTheme AS PT ON MPS.mpsIdx = PT.mpsIdx AND PT.mtCode LIKE '1%'
                            WHERE '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                            AND PPR.pprIdx IS NOT NULL                            
                            AND PPDP.ppdpSaleDay".$dayNum." > 0
                            AND PPDP.ppdpPercent".$dayNum." < 100";
            if($idxStrings != ""){
                $schQuery .= " AND PPDP.mpIdx NOT IN (".$idxStrings.")";
            }            
            $schQuery .= "  AND mpsOpen = '1'
                            AND PT.mtCode = '".$schVal."'
                            GROUP BY PPDP.mpIdx";
            if($schSort == "1"){
                $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND(), PPU.ppuExternalFlag DESC, RAND(), PPB.ppbMainPension DESC, RAND()";
            }else if($schSort == "2"){
                $schQuery .= "  ORDER BY PPB.ppbWantCnt DESC, RAND()";                
            }else if($schSort == "4"){
                $schQuery .= "  ORDER BY price ASC, RAND()";
            }else if($schSort == "5"){
                $schQuery .= "  ORDER BY price DESC, RAND()";                
            }else{
                $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND(), PPU.ppuExternalFlag DESC, RAND(), PPB.ppbMainPension DESC, RAND()";
            }
            if($iPod || $iPhone || $iPad ){
                
            }else{
                $schQuery .= "  LIMIT 20";
            }
            
            $result['lists'] = $this->db->query($schQuery)->result_array();
            if($_SERVER['REMOTE_ADDR'] == "211.119.165.88"){
                //echo "<pre>".$this->db->last_query()."</pre>";
            }
            
        }else if($schFlag == "theme"){
            $schQuery = "   SELECT COUNT(tot.mpIdx) AS totalCount FROM (SELECT PPDP.mpIdx
                            FROM pensionPrice AS PPDP
                            LEFT JOIN placePensionBasic AS PPB ON PPDP.mpIdx = PPB.mpIdx
                            LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPDP.mpIdx AND mmType LIKE '%YPS%' AND mpType = 'PS'
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
            $schQuery .= "  GROUP BY PPDP.mpIdx) AS tot";
            $count = $this->db->query($schQuery)->row_array();
        
            $result['count'] = $count['totalCount'];
            
            $schQuery = "   SELECT
                                PPDP.mpIdx, MPS.mpsAddr1, MPS.mpsName,
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
                                END AS price,
                                PPB.ppbReserve, PPB.ppbGrade, PPB.ppbImage, PPB.ppbWantCnt, PPB.ppbEventFlag
                            FROM pensionPrice AS PPDP
                            LEFT JOIN placePensionRoom AS PPR ON PPR.pprIdx = PPDP.pprIdx AND PPR.pprOpen = '1'
                            LEFT JOIN placePensionBasic AS PPB ON PPDP.mpIdx = PPB.mpIdx
                            LEFT JOIN pensionException AS PE ON PE.mpIdx = PPDP.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                            LEFT JOIN placePensionUse AS PPU ON PPU.mpIdx = PPB.mpIdx
                            LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPDP.mpIdx AND mmType LIKE '%YPS%' AND mpType = 'PS'
                            LEFT JOIN placePensionThemeFlag AS PPTF ON PPDP.mpIdx = PPTF.mpIdx
                            WHERE '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
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
            $schQuery .= "  GROUP BY PPDP.mpIdx";
            if($schSort == "1"){
                $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND(), PPU.ppuExternalFlag DESC, RAND(), PPB.ppbMainPension DESC, RAND()";
            }else if($schSort == "2"){
                $schQuery .= "  ORDER BY PPB.ppbWantCnt DESC, RAND()";                
            }else if($schSort == "4"){
                $schQuery .= "  ORDER BY price ASC, RAND()";
            }else if($schSort == "5"){
                $schQuery .= "  ORDER BY price DESC, RAND()";                
            }else{
                $schQuery .= "  ORDER BY PPB.ppbGrade DESC, RAND(), PPU.ppuExternalFlag DESC, RAND(), PPB.ppbMainPension DESC, RAND()";
            }
            
            if($iPod || $iPhone || $iPad ){
                
            }else{
                $schQuery .= "  LIMIT 20";
            }
                        
            $result['lists'] = $this->db->query($schQuery)->result_array();
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
                            PE.peIdx, PE.peTitle, PE.peStartDate, PE.peENdDate,
                            PPU.ppuPullFlag,
                            PT.theme, PPB.ppbImage
                        FROM placePensionBasic AS PPB
                        LEFT JOIN pensionException AS PEN ON PEN.mpIdx = PPB.mpIdx AND PEN.peSetDate = '".date('Y-m-d')."' AND PEN.peUseFlag = 'Y'
                        LEFT JOIN mergePlaceSite AS MPS ON MPS.mpIdx = PPB.mpIdx AND MPS.mmType LIKE '%YPS%' AND MPS.mpType = 'PS'
                        LEFT JOIN pensionPrice AS PPDP ON PPDP.mpIdx = PPB.mpIdx AND PPDP.ppdpSaleDay".$dayNum." > 0 AND PPDP.ppdpPercent".$dayNum." < 100 AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
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
                            AND MT.mtSite LIKE '%YPS%'
                            AND MT.mtCode LIKE '2%'
                            GROUP BY PT.mpsIdx
                        ) AS PT ON PT.mpsIdx = MPS.mpsIdx
                        WHERE PPB.mpIdx = '".$mpIdx."'
                        AND MPS.mpsOpen = '1'
                         ORDER BY price ASC 
                        LIMIT 1";
        $result = $this->db->query($schQuery)->row_array();
        
        return $result;
    }
    
    function getPensionRoomLists($mpIdx){
        $date = date('Y-m-d');
            
        $dateObj = new DateTime($date);
        $dayNum = $dateObj->format('N');
        
        if($dayNum < 5){
            $dayNum = "1";
        }
        
        $schQuery = "   SELECT PPR.pprIdx, PPR.pprName, PPR.pprSize, PPR.pprInMin, PPR.pprInMax, PPR.pprShape, PPR.pprFloorM, PPR.pprFloorS,
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
                        END AS price
                        FROM placePensionRoom AS PPR
                        LEFT JOIN pensionPrice AS PPDP ON PPDP.pprIdx = PPR.pprIdx AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                        LEFT JOIN pensionException AS PE ON PE.mpIdx = PPR.mpIdx AND PE.peSetDate = '".$date."' AND PE.peUseFlag = 'Y'
                        WHERE PPR.mpIdx = '".$mpIdx."' AND PPR.pprOpen > 0
                        GROUP BY PPR.pprIdx
                        ORDER BY PPR.pprNo DESC
                        ";
        $result = $this->db->query($schQuery)->result_array();
    
        return $result;
    }
    
    function getPensionRoomTypeLists($mpIdx){
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
                        
        $schQuery = "   SELECT PRT.prtIdx, PRT.prtName, PPR.pprSize, PPR.pprInMin, PPR.pprInMax, PPR.pprShape, PPR.pprFloorM, PPR.pprFloorS, PPDP.ppdpSaleDay".$dayNum." AS price, PRT.prtSort, PRT.pprIdxRepr, PPDP.* , PPR.pprIdx
                        FROM pensionRoomType AS PRT
                        LEFT JOIN placePensionRoom AS PPR ON PPR.pprIdx = PRT.pprIdxRepr AND PPR.mpIdx = PRT.mpIdx
                        LEFT JOIN pensionPrice AS PPDP ON PPDP.pprIdx = PRT.pprIdxRepr AND '".date('Y-m-d')."' BETWEEN PPDP.ppdpStart AND PPDP.ppdpEnd
                        WHERE PRT.mpIdx = '".$mpIdx."' 
                        AND PPR.pprOpen > 0
                        AND PRT.pprIdxRepr > 0
                        GROUP BY PRT.prtIdx
                        ORDER BY PRT.prtSort DESC ";                
        $result = $this->db->query($schQuery)->result_array();
    
        return $result;
    }
    
    function getPensionRoomType($mpIdx){
        $typeRowQuery = "SELECT * FROM `placePensionBasic` WHERE mpIdx = '".$mpIdx."' ";
        $typeRow = $this->db->query($typeRowQuery)->row_array();
        return $typeRow;
    }
    
    // ****************************************************** 지역상단 롤링배너 리스트 *********************************************************
    
    function topLocRolBanner($idx){ // 메인 인기지역 리스트\
        $setDate = date('Y-m-d');
        $this->db->start_cache();
        $this->db->like('altrbLocal', $idx);
        $this->db->where("'$setDate' BETWEEN altrbStartDate AND altrbEndDate",'',false);
        $this->db->where('altrbOpen', '1');
        $this->db->stop_cache();
        $result['count']    = $this->db->count_all_results('pensionDB.appLocTopRollingBanner');

        $this->db->select('altrbIdx,altrbTitle,altrbFilename1 AS altrbFilename, mpIdx, pensionName, altrbLocal');
        $this->db->order_by('altrbSort', 'asc');
        $this->db->order_by('rand()');
        $result['lists'] = $this->db->get('pensionDB.appLocTopRollingBanner')->result_array();
        
        $this->db->flush_cache();
       
        return $result;
    }
    // ****************************************************** 지역상단 롤링배너 리스트 *********************************************************
    
    function getVillaLists($idxString){        
        $this->db->join('placePensionBasic AS PPB','PPB.mpIdx = PPU.mpIdx','LEFT');
        $this->db->join('mergePlaceSite AS MPS',"MPS.mpIdx = PPU.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->db->where('PPB.ppbReserve','R');
        $this->db->where('MPS.mpsOpen','1');
        $this->db->where('PPU.ppuPullFlag','1');
        $result['count'] = $this->db->count_all_results('placePensionUse AS PPU');
        
        $this->db->select('MPS.mpsName, MPS.mpsAddr1, PPB.mpIdx, PPB.ppbImage');
        $this->db->join('placePensionBasic AS PPB','PPB.mpIdx = PPU.mpIdx','LEFT');
        $this->db->join('mergePlaceSite AS MPS',"MPS.mpIdx = PPU.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->db->where('PPB.ppbReserve','R');
        $this->db->where('MPS.mpsOpen','1');
        $this->db->where('PPU.ppuPullFlag','1');
        if(count($idxString) > 0){
            $this->db->where_not_in('PPU.mpIdx', $idxString);
        }
        $this->db->order_by('rand()');
        $result['lists'] = $this->db->get('placePensionUse AS PPU')->result_array();
        
        return $result;
    }
    
    function getNewLists($idxString){
        $setDate = date('Y-m-d');
        
        $this->db->join('placePensionBasic AS PPB','PPB.mpIdx = PN.mpIdx','LEFT');
        $this->db->join('mergePlaceSite AS MPS',"MPS.mpIdx = PN.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->db->where('MPS.mpsOpen','1');
        $this->db->where('PPB.ppbOpenDate != 0000-00-00');
        $this->db->where("'$setDate' BETWEEN PN.pnStart AND PN.pnEnd",'',false);
        $count = $this->db->count_all_results('pensionNew AS PN');
        $result['count'] = 30-$count; 
        
        $this->db->select('MPS.mpsName, MPS.mpsAddr1, PPB.mpIdx, PPB.ppbImage, PPB.ppbOpenDate');
        $this->db->join('mergePlaceSite AS MPS',"MPS.mpIdx = PPB.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        //$this->db->where('PPB.ppbReserve','R');
        $this->db->where('PPB.ppbOpenDate >= ','date_add(now(), interval -12 month)', false);
        $this->db->where('PPB.ppbOpenDate != 0000-00-00');
        $this->db->where('MPS.mpsOpen','1');
        if(count($idxString) > 0){
            $this->db->where_not_in('PPB.mpIdx', $idxString);
        }
        $this->db->order_by('rand()');
        $result['lists'] = $this->db->get('placePensionBasic AS PPB', $result['count'])->result_array();
        
        return $result;
    }

    function getNewAdLists(){
        $setDate = date('Y-m-d');
        $this->db->join('placePensionBasic AS PPB','PPB.mpIdx = PN.mpIdx','LEFT');
        $this->db->join('mergePlaceSite AS MPS',"MPS.mpIdx = PN.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->db->where('MPS.mpsOpen','1');
        $this->db->where('PPB.ppbOpenDate != 0000-00-00');
        $this->db->where("'$setDate' BETWEEN PN.pnStart AND PN.pnEnd",'',false);
        $result['count'] = $this->db->count_all_results('pensionNew AS PN');
        
        $schQuery = "   SELECT MPS.mpsName, MPS.mpsAddr1, PPB.mpIdx, PPB.ppbImage, PPB.ppbOpenDate, PN.pnImage, PN.pnIdx, PN.pnTag,
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
                        WHERE MPS.mpsOpen =  '1'
                        AND PPB.ppbOpenDate != 0000-00-00
                        AND '".date('Y-m-d')."' BETWEEN PN.pnStart AND PN.pnEnd
                        GROUP BY PN.pnIdx
                        ORDER BY pnSort DESC, RAND()";
        $result['lists'] = $this->db->query($schQuery)->result_array();
        
        return $result;
    }
    
    function getJejuLists($idxString){
        $result['count'] = $this->db->count_all_results('pensionJeju');
        
        $this->db->join('mergePlaceSite AS MPS',"MPS.mpIdx = PJ.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->db->join('placePensionBasic AS PPB','PPB.mpIdx = PJ.mpIdx','LEFT');
        $this->db->order_by('PJ.pjSort','DESC');
        $this->db->order_by('rand()');
        if(count($idxString) > 0){
            $this->db->where_not_in('PJ.mpIdx', $idxString);
        }
        $result['lists'] = $this->db->get('pensionJeju AS PJ')->result_array();
        
        return $result;
    }
}
?>