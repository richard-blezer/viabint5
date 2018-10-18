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
 * $Id: configure.php 3830 2014-05-06 13:00:00Z tim.neumann $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the GNU General Public License v2 or later
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/classes/RicardoBuyingModeProcessor.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/classes/RicardoPaymentDetailsProcessor.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/classes/RicardoShippingDetailsProcessor.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/classes/RicardoWarrantyProcessor.php');

class RicardoConfigure extends MagnaCompatibleConfigure {

	protected function getFormFiles() {
		$forms = parent::getFormFiles();
		
		#$forms[] = 'ordersExtend';
		$forms[] = 'orderStatus';
		$forms[] = 'email_template_generic';
		$forms[] = 'product_template_generic';
		
		#echo print_m($forms);
		
		return $forms;
	}
	
	protected function mlGetCountries(&$form) {
		//tbd
		return;
	}
	
	protected function mlGetMaxRelistCount(&$form) {
		try {
			$maxRelist = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetMaxRelistCount'));
		} catch (MagnaException $me) {
			$maxRelist = array (
				'DATA' => array (
					'MaxRelistCount' => 1
				)
			);
			return;
		}
		$form['values'] = array();
		for ($i = 0; $i <= intval($maxRelist['DATA']['MaxRelistCount']); $i++) {
			$form['values'][$i] = $i . ' x';
		}

		if (getDBConfigValue($this->form['prepare']['fields']['buyingmode']['key'], $this->mpID) === 'buy_it_now') {
			$form['values'][2147483647] = 'Bis ausverkauft';
		}
	}
	
	protected function loadChoiseValuesAfterProcessPOST() {
		if (isset($this->form['prepare']['fields']['maxrelistcount'])) {
			$this->mlGetMaxRelistCount($this->form['prepare']['fields']['maxrelistcount']);
		}
	}

	protected function loadChoiseValues() {
		global $_MagnaSession;
		
		if ($this->isAuthed) {
			try {
				$descriptionTemplates = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetTemplates'));
			} catch (MagnaException $me) {
				$descriptionTemplates = array (
					'DATA' => array('null' => ML_ERROR_LABEL)
				);
			}
			$this->form['prepare']['fields']['descriptiontemplate']['values'][-1] = ML_RICARDO_LABEL_NOTEMPLATES;
			foreach ($descriptionTemplates['DATA'] as $templateId => $templateName) {
				$this->form['prepare']['fields']['descriptiontemplate']['values'][$templateId] = $templateName;
			}
			
			try {
				$articleConditions = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetArticleConditions'));
			} catch (MagnaException $me) {
				$articleConditions = array (
					'DATA' => array('null' => ML_ERROR_LABEL)
				);
			}
			$this->form['prepare']['fields']['articlecondition']['values'] = $articleConditions['DATA'];

			try {
				$availability = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetArticleAvailability'));
			} catch (MagnaException $me) {
				$availability = array (
					'DATA' => array('null' => ML_ERROR_LABEL)
				);
			}
			$this->form['prepare']['fields']['availability']['values'] = $availability['DATA'];

			try {
				$firstPromotions = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetFirstPromotion'));
			} catch (MagnaException $me) {
				$firstPromotions = array (
					'DATA' => array(
						'Text' => ML_ERROR_LABEL,
						'Combobox' => array ('null' => ML_ERROR_LABEL)
					),
				);
			}
			$this->form['promotion']['fields']['firstpromotion']['desc'] = $firstPromotions['DATA']['Text'];
			$this->form['promotion']['fields']['firstpromotion']['values'] = $firstPromotions['DATA']['Combobox'];

			try {
				$secondPromotions = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetSecondPromotion'));
			} catch (MagnaException $me) {
				$secondPromotions = array (
					'DATA' => array(
						'Text' => ML_ERROR_LABEL,
						'Combobox' => array ('null' => ML_ERROR_LABEL)
					),
				);
			}
			$this->form['promotion']['fields']['secondpromotion']['desc'] = $secondPromotions['DATA']['Text'];
			$this->form['promotion']['fields']['secondpromotion']['values'] = $secondPromotions['DATA']['Combobox'];
		}
		
		$this->form['login']['fields']['lang']['values'] = array (
			'de' => ML_RICARDO_LANGUAGE_GERMAN,
			'fr' => ML_RICARDO_LANGUAGE_FRENCH,
		);
		
		mlGetOrderStatus($this->form['orderSyncState']['fields']['shippedstatus']);
		
		parent::loadChoiseValues();
	}

	protected function getAuthValuesFromPost() {
		$accessSettings = array();
		foreach ($_POST['conf'] as $sKey => $val) {
			if (strpos($sKey, $this->marketplace.'.access.') === 0) {
				$accessSettings[str_replace($this->marketplace.'.access.', '', $sKey)] = trim($val);
			}
		}
		$pwFields = array(
			'MPPASSWORD'
		);
		foreach ($pwFields as $pwField) {
			$accessSettings[$pwField] = $this->processPasswordFromPost('access.'.$pwField, $accessSettings[$pwField]);
			if ($accessSettings[$pwField] === false) {
				unset($_POST[$this->marketplace.'.access.'.$pwField]);
			}
		}
		foreach ($accessSettings as $field => $val) {
			if (empty($val)) {
				unset($_POST[$this->marketplace.'.access.'.$field]);
			}
		}
		
		#echo print_m($accessSettings, '$accessSettings');
		
		return $accessSettings;
	}

