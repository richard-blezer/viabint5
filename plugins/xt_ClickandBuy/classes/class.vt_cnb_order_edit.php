<?php
/*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: class.vt_cnb_order_edit.php 4611 2011-03-30 16:39:15Z mzanier $
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

define ('XT_CLICKANDBUY_TABLE', DB_PREFIX . '_plg_clickandbuy_ems');

class vt_cnb_order_edit {


  public function __construct() {
  } // __construct()

  public function addButton (order_edit $order_edit, PhpExt_Form_FormPanel $panel) {
    $url = 'adminHandler.php?load_section=vt_cnb_order_edit&plugin=xt_ClickandBuy&pg=overview';
    $url .= '&oid=' . $order_edit->oID;
    // __debug ($order_edit);die;
    $tmpBtn = PhpExt_Button::createTextButton(TEXT_EMS_STATUS_BTN,
    new PhpExt_Handler(PhpExt_Javascript::stm("addTab('". $url . "', '" . TEXT_EMS_STATUS_BTN . "');")));

    $panel->addButton($tmpBtn);
  }

  public function setPosition ($p) {
    $this->position = $p;
  }

  public function _getParams() {

    $header['order_id'] = array ('hidden' => true);
    $header['cnt'] = array ('hidden' => true);

    $params['display_checkCol']  = false;
		$params['display_statusTrueBtn']  = false;
		$params['display_statusFalseBtn']  = false;
		$params['display_newBtn']  = false;
		$params['display_editBtn']  = true;
		$params['display_deleteBtn']  = false;
		$params['display_actions'] = false;

		$params['header'] = $header;
		$params['master_key'] = 'cnt';

		return $params;

  }
  public function _get ($id = 0) {
    global $db;
    $obj = new stdClass;

    $data = array();
  	if ($this->url_data['get_data']) {
  	  $oid = $this->url_data['oid'];
  	  $sql = 'select * from ' . XT_CLICKANDBUY_TABLE .  " where order_id=$oid order by created";
  	  $res = $db->Execute ($sql);
  	  $cnt = 1;
  	  foreach ($res as $r) {
  	    $r['cnt'] = $cnt++;
  	    $data[] = $r;
  	  }
  	  //  __debug ($data, $sql);

		} elseif($ID) {
		} else {
		  $tmp['cnt'] = '';
		  $sql = 'show columns from ' . XT_CLICKANDBUY_TABLE;
		  $res = $db->Execute ($sql);
		  foreach ($res as $r) {
		    $tmp[$r['Field']] = '';
		  }
		  $data[] = $tmp;
			// $data = array ('order_id');
		}

		$obj->totalCount = count ($data);
		$obj->data = $data;

		  // __debug ($data);die;

    return $obj;
  } // _get()

  public function _set($data, $set_type = 'unused') {
    $obj = new stdClass();
    $obj->success = true;
    return $obj;
  }



} // class vt_cnb_order_edit
?>