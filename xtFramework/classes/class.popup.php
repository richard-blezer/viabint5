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


class popup
{
    public $resizeable = true;
    public $scrollbars = true;
    public $toolbar = false;
    public $height = 600;
    public $width = 600;
    public $target = '_blank';

    function getPopupLink($link, $link_text)
    {

        ($this->toolbar) ? $toolbar = '1' : $toolbar = '0';
        ($this->scrollbars) ? $scrollbars = 'yes' : $scrollbars = 'no';
        ($this->resizeable) ? $resizeable = 'yes' : $resizeable = 'no';

        $link = '<a href="' . $link . '" class="popuplink" rel="nofollow" target="_blank" onclick="window.open(\'' . $link . '\', \'popup\', \'toolbar=' . $toolbar . ', scrollbars=' . $scrollbars . ', resizable=' . $resizeable . ', height=' . $this->height . ', width=' . $this->width . '\');return false;">' . $link_text . '</a>';
        return $link;
    }
}