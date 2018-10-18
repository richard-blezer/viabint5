<?php
class ProductFilter extends formFilter {
    

    public static function  formFields(){
    	global $xtPlugin;
    	
        $eF = new ExtFunctions();
        
        $filter_product_name = PhpExt_Form_TextField::createTextField("filter_product_name",ucfirst(TEXT_NAME))->setEmptyText(TEXT_INCLUDES);
        $f[] = self::setWidth($filter_product_name, "150px");
		
		if(isset($_GET['parentNode'])) {
			$catst = explode("catst_",$_GET['parentNode']);
			$store_cat_id = '';
			if ($catst[1]) 
				$store_cat_id = 'catst_'.$catst[1];
		}
        $itemsPerPage = _SYSTEM_ADMIN_PAGE_SIZE;
        $filter_items_per_page = PhpExt_Form_NumberField::createNumberField("filter_items_per_page",ucfirst(TEXT_ITEMS_PER_PAGE), 'filter_items_per_page_' . __CLASS__ .'_product'.$_REQUEST['catID'].$store_cat_id)->setValue($itemsPerPage);
        $f[] = self::setWidth($filter_items_per_page, "150px");
        
        $f1 = PhpExt_Form_NumberField::createNumberField("filter_price_from",ucfirst(TEXT_PRODUCTS_PRICE))->setEmptyText(TEXT_FROM); 
        $f2 = PhpExt_Form_NumberField::createNumberField("filter_price_to","")->setEmptyText(TEXT_TO);   
        $f[] = self::twoCol($f1, $f2);
        
        $f[] = self::setWidth($eF->_comboBox('filter_manufacturer', ucfirst(FEED_MANUFACTURER),self::$dropdownUrl.'?get=manufacturers',"156"),"133px");
		
		$f[]  =self::setWidth($eF->_comboBox('filter_products_status', ucfirst(TEXT_ADMIN_ACTON_STATUS),self::$dropdownUrl.'?get=status_product',"156"),"133px");
		
		$f[]  =self::setWidth($eF->_comboBox('filter_master_slave_products', ucfirst(TEXT_FILTER_MASTER_SLAVE),self::$dropdownUrl.'?get=master_slave_product',"156"),"133px");
       
        $fieldset1 = new PhpExt_Form_FieldSet();
        $fieldset1 ->setBorder(false)
                    ->setBodyBorder(false)
                    ->setWidth("270px")
                    ->setCheckboxToggle(true)
                    ->setTitle(TEXT_ADVANCED_FILTER)
                    ->setAutoHeight(true)
                    ->setDefaults(new PhpExt_Config_ConfigObject(array("margin-top"=>"100px")))
                    ->setCollapsed(true);
        
       
        //$a[]  = PhpExt_Form_Checkbox::createCheckbox("filter_products_status",ucfirst(TEXT_ADMIN_ACTON_STATUS))->setFieldCssClass("checkBox");
        
        $a1 = self::setWidth(PhpExt_Form_NumberField::createNumberField("filter_stock_from",ucfirst(TEXT_PRODUCTS_QUANTITY)))->setEmptyText(TEXT_FROM);
        $a2 = PhpExt_Form_NumberField::createNumberField("filter_stock_to","") -> setEmptyText(TEXT_TO);   
        $a[] = self::twoCol2($a1, $a2);
		
		$a1 = self::setWidth(PhpExt_Form_NumberField::createNumberField("filter_weight_from",ucfirst(TEXT_PRODUCTS_WEIGHT)))->setEmptyText(TEXT_FROM);
        $a2 = PhpExt_Form_NumberField::createNumberField("filter_weight_to","") -> setEmptyText(TEXT_TO);   
        $a[] = self::twoCol2($a1, $a2);

        $a[] = PhpExt_Form_Checkbox::createCheckbox("filter_isDigital","<nobr>".ucfirst(TEXT_PRODUCTS_DIGITAL)."</nobr>");
        
        $a[] = PhpExt_Form_Checkbox::createCheckbox("filter_isFSK18",ucfirst(TEXT_PRODUCTS_FSK18));
      
        $a[]  = PhpExt_Form_Checkbox::createCheckbox("filter_isOnIndexPage",ucfirst(TEXT_ON_INDEXPAGE));
       
        $a[] = self:: setWidth($eF->_comboBox('filter_shop', ucfirst(TEXT_STORE), self::$dropdownUrl.'?get=stores',"156"),"133px");
        $a[] = self:: setWidth($eF->_comboBox('filter_permission', ucfirst(TEXT_PERMISSION), self::$dropdownUrl.'?get=customers_status',"156"),"133px");
       	
        ($plugin_code = $xtPlugin->PluginCode('class.productFilter.php:formFields_bottom')) ? eval($plugin_code) : false;
        
        foreach($a as $field){
          $fieldset1->addItem($field);  
        }
        
        $f[] = $fieldset1;
     
       return $f;
    }
    
  
    

    
    private static function twoCol2($f1, $f2){
        
        $columnPanel = new PhpExt_Panel();
        $columnPanel->setLayout(new PhpExt_Layout_ColumnLayout())->setWidth("268px")->setBorder(false);
        $firstColumn = new PhpExt_Panel();
        $firstColumn->setLayout(new PhpExt_Layout_FormLayout())->setBorder(false);
        $firstColumn->addItem(
	            $f1 ->setWidth("62px")
	          );
        
        $secondColumn = new PhpExt_Panel();
        $secondColumn->setLayout(new PhpExt_Layout_FormLayout())->setBorder(false);
        $secondColumn->addItem(
	            $f2->setHideLabel(true)->setWidth("62px")
               
              
	          );
        $columnPanel->addItem($firstColumn, new PhpExt_Layout_ColumnLayoutData(0.70));
        $columnPanel->addItem($secondColumn, new PhpExt_Layout_ColumnLayoutData(0.30));
        
        return $columnPanel;
    }
    
}
?>