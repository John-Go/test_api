<?php
class Imgsave extends CI_Controller {
	
	function __construct() {
        parent::__construct();
//		$CI   =& get_instance(); => 상속 관계로 인해서 의미없음 
		$this->uploadPath = '/home/site/admin/pension/test/';				//실제저장경로
		$this->tempDir   = "/home/site/yanoljaTravel_adm/data/testTemp/";	//임시저장경로
    }
	
	function testImgSave(){
		$uploadFileName = "image";		
		$mode = $this->input->post('mode');	
		$memberIdx = $this->input->post('mbIdx');
		$key = $this->input->post('key');
	
		$this->load->library('Imageupload',
			array(
             		'image' 		=> $uploadFileName,
		 	 		'key'			=> $key,
		  			'mbIdx' 		=> $memberIdx,
		  			'mode' 			=> $mode,
		 	 		'tempDir' 		=> "/home/site/yanoljaTravel_api/temp/testTemp/",
		  			'allowed_types'	=> "gif|jpg|png|jpeg",
		  			'upload_path' 	=> "/home/site/admin/pension/test/",	
		  			'max_size'		=> 10240000,
		  			'encrypt_name'	=> TRUE
			)    
		);
	}
	function index() {
		//이미지를 받는다.
			//->form 검증
		$uploadFileName = "image";		
		$mode = $this->input->post('mode');	
		$memberIdx = $this->input->post('mbIdx');

		if(!$memberIdx || !isset($memberIdx)){
			$beforePage = $_SERVER['HTTP_REFERER'];
			header('Location: ' . $beforePage);
			return false;
		}
		
		if(!$mode || !isset($mode)){
			$mode = 'update';
		}

		//임시저장된 이미지를 임시 폴더에 옮긴다
			//이미지저장
		$this->load->library('upload', array(
            'allowed_types' => 'gif|jpg|png|jpeg',
            'upload_path'   => $this->tempDir,
            'max_size'      => 10240000,
            'encrypt_name'  => TRUE
        ));
		
		
		
		
		//썸네일을 생성한다.
			//썸네일 생성을 위한 config 설정
			//썸네일 생성
			//임시저장 썸네일 폴더 안에 각각저장
		if(is_uploaded_file($_FILES[$uploadFileName]["tmp_name"]))
		{
			if($this->upload->do_upload($uploadFileName)) 
			{
				$this->thumbnailImage($memberIdx);
			}
			else
			{
				echo json_encode($this->upload->display_errors('<p>', '</p>'));
			}
		}
		else
		{
			echo json_encode("error_1<br>");
			return;
		}
		
		$data = $this->upload->data();
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
			$this->makeImageFolder($memberIdx);		//	이미지 서버에 썸네일 이미지 폴더 생성
		}
		
		
		//이미지 서버에 저장
			
			//이미지 서버내에 회원 idx, 썸네일종류 별로 폴더 생성 
			//임시저장 썸네일 이미지를 이미지 서버내 생성한 폴더로 전송한다.
			//임시저장 이미지 삭제
			//ftp 끊기
		//결과를 리턴
		
		
		$this->realUpload($tempDir, $newImage, $memberIdx);
		$this->tempImageDelete($tempDir, $newImage);
		$this->closeFtp();
	}

	function checkUploadMode($memberIdx, $newImage){
		echo "delectFileName : ".$this->uploadPath.$memberIdx.'/200x0/'.$newImage;
		$this->ftp->delete_file($this->uploadPath.$memberIdx.'/200x0/'.$newImage);
		$this->ftp->delete_file($this->uploadPath.$memberIdx.'/800x0/'.$newImage);
	}
	
	function thumbnailImage($memberIdx){
		$data = $this->upload->data();
		$newImage = $data['file_name'];

		if($newImage){
			$myDir     	= $data['file_path'];
			
			$filename 	= str_replace('/data/testTemp/','',$newImage);	//temp 에 저장되어있는 이미지 파일명
			
			$config200 	= $this->makeThumbConfig(200, 100, $myDir.$filename, $myDir."/200x0/".$filename, 90);	
			$config800 	= $this->makeThumbConfig(800, 100, $myDir.$filename, $myDir."/800x0/".$filename, 90);
			
			$this->makeThumb($config200);
			$this->makeThumb($config800);
		}
	}

	function connectFtp(){
		$this->load->config('_ftp');
        $this->load->library('ftp', $this->config->item('image')); 
        $this->ftp->connect();
	}
	function closeFtp(){
		$this->ftp->close();
	}
	function makeImageFolder($memberIdx){
		// 썸네일 이미지 폴더가 이미 있다면?
		
		$this->ftp->mkdir($this->uploadPath.$memberIdx, 0777);
		$this->ftp->mkdir($this->uploadPath.$memberIdx."/200x0/", 0777);
		$this->ftp->mkdir($this->uploadPath.$memberIdx."/800x0/", 0777);
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
		if ($this->image_lib == null) {
			$this->load->library('image_lib', $config);	
		} else {
			$this->image_lib->clear();
			$this->image_lib->initialize($config); 	
		}
					
        $this->image_lib->resize();
        $this->image_lib->clear();
	}
	
	function realUpload($tempDir, $newImage, $memberIdx){		
		$this->ftp->upload($tempDir."/800x0/".$newImage, $this->uploadPath.$memberIdx."/800x0/".$newImage, 'auto', 0775);
		$this->ftp->upload($tempDir."/200x0/".$newImage, $this->uploadPath.$memberIdx."/200x0/".$newImage, 'auto', 0775);
	}
	
	function tempImageDelete($tempDir, $newImage){
		unlink($tempDir."/".$newImage);
        unlink($tempDir."/800x0/".$newImage);
		unlink($tempDir."/200x0/".$newImage);
	}
	
}


?>