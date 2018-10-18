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


global $db,$language,$customers_status,$store_handler;

$payment_logos = array();
$rs = $db->Execute("SELECT * FROM ".TABLE_PAYMENT." AS a INNER JOIN ".TABLE_PAYMENT_DESCRIPTION." AS b ON a.payment_id = b.payment_id WHERE a.status = '1' AND b.language_code= 'de' ORDER BY a.sort_order ASC");
while (!$rs->EOF) {

    $payment_logos[] = array('payment_code' => $rs->fields['payment_code'], 'payment_name' => $rs->fields['payment_name'], 'payment_id' => $rs->fields['payment_id']);
    $rs->MoveNext();
}
$rs->Close();

if(is_array($payment_logos)){
    //Permission
    $rs_permission = $db->Execute("SELECT * FROM ".TABLE_CONTENT_PERMISSION." WHERE type='payment' ORDER BY pid ASC");
    $payment_permission = array();
    while (!$rs_permission->EOF) {

        $payment_permission[$rs_permission->fields['pid']][] = array('pid' => $rs_permission->fields['pid'], 'pgroup' => $rs_permission->fields['pgroup']);
        $rs_permission->MoveNext();
    }
    $rs_permission->Close();

    foreach($payment_logos as $k => $v){
        if(array_key_exists($v['payment_id'],$payment_permission)){
            $perm = $payment_permission[$v['payment_id']];
			
			
            foreach($perm as $pk =>$pv){
                $p_str = $pv['pgroup'];
				
                if(stripos($p_str, 'shop_') !== false){
                    $perm_id = str_replace('shop_','',$p_str);
                    if($perm_id == $store_handler->shop_id) 
					{
						if(_SYSTEM_GROUP_PERMISSIONS=='blacklist'){
							unset($payment_logos[$k]);
						}elseif(_SYSTEM_GROUP_PERMISSIONS=='whitelist'){
							$payment_logos_new[$k] = $payment_logos[$k];
						}
						
					}
                        
                }

                if(stripos($p_str, 'group_permission_') !== false){
                    $perm_id = str_replace('group_permission_','',$p_str);
					
					
                    if($perm_id == $customers_status->customers_status_id)
					{
						if(_SYSTEM_GROUP_PERMISSIONS=='blacklist'){
							unset($payment_logos[$k]);
						}elseif(_SYSTEM_GROUP_PERMISSIONS=='whitelist'){
							$payment_logos_new_customers[$k] = $payment_logos[$k];
						}
						
					}
                        
                }
            }
        }
    }
	
	if(_SYSTEM_GROUP_PERMISSIONS=='whitelist')
	{
		unset($payment_logos);
		$payment_logos = array();
		$ttt = array_intersect($payment_logos_new, $payment_logos_new_customers);
		
		foreach($payment_logos_new as $pln)
		{
			foreach($payment_logos_new_customers as $pln2)
			{
				if ($pln['payment_code']==$pln2['payment_code']) 
				{
					array_push($payment_logos,$pln);
				}
				
				
			}
			
		}
	}
}

$tpl_data = array('_payment_logos'=>$payment_logos);

$show_box = true;
?>