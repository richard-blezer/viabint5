<?php
/*
 #########################################################################
 #                       Shogate GmbH
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # http://www.shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Rev: 570 $
 #
 # @author Martin Weber, Shopgate GmbH	weber@shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #########################################################################
 */

if(!defined("E_DEPRECATED"))
	define("E_DEPRECATED", 8192);
	
$baseDir = realpath(dirname(__FILE__).'/../../../');
include_once $baseDir.'/xtFramework/admin/main.php';
if (!$xtc_acl->isLoggedIn()) {
	die('login required');
}

include_once realpath(dirname(__FILE__).'/../').'/classes/xt_shopgate_constants.php';
include_once realpath(dirname(__FILE__).'/../').'/classes/xt_shopgate_database.php';
include_once realpath(dirname(__FILE__).'/../').'/framework/helper/2d_is.php';

global $db;
?>

<?php 

$current_config = array();
$stores = array();

$result = $db->Execute("SELECT * FROM `" . TABLE_SHOPGATE_CONFIG . "`");
while(!$result->EOF) {
	$row = $result->fields;
	$current_config[$row["shop_id"]][$row["key"]] = $row["value"];
	$result->MoveNext();
}


$result = $db->Execute("SELECT * FROM ".TABLE_MANDANT_CONFIG);
while(!$result->EOF) {
	$stores[] = $result->fields;
	$result->MoveNext();
}

$qr_shop_is_active = array(
	'0' => "Nein, mein Shop ist noch nicht freigeschaltet",
	'1' => "Ja, mein Shop ist freigeschaltet",
);

$qr_gen_options = array(
	1 => "Standard (Über der Beschreibung)",
	2 => "Manuell platzieren",
	3 => "Deaktiviern"
);

$qr_own_app = array(
	'0' => "Nein, nur im Shopgate Marktplatz",
	'1' => "Ja, ich habe eine eigene App",
);

$qr_dst_options = array(
	sg_2d_is::SHOP_ITEM_CHECKOUT => "Direkt zur Kasse",
	sg_2d_is::SHOP_ITEM => "Produktseite anzeigen"
);

?>
<?php if(!empty($_POST['xt_shopgate'])):
	foreach($_POST['xt_shopgate'] as $storeId => $storeOption) {
		foreach($qr_gen_options as $i => $k) {
			if($k == $storeOption["qr_generator"]) {
				$storeOption["qr_generator"] = $i;
				break;
			}
		}
		
		foreach($qr_dst_options as $i => $k) {
			if($k == $storeOption["qr_destination"]) {
				$storeOption["qr_destination"] = $i;
				break;
			}
		}
		foreach($qr_own_app as $i => $k) {
			if($k == $storeOption["has_own_app"]) {
				$storeOption["has_own_app"] = $i;
				break;
			}
		}
//		foreach($qr_shop_is_active as $i => $k) {
//			if($k == $storeOption["shop_is_active"]) {
//				$storeOption["shop_is_active"] = $i;
//				break;
//			}
//		}

		if(!empty($current_config[$storeId])) {
			$db->Execute("DELETE FROM `" . TABLE_SHOPGATE_CONFIG . "` WHERE shop_id = $storeId");
		}
		
		$qry = "";
		foreach($storeOption as $key => $value) {
			if(!empty($qry)) $qry .= ",\n"; 
			$qry .= "( $storeId, '$key', '$value' )";
		}
		
		$qry = "INSERT INTO `" . TABLE_SHOPGATE_CONFIG . "`\n( `shop_id`, `key`, `value` )\nVALUES\n" . $qry;

		$db->Execute($qry);
	}
	echo '{"success":true}';
else:
?>

<div style="float: left;">
	<img src="../plugins/xt_shopgate/images/shopgate_logo_big.png" style="height: 100px; margin: 10px;" />
</div>
<div style="clear: both;"></div>

<div id="shopgateTest"></div>

<script type="text/javascript">

