<?php
class xt_reviewsFilter extends formFilter {
    
    
    public static function  formFields(){
        
   
        $eF = new ExtFunctions();
        
        $f1 = PhpExt_Form_DateField::createDateField("filter_date_from", ucfirst(TEXT_DATE)) ->setEmptyText(TEXT_FROM);
        $f1 =  self::setWidth($f1,"52px");
        $f2= PhpExt_Form_DateField::createDateField("filter_date_to", "")->setEmptyText(TEXT_TO); 
        $f2 =  self::setWidth($f2,"52px");
        $f[] = self::twoCol($f1, $f2);
        
        $f[] = self::setWidth(PhpExt_Form_TextField::createTextField("filter_product",ucfirst(TEXT_PRODUCT)),"150px");   
        
        $f1 = PhpExt_Form_Checkbox::createCheckbox("filter_active",ucfirst(TEXT_ACTIVE));
        $f1 ->setCssClass("checkBox");
        $f[] = $f1;
        $f[] = self::setWidth($eF->_multiComboBox2(ucfirst(TEXT_LANGUAGE),"filter_language", self::$dropdownUrl.'?get=language_codes'),"135px");   
        
        $f[] = self::setWidth($eF->_multiComboBox2(ucfirst(TEXT_REVIEW_RATING), "filter_rating", self::$dropdownUrl.'?get=rating'),"135px");  
        
        $f[] = self::setWidth(PhpExt_Form_TextField::createTextField("filter_title",ucfirst(TEXT_TITLE))->setEmptyText(TEXT_INCLUDES) ,"150px");    
        return $f;
    }
}
?>