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
# @version $Id$
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

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/class.xt_master_slave_products.php';

class paypal_qr
{

    var $customers_status = PAYPAL_QR_SHOPPING_CUSTOMER_STATUS_NEW;
 //   var $shopIdentifier = PAYPAL_QR_SHOPPING_SHOP_IDENTIFIER;
    var $shipping_costs_tax_class = PAYPAL_QR_SHOPPING_SHIPPING_TAX_CLASS_ID;
    var $order_status_new = PAYPAL_QR_SHOPPING_NEW_ORDER_STATUS;
    var $qr_code_template = 3;
    var $image_caching_time = 3600;
    var $api_version = '2.0';
    var $api_version_merchant = '1.0';
    var $debug = ___DEBUG;

    function __construct()
    {


        // identifier
        $this->shopIdentifier=$this->createIdenfifier();

        $this->api_user = md5($this->shopIdentifier);
        $this->api_key=md5($this->api_user);
        $this->api_signature=md5($this->api_key);

    //    $mode = 'testing';
        $mode = 'live';

        if($mode=='testing') {
            $this->API_Endpoint_merchant = 'https://qa.qrshp.de/MTSWindowShopping/merchantapi';
            $this->API_Endpoint_qr = 'https://qa.qrshp.de/MTSWindowShopping/ws/webshop';
        } else {
            $this->API_Endpoint_qr = 'https://www.qrshp.de/MTSWindowShopping/ws/webshop';
            $this->API_Endpoint_merchant = 'https://www.qrshp.de/MTSWindowShopping/merchantapi';
        }

    }


    /**
     * process Order Information
     *
     * @param $data
     */
    function processOrder($data)
    {
        global $db, $store_handler, $language;

        $order_language = $data->attributes->language;

        $lng = $language->_getLanguageList('admin', 'code');
        if (!isset($lng[$order_language])) {
            $this->order_language = _STORE_LANGUAGE;
        } else {
            $this->order_language = $order_language;
        }


        // check if customer allready existing
        if ($this->customerExists($data->data->address->email) == false) {
            // add new customer
            $address = $this->_convertAddress($data->data->address);
            $customers_id = $this->_registerCustomer($address);
            $address_book_id = $this->_registerAddress($address, $customers_id);
        } else {
            $customers_id = $this->customerExists($data->data->address->email);
            $address = $this->_convertAddress($data->data->address);
            $address_book_id = $this->_registerAddress($address, $customers_id);
        }

        // generate order
        $order_id = $this->_insertOrder($data, $customers_id, $address, $address_book_id);


    }

    private function _convertName($name)
    {
        $pos = stripos($name, ' ');
        $firstname = substr($name, 0, $pos);
        $lastname = substr($name, $pos + 1, strlen($name));
        return array('firstname' => $firstname, 'lastname' => $lastname);
    }

    private function _convertAddress($address)
    {
        $addr['customers_street_address'] = $address->street;
        $addr['customers_city'] = $address->city;
        $addr['customers_postcode'] = $address->zipcode;
        $addr['customers_country_code'] = $address->countryCode;
        $addr['customers_email_address'] = $address->email;
        $addr['customers_phone'] = '';
        $name = $this->_convertName($address->name);
        $addr['customers_firstname'] = $name['firstname'];
        $addr['customers_lastname'] = $name['lastname'];
        $addr['customers_company'] = '';
        return $addr;
    }

