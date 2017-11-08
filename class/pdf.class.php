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
# Description : PDF class 												#
# Requires    : Apache - PHP                                            #
#                                                                       #
#-----------------------------------------------------------------------#
*/

require_once('TCPDF.class.php');
//include_once('tcpdf/tcpdf_include.php');// Include the main TCPDF library (search for installation path).
if ( !class_exists( 'pdf' ) ) {
	class pdf extends TCPDF {
		protected $filename;
		protected $title = 'Audit Site';
		protected $htmlHeader;
		protected $author ="Rivetta.fr";
		protected $linkAuthor ="http://rivetta.fr";
		protected $addressAuthor = "2 rue de la cour Rosières - 88700 - PADOUX - VOSGES - France";
		public function __construct($filename,$html,$title='Audit Site',$orientation=PDF_PAGE_ORIENTATION) {
			//$companyInfo = $es->get_companyInfo();
			$this->filename = $filename;
			$this->title = $title;
		//	$this->htmlHeader =			'<table><tr><td><a href="http://rivetta.fr" target="_blank"><img src="images/cr.jpg" alt="Rivetta.pdf"></a></td><td><p>Audit INTERNT SITE: '. $title . '</p><p>by<a href="http://rivetta.fr" target="_blank">Rivetta.fr</a></p></td></tr></table>';
		$this->htmlHeader =	sprintf('AUDIT INTERNET SITE: %s<br>by <a href="%s" target="_blank">%s</a>', $this->title,$this->linkAuthor, $this->author);

			// create new PDF document
			//pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			@parent::__construct($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			// set document information
			$this->SetCreator(PDF_CREATOR);
			$this->SetAuthor('Rivetta.fr');
			$this->SetTitle($this->title);
			$this->SetSubject($this->title);
			$this->SetKeywords('PDF');
			// set default header data
			$this->SetHeaderData(PDF_HEADER_LOGO,16, "AUDIT INTERNET SITE: ".$this->title .'by Rivetta.fr'); // title == domain
			// set header and footer fonts
			$this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			$this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
			// set default monospaced font
			$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			// set margins
			$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
			$this->SetHeaderMargin(PDF_MARGIN_HEADER);
			$this->SetFooterMargin(PDF_MARGIN_FOOTER);
			// set auto page breaks
			$this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
			// set image scale factor
			$this->setImageScale(PDF_IMAGE_SCALE_RATIO);
			// set some language-dependent strings (optional)
			/*
			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
				require_once(dirname(__FILE__).'/lang/eng.php');
				$this->setLanguageArray($l);
			} */
			// set font
			$this->SetFont('helvetica', 'B', 10);
			// add a page
			$this->AddPage();
			//$this->Header();
			//$this->Write(0, $title, '', 0, 'C', true, 0, false, false, 0);
			$this->SetFont('helvetica', '', 12);
			$this->SetY(30);
			$this->writeHTML($html, true, false, false, false, '');
		}

		public function __destruct(){
			@parent::__destruct();
		}

		// Page footer
		public function Footer() {
			// Set font
			$this->SetFont('helvetica', 'I', 8);
			// Position at 15 mm from bottom
			$this->SetY(-15);
			// Page number
			$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
			$this->SetFont('helvetica', '', 6);
			$this->SetY(-10);
			// Page address
			$footer = sprintf("%s %s", $this->author,$this->addressAuthor);  //footer
			$this->Cell(0, 10,$footer, 0, false, 'C', 0, '', 0, false, 'T', 'M');
		}


    public function setHtmlHeader($htmlHeader) {
        $this->htmlHeader = $htmlHeader;
    }

		 public function Header() {
        // Logo
        $image_file = 'images/cr.jpg';
        $this->Image($image_file, 16, 8, 16, '', 'JPG', $this->linkAuthor, 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 12);
        // Title
				$this->writeHTMLCell(
	            $w = 0, $h = 0, $x = '40', $y = '8',
	            $this->htmlHeader, $border = 0, $ln = 1, $fill = 0,
	            $reseth = true, $align = 'top', $autopadding = false);
			$txt = sprintf('<a href="%s">%s</a><br>%s', $this->linkAuthor,$this->author,$this->addressAuthor);
			$this->SetFont('helvetica', '', 8);
			$this->writeHTMLCell(
						$w = 100, $h = 0, $x = '165', $y = '8',
						$txt, $border = 0, $ln = 1, $fill = 0,
						$reseth = true, $align = 'R', $autopadding = false);
				  // Logo
					//Image( $file, $x = '', $y = '', $w = 0, $h = 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false, $alt = false, $altimgs = array() )
	        $image_file = 'images/qr.jpg';
	        $this->Image($image_file, 267, 6, 16, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);
        //$this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }


		public function show($typeoutput='I'){
			// Close and output PDF document
			// This method has several options, check the source code documentation for more information.
			if(!empty($this->filename)){
				$filename= $this->filename;
			} else {
				$filename = "../doc/audit.pdf";
			}
			date_default_timezone_set('Europe/Paris');
			$this->Output($filename, $typeoutput);
		}
	}
}
?>
