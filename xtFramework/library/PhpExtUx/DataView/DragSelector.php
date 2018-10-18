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
include_once 'PhpExt/DataView.php';
/**
 * @package PhpExtUx_DragSelector
 *  */
class PhpExtUx_DragSelector extends PhpExt_Object
{
	// dragSafe
	/**
	 *
	 * @param boolean
	 * @return PhpExt_TreePanel
	 */
	public function setDragSafe($value) {
		$this->setExtConfigProperty("dragSafe", $value);
		return $this;
	}
	/**
	 *
	 * @return boolean
	 */
	public function getDragSafe() {
		return $this->getExtConfigProperty("dragSafe");
	}

	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.DataView.DragSelector",null);

		$validProps = array(
		              "dragSafe"
		);
		$this->addValidConfigProperties($validProps);
	}

	public function getJavascript($lazy = false, $varName = null) {
		return parent::getJavascript(false, $varName);
	}



}






?>