<?php
class gpension_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }
    
    function getRevInfo($rCode){
        $this->db->select('R.*, PPR.pprInMin');
        $this->db->where('R.rCode', $rCode);
        $this->db->join('pensionDB.placePensionRoom AS PPR','PPR.pprIdx = R.pprIdx','LEFT');
        $result = $this->db->get('pensionDB.reservation AS R')->row_array();
        
        return $result;
    }
    
    function getRevInfoLists($rIdx){
        $this->db->where('rIdx', $rIdx);
        $result = $this->db->get('pensionDB.pensionRevInfo')->result_array();
        
        return $result;
    }
    
    function tourCheck($mpIdx){
        $this->db->where('mpIdx', $mpIdx);
        $result = $this->db->get('pensionDB.placePensionBasic')->row_array();
        
        return $result;
    }
    
    function getConnectRoomFlag($pprIdx, $column){
        $this->db->where('pprIdx', $pprIdx);
        $this->db->select($column);
        $result = $this->db->get('pensionDB.placePensionConnect')->row_array();
        return $result;
    }
    
    function setRevEtcPoint($rIdx, $userName, $cancelFlag, $calFlag, $repCode){
        $this->db->set('rIdx', $rIdx);
        $this->db->set('userName', $userName);
        $this->db->set('repCode','gPension');
        $this->db->set('repName', 'G펜션');
        $this->db->set('repPoint','0');
        $this->db->set('repCancelFlag',$cancelFlag);
        $this->db->set('repCalFlag', $calFlag);
        $this->db->set('repAffIdx', $repCode);
        $this->db->set('repRegDate', date('Y-m-d H:i:s'));
        $this->db->insert('pensionDB.pensionRevEtcPoint');
    }
    
    function setRevError($rIdx, $type, $memo){
        $this->db->where('rIdx', $rIdx);
        $this->db->where('rlMemo', 'G펜션 로그 : '.$memo);
        $flag = $this->db->count_all_results('pensionDB.reservation_Log');
        
        if($flag == 0){
            $this->db->set('rIdx', $rIdx);
            $this->db->set('mbID','kimyw4');
            $this->db->set('rlRegDate', date('Y-m-d H:i:s'));
            $this->db->set('rlMemo', 'G펜션 로그 : '.$memo);
            $this->db->set('rlIP', $_SERVER['REMOTE_ADDR']);
            $this->db->insert('pensionDB.reservation_Log');
        }else if(substr($_SERVER['REMOTE_ADDR'],0,11) == "211.119.136"){
            $this->db->set('rIdx', $rIdx);
            $this->db->set('mbID','kimyw4');
            $this->db->set('rlRegDate', date('Y-m-d H:i:s'));
            $this->db->set('rlMemo', 'G펜션 로그 : '.$memo);
            $this->db->set('rlIP', $_SERVER['REMOTE_ADDR']);
            $this->db->insert('pensionDB.reservation_Log');
        }
    }
    
    function getEtcPoint($rIdx){
        $this->db->where('rIdx', $rIdx);
        $this->db->order_by('repIdx','DESC');
        $result = $this->db->get('pensionDB.reservationEtcPoint')->row_array();
        
        return $result;
    }
}