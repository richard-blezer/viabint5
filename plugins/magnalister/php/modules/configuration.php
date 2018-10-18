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
 * $Id: configuration.php 663 2015-04-07 10:35:04Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
/**
 * Global Configuration
 */

$_MagnaSession['mpID'] = '0';
 
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/Configurator.php');

/*
MagnaConnector::gi()->setTimeOutInSeconds(1);
try {
	MagnaConnector::gi()->submitRequest(array(
		'ACTION' => 'Ping',
		'SUBSYSTEM' => 'Core',
	));
} catch (MagnaException $e) {}
MagnaConnector::gi()->resetTimeOut();
*/

$form = json_decode(file_get_contents(DIR_MAGNALISTER.'config/'.$_lang.'/global.form'), true);

$keysToSubmit = array();
$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_general');
$cG->processPOST($keysToSubmit);

/* Passphrase is in DB now. Try to authenticate us */
if (isset($_POST['conf']['general.passphrase'])) {
	MagnaConnector::gi()->updatePassPhrase();
	if (!loadMaranonCacheConfig(true)) {
		echo '<p class="errorBox">'.ML_ERROR_UNAUTHED.'</p>';
	}
}

$passPhrase = getDBConfigValue('general.passphrase', '0');

if (empty($passPhrase) || isset($_GET['welcome'])) {
	$form = array(
		'general' => $form['general']
	);
	$partner = trim((string)@file_get_contents('installer/magnabundle.dat'));
	if (!empty($partner) && ($partner != 'key')) {
		$partner = 'partner='.$partner;
	} else {
		$partner = '';
	}

	unset($form['general']['headline']);
	/* Hier die bunte Startseite */
	echo '
		<p class="noticeBox bottomSpace">'.sprintf(ML_NOTICE_PLACE_PASSPHRASE, $partner).'</p>
		<div style="padding-bottom: 1em"></div>';
	$comercialText = '
		<div id="pageContent">'.fileGetContents(MAGNA_SERVICE_URL.MAGNA_APIRELATED.'promotion/?shopsystem='.SHOPSYSTEM, $warnings, 10).'</div>';	
	$comercialText = str_replace(
		array('##_PARTNER_##', ),
		array($partner,        ),
		$comercialText
	);

} else {
	$cG->setRequiredConfigKeys($requiredConfigKeys);
}

