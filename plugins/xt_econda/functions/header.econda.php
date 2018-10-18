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
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
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


echo '<script type="text/javascript">'."\n";
echo '<!--'."\n";
echo 'var emos_kdnr=\''.XT_ECONDA_USER_ID.'\';'."\n";
echo 'var emosPageId =\''.createPageId().'\';'."\n";
echo '//-->'."\n";
echo '</script>'."\n";
echo '<a name="emos_sid" rel="'.session_id().'" rev=""></a>'."\n";
echo '<a name="emos_name" title="siteid" rel="'.$store_handler->shop_id.'" rev=""></a>'."\n";  


function createPageId() {
    
    $qry = explode('&',$_SERVER['QUERY_STRING']);
    $url = $_SERVER['REQUEST_URI'];
    if (isset($qry['page'])) $url.=$qry['page'];
    if (isset($qry['page_action'])) $url.=$qry['page_action'];
    if (isset($qry['info'])) $url.=$qry['info'];
    if (isset($qry['cat'])) $url.=$qry['cat'];
    if (isset($qry['coID'])) $url.=$qry['coID'];
    if (isset($qry['mfn'])) $url.=$qry['mfn'];
    return md5($url);
    
}
?>