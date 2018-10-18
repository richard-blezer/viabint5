<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');

if (XT_MASTER_SLAVE_ACTIVE == 'true')
{
	$prr = new product($p_info->pID);
	if ($p_info->pID>0)
	{
		$sql = "SELECT products_master_flag, products_master_model FROM   " . TABLE_PRODUCTS . " where products_id = ?";
			$record = $db->Execute($sql,array((int)$p_info->pID));	
			
		if (($record->fields["products_master_flag"]==0) && ($record->fields["products_master_model"]!='') && (_PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE=='false') && (!isset($_GET['dl_media'])) )
		{
			$sql = "SELECT products_id FROM   " . TABLE_PRODUCTS . " where products_model = ?";
			$record2 = $db->Execute($sql,array($record->fields["products_master_model"]));
			
			$prr = new product($record2->fields["products_id"]);	
			
			$link_array = array('page'=> 'product', 'type'=>'product', 'name'=>$prr->data['products_name'], 'id'=>$prr->data['products_id'],'seo_url'=>$prr->data['url_text']);
			$xtLink->_redirect($xtLink->_link($link_array));
		}
		if (($record->fields["products_master_flag"]==0) && ($record->fields["products_master_model"]!='') && (_PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE=='true') && (!isset($_GET['dl_media'])) )
		{
			$link_array = array('page'=> 'product', 'type'=>'product', 'name'=>$p_info->data['products_name'], 'id'=>$p_info->data['products_id'],'seo_url'=>$p_info->data['url_text']);
			$pos = strpos($_SERVER['REQUEST_URI'],'action_ms=1');
			if ($pos===false) {
			    $l = $xtLink->_link($link_array);
			    $glue_str = (strpos($l, "?") === false) ? "?" : "&";
			    $xtLink->_redirect($l.$glue_str.'action_ms=1');
            }
		}
	}
}
?>