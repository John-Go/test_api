<?php
class Travel_model extends CI_Model {
	function __construct() {
		parent::__construct();
        $CI =& get_instance();
        $CI->dbInfo =& $this->load->database('info', TRUE);
	}

	// ****************************************************** 메인 *********************************************************
	public function mainEventBanner(){	//	메인 이벤트 배너
		$this->dbInfo->select('amebIdx,amebFilename,amebContent');
		$this->dbInfo->from('appMainTravelEventBanner');
		$this->dbInfo->where('amebOpen > ',0);
		$this->dbInfo->order_by('', 'random');
		$this->dbInfo->limit(1);

		return $this->dbInfo->get();
	}

	public function mainTopBanner(){	// 메인 베스트 배너
		$this->dbInfo->select('amtbIdx,mpIdx,amtbTitle,amtbFilename1 as amtbFilename');
		$this->dbInfo->where('amtbOpen > ',0);
		$this->dbInfo->order_by('amtbSort', 'asc')->order_by('rand()');

		return $this->dbInfo->get('appMainTravelTopBanner')->result_array();
	}

	public function mainLocBanner(){	// 메인 인기지역 배너
		$this->dbInfo->select('amlbIdx,amlbName,amlbCode,amlbContent,amlbColor,amlbColorF');
		$this->dbInfo->where('amlbOpen > ',0);
		//$this->dbInfo->order_by('amlbIdx', 'desc');
		$this->dbInfo->order_by('rand()');

		return $this->dbInfo->get('appMainTravelLocBanner')->result_array();
	}
	// ****************************************************** 메인 *********************************************************

	// ****************************************************** 인기지역 리스트 *********************************************************

	public function locBannerList($data){	// 메인 인기지역 리스트
		$this->dbInfo->start_cache();
		$this->dbInfo->where('MTB.amlbIdx', $data['idx']);
		$this->dbInfo->join('infoDB.ynjDateNewInfo PS', 'MTB.mpIdx = PS.dniIdx');
		$this->dbInfo->where('PS.dniOpen', 'Y');	// 게시
		$this->dbInfo->stop_cache();

		$result['count'] = $this->dbInfo->count_all_results('appMainTravelLocBannerJoin MTB');

		$this->dbInfo->order_by('PS.dniIdx', 'desc');
		$this->dbInfo->select('PS.*');
		$result['query'] = $this->dbInfo->get('appMainTravelLocBannerJoin MTB', $data['limit'], $data['offset'])->result_array();

		$this->dbInfo->flush_cache();
		return $result;
	}

	// ****************************************************** 인기지역 리스트 *********************************************************

	function mainLoc(){
		$this->dbInfo->select('ca_code,ca_name,ca_count');
		$this->dbInfo->from('infoDB.category');
		$this->dbInfo->where('ca_type','A');
		$this->dbInfo->where('ca_code >= ',1);
		$this->dbInfo->order_by("ca_code - 0", "ASC");
		return $this->dbInfo->get()->result_array();
	}

	public function travelLists($param) {
		$QueryString	= "
		SELECT
			CI.ci_visitCount, 
			CI.ci_type, 
			CI.ci_idx, 
			CA.ca_code as area, 
			CI.ca_code as theme,
			CC.ca_name as themeName,
			DNI.dniTitle,
			CONCAT(DNI.dniSi,' ',DNI.dniGugun) as addr,
			DNI.dniFileName,
			DNI.dniReadnum,
			DNII.*
		FROM infoDB.categoryInfo CA 
			INNER JOIN infoDB.categoryInfo CI ON CA.ci_type=CI.ci_type and CA.ci_idx=CI.ci_idx and CI.ci_openFG='Y' 
			LEFT JOIN infoDB.category CC ON CC.ca_type='T' and CI.ca_code=CC.ca_code
			LEFT JOIN infoDB.ynjDateNewInfo DNI ON CI.ci_idx=DNI.dniIdx 
			LEFT JOIN infoDB.ynjDateNewInfoImage DNII ON DNI.dniIdx=DNII.dniIdx 
		WHERE
			CA.ci_openFG='Y'
			and CI.ca_type='T'	
			and CA.ca_type='A' 
			and CA.ca_code>='".$param['locCode']."'
			and CA.ca_code<'".$param['locCode2']."'";
		if( $param['themeCode'] ) {
		$QueryString .= "
			and CI.ca_code>='".$param['themeCode']."'
			and CI.ca_code<'".$param['themeCode2']."'";
		}
		$QueryString .= "
			and CI.ci_type='D'
		group by CI.ci_type, CI.ci_idx
		order by CI.ci_visitCount DESC";

		$result['count']	= $this->dbInfo->query($QueryString);
		$QueryString .= " limit ".$param['offset'].", ".$param['perPage'];
		$result['obj']		= $this->dbInfo->query($QueryString);
		return $result;
	}

