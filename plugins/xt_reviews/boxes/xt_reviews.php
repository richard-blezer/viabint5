<?php
/*
#########################################################################
#                       xt:Commerce VEYTON 4.0 Shopsoftware
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#
# Copyright 2007-2013 xt:Commerce International Ltd. All Rights Reserved.
# This file may not be redistributed in whole or significant part.
# Content of this file is Protected By International Copyright Laws.
#
# ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
#
# http://www.xt-commerce.com
#
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#
# @version $Id: xt_blog_latest_comments.php 153 2011-04-27 12:03:31Z pd $
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
global $db, $category, $current_category_id, $page;

if(isset($xtPlugin->active_modules['xt_reviews']) && $xtPlugin->active_modules['xt_reviews'] == true){
    $limit = (isset($params['limit']) && (int)$params['limit'] != 0 ? $params['limit'] : 5);
    $box_type = $params['box_type'];

    switch ($box_type) {
        case 'last_reviews':
            $query = "SELECT * FROM " . TABLE_PRODUCTS_REVIEWS . " INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " USING(products_id) ";

            $query .= "WHERE review_date <= NOW() ";

            if (XT_REVIEWS_LAST_REVIEWS_CATEGORY_FILTER == "true" && $current_category_id) {
                $query .= " AND categories_id='$current_category_id' ";
            }

            $query .= " ORDER BY review_date DESC LIMIT $limit";
            $rs = $db->Execute($query);

            $show_box = $rs->RecordCount() > 0;
            if ($rs->RecordCount() > 0) {
                $reviews = array();

                while (!$rs->EOF) {
                    $product = dal::getInstance()->getProduct($rs->fields['products_id']);
                    $reviews[] = array_merge($rs->fields, array("url" => $product->data['products_link']));
                    $rs->MoveNext();
                }
                $rs->Close();

                $tpl_data = array("type" => $box_type, "reviews" => $reviews);
            }
            break;
        default:
            $sql = new getProductSQL_query();
            $sql->setPosition('product_listing');

            $sql->setFilter('GroupCheck');
            $sql->setFilter('StoreCheck');
            $sql->setFilter('Fsk18');
            $sql->setFilter('Status');
            $sql->setFilter('Seo');

            if (_STORE_STOCK_CHECK_DISPLAY == 'false' && _SYSTEM_STOCK_HANDLING == 'true') {
                $sql->setFilter('Stock');
            }

            if (XT_REVIEWS_TOP_RATED_CATEGORY_FILTER == 'true' && $current_category_id) {
                $sql->setFilter('Categorie', (int)$current_category_id);
            }

            $join = " LEFT JOIN " . TABLE_PRODUCTS_REVIEWS . " reviews ON reviews.products_id=p.products_id ";
            $sql->setSQL_TABLE($join);
            $sql->a_sql_sort = "rating DESC";
            $sql->setSQL_GROUP('p.products_id');

            $query = $sql->getSQL_query('p.products_id, (SUM(reviews.review_rating) / COUNT(reviews.review_id)) AS rating');
            $query .= ' LIMIT ' . $limit;

            $result1 = $db->GetAll($query);
            $count = count($result1);
            $show_box = $count > 0;
            $module_content = array();

            for ($i = 0; $i < $count; $i++) {
                $size = 'default';

                $product = dal::getInstance()->getProduct($result1[$i]['products_id'], $size);
                $module_content[] = $product->data;
            }
            $tpl_data = array("type" => $box_type, "top_products" => $module_content);
            break;
    }
}else{
    $show_box = false;
}