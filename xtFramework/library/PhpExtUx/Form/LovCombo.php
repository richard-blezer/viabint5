<?php
/**
 * PHP-Ext Library
 * http://php-ext.googlecode.com
 * @author Matthias Benkwitz <mb[at]bui-hinsche[dot]de>
 * @copyright 2008 Matthias Benkwitz
 * @license http://www.gnu.org/licenses/lgpl.html
 * @link http://php-ext.googlecode.com
 *
 * Reference for Ext JS:
 * /**
 * Ext.ux.form.LovCombo, List of Values Combo
 *
 * @author    Ing. Jozef Sakáloš
 * @copyright (c) 2008, by Ing. Jozef Sakáloš
 * @date      16. April 2008
 * @version   $Id: Ext.ux.form.LovCombo.js 285 2008-06-06 09:22:20Z jozo $
 *
 * @license Ext.ux.form.LovCombo.js is licensed under the terms of the Open Source
 * LGPL 3.0 license. Commercial use is permitted to the extent that the
 * code/component(s) do NOT become part of another Open Source or Commercially
 * licensed development library or toolkit without explicit permission.
 *
 * License details: http://www.gnu.org/licenses/lgpl.html
 */

/**
 * @see PhpExt_Form_ComboBox
 */
include_once 'PhpExt/Form/ComboBox.php';

/**
 * Provides a date input field with a Ext.DatePicker dropdown and automatic date validation.
 * @package PhpExt
 * @subpackage Form
 */
class PhpExt_Form_LovCombo extends PhpExt_Form_ComboBox
{
    // configuration options
    /**
     * @cfg {String} checkField name of field used to store checked state.
	 * It is automatically added to existing fields.
	 * Change it only if it collides with your normal field.
     * @param string $value
     * @return PhpExt_LovCombo
     */
    public function setCheckField($value) {
    	$this->setExtConfigProperty("checkField", $value);
    	return $this;
    }
    /**
     * @cfg {String} checkField name of field used to store checked state (defaults to 'checked').
     * @return string
     */
    public function getCheckField() {
    	return $this->getExtConfigProperty("checkField");
    }
    /**
     * @cfg {String}  separator to use between values and texts
     * @param string $value
     * @return PhpExt_LovCombo
     */
    public function setSeparator($value) {
    	$this->setExtConfigProperty("separator", $value);
    	return $this;
    }
    /**
     * @cfg {String} separator to use between values and texts (defaults to ',').
     * @return string
     */
    public function getSeparator() {
    	return $this->getExtConfigProperty("separator");
    }

	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.form.LovCombo", "lovcombo");

		$validProps = array(
		    "checkField",
		    "separator"
		);
		$this->addValidConfigProperties($validProps);
	}


    /**
	 * Helper function to create a ComboBox.  Useful for quick adding it to a ComponentCollection
	 *
	 * @param string $name The field's HTML name attribute.
	 * @param string $labelThe label text to display next to this field (defaults to '')
	 * @param string $id The unique id of this component (defaults to an auto-assigned id).
	 * @param string $hiddenName If specified, a hidden form field with this name is dynamically generated to store the field's data value (defaults to the underlying DOM element's name). Required for the combo's value to automatically post during a form submission.
	 * @return PhpExt_Form_ComboBox
	 */
	public static function createLovCombo($name, $label = null, $id = null, $hiddenName = null) {
	    $c = new PhpExt_Form_LovCombo();
	    $c->setName($name);
	    if ($label !== null)
	      $c->setFieldLabel($label);
	    if ($id !== null)
	      $c->setId($id);
	    if ($hiddenName !== null)
	      $c->setHiddenName($hiddenName);
	    return $c;
	}
}