	public function travelMapLists($latitude, $longitude){ // 펜션지도 좌표

		$this->dbInfo->start_cache();
		$this->dbInfo->where('mmType LIKE "%YPS%"');	// 타입
		$this->dbInfo->where('mpType', 'DC');	// 타입
		$this->dbInfo->where('mpsOpen > ', '0');	// 게시
		$this->dbInfo->having('distance <=', 15); 
		$this->dbInfo->group_by('mpIdx');

		$this->dbInfo->stop_cache();

		$this->dbInfo->select("
			mpIdx,mpsName,mpsAddr1,mpsMapX,mpsMapY, 
			( 6371 * acos( cos( radians($latitude) ) * cos( radians(mpsMapY) ) * cos( radians(mpsMapX) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians(mpsMapY) ) ) ) AS distance "
		);
		$this->dbInfo->order_by("mpsIdx asc");
		
		$row =  $this->dbInfo->get('mergePlaceSite');

		$result['count'] = $row->num_rows();
		$result['query'] = $row->result_array();

		$this->dbInfo->flush_cache();
		return $result;

	}


	// ***************************************************** 여행지 상세정보 *******************************************************

	function travelGetInfo($idx){	// 

		$this->dbInfo->select('dni.dniIdx,dni.dniTitle,dni.dniReadnum,dni.dniFileName,dni.dniTravelFileName,dni.dniTel,dni.dniExpenseMin,dni.dniExpenseMax,dni.dniBusinessTime,dnii.dniiTip,dni.dniParking,dni.dniDayOff,dni.dniMDate,dni.dniSi,dni.dniGugun,dni.dniAdress,dni.dniHomepage,dni.dniHomepageLink,dni.dniReporter,dni.dniGoogleX,dni.dniGoogleY,dni.dniTravelTemaKey,(dni.dniAfterPopularityCount+dni.dniAppraisalPopularityCount) as dniaAppraisalCount,(dniAfterPopularitySum+dniAppraisalPopularitySum) as dniaAppraisalSum,dnii.*');
		$this->dbInfo->where('dni.dniIdx', $idx);
		$this->dbInfo->where('dni.dniOpen', 'Y');
		$this->dbInfo->join('infoDB.ynjDateNewInfoImage as dnii', 'dni.dniIdx = dnii.dniIdx', 'left');

		$this->dbInfo->from('infoDB.ynjDateNewInfo as dni');

		return $this->dbInfo->get();
	}

	function travelThemes( $idx ){
		$string = '';
		$this->dbInfo->select('ca.ca_name');
		$this->dbInfo->from('infoDB.categoryInfo ci');
		$this->dbInfo->join('infoDB.category ca', 'ci.ca_code=ca.ca_code AND ca.ca_type = "T" ', 'inner');
		$this->dbInfo->where('ci.ca_type', 'T');
		$this->dbInfo->where('ci.ci_openFG', 'Y');
		$this->dbInfo->where('ci.ci_idx', $idx);
		$this->dbInfo->where('ci.ci_type', 'D');
		$this->dbInfo->order_by('ci.ci_idx', $idx);
		$this->dbInfo->limit(1);

		$result = $this->dbInfo->get();
		foreach( $result->result() as $row ){
			if( $string ) $string .= ',';
			$string .= $row->ca_name;
		}

		return $string;
	}

	// ***************************************************** 여행지 상세정보 *******************************************************

