<?php

class redirect_404Filter extends formFilter{
    
    public static function  formFields(){
        $eF = new ExtFunctions();
        $f[] = PhpExt_Form_TextField::createTextField("filter_keyword",ucfirst(TEXT_KEYWORD))
                ->setCssStyle(new PhpExt_Config_ConfigObject(array("width"=>"150px"))); 
 
		$f[] = self::setWidth($eF->_comboBox('filter_store', ucfirst(TEXT_STORE),self::$dropdownUrl.'?get=stores',"156"),"133px");
        return $f;
    }
}

?>