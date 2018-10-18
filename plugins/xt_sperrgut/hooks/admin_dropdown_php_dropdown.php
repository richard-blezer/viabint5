<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');
if ($request['get']=='xt_sperrgut_class') {
    $result = array();
    $result[] =  array('id' => 0,
        'name' => TEXT_EMPTY_SELECTION,
        'desc' => '');
    // query
    $rs = $db->Execute("SELECT * FROM ".TABLE_XT_SPERRGUT);
    while (!$rs->EOF) {
        $result[] =  array('id' => $rs->fields['id'],
            'name' => $rs->fields['description'],
            'desc' => '');
        $rs->MoveNext();
    }
}
if ($request['get']=='xt_sperrgut_calculate_module') {
    $result = array();
    $result[] =  array('id' => 'total',
        'name' => 'total',
        'desc' => '');
    $result[] =  array('id' => 'single',
        'name' => 'single',
        'desc' => '');
    $result[] =  array('id' => 'onemax',
        'name' => 'onemax',
        'desc' => '');
    $result[] =  array('id' => 'onemin',
        'name' => 'onemin',
        'desc' => '');
    // query
}

?>