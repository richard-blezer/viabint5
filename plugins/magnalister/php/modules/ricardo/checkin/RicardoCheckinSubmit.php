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
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/RicardoHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/classes/RicardoProductSaver.php');

class RicardoCheckinSubmit extends MagnaCompatibleCheckinSubmit {
	private $bVerify = false;
	private $oLastException = null;

	public function __construct($settings = array()) {
		global $_MagnaSession;

		$settings = array_merge(array(
			'language' => getDBConfigValue($settings['marketplace'] . '.lang', $_MagnaSession['mpID'], ''),
			'currency' => getCurrencyFromMarketplace($_MagnaSession['mpID']),
			'keytype' => getDBConfigValue('general.keytype', '0'),
			'itemsPerBatch' => 100,
			'mlProductsUseLegacy' => false,
		), $settings);

		parent::__construct($settings);
	}

	protected function processException($e) {
		$this->oLastException = $e;
	}

	public function getLastException() {
		return $this->oLastException;
	}

	protected function setUpMLProduct() {
		parent::setUpMLProduct();

		// Set Price and Quantity settings
		MLProduct::gi()->setPriceConfig(RicardoHelper::loadPriceSettings($this->mpID));
		MLProduct::gi()->setQuantityConfig(RicardoHelper::loadQuantitySettings($this->mpID));
	}

	protected function appendAdditionalData($iPID, $aProduct, &$aData) {
		$aPropertiesRow = MagnaDB::gi()->fetchRow('
			SELECT * FROM ' . TABLE_MAGNA_RICARDO_PROPERTIES . '
			 WHERE ' . ($this->settings['keytype'] == 'artNr'
				? 'products_model = "' . MagnaDB::gi()->escape($aProduct['ProductsModel']) . '"'
				: 'products_id = "' . $iPID . '"'
			) . '
			       AND mpID = ' . $this->_magnasession['mpID']
		);

		// Will not happen in sumbit cycle but can happen in loadProductByPId.
		if (empty($aPropertiesRow)) {
			$aData['submit'] = array();
			return;
		}

		$aData['submit']['SKU'] = $aData['submit']['ParentSKU'] = ($this->settings['keytype'] == 'artNr') ? $aProduct['MarketplaceSku'] : $aProduct['MarketplaceId'];
		$aData['submit']['Descriptions'] = array();

		if ($aPropertiesRow['LangDe'] === 'true') {
			$aData['submit']['Descriptions']['DE'] = array(
				'Title' => $aPropertiesRow['TitleDe'],
				'Subtitle' => $aPropertiesRow['SubtitleDe'],
				'Description' => $aPropertiesRow['DescriptionDe']
			);
		}

		if ($aPropertiesRow['LangFr'] === 'true') {
			$aData['submit']['Descriptions']['FR'] = array(
				'Title' => $aPropertiesRow['TitleFr'],
				'Subtitle' => $aPropertiesRow['SubtitleFr'],
				'Description' => $aPropertiesRow['DescriptionFr']
			);
		}
		
		if (isset($aPropertiesRow['DescriptionTemplate']) && $aPropertiesRow['DescriptionTemplate'] !== '-1') {
			$aData['submit']['DescriptionTemplate'] = $aPropertiesRow['DescriptionTemplate'];
		}
		
		if ($aPropertiesRow['Warranty'] == 0) {
			$aData['submit']['WarrantyDescription'] = array();

			if ($aPropertiesRow['LangDe'] === 'true') {
				$aData['submit']['WarrantyDescription']['DE'] = $aPropertiesRow['WarrantyDescriptionDe'];
			}

			if ($aPropertiesRow['LangFr'] === 'true') {
				$aData['submit']['WarrantyDescription']['FR'] = $aPropertiesRow['WarrantyDescriptionFr'];
			}
		}

		$aData['submit']['Quantity'] = $aData['quantity'];


		$imagePath = getDBConfigValue($this->marketplace . '.imagepath', $this->_magnasession['mpID'], SHOP_URL_POPUP_IMAGES);
		$imagePath = trim($imagePath, '/ ').'/';

		if (empty($aPropertiesRow['PictureUrl']) === false) {
			$pictureUrls = json_decode($aPropertiesRow['PictureUrl']);

			foreach ($pictureUrls as $image => $use) {
				if ($use == 'true') {
					$aData['submit']['Images'][] = array(
						'URL' => $imagePath . $image
					);
				}
			}
		}

		$aData['submit']['MarketplaceCategories'] = array(
			$aPropertiesRow['MarketplaceCategories']
		);

		$aData['submit']['Price'] = null;

