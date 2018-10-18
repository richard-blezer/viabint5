<?php
/*------------------------------------------------------------------------------
	$Id: class.nd_affiliate_inventory.php 67 2011-10-07 16:54:32Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class nd_affiliate_inventory {
	
	public $_table = TABLE_AFFILIATE_INVENTORY;
	public $_master_key = 'inventory_id';
	public $_image_key = 'inventory_image';
	public $_display_key = 'inventory_title';
	protected $_table_lang = null;
	protected $_table_seo = null;
	var $inventoryID = 0;
	var $inventoryData = array();
	var $deliveryRef = 0;
	var $deliveryType = '';
	var $deliveryCode = 'show';
	var $deliveryProduct = 0;
	
	function nd_affiliate_inventory($inventory_id = 0) {
		if($inventory_id > 0) {
			$this->inventoryID = (int)$inventory_id;
			$this->inventoryData = $this->_loadInventory();
		} else {
			return false;
		}
	}
	
	function _loadInventory() {
		global $db;
		
		$record = $db->Execute("SELECT *
								FROM " . $this->_table . "
								WHERE " . $this->_master_key . "='" . $this->inventoryID . "'");
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				$data = $record->fields;
				
				$record->MoveNext();
			}
			return $data;
		}else{
			return false;
		}
	}
	
	function getImpressionsTotal ($affiliate_id) {
		global $db, $store_handler;
		
		$impressions = 0;
		
		if(AFFILIATE_AFFILIATE_GLOBAL == 'false') {
			$shop_clause = " AND shop_id = '" . $store_handler->shop_id . "'";
		}
		
		$affiliate_banner_history = $db->Execute("SELECT sum(inventory_shown) as count 
													FROM " . TABLE_AFFILIATE_INVENTORY_HISTORY .  " 
													WHERE inventory_affiliate_id  = '" .  $affiliate_id . "'
													" . $shop_clause . "");
		
		$impressions = $affiliate_banner_history->fields['count'];
		
		return $impressions;
	}
	
	function drawProductDeeplink ($affiliate_id, $products_id) {
		global $db, $store_handler;
		
		$product = new product($products_id);

		$this->deliveryRef = (int)$affiliate_id;
		
		$link = $this->_buildProductsLink($product);
		
		if($this->deliveryRef > 0 && $products_id > 0) {
			
			$baseUrl = $this->_getBaseUrl();
			
			$html = '<b>' . AFFILIATE_TEXT_DEEPLINK . '</b><br />';
			$html .= '<textarea cols="90" rows="5">';
			if(AFFILIATE_BANNER_JS == 'true') {
				 $html .= "<script type='text/javascript'><!--//<![CDATA[\n";
				 $html .= "document.write (\"<scr\"+\"ipt type='text/javascript' src='" . $baseUrl . "nd_invent.php?ref=" . $this->deliveryRef . "&product=" . $products_id . "&code=js'></scr\"+\"ipt>\");\n";
				 $html .= "//]]>--></script>\n";
				 $html .= "<noscript>" . $link . "<img src='" . $baseUrl . "nd_invent.php?ref=" . $this->deliveryRef . "&product=" . $products_id . "&code=show' border='0' alt='" . $product->data['products_name'] . "'></a></noscript>";
			} else {
				$html .= $link . "<img src='" . $baseUrl . "nd_invent.php?ref=" . $this->deliveryRef . "&product=" . $products_id . "&code=show' border='0' alt='" . $product->data['products_name'] . "'></a>";
			}
			$html .= '</textarea>';
			return $html;
		} else {
			return false;
		}
	}
	
	function drawInventoryCode() {
		global $db, $store_handler, $xtLink;
		
		if($this->deliveryRef > 0 && $this->inventoryID > 0) {
			// get the host
			$baseUrl = $this->_getBaseUrl();
			
			if(AFFILIATE_BANNER_JS == 'true') {
				 $ta = "<script type='text/javascript'><!--//<![CDATA[\n";
				 $ta .= "document.write (\"<scr\"+\"ipt type='text/javascript' src='" . $baseUrl . "nd_invent.php?ref=" . $this->deliveryRef . "&inventory=" . $this->inventoryID . "&type=" . $this->deliveryType . "&code=js'></scr\"+\"ipt>\");\n";
				 $ta .= "//]]>--></script>\n";
				 $ta .= "<noscript>" . $this->_buildLink() . "<img src='" . $baseUrl . "nd_invent.php?ref=" . $this->deliveryRef . "&inventory=" . $this->inventoryID . "&code=show' border='0' alt=''></a></noscript>";
			} else {
				switch($this->deliveryType) {
					case 'i':
						$ta = $this->_buildLink() . "<img src='" . $baseUrl . "nd_invent.php?ref=" . $this->deliveryRef . "&inventory=" . $this->inventoryID . "&code=show' " . $this->_getDimTag('width', 'width=', '"') . " " . $this->_getDimTag('height', 'height=', '"') . "' border='0' alt=''></a>";
						break;
					case 'h':
						$ta = $this->inventoryData['inventory_html'];
						$this->_transferHTMLCode($ta);
						break;
					case 't':
						$ta = $this->_buildLink()  . $record->fields['inventory_text'] . "</a>";
						$this->_clearSonderzeichen($ta);
						break;
				}
			}
			
			return array('code' => $ta, 'id' => $this->inventoryID, 'title' => $this->inventoryData['inventory_title']);
		} else {
			return false;
		}
	}
	
	function countView() {
		global $db, $store_handler;
		
		$today = date('Y-m-d');
		
		if ($ref > 0) {
			$banner = $db->Execute("SELECT inventory_id 
									FROM " . TABLE_AFFILIATE_INVENTORY_HISTORY . "
									WHERE inventory_id = '" . $this->inventoryID  . "'
									AND shop_id = '" . $store_handler->shop_id . "'
									AND inventory_affiliate_id = '" . $this->deliveryRef. "'
									AND inventory_history_date = '" . $today . "'");
			if($banner->RecordCount() > 0) {
				$db->Execute("UPDATE " . TABLE_AFFILIATE_INVENTORY_HISTORY . "
								SET inventory_shown = inventory_shown + 1
								WHERE inventory_id = '" . $this->inventoryID . "'
								AND shop_id = '" . $store_handler->shop_id . "'
								AND inventory_affiliate_id = '" . $this->deliveryRef . "'
								AND inventory_history_date = '" . $today . "'");
			} else {
				$db->Execute("INSERT INTO " . TABLE_AFFILIATE_INVENTORY_HISTORY . " 
								(inventory_id, shop_id, inventory_affiliate_id, inventory_shown, inventory_history_date)
								VALUES ('" . $this->inventoryID . "', '" . $store_handler->shop_id . "', '" . $this->deliveryRef . "', '1', '" . $today . "')");
			}
		}
	}
	
	function deliverInventory() {
		
		if($this->inventoryID > 0) {
			if($this->inventoryData['inventory_status'] == 0) {
				return false;
			}
		
			$function = '_deliverInventory_' . $this->deliveryCode . '_' . $this->deliveryType;
			return $this->$function();
		}
		
		if($this->deliveryProduct > 0) {
			$function = '_deliverProduct_' . $this->deliveryCode;
			return $this->$function();
		}
		
		return false;
	}
	
	function _deliverInventory_js_i() {
		if($this->inventoryData['inventory_image'] != '' && file_exists('media/images/org/' . $this->inventoryData['inventory_image'])) {
			$this->countView();
			return "document.write(\"" . $this->_buildLink() . "<img " . $this->_getDimTag('width', 'width=', '"') . " " . $this->_getDimTag('height', 'height=', '"') . " src='" . $this->_getBaseUrl() . "media/images/org/" . $this->inventoryData['inventory_image'] . "' border='0' alt='' /></a>\");";
		}
		
		return false;
	}
	
	function _deliverInventory_show_i() {
		
		if($this->inventoryData['inventory_image'] != '' && file_exists('media/images/org/' . $this->inventoryData['inventory_image'])) {
			$this->countView();
			$image = 'media/images/org/' . $this->inventoryData['inventory_image'];
			$fp = fopen($image, "rb");
			if (!$fp) return false;
			// Get Image type
			$img_type = substr($image, strrpos($image, ".") + 1);
			// Get Imagename
			$pos = strrpos($image, "/");
			if($pos) {
				$img_name = substr($image, strrpos($image, "/" ) + 1);
			} else {
				$img_name = $image;
			}
			
			header ("Content-type: image/$img_type");
			header ("Content-Disposition: inline; filename=$img_name");
			fpassthru($fp);
			exit();
		} else {
			return '<br />';
		}
		
		return false;
	}
	
	function _deliverInventory_js_h() {
		if($this->inventoryData['inventory_html'] != '') {
			$this->countView();
			$ta = $this->inventoryData['inventory_html'];
			$this->_transferHTMLCode($ta);
			
			$html = '<div id="nd_invent" style="' . $this->_getDimTag('width', 'width: ') . '; ' . $this->_getDimTag('height', 'height: ') . '; ' . ' overflow: auto;">' . $ta . '</div>';
			$html = addcslashes($html, "\0..\37\"\\");
			$html = str_replace('</', '<"+"/', $html);
			return "document.write(\"" . $html . "\");";
		}
		
		return false;
	}
	
	function _deliverInventory_show_h() {
		
		return $this->_deliverInventory_show_i();
	}
	
	function _deliverInventory_js_t() {
		if($this->inventoryData['inventory_text'] != '') {
			$this->countView();
			$text = $this->inventoryData['inventory_text'];
			$this->_clearSonderzeichen($text);
			$text = nl2br($text);
			$text = addcslashes($text, "\0..\37\"\\");
			return "document.write(\"" . $this->_buildLink() . $text . "</a>\");";
		}
		
		return false;
	}
	
	function _deliverInventory_show_t() {
		
		return $this->_deliverInventory_show_i();
	}
	
	function _deliverProduct_js() {
		
		$product = new product($this->deliveryProduct);
		
		if($product->data['products_status'] != 1) {
			return false;
		}
		
		$tmp_img_data = explode(':', $product->data['products_image']);
		$img_name = $tmp_img_data[1];
		
		return "document.write(\"" . $this->_buildProductsLink($product) . "<img src='" . $this->_getBaseUrl() . "media/images/info/" . $img_name . "' border='0' alt='" . $product->data['products_name'] . "' /></a>\");";
		
		return false;
	}
	
	function _deliverProduct_show () {
		
		$product = new product($this->deliveryProduct);
		
		if($product->data['products_status'] != 1) {
			return false;
		}
		
		$tmp_img_data = explode(':', $product->data['products_image']);
		$img_name = $tmp_img_data[1];
		
		if($img_name != '') {
			$image = 'media/images/info/' . $img_name;
			$fp = fopen($image, "rb");
			if (!$fp) return false;
			// Get Image type
			$img_type = substr($image, strrpos($image, ".") + 1);
			// Get Imagename
			$pos = strrpos($image, "/");
			if ($pos) {
				$img_name = substr($image, strrpos($image, "/" ) + 1);
			} else {
				$img_name=$image;
			}
			header ("Content-type: image/$img_type");
			header ("Content-Disposition: inline; filename=$img_name");
			fpassthru($fp);
			exit();
		} else {
			return '<br />';
		}
		
		return false;
	}
	
	function _getDimTag($type, $string = '', $encaps = '') {
		if($inventory->inventoryData['inventory_' . $type] > 0) {
			return $string . $encaps . $inventory->inventoryData['inventory_' . $type] . $encaps;
		} else {
			return false;
		}
	}
	
	function _getBaseUrl() {
		global $db;
		
		return _SYSTEM_BASE_HTTP . _SRV_WEB;
	}
	
	function _buildLink() {
		global $xtLink;
		
		if(strpos($this->inventoryData['inventory_url'], '?')) {
			$sep = '&';
		} else {
			$sep = '?';
		}
		
		if($this->inventoryData['inventory_url'] != '') {
			$url = $this->inventoryData['inventory_url'] . $sep . 'ref=' . $this->deliveryRef . "&inventory=" . $this->inventoryID . "&type=" . $this->deliveryType;
		} else {
			$url = $xtLink->_link(array('page' => 'index', 'params' => 'ref=' . $this->deliveryRef . "&inventory=" . $this->inventoryID . "&type=" . $this->deliveryType));
		}
		
		if($this->inventoryData['inventory_target'] != '') {
			$target = $this->inventoryData['inventory_target'];
		} else {
			$target = '_blank';
		}
		
		return "<a href='" . $url . "' target='" . $target . "'>";
	}
	
	function _buildProductsLink($product) {
		global $xtLink;
		
		if((_SYSTEM_MOD_REWRITE == 'true') && ($product->data['url_text'] != '')) {  // seo_url verwenden 
			$link = _SYSTEM_BASE_HTTP . _SRV_WEB;
			if(_SYSTEM_SEO_FILE_TYPE != '') {
				$link .= $product->data['url_text'] . '.' . _SYSTEM_SEO_FILE_TYPE;
			} else {
				$link .= $product->data['url_text'];
			}
			if(_RMV_SESSION=='false') {
				if (!isset($_COOKIE[session_name()])) $link .= '?' . session_name() . '=' . session_id();
            }
            if(strpos($link, '?')) {
            	$link .= '&';
            } else {
            	$link .= '?';
            }
            $link .= 'ref=' . $this->deliveryRef;
		} else {
			$link_array = array('page' => 'product',
								'type' => 'product',
								'name' => $product->data['products_name'],
								'id' => $product->data['products_id'],
								'params' => 'ref=' . $this->deliveryRef);
			$link = $xtLink->_link($link_array);
		}
		
		return "<a href='" . $link . "' target='_blank'>";
	}
	
	function _transferHTMLCode(&$ta) {
		$ta = str_replace('###LINK###', $this->_buildLink(), $ta);
		$ta = str_replace('###LINKEND###', '</a>', $ta);
		$ta = str_replace('###REFERRER###', 'ref=' . $this->deliveryRef, $ta);
		$ta = str_replace('###AFFILIATE###', $this->deliveryRef, $ta);
		$this->_clearSonderzeichen($ta);
	}
	
	function _clearSonderzeichen(&$string) {
		$string = str_replace(array('ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'), array('&auml;', '&ouml;', '&uuml;', '&Auml;', '&Ouml;', '&Uuml;', '&szlig;'), $string);
	}
	
	function setPosition ($position) {
		$this->position = $position;
	}
	
	function _getParams() {
		global $language;
		
		$header['inventory_id'] = array('type' => 'hidden');
		$header['inventory_text'] = array('type' => 'textarea');
		$header['inventory_html'] = array('type' => 'htmleditor');
		
		$header['affiliate_shop_id'] = array('type' => 'dropdown', 								
											 'url'  => 'DropdownData.php?get=stores');
		
		$groupingPosition = 'inventory_html';
		$grouping['inventory_html'] = array('position' => $groupingPosition);
		
		$groupingPosition = 'inventory_text';
		$grouping['inventory_text'] = array('position' => $groupingPosition);

		$panelSettings[] = array('position' => 'info', 'text' => __define('TEXT_PRODUCT_ADD_INFOS'),
								 'groupingPosition' => array('inventory_html', 'inventory_text'));
		
		$params['header']         = $header;
		$params['grouping']       = $grouping;
		$params['panelSettings']  = $panelSettings;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
		$params['SortField']      = $this->_master_key;
		$params['SortDir']        = "DESC";
		
		$params['display_newBtn'] = true;

		if($this->url_data['edit_id']) {
			$params['exclude'] = array('inventory_expires_impressions',
									   'inventory_expires_date',
									   'inventory_scheduled_date',
									   'inventory_date_added',
									   'inventory_date_last_modified');
		} else {
			$params['exclude'] = array('inventory_expires_impressions',
									   'inventory_expires_date',
									   'inventory_scheduled_date',
									   'inventory_date_added',
									   'inventory_date_last_modified');
		}

		return $params;
	}
	
	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;
		
		$obj = new stdClass;

		if ($ID === 'new') {
               $obj = $this->_set(array(), 'new');
               $ID = $obj->new_id;
               $this->url_data['edit_id'] = $ID;
		}
		
		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', $this->sql_limit);

		if ($this->url_data['get_data']) {
			$data = $table_data->getData();
		} elseif($this->url_data['edit_id']) {
			$data = $table_data->getData($this->url_data['edit_id']);
		} else {
			$data = $table_data->getHeader();
		}
		
		if($table_data->_total_count!=0 || !$table_data->_total_count) {
			$count_data = $table_data->_total_count;
		} else {
			$count_data = count($data);
		}

        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
	}
	
	function _set($data, $set_type = 'edit') {
		global $db,$language,$filter;
		
		unset($data['inventory_image']);
		
		if($set_type == 'edit') {
			$data['inventory_date_last_modified'] = $db->BindTimestamp(time());
		}
		
		if($set_type == 'new') {
			$data['inventory_date_added'] = $db->BindTimestamp(time());
		}

		$obj = new stdClass;
		$oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$obj = $oC->saveDataSet();
		
		return $obj;
	}
	
	function _unset($id = 0) {
		global $db;
		
		if ($id == 0) return false;
		if ($this->position != 'admin') return false;

	    $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ".$id);
	}
}
?>