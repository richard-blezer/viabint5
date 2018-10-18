<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

// trusted shops certifacates in west-navi unter Einstellungen->Konfiguration
if (strstr($pid,'configuration')) {
    $arr[] = array(
        'text' => TEXT_XT_TRUSTED_SHOPS_CERTIFICATES,
        'url_d' => 'adminHandler.php?plugin=&plugin=xt_trusted_shops&load_section=xt_trusted_shops_certificates',
        'tabtext' => TEXT_XT_TRUSTED_SHOPS_CERTIFICATES,
        'pid' => 'tab_xt_trusted_shops_certificates',
        'id' => 'tab_xt_trusted_shops_certificates' ,
        'type' => 'I',
        'leaf' => true,
        'icon' => 'images/icons/folder_key.png'
    );
}