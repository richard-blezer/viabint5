<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');

if (XT_MASTER_SLAVE_ACTIVE == 'true') {
    if (!empty($data['order']['order_products'])) {
        foreach ($data['order']['order_products'] as $key => $pdata) {
            $data['order']['order_products'][$key]['product_options'] = empty($pdata['product_options']) ? array() : unserialize($pdata['product_options']);
        }
    }

    if (!empty($data['products'])) {
        include_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/class.xt_master_slave_functions.php';
        $f = new xt_master_slave_functions();

        foreach ($data['products'] as $k => $p) {
            $data['products'][$k]['product_options'] = $f->returnSelectedSlaveAttributes($p['products_id']);
        }
    }
}