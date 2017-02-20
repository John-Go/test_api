<?php
class Best_model extends CI_Model {
    function __construct() {
        parent::__construct();
        $CI =& get_instance();
        $CI->SV102 =& $this->load->database('YP', TRUE);
    }

    function getBestLists(){
        $setDate = date('Y-m-d');
        $this->SV102->select('PB.*, PPB.ppbOnline');
        $this->SV102->where("'$setDate' BETWEEN PB.pbStart AND PB.pbEnd",'',false);
        $this->SV102->where('PB.pbOpen','Y');
        $this->SV102->where('PB.pbMainImage IS NOT NULL','', false);
        $this->SV102->order_by('PB.pbSort','DESC');
        $this->SV102->order_by('rand()');
		$this->SV102->join('placePensionBasic AS PPB','PPB.mpIdx = PB.mpIdx','LEFT');
        $result = $this->SV102->get('pensionBest AS PB')->result_array();
       
        return $result;
    }
    
    function getBestInfo($pbIdx){
        $this->SV102->where('PB.pbIdx', $pbIdx);
        $this->SV102->where('PB.pbOpen','Y');
        $this->SV102->join('placePensionBasic AS PPB','PPB.mpIdx = PB.mpIdx','LEFT');
        $this->SV102->join('mergePlaceSite AS MPS',"MPS.mpIdx = PB.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
        $result = $this->SV102->get('pensionBest AS PB')->row_array();
        
        return $result;
    }
    
    function getThemeLists($mpsIdx){
        $schQuery = "   SELECT MT.mtName
                        FROM placeTheme AS PT
                        LEFT JOIN mergeTheme AS MT ON MT.mtCode = PT.mtCode AND MT.mtSite LIKE '%YPS%' AND MT.mtType = 'PS'
                        WHERE PT.mpsIdx = '".$mpsIdx."'
                        AND PT.mtCode LIKE '2%'
                        AND LENGTH(PT.mtCode) = '8'";
        $lists = $this->SV102->query($schQuery)->result_array();
        
        $result = "";
        if(count($lists) > 0){
            foreach($lists as $lists){
                $result .= "|".$lists['mtName'];
            }
            if($result != ""){
                $result = substr($result, 1);
            }
        }
        
        return $result;
    }

    function getRoomLists($mpIdx, $pbIdx){
        $schQuery = "   SELECT PBP.*, PPR.pprName, PPR.pprIdx
                        FROM pensionBestPhoto AS PBP
                        LEFT JOIN placePensionRoom AS PPR ON PPR.pprIdx = PBP.typeIdx AND PPR.mpIdx = '".$mpIdx."'
                        WHERE PBP.pbIdx = '".$pbIdx."'
                        AND PBP.pbpType = 'R'
                        AND PPR.pprIdx IS NOT NULL
                        ORDER BY pbpSort ASC";
        $result = $this->SV102->query($schQuery)->result_array();
        
        return $result;
    }
    
    function getFacLists($mpIdx, $pbIdx){
        $schQuery = "   SELECT PBP.*, PPE.ppeName, PPE.ppeIdx
                        FROM pensionBestPhoto AS PBP
                        LEFT JOIN placePensionEtc AS PPE ON PPE.ppeIdx = PBP.typeIdx AND PPE.mpIdx = '".$mpIdx."'
                        WHERE PBP.pbIdx = '".$pbIdx."'
                        AND PBP.pbpType = 'F'
                        AND PPE.ppeIdx IS NOT NULL
                        ORDER BY PPE.ppeNo ASC, pbpSort ASC";
        $result = $this->SV102->query($schQuery)->result_array();
        
        return $result;
    }
    
    function getLandLists($pbIdx){
        $this->SV102->where('pbIdx', $pbIdx);
        $this->SV102->where('pbpType','L');
        $result = $this->SV102->get('pensionBestPhoto')->result_array();
        
        return $result;
    }
    
    function getService($pbIdx){
        $this->SV102->where('pbIdx', $pbIdx);
        $this->SV102->order_by('pbsSort','ASC');
        $result = $this->SV102->get('pensionBestService')->result_array();
        
        return $result;
    }
    
    function getPensionLinkCheck($mpIdx, $mbIdx){
        $this->SV102->where('mpIdx', $mpIdx);
        $this->SV102->where('mbIdx', $mbIdx);
        $result = $this->SV102->count_all_results('pensionBasket');
        
        return $result;
    }
}
        