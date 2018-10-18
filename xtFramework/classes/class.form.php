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

class form
{

    public $_is_admin;

    function form ($admin = false)
    {
        if ($admin == true) {
            $this->_is_admin = true;
        }
    }

    function _setPos ($admin = false)
    {
        if ($admin == true) {
            $this->_is_admin = true;
        }
    }

    function _draw_form ($data)
    {
        global $xtLink, $page;

        if (!is_data($data))
            return false;

        $data['plain_name'] = $data['name'];
        $data['name'] = $data['name'] . rand(0, 500);

        if (!is_data($data['conn']))
            $data['conn'] = 'NOSSL';

        unset($data['plain_name']);

        $fdata = $data;

        $form_data = '<form ';

        while (list ($key, $value) = each($fdata)) {

            if ($key != 'params' && $key != 'action' && $key != 'link_params' && $key != 'paction' && $key != 'conn') {
                $form_data .= $key . '="' . $value . '" ';
            } elseif ($key == 'action') {

                if ($fdata['link_params'] == 'getParams') {
                    $params = $xtLink->_getParams(array('page_action'));
                    //$params = $xtLink->_getParams();
                } else {
                    $params = $fdata['link_params'];
                }

                if ($value == 'dynamic') {
                    $value = $page->page_name;
                } else {
                    $value = $value;
                }

                if ($this->_is_admin == true) {
                    $form_data .= $key . '="' . $xtLink->_adminlink(array('page' => $value, 'paction' => $fdata['paction'], 'params' => $params, 'conn' => $fdata['conn'])) . '" ';
                } else {
                    $form_data .= $key . '="' . $xtLink->_link(array('page' => $value, 'paction' => $fdata['paction'], 'params' => $params, 'conn' => $fdata['conn'])) . '" ';
                }

            } else {

                if ($key != 'link_params' && $key != 'paction' && $key != 'conn')
                    $form_data .= $value . ' ';

                if($key == 'novalidate' && $value == true){
                    $form_data .= $key.' ';
                }
            }
        }

        $form_data .= '>';

        return $form_data;

    }

    function _draw_field ($data)
    {

        if (!is_data($data))
            return false;



        if ($data['type']=='text' || $data['type']=='hidden' || $data['type']=='password' || $data['type']=='checkbox' || $data['type']=='radio' || $data['type']=='selection' || $data['type']=='file' || $data['type']=='email' || $data['type']=='tel' || $data['type']=='search' || $data['type']=='number' || $data['type']=='url' || $data['type']=='date' || $data['type']=='color' || $data['type']=='time' || $data['type']=='range'){
                $field_data = $this->_draw_input($data);
        }

        if ($data['type'] == 'select') {
            $field_data = $this->_draw_pull_down($data);
        }

        if ($data['type'] == 'textarea') {
            $field_data = $this->_draw_textarea($data);
        }

        if ($data['type'] == 'submit') {
            $field_data = $this->_draw_submitbutton($data);
        }

        if ($data['type'] == 'image') {
            $field_data = $this->_draw_imagebutton($data);
        }

        return $field_data;
    }

    function _draw_input ($data)
    {

        if (!is_data($data))
            return false;

        if (!is_data($data['id']) && !preg_match('/product/', $data['name']) && !preg_match('/qty/', $data['name'])) {
            $data['id'] = $data['name'] . rand(0, 500);

            $data['id'] = str_replace('[', '_', $data['id']);
            $data['id'] = str_replace(']', '_', $data['id']);
        }

        $fdata = $data;

        $allowed_params = array('class', 'params', 'id', 'style', 'type', 'name', 'value', 'size', 'maxlength', 'checked', 'border', 'src', 'alt', 'lowscr', 'width', 'height', 'align', 'vspace', 'hspace', 'readonly', 'disabled', 'accesskey', 'tabindex', 'language', 'onclick', 'onchange', 'onfocus', 'onblur', 'onkeypress', 'onkeydown', 'autocomplete');

        $field_data = '<input ';

        while (list ($key, $value) = each($fdata)) {
            if (in_array(strtolower($key), $allowed_params)) {
                if ($key != 'params') {
                    $field_data .= $key . '="' . $value . '" ';
                } else {
                    $field_data .= $value . ' ';
                }
            }
        }

        $field_data .= ' />';

        if (is_data($fdata['required']))
            $field_data .= TEXT_FIELD_REQUIRED;

        if (is_data($fdata['note']))
            $field_data .= ' ' . $fdata['note'];

        return $field_data;
    }

