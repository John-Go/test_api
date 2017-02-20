<?php
class Business_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}
	
	function insBusiness($pensionName, $location, $mobile, $homepage, $calendar){
		$this->db->set('baPensionName', $pensionName);
		$this->db->set('baLocation', $location);
		$this->db->set('baMobile', $mobile);
		$this->db->set('baSignFlag', 'A');
		$this->db->set('baHomepage', $homepage);
		$this->db->set('baCalendar', $calendar);
		$this->db->set('baRegDate', date('Y-m-d H:i:s'));
		$this->db->insert('businessApplication');
	}
}
?>