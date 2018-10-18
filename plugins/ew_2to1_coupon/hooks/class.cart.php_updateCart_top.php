<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
* Observe products quantity on cart update
*
* @author Jens Albert
* @copyright 8works <info@8works.de>
*
* Don't change anything from here on
* if you don't know what you're doing.
* Otherwise the earth might disappear
* in a large black hole. We'll blame you!
*/

if (ew_2to1_coupon::is2to1ProductSelf($data['products_key'])) {
    $qty = $data['qty'];
    if (($data['qty'] = ew_2to1_coupon::getMaxQtyFor2to1Product($qty)) < $qty) {
        global $info;
        $info->_addInfoSession(sprintf(TEXT_EW_2TO1_COUPON_ERROR_TO_MANY, $data['qty']), 'error');
    }
}