	// ***************************************************** 가보고싶어요 갯수 *******************************************************

	function travelTipCount($idx){	// 

		$this->db->where('mpIdx', $idx);
		$num = $this->db->count_all_results('travelBasket');

		if($num)
			return $num;
		
		return 0;
	}

	// ***************************************************** 가보고싶어요 갯수 *******************************************************


	// ***************************************************** 여행지 사진 리스트 *******************************************************
	function travelImageLists($idx, $limit, $offset) {	// 

		$this->dbInfo->select("*");
		$this->dbInfo->where('dniIdx', $idx);
		$result = $this->dbInfo->get('infoDB.ynjDateNewInfoImage', $offset, $limit)->row_array();

		return $result;
	}
	// ***************************************************** 여행지 사진 리스트 *******************************************************


	// ***************************************************** 여행 신고하기 등록 *******************************************************
	public function reportInsert($data){ // 

		$this->db->insert('travelReport', array(
			'mpIdx' => $data["mpIdx"],
			'mbIdx' => $data["mbIdx"],
			'trName' => urldecode($data["trName"]),
			'trTravelName' => urldecode($data["trTravelName"]),
			'trTravelAddress' => urldecode($data["trTravelAddress"]),
			'trContent' => urldecode($data["trContent"]),
			'trRegDate' => date("Y-m-d H:i:s")
		));

		return $this->db->insert_id();
	}
	// ***************************************************** 여행 신고하기 등록 *******************************************************


	// ***************************************************** 가고싶어요 등록 , 삭제 *******************************************************
	public function travelBasket($sector, $mpIdx, $mbIdx){ // 
		if(!strcmp($sector,"INSERT")){ // 등록

			$this->db->select('mpIdx');
			$this->db->where('mpIdx ', $mpIdx);
			$this->db->where('mbIdx ', $mbIdx);
			$this->db->from('travelBasket');

			if($this->db->get()->row()){
				return "이미 가고싶어요한 지역입니다. MY>가고싶어요에서 삭제하실 수 있습니다.";
			}else{
				$this->db->insert('travelBasket', array(
					'mpIdx' => $mpIdx,
					'mbIdx' => $mbIdx,
					'tbDate' => date("Y-m-d")
				));

				return 1;
			}
		}else{	// 삭제
			$this->db->where('mpIdx ', $mpIdx);
			$this->db->where('mbIdx ', $mbIdx);
			$this->db->delete('travelBasket'); 

			return 1;
		}
	}
	// ***************************************************** 가고싶어요 등록 , 삭제 *******************************************************


	// ***************************************************** 여행 팁 리스트 *******************************************************
	function tipLists($idx, $limit, $offset) {

		$this->dbInfo->start_cache();
		$this->dbInfo->where('mpIdx', $idx);
		$this->dbInfo->stop_cache();

		$result['count'] = $this->dbInfo->count_all_results('travelTip');

		$this->dbInfo->select("*");
		$this->dbInfo->order_by("ttIdx desc");
		$result['query'] = $this->dbInfo->get('travelTip', $offset, $limit)->result_array();

		$this->dbInfo->flush_cache();
		return $result;
	}
	// ***************************************************** 여행 팁 리스트 *******************************************************



	// ***************************************************** 여행 팁 등록 *******************************************************
	public function tipInsert($data){
		$this->dbInfo->insert('travelTip', array(
			'mpIdx' => $data['mpIdx'],
			'mbIdx' => $data['mbIdx'],
			'ttSector' => $data['ttSector'],
			'ttName' => $data['ttName'],
			'ttTravelName' => $data['ttTravelName'],
			'ttContent' => $data['ttContent'],
			'ttRegDate' => date('Y-m-d H:i:s')
		));

		return $this->dbInfo->insert_id();
	}
	// ***************************************************** 여행 팁 등록 *******************************************************


