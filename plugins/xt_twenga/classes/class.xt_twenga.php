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
 # @version $Id: class.xt_twenga.php 6060 2013-03-14 13:10:33Z mario $
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

class xt_twenga {
	
	protected $_table = TABLE_XT_TWENGA;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'tw_id';
	protected $position;
	///
	protected $twId = 0;
	protected $twShopId_num = 0;
	protected $twPartnerAuthKey = XT_TWENGA_PARTNER_AUTH_KEY;
	protected $twData_arr = array();
	protected $twSet_bol = false;

	
	/**
	 * 
	 * constructor
	 */
	public function __construct() {
		///
	}

	
	/**
	 * 
	 * set Twenga ID
	 * 
	 * @param int $id_num Twenga ID
	 * @param int $shopId_num Shop ID
	 * @return bool true, if ID was saved in class attribute, else false
	 */
	public function setTwengaId($id_num=0, $shopId_num=0) {
		global $db;
		
		if ($id_num != 0) {
			$this->twId = $id_num;
			return true;
		}
		elseif ($shopId_num != 0) {
			$check = $db->Execute("SELECT MAX(".$this->_master_key.") AS twid FROM ".$this->_table." WHERE shop_id=".$shopId_num." AND tw_status=1");
			$check_arr = $check->getArray();
			
			if ($check_arr[0]['twid'] > 0) {
				$this->twId = $check_arr[0]['twid'];
				return true;
			}
		}

		return false;
	}
	
	
	/**
	 * 
	 * get Twenga ID
	 * 
	 * @return int Twenga ID
	 */
	public function getTwengaId() {
		return $this->twId;
	}
	

	/**
	 * 
	 * setPosition
	 * 
	 * @param int $position position in list
	 */
	function setPosition ($position) {
		$this->position = $position;
	}
	

