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

if (empty($current_content_id)) {

	$xtLink->_redirect($xtLink->_link(array('page'=>'index')));

} else {
	// download ?
	if (isset($_GET['dl_media'])) {
		include_once(_SRV_WEBROOT.'xtFramework/classes/class.download.php');
		$download = new download();
		$download->_deleteOutOfDateLinks();
		$download->servePublicFile($_GET['dl_media']);
	}	
	
	($plugin_code = $xtPlugin->PluginCode('module_content.php:content_top')) ? eval($plugin_code) : false;

	$shop_content_data =  $_content->getHookContent($current_content_id, 'true');

	if ($shop_content_data['content_status']=='0' || !$shop_content_data['content_id']) {
		if (_SYSTEM_MOD_REWRITE_404 == 'true') header("HTTP/1.0 404 Not Found");
		
		$tmp_link  = $xtLink->_link(array('page'=>'404'));
		$xtLink->_redirect($tmp_link);
	}
	// content form ?
	if ($shop_content_data['content_form']!='' && file_exists(_SRV_WEBROOT.'xtCore/forms/'.$shop_content_data['content_form'])) {

		include _SRV_WEBROOT.'xtCore/forms/'.$shop_content_data['content_form'];

	} else {

		($plugin_code = $xtPlugin->PluginCode('module_content.php:content_data')) ? eval($plugin_code) : false;

		if (is_array($shop_content_data['subcontent'])) {
			$subdata = $shop_content_data['subcontent'];
			($plugin_code = $xtPlugin->PluginCode('module_content.php:sub_content_data')) ? eval($plugin_code) : false;
		}

        $navigation_link = array('page'=>'content', 'type'=>'content','name'=>$shop_content_data['title'],'id'=>$shop_content_data['content_id'],'seo_url' => $shop_content_data['url_text'],'conn'=>$conn);
        $navigation_link = $xtLink->_link($navigation_link); 
        
		$brotkrumen->_addItem($navigation_link,$shop_content_data['title']);
		
		$template = new Template();
        
        if($page->default_page=="index" && $shop_content_data['content_hook'] == 4){
            $content = $_content->getHookContent('4');
            $tpl = 'default.html';
            $tpl_data = array();
            $tpl_data = $content;
        }else{
            $tpl_data = array('data'=>$shop_content_data, 'subdata'=>$subdata);
            $tpl = 'content.html';
        }

		if (isset($_GET['popup'])) {
			$no_index_tag = true; 
            $index_tpl = 'popup.html';
			$tpl = 'popup_content.html';
		}
		
		($plugin_code = $xtPlugin->PluginCode('module_content.php:tpl_data')) ? eval($plugin_code) : false;
		$page_data = $template->getTemplate('smarty', '/'._SRV_WEB_CORE.'pages/'.$tpl, $tpl_data);
	}
}
?>