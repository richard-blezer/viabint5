<?php
/*
 #########################################################################
 #                       Shogate GmbH
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # http://www.shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Rev: 567 $
 #
 # @author Martin Weber, Shopgate GmbH	weber@shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #########################################################################
 */

define('SHOPGATE_PLUGIN_REVISION', "\$Rev: 567 $");

class ShopgatePlugin extends ShopgatePluginCore {
	private $shopId = 0;
	
	private $permissionBlacklist = true;
	
	private $orderImportStatusId = 16;
	private $orderShippingApprovedStatusId = 33;
	
	public function startup() {
		global $db;
		
		$this->permissionBlacklist = _SYSTEM_GROUP_PERMISSIONS == 'blacklist';
		
		$this->shopId = $this->config["plugin_shop_id"];

		if(!empty($this->config["plugin_order_import_status"])
		&& $_orderImportStstusId = $this->config["plugin_order_import_status"]) {
			$_orderImportStstusId = $db->GetOne("SELECT status_id FROM `".TABLE_SYSTEM_STATUS."` WHERE status_class = 'order_status' AND status_id = $_orderImportStstusId");
			if(!empty($_orderImportStstusId)) $this->orderImportStatusId = $_orderImportStstusId;
		}
		
		return true;
	}
	
	////////////////////////////////////////////////////////////////////////////
	// Items CSV Export
	////////////////////////////////////////////////////////////////////////////
	
	/**
	 * (non-PHPdoc)
	 * @see lib/ShopgatePluginCore::createItemsCsv()
	 */
	protected function createItemsCsv() {
		global $db, $language, $price, $currency,$product;

		$qry = "
			SELECT 
				p.products_id,
				p.products_model,
				pd.products_name,
				pd.products_description,
				pd.products_keywords,
				p.products_ean,
				p.products_quantity,
				p.products_price,
				p.products_image,
				p.last_modified,
				p.products_weight,
				p.products_status,
				m.manufacturers_name,products_sort,
				ssd1.status_name AS shipping_status,
				ssd2.status_name AS vpe_status,
				p.products_tax_class_id,
				p.products_vpe_status,
				p.products_vpe,
				p.products_vpe_value,
				p.products_quantity,
				p.products_sort,
				p.products_master_flag,
				p.products_fsk18
			FROM ".TABLE_PRODUCTS." p
			JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON pd.products_id = p.products_id
			LEFT JOIN ".TABLE_MANUFACTURERS." m ON (m.manufacturers_id = p.manufacturers_id AND m.manufacturers_status = 1)
			LEFT JOIN ".TABLE_SYSTEM_STATUS_DESCRIPTION." ssd1 ON (p.products_shippingtime = ssd1.status_id AND ssd1.language_code = '".$language->code."')
			LEFT JOIN ".TABLE_SYSTEM_STATUS_DESCRIPTION." ssd2 ON (p.products_vpe = ssd2.status_id AND ssd2.language_code = '".$language->code."')
			LEFT JOIN ".TABLE_PRODUCTS_PERMISSION." pm ON (pm.pid = p.products_id AND pm.pgroup = 'shop_".$this->shopId."')
			WHERE p.products_digital = 0
			  AND p.products_status = 1
			  AND (p.date_available < NOW() OR p.date_available IS NULL)
			  AND (p.products_master_model = '' OR p.products_master_model IS NULL)
			  AND UPPER(pd.language_code) = UPPER('".$language->code."')
		";
		if($this->permissionBlacklist) {
			$qry .= "AND (pm.permission IS NULL OR pm.permission = 0)";
		} else {
			$qry .= "AND (pm.permission IS NOT NULL AND pm.permission = 1)";
		}
		
		$maxId = $db->GetOne("SELECT MAX(products_id) FROM ".TABLE_PRODUCTS);
		
		$limit = 100;
		$startId = 1;
		
		while ($startId <= $maxId) {
			
			$result = $db->Execute($qry." AND p.products_id BETWEEN $startId AND ".($startId+$limit));
			
			while(!$result->EOF) {
				$items = $this->buildItem($result->fields);
				
				if(is_array($items)) {
					foreach($items as $item) {
						$this->addItem($item);
					}
				}
				
				$result->MoveNext();
			}
			
			$startId += $limit+1;
			
		} 
	}
	