	// ***************************************************** 여행 팁 추천 *******************************************************
	public function tipRecommend($data){
		$this->dbInfo->select('ttIdx')->where('ttIdx', $data['ttIdx'])->where('mbIdx', $data['mbIdx'])->from('travelTip');

		if($this->dbInfo->get()->row()){ // 내가쓴팁
			return 1;
		}else{
			$this->dbInfo->select('ttIdx');
			$this->dbInfo->where('ttIdx', $data['ttIdx']);
			$this->dbInfo->where('mbIdx', $data['mbIdx']);
			$this->dbInfo->from('travelTipRecommend');
			if($this->dbInfo->get()->row()){ // 이미추천하였음.
				return 2;
			}else{
				$this->dbInfo->insert('travelTipRecommend', array('ttIdx' => $data['ttIdx'],'mbIdx' => $data['mbIdx'],'ttRegDate' => date('Y-m-d H:i:s')));

				$this->dbInfo->set('ttRecommend', 'ttRecommend+1', FALSE);
				$this->dbInfo->where('ttIdx', $data['ttIdx']);
				$this->dbInfo->update('travelTip');
				
				return 3;
			}
		}
	}
	// ***************************************************** 여행 팁 추천 *******************************************************


	// ***************************************************** 여행 팁 정보 *******************************************************
	public function tipInfo($ttIdx,$mbIdx){
		$this->dbInfo->select('*');
		$this->dbInfo->where('ttIdx', $ttIdx);
		$this->dbInfo->where('mbIdx', $mbIdx);
		$this->dbInfo->from('travelTip');
		
		return $this->dbInfo->get()->row_array();
	}
	// ***************************************************** 여행 팁 정보 *******************************************************

	// ***************************************************** 여행 팁 수정 *******************************************************
	public function tipUpdate($data){
		$this->dbInfo->set('ttSector', $data['ttSector']);
		$this->dbInfo->set('ttName', $data['ttName']);
		$this->dbInfo->set('ttTravelName', $data['ttTravelName']);
		$this->dbInfo->set('ttContent', $data['ttContent']);

		$this->dbInfo->where('ttIdx', $data['ttIdx']);
		$this->dbInfo->where('mbIdx', $data['mbIdx']);
		$this->dbInfo->update('travelTip');
	}
	// ***************************************************** 여행 팁 수정 *******************************************************

	// ***************************************************** 여행 팁 삭제 *******************************************************
	public function tipDelete($data){
		$this->dbInfo->where('ttIdx', $data['ttIdx']);
		$this->dbInfo->where('mbIdx', $data['mbIdx']);
		$this->dbInfo->delete('travelTip'); 
	}
	// ***************************************************** 여행 팁 삭제 *******************************************************

	// ***************************************************** 여행 팁 신고 *******************************************************
	public function tipComplaintCheck($param) {
		extract( $param ); 
		$this->dbInfo->select('tcIdx');
		$this->dbInfo->where('mpIdx', $param['mpIdx']);
		$this->dbInfo->where('ttIdx', $param['ttIdx']);
		$this->dbInfo->where('mbIdx', $param['mbIdx']);
		return $this->dbInfo->get('travelTipComplaint')->num_rows();
	}

	public function tipComplaintIns($param) {
		extract( $param ); 
		$this->dbInfo->set('mpIdx', $param['mpIdx']);
		$this->dbInfo->set('ttIdx', $param['ttIdx']);
		$this->dbInfo->set('mbIdx', $param['mbIdx']);
		$this->dbInfo->set('regDate', date('Y-m-d H:i:s'));
		return $this->dbInfo->insert('travelTipComplaint');
	}


	public function tipComplaintUpdate($param) {
		extract( $param ); 
		$this->dbInfo->where('mpIdx', $param['mpIdx']);
		$this->dbInfo->where('ttIdx', $param['ttIdx']);
		$this->dbInfo->set('ttReport','ttReport+1',FALSE);
		return $this->dbInfo->update('travelTip');
	}
	// ***************************************************** 여행 팁 신고 *******************************************************

