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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
require_once DIR_MAGNALISTER_INCLUDES.'lib/v3fake/Alias/Database.php';
/**
 * layout:
 *		http://dev.magnalister.com/Concepts/InventorySelectList/produktauswahllisten.html
 *		http://dev.magnalister.com/Concepts/InventorySelectList/dropdowns.html
 * 
 * setting:
 * 			'ajaxSelector'    => true,
			'showCheckboxes'  => true,//immer true nicht notwendig
			'selectionName'   => 'general',//name in selection 
			'selectionValues' => array(),//magna_selection.data????
 */
abstract class MLProductList {
	/**
	 * @var int
	 */
	protected $iRowsPerPage = 25;
	/**
	 * @var array
	 */
	protected $aListConfig = array(
		array(
			'head' => array(
				'attributes'	=> 'class="nowrap edit" colspan="2"',
				'content'		=> '',
			),
			'field' => array('selection', 'image')
		),
		array(
			'head' => array(
				'attributes'	=> 'class="katProd"',
				'content'		=> 'ML_GENERIC_ITEM',
				'sort'			=> array('param' => 'name', 'field' => 'TRIM(pd.products_name)'),
				'altSort'		=> array('param' => 'sku',  'field' => 'p.products_model')
			),
			'field' => array('name'),
		),
		array(
			'head' => array(
				'attributes'	=> 'class="price"',
				'content'		=> 'ML_LABEL_SHOP_PRICE',
				'sort'			=> array('param' => 'price', 'field' => 'p.products_price'),
			),
			'field' => array('price'),
		)
	);
	protected $aMagnaSession = null;
	protected $aMagnaConfig = null;
	protected $iShopMandant = null;
	
	/**
	 * @var array
	 */
	protected $aUrl = null;
	
	/**
	 * @var SimplePrice $oPrice
	 */
	protected $oPrice = null;
	
	/**
	 * @var ML_Database_Model_Query_Select $oQuery
	 */
	protected $oQuery = null;
	
	protected $aDependencys = array();
	
	protected $aSelectionData = array();

	/**
	 * @var string main path  of directory of templates and dependency
	 */
	protected $sModulePath = '';
	
	/**
	 * value of magnalister_selection.selectionname
	 * @return string
	 */
	abstract protected function getSelectionName();
	
	public function __construct() {
		global $_MagnaSession, $magnaConfig, $_url;
		$this->aMagnaSession = &$_MagnaSession;
		$this->aMagnaConfig = &$magnaConfig;
		$this->iShopMandant = getDBConfigValue($this->aMagnaSession['currentPlatform'].'.shopmandant', $this->aMagnaSession['mpID'], null);
		$this->aUrl = &$_url;
		$this->oPrice = new SimplePrice();
		$this->oPrice->setCurrency($this->isAjax() ? DEFAULT_CURRENCY : getCurrencyFromMarketplace($this->aMagnaSession['mpID']));
		if(getDBConfigValue(array($this->aMagnaSession['currentPlatform'].'.exchangerate', 'update'), $this->aMagnaSession['mpID'], false)){
		    $success = false;
		    $oSimplePrice = new SimplePrice(null,getCurrencyFromMarketplace($this->aMagnaSession['mpID']));
		    $oSimplePrice->updateCurrencyByService($success);
		}
                $this->buildQuery();
		$this->sModulePath = 'modules/'.(strpos(strtolower(get_class($this)), 'magnacompatible') !== false ? 'magnacompatible' : strtolower($this->aMagnaSession['currentPlatform']));
		$this
			->addDependency('MLProductListDependencySearchFilter', array())
			->addDependency('MLProductListDependencyCategoryFilter', array())
			->addDependency('MLProductListDependencySelectionAction', array('selectionname' => $this->getSelectionName()))
			->addDependency('MLProductListDependencyStatusFilter', array('selectionname' => $this->getSelectionName()))
			->addDependency('MLProductListDependencyManufacturersFilter')
//			->addDependency('MLProductListDependencyHtmlAction', array('html' => '<input type="hidden" name="fuuuu" value="narf"><input type="submit" name="doStuff" value="Zeug">'))
		;
	}
	
