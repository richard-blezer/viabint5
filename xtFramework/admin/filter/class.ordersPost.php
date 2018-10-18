<?php

global $db;

require(_SRV_WEBROOT."xtFramework/admin/filter/class.formFilter.php");

if($_SESSION['filter_order_status_id'] != ""){
    
    $where_ar[] =" orders_status in (".$_SESSION['filter_order_status_id'].")";
}

if( $_SESSION['filter_order_shop_id'] != ""){
    
    $where_ar[] =" shop_id in (".$_SESSION['filter_order_shop_id'].")";
}

//payment way
if($_SESSION['filter_payment_way_id'] != ""){
    
    $sql = "select payment_code from ".TABLE_PAYMENT." where payment_id in (".$_SESSION['filter_payment_way_id'].")";
    $rs1 = $db->Execute($sql);
    $data1 = array();
    if ($rs1->RecordCount() > 0) {
            while (!$rs1->EOF){
                $data1[] =  "'".$rs1->fields['payment_code']."'";
                $rs1->MoveNext();
            }
    }
    $where_ar[] = " payment_code in (".implode(",",$data1).")";
}
//shipping way
if($_SESSION['filter_shipping_way_id'] != ""){

    $data1 = explode(",",$_SESSION['filter_shipping_way_id']);
    foreach($data1 as $val) $data2[] = "'".$val."'";
    $where_ar[] = " shipping_code in (".implode(",",$data2).")";
    
}

//comboxes end



 if(FormFilter::setTxt('filter_id_from') ){

       $where_ar[] = TABLE_ORDERS.".orders_id >='". $_SESSION['filter_id_from']."'"; 
   }

       
if( FormFilter::setTxt('filter_id_to') ){

       $where_ar[] = TABLE_ORDERS.". orders_id <= '". $_SESSION['filter_id_to']."'"; 
   }
   
   
if( FormFilter::setTxt('filter_date_from')){

       $where_ar[] = " date_purchased >='".FormFilter:: date_trans($_SESSION['filter_date_from'])."'"; 
 }

 
if(FormFilter::setTxt('filter_date_to')){

	    $where_ar[] = " DATE_FORMAT(date_purchased,'%Y%m%d') <= '".FormFilter:: date_trans_int($_SESSION['filter_date_to'])."'";
 }

 
if( FormFilter::setTxt('filter_name')){
           
           $where_ar[] = " ( billing_firstname like '%".$_SESSION['filter_name']."%' or  billing_lastname like '%".$_SESSION['filter_name']."%' )"; 
               
}
    
    
if(FormFilter::setTxt('filter_amount_from')){
    
       $ad_table = ", ".TABLE_ORDERS_STATS;
       $where_ar[] = TABLE_ORDERS_STATS.".orders_id = ".TABLE_ORDERS.".orders_id"; 
       $where_ar[] = " orders_stats_price >='". $_SESSION['filter_amount_from']."'"; 
}

       
if( FormFilter::setTxt('filter_amount_to')){
   
       if($ad_table == ""){
           $ad_table = ", ".TABLE_ORDERS_STATS;
           $where_ar[] = TABLE_ORDERS_STATS.".orders_id = ".TABLE_ORDERS.".orders_id"; 
       }
       
       $where_ar[] = " orders_stats_price <= '". $_SESSION['filter_amount_to']."'"; 
}
   
 
if( FormFilter::setTxt('filter_last_modify_from')){

       $where_ar[] = " last_modified >='". FormFilter::date_trans($_SESSION['filter_last_modify_from'])."'"; 
 }

  if(FormFilter::setTxt('filter_last_modify_to')){
       $where_ar[] = " last_modified<= '". FormFilter::date_trans($_SESSION['filter_last_modify_to'])."'"; 
   }
 
  
   
if( ($_SESSION['filter_email'] != "") ){

       $where_ar[] = " customers_email_address = '".$_SESSION['filter_email']."'"; 
}


?>