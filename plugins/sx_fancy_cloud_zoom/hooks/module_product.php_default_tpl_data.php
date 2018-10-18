<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if (SX_FANCY_CLOUD_ZOOM_ENABLED && array_key_exists('page', $_GET) && $_GET['page']=='product')
{
    define('SX_FANCY_CLOUD_ZOOM_DISPLAY', SX_FANCY_CLOUD_ZOOM_FB_ENABLED || SX_FANCY_CLOUD_ZOOM_CZ_ENABLED);
    $tpl_data['diplayFancyCloud'] = SX_FANCY_CLOUD_ZOOM_FB_ENABLED || SX_FANCY_CLOUD_ZOOM_CZ_ENABLED || (is_array($tpl_data['more_images']) && sizeof($tpl_data['more_images']>0));
}