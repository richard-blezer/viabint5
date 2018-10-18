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
 * $Id: AmazonCategoryView.php 510 2014-10-08 12:41:34Z miguel.heredia $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleCategoryView.php');

class AmazonCategoryView extends SimpleCategoryView {
	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '', $productIDs = array()) {
		parent::__construct($cPath, $settings, $sorting, $search, $productIDs);
		//$this->action = array('action' => 'matching');

		if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->_magnasession['mpID']));
		}
	}

	public function getAdditionalHeadlines() {
		return '
			<td class="lowestprice">'.ML_GENERIC_LOWEST_PRICE.'</td>
			<td class="matched">'.ML_AMAZON_LABEL_MATCHED.'</td>';
	}

	public function getAdditionalCategoryInfo($cID, $data = false) {
		$html = '
			<td>&mdash;</td>';
			
		$pIDs = $this->list['categories'][$cID]['allproductsids'];
		if (!empty($pIDs)) {
			$totalItems = count($pIDs);
			$itemsFailed = 0;
			$itemsMatched = 0;
			//echo var_dump_pre(getDBConfigValue('general.keytype'));
			#(getDBConfigValue('general.keytype', 'pID') == 'artNr')

			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$query = '
					SELECT p.products_id, `asin`
					  FROM '.TABLE_PRODUCTS.' p, '.TABLE_MAGNA_AMAZON_PROPERTIES.' pa
					 WHERE p.products_id IN (\''.implode('\', \'', $pIDs).'\')
					       AND pa.mpID=\''.$this->_magnasession['mpID'].'\'
					       AND p.products_model=pa.products_model
					       AND p.products_model<>\'\'
				';
			} else {
				$query = '
					SELECT products_id, `asin`
					  FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.'
					 WHERE products_id IN (\''.implode('\', \'', $pIDs).'\')
					       AND mpID=\''.$this->_magnasession['mpID'].'\'
				';
			}
				
			$matched = MagnaDB::gi()->fetchArray($query);

			foreach ($matched as $item) {
				if (empty($item['asin'])) {
					++$itemsFailed;
				} else {
					++$itemsMatched;
				}
			}
		} else {
			$totalItems = 0;
		}
		if (($itemsFailed == 0) && ($itemsMatched == 0)) { /* Nichts gematched und auch kein matching probiert */
			return $html.'
				<td title="'.ML_AMAZON_CATEGORY_MATCHED_NONE.'">'.
					html_image(DIR_MAGNALISTER_IMAGES . 'status/grey_dot.png', ML_AMAZON_CATEGORY_MATCHED_NONE, 9, 9).
				'</td>';
		}
		if ($itemsFailed == $totalItems) { /* Keine gematched */
			return $html.'
				<td title="'.ML_AMAZON_CATEGORY_MATCHED_FALUTY.'">'.
					html_image(DIR_MAGNALISTER_IMAGES . 'status/red_dot.png', ML_AMAZON_CATEGORY_MATCHED_FALUTY, 9, 9).
				'</td>';
		}
		if ($itemsMatched == $totalItems) {  /* Alle Items in Category gematched */
			return $html.'
				<td title="'.ML_AMAZON_CATEGORY_MATCHED_ALL.'">'.
					html_image(DIR_MAGNALISTER_IMAGES . 'status/green_dot.png', ML_AMAZON_CATEGORY_MATCHED_ALL, 9, 9).
				'</td>';
		}
		if (($itemsFailed > 0) || ($itemsMatched > 0)) { /* Einige nicht erfolgreich gematched */
			return $html.'
				<td title="'.ML_AMAZON_CATEGORY_MATCHED_INCOMPLETE.'">'.
					html_image(DIR_MAGNALISTER_IMAGES . 'status/yellow_dot.png', ML_AMAZON_CATEGORY_MATCHED_INCOMPLETE, 9, 9).
				'</td>';
		}

		return $html.'
			<td title="'.ML_ERROR_UNKNOWN.' $totalItems:'.$totalItems.' $itemsMatched:'.$itemsMatched.' $itemsFailed:'.$itemsFailed.'">'.
				html_image(DIR_MAGNALISTER_IMAGES . 'status/red_dot.png', ML_ERROR_UNKNOWN, 9, 9).
				html_image(DIR_MAGNALISTER_IMAGES . 'status/red_dot.png', ML_ERROR_UNKNOWN, 9, 9).
			'</td>';
	}

	public function getAdditionalProductInfo($pID, $data = false) {
		$a = MagnaDB::gi()->fetchRow('
			SELECT products_id, `asin`, `lowestprice` 
			  FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.'
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model=\''.$data['products_model'].'\''
						: 'products_id=\''.$pID.'\''
					).'
					AND mpID=\''.$this->_magnasession['mpID'].'\'
		');
		if (empty($a)) {
			return '
				<td>&mdash;</td>
				<td>'.html_image(DIR_MAGNALISTER_IMAGES . 'status/grey_dot.png', ML_AMAZON_PRODUCT_MATHCED_NO, 9, 9).'</td>';
		}
		if (empty($a['asin'])) {
			return '
				<td>&mdash;</td>
				<td>'.html_image(DIR_MAGNALISTER_IMAGES . 'status/red_dot.png', ML_AMAZON_PRODUCT_MATCHED_FAULTY, 9, 9).'</td>';
		}
		return '
			<td>'.((!empty($a['lowestprice']) && ($a['lowestprice'] > 0)) ?  $this->simplePrice->setPrice($a['lowestprice'])->format().'<br />&nbsp;' : '&mdash;').'</td>
			<td>'.html_image(DIR_MAGNALISTER_IMAGES . 'status/green_dot.png', ML_AMAZON_PRODUCT_MATCHED_OK, 9, 9).'</td>';
	}
	
	public function getFunctionButtons() {
		global $_url;
		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
var selectedItems = 0;
var progressInterval = null;
var percent = 0.0;

var _demo_sub = 0;
function updateProgressDemo() {
	_demo_sub -= 300;
	if (_demo_sub <= 0) {
		_demo_sub = 0;
		window.clearInterval(progressInterval);
		jQuery.unblockUI();
	}
	percent = 100 - ((_demo_sub / selectedItems) * 100);
	myConsole.log('Progress: '+_demo_sub+'/'+selectedItems+' ('+percent+'%)');	
	$('div#progressBarContainer div#progressPercent').html(Math.round(percent)+'%');
	$('div#progressBarContainer div#progressBar').css({'width' : percent+'%'});
}

function demoProgress() {
	jQuery.blockUI(blockUIProgress);
	selectedItems = _demo_sub = 4635;
	progressInterval = window.setInterval("updateProgressDemo()", 500);
}

function updateProgress() {
	jQuery.ajax({
		type: 'get',
		async: false,
		url: '<?php echo toURL($this->url, array('kind' => 'ajax', 'automatching' => 'getProgress'), true); ?>',
		success: function(data) {
			if (!is_object(data)) {
				//selectedItems = 0;
				return;
			}
			percent = 100 - ((data.x / selectedItems) * 100);
			myConsole.log('Progress: '+data.x+'/'+selectedItems+' ('+percent+'%)');
			$('div#progressBarContainer div#progressPercent').html(Math.round(percent)+'%');
			$('div#progressBarContainer div#progressBar').css({'width' : percent+'%'});
		},
		dataType: 'json'
	});
}
function runAutoMatching(matchSetting) {
	jQuery.blockUI(blockUIProgress);
	progressInterval = window.setInterval("updateProgress()", 500);
	jQuery.ajax({
		type: 'post',
		url: '<?php echo toURL($this->url, array('kind' => 'ajax', 'automatching' => 'start'), true); ?>',
		data: {
			'match': matchSetting
		},
		success: function(data) {
			window.clearInterval(progressInterval);
			jQuery.unblockUI();
			myConsole.log(data);
			$('#finalInfo').html(data).jDialog({
				buttons: {
					'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
						window.location.href = '<?php echo toURL($this->url, true); ?>';
					}
				}
			});
		},
		dataType: 'html'
	});
}

function handleAutomatching(matchSetting) {
	jQuery.ajax({
		type: 'get',
		async: false,
		url: '<?php echo toURL($this->url, array('kind' => 'ajax', 'automatching' => 'getProgress'), true); ?>',
		success: function(data) {
			if (!is_object(data)) {
				selectedItems = 0;
				return;
			}
			selectedItems = data.x;
		},
		dataType: 'json'
	});	
	myConsole.log(selectedItems);
	jQuery.unblockUI();

	if (selectedItems <= 0) {
		$('#noItemsInfo').jDialog();
	} else {
		$('#confirmDiag').jDialog({
			buttons: {
				'<?php echo ML_BUTTON_LABEL_ABORT; ?>': function() {
					$(this).dialog('close');
				},
				'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
					$(this).dialog('close');
					runAutoMatching(matchSetting);
				}
			}
		});
	}
}

