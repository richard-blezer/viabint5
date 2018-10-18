<?php
/*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Enterprise
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright �2007-2008 xt:Commerce GmbH. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~~~~ xt:Commerce VEYTON 4.0 Enterprise IS NOT FREE SOFTWARE ~~~~~~~~~~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: class.export.php 307 2013-02-23 16:28:28Z m.hinsche $
 # @copyright xt:Commerce GmbH, www.xt-commerce.com
 #
 # @author Mario Zanier, xt:Commerce GmbH	mzanier@xt-commerce.com
 #
 # @author Matthias Hinsche					mh@xt-commerce.com
 # @author Matthias Benkwitz				mb@xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce GmbH, Bachweg 1, A-6091 Goetzens (AUSTRIA)
 # office@xt-commerce.com
 #
 #########################################################################
 */


defined('_VALID_CALL') or die('Direct Access is not allowed.');

class export {
	var $data;

	protected $_table = TABLE_FEED;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'feed_id';

	function export($feed_id = '') {
		global $db;
		$feed_id=(int)$feed_id;
		if (!is_int($feed_id)) return false;
		$this->init($feed_id);

	}

	function init($feed_id) {
		global $db;

		
		if ($feed_id!='') {
			$this->feed_id = (int)$feed_id;
			$rs = $db->Execute("SELECT * FROM " . TABLE_FEED . " WHERE feed_id='" . $this->feed_id . "'");
			if ($rs->RecordCount()==1) {
				$this->data = $rs->fields;

				// load shop data
				if ($this->data['feed_store_id']>0) {
					$query = "SELECT * FROM ".TABLE_MANDANT_CONFIG." where shop_id = '" . $this->data['feed_store_id'] . "'";
					$record = $db->Execute($query);
					if ($record->RecordCount()==1) {
						$this->data['MANDANT'] = $record->fields;
					} else {
						return false;
					}
				} else {
					return false;
				}

			} else {
				return false;
			}


		}

	}

	function setAdmin() {
		$this->admin = true;
	}


	function _run() {
		$obj = new stdClass;
		global $db,$xtPlugin,$logHandler,$store_handler;
		global $xtLink;		
		if ($this->url_data['edit_id']) {
			$feed_id = $this->url_data['edit_id'];
			$this->init($feed_id);
			if (!$this->feed_id || !is_array($this->data)) {
				$obj->error = 'No DATA';
				$obj->success = false;
				return $obj;
			}
		}

		$timer = new timer();
		$timer->_start();

		$xtLink->showSessionID(false);

		// get type query

		$selection_query_raw = $this->_getQuery($this->data['feed_type'], $this->data['feed_language_id']);

		// ok first of all, write a file
		$rs = $db->Execute($selection_query_raw);


		$this->Template = new Smarty;

		$this->Template->force_compile = true;

		$this->Template->register_resource("db", array (
		$this,
			"feed_db_source",
			"feed_db_timestamp",
			"feed_db_secure",
			"feed_db_trusted"
			));

			$this->localFile = 'export/' . $this->data['feed_filename'].$this->data['feed_filetype'];
			$this->localFileRoot = _SRV_WEBROOT.$this->localFile;
			$this->file_name = $this->data['feed_filename'].$this->data['feed_filetype'];

			$fp = fopen($this->localFile, "w+");

			// write header
			fputs($fp, $this->replaceDelimiter($this->data['feed_header']) . "\n");
			if ($this->data['feed_print_browser']=='1') echo $this->replaceDelimiter($this->data['feed_header']). '<br />';

			$customers_status = new customers_status($this->data['feed_p_customers_status']);


			if ($this->data['feed_type']=='1') { // products
				$price = new price($customers_status->customers_status_id, $customers_status->customers_status_master,$this->data['feed_p_currency_code']);
				$this->_forceLang = $this->data['feed_language_code'];
				
				// set lang
				if(isset($this->_forceLang) && $this->_forceLang!='Ne') {
					$this->_setLang($this->_forceLang);
				}
				
				// set link
				$xtLink->setLinkURL($this->data['MANDANT']['shop_http']);

				// set shop id
				if ($this->data['feed_store_id']>0) $this->_setStore($this->data['feed_store_id']);

			}

			$export_count = 0;
			$log_data = array();

			// manufacturers

			$manufacturer= new manufacturer();
			$this->man_list = array();
			$_data = $manufacturer->getManufacturerList('admin');
			foreach ($_data as $mdata) {
				$this->man_list[$mdata['manufacturers_id']] =  $mdata['manufacturers_name'];

			}

			while (!$rs->EOF) {

				// get data
				$data = $this->_extractData($rs->fields, $this->data['feed_type']);

				// write body
				$this->Template->assign('data', $data);
				$line =  $this->Template->fetch("db:" . $this->feed_id);

				if ($this->data['feed_print_browser']=='1') echo $line. '<br />';

				fputs($fp, $this->replaceDelimiter($line) . "\n");
				$rs->MoveNext();
				$export_count++;
			}$rs->Close();

			//write footer
			fputs($fp, $this->data['feed_footer']);
			if ($this->data['feed_print_browser']=='1') echo $this->data['feed_footer']. '<br />';
			fclose($fp);

			// ok send by mail ?
			if ($this->data['feed_mail_flag'] == 1) {
				$this->deliverTOmail();
			}
			// FTP Push ?
			if ($this->data['feed_ftp_flag'] == 1) {
				$ftp_timer = new timer();
				$ftp_timer->_start();
				$this->deliverTOftp();
				$ftp_timer->_stop();
				$log_data['runtime_ftp'] = $ftp_timer->timer_total;
			}

			// POST upload ?
			if ($this->data['feed_post_flag'] == 1) {
				$form_timer = new timer();
				$form_timer->_start();
				$this->deliverTOform();
				$form_timer->_stop();
				$log_data['runtime_form'] = $form_timer->timer_total;
			}

			// delete file ?
			if ($this->data['feed_save'] != 1) {
						unlink(_SRV_WEBROOT . 'export/' . $this->data['feed_filename'].$this->data['feed_filetype']);
			} 

			$timer->_stop();

			$log_data['runtime_total'] = $timer->timer_total;
			$log_data['count'] = $export_count;
			$logHandler->_addLog('success','xt_export',$this->data['feed_id'],$log_data);

			$xtLink->showSessionID(true);
			
			$this->_resetStore();
			
			$this->_resetLang();

	}
	

