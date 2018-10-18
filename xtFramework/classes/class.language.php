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

class language extends language_content{

	public $default_language = _STORE_LANGUAGE;

	protected $_table = TABLE_LANGUAGES;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'languages_id';

	function language($code = ''){
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.language.php:_language_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$this->getPermission();
		$this->_getLanguage($code);

	}

	function getPermission(){
		global $store_handler, $customers_status, $xtPlugin;

		$this->perm_array = array(
			'shop_perm' => array(
				'type'=>'shop',
				'key'=>$this->_master_key,
				'value_type'=>'language',
				'pref'=>'l'
			)
		);
		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':getPermission')) ? eval($plugin_code) : false;
		$this->permission = new item_permission($this->perm_array);
		return $this->perm_array;
	}

	function _getLanguage($code = ''){
		global $db, $xtPlugin,$filter;

		($plugin_code = $xtPlugin->PluginCode('class.language.php:_getLanguage_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(isset($_GET['language'])){
			$_SESSION['selected_language']=$_GET['language'];
		}
		
		if ($code=='') {
			if(!empty($_SESSION['selected_language'])){
				$code = $_SESSION['selected_language'];
			}elseif(!empty($_SESSION['customer']->customer_info['customers_default_language'])){
				$code = $_SESSION['customer']->customer_info['customers_default_language'];
			}else{
				$code = $this->default_language;
			}
		}

		if($this->_checkStore($code, 'store')){
			$code = $code;
		}else{
			$code = $this->default_language;
		}
        $code = $filter->_filter($code,'lng');       
		$data = $this->_buildData($code);
        $data['environment_language']= $code;
        if ($data['content_language']!=$code) {
           $data['code']= $data['content_language'];
        }
        
		($plugin_code = $xtPlugin->PluginCode('class.language.php:_getLanguage_bottom')) ? eval($plugin_code) : false;
		while (list ($key, $value) = each($data)) {
			$this->$key = $value;
		}
	}


	/**
	 * set locale value for language
	 *
	 */
	function _setLocale() {
		if ($this->setlocale!='') {
			$locale_array = explode(';',$this->setlocale);
			if (is_array($locale_array)) {
				@setlocale(LC_TIME,$locale_array);
			}
		}

	}

	function _buildData($lang){
		global $db, $xtPlugin, $store_handler;

		($plugin_code = $xtPlugin->PluginCode('class.language.php:_buildData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$record = $db->CacheExecute("SELECT * FROM " . TABLE_LANGUAGES . " WHERE code = ?", array($lang));

		if($record->RecordCount() > 0){
			while(!$record->EOF){
				$data = $record->fields;
				$record->MoveNext();
			}$record->Close();
			($plugin_code = $xtPlugin->PluginCode('class.language.php:_buildData_bottom')) ? eval($plugin_code) : false;
			return $data;
		}else{
			return false;
		}
	}

    function getLanguageSwitchLinks($languages) {
        global $db,$page,$xtLink, $xtPlugin;

        switch($page->page_name) {

            case 'content';
                    $links = array();
                    global $current_content_id,$shop_content_data;
                    $query = "SELECT * FROM ".TABLE_SEO_URL." WHERE link_type='3' and link_id=?";
                    $rs = $db->Execute($query, array((int)$current_content_id));
                    while (!$rs->EOF) {
						$link_array = array(
							'page'=>'content',
							'type'=>'content',
							'name'=>$shop_content_data['content_title'],
							'id'=>$shop_content_data['content_id'],
							'seo_url' => $rs->fields['url_text'],
							'conn'=>$shop_content_data['link_ssl']
						);
						$url = $xtLink->_link($link_array);
						$links[$rs->fields['language_code']]=$url;
						$rs->MoveNext();
                    }

                    break;
            case 'product':
            case 'reviews':
                $links = array();
                global $current_product_id,$p_info;

                $query = "SELECT * FROM ".TABLE_SEO_URL." WHERE link_type='1' and link_id=?";

                $rs = $db->Execute($query, array($p_info->pID));
                while (!$rs->EOF) {
                    $link_array = array(
						'page'=>'product',
						'type'=>'product',
						'name'=>$p_info->data['products_name'],
						'id'=>$p_info->pID,
						'seo_url' => $rs->fields['url_text']
					);
                    $url = $xtLink->_link($link_array);
                    $links[$rs->fields['language_code']]=$url;
                    $rs->MoveNext();
                }
                break;
           case 'categorie':
                $links = array();
                global $category, $current_category_id;

                $query = "SELECT * FROM ".TABLE_SEO_URL." WHERE link_type='2' and link_id=?";

                $rs = $db->Execute($query, array($current_category_id));
                while (!$rs->EOF) {
                    $link_array = array(
						'page'=>'categorie',
						'type'=>'categorie',
						'name'=>$category->data['categories_name'],
						'id'=>$current_category_id,
						'seo_url' => $rs->fields['url_text']
					);
                    $url = $xtLink->_link($link_array);
                    $links[$rs->fields['language_code']]=$url;
                    $rs->MoveNext();
                }
                break;     
            case 'manufacturers':
                global $manufacturer, $current_manufacturer_id;
                $man = array('manufacturers_id' => $current_manufacturer_id);
                $man_data = $manufacturer->buildData($man);
                $query = "SELECT * FROM ".TABLE_SEO_URL." WHERE link_type='4' and link_id=?";
                $rs = $db->Execute($query, array($current_manufacturer_id));
                while (!$rs->EOF) {
                    $link_array = array(
						'page'=>'manufacturers',
						'type'=>'manufacturers',
						'name'=>$man_data['manufacturers_name'],
						'id'=>$current_manufacturer_id,
						'seo_url' => $rs->fields['url_text']
					);
                    $url = $xtLink->_link($link_array);
                    $links[$rs->fields['language_code']]=$url;
                    $rs->MoveNext();
                }
                break; 
            default:

                ($plugin_code = $xtPlugin->PluginCode('class.language.php:getLanguageSwitchLinks_bottom')) ? eval($plugin_code) : false;
                if(isset($plugin_return_value))
                return $plugin_return_value;
            }

        return $links;
    }

	function _getLanguageList($list_type = '',$index=''){
		global $db, $xtPlugin, $store_handler;

		if ($list_type=='admin' && $index=='') {
			if (isset($this->_cache_language_list)) {
				return $this->_cache_language_list;
			}
		}

		($plugin_code = $xtPlugin->PluginCode('class.language.php:_getLanguagelist_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($list_type=='store' || USER_POSITION=='store'){
			$table = $this->permission->_table;
			$where = $this->permission->_where;
		}

		$qry_where = " where l.languages_id != '' ".$where."";
       	
       	if ($list_type!='all')
        	$qry_where .= " and l.language_status = '1'";

		($plugin_code = $xtPlugin->PluginCode('class.language.php:_getLanguagelist_qry')) ? eval($plugin_code) : false;

		$record = $db->CacheExecute("SELECT * FROM " . TABLE_LANGUAGES . " l ".$table." ".$qry_where." order by sort_order");
		while(!$record->EOF){
			$record->fields['id'] = $record->fields['code'];
			$record->fields['text'] = $record->fields['name'];
			$record->fields['icon'] = $record->fields['image'];
            $record->fields['edit'] = $record->fields['allow_edit'];

			if ($index=='') $data[] = $record->fields;
            if ($index=='code') $data[$record->fields['code']] = $record->fields;   
			$record->MoveNext();
		}$record->Close();

		($plugin_code = $xtPlugin->PluginCode('class.language.php:_getLanguagelist_bottom')) ? eval($plugin_code) : false;

		// save if admin
		if ($list_type=='admin') {
			$this->_cache_language_list = $data;
		}

		return $data;
	}


	function _checkStore($code, $list_type='store'){
		global $xtPlugin, $db, $filter;

		($plugin_code = $xtPlugin->PluginCode('class.language.php:_checkStore_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($list_type=='store'){
			$table = $this->permission->_table;
			$where = $this->permission->_where;
		}
        
        if(!$this->_checkLanguageCode($code)){
           return false; 
        }
        
        $record = $db->CacheExecute(
			"SELECT code,language_status FROM " . TABLE_LANGUAGES . " l ".$table." where code = ? ".$where."",
			array($code)
		);
        if($record->RecordCount() > 0){
            if ($record->fields['language_status']=='0') return false;
            return true;
        }else{
            return false;
        }
	}
    
    public function _checkLanguageCode($code){
        global $db;
        $lang_arr = $this->_getLanguageList('store');
        $iscode = false;
        foreach($lang_arr as $k=>$v){
            if($v['code'] == $code){
                $iscode = true;
            }
        }
        
        return $iscode;
    }

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();

		$header['languages_id'] = array('type' => 'hidden');

		$header['image'] = array('type' => '');
		$header['code'] = array('max' => '2','min'=>'2');
		$header['default_currency'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=currencies','text'=>TEXT_CURRENCY_SELECT
		);
        
        $header['content_language'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=language_codes&skip_empty=true'
		);
        $header['allow_edit'] = array('type' => 'status');                    
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = 'sort_order';
		$params['languageTab']    = false;

		$params['display_checkCol']  = true;   
		$params['display_adminActionStatus'] = false;
        $params['display_statusTrueBtn']  = true;
        $params['display_statusFalseBtn']  = true;

		$params['display_newBtn'] = false;

		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$params['include'] = array ('languages_id','language_status', 'code', 'name', 'sort_order', 'language_charset', 'default_currency','language_content');
		}

		$rowActions[] = array('iconCls' => 'export_language', 'qtipIndex' => 'qtip1', 'tooltip' => 'Export');
		if ($this->url_data['edit_id'])
		$js = "var edit_id = ".$this->url_data['edit_id'].";";
		else
		$js = "var edit_id = record.id";
		$js.= "
		         var conn = new Ext.data.Connection();
                 conn.request({
                 url: 'row_actions.php',
                 method:'GET',
                 params: {'language_id': edit_id,'type': 'export_language_yml'},
                 success: function(responseObject) {
                           Ext.MessageBox.alert('Message', '".TEXT_LANG_EXPORT_SUCCESS."');
                          },
                 });";
		$rowActionsFunctions['export_language'] = $js;

        $rowActions[] = array('iconCls' => 'export_nottranslated', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_EXPORT_NOTTRANSLATED);
        if ($this->url_data['edit_id'])
        $js = "var edit_id = ".$this->url_data['edit_id'].";";
        else
        $js = "var edit_id = record.id";
        $js.= "
                 var conn = new Ext.data.Connection();
                 conn.request({
                 url: 'row_actions.php',
                 method:'GET',
                 params: {'language_id': edit_id,'type': 'export_nottranslated'},
                 success: function(responseObject) {
                           Ext.MessageBox.alert('Message', '".TEXT_LANG_EXPORT_SUCCESS."');
                          },
                 });";
        $rowActionsFunctions['export_nottranslated'] = $js;

		$js = "var edit_id = record.data.languages_id;";
		$js .= "Ext.Msg.show({
   title:'".TEXT_START_SEO."',
   msg: '".TEXT_START_ASK_SEO."',
   buttons: Ext.Msg.YESNO,
   animEl: 'elId',
   fn: function(btn){runSeoCheck(edit_id,btn);},
   icon: Ext.MessageBox.QUESTION
});";

		$js = "function runSeoCheck(edit_id,btn){
	  		var edit_id = edit_id;
	  		if (btn == 'yes') {
	  			addTab('row_actions.php?type=api_seo_url_frame&id='+edit_id,'... checking SEO URL ...');  
			}

		};";

		$params['rowActions']             = $rowActions;
		$params['rowActionsFunctions']    = $rowActionsFunctions;

		$extF = new ExtFunctions();
		$js = "addTab('adminHandler.php?load_section=language_import&new=true','".TEXT_LANGUAGE_IMPORT."');";
		$UserButtons['options_add'] = array('text'=>'TEXT_LANGUAGE_IMPORT', 'style'=>'options_add', 'icon'=>'add.png', 'acl'=>'edit', 'stm' => $js);
		$js = "addTab('adminHandler.php?load_section=language_sync&new=true','".TEXT_DOWNLOAD_TRANSLATIONS."');";
		$UserButtons['download_translations'] = array('text' => 'TEXT_DOWNLOAD_TRANSLATIONS', 'style' => 'download_translations', 'icon' => 'door_out.png', 'acl' => '', 'stm' => $js);
		
		$params['display_options_addBtn'] = true;
		$params['display_download_translationsBtn'] = true;
		$params['UserButtons']      = $UserButtons;

		return $params;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;
		$obj = new stdClass;
		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $this->perm_array);

		if ($this->url_data['get_data']){
			$data = $table_data->getData();
            foreach ($data as $key => $val) {
                
                $rs = $db->Execute(
					"SELECT count(*) as count FROM ".TABLE_LANGUAGE_CONTENT." WHERE language_code=? AND translated='1'",
					array($val['code'])
				);
                $translated = $rs->fields['count'];
                $rs = $db->Execute(
					"SELECT count(*) as count FROM ".TABLE_LANGUAGE_CONTENT." WHERE language_code=? AND translated='0'",
					array($val['code'])
				);
                $total=$rs->fields['count']+$translated;
                $data[$key]['language_content']=$total.' ('.$rs->fields['count'].'/'.$translated.')';
            }
		}elseif($ID){
			$data = $table_data->getData($ID);
			$data[0]['shop_permission_info']=_getPermissionInfo();
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

	function _set($data, $set_type = 'edit') {
		global $db,$language,$filter;

		$obj = new stdClass;
		$o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$obj = $o->saveDataSet();
		
		if (($data['language_status']==1) && ($data['allow_edit']==1))
		{
			$this->addLanguageToConfiguration($data);	
		}
		
		$set_perm = new item_permission($this->perm_array);
		$set_perm->_saveData($data, $data[$this->_master_key]);

		return $obj;
	}
    
	function addLanguageToConfiguration($data)
	{
		global $db;
		$record = $db->Execute("SELECT * FROM " . TABLE_MANDANT_CONFIG );

		if($record->RecordCount() > 0) {
			while(!$record->EOF){
				if (!$data['shop_'.$record->fields['shop_id']]) {
					$record2 = $db->Execute("SELECT * FROM " . TABLE_CONFIGURATION_MULTI.$record->fields['shop_id']." WHERE config_key='_store_email_footer_txt_".$data['code']."' " );
					if($record2->RecordCount() == 0)
					{
						$db->Execute("INSERT INTO " . TABLE_CONFIGURATION_MULTI.$record->fields['shop_id']. " (`config_key`, `config_value`, `group_id`, `sort_order`, `last_modified`, `date_added`, `type`, `url`) VALUES ('_store_email_footer_txt_".$data['code']."', 'DemoShop GmbH\nGeschäftsführer: Max Muster und Fritz Beispiel\n\nMax Muster Strasse 21-23\nD-0815 Musterhausen\nE-Mail: max.muster@muster.de\n\nHRB 123456\nAmtsgericht Musterhausen\nUStid-Nr. DE 000 111 222', 12, 8, NULL, '0000-00-00 00:00:00', 'textarea', '')");
						
						$db->Execute("INSERT INTO " . TABLE_CONFIGURATION_MULTI.$record->fields['shop_id']. " (`config_key`, `config_value`, `group_id`, `sort_order`, `last_modified`, `date_added`, `type`, `url`) VALUES ('_store_email_footer_html_".$data['code']."', 'DemoShop GmbH\nGeschäftsführer: Max Muster und Fritz Beispiel\n\nMax Muster Strasse 21-23\nD-0815 Musterhausen\nE-Mail: max.muster@muster.de\n\nHRB 123456\nAmtsgericht Musterhausen\nUStid-Nr. DE 000 111 222', 12, 8, NULL, '0000-00-00 00:00:00', 'textarea', '')");
					}
					$record2->Close();
				
				}
				  
				$record->MoveNext();
			}$record->Close();
		}
		
	}
	
    function _setStatus($id, $status) {
        global $db,$xtPlugin;

        $id = (int)$id;
        if (!is_int($id)) return false;

        $db->Execute(
			"update " . TABLE_LANGUAGES . " set language_status = ? where languages_id = ?",
			array($status, $id)
		);

    }

	function _seoCheck($lng_id) {
		global $xtLink;
		
		if (isset($_GET['limit_lower'])) {
			$this->limit_lower = (int)$_GET['limit_lower'];
		}

		if (isset($_GET['limit_upper'])) {
			$this->limit_upper = (int)$_GET['limit_upper'];
		}

		if (isset($_GET['counter'])) {
			$this->counter = (int)$_GET['counter'];
		}

		$this->counter+=10;

		if ($this->counter>50) {
			echo $this->_htmlHeader();
			echo '- export finished -<br />';
			echo '- exported datasets '.$this->count.'<br />';
			echo $this->_htmlFooter();
		} else {

			$params = 'type=api_seo_url_check&id='.$lng_id.'&sess_name='.session_name().'&sess_id='.session_id().
				'&limit_lower='.$this->limit_lower.
				'&limit_upper='.$this->limit_upper.
				'&counter='.$this->counter;
			echo $this->_displayHTML($xtLink->_adminlink(array('default_page'=>'xtAdmin/row_actions.php', 'params'=>$params)),$limit_lower,$limit_upper,$this->count);
		}
	}

	function _displayHTML($next_target,$lower=1,$upper=0,$total=0) {

		$process = $lower / $total * 100;
		if ($process>100) $process=100;

		$html='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="refresh" content="5; URL='.$next_target.'" />
<title>..import / export..</title>
<style type="text/css">
<!--
.process_rating_light .process_rating_dark {
background:#FF0000;
height:15px;
position:relative;
}

.process_rating_light {
height:15px;
margin-right:5px;
position:relative;
width:150px;
border:1px solid;
}

-->
</style>
</head>
<body>
<div class="process_rating_light"><div class="process_rating_dark" style="width:'.$process.'%">'.round($process,0).'%</div></div>
Processing '.$lower.' to '.$upper.' of total '.$total.'
</body>
</html>';
		return $html;

	}


	function _htmlHeader() {
		$html='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>..import / export..</title>
<style type="text/css">
<!--
ul.stack {padding:5px}
ul.stack li {}
ul.stack li.success {list-style:none; padding:5px 0px 2px 20px; background-image:url(xtAdmin/images/icons/accept.png); background-repeat:no-repeat; background-position:0px 4px;}
ul.stack li.error {list-style:none; padding:5px 0px 2px 20px; background-image:url(xtAdmin/images/icons/cross.png); background-repeat:no-repeat; background-position:0px 4px;}
-->
</style>
</head>
<body>';
		return $html;
	}

	function _htmlFooter() {
		$html ='</body></html>';
		return $html;
	}

	function _addLanguage() {
		global $db,$xtPlugin,$language;
	}

	function _unset($id = 0) {
		global $db,$xtPlugin;
		if ($id == 0) return false;
		$id = (int)$id;
		if (!is_int($id)) return false;
		$new_id = '';

		$query = "SELECT code FROM " . TABLE_LANGUAGES . " where languages_id =?";

		$record = $db->Execute($query, array($id));
		if($record->RecordCount() > 0){
			$current_code = $record->fields['code'];
		}else{
			return false;
		}
        
        // dont remove last language
        $query = "SELECT code FROM " . TABLE_LANGUAGES;
        $record = $db->Execute($query);
        if ($record->RecordCount()==1) return false;  
        

		$set_perm = new item_permission($this->perm_array);
		$set_perm->_deleteData($id);

		$db->Execute("DELETE FROM ". TABLE_LANGUAGES ." WHERE code = ?", array($current_code));
		$db->Execute("DELETE FROM ". TABLE_LANGUAGE_CONTENT ." WHERE language_code = ?", array($current_code));

		$db->Execute("DELETE FROM ". TABLE_COUNTRIES_DESCRIPTION ." WHERE language_code = ?", array($current_code));
		$db->Execute("DELETE FROM ". TABLE_MAIL_TEMPLATES_CONTENT ." WHERE language_code = ?", array($current_code));
		$db->Execute("DELETE FROM ". TABLE_MANUFACTURERS_DESCRIPTION ." WHERE language_code = ?", array($current_code));
		$db->Execute("DELETE FROM ". TABLE_MEDIA_DESCRIPTION ." WHERE language_code = ?", array($current_code));

		$db->Execute("DELETE FROM ". TABLE_PAYMENT_DESCRIPTION ." WHERE language_code = ?", array($current_code));
		$db->Execute("DELETE FROM ". TABLE_SHIPPING_DESCRIPTION ." WHERE language_code = ?", array($current_code));

		$db->Execute("DELETE FROM ". TABLE_PRODUCTS_DESCRIPTION ." WHERE language_code = ?", array($current_code));
		$db->Execute("DELETE FROM ". TABLE_CATEGORIES_DESCRIPTION ." WHERE language_code = ?", array($current_code));
		$db->Execute("DELETE FROM ". TABLE_SEO_URL ." WHERE language_code = ?", array($current_code));

		// update default values
		$query = "SELECT code FROM " . TABLE_LANGUAGES . " LIMIT 1";
		$rs = $db->Execute($query);
		$new_code=$rs->fields['code'];
		$db->Execute(
			"UPDATE ".TABLE_CUSTOMERS." SET customers_default_language=? WHERE customers_default_language=?",
			array($new_code, $current_code)
		);

		($plugin_code = $xtPlugin->PluginCode('class.language.php:_delete_bottom')) ? eval($plugin_code) : false;
	}
}