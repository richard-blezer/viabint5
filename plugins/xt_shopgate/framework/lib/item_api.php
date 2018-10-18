<?php 
class ShopgateItemApi extends ShopgateCoreApi {
	
	public function updateItem($item_number, ShopgateShopItem $item) {
		$params = array(
			"item_number"=>$item_number,
		);
		
		foreach($item->getArray() as $key=>$value)
			$params["item_arr[$key]"] = $value;
		
		$this->_execute("items_update", $params);
	}
	
	/**
	 * Ein Array vom Typ ShopgateShopItem updaten 
	 * 
	 * Die maximale größe des array beträgt 1000
	 * 
	 * @param array $items
	 */
	public function updateMultipleItems($items) {
		$params = array();
		
		if(count($items) > 1000) throw new ShopgateFrameworkException("Zu viele Items füe die Stapelverarbeitung");
		
		$i = 0;
		foreach($items as $item_number=>$item) {
			$item = $item->getArray();
			$_params = $this->buildPostParams($item);
			
			$params["items[$i][item_number]"] = $item_number;
			foreach($_params as $key=>$value) {
				$params["items[$i][item_arr]$key"] = $value;
			}
			$i++;
		}
				
		$this->_execute("items_batch_update", $params);
	}
	
	private function buildPostParams($array) {
		$params = array();

		foreach($array as $key=>$value) {
			if(is_array($value)) {
				$temp = $this->buildPostParams($value);
	
				foreach($temp as $key2=>$value2)
					$params["[$key]".$key2] = $value2;
			}
			else
				$params["[$key]"] = $value;
		}

		return $params;
	}
}

/**
 * To Use This, enter the Configuration to ShopgateConfiguration-Class
 * The ShopgateShopItem<br />
 * Required fields<br />
 * <ul>
 * <li>currency</li>
 * <li>name</li>
 * <li>item_number</li>
 * <li>tax</li>
 * <li>amount</li>
 * </ul>
 *
 * @author Martin Weber
 * @version 1.0
 * @category Shopgate API
 * @category Shopgate GmbH
 */
class ShopgateShopItem {
	// Required fields
	/**
	 * <b>Required</b><br/>
	 * Currency of the item. eg. EUR, USD...
	 * @var string
	 */
	private $currency = null;
	/**
	 * <b>Required</b><br/>
	 * The productname
	 * @var string
	 */
	private $name = null;
	/**
	 * <b>Required</b><br/>
	 * The item number. It must be unique in a shop
	 * @var string
	 */
	private $item_number = null;
	/**
	 * <b>Required</b><br/>
	 * The tax in percent
	 * @var decimal
	 */
	private $tax = null;
	/**
	 * <b>Required</b><br/>
	 * The price of the product.
	 * In EUR it must be given in cent.
	 * @var int
	 */
	private $amount = null;
	/**
	 * The identifier of the category where the Item placed in.
	 * 
	 * @var int
	 */
	private $category_number = null;
	
	//Optional Parameters
	/**
	 * The manufacturer of the Item.
	 * @var string
	 */
	private $manufacturer = null;
	/**
	 * The manufacturer item number of the Item.
	 * @var string
	 */
	private $manufacturer_number = null; 
//	private $relevance;
	/**
	 * The Manufacturer's suggested retail price.
	 * @var int
	 */
	private $msrp = null;
	/**
	 * The Shipping Cost
	 * @var int
	 */
	private $shipping_costs = null;
//	private $age_rating;
	/**
	 * The weight of the product in gramms
	 * @var int
	 */
	private $weight = null;
	/**
	 * Is the item is free for shipping.
	 * @var bool
	 */
	private $free_shipping = null;
	/**
	 * The EAN-Number<br/>
	 * The length is 8 or 13 Digits
	 * @see http://en.wikipedia.org/wiki/European_Article_Number
	 * @var string
	 */
	private $ean = null;
	/**
	 * The International Standard Book Number
	 * @see http://en.wikipedia.org/wiki/ISBN
	 * @var string
	 */
	private $isbn = null;
	/**
	 * A description. It can be formatted with HTML
	 * @var string
	 */
	private $description = null;
	/**
	 * Is the item currenty available
	 * @var bool
	 */
	private $available = null;
	/**
	 * The stock quantity 
	 * @var int
	 */
	private $stock_quantity = null;
	/**
	 * Ignore the stock
	 * @var bool
	 */
	private $ignore_stock = null;
	/**
	 * Is the item is active
	 * @var bool
	 */
	private $active = null;
	
