<?php
class Sms_model extends CI_Model {
	function __construct() {
		parent::__construct();
		/*
		$CI =& get_instance();
		$CI->dbSMS =& $this->load->database('sms', TRUE);
		*/

	}

	//문자발송
	function sendSMS( $set ){
		if(strlen($sms_data['msg']) <= 255){
			$this->db->set($set);
            return $this->db->insert('emma.em_smt_tran');
        }else{
        	$this->db->set($set);
            return $this->db->insert('emma.em_mmt_tran');
        }        
		/*
		$this->dbSMS->set( $set );

		return $this->dbSMS->insert('SMS_MSG');
		*/
	}
}