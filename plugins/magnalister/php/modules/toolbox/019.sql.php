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
 * $Id: viewchangelog.php 1271 2011-09-27 22:32:14Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER.'/db/019.sql.php');
$iStart = (isset($_GET['start']) ?(int)$_GET['start'] : 0);
if (!isset($_GET['processMpTable']) || $_GET['processMpTable'] == 'amazon') {
	// amazon
	$sMarketplace = 'amazon';
	$iLimit = 10;
	$oSql = new magna_updateAmazonForMultivariants($iStart, $iLimit);
} elseif ($_GET['processMpTable'] == 'ebay') {
	//ebay
	$sMarketplace = 'ebay';
	$iLimit = 20;
	$oSql = new magna_updateEbayForMultivariants($iStart, $iLimit);
}


$iStart = $iStart + $iLimit;
if ($oSql->getMasterCount() == 0) {
	//next marketplace
	$sMarketplace = $sMarketplace == 'amazon' ? 'ebay' : '';
	$iStart = 0;
} 
if (!empty($sMarketplace)) {
	?>
		<script type="text/javascript">
			document.location.href = "<?php echo toURL($_url, array('tool' => '019.sql', 'processMpTable' => $sMarketplace, 'start' => $iStart), true); ?>";
		</script>
	<?php
	echo '<a href="'.toURL($_url, array('processMpTable' => $sMarketplace, 'start' => $iStart)).'">next</a>';
} else {
	unset ($_url['tool']);
	echo '<a href="'.toURL($_url).'">Finish, back to toolbox</a>';
}