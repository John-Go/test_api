<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * AWS SNS Push Server
 *
 * @package     YanoljaTravel AWS Push Server
 * @author      YapenLab Dev Team
 * @copyright   Copyright (c) 2016
 * @since       Version 1.0
 */

require_once APPPATH.'libraries/aws/Aws_conf.php';

class Sns extends Aws_conf {

    /**
     * Applications ARN.
     * AWS SNS에 Platform applications가 아래와 같이 있어야 한다.
     */
    const _YAPEN_APP_IOS_ARN = 'app/APNS/yapen-app-ios';
    const _YAPEN_APP_ANDROID_ARN = 'app/GCM/yapen-app-android';
    const _CEO_APP_IOS_ARN = 'app/APNS/ceo-app-ios';
    const _CEO_APP_ANDROID_ARN = 'app/GCM/ceo-app-android';
    const _TEST_APP_IOS_ARN = 'app/APNS/test-app-ios';
    const _TEST_APP_ANDROID_ARN = 'app/GCM/test-app-android';

    private $sns_region = NULL;
    private $client = NULL;
    private $aws_sns_arn = NULL;

    // ------------------------------------------------------------------

    /**
     * AWS PUSH 서버 Region 입력과 Client를 생성한다.
     *
     * @return  void
    */
    function __construct() {
        $this->sns_region = self::$AWS_REGION_SEOUL;
        $this->aws_sns_arn = 'arn:aws:sns:'.$this->sns_region.':108926746920:';
        $this->sns_client();
    }

    // ------------------------------------------------------------------

