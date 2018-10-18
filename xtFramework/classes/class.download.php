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



class download {


	var $safe_dir = 'media/files/';
	var $public_dir = 'media/files_public/';
    var $check_time = '15'; // minutes 


	/**
	 * delete all out-of-time symlinks
	 *
	 */
	function _deleteOutOfDateLinks() {
		global $db;
		$sql = "SELECT * FROM ".TABLE_MEDIA_SYMLINK." WHERE symlink_valid<".$db->DBTimeStamp(time());
		$rs = $db->Execute($sql);
		if ($rs->RecordCount()>0) {
			while (!$rs->EOF) {
				$dir = _SRV_WEBROOT.'media/files_public/'.$rs->fields['symlink_dir'];
				if (is_dir($dir)) $this->remove_dir($dir);
				$db->Execute("DELETE FROM ".TABLE_MEDIA_SYMLINK." WHERE symlink_id='".$rs->fields['symlink_id']."'");
				$rs->MoveNext();
			}
		}

	}

	/**
	 * Remove dir with subdirectories and files
	 *
	 * @param string $dir
	 */
	function remove_dir($dir) {
		$handle = opendir($dir);
		while ($file = readdir($handle)) {
			if(!in_array($file, array(".", ".."))) {
				if(!is_dir($dir.$file))
				@unlink($dir.$file);
				else
				remove_dir($dir.$file);
			}
		}
		closedir($handle);
		@rmdir($dir);
	}

	function servePublicFile($media_id) {
		global $db,$xtLink,$info,$current_product_id;

		$media_id = (int)$media_id;

		if (!($rs = $this->checkDownloadPermission($media_id, 'free'))) {
			$this->gotoErrorDownload(array('page'=>'product','info'=>$current_product_id));
		} else {
			if ($this->fileExists($rs->fields['file'],$current_product_id)) {

				$dir = $this->generateRandString_permanent($current_product_id,$rs->fields['file']);
				$this->createPublicDir($dir);
				$this->_addSymlinkLog($dir);

				$this->_createSymlink($rs->fields['file'],$dir);
                $this->addTotalCount($media_id,'free');

				header("Location: " .  _SRV_WEB.$this->public_dir . $dir . "/" . $rs->fields['file']);
			}
		}
	}

	function serveFile($order_id,$media_id, $orders_products_id) {
		global $db,$xtLink,$info;

		$order_id = (int)$order_id;
		$media_id = (int)$media_id;
		$orders_products_id = (int)$orders_products_id;

		if (!is_int($order_id)) return false;
		if (!is_int($media_id)) return false;
		if (!is_int($orders_products_id)) return false;
		// check if allowed for download
		$query = "SELECT m.*,o.*,opm.* FROM ".TABLE_ORDERS_PRODUCTS_MEDIA." opm INNER JOIN ".TABLE_ORDERS." o ON opm.orders_id=o.orders_id INNER JOIN ".TABLE_MEDIA." m ON m.id=opm.media_id WHERE opm.orders_id=? and opm.media_id=? and opm.orders_products_id=? and o.customers_id=?";
		$rs = $db->Execute($query, array($order_id, $media_id, $orders_products_id, (int)$_SESSION['registered_customer']));

		if ($rs->RecordCount()!='1') {
			$this->_gotoErrorDownload(array('page'=>'customer','paction'=>'download_overview'));
		} else {
			if (!$this->checkDownloadPermission($media_id, 'order')) {
				$this->gotoErrorDownload(array('page'=>'customer','paction'=>'download_overview'));
			}
			// check if allowed ?
			if (!$this->_checkDowloadAllowed($rs->fields['date_purchased'],$rs->fields['max_dl_days'],$rs->fields['max_dl_count'],$rs->fields['download_count']))
				return false;

			if ($this->fileExists($rs->fields['file'],$rs->fields['orders_id'])) {

				$dir = $this->generateRandString_permanent($rs->fields['orders_id'],$rs->fields['file']);
				$this->createPublicDir($dir);
				$this->_addSymlinkLog($dir);

				$this->_createSymlink($rs->fields['file'],$dir);

				$this->addCount($rs->fields['orders_id'],$rs->fields['media_id'], $rs->fields['orders_products_id']);
                $this->addTotalCount($rs->fields['media_id']);
                $this->logDownload($rs->fields['media_id']);
					
				header("Location: " .  _SRV_WEB.$this->public_dir . $dir . "/" . $rs->fields['file']);
			}
		}
	}
	
