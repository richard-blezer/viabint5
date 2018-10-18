<?php
/*
*--------------------------------------------------------------
   exorbyte_views.php 2011-07-04
   exorbyte GmbH
   Line-Eid-Str. 1
   78467 Konstanz
   http://commerce.exorbyte.de
   Copyright (c) 2011 exorbyte GmbH, 
   author: Daniel Gebuehr
   --------------------------------------------------------------
   wrapping class for the views used in the exorbyte plugin module
   loads the view via buffering in the object, the view has access to object parameters
   returns html
  */
class ecos_view {
   protected $Ausgabe;

   function get_view($Datei) {
     /**
     * parsing view
     * @var $Datei: string file without extension
     * @return html          
     */
      ob_start();  //startet Buffer
      include(_SRV_WEB_PLUGINS."/exorbyte/views/".$Datei.".php");  
      $Ausgabe=ob_get_contents();  //Buffer wird geschrieben
      ob_end_clean();  //Buffer wird gelöscht
      return $Ausgabe;
   }
   function write_array_to_string($rs) {
      if(is_array($rs)) {
         foreach($rs as $key=>$value) {
            if(is_array($value)) {
               foreach($value as $key2=>$value2) {
                  $myString.="[".$key."]"."[".$key2."]=".$value2."\n";
               }
            }else  {      
               $myString.="[".$key."]=".$value."\n";
            }   
         }
         return $myString;
      }
      elseif($rs=="") {
         return -1;   
      }
      else {
         return $rs;   
      }
   }   
}   
?>
