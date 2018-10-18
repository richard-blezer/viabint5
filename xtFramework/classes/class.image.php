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

require_once (_SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.MediaFileTypes.php');

class image extends MediaFileTypes{

	var $resource;
	var $target_dir;
	var $extension;
	var $target_name;

	var $max_height;
	var $max_width;
    
    var $compression = 75;

	var $watermark;
	var $watermark_image;

	var $error;

	function __construct() {
		$this->type = 'images';
        $types = $this->getFileExt($this->type);
		$this->FileTypes 	= $types['FileTypes'];
		$this->UploadExt    = $types['UploadTypesArray'];
        
                
        if(constant('_SYSTEM_IMG_QUALITY')) {
            $this->compression = (int)_SYSTEM_IMG_QUALITY;
        } 
	    
	    $watermark_image = _SRV_WEBROOT._SRV_WEB_IMAGES.'watermark.png';
	    $this->watermark = false;
	    if (is_file($watermark_image)) {
	    	$this->watermark_image = $watermark_image;
	    	$this->watermark = true;
	    }
	    
		$watermark_image = _SRV_WEBROOT._SRV_WEB_IMAGES.'watermark.gif';
    	$this->watermark = false;
	    if (is_file($watermark_image)) {
    	    $this->watermark_image = $watermark_image;
    	    $this->watermark = true;
	    }
	}

    /**
    * resize image to given size
    * 
    */
	function createThumbnail() {

		if (!file_exists($this->resource)) {
			$this->error = ERROR_IMAGE_NOT_EXISTS;
			return false;
		}

		$this->extension = $this->_getExtension($this->resource);
		$this->filename = basename($this->resource);
		$this->image_name = str_replace($this->extension,'',$this->filename);

		// right extension ?
		$allowed = $this->UploadExt;
		if (!in_array($this->extension,$allowed)) {
			$this->error = '>'.$this->filename.'-'.$this->extension.'<'.ERROR_IMAGE_EXTENSION_NOT_SUPPORTED;
			return false;
		}
		if (!function_exists('imagecreatefromgif') && $this->extension=='.gif') {
			$this->error = ERROR_IMAGE_GIF_NOT_SUPPORTED;
			return false;
		}

		if ($this->extension=='.jpg' || $this->extension=='.jpeg')
		$image_source = imagecreatefromjpeg($this->resource);

		if ($this->extension=='.png')
		$image_source = imagecreatefrompng($this->resource);

		if ($this->extension=='.gif')
		$image_source = imagecreatefromgif($this->resource);


		// get height/width
		$old_width =imagesx($image_source);
		$old_height = imagesy($image_source);


		$ratio_width=$old_width/$this->max_width;
		$ratio_height=$old_height/$this->max_height;


		if($ratio_width>$ratio_height)	{
			$thumbnail_width=$this->max_width;
			$thumbnail_height=$old_height/$ratio_width;
		}
		else	{
			$thumbnail_height=$this->max_height;
			$thumbnail_width=$old_width/$ratio_height;
		}

        // check if we should create bigger thumb than resource
        if (_SYSTEM_IMG_SHRINK_ONLY=='true') {
            if ($thumbnail_width>$old_width or $thumbnail_height>$old_height) {
                 $thumbnail_width = $old_width;
                 $thumbnail_height = $old_height;
            }    
        }

		// create new image
		$image_thumbnail=imagecreatetruecolor($thumbnail_width,$thumbnail_height);
        
        if ($this->extension=='.png') imagealphablending($image_thumbnail, false);
        
		// resize image
		imagecopyresampled($image_thumbnail,$image_source,0,0,0,0,$thumbnail_width,$thumbnail_height,$old_width,$old_height);

		// create image in destinaion dir
		if ($this->target_name!='') {
			$this->image_name = $this->target_name;
		}
//		$this->image_name=$this->image_name.$this->extension;

		if ($this->watermark) $image_thumbnail = $this->addWatermark($image_thumbnail);

        if ($this->extension=='.png') {
            imagesavealpha($image_thumbnail, true);
            imagepng($image_thumbnail,$this->target_dir.$this->image_name,9);  
        }   else {
            imagejpeg($image_thumbnail,$this->target_dir.$this->image_name,$this->compression);    
        }

		imagedestroy($image_thumbnail);
		imagedestroy($image_source);

		return true;
	}

    function addWatermark($image_thumbnail) {
        global $xtPlugin;
		if($this->watermark_image){
			$watermark_image_ext = $this->_getExtension($this->watermark_image);
			
			if ($watermark_image_ext=='.gif')
			{
				$watermark_source = imagecreatefromgif($this->watermark_image);
			
				$watermark_width = imagesx($watermark_source);
		   		$watermark_height = imagesy($watermark_source);
	
		   		$thumbnail_height = imagesy($image_thumbnail);
		   		$thumbnail_width = imagesx($image_thumbnail);
	
		   		$watermark_position_height = $thumbnail_height-5-$watermark_height;
		   		$watermark_position_width = 5;
		   		
				($plugin_code = $xtPlugin->PluginCode(__CLASS__.':addWatermark_gif')) ? eval($plugin_code) : false;
                
		   		imagecopymerge($image_thumbnail,$watermark_source,$watermark_position_width,$watermark_position_height,0,0,$watermark_width,$watermark_height,100);
			}
			
			if ($watermark_image_ext=='.png'){
				
				$watermark_source = imagecreatefrompng($this->watermark_image);
				$watermark_width = imagesx($watermark_source);
				$watermark_height = imagesy($watermark_source);
				
				$thumbnail_height = imagesy($image_thumbnail);
				$thumbnail_width = imagesx($image_thumbnail);
				
				$image=imagecreatetruecolor($watermark_width, $watermark_height);
				imagealphablending($image, false);
				
                // place the watermark
                $dest_x=$thumbnail_width-$watermark_width-5;
                $dest_y=$thumbnail_height-$watermark_height-5;

                ($plugin_code = $xtPlugin->PluginCode(__CLASS__.':addWatermark_png')) ? eval($plugin_code) : false;

                imagecopy($image_thumbnail, $watermark_source, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height);
                imagesavealpha($image_thumbnail, true);
				
			}
				
		}

		return $image_thumbnail;
	}

	/**
	 * Extract extension from filename
	 *
	 * @param string $filename
	 * @return string extension
	 */
	function _getExtension($filename) {
		$extension = strtolower(strrchr($filename,"."));
		return $extension;
	}
}