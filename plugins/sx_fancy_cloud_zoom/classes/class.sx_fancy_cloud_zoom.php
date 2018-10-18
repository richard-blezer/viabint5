<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');
	
class fancy_cloud_zoom {
	
	function _getFancyCloudZoom() {
        global $xtMinify;

        $czMobileCheck = (!array_key_exists('mobile', $_GET) && $_SESSION['isMobile']!=true) || SX_FANCY_CLOUD_ZOOM_CZ_ENABLED_ON_MOBILE=='true';
        $fbMobileCheck = (!array_key_exists('mobile', $_GET) && $_SESSION['isMobile']!=true) || SX_FANCY_CLOUD_ZOOM_FB_ENABLED_ON_MOBILE=='true';




        // fancy nur laden wenn aktiviert
        if (SX_FANCY_CLOUD_ZOOM_FB_ENABLED == 'true' && $fbMobileCheck)
        {
            echo "
        	<link href='"._SRV_WEB._SRV_WEB_PLUGINS ."sx_fancy_cloud_zoom/javascript/fancybox/jquery.fancybox-1.3.4.css' type='text/css' rel='stylesheet'>
        	";
            $xtMinify->add_resource(_SRV_WEB_PLUGINS . 'sx_fancy_cloud_zoom/javascript/fancybox/jquery.xt-fancybox-1.3.4.js',131);
            $xtMinify->add_resource(_SRV_WEB_PLUGINS . 'sx_fancy_cloud_zoom/javascript/init-fancybox.js',132);
        }
        else {
            // optionen für cloudzomm setzen
            $xtMinify->add_resource(_SRV_WEB_PLUGINS . 'sx_fancy_cloud_zoom/javascript/cloud-zoom-disable-fancybox.js',130);
        }

        // zoomCloud verarbeitet IMMER die more_images ...
        $xtMinify->add_resource(_SRV_WEB_PLUGINS . 'sx_fancy_cloud_zoom/javascript/xt-cloud-zoom.js',140);

        // in abhängigkeit ob zoomCloud aktiviert werden verschiedne optionen geladen
        if (SX_FANCY_CLOUD_ZOOM_CZ_ENABLED == 'false' || !$czMobileCheck)
        {
            $xtMinify->add_resource(_SRV_WEB_PLUGINS . 'sx_fancy_cloud_zoom/javascript/cloud-zoom-disable-zoom.js',141);
        }
        // initailiserung cloudzomm mit den options von oben
        $xtMinify->add_resource(_SRV_WEB_PLUGINS . 'sx_fancy_cloud_zoom/javascript/init-cloud-zoom.js',142);

	}
}