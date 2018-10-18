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


class pluginGenerateXML{
	
	private $installSql = null;
	private $pluginData = null;
	private $version = null;
	private $type = null;
	
	function __construct($pluginCode) {
		$this->pluginCode = $pluginCode;
		$this->getPluginData();

	}

	function getPluginCode() {
		return $this->pluginCode;
	}
	
	function getPluginId() {
		return $this->pluginId;
	}
	
	function getPaymentId() {
		global $db;
		
		$result = $db->Execute(
			"SELECT * FROM ".TABLE_PAYMENT." where payment_code=? LIMIT 1",
			array($this->getPluginCode())
		);
		return $result->fields['payment_id'];		
	}
	
	function getPluginData() {
		global $db;

		$result = $db->Execute(
			"SELECT * FROM ".TABLE_PLUGIN_PRODUCTS." where code=? LIMIT 1",
			array($this->getPluginCode())
		);
		$this->xmlData = $result->fields;			
		$this->pluginId = $this->xmlData['plugin_id'];
		$this->version = $this->xmlData['version'];
		$this->type = $this->xmlData['type'];
		
	}
	function getPluginVersion() {
		return $this->version;
	}
	function getPluginSql() {
		global $db;

		$result = $db->Execute(
			"SELECT * FROM ".TABLE_PLUGIN_SQL." where plugin_id=?",
			array($this->getPluginId())
		);
		$this->xmlData['db_install'] = '<![CDATA['.$result->fields['install'].']]>';			
		$this->xmlData['db_uninstall'] = '<![CDATA['.$result->fields['uninstall'].']]>';			
	}
	
	function getHookPoints() {
		global $db;

		$result = $db->Execute("SELECT * FROM ".TABLE_PLUGIN_CODE." where plugin_id=?", array($this->getPluginId()));
		while (!$result->EOF) {
			$this->xmlData['plugin_code']['code'][] = array(
				'hook' => $result->fields['hook'],
				'phpcode' => '<![CDATA['.$result->fields['code'].']]>',
				'order' => $result->fields['sortorder'],
				'active' => $result->fields['code_status'],
			);
			$result->MoveNext();
		} 	
	}

	function getConfiguration() {
		global $db;

		if ($this->type == 'payment') {
			$result = $db->Execute(
				"SELECT * FROM ".TABLE_CONFIGURATION_PAYMENT." where payment_id=?",
				array($this->getPaymentId())
			);
			
		}
		
		$result = $db->Execute("SELECT * FROM ".TABLE_PLUGIN_CONFIGURATION." where plugin_id=?", array($this->getPluginId()));
		while (!$result->EOF) {
			$this->xmlData['configuration']['config'][$result->fields['config_key']] = array(
				'key' => $result->fields['config_key'],
				'value' => htmlspecialchars($result->fields['config_value']),
				'type' => $result->fields['type'],
				'url' =>$result->fields['url'],
			);
	
			$result->MoveNext();
		} 

	}
	
	function getLanguageContent() {
		global $db;

		$result = $db->Execute("SELECT * FROM ".TABLE_LANGUAGE_CONTENT." where plugin_key=? ", array($this->getPluginCode()));

		while (!$result->EOF) {
			$tmp = str_replace('_TITLE', '', $result->fields['language_key']);

			if (! $this->xmlData['configuration']['config'][$tmp]) {
				$lanData[$result->fields['language_key']]['key'] = $result->fields['language_key'];
				$lanData[$result->fields['language_key']]['class']=$result->fields['class'];
				$lanData[$result->fields['language_key']][strtolower($result->fields['language_code'])]['value']=$result->fields['language_value'];
			}
			else {
				
				$this->xmlData['configuration']['config'][$tmp][strtolower($result->fields['language_code'])] = array(
					'title'=>$result->fields['language_value'],
					'description'=>'',
				);

			}
			$result->MoveNext();
		}	
			
		if (is_array($lanData)) {
			foreach ($lanData as $key => $val)
				$this->xmlData['language_content']['phrase'][] = $val;
		}
	}
	
	function getPaymentData() {
		global $db;
		
		$rs = $db->Execute("SELECT 
								payment_id,
								payment_code, 
								payment_dir, 
								payment_icon,
								payment_tpl, 
								status as payment_status, 
								sort_order as payment_sort
							FROM ". TABLE_PAYMENT ." WHERE payment_code = ? LIMIT 1", array($this->getPluginCode()));
		
		if ($rs->RecordCount()>0) {
			$payment = $rs->fields;
			unset($payment['payment_id']);
			$result = $db->Execute("SELECT * FROM ".TABLE_PAYMENT_DESCRIPTION." where payment_id=? ", array($rs->fields['payment_id']));
			while (!$result->EOF) {
				$paymentLang[strtolower($result->fields['language_code'])] = array(
					'title'=>$result->fields['payment_name'],
					'description'=>$result->fields['payment_desc'],
				);
				$result->MoveNext();
			}
			
			$this->xmlData['payment'] = array_merge($payment,$paymentLang);
			 
			return true;
		}
		return false;				
	}
	
	function generateXml() {
		
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
		ini_set("display_errors", "1");
	   		
		$this->getPluginSql();
		$this->getConfiguration();
		
		$this->getHookPoints();
		$this->getLanguageContent();

		if (is_array($this->xmlData['configuration']['config'])) {
			foreach ($this->xmlData['configuration']['config'] as $key => $val) {
				unset($this->xmlData['configuration']['config'][$key]);
				$this->xmlData['configuration']['config'][] = $val;
			}
		}
		if ($this->type == 'payment') {
			$this->getPaymentData();			
		}
		$data = array('xtcommerceplugin'=>$this->xmlData);
		
		$xml = XML_serialize($data);
		return $xml;
	}
}