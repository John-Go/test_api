<?php
class Account_limit_check_model extends CI_Model {
    function __construct() {
        parent::__construct();
        $CI =& get_instance();
        $CI->dbYPS =& $this->load->database('yps', TRUE);
    }
    
    function getList(){
        $sch_sql = "SELECT
                            *
                    FROM
                            pensionDB.reservation A
                    LEFT JOIN pensionDB.reservationXpayInfo B ON A.rCode = B.LGD_OID
                    WHERE A.rPaymentMethod = 'PM03'
                    AND A.rPaymentState = 'PS01'
                    AND A.rPayFlag = 'Y'
                    AND A.rRoot = 'RO01'
                    GROUP BY B.LGD_OID";
        $result = $this->dbYPS->query($sch_sql)->result_array();
        return $result;
    }
    
    function getLimitTime($dateFor){
        $this->dbYPS->where('mpIdx','1');
        $this->dbYPS->where('pppnDay <=', $dateFor);
        $this->dbYPS->order_by('pppnDay','DESC');
        $result = $this->dbYPS->get('pensionDB.placePensionPenalty')->row_array();
        
        return $result;
    }
    
    function getSendSmsCheck($rIdx){
        $this->dbYPS->where('rIdx', $rIdx);
        $this->dbYPS->where('pscType','1');
        $result = $this->dbYPS->count_all_results('pensionDB.pensionSmsCheck');
        
        return $result;
    }
    
    function insSmsCheck($rIdx, $receiver, $sender, $sendDate){
        $this->dbYPS->set('rIdx', $rIdx);
        $this->dbYPS->set('sender', $sender);
        $this->dbYPS->set('receiver', $receiver);
        $this->dbYPS->set('pscSendFlag','N');
        $this->dbYPS->set('pscSendDate', $sendDate);
        $this->dbYPS->set('pscType','1');
        $this->dbYPS->insert('pensionDB.pensionSmsCheck');
    }
    
    function getSmsCheckLists(){
        $this->dbYPS->where('pscSendFlag','N');
        $this->dbYPS->where('pscType','1');
        $result = $this->dbYPS->get('pensionDB.pensionSmsCheck')->result_array();
        
        return $result;
    }
    
    function getRevInfo($rIdx){
        $this->dbYPS->where('rIdx', $rIdx);
        $this->dbYPS->where('rPayFlag','Y');
        $result = $this->dbYPS->get('pensionDB.reservation')->row_array();
        
        return $result;
    }
    
    function uptSmsCheckInfo($pscIdx){
        $this->dbYPS->set('pscSendFlag','Y');
        $this->dbYPS->where('pscIdx', $pscIdx);
        $this->dbYPS->where('pscSendFlag','N');
        $this->dbYPS->update('pensionDB.pensionSmsCheck');
    }
    
    function delSmsCheck($pscIdx){
        $this->dbYPS->where('pscIdx', $pscIdx);
        $this->dbYPS->delete('pensionDB.pensionSmsCheck');
    }
}
?>