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
 * $Id: CheckinSubmit.php 544 2014-11-12 22:28:24Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

abstract class CheckinSubmit {
	protected $mpID = 0;
	protected $marketplace = '';
	protected $_magnasession = array();
	protected $_magnashopsession = array();
	protected $magnaConfig = array();
	protected $url = array();
	
	protected $settings = array();
	
	protected $selection = array();
	protected $badItems = array();
	protected $disabledItems = array();
	
	protected $submitSession = array();
	protected $initSession = array();
	
	protected $ajaxReply = array();
	
	protected $lastRequest = array();
	
	protected $simpleprice = null;
	
	protected $ignoreErrors = false;
	
	private $_timer;

	protected $summaryAddText = ''; # extra Text, je nach Plattform (momentan nur bei eBay belegt)

	public function __construct($settings = array()) {
		global $_MagnaSession, $_MagnaShopSession, $magnaConfig, $_magnaQuery, $_url;
		
		$this->_timer = microtime(true);
		
		if (!isset($settings['marketplace'])) {
			$settings['marketplace'] = $_MagnaSession['currentPlatform'];
		}
		
		$this->mpID = $_MagnaSession['mpID'];
		$this->marketplace = $settings['marketplace'];
		
		$this->settings = array_merge(array(
			'itemsPerBatch'   => 50,
			'selectionName'   => 'checkin',
			'language'        => $_SESSION['magna']['selected_language'],
			'currency'        => DEFAULT_CURRENCY,
			'mlProductsUseLegacy' => true
		), $settings);
		
		if (ctype_digit($this->settings['language'])) {
			$this->settings['languagecode'] = mlGetLanguageCodeFromID($this->settings['language']);
		} else {
			$this->settings['languagecode'] = $this->settings['language'];
		}

		$this->_magnasession = &$_MagnaSession;
		$this->_magnashopsession = &$_MagnaShopSession;
		$this->magnaConfig = &$magnaConfig;
		$this->url = $_url;
		$this->realUrl = array (
			'mp' => $this->mpID,
			'mode' => $_magnaQuery['mode'],
			'view' => isset($_magnaQuery['view']) ? $_magnaQuery['view'] : '',
		);
		
		$this->simpleprice = new SimplePrice();
		/* /!\ Muss in erbenden Klassen entsprechend des Marketplaces gesetzt werden! /!\ */
		$this->simpleprice->setCurrency($this->settings['currency']);
		
		initArrayIfNecessary($this->_magnasession, array($this->mpID, 'submit'));
		$this->submitSession = &$this->_magnasession[$this->mpID]['submit'];
		initArrayIfNecessary($this->_magnasession, array($this->mpID, 'init'));
		$this->initSession = &$this->_magnasession[$this->mpID]['init'];
	}
	
	protected function setUpMLProduct() {
		// reset everything to the defaults
		MLProduct::gi()->resetOptions();
		// Set the language
		MLProduct::gi()->setLanguage($this->settings['language']);
	}
	