	protected function buildQuery(){
		$this->oQuery = MLDatabase::factorySelectClass()
			->select(
				array(
					'p.products_tax_class_id', 
					'p.products_id', 
					'pd.products_name', 
					'p.products_model',
					'p.products_quantity', 
					'p.products_image', 
					'p.products_price',
				)
			)
			->from(TABLE_PRODUCTS, 'p')
			->join(
				array(
					TABLE_PRODUCTS_DESCRIPTION,
					'pd',
					'p.`products_id` = pd.`products_id` AND pd.language_code = "'.$_SESSION['magna']['selected_language'].'"'.(((null !== $this->iShopMandant) && (MagnaDB::gi()->columnExistsInTable('products_store_id', TABLE_PRODUCTS_DESCRIPTION)))? ' AND pd.products_store_id = '.$this->iShopMandant.' ' : ''),
				),
				ML_Database_Model_Query_Select::JOIN_TYPE_INNER
			)
			->orderBy($this->getQuerySort())
			->limit(($this->getCurrentPage()-1) * $this->iRowsPerPage, $this->iRowsPerPage);
		;
		return $this;
	}
	protected function getQuerySort(){
		$sRequestSorting = $this->getRequest('sorting');
		$sSort = '';
		$aRequestSorting = explode('-', $sRequestSorting);
		if (
			count($aRequestSorting) == 2 
			&& in_array($aRequestSorting[1], array('asc', 'desc'))
		) {
			foreach ($this->aListConfig as $aListConfig) {
				foreach (array('sort', 'altSort') as $sKey) {
					if (
							isset($aListConfig['head'][$sKey])
							&& $aRequestSorting[0] == $aListConfig['head'][$sKey]['param']
					) {
						$sSort = $aListConfig['head'][$sKey]['field'].' '.  strtoupper($aRequestSorting[1]);
						break;
					}
				}
			}
		}
		if($sSort == ''){// default
			$sSort = 'TRIM(pd.products_name) ASC';
		}
		return $sSort;
	}
	
	public function injectDependency($sDependencyName, $aDependencyConfig = array()){
		return $this->addDependency($sDependencyName, $aDependencyConfig);
	}
	
	protected function addDependency($sDependencyName, $aDependencyConfig = array()){
		foreach (
			array(
				$this->sModulePath,
				'lib'
			)
			as $sFolder
		) {
			if (file_exists(DIR_MAGNALISTER_INCLUDES.$sFolder.'/classes/ProductList/Dependency/'.$sDependencyName.'.php')) {
				require_once DIR_MAGNALISTER_INCLUDES.$sFolder.'/classes/ProductList/Dependency/'.$sDependencyName.'.php';
				break;
			}
		}
		$oDependency = new $sDependencyName;
		$sMd5 = md5(json_encode(array(get_class($oDependency), $aDependencyConfig)));
		$aFilterRequest = $this->getRequest('filter');
		$mFilterRequest = isset($aFilterRequest[$oDependency->getIdent()]) ? $aFilterRequest[$oDependency->getIdent()] : null;
		$aActionRequest = $this->getRequest('action');
		$mActionRequest = isset($aActionRequest[$oDependency->getIdent()]) ? $aActionRequest[$oDependency->getIdent()] : null;
		$oDependency
			->setProductList($this)
			->setFilterRequest($mFilterRequest)
			->setActionRequest($mActionRequest)
			->setMagnaSession($this->aMagnaSession)
			->setMagnaConfig($this->aMagnaConfig)
			->setConfig($aDependencyConfig)
			->setQuery($this->oQuery)
		;
		$this->aDependencys[$sMd5]=$oDependency;
		return $this;
	}
	
	protected function getDependencies(){
		return $this->aDependencys;
	}
	
