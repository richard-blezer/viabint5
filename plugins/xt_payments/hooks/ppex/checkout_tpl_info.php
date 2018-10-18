<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';

if (XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true) {
    if (array_key_exists('XT_PPEXPRESS__conditions_accepted', $_SESSION) && $_SESSION['XT_PPEXPRESS__conditions_accepted']==1) {
        $smarty->_tpl_vars['conditions_accepted']=1;
        unset($_SESSION['XT_PPEXPRESS__conditions_accepted']);
    } else {
        $smarty->_tpl_vars['conditions_accepted']=0;
    }
}
?>