<?php
/*
 #########################################################################
#                       xt:Commerce  4.2 Shopsoftware
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

class seo_regenerate {
	
	const URL_TYPE_PRODUCT 		= 1;
	const URL_TYPE_CATEGORY 	= 2;
	const URL_TYPE_CONTENT 		= 3;
	const URL_TYPE_MANUFACTURER = 4;
	
	const URL_PROCESS_BATCH = 100;
	
	public $position = null;
	
	public function setPosition($position) {
		$this->position = $position;
	}
	
	public function _getParams() {
		$params = array();
		$header = array();
		
		$params['header']         = $header;
		$params['master_key']     = 'master_key';
		$params['default_sort']   = 'store_id';
		$params['SortField']      = 'store_id';
		$params['SortDir']        = "ASC";
		
		$params['master_key'] 	= 'master_key';
		$params['exclude'] 		= array('master_key');
		$params['GroupField']	= 'store_name';
		$params['display_editBtn'] = false;
		$params['display_deleteBtn'] = false;
		$params['display_newBtn'] = false;
		
		$rowActions[] = array('iconCls' => 'start', 'qtipIndex' => 'qtip1', 'tooltip' => 'Run');
		
		$js = "var edit_id = record.data.ei_id;";
		
		$js .= "Ext.Msg.show({
			   title:'".TEXT_START."',
			   msg: '".TEXT_START_ASK."',
			   buttons: Ext.Msg.YESNO,
			   animEl: 'elId',
				 fn: function(btn) {if (btn == 'yes') {addTab('row_actions.php?type=seo_regenerate&seckey="._SYSTEM_SECURITY_KEY."&store_id='+record.data.store_id+'&url_type='+record.data.url_type,'... Regenerating SEO ...');}},
			   icon: Ext.MessageBox.QUESTION
			});";
		
		$rowActionsFunctions['start'] = $js;
		
		$params['rowActions']             = $rowActions;
		$params['rowActionsFunctions']    = $rowActionsFunctions;
		
		return $params;
	}
	
	protected function _getUrlTypes() {
		global $xtPlugin;
		
		$return = array(
			self::URL_TYPE_PRODUCT 		=> TEXT_PRODUCT,
			self::URL_TYPE_CATEGORY 	=> TEXT_CATEGORY,
			self::URL_TYPE_CONTENT 		=> TEXT_CONTENT,
			self::URL_TYPE_MANUFACTURER => TEXT_MANUFACTURER,
		);
		
		($plugin_code = $xtPlugin->PluginCode('class.getUrlTypes.php:getUrlTypes_bottom')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value)) {
			return $plugin_return_value;
		}
		return $return;
	}
	
	public function _get($id = 0) {
		global $store_handler, $xtPlugin;
		
		if ($this->position != 'admin') { 
			return false;
		}
		
		$obj = new stdClass();
		$data = array();
		
		($plugin_code = $xtPlugin->PluginCode('class.seo_regenerate.php:_get_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value)) {
			return $plugin_return_value;
		}
		$stores = $store_handler->getStores();
		$urlTypes = $this->_getUrlTypes();
		
		if ($this->url_data['get_data']) {
			foreach ($stores as $store) {
				foreach ($urlTypes as $urlType => $urlTypeName) {
					$data[] = array(
						'master_key' => '',
						'url_type_text' => $urlTypeName,
						'store_id' => $store['id'],
						'url_type' => $urlType,
						'store_name' => $store['text'],
					);
				}	
			}
		} else {
			$data[] = array(
				'master_key' => '',
				'url_type_text' => '',
				'store_id' => '',
				'url_type' => '',
				'store_name' => '',
			);
		}
		
		$obj->totalCount = count($data);
		$obj->data = $data;
		
		return $obj;
	}
	
	public function regenerateUrls($store_id, $url_type, $offset) {
		global $xtLink, $logHandler;
		
		if ($offset == 0) {
			$logHandler->_addLog('info', 'xt_seo_regenerate', 0, array('message' => 'Regeneration started for store ' . $store_id . ' and type ' . $url_type));
		}
		$params['store_id'] = $store_id;
		$params['url_type'] = $url_type;
		$params['offset'] = $offset + self::URL_PROCESS_BATCH;
		$params['seo_regenerate'] = 1;
        $params['seckey'] = $_GET['seckey'];
		$totalRecords = $this->_getTotalRecordsForType($url_type);
		$batch = self::URL_PROCESS_BATCH;
		
		if ($totalRecords < $batch) {
			$batch = $totalRecords;
		}
		$this->_processItems($url_type, $store_id, $offset, $batch);
		
		$iframe_target = $xtLink->_adminlink(array('default_page'=>'cronjob.php','conn'=>'SSL', 'params'=>http_build_query($params)));
		if ($offset >= $totalRecords) {
			$logHandler->_addLog('info', 'xt_seo_regenerate', 0, array('message' => 'Regeneration finished for store ' . $store_id . ' and type ' . $url_type));
			echo $this->_htmlHeader();
			echo '- Regenerating finished -<br />';
			echo '- Regenerated '.$totalRecords.' items<br />';
			echo $this->_htmlFooter();
		} else {
			echo $this->_displayHTML($iframe_target,$offset,$offset + $batch,$totalRecords);
		}
	}
	
	protected function _processItems($type, $store_id, $offset, $batch) {
		global $db, $seo;

        switch ($type) {
            case self::URL_TYPE_PRODUCT:
                $sql = "SELECT products_id AS N FROM `" . TABLE_PRODUCTS . "` LIMIT ? OFFSET ?";
                $rs = $db->Execute($sql, array((int)$batch, (int)$offset));
                $table = TABLE_PRODUCTS;
                $table_lang = TABLE_PRODUCTS_DESCRIPTION;
                $table_seo = TABLE_SEO_URL;
                $seo_type = 'product';
                $link_name = 'products_name';
                $master_key = 'products_id';
                $store_id_field = 'products_store_id';

                break;
            case self::URL_TYPE_CATEGORY:
                $sql = "SELECT categories_id AS N FROM `" . TABLE_CATEGORIES . "` LIMIT ? OFFSET ?";
                $rs = $db->Execute($sql, array((int)$batch, (int)$offset));
                $table = TABLE_CATEGORIES;
                $table_lang = TABLE_CATEGORIES_DESCRIPTION;
                $table_seo = TABLE_SEO_URL;
                $seo_type = 'category';
                $link_name = 'categories_name';
                $master_key = 'categories_id';
                $store_id_field = 'categories_store_id';

                break;
            case self::URL_TYPE_CONTENT:
                $sql = "SELECT content_id AS N FROM `" . TABLE_CONTENT . "` LIMIT ? OFFSET ?";
                $rs = $db->Execute($sql, array((int)$batch, (int)$offset));
                $table = TABLE_CONTENT;
                $table_lang = TABLE_CONTENT_ELEMENTS;
                $table_seo = TABLE_SEO_URL;
                $seo_type = 'content';
                $link_name = 'content_title';
                $master_key = 'content_id';
                $store_id_field = 'content_store_id';

                break;
            case self::URL_TYPE_MANUFACTURER:
                $sql = "SELECT manufacturers_id AS N FROM `" . TABLE_MANUFACTURERS . "` LIMIT ? OFFSET ?";
                $rs = $db->Execute($sql, array((int)$batch, (int)$offset));
                $table = TABLE_MANUFACTURERS;
                $table_lang = TABLE_MANUFACTURERS_DESCRIPTION;
                $table_seo = TABLE_SEO_URL;
                $seo_type = 'manufacturer';
                $link_name = 'manufacturers_name';
                $master_key = 'manufacturers_id';
                $store_id_field = 'manufacturers_store_id';

                break;
            default:
                throw new Exception("Unknown type $type");
        }
		
		if ($rs->RecordCount() > 0) {
			while(!$rs->EOF) {
				
				$seo->_rebuildSeo($table, $table_lang, $table_seo, $type, $seo_type, $link_name, $master_key, $rs->fields["N"],$store_id_field,$store_id,'');
				$rs->MoveNext();
			}
			$rs->Close();
		}
	}
	
	protected function _getTotalRecordsForType($type) {
		global $db;
		
		switch ($type) {
			case self::URL_TYPE_PRODUCT:
				$rs = $db->Execute("SELECT COUNT(*) AS N FROM " . TABLE_PRODUCTS);
				$count = $rs->fields['N'];
				break;
			case self::URL_TYPE_CATEGORY:
				$rs = $db->Execute("SELECT COUNT(*) AS N FROM " . TABLE_CATEGORIES);
				$count = $rs->fields['N'];
				break;
			case self::URL_TYPE_CONTENT:
				$rs = $db->Execute("SELECT COUNT(*) AS N FROM " . TABLE_CONTENT);
				$count = $rs->fields['N'];
				break;
			case self::URL_TYPE_MANUFACTURER:
				$rs = $db->Execute("SELECT COUNT(*) AS N FROM " . TABLE_MANUFACTURERS);
				$count = $rs->fields['N'];
				break;
			default:
				$count = 0;
				break;
		}
		
		return $count;
	}
	
	public function _displayHTML($next_target, $lower=1, $upper=0,$total=0) {
	
		$process = $lower / $total * 100;
		if ($process>100) $process=100;
	
		$html='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="refresh" content="5; URL='.$next_target.'" />
<title>..Regenerating urls..</title>
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
	
	
	public function _htmlHeader() {
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
	
	public function _htmlFooter() {
		$html ='</body></html>';
		return $html;
	}
	
}