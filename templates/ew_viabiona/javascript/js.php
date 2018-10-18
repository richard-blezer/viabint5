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
 # @version $Id: class.acl_area.php 4618 2011-03-30 17:07:54Z mzanier $
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

use ew_viabiona\plugin as ew_viabiona_plugin;

$xtMinify->add_resource(_SRV_WEB_TEMPLATES . _STORE_TEMPLATE . '/javascript/script.js', 100);
$xtMinify->add_resource(_SRV_WEB_TEMPLATES . _STORE_TEMPLATE . '/javascript/affix.js', 110);
$xtMinify->add_resource(_SRV_WEB_TEMPLATES . _STORE_TEMPLATE . '/javascript/mobile.js', 120);
?>

<script type="text/javascript">
    /* <![CDATA[ */
    //language vars
    var TEXT_EW_VIABIONA_STILL = '<?php echo TEXT_EW_VIABIONA_STILL ?>';
    var TEXT_EW_VIABIONA_CHARACTERS_AVAILABLE = '<?php echo TEXT_EW_VIABIONA_CHARACTERS_AVAILABLE ?>';

    //config
    var CONFIG_EW_VIABIONA_PLUGIN_ANIMATIONS = <?php echo ew_viabiona_plugin::check_conf('CONFIG_EW_VIABIONA_PLUGIN_ANIMATIONS') ? 'true' : 'false' ?>;
    var CONFIG_EW_VIABIONA_PLUGIN_FLOATINGNAVIGATION = <?php echo ew_viabiona_plugin::check_conf('CONFIG_EW_VIABIONA_PLUGIN_FLOATINGNAVIGATION') ? 'true' : 'false' ?>;
    var CONFIG_EW_VIABIONA_PLUGIN_SIDEBUTTONS = <?php echo ew_viabiona_plugin::check_conf('CONFIG_EW_VIABIONA_PLUGIN_SIDEBUTTONS') ? 'true' : 'false' ?>;
    var CONFIG_EW_VIABIONA_PLUGIN_FLOATING = <?php echo ew_viabiona_plugin::check_conf('CONFIG_EW_VIABIONA_PLUGIN_FLOATING') ? 'true' : 'false' ?>;
    var CONFIG_EW_VIABIONA_PLUGIN_SOCIALSHARE =  <?php echo ew_viabiona_plugin::check_conf('CONFIG_EW_VIABIONA_PLUGIN_ALTERNATIVE_SHAREBUTTONS') ? 'true' : 'false' ?>;
    var CONFIG_EW_VIABIONA_PLUGIN_MEGANAV =  <?php echo ew_viabiona_plugin::check_conf('CONFIG_EW_VIABIONA_PLUGIN_MEGANAV') ? 'true' : 'false' ?>;
    var CONFIG_EW_VIABIONA_PLUGIN_RANDOM_TEASER =  <?php echo ew_viabiona_plugin::check_conf('CONFIG_EW_VIABIONA_PLUGIN_RANDOM_TEASER') ? 'true' : 'false' ?>;
    var CONFIG_EW_VIABIONA_PLUGIN_URL = '<?php echo _SRV_WEB_PLUGINS . _STORE_TEMPLATE . '_plugin'; ?>';
    /* ]]> */
</script>
<!-- BEGIN Mailchimp koppelungs kode-->
<script id="mcjs">!function(c,h,i,m,p){m=c.createElement(h),p=c.getElementsByTagName(h)[0],m.async=1,m.src=i,p.parentNode.insertBefore(m,p)}(document,"script","https://chimpstatic.com/mcjs-connected/js/users/fa5c25e99239a3bdb640d582a/3dd9b74a46998a635887e1142.js");</script>

<!-- END Mailchimp koppelungs kode-->

<!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-5RZVHS3');</script>
<!-- End Google Tag Manager -->

<!-- Begin Cookie Consent plugin 2018 rblezer -->
<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.css" />
<script src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.js"></script>
<script>
    window.addEventListener("load", function(){
        window.cookieconsent.initialise({
            "palette": {
                "popup": {
                    background: 'RGBA(220,220,220,0.8)', text:  '#454545'},
                button : {background : '#009901', color: '#454545' }
            },
            "theme" : "edgless",
            "content": {
                "message": "Viabiona verwendet Cookies, um Ihnen den bestmöglichen Service zu gewährleisten. Wenn Sie auf unsere Seite bleiben möchten, stimmen Sie der Cookie-Nutzung zu.",
                "dismiss": "OK!",
                "allow": 'Einverstanden',
                "deny": "Ablehnen",
                "link": "Cookie-Informationen",
                "href": "https://www.viabiona.com/de/cookie-consent"
            }

        })});
</script>
<!-- End Cookie Consent plugin 2018 rblezer -->

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
