<?php
F3::route('GET /',
	function() {
		
		$veri = Web::http('GET http://host:port/admin.cgi?pass=password&mode=viewxml');
		
		$veri = utf8_encode($veri);
		$veri = simplexml_load_string($veri);
		
		$veri = json_encode($veri);
		$veri = json_decode($veri, TRUE);
		
		$full = 0;
		if(isset($_GET['full'])){ $full = $_GET['full']; }
		
		if($veri['STREAMSTATUS'] == 1){
		
			$api_contents = icerikyarat($veri, $full);
			
		}else{
			
			$api_contents = array(
				'durum' => FALSE
			);

		}
		
		header('Content-type: application/json');
		echo json_encode($api_contents);
	}
);

F3::route('GET /yayincilar/@count', 
	function() {

		$yayinci = F3::get('PARAMS["count"]');
		header('Content-type: application/json');
		echo json_encode(yayincibilgileri($yayinci));
	}	
);

F3::route('GET /sunucular/@count', 
	function() {

		$sunucu = F3::get('PARAMS["count"]');
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(sunucubilgileri($sunucu));
	}	
);


function icerik($veri, $full){
	
	$yayinci = str_replace('sosyalradyo: ', '', $veri['SERVERTITLE']);
	
	$web = 'http://sosyalradyo.com/onair';

	$yorumlar = str_replace('http://git.sosyalradyo.com/?url=', '', $veri['SERVERURL']);
	
	if($yorumlar == '0'){
		$yorumlar == NULL;
	}elseif(preg_match("/\bfriendfeed.com\b/i", $yorumlar)) {
		$yorumlar = $yorumlar.'?embed=1';
	}
	
	if($full == 1){
		$dinleyicisayisi = array(
			'dinleyen' => $veri['CURRENTLISTENERS'],
			'essiz' => $veri['REPORTEDLISTENERS']
		);
	}else{
		$dinleyicisayisi = $veri['CURRENTLISTENERS'];
	}
  
	return array(
		'durum' => TRUE,
		'dinleyicisayisi' => $dinleyicisayisi,
		'calansarki' => $veri['SONGTITLE'],
		'program' => $yayinci,
		'yayinci' => yayincibilgileri($veri['AIM']),
		'bitrate' => $veri['BITRATE'],
		'yorumlar' => $yorumlar
	);
}

function icerikyarat($veri, $full){
	$somethings = icerik($veri, $full);
	//$somethings = turkceduzeltgeci($somethings);
	return $somethings;
}
	