<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/constants.php';

class xt_print_buttons
{
    protected $_table = TABLE_PRINT_BUTTONS;
    protected $_table_lang = TABLE_PRINT_BUTTONS_LANG;
    protected $_table_seo = null;
    protected $_master_key = COL_PRINT_BUTTONS_ID;

    public $url_data;
    protected $position;

    private $colTplType = COL_PRINT_BUTTONS_TEMPALTE_TYPE;

    function __construct($position = false)
    {
        if($position!==false)
        {
            $this->setPosition($position);
        }
    }

    function setPosition($position)
    {
        $this->position = $position;
    }

    function _getParams()
    {
        global $language;

        $header = array(
            COL_PRINT_BUTTONS_ID => array('type' => 'hidden', 'hidden' => false), /* ???? */
            $this->colTplType => array(
                'type' => 'dropdown',
                'url'  => 'DropdownData.php?get=template_type',
                'hidden' => false),
        );

        foreach ($language->_getLanguageList() as $key => $val) {
            $header['caption_' . $val['code']] = array(
                'type' => 'textfield',
            );
        }

        $params = array();
        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;

        $params['default_sort'] = $this->_master_key;
        $params['display_copyBtn'] = true;
        $params['display_checkCol'] = true;

        if ($this->url_data['pg'] == 'overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true) {
            $params['include'] = array('template_id', 'template_name', 'template_type');
        }

        return $params;
    }

    function _get($ID = 0)
    {
        global $xtPlugin, $db, $language;

        if ($this->position != 'admin') {
            return false;
        }

        $default_array = array(
            COL_PRINT_BUTTONS_ID => '',
            $this->colTplType => ''
        );

        foreach ($language->_getLanguageList() as $key => $val) {
            $default_array['caption_' . $val['code']] = '';
        }

        if (!$ID && !isset($this->sql_limit)) {
            $this->sql_limit = "0,25";
        }

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', $this->sql_limit, $this->perm_array);

        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
        } elseif ($ID === 'new') {
            $data = array($default_array);
        }elseif ($ID) {
            $data = $table_data->getData($ID);
        } else {
            $data = $table_data->getHeader();
        }

        foreach ($data as $k => $n)
        {
            $data[$k][$this->colTplType] = $data[$k][COL_TEMPLATE_TYPE];
            unset($data[$k][COL_TEMPLATE_TYPE]);
        }

        if ($table_data->_total_count != 0 || !$table_data->_total_count)
            $count_data = $table_data->_total_count;
        else
            $count_data = count($data);

        $obj = new stdClass();
        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
    }

    function _set($data, $set_type = 'edit')
    {
        $data[COL_TEMPLATE_TYPE] = $data[$this->colTplType];
        $oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $objC = $oC->saveDataSet();

        $obj = new stdClass;
        if (!is_numeric($data[$this->_master_key])) {
            $obj->new_id = $objC->new_id;
            $data = array_merge($data, array($this->_master_key => $objC->new_id));
        }

        $oCD = new adminDB_DataSave($this->_table_lang, $data, true, __CLASS__);
        $objCD = $oCD->saveDataSet();

        $set_perm = new item_permission($this->perm_array);
        $set_perm->_saveData($data, $data[$this->_master_key]);

        if ($objC->success && $objCD->success) {
            $obj->success = true;
        } else {
            $obj->success = false;
        }

        return $obj;
    }

    function _unset($id = 0)
    {
        global $db;

        $id = (int) $id;
        if (!$id || ($this->position != 'admin')) {
            return false;
        }

        $set_perm = new item_permission($this->perm_array);
        $set_perm->_deleteData($id);

        $db->Execute("DELETE FROM " . $this->_table . " WHERE " . $this->_master_key . " = " . $id);
        $db->Execute("DELETE FROM " . $this->_table_lang . " WHERE " . $this->_master_key . " = " . $id);
    }

    function _copy($id = 0)
    {
        $id = (int) $id;

        if (!$id || ($this->position != 'admin')) {
            return false;
        }

        $e_table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $this->perm_array, 'false');
        $e_data = $e_table_data->getData($id);
        $e_data = $e_data[0];
        unset($e_data[$this->_master_key]);

        $oE = new adminDB_DataSave($this->_table, $e_data);
        $objE = $oE->saveDataSet();

        $obj = new stdClass;
        $obj->new_id = $objE->new_id;
        $e_data[$this->_master_key] = $objE->new_id;

        $oED = new adminDB_DataSave($this->_table_lang, $e_data, true);
        $objED = $oED->saveDataSet();

        $set_perm = new item_permission($this->perm_array);
        $set_perm->_saveData($e_data, $e_data[$this->_master_key]);

        $obj = new stdClass;
        $obj->success = true;
        return $obj;
    }
/*
    public function getTemplate($langCode)
    {
        global $db;

        $template = XT_ORDERS_INVOICES_TEMPLATES_TEXT_MISSED;

        $query = "SELECT * FROM " . $this->_table . " i " . $this->permission->_table . " WHERE 1 " . $this->permission->_where . " and i.template_type='$this->_template_type'";
        $rs = $db->Execute($query);
        if ($rs->RecordCount() > 0) {
            $templateId = $rs->fields['template_id'];

            $query = "SELECT * FROM " . $this->_table_lang . " WHERE template_id='" . $templateId . "' AND language_code='" . $langCode . "'";
            $rs = $db->Execute($query);
            if ($rs->RecordCount() && !empty($rs->fields['template_body'])) {
                $template = $rs->fields['template_body'];
            }
        }

        return $template;
    }
*/

} 