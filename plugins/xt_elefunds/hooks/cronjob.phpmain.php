<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');
	if (_SYSTEM_SECURITY_KEY!=$_GET['seckey']){
		echo TEXT_WRONG_SYSTEM_SECURITY_KEY; return false;
	}
    if ($_GET["elefunds_account"]=="registered"){
        global $language,$store_handler;
        include_once _SRV_WEBROOT.'plugins/xt_elefunds/classes/class.xt_elefunds.php';
        $template = new Template();
        $receivers = xt_elefunds::getReceivers();
        
        $lang = $language->code;
        $all_receivers = $receivers->receivers->$lang;
        $error = false;
        $_error = array();
        $tpl_data = array('email'=>_CORE_DEBUG_MAIL_ADDRESS);
        if ($receivers==''){
             $_error[] = array('text' => _ERROR_ELEFUNDS_RECIEVERS);
            $tpl_data = array_merge($tpl_data,array('error'=>$_error));
        }
        if (isset($_POST["send"])){
          
            if ((!$_POST["email"]) || ($_POST["email"]=='')){
                $error = true;
                $_error[] = array('text' => _ERROR_ELEFUNDS_EMAIL);
            }
            
            $selected_receivers = array();
            for($i=0;$i<count($all_receivers);$i++){
                if (isset($_POST["receivers".$i])){
                    array_push($selected_receivers,(int)$_POST["receivers".$i]);
                    $all_receivers[$i]->selected= true;
                }
            }
            
            
            if (count($selected_receivers)==0){
                $error = true;
                $_error[] = array('text' => _ERROR_ELEFUNDS_NO_RECIEVERS);
            }
        
            if ($error) {
                $tpl_data = array_merge($tpl_data,array('error'=>$_error));
            } else {
                $store= $store_handler->getOldestShop();
                $res = xt_elefunds::RegisterElefundsAccount($store["shop_id"],$selected_receivers,$lang,$_POST["email"]);
                $res = json_decode($res);
                
               if ($res->success=="true"){
                  
                    $tpl_data = array_merge($tpl_data,array('success'=> TEXT_ELEFUNDS_ACCOUNT_REGED));
                    $rc = $db->Execute("SELECT * FROM " . TABLE_PLUGIN_CONFIGURATION . " WHERE config_key = 'XT_ELEFUNDS_REGISTERED_EMAIL'");
                    if ($rc->recordCount()>0){
                        $db->Execute("UPDATE " . TABLE_PLUGIN_CONFIGURATION . " SET config_value = ? WHERE config_key = 'XT_ELEFUNDS_REGISTERED_EMAIL'", array($_POST["email"]));
                    }else{
                        $db->Execute("INSERT INTO " . TABLE_PLUGIN_CONFIGURATION . " (config_key,config_value,plugin_id, type,shop_id) VALUES
                                        ('XT_ELEFUNDS_REGISTERED_EMAIL',?,(SELECT plugin_id FROM " . TABLE_PLUGIN_PRODUCTS . " WHERE code = 'xt_elefunds'),'text','1')", array(
                            $_POST["email"]
                        ));
                    }
    
               }else{
                   $_error[] = array('text' => TEXT_ELEFUNDS_ACCOUNT_ERROR);
                   $tpl_data = array_merge($tpl_data,array('error'=>$_error));
               }
            }
         }
        $tpl_data = array_merge($tpl_data,array('receivers'=>$all_receivers));
        $tpl_data = array_merge($tpl_data,array('elefunds_logo'=>_SYSTEM_BASE_URL . _SRV_WEB.'plugins/xt_elefunds/images/elefunds_reg_page.png'));
        $tpl_data = array_merge($tpl_data,array('check'=>_SYSTEM_BASE_URL . _SRV_WEB.'plugins/xt_elefunds/images/check3.png'));
       
        
        $tpl = 'elefunds.html';
        $template = new Template();
        $template->getTemplatePath($tpl, 'xt_elefunds', '', 'plugin');

        $tmp_data = $template->getTemplate('xt_elefunds_smarty', $tpl, $tpl_data);
        include _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_elefunds/css/ejsadmin.phpcss_styles.php';
       
        echo $tmp_data;
    }