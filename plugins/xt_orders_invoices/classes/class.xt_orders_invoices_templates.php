<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/constants.php';
require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/class.xt_orders_invoices.php';

class xt_orders_invoices_templates {
	protected $_table = TABLE_PDF_MANAGER;
	protected $_table_lang = TABLE_PDF_MANAGER_CONTENT;
	protected $_table_seo = null;
	protected $_master_key = 'template_id';
	protected $_permission = array();
	protected $_template_type = XT_ORDERS_INVOICES_DEFAULT_TEMPLATE_TYPE;
	
	public $url_data;
	protected $position;
	
	public function __construct($template_type = null, $shopId = 0, $customerStatus = 0) {
		global $store_handler;
	
		if ($template_type) {
			$this->_template_type = $template_type;
		}
	
		if ($shopId) {
			$store_handler->shop_id = $shopId;
		}
	
		$this->getPermission($customerStatus);
	}
	
	function setPosition($position) {
		$this->position = $position;
	}
	
	function getPermission($customerStatus) {
		$this->perm_array = array(
				'shop_perm' => array(
						'type' => 'shop',
						'key' => $this->_master_key,
						'value_type' => 'invoice',
						'pref' => 'i'
				),
				'group_perm' => array(
						'type'=>'group_permission',
						'key'=>$this->_master_key,
						'value_type' => 'invoice',
						'pref'=>'i'
				)
		);
	
		if($customerStatus) {
			$this->perm_array['group_perm']['status'] = $customerStatus;
		}
	
		$this->_permission = new item_permission($this->perm_array);
	
		return $this->perm_array;
	}
	
	public function getTemplate($langCode) {
		global $db;
	
		$template = array();
        $template['body'] = XT_ORDERS_INVOICES_TEMPLATES_TEXT_MISSED;
	
		$query = "SELECT * FROM " . $this->_table . " i " . $this->_permission->_table . " WHERE 1 " . $this->_permission->_where . " and i.template_type='$this->_template_type'";
		$rs = $db->Execute($query);
		if ($rs->RecordCount() > 0) {
            $template = array_merge($rs->fields, $template);
			$templateId = $rs->fields['template_id'];
            if ($template['template_use_be_lng']==1)
            {
                global $language;
                $langCode = $language->content_language;
            }
	
			$query = "SELECT * FROM " . $this->_table_lang . " WHERE template_id='" . $templateId . "' AND language_code='" . $langCode . "'";
			$rs = $db->Execute($query);
            if ($rs->RecordCount() && !empty($rs->fields['template_body'])) {
				$template['body'] = $rs->fields['template_body'];
            }
		}
	
		return $template;
	}

	public function getTemplateById($templateId, $langCode) {
		global $db;
		
		$template = array();
        $template['body'] = XT_ORDERS_INVOICES_TEMPLATES_TEXT_MISSED;

        $query = "SELECT * FROM " . $this->_table . " i WHERE i.template_id='$templateId'";
        $rs = $db->Execute($query);

        if ($rs->RecordCount() > 0) {
        	$template = array_merge($rs->fields, $template);

            $query = "SELECT * FROM " . $this->_table_lang . " WHERE template_id='" . $templateId . "' AND language_code='" . $langCode . "'";
            $rs = $db->Execute($query);
            if ($rs->RecordCount()) {
                if (empty($rs->fields['template_body'])) {
                    $template['body'] = "Empty Template: id => $templateId, language => $langCode";
                }
                else {
                    $template['body'] = $rs->fields['template_body'];
                }
            }
        }
		return $template;
	}
	
	public function preview($url_data) {
        $fname = 'invoice-dummy.json';
        $pathSrv = _SRV_WEBROOT._SRV_WEB_PLUGINS."xt_orders_invoices/resource/$fname";
        $pathWeb = _SRV_WEB._SRV_WEB_PLUGINS."xt_orders_invoices/resource/$fname";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "file://".$pathSrv);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        $json = curl_exec($ch);
        curl_close($ch);

        if($json==false)
        {
            $fh = fopen( "file://".$pathSrv, "r") or die("unable to open file ".$pathWeb);
            $json =  fread($fh,filesize("file://".$pathSrv));
            fclose($fh);
        }

        $data =  json_decode($json, true);