	function _setStore($id) {
		global $store_handler;

		$this->old_store = $store_handler->shop_id;
		$store_handler->shop_id = $id;

	}

	function _resetStore(){
		global $store_handler;

		if (isset($this->old_store)) {
		$store_handler->shop_id = $this->old_store;
		unset($this->old_store);
		}

	}
	
	function _setLang($code) {
		global $language;
		$this->old_session_lang = $_SESSION['language_code'];
		$_SESSION['language_code'] = $code;
		$this->old_lang=$language->code;
		$language->code=$code;
	}

	function _resetLang(){
		global $language;
		
		if (isset($this->old_session_lang)) {
			$_SESSION['language_code'] = $this->old_sesssion_lang;
			unset($this->old_session_lang);
		}
		
		if (isset($this->old_lang)) {
			$language->code = $this->old_lang;
			unset($this->old_lang);
		}
	}

	function _extractData(& $data, $type) {
		global $price,$xtPlugin,$customers_status,$man_list,$xtLink,$db;
		switch ($type) {

			// extract products data
			case 1 :

				$product = new product($data['products_id'],'full',1,$this->_forceLang);
				$data_array = $product->data;

				$data_array['products_id'] = $data['products_id'];
				$data_array['products_description_clean'] = $this->removeHTML($data_array['products_description']);
				$data_array['products_short_description_clean'] = $this->removeHTML($data_array['products_short_description']);


				if ($data_array['products_image']!=''){
					$tmp_img_data = explode(':', $data_array['products_image']);
					$data_array['products_image'] = $tmp_img_data[1];
				}
				
				$data_array['products_image_thumb'] =  $this->data['MANDANT']['shop_http']._SRV_WEB.'media/images/thumb/' . $data_array['products_image'];
				$data_array['products_image_info'] = $this->data['MANDANT']['shop_http']._SRV_WEB.'media/images/info/' . $data_array['products_image'];
				$data_array['products_image_popup'] = $this->data['MANDANT']['shop_http']._SRV_WEB.'media/images/popup/' . $data_array['products_image'];
				$data_array['products_image_org'] = $this->data['MANDANT']['shop_http']._SRV_WEB.'media/images/org/' . $data_array['products_image'];
				$data_array['currency'] = $this->data['feed_p_currency'];
				$data_array['manufacturers_name'] = '';
				if ($data_array['manufacturers_id']>0) {
					$data_array['manufacturers_name']  = $this->man_list[$data_array['manufacturers_id']];
				}
				
				// get category id
				
				$rs=$db->Execute("SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE master_link=1 and products_id='".$data['products_id']."'");
				if ($rs->RecordCount()==1) {
					$data_array['category']=$this->getCategory($rs->fields['categories_id']);
                    $data_array['category_tree']=$this->buildCAT($rs->fields['categories_id']);
                    //  => 
                    $data_array['category_tree'] = substr($data_array['category_tree'],0,-4);  
                     
                    
				}
				

				if ($this->data['feed_p_campaign'] != 0) {
					
					if (_SYSTEM_MOD_REWRITE==true) {
						$campaign = '?refID=' . $this->data['feed_p_campaign'];
					} else {
						$campaign = '&refID=' . $this->data['feed_p_campaign'];
					}
					
					$data_array['products_link'].=$campaign;
				}
				
				($plugin_code = $xtPlugin->PluginCode(__CLASS__.':extractData_products_bottom')) ? eval($plugin_code) : false;
				if(isset($plugin_return_value))
				return $plugin_return_value;
				
				return $data_array;
					

				break;

				// extract order
			case '2' :

				$row_data = array ();
				foreach ($data as $key => $val) {
					$order = new order($data['orders_id'],$data['customers_id']);
				}
				//__debug($order);
                $_order = array();
                $_order['order_customer'] = $order->order_customer;
                $_order['order_data']   = $order->order_data;
                $_order['order_products']   = $order->order_products;
                $_order['order_total_data']  = $order->order_total_data;
                $_order['order_total']  = $order->order_total;
				return $_order;
				break;


		}

	}

