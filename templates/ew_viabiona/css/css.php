<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

use ew_viabiona\plugin as ew_viabiona_plugin;

// Plugin error messages
class_exists('ew_viabiona\plugin') or die('Please install the required template plugin.');
ew_viabiona_plugin::status() or die(ew_viabiona_plugin::getPluginErrorMessage());

$xtMinify->add_resource('xtFramework/library/jquery/pikaday.css', 100);
?>

    <!-- RESPONSIVE SETUP -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

<?php if (ew_viabiona_plugin::isDebugMode()) : ?>
    <!-- PAGE LOAD TIMER INIT -->
    <script type="text/javascript">
        /* <![CDATA[ */
        var pageLoadTime = (new Date()).getTime();
        /* ]]> */
    </script>
<?php endif ?>

<?php if (ew_viabiona_plugin::check_conf('CONFIG_EW_VIABIONA_PLUGIN_WEBAPPICON')) : ?>

    <!-- WEB APP SUPPORT -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="HandheldFriendly" content="true"/>
    <?php if (($primaryColor = ew_viabiona_plugin::getPrimaryColor()) !== null) : ?>
        <meta name="theme-color" content="<?php echo $primaryColor ?>">
        <meta name="apple-mobile-web-app-status-bar-style" content="<?php echo $primaryColor ?>">
        <meta name="msapplication-TileColor" content="<?php echo $primaryColor ?>">
        <meta name="msapplication-navbutton-color" content="<?php echo $primaryColor ?>">
    <?php endif; ?>
    <?php
    /**
     * WEB APP ICONS
     *
     * Info: For each shop client you could create its own icon files like 'iconname_[shop_id].png'
     *
     * @example for shop client id 1 name it 'webapp-icon-192_1.png'
     */
    ?>
    <link rel="apple-touch-icon" sizes="192x192" href="<?php echo ew_viabiona_plugin::getWebAppIcon('webapp-icon-192', 'png') ?>">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo ew_viabiona_plugin::getWebAppIcon('webapp-icon-144', 'png') ?>">
    <link rel="apple-touch-icon" sizes="96x96" href="<?php echo ew_viabiona_plugin::getWebAppIcon('webapp-icon-96', 'png') ?>">
    <link rel="apple-touch-icon-precomposed" href="<?php echo ew_viabiona_plugin::getWebAppIcon('webapp-icon-96', 'png') ?>"/>
    <link rel="apple-touch-startup-image" href="<?php echo ew_viabiona_plugin::getWebAppIcon('webapp-splashscreen', 'png') ?>">
    <meta name="msapplication-TileImage" content="<?php echo ew_viabiona_plugin::getWebAppIcon('webapp-icon-144', 'png') ?>">

<?php endif; ?>