    /**
     * insert order into database
     *
     * @param $data
     * @param $customers_id
     * @param $address
     * @param $address_book_id
     * @return bool
     */
    private function _insertOrder($data, $customers_id, $address, $address_book_id)
    {
        global $db;

        // country
        $country = new countries(false);
        $country_info = $country->_getCountryData($address['customers_country_code']);

        // tax settings
        $tax = new tax();
        $tax_data = array('country_code' => $address['customers_country_code'], 'zone' => $country_info['zone_id']);
        $tax->setValues($tax_data);
        $tax->_buildData();


        $order_data = array();
        $order_data['customers_id'] = $customers_id;
        $order_data['customers_status'] = $this->customers_status;
        $order_data['customers_email_address'] = $address['customers_email_address'];
        $order_data['delivery_firstname'] = $address['customers_firstname'];
        $order_data['delivery_lastname'] = $address['customers_lastname'];
        $order_data['delivery_street_address'] = $address['customers_street_address'];
        $order_data['delivery_zone'] = $country_info['zone_id'];
        $order_data['delivery_city'] = $address['customers_city'];
        $order_data['delivery_postcode'] = $address['customers_postcode'];
        $order_data['delivery_country'] = $country_info['countries_name'];
        $order_data['delivery_country_code'] = $address['customers_country_code'];
        $order_data['delivery_address_book_id'] = $address_book_id;

        $order_data['billing_firstname'] = $order_data['delivery_firstname'];
        $order_data['billing_lastname'] = $order_data['delivery_lastname'];
        $order_data['billing_street_address'] = $order_data['delivery_street_address'];
        $order_data['billing_zone'] = $order_data['delivery_zone'];
        $order_data['billing_city'] = $order_data['delivery_city'];
        $order_data['billing_postcode'] = $order_data['delivery_postcode'];
        $order_data['billing_country'] = $country_info['countries_name'];
        $order_data['billing_country_code'] = $order_data['delivery_country_code'];
        $order_data['billing_address_book_id'] = $order_data['delivery_address_book_id'];

        $order_data['payment_code'] = 'paypal_qr';
        $order_data['shipping_code'] = '';
        $order_data['currency_code'] = 'EUR';
        $order_data['currency_value'] = '1';
        $order_data['language_code'] = $this->order_language;
        $order_data['orders_status'] = $this->order_status_new;
        $order_data['account_type'] = '0';
        $order_data['allow_tax'] = '1';
        $order_data['shop_id'] = '1';
        $order_data['date_purchased'] = $db->BindTimeStamp(time());
        $order_data['last_modified'] = $db->BindTimeStamp(time());
        $order_data['purchaseOrderIdentifier'] = $data->data->purchaseOrderIdentifier;
        $order_data['paymentReference'] = $data->attributes->paymentReference;

        // check if there is existing order

        $db->Execute("DELETE FROM " . TABLE_ORDERS . " WHERE purchaseOrderIdentifier=? ",array($order_data['purchaseOrderIdentifier']));

        $rs = $db->Execute("SELECT * FROM " . TABLE_ORDERS . " WHERE purchaseOrderIdentifier=? ", array($order_data['purchaseOrderIdentifier']));
        if ($rs->RecordCount() > 0) {
            return false;
        } else {
            $db->AutoExecute(TABLE_ORDERS, $order_data);

            $orders_id = $db->Insert_ID();

            $price = new price();

            $stock = new stock();
            // insert products
            $prods = 0;
            foreach ($data->data->positions as $key => $prod) {

                // new product
                $product = new product($prod->productIdentifier, 'default', 1, $this->order_language);

                $tax_class = $product->data['products_tax_class_id'];

                $tax_rate = $tax->data[$tax_class];

                $single_price = $prod->itemAmount / $prod->quantity;

                $product_data = array();
                $product_data['orders_id'] = $orders_id;
                $product_data['products_id'] = $prod->productIdentifier;
                $product_data['products_model'] = $product->data['products_model'];
                $product_data['products_name'] = $product->data['products_name'];
                $product_data['products_price'] = $price->_removeTax($single_price, $tax_rate);
                $product_data['products_discount'] = '0';
                $product_data['products_tax'] = $tax_rate;
                $product_data['products_tax_class'] = $tax_class;
                $product_data['products_quantity'] = $prod->quantity;
                $product_data['allow_tax'] = 1;

                // stock handling
                $stock->removeStock($prod->productIdentifier, $prod->quantity);

                $db->AutoExecute(TABLE_ORDERS_PRODUCTS, $product_data);
                $prods++;

            }

            // insert shipping costs
            $shipping_costs = $data->data->shippingAmount;
            if ($shipping_costs > 0) {

                $tax_rate = $tax->data[$this->shipping_costs_tax_class];

                $shipping_data = array();
                $shipping_data['orders_id'] = $orders_id;
                $shipping_data['orders_total_key'] = 'shipping';
                $shipping_data['orders_total_key_id'] = '1';
                $shipping_data['orders_total_model'] = 'Shipping';
                $shipping_data['orders_total_name'] = 'Shipping';
                $shipping_data['orders_total_price'] = $price->_removeTax($shipping_costs, $tax_rate);
                $shipping_data['orders_total_tax'] = $tax_rate;
                $shipping_data['orders_total_tax_class'] = $this->shipping_costs_tax_class;
                $shipping_data['orders_total_quantity'] = '1';
                $shipping_data['allow_tax'] = '1';
                $db->AutoExecute(TABLE_ORDERS_TOTAL, $shipping_data);

            }

            $stat = array();
            $stat['orders_id'] = $orders_id;
            $stat['orders_stats_price'] = $data->data->amount;
            $stat['products_count'] = $prods;
            $db->AutoExecute(TABLE_ORDERS_STATS, $stat);

            // order status history
            $stat = array();
            $stat['orders_id'] = $orders_id;
            $stat['orders_status_id'] = $this->order_status_new;
            $db->AutoExecute(TABLE_ORDERS_STATUS_HISTORY, $stat);

        }

        // display confirmation

        $json = '{
  "attributes": {
    "shopOrderReference": "' . $orders_id . '",
    "purchaseOrderIdentifier": "' . $order_data['purchaseOrderIdentifier'] . '"
  },
  "system": "WSPP",
  "type": "31",
  "version": "2.0"
}';

