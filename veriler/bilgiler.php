<?php
// Yayıncı bilgileri (@TODO: bunu veritabanına bağlamak lazım)
function yayincibilgileri($yayinci){
	$yayincilar = array(
		'kaloglu' => array(
			'ID' => 1,
			'isim' => 'Cihan Kaloglu',
			'url' => 'http://goo.gl/grjbH',
			'blog' => 'http://kaloglu.com/',
			'twitter' => 'kaloglu',
			'friendfeed' => 'kaloglu',
			'facebooksayfasi' => 'kaloglu.com',
			'bio' => 'Seviye KALOĞLU ile EVLİ!!!',
			'eposta' => 'attach.ch@gmail.com',
			'programadi' => 'Kaloglu.com',
			'surekli' => TRUE
		),
		'pacacican' => array(
			'ID' => 2,
			'isim' => 'Can Paçacı',
			'url' => 'http://can.pacaci.org/',
			'blog' => 'http://can.pacaci.org/',
			'twitter' => 'pacacican',
			'friendfeed' => 'canpacaci',
			'facebooksayfasi' => NULL,
			'bio' => 'Eski radyocu, Sosyal Radyo kurucularından.',
			'programadi' => 'Domates Biber Patlıcan',
			'eposta' => 'cancenin@gmail.com',
			'surekli' => TRUE
		),
		'aynebilim' => array(
			'ID' => 3,
			'isim' => 'aynebilim',
			'url' => 'http://friendfeed.com/aynebilm',
			'blog' => 'http://aynebilim.blogspot.com/',
			'twitter' => 'aynebilim',
			'friendfeed' => 'aynebilm',
			'facebooksayfasi' => NULL,
			'bio' => 'Ona ayn deyin, daha samimi',
			'programadi' => 'aynfm',
			'surekli' => TRUE
		)
	);
	
	if(array_key_exists($yayinci, $yayincilar)){
		return $yayincilar[$yayinci];
	}else{
		$bilgiler = json_decode(
				Web::http(
					'GET http://api.twitter.com/1/users/show/'.
						$yayinci.'.json'
				),
				TRUE
			);
		$bilgiler = array(
			'ID' => 0,
			'isim' => $bilgiler['name'],
			'url' => $bilgiler['url'],
			'blog' => NULL,
			'twitter' => $yayinci,
			'friendfeed' => NULL,
			'facebooksayfasi' => NULL,
			'bio' => $bilgiler['description'],
			'programadi' => NULL,
			'surekli' => FALSE
		);
		return $bilgiler;
	}
}