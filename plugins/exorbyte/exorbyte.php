<?php
//This is the code for the hookpoint-database
//to install it delete the test.php and put this code into the hook
//please remove the last lines with the db call -> debug purposes

//only intialize once:



global $db;
$msg = "Exorbyte Projekt ist deaktiviert!";

try {
   
  $sql = 'select plugin_id from '.TABLE_PLUGIN_PRODUCTS.' where code ="exorbyte";';
  $arr = $db->getRow($sql);
  $exo_plugin_id = $arr['plugin_id'] ;

  if( ((int)$data['plugin_id']) != $exo_plugin_id ) {
    return false;
  }    
  

  $resultset_config = $db->Execute("SELECT * FROM " . TABLE_PLUGIN_CONFIGURATION . " WHERE config_key='EXORBYTE_AKTIVIEREN'");



  if((!$rSetting) and ($resultset_config->fields["config_value"]=="true")) {
     $msg = "Projekt wurde erfolgreich aktualisiert!";
     //throw new Exception("Irgend einsfsdfsdfsd ist aufgetreten!");

     require_once PATH_EXORBYTE_PLUGIN.'/classes/exorbyte_registration.php';
     require_once PATH_EXORBYTE_PLUGIN.'/classes/exorbyte_soap.php';
     require_once PATH_EXORBYTE_PLUGIN.'/classes/exorbyte_views.php';

     $rSetting=new ecos_registration;
     $rSOAP=new ecos_soap;
     $rView=new ecos_view;
     $rSetting->plugin_id = $exo_plugin_id; 
      
     // get password
     $resultset_config = $db->Execute("SELECT * FROM " . TABLE_PLUGIN_CONFIGURATION . " WHERE config_key='EXORBYTE_PASSWORD'");
     $exo_pwd = $resultset_config->fields["config_value"];     
     $rSOAP->aData['password'] = $exo_pwd;
      
     // Exit if we are not in our plugin    
     $rSetting->get_exo_customer($rSOAP);
  
     $rSetting->exo_auth_mail=$rSOAP->aData['email'];
     $rSetting->exopw=$rSOAP->aData['customers_password'];
     $rSetting->exo_auth_firstname=$rSOAP->aData['exo_auth_firstname'];
     $rSetting->exo_auth_name=$rSOAP->aData['exo_auth_name'];
     $rSetting->exo_title=$rSOAP->aData['exo_title'];
     
     //Customer anlegen sofern noch keiner vorhanden
     if($rSOAP->aData['c_id']=="") {
        $rSetting->set_exo_customer($rSOAP);
     }
  
     //Export erstellen/updaten
   	 $rsfeed = $db->Execute("SELECT * FROM " . TABLE_FEED . " WHERE feed_title='exorbyte'");
   	
   	 
   	 $shop_root = substr(_SRV_WEB,0,strlen(_SRV_WEB)-strlen(_SRV_WEB_ADMIN));
   	 
     $HTR = $rSetting->http_request("GET",$_SERVER["HTTP_HOST"],80, _SYSTEM_BASE_URL.$shop_root."cronjob.php",array("feed_id"=>$rsfeed->fields["feed_id"]));
  
     //Projekt anlegen, wenn noch keines in der Tabelle steht
     $rSOAP->aData["p_id"]=$rSetting->get_exo_project_id();
  
     if($rSOAP->aData["p_id"]==-1) {
        $rSetting->check_projects($rSOAP);
  
        if($rSOAP->aData["p_id"]==-1) {
           $rSetting->add_exo_project($rSOAP);
        }
        $db->Execute("update " . TABLE_EXORBYTE . " set project_id='".$rSOAP->aData['p_id']."',project_name='".$rSOAP->aData['project_name']."'");   
        //Projekt installieren 
        $installresult=$rSOAP->SoapCall("installProject",$rSOAP->aData['c_id'],$rSOAP->aData['secure_key'],$rSOAP->aData['p_id']);
     }   
  }  



} catch ( Exception $e ) {
  $msg = "FEHLER: ".$e->getMessage();
  $db->Execute("UPDATE ".TABLE_PLUGIN_PRODUCTS." SET plugin_status='0' WHERE plugin_id='".$exo_plugin_id."';");     
}

$db->Execute("UPDATE ".TABLE_PLUGIN_CONFIGURATION." SET config_value='".$msg."' WHERE plugin_id='".$exo_plugin_id."' and config_key='EXORBYTE_STATUS_MESSAGE';");

?>