	public function init($mode, $items = -1) {
		if ($items == -1) {
			$items = (int)MagnaDB::gi()->fetchOne('
				SELECT count(*)
				  FROM '.TABLE_MAGNA_SELECTION.'
				 WHERE mpID=\''.$this->mpID.'\' AND
				       selectionname=\''.$this->settings['selectionName'].'\' AND
				       session_id=\''.session_id().'\'
			  GROUP BY selectionname
			');
		}

		/* Init all resources needed */
		$this->submitSession = array();
		$this->submitSession['state'] = array (
			'total' => $items,
			'submitted' => 0,
			'success' => 0,
			'failed' => 0
		);
		$this->submitSession['proceed'] = true;
		$this->submitSession['mode'] = $mode;
		$this->submitSession['initialmode'] = $mode;
	}
	
	abstract public function makeSelectionFromErrorLog();
	
	protected function initSelection($offset, $limit) {
		// join used to support sorting by products_name (same sort order as summary view)
		$newSelectionResult = MagnaDB::gi()->query('
		    SELECT ms.pID, ms.data
		      FROM '.TABLE_MAGNA_SELECTION.' ms
		 LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON pd.products_id = ms.pID AND pd.language_code = "'.$this->settings['languagecode'].'"
		     WHERE mpID="'.$this->mpID.'" AND
		           selectionname="'.$this->settings['selectionName'].'" AND
		           session_id="'.session_id().'"
		  ORDER BY pd.products_name ASC
		     LIMIT '.$offset.','.$limit.'
		');
		$this->selection = array();
		while ($row = MagnaDB::gi()->fetchNext($newSelectionResult)) {
			$this->selection[$row['pID']] = unserialize($row['data']);
		}
		
	}
	
	private function deleteSelection() {
		#return;
		foreach ($this->selection as $pID => &$data) {
			$this->badItems[] = $pID;
		}
		$this->badItems = array_merge(
			$this->badItems,
			$this->disabledItems
		);
		if (!empty($this->badItems)) {
			MagnaDB::gi()->delete(
				TABLE_MAGNA_SELECTION, 
				array(
					'mpID' => $this->mpID,
					'selectionname' => $this->settings['selectionName'],
					'session_id' => session_id()
				),
				'AND pID IN ('.implode(', ', $this->badItems).')'
			);
		}
	}
	
	/**
	 * Verify the data before it is processed. 
	 * Allows fixing of missing data or removing the product before bad things may happen.
	 */
	protected function checkSingleItem($pID, $product, $data) {
		return true;
	}
	
	/**
	 * can be overwritten to change product config
	 * @param int $pID
	 * @return array product
	 * @example { //multiple price-configs (ebay)
	 *		protected function getProduct($pID) {
	 *			if (!$this->settings['mlProductsUseLegacy']) {
	 *				// (pre)load prepare-data and check what listing type it is
	 *				if (prepareDataHaveMultiplePrices) {
	 *					MLProduct::gi()->setPriceConfig(array('chinese' => array(), 'buyItNow' => array()));
	 *				}
	 *			}
	 *			return parent::getProduct($pID);
	 *		}
	 * }
	 */
	protected function getProduct($pID) {
		if ($this->settings['mlProductsUseLegacy']) {
			$product = MLProduct::gi()->getProductByIdOld($pID, $this->settings['language']);
		} else {
			$product = MLProduct::gi()->getProductById($pID);
		}
		return $product;
	}
	
	protected function populateSelectionWithData() {
		$this->setUpMLProduct();
		foreach ($this->selection as $pID => &$data) {
			if (!isset($data['submit']) || !is_array($data['submit'])) {
				$data['submit'] = array();
			}
			#echo print_m($this->settings, '$this->settings');
			#echo print_m($this->selection, __METHOD__.'{L:'.__LINE__.'}');
			$product = $this->getProduct($pID);
			#var_dump($product);
			
			if (!$this->checkSingleItem($pID, $product, $data) || !is_array($product)) {
				$this->badItems[] = $pID;
				unset($this->selection[$pID]);
				#echo 'o.O';
				continue;
			}

			$mpID = $this->mpID;
			$marketplace = $this->marketplace;

			/* {Hook} "CheckinSubmit_AppendData": Enables you to extend or modify the product data.<br>
			   Variables that can be used: 
			   <ul><li>$pID: The ID of the product (Table <code>products.products_id</code>).</li>
			       <li>$product: The data of the product (Tables <code>products</code>, <code>products_description</code>,
			           <code>products_images</code> and <code>products_vpe</code>).</li>
			       <li>$data: The data of the product from the preparation tables of the marketplace.</li>
			       <li>$mpID: The ID of the marketplace.</li>
			       <li>$marketplace: The name of the marketplace.</li>
			   </ul>
			   <code>$product</code> and <code>$data</code> will be used to generate the <code>AddItems</code> request.
			 */
			if (($hp = magnaContribVerify('CheckinSubmit_AppendData', 1)) !== false) {
				require($hp);
			}
			
			$this->appendAdditionalData($pID, $product, $data);

			/* {Hook} "CheckinSubmit_PostAppendData": Enables you to extend or modify the product data, after our data processing.<br>
			   Variables that can be used: same as for CheckinSubmit_AppendData.
			 */
			if (($hp = magnaContribVerify('CheckinSubmit_PostAppendData', 1)) !== false) {
				require($hp);
			}
		}
	}

	protected function requirementsMet($product, $requirements, &$failed) {
		if (!is_array($product) || empty($product) || !is_array($requirements) || empty($requirements)) {
			$failed = true;
			return false;
		}
		$failed = array();
		foreach ($requirements as $req => $needed) {
			if (!$needed) continue;
			if (empty($product[$req]) && ($product[$req] !== '0')) {
				$failed[] = $req;
			}
		}
		return empty($failed);
	}
	
	abstract protected function appendAdditionalData($pID, $product, &$data);
	abstract protected function filterSelection();

	abstract protected function generateRequestHeader();
	
	abstract protected function generateRedirectURL($state);

	protected function processException($e) {}

	protected function sendRequest($abort = false) {
		$retResponse = array ();
		
		$request = $this->generateRequestHeader();
		$request['SUBSYSTEM'] = MagnaConnector::gi()->getSubSystem();
		$request['DATA'] = array();
		
		foreach ($this->selection as $pID => &$data) {
			$request['DATA'][] = $data['submit'];
		}
		arrayEntitiesToUTF8($request['DATA']);
		
		$this->preSubmit($request);
		
		$this->lastRequest = $request;
		
		$this->ajaxReply['ignoreErrors'] = true;
		
		try {
			/* Hau raus! :D */
			if ($abort) {
				die(print_m(json_indent(json_encode($request))));
			}
			#file_put_contents(dirname(__FILE__).'/submit.log', var_dump_pre($request, '$request', true));
			$checkInResult = MagnaConnector::gi()->submitRequest($request);
			//sleep(5);
			//$checkInResult = array ('STATUS' => 'SUCCESS', 'ERRORS' => array());
			//$this->ajaxReply['result'] = $checkInResult;
			
			$this->processSubmitResult($checkInResult);
			if (!array_key_exists('state',$this->submitSession)) { 
				$this->submitSession['state'] = array();
			}
			if (!array_key_exists('success',$this->submitSession['state'])) { 
				$this->submitSession['state']['success'] = 0;
			}
			if (!array_key_exists('failed',$this->submitSession['state'])) { 
				$this->submitSession['state']['failed'] = 0;
			}
			$this->submitSession['state']['success'] += count($this->selection);
			$this->submitSession['state']['failed']  += count($this->badItems);
			
			if (isset($this->submitSession['api'])) {
				unset($this->submitSession['api']);
			}
			$retResponse = $checkInResult;

		} catch (MagnaException $e) {
			$this->submitSession['state']['failed']  += count($this->badItems) + count($this->selection);

			$this->ajaxReply['exception'] = $e->getMessage();
			$this->submitSession['api']['exception'] = $e->getErrorArray();
			
			$subsystem = $e->getSubsystem();
			if (($subsystem != 'Core') && ($subsystem != 'PHP') && ($subsystem != 'Database')) {
				$this->ajaxReply['ignoreErrors'] = $this->ignoreErrors;
			} else {
				$this->ajaxReply['ignoreErrors'] = false;
			}
			
			//$this->ajaxReply['request'] = $this->submitSession['api']['exception']['REQUEST'];
			if (is_array($this->submitSession['api']['exception']) && array_key_exists('REQUEST', $this->submitSession['api']['exception'])) {
				unset($this->submitSession['api']['exception']['REQUEST']);
			}
			$this->ajaxReply['redirect'] = toURL(array(
				'mp' => $this->realUrl['mp'],
				'mode' => $this->realUrl['mode']
			));
			$retResponse = $this->submitSession['api']['exception'];
			
			$this->processException($e);
		}
		return $retResponse;
	}
	
	protected function preSubmit(&$request) {}
	
	abstract protected function postSubmit();
	abstract protected function processSubmitResult($result);

	protected function generateCustomErrorHTML() {
		return false;
	}

	public function submit($abort = false) {
		unset($_SESSION['magna_deletedFilter']); // Reset ebay inventory infos. @see CheckinCategoryView
		$this->initSelection(0, $this->settings['itemsPerBatch']);
		$this->ajaxReply['itemsPerBatch'] = $this->settings['itemsPerBatch'];

		/* Spaetestens beim 2. Durchgang muessen die Artikel hinzugefuegt werden,
		   da sie sonst die Artikel des 1. Durchganges zuvor loeschen wuerden. */
		if ($this->submitSession['state']['submitted'] > 0) {
			$this->submitSession['mode'] = 'ADD';
		}
		
		$this->submitSession['state']['submitted'] += count($this->selection);
		
		$this->populateSelectionWithData();
		
		$this->filterSelection();
		
		/* Wenn Artikel deaktiviert wurden (nicht fehlgeschlagen, z. B. Artikelanzahl == 0), 
		   werden sie nicht mit uebermittelt */
		$this->submitSession['state']['total'] -= count($this->disabledItems);
		$this->submitSession['state']['submitted'] -= count($this->disabledItems);
		/*
		echo print_m($this->selection);
		die();
		*/
		if (!empty($this->selection)) {
			MagnaConnector::gi()->setTimeOutInSeconds(600);
			@set_time_limit(600);
			$this->sendRequest($abort || isset($_GET['abort']));
			MagnaConnector::gi()->resetTimeOut();
		} else {
			$this->submitSession['state']['failed']  += count($this->badItems);
		}
		
		if (isset($this->submitSession['selectionFromErrorLog']) && !empty($this->submitSession['selectionFromErrorLog'])) {
			$this->submitSession['selectionFromErrorLog'] = array_diff($this->submitSession['selectionFromErrorLog'], $this->badItems);
		}

		//$this->ajaxReply['debug'] = print_m($this->submitSession, 'submitSession');
		$this->ajaxReply['state'] = $this->submitSession['state'];

		if (!empty($this->submitSession['api'])) {
			$this->ajaxReply['proceed'] = $this->submitSession['proceed'] = $this->ajaxReply['ignoreErrors'];
			$this->ajaxReply['api'] = $this->submitSession['api'];
			/* Firstly let us process the exceptions. If we analyse them and decide, that some of them are not
			 * critical, they'll not apper... */
			$this->ajaxReply['api']['customhtml'] = $this->generateCustomErrorHTML();
			/* ... in the following list. */
			$this->ajaxReply['api']['html'] = MagnaError::gi()->exceptionsToHTML(false);
			
			#print_r($this->ajaxReply['api']['exception']);
		}

		if (empty($this->submitSession['api']) || $this->ajaxReply['ignoreErrors']) {
			if (!isset($this->ajaxReply['reprocessSelection']) || !$this->ajaxReply['reprocessSelection']) {
				$this->deleteSelection();
			}
			if ($this->submitSession['state']['submitted'] >= $this->submitSession['state']['total']) {
				$this->ajaxReply['proceed'] = $this->submitSession['proceed'] = false;
				/* Auswertung... */
				if ($this->submitSession['state']['success'] != $this->submitSession['state']['total']) {
					/* Irgendwelche Fehler sind aufgetreten */
					$this->ajaxReply['redirect'] = $this->generateRedirectURL('fail');
				} else {
					$this->ajaxReply['redirect'] = $this->generateRedirectURL('success');
				}
				
				if ($this->submitSession['state']['success'] > 0) {
					$this->postSubmit();
					if (isset($this->submitSession['selectionFromErrorLog'])) {
						unset($this->submitSession['selectionFromErrorLog']);
					}
				}
				$this->ajaxReply['finaldialogs'] = $this->getFinalDialogs();
			} else {
				$this->ajaxReply['proceed'] = $this->submitSession['proceed'] = true;
			}
		}

		//header('Content-Type: text/plain; charset=utf-8');

		$this->ajaxReply['timer'] = microtime2human(microtime(true) -  $this->_timer);
		$this->ajaxReply['memory'] = memory_usage();
		
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
		header('Content-type: application/json');
		return json_encode($this->ajaxReply);
	}
	
	protected function getFinalDialogs() {
		/* Example:
		return array (
			array (
				'headline' => 'Eine Ueberschrift',
				'message' => 'Der Inhalt'
			),
			...
		);
		*/
		return array();
	}
	
	public function getLastRequest() {
		return $this->lastRequest;
	}
	
	public function renderBasicHTMLStructure() {
		//$this->initSelection(0, $this->settings['itemsPerBatch']);
		//$this->populateSelectionWithData();
		
		//$html = print_m($this->selection, '$this->selection').'
		$html = '
			<div id="checkinSubmit">
				<h1 id="threeDots">
					<span id="headline">'.ML_HEADLINE_SUBMIT_PRODUCTS.'</span><span class="alldots"
						><span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>&nbsp;
					</span>
				</h1>
				<hr/>
				<p>'.ML_NOTICE_SUBMIT_PRODUCTS.'</p>
				<div id="apiException" style="display:none;"><p class="errorBox">'.ML_ERROR_SUBMIT_PRODUCTS.'</p></div>
				<div id="uploadprogress" class="progressBarContainer">
					<div class="progressBar"></div>
					<div class="progressPercent"></div>
				</div>
				<br>
				<div id="checkinSubmitStatus" class="paddingBottom"></div>
				<div style="display: none; text-align: left; background: rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.2); border-radius: 3px 3px 3px 3px; margin-bottom: 1em; padding: 7px; font: 11px/1em monospace; white-space: pre;" id="checkinSubmitDebug">'.print_m($this->submitSession, 'submitSession').'</div>
			</div>
		';

		ob_start();?>
<script type="text/javascript" src="js/classes/CheckinSubmit.js"></script>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	var csaj = new GenericCheckinSubmitAjaxController();
	csaj.setTriggerURL('<?php echo toURL($this->realUrl, array('kind' => 'ajax'), true); ?>');
	csaj.addLocalizedMessages({
		'TitleInformation' : <?php echo json_encode(ML_LABEL_INFORMATION); ?>,
		'TitleAjaxError': 'Ajax '+<?php echo json_encode(ML_ERROR_LABEL); ?>,
		'LabelStatus': <?php echo json_encode(ML_GENERIC_STATUS); ?>,
		'LabelError': <?php echo json_encode(ML_ERROR_LABEL); ?>,
		'MessageUploadFinal': <?php echo json_encode(ML_STATUS_SUBMIT_PRODUCTS_SUMMARY.$this->summaryAddText); ?>,
		'MessageUploadStatus': <?php echo json_encode(ML_STATUS_SUBMIT_PRODUCTS); ?>,
		'MessageUploadFatalError': <?php echo json_encode(ML_STATUS_SUBMIT_PHP_ERROR); ?> 
	});
	csaj.setInitialUploadStatus('<?php echo $this->submitSession['state']['total']; ?>');
	csaj.doAbort(<?php echo isset($_GET['abort']) ? 'true' : 'false'; ?>);
	csaj.runSubmitBatch();
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();	
		ob_end_clean();
		return $html;
	}
}
