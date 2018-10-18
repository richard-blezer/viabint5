<?php
/*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Enterprise
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright �2007-2008 xt:Commerce GmbH. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~~~~ xt:Commerce VEYTON 4.0 Enterprise IS NOT FREE SOFTWARE ~~~~~~~~~~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce GmbH, www.xt-commerce.com
 #
 # @author Mario Zanier, xt:Commerce GmbH	mzanier@xt-commerce.com
 #
 # @author Matthias Hinsche					mh@xt-commerce.com
 # @author Matthias Benkwitz				mb@xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce GmbH, Bachweg 1, A-6091 Goetzens (AUSTRIA)
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

//require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.'/xt_reviews/classes/class.xt_reviews.php');
$reviews = new xt_reviews();

if ($page->page_action == '' && $_POST['paction'] != '')
    $page->page_action = $_POST['paction'];

if (isset($page->page_action) && $page->page_action != '') {

    switch ($page->page_action) {

        case 'write':
            ($plugin_code = $xtPlugin->PluginCode('reviews.php:write_top')) ? eval($plugin_code) : false;

            if (!isset($current_product_id) or $current_product_id == '') {
                $tmp_link = $xtLink->_link(array('page' => '404'));
                $xtLink->_redirect($tmp_link);
            }

            // 	check if logged in, if not, set snap and redirect
            if (!$_SESSION['registered_customer'] && XT_REVIEWS_ALLOW_GUEST_REVIEWS == "false") {
                $tmp_link = $xtLink->_link(array('page' => 'reviews', 'paction' => 'write', 'params' => 'info=' . $current_product_id));
                $brotkrumen->_setSnapshot($tmp_link);
                $info->_addInfoSession(TEXT_XT_REVIEWS_ERROR_LOGIN, 'error');
                $tmp_link = $xtLink->_link(array('page' => 'customer', 'paction' => 'login'));
                $xtLink->_redirect($tmp_link);
            }

            $p_info = new product($current_product_id, 'default');

            if (!$p_info->is_product) {
                $tmp_link = $xtLink->_link(array('page' => '404'));
                $xtLink->_redirect($tmp_link);
            }

            $rating_data = array(array('id' => '1', 'text' => '1'), array('id' => '2', 'text' => '2'), array('id' => '3', 'text' => '3'), array('id' => '4', 'text' => '4'), array('id' => '5', 'text' => '5'));

            $form_data = array();

            if (isset ($_POST['action']) && $_POST['action'] == 'add_review') {
                $result = $reviews->_addReview($_POST);
                if (!$result) {
                    $form_data = array('review_rating' => (int)$_POST['review_rating'], 'review_title' => $filter->_filter($_POST['review_title']), 'review_text' => $filter->_filter($_POST['review_text']));
                    $info->_addInfo(TEXT_XT_REVIEW_FORM_ERROR, 'error');
                } else {
                    $info->_addInfoSession(XT_REVIEWS_ADD_SUCCESS, 'success');
                    $tmp_link = $xtLink->_link(array('page' => 'reviews', 'paction' => 'success', 'params' => 'info=' . $current_product_id));
                    $xtLink->_redirect($tmp_link);
                }
            }
            $p_info->getBreadCrumbNavigation();
            $brotkrumen->_addItem($xtLink->_link(array('page' => 'reviews', 'paction' => 'write', 'params' => 'info=' . $current_product_id)), TEXT_XT_REVIEWS_WRITE);

		$tpl_data = array(
			'message' => $info->info_content,
			'review_stars_rating' => $reviews->getStars($p_info->data['products_id']),
			'products_name' => $p_info->data['products_name'],
			'rating' => $rating_data,
			'products_id' => $current_product_id,
			'review_rating' => 5
		);

            $tpl_data = array_merge($tpl_data, $form_data);
            $tpl_data = array_merge($tpl_data, $p_info->data);
            $tpl = 'write_review.html';

            $template = new Template();
            $template->getTemplatePath($tpl, 'xt_reviews', '', 'plugin');

            $page_data = $template->getTemplate('xt_write_reviews_smarty', $tpl, $tpl_data);
            break;

        case 'success':
            if (!isset($current_product_id) or $current_product_id == '') {
                $tmp_link = $xtLink->_link(array('page' => '404'));
                $xtLink->_redirect($tmp_link);
            }

            $p_info = new product($current_product_id, 'default');

            if (!$p_info->is_product) {
                $tmp_link = $xtLink->_link(array('page' => '404'));
                $xtLink->_redirect($tmp_link);
            }

            // check if logged in, if not, set snap and redirect
            if (!$_SESSION['registered_customer'] && XT_REVIEWS_ALLOW_GUEST_REVIEWS == "false") {
                $tmp_link = $xtLink->_link(array('page' => 'reviews', 'paction' => 'write', 'params' => 'info=' . $current_product_id));
                $brotkrumen->_setSnapshot($tmp_link);
                $info->_addInfoSession(XT_REVIEWS_ERROR_LOGIN, 'error');
                $tmp_link = $xtLink->_link(array('page' => 'customer', 'paction' => 'login'));
                $xtLink->_redirect($tmp_link);
            }

            $tpl_data = array('message' => $info->info_content, 'product_data' => $p_info->data, 'products_name' => $p_info->data['products_name'], 'rating' => $rating_data);
            $tpl = 'success_review.html';

            $p_info->getBreadCrumbNavigation();
            $brotkrumen->_addItem($xtLink->_link(array('page' => 'reviews', 'paction' => 'write', 'params' => 'info=' . $current_product_id)), TEXT_XT_REVIEWS_SUCCESS);

            $template = new Template();
            $template->getTemplatePath($tpl, 'xt_reviews', '', 'plugin');

            $page_data = $template->getTemplate('xt_success_reviews_smarty', $tpl, $tpl_data);

            break;

        case 'show':

            global $db, $xtLink;

            if (!isset($current_product_id) or $current_product_id == '') {
                $tmp_link = $xtLink->_link(array('page' => '404'));
                $xtLink->_redirect($tmp_link);
            }

            $reviews = new xt_reviews();
            $reviews_data = $reviews->getReviewsListing($current_product_id, false); 
            $p_info = new product($current_product_id, 'default');

            if (XT_REVIEWS_ALL_REVIEWS_PAGE == 'false') {
                $tmp_link = $xtLink->_link(array('page' => 'product', 'type' => 'product', 'name' => $p_info->data['products_name'], 'id' => $p_info->data['products_id'], 'seo_url' => $p_info->data['url_text']));
                $xtLink->_redirect($tmp_link);
            }

            if (count($reviews_data) == 0) $info->_addInfo(TEXT_XT_REVIEWS_NO_REVIEWS, 'warning');

            $p_info->getBreadCrumbNavigation();
            $brotkrumen->_addItem($xtLink->_link(array('page' => 'reviews', 'paction' => 'show', 'params' => 'info=' . $current_product_id)), TEXT_XT_REVIEWS_HEADING_REVIEWS);

            $link_reviews_write = $xtLink->_link(array('page' => 'reviews', 'paction' => 'write', 'params' => 'info=' . $p_info->data['products_id']));

		$tpl_data = array(
			'show_product' => XT_REVIEWS_PRODUCT_ON_ALL_REVIEWS_PAGE,
			'message' => $info->info_content,
			'link_reviews_write' => $link_reviews_write,
			'review_stars_rating' => $reviews->getStars($p_info->data['products_average_rating']),
			'product_data' => $p_info->data,
			'reviews_data' => $reviews_data,
			'NAVIGATION_COUNT' => $reviews->navigation_count,
			'NAVIGATION_PAGES' => $reviews->navigation_pages
		);

            $tpl = 'list_reviews.html';
            $tpl_data = array_merge($tpl_data, $p_info->data);
            $template = new Template();
            $template->getTemplatePath($tpl, 'xt_reviews', '', 'plugin');

            $page_data = $template->getTemplate('xt_list_reviews_smarty', $tpl, $tpl_data);

            break;
    }

}