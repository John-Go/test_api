<?php
class Basket_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * 펜션의 가고싶어요 수 가져오기
	 * 
	 * @param int mpIdx : mergePlace idx
	 * @return string : 가고싶어요 수
	 */
	public function getPensionBasketCountByMpIdx( $mpIdx )
	{
		$result = 0;
		
		if ( isset($mpIdx) && count($mpIdx) > 0 )
		{
			$this->db->where('mpIdx', $mpIdx);
			$result = $this->db->count_all_results('pensionBasket');
		}
		
		return (string)$result;
	}
	
	
	
	/**
	 * 여행지 가고싶어요 수 가져오기
	 * 
	 * @param int mpIdx : mergePlace idx
	 * @return string : 가고싶어요 수
	 */
	public function getTravelBasketCountByMpIdx( $mpIdx )
	{
		$result = 0;
		
		if ( isset($mpIdx) && count($mpIdx) > 0 )
		{
			$this->db->where('mpIdx', $mpIdx);
			$result = $this->db->count_all_results('travelBasket');
		}
		
		return (string)$result;
	}
}