	// ***************************************************** 여행지도 좌표 *******************************************************
	public function travelMap($latitude, $longitude){
		$this->dbInfo->start_cache();
		$this->dbInfo->where('dniOpen', 'Y');	// 게시
		$this->dbInfo->having('distance <=', 5); 

		$this->dbInfo->stop_cache();

		$this->dbInfo->select("
			dniIdx,dniTitle,dniSi,dniGugun,dniAdress,dniGoogleX,dniGoogleY, 
			( 6371 * acos( cos( radians( $latitude) ) * cos( radians( dniGoogleY ) ) * cos( radians( dniGoogleX ) - radians( $longitude ) ) + sin( radians( $latitude) ) * sin( radians( dniGoogleY ) ) ) ) AS distance "
		);
		$this->dbInfo->order_by("dniIdx asc");
		
		$row =  $this->dbInfo->get('infoDB.ynjDateNewInfo');

		$result['count'] = $row->num_rows();
		$result['query'] = $row->result_array();

		$this->dbInfo->flush_cache();
		return $result;
	}
	// ***************************************************** 여행지도 좌표 *******************************************************

	// ***************************************************** 테마코드별 이름출력 *******************************************************
	public function getCateInfo($tmain,$gt,$lt) {
		$this->dbInfo->where('ca_type', 'T');
		$this->dbInfo->where("ca_code REGEXP '\\".$tmain."[0-9]{".$gt.",".$lt."}$'");
		$this->dbInfo->where('ca_open', '1');
		$this->dbInfo->select('ca_name, ca_code');
		$this->dbInfo->order_by('ca_sort DESC');
		return $this->dbInfo->get('infoDB.category')->result_array();
	}
	// ***************************************************** 테마코드별 이름출력 *******************************************************

	// ***************************************************** 해당 지역에 등록된 여행지 키값 *******************************************************
	public function getCateTravelKey($type,$area) {
		$this->dbInfo->where('ci_type', $type);
		$this->dbInfo->where('ca_type', 'A');
		$this->dbInfo->where('ci_openFG', 'Y');
		$this->dbInfo->where('format(ca_code,0)', $area);
		$this->dbInfo->select('DISTINCT(ci_idx)');
		return $this->dbInfo->get('infoDB.categoryInfo')->result_array();
	}
	// ***************************************************** 해당 지역에 등록된 여행지 키값 *******************************************************

	// ***************************************************** 등록된 여행지 키값으로 테마 키값 검색 *******************************************************
	public function getCateTravelThemeKdy($sublen,$tmain,$gt,$lt,$ci_idxs) {
		$this->dbInfo->where('C.ci_type', 'D');
		$this->dbInfo->where('C.ca_type', 'T');
		$this->dbInfo->where('C.ci_openFG', 'Y');
		$this->dbInfo->where("ca_code REGEXP '".$tmain."[0-9]{".$gt.",".$lt."}$'");
		$this->dbInfo->where_in('C.ci_idx', $ci_idxs);
		$this->dbInfo->where('D.dniOpen', 'Y');
		$this->dbInfo->join('infoDB.categoryInfo C', 'D.dniIdx = C.ci_idx', 'left');
		$this->dbInfo->select('TRUNCATE(C.ca_code, '.$sublen.') AS mcode, COUNT(DISTINCT D.dniIdx) AS count', FALSE);
		//$this->dbInfo->group_by('mcode');
		$this->dbInfo->group_by('C.ci_idx');
		return $this->dbInfo->get('infoDB.ynjDateNewInfo D')->result_array();
	}
	// ***************************************************** 등록된 여행지 키값으로 테마 키값 검색 *******************************************************


	//여행지 정보 지역종류 및 카운트
	function getTravelAreaCategoryListWithCount(){
		$this->dbInfo->select('CA.ca_code, CA.ca_name, count(CI.ca_code) AS count, CA.ca_sort', FALSE);
		$this->dbInfo->from('infoDB.category CA');
		$this->dbInfo->join('infoDB.categoryInfo CI', 'CA.ca_code = CI.ca_code AND CI.ci_type = "D" AND CI.ci_openFG = "Y" AND CI.ca_type ="A" ', 'LEFT');
		$this->dbInfo->where('CA.ca_type', 'A');
		$this->dbInfo->group_by('CA.ca_code');
		$this->dbInfo->order_by('CAST(CA.ca_code AS UNSIGNED)', 'ASC', FALSE);
		$this->dbInfo->order_by('CA.ca_sort', 'DESC');

		
		return $this->dbInfo->get();
	}
}
?>