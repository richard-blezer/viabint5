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

/**
 * vat id validation for EU countries
 * validation rules: http://www.pruefziffernberechnung.de/U/USt-IdNr.shtml#PZDK
 *
 */
class vat_id {
	var $vatid,$country_code;

	function vat_id() {

		$this->rules = array();
		$this->rules[] = 'BE';
		$this->rules[] = 'DK';
		$this->rules[] = 'DE';
		$this->rules[] = 'FI';
		$this->rules[] = 'EL';
		$this->rules[] = 'IE';
		$this->rules[] = 'IT';
		$this->rules[] = 'LU';
		$this->rules[] = 'NL';
		$this->rules[] = 'AT';
		$this->rules[] = 'PL';
		$this->rules[] = 'PT';
		$this->rules[] = 'SE';
		$this->rules[] = 'SL';
		$this->rules[] = 'SI';
		$this->rules[] = 'ES';


		$this->liveCheck = array();
		$this->liveCheck[] = 'AD';
		$this->liveCheck[] = 'AT';
		$this->liveCheck[] = 'BE';
		$this->liveCheck[] = 'BG';
		$this->liveCheck[] = 'DK';
		$this->liveCheck[] = 'DE';
		$this->liveCheck[] = 'EE';
		$this->liveCheck[] = 'EL';
		$this->liveCheck[] = 'FI';
		$this->liveCheck[] = 'GI';
		$this->liveCheck[] = 'IE';
		$this->liveCheck[] = 'IS';
		$this->liveCheck[] = 'IT';
		$this->liveCheck[] = 'LV';
		$this->liveCheck[] = 'LT';
		$this->liveCheck[] = 'LU';
		$this->liveCheck[] = 'MT';
		$this->liveCheck[] = 'NL';
		$this->liveCheck[] = 'NO';
		$this->liveCheck[] = 'PL';
		$this->liveCheck[] = 'PT';
		$this->liveCheck[] = 'RO';
		$this->liveCheck[] = 'SE';
		$this->liveCheck[] = 'SI';
		$this->liveCheck[] = 'SK';
		$this->liveCheck[] = 'SL';
		$this->liveCheck[] = 'CZ';
		$this->liveCheck[] = 'HU';
		$this->liveCheck[] = 'CY';

		$this->liveCheck[] = 'FR';
		$this->liveCheck[] = 'GB';
		$this->liveCheck[] = 'IE';
		$this->liveCheck[] = 'ES';
		
		//http://en.wikipedia.org/wiki/VAT_identification_number
		$this->simpleCheck = array();
		//EU
		$this->simpleCheck[] = 'AT'; 	//Austria
		$this->simpleCheck[] = 'BE';	//Belgium
		$this->simpleCheck[] = 'BG';	//Bulgaria
		$this->simpleCheck[] = 'CY';	//Cyprus
		$this->simpleCheck[] = 'CZ';	//Czech Republic
		$this->simpleCheck[] = 'DK';	//Denmark
		$this->simpleCheck[] = 'EE';	//Estonia	
		$this->simpleCheck[] = 'FI';	//Finland
		$this->simpleCheck[] = 'FR';	//France and Monaco
		$this->simpleCheck[] = 'DE';	//Germany
		$this->simpleCheck[] = 'GR';	//Greece
		$this->simpleCheck[] = 'HU';	//Hungary
		$this->simpleCheck[] = 'IE';	//Ireland
		$this->simpleCheck[] = 'IT';	//Italy
		$this->simpleCheck[] = 'LV';	//Latvia
		$this->simpleCheck[] = 'LT';	//Lithuania
		$this->simpleCheck[] = 'LU';	//Luxembourg
		$this->simpleCheck[] = 'MT';	//Malta
		$this->simpleCheck[] = 'NL';	//Netherlands
		$this->simpleCheck[] = 'PL';	//Poland
		$this->simpleCheck[] = 'PT';	//Portugal
		$this->simpleCheck[] = 'RO';	//Romania
		$this->simpleCheck[] = 'SK';	//Slovakia
		$this->simpleCheck[] = 'SI';	//Slovenia
		$this->simpleCheck[] = 'ES';	//Spanien
		$this->simpleCheck[] = 'SE';	//Sweden
		$this->simpleCheck[] = 'GB';	//United Kingdom and Isle of Man
		
		
	}

