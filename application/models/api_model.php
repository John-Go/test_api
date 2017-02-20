<?php
class Api_model extends CI_Model {
	function __construct() {
		parent::__construct();

	}

	//api key를 가져옴
	function getKey(){
		$this->db->where('akCode', SITE);
		$this->db->where('akDevice', DEVICE);


		return $this->db->get('commonDB.appKey')->row();
	}

	//api url 목록을 가져옴
	function getAPIs(){
		$this->dbHTML->select('ad_id,ad_intro,ad_url,ad_method');
		$this->dbHTML->where('ad_code', SITE);


		return $this->dbHTML->get('ki_api_doc');
	}
}