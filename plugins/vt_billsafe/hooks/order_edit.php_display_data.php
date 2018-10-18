<?php
	if($this->order_data['order_data']['payment_code'] == 'vt_billsafe') {
		// Billsafe Log
		global $db, $price;

		$rs = $db->Execute("SELECT * FROM ".DB_PREFIX."_plg_vt_billsafe_log WHERE orders_id=? ORDER BY log_date ASC",array((int)$this->order_data['order_data']['orders_id']));

		$index = 0;

		if($rs->RecordCount()>0) {
			foreach($rs as $val) {
				$billsafe_log['billsafe_log'][$index]['log_id'] = $val['log_id'];
				$billsafe_log['billsafe_log'][$index]['log_type'] = $val['log_type'];

				if($val['log_type']=='voucher' || $val['log_type']=='reversalVoucher') {
					$amount = $db->GetOne("SELECT orders_total_price FROM ".TABLE_ORDERS_TOTAL." WHERE orders_total_id=?",array($val['products_id']));
					$tax_class = $db->GetOne("SELECT orders_total_tax_class FROM ".TABLE_ORDERS_TOTAL." WHERE orders_total_id=?",array($val['products_id']));
					$billsafe_log['billsafe_log'][$index]['products_name'] = number_format(round(($price->_BuildPrice($amount, $tax_class, 'show')*-1), 2), 2, ',', '');
				} elseif($val['log_type']=='directPayment')
					$billsafe_log['billsafe_log'][$index]['products_name'] = '';
				elseif($val['log_type']=='retoure')
					$billsafe_log['billsafe_log'][$index]['products_name'] = $val['products_id'];
				else
					$billsafe_log['billsafe_log'][$index]['products_name'] = $db->GetOne("SELECT products_name FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_products_id=?",array($val['products_id']));

				$billsafe_log['billsafe_log'][$index]['products_quantity'] = $val['products_quantity'];
				$billsafe_log['billsafe_log'][$index]['log_text'] = constant('VT_BILLSAFE_LOG_TYPE_' .  strtoupper($val['log_type']));
				$billsafe_log['billsafe_log'][$index]['log_date'] = $val['log_date'];

				if($val['log_type']=='shipping') {
					$sr_qty = $db->GetOne("SELECT SUM(products_quantity) FROM ".DB_PREFIX."_plg_vt_billsafe_log WHERE log_type='retoure' AND products_id=?",array($val['log_id']));
					$billsafe_log['billsafe_log'][$index]['shipping_retourned'] = ($sr_qty=='')?0:$sr_qty;
				}

				$index++;
			}
		} else {
			$billsafe_log['billsafe_log']['status'] = "false";
		}

		$tpl_data = array_merge($tpl_data, $billsafe_log);

		// Taxes
		require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.tax_class.php';
		$t = new tax_class();
		$_data = $t->_getTaxClassList();
		$tx['taxes'][0] = TEXT_EMPTY_SELECTION;

		foreach ($_data as $tdata)
			$tx['taxes'][$tdata['tax_class_id']] = $tdata['tax_class_title'];

		$tpl_data = array_merge($tpl_data, $tx);
	}
?>