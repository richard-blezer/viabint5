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
 * $Id: admin_view_top.php 492 2014-09-23 08:06:47Z miguel.heredia $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function renderTabs($pages, $kind, $selected, $baseURL) {
	$html = '
		<div class="magnaTabs2">
			<ul>';
	if (empty($selected)) {
		reset($pages);
		$selected = key($pages);
	}
	foreach ($pages as $url => $page) {	
		if (is_array($page)) {
			$title = $page['title'];
		} else {
			$title = $page;
		}
		$html .= '
				<li'.(($url == $selected) ? ' class="selected"' : '').'>
					<a href="'.toURL($baseURL, array($kind => $url)).'" title="'.$title.'">'.$title.'</a>
		 		</li>';
	}
	$html .= '
			</ul>
		</div>';
	return $html;
}

global $magnaConfig, $_executionTime, $_js, $_mainTitle, $_url,
	   $_MagnaSession, $_MagnaShopSession, $_magnaQuery, $_modules, 
	   $_updatedSuccessfully, $_pageCSS;

if (in_array(SHOPSYSTEM, array('oscommerce', 'xonsoft'))) {
	global $PHP_SELF;
}

if (array_key_exists('module', $_GET) && array_key_exists($_GET['module'], $_modules)) {
	$_mainTitle .= $_modules[$_GET['module']]['title'];
}

