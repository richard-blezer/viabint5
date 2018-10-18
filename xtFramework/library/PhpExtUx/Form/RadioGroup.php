<?php
/**
 * PHP-Ext Library
 * http://php-ext.googlecode.com
 * @author Matthias Benkwitz <mb[at]bui-hinsche[dot]de>
 * @copyright 2008 Matthias Benkwitz
 * @license http://www.gnu.org/licenses/lgpl.html
 * @link http://php-ext.googlecode.com
 *
 * Reference for Ext JS: http://extjs.com
 **
 * @author Robert Williams (vtswingkid)
 * @version 1.0.4
 */


/**
 * @see PhpExt_Form_Radio
 */
include_once 'PhpExt/Form/Radio.php';

/**
 * Single radio field. Same as {@link PhpExt_Form_Checkbox}, but provided as a convenience for automatically setting the input type. Radio grouping is handled automatically by the browser if you give each radio in a group the same name.
 * @package PhpExt
 * @subpackage Form
 */
class PhpExtUx_Form_RadioGroup extends PhpExt_Form_Radio
{
    /**
     * @cfg {String} focusClass The CSS class to use when the checkbox receives focus
     * @param string $value
     * @return PhpExt_RadioGroup
     */
    public function setFocusClass($value) {
    	$this->setExtConfigProperty("focusClass", $value);
    	return $this;
    }
    /**
     * @cfg {String} focusClass The CSS class to use when the checkbox receives focus (defaults to undefined).
     * @return string
     */
    public function getFocusClass() {
    	return $this->getExtConfigProperty("focusClass");
    }

    /**
     * @cfg {String} fieldClass The default CSS class for the checkbox (defaults to "x-form-field")
     * @param string $value
     * @return PhpExt_RadioGroup
     */
    public function setFieldClass($value) {
    	$this->setExtConfigProperty("fieldClass", $value);
    	return $this;
    }
    /**
     * @cfg {String} fieldClass The default CSS class for the checkbox (defaults to "x-form-field")
     * @return string
     */
    public function getFieldClass() {
    	return $this->getExtConfigProperty("fieldClass");
    }

   /**
     * @cfg {Boolean} checked True if the the checkbox should render already checked (defaults to false)
     * @param boolean
     * @return PhpExt_RadioGroup
     */
    public function setChecked($value) {
    	$this->setExtConfigProperty("checked", $value);
    	return $this;
    }
    /**
     * @cfg {Boolean} checked True if the the checkbox should render already checked (defaults to false)
     * @return boolean
     */
    public function getChecked() {
    	return $this->getExtConfigProperty("checked");
    }


	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.RadioGroup","radiogroup");
	}

    /**
	 * Helper function to create a Radio field.  Useful for quick adding it to a ComponentCollection
	 *
	 * @param string $name The field's HTML name attribute.
	 * @param string $label The label text to display next to this field (defaults to '')
	 * @param string $id The unique id of this component (defaults to an auto-assigned id).
	 * @return PhpExt_Form_Radio
	 */
	public static function createRadio($name, $label = null, $id = null, $inputValue = null) {
	    $c = new PhpExt_Form_Radio();
	    $c->setName($name);
	    if ($label !== null)
	      $c->setFieldLabel($label);
	    if ($id !== null)
	      $c->setId($id);
	    if ($inputValue !== null)
	      $c->setInputValue($inputValue);
        return $c;
	}
}

