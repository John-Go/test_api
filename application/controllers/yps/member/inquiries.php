<?php
class Inquiries extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->library('pension_lib');
		$this->load->model('_yps/member/member_model');
	}

	public function lists() {
		checkMethod('get');

		$idx = $this->input->get('idx');

		$page = $this->pension_lib->paramNummCheck($this->input->get('page'), 1, NULL);
		$limit = $this->pension_lib->paramNummCheck($this->input->get('limit'), 20, NULL);

		$offset = ($page - 1) * $limit;

		$result = $this->member_model->inquiriesLists($idx,$offset,$limit);

		if( !$result['count'] )
			$this->error->getError('0005');

		$no = 0;
		$ret = array();
		$ret['status'] = '1';
		$ret['failed_message'] = '';
		$ret['tnt_cnt'] = $result['count'].'';

		foreach ($result['query'] as $row) {
			$ret['lists'][$no]['arIdx'] = $row['arIdx'];
			$ret['lists'][$no]['name'] = $row['arName'];
			$ret['lists'][$no]['question'] = $row['arQuestion'];
			$ret['lists'][$no]['answer'] = (!empty($row['arAnswer'])) ? $row['arAnswer'] : '';
			$ret['lists'][$no]['date'] = $row['arRegDate'];
			$ret['lists'][$no]['answer_date'] = $row['arAnswerDate'];
			$no++;
		}

		echo json_encode( $ret );

//		$this->output->enable_profiler();
	}

	public function input() {
/*
<form name="form" method="post" action="/yps/member/inquiries/input?key=a3b1a551515fb16937f16fcb47e99b4f">
mbIdx <input type="text" name="mbIdx"><br />
arName <input type="text" name="arName"><br />
arQuestion <input type="text" name="arQuestion"><br />
<input type="submit" value="1:1등록">
</form>
*/
		$mbIdx = $this->input->post('mbIdx');
		$mpIdx = $this->input->post('mpIdx');
		$arType = $this->input->post('arType');
		$arName = $this->input->post('arName');
		$arQuestion = $this->input->post('arQuestion');

		if(!$mbIdx || !$arQuestion)
			$this->error->getError('0006');	// Key가 없을경우

		$row = $this->member_model->inquiriesInput(array(
								'mbIdx'=>$mbIdx,
								'mpIdx'=>$mpIdx,
								'arType'=>$arType,
								'arName'=>$arName,
								'arQuestion'=>$arQuestion
								));

		$ret = array();

		if($row){
			$ret['status'] = "1";
			$ret['failed_message'] = '';
		}else{
			$ret['status'] = "0";
			$ret['failed_message'] = '등록오류';
		}

		echo json_encode( $ret );

//		$this->output->enable_profiler();
	}


}
?>