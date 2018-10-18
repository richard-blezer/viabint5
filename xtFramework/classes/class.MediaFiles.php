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

class MediaFiles extends MediaData {

    function __construct() {
		$this->type = 'files';
		
        $types = $this->getFileExt($this->type);       	
    	
		$this->path 		= _SRV_WEB_MEDIA_FILES.'';
        $this->urlPath      = _SYSTEM_BASE_HTTP._SRV_WEB_UPLOAD;
		$this->FileTypes 	= $types['FileTypes'];
		$this->UploadExt    = $types['UploadTypesArray'];
    }

	function Upload ($filename) {
        $upload_good = false;
        $filename = $this->setExtensiontolower($filename);
        if(!$this->class)
        $this->setClass($this->url_data['currentType']);        
        
        $obj = new stdClass;
        
    	$upload = new uploader();
     	$upload->file='Filedata';
     	$upload->upload_dir = _SRV_WEBROOT._SRV_WEB_MEDIA_FILES;
     	$upload->new_name = $filename;
     	$upload->extensions = $this->UploadExt;
     	$response = $upload->uploadFile();
     	
	    if (!$response) {
	    	
    	    $obj->error = true;
    		$obj->waitMsg = 'Error:'.$upload->error;
    	} else {

    		if($this->url_data['currentType']=='files_free'){
    			$download_status = 'free';
    		}else{
    			$download_status = 'order';
    		}
		
    		$this->setMediaData(array(
				'file' => $filename,
				'type' => $this->type,
				'class' => $this->url_data['currentType'],
				'download_status'=>$download_status,
				'mgID'=>$this->url_data['mgID']
			));
    		$obj->success = true;
    	}
    	return $obj;
	}

	function readDir() {

		$path = $this->getPath();
		$dir = _SRV_WEBROOT.$path;
		$files = array();
		$d = dir($dir);

		while($name = $d->read()){
		    if(!preg_match('/\.('.$this->getFileTypes().')$/', $name)) continue;
		    $size = filesize($dir.$name);
		    $lastmod = filemtime($dir.$name);
		    
		    $img = 'icon_'.$this->_getExtension($name).'.gif';
		    		    
		    $files[] = array(
				'name'=>$name,
				'size'=>$size,
				'image'=>$img,
				'lastmod'=>$lastmod,
				'url'=> $this->urlPath._SRV_WEB_MEDIA_FILE_TYPES.$img,
				'url_full'=> $this->urlPath.$path.$name
			);
		}
		$d->close();
		return array('files'=>$files);
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;
		if ($this->position != 'admin') return false;

		$tmp_data = $this->readDir();
		$data = $tmp_data['files'];

		if(count($data) > 0){
			$obj = new stdClass;
			$obj->data = $data;
			$obj->totalCount = count($obj->data);

			return $obj;
		}else{
			return false;
		}
	}
	
	function getMediaFiles($id, $class) {
		global $db, $xtPlugin,$language,$xtLink;
		
		$id=(int)$id;
		if(!is_int($id) || !is_data($class)) return false;
				
		$med_perm_array = array(
			'group_perm' => array(
				'type'=>'group_permission',
				'key'=>'id',
				'value_type'=>'media_file',
				'pref'=>'m'
			)
		);
						 		   				
		$med_permission = new item_permission($med_perm_array);

		$qry = "SELECT 
					distinct m.*,md.* 
				FROM 
					".TABLE_MEDIA." m 
				left join 
					".TABLE_MEDIA_DESCRIPTION." md 
				on m.id=md.id 
				left join ".TABLE_MEDIA_LINK." ml 
				on m.id = ml.m_id ".$med_permission->_table." 
				where 
					link_id = ? ".$med_permission->_where."
				and md.language_code=?
				and m.download_status = 'free' 
				and m.status = 'true' 
				and ml.class=?";
		
		$record = $db->Execute($qry, array($id, $language->code, $class));
		if ($record->RecordCount() > 0) {
			while(!$record->EOF){

				if($record->fields['type']=='files'){
					$file = _SRV_WEBROOT.'media/files/'.$record->fields['file'];
					if (file_exists($file)) {
						$record->fields['icon'] = $this->_getIcon($record->fields['file']);
						$record->fields['media_size'] = filesize($file);
						
						/* quick & dirty */
						if ($class == 'product') {
							$link = $xtLink->_link(array('page'=>$class,'params'=>'info='.$id.'&dl_media='.$record->fields['id']));
						}
						
						$record->fields['download_url'] =$link;
						$data[] = $record->fields;
					}
				}

				$record->MoveNext();
			} $record->Close();

			return $data;
		}else{
			return false;
		}
	}
	
	function remove($data){
		unlink(_SRV_WEBROOT._SRV_WEB_MEDIA_DOWNLOADS.$data['file']);	
		$this->unsetMediaData($data['id']);
	}	
	
	/*
	media files
	*/
	function get_media_data($id, $class, $page, $params){
		global $mediaFiles, $xtLink;

		if ($data['tmp_files'] = $this->_getMediaFiles($id, $class, 'media', 'free')) { 
			
			foreach ($data['tmp_files'] as $key => $val){
				
				$file = _SRV_WEBROOT.'media/files/'.$val['file'];
				if (file_exists($file)) {
					$val['icon'] = $mediaFiles->getIcon($val['file']);
					$val['media_size'] = filesize($file);

					$link = $xtLink->_link(array('page'=>$page,'params'=>$params.'&dl_media='.$val['id']));
					$val['download_url'] =$link;
				}
   
				$data['files'][$key] = $val;
			}			
			
			return $data;
			
		} else {
			return false;
		}		
	}	
    function setUrlData($data=''){
    	if(is_array($data))
    	$this->url_data = $data;
    }
    	
	function setAutoReadFolderData($mgID='') {
		global $db;

	    $data = $this->readDir();
	    if (is_array($data['files'])) {
	        foreach ($data['files'] as $key => $imageData) {
	         $record = $db->Execute(
				 "SELECT id FROM ".$this->_table_media." where file=?",
				 array($imageData['name'])
			 );
    			if ($record->RecordCount() == 0) {
                	$this->setMediaData(array(
						'file' => $imageData['name'],
						'type' => $this->type,
						'class' => $this->url_data['currentType'],
						'mgID'=>$this->url_data['mgID'],
						'download_status'=>$this->url_data['download_status']
					));
    			}	        	
	        }
	        return true;
	    }
	    return false;
	}

    function setExtensiontolower($filename) {
        $extension = strtolower(strrchr($filename,"."));
        $new_name = substr($filename,0,strlen($filename)-strlen($extension)).$extension;
        return $new_name;
    }
}