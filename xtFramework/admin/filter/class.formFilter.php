<?php
class FormFilter{
    
    public static $dropdownUrl = "../xtAdmin/DropdownData.php";
    public static $iniValue = array();
        
     
    protected static function twoCol($f1, $f2){
        
        $columnPanel = new PhpExt_Panel();
        $columnPanel->setLayout(new PhpExt_Layout_ColumnLayout())->setWidth("268px")->setBorder(false);
        $firstColumn = new PhpExt_Panel();
        $firstColumn->setLayout(new PhpExt_Layout_FormLayout())->setBorder(false);
        $firstColumn->addItem(
	            $f1 ->setWidth("65px")
	          );
        
        $secondColumn = new PhpExt_Panel();
        $secondColumn->setLayout(new PhpExt_Layout_FormLayout())->setBorder(false);
        $secondColumn->addItem(
	            $f2->setHideLabel(true)->setWidth("68px")
               
              
	          );
        $columnPanel->addItem($firstColumn, new PhpExt_Layout_ColumnLayoutData(0.70));
        $columnPanel->addItem($secondColumn, new PhpExt_Layout_ColumnLayoutData(0.30));
        
        return $columnPanel;
    }
    
    protected static function setWidth($field, $width="auto"){
        
       $field->setCssStyle(new PhpExt_Config_ConfigObject(array("width"=>$width))); 
       return $field;
    }
    
    public static function setTxt($field){
    
        global $db;
         
        $ses_value = $_SESSION[$field];
        
        if(trim($ses_value) == "") return false;
        
        if(!sizeof(self::$iniValue)){
          $sql = "select language_value from ".TABLE_LANGUAGE_CONTENT." where  language_key in ('TEXT_INCLUDES','TEXT_TO', 'TEXT_FROM')";
          $rc = $db->Execute($sql);
    	  while(!$rc->EOF){
    			self::$iniValue[] = $rc->fields['language_value'];
                        $rc->MoveNext();
    		
          } $rc->Close();
        }
       
        if(in_array($ses_value,self::$iniValue )) return false;
   
        else return true;
        
    }
    
    public static function date_trans($date){

        $d = explode("/",$date);
        return  "20".$d[2]."-".$d[0]."-".$d[1];
    
    }

	public static function date_trans_int($date){

        $d = explode("/",$date);
        return  "20".$d[2].$d[0].$d[1];
    
    }
   
}


?>