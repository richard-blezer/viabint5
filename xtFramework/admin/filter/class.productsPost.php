<?php
  
global $db, $xtPlugin;
 
require_once(_SRV_WEBROOT."xtFramework/admin/filter/class.formFilter.php");

function setFloat($key){
    
    if( (isset($_SESSION[$key])) && ( (float)($_SESSION[$key])>0 ) )  return true;
    else return false;
    
}
function setInt($key){
    
    if( (isset($_SESSION[$key])) && ( (int)($_SESSION[$key])>0 ) )  return true;
    else return false;
    
}


       
   
if(FormFilter::setTxt('filter_product_name')){

   $ad_table = ", ".TABLE_PRODUCTS_DESCRIPTION;
   $where_ar[] = TABLE_PRODUCTS_DESCRIPTION.".products_id = ".TABLE_PRODUCTS.".products_id"; 
   $where_ar[] = " products_name like '%".$_SESSION['filter_product_name']."%'"; 
}

if (setInt('filter_permission')){
    
  if(!$ad_table){   
      $ad_table = ", ".TABLE_PRODUCTS_PERMISSION;
      $where_ar[] = TABLE_PRODUCTS_PERMISSION.".pid = ".TABLE_PRODUCTS.".products_id"; 
  }
  $where_ar[] ="  pgroup = 'group_permission_".$_SESSION['filter_permission']."'";
  
}

if(setFloat('filter_price_from') ) 
 $where_ar[] = " products_price >=  ". (float)$_SESSION['filter_price_from']; 

if( setFloat('filter_price_to') ) 
 $where_ar[] = " products_price <= ".(float)$_SESSION['filter_price_to']; 


if (setInt('filter_manufacturer'))
  $where_ar[] = " manufacturers_id = ".(int)$_SESSION['filter_manufacturer']; 


if(setInt('filter_stock_from'))
  $where_ar[] = " products_quantity >= ".(int)$_SESSION['filter_stock_from']; 

if(setInt('filter_stock_to'))
  $where_ar[] = " products_quantity <= ".(int)$_SESSION['filter_stock_to']; 
elseif ($_SESSION['filter_stock_to']=='0') $where_ar[] = " products_quantity = 0"; 

if(isset($_SESSION['filter_isDigital']) && $_SESSION['filter_isDigital'] )
  $where_ar[] = " products_digital = 1"; 

if(isset($_SESSION['filter_isFSK18']) && $_SESSION['filter_isFSK18'] )
  $where_ar[] = " products_fsk18 = 1 "; 
  
 if(setFloat('filter_weight_from') ) 
 $where_ar[] = " products_weight >=  ". (float)$_SESSION['filter_weight_from']; 

if( setFloat('filter_weight_to') ) 
 $where_ar[] = " products_weight <= ".(float)$_SESSION['filter_weight_to']; 
      
if(isset($_SESSION['filter_products_status']) && (int)$_SESSION['filter_products_status'] === 1 )
  $where_ar[] = " products_status = 1"; 
else if(isset($_SESSION['filter_products_status']) && (int)$_SESSION['filter_products_status'] === -1 )
    $where_ar[] = " products_status = 0";
  
if($_SESSION['filter_products_status'] == -1 )
  $where_ar[] = " products_status = 0"; 
  
if(isset($_SESSION['filter_master_slave_products']) && $_SESSION['filter_master_slave_products'] ==1 )
	$where_ar[] = " products_master_flag = 1";
	
if(isset($_SESSION['filter_master_slave_products']) && $_SESSION['filter_master_slave_products'] ==2 )
	$where_ar[] = " products_master_model !='' ";
	
if(isset($_SESSION['filter_master_slave_products']) && $_SESSION['filter_master_slave_products'] ==3 )
	$where_ar[] = " ((products_master_model !='') or (products_master_flag = 1) )  ";     

($plugin_code = $xtPlugin->PluginCode('class.productsPost.php:bottom')) ? eval($plugin_code) : false;
?>