	function makePlainString($string) {

		$string = str_replace("<br>", " ", $string);
		$string = str_replace("<br />", " ", $string);
		$string = str_replace(chr(13), " ", $string);
		$string = str_replace(";", ", ", $string);
		$string = str_replace("\"", "'", $string);
		$string = trim($string);
		$string = ereg_replace("[\r\t\n]", "", $string);

		return $string;
	}

	function removeHTML($string) {

		$string = html_entity_decode(strip_tags($string));
		$string = str_replace("<br>", " ", $string);
		$string = str_replace("<br />", " ", $string);
		$string = str_replace(chr(13), " ", $string);
		$string = str_replace(";", ", ", $string);
		$string = str_replace("\"", "'", $string);
		$string = trim($string);
		$string = ereg_replace("[\r\t\n]", "", $string);
		return $string;
	}

	function replaceDelimiter($string) {

		$string = str_replace('[<TAB>]', "\t", $string);
		return $string;

	}

	function _getQuery($type, $lang) {

		switch ($type) {
			case '1' : // products
				$group_check = '';
				$filter = '';
				$perm_where = '';

				// store permissions
				if (_SYSTEM_GROUP_CHECK == 'true' && $this->data['feed_store_id'] > 0) {				
					$perm_where = " left JOIN ".TABLE_PRODUCTS_PERMISSION." shop ON (shop.pid = p.products_id and shop.pgroup = 'shop_".$this->data['feed_store_id']."' )";
					if(_SYSTEM_GROUP_PERMISSIONS=='blacklist'){
						$perm_and = " and shop.permission IS NULL";
					} else {
						$perm_and = " and shop.permission = 1";	
					}
					
						
				}
				
				if (_SYSTEM_GROUP_CHECK == 'true' && $this->data['feed_p_customers_status'] > 0) {				
					$perm_where .= " left JOIN ".TABLE_PRODUCTS_PERMISSION." pgroup ON (pgroup.pid = p.products_id and pgroup.pgroup = 'group_permission_".$this->data['ffeed_p_customers_status']."' )";
					
				if(_SYSTEM_GROUP_PERMISSIONS=='blacklist'){
						$perm_and = " and pgroup.permission IS NULL";
					} else {
						$perm_and = " and pgroup.permission = 1";	
					}
				}

				if ($this->data['feed_p_slave']==0){
					$filter.= " and (p.products_master_model='' or p.products_master_model IS NULL) ";
				}
				
				if ($this->data['feed_manufacturer'] > 0) {
					$filter.= " and p.manufacturers_id='" . $this->data['feed_manufacturer'] . "'";
				}

				$query = "SELECT p.products_id FROM " . TABLE_PRODUCTS . " p ".$perm_where." WHERE p.products_status=1".$perm_and. $filter;
				return $query;
				break;

			case '2' : // orders
				$group_check = '';
				$status_check = '';
				$range_check = '';
				if ($this->data['feed_o_customers_status'] > 0) {
					$group_check = " and customers_status = " . $this->data['feed_o_customers_status'];
				}
				if ($this->data['feed_o_orders_status'] > 0) {
					$status_check = " and orders_status = " . $this->data['feed_o_orders_status'];
				}
				if ($this->data['feed_date_range_orders'] > 0) {
					$calc_date = mktime(0 - $this->data['feed_date_range_orders'], 0, 0, date("m"), date("d"), date("Y"));
					$calc_date = date('Y-m-d H:i:s', $calc_date);
					$range_check = " and date_purchased >= '" . $calc_date . "'";
				}
				$query = "SELECT orders_id, customers_id FROM " . TABLE_ORDERS . " WHERE orders_id>0" . $group_check . $status_check . $range_check;
                return $query;
				break;
		}
	}

