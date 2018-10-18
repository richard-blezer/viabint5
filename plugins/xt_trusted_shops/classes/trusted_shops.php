<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2014 xt:Commerce International Ltd. All Rights Reserved.
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


class trusted_shops
{
    public static function getXml($tsId)
    {
        if (!self::ts_cachecheck($filename = _SRV_WEBROOT.'cache/'. $tsId.".xml", 43200)) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, false);
            $url = "https://www.trustedshops.com/bewertung/show_xml.php?tsid=" . $tsId;
            curl_setopt($ch, CURLOPT_URL, $url);
            $output = curl_exec($ch);
            curl_close($ch);
            file_put_contents($filename, $output);
        }
        if ($xml = simplexml_load_file($filename))
        {
            return $xml;
        }
        return false;
    }

    public static function ts_cachecheck($filename_cache, $timeout = 10800)
    {
        if (file_exists($filename_cache))
        {
            $timestamp = filemtime($filename_cache); // Seconds
            if (time() - $timestamp < $timeout) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

} 