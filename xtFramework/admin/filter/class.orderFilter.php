<?php

class OrderFilter extends formFilter{
    
    
    public static function  formFields(){
        
        $eF = new ExtFunctions();
        
        $itemsPerPage = _SYSTEM_ADMIN_PAGE_SIZE;
        $filter_items_per_page = PhpExt_Form_NumberField::createNumberField("filter_items_per_page",ucfirst(TEXT_ITEMS_PER_PAGE), 'filter_items_per_page_' . __CLASS__ .'_order')->setValue($itemsPerPage);
        $f[] = self::setWidth($filter_items_per_page, "150px");
        
        $f1 = PhpExt_Form_NumberField::createNumberField("filter_id_from","ID") ->setEmptyText(TEXT_FROM);     
        $f2 = PhpExt_Form_NumberField::createNumberField("filter_id_to","") -> setEmptyText(TEXT_TO);   
        $f[] = self::twoCol($f1, $f2);
        
        $f1 = PhpExt_Form_DateField::createDateField("filter_date_from",  ucfirst(TEXT_DATE_PURCHASED)) ->setEmptyText(TEXT_FROM);       
        $f1 =  self::setWidth($f1,"52px");
        $f2 = PhpExt_Form_DateField::createDateField("filter_date_to","") -> setEmptyText(TEXT_TO);   
        $f2 =  self::setWidth($f2,"52px");
        $f[] = self::twoCol($f1, $f2);
        
        $f[] = self::setWidth($eF->_multiComboBox2( ucfirst(TEXT_STATUS), 'filter_order_status', self::$dropdownUrl.'?get=order_status'),"133px");
        
        
        $customer = PhpExt_Form_TextField::createTextField("filter_name",ucfirst(TEXT_CUSTOMER_NAME)) ->setEmptyText(TEXT_INCLUDES); 
        $customer = self::setWidth($customer,"150px");
        $f[] =  $customer;
        
        $f1 = PhpExt_Form_NumberField::createNumberField("filter_amount_from",ucfirst(TEXT_AMOUNT)) ->setEmptyText(TEXT_FROM);     
        $f2 = PhpExt_Form_NumberField::createNumberField("filter_amount_to","") -> setEmptyText(TEXT_TO);   
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
      
        $a[] = self::setWidth($eF->_multiComboBox2( ucfirst(TEXT_SHOP),  'filter_order_shop', self::$dropdownUrl.'?get=stores'),"130px");
         //TEXT_PAYMENT
        $a[] = self::setWidth($eF->_multiComboBox2(ucfirst(TEXT_PAYMENT), 'filter_payment_way',  self::$dropdownUrl.'?get=payment_methods'),"130px");
        
        $a[] = self::setWidth($eF->_multiComboBox2(ucfirst(TEXT_SHIPPING_PERMISSION), 'filter_shipping_way', self::$dropdownUrl.'?get=shipping_methods'),"130px");
        
        
        $f1 = PhpExt_Form_DateField::createDateField("filter_last_modify_from",	ucfirst(TEXT_LAST_MODIFIED)) ->setEmptyText(TEXT_FROM);       
        $f1 =  self::setWidth($f1,"52px");
        $f2 = PhpExt_Form_DateField::createDateField("filter_last_modify_to","") -> setEmptyText(TEXT_TO);   
        $f2 =  self::setWidth($f2,"52px");
        $a[] = self::twoCol($f1, $f2);
    
        $email = PhpExt_Form_TextField::createTextField("filter_email",ucfirst(TEXT_EMAIL), null, PhpExt_Form_FormPanel::VTYPE_EMAIL);
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