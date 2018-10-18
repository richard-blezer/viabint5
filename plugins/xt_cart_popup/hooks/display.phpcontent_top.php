<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');


if ( ($_SESSION['isMobile']==true) && (XT_CART_POPUP_MOBILE_STATUS=='false')) return true;

if (XT_CART_POPUP_STATUS =='true') 
{
	include _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_cart_popup/classes/class.xt_cart_popup.php';
	
	echo '<div class="loader_white" id="loader_white"></div>';
	
	if (isset($_SESSION['show_xt_cart_popup']))
	{
		
		$pr_count =count($_SESSION['cart']->content);
		$c_popup = new xt_cart_popup;
		$content = $c_popup->getCartContent();
		echo '<div class="cart_ajax_box" id ="cart_ajax_box" >'.$content.'</div>';
		echo '<script>showCartPopup("'.$_SESSION['show_xt_cart_popup'].'");</script>';
		unset($_SESSION['show_xt_cart_popup']);
	}
	else echo '<div class="cart_ajax_box" id ="cart_ajax_box" ></div>';
}
?>