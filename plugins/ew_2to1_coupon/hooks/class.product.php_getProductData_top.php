<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
* Translate 2:1 product fake id to origin product id
*
* @author Jens Albert
* @copyright 8works <info@8works.de>
*
* Don't change anything from here on
* if you don't know what you're doing.
* Otherwise the earth might disappear
* in a large black hole. We'll blame you!
*/

if (ew_2to1_coupon::is2to1ProductSelf($this->pID)) {
    $this->originalPid = $this->pID;
    $this->pID = ew_2to1_coupon::get2to1ProductId();
    $this->qty = ew_2to1_coupon::getMaxQtyFor2to1Product($this->qty);
}