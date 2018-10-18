<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Modifies all product data
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

use ew_viabiona\plugin as ew_viabiona_plugin;

if (class_exists('ew_viabiona\plugin') && ew_viabiona_plugin::status()) {

    //add main image to more images on product page
    if (isset($_GET['page']) && $_GET['page'] == 'product') {
        if (isset($this->data['more_images']) && !empty($this->data['more_images'])) {
            if (isset($this->data['products_image']) && !empty($this->data['products_image'])) {
                $this->data['more_images'] = array_merge(
                    array(
                        array(
                            'file' => $this->data['products_image'],
                            'data' => array(),
                        ),
                    ), $this->data['more_images']);
            }
        }
    }

}
