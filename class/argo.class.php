<?php

/*
	*
 	* Copyright (c) 2014-2020 Claudio Rivetta
	*
	* This file is part of Officinalab CMS. *
	*
	* @package Officinalab CMS
	* @author  Officinalab <contact@officinalab.fr>
	* @link    http://officinalab.fr


#-----------------------------------------------------------------------#
#                                                                       #
# Description : argo and luhn Class     	                					#
# Requires    : Apache - PHP                                            #
#                                                                       #
#-----------------------------------------------------------------------#
*/

if ( !class_exists( 'argo' ) ) {
	class argo {
		protected $url;

		public function __construct($url) {
			$this->url = $url;
		}

		public function __destruct() {
			$this->url = null;
		}

		// GET RANDOM USER AGENT for use with random proxy
		private function getRandomUserAgent() {
			$someUA = array (
			"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36",
			"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36",
			"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36",
			"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1",
			"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0",
			"Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25",
			"Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko",
			"Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko",
			"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246",
			"Mozilla/5.0 (Windows; U; Windows NT 6.1; rv:2.2) Gecko/20110201",
			"Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9a3pre) Gecko/20070330",
			"Opera/9.80 (X11; Linux i686; Ubuntu/14.10) Presto/2.12.388 Version/12.16");
			srand((double)microtime()*1000000);
			return $someUA[rand(0,count($someUA)-1)];
		}

		// function to load content from internet page
		//$proxyn = 0 doesn't use proxy
		//$proxyn = 1 use proxy
		//$proxyn = 2 use proxy but not last used (use next in list)
		public function getContent($proxyn = 0, $agentRandom = false, $follow = false) {
			$ch = curl_init();  // Crea la risorsa CURL
			if ($agentRandom == false) {
				$agent = $_SERVER["HTTP_USER_AGENT"];
			} else {
				$agent = $this->getRandomUserAgent();
			}
			if($proxyn != 0){ // USE PROXY
				$proxy = new proxy();
				$lastProxy = $proxy->getProxy($proxyn);
				unset($proxy);
				 /*	else { // set manual proxy
					$this->lastProxy = $proxyn;
				} */
				curl_setopt($ch, CURLOPT_PROXY,$lastProxy);    // Set CURLOPT_PROXY with proxy in $proxy variable
				//printf('<p class="small">%d -Proxy: %s -- User Agent: %s.</p>',$proxyn, $lastProxy, $agent);
			}
			//printf('<p class="small">%s - User Agent: %s.</p>', $this->url, $agent);
			// Imposta l'URL e altre opzioni
			curl_setopt($ch, CURLOPT_URL, $this->url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_USERAGENT, $agent );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			// Scarica l'URL e lo passa al browser
			$output = curl_exec($ch);
			$info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);// Chiude la risorsa curl
			if ($output === false || $info != 200) {
			 	  $output = '<p class="small">' . $info . "</p>";
			}
			return $output;
		}
	}
}



/*
private function getRandomProxy ( ) {
	//$proxies = array(); // Declaring an array to store the proxy list
	// Adding list of proxies to the $proxies array
	// Choose a random proxy
	if (isset($this->proxyList)) {  // If the $proxies array contains items, then
		$proxy = $this->proxyList[array_rand($this->proxyList)];    // Select a random proxy from the array and assign to $proxy variable
	}
	return $proxy;
}

public function testProxyList(){
	$seq = 3; //count($proxies)-1; Number of proxy I will test
	$proxies = array();
	for($t=0;$t<$seq;$t++){ // I will taake only 3 proxy for test
		$proxies[$t] = $this->getRandomProxy(); // Get a random proxy from list
	}
	array_splice($this->proxyList,0);
	$t=0;
	//foreach ($proxies as $key => $proxy){
	echo '<p>TEST PROXY list <progress id="testprogressbar" value="0" max="100"></progress></p>';
	while($t < $seq){
		echo "<p>" . $proxies[$t] . '</p>';
		$content = false;
		set_time_limit (30);
		$content = $this->getContent("http://officinalab.fr/assistance.xml",$proxies[$t]);
		if ($content != false) {
			$this->proxyList[]= $proxies[$t];
			echo '<p>Proxy ' . $proxies[$t] . ' ok!</p>';
			flush();
			ob_flush();
		}
		$p= round((($t+1)*100)/$seq);
		printf('<script>document.getElementById("testprogressbar").value="%s";</script>',$p);
		flush();
		ob_flush();
		$t++;
	}
	$fileProxy = new filetext("doc/Proxy.txt");
	$list = $this->proxyList;
	$fileProxy->writedata($list,"a");
	unset($fileProxy);
}

		private function getContent($url,$useProxy=true) {

			$agent = $this->getRandomUserAgent();
			if($useProxy===true){ // use anonymous proxy
				$proxy = $this->getRandomProxy();

				$context_options = array(
					'http' => array(
						'user_agent' => $agent,
						'follows_location' => 0,
						'proxy' => $proxy)
				);

				printf("<p>Proxy: %s -- User Agent: %s.</p>", $proxy,$agent);
			} else { // without proxy
				$context_options = array(
					'http' => array(
						'user_agent' => $agent,
						'follows_location' => 0)
				);
			}


			$context = stream_context_create($context_options);
			$content = false;
			try {
				$fp = fopen($url, 'rb', false, $context);
				if (!$fp) {
					throw new Exception("Error on $url, $php_errormsg");
				} else {
					$content = @stream_get_contents($fp);
					if ($content === false) {
						$meta = stream_get_meta_data($fp);
						print_r($meta);
						throw new Exception("Error reading data from $url, $php_errormsg");
					}
				}
			} catch(Exception $e){
				echo '<H4>Exception reçue : </h4><p>',  $e->getMessage(), "</p>";
			}

			if(!is_bool($fp)){
				fclose($fp);
			}

			return $content;
		}
		*/
?>
