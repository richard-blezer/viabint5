<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

class MLProduct {
	private static $instance = null;
	
	protected $languagesAvailable = array();
	protected $languagesSelected = array(
		'type' => 'single',
		'values' => array(), // array of languages.languages_id
	);
	
	/**
	 * type can be `single` or `multiple`
	 * @var array $priceConfig
	 */
	protected $priceConfig = array('type' => 'single', 'values' => array());
	
	protected $quantityConfig = array();
	
	protected $options = array();
	// Same as $options but specific to the current call.
	protected $optionsTmp = array();
	
	protected $simpleprice = null;
	
	protected $dbMatchings = array(
		'ManufacturerPartNumber' => array(),
		'tecDocKType' => array(),
	);
	
	protected $productMainSelectFields = '';
	protected $productOfferSelectFields = '';
	protected $attributesMainSelectFields = '';
	protected $productDescriptionSelectFields = '';
	
	protected $hasMasterItems = false;
	
	protected $cache = array();
	
	private function __construct() {
		$this->simpleprice = new SimplePrice();
		
		$this->buildSelectFields();
		
		$this->reset();
		
		$this->resetOptions();
		
		$this->hasMasterItems = MagnaDB::gi()->columnExistsInTable('products_master_model', TABLE_PRODUCTS);
	}
	
