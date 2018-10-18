<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2013 xt:Commerce International Ltd. All Rights Reserved.
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


class hermes_ExtAdminHandler  extends  ExtAdminHandler
{
    function __construct($extAdminHandler)
    {
        foreach ($extAdminHandler as $key => $value)
        {
            $this->$key = $value;
        }
    }

    function _switchFormField ($line_data, $lang_data = array()) {

        $label = $line_data['name'];
        //		$name = __define($line_data['text']);
        $name = $line_data['text'];
        $value = true;


        switch ($line_data['type']) {
            case "textarea":
                $data = PhpExt_Form_TextArea::createTextArea($label, $name);
                if (isset($line_data['height'])) {
                    $data->setHeight($line_data['height']);
                } else {
                    $data->setHeight('150');
                }
                if (isset($line_data['width'])) {
                    $data->setWidth($line_data['width']);
                } else {
                    $data->setWidth('70%');
                }

                break;
            case "admininfo":
                $data = PhpExt_Form_TextArea::createTextArea($label, $name);
                if (isset($line_data['height'])) {
                    $data->setHeight($line_data['height']);
                } else {
                    $data->setHeight('80');
                }
                if (isset($line_data['width'])) {
                    $data->setWidth($line_data['width']);
                } else {
                    $data->setWidth('70%');
                }
                $data->setCssClass('form_info');

                break;
            case "htmleditor":

                if(_SYSTEM_USE_WYSIWYG=='SimpleHtmlEditor'){

                    $data = PhpExt_Form_HtmlEditor::createHtmlEditor($label, $name);
                    if (isset($line_data['required'])) {
                        $line_data['required'] = '';
                    }
                    break;

                }elseif(_SYSTEM_USE_WYSIWYG=='TinyMce'){

                    $data = PhpExt_Form_TextArea::createTextArea($label, $name);
                    $data->setCssClass('TinyMce');
                    break;

                }else{

                    $data = PhpExt_Form_TextArea::createTextArea($label, $name);
                    if (isset($line_data['height'])) {
                        $data->setHeight($line_data['height']);
                    } else {
                        $data->setHeight('150');
                    }
                    if (isset($line_data['width'])) {
                        $data->setWidth($line_data['width']);
                    } else {
                        $data->setWidth('70%');
                    }

                    break;
                }

            case "image":
                $uniqueID = $name.'_'.time();

                // dummy field
                $data = PhpExt_Form_Hidden::createHidden($label, $name);

                if (!$line_data['value'])
                    $value = false;
                break;
            case "file":
                $uniqueID = $name.'_'.time();
                $data = PhpExt_Form_TextField::createTextField($label, $name, $uniqueID);

                $panel = $this->_fileUploadPanel($data, $uniqueID, $line_data);

                if (!$line_data['value'])
                    $value = false;
                break;
            case "date":
                $data = PhpExt_Form_DateField::createDateField($label, $name);
                $data->setFormat('Y-m-d');

                break;
            case "dropdown":
                if (empty($line_data['url'])) {
                    // default dropdownstatus
                    $url = 'DropdownData.php?get=status_truefalse';
                } else {
                    $url = $line_data['url'];
                }
                $data = $this->_comboBox($label, $name, $line_data['url'],$line_data['width']);
                break;
            case "dropdown_ms":
            case "dropdown_multi":
            case "dropdown_multiselect":
                if (empty($line_data['url'])) {
                    // default dropdownstatus
                    $url = 'DropdownData.php?get=status_truefalse';
                } else {
                    $url = $line_data['url'];
                }
                // dropdown with checkbox
                $data = $this->_multiComboBox($label, $name, $line_data['url']);
                break;
            case "itemselect":
                if (empty($line_data['url'])) {
                    // default dropdownstatus
                    $url = 'DropdownData.php?get=status_truefalse';
                } else {
                    $url = $line_data['url'];
                }
                $data = $this->_itemSelect($label, $name, $line_data['url']);
                break;


            case "truefalse": //for admin configuration
            case "status":

                $data = PhpExt_Form_Checkbox::createCheckbox($label, $name);
                $data->setCssClass("checkBox");
                if ($line_data['value'] && $line_data['value'] != 'false')
                    $data->setChecked(true);
                else
                    $data->setChecked(false);

                // fix: set 'on' value to 1
                $data->setInputValue(1);

                $line_data['required'] = false; // combo not required
                break;
            case "master_key":
                //$data = PhpExt_Form_TextField::createTextField($label, $name);
                //$line_data['required'] = true; // default combo required
                //$data->setDisabled(true);
                //break;

            case "hidden":
                $data = PhpExt_Form_Hidden::createHidden($label, $name);
                $line_data['required'] = false; // not required, user can not enter anything
                break;

            case "password":
                $data = PhpExt_Form_PasswordField::createPasswordField($label,$name);

                break;

            case "upload":
                $data = PhpExt_Form_UploadField::createUploadField($label,$name);
                break;

            default:
                $data = PhpExt_Form_TextField::createTextField($label, $name);
                if (isset($line_data['width'])) {
                    $data->setWidth($line_data['width']);
                } else {
                    $data->setWidth('300');
                }
                if (isset($line_data['disabled'])) $data->setDisabled(true);

                break;
        }
        ////////////////////////////////////////////////////////////////////////
        // field validation
        // readonly
        if ($line_data['readonly'] && !$this->getSetting('edit_masterkey')) {
            $data->setDisabled(true);
        }
        // minimum
        if ($line_data['min']) {
            $data->setMinLength($line_data['min']);
            $data->setMinLengthText(__define("ERROR_MIN"));
        }
        // maximum
        if ($line_data['max']) {
            $data->setMaxLength($line_data['max']);
            $data->setMaxLengthText(__define("ERROR_MAX"));
        }
        // required
        if ($line_data['required']) {
            $data->setAllowBlank(false);
            $data->setBlankText(__define("ERROR_BLANK"));
        }
        // field validation end
        ////////////////////////////////////////////////////////////////////////

        // set field value
        if ($value && $line_data['value']!=='' && $line_data['value']!=='0000-00-00 00:00:00') {
            $data->setValue($line_data['value']);
        }
        if ($panel)
            $data = $panel;
        return $data;
    }

