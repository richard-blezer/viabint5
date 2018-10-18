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

?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>

<meta name="apple-mobile-web-app-capable" content="no" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />

<link rel="apple-touch-icon" href="<?php echo _SYSTEM_BASE_URL . _SRV_WEB . _SRV_WEB_TEMPLATES . _STORE_TEMPLATE; ?>/img/icons/XT_114.png" />

<!-- iPhone -->
<link rel="apple-touch-startup-image" href="<?php echo _SYSTEM_BASE_URL . _SRV_WEB . _SRV_WEB_TEMPLATES . _STORE_TEMPLATE; ?>/img/320x480_iphone.png">
<?php

$xtMinify->add_resource(_SRV_WEB_TEMPLATES . _STORE_TEMPLATE.'/css/jquery.mobile-1.2.0.css',101);

// jquery mobile theme
$xtMinify->add_resource(_SRV_WEB_TEMPLATES . _STORE_TEMPLATE.'/css/mobile_themeroller.css',202);

$xtMinify->add_resource(_SRV_WEB_TEMPLATES . _STORE_TEMPLATE.'/css/flexslider.css',303);
$xtMinify->add_resource(_SRV_WEB_TEMPLATES . _STORE_TEMPLATE.'/css/mobile.css',404);

?>
