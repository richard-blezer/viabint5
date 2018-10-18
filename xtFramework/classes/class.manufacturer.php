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

class manufacturer {

	public $_master_key = 'manufacturers_id';
	public $_image_key = 'manufacturers_image';
	public $_table = TABLE_MANUFACTURERS;
	public $_display_key = 'manufacturers_name';
	protected $_table_lang = TABLE_MANUFACTURERS_DESCRIPTION;
	protected $_table_seo = TABLE_SEO_URL;
	public $store_field_exists = false;
	public $_store_field = 'manufacturers_store_id';

	public function __construct($mnf_id = 0)
	{
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:manufacturer_top')) ? eval($plugin_code) : false;

		if (isset($plugin_return_value))
			return $plugin_return_value;

		$this->getPermission();

		if ($mnf_id != 0)
		{
			$this->mnf_id = $mnf_id;
			$data = $this->getManufacturerData($mnf_id);
			$this->data = $this->buildData($data);
		}
	}

	public function getPermission()
	{
		global $store_handler, $customers_status, $xtPlugin;

		$this->perm_array = array(
			'shop_perm' => array(
				'type'	=> 'shop',
				'table'	=> TABLE_MANUFACTURERS_PERMISSION,
				'key'	=> $this->_master_key,
				'pref'	=> 'm'
			)
		);

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':getPermission')) ? eval($plugin_code) : false;

		$this->permission = new item_permission($this->perm_array);
		return $this->perm_array;
	}

	public function getManufacturerData($mID)
	{
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:getManufacturerData_top')) ? eval($plugin_code) : false;

		if (isset($plugin_return_value))
			return $plugin_return_value;

		$sql_tablecols = "m.*, mi.*, su.*";

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:getManufacturerData_sql_tablecols')) ? eval($plugin_code) : false;

		$this->sql_manufacturer = new getManufacturerSQL_query();
		$this->sql_manufacturer->setPosition('getManufacturerData');
		$this->sql_manufacturer->setFilter('Language');
		$this->sql_manufacturer->setFilter('Seo');
		$this->sql_manufacturer->setSQL_COLS(", " . $sql_tablecols);
		$this->sql_manufacturer->setSQL_WHERE("AND m.manufacturers_id = ".(int)$mID);
		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:getManufacturerData_SQL')) ? eval($plugin_code) : false;

		$query = $this->sql_manufacturer->getSQL_query();
		
		$record = $db->Execute($query);
		if ($record->RecordCount() > 0)
		{
			$data = $record->fields;
			($plugin_code = $xtPlugin->PluginCode('manufacturer:getManufacturerData_bottom')) ? eval($plugin_code) : false;
			return $data;
		}

		return false;
	}

	public function buildData(&$data)
	{
		global $xtPlugin, $xtLink, $module;
		
		($plugin_code = $xtPlugin->PluginCode('manufacturer:buildData_top')) ? eval($plugin_code) : false;

		if(isset($plugin_return_value))
			return $plugin_return_value;

		if (is_array($data) && count($data) == 1 && is_data($data['manufacturers_id']))
		$data = $this->getManufacturerData($data['manufacturers_id']);
		if ($data['manufacturers_name']!='')
		{
			$link_array = array(
				'page'		=> 'manufacturers',
				'type'		=> 'manufacturer',
				'name'		=> $data['manufacturers_name'],
				'id'		=> $data['manufacturers_id'],
				'seo_url'	=> $data['url_text']
			);
			$data['link'] = $xtLink->_link($link_array);

			if ( ! empty($data['manufacturers_image']))
				$data['manufacturers_image']= __CLASS__.':'.$data['manufacturers_image'];

			$data['id'] = $data['manufacturers_id'];
			$data['text'] = $data['manufacturers_name'];

			global $mediaImages;
			$media_images = $mediaImages->get_media_images($data['manufacturers_id'], __CLASS__);
			$data['more_images'] = $media_images['images'];
		}else return false;
		
		($plugin_code = $xtPlugin->PluginCode('manufacturer:buildData_bottom')) ? eval($plugin_code) : false;
		return $data;
	}

	public function getManufacturerList($type = 'default', $position = 'default', $order_by = '')
	{
		global $xtPlugin, $db;
		
		($plugin_code = $xtPlugin->PluginCode('manufacturer:getManufacturerList_top')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value))
			return $plugin_return_value;

		$this->sql_manufacturer = new getManufacturerSQL_query();
		$this->sql_manufacturer->setPosition('getManufacturerList');

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:getManufacturerList_SQL')) ? eval($plugin_code) : false;

		if ($type !== 'admin')
			$this->sql_manufacturer->setFilter('GroupCheck');

		if ($type === 'default')
		{
			$this->sql_manufacturer->setSQL_TABLE("LEFT JOIN ".TABLE_PRODUCTS." p ON p.manufacturers_id = m.manufacturers_id");
			$this->sql_manufacturer->setSQL_WHERE("AND p.products_status = 1");
		}

		if ($position === 'box')
		{
			$this->sql_manufacturer->setSQL_WHERE("AND m.manufacturers_status = 1");
		}

		$this->sql_manufacturer->setSQL_SORT(empty($order_by) ? 'm.manufacturers_name' : $order_by);

		$query = $this->sql_manufacturer->getSQL_query('DISTINCT m.manufacturers_id');
		
		
		$record = ($position === 'box')
			? $db->CacheExecute(_CACHETIME_MANUFACTURER_LIST, $query)
			: $record = $db->Execute($query);

		if ($record->RecordCount() > 0)
		{
			while( ! $record->EOF)
			{
				($plugin_code = $xtPlugin->PluginCode('manufacturer:getManufacturerList_data')) ? eval($plugin_code) : false;

				$d = $this->buildData($record->fields);
				if ($d!==false) $data[] = $d ;

				$record->MoveNext();
			}
			$record->Close();

			($plugin_code = $xtPlugin->PluginCode('manufacturer:getManufacturerList_bottom')) ? eval($plugin_code) : false;

			return $data;
		}

		return false;
	}

	public function getManufacturerSortDropdown($default)
	{
		// TODO
	}

	public function setPosition($position)
	{
		$this->position = $position;
	}

	function _getParams() {
		global $language, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:_getParams_top')) ? eval($plugin_code) : false;

		if (isset($plugin_return_value))
			return $plugin_return_value;
		
		if (StoreIdExists($this->_table_lang,$this->_store_field)) 
		{
			$this->store_field_exists=true;
		}
		
		$params = array();
		
		if ($this->store_field_exists)
			$params['languageStoreTab'] = true;
		$header['external_id'] = array('type' => 'hidden');
		$st = new multistore();
		$stores = $st->getStores();
		
		foreach ($stores as $store) {
			foreach ($language->_getLanguageList() as $val)
			{
				$add_to_f='';
				if ($this->store_field_exists) $add_to_f = 'store'.$store['id'].'_';
				$header['manufacturers_description_'.$add_to_f.$val['code']] = array('type' => 'htmleditor');
	
				if (_SYSTEM_HIDE_SUMAURL=='true') $header['url_text_'.$add_to_f.$val['code']] = array('type' => 'hidden');
				else $header['url_text_'.$add_to_f.$val['code']] = array('width' => 400);
	
				$header['meta_keywords_'.$add_to_f.$val['code']] = array('width' => 400);
				$header['meta_title_'.$add_to_f.$val['code']] = array('width' => 400);
				$header['meta_description_'.$add_to_f.$val['code']] = array('type' => 'textarea', 'width' => 400,'height' => 60);
				$header['manufacturers_store_id_'.$add_to_f.$val['code']] = array('type' => 'hidden');
				$header['store_id_'.$add_to_f.$val['code']] = array('type' => 'hidden');
				$header['manufacturers_url_'.$add_to_f.$val['code']] = array('type' => 'hidden');
			}
		}

		$header['products_sorting2'] = array(
			'type'	=> 'dropdown', 	// you can modyfy the auto type
			'url'	=> 'DropdownData.php?get=status_ascdesc'
		);

		$header['products_sorting'] = array(
			'type'	=> 'dropdown',	// you can modyfy the auto type
			'url'	=> 'DropdownData.php?get=manufacturers_sort'
		);
		
		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:_getParams_data')) ? eval($plugin_code) : false;

		$js = ($this->url_data['edit_id'])
			? "var edit_id = ".$this->url_data['edit_id'].";"
			: "var edit_id = record.id;";

		$extF = new ExtFunctions();
		$mjs = $extF->_MultiButton_stm('BUTTON_START_SEO', 'doMnfSeo');

		$params['display_MnfSeoMn'] = true;
		

		$params['header']	= $header;
		$params['master_key']	= $this->_master_key;
		$params['default_sort']	= $this->_master_key;
		//$params['path']	= "manufacturers/";
		
		$params['display_checkItemsCheckbox']  = true;
		$params['display_checkCol'] = true;
		$params['display_statusTrueBtn'] = true;
		$params['display_statusFalseBtn'] = true;
		$params['display_searchPanel'] = true;

		if ($this->url_data['pg'] === 'overview' && ! $this->url_data['edit_id'] && $this->url_data['new'] != true)
			$params['include'] = array('manufacturers_id', 'manufacturers_name', 'manufacturers_image', 'manufacturers_status');
		else
			$params['exclude'] = array('date_added', 'last_modified');

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:_getParams_bottom')) ? eval($plugin_code) : false;

		return $params;
	}

	function _getSearchIDs($search_data)
	{
		global $filter, $xtPlugin;

		$sql_tablecols = array('manufacturers_name', 'manufacturers_id');

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:_getSearchIDs')) ? eval($plugin_code) : false;

		foreach ($sql_tablecols as $tablecol)
		{
			$sql_where[]= "(".$tablecol." LIKE '%".$filter->_filter($search_data)."%')";
		}

		if (is_array($sql_where))
			$sql_data_array = ' ('.implode(' OR ', $sql_where).')';

		return $sql_data_array;
	}

	function _get($ID = 0)
	{
		global $xtPlugin, $db, $language;

		if ($this->position !== 'admin') return false;
        $where='';

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:_get_top')) ? eval($plugin_code) : false;

		if ($ID === 'new')
		{
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}
		else $obj = new stdClass;

		$ID = (int)$ID;

		if ($this->url_data['query'])
			$where .= $this->_getSearchIDs($this->url_data['query']);

		if ( ! $ID && ! isset($this->sql_limit))
			$this->sql_limit = "0,25";
		
		if ($this->store_field_exists) 
		{
			$store_field= $this->_store_field;
		}
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $where, $this->sql_limit, $this->perm_array,'','',$store_field);

		if ($this->url_data['get_data'])
			$data = $table_data->getData();
		elseif ($ID)
			$data = $table_data->getData($ID);
		else
			$data = $table_data->getHeader();

		$obj->totalCount = empty($table_data->_total_count)
			? count($data)
			: $table_data->_total_count;

		$obj->data = $data;

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:_get_bottom')) ? eval($plugin_code) : false;

		return $obj;
	}

	function _set($data, $set_type = 'edit')
	{
		global $db, $language, $filter, $seo, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:_set_top')) ? eval($plugin_code) : false;

		$obj = new stdClass;

		unset($data['manufacturers_image']);

		$oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$objC = $oC->saveDataSet();

		if ($set_type === 'new') // edit existing
		{
			$obj->new_id = $objC->new_id;
			$data = array_merge($data, array($this->_master_key=>$objC->new_id));
		}

		$oCD = new adminDB_DataSave($this->_table_lang, $data, true, __CLASS__,$this->store_field_exists);
		$objCD = $oCD->saveDataSet();

		// Build Seo URLS
		$st = new multistore();
		$stores = $st->getStores();
		foreach ($stores as $store) {
			foreach ($language->_getLanguageList() as $val)
			{
				$stor_f='';
				$store_f_update='';
				if ($this->store_field_exists) {
					$stor_f='store'.$store['id'].'_';
					$store_f_update = $store['id'];
				}
				
				if ( ! empty($data['url_text_'.$stor_f.$val['code']]) && $data['url_text_'.$stor_f.$val['code']] !== 'Suma URL')
				{
					$auto_generate = false;
				}
				else
				{
					$auto_generate = true;
					$data['url_text_'.$stor_f.$val['code']] = $data['manufacturers_name'];
				}
	
				if ($set_type === 'edit')	// edit existing
					$seo->_UpdateRecord('manufacturer', $data['manufacturers_id'], $val['code'], $data,$auto_generate,'',$store_f_update);
			}
		}
		
		$set_perm = new item_permission($this->perm_array);
		$set_perm->_saveData($data, $data[$this->_master_key]);

		if ($objC->success && $objCD->success)
			$obj->success = true;
		else
			$obj->failed = true;

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:_set_bottom')) ? eval($plugin_code) : false;

		return $obj;
	}

	function _setImage($id, $file)
	{
		global $xtPlugin,$db,$language,$filter,$seo;

		if ($this->position !== 'admin') return false;

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:_setImage_top')) ? eval($plugin_code) : false;

		$data[$this->_master_key] = $id;
		$data['manufacturers_image'] = $file;

		$o = new adminDB_DataSave($this->_table, $data);
		$obj = $o->saveDataSet();

		$obj->totalCount = 1;
		if ($obj->success)
			$obj->success = true;
		else
			$obj->failed = true;

		($plugin_code = $xtPlugin->PluginCode('class.manufacturer.php:_setImage_bottom')) ? eval($plugin_code) : false;

		return $obj;
	}

	function _rebuildSeo($id, $params)
	{
		global $xtPlugin, $db, $language, $filter, $seo;

		if ($this->position !== 'admin') return false;

		$obj = new stdClass;
		$rs=$db->Execute(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema=? AND table_name=? AND COLUMN_NAME = ? ",
			array(_SYSTEM_DATABASE_DATABASE, $this->_table_lang, $this->_store_field)
		);
		$s_id ='';
		if ($rs->RecordCount()>0){
			$s_id = $this->_store_field;
		}
		$seo->_rebuildSeo($this->_table, $this->_table_lang, $this->_table_seo, '4', 'manufacturer', 'manufacturers_name', $this->_master_key, $id,$s_id);

		$obj->success = true;
		return $obj;
	}

	function _unset($id = 0)
	{
		global $db;

		if (empty($id)) return false;

		$id = (int)$id;

		$set_perm = new item_permission($this->perm_array);
		$set_perm->_deleteData($id);

		$db->Execute("DELETE FROM ".$this->_table." WHERE ".$this->_master_key." = ?", array($id));
		if ($this->_table_lang !== null)
		$db->Execute("DELETE FROM ".$this->_table_lang." WHERE ".$this->_master_key." = ?", array($id));
		saveDeletedUrl($id,4);
		$db->Execute("DELETE FROM ".$this->_table_seo." WHERE link_id = ? AND link_type = 4", array($id));
	}

	function _setStatus($id, $status)
	{
		global $db, $xtPlugin;

		$id = (int)$id;

		$db->Execute(
			"UPDATE ".$this->_table." SET manufacturers_status = ? WHERE manufacturers_id = ?",
			array($status, $id)
		);

		// activate/deactivate relating products
		$db->Execute(
			"UPDATE ".TABLE_PRODUCTS." SET products_status = ? WHERE manufacturers_id = ?",
			array($status, $id)
		);
	}
}