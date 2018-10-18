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



class grid_keywords {


	function getKeywordsStat()
	{
		return $this->_getKeywordsStat();
	}
	
	function getKeywordsNoResultStat()
	{
		return $this->_getKeywordsStat('result_count = 0', "request_count");
	}
	
	private function _getKeywordsStat($where_clause = '', $orderBy = "last_date") {

		global $store_handler;

        $stores = $store_handler->getStores();

        $shopname = array();
        foreach ($stores as $sdata) {
            $shopname[$sdata['id']] = $sdata['text'];
        }

        $data = array();

        $keywords = $this->_getKeywords($where_clause, $orderBy);

        $i = 0;
        foreach ($keywords as $tdata){
            $i++;
            $data[] = array(
                $i,
                $shopname[$tdata['shop_id']],
                $tdata['keyword'],
                $tdata['result_count'],
                $tdata['request_count'],
                $tdata['last_date']
            );
		}

        return $data;

    }

    private function _getKeywords($where_clause = '', $orderBy = "last_date"){
        global $db,$store_handler;

         $rs = $db->Execute("SELECT shop_id, keyword, result_count, request_count, last_date
                             FROM " . TABLE_SEARCH . " 
                             WHERE 1 " . (!empty($where_clause) ? " AND " . $where_clause : "") . "
                             ORDER BY $orderBy DESC LIMIT 0, 100");

        $data = array();
		while (!$rs->EOF) {
			$data[] = array(
                "shop_id"   =>  $rs->fields['shop_id'],
                "keyword"     =>  $rs->fields['keyword'],
                "result_count"    =>  $rs->fields['result_count'],
                "request_count"    =>  $rs->fields['request_count'],
                "last_date" =>  $rs->fields['last_date']
             );

			$rs->MoveNext();
		}
		$rs->Close();

        return $data;
    }

}
?>