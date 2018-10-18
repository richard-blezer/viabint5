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



class grid_sales {


	function _getTotalSales($obj, $range, $count=10) {

		global $store_handler;

        $stores = $store_handler->getStores();

        $shopname = array();
        foreach ($stores as $sdata) {
            $shopname[$sdata['id']] = $sdata['text'];
        }

        $data = array();

        $totalSales = $this->_totalSales($obj, $range, $count);
            // $obj = 'quantity', 'amount'
            // $range = 'today', 'yesterday', 'week', 'month', 'year', 'all'

        $i = 0;
        foreach ($totalSales as $tdata){
            $i++;
            $data[] = array(
                $i,
                $shopname[$tdata['shopid']],
                $tdata['name'],
                $tdata['model'],
                $tdata['price'],
                $tdata['quantity'],
                $tdata['amount']
            );
		}

        return $data;

    }

    function _totalSales($obj, $range, $count){
        global $db,$store_handler;

        $year  = date('Y');
		$month = date('n');
		$week = date('W');
		$day = date('z')+1; // fix differnt start day for php and mysql function

        switch ($range){

            case 'today':
                $queryCondition = "and year(o.date_purchased) = '" . $year . "' and dayofyear(o.date_purchased) = '" . $day . "' ";
                break;

            case 'yesterday':
                $day = $day -1;
				if($day<=0){
					$year =  $year -1;
                    $day =365;
                    if ((date("z", mktime(0, 0, 0, 12, 31, $year))) == 365) $day = 366 ; // fix leap year
				}
                $queryCondition = "and year(o.date_purchased) = '" . $year . "' and dayofyear(o.date_purchased) = '" . $day . "' ";
                break;

            case 'week':
                $queryCondition = "and year(o.date_purchased) = '" . $year . "' and weekofyear(o.date_purchased) = '" . $week . "' ";
                break;

            case 'month':
                $queryCondition = "and year(o.date_purchased) = '" . $year . "' and month(o.date_purchased) = '" . $month . "' ";
                break;

            case 'year':
                $queryCondition = "and year(o.date_purchased) = '" . $year . "' ";
                break;

            case 'all':
                $queryCondition = "";
                break;
         }

         $rs = $db->Execute("SELECT o.shop_id as shopid, p.products_id as pid, p.products_name as name, p.products_model as model, p.products_price as price, sum(p.products_quantity) as quantity, ((p.products_price)*sum(p.products_quantity)) as amount
                             FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " p
                             WHERE o.orders_id = p.orders_id " .$queryCondition. "
                             GROUP BY pid ORDER BY ". $obj ." DESC LIMIT ".$count);
		
        $data = array();
		while (!$rs->EOF) {
			$data[] = array(
                "shopid"   =>  $rs->fields['shopid'],
                "name"     =>  $rs->fields['name'],
                "model"    =>  $rs->fields['model'],
                "price"    =>  $rs->fields['price'],
                "quantity" =>  $rs->fields['quantity'],
                "amount"   =>  $rs->fields['amount']
             );

			$rs->MoveNext();
		}$rs->Close();
         return $data;
    }

}
?>