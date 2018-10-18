<?php
/*------------------------------------------------------------------------------
	$Id: class.nd_affiliate_summary.php 64 2011-10-06 16:33:40Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if(!class_exists('nd_affiliate_affiliate')) {
	require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_affiliate.php';
}

class nd_affiliate_summary extends nd_affiliate_affiliate {
	
	function nd_affiliate_summary($affiliate_id = 0) {
		if($affiliate_id > 0) {
			$this->affiliateID = (int)$affiliate_id;
		}
	}
	
	function displaySummary() {
		global $price;
		
		if($this->url_data['aID'] > 0) {
			$this->affiliateID = (int)$this->url_data['aID'];
			$this->affiliateData = $this->_loadAffiliate();
			$this->buildSummary();
		}
		
		$html = '<div style="margin: 20px;">';
		$html .=  '<h2>' . TEXT_ND_AFFILIATE_SUMMARY . '</h2>';
		if($this->url_data['aID']) {
			$html .= '<h3>' . $this->affiliateData['affiliate_firstname'] . ' ' . $this->affiliateData['affiliate_lastname'] . '</h3></div>';
		}
		
		$html .= '<table cellspacing="10" cellpadding="10" align="left" bgcolor="#fff2cc" style="border: 1px solid #ead38e;">';
		if(!$this->url_data['aID']) {
			$html .= '<tr><td align="right">' . AFFILIATE_TEXT_AFFILIATES . ': </td><td>' . $this->affiliatesTotal . '</td></tr>';
		}
		$html .= '<tr><td align="right">' . AFFILIATE_TEXT_IMPRESSIONS . ': </td><td>' . $this->impressionsTotal . '</td></tr>';
		$html .= '<tr><td align="right">' . AFFILIATE_TEXT_CLICKS . ': </td><td>' . $this->clicksTotal . '</td></tr>';
		$html .= '<tr><td align="right">' . AFFILIATE_TEXT_SALES_NUMBERS . ': </td><td>' . $this->salesTotalNumbers . '</td></tr>';
		$html .= '<tr><td align="right">' . AFFILIATE_TEXT_SALES_TOTAL . ': </td><td>' . $price->_StyleFormat($this->salesTotalValue) . '</td></tr>';
		$html .= '<tr><td align="right">' . AFFILIATE_TEXT_AVERAGE . ': </td><td>' . $price->_StyleFormat($this->salesAverage) . '</td></tr>';
		$html .= '<tr><td align="right">' . AFFILIATE_TEXT_CONVERSION . ': </td><td>' . $price->_StyleFormat($this->conversionAverage) . '</td></tr>';
		$html .= '<tr><td align="right">' . AFFILIATE_TEXT_SALES_PROVISION . ': </td><td>' . $price->_StyleFormat($this->provisionTotal) . '</td></tr>';
		$html .= '<tr><td align="right">' . AFFILIATE_TEXT_AVERAGE_PERCENTAGE . ': </td><td>' . $this->provisionAverage . ' %</td></tr>';
		if(defined(AFFILIATE_LEAD_EXIST)) {
			$html .= '<tr><td align="right">' . AFFILIATE_TEXT_LEADS_PROVISION . ': </td><td>' . $price->_StyleFormat($this->leadsTotal) . '</td></tr>';
			$html .= '<tr><td align="right">' . AFFILIATE_TEXT_LEADS_NUMBERS . ': </td><td>' . $this->leadsTotalNumbers . '</td></tr>';
		}
		$html .= '</table>';
		
		return $html;
	}
	
	function buildSummary() {
		$this->getAffiliatesTotal();
		$this->getImpressionsTotal();
		$this->getClicksTotal();
		$this->getSalesTotal();
		$this->getLeadsTotal();
		
		$this->getSalesAverage();
		$this->getConversionAverage();
		$this->getProvisionAverage();
	}
	
	function getAffiliatesTotal () {
		global $db;
		
		if($this->affiliateID > 0) {
			$where = ' WHERE affiliate_id = ' . $this->affiliateID;
		}
		
		$affiliates = $db->Execute("SELECT count(*) as total FROM " . TABLE_AFFILIATE . $where);
		
		$this->affiliatesTotal = $affiliates->fields['total'];
	}
	
	function getImpressionsTotal () {
		global $db;
		
		if($this->affiliateID > 0) {
			$where = ' WHERE inventory_affiliate_id = ' . $this->affiliateID;
		}
		
		$impressions = $db->Execute("SELECT sum(inventory_shown) as total FROM " . TABLE_AFFILIATE_INVENTORY_HISTORY .  $where);
		
		if($impressions->fields['total'] > 0) {
			$this->impressionsTotal = $impressions->fields['total'];
		} else {
			$this->impressionsTotal = 0;
		}
	}
	
	function getClicksTotal () {
		global $db;
		
		if($this->affiliateID > 0) {
			$where = ' WHERE affiliate_id = ' . $this->affiliateID;
		}
		
		$clicks = $db->Execute("SELECT count(*) as total FROM " . TABLE_AFFILIATE_CLICKTHROUGHS . $where);
		
		$this->clicksTotal = $clicks->fields['total'];
	}
	
	function getSalesTotal () {
		global $db;
		
		if($this->affiliateID > 0) {
			$where = ' WHERE a.affiliate_id = ' . $this->affiliateID;
		}
		
		$count = $db->Execute("SELECT count(*) as total FROM " . TABLE_AFFILIATE_SALES . " a " . $where);
		
		$affiliate_sales = $db->Execute("SELECT sum(a.affiliate_value) as total, sum(a.affiliate_payment) as provision, count(*) as total_numbers
										 FROM " . TABLE_AFFILIATE_SALES . " a
										 LEFT JOIN " . TABLE_AFFILIATE . " aa on (aa.affiliate_id = a.affiliate_id )
										 LEFT JOIN " . TABLE_ORDERS . " o on (a.affiliate_orders_id = o.orders_id)
										 LEFT JOIN " . TABLE_SYSTEM_STATUS_DESCRIPTION . " ssd on (o.orders_status = ssd.status_id and ssd.language_code = '" . $language->code . "')" . $where);
		
		$this->salesTotalNumbers = $count->fields['total'];
		$this->salesTotalValue = $affiliate_sales->fields['total'];
		$this->provisionTotal = $affiliate_sales->fields['provision'];
	}
	
	function getSalesAverage () {
		global $price;
		
		if($this->salesTotalNumbers > 0) {
			$this->salesAverage = $this->salesTotalValue / $this->salesTotalNumbers;
		} else {
			$this->salesAverage = 0.00;
		}
	}
	
	function getLeadsTotal () {
		global $db;
		
		if($this->affiliateID > 0) {
			$where = ' WHERE a.affiliate_id = ' . $this->affiliateID;
		}
		
		$affiliate_leads = $db->Execute("SELECT sum(a.affiliate_payment) as provision, count(*) as total_numbers
										 FROM " . TABLE_AFFILIATE_LEADS . " a
										 LEFT JOIN " . TABLE_AFFILIATE . " aa on (aa.affiliate_id = a.affiliate_id )" .
										 $where);
		
		$this->leadsTotalNumbers = $affiliate_leads->fields['total_numbers'];
		$this->leadsTotal = $affiliate_leads->fields['provision'];
	}
	
	function getConversionAverage () {
		global $price;
		
		if($this->clicksTotal > 0) {
			$this->conversionAverage = $this->provisionTotal / $this->clicksTotal;
		} else {
			$this->conversionAverage = 0.00;
		}
	}
	
	function getProvisionAverage () {
		 if($this->salesAverage > 0) {
		 	$this->provisionAverage = round($this->provisionTotal / $this->salesTotalValue * 100 , 2);
		 } else {
			$this->provisionAverage = 0.00;
		 }
	}
}
?>