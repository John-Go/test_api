<?php

class Main extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->model('_yps/travel/travel_model');
		$this->load->library('yps/cate_count');
	}

	function index() {
		checkMethod('get');	// ���� �޼��带 ����

		$ret['status']					= '1';
		$ret['failed_message']	= '';
		$areaList								= NULL;
		$areaList2							= NULL;

		//DB���� ����Ʈ�� ������
		$result = $this->travel_model->getTravelAreaCategoryListWithCount();
        $popular_arr = array('2.001','15.012','10.014','12.001','9.005','12.014','14.011','13.002','9.013','13.011','14.021','3','16');
		//��������
		foreach( $result->result() as $row ){
			$tmp = @explode('.', $row->ca_code );

			//�����̸� 1������
			if( !isset($tmp[1]) ){
				$obj =& $areaList[$row->ca_code];
				$obj = array();
				$obj['ca_code'] = $row->ca_code;
				$obj['ca_name'] = $row->ca_name;
				$obj['count']		= $row->count;
			

			//�Ҽ��̸� 2������
			}else{
				$obj =& $areaList2[$tmp[0]][];
				$obj = array();
				$obj['ca_code'] = $row->ca_code;
				$obj['ca_name'] = $row->ca_name;
				$obj['count']		= $row->count;
			}
		}

		//json �� �´� ���·� �簡��
		foreach( $areaList as $key => $val ){
			$obj =& $ret['lists'][];
			$obj = array();
			$obj['locname']			= $val['ca_name'];
			$obj['tntcnt']			= '0';
            
            if(in_array($val['ca_code'],$popular_arr)){
                $obj['popularity']  = '1';
            }else{
                $obj['popularity']  = '0';
            }
			$obj['lists']				= array();

			//2�� ������ ������
			foreach( $areaList2[$val['ca_code']] as $sKey => $sVal ){
				if( !$sVal['count'] ) continue;
                if(in_array($sVal['ca_code'],$popular_arr)){
                    $obj['lists'][] = array(
                        'code'              => $sVal['ca_code'],
                        'name'              => $sVal['ca_name'],
                        'count'             => number_format($sVal['count']),
                        'popularity'    => '1'
                    );
                }else{
                    $obj['lists'][] = array(
                        'code'              => $sVal['ca_code'],
                        'name'              => $sVal['ca_name'],
                        'count'             => number_format($sVal['count']),
                        'popularity'    => '0'
                    );
                } 
                
				//1�� ������ ������ �ջ��Ŵ
				$obj['tntcnt'] = $obj['tntcnt']+$sVal['count'];
			}

			//2�������� ��ü ������ string ���·� ��ȯ
			$obj['tntcnt'] = number_format($obj['tntcnt']);
		}

	
		echo json_encode( $ret );
	}
}
?>