	/**
	 * Singleton - gets Instance
	 * @return MLProduct
	 */
	public static function gi() {
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Gets the information if the shop has the master/slave plugin installed.
	 *
	 * @return
	 *    true: If the module is installed, false otherwise.
	 */
	public function hasMasterItems() {
		return $this->hasMasterItems;
	}
	
	/**
	 * Sets the internal options. The options affect the behavior of the
	 * class. These options are very shop specific and might not do
	 * anything for certain conditions/shopsystems.
	 *
	 * @param array $options
	 *    Currently implemented options:
	 *      * includeVariations (default: true)
	 *        If set to false no variations will be loaded for the main product.
	 *      * sameVariationsToAttributes (default: false)
	 *        If the values of a variation dimension are the same for all variation products this dimension
	 *        will be converted to a simple attribute.
	 *      * allowSingleVariations (default: true)
	 *        If a product has only one variation (not dimension) it will be kept if this setting is true.
	 *        Otherwise the variation will be merged with the master product and the variation attributes
	 *        will be added to the master as regular attributes.
	 *
	 * @return $this
	 */
	public function setOptions(array $options) {
		$this->options = array_replace($this->options, $options);
		return $this;
	}
	
	/**
	 * Reset all options to their defaults. See setOptions() for more details.
	 *
	 * @return $this
	 */
	public function resetOptions() {
		$this->options = array (
			'includeVariations' => true,
			'sameVariationsToAttributes' => false,
			'allowSingleVariations' => true,
		);
		return $this;
	}
	
	/**
	 * Sets the temporary options that will be resetted after the product has been fetched.
	 *
	 * @param array $optionsTmp
	 *    @see self::setOptions() for defails
	 *
	 * @return void
	 */
	protected function setOptionsTmp(array $optionsTmp) {
		$this->optionsTmp = array_replace($this->options, $optionsTmp);
	}
	
	/**
	 * Resets the temporarily setted options back to the default.
	 */
	protected function resetOptionsTmp() {
		$this->optionsTmp = $this->options;
	}
	
	/**
	 * Loads the available languages from the languages table and stores them in
	 * self::languagesAvailable where the key is the language id and the value
	 * is the language code.
	 *
	 * @return void
	 */
	protected function loadLanguagesAvailable() {
		$languages = MagnaDB::gi()->fetchArray('SELECT languages_id, code FROM '.TABLE_LANGUAGES);
		$this->languagesAvailable = array();
		foreach ($languages as $lang) {
			$this->languagesAvailable[$lang['languages_id']] = strtolower($lang['code']);
		}
	}
	
	/**
	 * Returns the language code of a language id if it exists.
	 *
	 * @param int $id
	 *    The language id
	 *
	 * @return string|boolean
	 *    The language code or false if the id is not a valid language.
	 */
	public function languageIdToCode($id) {
		if (empty($this->languagesAvailable)) {
			$this->loadLanguagesAvailable();
		}
		return isset($this->languagesAvailable[$id])
			? $this->languagesAvailable[$id]
			: false;
	}
	
	/**
	 * Set the language that shall be used when loading language specific
	 * product data.
	 *
	 * @param mixed $language
	 *    The language as languages_id, ISO Code or the full name of the language.
	 * @return $this
	 */
	public function setLanguage($languages) {
		if (!is_array($languages)) {
			$this->languagesSelected['type'] = 'single';
			$languages = array($languages);
		} else {
			$this->languagesSelected['type'] = 'multiple';
		}
		
		$this->languagesSelected['values'] = array();
		foreach ($languages as $language) {
			if (is_numeric($language)) {
				$language = mlGetLanguageCodeFromID($language);
			}
			
			$this->languagesSelected['values'][$language] = strtolower($language);
		}
		return $this;
	}
	
	/**
	 * Converts a multi-language property to a single language property if requested.
	 *
	 * @param array $item
	 *    The property as array
	 * @return string
	 *    The property as string
	 */
	protected function processLanguageSpecificsSingle($item) {
		if (($this->languagesSelected['type'] != 'single') || !is_array($item)) {
			return $item;
		}
		return array_pop($item);
	}
	
	/**
	 * Converts a multiple multi-language properties to a single language properties if requested.
	 *
	 * @param array $items
	 *    The items with multi-language properties as arrays
	 * @return array
	 *    The items with a single language property as string
	 */
	protected function processLanguageSpecificsMulti($items) {
		if ($this->languagesSelected['type'] != 'single') {
			return $items;
		}
		foreach ($items as &$item) {
			if (!is_array($item)) {
				continue;
			}
			$item = array_pop($item);
		}
		return $items;
	}
	
	/**
	 * Populates the internal cache using a SQL query.
	 *
	 * @param string $cacheName
	 *    The name of the cache
	 * @param string $query
	 *    A database query that selects the columns `Key`, `Value` and `LanguageId` (optional).
	 *    If the table does not have these exact fiels use an alias for the columns.
	 *
	 * @return void
	 */
	protected function cachePopulate($cacheName, $query) {
		if (!isset($this->cache[$cacheName])) {
			$this->cache[$cacheName] = array();
		}
		$data = MagnaDB::gi()->fetchArray($query);
		if (empty($data)) {
			return;
		}
		$multiLang = isset($data[0]['LanguageId']);
		
		foreach ($data as $row) {
			if ($multiLang) {
				$this->cache[$cacheName][$row['Key']][$row['LanguageId']] = $row['Value'];
			} else {
				$this->cache[$cacheName][$row['Key']] = $row['Value'];
			}
		}
	}
	
	/**
	 * Checks if a cache block or a cached value exists.
	 *
	 * @param string $cacheName
	 *    The name of the cache
	 * @param string|bool $key
	 *    If set to false this method returns true if the cache block exists.
	 *    otherwise it checks for the key in the block.
	 *
	 * @return bool
	 */
	 protected function cacheKeyExists($cacheName, $key = false) {
		return $key === false
			? isset($this->cache[$cacheName])
			: isset($this->cache[$cacheName][$key]);
	}
	
	/**
	 * Returns the cached value for a cache block and key.
	 *
	 * @param string $cacheName
	 *    The name of the cache
	 * @param string $key
	 * @param mixed $default
	 *    A default in case the cache entry doesn't exist.
	 *
	 * @return mixed
	 */
	protected function cacheGetValue($cacheName, $key, $default = false) {
		return $this->cacheKeyExists($cacheName, $key)
			? $this->cache[$cacheName][$key]
			: $default;
	}
	
	/**
	 * Filters what ever has been returned from self::cacheGetValue() based
	 * on the requested language.
	 *
	 * @param array $data
	 *    The data that will be filtered. Keys are the language ids, values are their translations.
	 *
	 * @return array
	 *    The same as the parameter, except that the keys are now language codes and only those are
	 *    included that have been requested.
	 */
	protected function cacheFilterLanguage($data) {
		$r = array();
		foreach ($this->languagesSelected['values'] as $langId => $langCode) {
			$r[$langCode] = is_array($data) && isset($data[$langId])
				? $data[$langId]
				: '';
		}
		$r = $this->processLanguageSpecificsSingle($r);
		return $r;
	}
	
	/**
	 * Validates a price config array.
	 * 
	 * @param array $pConfig
	 * @return bool
	 *    true if valid, false otherwise.
	 */
	protected static function isValidPriceConfig($pConfig) {
		return is_array($pConfig)
			&& isset($pConfig['AddKind']) && isset($pConfig['Factor'])
			&& isset($pConfig['Signal']) && isset($pConfig['Group'])
			&& isset($pConfig['UseSpecialOffer']);
	}
	
	/**
	 * Returns a default price config that simply uses the normal 
	 * shop price.
	 *
	 * @return array
	 *    A simple price config array.
	 */
	protected function getDefaultPriceConfig() {
		return array (
			'AddKind' => 'percent',
			'Factor' => 0,
			'Signal' => '',
			'Group' => '',
			'UseSpecialOffer' => false,
			'Currency' => DEFAULT_CURRENCY,
			'ConvertCurrency' => false,
		);
	}
	
	/**
	 * Itereates $this->priceconfig[values] and updates
	 * the currency conversion rate if requested.
	 */
	protected function currencySetup() {
		$updated = array();
		foreach ($this->priceConfig['values'] as $name => $config) {
			$this->simpleprice->setCurrency($config['Currency']);
			if ($config['ConvertCurrency']
				// no update if this is the shop currency
				&& ($config['Currency'] != DEFAULT_CURRENCY)
				// already updated
				&& !in_array($config['Currency'], $updated)
			) {
				$updated[] = $config['Currency'];
				$success = false;
				$this->simpleprice->updateCurrencyByService($success);
				// @todo: handle case $success == false
			}
		}
	}
	
	/**
	 * Sets the price config that will be used to calculate prices.
	 *
	 * @param array $priceConfig
	 *    The price config, @see getDefaultPriceConfig() for the required keys.
	 *
	 * @return $this
	 */
	public function setPriceConfig($priceConfig) {
		$defaultConfig = $this->getDefaultPriceConfig();
		if (
			is_array($priceConfig)
			&& !self::isValidPriceConfig($priceConfig)
			&& is_array(current($priceConfig))
			&& self::isValidPriceConfig(current($priceConfig))
		) {
			$this->priceConfig['type'] = 'multiple';
			foreach ($priceConfig as $name => $config) {
				if (!self::isValidPriceConfig($config)) {
					unset($priceConfig[$name]);
				}
			}
		} else {
			if (!self::isValidPriceConfig($priceConfig)) {
				$priceConfig = $defaultConfig;
			}
			$this->priceConfig['type'] = 'single';
			$priceConfig = array('single' => $priceConfig);
		}
		foreach ($priceConfig as $name => $config) {
			$priceConfig[$name] = array_merge($defaultConfig, $config);
		}
		
		$this->priceConfig['values'] = $priceConfig;
		$this->currencySetup();
		return $this;
	}
	
	/**
	 * Validates a quantiy config array.
	 * 
	 * @param array $qConfig
	 * @return bool
	 *    true if valid, false otherwise.
	 */
	protected static function isValidQuantityConfig($qConfig) {
		return is_array($qConfig)
			&& isset($qConfig['Type']) && isset($qConfig['Value']);
	}
	
	/**
	 * Returns a default quantity config that simply uses the normal 
	 * shop quantity.
	 *
	 * @return array
	 *    A simple quantity config array.
	 */
	protected function getDefaultQuantityConfig() {
		return array (
			'Type' => 'stocksub',
			'Value' => 0, 
			'MaxQuantity' => 0,
			'ExcludeInactive' => false,
		);
	}
	
	/**
	 * Sets the quantity config that will be used to calculate quantities.
	 *
	 * @param array $quantityConfig
	 *    The quantity config, @see getDefaultQuantityConfig for the required keys.
	 *
	 * @return $this
	 */
	public function setQuantityConfig($quantityConfig) {
		if (!self::isValidQuantityConfig($quantityConfig)) {
			$quantityConfig = $this->getDefaultQuantityConfig();
		}
		// set optional values
		if (!isset($quantityConfig['MaxQuantity'])) {
			$quantityConfig['MaxQuantity'] = 0;
		}
		if (isset($quantityConfig['ExcludeInactive'])) {
			$quantityConfig['ExcludeInactive'] = (bool)$quantityConfig['ExcludeInactive'];
		} else {
			$quantityConfig['ExcludeInactive'] = false;
		}
		$this->quantityConfig = $quantityConfig;
		return $this;
	}
	
	/**
	 * Sets a db matching to extend the product with additional information.
	 * 
	 * @param string $for
	 *    The db matching category
	 * @param array $matchingConfig
	 *    The matching config. Required fields are Table, Column and Alias.
	 *
	 * @return $this
	 */
	public function setDbMatching($for, $matchingConfig) {
//		if (!array_key_exists($for, $this->dbMatchings)) {
//			return $this;
//		}
		if (!isset($matchingConfig['Table']) || empty($matchingConfig['Table'])
			|| !isset($matchingConfig['Column']) || empty($matchingConfig['Column'])
			|| !isset($matchingConfig['Alias']) // may be empty!
		) {
			return $this;
		}
		$this->dbMatchings[$for] = $matchingConfig;
		return $this;
	}
	
	/**
	 * Resets the settings excpet for the language.
	 *
	 * @return $this
	 */
	public function reset() {
		$this->setPriceConfig(false);
		$this->setQuantityConfig(false);
		
		foreach ($this->dbMatchings as $for => $matchingConfig) {
			$this->dbMatchings[$for] = array();
		}
		
		return $this;
	}
	
	/**
	 * Calculates the final price of the product based on the price config.
	 * This algorithm is basically a copy of SimplePrice::finalizePrice()
	 * with a few minor changes.
	 *
	 * @param float $basePrice
	 *    The netto price
	 * @param float $tax
	 *    The tax as percent value
	 * @param array $config
	 *    The config that will be used to calculate a price.
	 *
	 * @return float
	 *    The final price
	 */
	protected function calcPrice($basePrice, $tax, $config) {
		$this->simpleprice->setPrice($basePrice)->setCurrency($config['Currency']); // add the variation price
		
		$this->simpleprice->addTax($tax)->calculateCurr();

		switch ($config['AddKind']) {
			case 'percent': {
				$this->simpleprice->addTax((float)$config['Factor']);
				break;
			}
			case 'addition': {
				$this->simpleprice->addLump((float)$config['Factor']);
				break;
			}
			case 'constant': {
				$this->simpleprice->setPrice((float)$config['Factor']);
				break;
			}
		}
		return $this->simpleprice->roundPrice()
			->makeSignalPrice($config['Signal'])
			->getPrice()
		;
	}
	
	/**
	 * Calculates the final quantity of the product based on the quantity config.
	 *
	 * @param int $dbQuantity
	 *
	 * @return int
	 *    The final Quantity
	 */
	protected function calcQuantity($dbQuantity, $status) {
		if ($this->quantityConfig['ExcludeInactive'] && !$status) {
			return 0;
		}
		$dbQuantity = (int)$dbQuantity;
		switch ($this->quantityConfig['Type']) {
			case 'stocksub': {
				$dbQuantity -= $this->quantityConfig['Value'];
				break;
			}
			case 'lump': {
				$dbQuantity = $this->quantityConfig['Value'];
				break;
			}
		}
		
		if (($this->quantityConfig['MaxQuantity'] > 0) && ($this->quantityConfig['Type'] != 'lump')) {
			$dbQuantity = min($dbQuantity, $this->quantityConfig['MaxQuantity']);
		}
		$dbQuantity = max($dbQuantity, 0); // make sure it is always >= 0
		return $dbQuantity;
	}
	
	/**
	 * Translates magnalister_variations.variation_attributes array to
	 * language specific strings.
	 *
	 * @param array $productOptions
	 *    A list of arrays with the keys 'Group' and 'Value'
	 *
	 * @return array
	 *    A list of arrays with the keys 'Name' and 'Value'
	 */
	protected function translateProductsOptions($varaintId) {
		$attributes = MagnaDb::gi()->fetchArray('
			SELECT attributes_parent_id AS NameId, 
			       "" AS Name,
			       attributes_id AS ValueId,
			       "" AS Value
			  FROM '.TABLE_PRODUCTS_TO_ATTRIBUTES.'
			 WHERE products_id = "'.$varaintId.'"
		');
		if (empty($attributes)) {
			return array();
		}
		foreach ($attributes as &$attribute) {
			foreach (array('Name', 'Value') as $type) {
				if (!$this->cacheKeyExists('ProductOptions', $attribute[$type.'Id'])) {
					$this->cachePopulate('ProductOptions', '
						SELECT attributes_id AS `Key`, language_code AS LanguageId, attributes_name AS Value
						  FROM '.TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION.'
						 WHERE attributes_id = "'.$attribute[$type.'Id'].'"
					');
				}
				$attribute[$type] = $this->cacheFilterLanguage(
					$this->cacheGetValue('ProductOptions', $attribute[$type.'Id'], '')
				);
			}
		}
		return $attributes;
	}
	
	/**
	 * Loads the variations to a product.
	 *
	 * @param array $parent
	 *    The parent product
	 * @param bool $onlyOffer
	 *    If this is set to true only the offer data will be included
	 *    along with everything needed to "identify" the variation.
	 *
	 * @return array
	 *    List of variations or empty if no variations exist.
	 * @see fetchMultiVariations, translateProductsOptions
	 */
	protected function fetchVariations(&$parent, $onlyOffer) {
		if (!$this->optionsTmp['includeVariations'] || !$this->hasMasterItems || empty($parent['ProductsModel'])) {
			return array();
		}
		$variants = MagnaDB::gi()->fetchArray('
			   SELECT '.$this->attributesMainSelectFields.'
			     FROM '.TABLE_PRODUCTS.' p 
			    WHERE p.products_master_model = "'.MagnaDB::gi()->escape($parent['ProductsModel']).'"
			 ORDER BY p.products_sort
		');
		if (!empty($variants)) {
			$quantity = 0;
			
			$variationSets = array();

			foreach ($variants as &$product) {
				$product['ProductId'] = $product['VariationId'];
				{
					// emulate variant as master
					$product['MarketplaceId'] = 'ML'.$product['MarketplaceId'];
					$product['Variation'] = $this->translateProductsOptions($product['VariationId']);
					$product['Status'] = (bool)$product['Status'];
					
					foreach ($product['Variation'] as $vSet) {
						$variationSets[$vSet['NameId']][$vSet['ValueId']] = $vSet;
					}
					
					$this->completeBasePrice($product);
					foreach (array('ShippingTimeId', 'BasePrice') as $fromMaster) {
						if (empty($product[$fromMaster]) && isset($parent[$fromMaster])) {
							$product[$fromMaster] = $parent[$fromMaster];
						}
					}
					$product['ShippingTime'] = $this->getShippingTimeStringById($product['ShippingTimeId']);
					$this->completeTax($product);
					$this->getDbMatchings($product);
					$this->prepareParentPrices($product);
					unset($product['Currency']);
					$this->completeParentOffer($product);
					
					$product['Images'] = $this->getAllImagesByProductsId($product['ProductId']);
					
					$quantity += $product['Quantity'];
				}
				unset($product['ProductId']);
			}
			
			$parent['QuantityTotal'] = $quantity;
		}
		
		return empty($variants) ? array() : $variants;
	}
	
	/**
	 * Loads the manufacturer part number & other matchings based of a config db matching.
	 * 
	 * @param array &$product
	 *    The product
	 * 
	 * @return void
	 */
	protected function getDbMatchings(&$product) {
		foreach (array_keys($this->dbMatchings) as $sMatching) {
			if (empty($this->dbMatchings[$sMatching])) {
				continue;
			}
			if (empty($this->dbMatchings[$sMatching]['Alias'])) {
				$this->dbMatchings[$sMatching]['Alias'] = 'products_id';
			}
			$product[$sMatching] = MagnaDB::gi()->fetchOne('
				SELECT `' . $this->dbMatchings[$sMatching]['Column'] . '`
				  FROM `' . $this->dbMatchings[$sMatching]['Table'] . '`
				 WHERE `' . $this->dbMatchings[$sMatching]['Alias'] . '`="' . $product['ProductId'] . '"
				 LIMIT 1
			');
		}
	}
	
	/**
	 * Fetches the additional images for a product id. This does not include the main image of the product.
	 * 
	 * @param int $pID
	 *    The products id
	 * @return array
	 *    The image list
	 */
	public function getAdditionalImagesByProductsId($pId) {
		/* {Hook} "MLProduct_getProductImagesByID": Enables you to fetch additional product images in a different
		   method than using the products_images table.<br>
		   Variables that can be used: <ul>
		       <li>$pID: The ID of the product (Table <code>products.products_id</code>).</li>
		       <li>$images: Array that this function will return</li>
		   </ul>
		   Set $images array in this format:<br>
		   <pre>
			$images = array (
				0 => 'image1.jpg',
				1 => 'image2.jpg',
				2 => ...
			);
		   </pre>
		 */
		if (($hp = magnaContribVerify('MLProduct_getProductImagesByID', 1)) !== false) {
			$images = array();
			require($hp);
			
			if (is_array($images) && isset($images[0])) {
				return $images;
			}
			
			return array();
			
		} else {
			$images = MagnaDB::gi()->fetchArray('
				    SELECT m.file
				      FROM '.TABLE_MEDIA.' m, '.TABLE_MEDIA_LINK.' ml 
				     WHERE ml.link_id = "'.$pId.'"
				           AND ml.m_id = m.id
				           AND ml.class = "product"
				           AND m.file <> ""
				           AND m.file IS NOT NULL
						   AND m.type = "images"
				  ORDER BY ml.sort_order
				', true);
			if (empty($images)) {
				return array();
			} else {
				return $images;
			}
		}
		return array();
	}
	
	/**
	 * @deprecated
	 * Alias of self::getAdditionalProductImagesById()
	 * 
	 * @param int $pID
	 *    The products id
	 * @return array
	 *    The image list
	 */
	public function getProductImagesByID($pId) {
		return $this->getAdditionalImagesByProductsId($pId);
	}
	
	/**
	 * Completes the Images field and loads additional product images if there are any.
	 * 
	 * @param array &$product
	 *    The product
	 * 
	 * @return void
	 */
	protected function completeImages(&$product) {
		if (empty($product['Images'])) {
			$product['Images'] = array();
		} else {
			$product['Images'] = array($product['Images']);
		}
		$product['Images'] = array_merge($product['Images'], $this->getAdditionalImagesByProductsId($product['ProductId']));
	}
	
	/**
	 * Returns all images of a master product.
	 * @param int $pId
	 *    The id of the product
	 *
	 * @return array
	 *    A list of image names
	 */
	public function getAllImagesByProductsId($pId) {
		$product = MagnaDB::gi()->fetchRow('
			SELECT products_id AS ProductId, products_image AS Images 
			  FROM '.TABLE_PRODUCTS.'
			 WHERE products_id="'.$pId.'"
		');
		$this->completeImages($product);
		return $product['Images'];
	}
	
	/**
	 * Returns a query to load the status thingies from the status table in a
	 * form that self::cachePopulate() likes to eat.
	 *
	 * @return string
	 */
	protected function buildStatusQuery($class) {
		return '
			    SELECT s.status_id AS `Key`, d.language_code AS LanguageId, d.status_name AS Value
			      FROM '.TABLE_SYSTEM_STATUS.' s
			 LEFT JOIN '.TABLE_SYSTEM_STATUS_DESCRIPTION.' d ON s.status_id=d.status_id
			     WHERE s.status_class="'.$class.'"
		';
	}
	
	/**
	 * Loads the shippingtime cache if it is empty and converts the
	 * shipping time id to a string if it exists.
	 *
	 * @return string
	 *    The shipping time string in human readable format or an empty string in case of a failure.
	 */
	protected function getShippingTimeStringById($id) {
		// use lazy loading
		if (!$this->cacheKeyExists('ShippingTime')) {
			$this->cachePopulate('ShippingTime', $this->buildStatusQuery('shipping_status'));
		}
		return $this->cacheFilterLanguage(
			$this->cacheGetValue('ShippingTime', $id, '')
		);
	}
		
	/**
	 * Loads the manufacturer cache if it is empty and converts the
	 * manufacturer id to a string if it exists.
	 *
	 * @return string
	 *    The manufactuerer string or an empty string in case of a failure.
	 */
	protected function getManufacturerNameById($id) {
		// use lazy loading
		if (!$this->cacheKeyExists('ManufacturerName')) {
			$this->cachePopulate('ManufacturerName', '
				SELECT manufacturers_id AS `Key`, manufacturers_name AS Value
				  FROM '.TABLE_MANUFACTURERS.'
			');
		}
		return $this->cacheGetValue('ManufacturerName', $id, '');
	}
	
	/**
	 * Loads the manufacturer cache if it is empty and converts the
	 * manufacturer id to a string if it exists.
	 *
	 * @return string
	 *    The manufactuerer string or an empty string in case of a failure.
	 */
	protected function getVpeUnitById($id) {
		// use lazy loading
		if (!$this->cacheKeyExists('BasePriceUnit')) {
			$this->cachePopulate('BasePriceUnit', $this->buildStatusQuery('base_price'));
		}
		return $this->cacheFilterLanguage(
			$this->cacheGetValue('BasePriceUnit', $id, '')
		);
	}
	
	/**
	 * Completes the Vpe field and removes all Vpe helper fields.
	 * 
	 * @param array &$product
	 *    The product
	 * 
	 * @return void
	 */
	protected function completeBasePrice(&$product) {
		if ($product['VpeStatus']) {
			$product['VpeUnit'] = $this->getVpeUnitById($product['VpeUnit']);
		}
		if ($product['VpeStatus'] && !empty($product['VpeUnit']) && ((float)$product['VpeValue'] > 0)) {
			$product['BasePrice'] = array (
				'Unit' => $product['VpeUnit'],
				'Value' => $product['VpeValue'],
			);
		} else {
			$product['BasePrice'] = array ();
		}
		unset($product['VpeStatus']);
		unset($product['VpeUnit']);
		unset($product['VpeValue']);
	}
	
	/**
	 * Translates the tax class to a percent value and caches the result from SimplePrice.
	 * @return void
	 */
	protected function completeTax(&$product) {
		if (!((int)$product['TaxClass'] > 0)) {
			$product['TaxPercent'] = 0.0;
			return;
		}
		if (!isset($this->cache['Tax'][$product['TaxClass']])) {
			if (!isset($this->cache['Tax'])) {
				$this->cache['Tax'] = array();
			}
			$this->cache['Tax'][$product['TaxClass']] = SimplePrice::getTaxByClassID($product['TaxClass']);
		}
		$product['TaxPercent'] = (float)$this->cache['Tax'][$product['TaxClass']];
	}

	/**
	 * Prepares the price for the parent product and checks if a reduce price should be used if it exists.
	 * 
	 * @param array &$product
	 *    The product
	 * 
	 * @return void
	 */
	protected function prepareParentPrices(&$product) {
		foreach ($this->priceConfig['values'] as $name => $config) {
			$price = is_array($product['Price']) ? $product['Price'][$name] : $product['Price'];
			$product['Currency'][$name] = $config['Currency'];
			$product['Prices'][$name] = array (
				'Price' => $config['Group'] > 0
					? $this->simpleprice->setCurrency($config['Currency'])->getGroupPrice($config['Group'], $product['ProductId'])
					: $price,
				'Reduced' => $config['UseSpecialOffer']
					? $this->simpleprice->setCurrency($config['Currency'])->getSpecialOffer($product['ProductId'], $config['Group'])
					: 0.0
			);
			// Make sure the group price is > 0
			if (!((float)$product['Prices'][$name]['Price'] > 0)) {
				$product['Prices'][$name]['Price'] = $price;
			}

			// Make sure the reduced price is not greater than the normal price.
			if ($product['Prices'][$name]['Reduced'] > $product['Prices'][$name]['Price']) {
				$product['Prices'][$name]['Reduced'] = 0;
			}
		}
	}
	
	/**
	 * Completes the parent offer. Finalizes the price and quantity.
	 * 
	 * @param array &$product
	 *    The product
	 * 
	 * @return void
	 */
	protected function completeParentOffer(&$product) {
		$product['Price'] = array();
		foreach ($this->priceConfig['values'] as $name => $config) {
			// Price foo
			$product['Price'][$name] = $this->calcPrice($product['Prices'][$name]['Price'], $product['TaxPercent'], $config);
			if ((float)$product['Prices'][$name]['Reduced'] > 0) {
				$product['PriceReduced'][$name] = $this->calcPrice($product['Prices'][$name]['Reduced'], $product['TaxPercent'], $config);
			} else if (isset($product['PriceReduced'][$name])){
				unset($product['PriceReduced'][$name]);
			}
			unset($product['Prices'][$name]);
		}
		if (empty($product['PriceReduced'])) {
			unset($product['PriceReduced']);
		}
		unset($product['Prices']);
		
		if ($this->priceConfig['type'] == 'single') {
			$product['Price'] = current($product['Price']);
			if (isset($product['PriceReduced'])) {
				$product['PriceReduced'] = current($product['PriceReduced']);
			}
			if (isset($product['Currency'])) {
				$product['Currency'] = current($product['Currency']);
			}
		}
		
		// Quantity
		$product['Quantity'] = $this->calcQuantity($product['Quantity'], $product['Status']);
	}
	
	
	/**
	 * Looks in database-table ($table) for fieldnames ($fields) and build array for select-statement.
	 * Example:
	 *   $this->translateDbFields(product, array(array('name' => array('title', 'name'), array('id' => 'products_id'))), $prefix = 'p')
	 *   returns array('p.products_title as title', 'p.products_id as id')
	 *   or if product.title doesn't exist it
	 *   returns array('p.products_name as title', 'p.products_id as id').
	 *   If a field in general doesn't exist (here both 'title' and 'name') it
	 *   returns array('"" as title', 'p.products_id as id').
	 *
	 * @param string $table 
	 * @param array $fields
	 *     Example: array(magnal_field_name => as_field_name, magna_field_name => array(shop_field_name, alternate_shop_field_name, ..))
	 * @return array
	 *     SELECT-part for sql query.
	 */
	protected function translateDbFields($table, &$fields, $prefix = null) {
		$query = MagnaDB::gi()->fetchRow('SELECT * FROM '.$table.' LIMIT 1');
		if (!empty($query)) {
			foreach ($fields as $ml => $dbs) {
				if (!is_array($dbs)) {
					$dbs = array($dbs);
				}
				$found = false;
				foreach ($dbs as $db) {
					if (!empty($db) && array_key_exists($db, $query)) {
						$fields[$ml] = ($prefix === null ? '' : $prefix.'.').$db;
						$found = true;
						break;
					}
				}
				if (!$found) {
					$fields[$ml] = '';
				}
			}
			$productSelectFields = array();
			foreach ($fields as $ml => $db) {
				$productSelectFields[] = (empty($db) ? '""' : $db).' AS '.$ml;
			}
			$fields = $productSelectFields;
		}
	}
	
	/**
	 * Builds the SELECT string for the product and offer query and stores them in class attributes.
	 */
	protected function buildSelectFields() {
		$productsOffer = array ( // These fields are order specific and they exsist in every osC fork
			'ProductId' => 'products_id',
			'ProductsModel' => 'products_model',
			'MarketplaceId' => 'products_id',
			'MarketplaceSku' => 'products_model',
			'MarketplaceId' => 'products_id',
			'MarketplaceSku' => 'products_model',
			'Quantity' => 'products_quantity',
			'Price' => 'products_price',
			'PriceReduced' => '',
			'Currency' => '',
			'Status' => 'products_status',
			'TaxClass' => 'products_tax_class_id',
			'TaxPercent' => '',
		);
		$productFields = array ( // Some of these fiels don't exist in every osC fork.
			'EAN' => 'products_ean',
			'ShippingTimeId' => 'products_shippingtime',
			'ShippingTime' => '',
			'Images' => 'products_image',
			'DateAdded' => 'date_added',
			'LastModified' => 'last_modified',
			'DateAvailable' => 'date_available',
			'Weight' => 'products_weight',
			'ManufacturerId' => 'manufacturers_id',
			'Manufacturer' => '',
			'IsFSK18' => 'products_fsk18',
			'BasePrice' => '',
			'VpeUnit' => 'products_vpe',
			'VpeValue' => 'products_vpe_value',
			'VpeStatus' => 'products_vpe_status',
			'Attributes' => '',
		);
		foreach (array_keys($this->dbMatchings) as $sMatch) {
			$productFields[$sMatch] = '';
		}
		$descriptionFields = array ( 
			'Title' => 'products_name',
			'Description' => 'products_description',
			'ShortDescription' => 'products_short_description',
		);
		$metaFields = array (
			'Keywords' => 'meta_keywords',
			'BulletPoints' => 'meta_description',
			'ProductUrl' => 'url_text',
		);
		
		$prod = MagnaDB::gi()->fetchRow('SELECT * FROM '.TABLE_PRODUCTS.' LIMIT 1');
		$desc = MagnaDB::gi()->fetchRow('SELECT * FROM '.TABLE_PRODUCTS_DESCRIPTION.' LIMIT 1');
		$meta = MagnaDB::gi()->fetchRow('SELECT * FROM '.TABLE_SEO_URL.' LIMIT 1');
		
		if (!empty($prod)) {
			foreach ($productFields as $ml => $db) {
				if (!empty($db) && !array_key_exists($db, $prod)) {
					$productFields[$ml] = '';
				}
			}
		}
		if (!empty($desc)) {
			foreach ($descriptionFields as $ml => $dbs) {
				if (!is_array($dbs)) {
					$dbs = array($dbs);
				}
				$found = false;
				foreach ($dbs as $db) {
					if (!empty($db) && array_key_exists($db, $desc)) {
						$descriptionFields[$ml] = $db;
						$found = true;
						break;
					}
				}
				if (!$found) {
					$descriptionFields[$ml] = '';
				}
			}
		}		
		if (!empty($meta)) {
			foreach ($metaFields as $ml => $dbs) {
				if (!is_array($dbs)) {
					$dbs = array($dbs);
				}
				$found = false;
				foreach ($dbs as $db) {
					if (!empty($db) && array_key_exists($db, $meta)) {
						$metaFields[$ml] = $db;
						$found = true;
						break;
					}
				}
				if (!$found) {
					$metaFields[$ml] = '';
				}
			}
		}
		
		// build select statements
		$productSelectFields = array();
		foreach ($productsOffer as $ml => $db) {
			$productSelectFields[] = (empty($db) ? '""' : 'p.'.$db).' AS '.$ml;
		}
		$this->productOfferSelectFields = implode(', ', $productSelectFields);
		
		foreach ($productFields as $ml => $db) {
			$productSelectFields[] = (empty($db) ? '""' : 'p.'.$db).' AS '.$ml;
		}
		$this->productMainSelectFields = implode(', ', $productSelectFields);
		
		$productSelectFields = array();
		foreach ($descriptionFields as $ml => $db) {
			$productSelectFields[] = (empty($db) ? '""' : 'pd.'.$db).' AS '.$ml;
		}
		foreach ($metaFields as $ml => $db) {
			$productSelectFields[] = (empty($db) ? '""' : 'pm.'.$db).' AS '.$ml;
		}
		$this->productDescriptionSelectFields = implode(', ', $productSelectFields);
		
		$attributesSelectFields = array(
			'VariationId' => 'products_id',
			'MarketplaceId' => 'products_id',
			'MarketplaceSku' => 'products_model',
			'Variation' => '',//Array([0] => Array([NameId] => 1[Name] => Größe[ValueId] => 1[Value] => S))
			'Price' => 'products_price',
			'Quantity' => 'products_quantity',
			'Weight' => 'products_weight',
			'Status' => 'products_status',
			'TaxClass' => 'products_tax_class_id',
			'TaxPercent' => '',
			'ShippingTimeId' => 'products_shippingtime',
			'EAN' => 'products_ean',
			'VpeUnit' => 'products_vpe',
			'VpeValue' => 'products_vpe_value',
			'VpeStatus' => 'products_vpe_status',
		);
		$this->translateDbFields(TABLE_PRODUCTS, $attributesSelectFields, 'p');
		$this->attributesMainSelectFields = implode(', ', $attributesSelectFields);
	}
	
	/**
	 * Returns the products description for a product in the requested language.
	 *
	 * @param int $pId
	 *    The products id of the product
	 * @param int $language
	 *    The language id
	 *
	 * @return array|bool
	 *    The products description or false if the description does not exist for the
	 *    requested language.
	 */
	protected function loadProductsDescription($pId, $language) {
		$desc = MagnaDB::gi()->fetchRow(eecho('
			    SELECT '.$this->productDescriptionSelectFields.'
			      FROM '.TABLE_PRODUCTS_DESCRIPTION.' pd
			INNER JOIN '.TABLE_SEO_URL.' pm ON pd.products_id = pm.link_id AND pm.link_type = 1 AND pm.language_code = pd.language_code
			     WHERE pd.products_id = '.$pId.'
			           AND pd.language_code = "'.$language.'"
			     LIMIT 1
		', false));
		if (empty($desc)) {
			return false;
		}
		return $desc;
	}

	
	/**
	 * Post processes the variations. It might make some final modifications on the Variations element
	 * based on the set options.
	 *
	 * @param array $parent
	 *    The main product with its variations as Variations element
	 * @return array
	 *    The main product with the modified Variations element
	 */
	protected function postProcessVariations(array $parent) {
		if (empty($parent['Variations'])) {
			return $parent;
		}
		
		// If there is only one variation (not dimension but variation) don't try to convert the unique
		// variation attributes to real attributes. Otherwise the product would end up with an invalid
		// variation structure.
		if (($this->optionsTmp['sameVariationsToAttributes']) && (count($parent['Variations']) > 1)) {
			$variationSets = array();
			foreach ($parent['Variations'] as $product) {
				foreach ($product['Variation'] as $vSet) {
					$variationSets[$vSet['NameId']][$vSet['ValueId']] = $vSet;
				}
			}
			// Move fixed variations that don't differ in their values for all variations to the attributes part of the parent product.
			$attributes = array();
			if (!isset($parent['Attributes'])) {
				$parent['Attributes'] = array();
			}
			foreach ($variationSets as $nameId => $valueSets) {
				if (count($valueSets) == 1) {
					$attributes[$nameId] = true;
					$parent['Attributes'][] = array_shift($valueSets);
				}
			}
			if (!empty($attributes)) {
				foreach ($parent['Variations'] as &$product) {
					foreach ($product['Variation'] as $idx => $vSet) {
						if (isset($attributes[$vSet['NameId']])) {
							unset($product['Variation'][$idx]);
						}
					}
				}
			}
			if (empty($parent['Attributes'])) {
				unset($parent['Attributes']);
			}
		}
		
		if (!$this->optionsTmp['allowSingleVariations'] && (count($parent['Variations']) == 1)) {
			if (!isset($parent['Attributes'])) {
				$parent['Attributes'] = array();
			}
			$vItem = $parent['Variations'][0];
			foreach ($vItem['Variation'] as $vSet) {
				$parent['Attributes'][] = $vSet;
			}
			unset($vItem['Variation']);
			unset($vItem['VariationId']);
			
			unset($parent['QuantityTotal']);
			unset($parent['Variations']);
			
			$parent = array_replace($parent, $vItem);
		}
		
		return $parent;
	}

	/**
	 * Loads a complete product with full detail and its variations.
	 *
	 * @param int $pId
	 *    The id of the product
	 * @param array $optionsTmp
	 *    Affects the options only for this one call. They will be reseted to their
	 *    previous state. For the available options @see self::setOptions().
	 *
	 * @return array
	 *    The loaded product
	 * @todo
	 */
	public function getProductById($pId, array $optionsTmp = array()) {
		if (empty($this->languagesSelected['values'])) {
			throw new Exception('Please set a language first.');
		}
		
		$this->setOptionsTmp($optionsTmp);
		
		$product = MagnaDB::gi()->fetchRow(eecho('
			SELECT '.$this->productMainSelectFields.'
			  FROM '.TABLE_PRODUCTS.' p
			 WHERE p.products_id = '.(int)$pId.'
		', false));
		
		if (empty($product)) {
			$this->resetOptionsTmp();
			return $product;
		}
		
		$product['MarketplaceId'] = 'ML'.$product['MarketplaceId'];
		
		$desc = array();
		foreach ($this->languagesSelected['values'] as $langId => $langCode) {
			$descTmp = $this->loadProductsDescription($pId, $langId);
			if (!empty($descTmp)) {
				foreach ($descTmp as $descKey => $descVal) {
					if (!isset($desc[$descKey])) {
						$desc[$descKey] = array();
					}
					$desc[$descKey][$langCode] = $descVal;
				}
			}
		}
		unset($descTmp);
		
		if (empty($desc)) {
			$this->resetOptionsTmp();
			return false;
		}
		$desc = $this->processLanguageSpecificsMulti($desc);
		$product = array_merge($product, $desc);
		
		$product['Status'] = (bool)$product['Status'];
		$product['VpeStatus'] = (bool)$product['VpeStatus'];
		$product['IsFSK18']   = (bool)$product['IsFSK18'];
		
		$product['ShippingTime'] = $this->getShippingTimeStringById($product['ShippingTimeId']);
		$product['Manufacturer'] = $this->getManufacturerNameById($product['ManufacturerId']);
		
		$this->completeTax($product);
		$this->getDbMatchings($product);
		$this->completeBasePrice($product);
		$this->completeImages($product);
		
		if (empty($product['DateAvailable'])) {
			$product['DateAvailable'] = '0000-00-00 00:00:00';
		}
		if (empty($product['LastModified'])) {
			$product['LastModified'] = $product['DateAdded'];
		}
		if ((float)$product['Weight'] > 0) {
			$product['Weight'] = array (
				'Unit' => 'kg',
				'Value' => $product['Weight'],
			);
		} else {
			$product['Weight'] = array ();
		}

		$this->prepareParentPrices($product);
		
		$product['Attributes'] = array();
		$product['Variations'] = $this->fetchVariations($product, false);
		$this->completeParentOffer($product);
		
		$product = $this->postProcessVariations($product);
		
		$this->resetOptionsTmp();
		
		return $product;
	}
	
	/**
	 * Loads a complete product with full detail and its variations.
	 *
	 * @param int $pId
	 *    The id of the product
	 * @param array $optionsTmp
	 *    Affects the options only for this one call. They will be reseted to their
	 *    previous state. For the available options @see self::setOptions().
	 *
	 * @return array
	 *    The loaded product
	 */
	public function getProductOfferById($pId, array $optionsTmp = array()) {
		if (empty($this->languagesSelected['values'])) {
			throw new Exception('Please set a language first.');
		}
		
		$this->setOptionsTmp($optionsTmp);
		
		$product = MagnaDB::gi()->fetchRow(eecho('
			SELECT '.$this->productOfferSelectFields.'
			  FROM '.TABLE_PRODUCTS.' p
			 WHERE p.products_id = '.(int)$pId.'
		', false));
		
		if (empty($product)) {
			$this->resetOptionsTmp();
			return $product;
		}
		
		$product['MarketplaceId'] = 'ML'.$product['MarketplaceId'];
		
		$this->completeTax($product);
		$this->prepareParentPrices($product);
		
		$product['Variations'] = $this->fetchVariations($product, true);
		$this->completeParentOffer($product);
		
		$product = $this->postProcessVariations($product);
		
		$this->resetOptionsTmp();
		
		return $product;
	}
	
	/**
	 * Reduces the quantity for a product or variation.
	 * 
	 * @param string $sku
	 *    The SKU of the product.
	 * @param int $quantityDifference
	 *    A positive int increases the quantity, a negative one decreases it.
	 *
	 * @return $this
	 */
	public function changeQuantity($sku, $quantityDifference) {
		// @todo: implement
		return $this;
	}
	
	/**
	 * Fetches the category path for a product or category depending on the parameters
	 * 
	 * @param int $id
	 *    The id of the product or category
	 * @param string $for
	 *    Set this to 'product' to get the path for a product. For anything else 'category' is assumed.
	 * @param array &$cPath
	 *    Internally used for recursion. Do not pass an argument here.
	 *
	 * @return array
	 *    The category path
	 */
	public function getCategoryPath($id, $for = 'category', &$cPath = array()) {
		if ($for == 'product') {
			$cIDs = MagnaDB::gi()->fetchArray('
				SELECT categories_id FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id="'.MagnaDB::gi()->escape($id).'"
			', true);
			if (empty($cIDs)) {
				return array();
			}
			$return = array();
			foreach ($cIDs as $cID) {
				if ((int)$cID == 0) {
					$return[] = array('0');
				} else {
					$cPath = $this->getCategoryPath($cID);
					array_unshift($cPath, $cID);
					$return[] = $cPath;
				}
			}
			return $return;
		} else {
			$meh = MagnaDB::gi()->fetchOne(
				'SELECT parent_id FROM '.TABLE_CATEGORIES.' WHERE categories_id="'.MagnaDB::gi()->escape($id).'"'
			);
			$cPath[] = (int)$meh;
			if ($meh != '0') {
				$this->getCategoryPath($meh, 'category', $cPath);
			}
			return $cPath;
		}
	}

	/**
	 * Fetches a category path in the language of the current shop interface.
	 * Copied from xt:commerce 3.
	 *
	 * @param int $id
	 *    The id of the product or category
	 * @param string $from
	 *    Set this to 'product' to get the path for a product. For anything else 'category' is assumed.
	 * @param array &$categories_array
	 *    Internally used for recursion. Do not pass an argument here.
	 * @param int &$index
	 *    Internally used for recursion. Do not pass an argument here.
	 * @param int &$callCount
	 *    Internally used for recursion. Do not pass an argument here.
	 * 
	 * @return array
	 *    The category path
	 */
	public function generateCategoryPath($id, $from = 'category', $categories_array = array(), $index = 0, $callCount = 0) {
		if ($from == 'product') {
			$categories_query = MagnaDB::gi()->query('
				SELECT categories_id FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id = "'.$id.'"
			');
			while ($categories = MagnaDB::gi()->fetchNext($categories_query)) {
				if ($categories['categories_id'] == '0') {
					$categories_array[$index][] = array ('id' => '0', 'text' => ML_LABEL_CATEGORY_TOP);
				} else {
					$category_query = MagnaDB::gi()->query('
						SELECT cd.categories_name, c.parent_id 
						  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd 
						 WHERE c.categories_id = "'.$categories['categories_id'].'" 
						       AND c.categories_id = cd.categories_id 
						       AND cd.language_id = "'.$_SESSION['languages_id'].'"
					');
					$category = MagnaDB::gi()->fetchNext($category_query);
					$categories_array[$index][] = array (
						'id' => $categories['categories_id'],
						'text' => $category['categories_name']
					);
					if (($category['parent_id'] != '') && ($category['parent_id'] != '0')) {
						$categories_array = $this->generateCategoryPath($category['parent_id'], 'category', $categories_array, $index);
					}
				}
				++$index;
			}
		} else if ($from == 'category') {
			$category_query = MagnaDB::gi()->query('
				SELECT cd.categories_name, c.parent_id 
				  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd
				 WHERE c.categories_id = "'.$id.'" 
				       AND c.categories_id = cd.categories_id
				       AND cd.language_id = "'.$_SESSION['languages_id'].'"
			');
			$category = MagnaDB::gi()->fetchNext($category_query);
			$categories_array[$index][] = array (
				'id' => $id,
				'text' => $category['categories_name']
			);
			if (($category['parent_id'] != '') && ($category['parent_id'] != '0')) {
				$categories_array = $this->generateCategoryPath($category['parent_id'], 'category', $categories_array, $index, $callCount + 1);
			}
			if ($callCount == 0) {
				$categories_array[$index] = array_reverse($categories_array[$index]);
			}
		}
		return $categories_array;
	}
	
	/**
	 * Fetches a category path in the language of the current shop interface.
	 * Copied from xt:commerce 3.
	 *
	 * @param int $id
	 *    The id of the product or category
	 * @param string $from
	 *    Set this to 'product' to get the path for a product. For anything else 'category' is assumed.
	 * @param array &$categories_array
	 *    Internally used for recursion. Do not pass an argument here.
	 * @param int &$index
	 *    Internally used for recursion. Do not pass an argument here.
	 * @param int &$callCount
	 *    Internally used for recursion. Do not pass an argument here.
	 * 
	 * @return array
	 *    The category path
	 */
	private function generateMPCategoryPath($id, $from = 'category', $langID, $categories_array = array(), $index = 0, $callCount = 0) {
		$descCol = '';
		if (MagnaDB::gi()->columnExistsInTable('categories_description', TABLE_CATEGORIES_DESCRIPTION)) {
			$descCol = 'categories_description';
		} else {
			$descCol = 'categories_name';
		}
		$trim = " \n\r\0\x0B\xa0\xc2"; # last 2 ones are utf8 &nbsp;
		if ($from == 'product') {
			$categoriesQuery = MagnaDB::gi()->query('
				SELECT categories_id AS Id
				  FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id = "'.$id.'"
			');
			while ($categories = MagnaDB::gi()->fetchNext($categoriesQuery)) {
				if ($categories['Id'] != '0') {
					$category = MagnaDB::gi()->fetchRow('
						SELECT cd.categories_name AS `Name`, cd.'.$descCol.' AS `Description`, c.parent_id AS `ParentId`
						  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd 
						 WHERE c.categories_id = "'.$categories['Id'].'" 
						       AND c.categories_id = cd.categories_id 
						       AND cd.language_id = "'.$langID.'"
					');
					$c = array (
						'Id' => $categories['Id'],
						'ParentId' => $category['ParentId'],
						'Name' => trim(html_entity_decode(strip_tags($category['Name']), ENT_QUOTES, 'UTF-8'), $trim),
						'Description' => $category['Description'],
					);
					if ($c['ParentId'] == '0') {
						unset($c['ParentId']);
					}
					if ($c['Description'] == '') {
						$c['Description'] = $c['Name'];
					}
					$categories_array[$index][] = $c;
					if (($category['ParentId'] != '') && ($category['ParentId'] != '0')) {
						$categories_array = $this->generateMPCategoryPath($category['ParentId'], 'category', $langID, $categories_array, $index);
					}
				}
				++$index;
			}
		} else if ($from == 'category') {
			$category = MagnaDB::gi()->fetchRow('
				SELECT c.categories_id AS `Id`, cd.categories_name AS `Name`, cd.'.$descCol.' AS `Description`, c.parent_id AS `ParentId`
				  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd
				 WHERE c.categories_id = "'.$id.'" 
				       AND c.categories_id = cd.categories_id
				       AND cd.language_id = "'.$langID.'"
			');
			$c = array (
				'Id' => $category['Id'],
				'ParentId' => $category['ParentId'],
				'Name' => trim(html_entity_decode(strip_tags($category['Name']), ENT_QUOTES, 'UTF-8'), $trim),
				'Description' => $category['Description'],
			);
			if ($c['ParentId'] == '0') {
				unset($c['ParentId']);
			}
			if ($c['Description'] == '') {
				$c['Description'] = $c['Name'];
			}
			$categories_array[$index][] = $c;
			if (($category['ParentId'] != '') && ($category['ParentId'] != '0')) {
				$categories_array = $this->generateMPCategoryPath($category['ParentId'], 'category', $langID, $categories_array, $index, $callCount + 1);
			}
			if ($callCount == 0) {
				$categories_array[$index] = array_reverse($categories_array[$index]);
			}
		}
		
		return $categories_array;
	}
	
	/**
	 * @deprecated
	 * Fetches the product for one or multiple product ids.
	 * 
	 * @param mixed $pID
	 *    The products id or ids
	 * @param int $languages_id
	 *    The langauge that will be used for the title and description
	 * @param string $addQuery
	 *    An additional filter query that will appendet to the WHERE condition.
	 *
	 * @return array
	 *    The product(s)
	 */
	public function getProductByIdOld($pID, $languages_id = false, $addQuery = '') {
		$lIDs = MagnaDB::gi()->fetchArray('
			SELECT language_code FROM '.TABLE_PRODUCTS_DESCRIPTION.' WHERE products_id="'.$pID.'"
		', true);

		if ($languages_id === false) {
			$languages_id = $_SESSION['magna']['selected_language'];
		}

		if (is_numeric($languages_id)) {
			$languages_id = mlGetLanguageCodeFromID($languages_id);
		}
		
		if (!empty($lIDs) && !in_array($languages_id, $lIDs)) {
			$languages_id = array_shift($lIDs);
		}

		if (is_array($pID)) {
			$where = 'p.products_id IN ("'.implode('", "',  $pID).'")';
		} else {
			$where = 'p.products_id = "'.(int) $pID.'"';
		}

		$products = MagnaDB::gi()->fetchArray(eecho('
			SELECT *, date_format(p.date_available, "%Y-%m-%d") AS products_date_available 
			  FROM '.TABLE_PRODUCTS.' p,
			       '.TABLE_PRODUCTS_DESCRIPTION.' pd,
			       '.TABLE_SEO_URL.' su
			 WHERE '.$where.'
			       AND p.products_id = pd.products_id AND p.products_id = su.link_id
			       AND pd.language_code = "' . $languages_id . '"
			       AND su.language_code = "' . $languages_id . '"
			       AND su.link_type = 1
			      '.$addQuery.'
		', false));
		//var_dump($products); 
		if (!is_array($products) || empty($products)) return false;

		$finalProducts = array();
		foreach ($products as &$product) {
			if ($product['products_image']) {
				$product['products_allimages'] = array($product['products_image']);
			} else {
				$product['products_allimages'] = array();
			}
			$product['products_allimages'] = array_merge(
				$product['products_allimages'],
				(array)MagnaDB::gi()->fetchArray('
					SELECT m.file
					  FROM '.TABLE_MEDIA.' m, '.TABLE_MEDIA_LINK.' ml 
					 WHERE ml.link_id = "'.$product['products_id'].'"
						   AND ml.m_id = m.id
						   AND ml.class = "product"
				  ORDER BY ml.sort_order
				', true)
			);
			if (MagnaDB::gi()->tableExists(TABLE_SYSTEM_STATUS_DESCRIPTION) 
			    && $product['products_vpe']
			    && $product['products_vpe_value']
			) {
				$product['products_vpe_name'] = stringToUTF8(MagnaDB::gi()->fetchOne('
				    SELECT status_name FROM '.TABLE_SYSTEM_STATUS_DESCRIPTION.' 
				    WHERE status_id = '.$product['products_vpe'].'
				    AND language_code = "'.$languages_id.'"
				    ORDER BY status_id, language_code LIMIT 1
				'));
			}
			$finalProducts[$product['products_id']] = $product; 
		}
		if (!is_array($pID)) {
			return $products[0];
		}
		unset($products);
		return $finalProducts;
	}
	
	public function getCategoryPathOld($id, $for = 'category', &$cPath = array()) {
		if ($for == 'product') {
			$cIDs = MagnaDB::gi()->fetchArray('
				SELECT categories_id FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id="'.MagnaDB::gi()->escape($id).'"
			', true);
			if (empty($cIDs)) {
				return array();
			}
			$return = array();
			foreach ($cIDs as $cID) {
				if ((int)$cID == 0) {
					$return[] = array('0');
				} else {
					$cPath = $this->getCategoryPathOld($cID);
					array_unshift($cPath, $cID);
					$return[] = $cPath;
				}
			}
			return $return;
		} else {
			$meh = MagnaDB::gi()->fetchOne(
				'SELECT parent_id FROM '.TABLE_CATEGORIES.' WHERE categories_id="'.MagnaDB::gi()->escape($id).'"'
			);
			$cPath[] = (int)$meh;
			if ($meh != '0') {
				$this->getCategoryPathOld($meh, 'category', $cPath);
			}
			return $cPath;
		}
	}

	/* xt:Commerce Nachbildung */
	public function generateCategoryPathOld($id, $from = 'category', $categories_array = array(), $index = 0, $callCount = 0) {
		if ($from == 'product') {
			$categories_query = MagnaDB::gi()->query('
				SELECT categories_id FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id = "'.$id.'"
			');
			while ($categories = MagnaDB::gi()->fetchNext($categories_query)) {
				if ($categories['categories_id'] == '0') {
					$categories_array[$index][] = array ('id' => '0', 'text' => ML_LABEL_CATEGORY_TOP);
				} else {
					$category_query = MagnaDB::gi()->query('
						SELECT cd.categories_name, c.parent_id 
						  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd 
						 WHERE c.categories_id = "'.$categories['categories_id'].'"
						       AND c.categories_id = cd.categories_id 
						       AND cd.language_code = "'.$_SESSION['magna']['selected_language'].'"
					');
					$category = MagnaDB::gi()->fetchNext($category_query);
					$categories_array[$index][] = array (
						'id' => $categories['categories_id'],
						'text' => $category['categories_name']
					);
					if (($category['parent_id'] != '') && ($category['parent_id'] != '0')) {
						$categories_array = $this->generateCategoryPathOld($category['parent_id'], 'category', $categories_array, $index);
					}
				}
				++$index;
			}
		} else if ($from == 'category') {
			$category_query = MagnaDB::gi()->query('
				SELECT cd.categories_name, c.parent_id 
				  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd
				 WHERE c.categories_id = "'.$id.'"
				       AND c.categories_id = cd.categories_id
				       AND cd.language_code = "'.$_SESSION['magna']['selected_language'].'"
			');
			$category = MagnaDB::gi()->fetchNext($category_query);
			$categories_array[$index][] = array (
				'id' => $id,
				'text' => $category['categories_name']
			);
			if (($category['parent_id'] != '') && ($category['parent_id'] != '0')) {
				$categories_array = $this->generateCategoryPathOld($category['parent_id'], 'category', $categories_array, $index, $callCount + 1);
			}
			if ($callCount == 0) {
				$categories_array[$index] = array_reverse($categories_array[$index]);
			}
		}
	
		return $categories_array;
	}

	/**
	 * Calculates the summary of variations quantity
	 *
	 * @param int $iPid
	 *    The product id
	 *
	 * @param bool $bGetMainQuantity
	 *    If you want get the product quantity if there are no variants
	 *
	 * @return int
	 *    If product got variants it returns integer
	 *    else it returns false
	 */
	public function getProductVariationsQuantity($iPid, $bGetMainQuantity = false) {
		$aProduct = MagnaDB::gi()->fetchRow("
			SELECT products_model AS ProductsModel,
			       products_quantity AS Quantity
			  FROM ".TABLE_PRODUCTS."
			 WHERE products_id = '".$iPid."'
			 LIMIT 1
		");

		// Check for MasterSlavePlugin is installed
		if ($this->hasMasterItems && ($aProduct !== false) && !empty($aProduct['ProductsModel'])) {
			$aVariantsQuantity = MagnaDB::gi()->fetchArray('
				SELECT products_quantity
				  FROM '.TABLE_PRODUCTS.' p
				 WHERE p.products_master_model = "'.MagnaDB::gi()->escape($aProduct['ProductsModel']).'"
			', true);

			if (!empty($aVariantsQuantity)) {
				return array_sum($aVariantsQuantity);
			}
		}

		return (($bGetMainQuantity) ? $aProduct['Quantity'] : false);
	}
	
}
