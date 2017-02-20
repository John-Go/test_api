<?php
class Em_model extends CI_Model {
    function __construct() {
        parent::__construct();
        $CI =& get_instance();        
        @$CI->em =& $this->load->database('em', TRUE);
        $this->userKey = "598c4b287465f5138096b454bda386a1a984c081";
        $this->ceoKey = "0c1690430a51db1e91fbf95e7675a3285f110a80";
        $this->testKey = "55e07b0508de35a7fef2bfe1e8cd04dba8995a67";
    }
    
    function setException($cid, $rejectnumber, $trannumber, $regdate){
        $this->em->where('rejectnumber', $rejectnumber);
        $check = $this->em->count_all_results('msg_exception');
        
        if($check > 0){
            return "duplication";
        }else{
            $this->em->set('cid', $cid);
            $this->em->set('rejectnumber', $rejectnumber);
            $this->em->set('trannumber', $trannumber);
            $this->em->set('regdate', $regdate);
            $this->em->insert('msg_exception');
            
            return "success";
        }
    }
    
    function setTalk($msgType, $msg, $receiver, $type){        
        if($type == "U"){
            $key = $this->userKey;
            
            $this->em->set('date_client_req', date('Y-m-d H:i:s'));
            $this->em->set('subject', '');
            $this->em->set('content_type','0');        
            $this->em->set('attach_file_group_key','0');        
            $this->em->set('service_type','3');
            $this->em->set('broadcast_yn','N');
            $this->em->set('msg_status','1');
            $this->em->set('msg_type','1008');
            $this->em->set('ata_id','');
            $this->em->set('reg_date', date('Y-m-d H:i:s'));
            $this->em->set('callback', '16444816');
            $this->em->set('content',$msg);
            $this->em->set('recipient_num',trim(str_replace("-","",$receiver)));
            $this->em->set('sender_key',$key);
            $this->em->set('template_code',$msgType);
            $this->em->insert('em_mmt_tran');
        }else{
            $key = $this->ceoKey;
            if(strlen($msg) <= 100){
                $this->em->set('date_client_req', date('Y-m-d H:i:s'));
                $this->em->set('content', $msg);
                $this->em->set('callback', '16444816');
                $this->em->set('service_type','0');
                $this->em->set('broadcast_yn','N');
                $this->em->set('msg_status','1');
                $this->em->set('recipient_num',str_replace("-","",$receiver));
                $this->em->insert('emma.em_smt_tran');
            }else{
                $this->em->set('date_client_req', date('Y-m-d H:i:s'));
                $this->em->set('subject', '');
                $this->em->set('content_type','0');        
                $this->em->set('attach_file_group_key','0');        
                $this->em->set('service_type','3');
                $this->em->set('broadcast_yn','N');
                $this->em->set('msg_status','1');
                $this->em->set('msg_type','1001');
                $this->em->set('emma_id','');
                $this->em->set('callback', '16444816');
                $this->em->set('content',$msg);
                $this->em->set('recipient_num',str_replace("-","",$receiver));
                $this->em->insert('emma.em_mmt_tran');
            }
        }
    }

    function testTalk(){
        $key = $this->userKey;
            
        $msg = "[#{pensionName} -입금대기]

펜션명 : #{pension}

예약자 : #{user}
옵션 : #{options}

결제금액 : #{price}원
현장결제 : #{noPrice}원 (펜션 도착 후 결제)
#{methodText} : #{account}
#{payMethod}기한 : #{limit}

예약확인 : #{URL}

#{payMethod}기한 내 #{payMethod}확인이 되지 않으면, 예약이 취소됩니다.";
        $this->em->set('date_client_req', date('Y-m-d H:i:s'));
        $this->em->set('subject', '');
        $this->em->set('content_type','0');        
        $this->em->set('attach_file_group_key','0');        
        $this->em->set('service_type','3');
        $this->em->set('broadcast_yn','N');
        $this->em->set('msg_status','1');
        $this->em->set('msg_type','1008');
        $this->em->set('ata_id','');
        $this->em->set('reg_date', date('Y-m-d H:i:s'));
        $this->em->set('callback', '16444816');
        $this->em->set('content',$msg);
        $this->em->set('recipient_num','01064550315');
        $this->em->set('sender_key',$key);
        $this->em->set('template_code','YP_RW_M_3');
        $this->em->insert('em_mmt_tran');
    }
    
