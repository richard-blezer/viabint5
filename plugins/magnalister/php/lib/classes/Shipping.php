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
 * $Id: Shipping.php 132 2012-10-19 12:39:38Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 *
 * Made compartible with osCommerce. Usable in admin area.
 *
 */

/* -----------------------------------------------------------------------------------------
   $ shipping.php 1305 2005-10-14 10:30:03Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(shipping.php,v 1.22 2003/05/08); www.oscommerce.com 
   (c) 2003	 nextcommerce (shipping.php,v 1.9 2003/08/17); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class FakeOrder {
	public $delivery = array();
	
	public function __construct($countryID) {
		//var_dump($countryID); 
		$this->delivery['country'] = MagnaDB::gi()->fetchRow(
			'SELECT sd.countries_iso_code_2 AS id, 
					sdd.countries_name AS title,
					sd.countries_iso_code_2 AS iso_code_2, 
					sd.countries_iso_code_3 AS iso_code_3 
			   FROM '.TABLE_COUNTRIES.' sd
			    JOIN '.TABLE_COUNTRIES_DESCRIPTION.' sdd ON sd.countries_iso_code_2 = sdd.countries_iso_code_2
			  WHERE sd.countries_iso_code_2=\''.MagnaDB::gi()->escape($countryID).'\''
		);
	}
}

class MagnaShipping {
	// class constructor
	public function __construct() {
	}
	
	public function getShippingMethods() {
		return array();
	}
	
	public function methodExists() {
		return false;
	}
	
	public function configure($param1 = '') {
		
	}
	public function quote($param1 = '', $param2 = '') {
		
	}
	
	public function getShippingCost() {
		return 0;
	}
	
	public function cheapest() {
		return false;
	}
}
