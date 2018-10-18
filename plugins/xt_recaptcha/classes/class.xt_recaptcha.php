<?php
/*
 #########################################################################
 #                       xt:Commerce 5 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2016 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce 5 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
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

class xt_recaptcha
{
	var $api_url = 'https://www.google.com/recaptcha/api/siteverify';

    function __construct ()
    {
        $this->showCaptcha = _STORE_CAPTCHA;
        $this->publickey = XT_RECAPTCHA_PUBLICKEY;
        $this->privatekey = XT_RECAPTCHA_PRIVATEKEY;
        $this->theme = XT_RECAPTCHA_THEME;
        $this->size = XT_RECAPTCHA_SIZE;
        $this->isInvisible = XT_RECAPTCHA_INVISIBLE == 1 ? true : false;
    }

    public function isShowReCaptcha ()
    {
        if ($this->showCaptcha == 'ReCaptcha' && trim($this->publickey) != '' && trim($this->privatekey) != '')
            return true;
        else
            return false;
    }

    public function isInvisible ()
    {
        return $this->isInvisible;
    }

    public function getSize ()
    {
        return $this->size;
    }

    public function getPublickey ()
    {
        return trim($this->publickey);
    }

    public function getPrivatekey ()
    {
        return trim($this->privatekey);
    }

    public function getLang ()
    {
        global $language;
        if(empty($language))
        {
            $g_lng = 'de';
        }
        else {
            $g_lng = $language->code;
        }
        return strtolower($g_lng);
    }

    public function getTheme ()
    {
        return $this->theme;
    }
    
    public function verifyResponse($response)
    {
        if(strlen($response)>31)
        {
            $url = $this->api_url . "?secret=" . $this->privatekey . "&response=" . $response;

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);

            $curlData = curl_exec($curl);
            curl_close($curl);

            $res = json_decode($curlData, true);
            if ($res['success'])
            {
                return array('success' => true, 'error' => false);
            }
        }
    	return array('success' => false, 'error' => XT_RECAPTCHA_CHECK_FAILED);
    	
    }
}
