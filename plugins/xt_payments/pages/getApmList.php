<?php

ini_set('include_path', dirname(__FILE__).'/../../../');
include_once "xtCore/main.php";
include_once 'plugins/xt_payments/classes/class.GetPaymentMethodDetailsByMerchantContainer.php';

global $language, $currency;

$srvc = new GetPaymentMethodDetailsByMerchantContainer();

$srvc->getPaymentMethodDetailsByMerchant(array(
    'merchantId'    => $srvc->PPPMerchantID,
    'merchantSiteId'        => $srvc->PPPWebsiteID,
   // 'CustomerEmail'                 => $_SESSION['customer']->customer_info['customers_email_address'],
    'countryIsoCode'     => $_SESSION['customer']->customer_payment_address['customers_country_code'],
    'languageCode'          => $language->code,
    'currencyIsoCode'    => $currency->code,
    'gwMerchantName'    => $srvc->GWMerchantname,
    'gwPassword'    => $srvc->GWPassword
));

unset($_SESSION['APMGWList']);
$APMGWList = $srvc->paymentMethods;
if (!isset($APMGWList) && !is_array($APMGWList)){
    $APMGWList = array();
}
$_SESSION['APMGWList'] = $APMGWList;

foreach($APMGWList as $apm){ 
	if($apm->APMCode == "cc_card") {
		$apm->APMName = TEXT_PAYMENT_CARDS;
	}
}
$_SESSION['APMGWList'] = $APMGWList;

$template = new Template();
$template->getTemplatePath('apm.html', 'xt_payments', '', 'payment');
$tmp_data = $template->getTemplate(xt_payments_payment_smarty, 'apm.html', array('apms'=>$APMGWList));

?><script>
    arrPaymentCorrelationCaption = new Array();
    arrPaymentCorrelationRegex = new Array();
    arrPaymentCorrelationErrMsg = new Array();
</script>
<?php echo $tmp_data; ?><script>
    initApms();
</script>