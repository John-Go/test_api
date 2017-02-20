<?php
class SetDevice extends CI_Controller
{
    function __construct() {
        parent::__construct();
        $this->load->model('_app/device/device_model');
    }

    function index()
    {
        $push_active_switch = TRUE;
        $push_status = 'on';

        // param
        $pData      = $this->input->post();
        // log_message('error','Param => '.print_r($pData,true));

        unset($pData['key']);

        // init
        $success    = false;
        $mbArr      = array();
        $resArr     = array();

        // param init
        $pData['mbIdx']         = isset($pData['mbIdx']) ? intval($pData['mbIdx']) : '0';
        $mbArr                  = $pData['mbIdx'] != '0' ? $this->device_model->getMemberInfo($pData['mbIdx']) : '';
        $pData['mbID']          = isset($mbArr['mbIdx']) ? $mbArr['mbID'] : '';
        $pData['deviceKey']     = trim($pData['deviceKey']);
        if(!$pData['deviceKey'])
        {
            $this->error->getError('0006');
        }
        if(!isset($pData['deviceKey']))
        {
            $this->error->getError('0007');
        }

        // 모바일 종류
        $pData['mbType']        = trim($pData['mbType']);
        switch($pData['mbType'])
        {
            case 'A':   // 안드로이드
            case 'I':   // 아이폰
                break;
            default:
                $this->error->getError('0007');
                break;
        }

        // 앱 명
        $pData['appName']       = trim($pData['appName']);
        switch($pData['appName'])
        {
            case 'yapen':       // 야펜 앱
            case 'ceo':         // 사장님 앱
                break;
            default:
                $this->error->getError('0007');
                break;
        }

        $pData['loginFlag']     = isset($pData['loginFlag']) ? ($pData['loginFlag'] == '0' ? '0' : '1') : '1';
        // pushFlag 0000 ex) 첫째자리 전체동의여부 / 추후 추가
        $pData['pushFlag']      = isset($pData['pushFlag']) ? ($pData['pushFlag'] == '0' ? '0000' : '1000') : '1000';
        $pData['accessDate']    = date('Y-m-d H:i:s');
        $pData['logoutDate']    = '';
        $pData['expireDate']    = date('Y-m-d H:i:s', strtotime('+1 year', time()));

        // 디바이스 정보 여부 확인
        $deviceArr  = $this->device_model->getDevice($pData['deviceKey']);

        // 디바이스 있을 경우
        if($deviceArr){

            // 로그아웃시 ( 기존 정보 변경 X )
            if($deviceArr['loginFlag'] == '1' && $pData['loginFlag'] == '0'){
                $pData['logoutDate'] = date('Y-m-d H:i:s');
                $success    = $this->device_model->deviceLogout($pData['mbIdx'], $pData['deviceKey']);
            }else{
                if(!$pData['logoutDate']){
                    unset($pData['logoutDate']);
                }

                // device 정보 변경
                $success    = $this->device_model->uptDevice($deviceArr['dIdx'], $pData);
            }

            // Push 활성화.
            $pre_push_flag = $deviceArr['pushFlag']; // 기존 push 상태.
            $param_push_flag = $pData['pushFlag']; // 요청 push 상태.
            $dIdx = $deviceArr['dIdx'];

            // log_message('error','Pre => '.$pre_push_flag);
            // log_message('error','Param => '.$param_push_flag);
            // log_message('error','IDX '.$dIdx);

            // 기존의 Push 상태와 요청 Push 상태가 다를 때만 변경한다.
            if($pre_push_flag != $param_push_flag) {
                // log_message('error','Different');
                if($param_push_flag == '0000') {
                    $push_status = 'off';
                }
            } else {
                $push_active_switch = FALSE;
            }
        }
        // 디바이스 없을 경우
        else
        {
            // device 정보 등록
            $success = $this->device_model->insDevice($pData);
            $dIdx = $success;
        }

        if($push_active_switch) {
            // log_message('error','===========Switch '.$push_status);
            // Push Class Load
            include_once (APPPATH.'controllers/aws/sns_module.php');
            $sns_push = new Sns_module();
            $sns_push->push_switch($dIdx, $push_status);
        }

        // 등록 혹은 변경 성공시
        if($success)
        {
            $resArr['status']           = '1';
            $resArr['failed_message']   = '';
            echo json_encode($resArr);
        }
        // DB 변경 등록 실패시
        else
        {
            $this->error->getError('0008');
        }
    }
}
?>