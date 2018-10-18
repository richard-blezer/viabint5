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

/**
 * Return image tag or path
 *
 * $param may contain the following keys:
 * - type: t|m|mi|w|p (explode by "_)
 * - img: image path
 * - plg: plugin path
 * - subdir: subdirectory path
 * - class: class="{class}"
 * - alt: alt="{alt}"
 * - title: titl="{title}"
 * - itemprop: boolean (if true add itemprop="image")
 * - width: image width
 * - height: image height
 * - path_only: boolean (if true return only path)
 * @global type $language
 * @global type $template 
 * @global type $mediaImages
 * @param array $params
 * @param type $smarty 
 * @version 1.0.1 add itemprop,title,alt
 */
function smarty_function_img($params, & $smarty) {
    global $language, $template, $mediaImages;
    
    $type_array = explode("_", $params['type']);
    
    if(_STORE_IMAGES_PATH_FULL == 'true'){
        $path_base_url = _SYSTEM_BASE_URL;
    }else{
        $path_base_url = '';
    }
    
    if ($type_array[0] == 't') {

        $url = $path_base_url . _SRV_WEB . _SRV_WEB_TEMPLATES . $template->selected_template . '/';
    } elseif ($type_array[0] == 'm') {

        $url = $path_base_url . _SRV_WEB . _SRV_WEB_IMAGES;
    } elseif ($type_array[0] == 'mi') {

        $url = $path_base_url . _SRV_WEB . _SRV_WEB_ICONS;
    } elseif ($type_array[0] == 'w') {

        $url = $path_base_url . _SRV_WEB;
    } elseif ($type_array[0] == 'p') {

        $url = $path_base_url .__getImagePath($params['img'], $params['plg'], $params['subdir']);
    } else {
        $url = $params['url'];
    }

    $url = str_replace("/xtAdmin", '', $url);
    /*
      if ($params['img_class'] && !file_exists(_SRV_WEBROOT.'media/images/'.$type_array[1].'/'.$params['img']) && file_exists(_SRV_WEBROOT.'media/images/org/'.$params['img'])) {
      $mediaImages->setClass($params['img_class']);
      $mediaImages->processImage($params['img'], false);
      }
     */

    unset($type_array[0]);

    $file_type = __getFileType($params['img'], $type_array[1]);

    $params['img'] = $file_type['filename'];
    $url .= $file_type['main_dir'];

    if (is_data($type_array)) {
        while (list ($key, $value) = each($type_array)) {
            $url .= $value . '/';
        }
    }

    if (!empty($params['class']))
        $class = ' class="' . $params['class'] . '"';

    if (!empty($params['alt'])) {
        $class .= ' alt="' . $params['alt'] . '"';
    } else {
        // add empty alt tag
        $class .= ' alt=""';
    }


    if (!empty($params['title']))
        $class .= ' title="' . $params['title'] . '"';

    if (!empty($params['itemprop']) && ($params['itemprop'] === true) )
        $class .= ' itemprop="image" ';

    $size = "";

    if (!empty($params['width']) && $params['width'] != '0' &&  $params['width'] != 'auto') {
        if (file_exists(_SRV_WEBROOT . 'media/images/' . $type_array[1] . '/' . $params['img'])) {
            $image_size = getimagesize(_SRV_WEBROOT . 'media/images/' . $type_array[1] . '/' . $params['img']);
            $_height = $image_size[1];
            $_width = $image_size[0];

            $ratio_width = $_width / $params['width'];
            $height = $_height / $ratio_width;

            $size = ' width="' . $params['width'] . '" height="' . $height . '"';
        }
    }
    if (!empty($params['height']) && $params['height'] != '0' && $params['height'] != 'auto') {
        if (file_exists(_SRV_WEBROOT . 'media/images/' . $type_array[1] . '/' . $params['img'])) {
            $image_size = getimagesize(_SRV_WEBROOT . 'media/images/' . $type_array[1] . '/' . $params['img']);
            $_height = $image_size[1];
            $_width = $image_size[0];

            $ratio_width = $_width / $params['width'];
            $height = $_height / $ratio_width;

            $size = ' width="' . $params['width'] . '" height="' . $height . '"';
        }
    }
    if ($size == "") {
        if (file_exists(_SRV_WEBROOT . 'media/images/' . $type_array[1] . '/' . $params['img'])) {
            $image_size = getimagesize(_SRV_WEBROOT . 'media/images/' . $type_array[1] . '/' . $params['img']);
            $_height = $image_size[1];
            $_width = $image_size[0];
            $size = ' width="' . $_width . '" height="' . $_height . '"';
        }
    }

    if (!empty($params['itemprop'])) {
        $itemprop =  ' itemprop="'.$params['itemprop'] . '"';
    }

    //HOOKPOINT `smarty_function_img:params_bottom` - START
    global $xtPlugin;
    ($plugin_code = $xtPlugin->PluginCode('smarty_function_img:params_bottom')) ? eval($plugin_code) : false;
    //HOOKPOINT `smarty_function_img:params_bottom` - END

    $img = '<img src="' . $url . $params['img'] . '"' . $class . $size . $itemprop.' />';

    if (isset($params['path_only'])) {
        echo $url . $params['img'];
    } else {
        echo $img;
    }
}

/**
 * return filepath or false
 * @global type $template
 * @param string $file
 * @param string $dir
 * @param string $subdir
 * @return string|boolean 
 */
function __getImagePath($file, $dir, $subdir = '') {
    global $template;

    if ($subdir)
        $subdir = $subdir . '/';

    $img_root_path = _SRV_WEBROOT . _SRV_WEB_TEMPLATES . $template->selected_template . '/' . _SRV_WEB_PLUGINS . $dir . '/images/' . $subdir;
    $img_path = $path_base_url . _SRV_WEB . _SRV_WEB_TEMPLATES . $template->selected_template . '/' . _SRV_WEB_PLUGINS . $dir . '/images/' . $subdir;

    $img_root_plugin_path = _SRV_WEBROOT . _SRV_WEB_PLUGINS . $dir . '/images/' . $subdir;
    $img_plugin_path = $path_base_url . _SRV_WEB . _SRV_WEB_PLUGINS . $dir . '/images/' . $subdir;

    if (file_exists($img_root_path . $file)) {
        return $img_path;
    } elseif (file_exists($img_root_plugin_path . $file)) {
        return $img_plugin_path;
    } else {
        return false;
    }
}

function __getFileType($img, $type) {

    require_once(_SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.FileHandler.php');

    $tmp_img_data = explode(':', $img);
    $img_type = $tmp_img_data[0];
    $img_name = $tmp_img_data[1];

    $mf = new FileHandler();
    $mf->setParentDir(_SRV_WEB_IMAGES . $img_type . '/' . $type);
    $img_check = $mf->_checkFile($img_name);

    if ($img_check) {
        $img_array = array('main_dir' => $img_type . '/', 'filename' => $img_name);
    } else {

        if (preg_match('/:/', $img))
            $img = $img_name;

        $img_array = array('main_dir' => '', 'filename' => $img);
    }

    return $img_array;
}

?>