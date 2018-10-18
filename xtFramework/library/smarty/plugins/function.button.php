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
 * Generate button.
 * 
 * $param may contain the following keys:
 * - use_lang: none|string use(or not) language code to link
 * - plugin: string link to plugin
 * - text: string button text
 * - file: string button img
 * - space_left: int space characters to the left
 * - space_right: int space characters to the right
 * - type:  form|string type of button
 * - class: string class for button where type!="form"
 * - alt: string alt for button where type!="form"
 * 
 * For type="form" also availiable $params:
 * 
 * class,params,id,type,name,value,size,maxlength,checked,border,src,alt,lowscr,
 * width,height,align,vspace,hspace,readonly,disabled,accesskey,tabindex,
 * language,onclick,onchange,onfocus,onblur,onkeypress,onkeydown,autocomplete
 * 
 * @global type $template
 * @global type $form
 * @global type $language
 * @param array $params
 * @param type $smarty
 * @return string
 * @version 1.0.1 - add alt to all images 
 */
function smarty_function_button($params, & $smarty) {
    global $template, $form, $language;

    $lang_code = $language->environment_language;

    // Select Lang Dir
    if (isset($params['use_lang']) && $params['use_lang'] == 'none') {
        $lang_dir = '';
        $lang_pref = '';
    } else {
        $lang_dir = $lang_code . '/';
        $lang_pref = $lang_code;
    }

    // Select other Dirs
    $config_file = 'button_config.php';
    if (isset($params['plugin'])) {
        $button_root_dir = _SRV_WEBROOT . _SRV_WEB_TEMPLATES . $template->selected_template . '/' . $params['plugin'] . '/imgages/buttons/' . $lang_dir;
        $button_dir = _SRV_WEB_TEMPLATES . $template->selected_template . '/' . $params['plugin'] . '/imgages/buttons/' . $lang_dir;
        $button_source_dir = _SRV_WEBROOT . _SRV_WEB_TEMPLATES . $template->selected_template . '/' . $params['plugin'] . '/imgages/buttons/';
        $config_path_array[0] = _SRV_WEB_TEMPLATES . $template->selected_template . '/' . $params['plugin'] . '/imgages/buttons/';
        $config_path_array[1] = _SRV_WEB_TEMPLATES . $template->selected_template . '/img/buttons/';
        $config_path = _getPath($config_path_array, _SRV_WEBROOT, $config_file);
    } else {
        $button_root_dir = _SRV_WEBROOT . _SRV_WEB_TEMPLATES . $template->selected_template . '/img/buttons/' . $lang_dir;
        $button_dir = _SRV_WEB_TEMPLATES . $template->selected_template . '/img/buttons/' . $lang_dir;
        $button_source_dir = _SRV_WEBROOT . _SRV_WEB_TEMPLATES . $template->selected_template . '/img/buttons/';
        $config_path = _SRV_WEB_TEMPLATES . $template->selected_template . '/img/buttons/';
    }

    if (!file_exists($button_dir))
        mkdir($button_dir, 0755);

    // include button config
    include_once(_SRV_WEBROOT . _SRV_WEB_TEMPLATES . $template->selected_template . '/img/buttons/' . $config_file);

    $text = $params['text'];
    $file = $params['file'];
    $button_template = $params['btn_template'];
    $space_left = _BUTTON_LEFT_SPACE;
    $space_right = _BUTTON_RIGHT_SPACE;

    if (isset($params['space_left'])) {
        $space_left = $params['space_left'];
    }
    if (isset($params['space_right'])) {
        $space_right = $params['space_right'];
    }

    // only generate butto if not exists
    if (!file_exists($button_dir . $file)) {
        if ($button_template == '')
            $button_template = 'tpl_button_1.gif';
        $btn_source = $button_source_dir . $button_template;
        $btn_end_source = $button_source_dir . 'end_' . $button_template;

        if (is_file($btn_source)) {

            // source button
            $btn_source_img = imagecreatefromgif($btn_source);
            $btn_source_height = imagesy($btn_source_img);
            // Create the image

            $size = imagettfbbox(_BUTTON_FONT_SIZE, 0, _BUTTON_FONT, $text);
            $width = $size[2] + $size[0] + $space_left + $space_right;

            $im_des = imagecreate($width, $btn_source_height);
            $colourBlack = imagecolorallocate($im_des, 255, 255, 255);
            imagecolortransparent($im_des, $colourBlack);


            imagecopy($im_des, $btn_source_img, 0, 0, 0, 0, $width, $btn_source_height);



            // copy end image into existing image
            if (is_file($btn_end_source)) {
                $btn_source_img_end = imagecreatefromgif($btn_end_source);
                $btn_end_source_width = imagesx($btn_source_img_end);
                $start = $width - $btn_end_source_width;
                imagecopy($im_des, $btn_source_img_end, $start, 0, 0, 0, $btn_end_source_width, $btn_source_height);
            }


            $font_color = imagecolorallocate($im_des, _BUTTON_FONT_COLOR_R, _BUTTON_FONT_COLOR_G, _BUTTON_FONT_COLOR_B);
            imagettftext($im_des, _BUTTON_FONT_SIZE, 0, $space_left, _BUTTON_FONT_POS_VERTICAL, $font_color, _BUTTON_FONT, $text);


            imagegif($im_des, $button_dir . $file);
        }
    }

    if ($params['type'] == 'form') {
        // draw submit button
        $params['type'] = 'image';
        $params['src'] = $file;
        
        if(empty($params['alt']))
            $params['alt'] = $text;
        
        echo $form->_draw_field($params);
    } else {

        if (!empty($params['class']))
            $class = ' class="' . $params['class'] . '"';

        if (!empty($params['alt'])){
            $class .= ' alt="' . $params['alt'] . '"';
        }else{
            $class .= ' alt="' . $text . '"';
        }

        // generate normal image
        return '<img src="' . _SYSTEM_BASE_URL . _SRV_WEB . $button_dir . $file . '" ' . $class . ' />';
    }

    return;
}

?>