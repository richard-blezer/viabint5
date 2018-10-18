<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');

if (XT_CART_POPUP_STATUS )
{
	if (_PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE=='ajax')
	{
		$record = $db->Execute("SELECT * FROM ".TABLE_PRODUCTS." WHERE products_model = ?",array($cart_product->data['products_master_model']));
		if($record->RecordCount() > 0){
			$master_id = $record->fields["products_id"];
			$p = new product($master_id);
			$link_array = array('page'=> 'product', 'type'=>'product', 'name'=>$p->data['products_name'], 'id'=>$p->data['products_id'],'seo_url'=>$p->data['url_text']);
			$xtLink->_redirect($xtLink->_link($link_array));
		}
	}
	
}
?>