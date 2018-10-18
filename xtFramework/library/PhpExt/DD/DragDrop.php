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
 * @see PhpExt_Object
 * @subpackage DD
 */

include_once 'PhpExt/Object.php';
/**
 * @package PhpExt_DD_DragDrop
 *  */
class PhpExt_DD_DragDrop extends PhpExt_Object
{
	// available
	/**
	 * The availabe property is false until the linked dom element is accessible.
	 * @param boolean
	 * @return PhpExt_DD_DragDrop
	 */
	public function setAvailable($value) {
		$this->setExtConfigProperty("available", $value);
		return $this;
	}
	/**
	 * The availabe property is false until the linked dom element is accessible.
	 * @return boolean
	 */
	public function getAvailable() {
		return $this->getExtConfigProperty("available");
	}

	// config
	/**
	 * Configuration attributes passed into the constructor
	 * @param object
	 * @return PhpExt_DD_DragDrop
	 */
	public function setConfig($value) {
		$this->setExtConfigProperty("config", $value);
		return $this;
	}
	/**
	 * Configuration attributes passed into the constructor
	 * @return object
	 */
	public function getConfig() {
		return $this->getExtConfigProperty("config");
	}

	// groups
	/**
	 * The group defines a logical collection of DragDrop objects that are related. Instances only get events when interacting with other DragDrop object in the same group. This lets us define multiple groups using a single DragDrop subclass if we want.
	 * @param object
	 * @return PhpExt_DD_DragDrop
	 */
	public function setGroups($value) {
		$this->setExtConfigProperty("groups", $value);
		return $this;
	}
	/**
	 * The group defines a logical collection of DragDrop objects that are related. Instances only get events when interacting with other DragDrop object in the same group. This lets us define multiple groups using a single DragDrop subclass if we want.
	 * @return object
	 */
	public function getGroups() {
		return $this->getExtConfigProperty("groups");
	}

	// hasOuterHandles
	/**
	 * By default, drags can only be initiated if the mousedown occurs in the region the linked element is. This is done in part to work around a bug in some browsers that mis-report the mousedown if the previous mouseup happened outside of the window. This property is set to true if outer handles are defined.
	 * @param boolean
	 * @return PhpExt_DD_DragDrop
	 */
	public function setHasOuterHandles($value) {
		$this->setExtConfigProperty("hasOuterHandles", $value);
		return $this;
	}
	/**
	 * By default, drags can only be initiated if the mousedown occurs in the region the linked element is. This is done in part to work around a bug in some browsers that mis-report the mousedown if the previous mouseup happened outside of the window. This property is set to true if outer handles are defined.
	 * @return boolean
	 */
	public function getHasOuterHandles() {
		return $this->getExtConfigProperty("hasOuterHandles");
	}

	// id
	/**
	 * The id of the element associated with this object. This is what we refer to as the "linked element" because the size and position of this element is used to determine when the drag and drop objects have interacted.
	 * @param string
	 * @return PhpExt_DD_DragDrop
	 */
	public function setId($value) {
		$this->setExtConfigProperty("id", $value);
		return $this;
	}
	/**
	 * The id of the element associated with this object. This is what we refer to as the "linked element" because the size and position of this element is used to determine when the drag and drop objects have interacted.
	 * @return string
	 */
	public function getId() {
		return $this->getExtConfigProperty("id");
	}

	// invalidHandleClasses
	/**
	 * An indexted array of css class names for elements that will be ignored if clicked.
	 * @param string
	 * @return PhpExt_DD_DragDrop
	 */
	public function setInvalidHandleClasses($value) {
		$this->setExtConfigProperty("invalidHandleClasses", $value);
		return $this;
	}
	/**
	 * An indexted array of css class names for elements that will be ignored if clicked.
	 * @return string
	 */
	public function getInvalidHandleClasses() {
		return $this->getExtConfigProperty("invalidHandleClasses");
	}


	// invalidHandleIds
	/**
	 * An associative array of ids for elements that will be ignored if clicked
	 * @param string
	 * @return PhpExt_DD_DragDrop
	 */
	public function setInvalidHandleIds($value) {
		$this->setExtConfigProperty("invalidHandleIds", $value);
		return $this;
	}
	/**
	 * An associative array of ids for elements that will be ignored if clicked
	 * @return string
	 */
	public function getInvalidHandleIds() {
		return $this->getExtConfigProperty("invalidHandleIds");
	}

