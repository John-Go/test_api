<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {
	
	public function __construct()
	{
		//phpinfo();
		//show_404();
		parent::__construct();
	}
	
	public function elb_health_chk() {
		log_message('error','ELB Server name Master : '.$this->input->server('SERVER_ADDR'));
	}
}
