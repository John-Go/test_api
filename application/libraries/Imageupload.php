<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Imageupload{
	
	public function __construct($props = array()) {
		//parent::__construct();
		
		/**
		 * $props  = array(
		 * 		'image' 		=> 저장할 이미지 파일명
		 * 		'key'			=> 앱키,
		 * 		'mbIdx' 		=> 회원 index,
		 * 		'mode' 			=> insert / update flag,
		 * 		'tempDir' 		=> 임시파일을 저장할 디렉터리 경로,
		 * 		'allowed_types'	=> 허용 확장자 
		 * 		'upload_path' 	=> 업로드할 디렉터리 경로,	
		 * 		'max_size'		=> 허용 이미지 사이즈,
		 * 		'encrypt_name'	=> 파일명 암호화여부 (TRUE / FALSE)
		 * )
		 */
		if (count($props) > 0)
		{
			$this->initialize($props);
		}
		//$CI->uploadPath = '/home/site/admin/pension/test/';				//실제저장경로
		//$CI->tempDir   = "/home/site/yanoljaTravel_adm/data/testTemp/";	//임시저장경로
    }
	
	function initialize($props = array()) {
		$CI   =& get_instance();
		
		//이미지를 받는다.
			//->form 검증
		if(!$props['mbIdx'] || !isset($props['mbIdx'])){
			$beforePage = $_SERVER['HTTP_REFERER'];
			header('Location: ' . $beforePage);
			return false;
		}
		
		if(!$props['mode'] || !isset($props['mode'])){
			$mode = 'update';
		}
		
		//임시저장된 이미지를 임시 폴더에 옮긴다
			//이미지저장
		$CI->load->library('upload', array(
            'allowed_types' => $props['allowed_types'],
            'upload_path'   => $props['tempDir'],
            'max_size'      => $props['max_size'],
            'encrypt_name'  => $props['encrypt_name']
        ));
		
		//썸네일을 생성한다.
			//썸네일 생성을 위한 config 설정
			//썸네일 생성
			//임시저장 썸네일 폴더 안에 각각저장
			
			echo var_dump($_FILES[ $props['image'] ]);
			exit;
		if(is_uploaded_file($_FILES[ $props['image'] ]["tmp_name"]))
		{
			if($CI->upload->do_upload($uploadFileName)) 
			{
				echo "success";
				return;
				//$this->thumbnailImage($memberIdx);
			}
			else
			{
				echo json_encode($CI->upload->display_errors('<p>', '</p>'));
			}
		}
		else
		{
			echo json_encode("error_1<br>");
			return;
		}
		
		$data = $CI->upload->data();
		echo print_r($data);
		exit;
		$newImage = $data['file_name'];
		$tempDir = $data['file_path'];
		
		//ftp 연결
		$this->connectFtp();
		//insert, update 구분 업데이트인경우 폴더는 유지, 사진 삭제해줌
		
		if($mode == 'update'){
			
			$this->checkUploadMode($memberIdx, $newImage);	
		}else{
			$CI->makeImageFolder($memberIdx);		//	이미지 서버에 썸네일 이미지 폴더 생성
		}
		
		
		//이미지 서버에 저장
			
			//이미지 서버내에 회원 idx, 썸네일종류 별로 폴더 생성 
			//임시저장 썸네일 이미지를 이미지 서버내 생성한 폴더로 전송한다.
			//임시저장 이미지 삭제
			//ftp 끊기
		//결과를 리턴
		
		
		$CI->realUpload($tempDir, $newImage, $memberIdx);
		$CI->tempImageDelete($tempDir, $newImage);
		$CI->closeFtp();
	}

	function checkUploadMode($memberIdx, $newImage){
		echo "delectFileName : ".$CI->uploadPath.$memberIdx.'/200x0/'.$newImage;
		$CI->ftp->delete_file($CI->uploadPath.$memberIdx.'/200x0/'.$newImage);
		$CI->ftp->delete_file($CI->uploadPath.$memberIdx.'/800x0/'.$newImage);
	}
	
	function thumbnailImage($memberIdx){
		$data = $CI->upload->data();
		$newImage = $data['file_name'];

		if($newImage){
			$myDir     	= $data['file_path'];
			
			$filename 	= str_replace('/data/testTemp/','',$newImage);	//temp 에 저장되어있는 이미지 파일명
			
			$config200 	= $CI->makeThumbConfig(200, 100, $myDir.$filename, $myDir."/200x0/".$filename, 90);	
			$config800 	= $CI->makeThumbConfig(800, 100, $myDir.$filename, $myDir."/800x0/".$filename, 90);
			
			$CI->makeThumb($config200);
			$CI->makeThumb($config800);
		}
	}

	function connectFtp(){
		$CI->load->config('_ftp');
        $CI->load->library('ftp', $CI->config->item('image')); 
        $CI->ftp->connect();
	}
	function closeFtp(){
		$CI->ftp->close();
	}
	function makeImageFolder($memberIdx){
		// 썸네일 이미지 폴더가 이미 있다면?
		
		$CI->ftp->mkdir($CI->uploadPath.$memberIdx, 0777);
		$CI->ftp->mkdir($CI->uploadPath.$memberIdx."/200x0/", 0777);
		$CI->ftp->mkdir($CI->uploadPath.$memberIdx."/800x0/", 0777);
	}
	
	
	// * function makeThumbConfig (가로, 세로, 저장된 이미지 경로, 저장할 이미지 경로, 품질)
	function makeThumbConfig($width, $height, $source, $target, $quality) {
		$config['source_image']  = $source;
        $config['new_image']     = $target;
        $config['width']         = $width;
        $config['height']        = $height;
        $config['quality']       = $quality;
        $config['master_dim']    = 'width';
			
		return $config;
	}
	
		
	function makeThumb($config) {							//썸네일 이미지 생성
		if ($CI->image_lib == null) {
			$CI->load->library('image_lib', $config);	
		} else {
			$CI->image_lib->clear();
			$CI->image_lib->initialize($config); 	
		}
					
        $CI->image_lib->resize();
        $CI->image_lib->clear();
	}
	
	function realUpload($tempDir, $newImage, $memberIdx){		
		$CI->ftp->upload($tempDir."/800x0/".$newImage, $CI->uploadPath.$memberIdx."/800x0/".$newImage, 'auto', 0775);
		$CI->ftp->upload($tempDir."/200x0/".$newImage, $CI->uploadPath.$memberIdx."/200x0/".$newImage, 'auto', 0775);
	}
	
	function tempImageDelete($tempDir, $newImage){
		unlink($tempDir."/".$newImage);
        unlink($tempDir."/800x0/".$newImage);
		unlink($tempDir."/200x0/".$newImage);
	}
	
}


?>