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

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/constants.php';

class trusted_shops_ui_settings
{
    private $_settings = array(
        COL_TS_CERTS_KEY => '',
        COL_TS_CERTS_TYPE => '',
        COL_TS_CERTS_STATE => '',
        COL_TS_CERTS_LANG => '',
        COL_TS_CERTS_SHOW_BADGE => false,
        COL_TS_CERTS_SHOW_BADGE_POS => '0',
        COL_TS_CERTS_SHOW_SEAL => false,
        COL_TS_CERTS_SHOW_RATING => false,
        COL_TS_CERTS_SHOW_VIDEO => false,
        'rating_url' => '',
        'domain_tld' => '',
        COL_TS_CERTS_RATING_ENABLED => false,
        COL_TS_CERTS_SHOW_RICH_SNIPPETS => false,
        COL_TS_CERTS_RATE_LATER_AFTER => TS_DEF_RATE_LATER_AFTER
    );
    private static $_instance = null;

    private $_cert = null;

    public static function getSettings($lang, $domain, $params = array(), $forceInit = false)  // params um evtl werte zu überschreiben zb seal logo
    {
        $settingsOrig = self::$_instance->_settings;

        if (self::$_instance == null || $forceInit)
        {
            self::$_instance = new trusted_shops_ui_settings();
            if (!empty($lang) || !empty($domain))
            {
                self::$_instance->init($lang, $domain);
            }
        }
        $settings = self::$_instance->_settings;
        if (!empty($params[COL_TS_CERTS_SHOW_SEAL]))
        {
            $settings[COL_TS_CERTS_SHOW_SEAL] = self::$_instance->buildShowSeal(self::$_instance->_cert, $params[COL_TS_CERTS_SHOW_SEAL]);
        }
        if (!empty($params[COL_TS_CERTS_SHOW_VIDEO]))
        {
            $settings[COL_TS_CERTS_SHOW_VIDEO] = self::$_instance->buildShowVideo(self::$_instance->_cert, $params[COL_TS_CERTS_SHOW_VIDEO]);
        }
        if ($forceInit)
        {
            // rollback
            self::$_instance->_settings = $settingsOrig;
        }
        return $settings;
    }

    private function trusted_shops_ui_settings()
    {
    }

    private function init($lang, $domain)
    {
        global $db;

        $certs = array();
        // sprache berücksichtigen // nicify
        $sql = ("SELECT COUNT(`".COL_TS_CERTS_LANG."`) FROM `".TABLE_TS_CERTIFICATES."` WHERE `".COL_TS_CERTS_URL."` LIKE '$domain%' AND ".COL_TS_CERTS_LANG."='$lang'");
        $langCertsCount = $db->GetOne($sql);
        if ($langCertsCount)
        {
            $sql = ("SELECT * FROM `".TABLE_TS_CERTIFICATES."` WHERE `".COL_TS_CERTS_URL."` LIKE '$domain%' AND ".COL_TS_CERTS_LANG."='$lang'");
        }
        else{
            return;
        }
        $row = $db->Execute($sql);
        while(!$row->EOF)
        {
            $certs[] = $row->fields;
            $row->MoveNext();
        }
        $row->Close();
        usort($certs, array($this,'sortByUrlPathPartsCount'));
        // url mit meisten parts gewinnt
        // also www.shop.de/en vor www.shop.de
        //$script_uri = parse_url($_SERVER['SCRIPT_URI']);
        //$hostPath = $script_uri['host'].$script_uri['path'];
        foreach($certs as $cert)
        {
            if(0 === strpos($domain, $cert[COL_TS_CERTS_URL]))
            {
                $this->buildDisplaySetting($cert);
                break;
            }
        }
    }

