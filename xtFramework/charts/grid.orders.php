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


class grid_orders {

    function _getTotalGrid(){
        global $store_handler;

        $stores = $store_handler->getStores();
        
        $data = array();
        foreach ($stores as $sdata) {

            $customers = $this->_totalCustomers($sdata['id']);
            $products = $this->_totalProducts($sdata['id']);
            $today = $this->_totalAmount($sdata['id'], 'today');
            $yesterday = $this->_totalAmount($sdata['id'], 'yesterday');
            $week = $this->_totalAmount($sdata['id'], 'week');
            $month = $this->_totalAmount($sdata['id'], 'month');
            $year = $this->_totalAmount($sdata['id'], 'year');
            
            $data[] = array(
                $sdata['text'],
                $customers['customers'],
                $products['products'],
                $today['sales'],
                $yesterday['sales'],
                $week['sales'],
                $month['sales'],
                $year['sales']
            );
            
        }

        return $data;
        
    }
	
	function _totalAmount($shopid='', $totalRange='') {
		global $db,$store_handler;		

		$year  = date('Y');
		$month = date('n');
		$week = date('W');
		$day = date('z')+1; // fix differnt start day for php and mysql function
		
		switch ($totalRange){
			
			case 'today':
				$dateRange= 'YEAR(o.date_purchased) = ' . $year . ' and DAYOFYEAR(o.date_purchased) =' . $day;
				break;
			case 'yesterday':
				$day = $day -1;
				if($day<=0){
					$year =  $year -1;
                    $day =365;
                    if ((date("z", mktime(0, 0, 0, 12, 31, $year))) == 365) $day = 366 ; // fix leap year
				}
				$dateRange= 'YEAR(o.date_purchased) = ' . $year . ' and DAYOFYEAR(o.date_purchased) =' . $day;
				break;
			case 'week':
				$dateRange= 'YEAR(o.date_purchased) = ' . $year . ' and WEEKOFYEAR(o.date_purchased) =' . $week;
				break;
			case 'month':
				$dateRange= 'year(o.date_purchased) = ' . $year . ' and MONTH(o.date_purchased) = ' . $month ;
				break;
			case 'year':
				$dateRange= 'year(o.date_purchased) = ' . $year;
		}

        $rs = $db->Execute("SELECT sum(s.orders_stats_price) as sales, count(s.orders_id) as orders, o.shop_id as shopid 
                            FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATS . " s 
                            WHERE " . $dateRange . " and o.orders_id = s.orders_id and o.shop_id = " . $shopid . "
                            GROUP BY shopid");

        $data = array();
        $data['sales'] = $rs->fields['sales'];
        $data['orders'] = $rs->fields['orders'];  
        if (!isset($rs->fields['sales'])) $data['sales'] =0;   
        if (!isset($rs->fields['orders'])) $data['orders'] =0;         
		return $data;
	}

    function _totalCustomers($shopid='') {
		global $db,$store_handler;

        $rs = $db->Execute("SELECT count(*) as amount FROM " . TABLE_CUSTOMERS . "
                            WHERE shop_id = '".$shopid."'  GROUP BY shop_id");

        $data = array();
        $data['customers'] = $rs->fields['amount'];
        return $data;
    }

    function _totalProducts($shopid='') {
		global $db,$store_handler;

        $rs = $db->Execute("SELECT count(*) as amount FROM " . TABLE_PRODUCTS . "
                            WHERE products_owner = '".$shopid."'  GROUP BY products_owner");

        $data = array();
        $data['products'] = $rs->fields['amount'];
        return $data;
    }
}
?>