	protected function renderDependency($oDependency, $sMethod, $sTemplateFolder){
		$sTemplate = $oDependency->{'get'.$sMethod.'Template'}();
		if(empty($sTemplate)){
			$sOut= '';
		}else{
			ob_start();
			$this->renderTemplate(
				'dependency/'.$sTemplateFolder.'/'.$sTemplate,
				array('oObject' => $oDependency)
			);
			$sOut = ob_get_contents();
			ob_end_clean();
		}
		
			return $sOut;
	}
	
	protected function renderDependencyHeader($oDependency){
		return $this->renderDependency($oDependency, 'header', 'header');
	}

	protected function renderDependencyActionBottomLeft($oDependency){
		return $this->renderDependency($oDependency, 'actionBottomLeft', 'action');
	}
	
	protected function renderDependencyActionBottomRight($oDependency){
		return $this->renderDependency($oDependency, 'actionBottomRight', 'action');
	}
	
	protected function renderDependencyActionBottomCenter($oDependency){
		return $this->renderDependency($oDependency, 'actionBottomCenter', 'action');
	}
	
	protected function renderDependencyFilterLeft($oDependency){
		return $this->renderDependency($oDependency, 'filterLeft', 'filter');
	}
	
	protected function renderDependencyFilterRight($oDependency){
		return $this->renderDependency($oDependency, 'filterRight', 'filter');
	}
	
	protected function renderDependencyActionTop($oDependency){		
		return $this->renderDependency($oDependency, 'actionTop', 'action');
	}
	
	protected function init(){
		$aFilterKeyType = array('in' => null, 'notIn' => null);
		foreach ($this->getDependencies() as $oDependency) {
			$oDependency->manipulateQuery();
			$aDependencyFilterKeyTypes = $oDependency->getKeyTypeFilter();
			if (isset($aDependencyFilterKeyTypes['in']) && is_array($aDependencyFilterKeyTypes['in'])) {
				if ($aFilterKeyType['in'] === null) {
					$aFilterKeyType['in'] = $aDependencyFilterKeyTypes['in'];
				} else {
					$aFilterKeyType['in'] = array_intersect($aFilterKeyType['in'], $aDependencyFilterKeyTypes['in']);
				}
			}
			if (isset($aDependencyFilterKeyTypes['notIn']) && is_array($aDependencyFilterKeyTypes['notIn']) && !empty($aDependencyFilterKeyTypes['notIn'])) {
				if ($aFilterKeyType['notIn'] === null) {
					$aFilterKeyType['notIn'] = $aDependencyFilterKeyTypes['notIn'];
				} else {
					$aFilterKeyType['notIn'] = array_unique (array_merge ($aDependencyFilterKeyTypes['notIn'], $aFilterKeyType['notIn']));
				}
			}
		}
		foreach ($aFilterKeyType as $sType  => $aFilterIdents) {
			if ($aFilterIdents !== null) {
				$this->oQuery->where("
					p.".((getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id')." ".
					(($sType == 'in') ? "IN" : "NOT IN")."
					('".implode("', '", $aFilterIdents)."')"
				);
			}
		}
		foreach ($this->getDependencies() as $oDependency) {
			$oDependency->executeAction();
		}
	}
	
	public function __toString() {
		ob_start();
		if ($this->isAjax()) {
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Thu, 01 Jan 1970 00:00:00 GMT"); // Datum in der Vergangenheit
			header('Content-Type: text/plain');
			$this->init();
		} else {
			$this->init();
			$this->renderTemplate('skeleton');
		}
		$sOut = ob_get_contents();
		ob_end_clean();
		return $sOut;
	}
	
	/**
	 * @param string name of template
	 * @param array assoc for template vars
	 * <code><?php
	 *	$this->renderTemplate(
	 *		'path/to/template/sTemplateName', 
	 *		array('foo' => 'bar')
	 *	);// render template ./ProductList/template/path/to/template/sTemplateName.php and add var $foo = 'bar'
	 * ?></code>
	 * @return \ProductList
	 */
	public function renderTemplate(){
		if (func_num_args()>1) {
			extract(func_get_arg(1));
		}
		foreach (
			array(
				$this->sModulePath,
				'lib'
			)
			as $sFolder
		) {
			if (file_exists(DIR_MAGNALISTER_INCLUDES.$sFolder.'/classes/ProductList/templates/'.func_get_arg(0).'.php')) {
				
				include DIR_MAGNALISTER_INCLUDES.$sFolder.'/classes/ProductList/templates/'.func_get_arg(0).'.php';
				break;
			}
		}
		return $this;
	}
	
