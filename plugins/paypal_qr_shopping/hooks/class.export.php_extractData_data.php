<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');


if ($this->data['paypal_qr_generate']=='1') {

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'paypal_qr_shopping/classes/class.paypal_qr.php';

$pp_qr = new paypal_qr();
$resp = $pp_qr->generateQRcodeadmin($data['products_id'],$this->data['paypal_qr_type'],'true');
$data_array['paypal_qr_image'] = $this->data['MANDANT']['shop_http'] . _SRV_WEB . 'plugins/paypal_qr_shopping/qr_images/' . $resp;

}
?>