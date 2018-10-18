<?php

include_once 'class.XTPaymentsConnector.php';

class XTPaymentsRegistration extends XTPaymentsConnector {

	public $vat_no;
	public $lic_id;
	public $lic_type_id;
	public $customers_id;
	public $lic_company;
	public $lic_firstname;
	public $lic_lastname;
	public $lic_street;
	public $lic_telephone;
	public $lic_plz;
	public $lic_city;
	public $lic_country_id;
	public $lic_mailaddress;
	public $lic_key;
	public $lic_created;
	public $sf_account_id;
	public $html_template;
	public $message;
	public $success;
	public $domain;
	public $user_lang;
	public $lang_code;

	public $cpanel_url;
	private $cpanel_password;
	private $cpanel_name;

	public $errorMessage;

	function XTPaymentsRegistration($wsdl= XT_PAYMENTS_REGISTRATION_SERVICE_URL, $useWSDL = false){

		parent::__construct($wsdl, $useWSDL);

		$this->_setCPanelCredentials();
	}

	private function _setCPanelCredentials(){

		if(XT_PAYMENTS_TEST_MODE){
			$this->cpanel_url    = defined('XT_PAYMENTS_TEST_CPANELURL') ? XT_PAYMENTS_TEST_CPANELURL : "";
			$this->cpanel_password = defined('XT_PAYMENTS_TEST_CPANELPASSWORD') ? XT_PAYMENTS_TEST_CPANELPASSWORD : "";
			$this->cpanel_name  = defined('XT_PAYMENTS_TEST_CPANELMERCHANTNAME') ? XT_PAYMENTS_TEST_CPANELMERCHANTNAME : "";
		}
		else{
			$this->cpanel_url    = defined('XT_PAYMENTS_LIVE_CPANELURL') ? XT_PAYMENTS_LIVE_CPANELURL : "";
			$this->cpanel_password = defined('XT_PAYMENTS_LIVE_CPANELPASSWORD') ? XT_PAYMENTS_LIVE_CPANELPASSWORD : "";
			$this->cpanel_name  = defined('XT_PAYMENTS_LIVE_CPANELMERCHANTNAME') ? XT_PAYMENTS_LIVE_CPANELMERCHANTNAME : "";
		}
	}

	private function _getRegisterParameters(){

		$parameters = array();
		$parameters['lic_id']=$this->lic_id;
		$parameters['lic_type_id']=$this->lic_type_id;
		$parameters['lic_company']=$this->lic_company;
		$parameters["lic_firstname"] = $this->lic_firstname;
		$parameters["lic_lastname"] = $this->lic_lastname;
		$parameters["lic_street"] = $this->lic_street;
		$parameters["lic_telephone"] = $this->lic_telephone;
		$parameters["lic_plz"] = $this->lic_plz;
		$parameters["lic_city"] = $this->lic_city;
		$parameters['lic_country_id']=$this->lic_country_id;
		$parameters["lic_mailaddress"] = $this->lic_mailaddress;
		$parameters['lic_key'] = $this->lic_key;
		$parameters['lic_created'] = $this->lic_created;
		$parameters['sf_account_id'] = $this->sf_account_id;
		$parameters['vat_no'] = $this->vat_no;
		$parameters['user_lang'] = $this->user_lang;
		$parameters['lang_code'] = $this->lang_code;
		$parameters["domain"] = $this->domain;

		return $parameters;
	}

	public function loadRegistrationData($postData){

		$this->lic_type_id = $postData["lic_type_id"];
		$this->lic_company = $postData["lic_company"];
		$this->lic_firstname = $postData["lic_fistname"];
		$this->lic_lastname = $postData["lic_lastname"];
		$this->lic_street = $postData["lic_street"];
		$this->lic_telephone = $postData["lic_telephone"];
		$this->lic_plz = $postData["lic_plz"];
		$this->lic_city = $postData["lic_city"];
		$this->lic_country_id = $postData["lic_country_id"];
		$this->lic_mailaddress = $postData["lic_mailaddress"];
		$this->lic_key = $postData["lic_key"];
		$this->lic_created = $postData["lic_created"];
		$this->sf_account_id = $postData["sf_account_id"];
		$this->vat_no = $postData["vat_no"];
		$this->lic_id = $postData["lic_id"];
		$this->user_lang = $_SESSION['selected_language'];
		$this->domain = $_SERVER['HTTP_HOST'];

	}

