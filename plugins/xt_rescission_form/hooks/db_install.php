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
 # @version $Id: db_install.php 6578 2013-11-15 17:15:39Z silviyap $
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

$langs = array('de','en');
$tpls = array('rescission_form');
_installMailTemplatesRescissionForm($langs, $tpls);

/* Set SEO data for plugin */
$seo_plugin_file = _SRV_WEBROOT.'/xtFramework/classes/class.seo_plugins.php';
if (file_exists($seo_plugin_file))
{
	require_once $seo_plugin_file;

	$seo_plugin = new seo_plugins();
	$seo_plugin->setPluginSEO('xt_rescission_form');
}

function _installMailTemplatesRescissionForm($langs, $tpls) {
	global $db;

	 $mail_dir = _SRV_WEBROOT.'plugins/xt_rescission_form/installer/template/';

	foreach($tpls as $tpl)
	{
		$data = array(
				'tpl_type' => $tpl,
				'tpl_special' => '-1',
		);
		$c = (int) $db->GetOne("SELECT count(tpl_id) FROM ".TABLE_MAIL_TEMPLATES." where `tpl_type` = '".$data['tpl_type']."'");
		if ($c>0)
		{
			continue;
		}
		try {
			$db->AutoExecute(TABLE_MAIL_TEMPLATES ,$data);
		} catch (exception $e) {
			return $e->msg;
		}
		$tplId = $db->GetOne("SELECT `tpl_id` FROM `".TABLE_MAIL_TEMPLATES."` WHERE `tpl_type`='".$data['tpl_type']."'");

		foreach($langs as $lang)
		{
			$html = file_exists($mail_dir.$lang.'/'.$tpl.'_html.txt') ?  _getFileContentRescissionForm($mail_dir.$lang.'/'.$tpl.'_html.txt') : '';
			$txt = file_exists($mail_dir.$lang.'/'.$tpl.'_txt.txt') ?  _getFileContentRescissionForm($mail_dir.$lang.'/'.$tpl.'_txt.txt') : '';
			$subject = file_exists($mail_dir.$lang.'/subject.txt') ?  _getFileContentRescissionForm($mail_dir.$lang.'/subject.txt') : '';
			
			$data = array(
					'tpl_id' => $tplId,
					'language_code' => $lang,
					'mail_body_html' => $html,
					'mail_body_txt' => $txt,
					'mail_subject' => $subject,
			);
			try {
				$db->AutoExecute(TABLE_MAIL_TEMPLATES_CONTENT ,$data);
			} catch (exception $e) {
				return $e->msg;
			}
		}
	}
}

function _getFileContentRescissionForm($filename) {
	$handle = fopen($filename, 'rb');
    $content = fread($handle, filesize($filename));
    fclose($handle);
    return $content;
}

?>