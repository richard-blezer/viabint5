<?php

/**
 * Makes a Panel to provide the ability to upload multiple files using the SwfUpload flash script.
 *
 * @author Matthias Benkwitz (PhpExtUx_SwfUploadPanel)
 * @website http://www.bui-hinsche.de
 * @created 2008-03-06, 2008-05-06
 * @version 0.4, 0.1
 *
 *
 *
*/

/**
 * @see PhpExt_Ext
 */
include_once 'PhpExt/Ext.php';
/**
 * @see PhpExt_Panel
 */
include_once 'PhpExt/Form/FormPanel.php';

/**
 * @package PhpExtUx_SwfUploadPanel
 *  */
class PhpExtUx_Wizzard extends PhpExt_Form_FormPanel
{
	// ActiveItem
	/**
	 * A string id or the numeric index of the tab that should be initially activated on render (defaults to none).
	 * @param string|integer $value
	 * @return PhpExt_TabPanel
	 */
	public function setActiveItem($value) {
		$this->setExtConfigProperty("activeItem", $value);
		return $this;
	}
	/**
	 * A string id or the numeric index of the tab that should be initially activated on render (defaults to none).
	 * @return string|integer
	 */
	public function getActiveItem() {
		return $this->getExtConfigProperty("activeItem");
	}

	// cancelHandler
	/**
	 * A function called when the cancel button is clicked (can be used instead of click event)
	 * @param PhpExt_Handler|PhpExt_JavascriptStm $value
	 * @return PhpExt_Button
	 */
	public function setCancelHandler($value) {
		$this->setExtConfigProperty("cancelHandler", $value);
		return $this;
	}
	/**
	 * A function called when the cancel button is clicked (can be used instead of click event)
	 * @return PhpExt_Handler|PhpExt_JavascriptStm
	 */
	public function getCancelHandler() {
		return $this->getExtConfigProperty("cancelHandler");
	}

	// submitHandler
	/**
	 * A function called when the submit button is clicked (can be used instead of click event)
	 * @param PhpExt_Handler|PhpExt_JavascriptStm $value
	 * @return PhpExt_Button
	 */
	public function setSubmitHandler($value) {
		$this->setExtConfigProperty("submitHandler", $value);
		return $this;
	}
	/**
	 * A function called when the submit button is clicked (can be used instead of click event)
	 * @return PhpExt_Handler|PhpExt_JavascriptStm
	 */
	public function getSubmitHandler() {
		return $this->getExtConfigProperty("submitHandler");
	}

	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.PowerWizard",null);

		$validProps = array(
                        "activeItem",
                        "cancelHandler",
                        "submitHandler"
		);
		$this->addValidConfigProperties($validProps);
	}

	public function getJavascript($lazy = false, $varName = null) {
		return parent::getJavascript(false, $varName);
	}



}






?>