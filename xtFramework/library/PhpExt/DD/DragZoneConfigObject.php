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
 * @see PhpExt_Config_ConfigObject
 */
include_once 'PhpExt/Config/ConfigObject.php';


/**
 * @package PhpExt
 * @subpackage Data
 */
class PhpExt_DD_DragZoneConfigObject extends PhpExt_Config_ConfigObject
{
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

	public function __construct($ddGroup, $containerScroll = true, $dropAllowed = null,
		$dropNotAllowed = null, $hlColor = null) {
		parent::__construct();

		$validProps = array(
		              "ddGroup",
		              "dropAllowed",
		              "dropNotAllowed",
		              "containerScroll",
		              "hlColor"
		);
		$this->addValidConfigProperties($validProps);

		$this->setDdGroup($ddGroup);
		$this->setContainerScroll($containerScroll);
		// optional
		if ($dropAllowed !== null)
		$this->setDropAllowed($dropAllowed);
		if ($dropNotAllowed !== null)
		$this->setDropNotAllowed($dropNotAllowed);
		if ($hlColor !== null)
		$this->setHlColor($hlColor);

	}

}