    /**
     * AWS PUSH 서버에 디바이스를 등록. (전체 멤버리스트에 등록할때 사용함.)
     * Application에 디바이스를 등록하고 Subscribe, Topic 까지 등록한다.
     *
     * @param   string      $app_type           서비스 타입.  ex) 'yapen' or 'ceo' or 'test',  default = 'test'
     * @param   string      $device_type        디바이스 타입.  ex) 'ios' or 'android',  default = 'android'
     * @param   string      $device_token       디바이스 토큰.
     * @param   string      $topic_name
     * @return  json
    */
    public function add_device($app_type = 'yapen', $device_type = NULL, $device_token = NULL, $topic_name = NULL) {
        $app_add_device = $this->application_add_device($app_type, $device_type, $device_token);

        if($app_add_device['@metadata']['statusCode'] === 200) {
            $endpoint_arn = $app_add_device['EndpointArn'];
            $add_topic_res = $this->add_topic($endpoint_arn, $topic_name);
        }

        return $add_topic_res;
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS Application에만 디바이스를 등록한다.
     *
     * @param   string      $app_type           서비스 타입.  ex) 'yapen' or 'ceo' or 'test',  default = 'test'
     * @param   string      $device_type        디바이스 타입.  ex) 'ios' or 'android',  default = 'android'
     * @param   string      $device_token       디바이스 토큰.
     * @return  json
    */
    public function application_add_device($app_type = 'yapen', $device_type = 'android', $device_token = NULL) {
        $device_type = strtolower($device_type);
        $app_arn = $this->division_app_arn($app_type, $device_type);
        $result = $this->client->createPlatformEndpoint(array(
            'PlatformApplicationArn' => $app_arn,
            'Token' => $device_token,
        ));

        if( $result['@metadata']['statusCode'] != 200 ) {
            log_message('error','[AWS SNS ERROR] Application Add device Error');
        }

        return $result;
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS Topics에 유져 디바이스를 등록한다.
     *
     * @param   string      $app_endpoint      Add application end point.
     * @param   string      $topic_name
     * @return  bool
    */
    public function add_topic($app_endpoint = NULL, $topic_name = NULL) {
        $topic_arn = $this->aws_sns_arn.$topic_name;
        $aws_result = $this->client->subscribe(array(
            'TopicArn' => $topic_arn,
            'Protocol' => 'application',
            'Endpoint' => $app_endpoint
        ));

        if( $aws_result['@metadata']['statusCode'] === 200 ) {
            $aws_result['app_endpoint'] = $app_endpoint;
        } else {
            log_message('error','[AWS SNS ERROR] AWS SNS Subscribe(add_topic) Error.');
        }

        return $aws_result;
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS Topic및 Application에서 유져 디바이스를 삭제한다. (유져가 notification 설정을 off 했을 경우.)
     * Push 서버에서 디바이스를 완전히 삭제함.
     *
     * @param   string      $app_endpoint           Application end point.
     * @param   string      $subscription_arn       Subscription ARN.
     * @return  bool
    */
    public function del_device($app_endpoint = NULL, $subscription_arn = NULL) {
        $result = FALSE;
        $unsubscribe_res = $this->un_subscribe($subscription_arn);
        if($unsubscribe_res['@metadata']['statusCode'] === 200) {
            $del_application_res = $this->del_application($app_endpoint);
            if($del_application_res['@metadata']['statusCode'] === 200) {
                $result = TRUE;
            } else {
                log_message('error','[AWS SNS ERROR] AWS SNS Delete Applications Error');
            }
        } else {
            log_message('error','[AWS SNS ERROR] AWS SNS Unsubscribe Error');
        }

        return $result;
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS Applications에 유져 디바이스를 삭제한다.
     *
     * @param   string      $app_endpoint        application end point.
     * @return  json
    */
    public function del_application($app_endpoint = NULL) {
        $result = $this->client->deleteEndpoint(array(
            'EndpointArn' => $app_endpoint
        ));

        return $result;
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS Topic을 모두 리턴한다.
     *
     * @param   string      $next_token        List page next token
     * @return  json
    */
    public function get_topic_list($next_token = NULL) {
        $result = $this->client->listTopics(array(
            'NextToken' => $next_token,
        ));

        return $result;
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS Subuscribe 에서 유져 디바이스를 삭제한다.
     * Unsubscribe를 하면 Topic에서 삭제가 되기 때문에 전체 Push 대상에서 제외가 된다.
     *
     * @param   string      $subscription_arn        Subscription ARN.
     * @return  json
    */
    public function un_subscribe($subscription_arn = NULL) {
        $result = FALSE;
        if(isset($subscription_arn)) {
            $result = $this->client->unsubscribe(array(
                'SubscriptionArn' => $subscription_arn
            ));
        } else {
            log_message('error','[AWS SNS ERROR] AWS SNS Undefined subscription arn');
        }

        return $result;
    }

    // ------------------------------------------------------------------

    /**
     * Push Message를 보낸다.
     *
     * @param   string      $app_type          'yapen' or 'ceo'
     * @param   string      $message            보낼 메세지.
     * @param   string      $push_type         'all' == Yapen applications Topic에 포함된 모든 사용자. 'test' == test 사용자에게만 보낸다.
     * @return  json
    */
    public function publish($publish_type = 'individual', $arn = NULL, $message = NULL) {
        $result = TRUE;
        $arn_key = 'TargetArn'; // 고정값 변경불가.

        if( !isset($arn) ) {
            log_message('error','[AWS SNS ERROR] Undefined Topic name(Topic Arn)');
            return FALSE;
        }

        if($publish_type == 'all') {
            $arn_key = 'TopicArn'; // 고정값 변경불가.
            $target_arn = $this->aws_sns_arn.$arn;
            log_message('error','[AWS SNS SUCCESS] AWS SNS All Member Publish Topic Name => '.$arn);
        } else {
            $target_arn = $arn;
        }

        $push_result = $this->client->publish(array(
            $arn_key            => $target_arn,
            'Message'           => $message,
            'Subject'           => 'YanoljaTravel',
            'MessageStructure'  => 'json'
        ));

        if($push_result['@metadata']['statusCode'] != 200) {
            $result = FALSE;
            log_message('error','[AWS SNS ERROR] AWS SNS Publish Error!');
        }

        return $result;
    }

    public function list_subscriptions_by_topic($topic_arn = NULL, $next_token = NULL) {
        $result = $this->client->listSubscriptionsByTopic(array(
            // TopicArn is required
            'TopicArn' => $topic_arn,
            'NextToken' => $next_token
        ));

        return $result;
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS Client를 생성한다.
     *
     * @return  Void
    */
    public function create_topic($topic_name) {
        $result = TRUE;
        if(isset($topic_name)) {
            $result = $this->client->createTopic(array(
                'Name'     => $topic_name
            ));

            if($result['@metadata']['statusCode'] != 200) {
                $result = FALSE;
                log_message('error','[AWS SNS ERROR] AWS SNS Topic Create Error!');
            }

        } else {
            $result = FALSE;
            log_message('error','[AWS SNS ERROR] AWS SNS Topic Name undefined');
        }

        return $result;
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS Client를 생성한다.
     *
     * @return  Void
    */
    private function sns_client() {
        $aws_credentials = $this->aws_init();

        $this->client = new Aws\Sns\SnsClient([
            'version'     => 'latest',
            'region'      => $this->sns_region,
            'credentials' => $aws_credentials
        ]);
    }

    // ------------------------------------------------------------------

    /**
     * AWS SNS Client를 생성한다.
     *
     * @return  Void
    */
    public function get_applications($next_token = NULL) {
        $result = $this->client->listPlatformApplications(array(
            'NextToken' => $next_token
        ));

        return $result;
    }

    // ------------------------------------------------------------------

    /**
     * AWS Applications 분기 처리.
     *
     * @param   string      $app_type           서비스 타입.  ex) 'yapen' or 'ceo' or 'test',  default = 'test'
     * @param   string      $device_type        디바이스 토큰. ex) 'ios' or 'android',  default = 'android'
     * @return  string
    */
    private function division_app_arn($app_type = 'test', $device_type = 'android') {
        $app_type = strtolower($app_type);

        if($app_type == 'yapen') {
            $topic_arn = self::_YAPEN_APP_ANDROID_ARN;
            if( $device_type == 'ios' || $device_type == 'i') {
                $topic_arn = self::_YAPEN_APP_IOS_ARN;
            }
        } elseif($app_type == 'ceo') {
            $topic_arn = self::_CEO_APP_ANDROID_ARN;
            if( $device_type == 'ios' || $device_type == 'i') {
                $topic_arn = self::_CEO_APP_IOS_ARN;
            }
        } elseif($app_type == 'test') {
            $topic_arn = self::_TEST_APP_ANDROID_ARN;
            if( $device_type == 'ios' || $device_type == 'i') {
                $topic_arn = self::_TEST_APP_IOS_ARN;
            }
        } else {
            log_message('error','[AWS SNS ERROR] AWS SNS Not found Application!');
            return FALSE;
        }

        return $this->aws_sns_arn.$topic_arn;
    }

}