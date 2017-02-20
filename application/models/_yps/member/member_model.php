<?php
class Member_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	// ****************************************************** �α��� *********************************************************
	public function yanoljaPensionLogin($data){

		if ( isset($data['root']) && $data['root'] != 'YP' && !$this->memberIDCheck($data['root'].'.'.$data['mbID']) )
		{
			if( $data['root'] == 'FB' || $data['root'] == 'KA' ){
				// 페이스북 최초 로그인 시 회원가입 처리함
    			    $this->memberJoin(array(
                    'mbID'=>$data['root'].'.'.$data['mbID'],
                    'mbPassword'=>'',
                    'mbEmail'=>$data['mbEmail']
                ));
			}
			else if ( $data['root'] == 'YA' )
			{
                /*
				$this->ynjDB = $this->load->database('ynj', TRUE);
				$this->ynjDB->select('mID,mEmail,mMobile');
				$this->ynjDB->from('yanoljaDB.ynjMember');
				$this->ynjDB->where('mID =',$data['mbID']);
				$this->ynjDB->where('mPasswordMd5',md5($data['mbPassword']));
				$this->ynjDB->limit(1);
				$ynjMemberQuery = $this->ynjDB->get();
				
				if ( $ynjMemberQuery->num_rows() > 0 )
				{
					$ynjMember = $ynjMemberQuery->row_array();
					
					$data['mbEmail'] = $ynjMember['mEmail'];
					
					// 야놀자 최초 로그인 시 회원가입 처리함
					$this->memberJoin(array(
						'mbID'=>$data['root'].'.'.$data['mbID'],
						//'mbPassword'=>$data['mbPassword'],
						'mbPassword'	=> '',
						'mbEmail'=>$data['mbEmail']
					));
					
				}
				
				$this->ynjDB->close();
                */
			}
			
		}
		
		
		
		$this->db->select('MB.mbIdx,MB.mbID,MB.mbNick,MB.mbEmail,MB.mbMobile,DATE_FORMAT(MB.mbBirthday,\'%y%m%d\') as mbBirthday,MP.mpNowPoint AS mbPoint,MB.mbRegId, MB.mbGrade',FALSE);
		$this->db->from('member AS MB');
        $this->db->join('memberPoint AS MP','MB.mbIdx = MP.mbIdx','LEFT');
		$this->db->where('MB.mbID =',$data['root'].'.'.$data['mbID']);

		if ( isset($data['root']) && $data['root'] == 'YP' )
		{
			// 야놀자펜션 회원로그인 비번 체크
			//if(substr($_SERVER['REMOTE_ADDR'],0,11) != "211.119.165"){
			    $this->db->where('MB.mbPassword =',md5($data['mbPassword']));
			//}			
		}else if ( isset($data['root']) && $data['root'] == 'YA' ){
			// 야놀자 회원로그인 비번 체크
			/*
			$this->ynjDB = $this->load->database('ynj', TRUE);
            $this->ynjDB->select('mID,mEmail,mMobile');
            $this->ynjDB->from('yanoljaDB.ynjMember');
            $this->ynjDB->where('mID =',$data['mbID']);
            $this->ynjDB->where('mPasswordMd5',md5($data['mbPassword']));
            $this->ynjDB->limit(1);
            $ynjMemberQuery = $this->ynjDB->get()->row_array();
            $this->ynjDB->close();
            if(count($ynjMemberQuery) > 0){
                //$this->db->where('mbPassword','');
            }else{
                $this->db->where('mbPassword',md5($data['mbPassword']));
            }*/
		}		
		$this->db->where('MB.mbOut =','N');
		$this->db->limit(1);
		$result = $this->db->get()->row_array();
		
        if(count($result) > 0 && $data['mbDevID'] != ""){
            $this->db->where('mbIdx', $result['mbIdx']);
            $this->db->set('mbDeviceID', $data['mbDevID']);
            $this->db->update('member');
        }
		if ( isset($data['mbRegId']) && strlen(trim($data['mbRegId'])) > 0 
			&& isset($data['mbID']) && isset($result['mbID']) && $result['mbID'] == $data['mbID']
			&& isset($data['mbRegId']) && $result['mbRegId'] != trim($data['mbRegId']) )
		{
			$this->db->where('mbIdx', $result['mbIdx']);
			$this->db->update('member', array(
				'mbRegId' => trim($data['mbRegId'])
			));
			
			$result['mbRegId'] = trim($data['mbRegId']);
		}

		return $result;
	}
	// ****************************************************** �α��� *********************************************************

	// ****************************************************** ��й�ȣ Ȯ�� *********************************************************
	public function passwordConfirm($data){
		$this->db->select('mbID');
		$this->db->from('member');
		$this->db->where('mbIdx =',$data['mbIdx']);
		$this->db->where('mbPassword',md5($data['mbPassword']));
		$this->db->where('mbOut =','N');
		$this->db->limit(1);

		return $this->db->get()->row_array();
	}
	// ****************************************************** ��й�ȣ Ȯ�� *********************************************************

	// ****************************************************** ȸ������ ���� *********************************************************
	public function mypageUpdate($idx, $mbPassword, $data, $root = 'YP'){	
		$this->db->where('mbIdx', $idx);
		
		/*
		if ( isset($root) && $root == 'YP' )
		{			
			$this->db->where('mbPassword', $mbPassword);
		}
		*/

		$this->db->set( $data );

		$this->db->update('member', $data);
	}
	// ****************************************************** ȸ������ ���� *********************************************************

	// ****************************************************** ��������� *********************************************************
	public function mobileUpdate($idx, $data){
		$this->db->where('mbIdx', $idx);
		$this->db->update('member', $data); 
	}
	// ****************************************************** ��������� *********************************************************
	
	// ****************************************************** 아이디 중복 확인 *********************************************************
	public function memberIDCheck( $mbID ){
		$this->db->select('mbID');
		$this->db->from('member');
		$this->db->where('mbID =',$mbID);
		$this->db->limit(1);
		return $this->db->get()->num_rows() > 0;
	}
	// ****************************************************** ȸ���� üũ *********************************************************
	
	// ************************************************기존 회원가입 체크, 2개로 분리*****************************************
	// 수정자 : 김영웅
	// 수정일자 : 2014-05-19
	// ID Check
	public function memberJoinCheckID($data){
        $this->db->select('mbID');
        $this->db->from('member');
        $this->db->where('mbID =',$data['mbID']);
        $this->db->limit(1);
        return $this->db->get()->row_array();
    }
    
    //Email Check
    public function memberJoinCheckEMail($data){
        $this->db->select('mbID');
        $this->db->from('member');
        $this->db->where('mbEmail =',$data['mbEmail']);
        $this->db->limit(1);
        return $this->db->get()->row_array();
    }
	// ************************************************기존 회원가입 체크, 2개로 분리*****************************************

	// ****************************************************** ȸ���� *********************************************************
	public function memberJoin($data){ // 

		$result = $this->db->insert('member', array(
			'mbID' => $data["mbID"],
			'mbPassword' => md5($data["mbPassword"]),
			'mbEmail' => $data["mbEmail"],
			'mbRegDate' => date("Y-m-d H:i:s"),
			'mbOut'=>'N',
			'mbEmailAgree' => $data["mbEmailAgree"]
		));

		return $this->db->insert_id();
	}
	// ****************************************************** ȸ���� *********************************************************

	// ****************************************************** ������ ���� �� *********************************************************
	function travelTipLists($idx, $limit, $offset) {

		$this->db->start_cache();
		$this->db->where('mbIdx', $idx);
		$this->db->stop_cache();

		$result['count'] = $this->db->count_all_results('travelTip');

		$this->db->select("*");
		$this->db->order_by("ttIdx desc");
		$result['query'] = $this->db->get('travelTip', $offset, $limit)->result_array();

		$this->db->flush_cache();
		return $result;
	}
	// ****************************************************** ������ ���� �� *********************************************************


	// ****************************************************** ������ ��� �� *********************************************************
	function pensionTipLists($idx, $limit, $offset) {

		$this->db->start_cache();
		$this->db->where('mbIdx', $idx);
		$this->db->stop_cache();

		$result['count'] = $this->db->count_all_results('pensionTip');

		$this->db->select("ptIdx,mpIdx,mbIdx,ptName,ptSector,ptPensionName,ptContent,ptTravelName,ptRegDate,ptRecommend,ptPointSave,ptBlindFlag,ptAnswer");
		$this->db->order_by("ptIdx desc");
		$result['query'] = $this->db->get('pensionTip', $offset, $limit)->result_array();

		$this->db->flush_cache();
		return $result;
	}
	// ****************************************************** ������ ��� �� *********************************************************

	// ****************************************************** ������ ��� �� *********************************************************
	function memberPointLists($idx, $limit, $offset) {

		$this->db->start_cache();
		$this->db->where('mbIdx', $idx);
		$this->db->stop_cache();

		$result['count'] = $this->db->count_all_results('memberPointRaw');

		$this->db->select("*");
		$this->db->order_by("mprPointDate desc");
		$result['query'] = $this->db->get('memberPointRaw', $offset, $limit)->result_array();

		$this->db->flush_cache();
		return $result;
	}
	// ****************************************************** ������ ��� �� *********************************************************

	// ****************************************************** ����/�̺�Ʈ/�����ϴ��� ����Ʈ *********************************************************
	function notice($code, $limit, $offset) {

		$this->db->start_cache();
        //공지사항+이벤트로 강제 변경
		$this->db->where_in('anSector', $code);
		$this->db->where('anOpen >', '0');

		$this->db->stop_cache();

		$result['count'] = $this->db->count_all_results('appNotice');

		$this->db->select("*");
		$this->db->order_by("anIdx desc");
		$result['query'] = $this->db->get('appNotice', $offset, $limit)->result_array();

		$this->db->flush_cache();
		return $result;
	}
	// ****************************************************** ����/�̺�Ʈ/�����ϴ��� ����Ʈ *********************************************************

	// ****************************************************** 1:1 ���� ����Ʈ *********************************************************
	public function inquiriesLists($idx, $limit, $offset) {

		$this->db->start_cache();
		$this->db->where('mbIdx', $idx);
		$this->db->stop_cache();

		$result['count'] = $this->db->count_all_results('appInquiries');

		$this->db->select("*");
		$this->db->order_by("arIdx desc");
		$result['query'] = $this->db->get('appInquiries', $offset, $limit)->result_array();

		$this->db->flush_cache();
		return $result;
	}
	// ****************************************************** 1:1 ���� ����Ʈ *********************************************************

	// ****************************************************** 1:1 ���� �ۼ� *********************************************************
	public function inquiriesInput($input){
		$data = array(
			'mbIdx' => trim($input["mbIdx"]),
			'arName' => urldecode(trim($input["arName"])),
			'arQuestion' => urldecode(trim($input["arQuestion"])),
			'arRegDate' => date("Y-m-d H:i:s")
		);
		
		if ( isset($input['arType']) && strlen(trim($input['arType'])) > 0 )
		{
			$data['arType'] = trim($input['arType']);
		}
		else
		{
			$data['arType'] = '';	
		}
		
		if ( isset($input['mpIdx']) && trim($input['mpIdx']) > 0 )
		{
			$data['mpIdx'] = trim($input['mpIdx']);
		}
		else
		{
			$data['mpIdx'] = '';
		}
		
		$result = $this->db->insert('appInquiries', $data);

		return $this->db->insert_id();
	}
	// ****************************************************** 1:1 ���� �ۼ� *********************************************************

	
	public function findMbId( $data )
	{
		$result = array(
			'type' => '',
			'match' => 'N',
			'mbID' => '',
			'mbEmail' => '',
			'mbMobile' => ''
		);
		
		$this->load->helper('email');
		
		if ( isset($data['keyword']) && strlen(trim($data['keyword'])) > 0 ) 
		{
			$keyword = trim($data['keyword']);
			$mobile = preg_replace('/[^0-9]/', '', $keyword);
			$this->db->limit(1);
			if ( valid_email($keyword) ){
				//$this->db->where('mbEmail', $keyword);
				//$result['type'] = 'email';
				$this->db->where('mbID', 'YP.'.$keyword);
                $result['type'] = 'id';
				
			} 
			else if ( strlen($mobile) == 10  || strlen($mobile) == 11){
				$this->db->where('mbMobile', $mobile);
                
				$result['type'] = 'mobile';
			}
			else
			{
				$this->db->where('mbID', 'YP.'.$keyword);
				$result['type'] = 'id';
			}
			
            // 201405291250 pyh : YP회원 중 탈퇴가 아닌 회원만 조회
            $this->db->where("INSTR(mbID, 'YP.') >", "YP.");
            $this->db->where('mbOut', 'N');
            
			$this->db->select('mbIdx, mbID,mbEmail,mbMobile');
			$this->db->from('member');
			$query = $this->db->get();
			
			
			if ( $query->num_rows() == 1 ){
				$row = $query->row_array();
				$result['mbIdx'] = $row['mbIdx'];
				$result['match'] = 'Y';
				$result['mbID'] = preg_replace('/[A-Z][A-Z]./', '', $row['mbID']);;
				$result['mbEmail'] = $row['mbEmail'];
				$result['mbMobile'] = $row['mbMobile'];
			}
			
		}
		
		return $result;
	}

	// ***************************************** 내가한 펜션 가고싶어요 *********************************************
	function pensionBasketList($data) {
		$this->db->start_cache();
		$this->db->where('PB.mbIdx', $data['mbIdx']);
		$this->db->where('MPS.mmType LIKE "%YPS%"');
		$this->db->where('MPS.mpType', 'PS');
		//$this->db->where('MPS.mpsOpen > ', '0');
		$this->db->join('mergePlaceSite MPS', 'PB.mpIdx=MPS.mpIdx');
		$this->db->join('placePensionBasic PPB', 'PB.mpIdx=PPB.mpIdx');
		$this->db->stop_cache();
		$result['count']	= $this->db->count_all_results('pensionBasket PB');
		$this->db->select('MPS.mpsIdx, MPS.mpIdx, MPS.mpsAddr1, MPS.mpsName, PPB.ppbRoomMin, PPB.ppbImage');
		$result['obj']	= $this->db->get('pensionBasket PB')->result();
		$this->db->flush_cache();
		return $result;
	}

    function memberBasketLists($mbIdx, $limit, $offset){
        $this->db->where('mbIdx', $mbIdx);
        $result['count'] = $this->db->count_all_results('pensionBasket');
        
        $dayNum = date('N', strtotime(date('Y-m-d')));
        if($dayNum < 5){
            $dayNum = 1;
        }
        
        $schQuery = "   SELECT PS.mpIdx,PS.mpsName,PS.mpsAddr1,PPB.ppbImage, PPB.ppbWantCnt, PPB.ppbReserve,
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
                        FROM pensionDB.pensionBasket AS PB
                        LEFT JOIN pensionDB.mergePlaceSite PS ON PB.mpIdx = PS.mpIdx AND PS.mmType = 'YPS' AND PS.mpType = 'PS'
                        LEFT JOIN pensionDB.placePensionBasic AS PPB ON PPB.mpIdx = PB.mpIdx
                        LEFT JOIN pensionDB.pensionPrice AS PP ON PP.mpIdx = PB.mpIdx AND '".date('Y-m-d')."' BETWEEN PP.ppdpStart AND PP.ppdpEnd
                        LEFT JOIN pensionDB.pensionException AS PE ON PE.mpIdx = PB.mpIdx AND PE.peSetDate = '".date('Y-m-d')."' AND PE.peUseFlag = 'Y'
                        LEFT JOIN pensionDB.placePensionRoom AS PPR ON PPR.pprIdx = PP.pprIdx
                        LEFT JOIN pensionDB.pensionTodaySale AS PTS ON PTS.mpIdx = PPR.mpIdx AND PTS.pprIdx LIKE CONCAT('%',PPR.pprIdx,'%') AND '".date('Y-m-d')."' BETWEEN PTS.ptsStart AND PTS.ptsEnd AND '".date('H:i')."' BETWEEN PTS.ptsStartTime AND PTS.ptsEndTime AND PTS.ptsOpen = '1' AND PTS.ptsDay".$dayNum." = '1'
                        WHERE PB.mbIdx = '".$mbIdx."' 
                        AND PPR.pprOpen = '1' 
                        GROUP BY PB.mpIdx
                        ORDER BY PB.mpIdx DESC
                        LIMIT ".$limit." OFFSET ".$offset;
        $result['lists'] = $this->db->query($schQuery)->result_array();
       
        
        return $result;
    }

	// *************************************** 내가한 펜션 가고싶어요 삭제 ******************************************
	function pensionBasketDelete($data) {
		$this->db->where('mpIdx', $data['mpIdx']);
		$this->db->where('mbIdx', $data['mbIdx']);
		return $this->db->delete('pensionBasket');
	}

	public function travelBasketLists($mbIdx, $limit, $offset){	    
	    $this->db->where('mbIdx', $mbIdx);
	    $lists = $this->db->get('travelBasket')->result_array();
        $result['count'] = count($lists);
        $mpIdxArray = array();
        if(count($lists) > 0){
            $i=0;
            foreach($lists as $lists){
                $mpIdxArray[$i] = $lists['mpIdx'];
                $i++;
            }
            
            $this->dbInfo->select("DNI.dniIdx,DNI.dniTitle,DNI.dniSi,DNI.dniGugun,DNI.dniAdress,DNI.dniFileName,DNI.dniReadnum,DNII.dniiFileName1,DNII.dniiFileName2,DNII.dniiFileName3,DNII.dniiFileName4,DNII.dniiFileName5,DNII.dniiFileName6,DNII.dniiFileName7,DNII.dniiFileName8,DNII.dniiFileName9,DNII.dniiFileName10,DNII.dniiFileName11,DNII.dniiFileName12,DNII.dniiFileName13,DNII.dniiFileName14,DNII.dniiFileName15,DNII.dniiFileName16,DNII.dniiFileName17,DNII.dniiFileName18,DNII.dniiFileName19,DNII.dniiFileName20,DNII.dniiFileName21,DNII.dniiFileName22,DNII.dniiFileName23,DNII.dniiFileName24,DNII.dniiFileName25,DNII.dniiFileName26,DNII.dniiFileName27,DNII.dniiFileName28,DNII.dniiFileName29,DNII.dniiFileName30,DNII.dniiFileName31,DNII.dniiFileName32,DNII.dniiFileName33,DNII.dniiFileName34,DNII.dniiFileName35,DNII.dniiFileName36,DNII.dniiFileName37,DNII.dniiFileName38,DNII.dniiFileName39,DNII.dniiFileName40,DNII.dniiFileName41,DNII.dniiFileName42,DNII.dniiFileName43,DNII.dniiFileName44,DNII.dniiFileName45,DNII.dniiFileName46,DNII.dniiFileName47,DNII.dniiFileName48,DNII.dniiFileName49,DNII.dniiFileName50,DNII.dniiFileName51,DNII.dniiFileName52,DNII.dniiFileName53,DNII.dniiFileName54,DNII.dniiFileName55,DNII.dniiFileName56,DNII.dniiFileName57,DNII.dniiFileName58,DNII.dniiFileName59,DNII.dniiFileName60,DNII.dniiFileName61,DNII.dniiFileName62,DNII.dniiFileName63,DNII.dniiFileName64,DNII.dniiFileName65,DNII.dniiFileName66,DNII.dniiFileName67,DNII.dniiFileName68,DNII.dniiFileName69,DNII.dniiFileName70,DNII.dniiFileName71,DNII.dniiFileName72,DNII.dniiFileName73,DNII.dniiFileName74,DNII.dniiFileName75,DNII.dniiFileName76,DNII.dniiFileName77,DNII.dniiFileName78,DNII.dniiFileName79,DNII.dniiFileName80,DNII.dniiFileName81,DNII.dniiFileName82,DNII.dniiFileName83,DNII.dniiFileName84,DNII.dniiFileName85,DNII.dniiFileName86,DNII.dniiFileName87,DNII.dniiFileName88,DNII.dniiFileName89,DNII.dniiFileName90,DNII.dniiFileName91,DNII.dniiFileName92,DNII.dniiFileName93,DNII.dniiFileName94,DNII.dniiFileName95,DNII.dniiFileName96,DNII.dniiFileName97,DNII.dniiFileName98,DNII.dniiFileName99,DNII.dniiFileName100");
            $this->dbInfo->where('DNI.dniOpen', 'Y');
            $this->dbInfo->where_in('DNI.dniIdx', $mpIdxArray);
            $this->dbInfo->join('infoDB.ynjDateNewInfoImage as DNII', "DNI.dniIdx = DNII.dniIdx");
            $this->dbInfo->group_by('DNI.dniIdx');
            $result['query'] = $this->dbInfo->get('infoDB.ynjDateNewInfo DNI', $limit, $offset, TRUE)->result_array();
        }else{
            $result['query'] = array();
        }

		return $result;
	}

	// *************************************** 내가한 펜션 가고싶어요 삭제 ******************************************
	function travelBasketDelete($data) {
		$this->db->where('mpIdx', $data['mpIdx']);
		$this->db->where('mbIdx', $data['mbIdx']);
		return $this->db->delete('travelBasket');
	}


	// ******************************************* 모바일 변경 *********************************************
	function memberMobileChange($param) {
		extract( $param );
		$this->db->where('mbIdx', $param['mbIdx']);
		$this->db->set('mbMobile', $param['mobile_number']);
		return $this->db->update('member');
	}
	// ******************************************* 모바일 변경 *********************************************
	

	// ****************************************** mypage 내정보 ********************************************
	public function getMypageInfo($mbIdx) {
		$this->db->where('MB.mbIdx', $mbIdx);
		$this->db->select("(select count(rIdx) from reservation RV where RV.mbidx=MB.mbIdx AND rPayFlag = 'Y') as rvCnt");
		$this->db->select('(select count(mbIdx) from pensionBasket PB where PB.mbidx=MB.mbIdx) as pbCnt');
		$this->db->select('(select count(mbIdx) from travelBasket TB where TB.mbidx=MB.mbIdx) as tbCnt');
		$this->db->select('(select count(ptIdx) from pensionTip PT where PT.mbIdx=MB.mbIdx) as ptCnt');
		$this->db->select('(select count(ttIdx) from travelTip TT where TT.mbIdx=MB.mbIdx) as ttCnt');
        $this->db->select('(select count(arIdx) from appInquiries AR where AR.mbIdx=MB.mbIdx) as arCnt');
		return $this->db->get('member MB')->row_array();
	}

	public function getPointSch($mbIdx){
		$this->db->select('mpNowPoint');
		$this->db->where_in('mbIdx', $mbIdx);
		$result = $this->db->get('memberPoint')->row_array();

		return $result;
	}
	// ****************************************** mypage 내정보 ********************************************

	//닉네임 중복체크
	function checkDuplicated($field,$val){
		$this->db->where($field, $val);
		
		
		return $this->db->count_all_results('member');
	}
    
    function InsDeviceID($devID, $devOS){
        $insSql = "INSERT INTO pensionDeviceID(pdiDevID, pdiDevOS) VALUES('".$devID."','".$devOS."')
                    ON DUPLICATE KEY UPDATE pdiDevID = '".$devID."', pdiDevOS = '".$devOS."'";
        if($this->db->query($insSql)){
            return "O";
        }else{
            return "C";
        }
    }

    function reset_password($mbIdx, $new_pass, $new_pass_key, $expire_period = 900)
    {
        $this->db->set('mbPassword', md5($new_pass));
        $this->db->set('new_password_key', NULL);
        $this->db->set('new_password_requested', NULL);
        $this->db->where('mbIdx', $mbIdx);
        $this->db->where('new_password_key', $new_pass_key);
        $this->db->where('UNIX_TIMESTAMP(new_password_requested) >=', time() - $expire_period);

        $this->db->update('pensionDB.member AS m');
        return $this->db->affected_rows() > 0;
    }
    
    function can_reset_password($mbIdx, $new_pass_key, $expire_period = 900)
    {
        $this->db->select('1', FALSE);
        $this->db->where('mbIdx', $mbIdx);
        $this->db->where('new_password_key', $new_pass_key);
        $this->db->where('UNIX_TIMESTAMP(new_password_requested) >', time() - $expire_period);

        $query = $this->db->get('pensionDB.member AS m');
        return $query->num_rows() == 1;
    }
    
    function getUserInfo($mbIdx){
        $this->db->where('mbIdx', $mbIdx);
        $result = $this->db->get('member')->row_array();
        
        return $result;
    }
}
?>