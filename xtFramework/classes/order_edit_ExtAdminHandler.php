<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');


class order_edit_ExtAdminHandler  extends  ExtAdminHandler
{
    function __construct($extAdminHandler)
    {
        foreach ($extAdminHandler as $key => $value)
        {
            $this->$key = $value;
        }
    }


    function _changeRenderer()
    {
        // todo: check decimal places // fbo: das ist ein xt to do
        $changeRenderer = PhpExt_Javascript::functionDef(
            "change",
            "var decimal_places = 2;" .
            "val = parseFloat(val);" .
            "if (val==NaN) tmp_value = 1; " .
                "else tmp_value = val.toFixed(decimal_places);" .
                "if (val > 0) {" .
                "   return '<span style=\"color:green;\">' + tmp_value + '</span>';" .
                "} else if(val < 0) {" .
                "   return '<span style=\"color:red;\">' + tmp_value + '</span>';" .
                "} return val;",
            array("val")
        );
        return $changeRenderer;
    }

    function _comboBox($label, $name, $url = '',$listWidth='300',$listner='') {
        $reader = new PhpExt_Data_JsonReader();
        $reader->setRoot("topics")
            ->setTotalProperty("totalCount");
        $reader->addField(new PhpExt_Data_FieldConfigObject("id"));
        $reader->addField(new PhpExt_Data_FieldConfigObject("name"));
        $reader->addField(new PhpExt_Data_FieldConfigObject("desc"));

        $store = new PhpExt_Data_Store();
        $store->setProxy(new PhpExt_Data_HttpProxy($url))
            ->setReader($reader);
        // combobox with description tooltip
        $combo = new PhpExt_Form_ComboBox();
        $combo->setCssStyle(new PhpExt_Config_ConfigObject(array("width"=>"auto")));
        $combo->setTemplate('<tpl for="."><div ext:qtip="{id} {desc}" class="x-combo-list-item" >{name}</div></tpl>')
            ->setStore($store)
            ->setFieldLabel($name)
            ->setTitle(TEXT_ADMIN_DROPDOWN_SELECT)
            ->setHiddenName($label)
            ->setListWidth($listWidth)
            ->setTriggerAction(PhpExt_Form_ComboBox::TRIGGER_ACTION_ALL)
            ->setSelectOnFocus(true);

        if ($this->getSetting('gridType') == 'EditGrid') {
            $combo->setDisplayField("name");
            $combo->setValueField("id");
        } else {

            $combo->setDisplayField("name");
            $combo->setValueField("id");
        }
        return $combo;
    }
}