	// isTarget
	/**
	 * By default, all insances can be a drop target. This can be disabled by setting isTarget to false.
	 * @param boolean
	 * @return PhpExt_DD_DragDrop
	 */
	public function setIsTarget($value) {
		$this->setExtConfigProperty("isTarget", $value);
		return $this;
	}
	/**
	 * By default, all insances can be a drop target. This can be disabled by setting isTarget to false.
	 * @return boolean
	 */
	public function getIsTarget() {
		return $this->getExtConfigProperty("isTarget");
	}

	// maintainOffset
	/**
	 * Maintain offsets when we resetconstraints. Set to true when you want the position of the element relative to its parent to stay the same when the page changes
	 * @param boolean
	 * @return PhpExt_DD_DragDrop
	 */
	public function setMaintainOffset($value) {
		$this->setExtConfigProperty("maintainOffset", $value);
		return $this;
	}
	/**
	 * Maintain offsets when we resetconstraints. Set to true when you want the position of the element relative to its parent to stay the same when the page changes
	 * @return boolean
	 */
	public function getMaintainOffset() {
		return $this->getExtConfigProperty("maintainOffset");
	}

	// padding
	/**
	 * The padding configured for this drag and drop object for calculating the drop zone intersection with this object.
	 * @param int[]
	 * @return PhpExt_DD_DragDrop
	 */
	public function setPadding($value) {
		$this->setExtConfigProperty("padding", $value);
		return $this;
	}
	/**
	 * The padding configured for this drag and drop object for calculating the drop zone intersection with this object.
	 * @return int[]
	 */
	public function getPadding() {
		return $this->getExtConfigProperty("padding");
	}


	// primaryButtonOnly
	/**
	 * By default the drag and drop instance will only respond to the primary button click (left button for a right-handed mouse). Set to true to allow drag and drop to start with any mouse click that is propogated by the browser
	 * @param boolean
	 * @return PhpExt_DD_DragDrop
	 */
	public function setPrimaryButtonOnly($value) {
		$this->setExtConfigProperty("primaryButtonOnly", $value);
		return $this;
	}
	/**
	 * By default the drag and drop instance will only respond to the primary button click (left button for a right-handed mouse). Set to true to allow drag and drop to start with any mouse click that is propogated by the browser
	 * @return boolean
	 */
	public function getPrimaryButtonOnly() {
		return $this->getExtConfigProperty("primaryButtonOnly");
	}

	// xTicks
	/**
	 * Array of pixel locations the element will snap to if we specified a horizontal graduation/interval. This array is generated automatically when you define a tick interval.
	 * @param int[]
	 * @return PhpExt_DD_DragDrop
	 */
	public function setXTicks($value) {
		$this->setExtConfigProperty("xTicks", $value);
		return $this;
	}
	/**
	 * Array of pixel locations the element will snap to if we specified a horizontal graduation/interval. This array is generated automatically when you define a tick interval.
	 * @return int[]
	 */
	public function getXTicks() {
		return $this->getExtConfigProperty("xTicks");
	}


	// yTicks
	/**
	 * Array of pixel locations the element will snap to if we specified a vertical graduation/interval. This array is generated automatically when you define a tick interval.
	 * @param int[]
	 * @return PhpExt_DD_DragDrop
	 */
	public function setYTicks($value) {
		$this->setExtConfigProperty("yTicks", $value);
		return $this;
	}
	/**
	 * Array of pixel locations the element will snap to if we specified a vertical graduation/interval. This array is generated automatically when you define a tick interval.
	 * @return int[]
	 */
	public function getYTicks() {
		return $this->getExtConfigProperty("yTicks");
	}

	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.dd.DragDrop","dragdrop");

		$validProps = array(
		              "available",
		              "config",
		              "groups",
		              "hasOuterHandles",
		              "invalidHandleClasses",
		              "invalidHandleIds",
		              "isTarget",
		              "padding",
		              "primaryButtonOnly",
		              "xTicks",
		              "yTicks"
		);
		$this->addValidConfigProperties($validProps);
	}
}

?>