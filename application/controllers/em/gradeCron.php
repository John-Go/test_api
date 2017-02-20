<?php
/*
 *  10.0    - 지역상단 광고
 *  9.0     -
 *  8.5     - 온라인총판 
 *  8.0     - 지역 2순위
 *  7.0     - 
 *  6.0     - YBS
 *  5.4     - 떠나요
 *  5.0     - CRMS
 *  4.0     - 제휴사(G펜션, G펜션 예약대행, 비즈온, 펜션나라 적용)
 *  3.0     - 실시간 예약
 *  2.0     - 전화문의
 *  1.0     - 준제휴점
 *  0.0     - 판매대기
 */
 
class GradeCron extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('cron/grade_model');
    }
    
    function index(){
        $lists = $this->grade_model->getAdEndPension();
        
        if(count($lists) > 0){
            foreach($lists as $lists){
                $this->gradeSet($lists['mpIdx']);
                $this->grade_model->setPensionTopEnd($lists['ptIdx']);
            }
        }
		
		$gradeLists = $this->grade_model->getGradeEndLists();
		
		if(count($gradeLists) > 0){
			foreach($gradeLists as $gradeLists){
				$this->gradeSet($gradeLists['mpIdx']);
			}
		}
		
		$adLists = $this->grade_model->getAdGradeLists();
		
		if(count($adLists) > 0){
			foreach($adLists as $adLists){
				$this->gradeSet($adLists['mpIdx']);
			}
		}
		
		$gradeStartLists = $this->grade_model->getGradeLists();
		if(count($gradeStartLists) > 0){
			foreach($gradeStartLists as $gradeStartLists){
				$this->gradeSet($gradeStartLists['mpIdx']);
			}
		}
    }
    
    function pension(){
        $mpIdx = $this->input->get('mpIdx');
        
        if(!$mpIdx || $mpIdx == ""){
            return; exit;
        }
        
        $this->gradeSet($mpIdx);
        
    }
    
    function gradeSet($mpIdx){
    	$partnerArray = array('27','19','29','24');
		
        $info = $this->grade_model->getPensionInfo($mpIdx);
        
        $gradeInfo = $this->grade_model->getGradeInfo($mpIdx);
        
        $grade = 0;
		if(isset($info['ptIdx'])){
            $grade = 10.0;			
			$this->grade_model->setPensionGrade($mpIdx, $grade);
        }else if(isset($gradeInfo['pgIdx'])){
            $grade = $gradeInfo['pgGrade'];
            $this->grade_model->setPensionGrade($mpIdx, $grade);
        }else{
            /* 예약타입에 따라 차등 변경 */
            if($info['ppbReserve'] == "R"){
                $grade = 3.0;
            }else if($info['ppbReserve'] == "T"){
                $grade = 2.0;
            }else if($info['ppbReserve'] == "N"){
                $grade = 1.0;
            }else{
                $grade = 0.0;
            }
            /*
             * 제휴사 구분에 따라 변경
             * G펜션, G펜션 예약대행, 비즈온
             * 실시간예약 이상등급일때 적용
            */
            if($info['ppbReserve'] == "R"){
                if(in_array($info['ppbMainPension'],$partnerArray)){
                    $grade = 4.0;
                }
                if($info['ppuCrmsFlag'] == "1"){
                    $grade = 5.0;
                }
				if($info['ddeonayo'] != "" && $info['ddeonayo'] > 0){
					$grade = 5.4;
				}
                if($info['ppuExternalFlag'] == "1"){
                    $grade = 6.0;
                }
				if($info['ppbOnline'] == "1"){
					$grade = 8.5;
				}
            }
            
            if(isset($info['ptIdx'])){
                $grade = 10.0;
            }
            
            $this->grade_model->setPensionGrade($mpIdx, $grade);
        }
    }
}       