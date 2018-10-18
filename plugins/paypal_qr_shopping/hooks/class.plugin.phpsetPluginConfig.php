<?php


defined('_VALID_CALL') or die('Direct Access is not allowed.'); 

global $db;



  $sql = 'select plugin_id from '.TABLE_PLUGIN_PRODUCTS.' where code ="paypal_qr_shopping";';
  $arr = $db->getRow($sql);
  $qr_plugin_id = $arr['plugin_id'] ;

  if( ((int)$data['plugin_id']) != $qr_plugin_id ) {
    return false;
  }

// hardcode shop_id = 1
$sql = "SELECT * FROM ".TABLE_PLUGIN_CONFIGURATION." WHERE plugin_id=? and shop_id='1'";
$rs = $db->Execute($sql,array($qr_plugin_id));
$config_data = array();
while (!$rs->EOF) {
    $config_data[$rs->fields['config_key']]=$rs->fields['config_value'];
    $rs->MoveNext();
}

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'paypal_qr_shopping/classes/class.paypal_qr.php';

// query fields
//__debug($config_data);

// as long there is no qr qpi code there..
//if ($config_data['PAYPAL_QR_SHOPPING_SHOP_IDENTIFIER']=='Shop Identifier' or strlen($config_data['PAYPAL_QR_SHOPPING_SHOP_IDENTIFIER'])<5) {
//    $pp_qr = new paypal_qr();
//    $pp_qr->create_merchant_account();
if(strlen($config_data['PAYPAL_QR_SHOPPING_X_API_USER'])<5 or strlen($config_data['PAYPAL_QR_SHOPPING_X_API_KEY'])<5 or strlen($config_data['PAYPAL_QR_SHOPPING_X_API_SIGNATURE'])<5) {
    $pp_qr = new paypal_qr();
    $pp_qr->create_merchant_account();
} else {
    $pp_qr = new paypal_qr();
    $pp_qr->update_merchant_account();
}


/*

if (strlen(PAYPAL_QR_SHOPPING_SHOP_IDENTIFIER)<5 or PAYPAL_QR_SHOPPING_SHOP_IDENTIFIER =='Shop Identifier') {
  //  echo 'creating new user-..';
   $pp_qr = new paypal_qr();
   $pp_qr->create_merchant_account();
} else { // update merchant
    $pp_qr = new paypal_qr();
    $pp_qr->update_merchant_account();
  //  $pp_qr->Req_Read_Merchant_account();
  //  echo 'pff';
}
*/


?>
