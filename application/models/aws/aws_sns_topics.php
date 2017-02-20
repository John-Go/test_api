<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package     AWS
 * @author      YapenLab Dev Team
 * @copyright   Copyright (c) 2016
 * @since       Version 1.0
 */

class Aws_sns_topics extends CI_Model {

    private $table = 'awsSnsTopics';

    public function __construct() {
        parent::__construct();
    }

    // ------------------------------------------------------------------

    /**
     * Get member.
     *
     * @param   array           $paramss
     * @param   string          $fields
     * @return  object
     */
    public function get_topics($params, $fields = NULL) {
        if(!empty($fields)) {
            $this->db->select($fields);
        }

        $this->db->order_by("$this->table.subscriptionsCount", 'ASC');

        return $this->db->get_where($this->table, $params);
    }

    // ------------------------------------------------------------------

    /**
     * Insert Topic name.
     *
     * @param   string             $topic_name
     * @param   string             $service_kind     'yapen', 'ceo', 'test'
     * @return  bool
     */
    public function insert_topic($topic_name = NULL, $service_kind = 'yapen') {
        $this->db->set('topicName', $topic_name);
        $this->db->set('service_kind', $service_kind);
        $this->db->insert($this->table);

        return ( $this->db->affected_rows() > 0 ) ? $this->db->insert_id() : FALSE;
    }

    // ------------------------------------------------------------------

    /**
     * Get device token.
     *
     * @param   array          $where
     * @return  object
     */
    public function get_device_info($where = NULL) {
        if( isset($where) && is_array($where) ) {
            $this->db->where($where);
        }

        return $this->db->get('appDevice as AD');
    }

    // ------------------------------------------------------------------

    /**
     * Get device token.
     *
     * @param   array          $where
     * @return  object
     */
    public function get_test_member($dIdx = NULL) {
        $this->db->select('APTM.*, MB.mbEmail, MB.mbMobile, AD.mbID');
        $this->db->join('appDevice AS AD', 'AD.dIdx = APTM.dIdx', 'INNER');
        $this->db->join('member AS MB', 'MB.mbIdx = AD.mbIdx', 'INNER');
        if( isset($dIdx) ) {
            $this->db->where('APTM.dIdx = ', $dIdx);
        }

        return $this->db->get('awsPushTestMembers as APTM');
    }

    // ------------------------------------------------------------------

    /**
     * Update appDevice table.
     *
     * @param   array       $params
     * @param   array       $where
     * @return  bool
     */
    public function app_device_update($params, $where = NULL) {
        if( isset($where) && is_array($where) ) {
            $this->db->where($where);
        }

        return $this->db->update( 'appDevice', $params );
    }

    // ------------------------------------------------------------------

    /**
     * Update awsSnsTopics table.
     *
     * @param   int             $astIdx
     * @param   string          $arithmetic     'up' or 'down'
     * @return  bool
     */
    public function topics_count_up_down($ast_idx = NULL, $arithmetic = NULL) {
        if( strtolower($arithmetic) == 'up') {
            $subscriptionsCount = 'subscriptionsCount+1';
        } elseif(strtolower($arithmetic) == 'down') {
            $subscriptionsCount = 'subscriptionsCount-1';
        } else {
            log_message('error','[AWS SNS ERROR] awsSnsTopics : table count up or down fail.');
            return FALSE;
        }

        $query = "UPDATE $this->table SET subscriptionsCount = $subscriptionsCount WHERE astIdx = $ast_idx";
        return $this->db->query($query);
    }

    // ------------------------------------------------------------------

    /**
     * Insert awsPushTestMembers table.
     *
     * @param   int          $dIdx
     * @param   string       $app_endpoint
     * @param   string       $subscription_arn
     * @param   int          $astIdx
     * @return  bool
     */
    public function add_test_member($dIdx = NULL, $app_endpoint = NULL, $subscription_arn = NULL, $astIdx = NULL) {
        $sql = "INSERT INTO awsPushTestMembers (dIdx, app_endpoint, subscription_arn, astIdx)
                VALUES ('".$dIdx."', '".$app_endpoint."', '".$subscription_arn."', '".$astIdx."')
                ON DUPLICATE KEY UPDATE app_endpoint = '".$app_endpoint."', subscription_arn = '".$subscription_arn."', astIdx = '".$astIdx."', dIdx = '".$dIdx."'";

        return $this->db->query($sql);
    }

    // ------------------------------------------------------------------

    /**
     * Update awsSnsTopics table.
     *
     * @param   int             $astIdx
     * @param   string          $arithmetic     'up' or 'down'
     * @return  bool
     */
    public function del_test_member($dIdx = NULL) {
        return $this->db->delete('awsPushTestMembers', array('dIdx' => $dIdx));
    }
}