	protected function finalizeForm() {
		parent::finalizeForm();
		if (!$this->isAuthed) {
			$this->form = array (
				'login' => $this->form['login']
			);
			return;
		}
	}

	public static function languageMatching($args, &$value = '') {
		global $_MagnaSession;

		$languages = array('DE', 'FR');
		$shopLanguages = array('values' => array());
		mlGetLanguages($shopLanguages);

		$configValues = getDBConfigValue($args['key'], $_MagnaSession['mpID'], array());
		if (!is_array($configValues)) {
			$configValues = array();
		}
		$html = '<table class="nostyle" width="100%" style="float: left; margin-right: 2em;">
			<thead><tr>
				<th width="25%">'.ML_RICARDO_LABEL_LANGUAGE.'</th>
				<th width="75%">'.ML_LABEL_SHOP_LANGUAGE.'</th>
			</tr></thead>
			<tbody>';
		foreach ($languages as $lang) {
			$shopLangs = $shopLanguages['values'];
			$html .= '
				<tr>
					<td width="25%" class="nowrap">'.$lang.'</td>
					<td width="75%"><select name="conf['.$args['key'].']['.$lang.']">';
			foreach ($shopLangs as $sKey => $sVal) {
				$html .= '<option value="'.$sKey.'" '.(
					(array_key_exists($lang, $configValues) && ($configValues[$lang] == $sKey))
						? 'selected="selected"'
						: ''
					).'>'.$sVal.'</option>';
			}
			$html .= '
					</select></td>
				</tr>';
		}
		$html .= '</tbody></table><p>&nbsp;</p>';

		return $html;
	}
	
	
	public static function listingLanguage($args) {
		global $_MagnaSession;
		$arrayKey = explode('.', $args['key']);
		$id = implode('_', $arrayKey);
		
		$confDe = getDBConfigValue($args['key'] . '.de', $_MagnaSession['mpID'], array());		
		$confFr = getDBConfigValue($args['key'] . '.fr', $_MagnaSession['mpID'], array());
		
		if (!empty($confDe) || !empty($confFr)) {
			if ($confDe['val'] === true) {
				$de = 'checked';
			} else {
				$de = '';
			}
			
			if ($confFr['val'] === true) {
				$fr = 'checked';
			} else {
				$fr = '';
			}
		} else {
			$de = 'checked';
			$fr = '';
		}
		
		ob_start();
		?>
		<div class="input langCheckBoxes">
			<input type="hidden" value="false" name="conf[<?= $args['key'] ?>.de][val]"/>
			<input type="checkbox" value="true" id="config_<?= $id ?>_de_val" name="conf[<?= $args['key'] ?>.de][val]" <?= $de?>/>
			<label for="conf[<?= $args['key'] ?>.de]">DE</label>
			
			<input type="hidden" value="false" name="conf[<?= $args['key'] ?>.fr][val]"/>
			<input type="checkbox" value="true" id="config_<?= $id ?>_fr_val" name="conf[<?= $args['key'] ?>.fr][val]" <?= $fr?>/>
			<label for="conf[<?= $args['key'] ?>.de]">FR</label>
		</div>
		<script type="text/javascript">
			$(document).on("click", ".langCheckBoxes input", function(e) {
				if ($(".langCheckBoxes :checked").length === 0 && $(this).prop('checked') === false) {
					e.preventDefault();
				}
			});
		</script>
		<?php
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
	
	public static function leadTimeToShipMatching($args, &$value = '') {
		global $_MagnaSession;
		
		$aShippingTimes = array('values' => array());
		mlGetShippingStatus($aShippingTimes);
		$aShippingTimes = $aShippingTimes['values'];
		
		$aLeadTimeToShipMatching = getDBConfigValue($args['key'], $_MagnaSession['mpID'], array());
	
		try {
			$availability = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetArticleAvailability'));
		} catch (MagnaException $me) {
			return '&mdash';
		}
		$aOpts = $availability['DATA'];
		
		$html = '<table class="nostyle" width="100%" style="float: left; margin-right: 2em;">
			<thead><tr>
				<th width="25%">'.ML_LABEL_SHIPPING_TIME_SHOP.'</th>
				<th width="75%">'.ML_RICARDO_LABEL_SHIPPINGTIME.'</th>
			</tr></thead>
			<tbody>';
		foreach ($aShippingTimes as $stId => $stName) {
			$html .= '
				<tr>
					<td width="25%" class="nowrap">'.$stName.'</td>
					<td width="75%"><select name="conf['.$args['key'].']['.$stId.']">';
			foreach ($aOpts as $sKey => $sVal) {
				$html .= '<option value="'.$sKey.'" '.(
					(array_key_exists($stId, $aLeadTimeToShipMatching) && ($aLeadTimeToShipMatching[$stId] == $sKey))
						? 'selected="selected"'
						: ''
					).'>'.$sVal.'</option>';
			}
			$html .= '
					</select></td>
				</tr>';
		}
		$html .= '</tbody></table><p>&nbsp;</p>';
		
		#	$html .= print_m($taxes, '$taxes');
		#	$html .= print_m(func_get_args(), 'func_get_args');
		return $html;
	}
}

