<?php
   /*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: xt_cleverreach.php 4611 2011-03-30 16:39:15Z mzanier $
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');


class cleverreach{

    function exportOrders($limit=0) {
        global $db,$store_handler;
        if(XT_CLEVERREACH_API_KEY != "" && XT_CLEVERREACH_LIST_ID != ""){
            $crapi = cleverreach::api();
            
            $rs            = $db->Execute("SELECT * FROM ".TABLE_CUSTOMERS." as c
                                        left join ".TABLE_CUSTOMERS_ADDRESSES." as ca on c.customers_id = ca.customers_id limit ".$limit.", 50");
            $users = $rs->getArray();

            $count_users = 0;
            $count_orders = 0;
            foreach($users as $u){
                if($u["customers_gender"] == "m"){
                    $salutation = "Herr";
                }elseif($u["customers_gender"] == "f"){
                    $salutation = "Frau";
                }else{
                    $salutation = "";
                }
                $userData[] = array(
                    'email' => $this->make_safe($u["customers_email_address"]),
                    'registered' => $this->make_safe(strtotime($u["date_added"])),
                    'activated' => $this->make_safe(strtotime($u["date_added"])),
                    'source' => $this->make_safe('VEYTON'),
                    'salutation' => $this->make_safe($salutation),
                    'firstname' => $this->make_safe($u["customers_firstname"]),
                    'lastname' => $this->make_safe($u["customers_lastname"]),
                    'street' => $this->make_safe($u["customers_street_address"]),
                    'zip' => $this->make_safe($u["customers_postcode"]),
                    'city' => $this->make_safe($u["customers_city"]),
                    'country' => $this->make_safe($u["customers_country_code"]),
                    'company' => $this->make_safe($u["customers_company"]),
                    'user_defined' => array(0 => array('key' => 'opt_shop_id', 'value' => $u["shop_id"])));
            }
            $result_u = $crapi->addBatch(XT_CLEVERREACH_API_KEY, XT_CLEVERREACH_LIST_ID, $userData);
            if($result_u->status == "SUCCESS"){
                foreach($userData as $u)
                $rs_order = $db->Execute("select * from ".TABLE_ORDERS." as o
                                        left join ".TABLE_ORDERS_PRODUCTS." as op on o.orders_id = op.orders_id where customers_email_address = '".$u["email"]."'");
                
                $orders = $rs_order->getArray();
                foreach($orders as $o){
                    $orderData["order_id"] = $o["orders_id"];
                    $orderData["product"] = $o["products_name"];
                    $orderData["product_id"] = $o["products_id"];
                    $orderData["price"] = $o["products_price"];
                    $orderData["amount"] = (integer)$o["products_quantity"];
                    $orderData["purchase_date"] = strtotime($o["date_purchased"]);
                    $orderData["source"] = "Veyton Order";
                    $result_o = $crapi->addOrder(XT_CLEVERREACH_API_KEY, XT_CLEVERREACH_LIST_ID, $u["email"], $orderData);
                }
            }
            echo "update_import();";
        }
    }

    function make_safe($in){
        $in = utf8_encode($in);
        $in = str_replace("&amp;", "&",$in);
        $in = str_replace("&", "&amp; ",$in);
        $in = str_replace("<", "&lt;",$in);
        $in = str_replace(">", "&gt;",$in);
        return $in;
    }
    
    function _displayNLcheckBox($data) {
        global $xtPlugin, $xtLink, $db;
        
        $tpl = 'newsletter_checkbox.html';
        $tmp_data = '';
        $template = new Template();
        $template->getTemplatePath($tpl, 'xt_cleverreach', '', 'plugin');

        $tmp_data = $template->getTemplate('xt_cleverreach_smarty', $tpl, $tpl_data);
        return $tmp_data;
        
    }

    function api(){       
        return new SoapClient("http://api.cleverreach.com/soap/interface_v4.php?wsdl");
    }
    
    function prepare_list($apiKey, $listID){
        cleverreach::api()->addUserDefinedField($apiKey, $listID, "newsletter", "");
        cleverreach::api()->addUserDefinedField($apiKey, $listID, "shop_id", "");
        
        //filter anlegen
        
        $rule[0] = array("field"        => "opt_newsletter",
                      "operator"     => "AND",
                      "logic"        => "EQ",
                      "condition"    => "1");

        cleverreach::api()->createFilter($apiKey, $listID, "Veyton_newsletter", $rule);
        
        $rule[0] = array("field"        => "purchase_date",
                      "operator"     => "AND",
                      "logic"        => "NOTISNULL",
                      "condition"    => "");

        cleverreach::api()->createFilter($apiKey, $listID, "Veyton_customers", $rule);
        
    }

}
?>