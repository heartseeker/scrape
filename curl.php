<?php

function curlFunction($source_url){
	$ch = curl_init();
	
	$userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:15.0) Gecko/20100101 Firefox/15.0.1';
	curl_setopt($ch, CURLOPT_USERAGENT, 		$userAgent);
	curl_setopt($ch, CURLOPT_URL, 				$source_url);
	curl_setopt($ch, CURLOPT_HEADER,			false);
	curl_setopt($ch, CURLOPT_FAILONERROR,		true);
	curl_setopt($ch, CURLOPT_ENCODING, 			"UTF-8" );
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 	true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 		true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 	true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 			60);
	$html= curl_exec($ch);
	curl_close($ch);
	return $html;
}


$proxy = "http://175.41.150.106/p/rsite/index.php?q=";

$url = $proxy . urlencode("http://www.google.com/search?q=dota+2&tbs=cdr:1,cd_min:02-03-2012,cd_max:04-05-2013&num=100");

echo curlFunction($url);