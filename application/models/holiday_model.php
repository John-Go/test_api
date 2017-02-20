<?php
class Holiday_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }
    
    function getHoliday($startDate, $endDate){
        $schQuery = "   SELECT *
                        FROM holidayDate
                        WHERE hdDate BETWEEN '".$startDate."' AND '".$endDate."'
                        ORDER BY hdDate ASC";
        $result = $this->db->query($schQuery)->result_array();
        
        return $result;
    }
}
    
?>