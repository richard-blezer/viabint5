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

$rowActions[] = array('iconCls' => 'products_to_attributes', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_TO_ATTRIBUTES);
if ($this->url_data['edit_id'])
    $js = "var edit_id = " . $this->url_data['edit_id'] . ";";
else
    $js = "var edit_id = record.id;";

$extF = new ExtFunctions();
$js .= $extF->_RemoteWindow("TEXT_PRODUCTS_TO_ATTRIBUTES", "TEXT_PRODUCTS", "adminHandler.php?plugin=xt_master_slave&load_section=product_to_attributes&pg=getTreePanel&products_id='+edit_id+'", '', array(), 800, 600) . ' new_window.show();';

$rowActionsFunctions['products_to_attributes'] = $js;



$rowActions[] = array('iconCls' => 'generate_slaves', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_MASTER_SLAVE_GENERATE_SLAVE);
if ($this->url_data['edit_id'])
    $sjs = "var edit_id = " . $this->url_data['edit_id'] . ";";
else
    $sjs = "var edit_id = record.id;";
	$sjs .="var gh=Ext.getCmp('generate_slavesgridForm');if (gh) contentTabs.remove('node_generate_slaves');";
	$sjs .= "addTab('adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generate_slaves&pg=setStepOne&products_id='+edit_id+'&gridHandle=Step1','".TEXT_GENERATE_SLAVES_STEP_1."')";

	$rowActionsFunctions['generate_slaves'] = $sjs;
	
	
$rowActions[] = array('iconCls' => 'generate_slaves_list', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_MASTER_SLAVE_GENERATE_SLAVE_LIST);
if ($this->url_data['edit_id'])
    $sjs = "var edit_id = " . $this->url_data['edit_id'] . ";";
else
    $sjs = "var edit_id = record.id;";

	$sjs .= "addTab('adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generated_slaves&pg=overview&products_id='+edit_id+'','".TEXT_MASTER_SLAVE_GENERATE_SLAVE_LIST."')";

	$rowActionsFunctions['generate_slaves_list'] = $sjs;
	
?>