	/**
	 * Logs download action to database, only for order items
	 * @param int $mediaId
	 */
	protected function logDownload($mediaId) {
		global $db,$xtPlugin,$filter;
		
		// Log only order downloads
		if (!isset($_GET['order']))
			return;
		// Get order Id from request
		$orderId = $filter->_int($_GET['order']);
		($plugin_code = $xtPlugin->PluginCode('class.download.php:logDownload:top')) ? eval($plugin_code) : false;
		$query = sprintf("SELECT * FROM %s WHERE id=%s", TABLE_MEDIA, (int)$mediaId);
		$rs = $db->Execute($query);
		
		if ($rs->RecordCount()>0) {
			$downloadCount = 0;
			
			// Check if there is any previous log about this file, to get actual download count because real one can be reseted
			$query = sprintf("SELECT * FROM %s WHERE orders_id=%d AND media_id=%d AND download_action=1 ORDER BY download_log_id DESC LIMIT 1", TABLE_DOWNLOAD_LOG, (int)$orderId, (int)$mediaId);
			$log = $db->Execute($query);
			
			if ($log->RecordCount()>0) {
				$downloadCount = $log->fields['download_count'];
			}
			$downloadCount++;
			// Check current order downloads
			$totalDownloads = 0;
			$query = sprintf("SELECT * FROM %s WHERE orders_id=%d AND media_id=%d", TABLE_ORDERS_PRODUCTS_MEDIA, (int)$orderId, (int)$mediaId);
			$order_media = $db->Execute($query);
			
			if ($order_media->RecordCount() > 0) {
				$totalDownloads = $order_media->fields['download_count'];
			}
			
			$insert_array = array(
				'download_action' => 1, // 1 - Client download
				'download_count' => $downloadCount,
				'orders_id' => $orderId,
				'media_id' => $mediaId,
				'attempts_left' => (int)$rs->fields['max_dl_count'] - (int)$totalDownloads,
				'file' => $rs->fields['file'],
			);
			($plugin_code = $xtPlugin->PluginCode('class.download.php:logDownload:bottom')) ? eval($plugin_code) : false;
			$db->AutoExecute(TABLE_DOWNLOAD_LOG,$insert_array);
		}
	}
	
	/**
	 * create symlink using WEBROOT or DOCUMENT_ROOT dir
	 * @param $file
	 * @param $tmp_dir
	 * @return unknown_type
	 */
	function _createSymlink($file,$tmp_dir) {
		global $logHandler;
		
		//option for profilhost
		if (defined('_SYSTEM_SRV_WEBROOT_PREFIX') && _SYSTEM_SRV_WEBROOT_PREFIX != '') {
			$dir = _SYSTEM_SRV_WEBROOT_PREFIX. _SRV_WEBROOT;
		}
		else{
			$dir = _SRV_WEBROOT;
		}
		
        $dir_2 = $_SERVER['DOCUMENT_ROOT'];

        if (file_exists($dir.$this->safe_dir . $file)) {
          $dir = $dir; 

        } elseif($dir_2.$this->safe_dir . $file){
          $dir = $dir_2;  

        } else {

           $log_data = array();
           $log_data['error'] = 'no file found';
           $logHandler->_addLog('error','download','0',$log_data); 
        }

		if ( PHP_OS == "WINNT" ) {
		 	link($dir.$this->safe_dir . $file, $dir.$this->public_dir . $tmp_dir . "/" . $file);
		 } 
		 else {
		 	symlink($dir.$this->safe_dir . $file, $dir.$this->public_dir . $tmp_dir . "/" . $file);
		}
		
	}

	/**
	 * add log entry to media_symlink table
	 *
	 * @param string $dir
	 */
	function _addSymlinkLog($dir) {
		global $db;

		$path_array = array();
		$path_array['symlink_dir'] = $dir;
		$path_array['symlink_valid'] = date_add_hours(time(),2);
		$db->AutoExecute(TABLE_MEDIA_SYMLINK,$path_array);
	}

    /**
    * update total download counter
    * 
    * @param mixed $media_id
    */
    function addTotalCount($media_id,$type='free') {
        global $db,$current_product_id,$xtPlugin;
        
        $media_id = (int)$media_id;
        if (!is_int($media_id)) return;
        
        // check 24 hours IP lookup Table
        
        $rs = "SELECT * FROM ".TABLE_MEDIA_DOWNLOAD_IP." WHERE media_id=? and user_ip=? and download_time >= NOW()-".$this->check_time*60;
        $rs = $db->Execute($rs, array($media_id, md5($_SERVER['REMOTE_ADDR'])));
        if ($rs->RecordCount()==0) {
			$insert_array = array('user_ip'=>md5($_SERVER['REMOTE_ADDR']),'media_id'=>$media_id);
			$db->AutoExecute(TABLE_MEDIA_DOWNLOAD_IP,$insert_array);
			$db->Execute("UPDATE ".TABLE_MEDIA." SET total_downloads=total_downloads+1 WHERE id=?", array($media_id));
			if ($type=='free') $db->Execute("UPDATE ".TABLE_PRODUCTS." SET total_downloads=total_downloads+1 WHERE products_id=?", array($current_product_id));
			($plugin_code = $xtPlugin->PluginCode('class.download.php:addTotalCount')) ? eval($plugin_code) : false;
        }

        $db->Execute("DELETE FROM ".TABLE_MEDIA_DOWNLOAD_IP." WHERE download_time < NOW()-".$this->check_time*60);
    }
    
