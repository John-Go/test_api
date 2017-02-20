<?php
class Device_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	// mbIdx 로 회원 id
	function getMemberInfo($mbIdx)
	{
		$this->db->where('mbIdx', $mbIdx);
		return $this->db->get('member')->row_array();
	}

	// device 존재여부 확인
	function getDevice($deviceKey)
	{
		$this->db->where('deviceKey', $deviceKey);
		return $this->db->get('appDevice')->row_array();
	}

	// device 정보 등록
	function insDevice($arr)
	{
		foreach($arr as $k => $v)
		{
			$this->db->set($k, $v);
		}

		$this->db->insert('appDevice');
		return ( $this->db->affected_rows() > 0 ) ? $this->db->insert_id() : FALSE;
	}

	// device 정보 변경
	function uptDevice($dIdx, $arr)
	{
		foreach($arr as $k => $v)
		{
			$this->db->set($k, $v);
		}

		$this->db->where('dIdx', $dIdx);
		return $this->db->update('appDevice');
	}

	// app device logout 시
	function deviceLogout($mbIdx, $deviceKey)
	{
		$this->db->where('mbIdx', $mbIdx);
		$this->db->where('deviceKey', $deviceKey);
		$deviceArr	= $this->db->get('appDevice')->row_array();

		if(!$deviceArr)
		{
			return false;
		}

		$this->db->set('loginFlag', '0');
		$this->db->set('logoutDate', date('Y-m-d H:i:s'));
		$this->db->set('expireDate', date('Y-m-d H:i:s', strtotime('+1 year', time())));
		$this->db->where('mbIdx', $deviceArr['mbIdx']);
		$this->db->where('deviceKey', $deviceArr['deviceKey']);
		return $this->db->update('appDevice');
	}

}