<?php
class Cast_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    function getCastLists(){        
        $schQuery = "   SELECT PC.*, PCI.pciImage, COUNT(PCI.pciIdx) AS imgCount, PCI.pciWidth, PCI.pciHeight
                        FROM pensionCast AS PC
                        LEFT JOIN pensionCastImage AS PCI ON PC.pcIdx = PCI.pcIdx AND pciSort = '1'
                        WHERE '".date('Y-m-d')."' BETWEEN PC.pcStart AND PC.pcEnd
                        AND PC.pcViewFlag = 'Y'
                        GROUP BY PC.pcIdx
                        ORDER BY PC.pcSort ASC, RAND()";
        $result = $this->db->query($schQuery)->result_array();
        
        return $result;
    }
    
    function getCastImageLists($pcIdx, $limit){        
        $this->db->where('pcIdx', $pcIdx);
        $result['count'] = $this->db->count_all_results('pensionCastImage');
        
        $this->db->where('pcIdx', $pcIdx);
        $this->db->order_by('pciSort','ASC');
        $this->db->limit($limit);
        $result['lists'] = $this->db->get('pensionCastImage')->result_array();
        
        return $result;
    }
    
    function getCastInfo($pcIdx){
        $this->db->where('pcIdx', $pcIdx);
        $result = $this->db->get('pensionCast')->row_array();
        
        return $result;
    }
    
    function getCastMoreLists($limit, $offset){
        $schQuery = "   SELECT PC.*, PCI.pciImage, COUNT(PCI.pciIdx) AS imgCount, PCI.pciWidth, PCI.pciHeight
                        FROM pensionCast AS PC
                        LEFT JOIN pensionCastImage AS PCI ON PC.pcIdx = PCI.pcIdx AND pciSort = '1'
                        WHERE '".date('Y-m-d')."' BETWEEN PC.pcStart AND PC.pcEnd 
                        AND PC.pcViewFlag = 'Y'
                        GROUP BY PC.pcIdx
                        ORDER BY PC.pcRegDate DESC, RAND()
                        LIMIT ".$limit." offset ".$offset;
        $result = $this->db->query($schQuery)->result_array();
        
        return $result;
    }
    
    function getBestLists(){
        $setDate = date('Y-m-d');
        
        $this->db->where("'$setDate' BETWEEN pbStart AND pbEnd",'', false);
        $this->db->where('PB.pbOpen','Y');
        $this->db->where('PB.pbMainImage IS NULL','', false);
        $this->db->group_by('PB.pbIdx');
        $this->db->order_by('PB.pbSort','DESC');
        $this->db->order_by('rand()');
        $this->db->join('pensionBestImage AS PBI',"PBI.pbIdx = PB.pbIdx AND PBI.pbiRepr = '1'",'LEFT');
        $result = $this->db->get('pensionBest AS PB')->result_array();
        
        return $result;        
    }
    
    function getBestInfo($pbIdx){
        $this->db->where('PB.pbIdx', $pbIdx);
        $this->db->order_by('PBI.pbiRepr','DESC');
        $this->db->order_by('PBI.pbiSort','ASC');
        $this->db->join('pensionBestImage AS PBI','PBI.pbIdx = PB.pbIdx','LEFT');
        $result = $this->db->get('pensionBest AS PB')->result_array();
        
        return $result;
    }
}