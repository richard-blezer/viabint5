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

class content {
	
	public $_table = TABLE_CONTENT;
	public $_table_lang = TABLE_CONTENT_ELEMENTS;
	protected $_table_seo = TABLE_SEO_URL;
	public $_master_key = 'content_id';
	public $_display_key = 'content_title';
	public $_image_key = 'content_image';
	public $store_field_exists = false;
	public $_store_field = 'content_store_id';
	
	function content($cID=0) {
		global $xtPlugin, $db, $customers_status, $store_handler,$current_content_id;
		($plugin_code = $xtPlugin->PluginCode('class.content:content_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if ($current_content_id!='') {
			$this->current_content_id = $current_content_id;
			$this->getLevel($this->current_content_id);
		}

		if($cID!=0){
			$this->data = $this->getHookContent($cID, 'true');
		}
		
		$this->getPermission();

		$this->BLOCKS = array ();
		$query = "SELECT * FROM " . TABLE_CONTENT_BLOCK;
		$record = $db->Execute($query);
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				($plugin_code = $xtPlugin->PluginCode('class.content:content_query')) ? eval($plugin_code) : false;
				$this->BLOCKS[$record->fields['block_id']] = $record->fields['block_tag'];
				$record->MoveNext();
			}$record->Close();
		}else{
			return false;
		}

	}

	function getLevel ($coID) {
		$this->level = array_reverse(array_merge($this->getPath($coID), array (0)));
	}


	function getPath ($coID, $path = array()) {
		$path[]= $coID ;
		$parentID = $this->getParentID($coID);
		if ($parentID != 0)
		$path = $this->getPath($parentID, $path);
		return $path;
	}

	function getParentID($coID) {
		global $db;

		$coID=(int)$coID;

		$rs = $db->Execute(
			"SELECT content_parent as parent_id FROM ".TABLE_CONTENT." WHERE content_id=?",
			array($coID)
		);
		if ($rs->RecordCount()==1) {
			return $rs->fields['parent_id'];
		}
	}

	function getContentBox ($block, $nested = false) {
		return $this->getChildcontent(0,0,$block,$nested);
	}

	/**
	 * get child contents of content
	 *
	 * @param int $catID
	 * @param int $level
	 * @return array
	 */
	function getChildcontent ($coID, $level = 0,$block, $nested = false) {
		$data = $this->_getContentLinksbyParent($block,$coID);
		$level_data = array();

		if (is_array($data))
		while (list(,$cont_data) = each($data)) {
			$count = count($level_data);
			$level_data[$count] = $cont_data;
			$level_data[$count]['level'] = ($level+1);
			$level_data[$count]['active'] = '0';
			if (!is_array($this->level)) $this->level = array();
			if (in_array($cont_data['id'], $this->level)) {
				$level_data[$count]['active'] = '1'; // set active
			}
				if ($cont_data['children']>0)
				{
					$child_level_data = $this->getChildcontent($cont_data['id'], $level+1,$block,$nested);
					if (is_data($child_level_data)) {
						if (!$nested)
							$level_data = array_merge($level_data, $child_level_data);
						else
							$level_data[$count]['sub'] = $child_level_data;
					}
				}
				else $level_data[$count]['sub'] = $child_level_data;
		}
		return $level_data;
	}

	function _getContentLinksbyParent($block,$coID) {
        global $db,$language,$xtLink,$xtPlugin,$store_handler;

        if (strlen($coID) == 0)
            $coID = $this->current_content_id;
        $coID = (int)$coID;

        $store_field_seo='';
        $store_field_ce ='';
        $rs=$db->Execute(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema=? AND table_name=? AND COLUMN_NAME = 'content_store_id' ",
            array(_SYSTEM_DATABASE_DATABASE, TABLE_CONTENT_ELEMENTS)
        );
        if ($rs->RecordCount()>0)
        {
            $store = $store_handler;
            $store_field_seo= " and su.store_id='" . (int)$store->shop_id . "'";
            $store_field_ce= " and ce.content_store_id='" . (int)$store->shop_id . "'";
        }$rs->Close();
		
		$query = "SELECT ce.*,
						 su.url_text,
						 su.language_code,
						 c.*,
						 (SELECT count(c1.content_id) FROM ".TABLE_CONTENT." c1 WHERE c1.content_parent = c.content_id and content_status=1 ) as children
						 FROM
						 " . TABLE_CONTENT_TO_BLOCK . " ctb INNER JOIN " . TABLE_CONTENT_BLOCK . " cb ON ctb.block_id = cb.block_id
			 			 INNER JOIN " . TABLE_CONTENT . " c ON c.content_id = ctb.content_id
			 			 INNER JOIN " . TABLE_SEO_URL . " su ON (su.link_id = ctb.content_id and su.link_type='3' ".$store_field_seo.")
			 			 ".$this->permission->_table."
			 			 INNER JOIN " . TABLE_CONTENT_ELEMENTS . " ce ON c.content_id = ce.content_id AND su.language_code = ce.language_code ".$store_field_ce."
						 WHERE cb.block_status = 1
						 and c.content_status = 1 and c.content_parent=?
						 and ce.language_code = ?
						 and ctb.block_id = ?
						 ".$store_field_seo.$store_field_ce."
						" . $this->permission->_where . "
						 ORDER BY c.content_sort";
		
		$record = $db->Execute($query, array($coID, $language->code, (int)$block));
		$data = array ();
		if($record->RecordCount() > 0){
			while(!$record->EOF){
			$conn = 'NOSSL';
			if ($record->fields['link_ssl']=='1') $conn='SSL';
				$link_array = array(
					'page'=>'content',
					'type'=>'content',
					'name'=>$record->fields['content_title'],
					'id'=>$record->fields['content_id'],
					'seo_url' => $record->fields['url_text'],
					'conn'=>$conn
				);

				$url = $xtLink->_link($link_array);

				$data[] = array_merge($record->fields, array (
					'id' => $record->fields['content_id'],
					'link' => $url,
					'title' => $record->fields['content_title'],
					'level' => 0,
					'children' => $record->fields['children'])
				);
			$record->MoveNext();
			}
		}
		($plugin_code = $xtPlugin->PluginCode('class.content:getContentLinksbyParent_bottom')) ? eval($plugin_code) : false;
		return $data;
	}

	function get_Content_Links($block){
		global $xtPlugin, $db, $xtLink, $language;

		$query = "SELECT ce.*,
		                 c.*,
						 su.url_text,
						 su.language_code
						 FROM
						 " . TABLE_CONTENT_TO_BLOCK . " ctb INNER JOIN " . TABLE_CONTENT_BLOCK . " cb ON ctb.block_id = cb.block_id
			 			 INNER JOIN " . TABLE_CONTENT . " c ON c.content_id = ctb.content_id
			 			 INNER JOIN " . TABLE_SEO_URL . " su ON (su.link_id = ctb.content_id and su.link_type='3')
			 			 INNER JOIN " . TABLE_CONTENT_ELEMENTS . " ce ON c.content_id = ce.content_id AND su.language_code = ce.language_code
			 			 ".$this->permission->_table."
						 WHERE cb.block_status = 1
						 and c.content_status = 1
						 and ce.language_code = ?
						 and ctb.block_id = ?
						 " . $this->permission->_where . "
						 GROUP BY c.content_id
						 ORDER BY c.content_sort";

		$record = $db->Execute($query, array($language->code, (int)$block));
		$array = array ();
		if($record->RecordCount() > 0){
			while(!$record->EOF){
                $conn = 'NOSSL';
                if ($record->fields['link_ssl']=='1') $conn='SSL';

                $link_array = array(
					'page'=>'content',
					'type'=>'content',
					'name'=>$record->fields['content_title'],
					'id'=>$record->fields['content_id'],
					'seo_url' => $record->fields['url_text'],
					'conn'=>$conn
				);

				$url = $xtLink->_link($link_array);

				$array[] = array_merge($record->fields, array (
					'id' => $record->fields['content_id'],
					'link' => $url,
					'title' => $record->fields['content_title'],
					'level' => 0)
				);
				$record->MoveNext();
			}$record->Close();
			($plugin_code = $xtPlugin->PluginCode('class.content:get_Content_Links_bottom')) ? eval($plugin_code) : false;
			return $array;
		}else{
			return false;
		}
	}


	function getPermission(){
		global $store_handler, $customers_status, $xtPlugin;

		$this->perm_array = array(
			'shop_perm' => array(
				'type'=>'shop',
				'key'=>$this->_master_key,
				'value_type'=>'content',
				'pref'=>'c'
			),
			'group_perm' => array(
				'type'=>'group_permission',
				'key'=>$this->_master_key,
				'value_type'=>'content',
				'pref'=>'c'
			)
		);

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':getPermission')) ? eval($plugin_code) : false;

		$this->permission = new item_permission($this->perm_array);

		return $this->perm_array;
	}

	function getActivatedBlocks($cid) {
		global $xtPlugin, $db;
		($plugin_code = $xtPlugin->PluginCode('class.content:getActivatedBlocks_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$array = array ();
		$query = "SELECT * FROM " . TABLE_CONTENT_BLOCK . " cb, " . TABLE_CONTENT_TO_BLOCK . " ctb WHERE ctb.content_id=? AND ctb.block_id = cb.block_id";
		$record = $db->Execute($query, array((int) $cid));
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				($plugin_code = $xtPlugin->PluginCode('class.content:getActivatedBlocks_query')) ? eval($plugin_code) : false;
				$array[] = array (
					'id' => $record->fields['block_id'],
					'text' => $record->fields['block_tag']
				);
				$record->MoveNext();
			}$record->Close();
			($plugin_code = $xtPlugin->PluginCode('class.content:getActivatedBlocks_bottom')) ? eval($plugin_code) : false;
			return $array;
		}else{
			return false;
		}
	}

	function getSystemHooks() {
		global $xtPlugin, $db;
		($plugin_code = $xtPlugin->PluginCode('class.content:getSystemHooks_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$array = array ();
		$array[] = array (
			'id' => '0',
			'text' => '- None -'
			);
			$query = "SELECT * FROM " . TABLE_CONTENT_BLOCK . " WHERE block_protected = '1'";
			$record = $db->Execute($query);
			if($record->RecordCount() > 0){
				while(!$record->EOF){
					($plugin_code = $xtPlugin->PluginCode('class.content:getSystemHooks_query')) ? eval($plugin_code) : false;
					$array[] = array (
						'id' => $record->fields['block_id'],
						'text' => $record->fields['block_tag']
					);
					$record->MoveNext();
				}$record->Close();
				($plugin_code = $xtPlugin->PluginCode('class.content:getSystemHooks_bottom')) ? eval($plugin_code) : false;
				return $array;
			}else{
				return false;
			}
	}

	function getHookName($hid) {
		global $xtPlugin, $db;
		($plugin_code = $xtPlugin->PluginCode('class.content:getHookName_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$query = "SELECT * FROM " . TABLE_CONTENT_BLOCK . " WHERE block_id=?";
		$record = $db->Execute($query, array((int)$hid));
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				$data = $record->fields;
				$record->MoveNext();
			}$record->Close();
			($plugin_code = $xtPlugin->PluginCode('class.content:getHookName_bottom')) ? eval($plugin_code) : false;
			return $data['block_tag'];
		}else{
			return false;
		}
	}

	function getBlocks() {
		global $xtPlugin, $db;
		($plugin_code = $xtPlugin->PluginCode('class.content:getBlocks_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$array = array ();
		$query = "SELECT * FROM " . TABLE_CONTENT_BLOCK . " WHERE block_protected = '0'";
		$record = $db->Execute($query);
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				($plugin_code = $xtPlugin->PluginCode('class.content:getBlocks_query')) ? eval($plugin_code) : false;
				$array[] = array (
						'id' => $record->fields['block_id'],
						'text' => $record->fields['block_tag'],
						'name' => $record->fields['block_tag']
				);
				$record->MoveNext();
			}$record->Close();
			($plugin_code = $xtPlugin->PluginCode('class.content:getBlocks_bottom')) ? eval($plugin_code) : false;
			return $array;
		}else{
			return false;
		}
	}

	function getContentTree() {
		global $xtPlugin, $db;
		($plugin_code = $xtPlugin->PluginCode('class.content:getContentTree_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$tree = array ();
		$tree[] = array (
			'id' => '0',
			'text' => TEXT_TOP
		);
		$tree = $this->walkTree(0, $tree);
		($plugin_code = $xtPlugin->PluginCode('class.content:getContentTree_bottom')) ? eval($plugin_code) : false;
		return $tree;
	}

	function walkTree($parent = '0', $tree, $prefix = '') {
		global $xtPlugin, $db, $language;
		($plugin_code = $xtPlugin->PluginCode('class.content:walkTree_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$record = $db->Execute(
			"select ce.content_title,ce.content_id from " . TABLE_CONTENT . " c, " . TABLE_CONTENT_ELEMENTS . " ce where c.content_id = ce.content_id and ce.language_code = ? and c.content_parent = ?",
			array($language->code, (int)$parent)
		);
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				($plugin_code = $xtPlugin->PluginCode('class.content:walkTree_query')) ? eval($plugin_code) : false;
				$tree[] = array (
					'id' => $record->fields['content_id'],
					'text' => $prefix . $record->fields['content_title'],
					'name' => $prefix . $record->fields['content_title']
				);
				$tree = $this->walkTree($record->fields['content_id'], $tree, $prefix . '..');

				$record->MoveNext();
			}$record->Close();
			($plugin_code = $xtPlugin->PluginCode('class.content:walkTree_bottom')) ? eval($plugin_code) : false;
			return $tree;
		}else{
			return false;
		}
	}

	// catalog functions
	function getHookContent($hook, $is_id = 'false') {
		global $xtPlugin, $db, $language,$xtLink,$store_handler;
		($plugin_code = $xtPlugin->PluginCode('class.content:getHookContent_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($is_id == 'true'){
			$qry = "and c.content_id = '".(int)$hook."'";
		}else{
			$qry = "and c.content_hook = '".(int)$hook."'";
		}

		($plugin_code = $xtPlugin->PluginCode('class.content:getHookContent_beforeQuery')) ? eval($plugin_code) : false;
		$add_store_id='';
		$add_store_id2='';
		$rs=$db->Execute(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema=? AND table_name=? AND COLUMN_NAME = 'store_id' ",
			array(_SYSTEM_DATABASE_DATABASE, TABLE_SEO_URL)
		);
		if ($rs->RecordCount()>0){
			$add_store_id = " and store_id = '".(int)$store_handler->shop_id."'";
			
		}
		$rs=$db->Execute(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema=? AND table_name=? AND COLUMN_NAME = 'content_store_id' ",
			array(_SYSTEM_DATABASE_DATABASE, TABLE_CONTENT_ELEMENTS)
		);
		if ($rs->RecordCount()>0){
			$add_store_id2 = " and ce.content_store_id = '".(int)$store_handler->shop_id."'";
		}
		$query = "SELECT *
		                      FROM " . TABLE_CONTENT_ELEMENTS . " ce,
		                      " . TABLE_CONTENT. " c
		                      ".$this->permission->_table."
		                      LEFT JOIN " . TABLE_SEO_URL . " su ON (su.link_id = c.content_id and su.link_type='3' and su.language_code = ? ".$add_store_id.")
		                      WHERE
		                      c.content_id = ce.content_id
		                      " . $qry . $this->permission->_where . "
		                      AND ce.language_code =?".$add_store_id2;

		$record = $db->Execute($query, array($language->code, $language->code));

		$shop_content_data = $record->fields;

		($plugin_code = $xtPlugin->PluginCode('class.content:getHookContent_afterQuery')) ? eval($plugin_code) : false;
		if ($shop_content_data['content_file'] != '') {	
			$file = _SRV_WEBROOT. _SRV_WEB_MEDIA_CONTENT . $shop_content_data['content_file'];
			if (file_exists($file)) {
				ob_start();
				if (strpos($shop_content_data['content_file'], '.txt'))
				echo '<pre>';
					
				include (_SRV_WEBROOT. _SRV_WEB_MEDIA_CONTENT . $shop_content_data['content_file']);
				if (strpos($shop_content_data['content_file'], '.txt'))
				echo '</pre>';
				$shop_content_data['content_body'] = ob_get_contents();
				ob_end_clean();
			}
		}
		if ($shop_content_data['content_heading']=='') $shop_content_data['content_heading']=$shop_content_data['content_title'];

        $popup_link = array(
			'page'=>'content',
			'type'=>'content',
			'params'=>'popup=true',
			'name'=>$record->fields['content_title'],
			'id'=>$record->fields['content_id'],
			'seo_url' => $record->fields['url_text'],
			'conn'=>"SSL"
		);
        $popup_link = $xtLink->_link($popup_link);
        
		$popup = new popup();

		$shop_content_data['title'] = $shop_content_data['content_heading'];
		$shop_content_data['text'] = $shop_content_data['content_body'];
		
		$shop_content_data['content_popup_link'] = $popup->getPopupLink($popup_link,TEXT_POPUP_PRINT);
		$shop_content_data['content_link'] = $xtLink->_link(array('page'=>'content', 'params'=>'coID='.$shop_content_data['content_id'].''));;
		($plugin_code = $xtPlugin->PluginCode('class.content:getHookContent_beforeArray')) ? eval($plugin_code) : false;

		$content_array = array('body'=>$shop_content_data['content_body'],'title'=>$shop_content_data['content_heading']);

		$tmp_content_array = array();
		foreach ($shop_content_data as $key => $var) {
			$tmp_content_array = array($key	=> $var);
			$content_array = array_merge($content_array, $tmp_content_array);
		}
		if ($is_id) {
			$subContent['subcontent'] = $this->getSubContent($hook);
			$content_array = array_merge($content_array,$subContent);
		}


		if ($shop_content_data['content_image']=='') $this->data['content_image'] = 'noimage.gif';
		$shop_content_data['content_image']= __CLASS__.':'.$this->data['content_image'];
		
		global $mediaImages, $mediaFiles;
		$media_data = $mediaFiles->get_media_data($shop_content_data['content_id'], __CLASS__, 'content', 'coID='.$shop_content_data['content_id']);
		$media_images = $mediaImages->get_media_images($shop_content_data['content_id'], __CLASS__);
	
        if (is_array($media_images))
		$content_array['more_images'] = $media_images['images'];
		
        if (is_array($media_data))
		$content_array['media_files'] = $media_data['files'];
		
		($plugin_code = $xtPlugin->PluginCode('class.content:getHookContent_bottom')) ? eval($plugin_code) : false;
		return $content_array;
	}

	function getSubContent($hook) {
		global $xtPlugin, $db, $xtLink, $language, $store_handler;
		($plugin_code = $xtPlugin->PluginCode('class.content:getSubContent_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$qry = "and c.content_parent = '".(int)$hook."'";

		($plugin_code = $xtPlugin->PluginCode('class.content:getSubContent_beforeQuery')) ? eval($plugin_code) : false;
        $query = "SELECT *
				FROM " . TABLE_CONTENT_ELEMENTS . " ce,
				" . TABLE_CONTENT. " c
				".$this->permission->_table."
				LEFT JOIN " . TABLE_SEO_URL . " su ON (su.link_id = c.content_id and su.link_type='3' and su.language_code = ?)
				WHERE
				c.content_id = ce.content_id
				" . $qry . $this->permission->_where . "
				AND ce.language_code=?
				AND ce.content_store_id=?
				AND su.store_id = ce.content_store_id";

        $subContent = array();
        $record = $db->Execute($query, array($language->code, $language->code, $store_handler->shop_id));
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				($plugin_code = $xtPlugin->PluginCode('class.content:getSubContent_afterQuery')) ? eval($plugin_code) : false;
				if ($record->fields['content_heading']=='') $record->fields['content_heading']=$record->fields['content_title'];
				$link_array = array('page'=>'content', 'params'=>'coID='.$record->fields['content_id'],'seo_url' => $record->fields['url_text']);
				$url = $xtLink->_link($link_array);
				$subContent[] = array('title'=>$record->fields['content_heading'],'body_short'=>$record->fields['content_body_short'],'link'=>$url);
				$record->MoveNext();
			}$record->Close();
			($plugin_code = $xtPlugin->PluginCode('class.content:getSubContent_bottom')) ? eval($plugin_code) : false;
			return $subContent;
		}else{
			return false;
		}

	}

	function contentList() {
		global $db, $language;

		$record = $db->Execute(
			"select ce.content_title,ce.content_id from " . TABLE_CONTENT . " c, " . TABLE_CONTENT_ELEMENTS . " ce where c.content_id = ce.content_id and ce.language_code = ? Group By c.content_id",
			array($language->code)
		);
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				$tree[] = array (
					'id' => $record->fields['content_id'],
					'text' => $record->fields['content_title']
				);

				$record->MoveNext();
			}$record->Close();
			return $tree;
		}else{
			return false;
		}

	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $language,$_content, $xtPlugin;
		
		if (StoreIdExists($this->_table_lang,$this->_store_field)) 
		{
			$this->store_field_exists=true;
		}
		$params = array();
		if ($this->store_field_exists)
			$params['languageStoreTab'] = true;

		$st = new multistore();
		$stores = $st->getStores();
		
		foreach ($stores as $store) {
			foreach ($language->_getLanguageList() as $key => $val) {
				$add_to_f='';
				if ($this->store_field_exists) $add_to_f = 'store'.$store['id'].'_';
				$header['content_body_'.$add_to_f.$val['code']] = array('type' => 'htmleditor');
				$header['content_body_short_'.$add_to_f.$val['code']] = array('type' => 'htmleditor');
				//$header['content_file_'.$add_to_f.$val['code']] = array('type' => 'dropdown','url'  => 'DropdownData.php?get=micropages');
				$header['content_file_'.$add_to_f.$val['code']] = array('type' => 'hidden');
				if(_SYSTEM_HIDE_SUMAURL=='true'){
					$header['url_text_'.$add_to_f.$val['code']] = array('type'=>'hidden');
				}else{
					$header['url_text_'.$add_to_f.$val['code']] = array('width'=>400);
				}

				$header['meta_keywords_'.$add_to_f.$val['code']] = array('width'=>400);
				$header['meta_title_'.$add_to_f.$val['code']] = array('width'=>400);
				$header['meta_description_'.$add_to_f.$val['code']] = array('type' => 'textarea','width'=>400,'height'=>60);
				$header['content_store_id_'.$add_to_f.$val['code']] = array('type' => 'hidden');
				$header['store_id_'.$add_to_f.$val['code']] = array('type' => 'hidden');
			}
		}

		$blocks = $this->getBlocks();
		$groupingPosition = 'BLOCKS';
		foreach ($blocks as $key => $val) {
			$grouping['block_'.$val['id']] = array('position' => $groupingPosition);
			$header['block_'.$val['id']] = array('type'=>'status');
			if (!defined('TEXT_BLOCK_'.$val['id'])) define('TEXT_BLOCK_'.$val['id'],$val['text']);
		}
        $grouping['block_permission_info'] = array('position' => $groupingPosition);

		($plugin_code = $xtPlugin->PluginCode('class.content.php:_getParams_blocks')) ? eval($plugin_code) : false;
		
		$params['grouping'] = $grouping;

		$header['link_ssl'] = array('type' => 'status');

		$header['content_sort'] = array('type' => 'textfield');

		$header['content_form'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=content_forms'
		);

		$header['content_hook'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=content_blocks'
		);

		$header['content_parent'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=content_list'
		);

		$header['content_id'] = array('type' => 'hidden');
		
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id'].";";
		else
          $js = "var edit_id = record.id;";

        $extF = new ExtFunctions();
        $mjs = $extF->_MultiButton_stm('BUTTON_START_SEO', 'doContentSeo');
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
		$params['display_searchPanel']  = true;
		$params['display_checkItemsCheckbox']  = true;
		$params['display_checkCol']  = true;
		$params['display_statusTrueBtn']  = true;
		$params['display_statusFalseBtn']  = true;
		$params['display_copyBtn']  = true;
		$add_to_f='';
		if ($this->store_field_exists) $add_to_f = 'store'.$st->shop_id.'_';
		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$params['include'] = array ('content_id', 'content_status', 'content_title_'.$add_to_f.$language->code);
		}else{
			$params['exclude'] = array('content_file_'.$add_to_f.$language->code);
		}

		return $params;
	}
	
	function _getSearchIDs($search_data) {
		global $db, $filter;
		$ids = array();
		$record = $db->Execute("SELECT content_id FROM " . TABLE_CONTENT_ELEMENTS . " WHERE content_title LIKE '%{$filter->_filter($search_data)}%'");
		if ($record->RecordCount() > 0) {
		
			while(!$record->EOF){
				$records = $record->fields;
				$ids[] = $records['content_id'];
				$record->MoveNext();
			} $record->Close();
		}
		
		return (empty($ids) ? '' : "content_id IN (" . join(",", $ids) . ")");
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;
		$obj = new stdClass;
		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}

		$ID = (int)$ID;

		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}
		
		if ($this->store_field_exists) 
		{
			$store_field= $this->_store_field;
		}
		
		$where = '';
		if($this->url_data['query']){
			$sql_where = $this->_getSearchIDs($this->url_data['query']);
			$where .= $sql_where;
		}
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $where, $this->sql_limit, $this->perm_array,'','',$store_field);

		if ($this->url_data['get_data']){
			$data = $table_data->getData();

		}elseif($ID){
			$data = $table_data->getData($ID);
			$rs = $db->Execute("SELECT block_id FROM ".TABLE_CONTENT_TO_BLOCK." WHERE content_id=?", array($data[0]['content_id']));
			$ids = array();
			while (!$rs->EOF) {
				$ids[]=$rs->fields['block_id'];
				$rs->MoveNext();
			}

			$blocks = $this->getBlocks();
			foreach ($blocks as $key => $val) {
				if (in_array($val['id'],$ids)) {
					$data[0]['block_'.$val['id']]='1';
				} else {
					$data[0]['block_'.$val['id']]='0';
				}
			}
            $data[0]['group_permission_info']=_getPermissionInfo();
            $data[0]['shop_permission_info']=_getPermissionInfo();
            $data[0]['block_permission_info']=_getPermissionInfo();

		}else{
			$data = $table_data->getHeader();
		}

		if($table_data->_total_count!=0 || !$table_data->_total_count)
		$count_data = $table_data->_total_count;
		else
		$count_data = count($data);

		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}

	function _set($data, $set_type = 'edit'){
		global $db, $language, $filter, $seo;

		$obj = new stdClass;

		foreach ($data as $key => $val) {
			if($val == 'on')
				$val = 1;
			$data[$key] = $val;
		}

		unset($data['content_image']);
		
		$oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$objC = $oC->saveDataSet();

		if ($set_type=='new') {	// edit existing
			$obj->new_id = $objC->new_id;
			$data = array_merge($data, array($this->_master_key=>$objC->new_id));
		}

		$oCD = new adminDB_DataSave($this->_table_lang, $data, true, __CLASS__,$this->store_field_exists);
		$objCD = $oCD->saveDataSet();

		// Build Seo URLS 
		$st = new multistore();
		$stores = $st->getStores();
		foreach ($stores as $store) {
			foreach ($language->_getLanguageList() as $key => $val) {
				
				$stor_f='';
				$store_f_update='';
				if ($this->store_field_exists) {
					$stor_f='store'.$store['id'].'_';
					$store_f_update = $store['id'];
				}
				else {
					$stor_f='';
					$store_f_update = '';
				}
				
				if($data['url_text_'.$stor_f.$val['code']] != '' && $data['url_text_'.$stor_f.$val['code']]!='Suma URL'){
					$auto_generate = false;
				}else{
					$auto_generate = true;
					$data['url_text_'.$stor_f.$val['code']] = $data['content_title_'.$stor_f.$val['code']];
				}
	
				if ($set_type=='edit')	// edit existing
					$seo->_UpdateRecord('content',$data['content_id'], $val['code'], $data, $auto_generate,'',$store_f_update);
			}
		}
		// save blocks
		if ($set_type!='new') {
			$blocks = $this->getBlocks();
			$db->Execute("DELETE FROM ".TABLE_CONTENT_TO_BLOCK." WHERE content_id=?", array($data['content_id']));
			foreach ($blocks as $key => $val) {
				if ($data['block_'.$val['id']]=='1') {
					$insert_array= array();
					$insert_array['content_id']=(int)$data['content_id'];
					$insert_array['block_id']=$val['id'];
					$db->AutoExecute(TABLE_CONTENT_TO_BLOCK,$insert_array);
				}
			}
		}

		$set_perm = new item_permission($this->perm_array);
		$set_perm->_saveData($data, $data[$this->_master_key]);

		if ($objC->success && $objCD->success) {
			$obj->success = true;
		} else {
			$obj->failed = true;
		}

		return $obj;
	}

	function _setImage($id, $file) {
		global $xtPlugin,$db,$language,$filter,$seo;
		if ($this->position != 'admin') return false;

		($plugin_code = $xtPlugin->PluginCode('class.content.php:_setImage_top')) ? eval($plugin_code) : false;

		$obj = new stdClass;

		$data[$this->_master_key] = $id;
		$data['content_image'] = $file;

		$o = new adminDB_DataSave($this->_table, $data);
		$obj = $o->saveDataSet();

		$obj->totalCount = 1;
		if ($obj->success) {
			$obj->success = true;
		} else {
			$obj->failed = true;
		}

		($plugin_code = $xtPlugin->PluginCode('class.content.php:_setImage_bottom')) ? eval($plugin_code) : false;
		return $obj;
	}	
	
	function _rebuildSeo($id, $params){
		global $xtPlugin,$db,$language,$filter,$seo;
		if ($this->position != 'admin') return false;

		$obj = new stdClass;
		$rs=$db->Execute(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema=? AND table_name=? AND COLUMN_NAME = ? ",
			array(_SYSTEM_DATABASE_DATABASE, $this->_table_lang, $this->_store_field)
		);
		$s_id ='';
		if ($rs->RecordCount()>0){
			$s_id = $this->_store_field;
		}
		$seo->_rebuildSeo($this->_table, $this->_table_lang, $this->_table_seo, '3', 'content', 'content_title', $this->_master_key, $id,$s_id);		
	
		$obj->success = true;
		return $obj;			
			
	}	
	
	function _unset($id = 0) {
		global $db;
		if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if (!is_int($id)) return false;

		$set_perm = new item_permission($this->perm_array);
		$set_perm->_deleteData($id);

		$db->Execute("DELETE FROM ". TABLE_CONTENT ." WHERE ".$this->_master_key." = ?", array($id));
		$db->Execute("DELETE FROM ". TABLE_CONTENT_ELEMENTS ." WHERE ".$this->_master_key." = ?", array($id));
		$db->Execute("DELETE FROM ". TABLE_CONTENT_TO_BLOCK ." WHERE ".$this->_master_key." = ?", array($id));
		saveDeletedUrl($id,3);
		$db->Execute("DELETE FROM ". TABLE_SEO_URL ." WHERE link_type = '3' and link_id = ?", array($id));
	}

	function _setStatus($id, $status) {
		global $db,$xtPlugin;
		$id = (int)$id;

		$db->Execute("update " . TABLE_CONTENT . " set content_status = ? where content_id = ?", array($status, $id));
	}
	
	function _copy($ID){
		global $xtPlugin,$db,$language,$filter,$seo,$customers_status;
		if ($this->position != 'admin') return false;

		$ID=(int)$ID;
		if (!is_int($ID)) return false;

		($plugin_code = $xtPlugin->PluginCode('class.content.php:_copy_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$obj = new stdClass;

		// Content Data:
		$c_table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $this->perm_array, 'false');
		$c_data = $c_table_data->getData($ID);
        $c_data = $c_data[0];
        
        $old_content = $c_data[$this->_master_key];

		unset($c_data[$this->_master_key]);

		$oC = new adminDB_DataSave($this->_table, $c_data);
		$objC = $oC->saveDataSet();

		$obj->new_id = $objC->new_id;
		$c_data[$this->_master_key] = $objC->new_id;

		$oCD = new adminDB_DataSave($this->_table_lang, $c_data, true);
		$objCD = $oCD->saveDataSet();

		// Block Data:
		$b_table_data = new adminDB_DataRead(TABLE_CONTENT_TO_BLOCK, null, null, 'id', 'content_id='.$old_content, '', '', 'false');
		$b_data = $b_table_data->getData();

		for ($i = 0; $i < count($b_data); $i++) {
			unset($b_data[$i]['id']);
			$b_data[$i]['content_id'] = $obj->new_id;
       		$oB = new adminDB_DataSave(TABLE_CONTENT_TO_BLOCK, $b_data[$i], false, __CLASS__);
        	$objC2B = $oB->saveDataSet();
	    }

	    $set_perm = new item_permission($this->perm_array);
		$set_perm->_saveData($c_data, $c_data[$this->_master_key]);
	    
	    ($plugin_code = $xtPlugin->PluginCode('class.content.php:_copy_bottom')) ? eval($plugin_code) : false;

		$obj = new stdClass;
		$obj->success = true;
		return $obj;
	}
}