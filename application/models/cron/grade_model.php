<?php
class Grade_model extends CI_Model {
    function __construct() {
        parent::__construct();
        $CI =& get_instance();
        $CI->SV102 =& $this->load->database('YP', TRUE);
    }

    function getPensionInfo($mpIdx){
        $schQuery = "   SELECT PPB.*, PT.ptIdx, PPU.*, IFNULL(PPC.ddeonayoKey,'') AS ddeonayo
                        FROM pensionDB.placePensionBasic AS PPB
                        LEFT JOIN pensionDB.placePensionUse AS PPU ON PPU.mpIdx = PPB.mpIdx
                        LEFT JOIN pensionDB.pensionTop AS PT ON PT.mpIdx = PPB.mpIdx AND '".date('Y-m-d')."' BETWEEN PT.ptStart AND PT.ptEnd AND PT.ptOpen = 'Y'
                        LEFT JOIN pensionDB.placePensionConnect AS PPC ON PPC.mpIdx = PPB.mpIdx AND PPC.ddeonayoKey IS NOT NULL AND PPC.ddeonayoKey != ''
                        WHERE PPB.mpIdx = '".$mpIdx."'
                        GROUP BY PPB.mpIdx";
                        
        $result = $this->SV102->query($schQuery)->row_array();
        
        return $result;
    }
    
    function getGradeInfo($mpIdx){
        $schQuery = "   SELECT *
                        FROM pensionDB.pensionGrade AS PG
                        WHERE PG.mpIdx = '".$mpIdx."'
                        AND '".date('Y-m-d')."' BETWEEN PG.pgStart AND PG.pgEnd
                        ORDER BY PG.pgUptDate DESC
                        LIMIT 1";
        $result = $this->SV102->query($schQuery)->row_array();
        
        return $result;
    }
    
    function setPensionGrade($mpIdx, $grade){
        $this->db->where('mpIdx', $mpIdx);
        $this->db->set('ppbGrade', $grade);
        $this->db->update('pensionDB.placePensionBasic');
    }
    
    function getAdEndPension(){
        $yDate = date('Y-m-d', strtotime('-1 day'));
        
        $this->SV102->where('PT.ptEnd',$yDate);
        $this->SV102->where('PT.ptOpen','Y');
        $result = $this->SV102->get('pensionDB.pensionTop AS PT')->result_array();
        
        return $result;
    }
    
    function setPensionTopEnd($ptIdx){
        $this->db->where('ptIdx', $ptIdx);
        $this->db->set('ptOpen','N');
        $this->db->update('pensionDB.pensionTop');
    }
	
	function getGradeEndLists(){
		$yDate = date('Y-m-d', strtotime('-1 day'));
		
		$this->db->where('pgEnd', $yDate);
		$this->db->order_by('pgIdx','ASC');
		$result = $this->db->get('pensionDB.pensionGrade')->result_array();
		
		return $result;
	}
	
	function getAdGradeLists(){
		$this->db->where('ptStart', date('Y-m-d'));
		$this->db->where('ptOpen','Y');
		$result = $this->db->get('pensionDB.pensionTop')->result_array();
		
		return $result;
	}

	function getGradeLists(){
		$this->db->where('pgStart', date('Y-m-d'));
		$result = $this->db->get('pensionDB.pensionGrade')->result_array();
		
		return $result;
	}
}