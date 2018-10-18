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

class FileHandler {
	
	protected $_masterDir;
	protected $_parentDir;
	protected $_masterExt;

    function __construct() {
    	$this->setMasterDir(_SRV_WEBROOT);
    }

	/*---------------------------------------------------------------------------------------------*/
	// SETTINGS	START
	/*---------------------------------------------------------------------------------------------*/
    
    public function setMasterDir ($value){
    	$this->_masterDir = $value;
    }
    
    public function setParentDir ($value){
    	$this->_parentDir = $value;
    }    
    
    public function setMasterExt ($value){
    	$this->_masterExt = $value;
    }    

	/*---------------------------------------------------------------------------------------------*/
	// SETTINGS	END
	/*---------------------------------------------------------------------------------------------*/
    
    
	/*---------------------------------------------------------------------------------------------*/
	// HELPER START
	/*---------------------------------------------------------------------------------------------*/

    public function getMasterDir(){
    	return $this->_masterDir;
    }
    
    public function getParentDir(){
    	return $this->_parentDir;
    }    
    
    public function getMasterExt(){
    	return $this->_masterExt;
    }    
    
    public function cleanFileName($file){
    	
		$search = array();
		$replace = array();
	//	$this->getRegExps($search, $replace);
		
		$file_array = explode('.', $file);
		$file_name = $this->getFileNameNoExtension($file);//$file_array[0]; // silvia 07.10
		$file_ext = $this->getFileExt($file);//$file_array[1]; 				// silvia 07.10
		
		$validFileName  = preg_replace($search,$replace,$file_name);
		$validFileName  = preg_replace("/(-){2,}/","-",$validFileName);		
		$validFileName 	= preg_replace("/[^a-z0-9A-Z-\/-_]/i", "", $validFileName);		
		$validFileName 	= $this->sanitize($validFileName,false); // silvia 07.10
		$validFile = $validFileName.'.'.$file_ext;

		return($validFile);    	
       	
    }
	
	function getFileExt($f_name)
	{
		$file_name = explode(".",$f_name); 
		return $file_name[sizeof($file_name)-1];
	}
	
	function getFileNameNoExtension($f_name)
	{
		
		$file_name = explode(".",$f_name); 
		$result='';
		for ($t=0;$t<(sizeof($file_name)-1);$t++)
		{
			$result.= $file_name[$t];
		}
		return $result;
	}
	
	function sanitize($string, $force_lowercase = true, $strange = true) {
		$strip = array(
			"~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]", "§",
			"}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
			"â€”", "â€“", ",", "<", ".", ">", "/", "?"
		);
		$clean = trim(str_replace($strip, "", strip_tags($string)));
		$clean = preg_replace('/\s+/', "-", $clean);
		$clean = ($strange) ? preg_replace("/[^a-zA-Z0-9-_]/", "", $clean) : $clean ;
		$res= ($force_lowercase) ?
			(function_exists('mb_strtolower')) ?
				mb_strtolower($clean, 'UTF-8') :
				strtolower($clean) :
			$clean;
		if ($res=='') $res = 'noName'.date("YmdHms");
		
		return $res;
	}
    
    public function cleanDirName($dir){
    	
		$search = array();
		$replace = array();
		$this->getRegExps($search, $replace);
		
		$validName  = preg_replace($search,$replace,$dir);
		$validName  = preg_replace("/(-){2,}/","-",$validName);		
		$validName 	= preg_replace("/[^a-z0-9A-Z-\/-_]/i", "", $validName);		
		
		return($validName);
    }    
    
	protected function getRegExps(&$search, &$replace) {
		$search = array("/ä/","/ö/","/ü/","/Ä/","/Ö/","/Ü/","/ß/");
		$replace = array("ae","oe","ue","Ae","Oe","Ue","ss");
	}
      
	public function _checkDir($dir=''){

		if(!$dir)
		$dir = $this->_masterDir.$this->getParentDir();

		if(is_dir($dir))
		return true;
		else
		return false;
	}    
    
	public function _checkFile($file, $dir=''){
		
		if(!$dir)
		$dir = $this->_masterDir.$this->getParentDir();		
		
		if(is_file($dir.'/'.$file))
		return true;
		else
		return false;
	}	
	
	public function _createDir($dir=''){

		if(!$dir)
		$dir = $this->_masterDir.$this->getParentDir();		
		
		if(!$this->_checkDir($dir)){
			mkdir($dir);
			chmod($dir, 0777);
		}
	}	
	
	public function _createFile($data, $file, $dir='', $perm=0777){

		if(!$dir)
		$dir = $this->_masterDir.$this->getParentDir();		
		
		$file = $file.$this->getMasterExt();

	    if(!$this->_checkDir($dir)){
			$this->_createDir($dir);
		}

		$fp = fopen($dir.'/'.$file, "w+");
	    fputs($fp, $data);
	    fclose($fp);
		chmod($dir.'/'.$file, $perm);
	}	
	
	public function _renameDir($new_dir, $dir=''){
		
		if(!$dir)
		$dir = $this->_masterDir.$this->getParentDir();		
		
		if($this->_checkDir($dir)){
			rename($dir, $new_dir);
		}
	}
	
	public function _renameFile($new_file, $file, $dir=''){
		
		if(!$dir)
		$dir = $this->_masterDir.$this->getParentDir();	

		$file = $file.$this->getMasterExt();
		$new_file = $new_file.$this->getMasterExt();
		
		if($this->_checkFile($file, $dir)){
			rename($dir.$file, $dir.$new_file);
		}
	}	
		
	public function _deleteDir($dir=''){

		if(!$dir)
		$dir = $this->_masterDir.$this->getParentDir();		
		
		if($this->_checkDir($dir)){
			rmdir($dir);
		}
	}	
	
	public function _deleteFile($file, $dir=''){

		if(!$dir)
		$dir = $this->_masterDir.$this->getParentDir();		
		
		$file = $file.$this->getMasterExt();
		
		if (file_exists($dir.$file)) {
			unlink($dir.'/'.$file);
		}
	}
}