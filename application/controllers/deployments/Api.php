<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Continuous Deplioy
 *
 * @package     YanoljaTravel API Deploy
 * @author      YapenLab Dev Team
 * @copyright   Copyright (c) 2017
 * @since       Version 1.0
 */

require_once APPPATH.'controllers/deployments/Git_proc.php';

class Api extends Git_proc {

    /**
     * Continuous Deploy
     */

    const _SH_COMMAND = 'sh /gfdata/update.sh';

    function __construct() {
        // parent::__construct();
        $this->$deploy_kind = 'api';
    }

    // ------------------------------------------------------------------

    /**
     * Git Master Branch를 기준으로 Pull한다.
     *
     * @return  json
    */
    public function pull() {
        $CI =& get_instance();

        $res = $this->master_pull();
        log_message('error', 'Deploy Target server : '.print_r($res,true));
        return $CI->output->set_content_type('application/json')->set_output(json_encode($res));
    }

    // ------------------------------------------------------------------

    /**
     * Git Branch를 변경하고 Pull한다.
     *
     * @return  json
    */
    public function branch($br_name = NULL) {

        $res = '[DEPLOY ERROR] Undefine Branch Name.';
        if( !empty($br_name) ) {
            $res = shell_exec(self::_SH_COMMAND.' branch '.$br_name);
        } else {
            log_message('error','[DEPLOY ERROR] Undefine Branch Name.');
        }


        log_message('error', 'Deploy Target server : '.print_r($res,true));

        return $this->output->set_content_type('application/json')->set_output(json_encode($res));
    }

    public function index() {

        // $result = $this->liveExecuteCommand('ls -la');
        // $result = $this->liveExecuteCommand('sh /gfdata/update.sh pull');

        // if($result['exit_status'] === 0){
        //    // do something if command execution succeeds
        // } else {
        //     // do something on failure
        // }
        //
        $val = shell_exec('sh /gfdata/update.sh pull');
        log_message('error', 'Deploy Target server : '.print_r($val,true));

        return $this->output->set_content_type('application/json')->set_output(json_encode($val));
    }

    public function liveExecuteCommand($cmd){
        while (@ ob_end_flush()); // end all output buffers if any

        $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

        $live_output     = "";
        $complete_output = "";

        while (!feof($proc))
        {
            $live_output     = fread($proc, 4096);
            $complete_output = $complete_output . $live_output;
            echo "$live_output";
            @ flush();
        }

        pclose($proc);

        // get exit status
        preg_match('/[0-9]+$/', $complete_output, $matches);

        // return exit status and intended output
        return array (
                        'exit_status'  => intval($matches[0]),
                        'output'       => str_replace("Exit status : " . $matches[0], '', $complete_output)
                     );
    }
}