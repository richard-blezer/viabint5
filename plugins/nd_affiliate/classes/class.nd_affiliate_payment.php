<?php
/*------------------------------------------------------------------------------
	$Id: class.nd_affiliate_payment.php 71 2011-10-18 16:33:28Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class nd_affiliate_payment {
	
	public $_table = TABLE_AFFILIATE_PAYMENT;
	public $_table_lang = null;
	public $_table_seo = null;
	public $_master_key = 'affiliate_payment_id';
	var $period = '';
	var $entriesPerPage = 25;
	var $paymentID = '';
	var $paymentData = array();
	
	function nd_affiliate_payment ($payment_id = '') {
		global $db;
		
		if(!empty($payment_id)) {
			$this->paymentID = $payment_id;
			$this->paymentData = $this->_loadPayment();
		} else {
			return false;
		}
	}
	
	function _loadPayment() {
		global $db;
		
		$record = $db->Execute("SELECT *
								FROM " . TABLE_AFFILIATE_PAYMENT . "
								WHERE affiliate_payment_id='" . $this->paymentID . "'");
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				$data = $record->fields;
				
				$record->MoveNext();
			}
			return $data;
		}else{
			return false;
		}
	}
	
	function buildPeriodSelector ($affiliate_id) {
		global $db;
		
		$start = $db->Execute("SELECT MONTH(affiliate_date_account_created) as start_month, YEAR(affiliate_date_account_created) as start_year FROM " . TABLE_AFFILIATE . " WHERE affiliate_id = " . $affiliate_id . "");
		
		$return_array = array(array('id' => '', 'text' => AFFILIATE_TEXT_ALL));

		for($period_year = $start->fields['start_year']; $period_year <= date("Y"); $period_year++ ) {
			for($period_month = 1; $period_month <= 12; $period_month++ ) {
				if ($period_year == $start->fields['start_year'] && $period_month < $start->fields['start_month']) continue;
				if ($period_year ==  date("Y") && $period_month > date("m")) continue;
					$return_array[] = array( 'id' => $period_year . '-' . $period_month, 'text' => $period_year . '-' . $period_month) ;
			}
		}
		
		return $return_array;
	}
	
	function getPayments ($affiliate_id) {
		global $db, $language, $store_handler;
		
		if($this->period != '') {
			$period_split = split('-', $this->period);
			$period_clause = " AND YEAR(p.affiliate_payment_date) = " . $period_split[0] . " and MONTH(p.affiliate_payment_date) = " . $period_split[1];
		}
		
		if(AFFILIATE_AFFILIATE_GLOBAL == 'false') {
			$shop_clause = " AND p.affiliate_shop_id = '" . $store_handler->shop_id . "'";
		}

		$affiliate_payment = "SELECT p.*, " . $db->SQLDate('d.m.Y', 'p.affiliate_payment_date') . " as a_date, s.affiliate_payment_status_name
								FROM " . TABLE_AFFILIATE_PAYMENT . " p, " . TABLE_AFFILIATE_PAYMENT_STATUS . " s 
								WHERE p.affiliate_payment_status = s.affiliate_payment_status_id 
								AND s.affiliate_language_code = '" . $language->code . "'
								AND p.affiliate_id =  '" . $affiliate_id . "'
								" . $period_clause . "
								" . $shop_clause . "
								ORDER BY p.affiliate_payment_id DESC";
		
		$affiliate_payment_split = new split_page($affiliate_payment, $this->entriesPerPage);
		
		return $affiliate_payment_split;
	}
	
	function getTotal ($affiliate_id) {
		global $db, $store_handler;
		
		if(AFFILIATE_AFFILIATE_GLOBAL == 'false') {
			$shop_clause = " AND affiliate_shop_id = '" . $store_handler->shop_id . "'";
		}
		
		$affiliate_payment = $db->Execute("SELECT sum(affiliate_payment_total) as total 
											FROM " . TABLE_AFFILIATE_PAYMENT . "
											WHERE affiliate_id = '" . $affiliate_id . "'" . $shop_clause);
		
		if($affiliate_payment->RecordCount() > 0) {
			return $affiliate_payment->fields['total'];
		} else {
			return false;
		}
	}
	
	function buildOrderStatusQuery () {
		$orders_status = explode(';', AFFILIATE_PAYMENT_ORDER_STATUS);

		$where = '';
		
		if(is_array($orders_status)) {
			$sizeof = sizeof($orders_status);
			$where .= ' AND (';
			for($i=0; $i<sizeof($orders_status); $i++) {
				if(is_numeric($orders_status[$i])) {
					$where .= 'o.orders_status = ' . $orders_status[$i];
				} else {
					if($i > 0) {
						$where = substr($where, 0, -4);
					}
				}
				$sizeof--;
				if($sizeof > 0) {
					$where .= ' OR ';
				} else {
					$where .= ')';
				}
			}
		}
		
		return $where;
	}
	
	function processPayments () {
		global $db, $store_handler, $price;
		
		@set_time_limit(0);
		
		$i = 0;
		$affiliateArray = array();
		
		// get the min-Age of the order
		$time = time() - AFFILIATE_BILLING_TIME * 24 * 60 * 60;
		$oldday = date("Y-m-d H:i:s", $time);
		
		if(AFFILIATE_AFFILIATE_GLOBAL == 'false') {
			//$shop_clause = " AND a.affiliate_shop_id = '" . $store_handler->shop_id . "'";
			$shop_clause = ", a.affiliate_shop_id";
		}
		
		// Select all order-earnings for affiliates
		$affiliatesSales = $db->Execute("SELECT a.*, sum(a.affiliate_payment) as summe
									FROM " . TABLE_AFFILIATE_SALES . " a
									LEFT JOIN " . TABLE_ORDERS . " o ON a.affiliate_orders_id = o.orders_id
									WHERE a.affiliate_billing_status != 1" .
									$this->buildOrderStatusQuery() . "
									AND a.affiliate_date <= '" . $oldday . "'
									GROUP by a.affiliate_id" . $shop_clause);
									
		while (!$affiliatesSales->EOF) {
			$affiliateArray[$affiliatesSales->fields['affiliate_id']][$affiliatesSales->fields['affiliate_shop_id']] = array('summe' => $affiliatesSales->fields['summe']);
			$affiliatesSales->MoveNext();
		}
		
		// Select all leads-earnings for affiliates
		$affiliatesLeads = $db->Execute("SELECT a.*, sum(a.affiliate_payment) as summe
									FROM " . TABLE_AFFILIATE_LEADS . " a
									WHERE a.affiliate_billing_status != 1
									AND a.affiliate_date_valid <= '" . date("Y-m-d H:i:s", time()) . "'
									GROUP by a.affiliate_id" . $shop_clause);
		
		while (!$affiliatesLeads->EOF) {
			$affiliateArray[$affiliatesLeads->fields['affiliate_id']][$affiliatesLeads->fields['affiliate_shop_id']] = array('summe' => $affiliatesLeads->fields['summe']+$affiliateArray[$affiliatesLeads->fields['affiliate_id']][$affiliatesLeads->fields['affiliate_shop_id']]['summe']);
			$affiliatesLeads->MoveNext();
		}
		
		// Start Billing:
		
		$billingArray = array();
		
		foreach($affiliateArray as $affiliate_id => $shops) {
			
			// Get need tax informations for the affiliate
			require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.'nd_affiliate/classes/class.nd_affiliate_affiliate.php');
			$affiliate = new nd_affiliate_affiliate($affiliate_id);
			
			if($affiliate->affiliateData['affiliate_status'] == 0) {
				continue;
			}
			
//			$tax_rates = $db->Execute("SELECT tr.tax_rate
//									   FROM " . TABLE_TAX_RATES . " tr, " . TABLE_COUNTRIES . " c
//									   WHERE tr.tax_zone_id = c.zone_id
//									   AND tr.tax_class_id = 1
//									   AND c.countries_iso_code_2 = '" . $affiliate->affiliateData['affiliate_country_code'] . "'");
//			
//			$tax_rate = $tax_rates->fields['tax_rate'];
				
			foreach($shops as $shop_id => $value) {
				
				// skip the billing if sum is less than threshold
				if($value['summe'] < AFFILIATE_THRESHOLD) continue;
				
				$shop_clause = '';
				if(AFFILIATE_AFFILIATE_GLOBAL == 'false') {
					$shop_clause = " AND a.affiliate_shop_id = '" . $shop_id . "'";
				}
				
				// Get all orders which are AFFILIATE_BILLING_TIME days old
				$orders = $db->Execute("SELECT a.affiliate_orders_id
									    FROM " . TABLE_AFFILIATE_SALES . " a
									    LEFT JOIN " . TABLE_ORDERS . " o ON a.affiliate_orders_id = o.orders_id
									    WHERE a.affiliate_billing_status!=1 " . 
										$this->buildOrderStatusQuery() . 
										$shop_clause . "
										AND a.affiliate_id='" . $affiliate_id . "'
										AND a.affiliate_date <= '" . $oldday . "'");
										
				$orders_id ="(";
				while (!$orders->EOF) {
					$orders_id .= $orders->fields['affiliate_orders_id'] . ",";
					$orders->MoveNext();
				}
				$orders_id = substr($orders_id, 0, -1) .")";
				
				// mark as temporarily processed
				$db->Execute("UPDATE " . TABLE_AFFILIATE_SALES . " a
						  	  SET a.affiliate_billing_status = '99'
						  	  WHERE a.affiliate_id = '" .  $affiliate_id . "'
						  	  AND a.affiliate_orders_id in " . $orders_id . "");
				$db->Execute("UPDATE " . TABLE_AFFILIATE_LEADS . " a
							  SET a.affiliate_billing_status = '99'
							  WHERE a.affiliate_id = '" . $affiliate_id . "'
							  AND a.affiliate_date_valid <= '" . date("Y-m-d H:i:s", time()) . "'
							  " . $shop_clause . "");
				
				// get the sum of the temporarily marked sales for payout
				$payment = $db->Execute("SELECT sum(affiliate_payment) as affiliate_payment
									     FROM " . TABLE_AFFILIATE_SALES . "
									     WHERE affiliate_id = '" .  $affiliate_id . "'
									     AND  affiliate_billing_status = 99");
				
				// get all Leads
				$leads = $db->Execute("SELECT sum(affiliate_payment) as affiliate_payment
									   FROM " . TABLE_AFFILIATE_LEADS . "
									   WHERE affiliate_id = '" . $affiliate_id . "'
									   AND affiliate_billing_status = 99");
				
				if($affiliate->affiliateData['affiliate_vat_entitled'] == 1) {
					// USt. ausweisbar
					$totalEarning = $payment->fields['affiliate_payment'] + $leads->fields['affiliate_payment'];
					$affiliate_tax = round(($totalEarning*($affiliate->affiliateData['tax_rate']/100)), 2);
				} else {
					// Unternehmer ohne USt. Ausweis oder Privatperson
					$totalEarning = $payment->fields['affiliate_payment'] + $leads->fields['affiliate_payment'];
					$affiliate_tax = 0.00;
				}
				
				// Insert a new payment
				$sql_data_array = array('affiliate_id' => $affiliate_id,
										'affiliate_payment' => $totalEarning,
										'affiliate_payment_tax' => $affiliate_tax,
										'affiliate_payment_total' => $totalEarning + $affiliate_tax,
										'affiliate_payment_date' => $db->BindTimestamp(time()),
										'affiliate_payment_status' => '0',
										'affiliate_shop_id' => $shop_id,
										'affiliate_firstname' => $affiliate->affiliateData['affiliate_firstname'],
										'affiliate_lastname' => $affiliate->affiliateData['affiliate_lastname'],
										'affiliate_street_address' => $affiliate->affiliateData['affiliate_street_address'],
										'affiliate_suburb' => $affiliate->affiliateData['affiliate_suburb'],
										'affiliate_city' => $affiliate->affiliateData['affiliate_city'],
										'affiliate_postcode' => $affiliate->affiliateData['affiliate_postcode'],
										'affiliate_country_code' => $affiliate->affiliateData['affiliate_country_code'],
										'affiliate_company' => $affiliate->affiliateData['affiliate_company'],
										'affiliate_address_format_id' => '0',
										'affiliate_last_modified' => $db->BindTimestamp(time()));
										
				$db->AutoExecute(TABLE_AFFILIATE_PAYMENT, $sql_data_array, 'INSERT');
				
				$i++;
				
				$this->paymentID = $db->Insert_ID();
				
				$billingArray[] = array('name' =>$affiliate->affiliateData['affiliate_firstname'] . ' ' . $affiliate->affiliateData['affiliate_lastname'] . ' (' . $affiliate_id . ')', 'amount' => $totalEarning);
				
				// Mark all processed sales to payed
				$db->Execute("UPDATE " . TABLE_AFFILIATE_SALES . "
							  SET affiliate_payment_id = '" . $this->paymentID . "',
							  	  affiliate_billing_status = '1',
							  	  affiliate_payment_date = '" . $db->BindTimestamp(time()) . "'
							  WHERE affiliate_id = '" . $affiliate_id . "'
							  AND affiliate_billing_status = 99");
				// Mark all processed leads to payed
				$db->Execute("UPDATE " . TABLE_AFFILIATE_LEADS . "
							  SET affiliate_payment_id = '" . $this->paymentID . "',
							  	  affiliate_billing_status = '1',
							  	  affiliate_payment_date = '" . $db->BindTimestamp(time()) . "'
							  WHERE affiliate_id = '" . $affiliate_id . "'
							  AND affiliate_billing_status = 99");
				
				if (AFFILIATE_NOTIFY_AFTER_BILLING == 'true') {
					$this->_sendPaymentMail($affiliate_id);
				}
			}
		}
				
		$return = '<table border="0" cellpadding="5" cellspacing="5">';
		$return .= '<tr><td>' . $i . ' Payments processed</td></tr>';
		
		$return .= '<tr><td><table border="1" cellpadding="3" cellspacing="3">';
		foreach($billingArray as $billing) {
			$return .= '<tr><td>' . $billing['name'] . '</td><td>' . $price->_StyleFormat($billing['amount']) . '</td></tr>';
		}
		$return .= '</table></td></tr>';
		
		$return .= '<tr><td>Sie k&ouml;nnen diesen Tab nun schliessen<br />You can close this tab now</td></tr>';
		if($i > 0) {
			$return .= '<tr><td>Bitte bet&auml;tigen Sie im Tab "Auszahlungen" den Button "Neu Laden"<br />Please push the button "reload" in the "Payment"-Tab</td></tr>';
		}
		$return .= '</table>';
		
		return $return;
	}
	
	function _sendPaymentMail ($affiliate_id) {
		global $db, $store_handler, $xtLink;
		
		require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'nd_affiliate/classes/class.nd_affiliate_affiliate.php';
		$affiliate = new nd_affiliate_affiliate($affiliate_id);
		
		$payment = $this->_loadPayment();

		$mail = new xtMailer('nd_affiliate_payment');
		$mail->_addReceiver($affiliate->affiliateData['affiliate_email_address'], $affiliate->affiliateData['affiliate_firstname'] . ' ' . $affiliate->affiliateData['affiliate_lastname']);
		$mail->_assign('name', $affiliate->affiliateData['affiliate_firstname'] . ' ' . $affiliate->affiliateData['affiliate_lastname']);
		$mail->_assign('payment_id', $this->paymentID);
		$mail->_assign('payment_date', $payment['payment_date']);
		$mail->_assign('payment_url', $xtLink->_link(array('page' => 'affiliate_payment', 'params' => 'pID=' . $this->paymentID), 'xtAdmin/'));
		$mail->_sendMail();
	}
	
	function _sendPaymentUpdateMail ($affiliate_id) {
		global $db, $store_handler, $xtLink;
		
		require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'nd_affiliate/classes/class.nd_affiliate_affiliate.php';
		$affiliate = new nd_affiliate_affiliate($affiliate_id);
		
		$payment = $this->_loadPayment();

		$mail = new xtMailer('nd_affiliate_payment_update');
		$mail->_addReceiver($affiliate->affiliateData['affiliate_email_address'], $affiliate->affiliateData['affiliate_firstname'] . ' ' . $affiliate->affiliateData['affiliate_lastname']);
		$mail->_assign('name', $affiliate->affiliateData['affiliate_firstname'] . ' ' . $affiliate->affiliateData['affiliate_lastname']);
		$mail->_assign('payment_status', $payment['payment_status']);
		$mail->_assign('payment_id', $this->paymentID);
		$mail->_assign('payment_date', $payment['payment_date']);
		$mail->_assign('payment_url', $xtLink->_link(array('page' => 'affiliate_payment', 'params' => 'pID=' . $this->paymentID), 'xtAdmin/'));
		$mail->_sendMail();
	}
	
	function setPosition ($position) {
		$this->position = $position;
	}
	
	function _getParams() {
		global $language;
		
		$header['affiliate_payment_id'] = array('type' => 'hidden');
		$header['affiliate_old_payment_status'] = array('type' => 'hidden');
		$header['affiliate_payment_date'] = array('type' => 'date');
		
		$header['affiliate_country_code'] = array('type' => 'dropdown',
												  'url'  => 'DropdownData.php?get=countries');
												  
		$header['affiliate_shop_id'] = array('type' => 'dropdown', 								
											 'url'  => 'DropdownData.php?get=stores');
				
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
		$params['SortField']      = $this->_master_key;
		$params['SortDir']        = "DESC";
		
		$rowActions[] = array('iconCls' => 'nd_affiliate_printpayment', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_AFFILIATE_PRINTPAYMENT);
		if ($this->url_data['edit_id']) {
        	$js = "var edit_id = ".$this->url_data['edit_id'].";\n";
        } else {
        	$js = "var edit_id = record.id;\n";
        }
        $js .= "window.open('" . str_replace('/xtAdmin/adminHandler.php', '', $_SERVER['PHP_SELF']) . "/nd_affiliate_printpayment.php?pID='+edit_id, 'PDF', 'width=600,height=400,scrollbars=yes');";
		$rowActionsFunctions['nd_affiliate_printpayment'] = $js;
		
		$params['rowActions']             = $rowActions;
		$params['rowActionsFunctions']    = $rowActionsFunctions;
		
		if(!isset($this->url_data['aID'])) {
			$extF = new ExtFunctions();
			$js = $extF->_RemoteWindow("TEXT_AFFILIATE_PROCESS_PAYMENT","TEXT_AFFILIATE_PROCESS_PAYMENT","adminHandler.php?plugin=nd_affiliate&load_section=nd_affiliate_payment&pg=processPayments", '', array(), 800, 600).' new_window.show();';
			$UserButtons['process'] = array('text'=>'TEXT_AFFILIATE_PROCESS_PAYMENT', 'style'=>'process', 'icon'=>'arrow_inout.png', 'acl'=>'edit', 'stm'=>$js);
			$params['display_processBtn']  = true;
			$params['UserButtons']           = $UserButtons;
		}
		
		$params['display_newBtn'] = false;

		if($this->url_data['edit_id']) {
			$params['exclude'] = array('affiliate_payment_last_modified',
									   'affiliate_address_format_id');
		} else {
			$params['include'] = array('affiliate_payment_id',
									   'affiliate_id',
									   'affiliate_payment_total',
									   'affiliate_payment_date',
									   'affiliate_payment_status',
									   'affiliate_shop_id',
									   'affiliate_firstname',
									   'affiliate_lastname');
		}

		return $params;
	}
	
	function _get($ID = 0) {
		global $db, $language;

		if ($this->position != 'admin') return false;
		
		$obj = new stdClass;
		
		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}
		
		if(isset($this->url_data['aID'])) {
			$where = 'affiliate_id=' . (int)$this->url_data['aID'];
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $where, $this->sql_limit);

		if ($this->url_data['get_data']) {
			$data = $table_data->getData();
		} elseif($this->url_data['edit_id']) {
			$data = $table_data->getData($this->url_data['edit_id']);
			$this->paymentID = $data[0]['affiliate_payment_id'];
			$data[0]['affiliate_old_payment_status'] = $data[0]['affiliate_payment_status'];
		} else {
			$data = $table_data->getHeader();
		}

        if($table_data->_total_count!=0 || !$table_data->_total_count) {
			$count_data = $table_data->_total_count;
		} else {
			$count_data = count($data);
		}

        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
	}
	
	function _set($data, $set_type = 'edit') {
		global $db,$language,$filter;
		
		(!$data['affiliate_payment_status']) ? $data['affiliate_payment_status'] = 0 : $data['affiliate_payment_status'] = 1;
		(!$data['affiliate_old_payment_status']) ? $data['affiliate_old_payment_status'] = 0 : $data['affiliate_old_payment_status'] = 1;

		if($data['affiliate_old_payment_status'] != $data['affiliate_payment_status']) {
			if(AFFILIATE_NOTIFY_AFTER_BILLING == 'true') {
				$this->_sendPaymentUpdateMail($data['affiliate_id']);
				$affiliate_notified = 1;
			} else {
				$affiliate_notified = 0;
			}
			
			// Insert a new payment-history
			$sql_data_array = array('affiliate_payment_id' => $data['affiliate_payment_id'],
									'affiliate_new_value' => $data['affiliate_payment_status'],
									'affiliate_old_value' => $data['affiliate_old_payment_status'],
									'affiliate_date_added' => $db->BindTimestamp(time()),
									'affiliate_notified' => $affiliate_notified);
									
			$db->AutoExecute(TABLE_AFFILIATE_PAYMENT_STATUS_HISTORY, $sql_data_array, 'INSERT');
		}
		
		$obj = new stdClass;
		$oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$obj = $oC->saveDataSet();
		
		return $obj;
	}
	
	function _unset($id = 0) {
		global $db;
		
		if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if(!is_int($id)) return false;

	    $db->Execute("DELETE FROM " . $this->_table . " WHERE " . $this->_master_key . " = " . $id);
	    $db->Execute("DELETE FROM " . TABLE_AFFILIATE_PAYMENT_STATUS_HISTORY . " WHERE " . $this->_master_key . "=" . $id);
	}
}
?>