global $forceConfigView;
if (($forceConfigView !== false) && !isset($comercialText)) {
	echo $forceConfigView;
	$q = MagnaDB::gi()->query('
		SELECT products_model, COUNT(products_model) as cnt
		  FROM '.TABLE_PRODUCTS.' 
		 WHERE products_model <> \'\'
      GROUP BY products_model
        HAVING cnt > 1
	');
	$dblProdModel = array();
	while ($row = MagnaDB::gi()->fetchNext($q)) {
		$dblProdModel[] = $row['products_model'];
	}
	$evilProducts = MagnaDB::gi()->fetchArray('
		SELECT p.products_id, p.products_model, pd.products_name
		  FROM '.TABLE_PRODUCTS.' p
	 LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON p.products_id=pd.products_id AND pd.language_code = \''.$_SESSION['magna']['selected_language'].'\'
		 WHERE products_model=\'\' OR products_model IS NULL '.((!empty($dblProdModel))
		 	? 'OR products_model IN (\''.implode('\', \'', $dblProdModel).'\')'
		 	: ''
		 ).'
      ORDER BY p.products_model ASC, pd.products_name ASC
	');
	if (!empty($evilProducts)) {
		$traitorTable = '
		    <table class="datagrid">
		    	<thead><tr>
		    		<th>'.str_replace(' ', '&nbsp;', ML_LABEL_PRODUCT_ID).'</th>
		    		<th>'.ML_LABEL_ARTICLE_NUMBER.'</th>
		    		<th>'.ML_LABEL_PRODUCTS_WITH_INVALID_MODELNR.'</th>
		    	</tr></thead>
		    	<tbody>';
		    $oddEven = true;
			foreach ($evilProducts as $item) {
				$traitorTable .= '
					<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
						<td style="width: 1px;">'.$item['products_id'].'</td>
						<td style="width: 1px;">'.(empty($item['products_model']) ? '<i class="grey">'.ML_LABEL_NOT_SET.'</i>' : $item['products_model']).'</td>
						<td>'.(empty($item['products_name']) ? '<i class="grey">'.ML_LABEL_UNKNOWN.'</i>' : $item['products_name']).'</td>
					</tr>';
			}
		$traitorTable .= '
				</tbody>
			</table>';
		echo $traitorTable;
	}
}

echo $cG->renderConfigForm();
?>
<style>
body.magna div#content .ml-button {
/*
	background: linear-gradient(center top, rgba(255,255,255, 0.8) 0%, rgba(255,255,255,0) 50%, rgba(0,0,0,0) 50%, rgba(0,0,0,0.4) 100%), linear-gradient(left, red, orange, yellow, green, blue, indigo, violet);
	background: -moz-linear-gradient(center top, rgba(255,255,255, 0.8) 0%, rgba(255,255,255,0) 50%, rgba(0,0,0,0) 50%, rgba(0,0,0,0.4) 100%), -moz-linear-gradient(left, red, orange, yellow, green, blue, indigo, violet);
	background: 
	-webkit-gradient(linear, left top, left bottom, 
		color-stop(0.00, rgba(255,255,255, 0.8)), 
		color-stop(0.49, rgba(255,255,255, 0)), 
		color-stop(0.51, rgba(0,0,0, 0)), 
		color-stop(1.00, rgba(0,0,0,0.4))
	), -webkit-gradient(linear, left top, right top, 
		color-stop(0.00, red), 
		color-stop(16%, orange),
		color-stop(32%, yellow),
		color-stop(48%, green),
		color-stop(60%, blue),
		color-stop(76%, indigo),
		color-stop(1.00, violet)
	);
	text-shadow: 0px 0px 2px rgba(255,255,255, 1);
	background-position: 0px 0px;
*/
}
</style>
<?php
if (isset($comercialText)) echo $comercialText;

if (isset($_POST['conf']['general.callback.importorders'])) {
	$hours = array();
	foreach ($_POST['conf']['general.callback.importorders'] as $hour => $selected) {
		if (!ctype_digit($hour) && !is_int($hour)) {
			continue;
		}
		$hours[(int)$hour] = $selected == 'true';
	}
	$request = array (
		'ACTION' => 'SetCallbackTimers',
		'SUBSYSTEM' => 'Core',
		'DATA' => array (
			'Command' => 'ImportOrders',
			'Hours' => $hours
		),
	);
	try {
		MagnaConnector::gi()->submitRequest($request);
	} catch (MagnaException $e) {}
}

if (isset($_GET['SKU'])) {
	$pID = magnaSKU2pID($_GET['SKU']);
	if ($pID > 0) {
		$pIDh = '<pre>magnaSKU2pID('.$_GET['SKU'].') :: <a style="font:12px monospace;" href="categories.php?pID='.$pID.'&action=new_product">'.var_dump_pre($pID, true).'</a></pre>';
	} else {
		$pIDh = var_dump_pre(magnaSKU2pID($_GET['SKU']), 'magnaSKU2pID('.$_GET['SKU'].')');
	}
	echo $pIDh;
}

echo '<div id="switchSKU" class="dialog2" title="'.ML_TEXT_CONFIRM_SKU_CHANGE_TITLE.'">'.ML_TEXT_CONFIRM_SKU_CHANGE_TEXT.'</div>';
?>
<script type="text/javascript">/*<![CDATA[*/
	$(document).ready(function() {
		$('input[name="conf[general.keytype]"]').change(function (e) {
			$('#switchSKU').dialog({
				modal: true,
				width: '600px',
				buttons: {
					"<?php echo ML_BUTTON_LABEL_ABORT; ?>": function() {
						if ($('input[name="conf[general.keytype]"]')[1].checked) {
							$('input[name="conf[general.keytype]"]')[0].checked = true;
						} else {
							$('input[name="conf[general.keytype]"]')[1].checked = true;
						}
						$(this).dialog("close");
					},
					"<?php echo ML_BUTTON_LABEL_OK; ?>": function() {
						$(this).dialog("close");
					}
				}
			});
		});
	});
	/*]]>*/</script>
<?php

include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');