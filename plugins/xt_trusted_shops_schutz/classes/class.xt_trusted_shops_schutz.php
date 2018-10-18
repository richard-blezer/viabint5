<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: class.xt_trusted_shops_schutz.php 6060 2013-03-14 13:10:33Z mario $
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


class xt_trusted_shops_schutz {
    
    var $webservice_login = XT_TRUSTED_SHOPS_SCHUTZ_WS_LOGIN;
    var $webservice_pw = XT_TRUSTED_SHOPS_SCHUTZ_WS_PW;


    function xt_trusted_shops_schutz() {
        
        if (ACTIVATE_XT_TRUSTED_SHOPS_SCHUTZ_TEST=='true') {
           $this->wsdl_rFP = 'https://protection-qa.trustedshops.com/ts/protectionservices/ApplicationRequestService?wsdl'; 
        } else {
           $this->wsdl_rFP = 'https://protection.trustedshops.com/ts/protectionservices/ApplicationRequestService?wsdl';  
        }
    }
   
    /**
    * display protection box in checkout
    * 
    */
    function _displayProtectionBox() {
        global $xtPlugin, $xtLink, $db;
        
        $total = 0;
        if (is_object($_SESSION['cart'])) {
            $total = $_SESSION['cart']->content_total['plain'];
        }
        
        $types = $this->_getTypes(true,$total);
        
        $tpl = 'ts_checkbox_payment.html';
        $tpl_data = array('ts_types'=>$types,'TS_ID'=>$this->_getID());
        $template = new Template();
        $template->getTemplatePath($tpl, 'xt_trusted_shops_schutz', '', 'plugin');

        $tmp_data = $template->getTemplate('xt_trusted_shops_schutz_smarty', $tpl, $tpl_data);
        return $tmp_data;
        
    }
    
    function requestForProtection($oID) {
        global $logHandler,$db;
        
       $orders_id = (int)$oID;

       $rs = $db->Execute("SELECT customers_id FROM ".TABLE_ORDERS." WHERE orders_id='".$orders_id."'");

       $order = new order($orders_id,$rs->fields['customers_id']);

       // check if protection has been added to order
       if (!isset($_SESSION['ts_ks_type'])) return;    
  
       $request = array();
       $request['tsId']=$this->_getID();
       $request['tsProductId']=$_SESSION['ts_ks_type'];
       $request['amount']=(double)$order->order_total['total']['plain'];
       $request['currency']=$order->order_data['currency_code'];  
       
       $payment = $order->order_data['payment_code'];
       $payment_type=$this->_getPaymentMethod($payment);
       
       $request['paymentType']=$payment_type;
       $request['buyerEmail']=$order->order_data['customers_email_address'];
       $request['shopCustomerID']=$order->order_data['customers_id'];
       $request['shopOrderID']=$oID;
       $request['orderDate']=date('c',strtotime($order->order_data['date_purchased_plain']));
       $request['wsUser']=$this->webservice_login;
       $request['wsPassword']=$this->webservice_pw;

       include_once 'xtFramework/library/nusoap/nusoap.php'; 
       
       $wsdl = $this->wsdl_rFP;

       $client = new nusoap_client($wsdl,true);

       $err = $client->getError();
       if ($err) {
          $log = array();
          $log['data'] = array('error'=>$err);
          $logHandler->_addLog('error','xt_trusted_shops_schutz',$oID,$log['data']);
       }

        $response = $client->call('requestForProtection',$request); 
        
        // check if negative
        if ($response < 0) {
          $log = array();
          $log['data'] = array('error_code'=>$response,'error'=>'TS Webservice Error');
          $logHandler->_addLog('error','xt_trusted_shops_schutz',$oID,$log['data']); 
        } else {
            // success
            $db->Execute("UPDATE ".TABLE_ORDERS." SET ts_response='".(int)$response."', ts_type='".$_SESSION['ts_ks_type']."' WHERE orders_id='".(int)$oID."'");  
        }
        unset($_SESSION['ts_ks_type']);
        unset($_SESSION['ts_ks_checkbox']);
       
    }
    
    /**
    * display ID in admin
    * 
    * @param mixed $orders_id
    */
    function getTsAdmin($orders_id) {
        global $db;

        $template='';
        $rs = $db->Execute("SELECT ts_type,ts_response FROM ".TABLE_ORDERS." WHERE orders_id='".$orders_id."'");
        if ($rs->fields['ts_type']!='') {

            $template .='<div class="x-panel-header x-unselectable">'.__define('TEXT_TS_SCHUTZ_TITLE').'</div>';
            $template .='<table cellspacing="0" width="100%">'.
                '<tbody>'.
                '<tpl for="order_products" >';

                $template.='<tr>'.
                    '<td style="text-align:left;">Typ: '.$rs->fields['ts_type'].'</td>'.
                    '<td style="text-align:left;">ID: '.$rs->fields['ts_response'].'</td>'.
                '</tr>';
            
            $template.='</tpl>'.
                '</tbody></table><br /><br />';

        }
        return $template;
    }
        
