<?php
class Calendar_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}
	
    function getRoomList($mpIdx, $idxStrings){
        $this->db->where('mpIdx', $mpIdx);
        if($idxStrings != ""){
            //$this->db->where_not_in('pprIdx', $idxStrings);
        }
        //$this->db->limit('15');
        $this->db->where('pprOpen','1');
        $this->db->order_by('pprNo','DESC');
        $result = $this->db->get('placePensionRoom')->result_array();
        
        return $result;
    }
    
    function getPensionInfo($mpIdx){
        $this->db->where('mpIdx', $mpIdx);
        $result = $this->db->get('placePensionBasic')->row_array();
        
        return $result;
    }
    
    function getReserveList($mpIdx, $sunDate, $satDate){
        $sch_query = "  SELECT A.*, B.rPaymentState
                        FROM placePensionBlock A
                        LEFT JOIN reservation B ON A.rIdx = B.rIdx
                        WHERE A.mpIdx = '".$mpIdx."'
                        AND A.ppbDate BETWEEN '".$sunDate."' AND '".$satDate."'
                        ORDER BY pprIdx ASC, ppbDate ASC";
        $result = $this->db->query($sch_query)->result_array();
        
        return $result;        
    }
    
    function getHolidayLists($mpIdx, $date){
        $startDate = $date;
        $dateArray = explode("-", $date);
        $endDate = date('Y-m', mktime(0, 0, 0, $dateArray[1]+2, '01', $dateArray[0]));
        $this->db->where("SUBSTR(H.hDate,1,7) BETWEEN '$startDate' AND '$endDate'");        
        $this->db->join('holidayExclude AS HE',"H.hIdx = HE.hIdx AND HE.mpIdx = '".$mpIdx."'",'LEFT');        
        $result = $this->db->get('holiday AS H')->result_array();
        
        return $result;
    }
}
?>