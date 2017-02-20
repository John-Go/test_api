<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Point {
	private $CI;

	public function __construct() {
		$this->CI =& get_instance();
		$this->CI->load->model('_hts/shop/point_model');
	}

	/* 누적포인트를 구함 */
	public function getPoint( $mbID ){
		return $this->CI->point_model->getPoint( $mbID );		
	}

	/* 가용포인트를 구함 */
	public function getUsingPoint( $mbID ){
		return $this->CI->point_model->getUsingPoint( $mbID );
	}

	/* 포인트를 업데이트 */
	public function updatePoint( $mbID, $point ){
		return $this->CI->point_model->updatePoint( $mbID, $this->getPoint( $mbID )+$point );
	}

	/* 포인트 로그를 기록 */
	public function writePointLog( $mbID, $point, $code, $description = ''){
		return $this->CI->point_model->writePointLog( $mbID, $point, $code, $description );
	}

	/* 포인트를 사용한다 */
	public function usePoint( $mbID, $point ){
		//구매금액이 0이 될때까지 뺀다
		while( $point > 0 ){
			//가장 오래된 내역중에 포인트가 남은 row를 1개 가져옴
			$row = $this->CI->point_model->getRowBySort($mbID);
			
			//row를 업데이트함
			//row의 남은 포인트
			$remainPoint = $row['mslPoint']-$row['mslPointDeduce'];

			//뺴야될 포인트가 더 큰 경우
			if( $point > $remainPoint ){ 
				$point = $point-$remainPoint;
				$remainPoint = 0;
			
			//빼야될 포인트가 row의 남은 포인트보다 작은 경우
			}else{
				$remainPoint = $remainPoint-$point;
				$point = 0;
			}

			//row를 업데이트
			$this->CI->point_model->updatePointDeduce( $row['mslIdx'], $row['mslPoint']-$remainPoint );
		}
	}
}

?>