<?php
class Deploy extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    public function index() {
        exec('sh /gfdata/update.sh', $output);
        log_message('error', 'Api server : '.print_r($output,true));
    }
}