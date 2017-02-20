<?php
class Ynj_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		$CI =& get_instance();
		$CI->aaaDB = $this->load->database('ynj', TRUE);
	}

	// ***************************************************** 등록된 호텔 키값으로 테마 키값 검색 *******************************************************
	public function getHotelInfoCount($ci_idxs) {
		$this->aaaDB->where('hiOpen', 'Y');
		$this->aaaDB->where_in('hiIdx', $ci_idxs);
		//return $this->aaaDB->count_all_results('haHotelInfo');
	}
	// ***************************************************** 등록된 호텔 키값으로 테마 키값 검색 *******************************************************
	
	// ***************************************************** 등록된 모텔 키값으로 테마 키값 검색 *******************************************************
	public function getMotelInfoCount($ci_idxs) {
	//	$this->aaaDB->where('miOpen', 'Y');
	//	$this->aaaDB->where_in('miIdx', $ci_idxs);
	//	return $this->aaaDB->count_all_results('ynjMotelInfo');
	return 1;
	}
	// ***************************************************** 등록된 모텔 키값으로 테마 키값 검색 *******************************************************
}
?>