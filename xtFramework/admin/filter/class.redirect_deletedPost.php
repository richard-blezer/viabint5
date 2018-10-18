<?php


if ($_SESSION['filter_keyword'] != ""){     
     $where_ar[] = " (url_text LIKE '%".$_SESSION['filter_keyword']."%' or url_text_redirect LIKE '%".$_SESSION['filter_keyword']."%') ";          
}

if( $_SESSION['filter_store'] != ""){
    $where_ar[] =" store_id in (".$_SESSION['filter_store'].")";
}
     
if ($_SESSION['filter_link_type'] != "") {
    $where_ar[] = " link_type ='".$_SESSION['filter_link_type']."'"; 
 }
           
?>