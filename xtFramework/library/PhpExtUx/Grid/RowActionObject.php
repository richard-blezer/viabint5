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
 * @subpackage Grid
 */
class PhpExt_Grid_RowActionObject extends PhpExt_Config_ConfigObject
{


	public function seticonCls($value) {
		$this->setExtConfigProperty("iconCls", $value);
		return $this;
	}

	public function geticonCls() {
		return $this->getExtConfigProperty("iconCls");
	}

	public function seticonIndex($value) {
		$this->setExtConfigProperty("iconIndex", $value);
		return $this;
	}

	public function geticonIndex() {
		return $this->getExtConfigProperty("iconIndex");
	}

	public function setqtipIndex($value) {
		$this->setExtConfigProperty("qtipIndex", $value);
		return $this;
	}

	public function getqtipIndex() {
		return $this->getExtConfigProperty("qtipIndex");
	}

	public function settooltip($value) {
		$this->setExtConfigProperty("tooltip", $value);
		return $this;
	}

	public function gettooltip() {
		return $this->getExtConfigProperty("tooltip");
	}


	public function __construct($header) {
		parent::__construct();

		$validProps = array(
		    "align",
		    "iconIndex","qtipIndex","iconCls","tooltip"
		    );
		    $this->addValidConfigProperties($validProps);

		//    $this->setHeader($header);
	}


	public static function createAction($iconCls, $iconIndex = null, $qtipIndex = null, $tooltip = null) {
		$c = new PhpExt_Grid_RowActionObject($header);

		$c->seticonCls($iconCls);
		if ($iconIndex != null)
	//	$c->seticonIndex($iconIndex);
		if ($qtipIndex != null)
		$c->setqtipIndex($qtipIndex);
		if ($tooltip != null)
		$c->settooltip($tooltip);
			
		return $c;
	}
}

