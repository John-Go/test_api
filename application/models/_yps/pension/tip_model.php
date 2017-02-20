<?php
class Tip_model extends CI_Model {
    function __construct() {
        parent::__construct();
        $CI =& get_instance();
        $CI->SV102 =& $this->load->database('YP', TRUE);
    }
	
	function getPensionTipLists($mpIdx, $mbIdx = '', $limit, $offset){
		$this->SV102->where('PT.ptFlag','0');
		$this->SV102->where('PT.mpIdx', $mpIdx);
		$result['count'] = $this->SV102->count_all_results('pensionTip AS PT');
		
		$this->SV102->select("PT.*, MB.mbID, COUNT(PTR.ptrIdx) AS recom, IFNULL(PTC.pcIdx,'') AS pcIdx, IFNULL(PTR2.ptrIdx,'') AS ptrIdx", FALSE);
		$this->SV102->where('PT.mpIdx', $mpIdx);
		$this->SV102->where('PT.ptFlag','0');
		$this->SV102->order_by('PT.ptRegDate','DESC');
		$this->SV102->group_by('PT.ptIdx');
		$this->SV102->join('pensionDB.member AS MB','MB.mbIdx = PT.mbIdx','LEFT');
		$this->SV102->join('pensionDB.pensionTipRecommend AS PTR','PTR.ptIdx = PT.ptIdx','LEFT');
		$this->SV102->join('pensionDB.pensionTipRecommend AS PTR2',"PTR2.ptIdx = PT.ptIdx AND PTR2.mbIdx = '$mbIdx'",'LEFT');
		$this->SV102->join('pensionDB.pensionTipComplaint AS PTC',"PTC.ptIdx = PT.ptIdx AND PTC.mbIdx = '$mbIdx'",'LEFT');
		$result['lists'] = $this->SV102->get('pensionDB.pensionTip AS PT', $limit, $offset)->result_array();
		
		return $result;
	}
	
	function setTipRecommend($ptIdx, $mbIdx){
		$result = array();
		
		$this->db->where('ptIdx', $ptIdx);
		$info = $this->db->get('pensionDB.pensionTip')->row_array();
		
		$this->db->where('ptIdx', $ptIdx);
        $this->db->where('mbIdx', $mbIdx);
        $recommendCount = $this->db->count_all_results('pensionDB.pensionTipRecommend');
		
		
		if($recommendCount == 0){
			$this->db->set('ptIdx', $ptIdx);
			$this->db->set('mbIdx', $mbIdx);
			$this->db->set('ptRegDate', date('Y-m-d H:i:s'));
			$this->db->insert('pensionDB.pensionTipRecommend');
			
			$this->db->set('ptRecommend', ($info['ptRecommend']+1));
			$this->db->where('ptIdx', $ptIdx);
			$this->db->update('pensionDB.pensionTip');
			
			$result['type'] = "P";
			$result['message'] = "추천되었습니다!";
			$result['count'] = number_format(($info['ptRecommend']+1));
		}else{
			$this->db->where('ptIdx', $ptIdx);
			$this->db->where('mbIdx', $mbIdx);
			$this->db->delete('pensionDB.pensionTipRecommend');
			
			$this->db->set('ptRecommend', ($info['ptRecommend']-1));
			$this->db->where('ptIdx', $ptIdx);
			$this->db->update('pensionDB.pensionTip');
			
			$result['type'] = "M";
			$result['message'] = "추천이 취소되었습니다.";
			$result['count'] = number_format(($info['ptRecommend']-1));
		}
		
		return $result;
	}

	function setTipComplaint($mpIdx, $ptIdx, $mbIdx){
		$this->db->where('mpIdx', $mpIdx);
		$this->db->where('ptIdx', $ptIdx);
		$this->db->where('mbIdx', $mbIdx);
		$info = $this->db->get('pensionDB.pensionTipComplaint')->row_array();
		
		if(isset($info['pcIdx'])){
			$this->db->where('pcIdx', $info['pcIdx']);
			$this->db->delete('pensionDB.pensionTipComplaint');
			
			return "D";
		}else{
			$this->db->set('mpIdx', $mpIdx);
			$this->db->set('ptIdx', $ptIdx);
			$this->db->set('mbIdx', $mbIdx);
			$this->db->insert('pensionDB.pensionTipComplaint');
			
			return "I";
		}
	}

