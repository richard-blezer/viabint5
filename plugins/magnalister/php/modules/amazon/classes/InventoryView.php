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
 * $Id: InventoryView.php 453 2014-07-24 22:20:22Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
require_once (DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');

class InventoryView {
	private $settings = array();
	private $sort = array();

	private $latestChange = 0;
	private $latestReport = 0;
	private $numberofitems = 0;
	private $offset = 0;

	private $add = array();
	private $updatedelete = array();
	private $getPendingItemsCalled = false;
	private $renderableData = array();

	private $simpleprice = null;
	private $url = array();
	private $magnaSession = array();

	private $inventoryPurged = false;

	private $search = '';

	private $batchesProcessed = 0;

	public function __construct($settings = array()) {
		global $_MagnaShopSession, $_MagnaSession, $_url;
		
		$this->settings = array_merge(array(
			'maxTitleChars'	=> 35,
			'itemLimit'		=> 50,
		), $settings);

		$this->magnaSession = &$_MagnaSession;
		
		$this->simpleprice = new SimplePrice();
		$this->simpleprice->setCurrency(getCurrencyFromMarketplace($this->magnaSession['mpID']));
		$this->url = $_url;
		$this->url['view'] = 'inventory';

		if (array_key_exists('tfSearch', $_POST) && !empty($_POST['tfSearch'])) {
			$this->search = $_POST['tfSearch'];
		} else if (array_key_exists('search', $_GET) && !empty($_GET['search'])) {
			$this->search = $_GET['search'];
		}
		initArrayIfNecessary($_MagnaShopSession, array($this->magnaSession['mpID'], 'InventoryView', 'Add'));
		$this->add = &$_MagnaShopSession[$this->magnaSession['mpID']]['InventoryView']['Add'];
		#$this->add = array();
		initArrayIfNecessary($_MagnaShopSession, array($this->magnaSession['mpID'], 'InventoryView', 'UpdateDelete'));
		$this->updatedelete = &$_MagnaShopSession[$this->magnaSession['mpID']]['InventoryView']['UpdateDelete'];
		#$this->updatedelete = array();
		if (!array_key_exists('LatestReport', $_MagnaShopSession[$this->magnaSession['mpID']]['InventoryView'])) {
			$_MagnaShopSession[$this->magnaSession['mpID']]['InventoryView']['LatestReport'] = 0;
		}
		$this->latestReport = &$_MagnaShopSession[$this->magnaSession['mpID']]['InventoryView']['LatestReport'];
	}

	private function getInventory() {
		try {
			$request = array(
				'ACTION' => 'GetInventory',
				'LIMIT' => $this->settings['itemLimit'],
				'OFFSET' => $this->offset,
				'ORDERBY' => $this->sort['order'],
				'SORTORDER' => $this->sort['type']
			);
			if (!empty($this->search)) {
				$request['SEARCH'] = $this->search;
			}
			#echo print_m($request);
			$result = MagnaConnector::gi()->submitRequest($request);
			if ($result['LATESTCHANGE']) {
				$this->latestChange = strtotime($result['LATESTCHANGE']);
			}
			if ($result['LATESTREPORT']) {
				$latestReport = strtotime($result['LATESTREPORT']);
				if ($this->latestReport != $latestReport) {
					$this->getPendingItems();
				}
				$this->latestReport = $latestReport;
			}
			$this->numberofitems = (int)$result['NUMBEROFLISTINGS'];
			return $result;

		} catch (MagnaException $e) {
			$this->latestChange = 0;
			return array();
		}
	}

	private function getPendingItems() {
		//*
		if ($this->getPendingItemsCalled) {
			return;
		}
		//*/
		$this->getPendingItemsCalled = true;

		/* Gibt es neue Listings? */
		$this->add = array();
		$this->updatedelete = array();

		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetPendingItems',
			));
		} catch (MagnaException $e) {
			$result = array('DATA' => false);
		}
		#echo print_m($result);
		if (is_array($result['DATA']) && !empty($result['DATA'])) {
			foreach ($result['DATA'] as $item) {
				/* Get some more informations */
				if (($item['Mode'] == 'ADD') || ($item['Mode'] == 'PURGE')) {
					$pID = magnaSKU2pID($item['SKU']);
					$item['ShopItemName'] = MagnaDB::gi()->fetchOne('
						SELECT products_name 
						  FROM '.TABLE_PRODUCTS_DESCRIPTION.'
						 WHERE products_id=\''.$pID.'\'
						       AND language_code = \''.$_SESSION['magna']['selected_language'].'\'
					');
					unset($item['BatchID']);
					$this->add[$item['SKU']] = $item;
				} else {
					unset($item['BatchID']);
					$this->updatedelete[$item['SKU']] = $item;
				}
			}
		}
		#echo print_m('Reloaded Pending Items');
	}

	private function initInventoryView() {
		/* Listings beenden */
		if (isset($_POST['skus']) && is_array($_POST['skus']) && isset($_POST['action'])
			 && ($_SESSION['posttime'] != $_POST['timestamp']) // Re-Post Prevention
		) {
			$_SESSION['posttime'] = $_POST['timestamp'];
			switch ($_POST['action']) {
				case 'delete': {
					$skus = $_POST['skus'];
					$data = array();
					foreach ($skus as $sku) {
						$data[] = array (
							'SKU' => $sku,
						);
					}
					//*
					try {
						$result = MagnaConnector::gi()->submitRequest(array(
							'ACTION' => 'DeleteItems',
							'DATA' => $data,
							'UPLOAD' => true,
						));
						#echo print_m($result);
					} catch (MagnaException $e) { }
					//*/
					break;
				}
			}
		}

		if (isset($_GET['sorting'])) {
			$sorting = $_GET['sorting'];
		} else {
			$sorting = 'blabla'; // fallback for default
		}

		switch ($sorting) {
	        case 'sku':
	            $this->sort['order'] = 'SKU';
	            $this->sort['type']  = 'ASC';
	            break;
	        case 'sku-desc':
	            $this->sort['order'] = 'SKU';
	            $this->sort['type']  = 'DESC';
	            break;
	        case 'itemtitle':
	            $this->sort['order'] = 'ItemTitle';
	            $this->sort['type']  = 'ASC';
	            break;
	        case 'itemtitle-desc':
	            $this->sort['order'] = 'ItemTitle';
	            $this->sort['type']  = 'DESC';
	            break;
	        case 'asin':
	            $this->sort['order'] = 'ASIN';
	            $this->sort['type']  = 'ASC';
	            break;
	        case 'asin-desc':
	            $this->sort['order'] = 'ASIN';
	            $this->sort['type']  = 'DESC';
	            break;
	        case 'aprice':
	            $this->sort['order'] = 'Price';
	            $this->sort['type']  = 'ASC';
	            break;
	        case 'aprice-desc':
	            $this->sort['order'] = 'Price';
	            $this->sort['type']  = 'DESC';
	            break;
	        case 'quantity':
	            $this->sort['order'] = 'Quantity';
	            $this->sort['type']  = 'ASC';
	            break;
	        case 'quantity-desc':
	            $this->sort['order'] = 'Quantity';
	            $this->sort['type']  = 'DESC';
	            break;
	        case 'dateadded-desc':
	            $this->sort['order'] = 'DateAdded';
	            $this->sort['type']  = 'DESC';
	            break;
			case 'dateadded':
	        default:
	            $this->sort['order'] = 'DateAdded';
	            $this->sort['type']  = 'DESC';
	            break;
	    }

		if (isset($_GET['page']) && ctype_digit($_GET['page'])) {
			$this->offset = ($_GET['page'] - 1) * $this->settings['itemLimit'];
		} else {
			$this->offset = 0;
		}

	}

	private function sortByType($type) {
		$tmpURL = $this->url;
		if (!empty($this->search)) {
			$tmpURL['search'] = urlencode($this->search);
		}
		return '
			<span class="nowrap">
				<a href="'.toURL($tmpURL, array('sorting' => $type.'')).'" title="'.ML_LABEL_SORT_ASCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_ASCENDING.'" src="'.DIR_MAGNALISTER_IMAGES.'sort_up.png" />
				</a>
				<a href="'.toURL($tmpURL, array('sorting' => $type.'-desc')).'" title="'.ML_LABEL_SORT_DESCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_DESCENDING.'" src="'.DIR_MAGNALISTER_IMAGES.'sort_down.png" />
				</a>
			</span>';
	}

	private function prepareInventoryData() {
		$result = $this->getInventory();
		if (empty($this->add) && empty($this->updatedelete)) {
			$this->getPendingItems();
		}
		/*
		echo print_m(array(
			'add' => $this->add,
			'updel' => $this->updatedelete
		));
		//*/
		$this->renderableData = array();
		if (!empty($this->add)) {
			foreach ($this->add as $item) {
				if ($item['Mode'] == 'PURGE') {
					$result['DATA'] = array();
				}
				$item = array_merge(array(
					'pID' => magnaSKU2pID($item['SKU']),
					'ItemTitle' => '',
					'Type' => 'add',
				), $item);
				$item['DateAdded'] = strtotime($item['DateAdded']);
				$this->renderableData[] = $item;
			}
		}
		if (array_key_exists('DATA', $result) && !empty($result['DATA'])) {
			foreach ($result['DATA'] as $item) {
				if (array_key_exists($item['SKU'], $this->add)) continue;
				unset($item['ConditionType']);
				unset($item['ConditionNote']);
				unset($item['Description']);
				$item['Type'] = 'regular';
				$item['pID'] = magnaAmazonSKU2pID($item['SKU'], $item['ASIN']);

				
				if ($item['pID'] > 0) {
					$item['ShopItemName'] = (string)MagnaDB::gi()->fetchOne('
						SELECT products_name 
						  FROM '.TABLE_PRODUCTS_DESCRIPTION.'
						 WHERE products_id=\''.$item['pID'].'\'
						       AND language_code = \''.$_SESSION['magna']['selected_language'].'\'
					');
					if (is_array($variationTheme) && !empty($variationTheme['VariationTitle']) && !empty($variationTheme['VariationValue'])) {
						$item['ShopItemName'] .= ' '.$variationTheme['VariationTitle'].': '.$variationTheme['VariationValue'];
					}
					$item['Type'] = 'inventory';
				} else {
					$item['ShopItemName'] = '';
				}
				if (array_key_exists($item['SKU'], $this->updatedelete)) {
					$tItem = $this->updatedelete[$item['SKU']];
					if (!empty($tItem['Price'])) {
						$item['Price'] = $tItem['Price'];
					}
					if (!empty($tItem['Quantity'])) {
						$item['Quantity'] = $tItem['Quantity'];
					}
					$item['Type'] = strtolower($tItem['Mode']);
				}
				$item['DateAdded'] = strtotime($item['DateAdded']);
				$this->renderableData[] = $item;
			}
		}
	}

	private function renderDataGrid($id = '') {
		$html = '
			<table'.(($id != '') ? ' id="'.$id.'"' : '').' class="datagrid">
				<thead><tr>
					<td class="nowrap"><input type="checkbox" id="selectAll"/><label for="selectAll">'.ML_LABEL_CHOICE.'</label></td>
					<td>'.'SKU'.' '.$this->sortByType('sku').'</td>
					<td>'.ML_LABEL_SHOP_TITLE.'</td>
					<td>'.ML_AMAZON_LABEL_TITLE.' '.$this->sortByType('itemtitle').'</td>
					<td>ASIN '.$this->sortByType('asin').'</td>
					<td>'.ML_AMAZON_LABEL_AMAZON_PRICE.' '.$this->sortByType('aprice').'</td>
					<td>'.ML_LABEL_QUANTITY.' '.$this->sortByType('quantity').'</td>
					<td>'.ML_GENERIC_CHECKINDATE.' '.$this->sortByType('dateadded').'</td>
					<td>'.ML_GENERIC_STATUS.'</td>
				</tr></thead>
				<tbody>
		';
		$oddEven = false;
		#echo print_m($this->renderableData);
		foreach ($this->renderableData as $item) {
			if (!empty($item['ShopItemName'])) {
				$item['ShopItemNameShort'] = (
					(strlen($item['ShopItemName']) > $this->settings['maxTitleChars'] + 2) 
						? 
							(fixHTMLUTF8Entities(substr($item['ShopItemName'], 0, $this->settings['maxTitleChars']), ENT_COMPAT).'&hellip;')
						: 
							fixHTMLUTF8Entities($item['ShopItemName'], ENT_COMPAT)
				);
				$item['ShopItemName'] = fixHTMLUTF8Entities($item['ShopItemName'], ENT_COMPAT);
			} else {
				$item['ShopItemNameShort'] = $item['ShopItemNameShort'] = '&mdash;';
			}

			if (!empty($item['ItemTitle'])) {
				$item['ItemTitleShort'] = (
					(strlen($item['ItemTitle']) > $this->settings['maxTitleChars'] + 2) 
						? 
							(fixHTMLUTF8Entities(substr($item['ItemTitle'], 0, $this->settings['maxTitleChars']), ENT_COMPAT).'&hellip;')
						: 
							fixHTMLUTF8Entities($item['ItemTitle'], ENT_COMPAT)
				);
				$item['ItemTitle'] = fixHTMLUTF8Entities($item['ItemTitle'], ENT_COMPAT);
			} else {
				$item['ItemTitleShort'] = '<span class="italic grey">'.ML_LABEL_IN_QUEUE.'</span>';
				$item['ItemTitle'] = ML_LABEL_IN_QUEUE;
			}
			
			$item['SKU_Rendered'] = $item['SKU'];
			if ($item['Type'] == 'inventory') {
				// not supported in xtc4
				//$item['SKU_Rendered'] = '<a href="categories.php?pID='.$item['pID'].'&action=new_product" target="_blank" title="'.ML_LABEL_EDIT.'">'.$item['SKU'].'</a>';
			}

			$html .= '
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').' '.$item['Type'].'">
					<td><input type="checkbox" name="skus[]" value="'.$item['SKU'].'" '.((in_array($item['Type'], array(
						'add', 'delete', 'sysdelete'
					))) ? 'disabled="disabled"' : '').'/></td>
					<td>'.$item['SKU_Rendered'].'</td>
					<td title="'.$item['ShopItemName'].'">'.str_replace(' ', '&nbsp;', $item['ShopItemNameShort']).'</td>
					<td title="'.$item['ItemTitle'].'">'.str_replace(' ', '&nbsp;', $item['ItemTitleShort']).'</td>
					<td>'.(empty($item['ASIN']) 
						? '&mdash;' 
						: '<a href="http://www.amazon.de/gp/offer-listing/'.$item['ASIN'].'" '.
					      'title="'.ML_AMAZON_LABEL_PRODUCT_IN_AMAZON.'" '.
					      'target="_blank">'.$item['ASIN'].'</a>').
					'</td>
					<td>'.$this->simpleprice->setPrice($item['Price'])->format().'</td>
					<td>'.(($item['Quantity'] > 0) ? $item['Quantity'] : ML_LABEL_SOLD_OUT).'</td>
					<td>'.date("d.m.Y", $item['DateAdded']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['DateAdded']).'</span>'.'</td>';

			switch ($item['Type']) {
				case 'add': {
					$html .= '
						<td title="'.ML_AMAZON_LABEL_ADD_WAIT.'"><img src="'.DIR_MAGNALISTER_IMAGES.'status/grey_dot.png" alt="'.ML_AMAZON_LABEL_ADD_WAIT.'"/></td>';
					break;
				}
				case 'update': {
					$html .= '
						<td title="'.ML_AMAZON_LABEL_EDIT_WAIT.'"><img src="'.DIR_MAGNALISTER_IMAGES.'status/blue_dot.png" alt="'.ML_AMAZON_LABEL_EDIT_WAIT.'"/></td>';
					break;					
				}
				case 'delete':
				case 'sysdelete': {
					$html .= '
						<td title="'.ML_AMAZON_LABEL_DELETE_WAIT.'"><img src="'.DIR_MAGNALISTER_IMAGES.'status/red_dot.png" alt="'.ML_AMAZON_LABEL_DELETE_WAIT.'"/></td>';					
					break;
				}
				default: {
					$html .= '
						<td title="'.ML_AMAZON_LABEL_IN_INVENTORY.'"><img src="'.DIR_MAGNALISTER_IMAGES.'status/green_dot.png" alt="'.ML_AMAZON_LABEL_IN_INVENTORY.'"/></td>';
				}
			}
			$html .= '	
				</tr>';
		}
		$html .= '
				</tbody>
			</table>';

		return $html;
	}

	private function renderInventoryTable() {
		$html = '';

		if (empty($this->renderableData)) {
			$this->prepareInventoryData();
		}

		$html .= '
			<table class="magnaframe">
				<thead><tr><th>'.ML_LABEL_NOTE.'</th></tr></thead>
				<tbody><tr><td class="fullWidth">
					<table><tbody>
						<!--<tr><td>'.ML_AMAZON_LABEL_LAST_INVENTORY_CHANGE.':</td>
							<td>'.(($this->latestChange > 0) ? date("d.m.Y &\b\u\l\l; H:i:s", $this->latestChange) : ML_LABEL_UNKNOWN).'</td></tr>-->
						<tr><td>'.ML_AMAZON_LABEL_LAST_REPORT.'
								<div id="amazonInfo" class="desc"><span>
									'.ML_AMAZON_TEXT_CHECKIN_DELAY.'
								</span></div>:
							</td>
							<td>'.(($this->latestReport > 0) ? date("d.m.Y &\b\u\l\l; H:i:s", $this->latestReport) : ML_LABEL_UNKNOWN).'</td></tr>
						</tbody></table>
				</td></tr></tbody>
			</table>
			<div id="infodiag" class="dialog2" title="'.ML_LABEL_NOTE.'"></div>
		    <script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#amazonInfo\').click(function () {
						$(\'#infodiag\').html($(\'#amazonInfo span\').html()).jDialog();
					});
				});
			/*]]>*/</script>';

		if (isset($_POST['reload'])) {
			$html .= '
			<div id="reloaddiag" class="dialog2" title="'.ML_LABEL_NOTE.'">'.ML_AMAZON_TEXT_REFRESH_REQUEST_SEND.'</div>
		    <script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#reloaddiag\').jDialog();
				});
			/*]]>*/</script>
			';
		}
		if (isset($_POST['refreshStock'])) {
			@set_time_limit(60 * 10);
			require_once (DIR_MAGNALISTER_MODULES.'amazon/crons/AmazonSyncInventory.php');
			$asi = new AmazonSyncInventory($this->magnaSession['mpID'], 'amazon');
			$asi->process();
		}

		$pages = ceil($this->numberofitems / $this->settings['itemLimit']);
		$bla = '';
		$tmpURL = $this->url;
		if (isset($_GET['sorting'])) {
			$tmpURL['sorting'] = $_GET['sorting'];
		}
		if (!empty($this->search)) {
			$tmpURL['search'] = urlencode($this->search);
		}
		$currentPage = 1;
		if (isset($_GET['page']) && ctype_digit($_GET['page']) && (1 <= (int)$_GET['page']) && ((int)$_GET['page'] <= $pages)) {
			$currentPage = (int)$_GET['page'];
		}

		$offset = $currentPage * $this->settings['itemLimit'] - $this->settings['itemLimit'] + 1;
		$limit = $offset + count($this->renderableData) - 1;
		$html .= '<table class="listingInfo"><tbody><tr>
					<td class="ml-pagination">
						'.(($this->numberofitems > 0)
							?	('<span class="bold">'.ML_LABEL_PRODUCTS.':&nbsp; '.
								 $offset.' bis '.$limit.' von '.($this->numberofitems).'&nbsp;&nbsp;&nbsp;&nbsp;</span>'
								)
							:	''
						).'
						<span class="bold">'.ML_LABEL_CURRENT_PAGE.':&nbsp; '.$currentPage.'</span>
					</td>
					<td class="textright">
						'.renderPagination($currentPage, $pages, $tmpURL).'
					</td>
				</tr></tbody></table>';

		if (!empty($this->renderableData)) {
			$html .= $this->renderDataGrid('inventory');
		} else {
			$html .= '<table class="magnaframe"><tbody><tr><td>'.
						(empty($this->search) ? ML_AMAZON_LABEL_NO_INVENTORY : ML_LABEL_NO_SEARCH_RESULTS).
					 '</td></tr></tbody></table>';
		}

		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#selectAll').click(function() {
		state = $(this).attr('checked');
		$('#inventory input[type="checkbox"]:not([disabled])').each(function() {
			$(this).attr('checked', state);
		});
	});
	$('table.datagrid tbody tr').click(function() {
		cb = $('input[type="checkbox"]:not(:disabled)', $(this));
		if (cb.length != 1) return;
		if (cb.is(':checked')) {
			cb.removeAttr('checked');
		} else {
			cb.attr('checked', 'checked');
		}
	});
	$('table.datagrid tbody tr td input[type="checkbox"]').click(function () {
		this.checked = !this.checked;
	});
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();	
		ob_end_clean();
		
		return $html;
	}

	private function renderActionBox() {
		global $_modules;

		$left = '<input type="button" class="ml-button" value="'.ML_BUTTON_LABEL_DELETE.'" id="listingDelete" name="listing[delete]"/>';
		$right = '<table class="right"><tbody>
			<tr><td><input type="submit" class="ml-button fullWidth smallmargin" name="reload" value="'.ML_BUTTON_RELOAD_INVENTORY.'"/></td></tr>
			'.(in_array(getDBConfigValue('amazon.stocksync.tomarketplace', $this->magnaSession['mpID']), array('abs', 'auto'))
				? '<tr><td><input type="submit" class="ml-button fullWidth smallmargin" name="refreshStock" value="'.ML_BUTTON_REFRESH_STOCK.'"/></td></tr>'
				: ''
			).'
		</tbody></table>';
			
		ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#listingDelete').click(function() {
		if (($('#inventory input[type="checkbox"]:checked').length > 0) &&
			confirm(unescape(<?php echo "'".html2url(sprintf(ML_GENERIC_DELETE_LISTINGS, $_modules[$this->magnaSession['currentPlatform']]['title']))."'"; ?>))
		) {
			$('#action').val('delete');
			$(this).parents('form').submit();
		}
	});
});
/*]]>*/</script>
<?php // Durch aufrufen der Seite wird automatisch ein Aktualisierungsauftrag gestartet
		$js = ob_get_contents();	
		ob_end_clean();

		return '
			<input type="hidden" id="action" name="action" value="">
			<input type="hidden" name="timestamp" value="'.time().'">
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody><tr><td>
					<table><tbody><tr>
						<td class="firstChild">'.$left.'</td>
						<td><label for="tfSearch">'.ML_LABEL_SEARCH.':</label>
							<input id="tfSearch" name="tfSearch" type="text" value="'.fixHTMLUTF8Entities($this->search, ENT_COMPAT).'"/>
							<input type="submit" class="ml-button" value="'.ML_BUTTON_LABEL_GO.'" name="search_go" /></td>
						<td class="lastChild">'.$right.'</td>
					</tr></tbody></table>
				</td></tr></tbody>
			</table>
			'.$js;
	}

	public function renderView() {
		$html = '<form action="'.toUrl($this->url).'" id="amazonInventoryView" method="post">';
		$this->initInventoryView();
		$html .= $this->renderInventoryTable();
		return $html.$this->renderActionBox().'
			</form>
			<script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#amazonInventoryView\').submit(function () {
						jQuery.blockUI(blockUILoading);
					});
				});
			/*]]>*/</script>';
	}
}
