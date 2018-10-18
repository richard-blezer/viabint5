<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');


// west menu einstellungen -> konfiguration -> Versender
if ($pid == 'configuration') {
    $a = 0;

    $arr[] = array(
        'text' => TEXT_TRACKING_SHIPPER,
        'url_d' => 'adminHandler.php?plugin=xt_ship_and_track&load_section=xt_shipper&pg>=overview' ,
        'tabtext' => TEXT_TRACKING_SHIPPER ,
        'pid' => 'node_shipper' ,
        'id' => 'node_shipper' ,
        'type' => 'I',
        'leaf' => true,
        'icon' => "../"._SRV_WEB_ADMIN."images/icons/lorry.png"
    );
}

?>