function shopgateQrMgnt() {
	Ext.onReady(function(){
		var data = [
<?php foreach($qr_gen_options as $id => $label): ?>
['<?=$id?>', '<?=$label?>'],
<?php endforeach; ?>
					];
		
		var store = new Ext.data.SimpleStore({
	        fields: ['id','display'],
	        data: data
	    });
	    
		var qrDst = new Ext.data.SimpleStore({
	        fields: ['id','display'],
	        data: [
<?php foreach($qr_dst_options as $id => $label) echo "['$id', '$label'],"; ?>
		   	        ]
	    });

//		var shopIsActiveCombo = new Ext.data.SimpleStore({
//	        fields: ['id','display'],
//	        data: [
//<?php foreach($qr_shop_is_active as $id => $label) echo "['$id', '$label'],"; ?>
//		    ]
//	    });
	    
		var appCombo = new Ext.data.SimpleStore({
	        fields: ['id','display'],
	        data: [
<?php foreach($qr_own_app as $id => $label) echo "['$id', '$label'],"; ?>
		    ]
	    });
	    
		var _form = new Ext.FormPanel({
	        border:false,
			renderTo: document.getElementById('shopgateTest'),
	        items: {
	            xtype:'tabpanel',
	            activeTab: 0,
	            defaults:{autoHeight:true, bodyStyle:'padding:10px'},
	            items:[
<?php foreach($stores as $store): ?>
<?php 
$shopIsActive = empty($current_config[$store["shop_id"]]["shop_is_active"]) ? 0 : $current_config[$store["shop_id"]]["shop_is_active"];
$qrGenerator = empty($current_config[$store["shop_id"]]["qr_generator"]) ? 1 : $current_config[$store["shop_id"]]["qr_generator"];
$qrDestination = empty($current_config[$store["shop_id"]]["qr_destination"]) ? sg_2d_is::SHOP_ITEM_CHECKOUT : $current_config[$store["shop_id"]]["qr_destination"];
$hasOwnApp = empty($current_config[$store["shop_id"]]["has_own_app"]) ? 0 : $current_config[$store["shop_id"]]["has_own_app"];
$itunesLink = empty($current_config[$store["shop_id"]]["itunes_link"]) ? "" : $current_config[$store["shop_id"]]["itunes_link"];
$backgroundColor = empty($current_config[$store["shop_id"]]["background_color"]) ? "#333" : $current_config[$store["shop_id"]]["background_color"];
$foregroundColor = empty($current_config[$store["shop_id"]]["foreground_color"]) ? "#3d3d3d" : $current_config[$store["shop_id"]]["foreground_color"];
?>
	               {
	                title:'<?= $store["shop_title"] ?>',
	                layout:'form',
	                labelWidth: 150,
	                defaults: { width: 200, },
	                defaultType: 'textfield',
//	                listeners: { activate: selectTab },
	                items: [
	                {
//	                    fieldLabel: 'Unser Shop ist bei Shopgate freigeschaltet',
//	                    name: 'xt_shopgate[' + <?= $store["shop_id"] ?> + '][shop_is_active]',
//	                    xtype: 'combo',
//	                    store: shopIsActiveCombo,
//						allowBlank: false,
//				        displayField: 'display',
//				        valueField: 'id',
//				        selectOnFocus: true,
//				        mode: 'local',
//				        typeAhead: true,
//				        editable: false,
//				        triggerAction: 'all',
//	                    value: <?= $shopIsActive ?>, 
//					},{
						fieldLabel: 'Position des QR-Codes',
						name: 'xt_shopgate[' + <?= $store["shop_id"] ?> + '][qr_generator]',
				        xtype: 'combo',
				        store: store,
						allowBlank: false,
				        displayField: 'display',
				        valueField: 'id',
				        selectOnFocus: true,
				        mode: 'local',
				        typeAhead: true,
				        editable: false,
				        triggerAction: 'all',
				        value: <?= $qrGenerator ?>,
	                },{
	                	fieldLabel: 'Nach dem Scannen',
						name: 'xt_shopgate[' + <?= $store["shop_id"] ?> + '][qr_destination]',
						allowBlank: false,
				        xtype: 'combo',
				        store: qrDst,
//				        listeners: {select: toggleTemplateDescription},
				        displayField: 'display',
				        valueField: 'id',
				        selectOnFocus: true,
				        mode: 'local',
				        typeAhead: true,
				        editable: false,
				        triggerAction: 'all',
				        value: <?= $qrDestination ?>,
	                },{
						fieldLabel: 'Mein Shop ist als eigene App verfügbar',
						xtype: 'combo',
						name: 'xt_shopgate[' + <?= $store["shop_id"] ?> + '][has_own_app]',
						store: appCombo,
						allowBlank: false,
				        displayField: 'display',
				        valueField: 'id',
				        selectOnFocus: true,
				        mode: 'local',
				        typeAhead: true,
				        editable: false,
				        triggerAction: 'all',
						value: <?= $hasOwnApp ?>
					},{
						fieldLabel: 'Link zur eigenen App (falls eigene App vorhanden)',
						name: 'xt_shopgate[' + <?= $store["shop_id"] ?> + '][itunes_link]',
						value: '<?= $itunesLink ?>'
	                },{
						fieldLabel: 'Hintergrundfarbe',
						name: 'xt_shopgate[' + <?= $store["shop_id"] ?> + '][background_color]',
						value: '<?= $backgroundColor ?>'
	                },{
						fieldLabel: 'Schriftfarbe',
						name: 'xt_shopgate[' + <?= $store["shop_id"] ?> + '][foreground_color]',
						value: '<?= $foregroundColor ?>'
					}
					]
	            },
<?php endforeach; ?>
				]
	        },
	
	        buttons: [{
	            text: 'Save',
	            listeners: { click: saveData }
	        },{
	            text: 'Cancel',
	            type: 'reset' 
	        }]
	    });
	
		function saveData() {
			_form.getForm().submit(
				{
					url: '../plugins/xt_shopgate/pages/qr_mgnt.php',
					waitMsg: 'Bitte Warten ...',
				}
			);
		}
		
		function resetData() {
			_form.getForm().submit(
				{
					url: '../plugins/xt_shopgate/pages/qr_mgnt.php',
				}
			);
		}
	});
}

