<?php
/**
 * PHP-Ext Library
 * http://php-ext.googlecode.com
 * @author Sergei Walter <sergeiw[at]gmail[dot]com>
 * @copyright 2008 Sergei Walter
 * @license http://www.gnu.org/licenses/lgpl.html
 * @link http://php-ext.googlecode.com
 * 
 * Reference for Ext JS: http://extjs.com
 * 
 */

/**
 * @see PhpExt_Form_Field
 */
include_once 'PhpExt/Form/Field.php';

/**
 * Provides a lightweight HTML Editor component.
 * <p><b>Note: The focus/blur and validation marking functionality inherited from Ext.form.Field is NOT supported by this editor.</b></p>
 * <p>An Editor is a sensitive component that can't be used in all spots standard fields can be used. Putting an Editor within any element that has display set to 'none' can cause problems in Safari and Firefox due to their default iframe reloading bugs.</p>
 * @package PhpExt
 * @subpackage Form
 */
class PhpExt_Form_CKEditor extends PhpExt_Form_Field
{	

	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.form.CKEditor","ckeditor");
		
		$validProps = array(
		    "createLinkText",
		    "defaultLinkValue",
		    "enableAlignments",
		    "enableColors",
		    "enableFont",
		    "enableFontSize",
		    "enableFormat",
		    "enableLinks",
		    "enableLists",
		    "enableSourceEdit",
		    "fontFamilies"
		);
		$this->addValidConfigProperties($validProps);
	}	 

    /**
	 * Helper function to create an HtmlEditor.  Useful for quick adding it to a ComponentCollection
	 *
	 * @param string $name The field's HTML name attribute.
	 * @param string $label The label text to display next to this field (defaults to '')
	 * @param string $id The unique id of this component (defaults to an auto-assigned id).
	 * @return PhpExt_Form_HtmlEditor
	 */
	public static function createCKEditor($name, $label = null, $id = null) {
	    $c = new PhpExt_Form_CKEditor();
	    $c->setName($name);
	    if ($label !== null)
	      $c->setFieldLabel($label);
	    if ($id !== null)
	      $c->setId($id);
        return $c;
	}
	
}

