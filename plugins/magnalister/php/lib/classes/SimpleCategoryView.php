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
 * $Id: SimpleCategoryView.php 453 2014-07-24 22:20:22Z derpapst $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

class SimpleCategoryView {
	protected $cPath = 0;
	protected $search = '';
	protected $productsQuery = '';

	protected $sorting = false;
	protected $allowedProductIDs = array();

	protected $list = array('categories' => array(), 'products' => array());
	protected $settings;
	protected $showOnlyActiveProducts = false;
	protected $_magnasession;
	protected $url = array();
	protected $marketplace = '';
	protected $mpID = 0;

	protected $action = array();

	protected $selection;
	protected $newSelection;
	
	protected $isAjax = false;
	protected $ajaxReply = array();
	
	protected $categoryCheckboxStateCache = array();

	protected $simplePrice = null;
	
	protected $topHTML = '';

	/* caches */
	private $cat2ProdCacheFilter = array();
	private $cat2ProdCacheQuery = '';
	private $__cat2prodCache = array();
	private $__categoryCacheTD = array(); /* Top -> Down */
	private $__categoryCacheBU = array(); /* Bottom -> Up */

	/**
	 * @param $cPath	Selected Category. 0 == top category
	 * @param $sorting	How should the list be sorted? false == default sorting
	 * @param $search   Searchstring for Product
	 * @param $allowedProductIDs	Limit Products to a list of specified IDs, if empty show all Products
	 */
	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '', $allowedProductIDs = array()) {
		global $_MagnaSession, $_url, $magnaConfig;
		
		$this->_magnasession = &$_MagnaSession;
		$this->magnaConfig = &$magnaConfig;

		$this->settings = array_merge(array(
			'ajaxSelector'    => true,
			'showCheckboxes'  => true,
			'selectionName'   => 'general',
			'selectionValues' => array(),
		), $settings);
	
		$this->marketplace = $this->_magnasession['currentPlatform'];
		$this->mpID = $this->_magnasession['mpID'];
	
		$this->showOnlyActiveProducts = getDBConfigValue(
			array($this->_magnasession['currentPlatform'].'.'.$this->settings['selectionName'].'.status', 'val'),
			$this->_magnasession['mpID'],
			false
		);	
		
		$this->isAjax = isset($_GET['kind']) && ($_GET['kind'] == 'ajax');
		
		$this->cPath = isset($_GET['cPath']) ? $_GET['cPath'] : '0';
		$this->cPath = explode('_', $this->cPath);
		$this->cPath = array_pop($this->cPath);
		
		if (!ctype_digit($this->cPath)) {
			$this->cPath = '0';
		}
		//echo var_dump_pre($this->cPath, '$this->cPath');

		/*if (!$this->isAjax) {
			echo print_m($this->_magnasession['currentPlatform'].'.'.$this->settings['selectionName'].'.status');
			echo var_dump_pre($this->showOnlyActiveProducts, '$this->showOnlyActiveProducts');
		}//*/

//		initArrayIfNecessary($_MagnaSession, $_MagnaSession['currentPlatform'].'|selection|'.$this->settings['selectionName']);
//		$this->selection = &$_MagnaSession[$_MagnaSession['currentPlatform']]['selection'][$this->settings['selectionName']];
//		$_MagnaSession[$_MagnaSession['currentPlatform']]['selection'][$this->settings['selectionName']] = array();

		/*MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array (
			'selected' => false,
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'],
			'session_id' => session_id(),
		));*/
		
		$this->init();
		
		$newSelectionResult = MagnaDB::gi()->query('
			SELECT pID, data
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID = \''.$this->_magnasession['mpID'].'\'
			   AND selectionname = \''.$this->settings['selectionName'].'\'
			   AND session_id = \''.session_id().'\'
		');
		$this->selection = array();
		while ($row = MagnaDB::gi()->fetchNext($newSelectionResult)) {
			$this->selection[$row['pID']] = unserialize($row['data']);
		}

		if (empty($allowedProductIDs)) {
			$this->allowedProductIDs = $this->getProductIDsByCategoryID($this->cPath);
		} else {
			$this->allowedProductIDs = $allowedProductIDs;
		}

		// echo print_m($this->allowedProductIDs, '$this->allowedProductIDs');
		if ($this->isAjax) {
			if (preg_match('/^(.*)\[(.*)\]$/', $_POST['action'], $match)) {
				$_POST[$match[1]][$match[2]] = 0;
			}
			$_timer = microtime(true);
		}

		// echo print_m($this->allowedProductIDs, '$this->allowedProductIDs');

		$this->selectProducts();

		if (!empty($this->ajaxReply)) {
			$this->ajaxReply['timer'] = microtime2human(microtime(true) -  $_timer);
		}

		if (!$this->isAjax) {
			$this->sorting = $sorting;
			$this->search = $search;
			$this->simplePrice = new SimplePrice();
			$this->simplePrice->setCurrency(DEFAULT_CURRENCY);
			$this->url = $_url;
			if (empty($this->url['cPath'])) {
				unset($this->url['cPath']);
			}

			if (($this->search == '') && isset($_POST['tfSearch']) && !empty($_POST['tfSearch'])) {
				$this->search = $_POST['tfSearch'];
			}
			if (empty($this->sorting) && isset($_GET['sorting']) && !empty($_GET['sorting'])) {
				$this->sorting = $_GET['sorting'];
			}

			//echo print_m(array_diff($this->selection, $this->newSelection), 'diff($selection, $newSelection)');
		} else {
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Thu, 01 Jan 1970 00:00:00 GMT"); // Datum in der Vergangenheit
			header('Content-Type: text/plain');
		}
	}
	
	public function __destruct() {
		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'],
			'session_id' => session_id()
		));
		if (!empty($this->selection)) {
			$batch = array();
			foreach ($this->selection as $pID => $data) {
				$batch[] = array(
					'pID' => $pID,
					'data' => serialize($data),
					'mpID' => $this->_magnasession['mpID'],
					'selectionname' => $this->settings['selectionName'],
					'session_id' => session_id(),
					'expires' => gmdate('Y-m-d H:i:s')
				);
			}
			MagnaDB::gi()->batchinsert(TABLE_MAGNA_SELECTION, $batch, true);
			unset($batch);
		}
	}

	protected function init() {

	}

	private function selectProducts() {
		#echo print_m($_POST, true);
		$sPIDs = array();
		if (array_key_exists('selectableProducts', $_POST) &&
			($_POST['selectableProducts'] = trim($_POST['selectableProducts'])) &&
			!empty($_POST['selectableProducts'])
		) {
			$sPIDs = explode(':', $_POST['selectableProducts']);
		}
		$sCIDs = array();
		if (array_key_exists('selectableCategories', $_POST) && 
			($_POST['selectableCategories'] = trim($_POST['selectableCategories'])) &&
			!empty($_POST['selectableCategories'])
		) {
			$sCIDs = explode(':', $_POST['selectableCategories']);
		}

		if (array_key_exists('pAdd', $_POST)) {
			$pID = array_keys($_POST['pAdd']);
			$this->addProductsToSelection($pID);
			$this->ajaxReply = array (
				'type' => 'p',
				'checked' => true,
				'newname' => 'pRemove['.$pID[0].']'
			);
		}
		if (array_key_exists('pRemove', $_POST)) {
			$pID = array_keys($_POST['pRemove']);
			$this->removeProductsFromSelection($pID);
			$this->ajaxReply = array (
				'type' => 'p',
				'checked' => false,
				'newname' => 'pAdd['.$pID[0].']'
			);
		}
		if (array_key_exists('cAdd', $_POST)) {
			$cID = array_keys($_POST['cAdd']);
			if ($cID[0] == '0') {
				//echo print_m($sPIDs, '$sPIDs[add]');
				//echo print_m($sCIDs, '$sCIDs[add]');
				if (!empty($sPIDs) || !empty($sCIDs)) {
					if (!empty($sCIDs)) {
						foreach ($sCIDs as $sCID) {
							$this->addProductsToSelection($this->getProductIDsByCategoryID($sCID));
						}
					}
					if (!empty($sPIDs)) {
						$this->addProductsToSelection($sPIDs);
					}
				} else {
					$this->addProductsToSelection($this->allowedProductIDs);
				}
				$this->ajaxReply = array (
					'type' => 'a',
					'checked' => true,
					'newname' => 'cRemove[0]'
				);
			} else {
				#echo print_m($this->getProductIDsByCategoryID($cID[0]), true);
				$this->addProductsToSelection($this->getProductIDsByCategoryID($cID[0]));
				$this->ajaxReply = array (
					'type' => 'c',
					'checked' => true,
					'newname' => 'cRemove['.$cID[0].']'
				);
			}
		}
		if (array_key_exists('cRemove', $_POST)) {
			$cID = array_keys($_POST['cRemove']);
			if ($cID[0] == '0') {
				//echo print_m($sPIDs, '$sPIDs[add]');
				//echo print_m($sCIDs, '$sCIDs[add]');
				if (!empty($sPIDs) || !empty($sCIDs)) {
					if (!empty($sCIDs)) {
						foreach ($sCIDs as $sCID) {
							$this->removeProductsFromSelection($this->getProductIDsByCategoryID($sCID));
						}
					}
					if (!empty($sPIDs)) {
						$this->removeProductsFromSelection($sPIDs);
					}
				} else {
					$this->selection = array();
				}
				$this->ajaxReply = array (
					'type' => 'a',
					'checked' => false,
					'newname' => 'cAdd[0]'
				);
			} else {
				$this->removeProductsFromSelection($this->getProductIDsByCategoryID($cID[0]));
				$this->ajaxReply = array (
					'type' => 'c',
					'checked' => false,
					'newname' => 'cAdd['.$cID[0].']'
				);
			}
		}
	}

	public function getCategoryCache() {
		if (empty($this->__categoryCacheTD)) {
			$where = ((SHOPSYSTEM != 'oscommerce') && $this->showOnlyActiveProducts) ? 'WHERE categories_status<>0' : '';
			$catQuery = MagnaDB::gi()->query('
			    SELECT categories_id, parent_id 
			      FROM '.TABLE_CATEGORIES.' 
			     '.$where.'
			');
			$this->__categoryCacheTD = array();
			while ($tmp = MagnaDB::gi()->fetchNext($catQuery)) {
				$this->__categoryCacheTD[(int)$tmp['parent_id']][] = (int)$tmp['categories_id'];
				$this->__categoryCacheBU[(int)$tmp['categories_id']][] = (int)$tmp['parent_id'];
			}
			unset($catQuery);
			unset($tmp);
		}
	}

	public function getAllSubCategoriesOfCategory($cID = 0) {
		$this->getCategoryCache();

		$subCategories = isset($this->__categoryCacheTD[$cID]) ? $this->__categoryCacheTD[$cID] : array();
	
		if (!empty($subCategories)) {
			foreach ($subCategories as $c) {
				$b = $this->getAllSubCategoriesOfCategory($c);
				$this->mergeArrays($subCategories, $b);
			}
		}
	
		return $subCategories;
	}

	protected function setCat2ProdCacheQueryFilter($ex = array()) {
		$this->cat2ProdCacheFilter = $ex;
	}

	protected function setupCat2ProdCacheQuery($ex = array()) {
		if (!empty($ex)) {
			$this->cat2ProdCacheFilter = $ex;
		}
		$this->cat2ProdCacheQuery = '
		    SELECT DISTINCT p2c.products_id, p2c.categories_id 
		      FROM '.TABLE_PRODUCTS_TO_CATEGORIES.' p2c
		      JOIN '.TABLE_PRODUCTS.' p ON p.products_id = p2c.products_id
		';
		$where = '';
		$join = '';
		if (!empty($this->cat2ProdCacheFilter)) {
			foreach ($this->cat2ProdCacheFilter as $item) {
				$this->cat2ProdCacheQuery .= (empty($item['join'])) ? '' : '
		           '.$item['join'];
				$where .= (empty($item['where'])) ? '' : ' AND
		           '.$item['where'];
			}
		}
		$where .= ($this->showOnlyActiveProducts) ? ' AND p.products_status<>0' : '';
		$where .= (getDBConfigValue('general.keytype', '0') == 'artNr') ? ' AND p.products_model<>\'\'' : '';
		$this->cat2ProdCacheQuery .= $join.(!empty($where) ? '
		     WHERE '.substr($where, strlen(' AND')) : '');
		#echo var_dump_pre($this->showOnlyActiveProducts, '$this->showOnlyActiveProducts', true);
		#echo print_m($this->cat2ProdCacheQuery)."\n";
		//die();
	}

	public function getCat2ProdCache() {
		if (empty($this->__cat2prodCache)) {
			if (empty($this->cat2ProdCacheQuery)) $this->setupCat2ProdCacheQuery();
			$prod2catQuery = MagnaDB::gi()->query($this->cat2ProdCacheQuery);
			$this->__cat2prodCache = array();
			while ($tmp = MagnaDB::gi()->fetchNext($prod2catQuery)) {
				if ($tmp['products_id'] == '0') continue;
				$this->__cat2prodCache[(int)$tmp['categories_id']][] = (int)$tmp['products_id'];
			}
			#echo print_m($this->__cat2prodCache, '__cat2prodCache', true);
			unset($prod2catQuery);
			unset($tmp);
		}
	}

	public function getProductIDsByCategoryID($cID) {
		$this->getCat2ProdCache();

		$subCategories = array($cID);
		$c = $this->getAllSubCategoriesOfCategory($cID);
		$this->mergeArrays($subCategories, $c);
		unset($c);
			
		$productIDs = array();
		if (!empty($subCategories)) {
			foreach ($subCategories as $cC) {
				$copyArray = isset($this->__cat2prodCache[$cC]) ? $this->__cat2prodCache[$cC] : array();
				$this->mergeArrays(
					$productIDs,
					$copyArray
				);
			}
		}
		return array_unique($productIDs);
	}

	protected function getChildProductsOfThisLevel($cID) {
		if (empty($this->cat2ProdCacheQuery)) $this->setupCat2ProdCacheQuery();
		if ($cID == 0) {
			$productsWithCat = MagnaDB::gi()->fetchArray("
			    SELECT DISTINCT products_id
			      FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
			", true);
			return MagnaDB::gi()->fetchArray("
			    SELECT DISTINCT p.products_id
			      FROM ".TABLE_PRODUCTS." p
			           ".(!empty($productsWithCat) ? 'WHERE p.products_id NOT IN ('.implode(', ', $productsWithCat).')' : '')."
			", true);
		} else {
			$extQuery = $this->cat2ProdCacheQuery.' '.(
				(strpos($this->cat2ProdCacheQuery, 'WHERE') !== false)
					? 'AND'
					: 'WHERE'
				).' p2c.categories_id=\''.$cID.'\'';
			$pIDs = array();
			#echo print_m($extQuery, '$extQuery');
			$r = MagnaDB::gi()->query($extQuery);
			while($row = MagnaDB::gi()->fetchNext($r)) {
				$pIDs[] = $row['products_id'];
			}
			return $pIDs;
		}
	}

	private function mergeArrays(&$sourceArray, &$copyArray){
		//merge copy array into source array
		$i = 0;
		while (isset($copyArray[$i])){
			$sourceArray[] = $copyArray[$i];
			unset($copyArray[$i]);
			++$i;
		}
	}

	private function addProductsToSelection($productsToAdd) {
		if (!empty($productsToAdd)) {
			foreach ($productsToAdd as $p) {
				if (!array_key_exists($p, $this->selection)) {
					$this->selection[(string)$p] = $this->settings['selectionValues'];
				}
			}
		}
	}

	private function removeProductsFromSelection($productsToRemove) {
		if (!empty($productsToRemove)) {
			foreach ($productsToRemove as $p) {
				if (array_key_exists($p, $this->selection)) {
					unset($this->selection[(string)$p]);
				}
			}
		}
	}

	protected function getSorting() {
		if (!$this->sorting) {
			$this->sorting = 'name';
		}
	    switch ($this->sorting) {
	        case 'price'        :
	            $sort['cat']  = 'TRIM(cd.categories_name) ASC';
	            $sort['prod'] = 'p.products_price ASC';
	            break;
	        case 'price-desc'   :
	            $sort['cat']  = 'TRIM(cd.categories_name) ASC';
	            $sort['prod'] = 'p.products_price DESC';
	            break;
	        case 'name-desc'    :
	            $sort['cat']  = 'TRIM(cd.categories_name) DESC';
	            $sort['prod'] = 'TRIM(pd.products_name) DESC';
	            break;
	        case 'name'         :
	        default             :
	            $sort['cat']  = 'TRIM(cd.categories_name) ASC';
	            $sort['prod'] = 'TRIM(pd.products_name) ASC';
	            break;
	    }
		return $sort;
	}
	
	private function getParentCategories($cID, &$categories) {
		$topCID = ($this->search == '') ? $this->cPath : 0;
		
		$copyArray = isset($this->__categoryCacheBU[(int)$cID]) ? $this->__categoryCacheBU[(int)$cID] : array();
		if (empty($copyArray)) {
			return;
		}
		foreach($copyArray as $addCID) {
			if (!array_key_exists($addCID, $categories)) {
				if (($addCID == $topCID) || ($addCID == 0)) {
					return;
				}
				$categories[$addCID] = 0;
				$this->getParentCategories($addCID, $categories);
			}
		}
	}
	
	/**
	 * Sucht von Unterkategorie richtung Oberkategorie. Daher kann 
	 * $this->__categoryCacheTD nicht verwendet werden. Dieser ist fuer die andere
	 * Suchrichtung optimiert.
	 */
	private function getAllParentCategories(&$categories) {
		$this->getCategoryCache();
		$categories = array_flip($categories);

		foreach ($categories as $cID => $null) {
			$categories[(int)$cID] = 0;
			$this->getParentCategories($cID, $categories);
		}
		return array_keys($categories);
	}

	protected function retriveList() {
		$sort = $this->getSorting();
		if (!isset($_POST['FilterBy'])) { // no categories
			// echo print_m($this->allowedProductIDs);
			if (!empty($this->allowedProductIDs)) {
				$allowedCategories = MagnaDB::gi()->fetchArray('
					SELECT DISTINCT p2c.categories_id 
					  FROM '.TABLE_PRODUCTS_TO_CATEGORIES.' p2c
					 WHERE p2c.products_id IN ('.implode(', ', $this->allowedProductIDs).')
				', true);
				
				/* Get all involved parent categories */
				if (!empty($allowedCategories)) {
					// echo print_m($allowedCategories, '$allowedCategories');
					//$_t = microtime(true);
					$allowedCategories = $this->getAllParentCategories($allowedCategories);
					//echo microtime2human(microtime(true) - $_t);
				}
				//echo print_m($allowedCategories, '$allowedCategories');
	
				$allowedCategoriesWhere = 'c.categories_id IN ('.implode(', ', $allowedCategories).') AND ';
			} else {
				$allowedCategoriesWhere = '(0 = 1) AND '; // false... obviously
			}
	
			$queryStr = '
				  SELECT c.categories_id, cd.categories_name, c.categories_image, c.parent_id
				    FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd 
				   WHERE c.categories_id = cd.categories_id AND 
						 cd.language_code = \''.$_SESSION['magna']['selected_language'].'\' AND 
						 '.($this->showOnlyActiveProducts ? 'categories_status<>0 AND' : '').'
						 '.$allowedCategoriesWhere;
	
			if ($this->search != '') {
			    $queryStr .= "cd.categories_name like '%" . $this->search . "%' ";
		    } else {
		    	$queryStr .= "c.parent_id = '" . $this->cPath . "' ";
		    }
	
		    $queryStr .= "ORDER BY " . $sort['cat'];
			//echo print_m($queryStr, 'CategoryQuery');
			
			$categories = MagnaDB::gi()->fetchArray($queryStr);
			//echo var_dump_pre($categories, '$categories');
			$this->list['categories'] = array();
			if (!empty($categories)) {
				foreach ($categories as $category) {
					$category['allproductsids'] = $this->getProductIDsByCategoryID($category['categories_id']);
					$this->list['categories'][$category['categories_id']] = $category;
				}
			}
			unset($categories);
		}
		if ($this->productsQuery == '') {
			$this->setupProductsQuery();
		}
		
		//echo print_m($this->productsQuery, '$this->productsQuery');
		$products = MagnaDB::gi()->fetchArray($this->productsQuery);
		$this->list['products'] = array();
		if (!empty($products)) {
			foreach ($products as $product) {
				$this->list['products'][$product['products_id']] = $product;
			}
		}
		unset($products);

		//echo print_m($this->allowedProductIDs, '$this->allowedProductIDs');
	}

	protected function setupProductsQuery($fields = '', $from = '', $where = '') {
		$sort = $this->getSorting();
		
		if (!empty($this->allowedProductIDs)) {
			$whereProducs = 'p.products_id IN ('.implode(', ', $this->allowedProductIDs).')';
		} else {
			$whereProducs = '(0 = 1)'; // false again... ZOMG
		}

		$this->productsQuery = '
			SELECT p.products_tax_class_id, p.products_id, pd.products_name, p.products_model,
			       p.products_quantity, p.products_image, p.products_price, 
			       p2c.categories_id'.(($fields != '') ? (', '.$fields) : '').'
			  FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd,
			       '.TABLE_PRODUCTS_TO_CATEGORIES.' p2c
			       '.(($from != '') ? (', '.$from) : '').'
			 WHERE p.products_id = pd.products_id 
			   AND pd.language_code=\''.$_SESSION['magna']['selected_language'].'\'
			   AND p.products_id = p2c.products_id
				   '.(($this->showOnlyActiveProducts) ? 'AND p.products_status != 0' : '').'
			   AND '.$whereProducs.' '.(($where != '') ? ('AND '.$where) : '').' ';

		if ($this->search != '') {
			$addQuery = '';
			$search = MagnaDB::gi()->escape($this->search);
			/* {Hook} "SimpleCategoryView_ProductSearch": Enables you to extend the product search with additional features.<br>
			   Variables that can be used: <ul><li>$addQuery: extend the product serarch query with your own condition</li>
			   <li>$search: the string the user has searched for (already escaped, to unescape use MagnaDB::unescape())</li></ul>
			 */
			if (($hp = magnaContribVerify('SimpleCategoryView_ProductSearch', 1)) !== false) {
				require($hp);
			}
			if (empty($addQuery)) {
				$addQuery = '
		   	       AND (
		   	            pd.products_name LIKE \'%'.$this->search.'%\' OR p.products_model LIKE \'%'.$this->search.'%\'
		   	            OR p.products_id=\''.$this->search.'\'
		   	       )';
			}
			$this->productsQuery .= $addQuery.'
		  GROUP BY p.products_id ';
		    $this->productsQuery = str_replace('SELECT', 'SELECT DISTINCT', $this->productsQuery);
		    
		} else if (!isset($_POST['FilterBy'])) {
			$this->productsQuery .= 'AND p2c.categories_id = \''.$this->cPath.'\' ';
		}
		$this->productsQuery .= 'ORDER BY '.$sort['prod'];
		//echo print_m($this->productsQuery, 'ProductsQuery');
	}

	private function buildCPath($newCID) {
		return $newCID;
	}

	private function getCategoryCheckboxState($id) {
		if (!array_key_exists($id, $this->categoryCheckboxStateCache)) {
			if (count($this->selection) == 0) {
				$iTotal = 1; //something
				$iSelected = 0;
			}else{
				$aTotal=$this->getProductIDsByCategoryID($id, true);
				$iTotal = count($aTotal);
				$iSelected = found_in_array($aTotal, $this->selection);
			}
			if ($iSelected == 0) {
				$this->categoryCheckboxStateCache[$id] = array(
					'state' => 'unchecked',
					'add' => true
				);
			} else if ($iSelected < $iTotal) {
				$this->categoryCheckboxStateCache[$id] = array(
					'state' => 'semichecked',
					'add' => true
				);
			} else if ($iSelected == $iTotal) {
				$this->categoryCheckboxStateCache[$id] = array(
					'state' => 'checked',
					'add' => false
				);
			}
		}
		return $this->categoryCheckboxStateCache[$id];
	}

	private function categorySelector($cID) {
		$cB = $this->getCategoryCheckboxState($cID, true);
		return '<input type="'.($this->settings['ajaxSelector'] ? 'button' : 'submit').'" '.
		               'class="checkbox '.$cB['state'].'" value="" '.
		               'name="c'.($cB['add'] ? 'Add' : 'Remove').'['.$cID.']" '.
		               'id="c_'.$cID.'" '.
		               'title="'.($cB['add'] ? ML_LABEL_SELECT_ALL_PRODUCTS_OF_CATEGORY : ML_LABEL_DESELECT_ALL_PRODUCTS_OF_CATEGORY).'" />';
	}

	private function productSelector($pID) {
		if (array_key_exists($pID, $this->selection)) {
			return '<input type="'.($this->settings['ajaxSelector'] ? 'button' : 'submit').'" '.
			               'class="checkbox checked" value="" name="pRemove['.$pID.']" '.
			               'id="p_'.$pID.'" title="'.ML_LABEL_DESELECT_PRODUCT.'"/>';
		}
		return '<input type="'.($this->settings['ajaxSelector'] ? 'button' : 'submit').'" '.
		               'class="checkbox unchecked" value="" name="pAdd['.$pID.']" '.
		               'id="p_'.$pID.'" title="'.ML_LABEL_SELECT_PRODUCT.'"/>';
	}

	private function topSelectionButtons() {
		$label = '<label for="selectAll">'.ML_LABEL_CHOICE.'</label>';
		if (empty($this->list['categories']) && empty($this->list['products'])) {
			return '<input type="'.($this->settings['ajaxSelector'] ? 'button' : 'submit').'" 
			               class="checkbox" value="" title="'.ML_LABEL_NO_PRODUCTS_SELECTABLE.'" disable="disable"/>';
		}

		$toAdd = 0;
		$toRemove = 0;
		if (!empty($this->list['categories'])) {
			foreach ($this->list['categories'] as $cID => $value) {
				$cB = $this->getCategoryCheckboxState($cID);
				$cB['add'] ? ++$toAdd : ++$toRemove;
			}
		}
		//echo '<pre>$toAdd = '.$toAdd.'; $toRemove = '.$toRemove.';</pre>';
		if (!empty($this->list['products'])) {
			foreach ($this->list['products'] as $pID => $value) {
				array_key_exists($pID, $this->selection) ? ++$toRemove : ++$toAdd;
			}
		}
		//echo '<pre>$toAdd = '.$toAdd.'; $toRemove = '.$toRemove.';</pre>';
		if (($toAdd == 0) && ($toRemove > 0)) {
			$name = 'cRemove';
			$state = 'checked';
			$add = false;
		} else if (($toAdd > 0) && ($toRemove == 0)) {
			$name = 'cAdd';
			$state = 'unchecked';
			$add = true;
		} else {
			$name = 'cAdd';
			$state = 'semichecked';
			$add = true;
		}		
		
		$addFields = '';
		if (   !empty($this->search)
		    || (
		           isset($_POST['FilterBy'])
		        && ($_POST['FilterBy'] != 0)
		    )
		) {
			if (!empty($this->list['categories'])) {
				$addFields .= '
					<input type="hidden" id="selectableCategories" name="selectableCategories" value="'.implode(':', array_keys($this->list['categories'])).'"/>';
			}
			if (!empty($this->list['products'])) {
				$addFields .= '
					<input type="hidden" id="selectableProducts" name="selectableProducts" value="'.implode(':', array_keys($this->list['products'])).'"/>';
			}
		}
		return $addFields.'
			<input id="selectAll" type="'.($this->settings['ajaxSelector'] ? 'button' : 'submit').'" '.
			       'class="checkbox '.$state.'" value="" name="'.$name.'['.(!empty($this->search) ? 0 : $this->cPath).']" title="'.
			            (($this->cPath == 0) 
			            	? 
			            		($add ? ML_LABEL_SELECT_ALL_PRODUCTS : ML_LABEL_DESELECT_ALL_PRODUCTS) 
			            	:
			            		($add ? ML_LABEL_SELECT_ALL_PRODUCTS_OF_CATEGORY : ML_LABEL_DESELECT_ALL_PRODUCTS_OF_CATEGORY)
			            ).
			       '" />'.$label;
		
	}

	protected function sortByType($type) {
		return '
			<span class="nowrap">
				<a href="'.toURL($this->url, array('sorting' => $type.'')).'" title="'.ML_LABEL_SORT_ASCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_ASCENDING.'" src="'.DIR_MAGNALISTER_IMAGES.'sort_up.png" />
				</a>
				<a href="'.toURL($this->url, array('sorting' => $type.'-desc')).'" title="'.ML_LABEL_SORT_DESCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_DESCENDING.'" src="'.DIR_MAGNALISTER_IMAGES.'sort_down.png" />
				</a>
			</span>';
	}
	
	protected function getEmptyInfoText() {
		return ML_LABEL_EMPTY;
	}
	
	public function appendTopHTML($html) {
		$this->topHTML .= $html;
	}
	
	public function prependTopHTML($html) {
		$this->topHTML = $html.$this->topHTML;
	}
	
	public function printForm() {
		global $cPath_array; /* xt:commerce */

		if (array_key_exists('cPath', $_GET) && ($_GET['cPath'] != '')) {
			$this->url['cPath'] = $_GET['cPath'];
		}
		if ($this->sorting) {
			$this->url['sorting'] = $this->sorting;
		}

		if (empty($this->list['categories']) && empty($this->list['products'])) {
			$this->retriveList();
		}
		//echo print_m($this->list, '$this->list');

		$html = '
		<div id="managerUIElements">'.$this->topHTML.'<div class="visualClear"></div></div>
		<form class="categoryView" action="'.toURL($this->url).'" method="post">
			<input name="tfSearch" type="hidden" value="'.$this->search.'"/>
			<table class="list"><thead>
				<tr>
					<td class="nowrap edit"'.($this->settings['showCheckboxes'] ? ' colspan="2"' : '').'>
						'.($this->settings['showCheckboxes'] ? $this->topSelectionButtons() : '').'
					</td>
					<td class="katProd">'.ML_LABEL_CATEGORIES_PRODUCTS.' '.$this->sortByType('name').'</td>
					<td class="price">'.ML_LABEL_SHOP_PRICE.' '.$this->sortByType('price').'</td>
					'.$this->getAdditionalHeadlines().'
				</tr>
			</thead><tbody>
		';
		$odd = true;
		
		if (!empty($this->list['categories'])) {
			foreach ($this->list['categories'] as $category) {
				$html .= '
					<tr class="'.(($odd = !$odd) ? 'odd' : 'even').'">
						'.($this->settings['showCheckboxes'] ? '<td class="edit">'.$this->categorySelector($category['categories_id']).'</td>' : '').'
						<td class="image">'.generateProductCategoryThumb($category['categories_image'], 20, 20).'</td>
						<td><a href="'.toURL($this->url, array('cPath' => $this->buildCPath($category['categories_id']))).'">
							'.$this->imageHTML(DIR_MAGNALISTER_IMAGES.'folder.png', ML_LABEL_CATEGORY).' '.fixHTMLUTF8Entities($category['categories_name']).'
						</a></td>
						<td>&mdash;</td>
						'.$this->getAdditionalCategoryInfo($category['categories_id'], $category).'
					</tr>
				';
			}
		}
		if (!empty($this->list['products'])) {
			foreach ($this->list['products'] as $product) {
				$this->simplePrice->setPrice($product['products_price']);
				$netto = $this->simplePrice->format(true);
				$html .= '
					<tr class="'.(($odd = !$odd) ? 'odd' : 'even').'">
						'.($this->settings['showCheckboxes'] ? '<td class="edit">'.$this->productSelector($product['products_id']).'</td>' : '').'
						<td class="image">'.generateProductCategoryThumb($product['products_image'], ML_THUMBS_MINI, ML_THUMBS_MINI).'</td>
						<td><table class="nostyle"><tbody>
								<tr><td class="icoWidth">'.$this->imageHTML(DIR_MAGNALISTER_IMAGES.'shape_square.png', ML_LABEL_PRODUCT).'</td>
								    <td>'.fixHTMLUTF8Entities($product['products_name']).'</td></tr>
								<tr><td class="icoWidth">&nbsp;</td>
								    <td class="artNr">'.ML_LABEL_ART_NR_SHORT.': '.(!empty($product['products_model']) ? $product['products_model'] : '&mdash;').'</td></tr>
							</tbody></table>
						<td><table class="nostyle"><tbody>
								<tr><td>'.ML_LABEL_BRUTTO.':&nbsp;</td><td class="textright">'
									.$this->simplePrice->addTaxByTaxID($product['products_tax_class_id'])->format(true)
								.'</td></tr>
						    	<tr><td>'.ML_LABEL_NETTO.':&nbsp;</td><td class="textright">'.$netto.'</td></tr>
						    </tbody></table>
						</td>
						'.$this->getAdditionalProductInfo($product['products_id'], $product).'
					</tr>
				';
			}
		}
		if (empty($this->list['categories']) && empty($this->list['products'])) {
			$cols = substr_count($html, '</td>');
			$html .= '
				<tr class="even">
					<td class="center bold" colspan="'.($cols+1).'">'.$this->getEmptyInfoText().'</td>
				</tr>
			';
		}

		$html .= '
			</tbody></table>
		</form>';

		ob_start();?>
		<script type="text/javascript">/*<![CDATA[*/
function toggleCheckboxClasses(elem, state) {
	if (typeof(elem) == 'string') {
		elem = '#'+elem;
	}
	if (state) {
		$(elem).addClass('checked').removeClass('semichecked').removeClass('unchecked');
	} else {
		$(elem).removeClass('checked').removeClass('semichecked').addClass('unchecked');
	}
}
function str_replace(search, replace, subject) {
    return subject.split(search).join(replace);
}

$(document).ready(function() {
	$('form.categoryView input[type="button"]').each(function() {
		$(this).click(function () {
			elem = $(this);
			elemID = $(this).attr('id')
			sCIDs = '';
			sPIDs = '';
			if (elemID == 'selectAll') {
				sC = $('#selectableCategories');
				sP = $('#selectableProducts');
				if (sC.length > 0) {
					sCIDs = sC.val();
				}
				if (sP.length > 0) {
					sPIDs = sP.val();
				}
			}
			jQuery.blockUI(blockUILoading);
			jQuery.ajax({
				type: 'POST',
				url: '<?php echo toURL($this->url, array('kind' => 'ajax', 'ts' => time()), true);?>',
				dataType: 'json',
				//contentType: 'application/json',
				data: {
					'action': $(this).attr('name'),
					'selectableCategories': sCIDs,
					'selectableProducts': sPIDs
				},
				success: function(data) {
					if (data == undefined || data == null) { /* Ein seltsamer Bug. Fast nicht reproduzierbar. */
						if (debugging == true) {
							myConsole.log($(elem).attr('name'), data);
						} else {
							window.location.href = '<?php echo toURL($this->url, true); ?>';
						}
					}
					toggleCheckboxClasses(elemID, data.checked);
					$(elem).attr('name', data.newname);
					if ($(elem).attr('id') == 'selectAll') {
						$('form.categoryView input[type="button"]:not(#selectAll)').each(function () {
							if (data.checked) {
								$(this).attr('name', str_replace('Add', 'Remove', $(this).attr('name')));
							} else {
								$(this).attr('name', str_replace('Remove', 'Add', $(this).attr('name')));
							}
							toggleCheckboxClasses(this, data.checked);
						});
					} else {
						checkedX = 0;
						itemCount = $('form.categoryView input[type="button"]:not(#selectAll)').each(function () {
							if ($(this).hasClass('checked')) {
								++checkedX;
							}
						}).length;
						if (checkedX == 0) {
							toggleCheckboxClasses($('#selectAll').attr('name', str_replace('Remove', 'Add', $('#selectAll').attr('name'))), false);
						} else if (checkedX == itemCount) {
							toggleCheckboxClasses($('#selectAll').attr('name', str_replace('Add', 'Remove', $('#selectAll').attr('name'))), true);
						} else {
							$('#selectAll').attr(
								'name', 
								str_replace('Remove', 'Add', $('#selectAll').attr('name'))
							).removeClass('checked').removeClass('unchecked').addClass('semichecked');
						}
					}
					myConsole.log('It took '+data.timer+' to perform this action.');
					jQuery.unblockUI();
				},
				error: function(xhr, status, error) {
					if (debugging == true) {
						myConsole.log(xhr);
						jQuery.unblockUI();
					} else {
						window.location.href = '<?php echo toURL($this->url, true); ?>';
					}
				}
			});
		});
	});
	
	$('form.categoryView').submit(function() {
		jQuery.blockUI(blockUILoading); 
	});
});
		/*]]>*/</script><?php
		$html .= ob_get_contents();	
		ob_end_clean();

		$leftButtons = $this->getLeftButtons();
		if (empty($leftButtons)) {
			if (count($cPath_array) > 1) {
				$leftButtons = $cPath_array;
				array_pop($leftButtons);
				$leftButtons = '<a class="ml-button" href="'.toURL($this->url, array('cPath' => implode('_', $leftButtons))).'">'.
					$this->imageHTML(DIR_MAGNALISTER_IMAGES.'folder_back.png', ML_BUTTON_LABEL_BACK).' '. ML_BUTTON_LABEL_BACK . 
				'</a>';
			} else if (((count($cPath_array) == 1) && ($cPath_array[0] != '0')) || !empty($this->search)) {
				unset($this->url['cPath']);
				$leftButtons = '<a class="ml-button" href="'.toURL($this->url).'">'.
					$this->imageHTML(DIR_MAGNALISTER_IMAGES.'folder_back.png', ML_BUTTON_LABEL_BACK).' '. ML_BUTTON_LABEL_BACK . 
				'</a>';
			}
		}/* else {
			$cPathBack = '&nbsp;';
		}*/
		
		$functionButtons = $this->getFunctionButtons();
		$infoText = $this->getInfoText();
		$html .= '
			<form id="actionForm" name="actionForm" action="'.toURL($this->url, $this->action).'" method="post">
				<input type="hidden" name="timestamp" value="'.time().'"/>
				<table class="actions">
					<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
					<tbody>
						<tr class="firstChild"><td>
							<table><tbody><tr>
								<td class="firstChild">'.$leftButtons.'</td>
								<td><label for="tfSearch">'.ML_LABEL_SEARCH.':</label>
									<input id="tfSearch" name="tfSearch" type="text" value="'.fixHTMLUTF8Entities($this->search, ENT_COMPAT).'"/>
									<input type="submit" class="ml-button" value="'.ML_BUTTON_LABEL_GO.'" name="search_go" /></td>
								<td class="lastChild">'.$functionButtons.'</td>
							</tr></tbody></table>
						</td></tr>
						'.(($infoText != '') ? ('<tr><td colspan="2"><div class="h4">'.ML_LABEL_INFO.'</div>'.$infoText.'</td></tr>') : '').'
					</tbody>
				</table>
				<script type="text/javascript">/*<![CDATA[*/
					$(document).ready(function() {
						$(\'form#actionForm\').submit(function() {
							jQuery.blockUI(blockUILoading); 
						});
					});
				/*]]>*/</script>
			</form>
		';
		return $html;
	}
	
	public function renderAjaxReply() {
		return json_encode($this->ajaxReply);
	}

	private function imageHTML($fName, $alt = '') {
		$alt = ($alt != '') ? $alt : basename($fName);
		return '<img src="'.$fName.'" alt="'.$alt.'" />';
	}

	/* Wird von erbender Klasse ueberschrieben */
	public function getAdditionalHeadlines() { return ''; }

	/* Wird von erbender Klasse ueberschrieben */
	public function getAdditionalCategoryInfo($cID, $data = false) { return ''; }

	/* Wird von erbender Klasse ueberschrieben */
	public function getAdditionalProductInfo($pID, $data = false) { return ''; }

	/* Wird von erbender Klasse ueberschrieben */
	public function getFunctionButtons() { return ''; }
	
	/* Wird von erbender Klasse ueberschrieben */
	public function getLeftButtons() { return ''; }
	
	/* Wird von erbender Klasse ueberschrieben */
	public function getInfoText() { return ''; }

}