        $json = base64_encode($json);
        echo $json;

    }


    /**
     * register customer and add default address
     *
     * @param $data
     * @return mixed
     */
    private function _registerCustomer($data)
    {
        global $db, $store_handler, $xtPlugin;

        $customer = array();

        ($plugin_code = $xtPlugin->PluginCode('class.paypal_qr.php:_registerCustomer_top')) ? eval($plugin_code) : false;

        $customer['customers_status'] = $this->customers_status;
        $customer['customers_email_address'] = $data['customers_email_address'];
        $customer['account_type'] = '0';
        $customer['date_added'] = $db->BindTimeStamp(time());
        $customer['shop_id'] = $store_handler->shop_id;

        $db->AutoExecute(TABLE_CUSTOMERS, $customer);
        $customers_id = $db->Insert_ID();

        return $customers_id;
    }

    /**
     * insert customers address
     *
     * @param $data
     * @param $customers_id
     * @return mixed
     */
    private function _registerAddress($data, $customers_id)
    {
        global $db;

        $customers_address = array();
        $customers_address['customers_id'] = $customers_id;
        $customers_address['customers_firstname'] = $data['customers_firstname'];
        $customers_address['customers_lastname'] = $data['customers_lastname'];
        $customers_address['customers_street_address'] = $data['customers_street_address'];
        $customers_address['customers_postcode'] = $data['customers_postcode'];
        $customers_address['customers_city'] = $data['customers_city'];
        $customers_address['customers_country_code'] = $data['customers_country_code'];
        $customers_address['address_class'] = 'default';
        $customers_address['date_added'] = $db->BindTimeStamp(time());

        $qry = "SELECT address_book_id FROM " . TABLE_CUSTOMERS_ADDRESSES . " 
				WHERE customers_id=? and 
				customers_country_code=? and 
				customers_street_address=? LIMIT 0,1";

        $record = $db->Execute($qry,array($customers_address['customers_id'],$customers_address['customers_country_code'],$customers_address['customers_street_address']));
        if ($record->RecordCount() == 1) {
            return $record->fields['address_book_id'];
        } else {
            $db->AutoExecute(TABLE_CUSTOMERS_ADDRESSES, $customers_address);
            return $db->Insert_ID();
        }


    }


    function processBasket($data)
    {
        global $db;


        if (!isset($data->data->purchaseOrderIdentifier)) return false;


        // empty basket table
        $db->Execute("DELETE FROM xt_plg_paypal_qr_basket WHERE purchaseOrderIdentifier=? ",array($data->data->purchaseOrderIdentifier));

        $cart = new cart();
//__debug($data);
        // insert proucts
        foreach ($data->data->positions as $key => $product) {


            $insert = array();
            $insert['purchaseOrderIdentifier'] = $data->data->purchaseOrderIdentifier;
            $insert['customers_id'] = '0';
            $insert['products_key'] = $product->productIdentifier . '_XT';
            $insert['products_id'] = $product->productIdentifier;
            $insert['products_quantity'] = $product->quantity;
            $insert['positionNo'] = $product->positionNo;
            $insert['type'] = 'product';
            $insert['status'] = '1';
            $insert['date_added'] = $db->BindTimeStamp(time());
            $db->AutoExecute('xt_plg_paypal_qr_basket', $insert);


            $cart_data = array();
            $cart_data['product'] = $insert['products_id'];
            $cart_data['qty'] = $insert['products_quantity'];
            $cart->_addCart($cart_data);

        }

        $cart->_refresh();
 //       __debug($cart);
        //
        // TODO shipping calculation
        $total = $cart->total['plain'] + PAYPAL_QR_SHOPPING_SHIPPING_COSTS;

        // response
        $json = '{
  "attributes": {

  },
  "data": {
    "amount": "' . $total . '",
    "currencyCode": "978",
    "handlingAmount": "0",
    "itemAmount": ' . $cart->total['plain'] . ',
    "maxDeliveryTimeDays": null,
    "minDeliveryTimeDays": null,
    "positions": [';

        $i = 1;
        $prod = array();

        foreach ($cart->show_content as $key => $product) {
//__debug($cart->show_content);
            $price = $product['products_final_price']['plain'];
            if ($product['paypal_qr_price']>0) {
                $price = $product['paypal_qr_price'];
            }

            $prod[] = '      {
        "currencyCode": "978",
        "itemAmount": "' . $price . '",
        "positionNo": "' . $i . '",
        "productIdentifier": "' . $product['products_id'] . '",
        "quantity": "' . $product['products_quantity'] . '",
        "shippingAmount": 0,
        "taxAmount": 0,
        "validationCode": 0
      }';
            $i++;
        }

        $json .= implode(',', $prod);

        $json .= '],
    "purchaseOrderIdentifier": "' . $data->data->purchaseOrderIdentifier . '",
    "resendCount": 0,
    "shippingAmount": "'.PAYPAL_QR_SHOPPING_SHIPPING_COSTS.'",
    "shopIdentifier": "' . $this->shopIdentifier . '",
    "taxAmount": 0,
    "type": "PurchaseOrder",
    "validationCode": 0
  },
  "system": "WSPP",
  "type": "24",
  "version": "2.0"
}';

        if($this->debug=='true') echo $json;
        $json = base64_encode($json);
        echo $json;


    }

    function setLanguage($language_code) {
        $this->language = $language_code;
    }


    function product($pID, $language_code)
    {
        global $db;

        $this->setLanguage($language_code);

        $p_info = new product($pID, 'full', '1', $this->language);

        if ($p_info->data['products_quantity']<=0 or !$p_info->is_product) {
            $data = $this->mts_app_status('99','Produkt nicht in ausreichender Menge Verfügbar');
            $json = json_encode($data);
            $json = base64_encode($json);
            echo $json;
            die('');
        }

        $image = $p_info->data['products_image'];
        if ($image != 'product:') {
            $base = _SYSTEM_BASE_URL . _SRV_WEB . _SRV_WEB_IMAGES . 'info/';
            $image = $base . str_replace('product:', '', $p_info->data['products_image']);
        } else {
            $image = '';
        }

        $more_images = array();
        $additional_images = '';
        if (is_array($p_info->data['more_images'])) {
            $additional_images = array();
            foreach ($p_info->data['more_images'] as $key => $img) {
                $additional_images[] = $base . str_replace('product:', '', $img['file']);
                $more_images[] = array('imageUrl' => $base . str_replace('product:', '', $img['file']));
            }
            $additional_images = '"additionalImages":[{"imageUrl":"' . implode('"},{"imageurl":"', $additional_images) . '"}],';
        }

        // master /slave?
        $resp = array();
        $type = "Product";

        if ($p_info->data['products_master_flag']=='1') {
            $resp = $this->masterHandler($pID,$p_info->data['products_model']);
            if (isset($resp['variantLabel'])) $type = "VariantProduct";
            if (isset($resp['ColorSizeProduct'])) $type="ColorSizeProduct";
        } else {
            $resp = false;
        }


        $data = array();
        if (count($more_images) > 0) $data['additionalImages'] = $more_images;

        $data['type'] = $type;

        if ($type=='VariantProduct') {
            $data['variantLabel']=$resp['variantLabel'];
            $data['variants']=$resp['items'];
        } elseif ($type=='ColorSizeProduct') {
            $data['colorSizes']=$resp['items'];
        }


        // 987 = EURO hardcoded
        $data['currencyCode'] = "978";
        $data['imageUrl'] = $image;
        $price = $p_info->data['products_price']['plain'];
        if ($p_info->data['paypal_qr_price']>0) {
            $price = $p_info->data['paypal_qr_price'];
        }
        $data['itemAmount'] = number_format($price, 2);
        $data['name'] = $p_info->data['products_name'];
        $data['productDescription'] = strip_tags($p_info->data['products_description']);
        $data['productDescriptionShort'] = strip_tags($p_info->data['products_short_description']);
        $data['productIdentifier'] = (string)$pID;
        $data['shippingCosts'] = 0;
        $data['shopIdentifier'] = $this->shopIdentifier;
        $data['shortName'] = substr($p_info->data['products_name'], 0, 20);
        $data['taxAmount'] = 0;

        // digital good
        if ($p_info->data['products_digital']=='1') $data['digitalGood']='true';


       $json = $this->pp_json_encode($data);

        $json = base64_encode($json);
        echo $json;

    }

    private function pp_json_encode($arr)
    {

        $resp = array();
        $resp['data'] = $arr;
        $resp['system'] = "WSPP";
        $resp['type'] = "21";
        $resp['version'] = $this->api_version;

        $json = json_encode($resp);
        // remove "" from all float variables
        $json = preg_replace("/\"(\d{1,99}\.\d{2})\"/", "$1", $json);
        $json = str_replace("\"false\"","false",$json);
        $json = str_replace("\"true\"","true",$json);
        $json = str_replace("\/","/",$json);

        if($this->debug=='true')   {
            $obj = json_decode($json);
         //   __debug($obj);
            echo $json;
        }

        return $json;


    }

    /**
     *
     * check if customer exists in database
     *
     * @param $email
     * @return bool
     */
    private function customerExists($email)
    {
        global $db, $store_handler;

        $qry = "SELECT customers_id, customers_status FROM " . TABLE_CUSTOMERS . " 
				WHERE customers_email_address =? and 
				shop_id = ? and account_type != 1 LIMIT 0,1";
        $record = $db->Execute($qry,array( $email,$store_handler->shop_id));
        if ($record->RecordCount() == 1) {
            return $record->fields['customers_id'];
        }
        return false;
    }

    public function generateQRcodeadmin($pID,$label='',$force='false') {
        global $db;

        $image_filename = md5($this->shopIdentifier) . $pID . '.png';

    //    if (!file_exists(_SRV_WEBROOT._SRV_WEB_PLUGINS.'/paypal_qr_shopping/qr_images/'.$image_filename) or $force=='true') {

        // new product
        $this->setLanguage('de');
        $p_info = new product($pID, 'full', '1', $this->language);
        $product=$p_info->data;

        $data = array();
        $data['products_id'] = $pID;
        $data['shortName'] = substr($product['products_name'], 0, 20);
        $data['itemAmount'] = number_format($product['products_price']['plain'], 2);
        $data['taxAmount'] = '0';
        $data['currencyCode'] = '978';
        $data['products_id'] = $product['products_id'];

        if ($label=='') {
            $label = $product['paypal_qr_type'];
        }

    //    echo $label;
        $req = $this->Req_Product_Barcode($data,$label);

       //

//__debug($req);
        if ($req != -1) {
            $ch = fopen(_SRV_WEBROOT . _SRV_WEB_PLUGINS . '/paypal_qr_shopping/qr_images/' . $image_filename, 'w');
            fwrite($ch, $req);
            fclose($ch);
            $qry = "UPDATE " . TABLE_PRODUCTS . " SET paypal_qr_url=? WHERE products_id=?";
            $db->Execute($qry,array($image_filename,(int)$pID));
            return $image_filename;
        } else {
            // show error
            echo 'error';
        }

   //     } else {
   //         return $image_filename;
   //     }

    }


    private function Req_Product_Barcode($data,$label)
    {

        $json = '{
       "attributes":{
       "style":"Label'.$label.'"
       },
  "data":{
    "type": "Product",
    "shopIdentifier": "' . $this->shopIdentifier . '",
    "productIdentifier": "' . $data['products_id'] . '",
    "itemAmount": ' . $data['itemAmount'] . ',
    "shortName": "' . $data['shortName'] . '",
    "taxAmount": ' . $data['taxAmount'] . ',
    "currencyCode": "' . $data['currencyCode'] . '"
  },
  "system": "WSPP",
  "type": "32",
  "version": "2.0"
}';

        return $this->callService($json, $this->API_Endpoint_qr, 'ws-request');


    }

    private function masterHandler($pID, $model)
    {
        global $db;

        $data = array();

        $ms = new xt_master_slave_functions();

        $sql_where = " WHERE  " . TABLE_PRODUCTS . ".products_master_model=? ";
        $sql_where .= " AND " . TABLE_PRODUCTS . ".products_status = '1'";
        if (_STORE_STOCK_CHECK_DISPLAY == 'false' && _SYSTEM_STOCK_HANDLING == 'true') {
            $sql_where .= " AND " . TABLE_PRODUCTS . ".products_quantity > 0";
        }
        $sql = "
          SELECT products_id
          FROM   " . TABLE_PRODUCTS . $sql_where . ";";

        $rs = $db->Execute($sql,array($model));

        // slave list
        while (!$rs->EOF) {

            // check if there are options on this product

            $slave = new product($rs->fields['products_id'], 'full', '1', $this->language);

            $image = $slave->data['products_image'];
            if ($image != 'product:') {
                $base = _SYSTEM_BASE_URL . _SRV_WEB . _SRV_WEB_IMAGES . 'info/';
                $image = $base . str_replace('product:', '', $slave->data['products_image']);
            } else {
                $image = '';
            }

            $attributes = $this->getFullAttributesData($rs->fields['products_id']);

            if (count($attributes)==1) {
                // use variant model
                $data['variantLabel']=$attributes[0]['option_name'];
                $data['items'][]=array('imageUrl'=>$image,'itemAmount'=>number_format($slave->data['products_price']['plain'], 2),'productIdentifier'=>(string)$rs->fields['products_id'],'taxAmount'=>0,'variant'=>$attributes[0]['option_value_name']);


            } elseif(count($attributes)==2) {
                // check which one i color
                if ($attributes[0]['paypal_variant']=='size') {
                    $color = $attributes[1]['option_value_name'];
                    $size =$attributes[0]['option_value_name'];
                } else {
                    $color=$attributes[0]['option_value_name'];
                    $size=$attributes[1]['option_value_name'];
                }


                $price = $slave->data['products_price']['plain'];
                if ($slave->data['paypal_qr_price']>0) {
                    //TODO add tax
                    $price = $slave->data['paypal_qr_price'];
                }

                // use color/size
                $data['items'][]=array('imageUrl'=>$image,
                    'itemAmount'=>number_format($price, 2),
                    'productIdentifier'=>(string)$rs->fields['products_id'],
                    'taxAmount'=>0,
                    'size'=>$size,
                    'color'=>$color);
                $data['ColorSizeProduct']='true';

            } else{
                // not available
                return false;
            }


            $rs->MoveNext();
        }

        return $data;


    }

    private function getFullAttributesData ($pID) {
        global $db, $language;

        $option_data = $db->Execute("select padv.attributes_id as option_id,paa.paypal_qr_variant as paypal_variant, padv.attributes_name as option_name, pad.attributes_id as option_value_id, pad.attributes_name as option_value_name 
									from " . TABLE_PRODUCTS_TO_ATTRIBUTES . " pa 
									left join ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." pad on (pa.attributes_id = pad.attributes_id and pad.language_code = '" . $this->language . "') 
									left join ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." padv on (pa.attributes_parent_id = padv.attributes_id and padv.language_code = '" . $this->language . "') 
									left join ".TABLE_PRODUCTS_ATTRIBUTES." paa on (pa.attributes_id = paa.attributes_id) where pa.products_id =? ",array($pID));
        if($option_data->RecordCount() > 0){
            while(!$option_data->EOF){
                $data[] = $option_data->fields;
                $option_data->MoveNext();
            }
            $option_data->Close();
        }

        return $data;
    }


    /**
     *product not available anymore (stock etc)
     * */
    private function mts_app_status($code='08',$message='') {

        $resp['system'] = "WSPP";
        $resp['type'] = "00";
        $resp['version'] = $this->api_version;
        $resp['attributes']['code']=$code;
        $resp['attributes']['message']=$message;
        return $resp;

    }


    private function callService($json_string, $url, $param = '',$login='false')
    {
        global $db;

    //    echo $json_string;

        $post_string = base64_encode($json_string);

        //   $post_string = 'ws-request='.$post_string;
        $post_string = $param . '=' . $post_string;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if ($login=='true') {


            $sql = 'select plugin_id from '.TABLE_PLUGIN_PRODUCTS.' where code ="paypal_qr_shopping";';
            $arr = $db->getRow($sql);
            $qr_plugin_id = $arr['plugin_id'];

            // hardcode shop_id = 1
            $sql = "SELECT * FROM ".TABLE_PLUGIN_CONFIGURATION." WHERE plugin_id=? and shop_id='1'";
            $rs = $db->Execute($sql,array($qr_plugin_id));
            $config_data = array();
            while (!$rs->EOF) {
                $config_data[$rs->fields['config_key']]=$rs->fields['config_value'];
                $rs->MoveNext();
            }
          //  print_r($config_data);
            curl_setopt($ch,CURLOPT_HTTPHEADER,array('Expect:','X-QRSHOPPING-API-USERNAME: '.$config_data['PAYPAL_QR_SHOPPING_X_API_USER'],'X-QRSHOPPING-API-PASSWORD: '.$config_data['PAYPAL_QR_SHOPPING_X_API_KEY'],'X-QRSHOPPING-API-SIGNATURE: '.$config_data['PAYPAL_QR_SHOPPING_X_API_SIGNATURE']));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        //setting the nvpreq as POST FIELD to curl
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
                      // moving to display page to display curl errors
            echo curl_errno($ch);
            echo curl_error($ch);
            curl_close($ch);
            return -1;

        } else {

            // check for error
            $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

            if (!strstr($content_type, 'image/png')) {
                $res = base64_decode($response);
                $json = json_decode($res);
                $error = $json->attributes->message . ' ' . $json->attributes->code;
         //       echo 'Webservice Error:'.$error;
                // TODO system log
          //      echo $error;
            }

            curl_close($ch);
            return $response;

        }

    }

    function Req_Read_Merchant_account() {

        $resp['system'] = "MSSAPP";
        $resp['type'] = "02";
        $resp['version'] = $this->api_version_merchant;
        $resp['attributes']['shopIdentifier']=$this->shopIdentifier;

        $json = json_encode($resp);

        $resp = $this->callService($json, $this->API_Endpoint_merchant, 'merchant-api-request');

        $var = str_replace('<br>','',$resp);
        $json = base64_decode($var);
        $obj = json_decode($json);
    //    __debug($obj);
        if (isset($obj->attributes->message)) {
            // damn, error
            $msg = $obj->attributes->message;
            $code = $obj->attributes->code;
        }

    }

    function delete_merchant_account() {
        global $db,$xtLink;




        $data['system'] = "MSSAPP";
        $data['type'] = "04";
        $data['version'] = $this->api_version_merchant;
        $data['attributes']['shopIdentifier']=$this->shopIdentifier;

        $json = json_encode($data);
        $json = preg_replace("/\"(\d{1,99}\.\d{2})\"/", "$1", $json);
        $json = str_replace("\/","/",$json);
        $json = str_replace("\"false\"","false",$json);
        $json = str_replace("\"true\"","true",$json);
     //       __debug($data);
        $resp = $this->callService($json, $this->API_Endpoint_merchant, 'merchant-api-request','true');

        $var = str_replace('<br>','',$resp);
        $json = base64_decode($var);
        $obj = json_decode($json);
    //__debug($obj);
        if ($obj->attributes->ACK != 'Success') {
            $log_data = array();
            $log_data['module'] = 'paypal_qr_shopping';
            $log_data['class'] = 'error';
            $log_data['transaction_id'] = '0';
            $log_data['error_msg'] = 'account could not be deleted'.' '.$obj->attributes->message;
            $this->_addLogEntry($log_data);
        } else {
            $log_data = array();
            $log_data['module'] = 'paypal_qr_shopping';
            $log_data['class'] = 'success';
            $log_data['transaction_id'] = '0';
            $log_data['error_msg'] = 'account deleted';
            $this->_addLogEntry($log_data);
        }


    }

    function update_merchant_account() {
        global $db,$xtLink;

        // load db Settings

        $sql = 'select plugin_id from '.TABLE_PLUGIN_PRODUCTS.' where code ="paypal_qr_shopping";';
        $arr = $db->getRow($sql);
        $qr_plugin_id = $arr['plugin_id'];

        // hardcode shop_id = 1
        $sql = "SELECT * FROM ".TABLE_PLUGIN_CONFIGURATION." WHERE plugin_id=? and shop_id='1'";
        $rs = $db->Execute($sql,array($qr_plugin_id));
        $config_data = array();
        while (!$rs->EOF) {
            $config_data[$rs->fields['config_key']]=$rs->fields['config_value'];
            $rs->MoveNext();
        }


        if (PAYPAL_QR_SHOPPING_COUNTRY!='DE') {
            echo '{"success":false,"new_id":"","error_message":"PayPal QR Shopping wird aktuell nur DE unterstützt."}';
            die('');
        }

        // ok, check if QR API code etc allready defined

        // constant
        $data['system'] = "MSSAPP";
        $data['type'] = "03";
        $data['version'] = $this->api_version_merchant;
        $data['data']['type']='Shop';
        $data['data']['address']['type']='Address';

        $data['data']['address']['city']=$config_data['PAYPAL_QR_SHOPPING_CITY'];
        $data['data']['address']['country']='Deutschland';
        $data['data']['address']['countryCode']=$config_data['PAYPAL_QR_SHOPPING_COUNTRY'];
        $data['data']['address']['email']=$config_data['PAYPAL_QR_SHOPPING_EMAIL'];
        $data['data']['address']['firstname']=$config_data['PAYPAL_QR_SHOPPING_FIRSTNAME'];
        $data['data']['address']['name']=$config_data['PAYPAL_QR_SHOPPING_LASTNAME'];
        $data['data']['address']['street']=$config_data['PAYPAL_QR_SHOPPING_STREET'];
        $data['data']['address']['zipcode']=$config_data['PAYPAL_QR_SHOPPING_PLZ'];

        $data['data']['catalogueUrl']=$xtLink->_link(array('page' => 'callback', 'paction' => 'paypal_qr_shopping'));
        $data['data']['fulfillmentUrl']=$xtLink->_link(array('page' => 'callback', 'paction' => 'paypal_qr_shopping'));
        $data['data']['payPalNotifyUrl']=$xtLink->_link(array('page' => 'callback', 'paction' => 'paypal_qr_shopping'));


        $data['data']['shopIdentifier']=$this->shopIdentifier;

        // autogenerate api credentials

        $data['data']['externalApiUser']=$this->api_user;
        $data['data']['externalApiPassword']=$this->api_key;
        $data['data']['externalApiSignature']=$this->api_signature;

        if ($config_data['PAYPAL_QR_SHOPPING_SALE']=='true') {
            $data['data']['captureIncluded']='true';
        } else {
            $data['data']['captureIncluded']='false';
        }


        // load tnc
//        $data['data']['termsAndConditions']='';

        $data['data']['name']=$config_data['PAYPAL_QR_SHOPPING_SHOP_NAME'];
        $data['data']['shortname']=$config_data['PAYPAL_QR_SHOPPING_SHOP_NAME'];
        $data['data']['hiddenInLocator']=$config_data['PAYPAL_QR_SHOPPING_SHOW_SHOP'];

        // optional in doc, but isnt

        $logo = $config_data['PAYPAL_QR_SHOPPING_LOGO_URL'];
        $file =  _SRV_WEBROOT  . 'media/logo/'.$logo;

        $data['data']['logoData']='';
        $data['data']['logoSmallData']='';
        if (file_exists($file)) {
            $img_content = file_get_contents($file);
            $data['data']['logoSmallData']=base64_encode($img_content);
        }

        $logo = $config_data['PAYPAL_QR_SHOPPING_BANNER_LOGO_URL'];
        $file =  _SRV_WEBROOT  . 'media/logo/'.$logo;
        if (file_exists($file)) {
            $img_content = file_get_contents($file);
            $data['data']['logoData']=base64_encode($img_content);
        }


        $content = new content($config_data['PAYPAL_QR_SHOPPING_AGB_CONTENT']);
        $data['data']['termsAndConditionsData']='';
        if (isset($content->data['body'])) $data['data']['termsAndConditionsData']=base64_encode($content->data['body']);


        $json = json_encode($data);
        $json = preg_replace("/\"(\d{1,99}\.\d{2})\"/", "$1", $json);
        $json = str_replace("\/","/",$json);
        $json = str_replace("\"false\"","false",$json);
        $json = str_replace("\"true\"","true",$json);


        $error = $this->FormCheck($data);


        if (!$error) {
            $resp = $this->callService($json, $this->API_Endpoint_merchant, 'merchant-api-request','true');

            $var = str_replace('<br>','',$resp);
            $json = base64_decode($var);
            $obj = json_decode($json);

            if ($obj->type!='00') {
                echo '{"success":true,"new_id":""}';
                die('');
            } else {
                $error = $this->errorMessage($obj->attributes->code,$obj->attributes->message);

                echo '{"success":false,"new_id":"","error_message":"'.$this->errorMessage($obj->attributes->code,$obj->attributes->message).'"}';
                die('');
            }
        } else {
            echo '{"success":false,"new_id":"","error_message":"Bitte geben Sie alle mit * markierten Felder an"}';
            die('');
        }




    }

    private function FormCheck($data) {
        // check minimum fields
        if (strlen($data['data']['logoSmallData'])<10) return true;
        if (strlen($data['data']['logoData'])<2) return true;
        if (strlen($data['data']['address']['name'])<2) return true;
        if (strlen($data['data']['address']['street'])<2) return true;
        if (strlen($data['data']['address']['city'])<2) return true;
        if (strlen($data['data']['address']['email'])<2) return true;
        if (strlen($data['data']['address']['firstname'])<2) return true;
        if (strlen($data['data']['address']['street'])<2) return true;
        if (strlen($data['data']['address']['zipcode'])<2) return true;



        return false;
    }

    private function errorMessage($code,$message) {
        $messages = array();
        $messages['01'] = 'Der Shop konnte auf dem QRShopping-Server nicht gefunden werden. Bitte legen Sie ihren Shop erneut auf dem QRShopping-Server an oder kontaktieren Sie den Business Support.';
        $messages['02'] = 'Bitte stellen Sie sicher, dass Sie einen Shop-Identiﬁer eingegeben haben. Falls Sie einen Shop-Identiﬁer eingegeben haben und diese Fehlermeldung erneut erscheint, kontaktieren Sie bitte den Business Support.';
        $messages['04'] = 'Die Autorisierung konnte nicht durchgeführt werden. Bitte überprüfen Sie ihre QRShopping-API-Zugangsdaten.';
        $messages['05'] = 'Die Autorisierung konnte nicht durchgeführt werden. Bitte überprüfen Sie ihre QRShopping-API-Zugangsdaten.';

        if (isset($messages[$code]))
        return $messages[$code];

        return $message;

    }

    function createIdenfifier() {

        $_lic = _SRV_WEBROOT . 'lic/license.txt';
        if (!file_exists($_lic))
            die('- main lic missing -');
        $val_line = '';
        $bline = '';
        $_file_content = file($_lic);
        foreach ($_file_content as $bline_num => $bline) {
            if (preg_match('/key:/', $bline)) {
                $val_line = $bline;
                break;
            }
        }

        $val_line = explode(':', $val_line);
        $_shop_lic = '';
        $_shop_lic = trim($val_line[1]);

        $_shop_lic = substr($_shop_lic,0,10);

        return $_shop_lic;

    }

    function create_merchant_account() {
        global $db,$xtLink;

        if (PAYPAL_QR_SHOPPING_COUNTRY!='DE') {
            echo '{"success":false,"new_id":"","error_message":"PayPal QR Shopping wird aktuell nur DE unterstützt."}';
            die('');
        }

        // load values from DB
        $sql = 'select plugin_id from '.TABLE_PLUGIN_PRODUCTS.' where code ="paypal_qr_shopping";';
        $arr = $db->getRow($sql);
        $qr_plugin_id = $arr['plugin_id'];

        // hardcode shop_id = 1
        $sql = "SELECT * FROM ".TABLE_PLUGIN_CONFIGURATION." WHERE plugin_id=? and shop_id='1'";
        $rs = $db->Execute($sql,array($qr_plugin_id));
        $config_data = array();
        while (!$rs->EOF) {
            $config_data[$rs->fields['config_key']]=$rs->fields['config_value'];
            $rs->MoveNext();
        }


        // constant
        $data['system'] = "MSSAPP";
        $data['type'] = "0C";
        $data['version'] = $this->api_version_merchant;
        $data['data']['type']='Shop';
        $data['data']['address']['type']='Address';

        $data['data']['address']['city']=$config_data['PAYPAL_QR_SHOPPING_CITY'];
        $data['data']['address']['country']='Deutschland';
        $data['data']['address']['countryCode']=$config_data['PAYPAL_QR_SHOPPING_COUNTRY'];
        $data['data']['address']['email']=$config_data['PAYPAL_QR_SHOPPING_EMAIL'];
        $data['data']['address']['firstname']=$config_data['PAYPAL_QR_SHOPPING_FIRSTNAME'];
        $data['data']['address']['name']=$config_data['PAYPAL_QR_SHOPPING_LASTNAME'];
        $data['data']['address']['street']=$config_data['PAYPAL_QR_SHOPPING_STREET'];
        $data['data']['address']['zipcode']=$config_data['PAYPAL_QR_SHOPPING_PLZ'];

        $data['data']['catalogueUrl']=$xtLink->_link(array('page' => 'callback', 'paction' => 'paypal_qr_shopping'));
        $data['data']['fulfillmentUrl']=$xtLink->_link(array('page' => 'callback', 'paction' => 'paypal_qr_shopping'));
        $data['data']['payPalNotifyUrl']=$xtLink->_link(array('page' => 'callback', 'paction' => 'paypal_qr_shopping'));

        // create shopidentifier
        $data['data']['shopIdentifier']=$this->createIdenfifier();

        // autogenerate api credentials

       // $data['data']['externalApiUser']=$this->rndString(64);
      //  $data['data']['externalApiPassword']=$this->rndString(64);
     //   $data['data']['externalApiSignature']=$this->rndString(128);
        $data['data']['externalApiUser']=$this->api_user;
        $data['data']['externalApiPassword']=$this->api_key;
        $data['data']['externalApiSignature']=$this->api_signature;

        if ($config_data['PAYPAL_QR_SHOPPING_SALE']=='true') {
            $data['data']['captureIncluded']='true';
        } else {
            $data['data']['captureIncluded']='false';
        }



        // load tnc
        $data['data']['name']=$config_data['PAYPAL_QR_SHOPPING_SHOP_NAME'];
        $data['data']['shortname']=$config_data['PAYPAL_QR_SHOPPING_SHOP_NAME'];
        $data['data']['hiddenInLocator']=$config_data['PAYPAL_QR_SHOPPING_SHOW_SHOP'];

        $logo = $config_data['PAYPAL_QR_SHOPPING_LOGO_URL'];
        $file =  _SRV_WEBROOT  . 'media/logo/'.$logo;

        $data['data']['logoData']='';
        $data['data']['logoSmallData']='';
        if (file_exists($file)) {
            $img_content = file_get_contents($file);
            $data['data']['logoSmallData']=base64_encode($img_content);
        }

        $logo = $config_data['PAYPAL_QR_SHOPPING_BANNER_LOGO_URL'];
        $file =  _SRV_WEBROOT  . 'media/logo/'.$logo;
        if (file_exists($file)) {
            $img_content = file_get_contents($file);
            $data['data']['logoData']=base64_encode($img_content);
        }


        $content = new content($config_data['PAYPAL_QR_SHOPPING_AGB_CONTENT']);
        $data['data']['termsAndConditionsData']='';
        if (isset($content->data['body'])) $data['data']['termsAndConditionsData']=base64_encode($content->data['body']);



        $json = json_encode($data);
        $json = preg_replace("/\"(\d{1,99}\.\d{2})\"/", "$1", $json);
        $json = str_replace("\/","/",$json);
        $json = str_replace("\"false\"","false",$json);
        $json = str_replace("\"true\"","true",$json);


        $error = $this->FormCheck($data);

        if (!$error) {
       $resp = $this->callService($json, $this->API_Endpoint_merchant, 'merchant-api-request');

        $var = str_replace('<br>','',$resp);
        $json = base64_decode($var);
        $obj = json_decode($json);

        if ($obj->type=='1C') {


            echo '{"success":true,"new_id":"","goto_url":"'.$obj->attributes->url.'"}';
            die('');
        } else {
            $obj->attributes->message = str_replace("\n",'',$obj->attributes->message);

            echo '{"success":false,"new_id":"","error_message":"code: '.$obj->attributes->code.' '.addslashes($obj->attributes->message).'"}';
            die('');
        }

        } else {
            echo '{"success":false,"new_id":"","error_message":"Bitte geben Sie alle mit * markierten Felder an"}';
            die('');
        }


    }

    function _addLogEntry($log_data) {
        global $db;
        if (is_array($log_data['callback_data'])) $log_data['callback_data'] = serialize($log_data['callback_data']);
        //$log_data['created'] =  $db->BindDate(time());
        if ($log_data['transaction_id']==null) 	$log_data['transaction_id']='';
        $db->AutoExecute(TABLE_CALLBACK_LOG,$log_data,'INSERT');
        $last_id = $db->Insert_ID();
        return $last_id;
    }

    private function rndString($length) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $result = '';
        for ($i = 0; $i < $length; $i++)
            $result .= $characters[mt_rand(0, 61)];

        return $result;
    }

    private function updateConfigValue($config_key,$value) {
        global $db;

        $db->Execute("UPDATE ".TABLE_PLUGIN_CONFIGURATION." SET config_value=? WHERE config_key=? ",array($value,$config_key));

    }

}

?>