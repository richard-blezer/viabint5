<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');

    if (XT_MASTER_SLAVE_ACTIVE==true){
        $xtMinify->add_resource(_SRV_WEB_PLUGINS . 'xt_master_slave/css/master_slave.css',144);
    }
?>