	/**
	 * check european vat id
	 * 
	 * check by algorithm for known vat id country algorithm, perform live check for unknown 
	 *
	 * @param string $vat_id
	 * @param string $country_code
	 * @return true/false , -2 for no check, -99 for live check servicefault
	 */
	function _check($vat_id,$country_code) {
		$this->country_code = $country_code;
        $this->country_code = strtoupper($this->country_code);

		$param ='/[^a-zA-Z0-9]/';
		$vat_id=preg_replace($param,'',$vat_id);

		$this->vatid = $vat_id;

		$this->vatid = strtoupper($this->vatid);

		// add iso code to vat id
		if (substr($this->vatid, 0, 2) != $this->country_code) {
			$this->vatid = $this->country_code.$this->vatid;
		}
		 
		switch(_STORE_VAT_CHECK_TYPE){
			case 'simple':
				if (in_array($this->country_code,$this->simpleCheck)) {
					return $this->_simpleCheck();
				} else {
					// no simple check for your country available
					return false;
				}
				break;
			case 'complex':
				if (in_array($this->country_code,$this->rules)) {
					$function = '_'.strtoupper($this->country_code);
					$result = $this->$function();
					return $result; 
				} else {
					// no complex check for your country available
					return false;
				}
				break;
			case 'live':
				if (in_array($this->country_code,$this->liveCheck)) {
					return $this->_LIVE();
				} else {
					// no live check available
					return false;
				}
				break;
		}
	}


	function _BE() {

		if (!$this->_checkLength(11)) return false;

		$checksumme = substr($this->vatid,2,7);

		$checksumme = $checksumme % 97;
		$checksumme = 97 - $checksumme;

		$check = (int) substr($this->vatid,-2);
		if ($check != $checksumme) return false;
		return true;

	}

	function _DK() {

		if (!$this->_checkLength(10)) return false;

		$weight = array(2,7,6,5,4,3,2,1);
		$params = array(3,4,5,6,7,8,9,10);
		$checksumme = $this->_calcWeightedSum($weight,$params,'summe');

		if ($checksumme % 11 > 0) return false;
		return true;

	}

	function _DE() {
		if (!$this->_checkLength(11)) return false;

		$m = 10;
		$n = 11;

		$produkt = $m;
		for ($i = 2; $i < 10; $i ++) {
			$summe = ($this->vatid[$i]+$produkt)%$m;
			if ($summe==0) $summe = $m;
			$produkt = (2*$summe) % $n;
		}
		$checksumme = $n-$produkt;
		if (!$this->_checkSingle(11,$checksumme)) return false;
		return true;

	}

	function _FI() {
		if (!$this->_checkLength(10)) return false;

		$weight = array(7,9,10,5,8,4,2);
		$params = array(3,4,5,6,7,8,9);
		$checksumme = $this->_calcWeightedSum($weight,$params,'summe');

		$checksumme = $checksumme % 11;
		$checksumme = 11 - $checksumme;
		if (!$this->_checkSingle(10,$checksumme)) return false;
		return true;

	}

	function _EL() {
		if (!$this->_checkLength(11)) return false;

		$weight = array(256,128,64,32,16,8,4,2);
		$params = array(3,4,5,6,7,8,9,10);
		$checksumme = $this->_calcWeightedSum($weight,$params,'summe');
		$checksumme = $checksumme % 11;
		if ($checksumme>9) $checksumme = 0;
		if (!$this->_checkSingle(11,$checksumme)) return false;
		return true;
	}

	function _IE() {
		if (!$this->_checkLength(11)) return false;
	
		$weight = array(8,7,6,5,4,3,2,1);
		$params = array(64,28,42,15,24,6,10);
		$checksumme = $this->_calcWeightedSum($weight,$params,'summe');
		$checksumme = $checksumme % 23;
		
		if ($checksumme==5) $checksumme = 'E';
		
		if (!$this->_checkSingle(11,$checksumme)) return false;
		return true;
	}
	
	function _IT() {
		if (!$this->_checkLength(13)) return false;

		$weight = array(1,2,1,2,1,2,1,2,1,2);
		$params = array(3,4,5,6,7,8,9,10,11,12);
		$checksumme = $this->_calcWeightedSum($weight,$params,'quersumme');
		$checksumme = $checksumme % 10;
		$checksumme = 10 - $checksumme;
		if ($checksumme==10) $checksumme = 0;
		if (!$this->_checkSingle(13,$checksumme)) return false;
		return true;
	}

	function _LU() {
		if (!$this->_checkLength(10)) return false;

		$checksumme = substr($this->vatid,2,6);
		$checksumme = $checksumme % 89;


		$check = (int) substr($this->vatid,-2);
		if ($check != $checksumme) return false;
		return true;

	}

	function _NL() {
		if (!$this->_checkLength(14)) return false;

		if (!$this->_checkSingle(12,'B')) return false;

		$weight = array(9,8,7,6,5,4,3,2);
		$params = array(3,4,5,6,7,8,9,10);
		$checksumme = $this->_calcWeightedSum($weight,$params,'summe');
		$checksumme = $checksumme % 11;
		if (!$this->_checkSingle(11,$checksumme)) return false;
		return true;

	}


