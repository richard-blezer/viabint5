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

class shipping_zone extends default_table{


    protected $_table = TABLE_SHIPPING_ZONES;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'zone_id';

    function _getParams() {
        $params = array();

        $header['zone_id'] = array('type' => 'hidden');
        $header['zone_countries'] = array(
        	'type' => 'itemselect',
        	'url' => 'DropdownData.php?get=countries',
        	'valueUrl' => 'adminHandler.php?load_section=shipping_zone&pg=get_saved_shipping_zones&shipping_zone_id=' . $this->url_data['edit_id'],
        );

        $params['header']         = $header;
        $params['master_key']     = $this->_master_key;
        $params['default_sort']   = $this->_master_key;
        $params['languageTab']    = false;

        if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
            $params['include'] = array ('zone_id', 'zone_name');
        }

		global $xtPlugin;
		($plugin_code = $xtPlugin->PluginCode(__CLASS__ .':getParams_buttom')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
			return $plugin_return_value;
        return $params;
    }

	public function get_saved_shipping_zones() {
		global $db;
		$edit_id = $this->url_data['shipping_zone_id'];
		$obj = new stdClass();
		$obj->topics = array();
		$obj->totalCount = 0;
		
		if (!empty($edit_id)) {
			$query = "SELECT zone_countries FROM " . TABLE_SHIPPING_ZONES . " WHERE zone_id=" . (int)$edit_id;
			$record = $db->Execute($query);
			if($record->RecordCount() > 0) {
				$zones_array = explode(',', $record->fields['zone_countries']);
				
				if (!empty($zones_array)) {
					$countries = new countries();
					foreach ($zones_array as $code) {
						if (isset($countries->countries_list[$code])) {
							$obj->topics[] = array('id' => $code, 'name' => $countries->countries_list[$code]['countries_name'], 'desc' => '');
						}
					}
					$obj->totalCount = count($obj->topics);
				}
			}
			return json_encode($obj);
		}
	}
}