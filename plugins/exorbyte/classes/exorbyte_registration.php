<?php
/*
--------------------------------------------------------------
   exorbyte_registration.php 2011-07-01
   exorbyte GmbH
   Line-Eid-Str. 1
   78467 Konstanz
   http://commerce.exorbyte.de
   Copyright (c) 2011 exorbyte GmbH, 
   author: Daniel Gebuehr
   --------------------------------------------------------------
   class registration for the integration of exorbyte's search
   --------------------------------------------------------------
   made for the integration as xt:commerce-Plugin for 
   Version 4.0xx CE - Community Edition
   http://www.xt-commerce.de
   --------------------------------------------------------------
*/
class ecos_registration {
  public $plugin_id;
   protected $aAllowed_Keys = array("action","to_do","exo_confirm","exo_auth_mail",
   "exopw","exo_auth_firstname","exo_auth_name","telephone","get_pid","sn_template","email","password",
   "show_images","second_col","display_type","exo_company","exo_title","export_action","set","module");
   protected $aCustomer_Mandatories = array("exo_title","exo_auth_mail","exopw",
   "exo_auth_firstname","exo_auth_name");
     function init() {
     /**
     * Initialisation of all requests, check against allowed keys
     * @var $_REQUEST: user input array
     * @var $aAllowed_Keys array
     * @return keys as object elements          
     */
        if(isset($_REQUEST)) {
           foreach($_REQUEST as $key=>$value) {
              if(in_array($key,$this->aAllowed_Keys)) {
                 $this->$key=$value;
              }  

           }
        }
        if($this->action=="") {$this->action="aktivierung";}
     }         

     function get_exo_project_id() {
        global $db;
     /**
     * checks the contants for the exorbyte project id
     * no input
     * @return project_id or -1 if not set         
     */
       $rs_all=$db->getAll("SELECT * FROM ".TABLE_EXORBYTE);
       if(is_array($rs_all[0])) {$rs=$rs_all[0];}
       else {$rs=$rs_all;}
       if($rs['project_id']>0) {
          return $rs['project_id'];
       }
       return -1;   
     }
     function get_exo_project_info($rSOAP) {
     /**
     * check the project
     * @obj $rSOAP 
     * @return project info as array.          
     */
        $this->aProjectData=$rSOAP->SoapCall("getProjectInfo",$rSOAP->aData['c_id'],$rSOAP->aData['secure_key'],$rSOAP->aData['p_id']);
        $this->aIntegrationData=$rSOAP->SoapCall("getIntegration",$rSOAP->aData['c_id'],$rSOAP->aData['secure_key'],$rSOAP->aData['p_id']);
        return $rSOAP;
     }     
                 
     function authenticate_exo_customer($rSOAP,$email, $password) {
        $rSOAP->aData['email'] = $email;
        $rSOAP->aData['password'] = $password;
        $rSOAP->aData['shop_id'] = "xt";
        $res = $rSOAP->SoapCall("authenticateCustomer",$email,$password,$rSOAP->aData['shop_id']);

        if( !is_array($res) ){ return false; }
        return $res;
     }             
     
     function get_exo_customer($rSOAP) {
        global $db;
     /**
     * transfer customer data to use with SOAP
     * @obj $rSOAP
     * @return obj for later use          
     */
     
       $rs_all=$db->getAll("SELECT * FROM ".TABLE_EXORBYTE);
       if(is_array($rs_all[0])) {$rs=$rs_all[0];}
       else {$rs=$rs_all;}
       $rSOAP->aData['c_id']=$rs['customer_id'];
       $rSOAP->aData['secure_key']=$rs['secure_key'];
       
       
       // Return if customer data allready exist
       if( $rs['customer_id'] != '' ) { return $rSOAP; }
       
       // Throw exception if customer is to be created but 
       // no password is provided
       if( $rSOAP->aData['password'] == "" ) {
          $pwmsg = "Bitte geben sie bei der ersten Aktivierung ein Passwort an, ".
          "mit dem Sie sich anschließend in Ihren Administrationsbereich auf ".
          "http://commerce.exorbyte.de/ einloggen können.";
          throw new Exception($pwmsg);
       }
               
       // REMOVE PASSWORD FROM XTCOMMERCE
       $db->Execute("UPDATE ".TABLE_PLUGIN_CONFIGURATION." SET config_value='Bitte bei management.exorbyte.com aendern.' WHERE plugin_id='".$this->plugin_id."' and config_key='EXORBYTE_PASSWORD';");


        
       // Get user data     
       $aXTAllUser=$db->getAll("select * FROM ".TABLE_ADMIN_ACL_AREA_USER);
       if(is_array($aXTAllUser[0])) {$aXTUser=$aXTAllUser[0];}
       else {$aXTUSer=$aXTAllUser;}

       // Check if user exists
       $auth = $this->authenticate_exo_customer($rSOAP, $aXTUser["email"], $rSOAP->aData['password']);
         
       if ($auth['error_code']==401) {
          $auth=$rSOAP->SoapCall("addCustomer",$rSOAP->aData['email'],$rSOAP->aData['password'],$rSOAP->aData['shop_id']);
       }   
   	   $rSOAP->aData['c_id']=$auth['c_id'];
       $rSOAP->aData['secure_key']=$auth['secure_key'];
       $rSOAP->aData['email']=$aXTUser["email"];

       $db->Execute("insert into ".TABLE_EXORBYTE." (
       customer_id, secure_key ) values (
       '".$rSOAP->aData['c_id']."','".$rSOAP->aData['secure_key']."')");
       return $rSOAP;


       
       // Otherwise setup user data for later usage
       $rSOAP->aData['customers_email_address']=$aXTUser["email"];
       $rSOAP->aData['customers_password']= $rSOAP->aData['password'] ;
       $rSOAP->aData['exo_auth_firstname']=$aXTUser["firstname"];
       $rSOAP->aData['exo_auth_name']=$aXTUser["lastname"];
       $rSOAP->aData['exo_title']="Herrn";
     
       return $rSOAP;
     }
    function get_exo_uid(){
     /**
     * create the unique id for the exorbyte customer
     * no input
     * @return customer_id          
     */
        return substr( session_id(), 0, 3 ) . substr( md5( uniqid( '', true ).'|'.microtime() ), 0, 29 );
    }
 
