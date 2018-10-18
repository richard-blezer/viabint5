<?php
/*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Enterprise
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright ©2007-2008 xt:Commerce GmbH. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~~~~ xt:Commerce VEYTON 4.0 Enterprise IS NOT FREE SOFTWARE ~~~~~~~~~~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: button_config.php 3175 2009-03-12 07:17:53Z matthias $
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
 */


$language = new language();
$langFont = $language->_buildData($lang_code);

$buttonFont         = $langFont['font'];
$buttonFontSize     = $langFont['font_size'];
$buttonFontPosition = $langFont['font_position'];

define('_BUTTON_FONT',_SRV_WEBROOT.'media/fonts/'.$buttonFont);
define('_BUTTON_FONT_SIZE',$buttonFontSize);
define('_BUTTON_FONT_POS_VERTICAL',$buttonFontPosition);

/**
 * Default space from left border to beginning of button text
 *
 */
define('_BUTTON_LEFT_SPACE',10);
define('_BUTTON_RIGHT_SPACE',11);

define('_BUTTON_FONT_COLOR_R','255');
define('_BUTTON_FONT_COLOR_G','255');
define('_BUTTON_FONT_COLOR_B','255');
?>