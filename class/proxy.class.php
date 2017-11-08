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

if ( !class_exists( 'proxy' ) ) {
	class proxy {

    protected $url = "http://nntime.com/proxy-list-01.htm";
    protected $filename = "doc/Proxy.txt";
    protected static $proxyList = array();
    protected static $lastProxy = "";

    public function __construct() {
      if (file_exists($this->filename) && filesize($this->filename) > 0) {
        $fileProxy = new filetext($this->filename);
        self::$proxyList = $fileProxy->readdata();
        unset($fileProxy);
      }
		}

		public function __destruct() {
      $this->url = null;
			$this->filename = null;
      $this->lastProxy = null;
      unset(self::$proxyList);
		}

    //$move = 0 doesn't use proxy
    //$move = 1 use last proxy
    //$move = 2 use proxy but not last used (use next in list)
    public function getProxy ($move) { //
			//$proxies = array(); // Declaring an array to store the proxy list
			// Adding list of proxies to the $proxies array
			// Choose a random proxy
			if (empty(self::$proxyList)) {  // If the $proxies array contains items, then
				$this->getProxyList();
			}

      if (isset(self::$lastProxy) && !empty(self::$lastProxy)){
        $key = array_search ( self::$lastProxy , self::$proxyList);
        if($move == 2){ // proxy doesn't work correctly go to next
          if( ($key+1) < count(self::$proxyList) ){
            $next = $key +1; //next proxy in list
          }else{
            $next = 0; //restart from firt proxy
          }
          self::$lastProxy = self::$proxyList[$next];
        }
      } else {
        self::$lastProxy = self::$proxyList[0];
      }
			return self::$lastProxy;
		}

/*
    public function proxyListExists(){
    }

    public function setLastProxy($last){
      $this->lastProxy = $last;
    }
    public function getLastProxy(){
      return $this->lastProxy;
    }
*/

    public function getProxyList(){ // load proxy list from nntime
			echo "<p>Get PROXY list from :"  .$this->url . " ";
			echo '<progress id="getpprogressbar" value="0" max="100"></progress></p>';
			flush();
			ob_flush();
      $argo = new argo($this->url);
			$orcontent = $argo->getContent(false,false);
      unset($argo);
      $proxies = array();
			// <tr class="even"><td><input type="checkbox" name="c2" id="row2"  value="20795539540.146.85.19487368688089" onclick="choice()" /></td><td>200.146.85.194<script
			// 20795539540.146.85.19487368688089" onclick="choice()" /></td><td>200.146.85.194<script
			$classes=array("odd","even");
			$tot = $j = $max = 0;
			foreach($classes as $class){
				$tot = $tot + substr_count($orcontent,$class); // Get total of occurancy into web page
			}
			foreach($classes as $class){ // two class, 1 for each line alternatly
				$content = $orcontent; // take original use content var
				$neddle = sprintf('<tr class="%s"><td><input type="checkbox" name="', $class); // search html $neddle
				$lneddle =strlen($neddle);
				while($pos = strpos($content,$neddle)){  // repeat at last found neddle
					$p= round((($j+1)*100)/$tot);
					printf('<script>document.getElementById("getpprogressbar").value="%s";</script>',$p);
					flush();
					ob_flush();
					$content = substr($content,$pos+$lneddle); // del neddle
					$valpos = strpos($content,'value="');		// next first <td>
					$content = substr($content,$valpos+7); // del value="
					$port = substr($content,0, strpos($content,'"')); // = 20795539540.146.85.19487368688089
					$valpos = strpos($content,'<td>');		// next first <td>
					$content = substr($content,$valpos+4);  // del <td>
					$address = substr($content,0, strpos($content,'<script type="text/javascript">')); // 200.146.85.194
					$lastnum = substr($address,strrpos($address,".")+1); // get last nume of ip address = 194
					$lpos = strrpos($port,".") + strlen($lastnum) + 1; //get pos of the end of last number in port string
					$port= substr($port,$lpos); // 87368688089	is	results
					$proxies[$j]=array("address" => $address , "port" =>$port); // safe address ip and port in an array; port is not finaally result, I must extract right number
					if($max < strlen($port)){
						$max = $j;
					}
					$j++;
				}
			}
			// I know that max port is numeber of 4 digit I find selt if I take max lenght nulber i substract last for digits
			$lselt = strlen(substr($proxies[$max]['port'],0,-4)); // get lenght of selt
			array_splice(self::$proxyList,0);
			foreach ($proxies as $key => $proxy){ //correction of port value (sub selt)
				$proxies[$key]['port'] = substr($proxies[$key]['port'], $lselt);
				self::$proxyList[] = 'tcp://'. $proxies[$key]['address'] . ':' . $proxies[$key]['port']; // List of not verified proxy
			}
      $fileProxy = new filetext("doc/Proxy.txt");
    	$fileProxy->writedata(self::$proxyList,"a");
    	unset($fileProxy);
		}

    public function showProxyList(){
			echo "<pre>";
			print_r(self::$proxyList);
			echo"</pre>";
		}
  }
}
?>
