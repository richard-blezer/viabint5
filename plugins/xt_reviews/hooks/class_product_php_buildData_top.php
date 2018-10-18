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
 # @version $Id$
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

if (isset($xtPlugin->active_modules['xt_master_slave']))
{
	switch (XT_REVIEWS_MASTER_SLAVE)
	{
		case 'master_only':
			// If this is a slave - don't display ratings
			if (empty($this->data['products_master_flag']) && ! empty($this->data['products_master_model']))
			{
				return;
			}

			break;
		case 'slave_only':
			// If this is a master - don't display ratings
			if ($this->data['products_master_flag'] OR empty($this->data['products_master_model']))
			{
				return;
			}

			break;
		default:
			break;
	}
}

$reviews = new xt_reviews();

$this->data['review_stars_rating'] = $reviews->getStars($this->data['products_id']);
$this->data['products_rating_count'] = $reviews->getReviewsSum($this->data['products_id']);
$this->data['link_reviews_write'] = $xtLink->_link(array('page' => 'reviews', 'paction' => 'write', 'params' => 'info='.$this->data['products_id']));
$this->data['link_reviews_list'] = $xtLink->_link(array('page' => 'reviews', 'paction' => 'show', 'params' => 'info='.$this->data['products_id']));