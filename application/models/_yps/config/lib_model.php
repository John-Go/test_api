<?php
class Lib_model extends CI_Model {
	function __construct() {
		parent::__construct();
        $CI =& get_instance();
        $CI->dbInfo =& $this->load->database('info', TRUE);
	}

	function themeInfo($idx){
		$this->db->select('MT.mtName');
		$this->db->from('placeTheme PT');
        
        // 201406091105 pyh : 닫힌 테마가 열려서 추가
		// $this->db->join('mergeTheme MT', 'PT.mtCode = MT.mtCode', 'inner');
		$this->db->join('mergeTheme MT', 'PT.mtCode = MT.mtCode AND MT.mtOpen = 1', 'inner');
		
		$this->db->where('mpsIdx',$idx);
		$this->db->where('PT.mtCode >=', 2);
		$this->db->like('mtSite', 'YPS');
        
		$this->db->order_by("MT.mtSort", "desc");

		return $this->db->get()->result_array();
	}	

	function travelThemeInfo($idx){

		$this->dbInfo->select("C.ca_name");
		$this->dbInfo->where('CI.ci_idx', $idx);
		$this->dbInfo->where('CI.ca_type', 'T');
		$this->dbInfo->join('infoDB.category C', "CI.ca_type=C.ca_type and CI.ca_code=C.ca_code");
		$result = $this->dbInfo->get('infoDB.categoryInfo CI', 1, 0)->row_array();

		return $result['ca_name'];
	}

	function holidayLists($startDate, $endDate){
		$this->db->select("(hDate + INTERVAL -1 DAY) as ageDate");
		$this->db->where('(hDate + INTERVAL -1 DAY) >=', $startDate);
		$this->db->where('(hDate + INTERVAL -1 DAY) <=', $endDate);
		$result = $this->db->get('pensionDB.holiday', 1, 0)->result_array();

		$arrayResult = array();

		foreach ($result as $row) {
			$arrayResult[$row['ageDate']] = 1;
		}

		return $arrayResult;

	}

	function travelImageCount($idx) {
		$this->dbInfo->select('
		dniiFileName1,dniiFileName2,dniiFileName3,dniiFileName4,dniiFileName5,dniiFileName6,dniiFileName7,dniiFileName8,dniiFileName9,dniiFileName10,dniiFileName11,dniiFileName12,dniiFileName13,dniiFileName14,dniiFileName15,dniiFileName16,dniiFileName17,dniiFileName18,dniiFileName19,dniiFileName20,dniiFileName21,dniiFileName22,dniiFileName23,dniiFileName24,dniiFileName25,dniiFileName26,dniiFileName27,dniiFileName28,dniiFileName29,dniiFileName30,dniiFileName31,dniiFileName32,dniiFileName33,dniiFileName34,dniiFileName35,dniiFileName36,dniiFileName37,dniiFileName38,dniiFileName39,dniiFileName40,dniiFileName41,dniiFileName42,dniiFileName43,dniiFileName44,dniiFileName45,dniiFileName46,dniiFileName47,dniiFileName48,dniiFileName49,dniiFileName50,dniiFileName51,dniiFileName52,dniiFileName53,dniiFileName54,dniiFileName55,dniiFileName56,dniiFileName57,dniiFileName58,dniiFileName59,dniiFileName60,dniiFileName61,dniiFileName62,dniiFileName63,dniiFileName64,dniiFileName65,dniiFileName66,dniiFileName67,dniiFileName68,dniiFileName69,dniiFileName70,dniiFileName71,dniiFileName72,dniiFileName73,dniiFileName74,dniiFileName75,dniiFileName76,dniiFileName77,dniiFileName78,dniiFileName79,dniiFileName80,dniiFileName81,dniiFileName82,dniiFileName83,dniiFileName84,dniiFileName85,dniiFileName86,dniiFileName87,dniiFileName88,dniiFileName89,dniiFileName90,dniiFileName91,dniiFileName92,dniiFileName93,dniiFileName94,dniiFileName95,dniiFileName96,dniiFileName97,dniiFileName98,dniiFileName99,dniiFileName100');
		$this->dbInfo->where('dniIdx', $idx);
		return $this->dbInfo->get('infoDB.ynjDateNewInfoImage')->result_array();
	}

}
?>