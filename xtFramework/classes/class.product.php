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

class product {

	public $pID;
	public $qty;
	public $data = array();
	public $is_product;

	public $master_id = 'products_id';
	public $store_field_exists = false;

	public $_master_key = 'products_id';
	public $_image_key = 'products_image';
	public $_table = TABLE_PRODUCTS;
	public $_display_key = 'products_name';
	public $_table_lang = TABLE_PRODUCTS_DESCRIPTION;
	public $_store_field = 'products_store_id';
	
	protected $_table_seo = TABLE_SEO_URL;
	protected $_table_cat = TABLE_PRODUCTS_TO_CATEGORIES;

	function product($pID=0,$size='default', $qty=1, $force_lang='', $pos='product_info') {

		$this->sql_products = new getProductSQL_query();
		if($pos)
			$this->sql_products->setPosition($pos);

		$this->getPermission();

		if($pID != 0){
			$this->pID = (int)$pID;
			$this->qty = (int)$qty;
			$is_product = $this->getProductData($size,$force_lang);
			if (!$is_product) {
				$this->is_product=false;
			} else {
				$this->is_product=true;
			}

			if(USER_POSITION!='admin')
                $this->buildData($size,$force_lang);	                       
		} else {			
			return false;
		}              
	}

	function getPermission(){
		global $store_handler, $customers_status, $xtPlugin;

		$this->perm_array = array(
			'shop_perm' => array(
				'type'=>'shop',
				'table'=>TABLE_PRODUCTS_PERMISSION,
				'key'=>$this->_master_key,
				'simple_permissions' => 'true',
				'simple_permissions_key' => 'permission_id',
				'pref'=>'p'
			),
			'group_perm' => array(
				'type'=>'group_permission',
				'table'=>TABLE_PRODUCTS_PERMISSION,
				'key'=>$this->_master_key,
				'simple_permissions' => 'true',
				'simple_permissions_key' => 'permission_id',
				'pref'=>'p'
			)
		);

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':getPermission')) ? eval($plugin_code) : false;

		$this->permission = new item_permission($this->perm_array);

		return $this->perm_array;
	}

	function getProductData ($size,$force_lang) {
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.product.php:getProductData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
			return $plugin_return_value;

		switch ($size) {
			case "default":
			default:
				$sql_tablecols = 'p.*,pd.*,su.*';
				($plugin_code = $xtPlugin->PluginCode('class.product.php:getProductData_full_cols')) ? eval($plugin_code) : false;
				$sql_tablecols .= $plugin_code;
				$this->sql_products->setFilter('Language',$force_lang);
				$this->sql_products->setFilter('Seo',$force_lang);
				break;

			case "full":
				$sql_tablecols = 'p.*,pd.*,su.*';
				($plugin_code = $xtPlugin->PluginCode('class.product.php:getProductData_full_cols')) ? eval($plugin_code) : false;
				$sql_tablecols .= $plugin_code;
				$this->sql_products->setFilter('Language',$force_lang);
				$this->sql_products->setFilter('Seo',$force_lang);
				break;
                
            case "export":
                $sql_tablecols = 'p.*,pd.*,su.*';
                ($plugin_code = $xtPlugin->PluginCode('class.product.php:getProductData_full_cols')) ? eval($plugin_code) : false;
                $sql_tablecols .= $plugin_code;
                $this->sql_products->setFilter('Language',$force_lang);
                $this->sql_products->setFilter('Seo',$force_lang);
                $this->sql_products->setUserPosition('admin');
                break;

				($plugin_code = $xtPlugin->PluginCode('class.product.php:getProductData_new_size')) ? eval($plugin_code) : false;
		}


		$this->sql_products->setSQL_COLS(", " . $sql_tablecols);
        $this->sql_products->setSQL_WHERE("and p.products_id = '" . $this->pID . "'");

		($plugin_code = $xtPlugin->PluginCode('class.product.php:getProductData_SQL')) ? eval($plugin_code) : false;

		$query = "".$this->sql_products->getSQL_query()."";
        
		$record = $db->Execute($query);
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				$data = $record->fields;
				$record->MoveNext();
			}$record->Close();
			($plugin_code = $xtPlugin->PluginCode('class.product.php:getProductData_bottom')) ? eval($plugin_code) : false;
			//return $data;
			$this->data = $data;
			return true;
		}else{
			return false;
		}
	}

    function buildData($size='default',$force_lang='') {
        global $xtPlugin, $xtLink, $price,  $template,$_content,$customers_status,$system_shipping_link,$system_status;
        
		($plugin_code = $xtPlugin->PluginCode('class.product.php:buildData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
	
		
		$link_array = array('page'=> 'product', 'type'=>'product', 'name'=>$this->data['products_name'], 'id'=>$this->data['products_id'],'seo_url'=>$this->data['url_text']);

		$this->data['products_link'] = $xtLink->_link($link_array);

		if($customers_status->customers_status_show_price==1){
			$this->data['products_price'] = $this->_getPrice(array('format'=>true, 'curr'=>true, 'qty'=>$this->qty));
		}else{
			unset($this->data['products_price']);
		}

		$this->data['products_tax_info'] = $this->_getTax($this->data['products_tax_class_id']);

        if($this->data['products_unit']){
		    $a = $system_status->getSingleValue($this->data['products_unit']);

		$this->data['products_unit_name'] = $a["status_name"];
        }
		$shipping_link = $system_shipping_link->shipping_link;

		if ($shipping_link!='') {
			$this->data['products_shipping_link'] = $shipping_link;
		}

		if ($this->data['products_image']=='') $this->data['products_image'] = _STORE_PRODUCT_NO_PICTURE;
		$this->data['products_image']= __CLASS__.':'.$this->data['products_image'];

		// base price
		$this->getBasePrice();

		if (_SYSTEM_STOCK_RULES=='true') {
			$stock_image = $this->getStockTrafficRule($force_lang);
			if ($stock_image != false) $this->data['stock_image']=$stock_image;
		}

		if (_SYSTEM_SHIPPING_STATUS=='true' && $this->data['products_shippingtime']!=0){
			$this->data['shipping_status'] = $this->getShippingStatus('name');
			$this->data['shipping_status_image'] = $this->getShippingStatus('image');
			// new functions for full shipping data support
			$this->data['shipping_status_data'] = $this->getShippingStatusData();
		}

		// add to cart allowed ?
		$this->data['allow_add_cart'] = 'true';
		if ($customers_status->customers_status_show_price!='1') {
			$this->data['allow_add_cart'] = 'false';
		}
		// fsk18 article and fsk18 restriced ?
		if ($customers_status->customers_fsk18 == '0' && $this->data['products_fsk18'] == '1') {
			$this->data['allow_add_cart'] = 'false';
		}
        
        // check if available on is in past #0000650
        $currDate = date('Y-m-d');
		if ($currDate>$this->data['date_available']) $this->data['date_available']='';
        
		if($size=='full'){
			global $mediaImages, $mediaFiles;
			$media_data = $mediaFiles->get_media_data($this->data['products_id'], __CLASS__, 'product', 'info='.$this->data['products_id']);
			$media_images = $mediaImages->get_media_images($this->data['products_id'], __CLASS__);

			$this->data['more_images'] = $media_images['images'];			
			$this->data['media_files'] = $this->_getPermittedMediaData($media_data['files']);
		}
		
		($plugin_code = $xtPlugin->PluginCode('class.product.php:BuildData_bottom')) ? eval($plugin_code) : false;
	}

	function _getPrice($values)
	{
		global $xtPlugin, $xtLink, $price, $tax, $customers_status, $order_edit_controller;

		$format_type = 'default';

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getPrice_top')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value)) return $plugin_return_value;

		$products_tax = $tax->data[$this->data['products_tax_class_id']];

		if ($customers_status->customers_status_show_price_tax == '0')
		$products_tax = '0';

		$this->data['products_tax_rate'] = $products_tax;
		$this->curr = $values['curr'];

		// Products Price
		$products_price = $this->data['products_price'];
		$format_type = 'default';

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getPrice_center')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value)) return $plugin_return_value;

		// special price ?
		// TODO find better solution
		list($special_price_otax, $date_available, $date_expired) = $this->data['flag_has_specials'] ? $this->getSpecialPrice():array(false, '', '');
        // flag_has_specials is written by xt_special_products and is true even if there is no active special price, see product_sp_price::_updateProductsSpecialflag
        // we update products flag_has_specials depending on the result of $this->getSpecialPrice, see above
        $this->data['flag_has_specials'] = $special_price_otax ? true : false;
		if ($special_price_otax!=false) {

			// use group price as base price ?
			if ($this->data['price_flag_graduated_'.$price->p_group]=='1') {
				$grp_price = $this->getGroupPrice('group',1);
				if ($grp_price!=false) {
					$products_price = $grp_price['price'];
				}
			}

			if ($special_price_otax < $products_price) {

				$format_type = 'special';

				if($values['curr']=='true')
				$special_price_otax = $price->_calcCurrency($special_price_otax);

				// Add Tax
				$special_price = $price->_AddTax($special_price_otax, $products_tax);

			}

		} else {
			// no special price, query for graduated
			if ($this->data['price_flag_graduated_'.$price->p_group]=='1') {
				$grp_price = $this->getGroupPrice();
				if ($grp_price!=false) {
					$products_price = $grp_price['price'];
					if ($grp_price['no_graduated']==0) {
						$this->data['group_price'] = $grp_price;
						$cheapest_price_otax = $grp_price['cheapest'];
						$format_type = 'graduated';

						if($values['curr']=='true')
						$cheapest_price_otax = $price->_calcCurrency($cheapest_price_otax);

						// Add Tax
						$cheapest_price = $price->_AddTax($cheapest_price_otax, $products_tax);

					}
				}
			}

			// get ALL staffel
			if ($format_type!='graduated' && $this->data['price_flag_graduated_all']=='1') {
				$grp_price = $this->getGroupPrice('all');
				if ($grp_price!=false) {
					$products_price = $grp_price['price'];
					if ($grp_price['no_graduated']==0) {
						$this->data['group_price'] = $grp_price;
						$cheapest_price_otax = $grp_price['cheapest'];
						$format_type = 'graduated';
						
						// Add Tax
						$cheapest_price = $price->_AddTax($cheapest_price_otax, $products_tax);

					}
				}
			}

		}
		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getPrice_middle')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value)) return $plugin_return_value;

        $orderId = $order_edit_controller->_orders_id;

        $priceOverride = $_SESSION['order_edit_priceOverride'][$orderId];
        if ($priceOverride && array_key_exists($this->pID, $priceOverride) && $_GET['pg'] !== 'calculateGraduatedPrice' && $_GET['pg'] !== 'overview')
        {
			$new_price = $priceOverride[$this->pID];

			if ($order_edit_controller->_customers_status->customers_status_show_price_tax)
			{
                $new_price = $new_price / (1 + $products_tax / 100);
			}
            else
            {
                $new_price = $new_price * (1 + $products_tax / 100);
            }

			global $currency;
			if (_SYSTEM_ORDER_EDIT_USE_CUSTOMER_CURRENCY === 'true' && $currency->default_currency != $currency->code) // der eingegebne preis ist in vom shop abweichender kundenwährung
			{
				$new_price = round($new_price / $currency->value_multiplicator, 4);
			}

            $format_type = 'default';
            $products_price = $new_price;
		}

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getPrice_price')) ? eval($plugin_code) : false;

		// Check Currency
		if($values['curr']=='true')
		$products_price = $price->_calcCurrency($products_price);

		// Set Price without Tax
		$products_price_otax = $products_price;

		// Add Tax
		$products_price = $price->_AddTax($products_price, $products_tax);

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getPrice_afterProductsPrice')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		switch ($format_type) {
			case 'default':
				$format_array = array('price'=>$products_price, 'price_otax'=>$products_price_otax, 'format'=>$values['format'], 'format_type' => 'default', 'date_available' => $date_available, 'date_expired' => $date_expired);
				break;
			case 'special':
				$format_array = array('price'=>$special_price,'price_otax'=>$special_price_otax,'old_price'=>$products_price, 'old_price_otax'=>$products_price_otax, 'format'=>$values['format'], 'format_type' => 'special', 'date_available' => $date_available, 'date_expired' => $date_expired);
				break;
			case 'graduated':
				$format_array = array('price'=>$products_price,'price_otax'=>$products_price_otax,'cheapest_price'=>$cheapest_price, 'cheapest_price_otax'=>$cheapest_price_otax, 'format'=>$values['format'], 'format_type' => 'graduated', 'date_available' => $date_available, 'date_expired' => $date_expired);
				break;

			default:
			($plugin_code = $xtPlugin->PluginCode('class.product.php:_getPrice_Format')) ? eval($plugin_code) : false;
			if(isset($plugin_return_value))
			return $plugin_return_value;
		}
//var_dump($format_array, __LINE__, __FILE__);
		$price_data = $price->_Format($format_array);
		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getPrice_bottom')) ? eval($plugin_code) : false;
		return $price_data;


	}


	/**
	 * get special price of product
	 *
	 * return false or price array
	 *
	 * @param unknown_type $sp_type
	 * @return unknown
	 */
	function getSpecialPrice($sp_type='group') {
		global $xtPlugin, $db, $price, $customers_status;

		($plugin_code = $xtPlugin->PluginCode('class.product.php:getSpecialPrice_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$date = $db->DBTimeStamp(time());
		
		$where_from = " and (date_available <= " . $date . "  or date_available = 0)";
		$where_to = " and (date_expired >= " . $date."  or date_expired = 0)";

		$record = $db->Execute(
			"SELECT specials_price,id,date_available,date_expired FROM ".TABLE_PRODUCTS_PRICE_SPECIAL." WHERE products_id=? and group_permission_".$price->p_group."='1' ".$where_from.$where_to." and status='1' LIMIT 0,1",
			array($this->data['products_id'])
		);
		if ($record->RecordCount()==0) {
			$record = $db->Execute(
				"SELECT specials_price,id,date_available,date_expired FROM ".TABLE_PRODUCTS_PRICE_SPECIAL." WHERE products_id=? and group_permission_all='1' ".$where_from.$where_to." and status='1' LIMIT 0,1",
				array($this->data['products_id'])
			);
		}

		if($record->RecordCount() == 1){
			$data = $record->fields['specials_price'];
			$date_available = $record->fields['date_available'];
			$date_expired = $record->fields['date_expired'];
			($plugin_code = $xtPlugin->PluginCode('class.product.php:getSpecialPrice_data')) ? eval($plugin_code) : false;
			return array($data, $date_available, $date_expired);
		}else{
			return array(false, '', '');
		}
	}

	function getGroupPrice($sp_type='group',$qty_force = -1) {
		global $xtPlugin, $db, $price, $customers_status;

		($plugin_code = $xtPlugin->PluginCode('class.product.php:getGroupPrice_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$prices = array();
		if($customers_status->customers_status_graduated_prices != 0){
		
			if($sp_type=='group'){
				$record = $db->Execute(
					"SELECT * FROM ".TABLE_PRODUCTS_PRICE_GROUP.$price->p_group." WHERE products_id=? ORDER BY discount_quantity ASC",
					array($this->data['products_id'])
				);
			} else {
				$record = $db->Execute(
					"SELECT * FROM ".TABLE_PRODUCTS_PRICE_GROUP."all WHERE products_id=? ORDER BY discount_quantity ASC",
					array($this->data['products_id'])
				);
			}
			if($record->RecordCount() >= 1){
                if($record->RecordCount() == 1){
                    //only one check rule
                    if($record->fields['discount_quantity'] != 0 && $record->fields['discount_quantity'] != 1 ){
                        $prices[] = array('qty'=>1,'price'=>$this->data['products_price']);
                    }
                }
				while (!$record->EOF) {
					$prices[] = array('qty'=>$record->fields['discount_quantity'],'price'=>$record->fields['price']);
					$record->MoveNext();
				}$record->Close();

				$qty = $this->qty;
				if ($qty_force!=-1) $qty = $qty_force;
				$data = array();
				for ($i = 0, $n = sizeof($prices); $i < $n; $i++) {
					if ($prices[$i]['qty']==$qty) {
						$data['price']=$prices[$i]['price'];
						break;
					} else {
						// kleiner
						if (!isset($prices[$i+1]['qty'])) {
							$data['price']=$prices[$i]['price'];
							break;
						} else {
							if ($prices[$i+1]['qty']>$this->qty) {
								$data['price']=$prices[$i]['price'];
								break;
							}
						}
					}
				}
	
				$data['no_graduated']=0;
                //only one check rule
                if(sizeof($prices) == 1 && $prices[0]['qty']<2){
                    for ($i = 0, $n = sizeof($prices); $i < $n; $i++) {
                        $prices[$i]['price'] = $price->_calcCurrency($prices[$i]['price']);
                    }
                    $data['no_graduated']=1;
                } else{
					for ($i = 0, $n = sizeof($prices); $i < $n; $i++) {
                        $prices[$i]['price'] = $price->_calcCurrency($prices[$i]['price']);
                    }
                    $data['prices']=$prices;
                    $size = count($prices);
                    if ($size==0) $data['no_graduated']=1;

                    //$last = array_pop($prices);
                    $data['cheapest'] = $prices[$size-1]['price'];
                }

				($plugin_code = $xtPlugin->PluginCode('class.product.php:getGroupPrice_data')) ? eval($plugin_code) : false;
				return $data;
			} else {
				return false;
			}
		} else {
			return false;
		}

	}

	/* old, bad function!!! single data support */
	function getShippingStatus($val='name') {
		global $system_status;
		if (isset($system_status->values['shipping_status'][$this->data['products_shippingtime']])){
			return $system_status->values['shipping_status'][$this->data['products_shippingtime']][$val];
		}
	}
	
	/* new functions for full shipping data support */
	function getShippingStatusData() {
		global $system_status;
		if (isset($system_status->values['shipping_status'][$this->data['products_shippingtime']])) 
		return $system_status->values['shipping_status'][$this->data['products_shippingtime']];
	}
	
	function getStockTrafficRule($force_lang='') {
		if ($force_lang!='')
		{
			$system_status = new system_status($force_lang);
		}
		else 
		{
			global $system_status;
		}

		if (_SYSTEM_STOCK_RULES != 'true') return false;
		if (count($system_status->values['stock_rule'])==0) return false;

		reset($system_status->values['stock_rule']);

		if ($this->data['products_quantity'] > 0) {
			if ($this->data['products_average_quantity'] <= 0) {
				$percentage = 100;
			}else{
				$percentage = $this->data['products_quantity'] / $this->data['products_average_quantity'] * 100;
			}
			if ($percentage > 100)
			$percentage = 100;
		} else {
			$percentage = 0;
		}

		while (current($system_status->values['stock_rule'])) {
			$current = current($system_status->values['stock_rule']);

			if ($percentage < $current['data']['percentage']) {
				$next = next($system_status->values['stock_rule']);
				if ($percentage > $next['data']['percentage']) {
					return array ('name' => $next['name'],'image' => $next['image']);
				} else {
					prev($system_status->values['stock_rule']);
				}
			} elseif ($percentage == $current['data']['percentage']) {
				return array ('name' => $current['name'],'image' => $current['image']);
			}
			next($system_status->values['stock_rule']);
		}
	}

	function getBasePrice() {
		global $system_status,$price;
		
		if ($this->data['products_vpe_status'] == 1 && $this->data['products_vpe_value'] != 0.0 && $this->data['products_price']['plain'] > 0) {
			$_price = $price->_AddTax($this->data['products_price']['plain_otax'], $this->data['products_tax_rate']);

            $base_price_plain = $_price * (1/$this->data['products_vpe_value']);
            $base_price = $price->_StyleFormat($base_price_plain);
			if (isset($system_status->values['base_price'][$this->data['products_vpe']]))
			$this->data['base_price']=array('vpe'=>$system_status->values['base_price'][$this->data['products_vpe']],'price'=>$base_price,'price_plain'=>$base_price_plain);
        }
	}

    /**
    * get breadcrumb for product
    * 
    */
	function getBreadCrumbNavigation() {
		global $db,$brotkrumen,$store_handler;

		$query = "SELECT * FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id=? AND master_link='1' AND store_id=? LIMIT 0,1";
		$rs = $db->Execute($query, array($this->pID, $store_handler->shop_id));
		// add categories
		if ($rs->RecordCount()==1) {
			$category = new category($rs->fields['categories_id']);
			$path = $category->getNavigationPath($rs->fields['categories_id']);
			$path = array_reverse($path);
			foreach ($path as $key => $arr) {
				$brotkrumen->_addItem($arr['categories_link'],$arr['categories_name']);
			}
		}
		// add product
		$brotkrumen->_addItem($this->data['products_link'],$this->data['products_name']);
	}

	/**
	 * delete product data from all tables
	 *
	 * @param int $id
	 */

	function _delete($id) {
		global $db,$xtPlugin;

		$id = (int)$id;
		if (!is_int($id)) return false;

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_delete_top')) ? eval($plugin_code) : false;

		if (is_int($id)) {
			$db->Execute("DELETE FROM " . TABLE_PRODUCTS_PRICE_SPECIAL . " WHERE products_id = ?", array($id));
			$db->Execute("DELETE FROM " . TABLE_PRODUCTS . " WHERE products_id = ?", array($id));
			$db->Execute("DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id = ?", array($id));
			$db->Execute("DELETE FROM " . TABLE_PRODUCTS_DESCRIPTION . " WHERE products_id = ?", array($id));
			saveDeletedUrl($id,1);
			$db->Execute("DELETE FROM " . TABLE_SEO_URL . " WHERE link_id = ? and link_type='1'", array($id));
			$db->Execute("DELETE FROM " . TABLE_PRODUCTS_SERIAL . " WHERE products_id = ?", array($id));
			$db->Execute("DELETE FROM " . TABLE_MEDIA_LINK . " WHERE link_id = ? and class='product'", array($id));
			$db->Execute("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE products_id= ?", array($id));
			$db->Execute("DELETE FROM " . TABLE_PRODUCTS_PERMISSION . " WHERE pid = ?", array($id));
			
			($plugin_code = $xtPlugin->PluginCode('class.product.php:_delete_bottom')) ? eval($plugin_code) : false;
		}

	}

	function _setStatus($id, $status) {
		global $db,$xtPlugin;

		$id = (int)$id;
		if (!is_int($id)) return false;

		$db->Execute(
			"UPDATE " . TABLE_PRODUCTS . " SET products_status =? WHERE products_id = ?",
			array($status, $id)
		);

        //delete product from saved Cart, if status=0
        if($status == 0)
            $db->Execute("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE products_id= ?", array($id));
			
		($plugin_code =$xtPlugin->PluginCode('class.product.php:_setStatus_bottom')) ? eval($plugin_code) : false;
    }

	function _getTax($id){
		global $tax, $customers_status, $xtPlugin;

		$tax_rate = $tax->data[$id];

		if ($tax_rate > 0 && $customers_status->customers_status_show_price_tax != 0) {
			$tax_desc = TEXT_INCL.' ' . $tax_rate . ' % ' . TEXT_VAT;
		}elseif ($tax_rate > 0 && $customers_status->customers_status_show_price_tax == 0 && $customers_status->customers_status_add_tax_ot == 1) {
			$tax_desc = TEXT_ADD.' ' . $tax_rate . ' % ' . TEXT_VAT;
		}elseif ($tax_rate > 0 && $customers_status->customers_status_show_price_tax == 0 && $customers_status->customers_status_add_tax_ot == 0) {
			$tax_desc = TEXT_EXCL.' ' . $tax_rate . ' % ' . TEXT_VAT;
		}

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getTax_bottom')) ? eval($plugin_code) : false;
		
		
		$products_tax = array(
			'tax'=>$tax_rate,
			'tax_desc' => $tax_desc
		);

		return $products_tax;
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $language, $xtPlugin, $customers_status, $db;

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getParams_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if (StoreIdExists(TABLE_PRODUCTS_DESCRIPTION,$this->_store_field)) 
		{
			$this->store_field_exists=true;
		}
		$params = array();
		
		$params['display_saveBtn'] = true;	
		if ($this->store_field_exists)
			$params['languageStoreTab'] = true;

		$header['permission_id'] = array('type' => 'hidden');
		$header['products_owner'] = array('type' => 'hidden');
        $header['total_downloads'] = array('type' => 'hidden'); 

		$header['products_digital'] = array('type' => 'status');
		$header['products_serials'] = array('type' => 'status');

		$header['manufacturers_id'] = array('type' => 'dropdown','url'  => 'DropdownData.php?get=manufacturers');

		$header['products_tax_class_id'] = array('type' => 'dropdown','url'  => 'DropdownData.php?get=tax_classes');

		$header['products_shippingtime'] = array('type' => 'dropdown','url'  => 'DropdownData.php?get=shipping_time');

		$header['products_vpe'] = array('type' => 'dropdown','url'  => 'DropdownData.php?systemstatus=base_price');
        $header['products_unit'] = array('type' => 'dropdown','url'  => 'DropdownData.php?systemstatus=base_price');
        $header['products_type'] = array('renderer'  => 'slaveRenderer','type' => 'hidden','width'=>40);
		
		if($this->url_data['new'] == true && !$this->url_data['edit_id'] && !$this->url_data['get_singledata']) {

			$check_task = new adminTask();
			$check_task->setClass(__CLASS__);

			$check_new = $check_task->checkTask('new');

			if($check_new === 'new'){
			$obj = $this->_set(array(), 'new');
			$this->url_data['edit_id'] = $obj->new_id;
			}else{
				$this->url_data['edit_id'] = $check_new;
				$this->url_data['catID'] = _getSingleValue(array('value'=>'categories_id', 'table'=>TABLE_PRODUCTS_TO_CATEGORIES, 'key'=>'products_id', 'key_val'=>$check_new, 'key_where'=>' and master_link=1'));
				$this->url_data['changeMasterCat'] = true;
			}

			($plugin_code = $xtPlugin->PluginCode('class.product.php:_getParams_new')) ? eval($plugin_code) : false;
		}
		
		$st = new multistore();
		$stores = $st->getStores();
		
		foreach ($stores as $store) {	
			foreach ($language->_getLanguageList() as $key => $val) {
				if ($this->store_field_exists) $add_to_f = 'store'.$store['id'].'_';
				
				$header['products_description_'.$add_to_f.$val['code']] = array('type' => 'htmleditor');
				$header['products_short_description_'.$add_to_f.$val['code']] = array('type' => 'htmleditor');
	
				if(_SYSTEM_HIDE_SUMAURL=='true'){
					$header['url_text_'.$add_to_f.$val['code']] = array('type'=>'hidden');
				}else{
					$header['url_text_'.$add_to_f.$val['code']] = array('width'=>400);
				}
	
				$header['products_store_id_'.$add_to_f.$val['code']] = array('type'=>'hidden');
				$header['store_id_'.$add_to_f.$val['code']] = array('type'=>'hidden');
				$header['meta_keywords_'.$add_to_f.$val['code']] = array('width'=>400);
				$header['meta_title_'.$add_to_f.$val['code']] = array('width'=>400);
				$header['meta_description_'.$add_to_f.$val['code']] = array('type' => 'textarea','width'=>400,'height'=>60);
				if (count($stores)>1){
                    if (_SYSTEM_SHOW_OVERLOAD_MESSAGE=="true") $msg = TEXT_OVERLOAD_PRODUCT_DATA;
                    else $msg ='';
                    $listners = array("select"=>"OverLoadata('reload_st_".$add_to_f.$val['code']."','".$val['code']."','".$store['id']."',
                                   '".$this->url_data['edit_id']."','".$msg."');");

                    $header['reload_st_'.$add_to_f.$val['code']] = array('type' => 'dropdown','url'  => 'DropdownData.php?get=stores&current_store_id='.$store['id'],'listner'=>$listners);
                }else{
                    $header['reload_st_'.$add_to_f.$val['code']] =array('type'=>'hidden');
                }
			}
		}
		($plugin_code = $xtPlugin->PluginCode('class.product.php:_get_data')) ? eval($plugin_code) : false;

		$header['products_id'] = array('type' => 'hidden');
		$header['products_cmc'] = array('type' => 'hidden');
		$header['image_class'] = array('type' => 'hidden');
		$header['flag_has_specials'] = array('type' => 'hidden');
		$header['price_flag_graduated_all'] = array('type' => 'hidden');
		
		$c_status = $customers_status->_getStatusList('admin');

		foreach ($c_status as $key => $val) {
			$header['price_flag_graduated_'.$val['id']] = array('type' => 'hidden');
		}

		$groupingPosition = 'products_fsk18';
		$grouping['products_fsk18'] = array('position' => $groupingPosition);
        $grouping['products_unit'] = array('position' => 'products_vpe');
		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getParams_panelSettings')) ? eval($plugin_code) : false;

		$params['panelSettings']  = isset($panelSettings) ? $panelSettings : array();

		$store_to_url = '';
		if ($_GET['parentNode'])
		{
			$current_store = explode("catst_",$_GET['parentNode']);
			if ($current_store[1])
				$store_to_url = '&store_id='.$current_store[1];				
		}

		$rowActions[] = array('iconCls' => 'move_product', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_TO_CATEGORIES);
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($this->url_data['edit_id'])."';\n";
		else
          $js = "var edit_id = record.id; var edit_name=record.get('products_model');\n";
          $extF = new ExtFunctions();
		  $js.= $extF->_RemoteWindow("TEXT_PRODUCTS_TO_CATEGORIES","TEXT_PRODUCTS","adminHandler.php?load_section=product_to_mastercat&pg=getTreePanel&products_id='+edit_id+'".$store_to_url , '', array(), 800, 600).' new_window.show();';


		$rowActionsFunctions['move_product'] = $js;


		$rowActions[] = array('iconCls' => 'more_categories', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_TO_MORE_CATEGORIES);
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($this->url_data['edit_id'])."';\n";
		else
          $js = "var edit_id = record.id; var edit_name=record.get('products_model');\n";
          $extF = new ExtFunctions();
		  $js.= $extF->_RemoteWindow("TEXT_PRODUCTS_TO_MORE_CATEGORIES","TEXT_PRODUCTS","adminHandler.php?load_section=product_to_cat&pg=getTreePanel&products_id='+edit_id+'".$store_to_url, '', array(), 800, 600).' new_window.show();';

		$rowActionsFunctions['more_categories'] = $js;

		// Get media files tabs
		$rs = $db->Execute(
			'SELECT * FROM ' . TABLE_MEDIA_GALLERY . ' LEFT JOIN ' . TABLE_MEDIA_GALLERY_DESCRIPTION . ' USING(mg_id) WHERE class IN("files_free", "files_order") AND language_code=?',
			array($language->environment_language)
		);
		$tabs = array();
		
		$tabs[] = array(
				'url' => 'adminHandler.php',
				'url_short' => true,
				'params' => "load_section=product_to_media&currentType=product&link_id=' + product_edit_id + '",
				'title' => TEXT_ATTACHED_FILES,
		);
		while (!$rs->EOF) {
			$tabs[] = array(
				'url' => 'adminHandler.php',
				'url_short' => true,
				'params' => sprintf("load_section=MediaList&pg=overview&mgID=%s&galType=%s&products_id=' + product_edit_id + '", $rs->fields['mg_id'], $rs->fields['class']),
				'title' => $rs->fields['name'],
			);
			$rs->MoveNext();
		}$rs->Close();
		$rowActions[] = array('iconCls' => 'products_media', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_TO_MEDIA);
		if ($this->url_data['edit_id'])
			$js = "var product_edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($this->url_data['edit_id'])."';\n";
		else
			$js = "var product_edit_id = record.id; var edit_name=record.get('products_model');\n";
		$extF = new ExtFunctions();
		
		$js.= $extF->_TabRemoteWindow(TEXT_ATTACHED_FILES, $tabs, 800, 600, 'js') . "new_window.show();";
		if ($this->url_data['edit_id'])
			$js = "var product_edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($this->url_data['edit_id'])."';\n";
		else
			$js = "var product_edit_id = record.id; var edit_name=record.get('products_model');\n";
		$extF = new ExtFunctions();
		
		$js.= $extF->_TabRemoteWindow(TEXT_ATTACHED_FILES, $tabs, 800, 600, 'js') . "new_window.show();";
		$rowActionsFunctions['products_media'] = $js;
		
		// Product stats
		$rowActions[] = array('iconCls' => 'products_stats', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_STATS);
		if ($this->url_data['edit_id'])
			$js = "var product_edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($this->url_data['edit_id'])."';\n";
		else
			$js = "var product_edit_id = record.id; var edit_name=record.get('products_model');\n";
		$extF = new ExtFunctions();
		$tabs = array();
		$tabs[] = array(
				'url' => 'adminHandler.php',
				'url_short' => true,
				'params' => "load_section=product&pg=stats&view_id=' + product_edit_id + '",
				'title' => TEXT_PRODUCTS_STATS,
		);
		$js.= $extF->_RemoteWindow("TEXT_PRODUCTS_STATS","TEXT_PRODUCTS_STATS","adminHandler.php?load_section=product&pg=stats&view_id=' + product_edit_id + '", '', array(), 1000, 800).' new_window.show();';
		$rowActionsFunctions['products_stats'] = $js;
		// End product stats

		$rowActions[] = array('iconCls' => 'products_special_price', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_SPECIAL_PRICE);
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($this->url_data['edit_id'])."';\n";
		else
          $js = "var edit_id = record.id; var edit_name=record.get('products_model');\n";

          $js .= "addTab('adminHandler.php?plugin=xt_special_products&load_section=product_sp_price&pg=overview&products_id='+edit_id,'".TEXT_PRODUCTS_SPECIAL_PRICE." ('+edit_name+')', 'product_sp_price'+edit_id)";

		$rowActionsFunctions['products_special_price'] = $js;

		$rowActions[] = array('iconCls' => 'products_group_price', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_GROUP_PRICE);
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($this->url_data['edit_id'])."';\n";
		else
          $js = "var edit_id = record.id; var edit_name=record.get('products_model');\n";
          $js .= "addTab('adminHandler.php?load_section=product_price&pg=overview&products_id='+edit_id,'".TEXT_PRODUCTS_GROUP_PRICE." ('+edit_name+')', 'product_price'+edit_id)";

		$rowActionsFunctions['products_group_price'] = $js;

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getParams_row_actions')) ? eval($plugin_code) : false;

		$params['rowActions']             = $rowActions;
		$params['rowActionsFunctions']    = $rowActionsFunctions;
		
		$params['menuGroups']			   = isset($menuGroups) ? $menuGroups : array();
		
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id'].";";
		else
          $js = "var edit_id = record.id;";
                  

        $extF = new ExtFunctions();
        $mjs = $extF->_MultiButton_stm('BUTTON_START_SEO', 'doProductsSeo');
		 		
        $params['display_productsSeoMn']  = true;
 		$params['menuActions']             = isset($menuActions) ? $menuActions : array();
 		
		$params['header']         = $header;
		$params['grouping']         = $grouping;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = 'products_status';
		
		if (isset($this->sql_limit))
		{
			$exp= explode(",",$this->sql_limit);
			$params['PageSize'] = trim($exp[1]);
		}
		
		$params['display_checkItemsCheckbox']  = true;
		$params['display_searchPanel']  = true;
		$params['display_checkCol']  = true;
		$params['display_statusTrueBtn']  = true;
		$params['display_statusFalseBtn']  = true;
		$params['display_copyBtn']  = true;
		if ($this->url_data['edit_id'])
			$params['display_resetBtn'] = false;

		$stor= '';
		if ($_GET['parentNode'])
		{
			$current_store = explode("catst_",$_GET['parentNode']);
			if ($current_store[1])
				$stor = 'store'.$current_store[1].'_';
			else{
				if ($this->store_field_exists) $stor= 'store'.$st->shop_id.'_';
				
			}
		}
		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$params['include'] = array ('products_id','products_type', 'products_name_'.$stor.$language->code, 'products_model', 'products_quantity', 'products_price', 'products_status');
		}else{
			$params['exclude'] = array ('date_added', 'last_modified', 'products_average_rating', 'products_rating_count', 'products_ordered','products_transactions','language_code', 'external_id');
		}

			$menuGroups[] = array('group'=>'edit_data', 'group_name'=>TEXT_MULTI_ACTION_MENU, 'ToolbarPos'=>'Toolbar', 'Pos'=>'grid'); // Toolbarpos = TopToolbar or Toolbar, Pos = grid / edit / both
			$params['menuGroups'] = $menuGroups;					

			
			$menuActions['edit_data']['multi_copy'] = array(
				'text'=>'TEXT_MULTI_COPY',
				'status' =>true,
				'acl'=>'edit',
				'style'=>'multi_copy',
				'icon'=>'picture_go.png',
				'stm'=>$extF->_MultiButton_stm('TEXT_MULTI_COPY', 'doMultiCopy'),
				'func'=>'doMultiCopy',
				'flag'=>'multiFlag_copy',
				'flag_value'=>'true',
				'page'=>'window',
				'page_url'=>'adminHandler.php?load_section=ProductToCategories&source_cat='.$this->url_data['catID'].'&editType=copy&pg=getTreePanel',
				'page_title'=>'TEXT_MULTI_COPY'
			);
			
			$menuActions['edit_data']['multi_move'] = array(
				'text'=>'TEXT_MULTI_MOVE',
				'status' =>true,
				'acl'=>'edit',
				'style'=>'multi_move',
				'icon'=>'picture_go.png',
				'stm'=>$extF->_MultiButton_stm('TEXT_MULTI_MOVE', 'doMultiMove'),
				'func'=>'doMultiMove',
				'flag'=>'multiFlag_move',
				'flag_value'=>'true',
				'page'=>'window',
				'page_url'=>'adminHandler.php?load_section=ProductToCategories&source_cat='.$this->url_data['catID'].'&editType=move&pg=getTreePanel',
				'page_title'=>'TEXT_MULTI_MOVE'
			);
															
			$menuActions['edit_data']['multi_link'] = array(
				'text'=>'TEXT_MULTI_LINK',
				'status' =>true,
				'acl'=>'edit',
				'style'=>'multi_link',
				'icon'=>'picture_go.png',
				'stm'=>$extF->_MultiButton_stm('TEXT_MULTI_LINK', 'doMultiLink'),
				'func'=>'doMultiLink',
				'flag'=>'multiFlag_link',
				'flag_value'=>'true',
				'page'=>'window',
				'page_url'=>'adminHandler.php?load_section=ProductToCategories&source_cat='.$this->url_data['catID'].'&editType=link&pg=getTreePanel',
				'page_title'=>'TEXT_MULTI_LINK'
			);
			$params['display_multi_copyMn']  = true;																										
			$params['display_multi_moveMn']  = true;
			$params['display_multi_linkMn']  = true;
			
			$params['menuActions']             = $menuActions;	
					
		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getParams_bottom')) ? eval($plugin_code) : false;
		return $params;
	}
	
	/* return product description table header to hide them when store is selected */
    function returnProductDescFields(){
        $excl_array = array("products_id","language_code","reload_st","products_store_id");
        $table_data = new adminDB_DataRead($this->_table_lang, null, null, $this->_master_key, null, null, null);
        $data = $table_data->getHeader();
       
        $obj = new stdClass;
        $obj->success = true;
        $obj->data = $data[0];
        $d='product_meta_description_,product_meta_keywords_,product_meta_title_,wordcouter_product_meta_title_,wordcouter_product_meta_description_,wordcouter_product_meta_keywords_,';
        foreach($data[0] as $k=>$v){
            if (!in_array($k,$excl_array))
                $d .='product_'.$k.'_,'; 
        }
        $obj->data = rtrim($d, ",");
        echo json_encode($obj);
    }
	
	public function stats() {
		global $store_handler;
		// Create the panel
		$stat_products = new PhpExt_Panel();
		$stat_products
		->setAutoScroll(true)
		->setAutoWidth(true);
		
		// Instantiate new chart object
		$productsChart = new PhpExt_Amchart();
		// Set id
		$productsChart->setChartName('products-amchart-' . $this->url_data['view_id'])->setId('products-amchart-totals-' . $this->url_data['view_id']);
		
		// Set callback just before filtering so we can change chart axis values depending on the type of period
		// we want to filter
		$productsChart->attachListener('beforeFilterRequest', new PhpExt_Listener(PhpExt_Javascript::functionDef(null,
				'var displayType = Ext.getCmp("ChartDisplayType-' .  $this->url_data['view_id'] . '").getValue();' .
				'this.chart.valueAxes[0].title = (displayType == "amount") ? "Amount" : "Quantity";' .
				'var period = Ext.getCmp("ProductsPeriodTypeFilter-' .  $this->url_data['view_id'] . '").getValue();' .
				'switch (period) {' .
				'case "day":' .
				'this.chart.dataDateFormat = "YYYY-MM-DD";' .
				'this.chart.categoryAxis.minPeriod = "DD";' .
				'break;'.
				'case "month":' .
				'this.chart.dataDateFormat = "YYYY-MM";' .
				'this.chart.categoryAxis.minPeriod = "MM";' .
				'break;'.
				'case "year":' .
				'this.chart.dataDateFormat = "YYYY";' .
				'this.chart.categoryAxis.minPeriod = "YYYY";' .
				'break;'.
				'}'
		)));
		
		// Set data reader for this chart
		$reader = new PhpExt_Data_JsonReader();
		$reader->setRoot('topics')->setTotalProperty('totalCount');
		$reader->addField(new PhpExt_Data_FieldConfigObject("date"));
		
		// Set data proxy for this chart
		$fromStore = new PhpExt_Data_Store();
		$fromStore->setProxy(new PhpExt_Data_HttpProxy('chart.php?ChartType=product_sells&view_id=' . $this->url_data['view_id']))->setReader($reader);
		
		$productsChart->setStore($fromStore);
		// Some filters for the chart
		$fromFilter = PhpExt_Form_DateField::createDateField('ProductsFromDate', TEXT_FROM);
		$fromFilter->setFormat('Y-m-d H:i:s')->setValue(date('Y-m-d', strtotime('-1 month')))->setWidth(150)->setId('ProductsFrom-' . $this->url_data['view_id'] . '-Date');
		
		$toFilter = PhpExt_Form_DateField::createDateField('ProductsToDate', TEXT_TO);
		$toFilter->setFormat('Y-m-d H:i:s')->setValue(date('Y-m-d', strtotime('now')))->setWidth(150)->setId('ProductsTo-' . $this->url_data['view_id'] . '-Date');
		
		$helper = new ExtFunctions();
		$periodTypeFilter = $helper->_comboBox('ProductsPeriodType', TEXT_PERIOD_TYPE, 'DropdownData.php?get=filter_period');
		$periodTypeFilter->setWidth(150)->setId('ProductsPeriodTypeFilter-' . $this->url_data['view_id'])->setValue('day');
		
		$customerStatusFilter = $helper->_comboBox('CustomersStatus', TEXT_CUSTOMERS_STATUS, 'DropdownData.php?get=customers_status');
		$customerStatusFilter->setWidth(150)->setId('ProductsCustomersStatusFilter-' . $this->url_data['view_id']);
		
		$statsDisplayTypeFilter = $helper->_comboBox('ChartDisplayType', TEXT_DISPLAY_TYPE, 'DropdownData.php?get=product_stats_display_type');
		$statsDisplayTypeFilter->setWidth(150)->setId('ChartDisplayType-' . $this->url_data['view_id'])->setValue('quantity_sold');
		
		// Set filter Ids to the chart so when filtering is performet we can grab their values and pass them to the proxy
		$productsChart->setFilterWidgetsNames(array(
				'ProductsFrom-' . $this->url_data['view_id'] . '-Date', 
				'ProductsTo-' . $this->url_data['view_id'] . '-Date', 
				'ProductsPeriodTypeFilter-' . $this->url_data['view_id'], 
				'ProductsCustomersStatusFilter-' . $this->url_data['view_id'],
				'ChartDisplayType-' . $this->url_data['view_id'],
		));
		
		// Serial chart
		$serialChart = new AmSerialChart();
		$serialChart->setCategoryField('date');
		$serialChart->setPathToImages('../xtFramework/library/ext/ux/images/')->setDataDateFormat('YYYY-MM-DD')->setStartDuration(1);
		
		// Create axis object
		$axis = new AmCategoryAxis();
		$axis->setParseDates(true)
		->setMinPeriod('DD')
		->setAxisColor('#DADADA')
		->setTwoLineMode(true)
		->setGridPosition('start');
		//->setEqualSpacing(true);
		
		// Add the axis
		$serialChart->setCategoryAxis($axis);
		
		// Create cursor
		$cursor = new AmChartCursor();
		$cursor->setCursorAlpha(0.1)->setFullWidth(true);
		$serialChart->addChartCursor(AmChartCallable::createCallable($cursor));
		
		// Create legend
		$legend = new AmChartLegend();
		$legend->setMarginLeft(110)->setUseGraphSettings(false);
		$serialChart
		->addLegend(AmChartCallable::createCallable($legend))
		->addChartScrollbar(AmChartCallable::createCallable(new AmChartScrollbar()));
		
		$stores = $store_handler->getStores();
		
		// Create value axis
		$valueAxis = new AmValueAxis();
		$valueAxis
		->setAxisColor('#FF0000')
		->setAxisThickness(2)
		->setOffset(0)
		->setStackType('3d')
		->setTitle('Quantity')
		->setAxisAlpha(0);
		
		// Create graph for each store
		$graph = new AmGraph();
		$graph
		->setTitle(__define('TEXT_TOTAL'))
		->setValueField('total')
		->setBullet('round')
		->setBulletBorderThickness(1)
		->setHideBulletsCount(30)
		->setLineColor('#FF0000')
		->setFillAlphas(0.3)
		->setLineAlpha(0)
		->setType('column')
		->setBalloonText(__define('TEXT_TOTAL') . ' [[value]] ');
		$reader->addField(new PhpExt_Data_FieldConfigObject('total'));
		$serialChart->addValueAxis(AmChartCallable::createCallable($valueAxis))->addGraph(AmChartCallable::createCallable($graph));
		
		foreach ($stores as $storeData) {
			$color = random_color();
		
			$graph = new AmGraph();
			$graph
			->setTitle($storeData['text'])
			->setValueField($storeData['id'])
			->setBullet('round')
			->setBulletBorderThickness(1)
			->setHideBulletsCount(30)
			->setLineColor($color)
			->setBalloonText($storeData['text'] . ' - [[value]] ');
			$reader->addField(new PhpExt_Data_FieldConfigObject($storeData['id']));
			$serialChart->addGraph(AmChartCallable::createCallable($graph));
		}
		
		$productsChart->setChart($serialChart);
		
		// -------------- PIE CHART
		$customersByStoreChart = new PhpExt_Amchart();
		$customersByStoreChart->setChartName('products-amchart-by-store-' . $this->url_data['view_id'])->setId('products-amchart-totals-by-store-' . $this->url_data['view_id']);
		
		$reader = new PhpExt_Data_JsonReader();
		$reader->setRoot("topics")
		->setTotalProperty("totalCount");
		$reader->addField(new PhpExt_Data_FieldConfigObject("store_name"));
		$reader->addField(new PhpExt_Data_FieldConfigObject("store_total"));
		
		$fromstore = new PhpExt_Data_Store();
		$fromstore->setProxy(new PhpExt_Data_HttpProxy('chart.php?ChartType=product_sells_by_store&view_id=' . $this->url_data['view_id']))
		->setReader($reader);
		
		$customersByStoreChart->setStore($fromstore);
		
		$pieChart = new AmPieChart();
		$pieChart->setTitleField('store_name')
		->setValueField('store_total')
		->setAlpha(0.6)
		//->setBalloonText("[[title]]<br><span style=\"font-size:11px\"><b>[[value]] " . _STORE_CURRENCY . "</b> ([[percents]]%)</span>")
		->setHeight('100%');
		//->setDepth3D(15)
		//->setAngle(30);
		
		$legend = new AmChartLegend();
		$legend->setAlign("center")
		->setMarkerType("circle");
		
		$pieChart->addLegend(AmChartCallable::createCallable($legend));
		
		$customersByStoreChart->setChart($pieChart);
		$customersByStoreChart->setFilterWidgetsNames(array(
				'ProductsFrom-' . $this->url_data['view_id'] . '-Date', 
				'ProductsTo-' . $this->url_data['view_id'] . '-Date', 
				'ProductsPeriodTypeFilter-' . $this->url_data['view_id'], 
				'ProductsStatusFilter-' . $this->url_data['view_id'],
				'ChartDisplayType-' . $this->url_data['view_id'],
		));
		
		// -------------- END PIE CHART
		
		// Create filter panel
		$filterPanel = new PhpExt_Panel();
		$filterPanel->setTitle(__define("TEXT_FILTER"))
		->setAutoHeight(true);
		$filterPanel->setLayout(new PhpExt_Layout_FormLayout())->setBodyStyle("padding: 5px;");
		$filterPanel
		->setAutoWidth(true)
		->setAutoHeight(true)
		->setCssStyle('border:none;');
		//->setLayout(new PhpExt_Layout_FitLayout());
		$filterPanel->addItem($fromFilter)->addItem($toFilter)->addItem($periodTypeFilter)->addItem($customerStatusFilter)->addItem($statsDisplayTypeFilter);
		$filterPanel->addItem(PhpExt_Toolbar_Button::createButton(__define("TEXT_FILTER"), null, new PhpExt_Handler(PhpExt_Javascript::stm(
				$productsChart->getFilterEventJs() . $customersByStoreChart->getFilterEventJs()
		))));
		
		$columnPanel = new PhpExt_Panel();
		$columnPanel->setLayout(new PhpExt_Layout_ColumnLayout())->setAutoWidth(true)->setBorder(false);
		
		// Add filter panel to the tab
		$stat_products->addItem($filterPanel);
		
		// Column for first chart
		$firstColumn = new PhpExt_Panel();
		$firstColumn->setLayout(new PhpExt_Layout_FitLayout())->setBorder(false);
		$firstColumn->addItem(
				$productsChart
		);
		
		// Column for pie chart
		$secondColumn = new PhpExt_Panel();
		$secondColumn->setLayout(new PhpExt_Layout_FitLayout())->setBorder(false);
		$secondColumn->addItem(
				$customersByStoreChart
		);
		$columnPanel->addItem($firstColumn, new PhpExt_Layout_ColumnLayoutData(0.70));
		$columnPanel->addItem($secondColumn, new PhpExt_Layout_ColumnLayoutData(0.30));
		$stat_products->addItem($columnPanel);
		
		$stat_products->setRenderTo('product-stats-' . $this->url_data['view_id']);
		
		$js = PhpExt_Ext::OnReady(
				PhpExt_Javascript::stm(PhpExt_QuickTips::init()),
				$stat_products->getJavascript(false, 'stat' . $this->url_data['view_id'])
		);
		
		return '<script type="text/javascript">'. $js .'</script><div id="product-stats-' . $this->url_data['view_id'] . '"></div>';
	}

	function _getSearchIDs($search_data) {
		global $xtPlugin, $db, $language, $seo,$filter;

		$sql_tablecols = array('p.products_ean','p.products_id',
		                  'p.products_model',
		                  'pd.products_name');

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getSearchIDs_array')) ? eval($plugin_code) : false;

		$this->sql_Product = new getProductSQL_query();
		$this->sql_Product->setPosition('admin');
		$this->sql_Product->setFilter('Language');
		foreach ($sql_tablecols as $tablecol) {
			$sql_where[]= "(".$tablecol." LIKE '%".$filter->_filter($search_data)."%')";
		}
		$this->sql_Product->setSQL_WHERE(" and (".implode(' or ', $sql_where).")");
		$this->sql_Product->setSQL_GROUP(" p.products_id");
		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getSearchIDs_querry')) ? eval($plugin_code) : false;
		$query = "".$this->sql_Product->getSQL_query()."";

		$record = $db->Execute($query);
		if ($record->RecordCount() > 0) {

			while(!$record->EOF){
				$records = $record->fields;
				$data[] = $records['products_id'];
				$record->MoveNext();
			} $record->Close();
		}

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_getSearchIDs_bottom')) ? eval($plugin_code) : false;
		return $data;
	}

	function _getCategories($ID){
		global $db;

		$st = explode("catst_",$_GET['parentNode']);
		$add_where='';
		if ($st[1]){
			if (StoreIdExists(TABLE_PRODUCTS_TO_CATEGORIES,'store_id')) 
			{
				$add_where = " and p2c.store_id='".$st[1]."'";
			}
		}
		$query = "select products_id from
				 ".TABLE_PRODUCTS_TO_CATEGORIES . " p2c LEFT JOIN
				 ".TABLE_CATEGORIES." c ON p2c.categories_id = c.categories_id
				 where
				 ? in (c.categories_id, c.parent_id)".$add_where;

		$record = $db->Execute($query, array((int)$ID));
		if ($record->RecordCount() > 0) {

			while(!$record->EOF){
				$records = $record->fields;
				$data[] = $records['products_id'];
				$record->MoveNext();
			} $record->Close();
		}

		return $data;
	}


	function _get($ID=0){
		global $xtPlugin, $language,$store_handler;

		if ($this->position != 'admin') return false;

		$show_productList = 1;

		$obj = new stdClass;

		if ($ID === 'new') {
			$ID = $this->url_data['edit_id'];
		}

		$sql_where = '';

		if ($this->url_data['get_data'] && $this->url_data['catID']) {
			$this->url_data['catID'] = str_replace('subcat_','',$this->url_data['catID']);
			$cat_search_result = $this->_getCategories($this->url_data['catID']);

			if(!is_array($cat_search_result) || count($cat_search_result)==0){
				$show_productList = 0;
			}

		}

		if ($this->url_data['get_data'] && $this->url_data['query']) {
			$tmp_search_result = $this->_getSearchIDs($this->url_data['query']);

			if(is_array($cat_search_result)){
				foreach ($tmp_search_result as $skey => $sval) {
					if(in_array($sval, $cat_search_result)){
						$search_result[] = $sval;
					}
				}
				unset($cat_search_result);
			}else{
				$search_result = $tmp_search_result;
			}

		}

		if(is_array($search_result) && count($search_result)>0 || is_array($cat_search_result) && count($cat_search_result)>0){

			$p_search_result = array();

			if(is_array($search_result))
			$p_search_result = array_merge($p_search_result, $search_result);

			if(is_array($cat_search_result))
			$p_search_result = array_merge($p_search_result, $cat_search_result);

			$p_search_result = array_unique($p_search_result);

			$sql_where .= " products_id IN (".implode(',', $p_search_result).")";
		}

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_get_array')) ? eval($plugin_code) : false;
		
			// set limit if not set
		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}

		if(_SYSTEM_SIMPLE_GROUP_PERMISSIONS=='false')
		$permissions = $this->perm_array;
		else
		$permissions = '';
		$store_field= '';
		if ($this->store_field_exists) 
		{
			$store_field= $this->_store_field;
		}
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $sql_where, $this->sql_limit, $permissions,'','',$store_field);
		
		if ($this->url_data['get_data']){

			if($show_productList==1){
				$data = $table_data->getData();
				if(is_array($data)){
					foreach ($data as $key => $val) {
						$data[$key]['products_price'] = $this->build_price($data[$key]['products_id'], $data[$key]['products_price'], $data[$key]['products_tax_class_id']);
						//$data[$key]['products_type']=$data[$key]['products_master_model'];
						$data[$key]['products_type']=$data[$key]['products_master_model'].'_'.$data[$key]['products_master_flag'];
	                }
				}
				$data_count = $table_data->_total_count;

			}else{
				$data = '';
			}

		}elseif($ID){
			$data = $table_data->getData($ID);
			$data1[0] = array();
			$data[0]['products_cmc'] = $this->url_data['changeMasterCat'];

            $data[0]['group_permission_info']=_getPermissionInfo();
            $data[0]['shop_permission_info']=_getPermissionInfo();
			 $exclude_arr = array('reload_st_','url_text_');
			if(is_array($data)){
			   
				foreach ($data as $key => $val) {
					$data[$key]['products_price'] = $this->build_price($data[$key]['products_id'], $data[$key]['products_price'], $data[$key]['products_tax_class_id']);
                    $stores = $store_handler->getStores();
                    foreach ($stores as $store) {     
                        foreach ($language->_getLanguageList() as $k => $v) {
                            $add_to_f='';
                            if ($this->store_field_exists) $add_to_f = 'store'.$store['id'].'_';
                        
							if ($data[$key]['reload_st_'.$add_to_f.$v['code']]>0){
                                $data1[$key]['reload_st_'.$add_to_f.$v['code']] = $data[$key]['reload_st_'.$add_to_f.$v['code']];
                            }
                            $data[$key]['url_text_'.$add_to_f.$v['code']] = urldecode($data[$key]['url_text_'.$add_to_f.$v['code']]);
                        }
                    }
				}
			}
           
            $a = array_merge($data1[0],$data[0]);
            unset($data);
            $data[0] = $a;
		}else{
			$data = $table_data->getHeader();
		}
		
		($plugin_code = $xtPlugin->PluginCode('class.product.php:_get_bottom')) ? eval($plugin_code) : false;

		if($data_count!=0 || !$data_count)
		$count_data = $data_count;
		else
		$count_data = count($data);

		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;

	}

	function _set($data, $set_type='edit') {
		global $xtPlugin,$db,$language,$filter,$seo, $customers_status;
		if ($this->position != 'admin') return false;

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_set_top')) ? eval($plugin_code) : false;
		$data = $this->reloadDataFromStore($data);
		$obj = new stdClass;

		if($set_type=='new'){
			$data['date_added'] = $db->BindTimeStamp(time());
		}

		// UNSET SOME FIELDS;
		$exclude_fields = array('products_image', 'flag_has_specials', 'price_flag_graduated_all');
		$c_status = $customers_status->_getStatusList('admin');
		foreach ($c_status as $key => $val) {
			$exclude_fields[] = 'price_flag_graduated_'.$val['id'];
		}		
		
		if($set_type=='edit')
		$data['products_price'] = $this->build_price($data['products_id'], $data['products_price'], $data['products_tax_class_id'], 'save');
        
        if($data['products_status']==0){
            $db->Execute("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE products_id= '" .$data['products_id'] . "'");
        }
        
        if($data['products_status']=='true')
            $data['products_status']=1;
        elseif($data['products_status']=='false')
            $data['products_status']=0;
            
		$oP = new adminDB_DataSave(TABLE_PRODUCTS, $data);
		$oP->setExcludeFields($exclude_fields);
		
		$objP = $oP->saveDataSet();
		if ($objP->new_id) {
			$obj->new_id = $objP->new_id;
			$data[$this->master_id] = $objP->new_id;

			if ($this->url_data['catID']) {
				$this->url_data['catID'] = str_replace('subcat_','',$this->url_data['catID']);

				$exp = explode('_catst_',$_GET['parentNode']); // add store_id 
				if ($exp[1]){
					$data_array['store_id'] = $exp[1];
				}
				$data_array[$this->master_id] =  (int) $obj->new_id;
				$data_array['categories_id'] = (int) $this->url_data['catID'];

				if($set_type=='new'){
					$data_array['master_link'] = '1';
				}

				($plugin_code = $xtPlugin->PluginCode('class.product.php:_set_cat')) ? eval($plugin_code) : false;
				//$db->AutoExecute(TABLE_PRODUCTS_TO_CATEGORIES, $data_array);
				
				$oC = new adminDB_DataSave(TABLE_PRODUCTS_TO_CATEGORIES, $data_array);
				$objC = $oC->saveDataSet();
			}

		}elseif($data['products_cmc']==true){

			if ($this->url_data['catID']) {
				$this->url_data['catID'] = str_replace('subcat_','',$this->url_data['catID']);

				$exp = explode('_catst_',$_GET['parentNode']); // add store_id 
				if ($exp[1]){
					$data_array['store_id'] = $exp[1];
				}
				$data_array[$this->master_id] =  (int) $data[$this->master_id];
				$data_array['categories_id'] = (int) $this->url_data['catID'];
				$data_array['master_link'] = '1';

				($plugin_code = $xtPlugin->PluginCode('class.product.php:_set_cat_cmc')) ? eval($plugin_code) : false;
				//$db->AutoExecute(TABLE_PRODUCTS_TO_CATEGORIES, $data_array);
				
				$oC = new adminDB_DataSave(TABLE_PRODUCTS_TO_CATEGORIES, $data_array);
				$objC = $oC->saveDataSet();				
			}

		}
        

		// Build Seo URLS and product description
		$st = new multistore();
		$stores = $st->getStores();
		foreach ($stores as $store) {

			foreach ($language->_getLanguageList() as $key => $val) {
				
				$stor_f='';
				if ($this->store_field_exists) {
					$stor_f='store'.$store['id'].'_';
				}
	            $data['products_name_'.$stor_f.$val['code']] = trim($data['products_name_'.$stor_f.$val['code']]);
				
				$store_f_update='';
				if ($this->store_field_exists) {
					$stor_f='store'.$store['id'].'_';
					$store_f_update = $store['id'];
				}
				else {
					$stor_f='';
					$store_f_update = '';
				}

				if($data['url_text_'.$stor_f.$val['code']] != '' && $data['url_text_'.$stor_f.$val['code']]!='Suma URL'){
					$auto_generate = false;
				}else{
					$auto_generate = true;
					
					
					$data['url_text_'.$stor_f.$val['code']] = $data['products_name_'.$stor_f.$val['code']];
				}
				
				
				($plugin_code = $xtPlugin->PluginCode('class.product.php:_set_seo')) ? eval($plugin_code) : false;
				
					if($set_type=='edit')
					$seo->_UpdateRecord('product',$data['products_id'], $val['code'], $data, $auto_generate,'',$store_f_update);
			}
		}
		
		($plugin_code = $xtPlugin->PluginCode('class.product.php:_set_desc')) ? eval($plugin_code) : false;
		$oPD = new adminDB_DataSave(TABLE_PRODUCTS_DESCRIPTION, $data, true,__CLASS__,$this->store_field_exists);
		$objPD = $oPD->saveDataSet();
		
		($plugin_code = $xtPlugin->PluginCode('class.product.php:_set_perm')) ? eval($plugin_code) : false;

		$set_perm = new item_permission($this->perm_array);
		$set_perm->_saveData($data, $data[$this->_master_key]);
		$set_perm->_setSimplePermissionID('', $data['products_id'], 'categories_id', 'products_id', TABLE_CATEGORIES, TABLE_PRODUCTS, TABLE_PRODUCTS_TO_CATEGORIES);

		$obj->totalCount = 1;
		if ($objP->success && $objPD->success) {
			$obj->success = true;
		} else {
			$obj->failed = true;
		}

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_set_bottom')) ? eval($plugin_code) : false;
		return $obj;
	}

	/*
		rebuild data depending on selected reload store for each store and language
	*/
	function reloadDataFromStore($data){
        global $store_handler,$language;
       
        $stores = $store_handler->getStores();
        foreach ($stores as $store) {
            foreach ($language->_getLanguageList() as $key => $val) {
                $stor_f='';
                if ($this->store_field_exists) {
                    $stor_f='store'.$store['id'].'_';
                }
               if ($data['reload_st_'.$stor_f.$val['code']]>0){
                    $new_store = $data['reload_st_'.$stor_f.$val['code']];
                    $test = $data;
                    foreach($data as $k=>$v){
                        if(strpos($k,$stor_f.$val['code'])!==false){
                            $replaced = str_replace($stor_f.$val['code'],  'store'.$new_store.'_'.$val['code'], $k);
                             if (($k!='reload_st_'.$stor_f.$val['code']) && ($k!='url_text_'.$stor_f.$val['code'])){
                                $data[$k] = $data[$replaced];
                            }
                        }
                        
                    }
                }
            }
        }
       return $data;  
    }
	
	function _setImage($id, $file) {
		global $xtPlugin,$db,$language,$filter,$seo;
		if ($this->position != 'admin') return false;

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_setImage_top')) ? eval($plugin_code) : false;

		$obj = new stdClass;

		$data[$this->_master_key] = $id;
		$data['products_image'] = $file;

		$o = new adminDB_DataSave($this->_table, $data);
		$obj = $o->saveDataSet();

		$obj->totalCount = 1;
		if ($obj->success) {
			$obj->success = true;
		} else {
			$obj->failed = true;
		}

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_setImage_bottom')) ? eval($plugin_code) : false;
		return $obj;
	}	
	
	function _rebuildSeo($id, $params){
		global $xtPlugin,$db,$language,$filter,$seo;
		if ($this->position != 'admin') return false;

		$obj = new stdClass;
		$rs=$db->Execute(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema=? AND table_name=? AND COLUMN_NAME = ? ",
			array(_SYSTEM_DATABASE_DATABASE, $this->_table_lang, $this->_store_field)
		);
		$s_id ='';
		if ($rs->RecordCount()>0){
			$s_id = $this->_store_field;
		}
		$store_id ='';
		$exp = explode('_catst_',$_GET['parentNode']); // rebuild seo for category ID and store ID
		if ($exp[1]){
			$store_id = $exp[1];
		}
		$expl = explode('subcat_',$exp[0]);
		$add_table ='';
		$category_id ='';
		if((int)$expl[1] >0 ){
			$add_table = " INNER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." pc ON t.products_id=pc.products_id and pc.categories_id='". (int)$expl[1] ."'";
			$category_id = (int)$expl[1];
		}
		$seo->_rebuildSeo($this->_table, $this->_table_lang, $this->_table_seo, '1', 'product', 'products_name', $this->_master_key, $id,$s_id,$store_id,$add_table);		
	
		$obj->success = true;
		return $obj;			
			
	}
	
	function _copy($ID, $set_cat=true){
		global $xtPlugin,$db,$language,$filter,$seo,$customers_status;
		if ($this->position != 'admin') return false;

		$ID=(int)$ID;
		if (!is_int($ID)) return false;

		($plugin_code = $xtPlugin->PluginCode('class.product.php:_copy_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$obj = new stdClass;

		$store_field= '';
		if ($this->store_field_exists) 
		{
			$store_field= $this->_store_field;
		}
		// Product Data:
		$p_table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $this->perm_array, 'false','',$store_field);
		$p_data = $p_table_data->getData($ID);
        $p_data = $p_data[0];

        $old_product = $p_data[$this->_master_key];
        
        // Check for Productsmodel
        if($p_data['products_model']){
	        $start_count = 1;
	        $query = "select products_id from ".TABLE_PRODUCTS . " where products_model = ?";
			$record = $db->Execute($query, array($p_data['products_model']));
			$model_count = $record->RecordCount();
			
			$prod_count =  $model_count + $start_count;
			$p_data['products_model'] = $p_data['products_model']._SYSTEM_PRODUCT_COPY_PREFIX.$prod_count;	
		}
		// END Check

		unset($p_data[$this->_master_key]);
		$p_data['products_status'] = 0;

		$p_data['date_added'] = $db->BindTimeStamp(time());

		$oP = new adminDB_DataSave(TABLE_PRODUCTS, $p_data);
		$objP = $oP->saveDataSet();

		$obj->new_id = $objP->new_id;
		$p_data[$this->_master_key] = $objP->new_id;

		$oPD = new adminDB_DataSave(TABLE_PRODUCTS_DESCRIPTION, $p_data, true,__CLASS__, $this->store_field_exists);
		$objPD = $oPD->saveDataSet();

		// Cat Data:
		if($set_cat){
			$c_table_data = new adminDB_DataRead(TABLE_PRODUCTS_TO_CATEGORIES, null, null, 'categories_id', 'products_id='.$old_product, '', '', 'false');
			$c_data = $c_table_data->getData();
	
			for ($i = 0; $i < count($c_data); $i++) {
				$c_data[$i]['products_id'] = $obj->new_id;
	       		$oC = new adminDB_DataSave(TABLE_PRODUCTS_TO_CATEGORIES, $c_data[$i], false, __CLASS__);
	        	$objC2P = $oC->saveDataSet();
		    }
		}

	    // SEO URLs
		$st = new multistore();
		$stores = $st->getStores();
		foreach ($stores as $store) {
			foreach ($language->_getLanguageList() as $key => $val) {
				if ($this->store_field_exists) {
					$stor_f='store'.$store['id'].'_';
					$store_f_update = $store['id'];
				}
				else {
					$stor_f='';
					$store_f_update = '';
				}
		
				$auto_generate = true;
				$p_data['url_text_'.$stor_f.$val['code']] = $p_data['products_name_'.$stor_f.$val['code']];
				$seo->_UpdateRecord('product',$p_data[$this->_master_key], $val['code'], $p_data, $auto_generate, 'false',$store_f_update);
			}
		}

		// Permissions
		if(_SYSTEM_SIMPLE_GROUP_PERMISSIONS!='true'){
			$set_perm = new item_permission($this->perm_array);
			$set_perm->_saveData($p_data, $p_data[$this->_master_key]);
		}else{
			$set_perm = new item_permission($this->perm_array);
			$set_perm->_setSimplePermissionID('', $p_data[$this->_master_key], 'categories_id', $this->_master_key, TABLE_CATEGORIES, TABLE_PRODUCTS, TABLE_PRODUCTS_TO_CATEGORIES);
		}
		
		// Special Price
		$s_table_data = new adminDB_DataRead(TABLE_PRODUCTS_PRICE_SPECIAL, null, null, 'id', ' products_id='.$ID);
		$s_data = $s_table_data->getData();

		$s_count = count($s_data);

		for ($i = 0; $i < $s_count; $i++) {
			unset($s_data[$i]['id']);
			$s_data[$i]['products_id'] = $obj->new_id;
       		$oS = new adminDB_DataSave(TABLE_PRODUCTS_PRICE_SPECIAL, $s_data[$i], false, __CLASS__);
        	$objS2P = $oS->saveDataSet();
	    }

		// Group Price:
	    foreach ($customers_status->_getStatusList('admin', 'true') as $key => $val) {

			$g_table_data = new adminDB_DataRead(TABLE_PRODUCTS_PRICE_GROUP.$val['id'], null, null, 'id', ' products_id='.$ID);
			$g_data = $g_table_data->getData();

			$g_count = count($g_data);

			for ($i = 0; $i < $g_count; $i++) {
				unset($g_data[$i]['id']);
				$g_data[$i]['products_id'] = $obj->new_id;
	       		$oG = new adminDB_DataSave(TABLE_PRODUCTS_PRICE_GROUP.$val['id'], $g_data[$i], false, __CLASS__);
	        	$objG2P = $oG->saveDataSet();
		    }
	    }
        
        // more images 
        $mi = $db->Execute(
			"SELECT * FROM ".TABLE_MEDIA_LINK." WHERE link_id=? and class='product'",
			array($old_product)
		);
        while (!$mi->EOF) {
            $mi->fields['link_id'] = $obj->new_id; 
            unset($mi->fields['ml_id']);
            $oG = new adminDB_DataSave(TABLE_MEDIA_LINK, $mi->fields, false, __CLASS__);
            $objG2P = $oG->saveDataSet();         
            $mi->MoveNext();
        }       

	    ($plugin_code = $xtPlugin->PluginCode('class.product.php:_copy_bottom')) ? eval($plugin_code) : false;
	    
		$obj = new stdClass;
		$obj->new_pID = $objP->new_id;
		$obj->success = true;
		return $obj;
	}


	function _unset($pID) {

		$ID=(int)$pID;
		if (!is_int($ID)) return false;

		if(_SYSTEM_SIMPLE_GROUP_PERMISSIONS!='true'){
			$set_perm = new item_permission($this->perm_array);
			$set_perm->_deleteData($pID);
		}

		$this->_delete($pID);
		$obj = new stdClass;
		$obj->success = true;
		return $obj;
	}

	function build_price($id, $pprice, $tax_class='', $type='show'){
		global $price;

		if(!$tax_class)
		$tax_class = $price->getTaxClass('products_tax_class_id', TABLE_PRODUCTS, 'products_id', $id);

		$pprice = $price->_BuildPrice($pprice, $tax_class, $type);
		return $pprice;
	}
        
	// get only permitted files
	public function _getPermittedMediaData($mediaData)
	{
		if (!is_array($mediaData)) return;
			include_once(_SRV_WEBROOT.'xtFramework/classes/class.download.php');
			$download = new download();
			$permMedia = array();
			foreach($mediaData as $item) {
				if ($download->checkDownloadPermission($item['m_id'], 'free')) {
					$permMedia[] = $item;
				}
			}
			return $permMedia;
	}

}
