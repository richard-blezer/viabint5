<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/trusted_shops_ui_settings.php';

global $language;

if($this->oID)
{
    require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/class.xt_trusted_shops_certificates.php';

    global $language, $db;

    $domain = '';
    $sql = "SELECT * FROM ".TABLE_MANDANT_CONFIG." WHERE `shop_id`='".$this->order_data['shop_id']."'";
    $row = $db->Execute($sql);
    if (!$row->EOF)
    {
        $domain = $row->fields['shop_domain'];
    }
    $row->Close();
    $uiSettings = trusted_shops_ui_settings::getSettings($this->order_data['language_code'], $domain, array(), true);

    if ($uiSettings[COL_TS_CERTS_RATING_ENABLED])
    {
        $lng = 'en';

        $urlLater = '';
        $langAvail = array('de','en','es','fr','it','nl','pl');
        if (in_array($this->order_data['language_code'], $langAvail))
        {
            $lng = $this->order_data['language_code'];
        }

        $tsId = $uiSettings[COL_TS_CERTS_KEY];
        $orderNr = urlencode(base64_encode($this->oID));
        $buyerEmail = urlencode(base64_encode($this->order_customer['customers_email_address']));
        $orderDate = urlencode(base64_encode(date("Y-m-d", strtotime($this->order_data['date_purchased']))));
        $imgPathRate = _SRV_WEB_PLUGINS.'xt_trusted_shops/images/buttons/';
        $rateAfter = $uiSettings[COL_TS_CERTS_RATE_LATER_AFTER];

        $urlNow = 'https://www.trustedshops.com/buyerrating/';
        $urlLater = 'http://www.trustedshops.com/reviews/rateshoplater.php?';
        /*
        switch($lng)
        {
            case 'de':
                $urlNow = 'https://www.trustedshops.de/bewertung/';
                $urlLater = 'http://www.trustedshops.de/bewertung/rateshoplater.php?';
                break;
            case 'en':
                $urlNow = 'https://www.trustedshops.com/buyerrating/';
                $urlLater = 'http://www.trustedshops.com/buyerrating/rateshoplater.php?';
                break;
            case 'es':
                $urlNow = 'https://www.trustedshops.es/evaluacion/';
                $urlLater = 'http://www.trustedshops.es/evaluacion/rateshoplater.php?';
                break;
            case 'fr':
                $urlNow = 'https://www.trustedshops.fr/evaluation/';
                $urlLater = 'http://www.trustedshops.fr/evaluation/rateshoplater.php?';
                break;
            case 'pl':
                $urlNow = 'https://www.trustedshops.pl/opinia/';
                $urlLater = 'http://www.trustedshops.pl/opinia/rateshoplater.php?';
                break;
        }
        */
        $urlNow .=   "rate_{$tsId}.html&buyerEmail={$buyerEmail}&shopOrderID={$orderNr}&orderDate={$orderDate}";
        $urlLater .= "shop_id={$tsId}.html&buyerEmail={$buyerEmail}&shopOrderID={$orderNr}&orderDate={$orderDate}&days=$rateAfter";

        $tp_data = array(
            'tsId' => $uiSettings[COL_TS_CERTS_KEY],
            'tsOrderNr' => $orderNr,
            'tsBuyerEmail' => $buyerEmail,
            'tsOrderDate' => $orderDate,
            'tsImgLngRate' => $lng,
            'tsImgPathRate' => $imgPathRate,
            'urlNow' => $urlNow,
            'urlLater' => $urlLater,
            'rateAfterDays' => $rateAfter
        );
        $ordermail->Template->_tpl_vars['trusted_shops_data'] = $tp_data;
    }
}
