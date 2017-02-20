<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Email Class
 *
 * Permits email to be sent using Mail, Sendmail, or SMTP.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/email.html
 */

$CI =& get_instance();
$CI->load->library('email');
 
class Template_email extends CI_Email {

	var $protocol = 'sendmail';
	var $mailpath = '/usr/sbin/sendmail';
	var $wordwrap = FALSE;
	var $mailtype = 'html';
	var $validate = TRUE;

	
	var	$CI = NULL;
	var $from_name = '야놀자펜션';
	var $from_email = 'pension@yanolja.com';
	var $callback_email = 'pension@yanolja.com';
	var $to_email =  NULL;
	var $to_name =  NULL;
	var $subject =  NULL;
	var $contents = NULL;
	var $template = NULL;
	//var $template_file_path = '_yps/template_email/';
	var $template_file_path = YANOLJA_PENSION_SKIN_PATH;
	var $replace_data = array(
		'email_images_path' => 'http://img.yapen.co.kr/pension/web/email/',
		'yapen_url' => 'http://yapen.co.kr/'
	);


	/**
	 * Constructor - Sets Email Preferences
	 *
	 * The constructor can be passed an array of config values
	 */
	public function __construct($config = array())
	{
		parent::__construct();

		$this->template_file_path = YANOLJA_PENSION_SKIN_PATH . 'template_email/';
		$this->CI =& get_instance();
		$this->CI->load->library('form_validation');
	}

	
	
	
	/**
	 * 준비된 template를 사용하여 메일 보내기
	 * 
	 * @param string template_code : 템플릿 고유코드
	 * @return void 
	 */
	public function template_send( $template_code )
	{
		$this->set_template( $template_code );
		
		
		if ( isset($this->subject) && isset($this->template) )
		{
            $template = array();
            $template[] = $this->CI->load->view( $this->template_file_path.'_header', NULL, TRUE );
            $template[] = $this->template;
            $template[] = $this->CI->load->view( $this->template_file_path.'_footer', NULL, TRUE );
            $this->template = implode($template); 
            log_message('error',print_r($this->template,true)); 
            $this->CI->load->library('parser');
            
            $this->contents = $this->CI->parser->parse_string( $this->template, $this->replace_data, TRUE );
        	log_message('error',print_r($this->contents,true));    
            $this->from( $this->from_email, $this->from_name );
            $this->reply_to( $this->callback_email, $this->from_name );
            
            
            $this->subject( $this->subject );
            $this->message( $this->contents );
            
            $this->send();
		}
	}
	
	
	
	/**
	 * 입력 받은 from값이 member idx인지 email인지 확인하여
	 * email이면 바로 from값을 리턴하고,
	 * member idx인 경우 member의 email를 리턴한다.
	 * 
	 * @param string from : member idx 또는 email
	 * @return object this
	 */
	public function set_to( $to )
	{
		$email = NULL;
		$name = '';
		
		
		if ( $this->CI->form_validation->is_natural_no_zero($to) )
		{
			$this->CI->db->select( 'mbIdx, mbID, mbNick, mbEmail' );
			$this->CI->db->where( 'm.mbIdx', $to );
			//$this->CI->db->where( 'm.mbOut', 'N' );
			$this->CI->db->from( 'member AS m' );
			$member = $this->CI->db->get()->row_array();
			
			$mbID = ( isset($member['mbID']) ) ? explode('.', $member['mbID']) : NULL;
			
			$this->set_data(array(
				'아이디' => ( isset($mbID[1]) ) ? substr($mbID[1], 0, 2).'****' : '' 
			));
			$email = ( isset($member['mbEmail']) ) ? $member['mbEmail'] : NULL;
		}


		if ( $this->valid_email($to) )
		{
			$email = $to;
		}
		
		$this->to( $email );
		
		return $this;
	}
	
	
	
	/**
	 * 템플릿에서 사용한 변수들의 값들 설정
	 * 
	 * @param array replace_code : 탬플릿에 설정된 변수의 값들
	 * @return object this
	 */
	public function set_data( $replace_data = array() )
	{		
		if ( is_array($replace_data) && count($replace_data) > 0 )
		{
			$this->replace_data = array_merge( $this->replace_data, $replace_data );
		}
		
		return $this;
	}
	
	
	
	/**
	 * 템플릿을 구성한다
	 * 
	 * @param array replace_code : 탬플릿에 설정된 변수의 값들
	 * @return object this
	 */
	public function set_template( $template_code )
	{
		$template = array();
		
		$template['member_join_success'] = array(
			'code' => 'member_join_success',
			'subject' => '회원가입을 축하드립니다.'
		);
		
		$template['member_out_success'] = array(
			'code' => 'member_out_success',
			'subject' => '정상적으로 회원탈퇴가 되었습니다.'
		);
		
		$template['inquiries_answer'] = array(
			'code' => 'inquiries_answer',
			'subject' => '1:1문의에 대한 답변입니다.'
		);
		
		$template['reset_password_auth_email'] = array(
			'code' => 'reset_password_auth_email',
			'subject' => '비밀번호 재설정 인증메일입니다.'
		);
		
		$this->subject = '[야놀자펜션] ' . $template[$template_code]['subject'];
		$this->template = $this->CI->load->view( $this->template_file_path.$template[$template_code]['code'], NULL, TRUE );
		
		return $this;
	}
	


}
// END CI_Email class

/* End of file Email.php */
/* Location: ./system/libraries/Email.php */
