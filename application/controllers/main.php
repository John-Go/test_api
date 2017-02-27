<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {

	public function __construct()
	{
		//phpinfo();
		//show_404();
		parent::__construct();
	}

	public function index() {
		$this->load->view('welcome');
	}

	public function elb_health_chk() {
		log_message('error','ELB Server name 2 : '.$this->input->server('SERVER_ADDR'));
	}

	public function test() {
		//log_message('error','ELB Server name : '.$this->input->server('SERVER_ADDR'));
		//for ($i=0; $i < 100 ; $i++) {
		//
		//}
	}
}