    function _draw_pull_down ($data)
    {

        if (!is_data($data))
            return false;

        if (!is_data($data['id']))
            $data['id'] = $data['name'] . rand(0, 500);

        $fdata = $data;

        $allowed_params = array('class', 'params', 'name', 'multiple', 'size', 'readonly', 'disabled', 'language', 'onchange', 'tabindex', 'onfocus', 'onblur');

        $field_data = '<select ';

        while (list ($key, $value) = each($fdata)) {
            if (in_array(strtolower($key), $allowed_params)) {
                if ($key != 'value' || $key != 'default' || $key != 'required' || $key != 'note' || $key != 'type') {
                    if ($key != 'params') {
                        $field_data .= $key . '="' . $value . '" ';
                    } else {
                        $field_data .= $value . ' ';
                    }
                }
            }
        }

        $field_data .= '>';
        reset($fdata);
        foreach ($fdata['value'] as $dkey => $dval) {

            if ($dval['sort_group'] != '') {
                $sort_groups = true;
                if ($actual_sort_group == '' || $actual_sort_group != $dval['sort_group']) {
                    if ($actual_sort_group == '') {
                        $fields_data .= '<optgroup label="' . $dval['sort_group'] . '">';
                    } else {
                        $fields_data .= '</optgroup><optgroup label="' . $dval['sort_group'] . '">';
                    }
                    $actual_sort_group = $dval['sort_group'];
                }
            }

            $fields_data .= '<option ';
            if ($dval['style']) {
                $fields_data .= 'style="' . $dval['style'] . '"';
            }
            $fields_data .= 'value="' . $dval['id'] . '" ';

            if ($fdata['default'] == $dval['id']) {
                $fields_data .= 'selected="selected" ';
            }

            $fields_data .= '>';
            $fields_data .= $dval['text'];
            $fields_data .= '</option>';

        }

        if ($sort_groups == true) {
            $fields_data .= '</optgroup>';
        }

        $field_data .= $fields_data;

        $field_data .= '</select>';

        if (is_data($fdata['required']))
            $field_data .= TEXT_FIELD_REQUIRED;

        if (is_data($fdata['note']))
            $field_data .= ' ' . $data['note'];

        return $field_data;
    }


    function _draw_textarea ($data)
    {

        if (!is_data($data))
            return false;

        if (!is_data($data['id']))
            $data['id'] = $data['name'] . rand(0, 500);

        $fdata = $data;

        $allowed_params = array('class', 'params', 'id', 'name', 'cols', 'rows', 'wrap', 'readonly', 'disabled', 'tabindex', 'language', 'onchange', 'onkeypress');

        $field_data = '<textarea ';

        while (list ($key, $value) = each($fdata)) {
            if (in_array(strtolower($key), $allowed_params)) {
                if ($key != 'required' || $key != 'note' || $key != 'type' || $key != 'text') {
                    if ($key != 'params') {
                        $field_data .= $key . '="' . $value . '" ';
                    } else {
                        $field_data .= $value . ' ';
                    }
                }
            }
        }
        $field_data .= '>';

        $field_data .= $fdata['value'];

        $field_data .= '</textarea>';

        if (is_data($fdata['required']))
            $field_data .= TEXT_FIELD_REQUIRED;

        if (is_data($fdata['note']))
            $field_data .= ' ' . $fdata['note'];

        return $field_data;
    }

    function _draw_submitbutton ($data)
    {

        if (!is_data($data))
            return false;

        if (!is_data($data['id']))
            //$data['id'] = $data['name'];

            $fdata = $data;

        $allowed_params = array(
            'class',
            'params',
            'id',
            'type',
            'name',
            'value',
            'size',
            'maxlength',
            'checked',
            'border',
            'src',
            'alt',
            'lowscr',
            'width',
            'height',
            'align',
            'vspace',
            'hspace',
            'readonly',
            'disabled',
            'accesskey',
            'tabindex',
            'language',
            'onclick',
            'onchange',
            'onfocus',
            'onblur',
            'onkeypress',
            'onkeydown',
            'autocomplete'
        );

        $field_data = '<input ';

        while (list ($key, $value) = each($fdata)) {
            if (in_array(strtolower($key), $allowed_params)) {
                if ($key != 'required' || $key != 'note') {
                    if ($key != 'params' && $key != 'value') {
                        $field_data .= $key . '="' . $value . '" ';
                    } elseif ($key == 'value') {
                        $field_data .= $key . '="' . constant(strtoupper($value)) . '" ';
                    } else {
                        $field_data .= $value . ' ';
                    }
                }
            }
        }

        $field_data .= ' />';

        return $field_data;
    }

    function _draw_imagebutton ($data)
    {
        global $language, $template;

        if (!is_data($data))
            return false;

        if (!is_data($data['id']))
            //$data['id'] = $data['name'];

            $fdata = $data;

        $allowed_params = array(
            'class',
            'params',
            'id',
            'type',
            'name',
            'value',
            'size',
            'maxlength',
            'checked',
            'border',
            'src',
            'alt',
            'lowscr',
            'width',
            'height',
            'align',
            'vspace',
            'hspace',
            'readonly',
            'disabled',
            'accesskey',
            'tabindex',
            'language',
            'onclick',
            'onchange',
            'onfocus',
            'onblur',
            'onkeypress',
            'onkeydown',
            'autocomplete'
        );

        $field_data = '<input ';

        while (list ($key, $value) = each($fdata)) {
            if (in_array(strtolower($key), $allowed_params)) {
                if ($key != 'params' && $key != 'src') {
                    $field_data .= $key . '="' . $value . '" ';
                } elseif ($key == 'src') {
                    $field_data .= $key . '="' . _SYSTEM_BASE_URL . _SRV_WEB . _SRV_WEB_TEMPLATES . $template->selected_template . '/img/buttons/' . $language->environment_language . '/' . $value . '" ';
                } else {
                    $field_data .= $value . ' ';
                }
            }
        }

        $field_data .= ' />';

        return $field_data;
    }

    function _draw_end ()
    {
        return '</form>';
    }
}