	function getBody() {
		return stripslashes($this->data['feed_body']);
	}

	/**
	 * send file as mail attachment
	 *
	 */
	function deliverTOmail() {
		// send by mail
		// send mail to customer

		$body_html = nl2br($this->data['feed_mail_body']);
		$attachment = array();
		$attachment[] = $this->localFileRoot;
		$exportMail = new xtMailer('none');
		$exportMail->_setFrom(_CORE_DEBUG_MAIL_ADDRESS);
		$exportMail->_addReceiver($this->data['feed_mail'],'');
		$exportMail->_setSubject($this->data['feed_mail_header']);
		$exportMail->_setContent($body_html, $this->data['feed_mail_body']);
		$exportMail->_addAttachment($attachment);
		//	$exportMail->_setFrom($this->tplData['email_from'], $this->tplData['email_from_name']);
		//	$exportMail->_addReplyAddress($this->tplData['email_reply'], $this->tplData['email_reply_name']);
		$exportMail->_sendMail();

	}

	/**
	 * upload file to external FTP Server
	 *
	 */
	function deliverTOftp() {
		global $logHandler;

		// user & pass given?
		if ($this->data['feed_ftp_user'] == '' or $this->data['feed_ftp_password'] == '' or $this->data['feed_ftp_server'] == '') {
			$logHandler->_addLog('error','xt_export',$this->data['feed_id'],array('message'=>'ftp login failed'));
			return;
		}
		$connection_id = ftp_connect($this->data['feed_ftp_server']);
		$login_result = @ ftp_login($connection_id, $this->data['feed_ftp_user'], $this->data['feed_ftp_password']);
		if ($this->data['feed_ftp_passiv'] == 1)
		ftp_pasv($connection_id, 1);

		if ((!$connection_id) || (!$login_result)) {
			$logHandler->_addLog('error','xt_export',$this->data['feed_id'],array('message'=>'ftp login failed'));
			return;

		} else {
			// chdir if needed
			if ($this->data['feed_ftp_dir'] != '') {
				$chdir = @ ftp_chdir($connection_id, $this->data['feed_ftp_dir']);
				if ($chdir) {
					// create and upload ftp file
					$upload = @ ftp_put($connection_id, $this->file_name, $this->localFile, FTP_ASCII);

					if (!$upload) {
						$logHandler->_addLog('error','xt_export',$this->data['feed_id'],array('message'=>'ftp upload failed'));
					}
					ftp_close($connection_id);
				} else {
					$logHandler->_addLog('error','xt_export',$this->data['feed_id'],array('message'=>'ftp chdir failed'));
				}
			} else {
				// just upload
				$upload = @ ftp_put($connection_id, $this->file_name, $this->localFile, FTP_ASCII);
				if (!$upload) {
					$logHandler->_addLog('error','xt_export',$this->data['feed_id'],array('message'=>'ftp upload failed'));
				}
				ftp_close($connection_id);
			}
		}
	}

