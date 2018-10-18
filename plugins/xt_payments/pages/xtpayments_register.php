<?php
 
include_once dirname(__FILE__).'/../../../xtFramework/admin/main.php';
include_once dirname(__FILE__).'/../classes/class.XTPaymentsRegistration.php';

if (!$xtc_acl->isLoggedIn()) {
    die('login required');
}

$register = new XTPaymentsRegistration();
$register->loadRegistrationData($_POST);
if($register->checkRegistrationData()) {
	
	$soapResponseOk = $register->registerXTpayments();
	
	if($soapResponseOk!=0){
		//get success page from external URL
		$ch = curl_init();
		$timeout = 5;
		$baseName = basename(XT_PAYMENTS_REGISTRATION_SUCCESS_PAGE_URL);
		$dirnameName = dirname(XT_PAYMENTS_REGISTRATION_SUCCESS_PAGE_URL);
		$successPage = $dirnameName."/".(isset($_SESSION["selected_language"]) ? $_SESSION["selected_language"]."_" : "en_").$baseName;
		
		$file_headers = @get_headers($successPage);
		if($file_headers[0] == 'HTTP/1.1 404 Not Found' || $file_headers[0] == 'HTTP/1.0 404 Not Found') {
			$exists = false;
		}
		else {
			$exists = true;
		}
		
		if(!$exists) {
			$successPage = $dirnameName."/"."en_".$baseName;
		}
		
		curl_setopt($ch, CURLOPT_URL, $successPage);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$successPageContent = curl_exec($ch);
		curl_close($ch);
		
		//check default order statuses
		$detaultOrderStatusesOk = $register->checkDefaultOrderStatuses();
		
		$rs = $db->Execute("select payment_id from " . TABLE_PAYMENT . " where payment_code='xt_payments'");
		
		if ($soapResponseOk==1) $thank_you_text = '';
		else $thank_you_text = XT_PAYMENTS_REACTIVATION_THANKYOU_TEXT;
		

		$cpanelParams = new XTPaymentsRegistration();
		$tpl_data = array('payment_configuration_id'=>$rs->fields['payment_id'], 'payment_configuration_text'=>TEXT_PAYMENT.' edit', 
			'payment_cpanel_text'=>TEXT_XT_PAYMENTS_CONFIGURATION, 'payment_cpanel_url'=>$cpanelParams->cpanel_url."payment_methods/payment_methods.php", 
			'detaultOrderStatusesOk'=>$detaultOrderStatusesOk,
			'XT_PAYMENTS_REGISTRATION_THANKYOU_TITLE'=>XT_PAYMENTS_REGISTRATION_THANKYOU_TITLE,
			'XT_PAYMENTS_REGISTRATION_THANKYOU_SUBTITLE'=>XT_PAYMENTS_REGISTRATION_THANKYOU_SUBTITLE,
			'XT_PAYMENTS_REGISTRATION_THANKYOU_TEXT'=>XT_PAYMENTS_REGISTRATION_THANKYOU_TEXT,
			'XT_PAYMENTS_REGISTRATION_THANKYOU_TEXT2'=>$thank_you_text,
			'XT_PAYMENTS_REGISTRATION_ORDER_STATUSES_NOT_CONFIGURED'=>XT_PAYMENTS_REGISTRATION_ORDER_STATUSES_NOT_CONFIGURED,
			'XT_PAYMENTS_REGISTRATION_SHOP_CONFIGURATION_LINK'=>XT_PAYMENTS_REGISTRATION_SHOP_CONFIGURATION_LINK,
			'XT_PAYMENTS_REGISTRATION_PAYMENT_METHODS_CONFIGURATION_LINK'=>XT_PAYMENTS_REGISTRATION_PAYMENT_METHODS_CONFIGURATION_LINK,
			'successPageContent'=>$successPageContent
			);
		$html = '';
		$tpl = 'xtpayments_successful_registration.html';
		$template = new Template();
		$template->getTemplatePath($tpl, 'xt_payments', '', 'plugin');
		$html = $template->getTemplate('xtpayments_successful_registration_smarty', $tpl, $tpl_data);
		echo $html.'
		<script> 
			$(\'#dynamicFormContainer\').hide();
		</script> 
		';
	}
}
else {

	echo '<div class="registrationError">'.$register->errorMessage.'</div>
	<script> 
		$(\'#dynamicFormContainer\').show();
	</script> 
	';
}
?>