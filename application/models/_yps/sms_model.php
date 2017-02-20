<?php
class Sms_model extends CI_Model {
	function __construct() {
		parent::__construct();
		/*
		$this->dbsms = $this->load->database('sms', TRUE);
		*/
	}

	// sms 발송 
	public function send($array) {
		extract( $array ); 
		if (!$mobile || !$content) return FALSE;
		if(strlen($content) <= 255){
            $this->db->set('date_client_req', date('Y-m-d H:i:s'));
            $this->db->set('content', $content);
            $this->db->set('callback', (!empty($sender))?$sender:'16444816');
            $this->db->set('service_type','0');
            $this->db->set('broadcast_yn','N');
            $this->db->set('msg_status','1');
            $this->db->set('recipient_num',$mobile);
            return $this->db->insert('emma.em_smt_tran');
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
            $this->db->set('callback', (!empty($sender))?$sender:'16444816');
            $this->db->set('content',$content);
            $this->db->set('recipient_num',$mobile);
            return $this->db->insert('emma.em_mmt_tran');
        }        
		
	/*
		extract( $array ); 
		// $type=M (mms) , S (sms) , $sender (발송인) ,$mobile (수신자), $content (내용)
		if (!$mobile || !$content) return FALSE;

		return $this->dbsms->insert('SMS_MSG', array(
			'receiver' 	=> $mobile, 
			'Sender' 	=> (!empty($sender))?$sender:'16444816',
			'MsgType'	=> (!empty($type))?$type:'S',
			'msg' 		=> $content,
			'ReserveDT' => TIME_YMDHIS,
			'CreateDT' 	=> TIME_YMDHIS
		));
	*/
	}

	// 인증번호를 입력한다 
	public function setCertify($array) {
		extract( $array ); 
		// $session_id , $certifyKey
		if (!$array['idx'] || !$certifyKey) return FALSE;

		return $this->db->insert('logSmsCertify', array(
			'session_id'=> $array['idx'],
			'certifyKey'=> $certifyKey
		));
	}

	// 인증번호 확인
	public function getCertifyCount($idx) {
		// $session_id , $certifyKey
		if (!$idx) return FALSE;

		$this->db->start_cache();
		$this->db->where('session_id',$idx);
		$this->db->where('regDate >= DATE_ADD(NOW(), INTERVAL -1 DAY) ','',FALSE);
		$this->db->stop_cache();

		// 전체 인증 키를 가져온다
		$result['count']['total']	= $this->db->count_all_results('logSmsCertify');

		$this->db->start_cache();
		$this->db->where('regDate >= DATE_ADD(NOW(), INTERVAL -3 MINUTE) ','',FALSE);
		$this->db->stop_cache();

		// 현재 시간 기준으로 3분이내 등록된 적용 가능한 키갯수를 가져온다
		$result['count']['able']	= $this->db->count_all_results('logSmsCertify');

		$this->db->start_cache();
		$this->db->select('certifyKey ,regDate',FALSE)->order_by('regDate','desc')->limit(1);
		$this->db->stop_cache();

		// 현재 가장 마지막에 등록된 확인해야 할 인증번호 키를 가져온다.
		$result['array']	= $this->db->get('logSmsCertify',1)->row_array();

		$this->db->flush_cache();

		return $result;
	}
}