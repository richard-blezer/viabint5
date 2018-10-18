<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');


		
if (isset($xtPlugin->active_modules['tfm_amazon_payments']) && TFM_AMAZON_ENABLED == 'true')
{
    $pd = $payment_data;
    
  
    
    if(amazonLoged && $_COOKIE['account_mode_checkout']=='amazon')
    {
        foreach($payment_data as $pdk=>$pdv)
        {
                    if( $pdv["payment_code"] != "tfm_amazon_payments" )
                    {
                        unset($payment_data[$pdk]);
                    }
        }
        
        if (XT_PAYPAL_PLUS_ENABLED && USER_POSITION=='store' && isset($xtPlugin->active_modules['xt_paypal_plus']))
        {
           unset($xtPlugin->active_modules['xt_paypal_plus']);
        }
        
        
			if($payment_data['tfm_amazon_payments']['payment_tpl'] == "")
				$payment_data['tfm_amazon_payments']['payment_tpl'] = "amazon_payment_row.html";
	
		
    }else
    {
        foreach($payment_data as $pdk=>$pdv)
        {
                    if( $pdv["payment_code"] == "tfm_amazon_payments" )
                    {
                       unset($payment_data[$pdk]);
                    }
        }
    }
   
    
    
}

    
