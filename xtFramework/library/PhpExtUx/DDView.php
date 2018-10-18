<?php
/**
 * PHP-Ext Library
 * http://php-ext.googlecode.com
 * @author Matthias Benkwitz <mb[at]bui-hinsche[dot]de>
 * @copyright 2008 Matthias Benkwitz
 * @license http://www.gnu.org/licenses/lgpl.html
 * @link http://php-ext.googlecode.com
 *
 * Reference for Ext JS: http://extjs.com
 *
 * @class Ext.ux.DDView
 * A DnD enabled version of Ext.View.
 * @param {Element/String} container The Element in which to create the View.
 * @param {String} tpl The template string used to create the markup for each element of the View
 * @param {Object} config The configuration properties. These include all the config options of
 * {@link Ext.View} plus some specific to this class.<br>
 * <p>
 * Drag/drop is implemented by adding {@link Ext.data.Record}s to the target DDView. If copying is
 * not being performed, the original {@link Ext.data.Record} is removed from the source DDView.<br>
 * <p>
 * The following extra CSS rules are needed to provide insertion point highlighting:<pre><code>
.x-view-drag-insert-above {
    border-top:1px dotted #3366cc;
}
.x-view-drag-insert-below {
    border-bottom:1px dotted #3366cc;
}
</code></pre>
 *
 */


/**
 * @see PhpExt_Form_TextField
 */
include_once 'PhpExt/PhpExt_DataView.php';

/**
 *
 * @package PhpExtUx
 * @subpackage Form
 */
class PhpExtUx_DDView extends PhpExt_PhpExt_DataView
{
	// cfg
	/**    @cfg {String/Array} dragGroup The ddgroup name(s) for the View's DragZone. */
    /**    @cfg {String/Array} dropGroup The ddgroup name(s) for the View's DropZone. */
    /**    @cfg {Boolean} copy Causes drag operations to copy nodes rather than move. */
    /**    @cfg {Boolean} allowCopy Causes ctrl/drag operations to copy nodes rather than move. */
	/**
	 * (defaults to 'ASC')
	 * @param $value
	 * @return PhpExtUx_DDView
	 */
	public function setSortDir($value) {
		$this->setExtConfigProperty("sortDir", $value);
		return $this;
	}
	/**
	 * (defaults to 'ASC')
	 * @return string
	 */
	public function getSortDir() {
		return $this->getExtConfigProperty("sortDir");
	}

	/**
	 * Boolean (defaults to true)
	 * @param $value
	 * @return PhpExtUx_DDView
	 */
	public function setIsFormField($value) {
		$this->setExtConfigProperty("isFormField", $value);
		return $this;
	}
	/**
	 * (defaults to true)
	 * @return $boolean
	 */
	public function getIsFormField() {
		return $this->getExtConfigProperty("isFormField");
	}

	/**
	 * Boolean (defaults to true)
	 * @param $value
	 * @return PhpExtUx_DDView
	 */
	public function setMsgTarget($value) {
		$this->setExtConfigProperty("msgTarget", $value);
		return $this;
	}
	/**
	 * (defaults to true)
	 * @return $boolean
	 */
	public function getMsgTarget() {
		return $this->getExtConfigProperty("msgTarget");
	}



	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.DDView","ddview");
		$validProps = array(
		    "sortDir",

		);
		$this->addValidConfigProperties($validProps);

	}


}

