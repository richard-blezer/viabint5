<?php
require_once(dirname(__FILE__).'/../shopgate_library/shopgate.php');
require_once(dirname(__FILE__).'/constants.php');

class ShopgatePluginVeyton extends ShopgatePlugin {
	
	/**
	 * @var ShopgateConfigVeyton
	 */
	protected $config;
	
	protected $shopId = 0;

	protected $permissionBlacklist = true;

	protected $orderShippingApprovedStatusId = 33;

	protected $maxCategoriesOrder = 0;
	
	public function startup() {
		// Got your own config? Load it here . . .
		include_once('class.shopgate_config_veyton.php');
		$this->config = new ShopgateConfigVeyton();
		$this->config->loadConfigFromDatabase();
		
		$this->permissionBlacklist = _SYSTEM_GROUP_PERMISSIONS == 'blacklist';
		$this->shopId = $this->config->getPluginShopId();
	}

	/**
	 * Check if a plugin is installed and active by pluginCode
	 *
	 * See `xt_plugin_products`.`code`
	 *
	 * Return true if plugin is available and active
	 * otherwise false
	 *
	 * @param string $pluginCode
	 * @param string $minVersion lowest supported version. usually something like: '2.0.0'
	 * @return bool
	 */
	protected function checkPlugin($pluginCode, $minVersion = null ) {
		global $db;
		
		$this->log("execute checkPlugin() ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$plugin = $db->Execute("SELECT * FROM ".TABLE_PLUGIN_PRODUCTS." WHERE UPPER(code) = UPPER('{$pluginCode}') LIMIT 1;");
		
		// plugin found?
		if (empty($plugin->fields)) {
			return false;
		}
		
		// version okay?
		if (!empty($minVersion) && !version_compare($plugin->fields['version'], $minVersion, '>=')) {
			return false;
		}
		
		// active?
		return $plugin->fields["plugin_status"];
	}
	
	/**
	 * Requests the Shopgate Merchant API to update the status of an order
	 *
	 * This one gets called at the "class.order.php:_updateOrderStatus_bottom" hookpoint. It
	 * checks the status of the order at Veyton. If it's completed, a request to Shopgate's
	 * Merchant API is sent and in case of success, a note is added to the Veyton order.
	 *
	 * @param mixed[] $data Information about the order.
	 * @return bool false if there was an error, true otherwise
	 */
	public function updateOrderStatus($data) {
		global $db;

		// if the shipping is not completet yet just return:
		if ($data["orders_status_id"] != $this->config->getOrderStatusShipped()) {
			return true;
		}

		// get the order from the database
		$shopgateOrder = $db->Execute("SELECT * FROM ".TABLE_SHOPGATE_ORDERS." WHERE orders_id = {$data["orders_id"]} LIMIT 1;");
		if (empty($shopgateOrder->fields)) {
			// This is not a Shopgate-Order
			return true;
		}
		
		return $this->setOrderShippingCompleted($shopgateOrder->fields['shopgate_order_number'], $data['orders_id']);
	}

	////////////////////////////////////////////////////////////////////////////
	// Items CSV Export
	////////////////////////////////////////////////////////////////////////////

	/**
	 * (non-PHPdoc)
	 * @see lib/ShopgatePlugin::createItemsCsv()
	 */
	protected function createItemsCsv() {
		$this->log("Start export items...", ShopgateLogger::LOGTYPE_DEBUG);
		global $db, $language, $price, $currency, $product;
		
		$this->log("execute SQL seo urls...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$qry = "SELECT
			seo.url_text,
			seo.link_id,
			seo.link_type
			FROM ".TABLE_SEO_URL." seo
			WHERE seo.language_code = '".$language->code."' AND seo.link_type = 1
		";
		
		$seoUrlsResult = $db->Execute($qry);

		$seoUrls = array();
		while(!empty($seoUrlsResult) && !$seoUrlsResult->EOF) {
			$seoUrl = $seoUrlsResult->fields;
			
			$seoUrls[$seoUrl['link_id']] = trim(_SYSTEM_BASE_HTTP._SRV_WEB, '/').'/'.$seoUrl['url_text'].(_SYSTEM_SEO_FILE_TYPE !== "" ? '.'._SYSTEM_SEO_FILE_TYPE : '');
			$seoUrlsResult->MoveNext();
		}

		$this->log("build SQL for getting all products...", ShopgateLogger::LOGTYPE_DEBUG);
		
		// default fieldlist
		$productsFieldList = array(
			'p.products_id',
			'p.products_model',
			'pd.products_name',
			'pd.products_description',
			'pd.products_short_description',
			'pd.products_keywords',
			'p.products_ean',
			'p.products_quantity',
			'p.products_average_quantity',
			'p.products_price',
			'p.products_image',
			'p.products_startpage',
			'p.products_startpage_sort',
			'p.last_modified',
			'p.products_weight',
			'p.products_status',
			'm.manufacturers_name',
			'ssd1.status_name AS shipping_status',
			'ssd2.status_name AS vpe_status',
			'p.products_tax_class_id',
			'p.products_vpe_status',
			'p.products_vpe',
			'p.products_vpe_value',
			'p.products_sort',
			'p.products_fsk18',
		);
		
		if($this->checkPlugin('xt_product_options', '2.4.0')){
			$productsFieldList[] = 'products_options_flag';
			$productsFieldList[] = 'products_options_active_flag';
		}
		
		if($this->checkPlugin('xt_master_slave')){
			$productsFieldList[] = 'p.products_master_flag';
			
		}
		
		// find products
		$qry = "SELECT ".implode(",\n", $productsFieldList);
		
		$qry .= "
			FROM ".TABLE_PRODUCTS." p
			
			JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON pd.products_id = p.products_id
			LEFT JOIN ".TABLE_MANUFACTURERS." m ON (m.manufacturers_id = p.manufacturers_id AND m.manufacturers_status = 1)
			LEFT JOIN ".TABLE_SYSTEM_STATUS_DESCRIPTION." ssd1 ON (p.products_shippingtime = ssd1.status_id AND ssd1.language_code = '".$language->code."')
			LEFT JOIN ".TABLE_SYSTEM_STATUS_DESCRIPTION." ssd2 ON (p.products_vpe = ssd2.status_id AND ssd2.language_code = '".$language->code."')
			LEFT JOIN ".TABLE_PRODUCTS_PERMISSION." pm ON (pm.pid = p.products_id AND pm.pgroup = 'shop_".$this->shopId."')
			
			WHERE ( p.products_digital = 0 OR p.products_digital IS NULL )
				AND p.products_status = 1
				AND (p.date_available < NOW() OR p.date_available IS NULL)
				".($this->checkPlugin('xt_master_slave') ? " AND (p.products_master_model = '' OR p.products_master_model IS NULL)" : "")."
				AND pd.language_code = '".$language->code."'
		";

		if($this->permissionBlacklist) {
			$qry .= "AND (pm.permission IS NULL OR pm.permission = 0)";
		} else {
			$qry .= "AND (pm.permission IS NOT NULL AND pm.permission = 1)";
		}

		$this->log("execute SQL products_sort ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$this->maxProductsSort = $db->GetOne("SELECT MAX(products_sort) FROM ".TABLE_PRODUCTS);
		$this->maxProductsSort += 10000;

		if($this->splittedExport) {
			$qry .= " LIMIT {$this->exportOffset}, {$this->exportLimit}";
		}

		$this->log("execute SQL get products ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$productsResult = $db->Execute($qry);

		$this->log("execute SQL system status ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		// Get average quantity status rules & descriptions ( array(<trigger-percentage> => <status-name>) )
		$qry = "
			SELECT
				ss.status_values,
				ssd.status_name
			FROM ".TABLE_SYSTEM_STATUS." ss
			JOIN ".TABLE_SYSTEM_STATUS_DESCRIPTION." ssd
				ON ((ss.status_id = ssd.status_id) AND (ssd.language_code = '".$language->code."'))
			WHERE ss.status_class = 'stock_rule'";

		$rulesResult = $db->Execute($qry);
		$rules = array();
		while (!empty($rulesResult) && !$rulesResult->EOF) {
			$rule = $rulesResult->fields;
			$trigger = unserialize($rule['status_values']);
			$rules[(int) $trigger['data']['percentage']] = $rule['status_name'];
			$rulesResult->MoveNext();
		}
		ksort($rules);

		while(!empty($productsResult) && !$productsResult->EOF) {
			$this->log("start export products_id = ".$productsResult->fields["products_id"]." ...", ShopgateLogger::LOGTYPE_DEBUG);
			
			$items = $this->_buildItem($productsResult->fields, $rules, $seoUrls);

			if(is_array($items)) {
				foreach($items as $item) {
					$this->addItemRow($item);
				}
			}

			$productsResult->MoveNext();
		}
	}

	/**
	 *
	 * @param array $item
	 */
	private function _buildItem($item, $statusRules = array(), $seoUrls = array()) {
		global $db, $language, $price, $currency, $product;
		
		$this->log("execute _buildItem() ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$categories = $this->_getCategories($item);
		
		$images = $this->_getProductImages($item);

		$priceData = $price->buildPriceData($item["products_price"], $item["products_tax_class_id"]);

		$unitAmount = $priceData["plain"];
		
		/// Staffelpreise
		///
		/// Staffelpreise werden ignoriert, wenn ein Sonderpreis gegeben ist
		///
		$_groupPrices = $this->_getGroupPrices($item["products_id"]);
		$groupPrices = array();

		foreach($_groupPrices as $qty => $_price) {
			$_price = $_price * ( 1 + $priceData["tax_rate"] / 100 );
			$_price = $this->formatPriceNumber($_price);
			$groupPrices[] = $qty . "=>" . $_price;
		}
		$groupPrices = implode("||", $groupPrices);

		$amount_info_text = null;
		if(!empty($item["products_vpe_status"]) && $item["products_vpe_value"] != 0) {
			$amount_info_text = $priceData["plain"] / $item["products_vpe_value"];
			$amount_info_text = round($amount_info_text, 2);
			$amount_info_text = $price->_StyleFormat($amount_info_text);
			$amount_info_text = "$amount_info_text pro ".$item["vpe_status"];

			if( $item["vpe_status"] == "Ball" ) {
				$amount_info_text = round($item["products_vpe_value"], 0) . " Stück /" . $amount_info_text;
			}
		}

		if( !empty ( $_groupPrices ) ) {
			$_price = array_shift( $_groupPrices );
			$unitAmount = $_price * ( 1 + $priceData["tax_rate"] / 100 );
		}
		
		$properties = array();
		$specialPrice = $this->_getSpecialPrice($item["products_id"]);
		$oldPriceData = null;
		if($specialPrice != "na") {
			$specialPrice = $price->buildPriceData($specialPrice, $item["products_tax_class_id"]);

			if($specialPrice["plain"] < $priceData["plain"]) {
				$properties[] = "Angebot=>Statt {$priceData['formated']} nur {$specialPrice['formated']}";
				$oldPriceData = $priceData;
				$priceData = $specialPrice;
			}
			
			$groupPrices = ""; // Ignoriere Staffelpreise
			$unitAmount = $priceData["plain"];
		}

		if ($item['products_quantity'] < $item['products_average_quantity']) {
			if($item['products_average_quantity'] == 0){
				$realPercentage = 0;
			} else {
				$realPercentage = (int) ($item['products_quantity'] / $item['products_average_quantity'] * 100);
			}
			
			if ($realPercentage >= 0) {
				$selectedDescription = '';
				foreach ($statusRules as $percentage => $description) {
					if ($realPercentage >= $percentage) $selectedDescription = $description;
				}
			} else {
				if (isset($statusRules[0])) $selectedDescription = $statusRules[0];
			}

			$properties[] = 'Verfügbarkeit=>'.$selectedDescription;
		}

		$catSort = array();
		$_categoryNumbers = $this->_getProductCategoryIds($item["products_id"], $catSort);
		foreach ($_categoryNumbers as &$catNumber) {
			$sort = $item["products_sort"];
			if(isset($catSort[$catNumber]) && strtolower($catSort[$catNumber]["order"]) == "asc")
				$sort = $this->maxProductsSort - $sort;

			$catNumber = $catNumber . "=>" . $sort;
		}

		switch($this->config->getExportDescriptionType()) {
			case ShopgateConfigVeyton::EXPORT_DESCRIPTION:
				$productsDescription = $item["products_description"];
				break;
			case ShopgateConfigVeyton::EXPORT_SHORTDESCRIPTION:
				$productsDescription = $item["products_short_description"];
				break;
			case ShopgateConfigVeyton::EXPORT_DESCRIPTION_SHORTDESCRIPTION:
				$productsDescription = $item["products_description"]."<br/><br/>".$item["products_short_description"];
				break;
			case ShopgateConfigVeyton::EXPORT_SHORTDESCRIPTION_DESCRIPTION:
				$productsDescription = $item["products_short_description"]."<br/><br/>".$item["products_description"];
				break;
			default:
				$productsDescription = $item["products_description"];
				break;
		}
		
		$itemArr = $this->buildDefaultItemRow();
		$itemArr['item_number']					= $item["products_id"];
		$itemArr['item_number_public']			= $item['products_model'];
		$itemArr['manufacturer'] 				= $item["manufacturers_name"];
		$itemArr['item_name'] 					= $item["products_name"];
		$itemArr['description'] 				= $productsDescription;
		$itemArr['unit_amount'] 				= $this->formatPriceNumber($unitAmount, 2);
		$itemArr['available_text'] 				= $item["shipping_status"];
		$itemArr['urls_images'] 				= $images;
		$itemArr['use_stock']					= _STORE_STOCK_CHECK_BUY == "false" ? "1" : "0";
		$itemArr['categories'] 					= $categories;
		$itemArr['category_numbers']			= implode("||", $_categoryNumbers);
		$itemArr['basic_price'] 				= $amount_info_text;
		$itemArr['weight'] 						= (empty($item["products_weight"])?0:$item["products_weight"]*1000);
		$itemArr['tags'] 						= trim($item["products_keywords"]);
		$itemArr['currency'] 					= $currency->code;
		$itemArr['tax_percent'] 				= $priceData["tax_rate"];
		$itemArr['ean'] 						= $item["products_ean"];
		$itemArr['last_update'] 				= $item["last_modified"];
		$itemArr['properties']	 				= $properties;
		$itemArr['stock_quantity']		 		= $item["products_quantity"];
		$itemArr['block_pricing'] 				= $groupPrices;
		$itemArr['age_rating'] 					= $item["products_fsk18"] == 1 ? '18' : '';
		$itemArr['related_shop_item_numbers']	= $this->_getRelatedShopItems($item["products_id"]);
		$itemArr["is_highlight"]				= $item["products_startpage"];
		$itemArr["highlight_order_index"]		= $item["products_startpage_sort"];

		if(_SYSTEM_MOD_REWRITE == "true" && isset($seoUrls[$item["products_id"]])){
			$itemArr['url_deeplink'] = $seoUrls[$item["products_id"]];
		} else {
			$itemArr['url_deeplink'] = _SYSTEM_BASE_HTTP._SRV_WEB."index.php?page=product&info=".$item["products_id"];
		}

		$itemArr = $this->itemExportInternalOrderInfo($itemArr, $item);
		// add internal order info
		$itemArr = $this->itemExportAdditionalShippingCostsPerUnit($itemArr, $item);

		// BuI Hinsche GmbH refund system:
		$itemArr = $this->itemExportAddRefund($itemArr, $item);
		
		if (!empty($oldPriceData)) {
			$itemArr['old_unit_amount'] = $this->formatPriceNumber($oldPriceData["plain"], 2);
		}

		// convert internal order info and properties into Shopgate CSV spec
		$itemArr['internal_order_info'] = json_encode($itemArr['internal_order_info']);
		$itemArr['properties'] = implode('||', $itemArr['properties']);

		if ($this->checkPlugin('xt_product_options', '2.4.0') && $item['products_options_flag'] == 1
			&& $item['products_options_active_flag'] == 1 && ($this->checkPlugin('xt_master_slave') && $item['products_master_flag'] == 1)) {
			// we don't support xt_product_options with master/slave products
			return;
		} else if ($this->checkPlugin('xt_product_options', '2.4.0') && $item['products_options_flag'] == 1
			&& $item['products_options_active_flag'] == 1 && (!$this->checkPlugin('xt_master_slave') || $item['products_master_flag'] == 0)) {
			$options = $this->_getOptionValues($item);
			$i = 1;
			if (is_array($options['options'])){
				foreach ($options['options'] as $option_key => $values) {
					$opts = array();
					foreach ($values["values"] as $value) {
						$opts[] = $value["value_id"] . "=" . $value["value"] . "=>" . $value["price_offset"];
					}

					$itemArr["has_options"] = "1";
					$itemArr["option_".$i] = $values["group"]["group_id"] . "=" . $values["group"]["group_name"];
					$itemArr["option_".$i."_values"] = implode("||", $opts);
					$i++;
				}
			}
			
			$i = 1;
			if (is_array($options['inputs'])){
				foreach ($options['inputs'] as $ivalue) {
					$itemArr["input_field_".$i."_type"] = "text";
					$itemArr["input_field_".$i."_number"] = $ivalue["group_id"].'_'.$ivalue["value_id"];
					$itemArr["input_field_".$i."_label"] = $ivalue["group_name"].': '.$ivalue["value_name"];
					$itemArr["input_field_".$i."_infotext"] = '';
					$itemArr["input_field_".$i."_required"] = $ivalue["required"];
					$itemArr["input_field_".$i."_add_amount"] = $ivalue["price_offset"];
					$itemArr["has_input_fields"] = "1";
					$i++;
				}
			}

			$items = array($itemArr);
		} else {
			$variations = array();
			if($this->checkPlugin('xt_master_slave') && $item["products_master_flag"] == "1" && !empty($item["products_model"]) ){
				$variations = $this->_getVariations($item, $itemArr, $seoUrls);
				
				if(empty($variations)){
					$itemArr["active_status"] = self::PRODUCT_STATUS_ACTIVE;
					$itemArr["use_stock"] = 1;
					$itemArr["stock_quantity"] = 0;
				}
			}
			$items = array_merge(array($itemArr), $variations);
		}
		
		return $items;
	}
	
	protected function itemExportInternalOrderInfo($itemArr, $item) {
		global $currency;
		
		$this->log("execute itemExportInternalOrderInfo() ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$infos = array();
		$itemArr['internal_order_info']['exchange_rate'] = $currency->value_multiplicator;
		
		if ($this->checkPlugin('xt_sperrgut')) {
			$bulk = $this->getXtSperrgut($item['products_id']);
			$itemArr['internal_order_info']['xt_sperrgut'] = $bulk;
		}
		
		return $itemArr;
	}
	
	protected function itemExportAdditionalShippingCostsPerUnit($item, $product) {
		global $db, $price;
		
		$this->log("execute itemExportAdditionalShippingCostsPerUnit() ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$additionalShipping = 0;
		
		if( $this->checkPlugin("xt_sperrgut") ) {
			$additionalShipping = $this->getXtSperrgut( $product["products_id"] );
		}
		
		$item["additional_shipping_costs_per_unit"] = $this->formatPriceNumber( $additionalShipping );
		
		return $item;
	}
	
	/**
	 * Adds refund pricing to the product if the plugin is enabled.
	 *
	 * @param mixed[] $itemArr The item array to be modified for export.
	 * @param mixed[] $item The item array fetched from the database.
	 * @return mixed[] The modified item array for export
	 */
	protected function itemExportAddRefund($itemArr, $item) {
		return $itemArr;
	}
	
	/**
	 * Get the bulk shipping price for the product from the database
	 *
	 * Requires veyton plugin "xt_sperrgut"
	 *
	 * @param int $productId
	 * @return float
	 */
	protected function getXtSperrgut( $productId ) {
		global $db, $price;
		
		$this->log("execute getXtSperrgut() ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$sperrgut = 0;
		
		if( $this->checkPlugin("xt_sperrgut") ) {
			$qry = "
				SELECT
					s.price
				FROM `".DB_PREFIX."_sperrgut` s
				JOIN `".TABLE_PRODUCTS."` p ON ( p.xt_sperrgut_class = s.id )
				WHERE p.products_id = {$productId}
			";
				
			$bulk = $db->Execute($qry);
				
			if( $bulk->fields ) {
				$sperrgut = $bulk->fields["price"];
		
				$priceData = $price->buildPriceData($bulk->fields["price"], XT_SPERRGUT_TAX_CLASS);
				$sperrgut = $priceData["plain"];
			}
		}
		
		return $sperrgut;
	}

	private function _getOptionValues($item) {
		global $db, $price, $language;

		$this->log("execute _getOptionValues() ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$qry = "
		SELECT
			pto.products_id,
			pto.option_group_id,
			pov.option_value_id,
			pog.option_group_field,
			pov.option_value_field,
			pogd.option_group_name,
			povd.option_value_name,
			pto.option_required,
			pov.option_value_default_required,
			pto.option_price,
			pov.option_value_default_price,
			pto.option_p_prefix,
			pov.option_value_default_p_prefix
		FROM xt_plg_product_to_options pto
		JOIN xt_plg_product_option_groups pog ON (pog.option_group_id = pto.option_group_id AND pog.status = 1)
		JOIN xt_plg_product_option_groups_description pogd ON (pogd.option_group_id = pog.option_group_id AND pogd.language_code = '".$language->code."')
		JOIN xt_plg_product_option_values pov ON (pov.option_value_id = pto.option_value_id AND pov.status = 1)
		JOIN xt_plg_product_option_values_description povd ON (povd.language_code = '".$language->code."' AND povd.option_value_id = pto.option_value_id)
		WHERE pto.products_id = ".$item['products_id']."  AND pto.option_status = 1
		ORDER BY pto.option_group_id, pog.sort_order, pto.option_value_id, pov.sort_order
		";

		$result = $db->Execute($qry);

		/**
		 * [<group_id>][n] => array(
		 *   "value_id" => <value_id>,
		 *   "value" => "<VALUE>",
		 *   "price_offset" => <Price>
		 * )
		 */
		$options = array();

		while(!empty($result) && !$result->EOF) {

			$option_field = ($result->fields['option_value_field'])?$result->fields['option_value_field']:$result->fields['option_group_field'];
			$option_required = ($result->fields['option_required'])?$result->fields['option_required']:$result->fields['option_value_default_required'];
			$option_price = $price->_BuildPrice(($result->fields['option_price'])?$result->fields['option_price']:$result->fields['option_value_default_price'], $item["products_tax_class_id"]);
			$option_p_prefix = ($result->fields['option_p_prefix'])?$result->fields['option_p_prefix']:$result->fields['option_value_default_p_prefix'];

			$oprice = "";
			if ($option_p_prefix == "-") {
				$oprice = "-";
			}
			$oprice .= ((float) $option_price * 100);
			
			if(in_array($option_field, array('select', 'radio'))){

				if(!isset($options["options"][$result->fields["option_group_id"]]))
				$options["options"][$result->fields["option_group_id"]] = array("group" => array(), "values" => array());
				
				$options["options"][$result->fields["option_group_id"]]["group"] = array(
					"group_id" => $result->fields["option_group_id"],
					"group_name" => $result->fields["option_group_name"],
				);

				$options["options"][$result->fields["option_group_id"]]["values"][] = array(
					"value_id" => $result->fields["option_value_id"],
					"value" => $result->fields["option_value_name"],
			 		"price_offset" => $this->formatPriceNumber($oprice, 0),
				);

			}
			
			if(in_array($option_field, array('input', 'text'))){

				$options["inputs"][] = array(
					"group_id" => $result->fields["option_group_id"],
					"group_name" => $result->fields["option_group_name"],
					"value_id" => $result->fields["option_value_id"],
					"value_name" => $result->fields["option_value_name"],
			 		"price_offset" => $this->formatPriceNumber($oprice, 0),
			 		"required" => ($option_required === 'true')?true:'',
				);
			}
			
			if($option_field == 'checkbox'){

				if (!isset($options["options"][$result->fields["option_group_id"].'_'.$result->fields["option_value_id"]])) {
					$options["options"][$result->fields["option_group_id"].'_'.$result->fields["option_value_id"]] = array("group" => array(), "values" => array());
				}

				$options["options"][$result->fields["option_group_id"].'_'.$result->fields["option_value_id"]]["group"] = array(
					"group_id" => $result->fields["option_group_id"].'_'.$result->fields["option_value_id"],
					"group_name" => $result->fields["option_group_name"].': '.$result->fields["option_value_name"],
				);
					
				$options["options"][$result->fields["option_group_id"].'_'.$result->fields["option_value_id"]]["values"] = array(array(
					"value_id" => 0,
					"value" => 'nein',
			 		"price_offset" => $this->formatPriceNumber(0, 0),
			 		), array(
					"value_id" => 1,
					"value" => 'ja',
			 		"price_offset" => $this->formatPriceNumber($oprice, 0),
			 		)
				);
			}
			
			$result->MoveNext();
		}
		
		return $options;
	}

	private function _getRelatedShopItems($products_id) {
		global $db;

		$this->log("execute _getRelatedShopItems() ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$qry = "
			SELECT p.products_id
			FROM ".TABLE_PRODUCTS_CROSS_SELL." xsell
			JOIN ".TABLE_PRODUCTS." p ON p.products_id = xsell.products_id_cross_sell
			JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." pcat ON pcat.products_id = p.products_id
			LEFT JOIN ".TABLE_PRODUCTS_PERMISSION." pm ON (pm.pid = p.products_id AND pm.pgroup = 'shop_".$this->shopId."')
			WHERE xsell.products_id = $products_id
			  AND ( p.products_digital = 0 OR p.products_digital IS NULL )
			  AND p.products_status = 1
			  AND (p.date_available < NOW() OR p.date_available IS NULL)
			  ".($this->checkPlugin('xt_master_slave') ? " AND (p.products_master_model = '' OR p.products_master_model IS NULL)" : "");

		if($this->permissionBlacklist) {
			$qry .= "AND (pm.permission IS NULL OR pm.permission = 0)";
		} else {
			$qry .= "AND (pm.permission IS NOT NULL AND pm.permission = 1)";
		}

		$result = $db->Execute($qry);

		$xSellProductsNumbers = array();

		while(!$result->EOF) {
			$xSellProductsNumbers[] = $result->fields["products_id"];

			$result->MoveNext();
		}

		return implode("||", $xSellProductsNumbers);
	}

	/**
	 * Findet den Aktuellen Sonderpreis und liefert diesen zurück.
	 * Wird kein Sonderpreis gefunden, wird 'na' zurück gegeben.
	 *
	 * @param int $productId
	 * @return int
	 */
	private function _getSpecialPrice($productId) {
		global $db;
		
		$this->log("execute _getSpecialPrice() ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$qry = "
			SELECT *
			FROM ".TABLE_PRODUCTS_PRICE_SPECIAL."
			WHERE status = 1
			  AND products_id  = $productId
			  AND group_permission_all = 1
			  AND (date_available < NOW())
			  AND (date_expired > NOW())
		";
		$result = $db->Execute($qry);

		$specialPrice = "na";

		while(!$result->EOF) {
			$price = $result->fields["specials_price"];
			if($specialPrice == "na" || $specialPrice > $price)
			$specialPrice = $price;

			$result->MoveNext();
		}

		return $specialPrice;
	}

	/**
	 * Liefert die Staffelpreise eines Artikels
	 *
	 * @param int $productsId
	 * @return array
	 */
	private function _getGroupPrices($productsId) {
		global $db;
		
		$this->log("execute _getGroupPrices() ...", ShopgateLogger::LOGTYPE_DEBUG);

		if(DB_PREFIX!=''){
			$DB_PREFIX = DB_PREFIX . '_';
		}else{
			define('DB_PREFIX','xt');
			$DB_PREFIX = DB_PREFIX . '_';
		}

		$qry = "
			SELECT *
			FROM ".$DB_PREFIX."products_price_group_all
			WHERE products_id = $productsId
			ORDER BY discount_quantity ASC
		";
		$result = $db->Execute($qry);

		$groupPrices = array();

		while(!$result->EOF) {
			$gPrice = $result->fields;

			$groupPrices[$gPrice["discount_quantity"]] = $gPrice["price"];

			$result->MoveNext();
		}

		return $groupPrices;
	}

	/**
	 * Alle Bilder zum angegebenen Artikel holen
	 *
	 * @param array $item
	 * @return array
	 */
	private function _getProductImages($item) {
		$this->log("execute _getProductImages() ...", ShopgateLogger::LOGTYPE_DEBUG);
		$images = array();

		if (!empty($item["products_image"]))
			$this->_addImage($item["products_image"], $images);

		global $db;

		$qry = "
			SELECT
			m.file
			FROM ".TABLE_MEDIA_LINK." ml
			JOIN ".TABLE_MEDIA." m ON ml.m_id = m.id
			WHERE ml.class = 'product'
			  AND ml.type='images'
			  AND ml.link_id = '".$item["products_id"]."'
			ORDER BY ml.sort_order
		";

		$result = $db->Execute($qry);
		while (!$result->EOF) {
			$this->_addImage($result->fields["file"], $images);

			$result->MoveNext();
		}

		return implode("||", $images);
	}

	private function _addImage($image, &$stack) {
		$webPrefix = _SYSTEM_BASE_HTTP._SRV_WEB._SRV_WEB_IMAGES;
		$imgSrc = _SRV_WEBROOT . "/" . _SRV_WEB_IMAGES;

		if (file_exists($imgSrc."org/".$image)) {
			$stack[] = $webPrefix."org/".$image;

		} else if (file_exists($imgSrc."popup/".$image)) {
			$stack[] = $webPrefix."popup/".$image;

		} else if (file_exists($imgSrc."info/".$image)) {
			$stack[] = $webPrefix."info/".$image;

		} else if (file_exists($imgSrc."thumb/".$image)) {
			$stack[] = $webPrefix."thumb/".$image;

		}
	}

	public function _getProductCategoryIds($productId, &$catSort = array()) {
		global $db;
		$this->log("execute _getProductCategoryIds() ...", ShopgateLogger::LOGTYPE_DEBUG);

		$qry = "
			SELECT
			ptc.categories_id,
			p.products_sorting,
			p.products_sorting2
			FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
			JOIN " . TABLE_CATEGORIES . " p ON (ptc.categories_id = p.categories_id)
			LEFT JOIN ". TABLE_CATEGORIES_PERMISSION ." cm ON (cm.pid = ptc.categories_id AND cm.pgroup = 'shop_".$this->shopId."')
			WHERE ptc.products_id = ".$productId."
		";

		if($this->permissionBlacklist) {
			$qry .= "AND (cm.permission IS NULL OR cm.permission = 0)";
		} else {
			$qry .= "AND (cm.permission IS NOT NULL AND cm.permission = 1)";
		}
		$result = $db->Execute($qry);

		$catIds = array();
		while(!$result->EOF) {
			$catIds[] = $result->fields["categories_id"];

			$catSort[$result->fields["categories_id"]] = array(
				"field" => $result->fields["products_sorting"],
				"order" => $result->fields["products_sorting2"],
			);

			$result->MoveNext();
		}

		return $catIds;
	}

	/**
	 * Gibt den Kategoriebaum eines Produkts zurück
	 *
	 * Wenn in einem Pfad eine Kategorie dekativiert ist, wird der gesamte Pfad ignoriert!
	 *
	 * @param int $item
	 * @return string
	 */
	private function _getCategories($item) {
		$this->log("execute _getCategories() ...", ShopgateLogger::LOGTYPE_DEBUG);
		$catIds = $this->_getProductCategoryIds($item["products_id"]);
		$path = array();

		foreach($catIds as $catId) {
			$_path = $this->_getCategoryPaths($catId);

			if(!empty($_path))
			$path[] = $_path;
		}
		$path = implode("||", $path);

		return $path;
	}

	/**
	 * Liefert den Pfad zur angegebenen Kategorie.
	 *
	 * ACHTUNG: Rekursion!!
	 *
	 * @param int $catId
	 * @return string
	 */
	private function _getCategoryPaths($catId) {
		global $db, $language;
		
		$this->log("execute _getCategoryPaths() catId = ".$catId." ...", ShopgateLogger::LOGTYPE_DEBUG);
		$path = "";

		$qry = "
			SELECT
				c.categories_id,
				c.parent_id,
				cd.categories_name,
				c.categories_status
			FROM ".TABLE_CATEGORIES." c
			JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd ON c.categories_id = cd.categories_id
			LEFT JOIN ". TABLE_CATEGORIES_PERMISSION ." cm ON (cm.pid = c.categories_id AND cm.pgroup = 'shop_".$this->shopId."')
			WHERE cd.language_code = '".$language->code."'
			  AND c.categories_id = $catId
		";

		if($this->permissionBlacklist) {
			$qry .= "AND (cm.permission IS NULL OR cm.permission = 0)";
		} else {
			$qry .= "AND (cm.permission IS NOT NULL AND cm.permission = 1)";
		}

		$result = $db->GetRow($qry);

		$path = $result["categories_name"];
		if(!empty($result["parent_id"]) && $result["parent_id"] != $catId) {
			$_path = $this->_getCategoryPaths($result["parent_id"]);
			if(empty($_path)) return "";


			$path = $_path.(!empty($path)?"=>":"").$path;
		}

		return $path;
	}

	private function _getVariations($item, &$parent, $seoUrls = array()) {
		global $db, $language;

		$this->log("execute _getVariations() ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		// default fieldlist
		$productsFieldList = array(
			'p.products_id',
			'p.products_model',
			'p.products_master_model',
			'pd.products_name',
			'pd.products_description',
			'pd.products_keywords',
			'p.products_ean',
			'p.products_quantity',
			'p.products_average_quantity',
			'p.products_price',
			'p.products_image',
			'p.last_modified',
			'p.products_weight',
			'p.products_status',
			'm.manufacturers_name',
			'ssd1.status_name AS shipping_status',
			'ssd2.status_name AS vpe_status',
			'p.products_tax_class_id',
			'p.products_vpe_status',
			'p.products_vpe',
			'p.products_vpe_value',
			'p.products_sort',
			'p.products_fsk18',
		);
		
		$qry = "
			SELECT ".implode(",\n", $productsFieldList)."
			
			FROM ".TABLE_PRODUCTS." p
			
			LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON pd.products_id = p.products_id
			LEFT JOIN ".TABLE_MANUFACTURERS." m ON m.manufacturers_id = p.manufacturers_id
			LEFT JOIN ".TABLE_SYSTEM_STATUS_DESCRIPTION." ssd1 ON (p.products_shippingtime = ssd1.status_id AND ssd1.language_code = '".$language->code."')
			LEFT JOIN ".TABLE_SYSTEM_STATUS_DESCRIPTION." ssd2 ON (p.products_vpe = ssd2.status_id AND ssd2.language_code = '".$language->code."')
			
			WHERE (p.products_master_flag = 0 OR p.products_master_flag IS NULL)
				AND (p.products_digital = 0 OR p.products_digital IS NULL)
				AND p.products_status = 1
				AND p.products_master_model = '".$item["products_model"]."'
				AND pd.language_code = '".$language->code."'
			ORDER BY p.products_sort
		";
		$result = $db->Execute($qry);
		$variations = array();

		while(!$result->EOF) {
			$i=1;
			$item = $result->fields;
			
			$itemArr = $this->_buildItem($item, array(), $seoUrls);
			$itemArr = $itemArr[0];

			if(empty($itemArr["block_pricing"]))
				$itemArr["block_pricing"] = $parent["block_pricing"];

			/* Wenn Kindprodukt kein eigenes Bild besitzt, nutze das Bild des Elternproduktes falls vorhanden */
			if(empty($itemArr["urls_images"]) && !empty($parent["urls_images"])){
				$itemArr["urls_images"] = $parent["urls_images"];
			}

			$itemArr["categories"] = $parent["categories"];
			$itemArr["parent_item_number"] = $parent["item_number"];

			$options = $this->_getOptions($item);

			foreach($options as $option) {
				$parent["attribute_".$i] = html_entity_decode( $option["attributes_basename"] );
				$itemArr["attribute_".$i] = html_entity_decode( $option["attributes_name"] );
				$i++;
			}

			if(empty($options)) {
				$parent["attribute_".$i] = "Variante";
				$itemArr["attribute_".$i] = html_entity_decode( $item["products_name"] );
				$i++;
			}

			$parent["has_children"] = true;

			$variations[] = $itemArr;
			$result->MoveNext();
		}

		return $variations;
	}

	/**
	 * Laden Variationen
	 *
	 * In xt:Commerce 4 sind dies die Master/Slave Produkte
	 *
	 * @param array $item
	 */
	private function _getOptions($item) {
		global $db, $language;

		$qry = "
			SELECT DISTINCT
				a.attributes_id,
				a.attributes_parent,
				a.attributes_model,
				a.attributes_image,
				ad2.attributes_name AS attributes_basename,
				ad.attributes_name,
				ad.attributes_desc
				
			FROM ".DB_PREFIX."_plg_products_attributes a
			
			JOIN ".DB_PREFIX."_plg_products_attributes_description ad
				ON (ad.attributes_id = a.attributes_id AND ad.language_code = '".$language->code."')
				
			JOIN ".DB_PREFIX."_plg_products_to_attributes pta
				ON (
					pta.attributes_id = a.attributes_id AND
					pta.attributes_parent_id = a.attributes_parent
				)
				
			JOIN ".DB_PREFIX."_plg_products_attributes a2
				ON (
					(a2.attributes_parent = 0 OR a2.attributes_parent IS NULL) AND
					a2.attributes_id = a.attributes_parent
				)
				
			JOIN ".DB_PREFIX."_plg_products_attributes_description ad2
				ON (
					ad2.attributes_id = a2.attributes_id AND
					ad2.language_code = '".$language->code."'
				)
				
			LEFT JOIN ".DB_PREFIX."_plg_products_to_attributes pta2
				ON (
			 		pta2.attributes_id = a2.attributes_id AND
					pta2.attributes_parent_id = a2.attributes_parent
				)
				
			WHERE
				a.status = 1 AND
				pta.products_id = ".$item["products_id"]."
				
			ORDER BY a.attributes_parent, a.sort_order
		";
		
		$result = $db->Execute($qry);
		
		$variations = array();
		
		while(!$result->EOF) {
			$variations[] = $result->fields;
			$result->MoveNext();
		}
		
		return $variations;
	}

	protected function createReviewsCsv() {
		global $db;

		/* check if core-review-plugin is active */
		$qry = "
			SELECT
				plugin_status
			FROM ".DB_PREFIX."_plugin_products
			WHERE name = 'Reviews'
			AND type = 'core'
		";
		$result = $db->Execute($qry);

		if($result->fields['plugin_status'] != 1){
			/* plugin is not active -> throw Exception*/
			throw new Exception("Veyton ShopgatePlugin - Review-Plugin nicht aktiviert.");
		}

		$qry = "
			SELECT
				pr.review_rating,
				pr.review_id,
				pr.review_date,
				pr.review_text,
				pr.review_title,
				pr.products_id,
				ca.customers_firstname as firstname,
				ca.customers_lastname as lastname
			FROM ".DB_PREFIX."_products_reviews pr
			LEFT JOIN ".DB_PREFIX."_customers_addresses ca
			ON (pr.customers_id = ca.customers_id)
			WHERE pr.review_status = 1
		";

		$limit 	= 100;
		$offset = 0;

		do{
			$result = $db->Execute($qry." LIMIT $offset, $limit");

			while(!$result->EOF){
				$review = $this->buildDefaultReviewRow();
				$review["item_number"] 		= $result->fields["products_id"];
				$review["update_review_id"] = $result->fields["review_id"];
				$review["score"] 			= $result->fields["review_rating"] * 2;
				$review["name"] 			= $result->fields["firstname"]." ".$result->fields["lastname"];
				$review["date"] 			= $result->fields["review_date"];
				$review["title"] 			= $result->fields["review_title"];
				$review["text"] 			= stripcslashes($result->fields["review_text"]);

				$this->addItem($review);

				$result->MoveNext();
			}

			$offset += $limit;

		}while($result->RowCount() > 0);

	}

	protected function createPagesCsv() {
		//		foreach($pages as $page) {
		//			$itemArr = array();
		//			$itemArr["item_number"] = $page["item"];
		//			$itemArr["title"] 		= $page["head"];
		//			$itemArr["text"] 		= $page["text"];
		//
		//			$this->addItem($itemArr);
		//		}
	}

	public function getCustomer($user, $pass) {
		global $db, $language;

		// get customer data with or without addresses
		$qry = "
			SELECT
				customer.customers_id,
				customer.customers_cid,
				status.customers_status_id,
				status.customers_status_name,
				customer.customers_email_address

			FROM " . TABLE_CUSTOMERS ." AS customer

			JOIN " . TABLE_CUSTOMERS_STATUS . " AS s
				ON customer.customers_status = s.customers_status_id

			JOIN " . TABLE_CUSTOMERS_STATUS_DESCRIPTION . " AS status
				ON (s.customers_status_id = status.customers_status_id AND status.language_code = '".$language->code."')

			WHERE customers_email_address = '" . $user . "'
				AND customers_password = '".md5($pass)."'

			LIMIT 1
		";
		$result = $db->Execute($qry);

		// check for database errors
		if (empty($result) || !($result instanceof ADORecordSet)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR);
		}

		// if no record has been found the user name or password must be wrong
		if ($result->RowCount() < 1) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_WRONG_USERNAME_OR_PASSWORD);
		} else {
			$customerData = $result->fields;
		}

		// get the customer's address data
		$qry = "
			SELECT
				address.address_book_id,
				address.address_class,
				address.customers_gender,
				address.customers_firstname,
				address.customers_lastname,
				date_format(address.customers_dob, '%Y-%m-%d') as customers_birthday,
				address.customers_street_address,
				address.customers_suburb,
				address.customers_company,
				address.customers_postcode,
				address.customers_city,
				address.customers_country_code,
				address.customers_phone

			FROM " . TABLE_CUSTOMERS_ADDRESSES . " AS address

			WHERE address.customers_id = {$customerData['customers_id']}
		";
		$result = $db->Execute($qry);

		// check for database errors
		if (empty($result) || !($result instanceof ADORecordSet)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'No addresses found. Username: '.$user);
		}

		// check if addresses have been found (although getting the customer data should have failed then)
		if (empty($result) || $result->RowCount() < 1) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_NO_ADDRESSES_FOUND, 'Username: '.$user);
		} else {
			$addressDatasets = $result;
		}

		// build address objects list
		$addresses = array();
		$defaultAddress = null;
		$hasInvoice = false;
		$hasDelivery = false;
		while (!empty($addressDatasets) && !$addressDatasets->EOF) {
			$addressData = $addressDatasets->fields;

			try {
				// c = company, we don't use that
				if(!empty($addressData['customers_gender']) && $addressData['customers_gender'] == 'c'){
					$addressData['customers_gender'] = null;
				}

 				$address = new ShopgateAddress();
 				$address->setId($addressData['address_book_id']);
 				$address->setGender($addressData['customers_gender']);
				$address->setFirstName($addressData['customers_firstname']);
				$address->setLastName($addressData['customers_lastname']);
				$address->setBirthday($addressData['customers_birthday']);
				$address->setCompany($addressData['customers_company']);
				$address->setStreet1($addressData['customers_street_address']);
				$address->setStreet2($addressData['customers_suburb']);
				$address->setZipcode($addressData['customers_postcode']);
				$address->setCity($addressData['customers_city']);
				$address->setCountry($addressData['customers_country_code']);
				$address->setPhone($addressData['customers_phone']);
			} catch (ShopgateLibraryException $e) {
				// logging is done on exception construction
				continue;
			}

			// set address type
			switch (strtolower($addressData['address_class'])) {
				case 'default':
					$address->setAddressType(ShopgateAddress::BOTH);
					$defaultAddress = $address;
					array_unshift($addresses, $defaultAddress); // default address should be the first in line
				break;

				case 'payment':
					$address->setAddressType(ShopgateAddress::INVOICE);
					$addresses[] = $address;
				break;

				case 'shipping':
					$address->setAddressType(ShopgateAddress::DELIVERY);
					$addresses[] = $address;
				break;

				default:
					$addresses[] = $address;
			}

			$addressDatasets->moveNext();
		}

		// check for valid addresses (one invoice and one delivery at least)
		if (empty($addresses) || ((count($addresses) < 2) && empty($defaultAddress))) {
			$this->log(ShopgateLibraryException::buildLogMessageFor(ShopgateLibraryException::PLUGIN_NO_ADDRESSES_FOUND, 'User: '.$user));
		}

		// build customer object and return it
		try {
			$customer = new ShopgateCustomer();
			$customer->setCustomerId($customerData["customers_id"]);
			$customer->setCustomerNumber($customerData["customers_cid"]);
			$customer->setCustomerGroup($customerData["customers_status_name"]);
			$customer->setCustomerGroupId($customerData["customers_status_id"]);
			$customer->setGender($defaultAddress->getGender());
			$customer->setFirstName($defaultAddress->getFirstName());
			$customer->setLastName($defaultAddress->getLastName());
			$customer->setMail($customerData["customers_email_address"]);
			$customer->setPhone($defaultAddress->getPhone());
			$customer->setBirthday($defaultAddress->getBirthday());
			$customer->setAddresses($addresses);
		} catch (ShopgateLibraryException $e) {
			// Logging is done on exception construction but getCustomer() should not fail at this point
		}
		
		return $customer;
	}

	protected function createCategoriesCsv() {
		global $db, $language;

		$this->log("Start Export Categories...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$this->maxCategoriesOrder = $db->GetOne("SELECT MAX(sort_order) FROM ".TABLE_CATEGORIES);

		$this->log("Start Categories SEO-URL...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$qry = "SELECT
				seo.url_text,
				seo.link_id,
				seo.link_type
			FROM ".TABLE_SEO_URL." seo
			WHERE seo.language_code = '".$language->code."' AND seo.link_type = 2
		";
		$seoUrlsResult = $db->Execute($qry);
		
		$seoUrls = array();
		while(!empty($seoUrlsResult) && !$seoUrlsResult->EOF) {
			$seoUrl = $seoUrlsResult->fields;
			
			$seoUrls[$seoUrl['link_id']] = trim(_SYSTEM_BASE_HTTP._SRV_WEB, '/').'/'.$seoUrl['url_text'].(_SYSTEM_SEO_FILE_TYPE !== "" ? '.'._SYSTEM_SEO_FILE_TYPE : '');
			$seoUrlsResult->MoveNext();
		}
		
		$this->_buildCategoriesTree("0", $seoUrls);
		
		$this->log("Finish Export Categories...", ShopgateLogger::LOGTYPE_DEBUG);
	}

	private function _buildCategoriesTree($parentId = "0", $seoUrls = array()) {
		global $db, $language;

		$this->log("Start buldiding Categories tree: parent_id = ". $parentId ."...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$qry = "
			SELECT
			c.categories_id,
			c.parent_id,
			cd.categories_name,
			c.sort_order,
			c.categories_status,
			c.categories_image
			FROM ".TABLE_CATEGORIES." c
			JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd ON (c.categories_id = cd.categories_id)
			WHERE cd.language_code = '".$language->code."'
			  AND c.parent_id = $parentId
		";

		$webPrefix = _SYSTEM_BASE_HTTP._SRV_WEB._SRV_WEB_IMAGES."category/popup/";

		$results = $db->Execute($qry);
		while(!$results->EOF) {
			$item = $results->fields;

			$row = $this->buildDefaultCategoryRow();

			$row["category_number"] = $item["categories_id"];

			if (!empty($parentId) && ($parentId != $item['categories_id']))
				$row["parent_id"] = $parentId;

			$row["category_name"] = $item["categories_name"];

			if(!empty($item["categories_image"]))
				$row["url_image"] = $webPrefix.$item["categories_image"];

			$row["order_index"] = $this->maxCategoriesOrder - $item["sort_order"];
			$row["is_active"] = $item["categories_status"];

			if(_SYSTEM_MOD_REWRITE == "true" && isset($seoUrls[$item["categories_id"]])){
				$row["url_deeplink"] = $seoUrls[$item["categories_id"]];
			} else {
				$row["url_deeplink"] = _SYSTEM_BASE_HTTP._SRV_WEB."index.php?page=categorie&cat=".$item["categories_id"];
			}

			$this->addCategoryRow($row);

			if ($parentId != $item['categories_id']) {
				$this->_buildCategoriesTree($item["categories_id"], $seoUrls);
			}

			$results->MoveNext();
		}
	}

	public function createShopInfo(){
		global $db;

		$qry = "
			SELECT
				p.config_value
			FROM ".TABLE_CONFIGURATION." p
			Where p.config_key = '_SYSTEM_VERSION'
		";

		$result = $db->Execute($qry);

		$shopInfo['system_version'] = $result->fields;

		return $shopInfo;

	}
	////////////////////////////////////////////////////////////////////////////
	// Bestellabwicklung
	////////////////////////////////////////////////////////////////////////////
	public function addOrder(ShopgateOrder $order) {
		global $db, $language;

		$c = new countries();

		/* prüfe ob die Shopgate-Bestellung schon existiert */
		$qry = "SELECT * FROM ".TABLE_SHOPGATE_ORDERS." WHERE shopgate_order_number = '{$order->getOrderNumber()}'";
		$result = $db->Execute($qry);
		if ($result->RecordCount() > 0) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DUPLICATE_ORDER, "Shopgate order number: '{$order->getOrderNumber()}'.");
		}

		$deliveryAddress = $order->getDeliveryAddress();
		$billingAddress = $order->getInvoiceAddress();
		
		// check country is active in shop
		if(!isset($c->countries_list[$deliveryAddress->getCountry()])){
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_UNKNOWN_COUNTRY_CODE, "'{$deliveryAddress->getCountry()}' not active in shop", true);
		}
		if(!isset($c->countries_list[$billingAddress->getCountry()])){
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_UNKNOWN_COUNTRY_CODE, "'{$billingAddress->getCountry()}' not active in shop", true);
		}
		
		$phone = $order->getPhone();
		if (empty($phone)) {
			$phone = $order->getMobile();
		}

		$customerId = $order->getExternalCustomerId();
		if (empty($customerId)) {
			$customerId = $this->_createGuestUser($order);
		}
		$user = $this->_loadOrderUserById($customerId);

		$orderArr = array();
		$orderArr['customers_id']				= $user["customers_id"];
		$orderArr['customers_cid']				= $user["customers_cid"];
//		$orderArr['customers_vat_id']=0;
		$orderArr['customers_status']			= $user["customers_status"];
		$orderArr['customers_email_address']	= $order->getMail();

		// Fill delivery address information
		$orderArr['delivery_gender']			= $deliveryAddress->getGender();
		$orderArr['delivery_phone']				= $phone;
		$orderArr['delivery_fax']				= "";
		$orderArr['delivery_firstname']			= $deliveryAddress->getFirstName();
		$orderArr['delivery_lastname']			= $deliveryAddress->getLastName();
		$orderArr['delivery_company']			= $deliveryAddress->getCompany();
		$orderArr['delivery_company_2']			= "";
		$orderArr['delivery_company_3']			= "";
		$orderArr['delivery_street_address']	= $deliveryAddress->getStreet1();
		$orderArr['delivery_street_address']   .= ($deliveryAddress->getStreet2()) ? "<br />".$deliveryAddress->getStreet2() : '';

		$orderArr['delivery_suburb'] 			= '';
		$orderArr['delivery_city']				= $deliveryAddress->getCity();
		$orderArr['delivery_postcode']			= $deliveryAddress->getZipcode();
		$orderArr['delivery_country']			= $c->countries_list[$deliveryAddress->getCountry()]["countries_name"];
		$orderArr['delivery_country_code']		= $deliveryAddress->getCountry();

		$orderArr['billing_gender']				= $billingAddress->getGender();
		$orderArr['billing_phone']				= $phone;
		$orderArr['billing_fax']				= "";
		$orderArr['billing_firstname']			= $billingAddress->getFirstName();
		$orderArr['billing_lastname']			= $billingAddress->getLastName();
		$orderArr['billing_company']			= $billingAddress->getCompany();
		$orderArr['billing_company_2']			= "";
		$orderArr['billing_company_3']			= "";
		$orderArr['billing_street_address']		= $billingAddress->getStreet1();
		$orderArr['billing_street_address']    .= ($billingAddress->getStreet2()) ? "<br />". $billingAddress->getStreet2() : '';

		$orderArr['billing_suburb']				= "";
		$orderArr['billing_city']				= $billingAddress->getCity();
		$orderArr['billing_postcode']			= $billingAddress->getZipcode();
		$orderArr['billing_zone']				= "";
		//$orderArr['billing_zone_code']			= ""; // TODO: Kommt bei der neuen API mit
		$orderArr['billing_country']			= $c->countries_list[$billingAddress->getCountry()]["countries_name"];
		$orderArr['billing_country_code']		= $billingAddress->getCountry();

		$orderArr['shipping_code']				= "Standard";

		$orderArr['currency_code']				= $order->getCurrency();
		$orderArr['currency_value']				= 1;

		$orderArr['language_code']				= $language->code;
		$orderArr['comments']					= "Added By Shopgate" . date("Y-m-d H:i:s");
		$orderArr['last_modified']				= date("Y-m-d H:i:s");

		$orderArr['account_type']				= $user["account_type"];
		$orderArr['allow_tax']					= true;
		$orderArr['customers_ip']				= "";
		$orderArr['shop_id']					= $this->config->getPluginShopId();
		$orderArr['orders_data']				= serialize(array("shopgate_order_number" => $order->getOrderNumber()));

		$orderArr['date_purchased']				= $order->getCreatedTime("Y-m-d H:i:s");

		$orderArr['orders_status']				= $this->config->getOrderStatusOpen();
		
		// save the order
		$db->AutoExecute(TABLE_ORDERS, $orderArr, "INSERT");

		$dbOrderId = $db->Insert_ID();

		$this->_insertOrderItems($order, $dbOrderId, $orderArr['orders_status']);
		$this->_insertOrderTotal($order, $dbOrderId, $orderArr['orders_status']);
		$this->_insertOrderStatus($order, $dbOrderId, $orderArr['orders_status']);
		$this->_setOrderPayment($order, $dbOrderId, $user["customers_id"], $orderArr['orders_status']);
		
 		$this->_updateItemsStock($order);
 		
 		// update order status
 		$updateOrderArr = array();
 		$updateOrderArr['orders_status'] = $orderArr['orders_status'];
 		$db->AutoExecute(TABLE_ORDERS, $updateOrderArr, "UPDATE", "orders_id = {$dbOrderId}");

 		// insert shopgate specific order information
		$db->AutoExecute(TABLE_SHOPGATE_ORDERS, array(
			"orders_id" => $dbOrderId,
			"shopgate_order_number" => $order->getOrderNumber(),
			"is_paid" => $order->getIsPaid(),
			"is_shipping_blocked" => $order->getIsShippingBlocked(),
			"payment_infos" => $this->jsonEncode($order->getPaymentInfos()),
			"is_sent_to_shopgate" => 0,
			"modified" => date("Y-m-d H:i:s"),
			"created" => date("Y-m-d H:i:s"),
		), "INSERT");

		// save the order complete amount
		$db->AutoExecute(TABLE_ORDERS_STATS, array(
				"orders_id" => $dbOrderId,
				"orders_stats_price" => $order->getAmountComplete(),
				"products_count" => count($order->getItems())), "INSERT");

		$this->pushOrderToAfterBuy($dbOrderId, $order);
		
		return array(
			'external_order_id'=>$dbOrderId,
			'external_order_number'=>$dbOrderId
		);
	}
	
	private function _setOrderPayment(ShopgateOrder $order, $dbOrderId, $userId=null, &$currentOrderStatus) {
		global $db;
		$orderArr = array();
		$orderArr['subpayment_code'] = "";

		$payment = $order->getPaymentMethod();
		$paymentGroup = $order->getPaymentGroup();
		$paymentInfos = $order->getPaymentInfos();

		switch($payment) {
			case ShopgateOrder::SHOPGATE:
				$orderArr['payment_code']				= "shopgate";
				break;
			case ShopgateOrder::PREPAY:
				$orderArr['payment_code']				= "xt_prepayment";

				$orderArr["orders_data"]				= serialize(array(
					"shopgate_order_number" => $order->getOrderNumber(),
					"shopgate_purpose" => $paymentInfos["purpose"],
				));

				break;
			case ShopgateOrder::INVOICE:
				$orderArr['payment_code']				= "xt_invoice";
				break;
			case ShopgateOrder::COD:
				$orderArr['payment_code']				= "xt_cashondelivery";
				break;
			case ShopgateOrder::DEBIT:
				$orderArr['payment_code']				= "xt_banktransfer";

				$orderArr["orders_data"]				= serialize(
					array(
						"shopgate_order_number"		=> $order->getOrderNumber(),
						"customer_id"				=> $userId,
						"banktransfer_owner"		=> $paymentInfos["bank_account_holder"],
					    "banktransfer_bank_name"	=> $paymentInfos["bank_name"],
					    "banktransfer_blz"			=> $paymentInfos["bank_code"],
					    "banktransfer_number"		=> $paymentInfos["bank_account_number"],
					    "banktransfer_iban"			=> $paymentInfos["iban"],
					    "banktransfer_bic"			=> $paymentInfos["bic"]
					)
				);

				break;
			case ShopgateOrder::PAYPAL:
				$orderArr['payment_code']				= "xt_paypal";
				
				$this->_addOrderStatus($dbOrderId, $currentOrderStatus, $this->_createPaymentInfos($paymentInfos));

				break;
			default:
				$orderArr['payment_code']				= "mobile_payment";
				
				$this->_addOrderStatus($dbOrderId, $currentOrderStatus, $this->_createPaymentInfos($paymentInfos));
				
				break;
		}
		
		$db->AutoExecute(TABLE_ORDERS, $orderArr, "UPDATE", "orders_id = {$dbOrderId}");
	}

	/**
	 * Add a comment to History
	 *
	 * @param Integer $dbOrderId
	 * @param Integer $orders_status
	 * @param String $comment
	 * @param Boolean $customer_show_comment
	 * @param Boolean $customer_notified
	 */
	private function _addOrderStatus($dbOrderId, $orders_status, $comment, $customer_show_comment = 0, $customer_notified = 0) {
		global $db;
		
		static $commentNr = 0;

		$status['orders_id']				= $dbOrderId;
		$status['orders_status_id']			= $orders_status;
		$status['customer_notified']		= (int)$customer_notified;
		$status['date_added']				= date("Y-m-d H:i:s", time()+$commentNr++);
		$status['comments']					= $comment;
		$status['change_trigger']			= 'shopgate';
		$status['callback_id']				= '0';
		$status['customer_show_comment']	= (int)$customer_show_comment;
		$db->AutoExecute(TABLE_ORDERS_STATUS_HISTORY, $status, "INSERT");
	}

	/**
	 * Parse the paymentInfo - array and get as output a string
	 *
	 * @param Array $paymentInfos
	 * @param Integer $dbOrderId
	 * @param Integer $currentOrderStatus
	 *
	 * @return mixed String
	 */
	private function _createPaymentInfos($paymentInfos){
		$paymentInformation = '';
		foreach($paymentInfos as $key => $value){
			$paymentInformation .= $key.': '.$value."<br/>";
		}
		return $paymentInformation;
	}
	

	public function updateOrder(ShopgateOrder $order) {
		global $db;

		$qry = "
			SELECT
				o.*,
				so.shopgate_orders_id,
				so.is_paid,
				so.is_shipping_blocked,
				so.payment_infos
			FROM ".TABLE_ORDERS." o
			JOIN ".TABLE_SHOPGATE_ORDERS." so
				ON (so.orders_id = o.orders_id)
			WHERE so.shopgate_order_number = '{$order->getOrderNumber()}'";

		$orderArr = $db->GetRow($qry);

		if (!is_array($orderArr) || (count($orderArr)) <= 0) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_ORDER_NOT_FOUND, "Shopgate order number: '{$order->getOrderNumber()}'.");
		}

		$dbOrderId = $orderArr["orders_id"];
		unset($orderArr["orders_id"]);

		$errorOrderStatusIsSent = false;
		$errorOrderStatusAlreadySet = array();
		$statusShoppingsystemOrderIsPaid = $orderArr['is_paid'];
		$statusShoppingsystemOrderIsShippingBlocked = $orderArr['is_shipping_blocked'];
		$orderArr['last_modified'] = date("Y-m-d H:i:s");

		// check if shipping is already done, then throw at end of method a OrderStatusIsSent - Exception
		if($orderArr['orders_status'] == $this->config->getOrderStatusShipped() && ($statusShoppingsystemOrderIsShippingBlocked || $order->getIsShippingBlocked())){
			$errorOrderStatusIsSent = true;
		}
		
		if($order->getUpdatePayment() == 1){
			
			if(!is_null($statusShoppingsystemOrderIsPaid) && $order->getIsPaid() == $statusShoppingsystemOrderIsPaid &&
			!is_null($orderArr['payment_infos']) && $orderArr['payment_infos'] == $this->jsonEncode($order->getPaymentInfos())){
				$errorOrderStatusAlreadySet[] = 'payment';
			}
			
			if(!is_null($statusShoppingsystemOrderIsPaid) && $order->getIsPaid() == $statusShoppingsystemOrderIsPaid){
				// do not update is_paid
			} else {
				
				$comment = '';
				if($order->getIsPaid()){
					$comment = 'Bestellstatus von Shopgate geändert: Zahlung erhalten';
				} else {
					$comment = 'Bestellstatus von Shopgate geändert: Zahlung noch nicht erhalten';
				}
				$this->_addOrderStatus($dbOrderId, $orderArr['orders_status'], $comment, '0', '0');
				
				// update the shopgate order status information
				$ordersShopgateOrder = array(
					"is_paid" => (int)$order->getIsPaid(),
					"modified" => date("Y-m-d H:i:s"),
				);
				$db->AutoExecute(TABLE_SHOPGATE_ORDERS, $ordersShopgateOrder, "UPDATE", "shopgate_orders_id = {$orderArr['shopgate_orders_id']}");
				
				// update var
				$statusShoppingsystemOrderIsPaid = $order->getIsPaid();
			}
			
			// update paymentinfos
			if(!is_null($orderArr['payment_infos']) && $orderArr['payment_infos'] != $this->jsonEncode($order->getPaymentInfos())){
					
				$dbPaymentInfos = $this->jsonDecode($orderArr['payment_infos'], true);
				$paymentInfos = $order->getPaymentInfos();
					
				switch($order->getPaymentMethod()) {
					case ShopgateOrder::SHOPGATE:
					case ShopgateOrder::INVOICE:
					case ShopgateOrder::COD:
						break;
					case ShopgateOrder::PREPAY:
						if(isset($dbPaymentInfos['purpose']) && $paymentInfos['purpose'] != $dbPaymentInfos['purpose']){
							// Order is not paid yet
							$this->_addOrderStatus($dbOrderId, $orderArr['orders_status'], "Shopgate: Zahlungsinformationen wurden aktualisiert: <br/><br/>Der Kunde wurde angewiesen Ihnen das Geld mit dem Verwendungszweck: \"".
							$paymentInfos["purpose"]."\" auf Ihr Bankkonto zu überweisen", '0', '0');
							
							$orderData["orders_data"] = serialize(array(
								"shopgate_purpose" => $paymentInfos["purpose"],
							));
							$db->AutoExecute(TABLE_ORDERS, $orderData, "UPDATE", "orders_id = {$dbOrderId}");
						}
						
						break;
					case ShopgateOrder::DEBIT:
						
						$orderData["orders_data"] = serialize(
							array(
								"shopgate_order_number"		=> $order->getOrderNumber(),
								"banktransfer_owner"		=> $paymentInfos["bank_account_holder"],
								"banktransfer_bank_name"	=> $paymentInfos["bank_name"],
								"banktransfer_blz"			=> $paymentInfos["bank_code"],
								"banktransfer_number"		=> $paymentInfos["bank_account_number"],
								"banktransfer_iban"			=> $paymentInfos["iban"],
								"banktransfer_bic"			=> $paymentInfos["bic"]
							)
						);
						$db->AutoExecute(TABLE_ORDERS, $orderData, "UPDATE", "orders_id = {$dbOrderId}");
						
						$comment = "Shopgate: Zahlungsinformationen wurden aktualisiert: <br/><br/>". $this->_createPaymentInfos($paymentInfos);
						$this->_addOrderStatus($dbOrderId, $orderArr['orders_status'], $comment, '0', '0');
						
						break;
					case ShopgateOrder::PAYPAL:
						
						// Save paymentinfos in history
						$comment = "Shopgate: Zahlungsinformationen wurden aktualisiert: <br/><br/>". $this->_createPaymentInfos($paymentInfos);
						$this->_addOrderStatus($dbOrderId, $orderArr['orders_status'], $comment, '0', '0');
						
						
						break;
					default:
						// mobile_payment
						
						// Save paymentinfos in history
						$comment = "Shopgate: Zahlungsinformationen wurden aktualisiert: <br/><br/>". $this->_createPaymentInfos($paymentInfos);
						$this->_addOrderStatus($dbOrderId, $orderArr['orders_status'], $comment, '0', '0');
						
						break;
				}
			}
			
			$ordersShopgateOrder = array(
				"payment_infos" => $this->jsonEncode($order->getPaymentInfos()),
				"modified" => date("Y-m-d H:i:s"),
			);
			$db->AutoExecute(TABLE_SHOPGATE_ORDERS, $ordersShopgateOrder, "UPDATE", "shopgate_orders_id = {$orderArr['shopgate_orders_id']}");
			
		}

		if($order->getUpdateShipping() == 1){
			if(!is_null($statusShoppingsystemOrderIsShippingBlocked) && $order->getIsShippingBlocked() == $statusShoppingsystemOrderIsShippingBlocked){
				// shipping information already updated
				$errorOrderStatusAlreadySet[] = 'shipping';
			} else {
				if($orderArr['orders_status'] != $this->config->getOrderStatusShipped()){
					// set "new" status
					if($order->getIsShippingBlocked() == 1){
						$orderArr['orders_status'] = $this->config->getOrderStatusShippingBlocked();
					} else {
						$orderArr['orders_status'] = $this->config->getOrderStatusOpen();
					}
				}
				
				// Insert changes in history
				if($order->getIsShippingBlocked() == 0){
					$comment = 'Bestellstatus von Shopgate geändert: Bestellung freigegeben!';
				} else {
					$comment = 'Bestellstatus von Shopgate geändert: Bestellung ist nicht freigegeben!';
				}
				$this->_addOrderStatus($dbOrderId, $orderArr['orders_status'], $comment, '0', '0');
				
				// update the shopgate order status information
				$ordersShopgateOrder = array(
					"is_shipping_blocked" => (int)$order->getIsShippingBlocked(),
					"modified" => date("Y-m-d H:i:s"),
				);
				$db->AutoExecute(TABLE_SHOPGATE_ORDERS, $ordersShopgateOrder, "UPDATE", "shopgate_orders_id = {$orderArr['shopgate_orders_id']}");
			
				// update order stats
				$updateOrderArr = array();
				$updateOrderArr['orders_status'] = $orderArr['orders_status'];
				$db->AutoExecute(TABLE_ORDERS, $updateOrderArr, "UPDATE", "orders_id = {$dbOrderId}");
			}
		}
		
		if($errorOrderStatusIsSent){
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_ORDER_STATUS_IS_SENT);
		}
		
		if(!empty($errorOrderStatusAlreadySet)){
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_ORDER_ALREADY_UP_TO_DATE, implode(',', $errorOrderStatusAlreadySet), true);
		}

		$this->pushOrderToAfterBuy($dbOrderId, $order);
		
		return array(
			'external_order_id'=>$dbOrderId,
			'external_order_number'=>$dbOrderId
		);
	}

	private function _insertOrderItems(ShopgateOrder $order, $dbOrderId, &$currentOrderStatus) {
		global $db;

		$errors = '';
		$items = $order->getItems();
		foreach($items as $item) {
			$products_model = $item->getItemNumber();

			$orderInfo = $item->getInternalOrderInfo();
			$orderInfo = $this->jsonDecode($orderInfo, true);

			$product = $this->_loadProduct($products_model);

			if(empty($product) && $products_model == 'COUPON'){
				// workaround for shopgate coupons
				$product = array();
				$product['products_id'] = 0;
				$product['products_model'] = $products_model;
			} else if(empty($product)){
				$this->log(ShopgateLibraryException::buildLogMessageFor(ShopgateLibraryException::PLUGIN_ORDER_ITEM_NOT_FOUND, 'Shopgate-Order-Number: '. $order->getOrderNumber() .', DB-Order-Id: '. $dbOrderId .'; item (item_number: '.$products_model.'). The item will be skipped.'));
				$errors .= "\nItem (item_number: ".$products_model.") can not be found in your shoppingsystem. Please contact Shopgate. The item will be skipped.";

				$product['products_id'] = 0;
				$product['products_model'] = $products_model;
			}

			$itemArr = array();
			$itemArr["orders_id"]			= $dbOrderId;
			$itemArr["products_id"]			= $product['products_id'];
			$itemArr["products_model"]		= $product['products_model'];
			$itemArr["products_name"]		= $item->getName();
			$itemArr["products_price"]		= $item->getUnitAmountWithTax() / (1 + ($item->getTaxPercent() / 100));
			$itemArr["products_tax"]		= $item->getTaxPercent();
			$itemArr["products_tax_class"]	= $this->_getTaxClassByTaxRate( $item->getTaxPercent() );
			$itemArr["products_quantity"]	= $item->getQuantity();
			$itemArr["allow_tax"]			= true;
			
			$options = $item->getOptions();
			$inputs = $item->getInputs();

			if ((!empty($options) || !empty($inputs)) && $this->checkPlugin('xt_product_options', '2.4.0')) {
				$obj = array();
				$xtpop = new product($product['products_id'], NULL, $item->getQuantity());
				$xtpo = new xt_product_options($xtpop->data['products_id'], $xtpop->data);
				
				$xtpo->oData = $xtpo->_getData();
				$xtpo->oDataGrouped = $xtpo->_reGroupoData();
				$xtpo->possible_groups = $xtpo->_buildPossibleGroups();
				$xtpo->possible_values = $xtpo->_buildPossibleValues();
				$xtpo->groups = $xtpo->_getGroups();
				$xtpo->values = $xtpo->_getValues();

				if (is_array($options)) {
					foreach($options as $option) {
						$optionNumber = explode('_', $option->getOptionNumber());

						if (count($optionNumber) > 1) {
							// => checkbox
							$obj['products_info'][$product['products_id']][$optionNumber[0]] = $optionNumber[1];
						} elseif ($fieldtype = $xtpo->_getFieldType($xtpo->groups[$option->getOptionNumber()]['option_group_field'], $xtpo->values[$option->getOptionNumber()][$option->getValueNumber()]['option_value_field']) == 'select') {
							// => select
							$obj['products_info'][$product['products_id']][$option->getOptionNumber().'SELECT'] = $option->getValueNumber();
						} elseif ($fieldtype = $xtpo->_getFieldType($xtpo->groups[$option->getOptionNumber()]['option_group_field'], $xtpo->values[$option->getOptionNumber()][$option->getValueNumber()]['option_value_field']) == 'radio') {
							// => radio
							$obj['products_info'][$product['products_id']][$option->getOptionNumber()] = $option->getValueNumber();
						}
					}
				}
				
				if (is_array($inputs)) {
					foreach($inputs as $input) {
						$obj['products_info'][$product['products_id']][$input->getInputNumber()] = $input->getUserInput();
					}
				}
				
				$obj['product'] = $product['products_id'];
				$obj['products_id'] = $product['products_id'];
				
				$pdata = $xtpop->data;
				$product_info = $xtpo->_rebuildOptionsData($obj, $product['products_id']);
				
				$arrnew = $xtpo->_reGroupoData();
				foreach ($product_info['options'] as $key => $val) {
					$group_data = $xtpo->_getGroupData($val['option_group_id']);
					$value_data = $xtpo->_getValueData($val['option_value_id']);
					
					$key = explode("_", $key);
					
					if (is_array($arrnew[$key[0]][$key[1]]) && is_array($val) && is_array($group_data) && is_array($value_data)) {
						$product_info['options'][$key[0].'_'.$key[1]] = array_merge($val, $arrnew[$key[0]][$key[1]], $group_data, $value_data);
					}
					
					$product_info['options'][$key[0].'_'.$key[1]]['products_price'] = $pdata['products_price'];
					$product_info['options'][$key[0].'_'.$key[1]]['products_tax_value'] = $pdata['products_tax_rate'];
				}
				
				$itemArr["products_data"] = $product_info;
			}
			
			if(!isset($itemArr["products_data"]["options"])) {
				// Needed for afterbuy_pimpmyxt
				$itemArr["products_data"]["options"] = array();
			}
				
			// add refund
			$itemArr = $this->_insertOrderItemsAddRefund($itemArr, $item, $order->getCurrency());
			
			$itemArr['products_data'] = serialize($itemArr['products_data']);

			$db->AutoExecute(TABLE_ORDERS_PRODUCTS, $itemArr, "INSERT");
		}
		
		if (!empty($errors)) {
			$this->_addOrderStatus($dbOrderId, $currentOrderStatus, 'Beim Importieren der Bestellung sind Fehler aufgetreten: '. $errors);
		}
	}
	
	private function _insertOrderItemsAddRefund($itemArr, ShopgateOrderItem $item, $currency) {
		return $itemArr;
	}

	private function _insertOrderTotal(ShopgateOrder $order, $dbOrderId) {
		global $db, $price;
		
		$totals = array();
		$totals['shipping'] = array(
			'orders_total_key'			=> 'shipping',
			'orders_total_key_id'		=> '1',
			'orders_total_model'		=> 'Standard',
			'orders_total_name'			=> 'Versand',
			'orders_total_price'		=> $order->getAmountShipping(),
			'orders_total_tax'			=> null,
			'orders_total_tax_class'	=> "0",
			'orders_total_quantity'		=> "1", // nur einmal Versand berechnen != count($order->getOrderItems());
			'allow_tax'					=> "1",
		);
		
		$totals = $this->_insertOrderTotalAddBulkCharge($totals, $order);
		$totals = $this->_insertOrderTotalAddRefund($totals, $order);
		
		if ($order->getAmountShopPayment() != 0) {
			$paymentInfos = $order->getPaymentInfos();
			$totals['payment'] = array(
				'orders_total_key'			=> 'payment',
				'orders_total_key_id'		=> '2',
				'orders_total_model'		=> 'Standard',
				'orders_total_name'			=> 'Zahlungsartkosten'.(!empty($paymentInfos['shopgate_payment_name']) ? ' ('.$paymentInfos['shopgate_payment_name'].')' : ''),
				'orders_total_price'		=> $order->getAmountShopPayment() / (100 + $order->getPaymentTaxPercent()) * 100,
				'orders_total_tax'			=> $order->getPaymentTaxPercent(),
				'orders_total_tax_class'	=> $this->_getTaxClassByTaxRate($order->getPaymentTaxPercent()),
				'orders_total_quantity'		=> '1',
				'allow_tax'					=> '1',
			);
		}
		
		// run through all the totals, add the order's ID and save it into the database
		foreach ($totals as $total) {
			$total['orders_id'] = $dbOrderId;
			$db->AutoExecute(TABLE_ORDERS_TOTAL, $total, 'INSERT');
		}
	}
	
	/**
	 * Adds bulk charge to the order if the plugin is enabled.
	 *
	 * @param mixed[][] $total The list of order total arrays to be modified for export.
	 * @param ShopgateOrder $order The ShopgateOrder object to import.
	 * @return mixed[][] The modified list of order total arrays for export.
	 */
	private function _insertOrderTotalAddBulkCharge($totals, ShopgateOrder $order) {
		if (!$this->checkPlugin('xt_sperrgut')) return $totals;
		
		// calculate the bulk charge amount
		$bulkAmount = 0;
		foreach ($order->getItems() as $item) {
			/* @var $item ShopgateOrderItem */
			
			$orderInfo = $this->jsonDecode($item->getInternalOrderInfo(), true);
			if (!empty($orderInfo['xt_sperrgut'])) {
				$bulkAmount += $item->getQuantity() * $orderInfo['xt_sperrgut'];
			}
		}
			
		// remove the bulk charge from the total shipping amount
		$totals['shipping']['orders_total_price'] -= $bulkAmount;
			
		// add the bulk charge row to orders total
		$tax = $price->buildPriceData($bulkAmount, XT_SPERRGUT_TAX_CLASS);
		$totals['bulk_charge'] = array(
			'orders_total_key'			=> 'xt_sperrgut',
			'orders_total_key_id'		=> '',
			'orders_total_model'		=> '',
			'orders_total_name'			=> 'Sperrgutzuschlag',
			'orders_total_price'		=> $bulkAmount / (100 + $tax['tax_rate']) * 100,
			'orders_total_tax'			=> $tax['tax_rate'],
			'orders_total_tax_class'	=> XT_SPERRGUT_TAX_CLASS,
			'orders_total_quantity'		=> '1',
			'allow_tax'					=> '1',
		);
		
		return $totals;
	}

	/**
	 * Adds refund pricing to the order if the plugin is enabled.
	 *
	 * @param mixed[][] $total The list of order total arrays to be modified for export.
	 * @param ShopgateOrder $order The ShopgateOrder object to import.
	 * @return mixed[][] The modified list of order total arrays for export.
	 */
	private function _insertOrderTotalAddRefund($totals, ShopgateOrder $order) {
		return $totals;
	}

	private function _insertOrderStatus(ShopgateOrder $order, $dbOrderId, &$currentOrderStatus) {
		global $db;
		
		// set the status
		if($order->getIsShippingBlocked() == 0){
			$commentShipping 	= "<br/>Die Bestellung ist von Shopgate freigegeben.<br/>";
		} else {
			$currentOrderStatus = $this->config->getOrderStatusShippingBlocked();
			$commentShipping	= "<br/>Die Bestellung ist von Shopgate noch nicht freigegeben.<br/>";
		}
		
		// order is added and visible for customer
		$comment = "Bestellung von Shopgate hinzugefügt.";
		$this->_addOrderStatus($dbOrderId, $currentOrderStatus, $comment, 1, 1);
		
		$comment = '';
		if($order->getIsTest()){
			$comment .= "#### DIES IST EINE TESTBESTELLUNG ####<br/>";
		}
		$comment .= "Bestellnummer: ". $order->getOrderNumber().'<br/>';
		
		$paymentTransactionNumber = $order->getPaymentTransactionNumber();
		if(!empty($paymentTransactionNumber)){
			$comment .= "Payment-Transaktionsnummer: ". $paymentTransactionNumber.'<br/>';
		}
		
		$comment .= $commentShipping;
		
		// the invoice must not been sent twice to prevent false payments!
		// add a comment to inform the merchant about that fact
		if($order->getIsCustomerInvoiceBlocked()) {
			$comment .= '<br/><b>Achtung:</b><br/>Für diese Bestellung wurde bereits eine Rechnung versendet.<br/>Um eine reibungslose Abwicklung zu gewährleisten, <b>darf keine</b> weitere Rechnung an den Kunden versendet werden!<br/>';
		}
		
		$this->_addOrderStatus($dbOrderId, $currentOrderStatus, $comment);
	}

	private function _loadProduct($products_model) {
		global $db;

		$qry = "
			SELECT
				p.*
			FROM ".TABLE_PRODUCTS." p
			WHERE p.products_id = '$products_model'
		";

		$result = $db->Execute($qry);
		return $result->fields;
	}

	private function _loadOrderUserByNumber($userid) {
		global $db;

		if(empty($userid)) return null;

		$qry = "
			SELECT c.*
			FROM ".TABLE_CUSTOMERS." c
			WHERE c.customers_cid = ".$userid."
		";

		$result = $db->Execute($qry);
		$user = $result->fields;

		return $user;
	}

	private function _loadOrderUserById($userid) {
		global $db;

		if(empty($userid)) return null;

		$qry = "
			SELECT c.*
			FROM ".TABLE_CUSTOMERS." c
			WHERE c.customers_id = ".$userid."
		";

		$result = $db->Execute($qry);
		$user = $result->fields;

		return $user;
	}

	private function _setOrderAsShipped($order) {
		$orderAPI = new ShopgateOrderApi();
		$orderAPI->setShippingComplete($order);
	}

	/**
	 *
	 * Aktualisiert die Bestände der Bestellten Produkte
	 * @param ShopgateOrder $order
	 */
	private function _updateItemsStock(ShopgateOrder $order) {
		global $db;

		if(!_SYSTEM_STOCK_HANDLING) return;

		$items = $order->getItems();

		foreach($items as $item) {
			$orderInfo = $item->getInternalOrderInfo();

			$product_id = $item->getItemNumber();

			$product = $this->_loadProduct($product_id);

			$newQty = $product["products_quantity"] - $item->getQuantity();
			$db->AutoExecute(TABLE_PRODUCTS, array("products_quantity" => $newQty), "UPDATE", "products_id = '{$product_id}'");
		}
	}

	/**
	 * Erzeugt einen Eintrag für den Gast-User
	 * @param ShopgateOrder $order
	 */
	private function _createGuestUser(ShopgateOrder $order) {
		global $db;
		/**
		 * @var ShopgateOrderAddress
		 */
		$address = $order->getInvoiceAddress();

		$customer = array();
		$customer["customers_status"] = _STORE_CUSTOMERS_STATUS_ID_GUEST; // TODO
		$customer["customers_email_address"] = utf8_decode($order->getMail());

		$customer["customers_cid"] = '';
		$customer["customers_vat_id"] = '';
		$customer["customers_vat_id_status"] = 0;
		$customer["date_added"] = date( 'Y-m-d H:i:s' );
		$customer["last_modified"] = date( 'Y-m-d H:i:s' );
		$customer["shop_id"] = $this->config->getPluginShopId();
		$customer["customers_default_currency"] = strtoupper($order->getCurrency());
		$customer["customers_default_language"] = strtolower($address->getCountry());
		//		$customer["campaign_id"] = 0;
		$customer["payment_unallowed"] = "";
		$customer["shipping_unallowed"] = "";

		$qry  = "INSERT INTO `".TABLE_CUSTOMERS . "` (`";
		$qry .= implode("`, `", array_keys($customer));
		$qry .= "`) VALUES ('";
		$qry .= implode("', '", $customer);
		$qry .= "')";
		$db->Execute($qry);
		$customerId = $db->Insert_ID();

		///
		/// Die defaultadresse
		///
		$_address = array(
			"external_id" => null,
			"customers_id" => $customerId,
			"customers_gender" => $address->getGender(),
//			"customers_dob" => "1956-08-12 00:00:00",
			"customers_phone" => "",
			"customers_fax" => "",
			"customers_company" => $address->getCompany(),
// 			"customers_company_2" => "",
// 			"customers_company_3" => "",
			"customers_firstname" => $address->getFirstName(),
			"customers_lastname" => $address->getLastName(),
			"customers_street_address" => $address->getStreet1(),
			"customers_suburb" => "",
			"customers_postcode" => $address->getZipcode(),
			"customers_city" => $address->getCity(),
			"customers_country_code" => $address->getCountry(),
			"address_class" => "default",
			"date_added" => date( 'Y-m-d H:i:s' ),
			"last_modified" => date( 'Y-m-d H:i:s' )
		);

		$_address['customers_street_address']		= $address->getStreet1();
		if($address->getStreet2())
			$_address['customers_street_address']	.= "<br />" . $address->getStreet2();

		$qry  = "INSERT INTO `".TABLE_CUSTOMERS_ADDRESSES . "` (`";
		$qry .= implode("`, `", array_keys($_address));
		$qry .= "`) VALUES ('";
		$qry .= implode("', '", $_address);
		$qry .= "')";
		$db->Execute($qry);

		///
		/// Die Lieferadresse eintragen
		///
		$address = $order->getDeliveryAddress();
		$_address = array(
			"external_id" => null,
			"customers_id" => $customerId,
			"customers_gender" => $address->getGender(),
			"customers_phone" => "",
			"customers_fax" => "",
			"customers_company" => $address->getCompany(),
			"customers_firstname" => $address->getFirstName(),
			"customers_lastname" => $address->getLastName(),
			"customers_street_address" => $address->getStreet1(),
			"customers_suburb" => "",
			"customers_postcode" => $address->getZipcode(),
			"customers_city" => $address->getCity(),
			"customers_country_code" => $address->getCountry(),
			"address_class" => "shipping",
			"date_added" => date( 'Y-m-d H:i:s' ),
			"last_modified" => date( 'Y-m-d H:i:s' )
		);

		$qry  = "INSERT INTO `".TABLE_CUSTOMERS_ADDRESSES . "` (`";
		$qry .= implode("`, `", array_keys($_address));
		$qry .= "`) VALUES ('";
		$qry .= implode("', '", $_address);
		$qry .= "')";
		$db->Execute($qry);

		///
		/// Die Rechnungsdresse eintragen
		///
		$address = $order->getInvoiceAddress();
		$_address = array(
			"external_id" => null,
			"customers_id" => $customerId,
			"customers_gender" => $address->getGender(),
			"customers_phone" => "",
			"customers_fax" => "",
			"customers_company" => $address->getCompany(),
			"customers_firstname" => $address->getFirstName(),
			"customers_lastname" => $address->getLastName(),
			"customers_street_address" => $address->getStreet1(),
			"customers_suburb" => "",
			"customers_postcode" => $address->getZipcode(),
			"customers_city" => $address->getCity(),
			"customers_country_code" => $address->getCountry(),
			"address_class" => "payment",
			"date_added" => date( 'Y-m-d H:i:s' ),
			"last_modified" => date( 'Y-m-d H:i:s' )
		);

		$qry  = "INSERT INTO `".TABLE_CUSTOMERS_ADDRESSES . "` (`";
		$qry .= implode("`, `", array_keys($_address));
		$qry .= "`) VALUES ('";
		$qry .= implode("', '", $_address);
		$qry .= "')";
		$db->Execute($qry);

		return $customerId;
	}

	private function _getTaxClassByTaxRate($rate) {
		global $db;
		$rate = (float) $rate;
		$qry = "SELECT tax_class_id FROM ".TABLE_TAX_RATES." WHERE tax_rate = $rate";

		$class = $db->GetOne($qry);
		if(empty($class)) $class = 1;

		return $class;
	}

	public function getShopgateOrders($shopgate_order_numbers = array()) {
		// NOT IMPLEMENTED YET...
		return;
		global $db, $language;

		$qry = "
			SELECT
				so.shopgate_order_number,
				o.*,
				sd.status_name,
				csd.customers_status_name
			FROM ".TABLE_SHOPGATE_ORDERS." so
			JOIN ".TABLE_ORDERS." o ON (so.orders_id = o.orders_id)
			LEFT JOIN ".TABLE_SYSTEM_STATUS_DESCRIPTION." sd ON (sd.status_id = o.orders_status AND sd.language_code = '".$language->code."')
			LEFT JOIN ".TABLE_CUSTOMERS_STATUS_DESCRIPTION." csd ON (csd.customers_status_id = o.customers_status AND csd.language_code = '".$language->code."')
			WHERE so.shopgate_order_number IN ('".implode("','", $shopgate_order_numbers)."')
		";

		$result = $db->Execute($qry);

		$orders = array();

		while(!$result->EOF) {
			$row = $result->fields;
			$order = array();

			$order["shopgate_order_number"]				= $row["shopgate_order_number"];

			$order["order_id"]							= $row["orders_id"];
			$order["order_number"]						= "";

			$order["customer_id"]						= $row["customers_id"];
			$order["customer_number"]					= $row["customers_cid"];

			$order["customers_group_id"]				= $row["customers_status"];
			$order["customers_group_name"]				= $row["customers_status_name"];

// 			$order["mail"]								= $row["customers_email_address"];
// 			$order["phone"]								= $row["delivery_phone"];
// 			$order["mobile"]							= "";

			$order["status_id"]							= $row["orders_status"];
			$order["status_name"]						= $row["status_name"];
			$order["status_comment"]					= $row["comments"];

			$order["payment_method"]					= $row["payment_code"];
			$order["shopgate_payment_method"]			= "";

			$order["date_complete"]						= !empty($row["orders_date_finished"])?date("c", strtotime($row["orders_date_finished"])):null;
			$order["last_modified"]						= !empty($row["last_modified"])?date("c", strtotime($row["last_modified"])):null;

			$order["currency"]							= $row["currency_code"];
			$order["currency_value"]					= $row["currency_value"];
			$order["shipping_service"]					= $row["shipping_code"];

			// Delivery address
			$order["delivery_address"]["gender"]		= $row["delivery_gender"];
			$order["delivery_address"]["first_name"]	= $row["delivery_firstname"];
			$order["delivery_address"]["last_name"]		= $row["delivery_lastname"];
			$order["delivery_address"]["birthday"]		= "";//$row[""];
			$order["delivery_address"]["company"]		= $row["delivery_company"];
			$order["delivery_address"]["street_1"]		= $row["delivery_street_address"];
			$order["delivery_address"]["street_2"]		= "";
			$order["delivery_address"]["city"]			= $row["delivery_city"];
			$order["delivery_address"]["zipcode"]		= $row["delivery_postcode"];
			$order["delivery_address"]["country"]		= strtoupper($row["delivery_country_code"]);
			$order["delivery_address"]["country_name"]	= $row["delivery_country"];
			$order["delivery_address"]["state"]			= $row["delivery_zone_code"];
			$order["delivery_address"]["state_name"]	= $row["delivery_zone"];
			$order["delivery_address"]["phone"]			= $row["delivery_phone"];
			$order["delivery_address"]["mobile"]		= null;
			$order["delivery_address"]["mail"]			= $row["customers_email_address"];

			// Invoice address
			$order["invoice_address"]["gender"]			= $row["billing_gender"];
			$order["invoice_address"]["first_name"]		= $row["billing_firstname"];
			$order["invoice_address"]["last_name"]		= $row["billing_lastname"];
			$order["invoice_address"]["birthday"]		= "";//$row[""];
			$order["invoice_address"]["company"]		= $row["billing_company"];
			$order["invoice_address"]["street_1"]		= $row["billing_street_address"];
			$order["invoice_address"]["street_2"]		= "";
			$order["invoice_address"]["city"]			= $row["billing_city"];
			$order["invoice_address"]["zipcode"]		= $row["billing_postcode"];
			$order["invoice_address"]["country"]		= strtoupper($row["billing_country_code"]);
			$order["invoice_address"]["country_name"]	= $row["billing_country"];
			$order["invoice_address"]["state"]			= $row["billing_zone_code"]?$row["billing_zone_code"]:null;
			$order["invoice_address"]["state_name"]		= $row["billing_zone"]?$row["billing_zone"]:null;
			$order["invoice_address"]["phone"]			= $row["billing_phone"];
			$order["invoice_address"]["mobile"]			= null;
			$order["invoice_address"]["mail"]			= $row["customers_email_address"];

			// fetch the items for the order
			$order["items"] = array();
			$itemsResult = $db->Execute("
					SELECT
						op.*
					FROM ".TABLE_ORDERS_PRODUCTS." op
					WHERE op.orders_id = {$row["orders_id"]}");
			while(!$itemsResult->EOF) {
				$orderProduct = $itemsResult->fields;
				$item = array();

				$item["order_item_id"]					= $orderProduct["orders_products_id"];
				$item["item_id"]						= $orderProduct["products_id"];
				$item["item_number"]					= $orderProduct["products_model"];
				$item["name"]							= $orderProduct["products_name"];
				$item["unit_amount"]					= $orderProduct["products_price"];
				$item["tax"]							= $orderProduct["products_tax"];
				$item["quantity"]						= $orderProduct["products_quantity"];

				$products_data = unserialize($orderProduct["products_data"]);

				$order["items"][] = $item;
				$itemsResult->MoveNext();
			}

			// fetch the status history
			$order["status_history"] = array();
			$statusResult = $db->Execute("
					SELECT
						osh.*,
						sd.status_name
					FROM ".TABLE_ORDERS_STATUS_HISTORY." osh
					LEFT JOIN ".TABLE_SYSTEM_STATUS_DESCRIPTION." sd ON sd.status_id = osh.orders_status_id
					WHERE osh.orders_id = {$row["orders_id"]} AND sd.language_code = '".$language->code."'
					ORDER BY osh.date_added, osh.orders_status_history_id");

			while(!$statusResult->EOF) {
				$histotryRow = $statusResult->fields;

				$history = array();
				$history["history_id"]					= $histotryRow["orders_status_history_id"];
				$history["status_id"]					= $histotryRow["orders_status_id"];
				$history["status"]						= $histotryRow["status_name"];
				$history["message"]						= $histotryRow["comments"];
				$history["date_added"]					= date("c", strtotime($histotryRow["date_added"]));
				$history["customer_notified"]			= $histotryRow["customer_notified"];

				$order["status_history"][] = $history;
				$statusResult->MoveNext();
			}

			$order["delivery_notes"] = array();

			$orders[] = $order;
			$result->MoveNext();
		}


		return $orders;
	}

	protected function getOrders($customer_id, $order_numbers = array()) {

	}
	
	public function cron($jobname, $params, &$message, &$errorcount) {
		switch ($jobname) {
			case 'set_shipping_completed': $this->cronSetOrdersShippingCompleted($message, $errorcount); break;
			default: throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_CRON_UNSUPPORTED_JOB, 'Job name: "'.$jobname.'"', true);
		}
	}
	
	/**
	 * Marks shipped orders as "shipped" at Shopgate.
	 *
	 * This will find all orders that are marked "shipped" at Veyton but not at Shopgate yet and marks them "shipped" at Shopgate via
	 * Shopgate Merchant API.
	 *
	 * @param string $message Process log will be appended to this reference.
	 * @param int $errorcount This reference gets incremented on errors.
	 */
	protected function cronSetOrdersShippingCompleted(&$message, &$errorcount) {
		global $db;
		
		$shopgateOrders = $db->Execute(
			"SELECT DISTINCT ".
				"`".TABLE_SHOPGATE_ORDERS."`.`orders_id`, ".
				"`shopgate_order_number` ".
			"FROM `".TABLE_SHOPGATE_ORDERS."` ".
			"WHERE ".
				"`".TABLE_SHOPGATE_ORDERS."`.`is_sent_to_shopgate` = 0 ".
			";"
		);
		
		$sgOrdersList = array();
		while(!$shopgateOrders->EOF) {
			$sgOrdersList[] = $shopgateOrders->fields;
			$shopgateOrders->moveNext();
		}
		
		foreach($sgOrdersList as $sgOrder) {
			$orderData = $db->Execute(
				"SELECT DISTINCT ".
					"`".TABLE_ORDERS."`.`orders_id` ".
				"FROM `".TABLE_ORDERS."` ".
					"LEFT JOIN `".TABLE_ORDERS_STATUS_HISTORY."` ON (`".TABLE_ORDERS_STATUS_HISTORY."`.`orders_id` = `".TABLE_ORDERS."`.`orders_id`) ".
				"WHERE ".
					"`".TABLE_ORDERS."`.`orders_id` = '{$sgOrder['orders_id']}' ".
					"AND (".
						"`".TABLE_ORDERS."`.`orders_status` = '".$this->config->getOrderStatusShipped()."' ".
						"OR ".
						"`".TABLE_ORDERS_STATUS_HISTORY."`.`orders_status_id` = '".$this->config->getOrderStatusShipped()."'".
					")".
				";"
			);
			if(!$orderData->EOF) {
				if (!$this->setOrderShippingCompleted($sgOrder['shopgate_order_number'], $sgOrder['orders_id'])) {
					$errorcount++;
					$message .= 'Shopgate order number "'.$sgOrder['shopgate_order_number'].'": error'."\n";
				}
			}
		}
	}
	
	/**
	 * Sets the order status of a Shopgate order to "shipped" via Shopgate Merchant API
	 *
	 * @param string $shopgateOrderNumber The number of the order at Shopgate.
	 * @param int $orderId The ID of the order at Veyton.
	 * @return bool true on success, false on failure.
	 */
	protected function setOrderShippingCompleted($shopgateOrderNumber, $orderId) {
		global $db;
		$success = false;
		
		// These are expected and should not be added to error count:
		$ignoreCodes = array(ShopgateMerchantApiException::ORDER_ALREADY_COMPLETED, ShopgateMerchantApiException::ORDER_SHIPPING_STATUS_ALREADY_COMPLETED);
		
		try {
			
			// send request to Shopgate Merchant API
			$this->merchantApi->setOrderShippingCompleted($shopgateOrderNumber);
			
			// prepare message for order history
			$statusArr = array();
			$statusArr['orders_id'] = $orderId;
			$statusArr['orders_status_id'] = $this->config->getOrderStatusShipped();
			$statusArr['customer_notified'] = true;
			$statusArr['date_added'] = date("Y-m-d H:i:s");
			$statusArr['comments'] = 'Bestellung wurde bei Shopgate als versendet markiert';
			$statusArr['change_trigger'] = 'shopgate';
			$statusArr['callback_id'] = '0';
			$statusArr['customer_show_comment'] = true;
			
			$success = true;
		} catch (ShopgateLibraryException $e) {
			// prepare message for order history
			$statusArr = array();
			$statusArr['orders_id'] = $orderId;
			$statusArr['orders_status_id'] = $this->config->getOrderStatusShipped();
			$statusArr['customer_notified'] = false;
			$statusArr['date_added'] = date("Y-m-d H:i:s");
			$statusArr['comments'] = "Es ist ein Fehler im Shopgate-Plugin aufgetreten ({$e->getCode()}): {$e->getMessage()}";
			$statusArr['change_trigger'] = 'shopgate';
			$statusArr['callback_id'] = '0';
			$statusArr['customer_show_comment'] = false;
		} catch (ShopgateMerchantApiException $e) {
			// prepare message for order history
			$statusArr = array();
			$statusArr['orders_id'] = $orderId;
			$statusArr['orders_status_id'] = $this->config->getOrderStatusShipped();
			$statusArr['customer_notified'] = false;
			$statusArr['date_added'] = date("Y-m-d H:i:s");
			$statusArr['comments'] = "Es ist ein Fehler bei Shopgate aufgetreten ({$e->getCode()}): {$e->getMessage()}";
			$statusArr['change_trigger'] = 'shopgate';
			$statusArr['callback_id'] = '0';
			$statusArr['customer_show_comment'] = false;
			
			$success = (in_array($e->getCode(), $ignoreCodes)) ? true : false;
		} catch (Exception $e) {
			// prepare message for order history
			$statusArr = array();
			$statusArr['orders_id'] = $orderId;
			$statusArr['orders_status_id'] = $this->config->getOrderStatusShipped();
			$statusArr['customer_notified'] = false;
			$statusArr['date_added'] = date("Y-m-d H:i:s");
			$statusArr['comments'] = "Es ist ein unbekannter Fehler aufgetreten ({$e->getCode()}): {$e->getMessage()}";
			$statusArr['change_trigger'] = 'shopgate';
			$statusArr['callback_id'] = '0';
			$statusArr['customer_show_comment'] = false;
		}
		
		// Update shopgate order on success
		if($success) {
			$qry = 'UPDATE `'.TABLE_SHOPGATE_ORDERS.'` SET `is_sent_to_shopgate` = 1 WHERE `shopgate_order_number` = '.$shopgateOrderNumber.';';
			if (!$db->Execute($qry)) {
				$this->log(ShopgateLibraryException::buildLogMessageFor(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Failed query: '.$qry));
			}
		}
		
		// update order history
		$keyString = "`".implode("`, `", array_keys($statusArr))."`";
		$valueString = "'".implode("', '", $statusArr)."'";
		$qry = "INSERT INTO `".TABLE_ORDERS_STATUS_HISTORY."`\n ($keyString)\n VALUES\n ($valueString)";

		if (!$db->Execute($qry)) {
			$this->log(ShopgateLibraryException::buildLogMessageFor(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Failed query: '.$qry));
		}
		
		return $success;
	}
	
	protected function pushOrderToAfterBuy($orderID, ShopgateOrder $order) {
		if(!$order->getIsShippingBlocked()) {
			// AFTERBUY MODULE xt_pimpmyxt
			if($this->checkPlugin("xt_pimpmyxt")) {
				$afterbuy_class_path = _SRV_WEBROOT._SRV_WEB_PLUGINS.'/xt_pimpmyxt/classes/afterbuy_veyton.php';
				if (file_exists($afterbuy_class_path))
				{
					require_once($afterbuy_class_path);
					$afterbuy = new class_afterbuy ($orderID);
					$afterbuy->send();
				}
			}
		}
	}
	
	public function checkCart(ShopgateCart $shopgateCart) { }
	
	public function redeemCoupons(ShopgateCart $shopgateCart) { }
	
	public function getSettings() { }
	
}