	function getTipInfo($ptIdx, $mbIdx){
		$this->SV102->select('PT.*, MPS.mpsName, MB.mbID');
		$this->SV102->where('PT.ptIdx', $ptIdx);
		$this->SV102->where('PT.mbIdx', $mbIdx);
		$this->SV102->join('pensionDB.member AS MB','MB.mbIdx = PT.mbIdx','LEFT');
		$this->SV102->join('pensionDB.mergePlaceSite AS MPS',"MPS.mpIdx = PT.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
		$result = $this->SV102->get('pensionDB.pensionTip AS PT')->row_array();
		
		return $result;
	}
	
	function getUserTipLists($mbIdx, $limit, $offset){
		$this->SV102->where('PT.mbIdx', $mbIdx);
		$this->SV102->where('PT.ptFlag','0');
		$result['count'] = $this->SV102->count_all_results('pensionDB.pensionTip AS PT');
		
		$this->SV102->select("PT.*, MB.mbID, COUNT(PTR.ptrIdx) AS recom, IFNULL(PTC.pcIdx,'') AS pcIdx, IFNULL(PTR2.ptrIdx,'') AS ptrIdx, MPS.*", FALSE);
		$this->SV102->where('PT.mbIdx', $mbIdx);
		$this->SV102->where('PT.ptFlag','0');
		$this->SV102->order_by('PT.ptRegDate','DESC');
		$this->SV102->group_by('PT.ptIdx');
		$this->SV102->join('pensionDB.member AS MB','MB.mbIdx = PT.mbIdx','LEFT');
		$this->SV102->join('pensionDB.pensionTipRecommend AS PTR','PTR.ptIdx = PT.ptIdx','LEFT');
		$this->SV102->join('pensionDB.pensionTipRecommend AS PTR2',"PTR2.ptIdx = PT.ptIdx AND PTR2.mbIdx = '$mbIdx'",'LEFT');
		$this->SV102->join('pensionDB.pensionTipComplaint AS PTC',"PTC.ptIdx = PT.ptIdx AND PTC.mbIdx = '$mbIdx'",'LEFT');
		$this->SV102->join('pensionDB.mergePlaceSite AS MPS',"MPS.mpIdx = PT.mpIdx AND MPS.mmType = 'YPS' AND MPS.mpType = 'PS'",'LEFT');
		$result['lists'] = $this->SV102->get('pensionDB.pensionTip AS PT', $limit, $offset)->result_array();
		
		return $result;
	}
	
	function insTipInfo($mpIdx, $mbIdx, $content){
		$this->SV102->where('mpIdx', $mpIdx);
		$this->SV102->where('mmType','YPS');
		$this->SV102->where('mpType','PS');
		$pensionInfo = $this->SV102->get('mergePlaceSite')->row_array();
		
		$this->SV102->where('mbIdx', $mbIdx);
		$userInfo = $this->SV102->get('member')->row_array();
		
		$schQuery = "	SELECT R.*, MAX(PRI.rRevDate) + INTERVAL 1 MONTH AS maxDate, SUM(PRI.rPrice) AS rPrice
						FROM pensionDB.reservation AS R
						LEFT JOIN pensionDB.pensionRevInfo AS PRI ON R.rIdx = PRI.rIdx AND PRI.rState = 'PS02'
						WHERE R.rPayFlag = 'Y'
						AND R.rRoot = 'RO01'
						AND R.mpIdx = '".$mpIdx."'
						AND R.mbIdx = '".$mbIdx."'
						AND R.rPointSaveCheck = '0'
						AND PRI.rRevDate <= '".date('Y-m-d')."'
						AND PRI.rRevDate + INTERVAL 1 MONTH >= '".date('Y-m-d')."'
						AND PRI.rState = 'PS02'
						GROUP BY R.rIdx
						ORDER BY R.rRegDate DESC";
		$revInfo = $this->SV102->query($schQuery)->row_array();
		
		if(isset($revInfo['rIdx'])){
			$this->db->set('mbIdx', $mbIdx);
			$this->db->set('mpID', $userInfo['mbID']);
			$this->db->set('mprResvCode', $revInfo['rCode']);
			$this->db->set('mprPointCode', 'MP001');
			$this->db->set('mprPlusMinus', 'P');
			$this->db->set('mprPoint', (int)($revInfo['rPrice']*0.02));
			$this->db->set('mprPointDate', date('Y-m-d H:i:s'));
			$this->db->set('mplExpirationDate', date("Y-m-d",strtotime("+2 YEAR")));
			$this->db->insert('pensionDB.memberPointRaw');
			
			$this->db->where('rIdx', $revInfo['rIdx']);
			$this->db->set('rPointSaveCheck','1');
			$this->db->update('pensionDB.reservation');
			
			$this->db->set('rIdx', $revInfo['rIdx']);
			$this->db->set('ptPointSave', '1');
		}
		
		$this->db->set('mpIdx', $mpIdx);
		$this->db->set('mbIdx', $mbIdx);
		$this->db->set('ptSector','S01');
		$this->db->set('ptName', $userInfo['mbNick']);
		$this->db->set('ptPensionName', $pensionInfo['mpsName']);
		$this->db->set('ptTravelName','');
		$this->db->set('ptContent', $content);
		$this->db->set('ptRegDate', date('Y-m-d H:i:s'));
		$this->db->set('ptRecommend','0');
		$this->db->insert('pensionDB.pensionTip');
	}

	function uptTipInfo($ptIdx, $mbIdx, $content){
		$this->db->where('ptIdx', $ptIdx);
		$this->db->where('mbIdx', $mbIdx);
		$this->db->set('ptContent', $content);
		$this->db->update('pensionDB.pensionTip');
	}
	
	function delTipInfo($ptIdx){
		$this->db->where('ptIdx', $ptIdx);
		$this->db->delete('pensionDB.pensionTip');
		
		$this->db->where('ptIdx', $ptIdx);
		$this->db->delete('pensionDB.pensionTipComplaint');
		
		$this->db->where('ptIdx', $ptIdx);
		$this->db->delete('pensionDB.pensionTipRecommend');
	}
}