<?php
 
include_once 'class.XTPaymentsConnector.php';

class GetPaymentMethodDetailsByMerchantContainer extends XTPaymentsConnector {
	
	public $paymentMethods;
	public $xtPaymentsAPMListServiceUrl;

	function GetPaymentMethodDetailsByMerchantContainer(){
	
		if(XT_PAYMENTS_TEST_MODE){
            $this->xtPaymentsAPMListServiceUrl    = XT_PAYMENTS_TEST_APM_LIST_SERVICE_URL;
		}
		else{
            $this->xtPaymentsAPMListServiceUrl    = XT_PAYMENTS_LIVE_APM_LIST_SERVICE_URL;
        }
		
		parent::__construct($this->xtPaymentsAPMListServiceUrl, true);
	}
	
	public function getPaymentMethodDetailsByMerchant($parameters){
	
		$soapResponse = parent::getPaymentMethodDetailsByMerchant($parameters);
		
		$this->_loadObject($soapResponse);
	}
	
	private function _loadObject($soapResponse){
	
		if(isset($soapResponse["PaymentOptionsDetails"]["displayInfo"])) {
			
			$details = new GetPaymentMethodDetailsByMerchantContainerDetails();
			$details->loadObject($soapResponse["PaymentOptionsDetails"]);
			$this->paymentMethods[] = $details;
			
		}
		else if(isset($soapResponse["PaymentOptionsDetails"])){
			foreach($soapResponse["PaymentOptionsDetails"] as $apmDetails) {
			
				$details = new GetPaymentMethodDetailsByMerchantContainerDetails();
				$details->loadObject($apmDetails);
				$this->paymentMethods[] = $details;
			}
		}
	}
}

class ApmAdditionaField {

	// Correlations
	public $AccountInfoFieldName;
	public $ValidationRegex;
	public $ValidationMessage;
	public $ControlType;
	public $FieldCaption;
	public $SelectOptions;
	public $AvailableControlTypes;
	
	function ApmAdditionaField() {
	
		$this->AvailableControlTypes = array(
			"textbox" => "textbox",
			"select" => "select"
		);
	}
	
	public function loadObject($soapResponse) {
	
		$this->AccountInfoFieldName = $soapResponse["fieldName"];
		$this->FieldCaption = $soapResponse["fieldCaption"];
		$this->ControlType = $soapResponse["controlType"];
	
		if($this->ControlType == $this->AvailableControlTypes["select"]) {
		
			if(isset($soapResponse["correlationOptions"]["option"]) && is_array($soapResponse["correlationOptions"]["option"])) {
				foreach($soapResponse["correlationOptions"]["option"] as $option) {
					$this->SelectOptions[] = array("key" => $option["key"], "value" => $option["value"]);
				}
			}
			$this->ValidationRegex = "(.)*";
			$this->ValidationMessage = "";
		}
		else {
			
			$this->ValidationRegex = $soapResponse["validationRegex"];
			$this->ValidationMessage = $soapResponse["validationMessage"];
		}
		
	}
}

class GetPaymentMethodDetailsByMerchantContainerDetails {
	
	public $APMID;
	public $APMName;
	public $APMCode;
	
	// APMDisplayInfo block
	public $IsiFrame;
	public $Width;
	public $Height;
	
	// APMAdditionalInfo 
	public $IsCollector;
	public $IsMobileSupported;
	public $URLLogoSmall;
	public $URLLogoLarge;
	
	// Correlations
	public $correlations;
	
	public function loadObject($apmDetails) {
	
		$this->IsMobileSupported = isset($apmDetails["mobileSupported"]) ? $apmDetails["mobileSupported"] : null;
		$this->APMCode = isset($apmDetails["optionName"]) ? $apmDetails["optionName"] : null;
		$this->APMID = isset($apmDetails["paymentOptionUniqueId"]) ? $apmDetails["paymentOptionUniqueId"] : null;
		
		//displayInfo
		$this->APMName = isset($apmDetails["displayInfo"]["paymentOptionDisplayName"]) ? $apmDetails["displayInfo"]["paymentOptionDisplayName"] : null;
		
		$this->Height = isset($apmDetails["displayInfo"]["height"]) ? $apmDetails["displayInfo"]["height"] : null;
		$this->IsiFrame = isset($apmDetails["displayInfo"]["iframe"]) ? $apmDetails["displayInfo"]["iframe"] : null;
		$this->Width = isset($apmDetails["displayInfo"]["width"]) ? $apmDetails["displayInfo"]["width"] : null;

		$this->URLLogoLarge = isset($apmDetails["displayInfo"]["urlLogoLarge"]) ? $apmDetails["displayInfo"]["urlLogoLarge"] : null;
		$this->URLLogoSmall = isset($apmDetails["displayInfo"]["urlLogoSmall"]) ? $apmDetails["displayInfo"]["urlLogoSmall"] : null;
		
		if(is_array($apmDetails["paymentOptionCorrelations"])) {
			foreach($apmDetails["paymentOptionCorrelations"] as $apmCorrelations){
				if(isset($apmCorrelations["controlType"])) {
					
					if(!strlen($apmCorrelations['fieldCaption']) || !strlen($apmCorrelations['fieldName']))
						continue;
					$apmAdditionalField = new ApmAdditionaField();
					$apmAdditionalField->loadObject($apmCorrelations);
					$this->correlations[] = $apmAdditionalField;
				} else {
					foreach($apmCorrelations as $apmCorrelation) {
						if(!strlen($apmCorrelation['fieldCaption']) || !strlen($apmCorrelation['fieldName']))
							continue;
						$apmAdditionalField = new ApmAdditionaField();
						$apmAdditionalField->loadObject($apmCorrelation);
						$this->correlations[] = $apmAdditionalField;
					}
				}
			}
		}
	}
}