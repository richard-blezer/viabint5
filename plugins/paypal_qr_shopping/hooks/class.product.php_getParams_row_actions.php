<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');

$rowActions[] = array('iconCls' => 'paypal_qr', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PAYPAL_QR_CODE);
if ($this->url_data['edit_id'])
    $js = "var edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($products_model)."';\n";
else
    $js = "var edit_id = record.id; var edit_name=record.get('products_model');\n";
$js.= $extF->_RemoteWindow("TEXT_PAYPAL_QR_CODE","TEXT_PAYPAL_QR_CODE","adminHandler.php?plugin=paypal_qr_shopping&load_section=display_code&pg=showCode&products_id='+edit_id+'", '', array(), 500, 500).' new_window.show();';

$rowActionsFunctions['paypal_qr'] = $js;