$renderengine = MLBrowserDetect::gi()->get('Engine');
$os = MLBrowserDetect::gi()->get('Platform');
if ($os === 'win') {
	$os = 'windows';
}
if (MLBrowserDetect::gi()->compare('Browser', 'msie', '==')) {
	$msieVersion = floatval(MLBrowserDetect::gi()->get('BVersion'));
	if ($msieVersion < 10) {
		$renderengine = 'ielt10';
	} else if ($msieVersion < 9) {
		$renderengine = 'ielt9';
	} else if ($msieVersion < 8) {
		$renderengine = 'ielt8';
	}
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN""http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<?php
/* Force IE into Standards Mode */
if (MLBrowserDetect::gi()->compare('Browser', 'msie', '==')) {
	echo '		<meta http-equiv="x-ua-compatible" content="IE='.((int)MLBrowserDetect::gi()->get('BVersion')).'">'."\n";
}
		?>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<?php if (!isset($_GET['module']) || ($_GET['module'] != 'nojs')) {
			echo '<noscript><meta http-equiv="refresh" content="0;URL='.toURL(array('module' => 'nojs')).'"></noscript>'."\n";
		} ?>
		<title>magnalister :: <?php echo trim($_mainTitle, "- \t\n\r"); ?></title>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<link rel="stylesheet" type="text/css" href="css/jqueryui/jquery-ui-1.9.1.custom.css" />
		<link rel="stylesheet" type="text/css" href="css/magnalister.css?<?php echo CLIENT_BUILD_VERSION?>" /><?php
			if (isset($_pageCSS) && ($_pageCSS = trim($_pageCSS)) && !empty($_pageCSS)) {
				echo '
		<style type="text/css">
'.$_pageCSS.'
		</style>'."\n";
			}
		?>
		<style>
		@-moz-keyframes ml-css-spin {
			0% {-moz-transform: rotate(0deg);}
			100% {-moz-transform: rotate(360deg);}
		}
		@-webkit-keyframes ml-css-spin {
			0% {-webkit-transform: rotate(0deg);}
			100% {-webkit-transform: rotate(360deg);}
		}
		@keyframes ml-css-spin {
			0% {transform: rotate(0deg);}
			100% {transform: rotate(360deg);}
		}

		.ml-css-loading {
			-o-box-sizing: border-box;
			-ie-box-sizing: border-box;
			-moz-box-sizing: border-box;
			-webkit-box-sizing: border-box;
			box-sizing: border-box;
			-moz-animation: ml-css-spin .8s infinite linear;
			-webkit-animation: ml-css-spin .8s infinite linear;
			animation: ml-css-spin .8s infinite linear;
		}
		</style>
		<script type="text/javascript" src="js/debugFunctions.js"></script>
		<script type="text/javascript">/*<![CDATA[*/
			var debugging = <?php echo (MAGNA_DEBUG) ? 'true' : 'false'; ?>;
			if ((debugging === true) && window.console) {
				var myConsole = console;
			} else {
				var myConsole = {
					log: function(){},
					debug: function(){},
					info: function(){},
					warn: function(){},
					error: function(){},
					assert: function(){},
					dir: function(){},
					dirxml: function(){},
					trace: function(){},
					table: function(){},
					group: function(){},
					groupEnd: function(){},
					time: function(){},
					timeEnd: function(){},
					profile: function(){},
					profileEnd: function(){},
					count: function(){},
					table: function(){}
				}
			}
			
			var blockUICSS = {
				'border': 'none',
				'padding': '15px',
				'background-color': '#fff',
				'border-radius': '10px',
				'-moz-border-radius': '10px',
				'-webkit-border-radius': '10px',
				'opacity': '0.8',
				'color': '#000',
				'font-size': '15px',
				'font-weight': 'bold'
			};
			var blockUIMessage = '<span><?php echo ML_TEXT_PLEASE_WAIT; ?></span>';
			
			var blockUILoading = {
				overlayCSS: { 
					backgroundColor: '#fff',
					'opacity': '0.8',
					'z-index': '9000'
				},
				css: {
					'width': '32px',
					'height': '32px',
					'border-width': '4px',
					'border-style': 'solid',
					'border-color': 'rgba(199, 53, 47, 0.25) rgba(199, 53, 47, 0.25) rgba(199, 53, 47, 0.25) rgba(199, 53, 47, 1)',
					'border-radius': '32px',
					'padding': '0',
					'left': '50%',
					'margin': '0 0 0 -16px',
					'padding': '0',
					'top': '300px',
					'z-index': '9999',
					'background': 'transparent'
				},
				blockMsgClass: 'ml-css-loading',
				message: '<div></div>',
				onBlock: function() {
						jQuery('.blockUI.ml-css-loading.blockPage').bind('dblclick', function() {
								jQuery.unblockUI();
						});
				}
			};
			var blockUIProgress = {
				overlayCSS: { 
					'background': '#000',
					'opacity': '0.1',
					'z-index': '9000'
				},
				css: {
					'background': '#fff',
					'width': '200px',
					'margin-left': '-100px',
					'height': '16px',
					'left': '50%',
					'padding': '10px',
					'border': 'none',
					'border-radius': '10px',
					'-moz-border-radius': '10px',
					'-webkit-border-radius': '10px',
					'box-shadow': '0 0 20px #000000',
					'-moz-box-shadow': '0 0 20px #000000',
					'-webkit-box-shadow': '0 0 20px #000000',
					'z-index': '9001'
				},
				message: '<div id="progressBarContainer"><div id="progressBar"></div><div id="progressPercent">0%</div></div>'
			};
			
			/* Preload Loading Animation */
			progressbarImage = new Image(); 
			progressbarImage.src = "images/progressbar.png";
		/*]]>*/</script>
		<script type="text/javascript" src="js/jquery-1.8.3.js"></script>
		<script type="text/javascript" src="js/jquery.timers-1.2.js"></script>
		<script type="text/javascript" src="js/jquery.blockUI.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.9.1.custom.js"></script>
		<script type="text/javascript" src="js/jquery-ui-i18n.js"></script>
		<script type="text/javascript" src="js/jquery.ba-throttle-debounce.js"></script>
		
		<script type="text/javascript" src="js/magnalister_general.js"></script>
		<script type="text/javascript" src="js/classes/JSClass.js"></script>
		
		<script type="text/javascript" src="js/loading-timer.js"></script>
<?php
		if (!empty($_js)) {
			foreach ($_js as $js) {
echo '		<script type="text/javascript" src="'.$js.'"></script>'."\n";
			}
		}
?>
		<script type="text/javascript">/*<![CDATA[*/
			(function(jQuery) {
				jQuery.fn.jDialog = function(parameters, okFunction) {
					if (okFunction == undefined) {
						okFunction = function() {
						};
					}
					if (parameters == undefined) {
						parameters = {};
					}
					parameters = jQuery.extend({
						modal: true,
						width: 500,
						minHeight: 100,
						buttons: {
							OK: function() {
								jQuery(this).dialog('close');
								okFunction();
							}
						}
					}, parameters);
					jQuery(this).dialog(parameters);
				}
			})(jQuery);

			jQuery(document).ready(function() {
				jQuery("body").everyTime('120s', 'keepAlive', function(i) {
					jQuery.get(
					   "<?php echo FILENAME_MAGNALISTER; ?>", {
							'module':'ajax',
							'request':'keepAlive'
					   },
					   function(data) {
					   		//myConsole.log(data);
					   }
					);
				});

				var bgC = jQuery('#content').css('background-color');
				if (bgC.length > 1) {
					jQuery('td.boxCenter').css({'background-color': bgC});
				}
				$('#globalButtonBox span').on('click', (function() {
					// prevent multiple clicks on the button.
					var executed = false;
					return function (e) {
						if (executed) {
							e.preventDefault();
							return;
						}
						executed = true;
						$.blockUI(blockUILoading);
						window.location.href = $(e.target).data('href');
					}
				})());
			});
		/*]]>*/</script>
		<!--[if lt IE 9]><script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				$('div.magnamain').each(function() {
					$(this).css({height: this.scrollHeight < 181 ? "180px" : "auto"});
				});
			});
		/*]]>*/</script><![endif]-->
	</head>
	<body class="magna <?php echo SHOPSYSTEM.(isset($renderengine) ? ' '.$renderengine : '').' '.$os; ?> jqueryui">



