<?php
/*------------------------------------------------------------------------------
	$Id: pdf.php 61 2011-10-06 09:43:59Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

require('fpdf.php');

class PDF extends FPDF {
	
	function Header() {
		global $printpayment_title;
		// Logo
		$img_size = getimagesize(_SRV_WEBROOT . 'media/logo/' . _STORE_LOGO);
		$width = ($img_size[0]/72*25.4);
		$height = ($img_size[1]/72*25.4);
		$faktor = 30 / $height;
		$this->Image(_SRV_WEBROOT . 'media/logo/' . _STORE_LOGO, round(200-$width * $faktor, 0), 6, 0, 30);
		$this->SetFont('Arial','B',15);
		$this->Cell(80);
		$this->Cell(30,10,$printpayment_title,0,0,'C');
		$this->Ln(20);
	}
	
	function Footer() {
		$this->SetY(-15);
		$this->SetFont('Arial','I',8);
		$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
	
	function FancyTable($header, $data) {
		// Colors, line width and bold font
		$this->SetFillColor(100,100,100);
	    $this->SetTextColor(255);
	    $this->SetDrawColor(0,0,0);
	    $this->SetLineWidth(.3);
	    $this->SetFont('','B');
	    // Header
	    $w = array(10, 30, 30, 15, 15, 40, 40);
	    $a = array('C', 'L', 'C', 'C', 'C', 'R', 'R');
	    for($i=0;$i<count($header);$i++) {
	        $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
	    }
	    $this->Ln();
	    // Color and font restoration
	    $this->SetFillColor(230,230,230);
	    $this->SetTextColor(0);
	    $this->SetFont('');
	    // Data
	    $fill = false;
	    foreach($data as $row) {
	    	for($i=0;$i<count($header);$i++) {
	    		$this->Cell($w[$i],6,$row[$i],'LR',0,$a[$i],$fill);
	    	}
	        $this->Ln();
	        $fill = !$fill;
	    }
	    // Closing line
	    $this->Cell(array_sum($w),0,'','T');
	}
}