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
 * @package PhpExt_DD_DD
 * @subpackage DD
 *  */
class PhpExt_DD_DD extends PhpExt_DD_DragDrop
{
    /**
     * A DragDrop implementation where the linked element follows the mouse cursor during a drag.
     */
	// scroll
	/**
	 *
	 * @param boolean
	 * @return PhpExt_DD_DD
	 */
	public function setScroll($value) {
		$this->setExtConfigProperty("scroll", $value);
		return $this;
	}
	/**
	 *
	 * @return boolean
	 */
	public function getScroll() {
		return $this->getExtConfigProperty("scroll");
	}


	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.dd.DD","dd");

		$validProps = array(
		              "scroll"
		);
		$this->addValidConfigProperties($validProps);
	}
}






?>