     function set_exo_secure_key($cid,$pass){
     /**
     * generate the secure key for exorbyte
     * @var cid: customer id; 
     * @var pass: selected password
     * @return secure key          
     */
		  	$sSecureKey=md5($cid).md5(substr($pass,2,16));
			  return $sSecureKey;
     }    
     function call_exo_customer($rSOAP) {
     /**
     * Adds the customer to the database table
     * @obj rSOAP
     * @return obj with new settings          
     */
        $this->get_exo_customer($rSOAP);
        $rSOAP->SoapCall("addCustomer");
        /*$check=$rSOAP->aData;
        if((is_string($check)) and (substr($check,1,8)=="xception")) {
           $this->Message=$rSOAP->aData."<p>";
        }*/
        return $rSOAP;
     }   
     function add_exo_project($rSOAP) {
        global $db;
     /**
     * Add project and project integration, install updates
     * @obj rSOAP
     * @return obj with new settings          
     */
        $this->get_exo_customer($rSOAP);
        $jahr=date(Y)+1;
        $mail=str_replace("@","_",$rSOAP->aData['email']);
        $mail=str_replace(".","_",$mail);
        $rSOAP->aData['p_customer_id']=$rSOAP->aData['c_id'];
        $rSOAP->aData['product_id']="3";
        $rSOAP->aData['orderid']=$this->get_exo_uid();
        $rSOAP->aData['upgrade_orderid']='.';
        $rSOAP->aData['max_queries']="5000";
        $rSOAP->aData['max_records']="100000";
        $rSOAP->aData['coupon']="xtcommerce";
        $rSOAP->aData['contract_status']="time_trial";
        $rSOAP->aData['project_locale']="de_DE";
        $rSOAP->aData['purchase_logo_removal']='false';
        $rSOAP->aData['is_subscription']='0';
        $rSOAP->aData['date_expiry']="$jahr-".date(m)."-".date(d);
        $rSOAP->aData['locale']="de_DE";
        $rSOAP->aData['project_name']=$mail; 
        $rSOAP->aData['domain_name']=_SYSTEM_BASE_URL;
        
        $shop_root = substr(_SRV_WEB,0,strlen(_SRV_WEB)-strlen(_SRV_WEB_ADMIN));
        $data_src_url = _SYSTEM_BASE_URL.$shop_root._SRV_WEB_EXPORT."exorbyte.csv";
        
        $rSOAP->aData['source_data_url']=$data_src_url; 
        $rSOAP->aData['source_data_format']="billiger";
        $rSOAP->aData['source_data_file_format']="csv";
        $rSOAP->aData['source_data_field_delimiter']=";";
        $rSOAP->aData['source_data_cat_delimiter']=">";
        $rSOAP->aData['source_data_encoding']="utf-8";
        $ret=$rSOAP->SoapCall("addProject",$rSOAP->aData['c_id'],$rSOAP->aData['secure_key'],$rSOAP->aData);
        $rSOAP->aData['p_id']=$ret;
        $rSOAP->aData['projectKey']=$ret;
        $rSOAP->aData['p_product_id']=$rSOAP->aData['product_id'];
        $rSOAP->aData['p_project_id']=$ret;
        $rSOAP->aData['project_key']=$rSOAP->aData['orderid'];
        $ret2=$rSOAP->SoapCall("updateProject",$rSOAP->aData['c_id'],$rSOAP->aData['secure_key'],$rSOAP->aData['p_id'],$rSOAP->aData);
        $rSOAP->aData['search_field_sel']="input[name=keywords]";
        $rSOAP->aData['container_div_sel']="#content";
        $rSOAP->aData['is_url_trigger']='false';
        $ret3=$rSOAP->SoapCall("setIntegration",$rSOAP->aData['c_id'],$rSOAP->aData['secure_key'],$rSOAP->aData['p_id'],$rSOAP->aData);
        return $rSOAP;
     }
     
