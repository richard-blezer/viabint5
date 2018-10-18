<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');

if ($_GET['type']=='elefunds') {
        if (_SYSTEM_SECURITY_KEY!=$_GET['seckey'])
        {
            echo TEXT_WRONG_SYSTEM_SECURITY_KEY; return false;
        
        }
        require_once _SRV_WEBROOT.'plugins/xt_elefunds/classes/class.xt_elefunds.php';
        global $db;
        $rc = $db->Execute("SELECT * FROM " . TABLE_PLUGIN_CONFIGURATION . " WHERE config_key = 'XT_ELEFUNDS_REGISTERED_EMAIL'");
        if ($rc->recordCount()==0){
            $params = 'elefunds_account=registered&seckey='.$_GET['seckey'];
            $iframe_target = $xtLink->_adminlink(array('default_page'=>'cronjob.php','conn'=>'SSL' , 'params'=>$params));
        }else{
            $elefunds = new xt_elefunds;
            $iframe_target =$elefunds->CONFIGURATION_ELEFUNDS_LOGIN_PAGE;
            if ( $iframe_target==''){
				echo '<br />'.TEXT_ELEFUNDS_ACCOUNT_ALREADY_CREATED; die();
			}
        }

        echo '<iframe src="'.$iframe_target.'" frameborder="0" width="100%" height="98%"></iframe>';
        
         
    }

?>