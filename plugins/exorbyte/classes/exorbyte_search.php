<?php
/*
--------------------------------------------------------------
   exorbyte_search.php 2011-09-10
   exorbyte GmbH
   Line-Eid-Str. 1
   78467 Konstanz
   http://commerce.exorbyte.de
   Copyright (c) 2011 exorbyte GmbH, 
   author: Daniel Gebuehr
   --------------------------------------------------------------
   class registration for the integration of exorbyte's search
   --------------------------------------------------------------
   xt:commerce plugin
   http://www.xt-commerce.de
   --------------------------------------------------------------
*/

class ecos_registration {
     function get_exo_project_id() {
        global $db;
     /**
     * checks the database for the exorbyte project id
     * no input
     * @return project_id or -1 if not active          
     */
        $resultset_config=$db->Execute("SELECT * FROM " . TABLE_EXORBYTE );
        if($resultset_config->fields["project_id"] > 0) {
           return $resultset_config->fields["project_id"];
        }
        return -1;   
     }
     function show_script($rView) {
         $rView->p_id=$this->aData["project_id"];
         $rView->mm_project_name=$this->aData["project_name"];
         return $rView->get_view("script");
     } 
}         
?>
