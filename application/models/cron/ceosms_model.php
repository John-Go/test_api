<?php
class Ceosms_model extends CI_Model {
    function __construct() {
        parent::__construct();
        $this->userKey = "598c4b287465f5138096b454bda386a1a984c081";
        $this->ceoKey = "0c1690430a51db1e91fbf95e7675a3285f110a80";
    }

    function getRevInfo($rIdx){
        $this->db->select('R.*, PPA.ppaBank, PPA.ppaNumber, PPA.ppaOwner, PPB.ppbTel1, PPB.ppbTel2, PPB.ppbTel3, MPS.mpsAddrFlag, MPS.mpsAddr1, MPS.mpsAddr2, MPS.mpsAddr1New');
        $this->db->where('R.rIdx', $rIdx);
        $this->db->join('pensionDB.mergePlaceSite AS MPS'," MPS.mpIdx = R.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->db->join('pensionDB.placePensionAccount AS PPA',"PPA.mpIdx = R.mpIdx AND PPA.ppaRepr = '1'",'LEFT');
        $this->db->join('pensionDB.placePensionBasic AS PPB','PPB.mpIdx = R.mpIdx','LEFT');
        $result = $this->db->get('pensionDB.reservation AS R')->row_array();
        
        return $result;
    }
    
    function insSendSMS($userMsg, $receiver, $type, $msgCode = "", $rIdx, $mpIdx){
        $checkCount = 0;
        $smsIdx = "";
        if($type == "K"){
            $key = $this->userKey;
            
            $this->db->set('date_client_req', date('Y-m-d H:i:s'));
            $this->db->set('subject', '');
            $this->db->set('content_type','0');        
            $this->db->set('attach_file_group_key','0');        
            $this->db->set('service_type','3');
            $this->db->set('broadcast_yn','N');
            $this->db->set('msg_status','1');
            $this->db->set('msg_type','1008');
            $this->db->set('ata_id','');
            $this->db->set('reg_date', date('Y-m-d H:i:s'));
            $this->db->set('callback', '16444816');
            $this->db->set('content',$userMsg);
            $this->db->set('recipient_num',trim(str_replace("-","",$receiver)));
            $this->db->set('sender_key',$this->userKey);
            $this->db->set('template_code',$msgCode);
            $this->db->insert('imds.em_mmt_tran');
            
            $smsIdx = $this->db->insert_id();
            $checkCount++;
        }else{
            if(strlen($userMsg) <= 100){
                $this->db->set('date_client_req', date('Y-m-d H:i:s'));
                $this->db->set('content', $userMsg);
                $this->db->set('callback', '16444816');
                $this->db->set('service_type','0');
                $this->db->set('broadcast_yn','N');
                $this->db->set('msg_status','1');
                $this->db->set('recipient_num',trim(str_replace("-","",$receiver)));
                $this->db->insert('emma.em_smt_tran');
                
                $type = "S";
                $smsIdx = $this->db->insert_id();
                $checkCount++;
            }else{
                $this->db->set('date_client_req', date('Y-m-d H:i:s'));
                $this->db->set('subject', '');
                $this->db->set('content_type','0');        
                $this->db->set('attach_file_group_key','0');        
                $this->db->set('service_type','3');
                $this->db->set('broadcast_yn','N');
                $this->db->set('msg_status','1');
                $this->db->set('msg_type','1001');
                $this->db->set('emma_id','');
                $this->db->set('callback', '16444816');
                $this->db->set('content',$userMsg);
                $this->db->set('recipient_num',trim(str_replace("-","",$receiver)));
                $this->db->insert('emma.em_mmt_tran');
                
                $type = "L";
                $smsIdx = $this->db->insert_id();
                $checkCount++;
            }
        }

        if($checkCount > 0){
            $this->db->set('pssType', $type);
            $this->db->set('pssSender', '16444816');
            $this->db->set('pssReceiver', $receiver);
            $this->db->set('content', $userMsg);
            $this->db->set('pssIP', $_SERVER['REMOTE_ADDR']);
            $this->db->set('pssRegDate', date('Y-m-d H:i:s'));
            $this->db->set('mpIdx', $mpIdx);
            $this->db->set('rIdx', $rIdx);
            $this->db->set('smsIdx', $smsIdx);
            $this->db->insert('pensionDB.ybsSendSMS');
        }
    }
}