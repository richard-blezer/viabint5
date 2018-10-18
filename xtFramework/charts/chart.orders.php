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



class chart_orders {
    
    function __construct() {
        global $system_status;
        
        $order_status_values = $system_status->values['order_status'];
        $this->include_ids = $this->getRelevantOrderStatusID($order_status_values);
        
    }

	function _get() {

        $year  = date('Y');
		$month = date('n');

		switch ($_GET['type']) {

			case 'mamount':
				$this->_amountLine($year, $month, 'month');
				break;

            case 'mshare':
				$this->_amountPie($year, $month, 'month');
				break;

			case 'yamount':
				$this->_amountLine($year, '', 'year');
				break;

            case 'yshare':
				$this->_amountPie($year, '', 'year');
				break;

			case 'aamount':
				$this->_amountLine('', '', 'all');
				break;

            case 'ashare':
				$this->_amountPie('', '', 'all');
				break;

			case 'count':
				$this->_countLine('','','');
				break;

		}

	}

	function _amountLine($year, $month, $query) {
		global $db,$store_handler,$system_status;

        $g = new graph();
		$g->bg_colour = '#FFFFFF';

		$total_data = array();
        $data = array();
        $label = array();

		$stores = $store_handler->getStores();

        
		$i = 1;
        switch ($query) {

            case 'month':
                $g->title( TEXT_ORDERS.' '.$month.'/'.$year, _CHART_TITLE_STYLE );
                $g->set_x_legend( TEXT_DAY, 12, '#736AFF' );
                $g->set_tool_tip( '#key#<br>#val# ('.TEXT_DAY.':#x_label#)' );

                $days  = (date('t', mktime(0,0,0,$month, 1, $year))+1);
                for ($i=1; $i<$days; $i++) {
                    $data[] = 0;
                    $label[$i]=$i;
                }
                $total_data = $data;
                $data_initial = $data;
                foreach ($stores as $sdata) {
                    $data = $data_initial;
                    $rs = $db->Execute("SELECT sum(s.orders_stats_price) as amount, dayofmonth(o.date_purchased) as day FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATS . " s
                                        WHERE year(o.date_purchased) = '" . $year . "' and month(o.date_purchased) ='".$month."' and o.shop_id='".$sdata['id']."' and o.orders_id = s.orders_id GROUP BY day");

                    while (!$rs->EOF) {
                        $data[($rs->fields['day']-1)] = ($rs->fields['amount']) ? round($rs->fields['amount'],2) : '0';
                        $total_data[($rs->fields['day']-1)] +=$data[($rs->fields['day']-1)];
                        $rs->MoveNext();
                    }$rs->Close();
                    $$sdata['text'] = $data;
                    $i++;
                }

                break;

            case 'year':
                $g->title( TEXT_ORDERS.' '.$year, _CHART_TITLE_STYLE );
                $g->set_x_legend( TEXT_MONTH, 12, '#736AFF' );
                $g->set_tool_tip( '#key#<br>#val# ('.TEXT_MONTH.':#x_label#)' );

                $months = 12;
                for ($i=1; $i<=$months; $i++) {
                    $data[] = 0;
                    $label[$i]=$i;
                }
                $total_data = $data;
                $data_initial = $data;
                foreach ($stores as $sdata) {
                    $data = $data_initial; 
                    $rs = $db->Execute("SELECT sum(s.orders_stats_price) as amount , month(o.date_purchased) as month FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATS . " s
                                        WHERE year(o.date_purchased) = '" . $year . "' and o.shop_id='".$sdata['id']."' and o.orders_id = s.orders_id GROUP BY month");

                    while (!$rs->EOF) {
                        $data[($rs->fields['month']-1)] = ($rs->fields['amount']) ? round($rs->fields['amount'],2) : '0';
                        $total_data[($rs->fields['month']-1)] +=$data[($rs->fields['month']-1)];
                        $rs->MoveNext();
                    }$rs->Close();
                    $$sdata['text'] = $data;
                    $i++;
                }               

                break;

            case 'all':
                $g->title( TEXT_ORDERS, _CHART_TITLE_STYLE );
                $g->set_x_legend( TEXT_YEAR, 12, '#736AFF' );
                $g->set_tool_tip( '#key#<br>#val# ('.TEXT_YEAR.':#x_label#)' );

                $yearRange = 12;
                $yearArray = array(2003, 2004, 2005,  2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014);
                for ($i=0; $i<$yearRange; $i++) {
                    $data[$yearArray[$i]] = 0;
                    $label[$i]=$yearArray[$i];
                }
                $total_data = $data;
                $data_initial = $data;
                foreach ($stores as $sdata) {
                    $data = $data_initial; 
                    $rs = $db->Execute("SELECT sum(s.orders_stats_price) as amount , year(o.date_purchased) as year FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATS . " s
                                        WHERE o.shop_id='".$sdata['id']."' and o.orders_id = s.orders_id GROUP BY year");

                    while (!$rs->EOF) {
                        $data[($rs->fields['year'])] = ($rs->fields['amount']) ? round($rs->fields['amount'],2) : '0';
                        $total_data[($rs->fields['year'])] +=$data[($rs->fields['year'])];                        
                        $rs->MoveNext();
                    }$rs->Close();
                    $$sdata['text'] = $data;
                    $i++;
                }                

                break;
            
        }

		if (count($stores)>1) {
			$g->set_data($total_data);
			$g->bar_fade( 50, '#C99AAF', 'Total', 10 );
		}

		$j = 6;
		foreach ($stores as $sdata) {
			$g->set_data( $$sdata['text'] );
			$g->line( 1, constant(_CHART_COLOR_.$j),$sdata['text'], 10 );
			$j++;
		}

		$g->set_y_legend( TEXT_AMOUNT, 12, '#736AFF' );

		$g->set_y_max( max($total_data) );

		$g->x_axis_colour( '#909090', '#FFFFFF' );
		$g->y_axis_colour( '#909090', '#EEEEEE' );

		$g->set_x_labels( $label );
		$g->set_x_label_style( 10, '#000000', 0, 1, '#ffffff' );

		$g->y_label_steps( 5 );	

		echo $g->render();
	}

	function _amountPie($year, $month, $query) {
		global $db,$store_handler;

        $g = new graph();
		$g->bg_colour = '#FFFFFF';

		$total_data = array();
        $data = array();
        $label = array();

		$stores = $store_handler->getStores();

		$i = 1;
        switch ($query) {

            case 'month':
                $g->title(  TEXT_ORDERS .' '.$month.'/'.$year, _CHART_TITLE_STYLE );

                $rs = $db->Execute("SELECT sum(s.orders_stats_price) as amount, o.shop_id as shopid FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATS . " s
                                    WHERE year(o.date_purchased) = '" . $year . "' and month(o.date_purchased) = '" . $month . "' and o.orders_id = s.orders_id GROUP BY shopid ORDER BY shopid");

                break;

            case 'year':
                $g->title(  TEXT_ORDERS .' '.$year, _CHART_TITLE_STYLE );

                $rs = $db->Execute("SELECT sum(s.orders_stats_price) as amount, o.shop_id as shopid FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATS . " s
                                    WHERE year(o.date_purchased) = '" . $year . "' and o.orders_id = s.orders_id GROUP BY shopid ORDER BY shopid");

                break;

            case 'all':
                $g->title(  TEXT_ORDERS , _CHART_TITLE_STYLE );

                $rs = $db->Execute("SELECT sum(s.orders_stats_price) as amount, o.shop_id as shopid FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATS . " s
                                    WHERE o.orders_id = s.orders_id  GROUP BY shopid ORDER BY shopid");

                break;
            
        }
		
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

        $g->pie(60,'#505050','{font-size: 12px; color: '._CHART_COLOR_3.';}');
		$g->pie_values( $pieData, $pieShop );
		$g->pie_slice_colours( $pieSliceColor );
		$g->set_tool_tip( '#val#%' );

		echo $g->render();
	}

	function _countLine($year, $month, $query) {
		global $db,$store_handler;

        $g = new graph();
		$g->bg_colour = '#FFFFFF';

		$total_data = array();
        $data = array();
        $label = array();

		$stores = $store_handler->getStores();

		$i = 1;
        switch ($query) {

            case 'month':
                $g->title( TEXT_ORDERS_COUNT.' '.$month.'/'.$year, _CHART_TITLE_STYLE );
                $g->set_x_legend( TEXT_DAY, 12, '#736AFF' );
                
                $days  = (date('t', mktime(0,0,0,$month, 1, $year))+1);
                for ($i=1; $i<$days; $i++) {
                    $data[] = 0;
                    $label[$i]=$i;
                }

                $data_initial = $data;
                foreach ($stores as $sdata) {
                    $data = $data_initial;

                    $rs = $db->Execute("SELECT count(*) as count, dayofmonth(o.date_purchased) as day FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATS . " s
                                        WHERE year(o.date_purchased) = '" . $year . "' and o.shop_id='".$sdata['id']."' and o.orders_id = s.orders_id  GROUP BY day");

                    while (!$rs->EOF) {
                        $data[($rs->fields['day']-1)] = ($rs->fields['count']) ? round($rs->fields['count'],2) : '0';
                        $total_data[($rs->fields['day']-1)] +=$data[($rs->fields['day']-1)];
                        $rs->MoveNext();
                    }$rs->Close();
                    $$sdata['text'] = $data;
                    $i++;
                }

                break;

            case 'year':
                $g->title( TEXT_ORDERS_COUNT.' '.$year, _CHART_TITLE_STYLE );
                $g->set_x_legend( TEXT_MONTH, 12, '#736AFF' );

                $months = 12;
                for ($i=1; $i<=$months; $i++) {
                    $data[] = 0;
                    $label[$i]=$i;
                }

                $data_initial = $data;
                foreach ($stores as $sdata) {
                    $data = $data_initial;

                    $rs = $db->Execute("SELECT count(*) as count, month(o.date_purchased) as month FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATS . " s
                                        WHERE year(o.date_purchased) = '" . $year . "' and o.shop_id='".$sdata['id']."' and o.orders_id = s.orders_id GROUP BY month");

                    while (!$rs->EOF) {
                        $data[($rs->fields['month']-1)] = ($rs->fields['count']) ? round($rs->fields['count'],2) : '0';
                        $total_data[($rs->fields['month']-1)] +=$data[($rs->fields['month']-1)];
                        $rs->MoveNext();
                    }$rs->Close();
                    $$sdata['text'] = $data;
                    $i++;
                }

                break;

            case 'all':
                $g->title( TEXT_ORDERS_COUNT, _CHART_TITLE_STYLE );
                $g->set_x_legend( TEXT_YEAR, 12, '#736AFF' );

                $yearRange = 12;
                $yearArray = array(2003, 2004, 2005,  2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014);
                for ($i=0; $i<$yearRange; $i++) {
                    $data[$yearArray[$i]] = 0;
                    $label[$i]=$yearArray[$i];
                }

                $data_initial = $data;
                foreach ($stores as $sdata) {
                    $data = $data_initial;

                    $rs = $db->Execute("SELECT count(*) as count, year(o.date_purchased) as year FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATS . " s
                                        WHERE o.shop_id='".$sdata['id']."' and o.orders_id = s.orders_id GROUP BY year");

                    while (!$rs->EOF) {
                        $data[($rs->fields['year'])] = ($rs->fields['count']) ? round($rs->fields['count'],2) : '0';
                        $total_data[($rs->fields['year'])] +=$data[($rs->fields['year'])];
                        $rs->MoveNext();
                    }$rs->Close();
                    $$sdata['text'] = $data;
                    $i++;
                }

                break;

        }

		if (count($stores)>1) {
			$g->set_data($total_data);
			$g->bar_fade( 50, '#C99AAF', 'Total', 10 );
		}

		$j = 6;
		foreach ($stores as $sdata) {
			$g->set_data( $$sdata['text'] );
			$g->line( 1, constant(_CHART_COLOR_.$j),$sdata['text'], 10 );
			$j++;
		}

        $g->set_y_legend( TEXT_COUNT, 12, '#736AFF' );

		$g->set_y_max( max($total_data) );

		$g->x_axis_colour( '#909090', '#FFFFFF' );
		$g->y_axis_colour( '#909090', '#EEEEEE' );		

		$g->set_x_labels( $label );
		$g->set_x_label_style( 10, '#000000', 0, 1, '#ffffff' );

		$g->y_label_steps( 5 );

		$g->set_tool_tip( '#key#<br>#val# (#x_label#)' );

		echo $g->render();
	}
    
    
    function getRelevantOrderStatusID($status_array) {
       
        
        $include_id = array();
        
        if (!is_array($status_array)) return $include_id;
        
        foreach ($status_array as $key => $val) {
            if ($val['data']['calculate_statistic'] == '1') {
                $include_id[]=$val['id'];    
            }
        }
        
        return $include_id;
    }

}
?>