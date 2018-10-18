<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Modify registration form before validation
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

// accept confirmation, when no field is present
if (trim($data["cust_info"]['customers_email_address_confirm']) == '') {
    $data["cust_info"]['customers_email_address_confirm'] = $data["cust_info"]['customers_email_address'];
}

// split field customers_street_address to seperate fields for name and number
if (trim($data["default_address"]['customers_street_address']) == '') {
    $streetCorrect = true;
    if (isset($data["default_address"]['customers_street_address_name'])) {
        $streetName = trim($data["default_address"]['customers_street_address_name']);
        if (empty($streetName)) {
            $streetCorrect = false;
        }
    }
    if (isset($data["default_address"]['customers_street_address_number'])) {
        $streetNumber = (int)$data["default_address"]['customers_street_address_number'];
        if ($streetNumber == 0) {
            $streetCorrect = false;
        }
    }
    if ($streetCorrect) {
        $data["default_address"]['customers_street_address'] = "$streetName $streetNumber";
    }
}