		if ($aPropertiesRow['BuyingMode'] === 'buy_it_now' || ($aPropertiesRow['BuyingMode'] === 'auction' && $aPropertiesRow['EnableBuyNowPrice'] === 'on')) {
			$aData['submit']['Price'] =
				isset($aPropertiesRow['BuyNowPrice'])
				? $aPropertiesRow['BuyNowPrice']
				: $aData['price'];
		} else if ($aPropertiesRow['BuyingMode'] === 'auction' && $aPropertiesRow['BuyNowPrice'] !== null && $aPropertiesRow['BuyNowPrice'] !== 0) {
			$aData['submit']['Price'] = $aPropertiesRow['BuyNowPrice'];
		}

		$aData['submit']['ListingType'] = $aPropertiesRow['BuyingMode'];

		if ($aPropertiesRow['BuyingMode'] === 'auction') {
			$aData['submit']['Auction'] = array(
				'StartPrice' => $aPropertiesRow['StartPrice'],
				'Increment' => $aPropertiesRow['Increment'],
			);
		}

		$aData['submit']['ConditionType'] = $aPropertiesRow['ArticleCondition'];

		$shippingService = array(
			'Service' => $aPropertiesRow['ShippingDetails'],
			'Cost' => $aPropertiesRow['ShippingCost']
		);

		if ($aPropertiesRow['PackageSize'] !== null) {
			$shippingService['PackageSize'] = $aPropertiesRow['PackageSize'];
		}

		$aData['submit']['ShippingServices'] = array(
			$aData['submit']['ShippingServices'] = $shippingService
		);

		$aData['submit']['DeliveryCondition'] = $aPropertiesRow['ShippingDetails'];
		if ($aPropertiesRow['ShippingDetails'] === '0') {
			$aData['submit']['DeliveryDescription'] = array();

			if ($aPropertiesRow['LangDe'] === 'true') {
				$aData['submit']['DeliveryDescription']['DE'] = $aPropertiesRow['ShippingDescriptionDe'];
			}

			if ($aPropertiesRow['LangFr'] === 'true') {
				$aData['submit']['DeliveryDescription']['FR'] = $aPropertiesRow['ShippingDescriptionFr'];
			}
		}

		$aData['submit']['MaxRelistCount'] = $aPropertiesRow['MaxRelistCount'];
		$aData['submit']['StartTime'] = $aPropertiesRow['StartDate'];
		$aData['submit']['EndTime'] = $aPropertiesRow['EndTime'];
		$aData['submit']['ListingDuration'] = $aPropertiesRow['Duration'];
		$aData['submit']['PaymentMethods'] = json_decode($aPropertiesRow['PaymentDetails']);

		if (in_array(0, $aData['submit']['PaymentMethods'])) {
			$aData['submit']['PaymentDescription'] = array();

			if ($aPropertiesRow['LangDe'] === 'true') {
				$aData['submit']['PaymentDescription']['DE'] = $aPropertiesRow['PaymentdetailsDescriptionDe'];
			}

			if ($aPropertiesRow['LangFr'] === 'true') {
				$aData['submit']['PaymentDescription']['FR'] = $aPropertiesRow['PaymentdetailsDescriptionFr'];
			}
		}

		if ((empty($aPropertiesRow['FirstPromotion']) === false && $aPropertiesRow['FirstPromotion'] !== '-1') || (empty($aPropertiesRow['SecondPromotion']) === false && $aPropertiesRow['SecondPromotion'] !== '-1')) {
			$aData['submit']['Promotions'] = array();

			if (empty($aPropertiesRow['FirstPromotion']) === false && $aPropertiesRow['FirstPromotion'] !== '-1') {
				$aData['submit']['Promotions'][] = $aPropertiesRow['FirstPromotion'];
			}

			if (empty($aPropertiesRow['SecondPromotion']) === false && $aPropertiesRow['SecondPromotion'] !== '-1') {
				$aData['submit']['Promotions'][] = $aPropertiesRow['SecondPromotion'];
			}
		}

		if (getDBConfigValue(array($this->marketplace.'.leadtimetoshipmatching.prefer', 'val'), $this->mpID, false)) {
			$aData['submit']['ShippingTime'] = getDBConfigValue(
				array($this->marketplace.'.leadtimetoshipmatching.values', $aProduct['ShippingTimeId']),
				$this->mpID,
				$aPropertiesRow['Availability']
			);
		} else {
			$aData['submit']['ShippingTime'] = $aPropertiesRow['Availability'];
		}

		$aData['submit']['ItemTax'] = $aProduct['TaxPercent'];
	}

	protected function markAsFailed($sku) {
		$iPID = magnaSKU2pID($sku);
		$this->badItems[] = $iPID;
		unset($this->selection[$iPID]);
	}

	protected function postSubmit() {
		try {
			//*
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'UploadItems',
			));
			//*/
		} catch (MagnaException $e) {
			$this->submitSession['api']['exception'] = $e;
			$this->submitSession['api']['html'] = MagnaError::gi()->exceptionsToHTML();
		}
	}

}
