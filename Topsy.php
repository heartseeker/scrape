<?php
set_time_limit(0);

class Topsy {
	
	private $_setting = array();
	private $_date = array();

	
	public function __construct(array $date) {
		
		$param = array(
			'offset' 			=> 0,
			'perpage' 			=> 100,
			'sort_method'		=> 'date',
			'type'				=> 'link',
			'apikey'			=> '09C43A9B270A470B8EB8F2946A9369F3',
			'_'					=> strtotime(date('Y-m-d H:i:s'))
		);
		
		$setting = array_merge($param, $date);
		
		$this->_setting = $setting;
		
		$this->setDateRange($date['mintime'], $date['maxtime']);
		
		$this->_lastDate = $date['maxtime'];
		
	}
	
	public function curl($source_url) {
		
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
	
	
	public function setUrl(array $param) {
		
		$url = "http://otter.topsy.com/search.js?" . http_build_query($param);
		
		return $url;
		
	}
	
	public function setDateRange($startDate, $endDate, $format="Y-m-d H:i:s") {
		
		$startDate 	= date("Y-m-d H:i:s", $startDate);
		$endDate 	= date("Y-m-d H:i:s", $endDate);
		
		//Create output variable
		$datesArray = array();
		//Calculate number of days in the range
		$total_days = round(abs(strtotime($endDate) - strtotime($startDate)) / 86400, 0) + 1;
  
		//Populate array of weekdays and counts
		for($day=0; $day<$total_days; $day++) {
			
			$datesArray[] = date($format, strtotime("{$startDate} + {$day} days"));
			
		}
	
		//Return results array
		$this->_date =  $datesArray;
	
	}
	
	public function getDateRange() {
		
		return $this->_date;
	}
	
	/*
	 * @Return all of the data in a given date range
	 * @param $since = Since Date, $until = Until what date
	 * @ return array = dataset
	 * 
	 */
	
	 
	public function getData($since, $until) {
	 	
		
		$this->_setting['mintime'] = strtotime($since);
		$this->_setting['maxtime'] = strtotime($until);
		$this->_setting['offset'] = 0;
		
		//echo "Since: " . date("Y-m-d H:i:s", $this->_setting['mintime']) . " - Until: " . date("Y-m-d H:i:s", $this->_setting['maxtime']) . '<br/>';
		
		$url = $this->setUrl($this->_setting);
		
		echo 'Url: ' . $url . '<br/>';
		
		$html = $this->curl($url);
		
		$html = json_decode($html, true);
		
		$total = $html['response']['total'];
		$data1 = $html['response']['list'];

		//echo '<pre>' , print_r($data1) , '</pre>';
		
		foreach($data1 as $field) {

			$myType = $field["mytype"];

			if($myType == 'link') {
						
			$article_url	= $field['trackback_permalink'];
			$content		= addslashes($field['content']);
			$author			= strtoupper($field['trackback_author_name']);
			@$article_id	= end(explode("/", $article_url));
			$headline 		= "FROM: " . $author;
			$img_url		= '';
			$publish_date	= date("Y-m-d H:i:s", $field['firstpost_date']);
			$main_url		= $field['url'];
			
			//echo $article_id . '<br/>';
			
			$this->saveData($article_id, $headline, $author, $content, $main_url, $publish_date, $img_url);
			}		
		}
		
		//echo '<pre>' . print_r($data1, true) . '</pre>';
		
		
		
		echo $total . '<br/>';
		
		if($total > 100) {
			
			$offset = 0;
			do {
				$offset += 100;
				
				$this->_setting['offset'] = $offset;
				
				$url = $this->setUrl($this->_setting);
		
				$html = $this->curl($url);
				
				$html = json_decode($html, true);
				
				$data = $html['response']['list'];
				
				
				if(isset($data)) {
				
					foreach($data as $field) {
						
						$article_url	= $field['trackback_permalink'];
						$content		= addslashes($field['content']);
						$author			= strtoupper($field['trackback_author_nick']);
						@$article_id	= end(explode("/", $article_url));
						$headline 		= "TWEET FROM: " . $author;
						$img_url		= $field['topsy_author_img'];
						$publish_date	= date("Y-m-d H:i:s", $field['firstpost_date']);
						
						//echo $article_id . '<br/>';
						
						$this->saveData($article_id, $headline, $author, $content, $article_url, $publish_date, $img_url);
						
					}
				
				}
				
				//echo 'Offset: ' . $offset . '<br/>';
				
				//echo 'Settings: ' . '<br/>';
				
				//echo '<pre>' . print_r($this->_setting, true) . '</pre>';
				
				//echo '<pre>' . print_r($html, true) . '</pre>';
				
				//echo $html['response']['total'] . '<br/>';
				
			} while($offset < $total);
			
		}
		
		return $html;
	}

	/*
	 * @Return all of the data in a day
	 * @param $initialDay = day (Y-m-d H:i:s) format
	 * @ return array = dataset
	 * 
	 */
	
	public function getDayData($initialDay) {
		
		$initialDay = strtotime($initialDay);
		
		$since = date("Y-m-d H:i:s", $initialDay);;
		$until = date("Y-m-d 00:59:59", $initialDay);
		
		$finalHour = strtotime(date("Y-m-d 23:59:59", strtotime($since)));
		
		do {
			
			$data = $this->getData($since, $until);
			
			$since = $until;

			$until = date("Y-m-d H:i:s", strtotime($until) + (1 * 3600));
			
			$timeStampUntil = strtotime($until);
			
			echo date("Y-m-d H:i:s", $finalHour) . " == " . date("Y-m-d H:i:s", $timeStampUntil) . '<br/>';
			
		} while($timeStampUntil != $finalHour);
		
	}
	
	public function scrape() {
		
		$dates = $this->getDateRange();


		
		
		foreach($dates as $date) {
		
			$this->getDayData($date);
		
		}
	
		
		
	}
	
	public function connect($host, $username, $password, $dbname) {
		
		mysql_connect($host, $username, $password);
		mysql_select_db($dbname);
		
	}
	
	public function saveData($article_id, $headline, $author, $content, $article_url, $publish_date, $img_url) {
		
		$this->connect("localhost", "root", "", "twitter_scrape");
		
		$cQuery = "SELECT * FROM twitter_articles WHERE `article_id` = '$article_id'";
		$cResult = mysql_query($cQuery);
		
		if(!$cRs = mysql_fetch_assoc($cResult)) {
			
			$query  = "INSERT INTO twitter_articles (`article_id`, `headline`, `author`, `content`, `article_url`, `media_provider`, `publish_date`, `img_url`)";
			$query .= " VALUES('$article_id', '$headline', '$author', '$content', '$article_url', 'TWITTER', '$publish_date', '$img_url')";
			$result = mysql_query($query);
			
		}
		
		
	}
	
}
