<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class _dbClose {
    function index() {
        $CI =& get_instance();
        $CI->db->close();
    }
}
?>