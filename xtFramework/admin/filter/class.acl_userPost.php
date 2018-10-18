<?php


if(FormFilter::setTxt('filter_name')) {
           
           $where_ar[] = " ( firstname like '%".$_SESSION['filter_name']."%'
                             OR
                             lastname like '%".$_SESSION['filter_name']."%' 
                            )";
 }
 
if(FormFilter::setTxt('filter_username')) {
    
   $where_ar[] = " handle like '%".$_SESSION['filter_username']."%'";
   
}

if(isset($_SESSION['filter_userstatus']) && $_SESSION['filter_userstatus']){
         
    $where_ar[] = " status = 1 "; 
}

if($_SESSION['filter_usergroup_id'] != ""){
    
    $where_ar[] = " group_id in (".$_SESSION['filter_usergroup_id'].")";
    
}


       
?>