    private function buildDisplaySetting($cert)
    {
        if (!empty($cert[COL_TS_CERTS_KEY]))
        {
            global $db;

            $this->_cert = $cert;

            $this->_settings[COL_TS_CERTS_KEY] = $cert[COL_TS_CERTS_KEY];
            $state = $this->_settings[COL_TS_CERTS_STATE] = $cert[COL_TS_CERTS_STATE];
            $type = $this->_settings[COL_TS_CERTS_TYPE] = $cert[COL_TS_CERTS_TYPE];

            $this->_settings[COL_TS_CERTS_RATING_ENABLED] = $cert[COL_TS_CERTS_RATING_ENABLED];
            $this->_settings[COL_TS_CERTS_SHOW_RICH_SNIPPETS] = $cert[COL_TS_CERTS_SHOW_RICH_SNIPPETS];

            $this->_settings[COL_TS_CERTS_SHOW_BADGE] =
                ($state === TS_CERT_STATE_TEST || $state === TS_CERT_STATE_INTEGRATION || $state === TS_CERT_STATE_PRODUCTION)
                &&
                ($type === TS_CERT_TYPE_CLASSIC || $type === TS_CERT_TYPE_MIGRATION || $type === TS_CERT_TYPE_EXCELLENCE)
                &&
                $cert[COL_TS_CERTS_SHOW_BADGE] != '0' ? $cert[COL_TS_CERTS_SHOW_BADGE] : false;

            $this->_settings[COL_TS_CERTS_SHOW_RATING] =
                ($state === TS_CERT_STATE_TEST || $state === TS_CERT_STATE_INTEGRATION || $state === TS_CERT_STATE_PRODUCTION)
                &&
                ($type === TS_CERT_TYPE_CLASSIC || $type === TS_CERT_TYPE_MIGRATION || $type === TS_CERT_TYPE_EXCELLENCE)
                &&
                $cert[COL_TS_CERTS_SHOW_RATING] != '0';

            $this->_settings[COL_TS_CERTS_SHOW_SEAL] = $this->buildShowSeal($cert);

            $this->_settings[COL_TS_CERTS_SHOW_VIDEO] = $this->buildShowVideo($cert);

            $this->_settings[COL_TS_CERTS_LANG] = $cert[COL_TS_CERTS_LANG];

            $tld = 'com';
            switch($cert[COL_TS_CERTS_LANG])
            {
                case 'de':
                    $tld = 'de';
                    break;
                case 'en':
                    $tld = 'co.uk';
                    break;
                case 'es':
                    $tld = 'es';
                    break;
                case 'fr':
                    $tld = 'fr';
                    break;
                case 'pl':
                    $tld = 'pl';
                    break;
            }
            $this->_settings['domain_tld'] = $tld;

            $ratingUrl = constant('TS_URL_RATING_'.strtoupper($cert[COL_TS_CERTS_LANG]));
            $ratingUrl = str_replace('TS_KEY', $cert[COL_TS_CERTS_KEY], $ratingUrl);
            $this->_settings['rating_url'] = $ratingUrl;

            $this->_settings[COL_TS_CERTS_SHOW_BADGE_POS] = $cert[COL_TS_CERTS_SHOW_BADGE_POS];

            $this->_settings[COL_TS_CERTS_RATE_LATER_AFTER] = $cert[COL_TS_CERTS_RATE_LATER_AFTER];
            $sql = "SELECT `language_value` FROM ".TABLE_LANGUAGE_CONTENT." WHERE `language_key`='TEXT_TPL_TS_RATE_LATER_INFO' AND `language_code`='".$cert[COL_TS_CERTS_LANG]."'";
            $txt = $db->GetOne($sql);
            if ($txt)
            {
                $txt = str_replace('{rate_after_days}', $cert[COL_TS_CERTS_RATE_LATER_AFTER], $txt);
                define('TEXT_TS_RATE_LATER_INFO', $txt);
            }

        }
    }

    private function buildShowSeal($cert, $seal=false)
    {
        $state = $cert[COL_TS_CERTS_STATE];
        $type = $cert[COL_TS_CERTS_TYPE];
        $show =
            ($state === TS_CERT_STATE_TEST || $state === TS_CERT_STATE_INTEGRATION || $state === TS_CERT_STATE_PRODUCTION)
            &&
            ($type === TS_CERT_TYPE_CLASSIC || $type === TS_CERT_TYPE_MIGRATION || $type === TS_CERT_TYPE_EXCELLENCE)
            &&
            ($cert[COL_TS_CERTS_SHOW_SEAL] != '0' && !empty($cert[COL_TS_CERTS_SHOW_SEAL])) ?
                $seal ? _SRV_WEB._SRV_WEB_PLUGINS.'xt_trusted_shops/images/seal/'.$seal :
                    _SRV_WEB._SRV_WEB_PLUGINS.'xt_trusted_shops/images/seal/'.$cert[COL_TS_CERTS_SHOW_SEAL] :
                false;
        return $show;
    }

    private function buildShowVideo($cert, $video=false)
    {
        $state = $cert[COL_TS_CERTS_STATE];
        $type = $cert[COL_TS_CERTS_TYPE];
        $show =
            ($state === TS_CERT_STATE_TEST || $state === TS_CERT_STATE_INTEGRATION || $state === TS_CERT_STATE_PRODUCTION)
            &&
            ($type === TS_CERT_TYPE_CLASSIC || $type === TS_CERT_TYPE_MIGRATION || $type === TS_CERT_TYPE_EXCELLENCE)
            &&
            ($cert[COL_TS_CERTS_SHOW_VIDEO] != '0' && !empty($cert[COL_TS_CERTS_SHOW_VIDEO])) ?
                $video ? _SRV_WEB._SRV_WEB_PLUGINS.'xt_trusted_shops/images/video/'.$video :
                    _SRV_WEB._SRV_WEB_PLUGINS.'xt_trusted_shops/images/video/'.$cert[COL_TS_CERTS_SHOW_VIDEO] :
                false;
        return $show;
    }

    private function sortByUrlPathPartsCount($a, $b)
    {
        $a_path = parse_url('http://'.$a[COL_TS_CERTS_URL], PHP_URL_PATH); // schema omit erst ab PHP 5.4.7
        $a_pathPartsCount = $this->countPathPart($a_path);
        $b_path = parse_url('http://'.$b[COL_TS_CERTS_URL], PHP_URL_PATH);
        $b_pathPartsCount = $this->countPathPart($b_path);

        return $a_pathPartsCount < $b_pathPartsCount;
    }

    private function countPathPart($path)
    {
        if (empty($path) || $path=='/') return 0;

        // anzahl '/' finden. dabei letztes zeichen ignorieren, könnte ein idF unnötiges '/' finden
        $c = substr_count($path,'/',0, strlen($path)-2);
        return $c;
    }

} 