	/**
	 * The number of the parent_item<br />
	 * If this element is the root-element or isn't there any children, keep it emoty or null 
	 * @var string
	 */
	private $parent_item_number = null;
	
	private $hasVariations = null;
	
	private $attribute_1 = null;
	private $attribute_2 = null;
	private $attribute_3 = null;
	private $attribute_4 = null;
	private $attribute_5 = null;
	
	/**
	 * The variation of the product (max. 5 fields)
	 * If this is the root-element its contains only the name of the variation
	 * If this ShopItem is a variation, it contains the values
	 * 
	 * @var array
	 */
	private $variations = array();
	
	/**
	 * Sets the currency value
	 *
	 * @param string $currency
	 * @return void
	 */
	public function setCurrency($currency) {
		$this->currency = $currency;
	}
	
	/**
	 * Returns the currency value
	 *
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}
	
	/**
	 * Sets the name value
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	/**
	 * Returns the name value
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Sets the item_number value
	 *
	 * @param string $item_number
	 * @return void
	 */
	public function setItemNumber($item_number) {
		$this->item_number = $item_number;
	}
	
	/**
	 * Returns the item_number value
	 *
	 * @return string
	 */
	public function getItemNumber() {
		return $this->item_number;
	}
	
	/**
	 * Sets the tax value
	 *
	 * @param decimal $tax
	 * @return void
	 */
	public function setTax($tax) {
		$this->tax = $tax;
	}
	
	/**
	 * Returns the tax value
	 *
	 * @return decimal
	 */
	public function getTax() {
		return $this->tax;
	}
	
	/**
	 * Sets the amount value
	 *
	 * @param int $amount
	 * @return void
	 */
	public function setAmount($amount) {
		$this->amount = $amount;
	}
	
	/**
	 * Returns the amount value
	 *
	 * @return int
	 */
	public function getAmount() {
		return $this->amount;
	}
	
	/**
	 * Sets the category_number value
	 *
	 * @param int $category_number
	 * @return void
	 */
	public function setCategoryNumber($category_number) {
		$this->category_number = $category_number;
	}
	
	/**
	 * Returns the category_number value
	 *
	 * @return int
	 */
	public function getCategoryNumber() {
		return $this->category_number;
	}
	
	/**
	 * Sets the manufacturer value
	 *
	 * @param string $manufacturer
	 * @return void
	 */
	public function setManufacturer($manufacturer) {
		$this->manufacturer = $manufacturer;
	}
	
	/**
	 * Returns the manufacturer value
	 *
	 * @return string
	 */
	public function getManufacturer() {
		return $this->manufacturer;
	}
	
	/**
	 * Sets the manufacturer_number value
	 *
	 * @param string $manufacturer_number
	 * @return void
	 */
	public function setManufacturerNumber($manufacturer_number) {
		$this->manufacturer_number = $manufacturer_number;
	}
	
	/**
	 * Returns the manufacturer_number value
	 *
	 * @return string
	 */
	public function getManufacturerNumber() {
		return $this->manufacturer_number;
	}
	
	/**
	 * Sets the msrp value
	 *
	 * @param int $msrp
	 * @return void
	 */
	public function setMsrp($msrp) {
		$this->msrp = $msrp;
	}
	
	/**
	 * Returns the msrp value
	 *
	 * @return int
	 */
	public function getMsrp() {
		return $this->msrp;
	}
	
	/**
	 * Sets the shipping_costs value
	 *
	 * @param int $shipping_costs
	 * @return void
	 */
	public function setShippingCosts($shipping_costs) {
		$this->shipping_costs = $shipping_costs;
	}
	
	/**
	 * Returns the shipping_costs value
	 *
	 * @return int
	 */
	public function getShippingCosts() {
		return $this->shipping_costs;
	}
	
	/**
	 * Sets the weight value
	 *
	 * @param int $weight
	 * @return void
	 */
	public function setWeight($weight) {
		$this->weight = $weight;
	}
	
	/**
	 * Returns the weight value
	 *
	 * @return int
	 */
	public function getWeight() {
		return $this->weight;
	}
	
