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
 * @package PhpExtUx_LabelEditor
 *  */
class PhpExtUx_LabelEditor extends PhpExt_DataView
{

	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.DataView.LabelEditor",null);

		$validProps = array(
		);
		$this->addValidConfigProperties($validProps);
	}

	public function getJavascript($lazy = false, $varName = null) {
		return parent::getJavascript(false, $varName);
	}



}






?>