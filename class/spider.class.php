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
# Description : spider Class     	                									    #
# Requires    : Apache - PHP                                            #
#                 to extract links from a site                          #
#-----------------------------------------------------------------------#
*/

if ( !class_exists( 'spider' ) ) {
	class spider {
    protected $domain;
    protected $page;
    protected $queryString;
    protected $extensionsOk= array("/","","php", "htm", "html", "xhtml", "phtml", "dhtml", "asp", "aspx", "php3", "py", "jsp", "shtml", "rhtml");
		protected $pageExcluded = array("style.php",'403.php','500.php');
		protected static $html;

    public function __construct($domain, $page = '', $queryString = false) {
			$this->domain = $domain;
      $this->page = $page;
      $this->queryString = $queryString;
		}

		public function __destruct() {
      $this->url = null;
      $this->domain = null;
      $this->page = null;
      $this->queryString = null;
      unset($this->pageExcluded);
		}

	  public function crawlRecursive($level=1, $max=0, $listLinks=array(), $linksVisits=array()){
			if(empty($listLinks)){
					$newTabLinks[0] = array($this->page);
			}else {
					$newTabLinks[0] = $listLinks;
			}
      $newTabLinks[1] = $linksVisits;
      $nb=0;
      while($nb < $level){
        if (!empty($newTabLinks[0])) {
          $newTabLinks = $this->extractionRecursive($newTabLinks[0], $newTabLinks[1], $max);
        }
        $nb++;
      }
      if (in_array("/",$newTabLinks[0])) {
        $newTabLinks[0] = array();
      }
      $results = array_merge($newTabLinks[0], $newTabLinks[1]);
      $results = array_unique($results);
      $domain = $this->domain;
      if (!preg_match("#^https?://#iU", $domain)){
        $domain = "http://" . $domain;
      }
      if (substr($domain,0,1) == "/") {
        $domain = substr($domain,1);
      }
      $regex = "#^(https?://.*)/([a-zA-Z0-9_./-]+)$#iu";
      if (preg_match($regex, $domain, $sections)) {
        $domain = $sections[1];
      }
      if (substr($domain, -1, 1) != "/") {
        $domain = $domain . "/";
      }
      foreach ($results as $link) {
        $finalTab[] = $domain.$link;
      }
      $finalTab[] = $domain;
      sort($finalTab);
      return $finalTab;
    }

	  private function extractionRecursive($tabLinks, $linksVisits = array(), $max = 0){
      $ok = false;
			if ($max == 0 || $max == '') {
        $max = count($tabLinks);
      }
      $nb = 0;
			foreach ($tabLinks as $link) {
				if (!in_array($link, $linksVisits) && $link != "" && $nb < $max) {
          $sublist = new spider($this->domain, $link, $this->queryString);
          $newTabLinks[] = $sublist->extractLinks();
					unset($sublist);
          if ($link != "/") {
            $linksVisits[] = $link;
          }
          $ok = true;
        }
        $nb++;
      }
      if ($ok == true && !empty($newTabLinks)) {
				$tab=array();
				foreach ($newTabLinks as $links) {
          foreach ($links as $link) {
            $tab[] =  $link;
          }
        }
        $tab = array_unique($tab);
        $results[0]= array_unique($tab);
        $results[1]= $linksVisits;
        return $results;
      } else {
        $results[0] = $tabLinks;
        $results[1] = $linksVisits;
        return $results;
      }
    }

    private function extractLinks(){
      $domain = $this->domain;
      $page = $this->page;
			$intLinks=array();
			if (!empty($domain)) {
        if (!preg_match("#^https?://#iU", $domain)) {
          $domain = "http://". $domain;
        }
        if(preg_match("#".$domain."#iU", $page)){
          $page = str_ireplace($domain, '', $page);
        }
        if (substr($domain, -1,1) != "/" && substr($page, 0,1) != '/') {
          $url = $domain . "/" . $page;
        }else {
          $url = $domain . $page;
          //$domain = substr($domain, 0 , -1);
        }
				set_time_limit (30);
				$argo = new argo($url);
        $content = $argo->getContent(0,true,true); //$proxy, agent, follow option
				$docHTML = new DOMDocument();
				$docHTML->preserveWhiteSpace = true; // needs to be before loading, to have any effect
				//  load the html into the dom
				@$docHTML->loadHTML( $content);
				$links = $docHTML->getElementsByTagName('a');
				foreach($links as $key => $item){
					$attr = $item->getAttribute('href');
					$domLen = strlen($this->domain);
					$hrefDom = substr ($attr , 0, $domLen );
					//echo $attr;
					if( $this->domain == $hrefDom || substr($attr, 0, 1) == "/" ||  substr($attr, 0, 1) == "."){
						$extension = pathinfo($attr, PATHINFO_EXTENSION);
					  $qsOk = false;
					  if($this->queryString == true && preg_match("#[\?\#\&][^=]+=.*#iU", $extension)){
              $qsOk = true;
            }else {
              $qsOk = false;
            }
					  if ($qsOk == true || in_array($extension, $this->extensionsOk)) {
              $urlValide = true;
            }else {
              $urlValide = false;
            }
					  if ($urlValide == true && !in_array($attr, $this->pageExcluded)) {
              $intLinks[]=$attr;
            } else {
							$intLinks[]=false;
            }
					}
				}
				//echo  htmlentities( $content);
 	      $this->titleMetaCount($content);
			  unset($argo);
				//print_r($intLinks);
      }
			return $intLinks;
    }

		private function removeElementsByTagName($tagName, $document) {
		  $nodeList = $document->getElementsByTagName($tagName);
		  while (($nodes = $document->getElementsByTagName($tagName)) && $nodes->length) {
	        $nodes->item(0)->parentNode->removeChild($nodes->item(0));
	    }
			$document->formatOutput = false;
			return $document->saveHTML( );
		}

		private function titleMetaCount($content){
			$result = $strTotal = $title = '';
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			$docHTML = new DOMDocument();
			$docHTML->preserveWhiteSpace = true; // needs to be before loading, to have any effect
			//  load the html into the dom
			@$docHTML->loadHTML( $content);
			$contentTXT =  $this->removeElementsByTagName('script', $docHTML);
			$contentTXT = $this->removeElementsByTagName('style', $docHTML);
			$contentTXT = $this->removeElementsByTagName('link', $docHTML);
			//	echo "<pre>". htmlentities( $contentTXT) . "</pre>";
			$images = $docHTML->getElementsByTagName('img');
			$nodes = $docHTML->getElementsByTagName('title');
			if(isset($nodes->item(0)->nodeValue) && !empty($nodes->item(0)->nodeValue)){
				$title = $nodes->item(0)->nodeValue;
			}
			//get and display what you need:
			$metas = $docHTML->getElementsByTagName('meta');
			for ($i = 0; $i < $metas->length; $i++)		{
			    $meta = $metas->item($i);
			    if($meta->getAttribute('name') == 'description'){
			        $description = $meta->getAttribute('content');
			 		}
					if($meta->getAttribute('name') == 'keywords'){
						$metaKeywords = $meta->getAttribute('content');
				  }
			}
			$result.= '<section>';
			$header="";
			$nbImg = $nbAlt = 0;
			if(!empty($title)){
				$lenTitle = strlen($title);
				$header .= "<div><b>TITLE: </b><span>". $title . "</span>";
				if($lenTitle < 61) {
						$header .= '<span class="green"> ('.$lenTitle.' chars on 60 visibles)</span>';
				} else {
						$header .= '<span class="red"> ('.$lenTitle.' chars on 60 visibles)</span>';
				}
				$header .= "</div>";
				//$title = $this->cutStr($title);
			}
			if(!empty($description)){
				$lenDesc = strlen($description);
				 $header .= "<div><b>DESCRIPTION: </b><span>". $description . "</span>";
				 if($lenDesc > 0 && $lenDesc < 151 ){
					 $header .= '<span class="green"> ('.$lenDesc . ' chars on 150 maximum conseilled)</span>';
				 } elseif($lenDesc == 0){
					 $header .=  '<span class="red"> (Empty field!)</span>';
				 }else{
					 $header .=  '<span class="red"> ('.$lenDesc.' chars on 150 maximum conseilled)</span>';
				 }
				 $header .=  "</div>";
				 //$description = $this->cutStr($description);
			}
			if(!empty($metaKeywords)){
				$tabWords = explode(" ", $metaKeywords);
				$nbWords = count($tabWords,1);
				$header .=  "<div><b>KEYWORDS: </b><span>". $metaKeywords . "</span>";
				if($nbWords != 0){
					$header .= '<span class="green"> ('.$nbWords . ' keywords)</span>';
				}
				$header .=  "</div>";
			}
			foreach ($images as $image) {
			  $nbImg++;
				if( $image->getAttribute('alt') != ""){
					$nbAlt++;
				} else {
					$header.= "<div>" .$image->getAttribute('src') . "</div>";
				}
			}
			if($nbImg >0){
				$header.= "<div><b>IMAGES: </b><span>".$nbAlt . " ALT attributes on " .   $nbImg . " images</span></div>";
			}
			$t = $this->page;
			if($this->page =="/"){
				$t= $this->domain;
			}
			$result .= '<h2>Audit '.$t.'</h2>';
			$result .= $header;
		  $result .= '<div class="linehead"><b>KEYWORDS OCCURRENCES IN CONTENT (words appears more than 5 times): </b></div>';
			$contentTXT = strip_tags($contentTXT);
			$contentTXT = html_entity_decode($contentTXT, ENT_QUOTES, 'UTF-8');
			//$result .= "<div><pre>".$contentTXT."</pre></div>";
			$cut=$this->cutStr($contentTXT);
			$result .='<div class="tablepdf">';
		  foreach ($cut as $key => $value) {
		    if ($value > 4) {
					 $result .= '<span class="line">'.$key.'<b class="valuemore"> '.$value.'</b></span>';
		    }
				/*
				else {
				 $result .= '<span>'.$key."</span>\n";
		        $result .= '<span class="valueone">'.$value."</span>\n";
		    } */

		  }
		  $result .= "</div></section>";
			self::$html .=$result;
		  return $result;
		}

			private function cutStr($str = "", $sort = array("VALUE","DESC"), $charset='UTF-8'){
			  $chars = "ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜŸ";
			  $chars .="àáâãäåçèéêëìíîïñòóôõöøùúûüÿ";
			  $chars .="0123456789";
				$stopWords = array("est","l","c","s","n","this","ce","ne","au","aux" ,"à", "le", "la", "les", "un", "une", "d", "des", "de", "du", "mais", "ou", "et", "donc", "or", "ni", "car", "se", "en","il","elle","celui-ci","qui","par","pour","que", "afin", "alors", "après", "assez", "travers", "au-dessous", "au-dessus", "aujourd’hui", "auprès", "aussi", "déjà", "depuis", "dessous", "dessus", "devant", "dont", "durant", "encore", "enfin", "non", "parce", "par-dessous", "par-dessus", "parfois", "parmi", "pas", "pendant", "personne", "soudain", "sous", "souvent", "sur", "surtout", "tant", "tantôt",  "tard", "tôt", "toujours", "ailleurs", "ainsi", "dès", "désormais", "dorénavant", "malgré", "mieux", "moindre", "moins", "naguère", "néanmoins", "sans", "sauf", "selon", "seulement", "sinon", "sitôt", "aussitôt", "autant", "autour", "autrefois", "autrement", "avant", "avec", "beaucoup", "bien", "bientôt", "ensuite", "entre", "envers", "exprès", "fois", "hélas", "hier", "ici", "jamais", "là-bas", "peu", "plus", "plusieurs", "plutôt", "pourquoi", "pourtant", "près", "presque", "puis", "toutefois", "très",  "trop", "vers", "voici", "voilà", "vraiment", "auparavant",  "guère", "gré", "pis", "ceci", "cela", "cependant", "chez", "comme", "comment", "dans", "dedans", "dehors", "loin", "longtemps", "lors", "lorsque", "maintenant", "malgré", "quand", "quelquefois", "quoi", "quoique", "certes", "hors", "volontiers", "abord", "davantage","demain","être", "sont", "avoir", "ont", "ces","cookie","son" );
				$deleteValue = array('-','(',')','[',']','{','}','_',"'",'"',"’");
			  $str = mb_strtolower($str, $charset );
			  $tabClean = str_word_count($str, 1, $chars);
			  foreach($tabClean as $key => $value){
					$vallen = strlen($value);
					$value = str_replace("’","'",$value);
					//$value = str_replace("’","'",$value);
					$posap = strpos("'",$value);
					if( $posap != false ){
						$posap++;
						if($vallen == $posap){
							$value="";
						} else {
							$value = substr($value,$posap);
						}
					}
					if ( empty($value) || in_array($value, $stopWords) || in_array($value, $deleteValue) || $vallen < 3 ) {
			      unset($tabClean[$key]);
			    }else {
						$tabClean[$key] = $value;
					}
			  }
			  $nbValues = array_count_values($tabClean);
			  if ($sort[0] == "VALUE" || $sort[0] == "value") {
			    if ($sort[1] == "ASC") {
			      asort($nbValues);
			    }else {
			      arsort($nbValues);
			    }
			  }elseif ($sort[0] == "KEY" || $sort[0] == "key") {
			    if ($sort[1] == "ASC") {
			      ksort($nbValues);
			    }else {
			      krsort($nbValues);
			    }
			  }
			  return $nbValues;
			}

		public function printHTML(){
			$html= html_entity_decode(self::$html);
			echo $html;
		}

		public function getHTML(){
			$html = str_replace("'","&rsquo;",self::$html);
			return $html;
		}


  }
}