	/**
	 * Sets the free_shipping value
	 *
	 * @param bool $free_shipping
	 * @return void
	 */
	public function setFreeShipping($free_shipping) {
		$this->free_shipping = $free_shipping;
	}
	
	/**
	 * Returns the free_shipping value
	 *
	 * @return bool
	 */
	public function getFreeShipping() {
		return $this->free_shipping;
	}
	
	/**
	 * Sets the ean value
	 *
	 * @param string $ean
	 * @return void
	 */
	public function setEAN($ean) {
		$this->ean = $ean;
	}
	
	/**
	 * Returns the ean value
	 *
	 * @return string
	 */
	public function getEAN() {
		return $this->ean;
	}
	
	/**
	 * Sets the isbn value
	 *
	 * @param string $isbn
	 * @return void
	 */
	public function setISBN($isbn) {
		$this->isbn = $isbn;
	}
	
	/**
	 * Returns the isbn value
	 *
	 * @return string
	 */
	public function getISBN() {
		return $this->isbn;
	}
	
	/**
	 * Sets the description value
	 *
	 * @param string $description
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}
	
	/**
	 * Returns the description value
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Sets the available value
	 *
	 * @param bool $available
	 * @return void
	 */
	public function setAvailable($available) {
		$this->available = $available;
	}
	
	/**
	 * Returns the available value
	 *
	 * @return bool
	 */
	public function getAvailable() {
		return $this->available;
	}
	
	/**
	 * Sets the stock_quantity value
	 *
	 * @param int $stock_quantity
	 * @return void
	 */
	public function setStockQuantity($stock_quantity) {
		$this->stock_quantity = $stock_quantity;
	}
	
	/**
	 * Returns the stock_quantity value
	 *
	 * @return int
	 */
	public function getStockQuantity() {
		return $this->stock_quantity;
	}
	
	/**
	 * Sets the ignore_stock value
	 *
	 * @param bool $ignore_stock
	 * @return void
	 */
	public function setIgnoreStock($ignore_stock) {
		$this->ignore_stock = $ignore_stock;
	}
	
	/**
	 * Returns the ignore_stock value
	 *
	 * @return bool
	 */
	public function getIgnoreStock() {
		return $this->ignore_stock;
	}
	
	/**
	 * Sets the active value
	 *
	 * @param bool $active
	 * @return void
	 */
	public function setActive($active) {
		$this->active = $active;
	}
	
	/**
	 * Returns the active value
	 *
	 * @return bool
	 */
	public function getActive() {
		return $this->active;
	}
	
	/**
	 * Sets the parent_item_number value
	 *
	 * @param string $parent_item_number
	 * @return void
	 */
	public function setParentItemNumber($parent_item_number) {
		$this->parent_item_number = $parent_item_number;
	}
	
	/**
	 * Returns the parent_item_number value
	 *
	 * @return string
	 */
	public function getParentItemNumber() {
		return $this->parent_item_number;
	}

	/**
	 * Sets the attribute_1 value
	 *
	 * @param string $attribute_1
	 * @return void
	 */
	public function setAttribute1($attribute_1) {
		$this->attribute_1 = $attribute_1;
	}
	
	/**
	 * Returns the attribute_1 value
	 *
	 * @return string
	 */
	public function getAttribute1() {
		return $this->attribute_1;
	}
	
	/**
	 * Sets the attribute_2 value
	 *
	 * @param string $attribute_2
	 * @return void
	 */
	public function setAttribute2($attribute_2) {
		$this->attribute_2 = $attribute_2;
	}
	
	/**
	 * Returns the attribute_2 value
	 *
	 * @return string
	 */
	public function getAttribute2() {
		return $this->attribute_2;
	}
	
	/**
	 * Sets the attribute_3 value
	 *
	 * @param string $attribute_3
	 * @return void
	 */
	public function setAttribute3($attribute_3) {
		$this->attribute_3 = $attribute_3;
	}
	
	/**
	 * Returns the attribute_3 value
	 *
	 * @return string
	 */
	public function getAttribute3() {
		return $this->attribute_3;
	}
	
	/**
	 * Sets the attribute_4 value
	 *
	 * @param string $attribute_4
	 * @return void
	 */
	public function setAttribute4($attribute_4) {
		$this->attribute_4 = $attribute_4;
	}
	
	/**
	 * Returns the attribute_4 value
	 *
	 * @return string
	 */
	public function getAttribute4() {
		return $this->attribute_4;
	}
	
