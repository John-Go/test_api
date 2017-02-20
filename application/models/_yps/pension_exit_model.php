<?php
class Pension_exit_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}
	public function getPensionLists(){
		$schQuery = "   SELECT MPS.mpsName, ARA.* 
                        FROM pensionDB.appRandomAd AS ARA
                        LEFT JOIN pensionDB.mergePlaceSite AS MPS ON MPS.mpIdx = ARA.mpIdx
                        WHERE MPS.mmType LIKE '%YPS%' AND MPS.mpType LIKE '%PS%' 
                        AND ARA.araOpen =  '1'
                        AND '".date('Y-m-d')."' BETWEEN ARA.araStartDate AND ARA.araEndDate
                        ORDER BY  RAND()
						LIMIT 1
        ";
        $result = $this->db->query($schQuery)->result_array();
        return $result;
	}	
}	
?>