	/**
	 * set download counter +1
	 *
	 * @param int $order_id
	 * @param int $media_id
	 */
	function addCount($order_id,$media_id, $orders_products_id) {
		global $db;

		$order_id = (int)$order_id;
		$media_id = (int)$media_id;
		$orders_products_id = (int)$orders_products_id;

		$db->Execute(
			"UPDATE ".TABLE_ORDERS_PRODUCTS_MEDIA." SET download_count=download_count+1 WHERE orders_id=? and media_id=? and orders_products_id=?",
			array($order_id, $media_id, $orders_products_id)
		);
	}
	
	/*Generates permanent directory for downloading file 
	 * based on produst_id and file name*/
	function generateRandString_permanent($product_id,$file) {
		$st =  $product_id.'_'.$file;
		$string = md5($st);
		return $string;
	}
	
	function generateRandString() {
		$allowedChars = 'abcdefghijklmnopqrstuvwxyz';
		srand((double) microtime() * 1000000);
		$string = '';
		for ($i = 1; $i <= rand(8,12); $i++) {
			$q = rand(1,24);
			$string = $string . $allowedChars[$q];
		}

		return $string;
	}

	/**
	 * check if download file exists (original), add log entry if failed
	 *
	 * @param string $file
	 * @param int $orders_id
	 * @return boolean
	 */
	function fileExists($file,$orders_id) {
		global $logHandler;
		if (!file_exists(_SRV_WEBROOT.$this->safe_dir.$file)) {
			$log_data = array();
			$log_data['file_not_exists'] = $file;
			$logHandler->_addLog('error','download_system',$orders_id,$log_data);
			return false;
		}
		return true;
	}

	/**
	 * create dir in files_public folder
	 *
	 * @param unknown_type $dir
	 */
	function createPublicDir($dir) {
		umask(0000);
        mkdir(_SRV_WEBROOT.$this->public_dir.$dir,0777);
	}


	/**
	 * check if download is allowed (date range, count)
	 *
	 * @param datetime $date
	 * @param int $days
	 * @param int $count_max
	 * @param int $count_loaded
	 * @return boolean
	 */
	function _checkDowloadAllowed($date,$days=0,$count_max=0,$count_loaded=0) {

		$count_left = $count_max-$count_loaded;
		$date_until = vtn_date_add(datetime_to_timestamp($date),$days);
		$diff = time()-$date_until;

		// count
		if ($count_left<=0 && $count_max>0) return false;

		// date
		if ($days>0) {
			if ($diff>0) return false;
		}

		return true;

	}
        
        //check if customer has permission to view file
        public function checkDownloadPermission($mediaId, $downloadStatus)
        {
            global $db;
            $mediaId = intval($mediaId);
            $permList = array(
                'group_perm' => array(
                    'type'          => 'group_permission',
                    'key'           => 'id',
                    'value_type'    => 'media_file','pref'=>'m',
            ));
            $permission = new item_permission($permList);
            $query = sprintf("SELECT distinct m.* FROM %s m %s where m.id='%d'%s and m.download_status='%s' and m.status='true'", 
                    TABLE_MEDIA, $permission->_table, (int)$mediaId, $permission->_where, $downloadStatus);
            $record = $db->Execute($query);
            $lang_permission = $this->checkDownloadLanguagePermission($mediaId, $downloadStatus);
			
            if (($record->RecordCount() == 1) && $lang_permission=='true') {
                return $record;
            }
            return false;
        }
        
		public function checkDownloadLanguagePermission($mediaId, $downloadStatus)
		{
			global $db,$language;
		
			$record = $db->Execute(
				"SELECT ml_id FROM " . TABLE_MEDIA_LANGUAGES . " WHERE m_id=? and language_code = ? ",
				array($mediaId, $language->code)
			);
			
			if ($record->RecordCount() == 1) {
                if(_SYSTEM_GROUP_PERMISSIONS=='blacklist') return false;
				elseif(_SYSTEM_GROUP_PERMISSIONS=='whitelist') return true;
            }
			else {
				if(_SYSTEM_GROUP_PERMISSIONS=='blacklist') return true;
				elseif(_SYSTEM_GROUP_PERMISSIONS=='whitelist') return false;
			}
            
		}
		
        //go to error download page
        public function gotoErrorDownload($linkList)
        {
            global $xtLink, $info;            
            $linkList = is_array($linkList) ? $linkList : array();
            $link  = $xtLink->_link($linkList);
            $info->_addInfoSession(ERROR_DOWNLOAD,'error');
            $xtLink->_redirect($link);
        }
}