new shopgateQrMgnt();
</script>


<link rel="stylesheet" type="text/css" href="../plugins/xt_shopgate/css/box_xt_shopgate.css" />
<!--[if IE 7]>
<style type="text/css">
	#shopgate_qr_banner td.shopgate_qr_code img {
		margin-bottom: -5px;
	}
</style>
<![endif]-->
<!--[if IE 6]>
<style type="text/css">
	#shopgate_qr_banner td.shopgate_qr_code img {
		margin-bottom: -5px;
	}
	
	#shopgate_qr_banner td.shopgate_bubble div {
		filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='bubble_bg.png', sizingMethod='scale');
		background:none;
	}	
</style>
<![endif]-->

<div style="margin: 20px;">
<h2>Beispiel</h2>

<div style="width: 600px;">

<?php

$smarty = new Smarty();
$smarty->caching = false;
$smarty->compile_dir = _SRV_WEBROOT . "templates_c";
$smarty->assign("shopgate_qr_code", "../plugins/xt_shopgate/images/qr_m_shopgate_com.png");
$smarty->assign("shopgate_itunes_url", SHOPGATE_ITUNES_URL);
$smarty->assign("temp", "../");
$smarty->display( _SRV_WEBROOT."/plugins/xt_shopgate/templates/boxes/box_xt_shopgate.html");

?>
</div>
</div>

<div id="shopgateTemplateDescription"  style="margin: 20px;">

<h2>QR-Code manuell in ihr Template einbauen</h2>
<strong>
Um die untenstehende Box Manuell in ihr Template einzubauen, <br />
muss folgende Zeile in das Template der Produkte eingebaut werden:
</strong>
<br />
<br />
{box name=xt_shopgate type=user}

</div>
<?php endif; ?>