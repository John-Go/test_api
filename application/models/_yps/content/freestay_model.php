<?php
class Freestay_model extends CI_Model {
    function __construct() {
        parent::__construct();
        $CI =& get_instance();
        $CI->SV102 =& $this->load->database('YP', TRUE);
    }

    function getFreeStayLists(){
        $schQuery = "   SELECT MPS.mpsName, PFS.*, PFSI.*, GROUP_CONCAT(PFSL.pfslName) AS userName, GROUP_CONCAT(PFSL.pfslMobile) AS userMobile
                        FROM pensionFreeStay AS PFS
                        LEFT JOIN mergePlaceSite AS MPS ON PFS.mpIdx = MPS.mpIdx AND MPS.mmType LIKE '%YPS%' AND MPS.mpType LIKE '%PS%'
                        LEFT JOIN pensionFreeStayImage AS PFSI ON PFS.pfsIdx = PFSI.pfsIdx AND PFSI.pfsRepr = '1'
                        LEFT JOIN pensionFreeStayLists AS PFSL ON PFSL.pfsIdx = PFS.pfsIdx AND PFSL.pfslEvent = 'Y'
                        WHERE PFS.pfsOpen = '1'
                        AND PFS.pfsRevDate <= '".date('Y-m-d H:i:s')."'
                        GROUP BY PFS.pfsIdx
                        ORDER BY PFS.pfsEnd DESC, PFS.pfsIdx DESC
                        LIMIT 20";
                        
        $result = $this->SV102->query($schQuery)->result_array();
        
        return $result;
    }
    
    function getFreeStayInfo($pfsIdx){
        $this->SV102->select('PFS.*, MPS.mpsName, COUNT(PFSL.pfslIdx) AS totalCount');
        $this->SV102->where('PFS.pfsIdx', $pfsIdx);
        $this->SV102->join('mergePlaceSite AS MPS',"PFS.mpIdx = MPS.mpIdx AND MPS.mmType LIKE '%YPS%' AND MPS.mpType LIKE '%PS%'",'LEFT');
        $this->SV102->join('pensionFreeStayLists AS PFSL','PFSL.pfsIdx = PFS.pfsIdx','LEFT');
        $this->SV102->group_by('PFS.pfsIdx');
        $result = $this->SV102->get('pensionFreeStay AS PFS')->row_array();
        
        return $result;
    }
    
    function getFreeStayImage($pfsIdx){
        $this->SV102->where('pfsIdx', $pfsIdx);
        $this->SV102->order_by('pfsSort','ASC');
        $result = $this->SV102->get('pensionFreeStayImage')->result_array();
        
        return $result;
    }
    
    function getRoomInfo($pprIdx){
        $pprIdxArray = explode("|", $pprIdx);
        
        $this->SV102->where_in('pprIdx', $pprIdxArray);        
        $lists = $this->SV102->get('placePensionRoom')->result_array();
        
        $result = array();
        if(count($lists) > 0){
            foreach($lists as $lists){
                $result[$lists['pprIdx']] = $lists['pprName'];
            }
        }

        return $result;
    }
    
    function insUseData($mbIdx, $mbEmail, $mbMobile, $pfsIdx, $device){        
        $this->db->where('mbIdx', $mbIdx);
        $info = $this->db->get('member')->row_array();
        
        $this->db->where('pfslSetDate', date('Y-m-d'));
        if($mbEmail){
            $this->db->where('pfslName', str_replace("YP.","",$mbEmail));
        }else{
            $this->db->where('pfslName', str_replace("YP.","",$info['mbID']));
        }
        $this->db->where('pfsIdx', $pfsIdx);
        $check = $this->db->count_all_results('pensionFreeStayLists');
        
        if($check > 100){
            return $check;
        }else{
            $this->db->set('pfslFlag', $device);
            if($mbEmail){
                $this->db->set('pfslName', str_replace("YP.","",$mbEmail));
            }else{
                $this->db->set('pfslName', str_replace("YP.","",$info['mbID']));
            }
            if($mbMobile){
                $this->db->set('pfslMobile', trim(str_replace("-","",$mbMobile)));
            }else{
                $this->db->set('pfslMobile', trim(str_replace("-","",$info['mbMobile'])));
            }
            $this->db->set('pfslIP', $_SERVER['REMOTE_ADDR']);
            $this->db->set('pfslSetDate', date('Y-m-d'));
            $this->db->set('pfslRegDate', date('Y-m-d H:i:s'));
            $this->db->set('pfslCount','1');
            $this->db->set('pfsIdx', $pfsIdx);
            $this->db->set('pfslEvent','N');
            $this->db->insert('pensionFreeStayLists');
            
            return "";
        }
        
        return "";
    }
}
        