<?php
if (version_compare(CURRENT_CLIENT_VERSION, LOCAL_CLIENT_VERSION, '>') && !version_compare(MINIMUM_CLIENT_VERSION, LOCAL_CLIENT_VERSION, '>')) {
	if (!MAGNA_SAFE_MODE) {
		preg_match('/#([^#]*)#/', ML_TEXT_NEW_VERSION, $matches);
		echo '
			<p class="successBox">
				'.sprintf(str_replace(
					$matches[0],
					'<a href="'.toUrl(array('update' => 'true')).'" title="Update">'.$matches[1].'</a>',
					ML_TEXT_NEW_VERSION
				), CURRENT_CLIENT_VERSION).'
			</p>';
	} else {
		echo '
			<p class="successBox">
				'.sprintf(ML_TEXT_NEW_VERSION_SAFE_MODE, CURRENT_CLIENT_VERSION).'
			</p>';
	}
	
}
if (MAGNA_SAFE_MODE && isset($_GET['update']) && ($_GET['update'] == 'true') && !$_updatedSuccessfully) {
	echo '
		<p class="noticeBox">'.str_replace(
			'#LINK_GLOBAL_CONF#', 
			'<a href="'.toURL(array('module' => 'configuration')).'">'.ML_MODULE_GLOBAL_CONFIG.'</a>',
			ML_TEXT_GENERIC_SAFE_MODE
		).'</p>';
}
if ($_updatedSuccessfully) {
	echo '<p class="successBox">'.ML_TEXT_UPDATE_SUCCESS.'</p>';
	if (MAGNA_SHOP_CHANGES) {
		echo '
			<div id="infoshopchangesdiag" class="dialog2" title="'.ML_LABEL_ATTENTION.'">'.ML_TEXT_UPDATE_SHOP_CHANGES.'</div>
			<script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#infoshopchangesdiag\').jDialog();
				});
			/*]]>*/</script>
		';
	}
}
if (LOCAL_CLIENT_VERSION == '0') {
	echo '<p class="errorBox">'.ML_ERROR_COULD_NOT_LOAD_LOCAL_CLIENTVERSION.'</p>';	
}
$globalButtons = array (
	array (
		'title' => ML_LABEL_IMPORT_ORDERS,
		'icon' => 'cart',
		'link' => array('do' => 'ImportOrders'),
	),
	array (
		'title' => ML_LABEL_SYNC_ORDERSTATUS,
		'icon' => 'upload',
		'link' => array('do' => 'SyncOrderStatus'),
	),
	array (
		'title' => ML_LABEL_SYNC_INVENTORY,
		'icon' => 'sync',
		'link' => array('do' => 'SyncInventory', 'MLDEBUG' => 'true'),
	),
	array (
	 	'title' => ML_LABEL_UPDATE,
		'icon' => 'update',
		'link' => array('update' => 'true'),
	),
);
?>
	<h1 id="magnalogo"><a href="<?php echo toURL(); ?>" title="<?php echo ML_HEADLINE_MAIN; ?>">
		<img style="margin: 1px 0px -2px 12px;" src="<?php echo DIR_MAGNALISTER_IMAGES; ?>xtmulticonnect_logo.png" alt="<?php echo ML_HEADLINE_MAIN; ?>" width="172" height="31"/>
	</a></h1><!--<span style="display: inline-block; padding-left: 3px; padding-top: 36px;"> :: Debug &#4314;(&#3232;&#30410;&#3232;&#4314;</span>-->
	<div id="globalButtonBox"><?php
		foreach ($globalButtons as $blargh) {
			echo '<span class="gfxbutton border '.$blargh['icon'].'" data-href="'.toURL($_url, $blargh['link']).'" title="'.$blargh['title'].'"></span> ';
		}
	?></div>
	<div class="visualClear">&nbsp;</div>
	<div id="magnaErrors"><p class="errorBox"><?php echo ML_ERROR_API; ?></p><div></div></div>
