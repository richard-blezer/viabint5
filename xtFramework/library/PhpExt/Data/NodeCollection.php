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
 * @see PhpExt_AbstractCollection
 */
include_once 'PhpExt/AbstractCollection.php';
/**
 * @see PhpExt_Data_Node
 */
include_once 'PhpExt/Data/Node.php';


/**
 * Provides functionality to manage a PPhpExt_Data_Node Collection
 *
 * @package PhpExt
 * @subpackage Data
 */
class PhpExt_Data_NodeCollection extends PhpExt_AbstractCollection
{

	public function __construct($collection = array()) {
		parent::__construct($collection);
	}

	/**
	 * Adds a PhpExt_Data_Node to the Collection
	 *
	 * @param PhpExt_Data_Node $object
	 * @param string $name
	 * @return int the index of the new element
	 */
	public function add(PhpExt_Data_Node $object, $name = null) {
		return $this->addObject($object, $name);
	}

	/**
	 * Gets the Component with the key specified by $name
	 *
	 * @param string $name
	 * @return PhpExt_Data_Node
	 */
	public function getByName($name) {
		return $this->getObjectByName($name);
	}

	/**
	 * Gets the Component in the specified index
	 *
	 * @param int $index
	 * @return PhpExt_Data_Node
	 */
	public function &getByIndex($index) {
		return $this->getObjectByIndex($index);
	}

}


