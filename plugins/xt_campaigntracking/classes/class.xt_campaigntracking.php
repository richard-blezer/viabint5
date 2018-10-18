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


class xt_campaigntracking
{
	public static $key = 'hash';
	public static $custom_reference_key = 'campaign';
	public $hash;
	public $linked_class;
	public $linked_id;
	public $campaignTrackingId;
	public $passedByReferenceMethod = '';
	public $checkoutCompletedMethod = '';
	public $customReference = '';

	public function xt_campaigntracking($hash = '', $linked_class = '', $id = 0)
	{
		if ($hash)
		{
			global $db;
			$hash = htmlspecialchars($hash);
			$record = $db->Execute("select * from " . TABLE_CAMPAIGNTRACKING . " where hash =? ",array($hash));
			if ($record->RecordCount() == 1)
			{
				$this->hash = $record->fields['hash'];
				$this->linked_class = $record->fields['linked_class'];
				$this->linked_id = $record->fields['linked_id'];
				$this->campaignTrackingId = $record->fields['id'];
				$this->passedByReferenceMethod = $record->fields['passed_by_reference_method'];
				$this->checkoutCompletedMethod = $record->fields['checkout_completed_method'];
				$this->customReference = $record->fields['custom_reference'];
				$record->Close();
			}
		}
		else 
			if ($linked_class && $id)
			{
				$this->_xt_campaigntracking($linked_class, $id);
			}
	}

	public function _xt_campaigntracking($linked_class, $id)
	{
		global $db;
		if (!$this->getCampaignTrackingHash($linked_class, $id))
		{
			$hash = md5('campaigntracking' . uniqid(rand(0, 100)));
			$db->Execute("INSERT INTO " . TABLE_CAMPAIGNTRACKING . " (linked_class, linked_id, hash) VALUES (?,?,? )", array($linked_class,$id, $hash));
			$this->hash = $hash;
			$this->linked_class = $linked_class;
			$this->linked_id = $id;
			$this->campaignTrackingId = $db->Insert_ID(TABLE_CAMPAIGNTRACKING, 'id');
		}
	}

	public function getCampaignTrackingHash($linked_class, $id)
	{
		global $db;
		$record = $db->Execute("select * from " . TABLE_CAMPAIGNTRACKING . " where linked_class =?  and linked_id =? ",array($linked_class,$id));
		if ($record->RecordCount() == 1)
		{
			$this->hash = $record->fields['hash'];
			$this->linked_class = $record->fields['linked_class'];
			$this->linked_id = $record->fields['linked_id'];
			$this->campaignTrackingId = $record->fields['id'];
			$this->passedByReferenceMethod = $record->fields['passed_by_reference_method'];
			$this->checkoutCompletedMethod = $record->fields['checkout_completed_method'];
			$this->customReference = $record->fields['custom_reference'];
			$record->Close();
			return $this->hash;
		}
		else
		{
			return false;
		}
	}

	public function _remove()
	{
		global $db;
		$db->Execute("delete from " . TABLE_CAMPAIGNTRACKING . " where linked_class =? and linked_id =? ",array($this->linked_class,$this->linked_id));
	}

	public function passedByReference()
	{
		global $xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.xt_campaigntracking.php:passedByReference')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value))
			return $plugin_return_value;
		$trackableObject = new $this->linked_class($this->linked_id);
		if ($this->passedByReferenceMethod)
		{
			$trackableObject->{$this->passedByReferenceMethod}();
		}
	}

	public function checkoutCompleted()
	{
		global $xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.xt_campaigntracking.php:chekoutCompleted')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value))
			return $plugin_return_value;
		$trackableObject = new $this->linked_class($this->linked_id);
		if ($this->checkoutCompletedMethod)
		{
			$trackableObject->{$this->checkoutCompletedMethod}();
		}
	}

	public function getUrlParameters()
	{
		$params = xt_campaigntracking::$key . '=' . $this->hash;
		if ($this->customReference)
		{
			$params .= '&' . xt_campaigntracking::$custom_reference_key . '=' . $this->customReference;
		}
		return $params;
	}

	public function setPassedByReferenceMethod($method = '')
	{
		global $db;
		$db->Execute("UPDATE " . TABLE_CAMPAIGNTRACKING . " SET passed_by_reference_method =? where id =?",array($method,$this->campaignTrackingId));
		$this->passedByReferenceMethod = $method;
	}

	public function setCheckoutCompletedMethod($method = '')
	{
		global $db;
		$db->Execute("UPDATE " . TABLE_CAMPAIGNTRACKING . " SET checkout_completed_method =? where id = ? ",array($method,$this->campaignTrackingId));
		$this->checkoutCompletedMethod = $method;
	}

	public function setCustomReference($customReference = '')
	{
		global $db;
		$db->Execute("UPDATE " . TABLE_CAMPAIGNTRACKING . " SET custom_reference =? where id =? ",array($customReference,$this->campaignTrackingId));
		$this->customReference = $customReference;
	}
}
?>