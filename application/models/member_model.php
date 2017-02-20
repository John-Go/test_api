<?php
class Member_model extends CI_Model {

	function __construct() {
		parent::__construct();
	}
	

	/**
	 * Set new password key for user.
	 * This key can be used for authentication when resetting user's password.
	 *
	 * @param	int
	 * @param	string
	 * @return	bool
	 */
	public function set_password_key( $mbIdx )
	{
		$new_pass_key = md5(rand().microtime());
		$this->db->set('new_password_key', $new_pass_key);
		$this->db->set('new_password_requested', date('Y-m-d H:i:s'));
		$this->db->where('mbIdx', $mbIdx);

		$this->db->update('pensionDB.member AS m');
		
		return ( $this->db->affected_rows() > 0 ) ? $new_pass_key : NULL;
	}

	/**
	 * Check if given password key is valid and user is authenticated.
	 *
	 * @param	int
	 * @param	string
	 * @param	int
	 * @return	void
	 */
	function can_reset_password($mbIdx, $new_pass_key, $expire_period = 900)
	{
		$this->db->select('1', FALSE);
		$this->db->where('mbIdx', $mbIdx);
		$this->db->where('new_password_key', $new_pass_key);
		$this->db->where('UNIX_TIMESTAMP(new_password_requested) >', time() - $expire_period);

		$query = $this->db->get('pensionDB.member AS m');
		return $query->num_rows() == 1;
	}

	/**
	 * Change user password if password key is valid and user is authenticated.
	 *
	 * @param	int
	 * @param	string
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	function reset_password($mbIdx, $new_pass, $new_pass_key, $expire_period = 900)
	{
		$this->db->set('mbPassword', md5($new_pass));
		$this->db->set('new_password_key', NULL);
		$this->db->set('new_password_requested', NULL);
		$this->db->where('mbIdx', $mbIdx);
		$this->db->where('new_password_key', $new_pass_key);
		$this->db->where('UNIX_TIMESTAMP(new_password_requested) >=', time() - $expire_period);

		$this->db->update('pensionDB.member AS m');
		return $this->db->affected_rows() > 0;
	}
    
    function getMemberInfo($mbIdx){
        $this->db->where('mbIdx', $mbIdx);
        $result = $this->db->get('pensionDB.member')->row_array();
        
        return $result;
    }
}
	
?>