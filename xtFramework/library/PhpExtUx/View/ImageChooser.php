<?php

/**
 *
 *
 * @author Matthias Benkwitz (PhpExtUx_ImageChooser)
 * @website http://www.bui-hinsche.de
 * @created 2008-05-05
 * @version 0.1
 *
example:

$chooser = new PhpExtUx_ImageChooser();
$chooser->setUrl(<your url>)
		->setHeight(300)
		->setWidth(450);

 *
 *
**/
/**
 * @see PhpExt_Ext
 */
include_once 'PhpExt/Ext.php';

/**
 * @package PhpExtUx_ImageChooser
 *  */
class PhpExtUx_ImageChooser extends PhpExt_Object
{
    // url
    /**
     */
    public function setUrl($value) {
    	$this->setExtConfigProperty("url", $value);
    	return $this;
    }
    /**
     */
    public function getUrl() {
    	return $this->getExtConfigProperty("url");
    }

    // file_types_description
    /**
     */
    public function setHeight($value) {
    	$this->setExtConfigProperty("height", $value);
    	return $this;
    }
    /**
     */
    public function getHeight() {
    	return $this->getExtConfigProperty("height");
    }

    // width
    /**
     */
    public function setWidth($value) {
    	$this->setExtConfigProperty("width", $value);
    	return $this;
    }
    /**
     */
    public function getWidth() {
    	return $this->getExtConfigProperty("width");
    }



	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("ImageChooser","chooser");

		$validProps = array(
			"height",
			"width",
		    "url"
		);
		$this->addValidConfigProperties($validProps);
	}

	public function getJavascript($lazy = false, $varName = null) {
		return parent::getJavascript(false, $varName);
	}



}






?>