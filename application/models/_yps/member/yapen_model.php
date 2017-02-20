<?php
class Yapen_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }
    
    function getEmailCheck($mbID){
        $this->db->where("mbID = 'YP.".$mbID."'",'',false);
        $this->db->where('mbOut','N');
        $result = $this->db->count_all_results('member');
        
        return $result;
    }
    
    function sendMobile($mbMobile, $content){
        $this->db->set('date_client_req', date('Y-m-d H:i:s'));
        $this->db->set('content', $content);
        $this->db->set('callback', '16444816');
        $this->db->set('service_type','0');
        $this->db->set('broadcast_yn','N');
        $this->db->set('msg_status','1');
        $this->db->set('recipient_num',$mbMobile);
        return $this->db->insert('emma.em_smt_tran');
    }
    
    function getMaxSMSCount($mbMobile){
        $this->db->where('recipient_num',$mbMobile);
        $this->db->where('SUBSTR(date_client_req,1,10)', date('Y-m-d'));
        $result = $this->db->count_all_results('emma.em_smt_log_'.date('Ym'));
        
        return $result;
    }
    
    function getMemberInfo($mbIdx){
        $this->db->select('MB.*, MP.mpNowPoint');
        $this->db->where('MB.mbIdx', $mbIdx);
        $this->db->where('MB.mbOut','N');
        $this->db->join('memberPoint AS MP','MP.mbIdx = MB.mbIdx','LEFT');
        $result = $this->db->get('member AS MB')->row_array();
        
        return $result;
    }
    
    function memberLogin($mbID, $mbPW, $type){
        if($type == "FB"){
            $this->db->select('MB.*, MP.mpNowPoint');
            $this->db->where('MB.mbFacebook', $mbID);
            $this->db->where('MB.mbFacebookFlag','Y');
            $this->db->where('MB.mbOut','N');
            $this->db->join('memberPoint AS MP','MP.mbIdx = MB.mbIdx','LEFT');
            $result = $this->db->get('member AS MB')->row_array();
        }else if($type == "KA"){
            $this->db->select('MB.*, MP.mpNowPoint');
            $this->db->where('MB.mbKakao', $mbID);
            $this->db->where('MB.mbKakaoFlag','Y');
            $this->db->where('MB.mbOut','N');
            $this->db->join('memberPoint AS MP','MP.mbIdx = MB.mbIdx','LEFT');
            $result = $this->db->get('member AS MB')->row_array();
        }else{
            $this->db->select('MB.*, MP.mpNowPoint');
            $this->db->where('MB.mbID', 'YP.'.$mbID);
            $this->db->where('MB.mbPassword', md5($mbPW));
            $this->db->where('MB.mbOut','N');
            $this->db->join('memberPoint AS MP','MP.mbIdx = MB.mbIdx','LEFT');
            $result = $this->db->get('member AS MB')->row_array();
        }
        
        
        return $result;
    }
    
    function setMemberEmail($mbIdx){
        $uptQuery = "UPDATE member SET mbEmail = REPLACE(mbID,'YP.','') WHERE mbIdx = '".$mbIdx."'";
        $this->db->query($uptQuery);
    }
    
    function uptMemberOut($mbIdx, $code, $reason){
        $this->db->set('mbOut','Y');
        $this->db->set('mbOutDate', date('Y-m-d H:i:s'));
        $this->db->set('mbOutReason', $code);
        $this->db->set('mbOutReasonDescription', $reason);
        $this->db->where('mbIdx', $mbIdx);
        $this->db->update('member');
    }
    
    function uptMemberPassword($mbIdx, $mbPW){
        $this->db->where('mbIdx', $mbIdx);
        $this->db->set('mbPassword', md5($mbPW));
        $this->db->update('member');
    }
    
    function uptMemberInfo($pData){
        if(isset($pData['nick'])){
            $this->db->set('mbNick', rawurldecode($pData['nick']));
        }
        if(isset($pData['birthday'])){
            $this->db->set('mbBirthday', $pData['birthday']);
        }
        if(isset($pData['mobile'])){
            $this->db->set('mbMobile', $pData['mobile']);
        }
        if(isset($pData['facebookID'])){
            $this->db->set('mbFacebook', $pData['facebookID']);
        }
        if(isset($pData['facebookFlag'])){
            $this->db->set('mbFacebookFlag', $pData['facebookFlag']);
        }
        if(isset($pData['kakaoID'])){
            $this->db->set('mbKakao', $pData['kakaoID']);
        }
        if(isset($pData['kakaoFlag'])){
            $this->db->set('mbKakaoFlag', $pData['kakaoFlag']);
        }
        if(isset($pData['photo'])){
            //$this->db->set('mbPhoto', $pData['photo']);
            $this->db->set('mbPhoto', '');
        }
        if(isset($pData['type'])){
            $this->db->set('mbType', $pData['type']);
        }
        if(isset($pData['mailAgree'])){
            $this->db->set('mbEmailAgree', $pData['mailAgree']);
        }
        $this->db->set('mbMobileCertify','Y');
        $this->db->set('mbModDate', date('Y-m-d H:i:s'));
        $this->db->set('mbDeviceID', $pData['device']);
        $this->db->set('mbVer','1');
        $this->db->where('mbIdx', $pData['idx']);
        $this->db->update('member');
    }

    function insMemberInfo($pData){
        if(isset($pData['facebookID'])){
            $this->db->where('mbFacebook', $pData['facebookID']);
            $facebookFlag = $this->db->count_all_results('member');
            if($facebookFlag > 0 && $pData['facebookID'] != ''){
                $this->db->where('mbFacebook', $pData['facebookID']);
                $this->db->set('mbFacebook', '');
                $this->db->set('mbFacebookFlag', 'N');
                $this->db->update('member');
            }
        }
        if(isset($pData['kakaoID'])){
            $this->db->where('mbKakao', $pData['kakaoID']);
            $facebookFlag = $this->db->count_all_results('member');
            if($facebookFlag > 0 && $pData['kakaoID'] != ''){
                $this->db->where('mbKakao', $pData['kakaoID']);
                $this->db->set('mbKakao', '');
                $this->db->set('mbKakaoFlag', 'N');
                $this->db->update('member');
            }
        }
        if(isset($pData['nick'])){
            $this->db->set('mbNick', rawurldecode($pData['nick']));
        }
        if(isset($pData['birthday'])){
            $this->db->set('mbBirthday', $pData['birthday']);
        }
        if(isset($pData['mobile'])){
            $this->db->set('mbMobile', $pData['mobile']);
        }
        if(isset($pData['facebookID'])){
            $this->db->set('mbFacebook', $pData['facebookID']);
        }
        if(isset($pData['facebookFlag'])){
            $this->db->set('mbFacebookFlag', $pData['facebookFlag']);
        }
        if(isset($pData['kakaoID'])){
            $this->db->set('mbKakao', $pData['kakaoID']);
        }
        if(isset($pData['kakaoFlag'])){
            $this->db->set('mbKakaoFlag', $pData['kakaoFlag']);
        }
        if(isset($pData['photo'])){
            //$this->db->set('mbPhoto', $pData['photo']);
            $this->db->set('mbPhoto', '');
        }
        if(isset($pData['type'])){
            $this->db->set('mbType', $pData['type']);
        }
        if(isset($pData['mailAgree'])){
            $this->db->set('mbEmailAgree', $pData['mailAgree']);
        }
        if(!isset($pData['device'])){
            $pData['device'] = "";
        }
        $this->db->set('mbID', "YP.".$pData['id']);
        $this->db->set('mbPassword', md5($pData['pw']));
        $this->db->set('mbEmail', rawurldecode($pData['id']));
        $this->db->set('mbMobileCertify','Y');
        $this->db->set('mbModDate', date('Y-m-d H:i:s'));
        $this->db->set('mbRegDate', date('Y-m-d H:i:s'));
        $this->db->set('mbDeviceID', $pData['device']);
        $this->db->set('mbVer','1');
        $this->db->insert('member');
        
        return $this->db->insert_id();
    }

    function getSocialCheck($mbID, $type){
        $this->db->where('mbID', $type.".".$mbID);
        $result = $this->db->count_all_results('member');
        
        return $result."";
    }
    
    function uptVersion($mbIdx){
        $this->db->where('mbIdx', $mbIdx);
        $this->db->set('mbVer','1');
		$this->db->set('mbLastestLogin', date('Y-m-d H:i:s'));
        $this->db->update('member');
    }
    
    function uptConnect($pData){
        if($pData['type'] == "KA"){
            $this->db->where('mbKakao', $pData['kakaoID']);
            $this->db->where('mbKakaoFlag','Y');
            $flag = $this->db->count_all_results('member');
            
            if($flag > 0){
                if($pData['kakaoID'] != ''){
                    $this->db->where('mbKakao', $pData['kakaoID']);
                    $this->db->set('mbKakao', '');
                    $this->db->set('mbKakaoFlag', 'N');
                    $this->db->update('member');
                }
            }
        }else if($pData['type'] == "FB"){
            $this->db->where('mbFacebook', $pData['facebookID']);
            $this->db->where('mbFacebookFlag','Y');
            $flag = $this->db->count_all_results('member');
            
            if($flag > 0){
                if($pData['facebookID'] != ""){
                    $this->db->where('mbFacebook', $pData['facebookID']);
                    $this->db->set('mbFacebook', '');
                    $this->db->set('mbFacebookFlag', 'N');
                    $this->db->update('member');
                }
            }
        }
        
        $uptFlag = 0;
        if(isset($pData['facebookID'])){
            $this->db->set('mbFacebook', $pData['facebookID']);
            $this->db->set('mbFacebookFlag',$pData['facebookFlag']);
            $uptFlag++;
        }
        if(isset($pData['kakaoID'])){
            $this->db->set('mbKakao', $pData['kakaoID']);
            $this->db->set('mbKakaoFlag',$pData['kakaoFlag']);
            $uptFlag++;
        }
        if(isset($pData['photo'])){
            //$this->db->set('mbPhoto', $pData['photo']);
            $this->db->set('mbPhoto', '');
            $uptFlag++;
        }
        if($uptFlag > 0){
            $this->db->where('mbIdx', $pData['idx']);
            $this->db->update('member');
        }
        
        return "1";
    }

    function getSocialID($basicID, $flag){
        $this->db->where('mbID',$flag.'.'.$basicID);
        $this->db->where('mbOut','N');
        $this->db->where('mbVer','0');
        $result = $this->db->get('member')->row_array();
        
        return $result;        
    }
    
    function setSocialData($mbIdx, $mbID, $basicIdx, $flag){
        //팁, 예약, 가고싶어요, 1:1질문, 마일리지 이관 로직
        
        //팁
        $this->db->where('mbIdx', $basicIdx);
        $this->db->set('mbIdx', $mbIdx);
        $this->db->update('pensionTip');
        
        //예약
        $this->db->where('mbIdx', $basicIdx);
        $this->db->set('mbIdx', $mbIdx);
        $this->db->set('mbID', $mbID);
        $this->db->update('reservation');
        
        //1:1질문
        $this->db->where('mbIdx', $basicIdx);
        $this->db->set('mbIdx', $mbIdx);
        $this->db->update('appInquiries');
        
        //마일리지 이관
        $this->db->where('mbIdx', $mbIdx);
        $this->db->where("mprResvCode LIKE 'COUPON0009%'",'',false);
        $couponFlag = $this->db->count_all_results('memberPointRaw');
        
        $this->db->where('mbIdx', $basicIdx);
        $this->db->set('mbIdx', $mbIdx);
        $this->db->set('mpID', $mbID);
        if($couponFlag == "1"){
            $this->db->where("mprResvCode NOT LIKE 'COUPON0009%'",'',false);
        }
        $this->db->update('memberPointRaw');
        
        //마일리지 정보
        
        
        $this->db->where('mbIdx', $basicIdx);
        $mbInfo = $this->db->get('memberPoint')->row_array();
        if($couponFlag == "1"){
            $mbInfo['mpNowPoint'] = (int)$mbInfo['mpNowPoint']-3000;
        }
        if(isset($mbInfo['mbIdx'])){
            $this->db->where('mbIdx', $mbIdx);
            $mFlagInfo = $this->db->get('memberPoint')->row_array();
            $this->db->set('mbIdx', $mbIdx);
            $this->db->set('mpId', $mbID);
            if(isset($mFlagInfo['mbIdx'])){
                $this->db->where('mbIdx', $mbIdx);
                $this->db->set('mpNowPoint',($mFlagInfo['mpNowPoint']+$mbInfo['mpNowPoint']));
                $this->db->set('mpUsePoint',($mFlagInfo['mpUsePoint']+$mbInfo['mpUsePoint']));
                $this->db->update('memberPoint');
                //echo $this->db->last_query();
            }else{
                $this->db->set('mpNowPoint',$mbInfo['mpNowPoint']);
                $this->db->set('mpUsePoint',$mbInfo['mpUsePoint']);
                $this->db->insert('memberPoint');
            }
        }else{
            $this->db->where('mbIdx', $mbIdx);
            $mFlagInfo = $this->db->get('memberPoint')->row_array();
            if(!isset($mFlagInfo['mbIdx'])){
                $this->db->set('mpNowPoint', '0');
                $this->db->set('mpUsePoint', '0');
                $this->db->set('mbIdx', $mbIdx);
                $this->db->set('mpId', $mbID);
                $this->db->insert('memberPoint');
            }
        }

        $this->db->set('mbVer','1');
        $this->db->where('mbIdx', $basicIdx);
        $this->db->update('member');
        
        if($flag == "KA"){
            $this->db->set('mbKakaoMileage','Y');
        }else{
            $this->db->set('mbFacebookMileage','Y');
        }
        $this->db->where('mbIdx', $mbIdx);
        $this->db->update('member');
    }

    function insMemberMileage($mbIdx, $mbID){
        $this->db->where('mbIdx', $mbIdx);
        $this->db->where("mprResvCode LIKE 'COUPON0010%'",'',false);
        $count = $this->db->count_all_results('memberPointRaw');
        if($count == 0){
            $this->db->set('mbIdx', $mbIdx);
            $this->db->set('mpID', $mbID);
            $this->db->set('mprResvCode','COUPON0010'.date('mdHis'));
            $this->db->set('mprPointCode','MP003');
            $this->db->set('mprPlusMinus','P');
            $this->db->set('mprPoint', '1000');
            $this->db->set('mprPointDate',date('Y-m-d H:i:s'));
            $this->db->set('mprCronFlag','N');
            $this->db->set('mprUseYn','N');
            $this->db->set('mprMemo','통합 회원가입 쿠폰');
            $this->db->set('mplExpirationDate', date('Y-m-d' , strtotime('+2 year')));
            $this->db->insert('pensionDB.memberPointRaw');
        }
    }
}