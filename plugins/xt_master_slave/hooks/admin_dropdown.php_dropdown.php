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

if ($request['get'] == 'plg_xt_master_slave_redirect_to_slaves') {
	if (!isset($result)) $result = array();
	$result[] = array('id' => 'true', 'name' => XT_MASTER_SLAVE_STAY_IN_MASTER_TRUE, 'desc' => XT_MASTER_SLAVE_STAY_IN_MASTER_TRUE);
	$result[] = array('id' => 'false', 'name' => XT_MASTER_SLAVE_STAY_IN_MASTER_FALSE, 'desc' => XT_MASTER_SLAVE_STAY_IN_MASTER_FALSE);
	$result[] = array('id' => 'ajax', 'name' => XT_MASTER_SLAVE_STAY_IN_MASTER_PLUS_AJAX, 'desc' => XT_MASTER_SLAVE_STAY_IN_MASTER_PLUS_AJAX);
}

if ($request['get'] == 'plg_xt_master_slave_shop_search') {
	if (!isset($result)) $result = array();
	$result[] = array('id' => 'master', 'name' => XT_MASTER_SLAVE_MASTER_PRODUCTS, 'desc' => XT_MASTER_SLAVE_MASTER_PRODUCTS);
	$result[] = array('id' => 'slave', 'name' => XT_MASTER_SLAVE_SLAVE_PRODUCTS, 'desc' => XT_MASTER_SLAVE_SLAVE_PRODUCTS);
}

include_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/class.xt_master_slave_functions.php';
if (isset($xtPlugin->active_modules['xt_master_slave'])) {
    switch ($request['get']) {
        case 'products_model':
            require_once _SRV_WEBROOT . 'plugins/xt_master_slave/classes/class.xt_master_slave.php';
            $pmodel_ms = new xt_master_slave();
            $result = $pmodel_ms->getProductsMaster();
            break;
        case 'attrib_tree':
            require_once _SRV_WEBROOT . 'plugins/xt_master_slave/classes/class.xt_master_slave.php';
            $tree_ms = new xt_master_slave();
            $result = $tree_ms->getAttribTree();
            break;
        case 'attrib_parent':
            require_once _SRV_WEBROOT . 'plugins/xt_master_slave/classes/class.xt_master_slave.php';
            $ap = new xt_master_slave();
            $result = $ap->getAttribParent();
            break;
		  case 'attribute_templates':
            require_once _SRV_WEBROOT . 'plugins/xt_master_slave/classes/class.xt_master_slave.php';
			
            $ap = new xt_master_slave();
            $result = $ap->getAttributeTemplate();
            break;	
        case 'products_option_template':
            $result = $dropdown->getProductOptionTemplate('xt_master_slave/templates/options/', true, 'true');
            break;
        case 'products_option_list_template':
            $result = $dropdown->getProductOptionListTemplate('xt_master_slave/templates/product_listing/', true, 'true');
            break;
        case 'master_price_view':
            $result = xt_master_slave_functions::get_master_price_view_flags();
            break;
    }
}
?>