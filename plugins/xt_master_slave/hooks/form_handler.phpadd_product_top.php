<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');

if (XT_MASTER_SLAVE_ACTIVE == 'true'){
    include_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/class.xt_master_slave_functions.php';
    unset($_SESSION['reload_select_ms']);
    unset($_SESSION['xt_master_slave'][$data_array['product']]['error']);
    global $db;
    $rs = $db->Execute("SELECT products_master_flag,products_model FROM   " . TABLE_PRODUCTS . " where products_id=?",array((int)$data_array['product']));
    
    if ($rs->RecordCount()>0){
        if ($rs->fields['products_master_flag']==1){
            $not_selected = xt_master_slave_functions::getNotSelectedOptions($rs->fields['products_model']);
            
            if (count($not_selected)>0){
                $link_array = array('page'=> 'product', 'type'=>'product', 'id'=>$data_array['product'],'params'=>'info='.$data_array['product']);
                $p_link = $xtLink->_link($link_array);
                foreach($not_selected as $a){
                    $_SESSION['xt_master_slave'][$data_array['product']]['error'][$a] = TEXT_OPTION_NOT_SELECTED;
                }
                $_SESSION['reload_select_ms'] = $_SESSION['select_ms'];
                header("Location: ".$p_link);
                die();
            }
        }
        
    }
   
}
