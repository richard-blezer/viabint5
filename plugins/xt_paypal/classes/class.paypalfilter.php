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
 # @version $Id: class.paypalfilter.php 5382 2012-07-23 14:03:53Z tu $
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
class PaypalFilter extends formFilter{
    
    
    public static function formFields(){
        
        $eF = new ExtFunctions();
        
        $order_id = PhpExt_Form_TextField::createTextField("filter_paypal_order_id",ucfirst(TEXT_ORDERS_ID)) ->setEmptyText(TEXT_INCLUDES);
        $order_id = self::setWidth($order_id,"150px");
        $f[] =  $order_id;
        
        $customer_lastname = PhpExt_Form_TextField::createTextField("filter_paypal_customer_lastname",ucfirst(TEXT_CUSTOMERS_LASTNAME)) ->setEmptyText(TEXT_INCLUDES);
        $customer_lastname = self::setWidth($customer_lastname,"150px");
        
        $f1 = PhpExt_Form_DateField::createDateField("filter_paypal_date_from",  ucfirst(TEXT_DATE_PURCHASED)) ->setEmptyText(TEXT_FROM);
        $f1 =  self::setWidth($f1,"52px");
        $f2 = PhpExt_Form_DateField::createDateField("filter_paypal_date_to","") -> setEmptyText(TEXT_TO);
        $f2 =  self::setWidth($f2,"52px");
        $f[] = self::twoCol($f1, $f2);
        
        $f[] = self::setWidth($eF->_multiComboBox2( ucfirst(TEXT_STATUS), 'filter_paypal_order_status', self::$dropdownUrl.'?get=order_status'));
        
        
        $customer_firstname = PhpExt_Form_TextField::createTextField("filter_paypal_customer_firstname",ucfirst(TEXT_CUSTOMERS_FIRSTNAME)) ->setEmptyText(TEXT_INCLUDES);
        $customer_firstname = self::setWidth($customer_firstname,"150px");
        $customer_lastname = PhpExt_Form_TextField::createTextField("filter_paypal_customer_lastname",ucfirst(TEXT_CUSTOMERS_LASTNAME)) ->setEmptyText(TEXT_INCLUDES);
        $customer_lastname = self::setWidth($customer_lastname,"150px");
        $f[] =  $customer_firstname;
        $f[] =  $customer_lastname;
        
        $f1 = PhpExt_Form_NumberField::createNumberField("filter_paypal_amount_from",ucfirst(TEXT_AMOUNT)) ->setEmptyText(TEXT_FROM);
        $f2 = PhpExt_Form_NumberField::createNumberField("filter_paypal_amount_to","") -> setEmptyText(TEXT_TO);
        $f[] = self::twoCol($f1, $f2);
        
        
        $fieldset1 = new PhpExt_Form_FieldSet();
        $fieldset1 ->setBorder(false)
                    ->setBodyBorder(false)
                    ->setBodyStyle("none")
                    ->setWidth("265px")
                    ->setCheckboxToggle(true)
                    ->setTitle("Advanced Filter")
                    ->setAutoHeight(true)
                   // ->setLabelWidth("60")
                    
                    ->setDefaults(new PhpExt_Config_ConfigObject(array("margin-top"=>"80px")))
                    ->setCollapsed(true);
      
        $a[] = self::setWidth($eF->_multiComboBox2( ucfirst(TEXT_SHOP),  'filter_paypal_order_shop', self::$dropdownUrl.'?get=stores'));
        
        $a[] = self::setWidth($eF->_multiComboBox2(ucfirst(TEXT_SHIPPING_PERMISSION), 'filter_paypal_shipping_way', self::$dropdownUrl.'?get=shipping_methods'));
        
        
        $f1 = PhpExt_Form_DateField::createDateField("filter_paypal_last_modify_from",	ucfirst(TEXT_LAST_MODIFIED)) ->setEmptyText(TEXT_FROM);
        $f1 =  self::setWidth($f1,"52px");
        $f2 = PhpExt_Form_DateField::createDateField("filter_paypal_last_modify_to","") -> setEmptyText(TEXT_TO);
        $f2 =  self::setWidth($f2,"52px");
        $a[] = self::twoCol($f1, $f2);
    
        $email = PhpExt_Form_TextField::createTextField("filter_paypal_email",ucfirst(TEXT_EMAIL), null, PhpExt_Form_FormPanel::VTYPE_EMAIL);
        $email = self::setWidth($email,"150px");
        
        $a[] = $email;
        foreach($a as $field){
          $fieldset1->addItem($field);  
        }
       
        $f[] = $fieldset1;
        
        
      
        
        return $f;
    }
}


?>