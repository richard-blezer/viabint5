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



class chart_products {


	function _get() {

		switch ($_GET['type']) {

			case 'sweek':
				$this->_saleWeekPie($_GET['week'],$_GET['month'],$_GET['year']);
				break;
				
			case 'smonth':
				$this->_saleMonthPie($_GET['month'],$_GET['year']);
				break;
			case 'aweek':
				$this->_amountWeekPie($_GET['week'],$_GET['month'],$_GET['year']);
				break;
				
			case 'amonth':
				$this->_amountMonthPie($_GET['month'],$_GET['year']);
				break;
		}

	}	
	

	function _saleWeekPie($week='',$month='',$year='') {
		global $db,$store_handler;
		
		$year  = (($year!='')  ? (int)$year  : date('Y'));
		$month = (($month!='') ? (int)$month : date('n'));
		$week  = (($month!='') ? (int)$week  : date('W'));

		$stores = $store_handler->getStores();

		$g = new graph();
		$g->title( 'Orders Share '.$month.'/'.$year, _CHART_TITLE_STYLE );
		$g->pie(60,'#505050','{font-size: 12px; color: '.constant(_CHART_COLOR_3).';}');
		$g->bg_colour = '#FFFFFF';

		$rs = $db->Execute("SELECT sum(p.products_quantity) as quantity, p.products_price as price, p.products_id as pid, p.products_name as name FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " p  WHERE year(o.date_purchased) = '" . $year . "' and month(o.date_purchased) = '" . $month . "' and week(o.date_purchased) = '" . $week . "' and o.orders_id = p.orders_id GROUP BY pid ORDER BY quantity DESC LIMIT 10");
		
		$data = array();
		$totalamount = 0;		
		while (!$rs->EOF) {
			$amount = ($rs->fields['amount']) ? round($rs->fields['amount'],2) : '0';
			$data[$rs->fields['shopid']] = $amount;
			$totalamount += $amount;
			$rs->MoveNext();
		}$rs->Close();		

		$pieShop = array();
		$pieData = array();
		$pieSliceColor = array();
		$i=1;
		foreach ($stores as $sdata) {
			if( $data[$sdata['id']] > 0 ){
				$pieShop[] = $sdata['text'] . ' - ' . $data[$sdata['id']] ;
				$pieData[] = $data[$sdata['id']] / $totalamount * 100;
				$pieSliceColor[] = constant(_CHART_COLOR_.$i);
				$i++;
			}
		}
		
		$g->pie_values( $pieData, $pieShop );

		$g->pie_slice_colours( $pieSliceColor );

		$g->set_tool_tip( '#val#%' );

		echo $g->render();
	}	

}
?>