<?php

if(isset($_SESSION['filter_isOnIndexPage']) && $_SESSION['filter_isOnIndexPage'] ) {
    $ad_table = ", ".DB_PREFIX."_startpage_products sp ";
    $where_ar[] = " sp.products_id = ".TABLE_PRODUCTS.".products_id"; 
    if (isset($_SESSION['filter_shop'])){
        $where_ar[] = " sp.shop_id = ".$_SESSION['filter_shop']; 
    }
	
}