	/**
	 * Upload file to normal formular via POST
	 *
	 */
	function deliverTOform() {
		global $logHandler;

		$file = $this->localFile;

		$ch = curl_init();
		$data = array();
		$data[$this->data['feed_post_field']]="@".$file;
		curl_setopt($ch, CURLOPT_URL, $this->data['feed_post_server']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$postResult = curl_exec($ch);

		if (curl_errno($ch)) {
			$logHandler->_addLog('error','xt_export',$this->data['feed_id'],array('message'=>'curl error'));
			exit ();
		}
		curl_close($ch);


	}


	function getCategory($catID) {
		global $db;
		if (isset($this->_CAT[$catID])) return $this->_CAT[$catID];	
		$rs = $db->Execute("SELECT categories_name FROM ".TABLE_CATEGORIES_DESCRIPTION." WHERE categories_id='".$catID."' and language_code='".$this->_forceLang."'");
		if ($rs->RecordCount()==1) {
			$this->_CAT[$catID]=$rs->fields['categories_name'];
			return $this->_CAT[$catID];
		}		
	}
	
	function getParent($catID) {
		global $db;

		if (isset ($this->PARENT[$catID])) {
			return $this->PARENT[$catID];
		} else {
			$rs = $db->Execute("SELECT parent_id FROM " . TABLE_CATEGORIES . " WHERE categories_id='" . $catID . "'");
			$this->PARENT[$catID] = $rs->fields['parent_id'];
			return $rs->fields['parent_id'];
		}
	}
    
    /**
    * get Category tree
    * 
    * @param mixed $catID
    * @return mixed
    */
    function buildCAT($catID)
    {

        if (isset($this->CAT[$catID]))
        {
         return  $this->CAT[$catID];
        } else {
           $cat=array();
           $tmpID=$catID;

               while ($this->_getParent($catID)!=0 || $catID!=0)
                {
                    $cat[]=$this->getCategory($catID);
                    $catID=$this->_getParent($catID); 
                    

               }
               $catStr='';
               for ($i=count($cat);$i>0;$i--)
               {
                  $catStr.=$cat[$i-1].' => ';
               }
               $this->CAT[$tmpID]=$catStr;
        return $this->CAT[$tmpID];
        }
    }
    
   function _getParent($catID)
    { global $db;
      if (isset($this->PARENT[$catID]))
      {
       return $this->PARENT[$catID];
      } else {
        $rs=$db->Execute("SELECT parent_id FROM ".TABLE_CATEGORIES." WHERE categories_id='".$catID."'");
       $this->PARENT[$catID]=$rs->fields['parent_id'];
       return  $rs->fields['parent_id'];
      }
    }


	// Admin
	function _get($ID = 0) {
		global $db;

		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}
		$ID=(int)$ID;

		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', $this->sql_limit, $this->perm_array);

		if ($this->url_data['get_data']){
			$data = $table_data->getData();


			if (count($data)>0) {
				foreach ($data as $key => $arr) {

					$query = "SELECT * FROM ".TABLE_SYSTEM_LOG." WHERE module='xt_export' and class='success' and identification='".$arr['feed_id']."' ORDER BY created DESC LIMIT 0,1";
					$rs = $db->Execute($query);
					$records['last_runtime'] = ' - ';
					$records['last_run'] = ' - ';
					$records['last_count'] = ' - ';
					if ($rs->RecordCount()==1) {
						$records['last_run'] = $rs->fields['created'];
						$runtime = unserialize($rs->fields['data']);

						$records['last_runtime'] = $runtime['runtime_total'];
						$records['last_count'] = $runtime['count'];
					}

					$data[$key] = array_merge($arr,$records);
				}
			}


		}elseif($ID){
			$data = $table_data->getData($ID);
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

	function _set($data) {
		global $db,$language,$filter;

		$obj = new stdClass;
		$oM = new adminDB_DataSave(TABLE_FEED, $data);
		$obj = $oM->saveDataSet();

		return $obj;
	}

	function _getParams() {
		global $language;

		$params = array();

		$header['feed_id'] = array('type' => 'hidden');

		$header['feed_body'] = array('type' => 'textarea','height'=>'200','width'=>'100%');
		$header['feed_header'] = array('type' => 'textarea','width'=>'100%');
		$header['feed_footer'] = array('type' => 'textarea','width'=>'100%');

		$header['feed_filename'] = array('type' => 'textfield');
		$header['feed_filetype'] = array('type' => 'textfield');

		$header['feed_mail_body'] = array('type' => 'textarea','height'=>'200');

		$header['feed_mail'] = array('width'=>'300');
		$header['feed_ftp_server'] = array('width'=>'300');
		$header['feed_ftp_dir'] = array('width'=>'300');

		$header['feed_date_range'] = array('type' => 'textfield');
		$header['feed_date_range_orders'] = array('type' => 'textfield');

		$header['feed_save'] = array('type' => 'status');
		$header['feed_browser'] = array('type' => 'status');
		$header['feed_print_browser'] = array('type' => 'status');

		$header['feed_manufacturer'] = array(
									'type' => 'dropdown', 								// you can modyfy the auto type
									'url'  => 'DropdownData.php?get=manufacturers','text'=>TEXT_MANUFACTURER_SELECT);

		$header['feed_language_code'] = array(
									'type' => 'dropdown', 								// you can modyfy the auto type
									'url'  => 'DropdownData.php?get=language_codes','text'=>TEXT_LANGUAGE_SELECT);

		$header['feed_o_orders_status'] = array(
									'type' => 'dropdown', 								// you can modyfy the auto type
									'url'  => 'DropdownData.php?systemstatus=order_status','text'=>TEXT_ORDERS_STATUS_SELECT);

		$header['feed_p_customers_status'] = array(
									'type' => 'dropdown', 								// you can modyfy the auto type
									'url'  => 'DropdownData.php?get=customers_status','text'=>TEXT_CUSTOMERS_STATUS_SELECT);


		$header['feed_o_customers_status'] = array(
									'type' => 'dropdown', 								// you can modyfy the auto type
									'url'  => 'DropdownData.php?get=customers_status','text'=>TEXT_CUSTOMERS_STATUS_SELECT);

		$header['feed_p_currency_code'] = array(
									'type' => 'dropdown', 								// you can modyfy the auto type
									'url'  => 'DropdownData.php?get=currencies','text'=>TEXT_CURRENCY_SELECT);


		$header['feed_store_id'] = array(
									'type' => 'dropdown', 								// you can modyfy the auto type
									'url'  => 'DropdownData.php?get=stores');

		$groupingPosition = 'PRODUCTS';
		$grouping['feed_language_code'] = array('position' => $groupingPosition);
		$grouping['feed_p_currency_code'] = array('position' => $groupingPosition);
		$grouping['feed_p_customers_status'] = array('position' => $groupingPosition);
		$grouping['feed_p_campaign'] = array('position' => $groupingPosition);
        $grouping['feed_p_slave'] = array('position' => $groupingPosition); 
        
        $header['feed_p_slave'] = array('type' => 'status');
        
		$header['feed_p_campaign'] = array(
									'type' => 'dropdown', 								// you can modyfy the auto type
									'url'  => 'DropdownData.php?systemstatus=campaign','text'=>TEXT_CAMPAIGN_SELECT);
		$grouping['feed_manufacturer'] = array('position' => $groupingPosition);


		$groupingPosition = 'ORDERS';
		$grouping['feed_o_orders_status'] = array('position' => $groupingPosition);
		$grouping['feed_date_range_orders'] = array('position' => $groupingPosition);
		$grouping['feed_date_from_orders'] = array('position' => $groupingPosition);
		$grouping['feed_date_to_orders'] = array('position' => $groupingPosition);
		$grouping['feed_o_customers_status'] = array('position' => $groupingPosition);

		$groupingPosition = 'FTP';
		$grouping['feed_ftp_flag'] = array('position' => $groupingPosition);
		$grouping['feed_ftp_server'] = array('position' => $groupingPosition);
		$grouping['feed_ftp_user'] = array('position' => $groupingPosition);
		$grouping['feed_ftp_password'] = array('position' => $groupingPosition);
		$header['feed_ftp_password'] = array('type' => 'password');
		$grouping['feed_ftp_server'] = array('position' => $groupingPosition);
		$grouping['feed_ftp_dir'] = array('position' => $groupingPosition);
		$grouping['feed_ftp_passiv'] = array('position' => $groupingPosition);
		$header['feed_ftp_passiv'] = array('type' => 'status');

		$groupingPosition = 'MAIL';
		$grouping['feed_mail'] = array('position' => $groupingPosition);
		$grouping['feed_mail_flag'] = array('position' => $groupingPosition);
		$grouping['feed_mail_header'] = array('position' => $groupingPosition);
		$grouping['feed_mail_attachment'] = array('position' => $groupingPosition);
		$grouping['feed_mail_body'] = array('position' => $groupingPosition);

		$groupingPosition = 'POST';
		$grouping['feed_post_flag'] = array('position' => $groupingPosition);
		$grouping['feed_post_server'] = array('position' => $groupingPosition);
		$grouping['feed_post_field'] = array('position' => $groupingPosition);

		$groupingPosition = 'SECURITY';
		$grouping['feed_pw_flag'] = array('position' => $groupingPosition);
		$grouping['feed_pw_user'] = array('position' => $groupingPosition);
		$grouping['feed_pw_pass'] = array('position' => $groupingPosition);


		$panelSettings[] = array('position' => 'type',     'text' => __define('TEXT_EXPORT_TYPE'),     'groupingPosition' => array('PRODUCTS','ORDERS'));
		$panelSettings[] = array('position' => 'settings', 'text' => __define('TEXT_EXPORT_SETTINGS'), 'groupingPosition' => array('MAIL', 'FTP','POST'));
		$params['panelSettings']  = $panelSettings;

		//$grouping['feed_ftp_server'] = array('position' => $groupingPosition);
		if (!$this->url_data['edit_id'] && $this->url_data['new'] != true) {
			$params['include'] = array ('feed_id','last_runtime','last_run','last_count', 'feed_title', 'feed_type','feed_filename','feed_filetype');
		} else {
			$params['exclude'] = array('last_runtime','last_run','last_count');
		}

		$rowActions[] = array('iconCls' => 'run_export', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_RUN_EXPORT);

        if ($this->url_data['edit_id'])
          $js = "var edit_id = ".$this->url_data['edit_id'].";";
        else 
		  $js = "var edit_id = record.id;";
          
		$js .= "Ext.Msg.show({
   title:'".TEXT_START."',
   msg: '".TEXT_START_ASK."',
   buttons: Ext.Msg.YESNO,
   animEl: 'elId',
   fn: function(btn){runEmport(edit_id,btn);},
   icon: Ext.MessageBox.QUESTION
});";

		$rowActionsFunctions['run_export'] = $js;


		$js = "function runEmport(edit_id,btn){
	  		var edit_id = edit_id;
	  		if (btn == 'yes') {
	  			var conn = new Ext.data.Connection();
                 conn.request({
                 url: '../cronjob.php',
                 method:'GET',
                 params: {'feed_id': edit_id},
                 success: function(responseObject) {
                           Ext.MessageBox.alert('Message', '".TEXT_EXPORT_SUCCESS."');
                          }
                 });
			}

		};";

