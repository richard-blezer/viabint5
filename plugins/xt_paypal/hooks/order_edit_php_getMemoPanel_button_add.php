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
 # @version $Id: order_edit.php 5674 2013-01-18 16:32:24Z stefan $
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

global $js,$db;

// only show button if there is a authorization id
    $query = "SELECT * FROM ".TABLE_ORDERS." WHERE orders_id='".$this->oID."'";
    $rs = $db->GetRow($query);

        if (count($rs)>0 && $rs['authorization_amount']>0) {

$js .= "Ext.Msg.show({
		   title:'".TEXT_START."',
		   msg: '".TEXT_PAYPAL_GET_FUNDS_ASK."',
		   buttons: Ext.Msg.YESNO,
		   animEl: 'elId',
		   fn: function(btn){doCapture(".$this->oID.",btn);},
		   icon: Ext.MessageBox.QUESTION
		});";

$js .= "function doCapture(order_id, btn){
			  		if (btn == 'yes') {
			  			var conn = new Ext.data.Connection();
		                 conn.request({
		                 url: '../plugins/xt_paypal/scripts/doCapture.php',
		                 method:'GET',
		                 params: {'order_id': order_id},
		                 success: function(responseObject) {
		                 		   contentTabs.getActiveTab().getUpdater().refresh();
		                           Ext.MessageBox.alert('Message', responseObject.responseText);

		                          }
		                 });
					}

				};";

$paypalCaptureButton = PhpExt_Button::createTextButton(__define("BUTTON_CAPTURE_PAYPAL_FUNDS"),
    new PhpExt_Handler(PhpExt_Javascript::stm($js)));

//  $submitBtn = PhpExt_Button::createTextButton("Submit");
$paypalCaptureButton->setType(PhpExt_Button::BUTTON_TYPE_BUTTON);
$Panel->addButton($paypalCaptureButton);

        }

?>