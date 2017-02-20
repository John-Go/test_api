<?php
class Info_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    function getLocationInfo(){
        $this->db->like('mtSite','YPS');
        $this->db->like('mtType','PS');
        $this->db->where('LENGTH(mtCode)','5');
        $this->db->where('mtOpen','1');
        $this->db->where("mtCode LIKE '1%'");
        $result = $this->db->get('mergeTheme')->result_array();
        
        return $result;
    }
    
    function getPensionCount($code){
        $schQuery = "   SELECT COUNT(re.mpIdx) AS cnt FROM (SELECT PPU.mpIdx
                        FROM placePensionUse AS PPU
                        LEFT JOIN mergePlaceSite AS PPS ON PPU.mpIdx = PPS.mpIdx AND PPS.mmType LIKE '%YPS%' AND PPS.mpType LIKE '%PS%'
                        LEFT JOIN placeTheme AS PT ON PPS.mpsIdx = PT.mpsIdx
                        WHERE ppuPullFlag = '1'
                        AND PT.mtCode LIKE '".$code."%'
                        AND PPS.mpsOpen = '1'
                        GROUP BY PPU.mpIdx) re";
        $result = $this->db->query($schQuery)->row_array();
        
        return $result['cnt'];
    }
}
?>