<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Manage html error reporting
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

    ?>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <?php foreach (ew_viabiona_plugin::getIE8js() as $file) : ?>
    <script type="text/javascript" src="<?php echo $file ?>"></script>
    <?php endforeach ?>
    <![endif]-->

    <?php

}