	function _AT() {

		if (!$this->_checkLength(11)) return false;
		if (!$this->_checkSingle(3,'U')) return false;

		$weight = array(1,2,1,2,1,2,1);
		$params = array(4,5,6,7,8,9,10);
		$checksumme = $this->_calcWeightedSum($weight,$params,'quersumme');

		$checksumme = 96 - $checksumme;
		if ($checksumme>10) {
			$checksumme = (string)$checksumme;
			$checksumme = $checksumme[1];
		}
		if (!$this->_checkSingle(11,$checksumme)) return false;
		return true;

	}

	function _PL() {

		if (!$this->_checkLength(12)) return false;


		$weight = array(6,5,7,2,3,4,5,6,7);
		$params = array(3,4,5,6,7,8,9,10,11);
		$checksumme = $this->_calcWeightedSum($weight,$params,'summe');
		$checksumme = $checksumme % 11;

		if (!$this->_checkSingle(12,$checksumme)) return false;
		return true;

	}

	function _PT() {
		if (!$this->_checkLength(11)) return false;

		$weight = array(9,8,7,6,5,4,3,2);
		$params = array(3,4,5,6,7,8,9,10);
		$checksumme = $this->_calcWeightedSum($weight,$params,'summe');
		$checksumme = $checksumme % 11;
		$checksumme = 11-$checksumme;
		if ($checksumme>9) $checksumme=0;
		if (!$this->_checkSingle(11,$checksumme)) return false;
		return true;

	}

	function _SE() {
		if (!$this->_checkLength(14)) return false;

		$weight = array(2,1,2,1,2,1,2,1,2);
		$params = array(3,4,5,6,7,8,9,10,11);
		$checksumme = $this->_calcWeightedSum($weight,$params,'quersumme');
		$checksumme = $checksumme % 10;
		$checksumme = 10-$checksumme;

		if ($checksumme==10) $checksumme=0;
		if (!$this->_checkSingle(12,$checksumme)) return false;
		return true;

	}
	function _SL() {
		if (!$this->_checkLength(10)) return false;

		$weight = array(8,7,6,5,4,3,2);
		$params = array(3,4,5,6,7,8,9);
		$checksumme = $this->_calcWeightedSum($weight,$params,'summe');
		$checksumme = $checksumme % 11;
		$checksumme = 11 - $checksumme;
		if ($checksumme==0) return false;
		if ($checksumme==1) $checksumme = 0;
		if (!$this->_checkSingle(10,$checksumme)) return false;
		return true;

	}
	
	function _SI() {
		if (!$this->_checkLength(11)) return false;
	
		$weight = array(8,7,6,5,4,3,2,1);
		$params = array(40,63,0,40,8,12,6);
		$checksumme = $this->_calcWeightedSum($weight,$params,'summe');
		$checksumme = $checksumme % 11;
		$checksumme = 11-$checksumme;
	
		if (!$this->_checkSingle(11,$checksumme)) return false;
		return true;
	}
	
	function _ES() {
		if (!$this->_checkLength(11)) return false;
	
		$weight = array(2,1,2,1,2,1,2,1);
		$params = array(2,3,10,8,10,6,4);
		$checksumme = $this->_calcWeightedSum($weight,$params,'summe');
		$checksumme = $checksumme % 10;
		$checksumme = 10-$checksumme;

		if (!$this->_checkSingle(11,$checksumme)) return false;
		return true;
	}
	
	
	function _calcWeightedSum($weight,$params,$type = 'quersumme') {

		$sum = 0;
		foreach ($params as $key => $var) {
			switch ($type) {
				case 'quersumme':
					$wert = $this->vatid[$var-1];
					$multiplikator = $weight[$key];
					$wert = $wert*$multiplikator;
					if ($wert>=10) {
						$wert = (string)$wert;
						$wert = $wert[0]+$wert[1];
					}
					$sum += $wert;
					break;
				case 'summe':
					$wert = $this->vatid[$var-1];
					$multiplikator = $weight[$key];
					$wert = $wert*$multiplikator;
					$sum += $wert;
					break;
			}
		}
		return $sum;

	}

	/**
	 * check if string is in correct length
	 *
	 * @param int $length
	 * @return boolean
	 */
	function _checkLength($length) {
		if (strlen($this->vatid)!=$length) return false;
		return true;
	}

	/**
	 * check if single char matches
	 *
	 * @param int $pos
	 * @param string $char
	 * @return boolean
	 */
	function _checkSingle($pos,$char) {
		if($this->vatid[$pos-1]!=$char) return false;
		return true;
	}
	
	