    function getRevInfo($rIdx){
        $this->db->where('R.rIdx', $rIdx);
        $this->db->join('pensionDB.placePensionRoom AS PPR','PPR.pprIdx = R.pprIdx','LEFT');
        $this->db->join('pensionDB.mergePlaceSite AS MPS',"MPS.mpIdx = R.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $this->db->join('pensionDB.placePensionBasic AS PPB','PPB.mpIdx = R.mpIdx','LEFT');
        $this->db->join('pensionDB.reservationXpayInfo AS RXI','R.rPgCode = RXI.LGD_TID','LEFT');
        $this->db->group_by('R.rIdx');
        $result = $this->db->get('pensionDB.reservation AS R')->row_array();
        
        return $result;
    }
    
    function setSMS($phone, $msg){
        $this->em->set('date_client_req', date('Y-m-d H:i:s'));
        $this->em->set('content', $msg);
        $this->em->set('callback', '16444816');
        $this->em->set('service_type','0');
        $this->em->set('broadcast_yn','N');
        $this->em->set('msg_status','1');
        $this->em->set('recipient_num',$phone);
        $this->em->insert('em_smt_tran');
    }
    
    function returnMsg(){
        $checkArray = array('2000','3050', '3049', '3051', '3054', '9998', '9999', '3021','3022','3024','3025','3026','3027','3028','3034','3036','1001','1002','1003','1009','1010','1022','1012','1013','1018','1019','1020','E901','E903','E904','E905','E906','E915','E916','E917','E918','E919','E920','E999');
        $this->em->where_in('mt_report_code_ib', $checkArray);
		$this->em->where('reg_date >=', '2016-06-02 11:30:00');
        $this->em->order_by('mt_pr','ASC');
        $lists = $this->em->get('em_mmt_tran')->result_array();
        
        if(count($lists) > 0){
            foreach($lists as $lists){
                if(strlen($lists['content']) <= 100){
                    $this->em->set('date_client_req', date('Y-m-d H:i:s'));
                    $this->em->set('content', $lists['content']);
                    $this->em->set('callback', '16444816');
                    $this->em->set('service_type','0');
                    $this->em->set('broadcast_yn','N');
                    $this->em->set('msg_status','1');
                    $this->em->set('recipient_num',str_replace("-","",$lists['recipient_num']));
                    $this->em->insert('emma.em_smt_tran');
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
                    $this->db->set('content',$lists['content']);
                    $this->db->set('recipient_num',str_replace("-","",$lists['recipient_num']));
                    $this->db->insert('emma.em_mmt_tran');
                }
                $this->em->set('mt_report_code_ib','RMSG');
                $this->em->where('mt_pr', $lists['mt_pr']);
                $this->em->update('em_mmt_tran');
            }
        }
    }

    function resendLMS(){
        $setTable = date('Ym', mktime(0, 0, 0, date('m'), date('d')-1, date('Y')));
        $setDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-1, date('Y')));
        
        $insQuery = "INSERT INTO emma.em_smt_tran (date_client_req, content, callback, service_type, broadcast_yn, msg_status, recipient_num)
                    (SELECT now(), content, callback, service_type, broadcast_yn, msg_status, recipient_num
                    FROM emma.em_smt_log_".$setTable."
                    WHERE mt_report_code_ib IN ('2000','2001','3021','3022','3024','3025','3026','3027','3028','3034','3036','1001','1002','1003','1009','1010','1022','1012','1013','1018','1019','1020','E901','E903','E904','E905','E906','E915','E916','E917','E918','E919','E920','E999')
                    AND SUBSTR(date_client_req,1,10) = '".$setDate."'
                    AND recipient_num != '')";
        $this->db->query($insQuery);
        $insQuery = "INSERT INTO emma.em_mmt_tran (date_client_req, `subject`, content_type, attach_file_group_key, service_type, broadcast_yn, msg_status, msg_type, emma_id, callback, content, recipient_num)
                    (SELECT NOW(), `subject`, content_type, attach_file_group_key, service_type, broadcast_yn, msg_status, msg_type, emma_id, callback, content, recipient_num
                    FROM emma.em_mmt_log_201512
                    WHERE mt_report_code_ib IN ('2000','2001','3021','3022','3024','3025','3026','3027','3028','3034','3036','1001','1002','1003','1009','1010','1022','1012','1013','1018','1019','1020','E901','E903','E904','E905','E906','E915','E916','E917','E918','E919','E920','E999')
                    AND SUBSTR(date_client_req,1,10) = '".$setDate."'
                    AND recipient_num != '')";
        $this->db->query($insQuery);
    }
    
    function checkReMsg($msg, $receiver){
        $this->db->like('content', $msg);
        $this->db->where('recipient_num', $receiver);
        $this->db->where('reg_date > DATE_ADD(NOW(), INTERVAL -3 HOUR)','',false);
        $result = $this->db->count_all_results('emma.em_mmt_log_'.date('Ym'));
        
        return $result;
    }
    
    function checkReMsgUser($msg, $receiver){
        $this->db->like('content', $msg);
        $this->db->where('recipient_num', $receiver);
        $this->db->where('reg_date > DATE_ADD(NOW(), INTERVAL -3 HOUR)','',false);
        $result = $this->db->count_all_results('imds.em_mmt_tran');
        
        return $result;
    }
}