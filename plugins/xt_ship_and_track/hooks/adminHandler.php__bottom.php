<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS."xt_ship_and_track/classes/hermes_ExtAdminHandler.php";

// eigenen ExtAdminHandler verwenden //
if ($_REQUEST['plugin'] == 'xt_ship_and_track'
    && $_REQUEST['load_section'] != 'xt_shipper'
    /**&& $_REQUEST['gridHandle']=='xt_hermesgridForm'*/)
{
    $form_grid = new hermes_ExtAdminHandler($form_grid);
}

?>