	/**
	 * 
	 * @param array $item
	 */
	private function buildItem($item) {
		global $db, $language, $price, $currency,$product;
		$categories = $this->getCategories($item);
		
		if(empty($categories) && (empty($item ["products_master_model"]))) return;
		
		$images = $this->getProductImages($item);
		
		$tax = $product->_getTax($item["products_tax_class_id"]);
		if(empty($tax["tax"]))
			$tax["tax"] = 19; //TODO
			
		$shipping_cost = 0;
		
		$unit_amount = round($item["products_price"]*(1+$tax["tax"]/100), 2);
		
		
		/// Staffelpreise
		///
		/// Staffelpreise werden ignoriert, wenn ein Sonderpreis gegeben ist
		///
		$_groupPrices = $this->getGroupPrices($item["products_id"]);
		$groupPrices = array();
		foreach($_groupPrices as $qty => $_price) {
			$_price = round($price*(1+$tax["tax"]/100), 2);
			$groupPrices[] = $qty . "=>" . $_price;
		}
		unset($_groupPrices);
		$groupPrices = implode("||", $groupPrices);
		
		
		$amount_info_text = null;
		if(!empty($item["products_vpe_status"]) && $item["products_vpe_value"] != 0) {
			$amount_info_text = $unit_amount / $item["products_vpe_value"];
			$amount_info_text = round($amount_info_text, 2);
			$amount_info_text = $price->_StyleFormat($amount_info_text);
			$amount_info_text = "$amount_info_text pro ".$item["vpe_status"];
		}
		
		
		$properties = $this->buildProperties($item);
		
		$specialPrice = $this->getSpecialPrice($item["products_id"]);
		if($specialPrice != "na") {
			$specialPrice = round($specialPrice*(1+$tax["tax"]/100), 2);
		
			if($specialPrice < $unit_amount) {
				$properties .= "||Angebot=>Statt $unit_amount € nur $specialPrice €";
				
				$unit_amount = $specialPrice;
				
				$groupPrices = ""; // Ignoriere Staffelpreise
			}
		}
				
		$itemArr = $this->buildDefaultRow();
		$itemArr['item_number'] 	= $item["products_id"];
		$itemArr['manufacturer'] 	= $item["manufacturers_name"];
		$itemArr['item_name'] 		= $item["products_name"];
		$itemArr['description'] 	= $item["products_description"];
		$itemArr['unit_amount'] 	= $unit_amount;
		$itemArr['available_text'] 	= $item["shipping_status"];
		$itemArr['url_deeplink'] 	= _SYSTEM_BASE_HTTP._SRV_WEB."index.php?page=product&info=".$item["products_id"];
		$itemArr['urls_images'] 	= $images;
		$itemArr['use_stock']		= $this->config["plugin_use_stock"]?'1':'0';
		$itemArr['categories'] 		= $categories;
		$itemArr['basic_price'] 	= $amount_info_text;
		$itemArr['weight'] 			= (empty($item["products_weight"])?0:$item["products_weight"]);
		$itemArr['tags'] 			= $item["products_keywords"];
		$itemArr['currency'] 		= $currency->code;
		$itemArr['tax_percent'] 	= $tax["tax"];
		$itemArr['ean'] 			= $item["ean_number"];
		$itemArr['last_update'] 	= $item["last_modified"];
		$itemArr['properties'] 		= $properties;
		$itemArr['stock_quantity'] 	= $item["products_quantity"];
		$itemArr['sort_order'] 		= $item["products_sort"];
		$itemArr['block_pricing'] 	= $groupPrices;
		$itemArr['age_rating'] 		= $item["products_fsk18"] == 1 ? '18' : '';
		$itemArr['shipping_costs_per_order'] 			= $shipping_cost;
		$itemArr['additional_shipping_costs_per_unit'] 	= 0;
		$itemArr['related_shop_item_numbers'] 			= $this->getRelatedShopItems($item["products_id"]);
		
		
		$variations = array();
		if($item["products_master_flag"] == "1"){
			$variations = $this->getVariations($item, &$itemArr);		
		}
		$items = array_merge(array($itemArr), $variations);
		
		return $items;
	} 

	
	private function getRelatedShopItems($products_id) {
		global $db;

		$qry = "
			SELECT p.products_id
			FROM ".TABLE_PRODUCTS_CROSS_SELL." xsell
			JOIN ".TABLE_PRODUCTS." p ON p.products_id = xsell.products_id_cross_sell
			JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." pcat ON pcat.products_id = p.products_id
			LEFT JOIN ".TABLE_PRODUCTS_PERMISSION." pm ON (pm.pid = p.products_id AND pm.pgroup = 'shop_".$this->shopId."')
			WHERE xsell.products_id = $products_id
			  AND p.products_digital = 0
			  AND p.products_status = 1
			  AND (p.date_available < NOW() OR p.date_available IS NULL)
			  AND (p.products_master_model = '' OR p.products_master_model IS NULL)
		";
		
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
	private function getSpecialPrice($productId) {
		global $db;
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
	private function getGroupPrices($productsId) {
		global $db;
		
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
			ORDER BY discount_quantity
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
	private function getProductImages($item) {
		$webPrefix = _SYSTEM_BASE_HTTP._SRV_WEB._SRV_WEB_IMAGES."org/";
		$images = array();
		if(!empty($item["products_image"]))
			$images[] = $webPrefix.$item["products_image"];
		
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
		while(!$result->EOF) {
			$images[] = $webPrefix.$result->fields["file"];
			$result->MoveNext();
		}
		
		return implode("||", $images);
	}
	
	/**
	 * Gibt den Kategoriebaum eines Produkts zurück
	 * 
	 * Wenn in einem Pfad eine Kategorie dekativiert ist, wird der gesamte Pfad ignoriert!
	 * 
	 * @param int $item
	 * @return string
	 */
	private function getCategories($item) {
		global $db;
		
		$qry = "
			SELECT
			ptc.categories_id
			FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
			LEFT JOIN ". TABLE_CATEGORIES_PERMISSION ." cm ON (cm.pid = ptc.categories_id AND cm.pgroup = 'shop_".$this->shopId."')
			WHERE ptc.products_id = ".$item["products_id"]."
		";
		
		if($this->permissionBlacklist) {
			$qry .= "AND (cm.permission IS NULL OR cm.permission = 0)";
		} else {
			$qry .= "AND (cm.permission IS NOT NULL AND cm.permission = 1)";
		}
		$result = $db->Execute($qry);
		
		$path = array();
		while(!$result->EOF) {
			$_path = $this->getCategoryPaths($result->fields["categories_id"]);
			
			if(!empty($_path))
				$path[] = $_path;
			
			$result->MoveNext();
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
	private function getCategoryPaths($catId) {
		global $db, $language;
		$path = "";

		if(empty($catId))
			return null;
			
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
				
		if(empty($result))
			return $path;
		
		if(empty($result["categories_status"]))
			return "";
			
		
		$path = $result["categories_name"];
		if(!empty($result["parent_id"])) {
			$_path = $this->getCategoryPaths($result["parent_id"]);
			if(empty($_path)) return "";
			
			
			$path = $_path.(!empty($path)?"=>":"").$path;
		}
		
		return $path;
	}
	
	private function getVariations($item, &$parent) {
		global $db, $language;
		
		$qry = "
			SELECT 
				p.products_id,
				p.products_model,
				pd.products_name,
				pd.products_description,
				pd.products_keywords,
				p.products_ean,
				p.products_quantity,
				p.products_price,
				p.products_image,
				p.last_modified,
				p.products_weight,
				p.products_status,
				m.manufacturers_name,
				p.products_sort,
				ssd1.status_name AS shipping_status,
				ssd2.status_name AS vpe_status,
				p.products_tax_class_id,
				p.products_vpe_status,
				p.products_vpe,
				p.products_vpe_value,
				p.products_quantity,
				p.products_sort,
				ptc.categories_id,
				p.products_master_model
			FROM ".TABLE_PRODUCTS." p
			LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON pd.products_id = p.products_id
			LEFT JOIN ".TABLE_MANUFACTURERS." m ON m.manufacturers_id = p.manufacturers_id
			LEFT JOIN ".TABLE_SYSTEM_STATUS_DESCRIPTION." ssd1 ON (p.products_shippingtime = ssd1.status_id AND ssd1.language_code = '".$language->code."')
			LEFT JOIN ".TABLE_SYSTEM_STATUS_DESCRIPTION." ssd2 ON (p.products_vpe = ssd2.status_id AND ssd2.language_code = '".$language->code."')
			LEFT JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." ptc ON ptc.products_id = p.products_id
			WHERE p.products_master_flag = 0
			  AND p.products_digital = 0
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
			$itemArr = $this->buildItem($item);
			$itemArr = $itemArr[0];
			
			/* Wenn Kindprodukt kein eigenes Bild besitzt, nutze das Bild des Elternproduktes falls vorhanden */
			if(empty($itemArr["urls_images"]) && !empty($parent["urls_images"])){
				$itemArr["urls_images"] = $parent["urls_images"];
			}
			
			$itemArr["categories"] = $parent["categories"];
			$itemArr["parent_item_number"] = $parent["item_number"];
			
			$options = $this->getOptions($item);

			foreach($options as $option) {
				$parent["attribute_".$i] = $option["attributes_basename"];
				$itemArr["attribute_".$i] = $option["attributes_name"];
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
	private function getOptions($item) {
		global $db;
		
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
			  ON (ad.attributes_id = a.attributes_id AND ad.language_code = 'de') 
			JOIN ".DB_PREFIX."_plg_products_to_attributes pta
			  ON (pta.attributes_id = a.attributes_id
			 AND  pta.attributes_parent_id = a.attributes_parent)
			 
			JOIN ".DB_PREFIX."_plg_products_attributes a2
			  ON (a2.attributes_parent = 0
			 AND a2.attributes_id = a.attributes_parent) 
			JOIN ".DB_PREFIX."_plg_products_attributes_description ad2
			  ON (ad2.attributes_id = a2.attributes_id AND ad2.language_code = 'de') 
			LEFT JOIN ".DB_PREFIX."_plg_products_to_attributes pta2
			  ON (pta2.attributes_id = a2.attributes_id
			 AND  pta2.attributes_parent_id = a2.attributes_parent)
			 
			WHERE a.status = 1
			  AND pta.products_id = ".$item["products_id"]."
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
				
				$review["item_number"] 		= $result->fields["products_id"];
				$review["update_review_id"] = $result->fields["review_id"];
				$review["score"] 			= $result->fields["review_rating"] * 2;
				$review["name"] 			= $result->fields["firstname"]." ".$result->fields["lastname"];
				$review["date"] 			= $result->fields["review_date"];
				$review["title"] 			= $result->fields["review_title"];
				$review["text"] 			= $result->fields["review_text"];
				
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
	
	public function getUserData($user, $pass) {
		global $db;
		$qry = "
			SELECT
				customer.customers_id,
				customer.customers_password,
				customer.customers_email_address,
				address.customers_phone,
				address.customers_gender,
				address.customers_company,
				address.customers_firstname,
				address.customers_lastname,
				address.customers_street_address,
				address.customers_postcode,
				address.customers_city,
				address.customers_country_code
			FROM " . TABLE_CUSTOMERS ." AS customer
			JOIN " . TABLE_CUSTOMERS_ADDRESSES ." AS address
			  ON customer.customers_id = address.customers_id
			WHERE customers_email_address = '" . $user . "'
			  AND customers_password = '".md5($pass)."'
			LIMIT 1
		";

		$result = $db->Execute($qry);
		
		if ($result->RowCount() == 0) {
			throw new ShopgateFrameworkException("Veyton ShopgatePlugin - Invalid Login Data for User.");
		} else {
			$customer = $result->fields;
			$userData = new ShopgateShopCustomer();
			$userData->setCustomerNumber($customer["customers_id"]);
			$userData->setSurname($customer["customers_lastname"]);
			$userData->setFirstName($customer["customers_firstname"]);
			$userData->setCity($customer["customers_city"]);
			$userData->setMobile("");
			$userData->setPhone($customer["customers_telephone"]);
			$userData->setZip($customer["customers_postcode"]);
			$userData->setStreet($customer["customers_street_address"]);
			$userData->setGender($customer["customers_gender"]=="m"?ShopgateShopCustomer::MALE:ShopgateShopCustomer::FEMALE);
			$userData->setCountry($customer["countries_iso_code_2"]);
			$userData->setCompany($customer["customers_company"]);
			$userData->setMail($customer["customers_email_address"]);

			return $userData;
		}

		return false;
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
	public function saveOrder(ShopgateOrder $order) {
		global $db;
		
		/* prüfe ob die Shopgate-Bestellung schon existiert */
		$orderNumber = $order->getOrderNumber();
		$qry = "
			SELECT
				*
			FROM ".TABLE_SHOPGATE_ORDERS."
			WHERE shopgate_order_number = '".$orderNumber."'
		";
		$result = $db->GetRow($qry);
		if(!empty($result)){
			/* Bestellung mit der Bestellnummer ist schon vorhanden */
			throw new ShopgateFrameworkException("Veyton ShopgatePlugin - Bestellung mit der Bestellnummer $orderNumber schon vorhanden!");
		}
		
		$customerAddress = $order->getCustomerAddress();
		$deliveryAddress = $order->getDeliveryAddress();
		$billingAddress = $order->getInvoiceAddress();
		
		$phone = $order->getCustomerPhone();
		if(empty($phone))
			$phone = $order->getCustomerMobile();
		
		$user = $this->loadOrderUser($order);
		
		$gender = "";
		if(isset($user["addresses"][0]))
			$gender = $user["addresses"][0]["customers_gender"];
		
		$orderArr['customers_id']=$user["customers_id"];
//		$orderArr['customers_cid']=0;
//		$orderArr['customers_vat_id']=0;
		$orderArr['customers_status']=$user["customers_status"];
		$orderArr['customers_email_address']=$order->getCustomerMail();
		
		$orderArr['delivery_gender']=$gender;
		$orderArr['delivery_phone']=$phone;
		$orderArr['delivery_fax']="";
		$orderArr['delivery_firstname']=$deliveryAddress->getFirstName();
		$orderArr['delivery_lastname']=$deliveryAddress->getSurname();
		$orderArr['delivery_company']=$deliveryAddress->getCompany();
//		$orderArr['delivery_company_2']="";
//		$orderArr['delivery_company_3']="";
		$orderArr['delivery_street_address']=$deliveryAddress->getStreet();
//		$orderArr['delivery_suburb']="";
		$orderArr['delivery_city']=$deliveryAddress->getCity();
		$orderArr['delivery_postcode']=$deliveryAddress->getZipcode();
//		$orderArr['delivery_zone']="";
//		$orderArr['delivery_zone_code']="";
		$orderArr['delivery_country']="Deutschland";
		$orderArr['delivery_country_code']=$deliveryAddress->getCountry();
//		$orderArr['delivery_address_book_id']="NULL";
		
		$orderArr['billing_gender']=$gender;
		$orderArr['billing_phone']=$phone;
		$orderArr['billing_fax']="";
		$orderArr['billing_firstname']=$deliveryAddress->getFirstName();
		$orderArr['billing_lastname']=$deliveryAddress->getSurname();
		$orderArr['billing_company']=$deliveryAddress->getCompany();
		$orderArr['billing_company_2']="";
		$orderArr['billing_company_3']="";
		$orderArr['billing_street_address']=$deliveryAddress->getStreet();
		$orderArr['billing_suburb']="";
		$orderArr['billing_city']=$deliveryAddress->getCity();
		$orderArr['billing_postcode']=$deliveryAddress->getZipcode();
		$orderArr['billing_zone']="";
		$orderArr['billing_zone_code']="";
		$orderArr['billing_country']="Deutschland";
		$orderArr['billing_country_code']=$deliveryAddress->getCountry();
//		$orderArr['billing_address_book_id']="NULL";
		
		$orderArr['payment_code']="shopgate";
		$orderArr['subpayment_code']="";
		$orderArr['shipping_code']="Standard";
		$orderArr['currency_code']=$order->getOrderCurrency();
		$orderArr['currency_value']="EUR";
		$orderArr['language_code']="DE";
		$orderArr['comments']="Added By Shopgate" . date("Y-m-d h:i:s");
		$orderArr['last_modified']=date("Y-m-d h:i:s");
		$orderArr['date_purchased']=date("Y-m-d h:i:s");
		$orderArr['orders_status']=$this->orderImportStatusId;
//		$orderArr['orders_date_finished']="";
		$orderArr['account_type']=$user["account_type"];
		$orderArr['allow_tax']=true;
		$orderArr['customers_ip']="127.0.0.1";
		$orderArr['shop_id']=$this->config['plugin_shop_id'];
		$orderArr['orders_data']=date("Y-m-d h:i:s");
//		$orderArr['campaign_id']="0";
		
		$valueString = "";
		$keyString = "";
		foreach($orderArr as $key=>$value) {
			if(!empty($keyString)) $keyString.=",\n";
			if(!empty($valueString)) $valueString.=",\n";
			$keyString.="`".$key."`";
			$valueString.="'".$value."'";
		}
		
		$qry = "INSERT INTO ".TABLE_ORDERS."\n ($keyString)\n VALUES\n ($valueString)";
		$result = $db->Execute($qry);
		
		$dbOrderId = $db->Insert_ID();
		$this->insertOrderItems($order, $dbOrderId);
		$this->insertOrderTotal($order, $dbOrderId);
		$this->insertOrderStatus($order, $dbOrderId);
		
		$qry = "
			INSERT INTO ".TABLE_SHOPGATE_ORDERS." (`orders_id`, `shopgate_order_number`) VALUES
			('".$dbOrderId."', '".$order->getOrderNumber()."')
		";
		$db->Execute($qry);
		
		$qry = "
			INSERT INTO ".TABLE_ORDERS_STATS." (orders_id, orders_stats_price, products_count) VALUES
			($dbOrderId, ".($order->getAmountComplete()/100).", ".count($order->getOrderItems()).")
		";
		$db->Execute($qry);
	}
	
	private function insertOrderItems(ShopgateOrder $order, $dbOrderId) {
		global $db;
		$items = $order->getOrderItems();

		foreach($items as $item) {
			$orderInfo = $item->getInternalOrderInfo(true);
			
			$products_model = $item->getItemNumber();

			if(isset($orderInfo["base_item_number"]))
				$products_model = $orderInfo["base_item_number"];
			
			$product = $this->loadProduct($products_model);
			
			$itemArr["orders_id"] = $dbOrderId;
			$itemArr["products_id"] = $product['products_id'];
			$itemArr["products_model"] = $product['products_model'];
			$itemArr["products_name"] = $item->getName();
			$itemArr["products_price"] = $item->getUnitAmount()/100;
			$itemArr["products_tax"] = $item->getTaxPercent();
			$itemArr["products_tax_class"] = "1";
			$itemArr["products_quantity"] = $item->getQuantity();
			$itemArr["allow_tax"] = true;
			
			$valueString = "";
			$keyString = "";
			foreach($itemArr as $key=>$value) {
				if(!empty($keyString)) $keyString.=",";
				if(!empty($valueString)) $valueString.=",";
				$keyString.="`".$key."`";
				$valueString.="'".$value."'";
			}
		
			$qry = "INSERT INTO ".TABLE_ORDERS_PRODUCTS."\n ($keyString)\n VALUES\n ($valueString)";
			$db->Execute($qry);
		}
	}
	
	private function insertOrderTotal(ShopgateOrder $order, $dbOrderId) {
		global $db;
		$totalArr = array();
		$totalArr['orders_id'] = $dbOrderId;
		$totalArr['orders_total_key'] = 'shipping';
		$totalArr['orders_total_key_id'] = '1';
		$totalArr['orders_total_model'] = 'Standard';
		$totalArr['orders_total_name'] = 'Standard';
		$totalArr['orders_total_price'] = ($order->getAmountShipping()/1.2)/100;
		$totalArr['orders_total_tax'] = "20"; // TODO
		$totalArr['orders_total_tax_class'] = "1"; // TODO
		$totalArr['orders_total_quantity'] = "1"; // nur einmal Versand berechnen != count($order->getOrderItems());
		$totalArr['allow_tax'] = true;
		
		$valueString = "";
		$keyString = "";
		foreach($totalArr as $key=>$value) {
			if(!empty($keyString)) $keyString.=",";
			if(!empty($valueString)) $valueString.=",";
			$keyString.="`".$key."`";
			$valueString.="'".$value."'";
		}
	
		$qry = "INSERT INTO ".TABLE_ORDERS_TOTAL."\n ($keyString)\n VALUES\n ($valueString)";
		$db->Execute($qry);
	}
	 
	private function insertOrderStatus(ShopgateOrder $order, $dbOrderId) {
		global $db;
		
		$statusArray = array();
		$statusArr = array();
		
		$statusArr['orders_id'] = $dbOrderId;
		$statusArr['orders_status_id'] = $this->orderImportStatusId;
		$statusArr['customer_notified'] = true;
		$statusArr['date_added'] = date("Y-m-d h:i:s");
		$statusArr['comments'] = 'Bestellung von Shopgate hinzugefügt.';
		$statusArr['change_trigger'] = 'shopgate';
		$statusArr['callback_id'] = '0';
		$statusArr['customer_show_comment'] = true;
		$statusArray[] = $statusArr;
		$statusArr['customer_notified'] = false;
		$statusArr['comments'] = 'Bestellnummer: '. $order->getOrderNumber();
		$statusArray[] = $statusArr;
		
		
		$values = "";
		foreach($statusArray as $status) {
			$valueString = "";
			$keyString = "";
			foreach($status as $key=>$value) {
				if(!empty($keyString)) $keyString.=",";
				if(!empty($valueString)) $valueString.=",";
				$keyString.="`".$key."`";
				$valueString.="'".$value."'";
			}
			
			if(!empty($values)) $values.=",\n ";
			$values .= "($valueString)";
		}
	
		$qry = "INSERT INTO ".TABLE_ORDERS_STATUS_HISTORY."\n ($keyString)\n VALUES\n $values";
		$db->Execute($qry);
	} 
	
	private function loadProduct($products_model) {
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
	
	private function loadOrderUser(ShopgateOrder $order) {
		global $db;
		
		$userid = $order->getExternalCustomerNumber();
		if(empty($userid)) return null;
		
		$qry = "
			SELECT
			c.*
			FROM ".TABLE_CUSTOMERS." c
			WHERE c.customers_id = ".$userid."
		";

		$result = $db->Execute($qry);
		$user = $result->fields;
		
		$qry = "
			SELECT
			ca.*
			FROM ".TABLE_CUSTOMERS_ADDRESSES." ca
			WHERE ca.customers_id = ".$userid."
		";

		$result = $db->Execute($qry);
		while(!$result->EOF) {
			$user["addresses"][] = $result->fields;
			$result->MoveNext();
		}
		
		return $user;
	}
	
	private function setOrderAsShipped($order) {
		$orderAPI = new ShopgateOrderApi();
		$orderAPI->setShippingComplete($order);
	}
	
	private function buildProperties($product) {
		$properties = array();
		
		if(!empty($product["products_model"]))
			$properties[] = "Artikelnummer=>$product[products_model]";
			
		$properties = implode("||", $properties);
		return $properties;
	}	
}
