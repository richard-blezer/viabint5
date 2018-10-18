<?php


defined('_VALID_CALL') or die('Direct Access is not allowed.'); 

global $db;
   
  $sql = 'SELECT plugin_id FROM '.TABLE_PLUGIN_PRODUCTS.' where code ="xt_ekomi";';
  $arr = $db->getRow($sql);
  $plugin_id = $arr['plugin_id'] ;

  if( ((int)$data['plugin_id']) != $plugin_id ) {
    return false;
  }


  if ($data['plugin_status']!=1) return false;


  if (!class_exists('ekomi')) require _SRV_WEBROOT.'plugins/xt_ekomi/classes/class.ekomi.php';

  $ekomi = new ekomi();
  $ekomi->api_id = $data['conf_XT_EKOMI_API_ID_shop_1'];
  $ekomi->api_key = $data['conf_XT_EKOMI_API_KEY_shop_1'];


  $ekomi->setMailTemplate();

?>