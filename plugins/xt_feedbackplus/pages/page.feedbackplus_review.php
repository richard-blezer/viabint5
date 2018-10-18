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
 # @version $Id: reviews.php 6228 2013-04-12 16:13:58Z stefan $
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

include_once _SRV_WEBROOT.'plugins/xt_reviews/classes/class.xt_reviews.php';
$reviews = new xt_reviews();


if ($page->page_action == '' && $_POST['paction'] != '')
    $page->page_action = $_POST['paction'];

if (isset($page->page_action) && $page->page_action != '') {

    switch ($page->page_action) {

        case 'write':

            // check OID + hash
            $ID = $_GET['ID'];
            $ID=$filter->_charNum($ID);
            $hash = $_GET['hash'];
            $hash=$filter->_charNum($hash);

            // stlen check

            // check if exists
            $sql = "SELECT * FROM ".TABLE_ORDERS." WHERE md5(orders_id)=? and feedback_hash=? LIMIT 0,1";
            $rs = $db->Execute($sql,array($ID,$hash));
            if ($rs->RecordCount()==0 or strlen($hash)!=32 or strlen($ID)!=32) {
                // order / id not found
                echo 'count'.$rs->RecordCount();
                $info->_addInfo(TEXT_XT_FEEDBACKPLUS_ERROR_NOT_FOUND, 'error');
            }
            else
            {
            $orders_id = $rs->fields['orders_id'];

            // check if review was already added
            $sql = "SELECT * FROM ".TABLE_FEEDBACKPLUS_LIFE_CIRCLES." WHERE feedback_life_circle_id=?";
            $cs =$db->Execute($sql,array($rs->fields['feedback_life_circle_id']));
                if ($cs->RecordCount()==1 && ($cs->fields['review_submited_date']!=NULL && $cs->fields['review_submited_date']>'2005-00-00 00:00:00'))
                {
                $info->_addInfo(XT_FEEDBACKPLUS_DOUBLE_REVIEW_MESSAGE, 'error');
                }
                else
                {
            // check if we are saving data
            $form_data = array();

            // get products for this order
            $sql = "SELECT * FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id=?";
            $rrs = $db->Execute($sql,array($orders_id));

            $products=array();
                    while (!$rrs->EOF)
                    {
                $p_info = new product($rrs->fields['products_id'], 'default');
                $products[$rrs->fields['products_id']]=array('products_name'=>$p_info->data['products_name'],'products_id'=>$rrs->fields['products_id'],'rating'=>5);
                $rrs->MoveNext();
            }

            if (isset ($_POST['action']) && $_POST['action'] == 'add_reviews') {
                $error = false;



                $insert = array();
                $form_data=array();
                        foreach ($products as $key => $arr)
                        {

                    $rating = (int)$_POST['review_rating_'.$arr['products_id']];
                    $title = $filter->_filter($_POST['review_title_'.$arr['products_id']]);
                    $text = $filter->_filter($_POST['review_text_'.$arr['products_id']]);

                    $form_data = array('review_rating' => $rating,
                        'review_title' => $title,
                        'review_text' => $text);

                    $products[$arr['products_id']]['prefill']=$form_data;
                    $products[$arr['products_id']]['rating']=$rating;

                    $rev = array('review_rating'=>$rating,
                        'review_title'=>$title,
                        'review_text'=>$text,
                        'customers_id'=>$rs->fields['customers_id'],
                        'feedback_life_circle_id'=>$rs->fields['feedback_life_circle_id'],
                        'products_id'=>$arr['products_id'],
                        'orders_id'=>$orders_id,
                                'review_source'=> $campain_testing == 0 ? 'feedback+' : 'feedback+test');

                    if (strlen($title)<5 or strlen($rating)<1 or strlen($text)<5) {
                        $error=true;
                    } else {
                        $insert[]=$rev;
                    }
                }

                        if (!$error)
                        {
                    foreach ($insert as $key => $arr) {
                        $result = $reviews->_addReview($arr,'true');
                    }

                    $feedback = new xt_feedbackplus();
                    $feedback->reviewAdded($rs->fields['feedback_life_circle_id']);

                    // forward success
                    $info->_addInfoSession(XT_FEEDBACKPLUS_REVIEW_SUBMIT_MESSAGE, 'success');
                    $tmp_link = $xtLink->_link(array('page' => 'feedback', 'paction' => 'success'));
                    $xtLink->_redirect($tmp_link);

                        }
                        else
                        {
                    $info->_addInfo(TEXT_XT_REVIEW_FORM_ERROR, 'error');
                }

            }

            }
            }

            $rating_data = array(array('id' => '1', 'text' => '1'), array('id' => '2', 'text' => '2'), array('id' => '3', 'text' => '3'), array('id' => '4', 'text' => '4'), array('id' => '5', 'text' => '5'));

            $tpl_data = array('message' => $info->info_content,'rating' => $rating_data,'products'=>$products);
            $tpl = 'write_review.html';

            $template = new Template();
            $template->getTemplatePath($tpl, 'xt_feedbackplus', '', 'plugin');

            $page_data = $template->getTemplate('xt_write_reviews_smarty', $tpl, $tpl_data);
            break;

        case 'success':

            $tpl_data = array('message' => $info->info_content);
            $tpl = 'success_review.html';


            $template = new Template();
            $template->getTemplatePath($tpl, 'xt_feedbackplus', '', 'plugin');

            $page_data = $template->getTemplate('xt_success_reviews_smarty', $tpl, $tpl_data);

            break;
    }
}
?>