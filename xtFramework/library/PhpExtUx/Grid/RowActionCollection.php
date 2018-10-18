<?php
/**
 * PHP-Ext Library
 * http://php-ext.googlecode.com
 * @author Mario Zanier (mzanier[at]xt-commerce[dot]com)
 * @website http://www.xt-commerce.com
 * @created 2008-06-07
 * @version 0.1
 * @copyright 2008 Mario Zanier
 * @license http://www.gnu.org/licenses/lgpl.html
 * @link http://php-ext.googlecode.com
 *
 * Reference for Ext JS: http://extjs.com
 *
 */

include_once 'PhpExt/AbstractCollection.php';


class PhpExtUx_Grid_RowActionCollection extends PhpExt_AbstractCollection

{

	public function __construct($collection = array()) {
		parent::__construct($collection);
	}

	public function add(PhpExt_Grid_RowActionObject $object, $name = null) {
		return $this->addObject($object, $name);
	}


	public function getByName($name) {
		return $this->getObjectByName($name);
	}

	public function &getByIndex($index) {
		return $this->getObjectByIndex($index);
	}

}
?>