	public function checkRegistrationData(){

		if(!strlen($this->lic_firstname)) {$this->errorMessage = XT_PAYMENTS_REGISTRATION_EMPTY_FIRST_NAME; return false;}
		if(!strlen($this->lic_lastname)) {$this->errorMessage = XT_PAYMENTS_REGISTRATION_EMPTY_LAST_NAME; return false;}
		if(!strlen($this->lic_company)) {$this->errorMessage = XT_PAYMENTS_REGISTRATION_EMPTY_COMPANY; return false;}
		//if(!strlen($this->vat_no)) {$this->errorMessage = "Please enter your Vat No"; return false;}
		if(!strlen($this->lic_street)) {$this->errorMessage = XT_PAYMENTS_REGISTRATION_EMPTY_STREET_ADDRESS; return false;}
		if(!strlen($this->lic_plz)) {$this->errorMessage = XT_PAYMENTS_REGISTRATION_EMPTY_ZIP; return false;}
		if(!strlen($this->lic_city)) {$this->errorMessage = XT_PAYMENTS_REGISTRATION_EMPTY_CITY; return false;}
		if(!strlen($this->lic_telephone)) {$this->errorMessage = XT_PAYMENTS_REGISTRATION_EMPTY_TELEPHONE; return false;}
		if(!strlen($this->lic_mailaddress)) {$this->errorMessage = XT_PAYMENTS_REGISTRATION_EMPTY_EMAIL_ADDRESS; return false;}
		if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $this->lic_mailaddress)) {$this->errorMessage = XT_PAYMENTS_REGISTRATION_INVALID_EMAIL_ADDRESS; return false;}

		/*if(!strlen($this->sf_account_id)) {$this->errorMessage = "Missing sf account id"; return false;}
		//if(!strlen($this->lic_id)) {$this->errorMessage = "Missing lic id"; return false;}
		if(!strlen($this->lic_key)) {$this->errorMessage = "Missing lic key"; return false;}
		if(!strlen($this->lic_created)) {$this->errorMessage = "Missing lic created"; return false;}
		if(!strlen($this->lic_type_id)) {$this->errorMessage = "Missing lic type id"; return false;}
		*/
		return true;
	}

	public function registerXTpayments(){

		global $db;

		$parameters = $this->_getRegisterParameters();

		$soapResponse = parent::registerXTpayments($parameters);

		if($soapResponse["success"]=="1"){

			/* Save returned from registration data into payments_tmp */
			$db->Execute("TRUNCATE TABLE ".DB_PREFIX."_payments_tmp");

			$sp = $soapResponse["result"];
			foreach($sp["test"] as $key => $value) {

				$db->Execute("INSERT INTO ".DB_PREFIX."_payments_tmp (`payment_code`,`payment_type`,`col_key`,`col_value` ) VALUES('xt_payments', 'test', '".$key."', '".$value."');");
			}
			foreach($sp["live"] as $key => $value) {

				$db->Execute("INSERT INTO ".DB_PREFIX."_payments_tmp (`payment_code`,`payment_type`,`col_key`,`col_value` ) VALUES('xt_payments', 'live', '".$key."', '".$value."');");
			}
			/* -- end -- Save returned from registration data into payments_tmp */

			$this->_saveMerchantAccountInfo($soapResponse["result"]);
			//$db->Execute("update " . TABLE_CONFIGURATION_PAYMENT . " set config_value='true' WHERE config_key='XT_PAYMENTS_REGISTERED'");
			return 1; // return true;

		}
		/* LIcense has been already activated */
		else if($soapResponse["error_code"]=="9"){

			$rsss22= $db->Execute("Select count(*) As row FROM ".DB_PREFIX."_payments_tmp where payment_code = 'xt_payments'");
			/* If payments_tmp has stored license information => activate xt_payments */

			if($rsss22->fields['row']>0)
			{
				$rss = $db->Execute("Select * from ".DB_PREFIX."_payments_tmp where payment_code = 'xt_payments'  ");
				$resARR = array(array());

				if (count($rss)>0)
				{
					while (!$rss->EOF)
					{
						$resARR[$rss->fields['payment_type']][$rss->fields['col_key']] = $rss->fields['col_value'];
						$rss->MoveNext();
					}
				}

				$this->_saveMerchantAccountInfo($resARR);
				//$db->Execute("update " . TABLE_CONFIGURATION_PAYMENT . " set config_value='true' WHERE config_key='XT_PAYMENTS_REGISTERED'");

				return 2;
			}
			else{
				echo '<div class="registrationError">'.$soapResponse["message"].'</div>
					<script> 
						$(\'#dynamicFormContainer\').show();
					</script> ';
				return 0;
			}
		}
		/* -- end -- LIcense has been already activated */
		else{
			echo '<div class="registrationError">'.$soapResponse["message"].'</div>
			<script> 
				$(\'#dynamicFormContainer\').show();
			</script> ';
			return 0; //return false;
		}
	}

	private function _saveMerchantAccountInfo($soapResponse){

		global $db, $store_handler;

		$rs = $db->Execute("select payment_id from " . TABLE_PAYMENT . " where payment_code='xt_payments'");

		$stores = $store_handler->getStores();
		foreach ($stores as $sdata) {
			$shop_id = $sdata['id'];

			foreach($soapResponse["test"] as $key => $value) {

				$additionalValue = "";
				if(strtoupper($key)=="PPPURL") {
					$sqlNewConfigValue = "replace into " . TABLE_CONFIGURATION_PAYMENT . " (config_key, config_value, group_id, sort_order, last_modified, date_added, type, url, payment_id, shop_id) 
					values('XT_PAYMENTS_TEST_APM_LIST_SERVICE_URL', '".$value."PaymentOptionInfoService?wsdl', '0', '1', now(), now(), 'hidden', '',  '".$rs->fields['payment_id']."', '".$shop_id."')";

					$db->Execute($sqlNewConfigValue);

					$additionalValue = "ppp/purchase.do";
				}

				if(strtoupper($key)=="CPANELURL") {
					define('XT_PAYMENTS_TEST_CPANELURL', $value);
				}

				if(strtoupper($key)=="PPPWEBSITEID") {
					$sqlNewConfigValue = "replace into " . TABLE_CONFIGURATION_PAYMENT . " (config_key, config_value, group_id, sort_order, last_modified, date_added, type, url, payment_id, shop_id) 
					values('XT_PAYMENTS_TEST_".strtoupper($key)."', '".$value.$additionalValue."', '0', '1', now(), now(), 'textfield', '',  '".$rs->fields['payment_id']."', '".$shop_id."')";

				} else {
					$sqlNewConfigValue = "replace into " . TABLE_CONFIGURATION_PAYMENT . " (config_key, config_value, group_id, sort_order, last_modified, date_added, type, url, payment_id, shop_id)
					values('XT_PAYMENTS_TEST_".strtoupper($key)."', '".$value.$additionalValue."', '0', '1', now(), now(), 'hidden', '',  '".$rs->fields['payment_id']."', '".$shop_id."')";
				}

				$db->Execute($sqlNewConfigValue);

			}

			foreach($soapResponse["live"] as $key => $value) {

				$additionalValue = "";
				if(strtoupper($key)=="PPPURL") {
					$sqlNewConfigValue = "replace into " . TABLE_CONFIGURATION_PAYMENT . " (config_key, config_value, group_id, sort_order, last_modified, date_added, type, url, payment_id, shop_id) 
					values('XT_PAYMENTS_LIVE_APM_LIST_SERVICE_URL', '".$value."PaymentOptionInfoService?wsdl', '0', '1', now(), now(), 'hidden', '',  '".$rs->fields['payment_id']."', '".$shop_id."')";

					$db->Execute($sqlNewConfigValue);
					$additionalValue = "ppp/purchase.do";
				}

				if(strtoupper($key)=="CPANELURL") {
					define('XT_PAYMENTS_LIVE_CPANELURL', $value);
				}

				if(strtoupper($key)=="PPPWEBSITEID") {
					$sqlNewConfigValue = "replace into " . TABLE_CONFIGURATION_PAYMENT . " (config_key, config_value, group_id, sort_order, last_modified, date_added, type, url, payment_id, shop_id) 
					values('XT_PAYMENTS_LIVE_".strtoupper($key)."', '".$value.$additionalValue."', '0', '0', now(), now(), 'textfield', '',  '".$rs->fields['payment_id']."', '".$shop_id."')";

				} else {
					$sqlNewConfigValue = "replace into " . TABLE_CONFIGURATION_PAYMENT . " (config_key, config_value, group_id, sort_order, last_modified, date_added, type, url, payment_id, shop_id)
					values('XT_PAYMENTS_LIVE_".strtoupper($key)."', '".$value.$additionalValue."', '0', '0', now(), now(), 'hidden', '',  '".$rs->fields['payment_id']."', '".$shop_id."')";
				}

				$db->Execute($sqlNewConfigValue);

			}

			$db->Execute("update " . TABLE_CONFIGURATION_PAYMENT . " set config_value='true' WHERE config_key='XT_PAYMENTS_REGISTERED' && shop_id='".$shop_id."'");
		}
		// activate method
		$db->Execute("update " . TABLE_PAYMENT . " set status='1' WHERE payment_id='".$rs->fields['payment_id']."'");
	}

	public function checkDefaultOrderStatuses(){

		global $db;
		$rs = $db->Execute("select 1 as status_ok from ".TABLE_SYSTEM_STATUS." where status_id='17'");
		$pendingStatusOk = intval($rs->fields['status_ok']) ? true : false;

		$rs = $db->Execute("select 1 as status_ok from ".TABLE_SYSTEM_STATUS." where status_id='23'");
		$approvedStatusOk = intval($rs->fields['status_ok']) ? true : false;

		$rs = $db->Execute("select 1 as status_ok from ".TABLE_SYSTEM_STATUS." where status_id='32'");
		$declinedStatusOk = intval($rs->fields['status_ok']) ? true : false;

		$rs = $db->Execute("select 1 as status_ok from ".TABLE_SYSTEM_STATUS." where status_id='34'");
		$errorStatusOk = intval($rs->fields['status_ok']) ? true : false;

		return ($pendingStatusOk && $approvedStatusOk && $declinedStatusOk && $errorStatusOk);
	}

	public function getUserdata($parameters){

		$soapResponse = parent::getUserdata($parameters);
		$this->_loadObject($soapResponse);
	}

	private function _loadObject($soapResponse){

		$this->lic_id = $soapResponse["result"]["lic_id"];
		$this->lic_type_id = $soapResponse["result"]["lic_type_id"];
		$this->customers_id = $soapResponse["result"]["customers_id"];
		$this->lic_company = $soapResponse["result"]["lic_company"];
		$this->lic_firstname = $soapResponse["result"]["lic_firstname"];
		$this->lic_lastname = $soapResponse["result"]["lic_lastname"];
		$this->lic_street = $soapResponse["result"]["lic_street"];
		$this->lic_telephone = $soapResponse["result"]["lic_telephone"];
		$this->lic_plz = $soapResponse["result"]["lic_plz"];
		$this->lic_city = $soapResponse["result"]["lic_city"];
		$this->lic_country_id = $soapResponse["result"]["lic_country_id"];
		$this->lic_mailaddress = $soapResponse["result"]["lic_mailaddress"];
		$this->lic_key = $soapResponse["result"]["lic_key"];
		$this->lic_created = $soapResponse["result"]["lic_created"];
		$this->sf_account_id = $soapResponse["result"]["sf_account_id"];
		$this->vat_no = $soapResponse["result"]["vat_no"];
		$this->user_lang = $soapResponse["result"]["user_lang"];
		$this->html_template = $soapResponse["result"]["html_template"];
		$this->message = $soapResponse["message"];
		$this->success = $soapResponse["success"];
	}

	public function setPosition(){}
	public function _getParams(){}

	public function xtPaymentsCPanelLoginCredentials() {

		$data = array('username' => $this->cpanel_name, 'password' => md5($this->cpanel_password));
		$url = $this->cpanel_url.'xt/specialLogin.php';//'http://srv-bsf-cpanel03.gw-4u.com/test/specialLogin.php';

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}

	public function xtPaymentsCPanelLogin() {

		if(strlen($this->cpanel_name) && strlen($this->cpanel_password)){
			$result = json_decode($this->xtPaymentsCPanelLoginCredentials());
			return '<iframe style="border: 0px; height: 100%; width: 100%;" src="'.$this->cpanel_url.'payment_methods/payment_methods.php?token='.$result->token.'&XT_SHOP_SESSION='.$result->session.'"></iframe>';
		}
		else{
			return '<div class="registrationError">Please, register for xtPayments first : <a href="#", onclick="addTab(\'adminHandler.php?plugin=xt_payments&load_section=XTPaymentsRegistration&pg=xtPaymentsCommercialPage\',\'xt:Commerce Payments Registration\');">REGISTER</a></div>';
		}
	}

	public function xtPaymentsCommercialPage() {

		$ch = curl_init();

		$timeout = 5;
		$baseName = basename(XT_PAYMENTS_COMMERCIAL_PAGE_URL);
		$dirnameName = dirname(XT_PAYMENTS_COMMERCIAL_PAGE_URL);
		$commPage = $dirnameName."/".(isset($_SESSION["selected_language"]) ? $_SESSION["selected_language"]."_" : "en_").$baseName;

		$file_headers = @get_headers($commPage);
		if($file_headers[0] == 'HTTP/1.1 404 Not Found' || $file_headers[0] == 'HTTP/1.0 404 Not Found') {
			$exists = false;
		}
		else {
			$exists = true;
		}

		if(!$exists) {
			$commPage = $dirnameName."/"."en_".$baseName;
		}

		curl_setopt($ch, CURLOPT_URL, $commPage);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);

		curl_close($ch);
		//$data = '<input type="button" id="registerButton" value="REGISTER" />';
		$data = '
			<script>
			function xtPaymentsRegistration()
			{
				$.post("../plugins/xt_payments/pages/xtpayments_register_form.php", function(data) {
					$("#registrationContent").html(data);
				});	
			}
			</script>

			<div id="registrationContent">
				'.$data.'
				<script>
					$("#registerButton").click(function() {
						$("#registrationContent").html("<div class=\"loading-indicator\">Loading...</div>");
						xtPaymentsRegistration();
						
					});
				</script>
			</div>';
		return $data;

	}

	public function xtPaymentsRegistrationProcess() {

		$aInstalledCredentials = $this->_xtPaymentsGetInstalledCredentials();

		if(strlen($aInstalledCredentials['lic_key']) && strlen($aInstalledCredentials['email_address'])) {

			$soapParams = array('lic_key'=>$aInstalledCredentials['lic_key'],'email_address'=>$aInstalledCredentials['email_address'], 'lang_code' => $_SESSION['selected_language']);
			$this->getUserdata($soapParams);

			$toc_url = "http://xtpayments.com/en/terms-and-conditions.html";
			if($_SESSION['selected_language']=="de"){
				$toc_url = "http://xtpayments.com/allgemeine-gesch%C3%A4ftsbedingungen.html";
			}
			if  (!isset($this->lic_id)) {
				return $this->message;
			}
			return '
			<div id="dynamicFormMessage"></div>
				<div id="dynamicFormContainer">
					'.base64_decode($this->html_template).'
				</div>
				<style>
				.deactivateRegisterButton{
					background: none !important;
					background-color: #c0c0c0 !important;
				}
				</style>
				<script> 
					
					var options = { 
						beforeSubmit:  function() { $("#dynamicFormContainer").hide(); $("#dynamicFormMessage").html("<div class=\"loading-indicator\">Loading...</div>"); },  // pre-submit callback 
						success:       function(responseText) { $(\'#dynamicFormMessage\').html(responseText); }  // post-submit callback
					}; 
					
					var dynamicForm = $(\'#dynamicFormContainer form\');/*var dynamicForm = $(\'#dynamicFormContainer\').children("form");*/
					
					// bind \'dynamicFormContainer\' and provide a simple callback function 
					dynamicForm.attr("action","../plugins/xt_payments/pages/xtpayments_register.php");
					dynamicForm.attr("method","post");
					dynamicForm.attr("id","registerFormId");
					
					dynamicForm.ajaxForm(options);
					
					$("#registerButton").toggleClass(\'deactivateRegisterButton\');
					
					dynamicForm.find(\'input[type=checkbox]\').each(function(i, field){
						$(field).bind(
							"click",
							function() {
								
								$("#registerButton").toggleClass(\'deactivateRegisterButton\');
							}
						);
					
					});
					
					$("#registerButton").click(function() {
						dynamicForm.find(\'input[type=checkbox]\').each(function(i, field){
							if($(field).is(\':checked\')){
								$("form#registerFormId").submit();
							}
							/*else{
								alert(\'Please accept toc\');
							}*/
						});
						
					});
				</script> 
			';
		}
		else {
			return 'Error while trying to register xtPayments';
		}
	}

	private function _xtPaymentsGetInstalledCredentials() {

		$_lic = _SRV_WEBROOT . 'lic/license.txt';
		if (!file_exists($_lic))
			die('- main lic missing -');
		$val_line = '';
		$bline = '';
		$_file_content = file($_lic);
		foreach ($_file_content as $bline_num => $bline)
		{
			if (ereg('key:', $bline))
			{
				$val_line = $bline;
				break;
			}
		}

		$val_line = explode(':', $val_line);
		$_shop_lic = '';
		$_shop_lic = trim($val_line[1]);

		foreach ($_file_content as $bline_num => $bline)
		{
			if (ereg('mailaddress:', $bline))
			{
				$val_line = $bline;
				break;
			}
		}

		$val_line = explode(':', $val_line);
		$_shop_mail = '';
		$_shop_mail = trim($val_line[1]);

		return array('lic_key' => $_shop_lic, 'email_address' => $_shop_mail);
	}
}