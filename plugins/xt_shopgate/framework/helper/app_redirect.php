<?
/**
 * xtCommerce:
 * includes/header.php Zeile 40 (nach <meta...>)
 * 
 * include(DIR_FS_CATALOG.'/shopgate/helper/app_redirect.php');
 */

?>

<? if(preg_match("/iPhone|iPad|iPod/", $_SERVER['HTTP_USER_AGENT'])) : ?>

<?
	require_once(dirname(__FILE__).'/../lib/framework.php');
	$config = ShopgateConfig::validateAndReturnConfig();
	$shopNumber = $config["shop_number"];

	if($config["server"] == "pg")
		$url = "http://start.shopgatepg.com/$shopNumber";
	else
		$url = "http://start.shopgate.com/$shopNumber";
?>

	<script type="text/javascript">
		<? if(!isset($_COOKIE["shopgate_use_app"])): ?>
			if(confirm("Moechten sie die Mobile App benutzen?")) {
				window.location.href = '<?= $url ?>';
			} else {
				document.cookie = 'shopgate_use_app=0; path=/';
			}
		<? endif; ?>
	</script>
<? endif; ?>