$(document).ready(function() {
	$('#desc_man_match').click(function() {
		$('#manMatchInfo').jDialog();
	});
	$('#desc_auto_match').click(function() {
		$('#autoMatchInfo').jDialog();
	});
	$('#automatching').click(function() {
		//jQuery.blockUI(jQuery.extend(blockUILoading, {onBlock: handleAutomatching()}));
		var blockUILoading2 = jQuery.extend({}, blockUILoading);
		jQuery.blockUI(jQuery.extend(blockUILoading2, {onBlock: function() {
			handleAutomatching($('#match_settings input[type="radio"]:checked').val());
		}}));
		
	});
});
/*]]>*/</script>
<?php
		$js = ob_get_contents();
		ob_end_clean();

		$mmatch = getDBConfigValue(array('amazon.multimatching', 'rematch'), $this->_magnasession['mpID']);

		return '
			<input type="hidden" value="'.$this->settings['selectionName'].'" name="selectionName"/>
			<input type="hidden" value="_" id="actionType"/>
			<table class="right"><tbody>
				<tr>
					<td id="match_settings" rowspan="2" class="textleft inputCell">
						<input id="match_all_rb" type="radio" name="match" value="all" '.($mmatch ? 'checked="checked"' : '').'/>
						<label for="match_all_rb">'.ML_LABEL_ALL.'</label><br />
						<input id="match_notmatched_rb" type="radio" name="match" value="notmatched" '.(!$mmatch ? 'checked="checked"' : '').'/>
						<label for="match_notmatched_rb">'.ML_AMAZON_LABEL_ONLY_NOT_MATCHED.'</label>
					</td>
					<td class="texcenter inputCell">
						<input type="submit" class="fullWidth ml-button smallmargin mlbtn-action" value="'.ML_AMAZON_LABEL_MANUAL_MATCHING.'" id="matching" name="matching"/>
					</td>
					<td>
						<div class="desc" id="desc_man_match" title="'.ML_LABEL_INFOS.'"><span>'.ML_AMAZON_LABEL_MANUAL_MATCHING.'</span></div>
					</td>
				</tr>
				<tr>
					<td class="texcenter inputCell">
						<input type="button" class="fullWidth ml-button smallmargin mlbtn-action" value="'.ML_AMAZON_LABEL_AUTOMATIC_MATCHING.'" id="automatching" name="automatching"/>
					</td>
					<td>
						<div class="desc" id="desc_auto_match" title="'.ML_LABEL_INFOS.'"><span>'.ML_AMAZON_LABEL_AUTOMATIC_MATCHING.'</span></div>
					</td>
				</tr>
			</tbody></table>
			<div id="finalInfo" class="dialog2" title="'.ML_LABEL_INFORMATION.'"></div>
			<div id="noItemsInfo" class="dialog2" title="'.ML_LABEL_NOTE.'">'.ML_AMAZON_TEXT_MATCHING_NO_ITEMS_SELECTED.'</div>
			<div id="manMatchInfo" class="dialog2" title="'.ML_LABEL_INFORMATION.' '.ML_AMAZON_LABEL_MANUAL_MATCHING.'">'.ML_AMAZON_TEXT_MANUALLY_MATCHING_DESC.'</div>
			<div id="autoMatchInfo" class="dialog2" title="'.ML_LABEL_INFORMATION.' '.ML_AMAZON_LABEL_AUTOMATIC_MATCHING.'">'.ML_AMAZON_TEXT_AUTOMATIC_MATCHING_DESC.'</div>
			<div id="confirmDiag" class="dialog2" title="'.ML_LABEL_NOTE.'">'.ML_AMAZON_TEXT_AUTOMATIC_MATCHING_CONFIRM.'</div>
		'.$js;

	}

	public function getLeftButtons() {
		return '<input type="submit" class="ml-button" value="'.ML_AMAZON_BUTTON_MATCHING_DELETE.'" id="unmatching" name="unmatching"/>';
	}
	
}
