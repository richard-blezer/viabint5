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

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if ($page->page_name=='404') header('HTTP/1.0 404 Not Found');
header('Content-Type: text/html; charset='._SYSTEM_CHARSET);

$doctype_html4 = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'. PHP_EOL;
$doctype_html5 = "<!DOCTYPE html>". PHP_EOL;

($plugin_code = $xtPlugin->PluginCode('display.php:doctype')) ? eval($plugin_code) : false;

//html5 doctype
if(defined("_STORE_META_DOCTYPE_HTML") && strtolower(_STORE_META_DOCTYPE_HTML) == 'html5'){
    echo $doctype_html5;
    echo '<html lang="'.$language->code.'">'. PHP_EOL;
} elseif($client_detect->mobile==true || $client_detect->tablet==true) {
    $doctype_html5 = '<!DOCTYPE html PUBLIC "-//OPENWAVE//DTD XHTML Mobile 1.0//EN" "http://www.openwave.com/dtd/xhtml-mobile10.dtd">'. PHP_EOL;
    echo $doctype_html5;
    echo '<html lang="'.$language->code.'">'. PHP_EOL;

}else{
    echo $doctype_html4;
    echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$language->code.'" lang="'.$language->code.'">'. PHP_EOL;
}
?>
<head>
<base href="<?php echo _SYSTEM_BASE_URL . _SRV_WEB; ?>" />
<?php
	$meta_tags->_showTags($show_shop_content);
	//tpl styles
	include _SRV_WEBROOT._SRV_WEB_CORE.'styles.php';
	include _SRV_WEBROOT._SRV_WEB_TEMPLATES._STORE_TEMPLATE.'/css/css.php';
	include _SRV_WEBROOT._SRV_WEB_CORE.'javascript.php';
	include _SRV_WEBROOT._SRV_WEB_TEMPLATES._STORE_TEMPLATE.'/javascript/js.php';

    $xtMinify->serveFile();




?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php ($plugin_code = $xtPlugin->PluginCode('display.php:content_head')) ? eval($plugin_code) : false; ?>
<?php 
$fav = strtolower(_STORE_FAVICON);

if(strpos($fav,".ico")!==false){
    echo '<link rel="shortcut icon" href="'._SYSTEM_BASE_URL . _SRV_WEB.'media/logo/'._STORE_FAVICON.'" type="image/x-icon" />'. PHP_EOL;
}elseif(strpos($fav,".png")!==false){
    echo '<link rel="icon" href="'._SYSTEM_BASE_URL . _SRV_WEB. 'media/logo/' ._STORE_FAVICON.'" type="image/png" />'. PHP_EOL;
}elseif(strpos($fav,".gif")===false && strpos($fav,".jpg")===false){
    echo '<link rel="shortcut icon" href="'._SYSTEM_BASE_URL . _SRV_WEB.'media/logo/'._STORE_FAVICON.'.ico" type="image/x-icon" />'. PHP_EOL;
    echo '<link rel="icon" href="'._SYSTEM_BASE_URL . _SRV_WEB. 'media/logo/' ._STORE_FAVICON.'.png" type="image/png" />'. PHP_EOL;
}    
?>
</head>
<?php

$body = '<body class="'.$client_detect->client_details->class.'">';
($plugin_code = $xtPlugin->PluginCode('display.php:content_top')) ? eval($plugin_code) : false;

echo $body;
if ($installer_warning==true) {
    echo '<div id="installer_warning">'.WARNING_INSTALL.'</div>';
}
echo $show_shop_content ?>
<?php

($plugin_code = $xtPlugin->PluginCode('display.php:content_bottom')) ? eval($plugin_code) : false;

if(_SYSTEM_PARSE_TIME=='true'){
echo '<div class="copyright">'.$logHandler->parseTime(true).'</div>';
}
?>
</body>
</html>