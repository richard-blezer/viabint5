<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if ( ($_SESSION['isMobile']==true) && (XT_CART_POPUP_MOBILE_STATUS=='false')) return true;

if (XT_CART_POPUP_STATUS =='true') {
    $xtMinify->add_resource(_SRV_WEB_PLUGINS . 'xt_cart_popup/css/ajax_cart.css',143);
}
