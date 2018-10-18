<?php
    
    
 if( FormFilter::setTxt('filter_date_from')){
           
           $where_ar[] = " review_date >= '". FormFilter::date_trans($_SESSION['filter_date_from'])."'"; 
       }
       
   if( FormFilter::setTxt('filter_date_to')) {
           
           $where_ar[] = " review_date <= '". FormFilter::date_trans($_SESSION['filter_date_to'])."'"; 
   }
       
   if(FormFilter::setTxt('filter_product')) {
       
           $ad_table = ", ".TABLE_PRODUCTS_DESCRIPTION;
           $where_ar[] = TABLE_PRODUCTS_DESCRIPTION.".products_id = ".TABLE_PRODUCTS_REVIEWS.".products_id"; 
           $where_ar[] = " products_name like '%".$_SESSION['filter_product']."%'"; 
           
    }
       
   if(isset($_SESSION['filter_active']) && $_SESSION['filter_active'] ){
       
          $where_ar[] = " review_status = 1"; 
   }
   
   if( FormFilter::setTxt('filter_title')) {
       
        $where_ar[] = " review_title like '%".$_SESSION['filter_title']."%'";
        
   }
   
   
    if($_SESSION['filter_language_id'] != ""){
        
        $l = explode(",", $_SESSION['filter_language_id']);
        foreach($l as $l_l){
            $ls[] = "'".$l_l."'";
        }
        
        $where_ar[] = " language_code in (".implode(",",$ls).")";
        
    }
    
     if($_SESSION['filter_rating_id'] != ""){
        
        $where_ar[] = " review_rating in (".$_SESSION['filter_rating_id'].")";
        
    }
   
?>