    function _multiactionPopup($fname, $page_url='', $page_title='') {
        //		$data = $this->_ExtSubmitButtonHandler(array('url' => ));

        $string = "  ".$this->multiselectStm('record_ids')."";

        // fbo test // $string .= "console.log(record_ids); return;";


        $string .= "  	if (record_ids == '') {";
        $string .= "     return ; /* record_ids = ".$this->getSelectionItem()."*/;";
        $string .= "  	}\n";

        $string.= $this->_RemoteWindow("".$page_title."","".$page_title."","".$page_url."&value_ids='+record_ids+'", true, array(), 500, 300).' new_window.show();';


        $renderer = PhpExt_Javascript::functionDef(''.$fname.'', $string, array());
        $renderer->Statement = $fname.'();'.$renderer->Statement;

        return $renderer;

    }

    function _multiactionWindow($fname, $page_url='', $page_title='') {
        //		$data = $this->_ExtSubmitButtonHandler(array('url' => ));

        $string = "  ".$this->multiselectStm('record_ids')."";

        // fbo test // $string .= "console.log(record_ids); return;";


        $string .= "  	if (record_ids == '') {";
        $string .= "     return ; /* record_ids = ".$this->getSelectionItem()."*/;";
        $string .= "  	}\n";

        $string .= "    window.open('".$page_url."&value_ids='+record_ids,'_blank');";

        $renderer = PhpExt_Javascript::functionDef(''.$fname.'', $string, array());
        $renderer->Statement = $fname.'();'.$renderer->Statement;

        return $renderer;

    }

    function multiselectStm ($var = 'record_ids') {
        $js = "
             var records = new Array();
             records = ".$this->code."ds.getModifiedRecords();
             ".$this->_getGridModifiedRecordsData()."
		 	 var ".$var." = '';
		 	 for (var i = 0; i < records.length; i++) {
		 	     if (records[i].get('selectedItem'))
		 	      ".$var." += records[i].get('".$this->getMasterKey()."') + ',';
		 	 }


		 	 ";
        return $js;
    }

    function  _ExtSubmitButtonHandler ($data) {
        $js = '';

        if ($data['url'])
            $js.= " url: '".$data['url']."'";

        if ($data['success'])
        {
            $js.= ", success: ".$data['success'];
        }
        elseif ($data['close']=='close')
        {
            $js.= ", success: function(form,action){var r = action.result; if (!r.success){Ext.Msg.alert('".__define('TEXT_ALERT')."', r.errorMsg);} else{ Ext.MessageBox.alert('".__define('TEXT_ALERT')."',r.msg ? r.msg : '".__define('TEXT_ALERT')."');} contentTabs.remove(contentTabs.getActiveTab()); var gh=Ext.getCmp('".$_REQUEST['gridHandle']."'); if (gh) gh.getStore().reload(); }";
        }
        else
        {
            $js.= ", success: function(form,action){var r = action.result; if (!r.success){Ext.Msg.alert('".__define('TEXT_ALERT')."', r.errorMsg);} else{ Ext.MessageBox.alert('".__define('TEXT_ALERT')."',r.msg ? r.msg : '".__define('TEXT_ALERT')."');} contentTabs.remove(contentTabs.getActiveTab()); var gh=Ext.getCmp('".$_REQUEST['gridHandle']."'); if (gh) gh.getStore().reload(); }";
        }

        if ($data['failure'])
        {
            $js.= ", failure: ".$data['failure']."";
        }
        else
        {
            $js.= ", failure: function(form,action){var r = action.result; Ext.Msg.alert('".__define('TEXT_ALERT')."', r.errorMsg);}";
        }
        if ($data['params'])
            $js.= ", params: ".$data['params']."";
        $js.= ", waitMsg: '".__define("TEXT_LOADING")."'";

        return $js;
    }


}
?>