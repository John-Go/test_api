<?php
class Common_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	/* API KEY 를 가져오는 메서드 */
	function getApiKey( $key ) {
		if ( !$key ) return FALSE;

		$this->db->where('akKey', $key);
		$this->db->from('commonDB.appKey');
		$result = $this->db->get();

		$ret = array();
		if( $result->num_rows() ){
			$row = $result->row();
			$ret['device']	= $row->akDevice;
			$ret['site']	= $row->akCode;
		}

		return $ret;
	}
}