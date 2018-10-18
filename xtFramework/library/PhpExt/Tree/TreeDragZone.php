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
include_once 'PhpExt/DD/DragDrop.php';
/**
 * @package PhpExt_Tree
 * @subpackage Tree, DD
 *  */
class PhpExt_Tree_TreeDragZone extends PhpExt_DD_DragZone
{
	// ddGroup
	/**
	 * A named drag drop group to which this object belongs. If a group is specified, then this object will only interact with other drag drop objects in the same group (defaults to 'TreeDD').
	 * @param string
	 * @return PhpExt_DD_DD
	 */
	public function setDdGroup($value) {
		$this->setExtConfigProperty("ddGroup", $value);
		return $this;
	}
	/**
	 * A named drag drop group to which this object belongs. If a group is specified, then this object will only interact with other drag drop objects in the same group (defaults to 'TreeDD').
	 * @return string
	 */
	public function getDdGroup() {
		return $this->getExtConfigProperty("ddGroup");
	}


	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.tree.TreeDragZone","treedragzone");

		$validProps = array(
		              "ddGroup"
		);
		$this->addValidConfigProperties($validProps);
	}
}






?>