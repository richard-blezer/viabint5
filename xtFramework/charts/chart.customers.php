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



class chart_customers {

	function _get() {

        $year  = date('Y');
		$month = date('n');

		switch ($_GET['type']) {

			case 'month':
				$this->_countLine($year, $month, 'month');
				break;

            case 'mshare':
				$this->_countPie($year, $month, 'month');
				break;

			case 'year':
				$this->_countLine($year,'', 'year');
				break;

            case 'yshare':
				$this->_countPie($year,'', 'year');
				break;

            case 'all':
				$this->_countLine('', '', 'all');
				break;

            case 'ashare':
				$this->_countPie('', '', 'all');
				break;

		}

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
                $g->title( TEXT_CUSTOMERS.' '.$month.'/'.$year, _CHART_TITLE_STYLE );
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

                    $rs = $db->Execute("SELECT count(*) as count, dayofmonth(date_added) as day FROM " . TABLE_CUSTOMERS . "
                                        WHERE year(date_added) = '" . $year . "' and month(date_added) = '" . $month . "' and shop_id='".$sdata['id']."' GROUP BY day");

                    while (!$rs->EOF) {
                        $data[($rs->fields['day']-1)] = $rs->fields['count'];
                        $total_data[($rs->fields['day']-1)] +=$data[($rs->fields['day']-1)];
                        $rs->MoveNext();
                    }$rs->Close();
                    $$sdata['text'] = $data;
                    $i++;
                }

                break;

            case 'year':
                $g->title( TEXT_CUSTOMERS.' '.$year, _CHART_TITLE_STYLE );
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

                    $rs = $db->Execute("SELECT count(*) as count, month(date_added) as month FROM " . TABLE_CUSTOMERS . "
                                        WHERE year(date_added) = '" . $year . "' and shop_id='".$sdata['id']."' GROUP BY month");

                    while (!$rs->EOF) {
                        $data[($rs->fields['month']-1)] = $rs->fields['count'];
                        $total_data[($rs->fields['month']-1)] +=$data[($rs->fields['month']-1)];
                        $rs->MoveNext();
                    }$rs->Close();
                    $$sdata['text'] = $data;
                    $i++;
                }

                break;

            case 'all':
                $g->title( TEXT_CUSTOMERS, _CHART_TITLE_STYLE );
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

                    $rs = $db->Execute("SELECT count(*) as count, year(date_added) as year FROM " . TABLE_CUSTOMERS . "
                                        WHERE shop_id='".$sdata['id']."' GROUP BY year");

                    while (!$rs->EOF) {
                        $data[($rs->fields['year'])] = $rs->fields['count'];
                        $total_data[($rs->fields['year'])] +=$data[($rs->fields['year'])];
                        $rs->MoveNext();
                    }$rs->Close();
                    $$sdata['text'] = $data;
                    $i++;
                }

                break;

        }

		if (count($stores)>1) {
			$g->set_data( $total_data );
			$g->bar_fade( 50, '#C99AAF', 'Total', 10 );
		}
						
		$j = 6;
		foreach ($stores as $sdata) {
			$g->set_data( $$sdata['text'] );
			$g->line( 1, constant(_CHART_COLOR_.$j),$sdata['text'], 10 );
			$j++;		
		}
		
		$g->set_y_max( max($total_data) );

		$g->x_axis_colour( '#909090', '#FFFFFF' );
		$g->y_axis_colour( '#909090', '#EEEEEE' );
		
		$g->set_y_legend( TEXT_CUSTOMERS_COUNT, 12, '#736AFF' );

		$g->set_x_labels( $label );
		$g->set_x_label_style( 10, '#000000', 0, 1, '#ffffff' );

		$g->y_label_steps( 5 );

		echo $g->render();
	}

    function _countPie($year, $month, $query) {
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
                $g->title(  TEXT_CUSTOMERS .' '.$month.'/'.$year, _CHART_TITLE_STYLE );

                $rs = $db->Execute("SELECT count(*) as amount, shop_id as shopid FROM " . TABLE_CUSTOMERS . "
                                    WHERE year(date_added) = '" . $year . "' and month(date_added) = '" . $month . "' GROUP BY shopid ORDER BY shopid");

                break;

            case 'year':
                $g->title(  TEXT_CUSTOMERS .' '.$year, _CHART_TITLE_STYLE );

                $rs = $db->Execute("SELECT count(*) as amount, shop_id as shopid FROM " . TABLE_CUSTOMERS . "
                                    WHERE year(date_added) = '" . $year . "' GROUP BY shopid ORDER BY shopid");

                break;

            case 'all':
                $g->title(  TEXT_CUSTOMERS , _CHART_TITLE_STYLE );

                $rs = $db->Execute("SELECT count(*) as amount, shop_id as shopid FROM " . TABLE_CUSTOMERS . "
                                    GROUP BY shopid ORDER BY shopid");

                break;

        }

		$data = array();
		$totalamount = 0;
		while (!$rs->EOF) {
			$amount = $rs->fields['amount'];
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

        $g->pie(60,'#505050','{font-size: 12px; color: '.constant(_CHART_COLOR_3).';}');
		$g->pie_values( $pieData, $pieShop );
		$g->pie_slice_colours( $pieSliceColor );
		$g->set_tool_tip( '#val#%' );

		echo $g->render();
    }

}
?>