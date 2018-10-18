<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if ($request['get']=='imexport_types') {
    if(!isset($result)) $result = array();
    $result[] =  array('id' => 'export',
        'name' => 'Export',
        'desc' => '');
    $result[] =  array('id' => 'import',
        'name' => 'Import',
        'desc' => '');
}
if ($request['get']=='imexport_typesspec') {
    if(!isset($result)) $result = array();
    $result[] =  array('id' => 'products',
        'name' => 'Products',
        'desc' => '');
    /*$result[] =  array('id' => 'categories',
                 'name' => 'Categories',
                 'desc' => '');
    $result[] =  array('id' => 'prod2cat',
                 'name' => 'Products 2 Categories',
                 'desc' => '');
    */
}
if ($request['get']=='imexport_matching') {
    if(!isset($result)) $result = array();


    $result[] =  array('id' => 'products_id',
        'name' => 'Products - ID',
        'desc' => '');
    $result[] =  array('id' => 'products_model',
        'name' => 'Products - Model',
        'desc' => '');
    $result[] =  array('id' => 'products_ean',
        'name' => 'Products - EAN',
        'desc' => '');
    $result[] =  array('id' => 'external_id',
        'name' => 'Products - External ID',
        'desc' => '');
}
if ($request['get']=='imexport_matching_2') {
    if(!isset($result)) $result = array();



    $result[] =  array('id' => '',
        'name' => 'None',
        'desc' => '');
    $result[] =  array('id' => 'manufacturers_id',
        'name' => 'Manufacturers - ID',
        'desc' => '');
}
if ($request['get']=='imexport_delimiter') {
    if(!isset($result)) $result = array();
    $result[] = array ('id' => ';', 'name' => ';', 'desc' => ';');
    $result[] = array ('id' => ',', 'name' => ',', 'desc' => ',');
}
if ($request['get']=='imexport_enclosure') {
    if(!isset($result)) $result = array();
    $result[] = array ('id' => '"', 'name' => '"', 'desc' => '"');
    $result[] = array ('id' => "'", 'name' => "'", 'desc' => "'");
}