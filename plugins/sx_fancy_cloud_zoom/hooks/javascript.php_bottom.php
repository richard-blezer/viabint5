<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if(SX_FANCY_CLOUD_ZOOM_ENABLED)
{
    require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'sx_fancy_cloud_zoom/classes/class.sx_fancy_cloud_zoom.php';
    $cloud_zoom = new fancy_cloud_zoom();
    $cloud_zoom->_getFancyCloudZoom();
}