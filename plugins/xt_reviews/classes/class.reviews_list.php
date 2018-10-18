<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: class.reviews_list.php 6125 2013-03-22 12:23:00Z tu $
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class reviews_list extends products_list {


	/**
	 * Get list with reviews
	 *
	 * @param int $products_id
	 * @return array
	 */
	function getReviewsListing($products_id) {
		global $xtPlugin, $xtLink, $db,$language;

		$products_id = (int)$products_id;
		if ($products_id=='') return false;

		$review = new xt_reviews();

		$query = "SELECT * FROM ".TABLE_PRODUCTS_REVIEWS." WHERE language_code = '' OR language_code='".$language->code."' AND products_id='".$products_id."' and review_status='1'";

		$rs = $db->CacheExecute($query);

		if ($rs->RecordCount()==0) return false;

		$reviews = array();
		while (!$rs->EOF) {
			$reviews[] = $rs->fields;
			$rs->MoveNext();
		}

		// shuffle if more than max
		if (XT_REVIEWS_MAX_DISPLAY_PRODUCTS<$rs->RecordCount()) {
			shuffle($reviews);
			$reviews = array_slice($reviews, 0,XT_REVIEWS_MAX_DISPLAY_PRODUCTS);
		}

		$module_content = array();
		foreach ($reviews as $key => $arr) {
			$arr['review_rating'] = $review->_getReviewsStars($arr['review_id']);
			$customer = new customer($arr['customers_id']);

			$arr['review_editor'] = $customer->customer_default_address['customers_firstname'].' '.substr($customer->customer_default_address['customers_lastname'],0,1).'.';
			$module_content[] = $arr;
		}


		return $module_content;
	}

	function _display($products_id) {
		global $xtPlugin, $xtLink, $db, $p_info;


		$products_id = (int)$products_id;
		if ($products_id=='') return false;

		$module_content = $this->getReviewsListing($products_id);
		if (!$module_content) return false;
		$tpl_data = array('reviews_data'=>$module_content,'show_reviews_button'=>XT_REVIEWS_ALL_REVIEWS_PAGE,'link_reviews_list'=>$p_info->data['link_reviews_list']);

		$tmp_data = '';
		$tpl = 'products_reviews_list.html';
		$template = new Template();
		$template->getTemplatePath($tpl, 'xt_reviews', '', 'plugin');

		$tmp_data = $template->getTemplate('xt_reviews_p_list_smarty', $tpl, $tpl_data);
		return $tmp_data;

	}

}
?>