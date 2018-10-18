<?php

class CustomerFilter extends formFilter{
    
    
    public static function  formFields(){
        
        $eF = new ExtFunctions();
        $f[] = PhpExt_Form_TextField::createTextField("filter_name",ucfirst(TEXT_NAME))
                ->setEmptyText(TEXT_INCLUDES)
                ->setCssStyle(new PhpExt_Config_ConfigObject(array("width"=>"150px"))); 
       
        $f[] = self::setWidth($eF->_multiComboBox2(ucfirst(TEXT_STATUS), "filter_status", self::$dropdownUrl.'?get=customers_status&skip_empty=true')
               ->setCssStyle(new PhpExt_Config_ConfigObject(array("width"=>"auto"))),"133px");
        
        $f[] = PhpExt_Form_TextField::createTextField("filter_company",ucfirst(TEXT_STORE_ACCOUNT_COMPANY))
               ->setEmptyText(TEXT_INCLUDES)
               ->setCssStyle(new PhpExt_Config_ConfigObject(array("width"=>"150px"))); ; 
        
        //advanced
        $fieldset1 = new PhpExt_Form_FieldSet();
        $fieldset1 ->setBorder(false)
                    ->setBodyBorder(false)
                    ->setWidth("265px")
                    ->setCheckboxToggle(true)
                    ->setTitle("Advanced Filter")
                    ->setAutoHeight(true)
                    ->setDefaults(new PhpExt_Config_ConfigObject(array("margin-top"=>"100px")))
                    ->setCollapsed(true);
        
		$a[] = self::setWidth($eF->_multiComboBox2( ucfirst(TEXT_SHOP),  'filter_customers_shop', self::$dropdownUrl.'?get=stores&skip_empty=true'),"133px");

		$a[] = self::setWidth($eF->_multiComboBox2( ucfirst(TABTEXT_CUSTOMERS_STATUS),  'filter_customers_status', self::$dropdownUrl.'?get=customers_status&skip_empty=true'),"133px");

        $a[] = PhpExt_Form_TextField::createTextField("filter_email",ucfirst(TEXT_EMAIL), null, PhpExt_Form_FormPanel::VTYPE_EMAIL)
                 ->setCssStyle(new PhpExt_Config_ConfigObject(array("width"=>"150px"))); 
        
        $a[] = self::setWidth($eF->_comboBox('filter_language', ucfirst(TEXT_LANGUAGE), self::$dropdownUrl.'?get=language_codes',"154")
                ->setCssStyle(new PhpExt_Config_ConfigObject(array("width"=>"auto"))),"133px");
        
        $a[] = self::setWidth($eF->_comboBox('filter_gender', ucfirst(TEXT_CUSTOMERS_GENDER), self::$dropdownUrl.'?get=gender1',"154")
                ->setCssStyle(new PhpExt_Config_ConfigObject(array("width"=>"auto"))),"133px");
				
        global $xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.customerFilter.php:formFields_bottom')) ? eval($plugin_code) : false;
		
        foreach($a as $field){
          $fieldset1->addItem($field);  
        }
        
        $f[] = $fieldset1;
        
        return $f;
    }
}


?>