	/**
	 * perform vat id LIVE check at VIES VAT number validation
	 *
	 * @return boolean
	 */
	function _LIVE() {
		global $logHandler;
		
		//nusoap has problem with utf-8
		//old link: http://ec.europa.eu/taxation_customs/vies/api/checkVatPort
		//require_once 'xtFramework/library/nusoap/nusoap.php';

		$vat_id = str_replace($this->country_code,'',$this->vatid);
		$params = array('countryCode'=>$this->country_code,'vatNumber'=>$vat_id);
		$client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
		
		
		try {
			$result = $client->checkVat($params);
		}
		catch (SoapFault $e) {
			//enhanced error reporting
			$result = $e->faultstring;
			$n = preg_match('/\{ \'([A-Z_]*)\' \}/', $result, $matches);
			$result = $matches[1];
			$errors = array(
					'INVALID_INPUT'       => 'The provided CountryCode is invalid or the VAT number is empty',
					'SERVICE_UNAVAILABLE' => 'The SOAP service is unavailable, try again later',
					'MS_UNAVAILABLE'      => 'The Member State service is unavailable, try again later or with another Member State',
					'TIMEOUT'             => 'The Member State service could not be reached in time, try again later or with another Member State',
					'SERVER_BUSY'         => 'The service cannot process your request. Try again later.'
			);
		}
		
		if (!is_object($result)) {
			// save system log
			$log_data = array();
			$log_data['error_str'] = serialize($result);
			$log_data['debug_str'] = $this->vatid;
			$log_data['endpoint'] = "http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl";
			$logHandler->_addLog('error','vatid_check','0',$log_data);
			
			return false;
		}
		
		if ($result->valid==true) return true;
		return false; 

	}
	
	/**
	 * 
	 * check only Lenght, Numberic & Chars of vat number
	 * http://en.wikipedia.org/wiki/VAT_identification_number
	 * 
	 * @return boolean
	 */
	function _simpleCheck(){

		switch ($this->country_code){
			case 'AT': 	//Austria
				if (!$this->_checkLength(11)) return false;
				if (!$this->_checkSingle(3,'U')) return false;
				break;
			case 'BE':	//Belgium
				if (!$this->_checkLength(12)) return false;
				if (!$this->_checkSingle(3,'0')) return false;
				break;
			case 'BG':	//Bulgaria
				if (!$this->_checkLength(11) || !$this->_checkLength(12)) return false;
				break;
			case 'CY':	//Cyprus
				if (!$this->_checkLength(11)) return false;
				break;
			case 'CZ':	//Czech Republic
				if (!$this->_checkLength(10) || !$this->_checkLength(11) || !$this->_checkLength(12)) return false;
				break;
			case 'DK':	//Denmark
				if (!$this->_checkLength(10)) return false;
				break;
			case 'EE':	//Estonia
				if (!$this->_checkLength(11)) return false;
				break;	
			case 'FI':	//Finland
				if (!$this->_checkLength(10)) return false;
				break;
			case 'FR':	//France and Monaco
				if (!$this->_checkLength(13)) return false;
				break;
			case 'DE':	//Germany
				if (!$this->_checkLength(11)) return false;
				break;
			case 'GR':	//Greece
				if (!$this->_checkLength(11)) return false;
				break;
			case 'HU':	//Hungary
				if (!$this->_checkLength(10)) return false;
				break;
			case 'IE':	//Ireland
				if (!$this->_checkLength(10)) return false;
				break;
			case 'IT':	//Italy
				if (!$this->_checkLength(13)) return false;
				break;
			case 'LV':	//Latvia
				if (!$this->_checkLength(13)) return false;
				break;
			case 'LT':	//Lithuania
				if (!$this->_checkLength(11) || !$this->_checkLength(14)) return false;
				break;
			case 'LU':	//Luxembourg
				if(!$this->_checkLength(10)) return false;
				break;
			case 'MT':	//Malta
				if(!$this->_checkLength(10)) return false;
				break;
			case 'NL':	//Netherlands
				if(!$this->_checkLength(14)) return false;
				break;
			case 'PL':	//Poland
				if(!$this->_checkLength(12)) return false;
				break;
			case 'PT':	//Portugal
				if(!$this->_checkLength(11)) return false;
				break;
			case 'RO':	//Romania
				if (strlen($this->vatid) < 4 && strlen($this->vatid) > 12) return false;
				break;
			case 'SK':	//Slovakia
				if(!$this->_checkLength(12)) return false;
				break;
			case 'SI':	//Slovenia
				if(!$this->_checkLength(10)) return false;
				break;
			case 'ES':	//Spanien
				if(!$this->_checkLength(11)) return false;
				break;
			case 'SE':	//Sweden
				if(!$this->_checkLength(14)) return false;
				break;
			case 'GB':	//United Kingdom and Isle of Man
                if(!($this->_checkLength(7) || $this->_checkLength(11) || $this->_checkLength(14))) return false;
				break;
		} 
		return true;
	}

}
?>