<?php
$structure = magnaGenerateNavStructure();

echo '
	<div class="magnaTabs2">
		<ul>';
foreach ($structure as $item) {	
	if (empty($item['label'])) {
		$labelhtml = '';
		$labelattr = $item['title'];
	} else {
		$labelhtml = str_replace(
			array('&lt;', '&gt;', '&quot;'),
			array('<', '>', '"'),
			fixHTMLUTF8Entities($item['label'])
		);
		$labelattr = $item['title'].' :: '.str_replace(
			array('<', '>', '"'),
			array('&lt;', '&gt;', '&quot;'),
			strip_tags($labelhtml)
		);
		$labelhtml = ' &nbsp;'.$labelhtml;
	}
	if ($item['title'] == ML_LABEL_MORE_MODULES) {
		$item['title'] = '&hellip;';
	}
	echo '
			<li class="'.$item['class'].'">
				<a href="'.$item['url'].'" title="'.$labelattr.'">'.(
					!empty($item['image'])
						? ('<img src="'.$item['image'].'" alt="'.$labelattr.'"/>')
						: $item['title']
				).$labelhtml.'</a>
			</li>';
}
echo '
		</ul>
	</div>';
echo "\n";
?>
								<div id="content" class="magnamain">
<?php
$_additionalDivs = 0;
$tmpMagnaQuery = $_magnaQuery;
if (array_key_exists($_MagnaSession['currentPlatform'], $_modules)) {
	$module = $_modules[$_MagnaSession['currentPlatform']];
	if (isset($_GET['mode']) && array_key_exists($_GET['mode'], $module['pages'])) {
		$tmpMagnaQuery['mode'] = $_GET['mode'];
	}

	if (array_key_exists('pages', $module)) {
		echo renderTabs(
			$module['pages'],
			'mode',
			$tmpMagnaQuery['mode'],
			$_url
		);
	}
	echo '<div class="magnamain">';
	++$_additionalDivs;
	
	if (is_array($module['pages'][$tmpMagnaQuery['mode']])) {
		echo renderTabs(
			$module['pages'][$tmpMagnaQuery['mode']]['views'],
			'view',
			$_magnaQuery['view'],
			array_merge($_url, array('mode' => $tmpMagnaQuery['mode']))
		);
		echo '<div class="magnamain">';
		++$_additionalDivs;
	}
}