	/**
	 * Sets the attribute_5 value
	 *
	 * @param string $attribute_5
	 * @return void
	 */
	public function setAttribute5($attribute_5) {
		$this->attribute_5 = $attribute_5;
	}
	
	/**
	 * Returns the attribute_5 value
	 *
	 * @return string
	 */
	public function getAttribute5() {
		return $this->attribute_5;
	}
	
	/**
	 * Returns the hasVariations value
	 *
	 * @return bool
	 */
	public function getHasVariations($data=array()) {
		return $this->hasVariations;
		if(!empty($data))
			$this->_parseShopItem($data);
	}
	
	
	public function __construct($data=array()) {
		if(!empty($data))
			$this->_parseShopItem($data);
	}

	/**
	 * The variations of the product
	 * @return array
	 */
	public function getVariations() {
		return $this->variations;
	}
	
	/**
	 * Save the variation and added it to the list. It will save the current item
	 * @param ShopgateShopItem $variation
	 * @return bool
	 */
	public function addVariation(ShopgateShopItem $variation) {
		if(!empty($this->parent_item_number))
			return false;
		
		$this->save();
		
		$variation->setParentItemNumber($this->getParentItemNumber());
		$variation->save();
		
		$this->variations[] = $variation;
		
		return true;
	}

	/**
	 * Remove a variation from the list
	 * @param ShopgateShopItem $variation
	 * @return unknown_type
	 */
	public function removeVariation(ShopgateShopItem $variation) {
		$variation->delete();
		foreach($this->variations as $key=>$_variation)
			if($_variation->getItemNumber() == $variation->getItemNumber())
				unset($this->variations[$key]);
	}
	
	private function _parseShopItem($item) {
		$this->active = $item["is_active"];
		$this->amount = $item["unit_amount_with_tax"];
		$this->currency = $item["currency_id"];
		$this->description = $item["description"];
		$this->available = $item["is_available"];
		$this->category_number = null;
		$this->ean = $item["ean"];
		$this->free_shipping = $item["is_free_shipping"];
		$this->ignore_stock = $item["ignore_stock"];
		$this->isbn = $item["isbn"];
		$this->item_number = $item["shop_item_number"];
		$this->manufacturer = $item["manufacturer"];
		$this->manufacturer_number = $item["manufacturer_item_number"];
		$this->msrp = $item["msrp"];
		$this->name = $item["name"];
		$this->parent_item_number = "";
		$this->stock_quantity = $item["stock_quantity"];
		$this->tax = $item["tax_percent"];
		$this->variations = array();
		$this->weight = $item["weight"];
		$this->hasVariations = $item["has_children"];
		
		if($this->hasVariations && isset($item["childrens"])) {
			foreach($item["childrens"] as $_variation) {
				$this->variations[] = new ShopgateShopItem($_variation);
			}
		}
	}
	
