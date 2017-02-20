<?php
class Magazine_model extends CI_Model {
    function __construct() {
        parent::__construct();
        $CI =& get_instance();
        $CI->SV102 =& $this->load->database('YP', TRUE);
    }

    function getMagLists($locCode, $tag, $idxStrings){
		$this->SV102->where('PM.pmOpen', '1');
        if($tag){
            $this->SV102->like('PM.pmTag', $tag);
        }
        if($locCode){
            $this->SV102->join('pensionMagLocation AS PML',"PM.pmIdx = PML.pmIdx AND PML.mtCode = '".$locCode."'",'LEFT');
            $this->SV102->where('PML.pmIdx IS NOT NULL');
        }
        $result['count'] = $this->SV102->count_all_results('pensionMag AS PM');
        
		$this->SV102->where('PM.pmOpen', '1');
        if($tag){
            $this->SV102->like('PM.pmTag', $tag);
        }
        if($locCode){
            $this->SV102->join('pensionMagLocation AS PML',"PM.pmIdx = PML.pmIdx AND PML.mtCode = '".$locCode."'",'LEFT');
            $this->SV102->where('PML.pmIdx IS NOT NULL');
        }
        if(count($idxStrings) > 0){
            $this->SV102->where_not_in('PM.pmIdx', $idxStrings);
        }
        $this->SV102->join('pensionMagImage AS PMI',"PMI.pmIdx = PM.pmIdx AND PMI.pmiRepr = '1'",'LEFT');
        $this->SV102->order_by('PM.pmRegDate','DESC');
        $this->SV102->group_by('PM.pmIdx');
        
        $result['lists'] = $this->SV102->get('pensionMag AS PM', 20)->result_array();
        
        return $result;
    }

    function getMagLocation(){
		$schQuery = "
			SELECT 
				COUNT(PML.pmIdx) AS cnt, PML.mtCode,  PML.mtName
			FROM 
				pensionMagLocation AS PML 
				LEFT JOIN pensionMag AS PM ON PM.pmIdx = PML.pmIdx  
			WHERE 
				PM.pmOpen = '1'
			GROUP BY mtCode
		";
        $result = $this->SV102->query($schQuery)->result_array();
        
        return $result;
    }
    
    function getMagInfo($pmIdx){
        $this->SV102->where('pmIdx', $pmIdx);
        $result = $this->SV102->get('pensionMag')->row_array();

        return $result;
    }
    
    function getMagLocationInfo($pmIdx){
        $this->SV102->where('pmIdx', $pmIdx);
        $lists = $this->SV102->get('pensionMagLocation')->result_array();
        
        $locText = "";
        if(count($lists) > 0){
            foreach($lists as $lists){
                $locText .= "·".$lists['mtName'];
            }
            $locText = mb_substr($locText,1);
        }else{
            $locText = "기타";
        }
        
        return $locText;        
    }
    
    function getMagImageLists($pmIdx){
        $this->SV102->where('pmIdx', $pmIdx);
        $this->SV102->order_by('pmiRepr','DESC');
        $this->SV102->order_by('pmiSort','ASC');
        $result = $this->SV102->get('pensionMagImage')->result_array();
        
        return $result;
    }
    
    function gatAdLists(){
        $schQuery = "   SELECT PB.mpIdx, PB.pensionName, PPB.ppbImage
                        FROM pensionBest AS PB
                        LEFT JOIN placePensionBasic AS PPB ON PPB.ppbIdx = PB.mpIdx
                        WHERE '".date('Y-m-d')."' BETWEEN PB.pbStart AND PB.pbEnd
                        AND PB.pbOpen = 'Y'
                        AND PPB.ppbImage IS NOT NULL
                        GROUP BY PB.pbIdx
                        UNION ALL
                        SELECT PS.mpIdx,PS.mpsName AS pensionName, PPB.ppbImage
                        FROM appRandomBanner AS MTB
                        LEFT JOIN mergePlaceSite PS ON MTB.mpIdx = PS.mpIdx AND PS.mmType = 'YPS' AND PS.mpType = 'PS'
                        LEFT JOIN placePensionBasic AS PPB ON PPB.mpIdx = PS.mpIdx
                        WHERE MTB.arbOpen = '1'
                        AND PPB.ppbImage IS NOT NULL
                        AND '".date('Y-m-d')."' BETWEEN MTB.arbStartDate AND MTB.arbEndDate
                        ORDER BY RAND()
                        LIMIT 6";
        $result = $this->SV102->query($schQuery)->result_array();
        
        return $result;
    }
}