	/**
	 * 
	 * _getParams
	 * 
	 * @return array $params parameters for backend display
	 */
	function _getParams() {
		global $language, $xtPlugin;
		
		$header = array();
		$params = array();
		
		$header['tw_id']    = array('type' => 'hidden');
		$header['shop_id']  = array('type' => 'dropdown', 'url' => 'DropdownData.php?get=stores');
		$header['tw_hash_key']  = array('type' => 'textfield');
		$header['tw_login']  = array('type' => 'textfield');
		$header['tw_password']  = array('type' => 'textfield');
		$header['tw_site_url']  = array('type' => 'textfield');
		$header['tw_feed_url']  = array('type' => 'textfield');
		$header['tw_status']  = array('type' => 'status');
				
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = "tw_id";
		$params['SortField']      = "tw_id";
		$params['SortDir']        = "ASC";

		$params['display_checkCol']  = true;
		$params['display_statusTrueBtn']  = true;
		$params['display_statusFalseBtn']  = true;

		$params['display_newBtn'] = true;

		$params['exclude'] = array('');
		
		// edit ID
		if ($this->url_data['edit_id'])
			$js_str = "var edit_id = ".$this->url_data['edit_id']."; ";
		else
			$js_str = "if (typeof record != 'undefined') { var edit_id = record.id; } else { var edit_id = '-'; } ";
			// 1.0.1: general bug in xtAdmin = record is not defined
		
		
		$rowActions[] = array('iconCls'=>'plg_xt_twenga_subscribe', 'qtipIndex'=>'qtip1', 'tooltip'=>TEXT_TWENGA_SUBSCRIBE);
		$js = $js_str . "addTab('adminHandler.php?load_section=xt_twenga&plugin=xt_twenga&pg=subscribe&tw_id='+edit_id, '".TEXT_TWENGA_SUBSCRIBE." ('+edit_id+')') ";
		$rowActionsFunctions['plg_xt_twenga_subscribe'] = $js;

		$rowActions[] = array('iconCls'=>'plg_xt_twenga_activate', 'qtipIndex'=>'qtip4', 'tooltip'=>TEXT_TWENGA_ACTIVATE);
		$js = $js_str . "addTab('adminHandler.php?load_section=xt_twenga&plugin=xt_twenga&pg=activate&tw_id='+edit_id, '".TEXT_TWENGA_ACTIVATE." ('+edit_id+')') ";
		$rowActionsFunctions['plg_xt_twenga_activate'] = $js;
		
		
		$params['rowActions']             = $rowActions;
		$params['rowActionsFunctions']    = $rowActionsFunctions;
		
		
		($plugin_code = $xtPlugin->PluginCode('class.xt_twenga.php:_getParams_bottom')) ? eval($plugin_code) : false;
		return $params;
	}

	
	/**
	 * 
	 * get entry from database
	 * 
	 * @param int $ID id of entry
	 * @return object $obj object of entry
	 */
	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
               $obj = $this->_set(array(), 'new');
               $ID = $obj->new_id;
		}

		// 1.0.1: save ID to session for use when edit_id is set to -
		$_SESSION['xt_twenga']['admin']['new_id'] = $ID;
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);

		if ($this->url_data['get_data'])
        	$data = $table_data->getData();
        elseif($ID)
        	$data = $table_data->getData($ID);
        else
			$data = $table_data->getHeader();

		if($table_data->_total_count!=0 || !$table_data->_total_count)
			$count_data = $table_data->_total_count;
		else
			$count_data = count($data);

		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}
	
	
	/**
	 * 
	 * set status
	 * 
	 * @param int $id id of entry
	 * @param int $status status is active(1) or inactive(0)
	 */
	function _setStatus($id, $status) {
		global $db,$xtPlugin;

		$id = (int)$id;
		if (!is_int($id)) return false;
		
		$db->Execute("update " . $this->_table . " set tw_status = ".$status." where ".$this->_master_key." = '" . $id . "'");
	}
	
	
	/**
	 * 
	 * save entry to database
	 * 
	 * @param array $data data
	 * @param string $set_type set_type
	 */
	function _set($data, $set_type='edit') {
		global $db, $language, $filter;

		// automatically fill the two URL text fields
		if (($data['tw_site_url'] == "" or strtolower($data['tw_site_url']) == "new") and $data['shop_id'] != 0) {
			$site_res = $db->Execute("SELECT shop_http FROM ".TABLE_MANDANT_CONFIG." WHERE shop_id=".$data['shop_id']);
			$site_arr = $site_res->getArray();
			if (isset($site_arr[0]['shop_http'])) {
				$data['tw_site_url'] = $site_arr[0]['shop_http'];
			}
		}
		if (($data['tw_feed_url'] == "" or strtolower($data['tw_feed_url']) == "new") and $data['shop_id'] != 0) {
			if ($data['tw_site_url'] != "" and strtolower($data['tw_site_url']) != "new") {
				$siteUrl_str = $data['tw_site_url'];
			}
			else {
				$site_res = $db->Execute("SELECT shop_http FROM ".TABLE_MANDANT_CONFIG." WHERE shop_id=".$data['shop_id']);
				$site_arr = $site_res->getArray();
				if (isset($site_arr[0]['shop_http'])) {
					$siteUrl_str = $site_arr[0]['shop_http'];
				}
			}
			if (substr($siteUrl_str, -1, 1) != "/") {
				$siteUrl_str .= "/";
			}
			$feed_res = $db->Execute("SELECT feed_id FROM ".TABLE_FEED." WHERE feed_title='Twenga XML Feed' AND feed_store_id=".$data['shop_id']);
			$feed_arr = $feed_res->getArray();
			if (isset($feed_arr[0]['feed_id'])) {
				$data['tw_feed_url'] = $siteUrl_str."cronjob.php?feed_id=".$feed_arr[0]['feed_id'];
			}
		}
		
		$twengaObj = new stdClass();
				
		$oMain = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$twengaObj = $oMain->saveDataSet();
		
		if ($set_type=='new') {	// edit existing
			$data = array_merge($data, array($this->_master_key=>$twengaObj->new_id));
		}

		return $twengaObj;
	}
	
	
	/**
	 * 
	 * delete entry
	 * 
	 * @param int $id id of entry
	 */
	function _unset($id = 0) {
		global $db;
		if ($id == 0) return false;

		$db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ".$id);
		if ($this->_table_lang !== null)
			$db->Execute("DELETE FROM ". $this->_table_lang ." WHERE ".$this->_master_key." = ".$id);
	}
	
	
	/**
	 * 
	 * read Twenga data from database; is only called once
	 * 
	 * @param int $id_num Twenga ID
	 * @return bool true on success, else false
	 */
	protected function readTwengaData($id_num=0) {
		global $db;

		$this->twSet_bol = false;
		if ($id_num == 0) return false;
		
		$twengaData = $db->Execute("SELECT * FROM ".$this->_table." WHERE tw_id=".$id_num);
		$twengaData_arr = $twengaData->getArray();
		
		if (!empty($twengaData_arr)) {
			$this->twData_arr = $twengaData_arr[0];
			$this->twData_arr['tw_partner_auth_key'] = $this->twPartnerAuthKey;
			// 1.0.1: only set to true, if shop_id is not zero
			if ($this->twData_arr['shop_id'] != 0) {
				$this->twSet_bol = true;
			}
			return true;
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * 
	 * get Twenga data - if read from class attributes
	 * 
	 * @param int $id_num Twenga ID
	 * @return array|bool Twenga data or false
	 */
	public function getTwengaData($id_num) {
		if ($id_num == 0) return false;
		
		if ($this->twSet_bol) {
			return $this->twData_arr;
		}
		else {
			$success_bol = $this->readTwengaData($id_num);
			if ($success_bol)
				return $this->twData_arr;
			else
				return false;
		}
	}
	
	
	/**
	 * 
	 * get country code of the store set in Twenga
	 * 
	 * @return string $storeCountry_str country code
	 */
	protected function getStoreCountry() {
		global $db;
		
		$twengaData_arr = $this->getTwengaData($this->getTwengaId());
		// 1.0.1: as long as shop_id is still zero, use DE as default
		if ($twengaData_arr['shop_id'] == 0) {
			$storeCountry_str = "DE";
		}
		else {
			$configTable_str = TABLE_CONFIGURATION_MULTI.$twengaData_arr['shop_id'];
			$storeCountry_res = $db->Execute("SELECT config_value AS storecountry FROM ".$configTable_str." WHERE config_key='_STORE_COUNTRY'");
			$storeCountry_arr = $storeCountry_res->getArray();
			if (isset($storeCountry_arr[0]['storecountry']) and strlen($storeCountry_arr[0]['storecountry']) == 2) {
				$storeCountry_str = $storeCountry_arr[0]['storecountry'];
			}
			else {
				$storeCountry_str = "DE";
			}
		}
		
		return $storeCountry_str;
	}
	
	
	/**
	 * 
	 * display subscription link in backend
	 * 
	 * @param array $input_arr data from backend
	 * @return void
	 */
	public function subscribe($input_arr) {
		global $db;

		print "<div class=\"twenga_body\">";
		print "<img src=\"../"._SRV_WEB_PLUGINS.'xt_twenga/images/logo_big.jpg'."\" /><span class=\"twenga_headline\">".strtoupper(TEXT_TWENGA_SUBSCRIBE)."</span><br /><br />";
		
		// 1.0.1: if edit_id is - use ID from session
		if ($input_arr['tw_id'] == '-') {
			if (is_numeric($_SESSION['xt_twenga']['admin']['new_id'])) {
				$input_arr['tw_id'] = $_SESSION['xt_twenga']['admin']['new_id'];
			}
			else {
				$input_arr['tw_id'] = 0;
			}
		}
		
		$idSet_bol = $this->setTwengaId($input_arr['tw_id']);
		
		if ($idSet_bol) {
			print "<div class=\"twenga_plain\">".TEXT_TWENGA_SUBSCRIBE_INTRO."</div>";
		}
		else {
			print "<div class=\"twenga_error\">".TEXT_TWENGA_PLUGIN_ERROR."</div>";
			print "</div>";
			return;
		}
				
		$subscriptionLink_str = $this->getSubscriptionLink();
		
		if (substr($subscriptionLink_str, 0, 4) != "http") {
			print "<div class=\"twenga_error\">".TEXT_TWENGA_FEEDBACK_ERROR.": ".$subscriptionLink_str."</div>";
			print "</div>";
			return;
		}
		else {
			print "<div class=\"twenga_plain\">".TEXT_TWENGA_SETUP_ACCOUNT.": <a href=\"".$subscriptionLink_str."\" target=\"_blank\">".TEXT_TWENGA_SUBSCRIPTION_LINK."</a></div>";
		}
		
		print "</div>";
		return;
	} 

	
	/**
	 * 
	 * activate - tell twenga shop is ready to go
	 * 
	 * @param array $input_arr data from backend
	 * @return void
	 */
	public function activate($input_arr) {
		global $db;

		print "<div class=\"twenga_body\">";
		print "<img src=\"../"._SRV_WEB_PLUGINS.'xt_twenga/images/logo_big.jpg'."\" /><span class=\"twenga_headline\">".strtoupper(TEXT_TWENGA_ACTIVATE)."</span><br /><br />";
		
		// 1.0.1: if edit_id is - use ID from session
		if ($input_arr['tw_id'] == '-') {
			if (is_numeric($_SESSION['xt_twenga']['admin']['new_id'])) {
				$input_arr['tw_id'] = $_SESSION['xt_twenga']['admin']['new_id'];
			}
			else {
				$input_arr['tw_id'] = 0;
			}
		}
		
		$idSet_bol = $this->setTwengaId($input_arr['tw_id']);
		
		if ($idSet_bol) {
			print "<div class=\"twenga_plain\">".TEXT_TWENGA_ACTIVATE_INTRO."</div>";
		}
		else {
			print "<div class=\"twenga_error\">".TEXT_TWENGA_PLUGIN_ERROR."</div>";
			print "</div>";
			return;
		}
		
		// checking the hashkey while activating Twenga
		$valid_str = $this->hashkeyExist();

		if ($valid_str != "true") {
			print "<div class=\"twenga_error\"><br />".TEXT_TWENGA_FEEDBACK_ERROR.": ".$valid_str."</div>";
			print "</div>";
			return;
		}
		else {
			print "<div class=\"twenga_success\"><br />".TEXT_TWENGA_EXIST_VALID."</div>";
		}
		// end hashkey
		

		$result_str = $this->activateTwenga();
		
		if ($result_str != "true") {
			print "<div class=\"twenga_error\"><br />".TEXT_TWENGA_FEEDBACK_ERROR.": ".$result_str."</div>";
			print "</div>";
			return;
		}
		else {
			print "<div class=\"twenga_success\"><br />".TEXT_TWENGA_ACTIVATE_OKAY."</div>";
		}
		

		// adding the feed
		$resultAdd_str = $this->addFeed();
		
		if ($resultAdd_str != "true") {
			print "<div class=\"twenga_error\"><br />".TEXT_TWENGA_FEEDBACK_ERROR.": ".$resultAdd_str."</div>";
			print "</div>";
			return;
		}
		else {
			print "<div class=\"twenga_success\"><br />".TEXT_TWENGA_ADDFEED_OKAY."</div>";
		}
		// feed
		
		print "</div>";
		return;
	} 
	
	
	
	//////////////
	/// TWENGA ///
	//////////////
	
	
	/**
	 * 
	 * call Site/Exist at twenga
	 * 
	 * @return string twenga response
	 */
	public function hashkeyExist() {
		// get twenga ID and data
		$twengaId_num = $this->getTwengaId();
		$twengaData_arr = $this->getTwengaData($twengaId_num);
		
		// set params for API call
		$params_arr = array(
			"PARTNER_AUTH_KEY" => $twengaData_arr['tw_partner_auth_key'],
			"key" => $twengaData_arr['tw_hash_key']
		);
		
		$reply_mix = $this->callTwengaApi("exist", $params_arr, "JSON");

		// error handling
		if (!is_array($reply_mix)) {
			return "Response is not an array.";
		}

		if (array_key_exists("error", $reply_mix)) {
			return $reply_mix['error']['message'];
		}
		elseif (array_key_exists("response", $reply_mix)) {
			return $reply_mix['response']['message'];
		}
		else {
			return "Unknown Error.";
		}
	}
	

	/**
	 * 
	 * get subscription link from API
	 * 
	 * @return string twenga response
	 */
	protected function getSubscriptionLink() {
		// get twenga ID and data
		$twengaId_num = $this->getTwengaId();
		$twengaData_arr = $this->getTwengaData($twengaId_num);
						
		$params_arr = array(
			"PARTNER_AUTH_KEY" => $twengaData_arr['tw_partner_auth_key'],
			"site_url" => $twengaData_arr['tw_site_url'],
			"feed_url" => $twengaData_arr['tw_feed_url'],
			"country" => $this->getStoreCountry()
		);
		
		$reply_mix = $this->callTwengaApi("subscribe", $params_arr, "JSON");

		// error handling
		if (!is_array($reply_mix)) {
			return "Response is not an array.";
		}

		if (array_key_exists("error", $reply_mix)) {
			return $reply_mix['error']['message'];
		}
		elseif (array_key_exists("response", $reply_mix)) {
			return $reply_mix['response']['message'];
		}
		else {
			return "Unknown Error.";
		}
	}

	
	/**
	 * 
	 * add/replace feed URL at twenga
	 * 
	 * @return string response from twenga
	 */

	protected function addFeed() {
		$twengaId_num = $this->getTwengaId();
		$twengaData_arr = $this->getTwengaData($twengaId_num);
		
		$params_arr = array(
			"PARTNER_AUTH_KEY" => $twengaData_arr['tw_partner_auth_key'],
			"key" => $twengaData_arr['tw_hash_key'],
			"feed_url" => $twengaData_arr['tw_feed_url']
		);
		
		$reply_mix = $this->callTwengaApi("feed", $params_arr, "JSON");

		// error handling
		if (!is_array($reply_mix)) {
			return "Response is not an array.";
		}

		if (array_key_exists("error", $reply_mix)) {
			return $reply_mix['error']['message'];
		}
		elseif (array_key_exists("response", $reply_mix)) {
			return $reply_mix['response']['message'];
		}
		else {
			return "Unknown Error.";
		}
	}

		
	
	/**
	 * 
	 * call API to activate tracking
	 * 
	 * @return string response from twenga
	 */
	protected function activateTwenga() {
		$twengaId_num = $this->getTwengaId();
		$twengaData_arr = $this->getTwengaData($twengaId_num);
		
		$params_arr = array(
			"PARTNER_AUTH_KEY" => $twengaData_arr['tw_partner_auth_key'],
			"key" => $twengaData_arr['tw_hash_key']
		);
		
		$reply_mix = $this->callTwengaApi("activate", $params_arr, "JSON");
		
		// error handling
		if (!is_array($reply_mix)) {
			return "Response is not an array.";
		}

		if (array_key_exists("error", $reply_mix)) {
			return $reply_mix['error']['message'];
		}
		elseif (array_key_exists("response", $reply_mix)) {
			return $reply_mix['response']['message'];
		}
		else {
			return "Unknown Error.";
		}
	}

	
	/**
	 * 
	 * call API to validate or cancel order
	 * 
	 * @param bool $validate_bol true for validate, false for cancel
	 * @param array $params_arr twenga params
	 */
	public function confirmOrder($validate_bol, $params_arr) {
		// test if order exists
		$replyExist_mix = $this->callTwengaApi("order_exist", $params_arr, "JSON");
		
		// if basket_id exists validate or cancel order at Twenga
		if ($replyExist_mix == "true") {
			if ($validate_bol) {
				// validate
				$replyValidate_mix = $this->callTwengaApi("order_validate", $params_arr, "JSON");
				// log error
				if (!is_array($replyValidate_mix)) {
					$this->logError("order validate", 0, "twenga response was empty");
				}
				elseif (isset($replyValidate_mix['error'])) {
					$this->logError("order validate", $replyValidate_mix['error']['code'], $replyValidate_mix['error']['message']);
				}
			}
			else {
				// cancel
				$replyCancel_mix = $this->callTwengaApi("order_cancel", $params_arr, "JSON");
			}
		}
		else {
			$this->logError("order exist", 0, "twenga order does not exist");
		}
		
		// delete Twenga from SESSION
		if (isset($_SESSION['twenga'])) unset($_SESSION['twenga']);
	}
	
	
	/**
	 * 
	 * write error log to database
	 * 
	 * @param string $class_str method or such
	 * @param int $ident_num error code
	 * @param string $data_str error message
	 */
	protected function logError($class_str, $ident_num, $data_str) {
		global $db;
		
		$db->Execute("INSERT INTO ".TABLE_SYSTEM_LOG." (class, module, identification, data) VALUES (" .
			"'".$class_str."', 'xt_twenga (".$this->getTwengaId().")', '".$ident_num."', '".$data_str."')");
	}
	
	
	
	/**
	 * 
	 * get tracking script from Twenga
	 * 
	 * @param array $params_arr Twenga parameters
	 * @return string $output_str javascript tags or meta tag with error
	 */
	public function getTrackingScript($params_arr) {
		// make call to Twenga API
		$response_arr = $this->callTwengaApi("tracking", $params_arr, "JSON");
		
		// in case twenga API cannot be reached, response is NOT an array!!
		if (!is_array($response_arr)) {
			$output_str = "\n".'<meta name="twenga-error" content="API could not be reached" />'."\n";
			$this->logError("tracking script", 0, "API could not be reached");
			return $output_str;
		}
		
		// error handling
		if (array_key_exists("error", $response_arr)) {
			$error_str = $response_arr['error']['code']." # ".$response_arr['error']['message'];
			$output_str = "\n".'<meta name="twenga-error" content="'.$error_str.'" />'."\n";
			$this->logError("tracking script", 0, $error_str);
		}
		elseif (array_key_exists("response", $response_arr)) {
			if (!is_array($_SESSION['twenga'])) {
				$_SESSION['twenga'] = array();
			}
			$_SESSION['twenga']['basket_id'] = $params_arr['basket_id'];
			$_SESSION['twenga']['user_id'] = $params_arr['user_id'];
			$_SESSION['twenga']['payment_method'] = $params_arr['payment_method'];
			$output_str = $response_arr['response']['message'];
		}
		else {
			$output_str = "\n".'<meta name="twenga-error" content="unknown" />'."\n";
			$this->logError("tracking script", 0, "unknown");
		}
		
		return $output_str;
	}
	
	
	/**
	 * 
	 * call Twenga API
	 * 
	 * @param string $method_str API method to be called
	 * @param array $params_arr parameters for the call
	 * @param string $format_str response format (XML or JSON)
	 * @return bool|string|array false on failure, string can be empty or filled, array with error or response
	 */
	protected function callTwengaApi($method_str, $params_arr, $format_str="XML") {
		$restricted_bol = true;
		$url_str = "http://rts.twenga.com/api/";
		
		switch ($method_str) {
			case "subscribe":
				$url_str .= "Site/GetSubscriptionLink";
				$restricted_bol = false;
				break;
				
			case "exist":
				$url_str .= "Site/Exist";
				break;
			
			case "activate":
				$url_str .= "Site/Activate";
				break;

			case "tracking":
				$url_str .= "Site/GetTrackingScript";
				break;
			
			case "feed":
				$url_str .= "Site/AddFeed";
				break;
			
			case "order_exist":
				$url_str .= "Order/Exist";
				break;
			
			case "order_validate":
				$url_str .= "Order/Validate";
				break;
			
			case "order_cancel":
				$url_str .= "Order/Cancel";
				break;
			
			default:
				return false;
				break;
		}
		
		// format of response/error; default is XML
		if ($format_str == "JSON") {
			$url_str .= ".JSON/?";
		}
		else {
			$url_str .= ".XML/?";
		}
		
		// check for params
		if (!is_array($params_arr) or empty($params_arr)) {
			return false;
		}
		else {
			$params_str = http_build_query($params_arr, '', '&');
		}
		
		// make curl call
		$curlHandle = curl_init($url_str.$params_str);
		$curlOptions_bol = curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		if ($restricted_bol) {
			$twengaLogin_arr = $this->getTwengaData($this->getTwengaId());
			$curlOptions_bol = curl_setopt($curlHandle, CURLOPT_USERPWD, md5($twengaLogin_arr['tw_login']).':'.md5($twengaLogin_arr['tw_password']));
		}	
		$feedback_str = curl_exec($curlHandle);
		curl_close($curlHandle);
				
		
		// using JSON in all calls now, since transformation (to array) is just this line
		if ($format_str == "JSON") {
			$feedback_str = json_decode($feedback_str, true);
		}
				
		return $feedback_str;
	}
	
	
}

?>