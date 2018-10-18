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
 *
 * Reference for Ext JS: http://extjs.com
 *
 */


/**
 * @see PhpExt_Object
 */
include_once'PhpExt/Object.php';

/**
 *  Provides sorting of nodes in a TreePanel
 *
 * @package PhpExt
 * @subpackage Tree
 */
abstract class PhpExt_Tree_TreeSorter extends PhpExt_Object
{
	// caseSensitive
	/**
	 * true for case sensitive sort (defaults to false)
	 * @param boolean
	 * @return PhpExt_Tree_TreeSorter
	 */
	public function setCaseSensitive($value) {
		$this->setExtConfigProperty("caseSensitive", $value);
		return $this;
	}
	/**
	 * true for case sensitive sort (defaults to false)
	 * @return boolean
	 */
	public function getCaseSensitive() {
		return $this->getExtConfigProperty("caseSensitive");
	}

	// dir
	/**
	 * The direction to sort (asc or desc) (defaults to asc)
	 * @param string $value
	 * @return PhpExt_Tree_TreeSorter
	 */
	public function setDir($value) {
		$this->setExtConfigProperty("dir", $value);
		return $this;
	}
	/**
	 * The direction to sort (asc or desc) (defaults to asc)
	 * @return string
	 */
	public function getDir() {
		return $this->getExtConfigProperty("dir");
	}

	// folderSort
	/**
	 * True to sort leaf nodes under non leaf nodes
	 * @param boolean
	 * @return PhpExt_Tree_TreeSorter
	 */
	public function setFolderSort($value) {
		$this->setExtConfigProperty("folderSort", $value);
		return $this;
	}
	/**
	 * True to sort leaf nodes under non leaf nodes
	 * @return boolean
	 */
	public function getFolderSort() {
		return $this->getExtConfigProperty("folderSort");
	}

	// leafAttr
	/**
	 * The attribute used to determine leaf nodes in folder sort (defaults to "leaf")
	 * @param string $value
	 * @return PhpExt_Tree_TreeSorter
	 */
	public function setLeafAttr($value) {
		$this->setExtConfigProperty("leafAttr", $value);
		return $this;
	}
	/**
	 * The attribute used to determine leaf nodes in folder sort (defaults to "leaf")
	 * @return string
	 */
	public function getLeafAttr() {
		return $this->getExtConfigProperty("leafAttr");
	}

	// property
	/**
	 * The named attribute on the node to sort by (defaults to text)
	 * @param string $value
	 * @return PhpExt_Tree_TreeSorter
	 */
	public function setProperty($value) {
		$this->setExtConfigProperty("property", $value);
		return $this;
	}
	/**
	 * The named attribute on the node to sort by (defaults to text)
	 * @return string
	 */
	public function getProperty() {
		return $this->getExtConfigProperty("property");
	}

	// sortType
	/**
	 * A custom "casting" function used to convert node values before sorting
	 * @param Function
	 * @return PhpExt_Tree_TreeSorter
	 */
	public function setProperty($value) {
		$this->setExtConfigProperty("sortType", $value);
		return $this;
	}
	/**
	 * A custom "casting" function used to convert node values before sorting
	 * @return Function
	 */
	public function getProperty() {
		return $this->getExtConfigProperty("sortType");
	}

	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.tree.TreeSorter", "treesorter");
		$validProps = array(
		      "caseSensitive",
		      "dir",
		      "folderSort",
		      "leafAttr",
		      "property",
		      "sortType"
		);
		$this->addValidConfigProperties($validProps);
	}
}



