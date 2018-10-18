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

class search_query extends getProductSQL_query{

	function F_Keywords ($data=0) {
		global $xtPlugin;
        $sdesc='';
        $desc='';
		($plugin_code = $xtPlugin->PluginCode('class.search_query.php:F_Keywords_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$keywords = $data['keywords'];
        $split_keywords = explode(' ',$data['keywords']);

        if (_SYSTEM_SEARCH_SPLIT=='true' && count($split_keywords)>1) {

            if($data['sdesc']=='on') {
                $like = array();
                foreach ($split_keywords as $key=>$val) {
                    $like[]="pd.products_short_description LIKE '%".$val."%'";
                }

                $sdesc =" or (".implode(' AND ',$like).")";  
            }

            if($data['desc']=='on') {
                $like = array();
                foreach ($split_keywords as $key=>$val) {
                    $like[]="pd.products_description LIKE '%".$val."%'";
                }

                $desc =" or (".implode(' AND ',$like).")";  
            }
             
            $pname_like = array();
            $pkeywords_like = array();
            $pmodel_like = array();
            $pean_like = array();
            foreach ($split_keywords as $key=>$val) {
                $pname_like[]="pd.products_name LIKE '%".$val."%'";
                $pkeywords_like[]="pd.products_keywords LIKE '%".$val."%'";
                $pmodel_like[]="p.products_model LIKE '%".$val."%'";
                $pean_like[]="p.products_ean LIKE '%".$val."%'";
            }
            $pname =" (".implode(' OR ',$pname_like).")";
            $pkeywords =" or (".implode(' OR ',$pkeywords_like).")";
            $pmodel =" or (".implode(' OR ',$pmodel_like).")";
            $pean =" or (".implode(' OR ',$pean_like).")";
            
            $sql_where = "AND (".$pname.$pkeywords.$pmodel.$pean.$sdesc.$desc.")"; 
            
            $this->setSQL_WHERE($sql_where);
        }  else {
        
            if($data['sdesc']=='on')
            $sdesc = "or pd.products_short_description LIKE '%".$keywords."%' ";

            if($data['desc']=='on')
            $desc = "or pd.products_description LIKE '%".$keywords."%' ";

            if(!empty($keywords)){
                $this->setSQL_WHERE("AND (pd.products_keywords LIKE '%".$keywords."%' or pd.products_name LIKE '%".$keywords."%' or p.products_model LIKE '%".$keywords."%' or p.products_ean LIKE '%".$keywords."%' ".$sdesc.$desc.")");

            }
        }
	}
	
	function F_MultiCheck ($params='') {
		global $xtPlugin;

	    ($plugin_code = $xtPlugin->PluginCode(__CLASS__.':F_MultiCheck')) ? eval($plugin_code) : false;
	}		
}