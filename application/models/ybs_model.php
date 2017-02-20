<?
class Ybs_model extends CI_Model{
	function __construct(){
		parent::__construct();
		$this->userKey = "598c4b287465f5138096b454bda386a1a984c081";
		$this->ceoKey = "0c1690430a51db1e91fbf95e7675a3285f110a80";
	}

	function getRevInfo($rIdx){
		$this->db->where('R.rIdx', $rIdx);
		$this->db->join('pensionDB.placePensionRoom AS PPR','PPR.pprIdx = R.pprIdx','LEFT');
		$this->db->join('pensionDB.mergePlaceSite AS MPS',"MPS.mpIdx = R.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
		$this->db->join('pensionDB.placePensionBasic AS PPB','PPB.mpIdx = R.mpIdx','LEFT');
		$this->db->group_by('R.rIdx');
		$result = $this->db->get('pensionDB.reservation AS R')->row_array();
		
		return $result;
	}

	function getAccountInfo($rCode){
		$this->db->where('rCode', $rCode);
		$result = $this->db->get('pensionDB.reservationCeoAccount')->row_array();
		
		return $result;
	}


}


?>