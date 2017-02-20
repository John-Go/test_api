<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// user ybs msg Type
$config['ybsMsgType']['U'] = array(
	'YBS_W' => "[입금대기]
펜션명 : #{pensionName}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

예약자 : #{user}
인원 : #{people}
옵션 : #{options}

결제금액 : #{price}원
입금계좌 : #{account}
입금기한 : #{limit}

예약확인 : #{URL}",

	'YBS_S'	=> "[예약완료]
예약번호 : #{revCode}
펜션명 : #{pensionName}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

주소 : #{address}
연락처 : #{phoneNumber}

예약자 : #{user}
인원 : #{people}
옵션 : #{options}

결제금액 : #{price}원
현장결제 : #{noPrice}원 (펜션 도착 후 결제)

예약확인 : #{URL}
예약이 완료되었습니다. 즐거운 여행 되세요.",

	'YBS_C'	=> "[예약취소]
펜션명 : #{pensionName}
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박
예약자 : #{user}

예약확인 : #{URL}
취소가 완료되었습니다"
);

// ceo ybs msg Type
$config['ybsMsgType']['C'] = array(
	'YBS_CW'	=> "[입금대기]
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

예약자 : #{user}
연락처 : #{phoneNumber}
인원 : #{people}
옵션 : #{options}

예약접수 : #{regDate}
결제금액 : #{price}원 입금대기 중입니다.

[사장님 페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

	'YBS_CS'	=> "[예약완료]
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박

예약자 : #{user}
연락처 : #{phoneNumber}
인원 : #{people}
옵션 : #{options}

픽업여부 : #{pickup}
도착예정시간 : #{inTime}
요청사항 : #{request}

예약접수 : #{regDate}
결제금액 : #{price}원
현장결제 : #{noPrice}원 (고객 도착 후 결제)

[사장님 페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

	'YBS_CC'	=> "[예약취소]
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박
예약자 : #{user}

예약이 취소되었습니다.

[사장님 페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC",

	'YBS_CWC'	=> "[미입금취소]
객실명 : #{roomName}
입실일 : #{startDate}(#{dayName}) / #{day}박
예약자 : #{user}

예약이 취소되었습니다.

[사장님 페이지 바로가기]
http://ceo.yapen.co.kr
[카카오톡 사장님 고객센터]
http://goo.gl/scHffC"
);