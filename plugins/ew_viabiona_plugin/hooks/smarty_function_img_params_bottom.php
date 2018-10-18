<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * smarty_function_img:params_bottom
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

use ew_viabiona\createThumbnails as ew_create_thumbnails;

if (class_exists('ew_viabiona\createThumbnails') && ew_create_thumbnails::status()) {

    $imageFilename = trim($params['img']);
    $imageClass = trim($type_array[1]);
    $imagePath = _SRV_WEBROOT . _SRV_WEB_IMAGES . $imageClass . '/' . $imageFilename;

    if (!ew_create_thumbnails::thumbExists($imagePath)) {
        $createThumb = new ew_create_thumbnails($imageClass);
        $createThumb->processImage($imageFilename);
    }

}