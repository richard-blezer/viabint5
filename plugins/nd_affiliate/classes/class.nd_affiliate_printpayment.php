<?php
/*------------------------------------------------------------------------------
	$Id: class.nd_affiliate_printpayment.php 61 2011-10-06 09:43:59Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if(!class_exists('nd_affiliate_payment')) {
	require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_payment.php';
}

if(!class_exists('nd_affiliate_affiliate')) {
	require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_affiliate.php';
}

if(!class_exists('FPDF')) {
	require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/library/fpdf/pdf.php';
}

class nd_affiliate_printpayment extends nd_affiliate_payment {
	
	public $_table = TABLE_AFFILIATE_PAYMENT;
	public $_table_lang = null;
	public $_table_seo = null;
	public $_master_key = 'affiliate_payment_id';
	
	function nd_affiliate_printpayment($payment_id = '') {
		if(!empty($payment_id)) {
			$this->paymentID = $payment_id;
			$this->paymentData = $this->_loadPayment();
		} else {
			$this->paymentData = $_POST;
		}
	}
  	
	function printPayment() {
		global $db;
		
		if($this->url_data['pID'] > 0) {
			$this->paymentID = (int)$this->url_data['pID'];
			$this->paymentData = $this->_loadPayment();
		}
		
		$this->_renderPDF();
		
	  	die();
	}
	
	function _renderPDF() {
		global $printpayment_title, $price;
		
		$affiliate = new nd_affiliate_affiliate($this->paymentData['affiliate_id']);
		
		$printpayment_title = sprintf(AFFILIATE_TEXT_PRINTPAYMENT_TITLE, $this->paymentID);
		
		$pdf = new PDF();
		$pdf->AliasNbPages();
		$pdf->AddPage();
		
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(100, 3, '', 'LTR', 1, 'L');
		$pdf->Cell(100, 8, ' '.$this->paymentData['affiliate_firstname'] . ' ' . $this->paymentData['affiliate_lastname'], 'LR', 1, 'L');
		$pdf->Cell(100, 8, ' '.$this->paymentData['affiliate_street_address'], 'LR', 1, 'L');
		if($this->paymentData['affiliate_suburb'] != '') {
			$pdf->Cell(100, 8, ' '.$this->paymentData['affiliate_suburb'], 'LR', 1, 'L');
		} else {
			$blank = true;
		}
		$pdf->Cell(100, 8, ' '.$this->paymentData['affiliate_postcode'] . ' ' . $this->paymentData['affiliate_city'], 'LR', 1, 'L');
		if($blank) {
			$pdf->Cell(100, 8, '', 'LR', 1, 'L');
		}
		$pdf->Cell(100, 8, '', 'LBR', 1, 'L');
		
		$pdf->Ln(10);
		
		$printpayment_date = date_short($this->paymentData['affiliate_payment_date']);
		$pdf->Cell(0, 10, $printpayment_date, 0, 0, 'R');
		
		$pdf->Ln(10);
		
		$printpayment_header = array('Id', 'Date', 'Orders-ID', 'L', 'T', 'Orders-Value', 'Provision');
		$printpayment_sales = array_merge($this->getSales(), $this->getLeads());
		$pdf->FancyTable($printpayment_header, $printpayment_sales);
		$pdf->Ln();
		
		if($this->paymentData['affiliate_payment_tax'] > 0 && $affiliate->affiliateData['affiliate_vat_entitled'] == 1) {
			$pdf->Cell(140, 6, AFFILIATE_TEXT_SUM_OT, 1, 0, 'R');
			$pdf->Cell(40, 6, $price->_StyleFormat($this->paymentData['affiliate_payment']), 1, 1, 'R');
			$pdf->Cell(140, 6, sprintf(AFFILIATE_TEXT_TAX, round($affiliate->affiliateData['tax_rate'], 2)), 1, 0, 'R');
			$pdf->Cell(40, 6, $price->_StyleFormat($this->paymentData['affiliate_payment_tax']), 1, 1, 'R');
		}

		$pdf->SetFont('', 'B');
		$pdf->Cell(140, 6, AFFILIATE_TEXT_SUM_TOTAL, 1, 0, 'R');
		$pdf->Cell(40, 6, $price->_StyleFormat($this->paymentData['affiliate_payment_total']), 1, 1, 'R');
		
		$pdf->SetFont('', 'I', '8');
		$pdf->Cell(180, 10, 'L = Level  -  T = Type (S = Sale - L = Lead)', 0, 1, 'R');
		
		$pdf->Ln();
		$pdf->Ln();
		
		$pdf->MultiCell(0, 5, utf8_decode(_STORE_EMAIL_FOOTER_TXT), 0, 'L');
		$pdf->Output('', 'I');
	}
	
	function getSales() {
		global $db, $price;
		
		if(!class_exists('nd_affiliate_sales')) {
			require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_sales.php';
		}
		
		$record = $db->Execute("SELECT affiliate_sales_id FROM " . TABLE_AFFILIATE_SALES . " WHERE affiliate_payment_id = " . $this->paymentID . " ORDER BY affiliate_sales_id");
		$sales = array();
		while(!$record->EOF) {
			$sale = new nd_affiliate_sales($record->fields['affiliate_sales_id']);
			
			$sale->saleData['affiliate_date'] = date_short($sale->saleData['affiliate_date']);
			$sale->saleData['affiliate_value'] = $price->_StyleFormat($sale->saleData['affiliate_value']);
			$sale->saleData['affiliate_payment'] = $price->_StyleFormat($sale->saleData['affiliate_payment']);
			
			$sales[] = array($sale->saleID, $sale->saleData['affiliate_date'], $sale->saleData['affiliate_orders_id'], $sale->saleData['affiliate_level'], 'S', $sale->saleData['affiliate_value'], $sale->saleData['affiliate_payment']);
			
			$record->MoveNext();
		}
		
		return $sales;
	}
	
	function getLeads() {
		global $db, $price;
		
		if(!class_exists('nd_affiliate_leads')) {
			require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_leads.php';
		}
		
		$record = $db->Execute("SELECT affiliate_leads_id FROM " . TABLE_AFFILIATE_LEADS . " WHERE affiliate_payment_id = " . $this->paymentID . " ORDER BY affiliate_leads_id");
		$leads = array();
		while(!$record->EOF) {
			$lead = new nd_affiliate_leads($record->fields['affiliate_leads_id']);
			
			$lead->leadData['affiliate_date'] = date_short($lead->leadData['affiliate_date']);
			$lead->leadData['affiliate_value'] = $price->_StyleFormat($lead->leadData['affiliate_value']);
			$lead->leadData['affiliate_payment'] = $price->_StyleFormat($lead->leadData['affiliate_payment']);
			
			$leads[] = array($lead->leadID, $lead->leadData['affiliate_date'], ' - ', $lead->leadData['affiliate_level'], 'L', $lead->leadData['affiliate_lead_target'], $lead->leadData['affiliate_payment']);
			
			$record->MoveNext();
		}
		
		return $leads;
	}
}
?>