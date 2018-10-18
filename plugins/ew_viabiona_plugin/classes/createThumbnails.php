<?php

namespace ew_viabiona;

use ew_viabiona\plugin as ew_viabiona_plugin;

/**
 * Auto create thumbnails with image processing method
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */
class createThumbnails extends \MediaImages
{
    /**
     * parent::__construct
     * Overwritten for setting image type
     *
     * @param string $folder the requested folder like thumb, info, popup, ...
     * @param string $class  default, product, manufacturer, category, content
     */
    function __construct($folder = null, $class = 'default')
    {
        parent::__construct();

        $this->imageTypes = $this->getImageTypeByFolder($folder);
        $this->setClass(isset($this->imageTypes['class']) ? $this->imageTypes['class'] : $class);
    }

    /**
     * Get image type by its folder name
     *
     * @param string $folder
     * @return array
     */
    public static function getImageTypeByFolder($folder)
    {
        global $db;

        if (empty($folder))
            return array();

        $record = $db->Execute("SELECT * FROM `" . TABLE_IMAGE_TYPE . "` WHERE `folder` = '$folder'");

        return isset($record->fields['class']) ? $record->fields : array();
    }

    /**
     * Get org image path
     *
     * @param string $image Image Filename
     * @return null|string
     */
    public static function getOrgImage($image)
    {
        if (!is_string($image) || empty($image))
            return null;

        return file_exists($path = realpath(self::getOrgDir() . trim($image))) ? $path : null;
    }

    /**
     * Get Image Org Dir
     */
    public static function getOrgDir()
    {
        return (($dir = _SRV_WEBROOT . _SRV_WEB_IMAGES . _DIR_ORG) && is_dir($dir)) ? $dir : null;
    }

    /**
     * PLUGIN STATUS
     */
    public static function status()
    {
        if (!ew_viabiona_plugin::status())
            return false;

        if (self::isFrontend() && !self::check_conf('CONFIG_EW_VIABIONA_PLUGIN_CREATE_THUMBS_FRONTEND'))
            return false;

        return true;
    }

    /**
     * Check if is frontend
     *
     * @return bool true|false
     */
    public static function isFrontend()
    {
        return !self::isAdmin();
    }

    /**
     * Check if is backend
     *
     * @return bool true|false
     */
    public static function isAdmin()
    {
        return (defined('USER_POSITION') && USER_POSITION == 'admin') ? true : false;
    }

    /**
     * GET CONFIGURATION SETTING
     *
     * @param    string $key Configuration Key / CONSTANT
     * @return    bool    Returns config value
     */
    public static function check_conf($key)
    {
        return ew_viabiona_plugin::check_conf($key);
    }

    /**
     * parent::processImage
     * Overwritten to check if thumb already exists
     *
     * @param string $image_name
     * @param bool   $overwrite
     */
    public function processImage($image_name, $overwrite = false)
    {
        $path = $this->getPath();
        $dir = _SRV_WEBROOT . $path;
        $original = $dir . 'org/';
        $types = $this->getImageTypes();
        foreach ($types as $key => $typ) {

            if ($this->thumbExists($dir . '/' . $typ['folder'] . '/' . $image_name))
                continue;

            $this->check_folder($typ['folder'], $dir);

            if ($typ['process'] != 'false') {
                $image = new \image();

                $image->max_height = $typ['height'];
                $image->max_width = $typ['width'];
                $image->resource = $original . $image_name;
                $image->target_dir = $dir . '/' . $typ['folder'] . '/';
                $image->target_name = $image_name;
                $image->watermark = $typ['watermark'] == 'false' ? false : true;

                if ($overwrite || !is_file($image->target_dir)) {
                    $this->response = $image->createThumbnail();
                }

            } else {
                copy($original . $image_name, $dir . $typ['folder'] . '/' . $image_name);
            }
        }

    }

    /**
     * parent::getImageTypes
     * Overwritten to get only one image class, when called from frontend
     *
     * @param string $class
     * @return array
     * @return array
     */
    public function getImageTypes($class = '')
    {
        if (self::isAdmin())
            return parent::getImageTypes($class);

        if (empty($this->imageTypes))
            return array();

        return array($this->imageTypes);
    }

    /**
     * Checks if thumb exists
     *
     * @param string $thumb Server path to thumbnail
     * @return bool
     */
    public static function thumbExists($thumb)
    {
        return @file_exists(realpath($thumb)) ? true : false;
    }

    /**
     * parent::check_folder
     * Overwritten for recursive mkdir
     *
     * @param string $folder
     * @param string $dir
     */
    public function check_folder($folder, $dir)
    {
        if (!is_dir($dir . $folder)) {
            mkdir($dir . $folder, 0777, true);
        }
    }
}