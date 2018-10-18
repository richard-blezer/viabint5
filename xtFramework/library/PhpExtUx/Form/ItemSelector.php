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
 * @see PhpExt_Form_Field
 */
include_once 'PhpExt/Form/Field.php';

/**
 *
 * @package PhpExt
 * @subpackage Form
 */
class PhpExtUx_Form_ItemSelector extends PhpExt_Form_ComboBox
{
	// configs
	/**
	 * @cfg string (defaults to 200)
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setMsWidth($value) {
		$this->setExtConfigProperty("msWidth", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to 200)
	 * @return $string
	 */
	public function getMsWidth() {
		return $this->getExtConfigProperty("msWidth");
	}

	/**
	 * @cfg string (defaults to 300)
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setMsHeight($value) {
		$this->setExtConfigProperty("msHeight", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to 300)
	 * @return $string
	 */
	public function getMsHeight() {
		return $this->getExtConfigProperty("msHeight");
	}


	/**
	 * @cfg Boolean (defaults to false)
	 * @param Boolean
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setHideNavIcons($value) {
		$this->setExtConfigProperty("hideNavIcons", $value);
		return $this;
	}
	/**
	 * @cfg Boolean (defaults to false)
	 * @return Boolean
	 */
	public function getHideNavIcons() {
		return $this->getExtConfigProperty("hideNavIcons");
	}

	/**
	 * @cfg string (defaults to "")
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setImagePath($value) {
		$this->setExtConfigProperty("imagePath", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to "")
	 * @return $string
	 */
	public function getImagePath() {
		return $this->getExtConfigProperty("imagePath");
	}

	/**
	 * @cfg string (defaults to "up2.gif")
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setIconUp($value) {
		$this->setExtConfigProperty("iconUp", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to "up2.gif")
	 * @return $string
	 */
	public function getIconUp() {
		return $this->getExtConfigProperty("iconUp");
	}

	/**
	 * @cfg string (defaults to "down2.gif")
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setIconDown($value) {
		$this->setExtConfigProperty("iconDown", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to "down2.gif")
	 * @return $string
	 */
	public function getIconDown() {
		return $this->getExtConfigProperty("iconDown");
	}

	/**
	 * @cfg string (defaults to "left2.gif")
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setIconLeft($value) {
		$this->setExtConfigProperty("iconLeft", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to "left2.gif")
	 * @return $string
	 */
	public function getIconLeft() {
		return $this->getExtConfigProperty("iconLeft");
	}

	/**
	 * @cfg string (defaults to "right2.gif")
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setIconRight($value) {
		$this->setExtConfigProperty("iconRight", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to "right2.gif")
	 * @return $string
	 */
	public function getIconRight() {
		return $this->getExtConfigProperty("iconRight");
	}

	/**
	 * @cfg string (defaults to "top2.gif")
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setIconTop($value) {
		$this->setExtConfigProperty("iconTop", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to "top2.gif")
	 * @return $string
	 */
	public function getIconTop() {
		return $this->getExtConfigProperty("iconTop");
	}

	/**
	 * @cfg string (defaults to "bottom2.gif")
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setIconBottom($value) {
		$this->setExtConfigProperty("iconBottom", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to "bottom2.gif")
	 * @return $string
	 */
	public function getIconBottom() {
		return $this->getExtConfigProperty("iconBottom");
	}


	/**
	 * @cfg boolean (defaults to true)
	 * @param $boolean
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setDrawUpIcon($value) {
		$this->setExtConfigProperty("drawUpIcon", $value);
		return $this;
	}
	/**
	 * @cfg boolean (defaults to true)
	 * @return $boolean
	 */
	public function getDrawUpIcon() {
		return $this->getExtConfigProperty("drawUpIcon");
	}

	/**
	 * @cfg boolean (defaults to true)
	 * @param $boolean
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setDrawDownIcon($value) {
		$this->setExtConfigProperty("drawDownIcon", $value);
		return $this;
	}
	/**
	 * @cfg boolean (defaults to true)
	 * @return $boolean
	 */
	public function getDrawDownIcon() {
		return $this->getExtConfigProperty("drawDownIcon");
	}

	/**
	 * @cfg boolean (defaults to true)
	 * @param $boolean
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setDrawLeftIcon($value) {
		$this->setExtConfigProperty("drawLeftIcon", $value);
		return $this;
	}
	/**
	 * @cfg boolean (defaults to true)
	 * @return $boolean
	 */
	public function getDrawLeftIcon() {
		return $this->getExtConfigProperty("drawLeftIcon");
	}

