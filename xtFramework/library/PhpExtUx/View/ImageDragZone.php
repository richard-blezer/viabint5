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
 * @see PhpExt_Ext
 */
include_once 'PhpExt/DD/DragZoneConfigObject.php';
/**
 * @see PhpExt_Ext
 */
include_once 'PhpExt/DD/DragZoneConfigObjectCollection.php';
/**
 * @package PhpExtUx_ImageChooser
 *  */
class PhpExtUx_ImageDragZone extends PhpExt_Object {

    // view
    /**
     */
    protected function setView($value) {
    	$this->setExtConfigProperty("view", $value);
    	return $this;
    }
    /**
     */
    protected function getView() {
    	return $this->getExtConfigProperty("view");
    }
    // config
    /**
     */
    protected function setConfig($value) {
    	$this->setExtConfigProperty("config", $value);
    	return $this;
    }
    /**
     */
    protected function getConfig() {
    	return $this->getExtConfigProperty("config");
    }

	public function __construct(PhpExt_DataView $view, PhpExt_DD_DragZoneConfigObject $config) {
		parent::__construct();
		$this->setExtClassInfo("ImageDragZone", null);

		$validProps = array(
			"view",
			"config"
		);
		$this->addValidConfigProperties($validProps);

		$this->setConfig($config);
		$this->setView($view);
	}

	public function getJavascript($lazy = false, $varName = null) {
		if ($this->_varName == null) {
			$configParams = $this->getConfig($lazy);

			$view = $this->getView($lazy);


			$className = $this->_extClassName;

			$js = "new $className(".$view->getJavascript().", ".$configParams->getJavascript().")";
			if ($varName != null) {
				$this->_varName = $varName;
				$js = "var $varName = $js;";
			}
			return $js;
		}
		else
			return $this->_varName;
	}

}

?>