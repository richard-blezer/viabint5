<?php
/**
 * @see PhpExt_Ext
 */
include_once 'PhpExt/Ext.php';
/**
 * @see PhpExt_Form_Checkbox
 */
include_once 'PhpExt/Form/Checkbox.php';
/**
 * @see PhpExt_Toolbar_IToolbarItem
 */
include_once 'PhpExt/Toolbar/IToolbarItem.php';

/**
 * @package PhpExtUx
 * @subpackage App
 */
class PhpExtUx_App_CheckboxField extends PhpExt_Form_Checkbox implements PhpExt_Toolbar_IToolbarItem
{	
	public function __construct($id) {
		parent::__construct();
 		$this->setExtClassInfo("Ext.app.CheckboxField","checkbox");
        $this->setId('check_all_prod_'.$id);
	}
	
	public function getJavascript($lazy = false, $varName = null) {		
		return parent::getJavascript(false, $varName);
	}
}

?>