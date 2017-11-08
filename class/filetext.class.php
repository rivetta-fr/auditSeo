<?php
if ( !class_exists( 'filetext' ) ) {
	class filetext {

		protected $filename;
		
		public function __construct($filen ) {
			$this->filename = $filen;
		}

		public function __destruct() {
			$this->filename = null;
		}
		public function readdata(){
			if ($handle = fopen($this->filename, 'r')) {
				$list = array();
				while (($buffer = fgets($handle)) !== false) {
					$list[] = substr($buffer,strpos($buffer,";")+1,-1);
				}
			} else {
				$list = false;
				echo "<h2>Impossible d&apos;ouvrir le fichier ($this->filename)!</h2>";
			}
			return $list;
		}
		public function writedata($data,$accesstype='a'){

			// Dans notre exemple, nous ouvrons le fichier $filename en mode d'ajout
			// Le pointeur de fichier est placé à la fin du fichier
			// c'est là que $somecontent sera placé
			if ($handle = fopen($this->filename, $accesstype)) {
				foreach($data as $key => $info){
					if(is_array ( $info )){
						$string = implode ( ";" , $info );
					}else{
						$string =$info;
					}

					$somecontent = $key . ";" . $string . "\n";
					// Ecrivons quelque chose dans notre fichier.
					if (fwrite($handle, $somecontent) === FALSE) {
						echo "<h2>Impossible d&apos;&eacute;crire dans le fichier ($this->filename)!</h2>";
						exit;
					}
				}
				echo "<h2>L&apos;&eacute;criture dans le fichier ($this->filename) a r&eacute;ussi!</h2>";
				fclose($handle);
			} else {
				echo "<h2>Impossible d&apos;ouvrir le fichier ($this->filename)!</h2>";
			}
		}
	}
}
?>
