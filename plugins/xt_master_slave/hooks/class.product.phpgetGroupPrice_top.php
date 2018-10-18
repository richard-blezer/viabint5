<?php
include_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/class.xt_master_slave_functions.php';
if (($this->data['products_master_model']!='') && (XT_MASTER_SLAVE_SUM_SLAVE_QUANTITY_FOR_GRADUATED_PRICE=='true')){
    
   if($sp_type=='group'){
       if ($this->data['price_flag_graduated_'.$price->p_group]!=1) return true;  // don't continue
   }else{
       if ($this->data['price_flag_graduated_all']!=1) return true;  // don't continue
   }
   $slaves =  xt_master_slave_functions::get_slave_from_master($this->data['products_master_model']);
   $product_slaves = array(); // products_id of all slaves for the current products_master_model
    if ($slaves->RecordCount() > 0) {
        while (!$slaves->EOF) {
            array_push($product_slaves,$slaves->fields['products_id'].'_XT');
            $slaves->MoveNext();
        }
    }

    $products_in_cart = $_SESSION['cart']->content; // products in the cart
   
    if ((count($products_in_cart)>0)&& (count($product_slaves)>0)){
        $slaves_qnt=0;
        foreach($products_in_cart as $p=>$k){
            if (in_array($p, $product_slaves)){
                $slaves_qnt += $k['products_quantity'];
            }
        }
        if ($this->qty!=$slaves_qnt) {
            // change the quantity so the graduated price could be generated based on total master quantity (it's a sum of all slaves quantities)
             $this->qty = $slaves_qnt; 
        }   
    }
}

?>