	/**
	 * @param bool $blIncludeFilter
	 * @param bool $blIncludePage
	 * @param array $aAdditionalParams
	 * @return string
	 */
	public function getUrl($blIncludeFilter, $blIncludePage, $blIncludeSorting, $aAdditionalParams = array()){
		$blAjax = isset($aAdditionalParams['kind']) && $aAdditionalParams['kind'] == 'ajax';
		return toURL($this->getUrlParameters($blIncludeFilter, $blIncludePage, $blIncludeSorting), $aAdditionalParams, $blAjax);
	}
	
	/**
	 * @param bool $blIncludeFilter
	 * @param bool $blIncludePage
	 * @return string
	 */
	public function getUrlParameters($blIncludeFilter, $blIncludeSorting, $blIncludePage){
		$aUrl = $this->aUrl;
		if($blIncludeFilter){
			$aFilter = $this->getRequest('filter');
			foreach ($this->getDependencies() as $oFilter) {
				$sRequest = isset($aFilter[$oFilter->getIdent()]) ? $aFilter[$oFilter->getIdent()] : null;
				if (!empty($sRequest)) {
					$aUrl['filter['.$oFilter->getIdent().']'] = urlencode($sRequest);
				}
			}
		}
		if($blIncludePage){
			$aUrl['page'] = $this->getCurrentPage();
		}
		if($blIncludeSorting) {
			$aUrl['sorting'] = $this->getRequest('sorting');
		}
		return $aUrl;
	}
	
	/**
	 * @return int
	 */
	protected function getCurrentPage(){
		return ($this->getRequest('page') === null) ? 1 : $this->getRequest('page');
	}
	
	/**
	 * @return int
	 */
	protected function getPageCount(){
		$iPages = $this->oQuery->getCount() / $this->iRowsPerPage;
		if((int) $iPages != $iPages){
			++$iPages;
		}
		return (int)$iPages;
	}
	
	/**
	 * @return SimplePrice 
	 */
	protected function getPrice(){
		return $this->oPrice;
	}
	
	/**
	 * @return array sql-result
	 */
	protected function getProducts(){
		$aResult = $this->oQuery->getResult();
//		echo print_m($aResult, 'result');
//		echo $this->oQuery->getQuery();
		return $aResult;
	}
	
	/**
	 * @param string $sName key of request
	 * @return null $sName not found
	 * @return mixed request
	 */
	protected function getRequest($sName){
		if(isset($_POST[$sName])){
			return $_POST[$sName];
		}elseif(isset($_GET[$sName])){
			return $_GET[$sName];
		}else{
			return null;
		}
	}
	
	/**
	 * @return bool
	 */
	public function isAjax(){
		return $this->getRequest('kind')=='ajax';
	}
	
	protected function getSelectionData($aRow, $sFieldName = null) {
		if (!isset($this->aSelectionData[$aRow['products_id']])) {
			$this->aSelectionData[$aRow['products_id']] = MagnaDB::gi()->fetchRow("
				SELECT * 
				FROM ".TABLE_MAGNA_SELECTION." 
				WHERE 
					`pID` = '".$aRow['products_id']."'
					AND `session_id` = '".session_id()."' 
					AND `mpID` = '".$this->aMagnaSession['mpID']."'
					AND `selectionname` = '".$this->getSelectionName()."'
			");
		}
		if($sFieldName === null){
			return $this->aSelectionData[$aRow['products_id']];
		}else{
			return isset($this->aSelectionData[$aRow['products_id']][$sFieldName]) ? $this->aSelectionData[$aRow['products_id']][$sFieldName] : null;
		}
	}
	
	protected function isInSelection($aRow) {
		return $this->getSelectionData($aRow, 'pID') !== null;
	}
	
}
