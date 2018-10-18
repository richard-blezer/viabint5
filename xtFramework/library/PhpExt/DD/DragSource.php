<?php
/**
 * PHP-Ext Library
 * http://php-ext.googlecode.com
 * @author Matthias Benkwitz (Matthias[dot]Benkwitz[at]gmx[dot]de)
 * @website http://www.bui-hinsche.de
 * @created 2008-06-07
 * @version 0.1
 * @copyright 2008 Matthias Benkwitz
 * @license http://www.gnu.org/licenses/lgpl.html
 * @link http://php-ext.googlecode.com
 * /

/**
 * @see PhpExt_Ext
 */
include_once 'PhpExt/Ext.php';
/**
 * @see PhpExt_DataView
 */
include_once 'PhpExt/DD/DDProxy.php';
/**
 * @package PhpExt_DD_DD
 * @subpackage DD
 *  */
class PhpExt_DD_DragSource extends PhpExt_DD_DDProxy
{
    /**
     *  A simple class that provides the basic implementation needed to make any element draggable.
     */


	// ddGroup
	/**
	 * A named drag drop group to which this object belongs. If a group is specified, then this object will only interact with other drag drop objects in the same group (defaults to undefined).
	 * @param String
	 * @return PhpExt_DD_DragSource
	 */
	public function setDdGroup($value) {
		$this->setExtConfigProperty("ddGroup", $value);
		return $this;
	}
	/**
	 * A named drag drop group to which this object belongs. If a group is specified, then this object will only interact with other drag drop objects in the same group (defaults to undefined).
	 * @return String
	 */
	public function getDdGroup() {
		return $this->getExtConfigProperty("ddGroup");
	}


	// dropAllowed
	/**
	 * The CSS class returned to the drag source when drop is allowed (defaults to "x-dd-drop-ok").
	 * @param boolean
	 * @return PhpExt_DD_DragSource
	 */
	public function setDropAllowed($value) {
		$this->setExtConfigProperty("dropAllowed", $value);
		return $this;
	}
	/**
	 * The CSS class returned to the drag source when drop is allowed (defaults to "x-dd-drop-ok").
	 * @return boolean
	 */
	public function getDropAllowed() {
		return $this->getExtConfigProperty("dropAllowed");
	}

	// dropNotAllowed
	/**
	 * The CSS class returned to the drag source when drop is not allowed (defaults to "x-dd-drop-nodrop").
	 * @param boolean
	 * @return PhpExt_DD_DragSource
	 */
	public function setDropNotAllowed($value) {
		$this->setExtConfigProperty("dropNotAllowed", $value);
		return $this;
	}
	/**
	 * The CSS class returned to the drag source when drop is not allowed (defaults to "x-dd-drop-nodrop").
	 * @return boolean
	 */
	public function getDropNotAllowed() {
		return $this->getExtConfigProperty("dropNotAllowed");
	}


	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.dd.DDProxy","ddproxy");

		$validProps = array(
		              "ddGroup",
		              "dropAllowed",
		              "dropNotAllowed"
		);
		$this->addValidConfigProperties($validProps);
	}
}






?>