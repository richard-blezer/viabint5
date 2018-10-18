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

class bestseller_products extends products_list
{
    function getbestsellerProductListing ($data)
    {
        global $xtPlugin, $xtLink, $db, $current_category_id;
        ($plugin_code = $xtPlugin->PluginCode('plugin_bestseller_products:getbestsellerProductListing_top')) ? eval($plugin_code) : false;

        if (isset($plugin_return_value))
            return $plugin_return_value;

        if (XT_BESTSELLER_PRODUCTS_SHOW_TYPE == 'slave' || XT_BESTSELLER_PRODUCTS_SHOW_TYPE == 'nothing') {
            if(isset($xtPlugin->active_modules['xt_master_slave']) &&  $xtPlugin->active_modules['xt_master_slave']== 'true'){
                $this->sql_products->setSQL_WHERE("and (p.products_master_flag ='0' OR p.products_master_flag IS NULL) ");
            }
        }

        if(XT_BESTSELLER_PRODUCTS_SHOW_TYPE == 'nothing'){
            if(isset($xtPlugin->active_modules['xt_master_slave']) &&  $xtPlugin->active_modules['xt_master_slave']== 'true'){
                $this->sql_products->setSQL_WHERE(" and (p.products_master_model is NULL OR p.products_master_model ='') ");
            }
        }

        if (!$this->current_category_id)
            $this->current_category_id = $current_category_id;

        if ($this->current_category_id != 0 && XT_BESTSELLER_PRODUCTS_CATEGORY_DEPENDS == 'true')
            $this->sql_products->setFilter('Categorie_Recursive', $this->current_category_id);

        if (is_data($_GET['filter_id']))
            $this->sql_products->setFilter('Manufacturer', (int)$_GET['filter_id']);

        if(isset($xtPlugin->active_modules['xt_master_slave']) &&  $xtPlugin->active_modules['xt_master_slave']== 'true'){
            $this->sql_products->setSQL_WHERE("and (p.products_master_flag ='0' OR p.products_master_flag IS NULL) ");
        }

        $this->sql_products->setSQL_SORT("p.products_ordered DESC");


        ($plugin_code = $xtPlugin->PluginCode('plugin_bestseller_products:getbestsellerProductListing_query')) ? eval($plugin_code) : false;

        /************** added by PD *******************/
        if ($this->sql_products->user_position == 'store') {
            $this->sql_products->setFilter('GroupCheck');
            $this->sql_products->setFilter('StoreCheck');
            $this->sql_products->setFilter('Fsk18');
            $this->sql_products->setFilter('Status');
            $this->sql_products->setFilter('Seo');

            if (_STORE_STOCK_CHECK_DISPLAY == 'false' && _SYSTEM_STOCK_HANDLING == 'true') {
                $this->sql_products->setFilter('Stock');
            }
        }
        $this->sql_products->getFilter();
        $this->sql_products->getHooks();
        $slaves = array();
        if (XT_BESTSELLER_PRODUCTS_SHOW_TYPE == 'master' && isset($xtPlugin->active_modules['xt_master_slave']) &&  $xtPlugin->active_modules['xt_master_slave']== 'true') {
            $query = 'SELECT p.products_id, p.products_master_model as model, SUM(p.products_ordered) as count FROM ' . TABLE_PRODUCTS
                . ' as p WHERE p.products_master_model is not NULL AND p.products_master_model !="" GROUP BY p.products_master_model';
            $result = $db->GetAll($query);


            for ($j = 0; $j < count($result); $j++) {
                $slaves[$result[$j]['model']]['products_ordered'] = $result[$j]['count'];
                $query = 'SELECT products_id FROM ' . TABLE_PRODUCTS . ' WHERE products_model=? AND products_master_flag="1" LIMIT 1';
                $rs_d = $db->GetAll($query,array($result[$j]['model']));
                $slaves[$result[$j]['model']]['products_id'] = $rs_d[0]['products_id'];
            }
        }

       $query = 'SELECT p.products_id FROM ' . $this->sql_products->a_sql_table;

        if (is_data($this->sql_products->a_sql_where))
            $query .= ' WHERE ' . $this->sql_products->a_sql_where .' AND p.products_ordered > 0';

        $query .= ' GROUP BY p.products_id ';

        if (is_data($this->sql_products->a_sql_sort))
            $query .= ' ORDER BY ' . $this->sql_products->a_sql_sort;

        if ($data['paging']) {
            $_cachesecs = 0;

            if (XT_BESTSELLER_PRODUCTS_CACHE_HOURS > 0) {
                $_cachesecs = XT_BESTSELLER_PRODUCTS_CACHE_HOURS * 60 * 60;
            }

            $pages = new split_page($query, $data['limit'], $xtLink->_getParams(array
            (
                'next_page',
                'info'
            )), $_cachesecs, 'false');

            $this->navigation_count = $pages->split_data['count'];
            $this->navigation_pages = $pages->split_data['pages'];
        }

        if ($data['paging']) {
            $query .= ' LIMIT ' . (((int)$pages->split_data['actual_page'] - 1) * (int)$data['limit']) . ',' . $data['limit'];
        } else {
            $query .= ' LIMIT ' . $data['limit'];
        }

        $result1 = $db->GetAll($query);
        $count = count($result1);
        for ($i = 0; $i < $count; $i++) {
            $size = 'default';
            ($plugin_code = $xtPlugin->PluginCode('plugin_bestseller_products:getbestsellerProductListing_size')) ? eval($plugin_code) : false;
            $product = new product($result1[$i]['products_id'], $size);
            ($plugin_code = $xtPlugin->PluginCode('plugin_bestseller_products:getbestsellerProductListing_data')) ? eval($plugin_code) : false;

            if (isset($slaves[$product->data['products_master_model']])) {
                $pmodel = $product->data['products_master_model'];
                $product->data['products_ordered'] = $slaves[$pmodel];
                $product = new product($slaves[$pmodel]['products_id'], $size);
                $product->data['products_ordered'] = $slaves[$pmodel]['products_ordered'];
            }

            if ($product->data['products_ordered'] > 0) {
                $module_content[] = $product->data;
            }
        }

        if (is_array($module_content)) {
            usort($module_content, array
            (
                $this,
                "cmp"
            ));
        } else {
            return;
        }

        //        if ($data['paging']) {
        //            $module_content = array_splice($module_content, (($pages->split_data['actual_page'] - 1) * $data['limit']), $data['limit']);
        //        }
        ($plugin_code = $xtPlugin->PluginCode('plugin_bestseller_products:getbestsellerProductListing_bottom')) ? eval($plugin_code) : false;

        return $module_content;
    }

    function cmp ($a, $b)
    {
        if ($a['products_ordered'] == $b['products_ordered']) {
            return 0;
        }
        return ($a['products_ordered'] > $b['products_ordered']) ? -1 : 1;
    }
}

?>