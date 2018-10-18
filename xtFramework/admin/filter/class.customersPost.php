<?php

//require_once _SRV_WEBROOT . '/xtFramework/library/FirePHPCore/fb.php';
if ($_SESSION['filter_email'] != ""){
          
     $where_ar[] = " customers_email_address = '".$_SESSION['filter_email']."'";
          
}

if(FormFilter::setTxt('filter_company')) {
   
           $ad_table = ", ".TABLE_CUSTOMERS_ADDRESSES;
           $where_ar[] = TABLE_CUSTOMERS_ADDRESSES.".customers_id = ".TABLE_CUSTOMERS.".customers_id"; 
           $where_ar[] = " customers_company like '%".$_SESSION['filter_company']."%'"; 
}

if( $_SESSION['filter_customers_shop_id'] != ""){

    $where_ar[] =" shop_id in (".$_SESSION['filter_customers_shop_id'].")";
}

if(FormFilter::setTxt('filter_name')) {

           if(!$ad_table){   
               $ad_table = ", ".TABLE_CUSTOMERS_ADDRESSES;
               $where_ar[] = TABLE_CUSTOMERS_ADDRESSES.".customers_id = ".TABLE_CUSTOMERS.".customers_id"; 
           }
           $where_ar[] = " ( customers_firstname like '%".$_SESSION['filter_name']."%' or  customers_lastname like '%".$_SESSION['filter_name']."%' ) "; 
 }
 
       
if ($_SESSION['filter_gender'] != "") {
           
           if(!$ad_table){   
               $ad_table = ", ".TABLE_CUSTOMERS_ADDRESSES;
               $where_ar[] = TABLE_CUSTOMERS_ADDRESSES.".customers_id = ".TABLE_CUSTOMERS.".customers_id"; 
           }
           $where_ar[] = " customers_gender ='".$_SESSION['filter_gender']."'"; 
 }
       
       
if ($_SESSION['filter_language'] != "") {
                
       $rs1 = $db->Execute("SHOW FIELDS FROM xt_customers WHERE field = 'customers_default_language'");
       if ($rs1->RecordCount() > 0){ mail("dddd","www","kkkkk");
           $where_ar[] = " customers_default_language ='".$_SESSION['filter_language']."'"; 
       }
       
 }
       
       
if ($_SESSION['filter_status_id'] != "") {
            
       $where_ar[] = " customers_status in (".$_SESSION['filter_status_id'].")"; 
}

if (!empty($_SESSION['filter_customers_status_id'])) {
    $where_ar[] = " customers_status IN (" . $_SESSION['filter_customers_status_id'] . ")";
}

global $xtPlugin;
($plugin_code = $xtPlugin->PluginCode('class.customersPost.php:bottom')) ? eval($plugin_code) : false;      
       
?>