<?php
/*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Enterprise
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2009 xt:Commerce GmbH. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~~~~ xt:Commerce VEYTON 4.0 Enterprise IS NOT FREE SOFTWARE ~~~~~~~~~~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: css.php 3560 2009-07-01 10:43:11Z mzanier $
 # @copyright xt:Commerce GmbH, www.xt-commerce.com
 #
 # @author Mario Zanier, xt:Commerce GmbH	mzanier@xt-commerce.com
 #
 # @author Matthias Hinsche					mh@xt-commerce.com
 # @author Matthias Benkwitz				mb@xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce GmbH, Bachweg 1, A-6091 Goetzens (AUSTRIA)
 # office@xt-commerce.com
 #
 #########################################################################
 
 BSP:
 <link href="<?php echo _SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_TEMPLATES._STORE_TEMPLATE.'/javascript/example.ext' ?>" media="all" rel="stylesheet" type="text/css" />
 $xtMinify->add_resource(_SRV_WEB_TEMPLATES._STORE_TEMPLATE.'/css/nivo-slider.css',210);

 */

?>

 <link href="<?php echo _SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_TEMPLATES._STORE_TEMPLATE.'/css/colorbox.css' ?>" media="all" rel="stylesheet" type="text/css" />

 <link href="<?php echo _SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_TEMPLATES._STORE_TEMPLATE.'/css/jquery-ui-1.9.2.custom.css' ?>" media="all" rel="stylesheet" type="text/css" />


<?php
if ($_SESSION['isMobile'] == true)
{
	?>
	<link href="<?php echo _SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_TEMPLATES._STORE_TEMPLATE.'/css/mobile.css' ?>" media="all" rel="stylesheet" type="text/css" />
	<?php
}
?>

<?php if ($_SESSION['isMobile'] == true) : ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
<?php else : ?>
    <meta name="viewport" content="maximum-scale=1.0" /> 
<?php endif; ?>