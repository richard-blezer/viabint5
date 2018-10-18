<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if(XT_PAYMENTS_REGISTERED=='false') {
	echo '<script type="text/javascript" src="' . _SYSTEM_BASE_URL . '' . _SRV_WEB. '../' . _SRV_WEB_PLUGINS . 'xt_payments/js/onload_xtpayments.js"></script>'."\n";
}

echo '<script type="text/javascript" src="' . _SYSTEM_BASE_URL . '' . _SRV_WEB. '../' . _SRV_WEB_PLUGINS . 'xt_payments/js/xtpayments.js"></script>'."\n";
echo '<link rel="stylesheet" type="text/css" href="' . _SYSTEM_BASE_URL . '' . _SRV_WEB . '../' . _SRV_WEB_PLUGINS.'xt_payments/css/xt_payments_pages.css'.'" />';
?>