	public function getArray() {
		$obj = get_object_vars($this);
		$array = array();
		
		foreach($obj as $key=>$value) {
			if($value === null)
				continue;
			
			$array[$key] = $value;
		}
		
		return $array;
	}
}
//
//
///**
// * To use this, enter the configuration to ShopgateConfiguration-Class
// * 
// * An interface to the Shopgate ShopCategories
// * 
// * @author Martin Weber
// * @version 1.0.0
// */
//class ShopgateShopCategory {
//	private $name = "";
//	private $order = 0;
//	private $category_number = null;
//	private $description = "";
//	private $path = "";
//	private $active = true;
//	private $image = "";
//	
//	/**
//	 * array of child items
//	 * 
//	 * @var array
//	 */
//	private $childs = array();
//	
//	/**
//	 * Sets the name value
//	 *
//	 * @param stirng $name
//	 * @return void
//	 */
//	public function setName($name) {
//		$this->name = $name;
//	}
//	
//	/**
//	 * Returns the name value
//	 *
//	 * @return stirng
//	 */
//	public function getName() {
//		return $this->name;
//	}
//	
//	/**
//	 * Sets the order value
//	 *
//	 * @param int $order
//	 * @return void
//	 */
//	public function setOrder($order) {
//		$this->order = $order;
//	}
//	
//	/**
//	 * Returns the order value
//	 *
//	 * @return int
//	 */
//	public function getOrder() {
//		return $this->order;
//	}
//	
//	/**
//	 * Returns the category_number value
//	 *
//	 * @return int
//	 */
//	public function getCategoryNumber() {
//		return $this->category_number;
//	}
//	
//	/**
//	 * Returns the path value
//	 *
//	 *  @return string
//	 */
//	public function getPath() {
//		return $this->path;
//	}
//	
//	/**
//	 * Sets the active value
//	 *
//	 * @param bool $active
//	 * @return void
//	 */
//	public function setActive($active) {
//		$this->active = $active;
//	}
//	
//	/**
//	 * Returns the active value
//	 *
//	 * @return bool
//	 */
//	public function getActive() {
//		return $this->active;
//	}
//	
//	/**
//	 * Sets the description value
//	 *
//	 * @param string $description
//	 * @return void
//	 */
//	public function setDescription($description) {
//		$this->description = $description;
//	}
//	
//	/**
//	 * Returns the description value
//	 *
//	 * @return string
//	 */
//	public function getDescription() {
//		return $this->description;
//	}
//	
//	/**
//	 * Returns the childs value
//	 *
//	 * @return array
//	 */
//	public function getChilds() {
//		return $this->childs;
//	}
//	
//	/**
//	 * Sets the image value
//	 *
//	 * @param string $images
//	 * @return void
//	 */
//	public function setImage($image) {
//		$this->image = $image;
//	}
//	
//	/**
//	 * Returns the images value
//	 *
//	 * @return string
//	 */
//	public function getImage() {
//		return $this->image;
//	}
//	
//	public function __construct() {
//		parent::__construct();
//	}
//	
//	/**
//	 * Save the category
//	 * @param ShopgateShopCategory $parent_category
//	 */
//	public function save(ShopgateShopCategory $parent_category = null) {
//		$params = array(
//			'category_number' => $this->category_number,
//			'name' => $this->getName(),
//			'description' => $this->getDescription(),
//			'image'=>$this->getImage(),
//		);
//		
//		if(!empty($parent_category))
//			$params['parent_number'] = $parent_category->getCategoryNumber();
//			
//		$params = $this->_transformArray("categories[0]", $params);
//		$this->_execute('categories_save', $params);
//	}
//	
//	/**
//	 * Delete a category. 
//	 * 
//	 * @param $category
//	 */
//	public function delete($deleteSubcategories = false, $deleteItems=false) {
//		$params = array(
//			'category_number'=>$this->category_number,
//			'delete_subcategories' => $deleteSubcategories,
//			'delete_items' => $deleteItems,
//		);
//		
//		debug($this->_execute('categories_delete', $params));
//	} 
//	
//	/**
//	 * Added a child to a category. The child will be save on the server.
//	 * 
//	 * @param $category
//	 */
//	public function addChild(ShopgateShopCategory $category) {
//		$category->save($this);
//		$this->childs[] = $category;
//	}
//	
//	/**
//	 * 
//	 * Insert a ShopgateShopItem to this category
//	 * 
//	 * @param ShopgateShopItem $item
//	 */
//	public function addShopgateShopItem(ShopgateShopItem $item) {
//		$params = array(
//			'category_number' => $this->getCategoryNumber(),
//			'item_number' => $item->getItemNumber()
//		);
//		$this->_execute("categories_add_item", $params);
//	}
//	
//	/**
//	 * Load the full category-tree
//	 */
//	public function loadTree() {
//		$tree = $this->_execute("categories_get_tree", array());
//		$tree = $tree["category_tree"];
//		
//		foreach($tree as $node) {
//			$this->category_number = $node["id"];
//			$this->name = $node["name"];
//			$this->order = $node["order"];
//			$this->active = $node["active"];
//			
//			if(isset($node["categories"])) {
//				foreach($node["categories"] as $child) {
//					$this->childs[] = $this->buildTree($child);
//				}
//			}
//		}
//	}
//
//	/**
//	 * Build the object tree
//	 * @param array $node
//	 * @return ShopgateShopCategory
//	 */
//	private function buildTree($node) {
//		$child = new ShopgateShopCategory();
//		$child->category_number = $node["id"];
//		$child->name = $node["name"];
//		$child->order = $node["order"];
//		$child->active = $node["active"];
//		
//		if(isset($node["categories"])) {
//			foreach($node["categories"] as $_child) {
//				$child->childs[] = $child->buildTree($_child);
//			}
//		}
//		
//		return $child;
//	}
//}

