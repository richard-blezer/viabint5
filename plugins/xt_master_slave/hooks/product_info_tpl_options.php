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

if (XT_MASTER_SLAVE_ACTIVE == 'true') {
    global $current_product_id;
    require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/class.xt_master_slave_products.php';
    $xt_ms = new master_slave_products();
    $xt_ms->setProductID($current_product_id);
	$master_model = $smarty->get_template_vars('products_master_model');
    if ($master_model !=''){
        $master = xt_master_slave_functions::getMasterData($master_model);
        $m_id = $master['products_id'];
    }else{
         $m_id = $current_product_id;
    }

    if ($_POST['action']!='select_ms') {
        $xt_ms->unsetFilter();
    } elseif (is_array($_POST['id'])) {
        $xt_ms->setFilter($_POST['id']);
		foreach($_POST['id'] as $k=>$v){
            if (isset($_SESSION['xt_master_slave'][$m_id]['error'][$k])){
              
                unset($_SESSION['xt_master_slave'][$m_id]['error'][$k]);
            }
        }
    }
	
	if (isset($_SESSION['reload_select_ms'])) {
        foreach($_SESSION['reload_select_ms'] as $a){
            $xt_ms->setFilter($a['id']);
        }
        unset( $_SESSION['reload_select_ms']);
    }

    $xt_ms->getMasterSlave();
    echo $xt_ms->productOptions;
	
	$all_slaves = $xt_ms->getAllSlaves();
	if ((count($all_slaves)==1)&& (_PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE=='ajax')) 
	{
		
		$options = $xt_ms->getSlaveOptions($all_slaves[0]);
		foreach($options as $k => $v)
		{
			$setId = 'id['.$k.']'.$v;
		}
		echo "<script >loadOptions('".$setId."','0')</script>";
	
	}
}
?>