		$params['rowActionsJavascript'] = $js;


		$params['rowActions']             = $rowActions;
		$params['rowActionsFunctions']    = $rowActionsFunctions;
		$params['header']                 = $header;
		$params['grouping']               = $grouping;
		$params['master_key']             = 'feed_id';
		$params['default_sort']           = 'feed_id';

		return $params;
	}

	function _unset($id = 0) {
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.export.php:_unset_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if(!is_int($id)) return false;

		$db->Execute("DELETE FROM ". TABLE_FEED ." WHERE feed_id = '".$id."'");
		$db->Execute("DELETE FROM ". TABLE_SYSTEM_LOG ." WHERE identification = '".$id."' and module='xt_export'");
		//$query = "SELECT * FROM ".TABLE_SYSTEM_LOG." WHERE module='xt_export' and class='success' and identification='".$record->fields['feed_id']."' ORDER BY created DESC LIMIT 0,1";

		($plugin_code = $xtPlugin->PluginCode('class.export.php:_unset_bottom')) ? eval($plugin_code) : false;

	}


	function setPosition ($position) {
		$this->position = $position;
	}


	function feed_db_source($tpl_name, & $tpl_source, & $smarty) {
		global $db;
		$tpl_query = "SELECT feed_body FROM " . TABLE_FEED . " WHERE feed_id='" . (int) $tpl_name . "'";
		$rs = $db->Execute($tpl_query);
		$tpl_source = $rs->fields['feed_body'];
		return true;

	}

	function feed_db_timestamp($tpl_name, & $tpl_timestamp, & $smarty) {
		$tpl_timestamp = NULL;
		return true;

	}

	function feed_db_secure($tpl_name, & $smarty) {
		// assume all templates are secure
		return true;
	}

	function feed_db_trusted($tpl_name, & $smarty) {
	}

}
?>
