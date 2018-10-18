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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/listings/MagnaCompatibleInventoryView.php');

class RicardoInventoryView extends MagnaCompatibleInventoryView {
	public function __construct() {
		parent::__construct();
	}
	
	public function renderActionBox() {
		global $_modules;

		$js = '';
		$left = (!empty($this->renderableData) ? 
			'<input type="button" class="ml-button" value="'.ML_BUTTON_LABEL_DELETE.'" id="listingDelete" name="listing[delete]"/>' : 
			''
		);
		$right = '<table class="right"><tbody>
			'.(in_array(getDBConfigValue('meinpaket.stocksync.tomarketplace', $this->mpID), array('abs', 'auto'))
				? '<tr><td><input type="submit" class="ml-button fullWidth smallmargin" name="refreshStock" value="'.ML_BUTTON_REFRESH_STOCK.'"/></td></tr>'
				: ''
			).'
		</tbody></table>';

		ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {	
	$('#listingDelete').click(function() {
		var me = this;
		if (($('#csinventory input[type="checkbox"]:checked').length > 0) &&
			confirm(unescape(<?php echo "'".html2url(sprintf(ML_GENERIC_DELETE_LISTINGS, $_modules[$this->marketplace]['title']))."'"; ?>))
		) {
			var d = $('#afterdelete').html();
			$('#infodiag').html(d).jDialog(
				{'width': (d.length > 1000) ? '700px' : '500px'},
				function() {
					$('#action').val('delete');
					$(me).parents('form').submit();
				})
		}
	});
});
/*]]>*/</script>
<?php // Durch aufrufen der Seite wird automatisch ein Aktualisierungsauftrag gestartet
		$js = ob_get_contents();	
		ob_end_clean();

		if (($left == '') && ($right == '')) {
			return '';
		}
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
			<div id="infodiag" class="dialog2" title="' . ML_LABEL_INFORMATION . '"></div>
			<span id="afterdelete" style="display: none">' . ML_RICARDO_AFTER_DELETE . '</span>
			'.$js;
	}

	protected function prepareInventoryItemData(&$item) {
		$item['MarketplaceTitle'] = $item['Title'];
		$item['MarketplaceTitleShort'] = (mb_strlen($item['MarketplaceTitle'], 'UTF-8') > $this->settings['maxTitleChars'] + 2)
			? (fixHTMLUTF8Entities(mb_substr($item['MarketplaceTitle'], 0, $this->settings['maxTitleChars'], 'UTF-8')).'&hellip;')
			: fixHTMLUTF8Entities($item['MarketplaceTitle']);
	}

	protected function getFields() {
		return array(
			'SKU' => array (
				'Label' => ML_LABEL_SKU,
				'Sorter' => 'sku',
				'Getter' => null,
				'Field' => 'SKU'
			),
			'Title' => array (
				'Label' => ML_LABEL_SHOP_TITLE,
				'Getter' => 'getTitle',
				'Field' => null,
			),
			'MarketplaceTitle' => array (
				'Label' => ML_LABEL_TITLE,
				'Sorter' => 'title',
				'Getter' => 'getMarketplaceTitle',
				'Field' => null,
			),
			'Price' => array (
				'Label' => ML_GENERIC_PRICE,
				'Sorter' => 'price',
				'Getter' => 'getItemPrice',
				'Field' => null
			),
			'Quantity' => array (
				'Label' => ML_LABEL_QUANTITY,
				'Sorter' => 'quantity',
				'Getter' => null,
				'Field' => 'Quantity',
			),
			'LastModified' => array (
				'Label' => 'Letzte Synchronisation',
				'Sorter' => 'lastmodified',
				'Getter' => 'getLastModified',
				'Field' => null
			),
		);
	}

	protected function getMarketplaceTitle($item) {
		return '<td title="' . fixHTMLUTF8Entities($item['MarketplaceTitle'], ENT_COMPAT) . '">' . $item['MarketplaceTitleShort'] . '</td>';
	}

	protected function getLastModified($item) {
		$item['LastSync'] = strtotime($item['LastSync']);
		if ($item['LastSync'] < 0) {
			return '<td>-</td>';
		}
		return '<td>'.date("d.m.Y", $item['LastSync']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['LastSync']).'</span>'.'</td>';
	}

	protected function postDelete() {
		MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'UploadItems'
		));
	}
}
