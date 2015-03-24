<?php

require_once "Topsy.php";

//$url = "http://otter.topsy.com/search.js?q=paul+george&offset=999&perpage=5&window=d&call_timestamp=1385018177821&apikey=09C43A9B270A470B8EB8F2946A9369F3&_=1385018634243&mintime=" . $since . "&maxtime=" . $until . "&sort_method=date";

$since = strtotime("2013-05-01 00:00:00");

$until = strtotime("2013-11-22 23:59:59");

$setting = array(
	'q' 		=> 'cathay lounge',
	'mintime'	=> $since,
	'maxtime'	=> $until
);


$t = new Topsy($setting);



$data = $t->scrape();

//echo '<pre>' . print_r($data, true) . '</pre>';


