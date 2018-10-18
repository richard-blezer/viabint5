<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class payment_query extends SQL_query{
	var $filterFunctions; // sql filter functions array
	var $a_sql_table, $a_sql_where, $a_sql_sort, $a_sql_group;

	function payment_query() {
		global $xtPlugin;
		$this->setSQL_TABLE(TABLE_PAYMENT . " p ");
		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':class_top')) ? eval($plugin_code) : false;
		
	}

	function getSQL_query($sql_cols = '', $filter_type='string') {

		$this->getFilter();
		$this->getHooks();

		$sql = 'SELECT '.$sql_cols.$this->a_sql_cols.' FROM '.$this->a_sql_table;
		if (is_data($this->a_sql_where))
			$sql.=' WHERE '.$this->a_sql_where;
		if (is_data($this->a_sql_group))
			$sql.=' GROUP BY '.$this->a_sql_group;
		if (is_data($this->a_sql_sort))
			$sql.=' ORDER BY '.$this->a_sql_sort;
		if (is_data($this->a_sql_limit))
			$sql.=' LIMIT '.$this->a_sql_limit;
		return $sql;
	}


	//////////////////////////////////////////////////////

	function F_Language($lang_code=''){
		global $db, $language;

		if(empty($lang_code))
			$lang_code = $language->code;

		$this->setSQL_TABLE("LEFT JOIN " . TABLE_PAYMENT_DESCRIPTION . " pd ON p.payment_id = pd.payment_id");
		$this->setSQL_WHERE("pd.language_code = " . $db->Quote($lang_code) . " ");
	}

	function F_GroupCheck () {
		global $customers_status;

		if (_SYSTEM_GROUP_CHECK == 'true' && isset($customers_status->customers_status_id)) {
			$perm_array = array(
				array(
					'type'=>'group_permission',
					'key'=>'payment_id',
					'value_type'=>'payment',
					'pref'=>'p'
				)
			);

			$perm = new item_permission($perm_array);

			$this->setSQL_TABLE($perm->_table);
			$this->setSQL_WHERE($perm->_where);
		}

	}

	function F_StoreCheck() {
		global $store_handler;

		if (_SYSTEM_GROUP_CHECK == 'true' && isset($store_handler->shop_id)) {
			$perm_array = array(
				array(
					'type'=>'shop',
					'key'=>'payment_id',
					'value_type'=>'payment',
					'pref'=>'p'
				)
			);

			$perm = new item_permission($perm_array);

			$this->setSQL_TABLE($perm->_table);
			$this->setSQL_WHERE($perm->_where);
		}
	}
	
	function F_MultiCheck ($params='') {
		global $xtPlugin;

	    ($plugin_code = $xtPlugin->PluginCode(__CLASS__.':F_MultiCheck')) ? eval($plugin_code) : false;
	}

    function F_MobileCheck() {

        if (isset($_SESSION['isMobile']) && $_SESSION['isMobile']==true) {
            $this->setSQL_WHERE("and p.payment_available_mobile = '1' ");
        }

    }
	
	function F_ShippingCheck() {
		global $store_handler;

		if (isset($_SESSION['selected_shipping'])) {
			$perm_array = array(
				array(
					'type'=>'shipping_permission',
					'key'=>'payment_id',
					'value_type'=>'payment',
					'pref'=>'p'
				)
			);

			$perm = new item_permission($perm_array);

			$this->setSQL_TABLE($perm->_table);
			$this->setSQL_WHERE($perm->_where);
		}
	}


	function F_StatusCheck() {
		$this->setSQL_WHERE("and p.status = 1 ");
	}
}