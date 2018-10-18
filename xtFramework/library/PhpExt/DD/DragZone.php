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
include_once 'PhpExt/DD/DragSource.php';
/**
 * @package PhpExtUx_DragSelector
 *  */
class PhpExt_DD_DragZone extends PhpExt_DD_DragSource
{
	// containerScroll
	/**
	 * True to register this container with the Scrollmanager for auto scrolling during drag operations.
	 * @param boolean
	 * @return PhpExt_TreePanel
	 */
	public function setContainerScroll($value) {
		$this->setExtConfigProperty("containerScroll", $value);
		return $this;
	}
	/**
	 * True to register this container with the Scrollmanager for auto scrolling during drag operations.
	 * @return boolean
	 */
	public function getContainerScroll() {
		return $this->getExtConfigProperty("containerScroll");
	}

	// hlColor
	/**
	 * The color to use when visually highlighting the drag source in the afterRepair method after a failed drop (defaults to "c3daf9" - light blue)
	 * @param String
	 * @return PhpExt_TreePanel
	 */
	public function setHlColor($value) {
		$this->setExtConfigProperty("hlColor", $value);
		return $this;
	}
	/**
	 * The color to use when visually highlighting the drag source in the afterRepair method after a failed drop (defaults to "c3daf9" - light blue)
	 * @return String
	 */
	public function getHlColor() {
		return $this->getExtConfigProperty("hlColor");
	}

	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.dd.DragZone","dragzone");

		$validProps = array(
		              "containerScroll",
		              "hlColor"
		);
		$this->addValidConfigProperties($validProps);
	}

}






?>