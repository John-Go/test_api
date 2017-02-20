<?php
class Smsnew_model extends CI_Model {
	function __construct() {
		parent::__construct();
		$this->userKey	= "598c4b287465f5138096b454bda386a1a984c081";
		$this->ceoKey	= "0c1690430a51db1e91fbf95e7675a3285f110a80";
        $this->userYBSKey = "b0e39c83830344825c0e225e0d1397800efe7970";
	}

	// 주문정보
	function getRevInfo($rIdx){
		$this->db->select('R.*, PPA.ppaBank, PPA.ppaNumber, PPA.ppaOwner, PPA.ppaDepositBankName, PPA.ppaDepositNo, PPA.ppaDepositOwner, PPA.ppaDepositFlag,  PPB.ppbTel1, PPB.ppbTel2, PPB.ppbTel3, MPS.mpsAddrFlag, MPS.mpsAddr1, MPS.mpsAddr2, MPS.mpsAddr1New, MPS.mpsTel, PPR.pprInMin');
		$this->db->where('R.rIdx', $rIdx);
		$this->db->join('pensionDB.mergePlaceSite AS MPS'," MPS.mpIdx = R.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
		$this->db->join('pensionDB.placePensionAccount AS PPA',"PPA.mpIdx = R.mpIdx AND PPA.ppaRepr = '1'",'LEFT');
		$this->db->join('pensionDB.placePensionBasic AS PPB','PPB.mpIdx = R.mpIdx','LEFT');
		$this->db->join('pensionDB.placePensionRoom AS PPR','PPR.pprIdx = R.pprIdx','LEFT');
		$result = $this->db->get('pensionDB.reservation AS R')->row_array();

		return $result;
	}

	function getRevInfoLists($rIdx)
	{
		$this->db->where('rIdx', $rIdx);
		$result	= $this->db->get('pensionDB.pensionRevInfo')->result_array();

		return $result;
	}

	function getRevOptionLists($rIdx)
	{
		$this->db->where('rIdx', $rIdx);
		$result	= $this->db->get('pensionDB.pensionRevOption')->result_array();

		return $result;
	}

	function getCeoAccountInfo($rCode){
		$this->db->where('rCode', $rCode);
		$result = $this->db->get('pensionDB.reservationCeoAccount')->row_array();
		return $result;
	}

	function getAccountInfo($rCode)
	{
		$this->db->where('LGD_OID', $rCode);
		$result = $this->db->get('pensionDB.reservationXpayInfo')->row_array();
		return $result;
	}

	function getAccountInfoNew($rCode){
		$this->db->where('ordr_idxx', $rCode);
		$result = $this->db->get('pensionDB.pensionRevAccountLog')->row_array();
		return $result;
	}

	function getArsInfo($rCode){
		$this->db->where('ordr_idxx', $rCode);
		$result = $this->db->get('pensionDB.pensionRevArsLog')->row_array();
		return $result;
	}

	function getPensionMsgTemplate($rRoot, $state){
		$this->db->where('rRoot', $rRoot);
		$this->db->where('pmtFlag', '1');
		$this->db->like('rPaymentState', '|' . $state . '|');
		$resArr	= $this->db->get('pensionDB.pensionMsgTemplate')->result_array();

		$result	= array();
		$result['stateArr']	= $resArr[0]['rPaymentState'];

		foreach($resArr as $k => $arr){
			$result['lists'][$arr['pmtVer']]	= $arr;
		}

		return $result;
	}

	function getPensionMsgTemplateTest($rRoot, $state){
		$this->db->where('rRoot', $rRoot);
		$this->db->where('pmtFlag', '0');
		$this->db->like('rPaymentState', '|' . $state . '|');
		$resArr	= $this->db->get('pensionDB.pensionMsgTemplate')->result_array();

		$result	= array();
		$result['stateArr']	= $resArr[0]['rPaymentState'];

		foreach($resArr as $k => $arr){
			$result['lists'][$arr['pmtVer']]	= $arr;
		}

		return $result;
	}

	function getPensionMsgTemplateInfo($code)
	{
		$this->db->where('pmtCode', $code);
		return $this->db->get('pensionDB.pensionMsgTemplate')->row_array();
	}

	function getRevMsgFlag($rIdx)
	{
		$this->db->where('rIdx', $rIdx);
		$res	= $this->db->get('pensionDB.pensionRevMsg');

		$result	= array();

		foreach($res->result_array() as $arr)
		{
			$result[$arr['type']][$arr['typeIdx']]	= $arr;
		}

		return $result;
	}

	function uptRevMsgFlag($prmIdxArr, $type)
	{
		foreach($prmIdxArr as $k => $v)
		{
			$this->db->set($type, '1');
			$this->db->where('prmIdx', $v);

			$this->db->update('pensionDB.pensionRevMsg');
		}
	}

	function sendSMS($msg, $receiver, $type, $msgCode = "")
	{
		// log_message('error','Receiver => '.$receiver);
		// log_message('error','Type => '.$type);
		// log_message('error','MsgCode => '.$msgCode);



		// 설정
		$key	= null;


		// 알림톡
		if($type == 'K'){
            $key    = $this->userKey;
            $this->db->set('date_client_req', date('Y-m-d H:i:s'));
            $this->db->set('subject', '');
            $this->db->set('content_type', '0');
            $this->db->set('attach_file_group_key', '0');
            $this->db->set('service_type', '3');
            $this->db->set('broadcast_yn', 'N');
            $this->db->set('msg_status', '1');
            $this->db->set('msg_type', '1008');
            $this->db->set('ata_id', '');
            $this->db->set('reg_date', date('Y-m-d H:i:s'));
            $this->db->set('callback', '16444816');
            $this->db->set('content', $msg);
            $this->db->set('recipient_num', str_replace("-","",trim($receiver)));
            $this->db->set('sender_key', $key);
            $this->db->set('template_code', $msgCode);
            $this->db->insert('imds.em_mmt_tran');
        }else if($type == 'YBSK'){
            $key    = $this->userYBSKey;
            $this->db->set('date_client_req', date('Y-m-d H:i:s'));
            $this->db->set('subject', '');
            $this->db->set('content_type', '0');
            $this->db->set('attach_file_group_key', '0');
            $this->db->set('service_type', '3');
            $this->db->set('broadcast_yn', 'N');
            $this->db->set('msg_status', '1');
            $this->db->set('msg_type', '1008');
            $this->db->set('ata_id', '');
            $this->db->set('reg_date', date('Y-m-d H:i:s'));
            $this->db->set('callback', '16444816');
            $this->db->set('content', $msg);
            $this->db->set('recipient_num', str_replace("-","",trim($receiver)));
            $this->db->set('sender_key', $key);
            $this->db->set('template_code', $msgCode);
            $this->db->insert('imds.em_mmt_tran');
        }else if($type == 'YBS'){
		    $key  = $this->ceoKey;

            if(strlen($msg) <= 100){
                $this->db->set('date_client_req', date('Y-m-d H:i:s'));
                $this->db->set('content', $msg);
                $this->db->set('callback', '16447858');
                $this->db->set('service_type','0');
                $this->db->set('broadcast_yn','N');
                $this->db->set('msg_status','1');
                $this->db->set('recipient_num',str_replace("-", "", trim($receiver)));
                $this->db->insert('emma.em_smt_tran');
            }else{
                $this->db->set('date_client_req', date('Y-m-d H:i:s'));
                $this->db->set('subject', '');
                $this->db->set('content_type', '0');
                $this->db->set('attach_file_group_key', '0');
                $this->db->set('service_type', '3');
                $this->db->set('broadcast_yn', 'N');
                $this->db->set('msg_status', '1');
                $this->db->set('msg_type', '1001');
                $this->db->set('emma_id', '');
                $this->db->set('callback', '16447858');
                $this->db->set('content', $msg);
                $this->db->set('recipient_num', str_replace("-", "", trim($receiver)));
                $this->db->insert('emma.em_mmt_tran');
            }
        }else{
		    // SMS
			$key = $this->ceoKey;

            if(strlen($msg) <= 100)
            {
                $this->db->set('date_client_req', date('Y-m-d H:i:s'));
                $this->db->set('content', $msg);
                $this->db->set('callback', '16444816');
                $this->db->set('service_type','0');
                $this->db->set('broadcast_yn','N');
                $this->db->set('msg_status','1');
                $this->db->set('recipient_num',str_replace("-", "", trim($receiver)));
                $this->db->insert('emma.em_smt_tran');
            }else{
                $this->db->set('date_client_req', date('Y-m-d H:i:s'));
                $this->db->set('subject', '');
                $this->db->set('content_type', '0');
                $this->db->set('attach_file_group_key', '0');
                $this->db->set('service_type', '3');
                $this->db->set('broadcast_yn', 'N');
                $this->db->set('msg_status', '1');
                $this->db->set('msg_type', '1001');
                $this->db->set('emma_id', '');
                $this->db->set('callback', '16444816');
                $this->db->set('content', $msg);
                $this->db->set('recipient_num', str_replace("-", "", trim($receiver)));
                $this->db->insert('emma.em_mmt_tran');
            }
		}

		return true;
	}

	function sendSMSNew($msg, $receiver, $type, $msgCode = "")
	{
		// 설정
		$key	= null;


		// 알림톡
		if($type == 'K')
		{
			$key	= $this->userKey;
			$this->db->set('date_client_req', date('Y-m-d H:i:s'));
			$this->db->set('subject', '');
			$this->db->set('content_type', '0');
			$this->db->set('attach_file_group_key', '0');
			$this->db->set('service_type', '3');
			$this->db->set('broadcast_yn', 'N');
			$this->db->set('msg_status', '1');
			$this->db->set('msg_type', '1008');
			$this->db->set('ata_id', '');
			$this->db->set('reg_date', date('Y-m-d H:i:s'));
			$this->db->set('callback', '16444816');
			$this->db->set('content', $msg);
			$this->db->set('recipient_num', str_replace("-","",trim($receiver)));
			$this->db->set('sender_key', $key);
			$this->db->set('template_code', $msgCode);
			$this->db->insert('imds.em_mmt_tran');
		}
		// SMS
		else
		{
			$key	= $this->ceoKey;

			if(strlen($msg) <= 100)
			{
				$this->db->set('date_client_req', date('Y-m-d H:i:s'));
				$this->db->set('content', $msg);
				$this->db->set('callback', '16444816');
				$this->db->set('service_type','0');
				$this->db->set('broadcast_yn','N');
				$this->db->set('msg_status','1');
				$this->db->set('recipient_num',str_replace("-", "", trim($receiver)));
				$this->db->insert('emma.em_smt_tran');
			}
			else
			{
				$this->db->set('date_client_req', date('Y-m-d H:i:s'));
				$this->db->set('subject', '');
				$this->db->set('content_type', '0');
				$this->db->set('attach_file_group_key', '0');
				$this->db->set('service_type', '3');
				$this->db->set('broadcast_yn', 'N');
				$this->db->set('msg_status', '1');
				$this->db->set('msg_type', '1001');
				$this->db->set('emma_id', '');
				$this->db->set('callback', '16444816');
				$this->db->set('content', $msg);
				$this->db->set('recipient_num', str_replace("-", "", trim($receiver)));
				$this->db->insert('emma.em_mmt_tran');
			}
		}
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