    /**
    * get types
    * 
    * @param mixed $format
    */
    function _getTypes($format = false,$cart_amount=0) {
        global $price;
        
        $codes = array();
        // Hardcode TS products
        //$types = XT_TRUSTED_SHOPS_SCHUTZ_TYPE;
        $types = 'TS080501_500_30_EUR;0.82;500|TS080501_1500_30_EUR;2.47;1500|TS080501_2500_30_EUR;4.12;2500|TS080501_5000_30_EUR;8.24;5000|TS080501_10000_30_EUR;16.47;10000|TS080501_20000_30_EUR;32.94;20000';
        $types = explode('|',$types);

        if (count($types)==0) return;
        $i=0;
        $selected = 0;
        $tmp = array();
        foreach ($types as $key => $val) {
            $arr = explode(';',$val);
            
            if ($format==true) {
              $arr[1] = $price->_getPrice(array('price'=>$arr[1], 'tax_class'=>XT_TRUSTED_SHOPS_SCHUTZ_TAX_CLASS, 'curr'=>true, 'format'=>true, 'format_type'=>'default'));
              
            }
            
            $codes[$arr[0]] = array('id'=>$arr[0],'price'=>$arr[1],'protected'=>$arr[2],'text'=>'Protection acheteur jusqu\'&agrave; '.$arr[2].' € ('.$arr[1]['formated'].' TVA incluse)');
            $tmp[$i]=array('protected'=>$arr[2]);  
        
            // check if actual ist lower, unset last   
           if ($arr[2]>$cart_amount) {
               if (is_array($tmp[$i-1])) {
                   if ($tmp[$i-1]['protected']<$cart_amount) {
                    $selected=$arr[0];     
                   }
               } else {
                  $selected=$arr[0];      
               }
               
           }

           $i++;
        }
        define('_XT_TS_SCHUTZ_SELECTED',$selected);
        
        return $codes; 
        
    }
    
    function _removeFromCart() {
        unset($_SESSION['ts_ks_type']);
        unset($_SESSION['cart']->show_sub_content['xt_ts_schutz']);
        unset($_SESSION['cart']->sub_content['xt_ts_schutz']);
    }
    
     private function _getPaymentMethod($payment_code) {
        
        switch ($payment_code) {
            
            case 'xt_invoice':
            case 'xt_billpay':
                return 'INVOICE';
                break;

            case 'xt_saferpay':
            case 'pay_ogone':
            case 'pay_paymentpartner':
            case 'xt_qenta':
                return 'CREDIT_CARD';
                break;
            case 'xt_moneybookers':
                return 'MONEYBOOKERS';
                break;
            case 'xt_paypal':
                return 'PAYPAL';
                break;
            case 'xt_sofortueberweisung':
                return 'DIRECT_E_BANKING';
                break;
            case 'xt_clickandbuy':
                return 'CLICKANDBUY';
                break;
            case 'xt_cashondelivery':
                return 'CASH_ON_DELIVERY';
                break;
            case 'xt_cashpayment':
            case 'xt_prepayment': 
                return 'PREPAYMENT';
                break;
            case 'xt_banktransfer':
                return 'DIRECT_DEBIT';
            default:
                return 'OTHER';
                break;
              
        }
        
    }
    
    function _addToCart() {

       if (!isset($_POST['ts_ks_type']))  return false;
       $type = $_POST['ts_ks_type'];

       $types = $this->_getTypes();

       if (!is_array($types[$type])) return false; 
       $_SESSION['ts_ks_type'] = $type; 

         
        $ts_data_array = array('customer_id' => $_SESSION['registered_customer'],
                                             'qty' => '1',
                                             'name' => TEXT_TS_SCHUTZ_TITLE,
                                             'price' => $types[$type]['price'],
                                             'tax_class' => XT_TRUSTED_SHOPS_SCHUTZ_TAX_CLASS,
                                             'sort_order' => '10',
                                             'type' => 'xt_ts_schutz'
                                             );

        $_SESSION['cart']->_addSubContent($ts_data_array);
        $_SESSION['cart']->_refresh();
        
        return true;
    }
    
    function _getID() {
        global $xtPlugin, $xtLink, $db,$success_order;

        global $language;

        if ($language->code=='de') {
            $shop_id = XT_TRUSTED_SHOPS_SCHUTZ_KEY_DE;
        }
        if ($language->code=='en') {
            $shop_id = XT_TRUSTED_SHOPS_SCHUTZ_KEY_EN;
        }
        if ($language->code=='fr') {
            $shop_id = XT_TRUSTED_SHOPS_SCHUTZ_KEY_FR;
        }
        return $shop_id;

    }
    
    function _validID($id) {
        if (strlen($id)!=33) return false;
        if (substr($id,0,1)!='X') return false;
        return true;    
    }

}
?>