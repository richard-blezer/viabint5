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

include_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/class.xt_master_slave_functions.php';

if (XT_MASTER_SLAVE_ACTIVE == 'true')
{
    
	if (($this->data['products_master_flag'] == '1') || ($this->data['products_master_flag_new'] == '1')){
		global $current_product_id;
	    $this->data['allow_add_cart'] = 'false'; // disables the add_to_cart button
		
		if ($_REQUEST['action']!='select_ms') {
			xt_master_slave_functions::unsetFilter();
		} elseif (is_array($_POST['id'])) {
			xt_master_slave_functions::setFilter($_POST['id'],$this->data['products_id']);
		}
		if (($current_product_id>0) && (_PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE!='true') && !isset($_SESSION['select_ms'][$this->data['products_id']])) // make sure we are in product_info
		{
	   		$slaves_from_master = xt_master_slave_functions::get_slave_from_master($this->data['products_model']);
			if ($slaves_from_master->_numOfRows==1)
			{
				$slave_attr = xt_master_slave_functions::returnSingleSlaveAttributes($slaves_from_master->fields['products_id']);
				foreach($slave_attr as $sl_a)
				{
					xt_master_slave_functions::setFilter($sl_a,$this->data['products_id']);
				}
			}
		}	 
		
		if ($_SESSION['select_ms'])
		{
			//$this->data['products_price'] = xt_master_slave_functions::get_master_price('sp', $this->data['products_model'], $this->data['products_price'],$this->data['products_id']);
			$master_data = $this->data;
			
			$res = xt_master_slave_functions::slave_products_id($this->data['products_model'], $this->data['products_id']);

			if ($res){
				$this->data = $res->data;
			}
           
			if (XT_MASTER_SLAVE_LOAD_MASTER_IMAGE_IN_SLAVE=='false')
				$this->data['products_image'] = xt_master_slave_functions::slave_image($master_data['products_model'], $master_data['products_image'],$master_data['products_id'],$this->data['products_image_from_master']); 
			else {
				$this->data['products_image'] =  $master_data['products_image'];
			}
            
		}
		else 
		{	
			$this->data['products_price'] = xt_master_slave_functions::get_master_price($this->data['products_option_master_price'], $this->data['products_model'], $this->data['products_price']);
		}

		$this->data['slaves_attributes'] =  xt_master_slave_functions::returnSlavesAttributes($this->data['products_model']);
	}
	else
	{
		$this->data['products_master_flag_new'] = 0;
		if ((_PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE=='false') and ($this->data['products_master_model']!='')) $this->data['products_master_flag_new'] = 1;
		 
		if (($this->data['products_master_model']!=''))
        {
            $m_data = xt_master_slave_functions::getMasterData($this->data['products_master_model']);
            if (XT_MASTER_SLAVE_LOAD_MASTER_IMAGE_IN_SLAVE=='false'){
                    
                 $this->data['products_image'] = xt_master_slave_functions::slave_image( $m_data['products_model'],  $m_data['products_image'], $m_data['products_id'],$this->data['products_image_from_master'],$this->data['products_image']);
            }                
            else {
                $this->data['products_image'] =  $m_data['products_image'];
            } 
            
        }
		
	}
}


?>