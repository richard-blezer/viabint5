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
include_once 'PhpExt/DD/DD.php';
/**
 * @package PhpExt_DD_DD
 * @subpackage DD
 *  */
class PhpExt_DD_DDProxy extends PhpExt_DD_DD
{
    /**
     * A DragDrop implementation that inserts an empty, bordered div into the document that follows the cursor during drag operations.
     * At the time of the click, the frame div is resized to the dimensions of the linked html element, and moved to the exact location of the linked element.
     * References to the "frame" element refer to the single proxy element that was created to be dragged in place of all DDProxy elements on the page.
     */


	// dragElId
	/**
	 * <static> The default drag frame div id
	 * @param String
	 * @return PhpExt_DD_DDProxy
	 */
	public function setDragElId($value) {
		$this->setExtConfigProperty("dragElId", $value);
		return $this;
	}
	/**
	 * <static> The default drag frame div id
	 * @return String
	 */
	public function getDragElId() {
		return $this->getExtConfigProperty("dragElId");
	}


	// centerFrame
	/**
	 * By default the frame is positioned exactly where the drag element is, so we use the cursor offset provided by Ext.dd.DD. Another option that works only if you do not have constraints on the obj is to have the drag frame centered around the cursor. Set centerFrame to true for this effect.
	 * @param boolean
	 * @return PhpExt_DD_DDProxy
	 */
	public function setCenterFrame($value) {
		$this->setExtConfigProperty("centerFrame", $value);
		return $this;
	}
	/**
	 * By default the frame is positioned exactly where the drag element is, so we use the cursor offset provided by Ext.dd.DD. Another option that works only if you do not have constraints on the obj is to have the drag frame centered around the cursor. Set centerFrame to true for this effect.
	 * @return boolean
	 */
	public function getCenterFrame() {
		return $this->getExtConfigProperty("centerFrame");
	}


	// resizeFrame
	/**
	 * By default we resize the drag frame to be the same size as the element we want to drag (this is to get the frame effect). We can turn it off if we want a different behavior.
	 * @param boolean
	 * @return PhpExt_DD_DDProxy
	 */
	public function setResizeFrame($value) {
		$this->setExtConfigProperty("resizeFrame", $value);
		return $this;
	}
	/**
	 * By default we resize the drag frame to be the same size as the element we want to drag (this is to get the frame effect). We can turn it off if we want a different behavior.
	 * @return boolean
	 */
	public function getResizeFrame() {
		return $this->getExtConfigProperty("resizeFrame");
	}


	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.dd.DDProxy","ddproxy");

		$validProps = array(
		              "dragElId",
		              "centerFrame",
		              "resizeFrame"
		);
		$this->addValidConfigProperties($validProps);
	}
}






?>