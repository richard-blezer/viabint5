<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');

if ( ($_SESSION['isMobile']==true) && (XT_CART_POPUP_MOBILE_STATUS=='false')) return true;

if (XT_CART_POPUP_STATUS =='true') 
{
	$_SESSION['show_xt_cart_popup'] = $data_array["product"];
	
	($plugin_code = $xtPlugin->PluginCode('class.xt_cart_popup.php:add_product_bottom')) ? eval($plugin_code) : false;
	
	$tmp_link = (!empty($_SERVER['HTTPS'])?'https://':'http://'). $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	$xtLink->_redirect($tmp_link);
} 

?>