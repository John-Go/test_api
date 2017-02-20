<?php
class Ssan_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		$CI =& get_instance();
		$CI->smtDB = $this->load->database('smt', TRUE);
	}

	public function getSsanmotelInfoCount($ci_idxs) {
		$this->smtDB->where('miOpen', 'Y');
		$this->smtDB->where_in('miIdx', $ci_idxs);
		return $this->smtDB->count_all_results('ssanMotelInfoCommon');
	}
}
?>