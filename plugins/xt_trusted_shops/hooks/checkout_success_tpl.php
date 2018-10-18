<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/trusted_shops_ui_settings.php';

global $language, $store_handler;
$uiSettings = trusted_shops_ui_settings::getSettings($language->content_language, SHOP_DOMAIN_HTTP);

if ($uiSettings[COL_TS_CERTS_SHOW_BADGE])
{
    require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/class.xt_trusted_shops_certificates.php';

    global $success_order;
    if($success_order)
    {
        // html snippet für trusted shops popup auf checkout-success
        $tpl = 'trusted_shops_protection_offer.tpl.html';
        $template = new Template();
        $template->getTemplatePath($tpl, 'xt_trusted_shops', '', 'plugin');
        $tp_data = array(
            'tsCheckoutOrderNr' => $success_order->oID,
            'tsCheckoutBuyerEmail' => $success_order->order_customer['customers_email_address'],
            'tsCheckoutBuyerId' => $success_order->order_customer['customers_id'],
            'tsCheckoutOrderAmount' => $success_order->order_total['total']['plain'],
            'tsCheckoutOrderCurrency' => $success_order->order_data['currency_code'],
            'tsCheckoutOrderPaymentType' => trusted_shops_certificates::mapPaymentMethod($success_order->order_data['payment_code']),
            'tsCheckoutOrderEstDeliveryDate' => '' // optional
        );
        $tpl_html = $template->getTemplate('', $tpl, $tp_data);
        echo $tpl_html;
    }
}

if ($uiSettings[COL_TS_CERTS_RATING_ENABLED])
{
    require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/class.xt_trusted_shops_certificates.php';

    global $success_order, $language;

    if($success_order)
    {
        // html snippet für trusted shops popup auf checkout-success
        $tpl = 'trusted_shops_rate_shop.tpl.html';
        $template = new Template();
        $template->getTemplatePath($tpl, 'xt_trusted_shops', '', 'plugin');

        $lng = 'en';

        $urlLater = '';
        $langAvail = array('de','en','es','fr','it','nl','pl');
        if (in_array($language->content_language, $langAvail))
        {
            $lng = $language->content_language;
        }

        $tsId = $uiSettings[COL_TS_CERTS_KEY];
        $orderNr = urlencode(base64_encode($success_order->oID));
        $buyerEmail = urlencode(base64_encode($success_order->order_customer['customers_email_address']));
        $orderDate = urlencode(base64_encode(date("Y-m-d", strtotime($success_order->order_data['date_purchased']))));
        $imgPathRate = _SRV_WEB._SRV_WEB_PLUGINS.'xt_trusted_shops/images/buttons/';
        $daysRateLater = $uiSettings[COL_TS_CERTS_RATE_LATER_AFTER];

        $urlNow = 'https://www.trustedshops.com/buyerrating/';
        $urlLater = 'https://www.trustedshops.com/reviews/rateshoplater.php?';
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
        $urlLater .= "shop_id={$tsId}.html&buyerEmail={$buyerEmail}&shopOrderID={$orderNr}&orderDate={$orderDate}&days=$daysRateLater";

        $tp_data = array(
            'tsId' => $uiSettings[COL_TS_CERTS_KEY],
            'tsOrderNr' => $orderNr,
            'tsBuyerEmail' => $buyerEmail,
            'tsOrderDate' => $orderDate,
            'tsImgPathRate' => $imgPathRate,
            'tsImgLngRate' => $lng,
            'urlNow' => $urlNow,
            'urlLater' => $urlLater
        );
        $tpl_html = $template->getTemplate('', $tpl, $tp_data);
        echo $tpl_html;
    }
}