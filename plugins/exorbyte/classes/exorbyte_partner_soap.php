<?php
/*
--------------------------------------------------------------
   exorbyte_soap.php 2011-06-22
   exorbyte GmbH
   Line-Eid-Str. 1
   78467 Konstanz
   http://commerce.exorbyte.de
   Copyright (c) 2011 exorbyte GmbH, 
   author: Daniel Gebuehr
   --------------------------------------------------------------
   soap integration class to access the exorbyte database
   --------------------------------------------------------------
*/
defined('WSDL_URL') || define('WSDL_URL','https://management.exorbyte.com/Partner-Soap/soap?wsdl');
//defined('WSDL_URL') || define('WSDL_URL','https://test-management.sellbysearch.com/Partner-Soap/soap?wsdl');
defined('WSDL')     || define('WSDL','https://management.exorbyte.com/Partner-Soap/soap?wsdl');
//defined('WSDL')     || define('WSDL','https://test-management.sellbysearch.com/Partner-Soap/soap?wsdl');
                                                                                                     


                                                                                                        
class ecos_soap {
	 /**
     * WSDL URL of the ECOS Web Server SOAP API
     * @var string
     */
	protected static $WSDLURL=WSDL_URL;
	protected $_oSoap;
	public $_logtable='ecos_soap_log';
	protected $_WSDLdebug=true;
	/**
     * Template for the ex_user, overriden for  max compatibility, e.g. harmless 
     * @var string
     */
	protected $aSoap_options=array('trace'=>true);
	public $aData;
	protected $aFunctions;
	protected $aOldData;
	protected $blEx_ecos_CustomerOnSave=false;
	protected $_lastXMLReq;
	protected $_ret;
	protected $_lastXMLRes;

	
	function __construct(){
	}
	
  protected function init_ecos_soap(){
    try{
		  if($this->_WSDLdebug){
			   ini_set("soap.wsdl_cache_enabled", "0"); 
		  }
		  $this->_oSoap=new SoapClient(WSDL,$this->aSoap_options);
		  $ret=true;
		  $this->_oSoap->__setCookie('XDEBUG_SESSION','htr');

		  $this->aFunctions=$this->_oSoap->__getFunctions();
		  $this->aTypes=$this->_oSoap->__getTypes();
    } catch (Exception $e) { 
      $SOAP_MSG = "Es konnte keine Verbindung zu https://management.exorbyte.com aufgebaut werden. ".
            "Stellen sie sicher das die Seite von Ihrem Webserver aus erreichbar und PHP ".
            "für die Verwendung von SSL konfiguriert ist." ;
      throw new Exception($SOAP_MSG);
		}
		return $ret;
	}
	
	public function SoapCall(){
    if(is_null($this->_oSoap)){
       $this->init_ecos_soap();
    }
    $mArgs = func_num_args();
    $aArgs=func_get_args();
    if(($mArgs < 2) or ($mArgs > 5)) {
       throw new Exception("Invalid number of arguments ".$mArgs." passed to the SOAP call");
    }
    $SOAPaction = $aArgs[0];
    switch ($mArgs) {
       case 2:
         $ret = $this->_oSoap->$SOAPaction($aArgs[1]);
         break;
       case 3:
         $ret = $this->_oSoap->$SOAPaction($aArgs[1], $aArgs[2]);
         break;
       case 4:
         $ret = $this->_oSoap->$SOAPaction($aArgs[1], $aArgs[2], $aArgs[3]);
         break;
       case 5: 
         $ret = $this->_oSoap->$SOAPaction($aArgs[1], $aArgs[2], $aArgs[3], $aArgs[4]);
         break;
    }
    
		if(is_null($ret)){
		  $ret='Error, empty result';
		}
		$this->_ret=$ret;
		$this->_lastXMLReq=$this->_oSoap->__last_request;
		$this->_lastXMLRes=$this->_oSoap->__last_response;
		return $ret;
	}
	public function setData($data){
		$this->aOldData=$this->aData;
		$this->aData=$this->validateData($data);
	}
	public function setAction($action){
		$this->sEx_action=$action;
	}
	public function getAction(){
		return $this->sEx_action;
	}
	protected function validateData($data=null){
		if(is_null($data)){
			$data=$this->aData;
		}
		foreach ($data as $key=>$value){
			if($value===''){
				unset($data[$key]);
			}
		}
		return $data;
	}
	public function checkData($data){
	    foreach($this->aDefault as $key=>$value){
		    if(!isset($data[$key])){
			    $data[$key]=$value;
		    }elseif($data[$key]===''){
			    $data[$key]=$value;
		    }
	    }
	    $this->setData($data);
	}
	public function getEcosURL($ecosAppURL=false){
		  $test=parse_url(self::$WSDLURL);
		  if (!$ecosAppURL){
		  	return $test['scheme'].'://'.$test['host'].'/'.'index.php?';
		  } else {
		  	return $test['scheme'].'://'.$test['host'].'/'.$ecosAppURL;
		  }  
	}

}
?>