        if(is_array($data))
        {
            $oi = new xt_orders_invoices();
            $oi->_getPdfContent($data, false, $url_data['print_template_id']);

        }
        else {
            die('no data found in '.$pathWeb);
        }
    }
	
    function _getParams() {
        global $language;

        $header = array(
            'template_id' => array('type' => 'hidden', 'hidden' => false),
            'template_name' => array('type' => 'textfield', 'hidden' => false),
        	'template_type' => array('type' => 'textfield', 'hidden' => false),
        	'template_pdf_out_name' => array('type' => 'textfield', 'hidden' => false, 'width' => '600px'),
        	'template_use_be_lng' => array('type' => 'status'),
        );

        foreach ($language->_getLanguageList() as $key => $val) {
            $header['template_body_' . $val['code']] = array(
                'type' => 'textarea',
                'height' => '400',
                'width' => '100%'
            );
        }

        $params = array();
        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;

        $params['default_sort'] = $this->_master_key;
        $params['display_copyBtn'] = true;
        $params['display_checkCol'] = true;

        if ($this->url_data['pg'] == 'overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true) {
            $params['include'] = array('template_id', 'template_name');
        }
        if (!$this->url_data['edit_id'] && $this->url_data['new'] != true)
        {
            foreach ($language->_getLanguageList() as $key => $val)
            {
                $params['exclude'][] = 'template_body_' . $val['code'];
            }
        }
        
        $editId = $this->url_data['edit_id'] ? "'".$this->url_data['edit_id']."'" : 'record.data.template_id';
        
        $rowActions[] = array('iconCls' => 'xt_orders_invoices_print_dummy', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_XT_ORDERS_INVOICES_PRINT_DUMMY);
        $js = "window.open('adminHandler.php?plugin=xt_orders_invoices&load_section=xt_orders_invoices_templates&pg=preview&print_template_id='+".$editId.", '');";
        $rowActionsFunctions['xt_orders_invoices_print_dummy'] = $js;

        $params['rowActions'] = $rowActions;
        $params['rowActionsFunctions'] = $rowActionsFunctions;

        return $params;
    }

    function _get($ID = 0) {
        global $xtPlugin, $db, $language;
		$obj = new stdClass;
        if ($this->position != 'admin') {
            return false;
        }

        if ($ID === 'new') {
            $obj = $this->_set(array(), 'new');
            $ID = $obj->new_id;
        }

        if (!$ID && !isset($this->sql_limit)) {
            $this->sql_limit = "0,25";
        }

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', $this->sql_limit, $this->perm_array);

        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
        } elseif ($ID) {
            $data = $table_data->getData($ID);
            $data[0]['group_permission_info']=_getPermissionInfo();
            $data[0]['shop_permission_info']=_getPermissionInfo();
        } else {
            $data = $table_data->getHeader();
        }

        if ($table_data->_total_count != 0 || !$table_data->_total_count)
            $count_data = $table_data->_total_count;
        else
            $count_data = count($data);

        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
    }

    function _set($data, $set_type = 'edit') {
    	
        $obj = new stdClass;

        // plg relies on at least on template of type XT_ORDERS_INVOICES_DEFAULT_TEMPLATE_TYPE
        // prevent renaming of last one
        if ($set_type == 'edit' && $data[COL_TEMPLATE_TYPE]!=XT_ORDERS_INVOICES_DEFAULT_TEMPLATE_TYPE) {
            global $db;
            $sql = "SELECT `".COL_TEMPLATE_TYPE."` FROM ".$this->_table." WHERE `".$this->_master_key."`='".$data[$this->_master_key]."'";
            $oldType = $db->GetOne($sql);
            $sql = "SELECT count(`".COL_TEMPLATE_TYPE."`) FROM ".$this->_table." WHERE `".COL_TEMPLATE_TYPE."`='".XT_ORDERS_INVOICES_DEFAULT_TEMPLATE_TYPE."'";
            $tplTypeCount = (int) $db->GetOne($sql);
            if($oldType === XT_ORDERS_INVOICES_DEFAULT_TEMPLATE_TYPE && $tplTypeCount===1) {
                $obj->success = false;
                $obj->error_message = XT_ORDERS_INVOICES_ERROR_CANT_CHANGE_TEMPLATE_TYPE;
                return $obj;
            }
    	}
  		
        $oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $objC = $oC->saveDataSet();
		
        if ($set_type == 'new') {
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

    function _unset($id = 0) {
        global $db;
        
        $obj = new stdClass;

        // plg relies on at least on template of type XT_ORDERS_INVOICES_DEFAULT_TEMPLATE_TYPE
        // prevent deletion of last one
        $sql = "SELECT `".COL_TEMPLATE_TYPE."` FROM ".$this->_table." WHERE `".$this->_master_key."`='".$id."'";
        $tplType = $db->GetOne($sql);
        $sql = "SELECT count(`".COL_TEMPLATE_TYPE."`) FROM ".$this->_table." WHERE `".COL_TEMPLATE_TYPE."`='".XT_ORDERS_INVOICES_DEFAULT_TEMPLATE_TYPE."'";
        $tplTypeCount = (int) $db->GetOne($sql);
        if($tplType === XT_ORDERS_INVOICES_DEFAULT_TEMPLATE_TYPE && $tplTypeCount===1) {
            $obj->success = false;
            $obj->error_message = XT_ORDERS_INVOICES_ERROR_CANT_DELETE_LAST_TEMPLATE;
            return $obj;
        }

        $id = (int) $id;
        if (!$id || ($this->position != 'admin')) {
            $obj->success = false;
            $obj->error_message = "no id or not admin";
            return $obj;
        }

        $set_perm = new item_permission($this->perm_array);
        $set_perm->_deleteData($id);

        $db->Execute("DELETE FROM " . $this->_table . " WHERE " . $this->_master_key . " = " . $id);
        $db->Execute("DELETE FROM " . $this->_table_lang . " WHERE " . $this->_master_key . " = " . $id);
        
        $obj->success = true;
        return $obj;
    }

    function _copy($id = 0) {
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

}

?>