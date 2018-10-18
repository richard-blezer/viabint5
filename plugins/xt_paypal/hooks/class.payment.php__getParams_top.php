<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

//require_once _SRV_WEBROOT. _SRV_WEB_PLUGINS. 'xt_klarna/classes/class.xt_klarna.php';

/**
 * Add the test connection buttons.
 */
$pObj = $this->_get($this->payment_id);
$is_ppp = false;
if ($pObj) {
    if ($pObj->data[0]['payment_code'] == 'xt_paypal') {
        $is_ppp = true;
    }
}

if ($is_ppp == true && $this->url_data['edit_id'])
{
    $extF = new ExtFunctions();

    // Add the button for connection test
    $rowActions[] = array(
        'iconCls' => 'ppp_test_connection',
        'qtipIndex' => 'qtip3',
        'qtip' => TEXT_PAYPAL_TEST_CONNECTION,
        'tooltip' => TEXT_PAYPAL_TEST_CONNECTION
    );
    $window = $extF->_RemoteWindow(
        TEXT_PAYPAL_TEST_CONNECTION,
        TEXT_PAYPAL_TEST_CONNECTION,
        "adminHandler.php?plugin=xt_paypal&load_section=xt_paypal&pg=testConnection",
        true,
        array(),
        900,
        600
    );
    $rowActionsFunctions["paypal_test_connection"] = "
    "
    . $window . "
    new_window.show();

    ";

}