     function check_projects($rSOAP) {
        global $db;
     /**
     * Retrieve projects of user - just in case a project id was not written back in the database
     * @obj rSOAP
     * @return obj with new settings          
     */
        $ret=$rSOAP->SoapCall("getProjects",$rSOAP->aData['c_id'],$rSOAP->aData['secure_key']);
        if($ret[0]['p_id']!=0) {
           $rSOAP->aData['p_id']=$ret[0]['p_id'];
        }
     }     
     /**
     * http call via fsockopen to trigger the export
     * @input see comments below
     * @return string with the output or error code
     * example usage:
     *      http_request('GET', 'www.yourdomain.net', 80, 
     *      '/path/file.php', array('key_to_get' => 'value_to_get'), 
     *      array(), 
     *      array(), 
     *      array());               
     */
     function http_request( 
        $verb = 'GET',             /* HTTP Request Method (GET and POST supported) */
        $ip,                       /* Target IP/Hostname */ 
        $port = 80,                /* Target TCP port */ 
        $uri = '/',                /* Target URI */ 
        $getdata = array(),        /* HTTP GET Data ie. array('var1' => 'val1', 'var2' => 'val2') */
        $postdata = array(),       /* HTTP POST Data ie. array('var1' => 'val1', 'var2' => 'val2') */
        $cookie = array(),         /* HTTP Cookie Data ie. array('var1' => 'val1', 'var2' => 'val2') */
        $custom_headers = array(), /* Custom HTTP headers ie. array('Referer: http://localhost/ */
        $timeout = 1000,           /* Socket timeout in milliseconds */ 
        $req_hdr = false,          /* Include HTTP request headers */ 
        $res_hdr = false           /* Include HTTP response headers */ 
     ) { 
        $ret = ''; 
        $verb = strtoupper($verb); 
        $cookie_str = ''; 
        $getdata_str = count($getdata) ? '?' : ''; 
        $postdata_str = ''; 

        foreach ($getdata as $k => $v) 
           $getdata_str .= urlencode($k) .'='. urlencode($v) . '&'; 

        foreach ($postdata as $k => $v) 
           $postdata_str .= urlencode($k) .'='. urlencode($v) .'&'; 

        foreach ($cookie as $k => $v) 
           $cookie_str .= urlencode($k) .'='. urlencode($v) .'; '; 

        $crlf = "\r\n"; 
        $req = $verb .' '. $uri . $getdata_str .' HTTP/1.1' . $crlf; 
        $req .= 'Host: '. $ip . $crlf; 
        $req .= 'User-Agent: Mozilla/5.0 Firefox/3.6.12' . $crlf; 
        $req .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . $crlf;
        $req .= 'Accept-Language: en-us,en;q=0.5' . $crlf; 
        $req .= 'Accept-Encoding: deflate' . $crlf; 
        $req .= 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . $crlf; 
    
        foreach ($custom_headers as $k => $v) 
           $req .= $k .': '. $v . $crlf; 
        
        if (!empty($cookie_str)) 
           $req .= 'Cookie: '. substr($cookie_str, 0, -2) . $crlf; 
        
        if ($verb == 'POST' && !empty($postdata_str)) { 
           $postdata_str = substr($postdata_str, 0, -1); 
           $req .= 'Content-Type: application/x-www-form-urlencoded' . $crlf; 
           $req .= 'Content-Length: '. strlen($postdata_str) . $crlf . $crlf; 
           $req .= $postdata_str; 
        } 
        else $req .= $crlf; 

        if ($req_hdr) 
           $ret .= $req; 
    
        if (($fp = @fsockopen($ip, $port, $errno, $errstr)) == false) 
           return "Error $errno: $errstr\n"; 
    
    

        stream_set_timeout($fp, 0, $timeout * 1000); 
 
       fputs($fp, $req); 
       while ($line = fgets($fp)) $ret .= $line; 
       fclose($fp); 

       if (!$res_hdr) 
           $ret = substr($ret, strpos($ret, "\r\n\r\n") + 4); 
    
       return $ret; 
   } 
}         
?>
