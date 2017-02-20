<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class _Common {
	function index() {
		$CI =& get_instance();
        
		if( !$CI->input->get_post('debug') )
			header("Content-Type: Application/json; charset=UTF-8");
		else 
			header("Content-Type: text/html; charset=UTF-8");

		header("Expires: 0"); // rfc2616 - Section 14.21
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
		header("Cache-Control: pre-check=0, post-check=0, max-age=0"); // HTTP/1.1
		header("Pragma: no-cache"); // HTTP/1.0
        ini_set('allow_persistent','off');
        
		// 이전 페이지 체크
		$referer = parse_url($CI->input->server('HTTP_REFERER'));
		$repSelf = str_replace('/index.php', '', $CI->input->server('PHP_SELF'));
		$url = ( !empty($referer['path']) && $referer['path'] != $repSelf )
			? $CI->input->server('HTTP_REFERER')
			: $repSelf;


		/* API URL 의 첫번째 세그먼트로 업체사이트 코드를 가져옴 */
		//if( strtoupper($CI->uri->segment(1)) != 'COMMON' ) define('SITE', strtoupper($CI->uri->segment(1)));

		//db접속
		if( $CI->uri->segment(1) == 'common' ) $connect = 'yps';
		else $connect = $CI->uri->segment(1);
        if($connect == "ypv" || $connect == "v3" || $connect == "connect" || $connect == 'main' ){
            $connect = "yps";
        }
		$CI->db = $CI->load->database( $connect, TRUE );

		/* API KEY 검사 */
		//common 모델을 로드함
		$CI->load->model('Common_model', 'common');

		$return = array();
		$notSegment = array('em','v3','connect','main');
        if(!in_array($CI->uri->segment(1),$notSegment)){
    		$key	= $CI->input->get_post('key');
    		$result = $CI->common->getApiKey( $key );
    
    		// api key가 넘어오지 않은 경우
    		if( !$key ) $CI->error->getError('0003');
    
    		//일치하는 api가 있는지 체크
    		if( !count($result) ) $CI->error->getError('0004');
    
    		if( ($CI->uri->segment(1) == 'common') && ($CI->uri->segment(2) == 'get_api') ){
    			define('SITE', strtoupper($CI->uri->segment(3)) );
    			define('DEVICE', strtoupper($CI->uri->segment(4)) );
    		}else{
    
    			define('URL', $url);
    			define('DEVICE', strtoupper( $result['device'] ));		// ANDROID or IOS
    			define('SITE', strtoupper( $result['site'] ));		// 사이트종류
    		}
    
    		//접근 폴더(어플종류)를 제한함
    		if( SITE != strtoupper($CI->uri->segment(1)) ){
    
    			if( 'COMMON' != strtoupper($CI->uri->segment(1)) ){
    				$CI->error->getError('0004');
    			}
    		}
        }
	}
}
