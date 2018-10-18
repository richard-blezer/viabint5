<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ldt. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ldt., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ldt., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');


global $xtLink, $page;

$tpl_data = array();
$tpl_data['hasMobile']=_STORE_MOBILE_ACTIVATE;


if($_SESSION['isMobile']){
    $tpl_data['link'] = $xtLink->_link(array('params' => $xtLink->_getParams(array('mobile')).'&page='.$page->page_name.'&page_action='.$page->page_action.'&mobile=false'));
} else {
    $tpl_data['link'] = $xtLink->_link(array('params' => $xtLink->_getParams(array('mobile')).'&page='.$page->page_name.'&page_action='.$page->page_action.'&mobile=true'));
}
?>