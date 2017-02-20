<?php
class Sns_module extends CI_Controller {

    /**
     * Default const
     */
    const _TOPICS_LIMIT = 95000; // 하나의 토픽에 들어가는 최대 Subscriptions. (변경 금지.)
    const _DEFAULT_TOPIC_NAME = '-all-members-topic-'; //  ex.) yapen-all-members-topic-10

    function __construct() {
        parent::__construct();

        $this->load->library('aws/Sns');
        $this->load->database('yps');
        $this->load->model('aws/aws_sns_topics');
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS에 Device 추가 후 Topic까지 subscribe 한다.
     *
     * @param   int             $dIdx               appDevice 테이블 dIdx
     * @param   string          $device_token
     * @param   string          $device_type        'A' or 'I'
     * @param   string          $service_kind       'yapen', 'ceo', 'test'
     * @return  bool
    */
    public function add_device($dIdx = NULL, $device_token = NULL, $device_type = NULL, $service_kind = 'yapen') {
        $result = FALSE;

        $enable_topics = $this->topics_compare($service_kind);
        if($enable_topics['status']) {
            $topic_name = $enable_topics['result']['topicName'];
            $ast_idx = $enable_topics['result']['astIdx'];

            // AWS SNS에 Device 추가 후 Topic까지 subscribe 한다.
            $add_device_res = $this->sns->add_device($service_kind, $device_type, $device_token, $topic_name);

            if( $add_device_res['@metadata']['statusCode'] === 200 ) {
                $add_device_data['app_endpoint'] = $add_device_res['app_endpoint'];
                $add_device_data['subscription_arn'] = $add_device_res['SubscriptionArn'];
                $add_device_data['astIdx'] = $ast_idx;
                $add_device_data['pushFlag'] = '1000';

                $app_device_res = $this->aws_sns_topics->app_device_update($add_device_data, array('dIdx' => $dIdx));
                if($app_device_res) {
                    $result = $this->aws_sns_topics->topics_count_up_down($ast_idx, 'up');
                }
            }
        }

        return $result;
    }

    // ------------------------------------------------------------------

    /**
     * Push on & off
     * Push off를 할경우 Tester 디바이스 정보도 같이 없어 진다.
     *
     * @param   int            dIdx         appDevice 테이블 dIdx
     * @param   status         status       'on' or 'off'
     * @return  bool
    */
    public function push_switch($dIdx = NULL, $status = NULL) {
        $result = FALSE;
        $endpoint_exist = FALSE;
        $get_device_data = $this->aws_sns_topics->get_device_info( array('dIdx' => $dIdx) );

        if($get_device_data->num_rows() > 0) {
            $get_device_info = $get_device_data->row();

            $app_endpoint       = $get_device_info->app_endpoint;
            $subscription_arn   = $get_device_info->subscription_arn;
            $device_token       = $get_device_info->deviceKey;
            $device_type        = $get_device_info->mbType;
            $astIdx             = $get_device_info->astIdx;
            $service_kind       = $get_device_info->appName;

            // Device를 삭제하기 위해서는 app_endpoint 와 subscription_arn이 모두 존재 해야 한다.
            // Device를 추가 하기 위해서는 app_endpoint 와 subscription_arn이 모두 비어 있어야 한다.
            if(!empty($app_endpoint) && !empty($subscription_arn)) {
                $endpoint_exist = TRUE;
            }

            if( strtolower($status) == 'off') {
                if($endpoint_exist) {
                    $this->sns->del_device($app_endpoint, $subscription_arn);
                    $this->aws_sns_topics->topics_count_up_down($astIdx, 'down');
                }

                // Tester user라면 같이 삭제한다.
                $this->del_tester($dIdx);

                $device_update_param['app_endpoint'] = NULL;
                $device_update_param['subscription_arn'] = NULL;
                $device_update_param['astIdx'] = NULL;
                $device_update_param['pushFlag'] = '0000';

                $result = $this->aws_sns_topics->app_device_update($device_update_param, array('dIdx' => $dIdx));
            } elseif( strtolower($status) == 'on' && empty($subscription_arn) ) {
                $result = $this->add_device($dIdx, $device_token, $device_type, $service_kind);
            } else {
                log_message('error','[AWS SNS ERROR] Push Switch Unresponsiveness. =>'.$dIdx);
            }
        } else {
            log_message('error','[AWS SNS ERROR] Push switch Not found User Device. dIdx => '.$dIdx);
        }

        return $result;
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS Test Topic에 Tester device 정보를 등록한다.
     * Push off가 되어 있다며, Tester로 등록 할수가 없다. Push on으로 변경하고 등록해야 한다.
     *
     * @param   int         dIdx       appDevice 테이블 dIdx
     * @return  bool
    */
   public function add_tester($dIdx = NULL) {
        $result = FALSE;
        $get_device_data = $this->aws_sns_topics->get_device_info( array('dIdx' => $dIdx) );
        $device_data_rows = $get_device_data->num_rows();

        // AWS SNS Topics에 test 토픽이 존재하는지 검사한다.
        $confirm_topic = $this->topics_compare('test');
        if( $device_data_rows > 0 && $confirm_topic['status'] == TRUE ) {
            $get_device_info = $get_device_data->row();
            $app_device_idx = $get_device_info->dIdx;
            $app_endpoint = $get_device_info->app_endpoint;

            if($app_endpoint == NULL) {
                log_message('error','[AWS SNS ERROR] App endpoint NULL. try push_switch device_token on');
                return $result;
            }

            $topic_name = $confirm_topic['result']['topicName'];
            $ast_idx = $confirm_topic['result']['astIdx'];

            $add_test_topic_res = $this->sns->add_topic($app_endpoint, $topic_name);
            if($add_test_topic_res['@metadata']['statusCode'] === 200) {
                $subscription_arn = $add_test_topic_res['SubscriptionArn'];

                // awsPushTestMembers 테이블에 Tester를 저장한다.
                // DUPLICATE KEY를 이용해 사용자 정보가 있다면 새로운 정보로 Update 하고 없으면 Insert 한다.
                if( $this->aws_sns_topics->add_test_member($dIdx, $app_endpoint, $subscription_arn, $ast_idx) ) {
                    $result = TRUE;

                    // 테이블에 Insert 되었을때만 test topic count수 를 증가 시킨다.
                    if( $this->db->affected_rows() > 0 ) {
                        $result = $this->aws_sns_topics->topics_count_up_down($ast_idx, 'up');
                    }
                    log_message('error','[AWS SNS SUCCESS] Add Test Member => '.$app_device_idx);
                }
            }
        } else {
            if($device_data_rows == 0) {
                log_message('error','[AWS SNS ERROR] Undefined User.');
            }
        }

        if($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        } else {
            return $result;
        }
    }

    // ------------------------------------------------------------------

    /**
     * Push tester del
     *
     * @param   int          dIdx                appDevice 테이블 dIdx
     * @return  bool
    */
    public function del_tester($dIdx = NULL) {
        $result = TRUE;
        $get_tester_topic_info = $this->aws_sns_topics->get_test_member( $dIdx );

        if($get_tester_topic_info->num_rows() > 0) {
            $topic_info = $get_tester_topic_info->row();
            $ast_idx = $topic_info->astIdx;
            $subscription_arn = $topic_info->subscription_arn;

            // AWS SNS Test topic에서만 Device 정보 삭제.
            $un_subscribe_res = $this->sns->un_subscribe($subscription_arn);
            if($un_subscribe_res['@metadata']['statusCode'] === 200 ) {
                if($this->aws_sns_topics->del_test_member($dIdx)) {
                    $result = $this->aws_sns_topics->topics_count_up_down($ast_idx, 'down');
                }
            } else {
                log_message('error','[AWS SNS ERROR] AWS SNS Test member unsubscribe Error.');
            }
        }

        if($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        } else {
            return $result;
        }
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS Topic과 DB에 있는 Topic이름을 비교한다.
     * Topic의 유무성은 DB기준이며, 없을 경우 status false를 리턴한다.
     * Topic이 여러개 존재한다면, 구독수가 가장 적은 순서대로 Topic리스트를 리턴한다.
     *
     * @param   string      $service_kind       'yapen', 'ceo', 'test'
     * @return  Array
    */
    public function topics_compare($service_kind = NULL) {
        $res['status'] = FALSE;
        $res['result'] = NULL;
        $db_topic_exist_res = TRUE;
        $db_topics = array();
        $aws_topics = array();

        if( ! $this->service_confirm($service_kind) ) return $res;

        $get_topics = $this->aws_sns_topics->get_topics(array('service_kind' => $service_kind));
        $get_topic_rows = $get_topics->num_rows();

        if($get_topic_rows > 0) {
            $db_get = $get_topics->result_array();
            foreach ($db_get as $db_t) {
                array_push($db_topics, $db_t['topicName']);
            }

            /**
             * AWS Topic Get list.
             */
            $aws_get_topic = $this->sns->get_topic_list();

            if($aws_get_topic['@metadata']['statusCode'] === 200) {
                foreach ($aws_get_topic['Topics'] as $aws_t) {
                    $aws_topic = $aws_t['TopicArn'];
                    $aws_topic = explode(':', $aws_topic);
                    array_push($aws_topics, $aws_topic[5]);
                }

                /**
                 * DB 기준으로 Topic을 비교한다.
                 */
                foreach ($db_topics as $value) {
                    if( !in_array($value, $aws_topics) ) {
                        $db_topic_exist_res = FALSE;
                        break;
                    }
                }
            }

            if($db_topic_exist_res) {
                $res['status'] = $db_topic_exist_res;

                /**
                 * Topic하나의 최대 subscribe를 95,000으로 제한함. (변경 금지.)
                 * 95,000보다 적은 count의 토픽에 device를 추가한다.
                 */
                if($db_get[0]['subscriptionsCount'] <= self::_TOPICS_LIMIT) {
                    $res['result']['topicName'] = $db_get[0]['topicName'];
                    $res['result']['astIdx'] = $db_get[0]['astIdx'];
                } else {
                    log_message('error','[AWS SNS ERROR] AWS SNS Topic Full. Error');
                    if($service_kind != 'test') {
                        $next_count = (int) $get_topic_rows + 1;
                        $create_topic_name = $service_kind.self::_DEFAULT_TOPIC_NAME.$next_count;
                        $res = $this->create_topic($create_topic_name, $service_kind);
                    }
                }
            } else {
                log_message('error','[AWS SNS ERROR] AWS SNS Topic Not found');
            }
        }

        return $res;
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS Topic을 생성한다.
     *
     * @param   string      $topic_name
     * @param   string      $service_kind       'yapen', 'ceo', 'test'
     * @return  Array
    */
    private function create_topic($topic_name = NULL, $service_kind = NULL) {
        $result['status'] = FALSE;
        $result['result'] = NULL;

        if( ! $this->service_confirm($service_kind) ) return $result;

        if( $this->sns->create_topic($topic_name) ) {
            $topic_id = $this->aws_sns_topics->insert_topic($topic_name, $service_kind);

            if($topic_id) {
                $result['status'] = TRUE;
                $result['result']['topicName'] = $topic_name;
                $result['result']['astIdx'] = $topic_id;
            }
        }

        return $result;
    }

    // ------------------------------------------------------------------

    /**
     * Service 이름을 확인한다.
     * 새로운 Service가 나오면 case를 추가해 줘야 한다.
     *
     * @param   string      $service_kind      'yapen', 'ceo', 'test'
     * @return  bool
    */

    private function service_confirm($service_kind = NULL) {
        $res = TRUE;
        switch ($service_kind) {
            case 'yapen': break;
            case 'ceo': break;
            case 'test': break;
            default:
                $res = FALSE;
                log_message('error','[AWS SNS ERROR] AWS SNS Service Not found');
                break;
        }

        return $res;
    }
}