	/**
	 * @cfg boolean (defaults to true)
	 * @param $boolean
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setDrawRightIcon($value) {
		$this->setExtConfigProperty("drawRightIcon", $value);
		return $this;
	}
	/**
	 * @cfg boolean (defaults to true)
	 * @return $boolean
	 */
	public function getDrawRightIcon() {
		return $this->getExtConfigProperty("drawRightIcon");
	}

	/**
	 * @cfg boolean (defaults to true)
	 * @param $boolean
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setDrawTopIcon($value) {
		$this->setExtConfigProperty("drawTopIcon", $value);
		return $this;
	}
	/**
	 * @cfg boolean (defaults to true)
	 * @return $boolean
	 */
	public function getDrawTopIcon() {
		return $this->getExtConfigProperty("drawTopIcon");
	}

	/**
	 * @cfg boolean (defaults to true)
	 * @param $boolean
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setDrawButIcon($value) {
		$this->setExtConfigProperty("drawButIcon", $value);
		return $this;
	}
	/**
	 * @cfg boolean (defaults to true)
	 * @return $boolean
	 */
	public function getDrawButIcon() {
		return $this->getExtConfigProperty("drawButIcon");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setFromStore($value) {
		$this->setExtConfigProperty("fromStore", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to null)
	 * @return $string
	 */
	public function getFromStore() {
		return $this->getExtConfigProperty("fromStore");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setToStore($value) {
		$this->setExtConfigProperty("toStore", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to null)
	 * @return $string
	 */
	public function getToStore() {
		return $this->getExtConfigProperty("toStore");
	}

	/**
	 * @cfg boolean (defaults to false)
	 * @param $boolean
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setSwitchToFrom($value) {
		$this->setExtConfigProperty("switchToFrom", $value);
		return $this;
	}
	/**
	 * @cfg boolean (defaults to false)
	 * @return $boolean
	 */
	public function getSwitchToFrom() {
		return $this->getExtConfigProperty("switchToFrom");
	}

	/**
	 * @cfg boolean (defaults to false)
	 * @param $boolean
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setAllowDup($value) {
		$this->setExtConfigProperty("allowDup", $value);
		return $this;
	}
	/**
	 * @cfg boolean (defaults to false)
	 * @return $boolean
	 */
	public function getAllowDup() {
		return $this->getExtConfigProperty("allowDup");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setDelimiter($value) {
		$this->setExtConfigProperty("delimiter", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to null)
	 * @return $string
	 */
	public function getSelimiter() {
		return $this->getExtConfigProperty("delimiter");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setToLegend($value) {
		$this->setExtConfigProperty("toLegend", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to null)
	 * @return $string
	 */
	public function getToLegend() {
		return $this->getExtConfigProperty("toLegend");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setFromLegend($value) {
		$this->setExtConfigProperty("fromLegend", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to null)
	 * @return $string
	 */
	public function getFromLegend() {
		return $this->getExtConfigProperty("fromLegend");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setToSortField($value) {
		$this->setExtConfigProperty("toSortField", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to null)
	 * @return $string
	 */
	public function getToSortField() {
		return $this->getExtConfigProperty("toSortField");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setFromSortField($value) {
		$this->setExtConfigProperty("fromSortField", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to null)
	 * @return $string
	 */
	public function getFromSortField() {
		return $this->getExtConfigProperty("fromSortField");
	}

	/**
	 * @cfg string (defaults to 'ASC')
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setToSortDir($value) {
		$this->setExtConfigProperty("toSortDir", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to 'ASC')
	 * @return $string
	 */
	public function getToSortDir() {
		return $this->getExtConfigProperty("toSortDir");
	}

	/**
	 * @cfg string (defaults to 'ASC')
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setFromSortDir($value) {
		$this->setExtConfigProperty("fromSortDir", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to 'ASC')
	 * @return $string
	 */
	public function getFromSortDir() {
		return $this->getExtConfigProperty("fromSortDir");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setToTBar($value) {
		$this->setExtConfigProperty("toTBar", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to null)
	 * @return $string
	 */
	public function getToTBar() {
		return $this->getExtConfigProperty("toTBar");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_ItemSelector
	 */
	public function setFromTBar($value) {
		$this->setExtConfigProperty("fromTBar", $value);
		return $this;
	}
	/**
	 * @cfg string (defaults to null)
	 * @return $string
	 */
	public function getFromTBar() {
		return $this->getExtConfigProperty("fromTBar");
	}

    // fromData
    /**
 	* @var PhpExt_ToolConfigObjectCollection
 	*/
    protected $fromData;
    /**
     * A {@link PhpExt_ToolConfigObjectCollection} of tool buttons to be added to the header tool area.
     * Note that apart from the toggle tool which is provided when a panel is collapsible, these tools only provide the visual button. Any required functionality must be provided by adding handlers that implement the necessary behavior.
     * @return PhpExt_ToolConfigObjectCollection
     */
    public function getFromData() {
    	return $this->getExtConfigProperty("fromData");
    }

    /**
     * A {@link PhpExt_ToolConfigObject} to add to the tools collection
     * @param PhpExt_ToolConfigObject $tool
     * @param string $name Optional key to locate the tool on the collection
     * @return PhpExt_Panel
     */
    public function addFromData($data, $name = null) {
        $this->_fromData->add($data, $name);
        return $this;
    }

    // toData
    /**
 	* @var PhpExt_ToolConfigObjectCollection
 	*/
    protected $toData;
    /**
     * A {@link PhpExt_ToolConfigObjectCollection} of tool buttons to be added to the header tool area.
     * Note that apart from the toggle tool which is provided when a panel is collapsible, these tools only provide the visual button. Any required functionality must be provided by adding handlers that implement the necessary behavior.
     * @return PhpExt_ToolConfigObjectCollection
     */
    public function getToData() {
    	return $this->getExtConfigProperty("toData");
    }

    /**
     * A {@link PhpExt_ToolConfigObject} to add to the tools collection
     * @param PhpExt_ToolConfigObject $tool
     * @param string $name Optional key to locate the tool on the collection
     * @return PhpExt_Panel
     */
    public function addToData($data, $name = null) {
        $this->_toData->add($data, $name);
        return $this;
    }

    // dataFields
    /**
 	* @var PhpExt_ToolConfigObjectCollection
 	*/
    protected $dataFields;
    /**
     * A {@link PhpExt_ToolConfigObjectCollection} of tool buttons to be added to the header tool area.
     * Note that apart from the toggle tool which is provided when a panel is collapsible, these tools only provide the visual button. Any required functionality must be provided by adding handlers that implement the necessary behavior.
     * @return PhpExt_ToolConfigObjectCollection
     */
    public function getDataFields() {
    	return $this->getExtConfigProperty("dataFields");
    }

    /**
     * A {@link PhpExt_ToolConfigObject} to add to the tools collection
     * @param PhpExt_ToolConfigObject $tool
     * @param string $name Optional key to locate the tool on the collection
     * @return PhpExt_Panel
     */
    public function addDataFields($dataFields, $name = null) {
        $this->_dataFields->add($dataFields, $name);
        return $this;
    }

	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.ItemSelector","itemselector");
		$validProps = array(
            	"msWidth",
            	"msHeight",
            	"hideNavIcons",
            	"imagePath",
            	"iconUp",
            	"iconDown",
            	"iconLeft",
            	"iconRight",
            	"iconTop",
            	"iconBottom",
            	"drawUpIcon",
            	"drawDownIcon",
            	"drawLeftIcon",
            	"drawRightIcon",
            	"drawTopIcon",
            	"drawBotIcon",
            	"fromStore",
            	"toStore",
            	"fromData",
            	"toData",
		        "dataFields",
            	"switchToFrom",
            	"allowDup",
            	"delimiter",
            	"toLegend",
            	"fromLegend",
            	"toSortField",
            	"fromSortField",
            	"toSortDir",
            	"fromSortDir",
            	"toTBar",
            	"fromTBar"

		);

		$this->addValidConfigProperties($validProps);
		/*
		$this->_Toolbar = new PhpExt_Toolbar_Toolbar();
		$this->_extConfigProperties['tbar'] = $this->_Toolbar;

		$this->_dataFields = new PhpExt_ToolConfigObjectCollection();
		$this->_dataFields->setForceArray(true);
		$this->_extConfigProperties['dataFields'] = $this->_dataFields;

		$this->_fromData = new PhpExt_ToolConfigObjectCollection();
		$this->_fromData->setForceArray(true);
		$this->_extConfigProperties['fromData'] = $this->_fromData;

		$this->_toData = new PhpExt_ToolConfigObjectCollection();
		$this->_toData->setForceArray(true);
		$this->_extConfigProperties['toData'] = $this->_toData;
		*/
	}



}

