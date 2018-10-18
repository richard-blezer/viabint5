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

class client_detect {


    var $mobile_active = _STORE_MOBILE_ACTIVATE;
    var $tablet_is_mobile = _STORE_TABLET_IS_MOBILE;
    var $mobile = false;
    var $tablet = false;
    var $check_type =_STORE_MOBILE_SWITCH_METHOD;


    function __construct() {

        require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'library/mobile-detect/Mobile_Detect.php';
        $this->detect = new Mobile_Detect();

        // check for mobile link parameter
        if (isset($_GET['mobile'])) {
            if ($_GET['mobile']=='true') {
                if ($this->mobile_active==true) $_SESSION['isMobile']=true;
            } else {
                $_SESSION['isMobile']=false;
            }
        }

        if (!isset($_SESSION['isMobile']) && $this->check_type=='auto') {
            $this->_isMobile();
        } else {
            $this->mobile = $_SESSION['isMobile'];
        }

     //   $this->mobile = true;
    }

    /**
     *
     * detect mobile device
     *
     * @return bool
     */
    function _isMobile() {

        if ($this->mobile_active=='false') return false;

        if ($this->detect->isTablet()==true) {
            $this->mobile = false;
            $this->tablet = true;
            if($this->tablet_is_mobile=='true'){
                $_SESSION['isMobile']=true;
            }
            else{
                $_SESSION['isMobile']=false;
            }
        } else {
            if ($this->detect->isMobile()==true) {
                $this->mobile = true;
                $this->tablet = false;
                $_SESSION['isMobile']=true;
            }
        }

    }

    /**
     *
     * detect tablet device
     *
     * @return bool
     */
    function _isTablet() {

        if ($this->tablet_is_mobile=='false') return false;

        if ($this->detect->isTablet()==true) {
            return true;
        } else {
            return false;
        }
    }


    function _getEnvironment() {

        require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'library/browser-detect/agents.php';
        require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'library/browser-detect/Environment.php';
        $environment = new Environment();

        $data = $environment->agent();

        $this->client_details = $data;


    }


}



?>