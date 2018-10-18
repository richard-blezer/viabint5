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
 * @see PhpExt_Form_ComboBox
 */
include_once 'PhpExt/Form/ComboBox.php';
include_once 'PhpExt/Config/ConfigObject.php';
/**
 *
 * @package PhpExt
 * @subpackage Form
 */
class PhpExtUx_Form_MultiSelect extends  PhpExt_Form_ComboBox
{
	// configs
	/**
	 * @cfg Boolean (defaults to false)
	 * @param Boolean
	 * @return PhpExtUx_Form_Multiselect
	 */
	public function setCopy($value) {
		$this->setExtConfigProperty("copy", $value);
		return $this;
	}
	/**
	 * @cfg Boolean (defaults to false)
	 * @return Boolean
	 */
	public function getCopy() {
		return $this->getExtConfigProperty("copy");
	}

	/**
	 * @cfg Boolean (defaults to false)
	 * @param Boolean
	 * @return PhpExtUx_Form_Multiselect
	 */
	public function setAllowDup($value) {
		$this->setExtConfigProperty("allowDup", $value);
		return $this;
	}
	/**
	 * @cfg Boolean (defaults to false)
	 * @return Boolean
	 */
	public function getAllowDup() {
		return $this->getExtConfigProperty("allowDup");
	}

	/**
	 * @cfg Boolean (defaults to false)
	 * @param Boolean
	 * @return PhpExtUx_Form_Multiselect
	 */
	public function setAllowTrash($value) {
		$this->setExtConfigProperty("allowTrash", $value);
		return $this;
	}
	/**
	 * @cfg Boolean (defaults to false)
	 * @return Boolean
	 */
	public function getAllowTrash() {
		return $this->getExtConfigProperty("allowTrash");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_Multiselect
	 */
	public function setLegend($value) {
		$this->setExtConfigProperty("legend", $value);
		return $this;
	}
	/**
	 * @cfg strung (defaults to null)
	 * @return $string
	 */
	public function getLegend() {
		return $this->getExtConfigProperty("legend");
	}

	/**
	 * @cfg string (defaults to ',')
	 * @param $string
	 * @return PhpExtUx_Form_Multiselect
	 */
	public function setDelimiter($value) {
		$this->setExtConfigProperty("delimiter", $value);
		return $this;
	}
	/**
	 * @cfg strung (defaults to ',')
	 * @return $string
	 */
	public function getDelimiter() {
		return $this->getExtConfigProperty("delimiter");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_Multiselect
	 */
	public function setDragGroup($value) {
		$this->setExtConfigProperty("dragGroup", $value);
		return $this;
	}
	/**
	 * @cfg strung (defaults to null)
	 * @return $string
	 */
	public function getDragGroup() {
		return $this->getExtConfigProperty("dragGroup");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_Multiselect
	 */
	public function setDropGroup($value) {
		$this->setExtConfigProperty("dropGroup", $value);
		return $this;
	}
	/**
	 * @cfg strung (defaults to null)
	 * @return $string
	 */
	public function getDropGroup() {
		return $this->getExtConfigProperty("dropGroup");
	}

	// Toolbar
	/**
	 *
	 * @var PhpExt_Toolbar_Toolbar
	 */
	protected $_Toolbar = null;
    /**
     * The toolbar of Multiselect. This is a PhpExt_Toolbar_Toolbar object or any of its descendants.
     * @param PhpExt_Toolbar_Toolbar $value
     * @return PhpExt_Panel
     */
    public function setToolbar(PhpExt_Toolbar_Toolbar $value) {
		$this->_Toolbar = $value;
		$this->_extConfigProperties['tbar'] = $this->_Toolbar;
		return $this;
	}
	/**
	 * The toolbar of Multiselect. This is a PhpExt_Toolbar_Toolbar object or any of its descendants.
	 * @return PhpExt_Toolbar_Toolbar
	 */
	public function getToolbar() {
		return $this->_Toolbar;
	}

	/**
	 * @cfg Boolean (defaults to false)
	 * @param Boolean
	 * @return PhpExtUx_Form_Multiselect
	 */
	public function setAppendOnly($value) {
		$this->setExtConfigProperty("appendOnly", $value);
		return $this;
	}
	/**
	 * @cfg Boolean (defaults to false)
	 * @return Boolean
	 */
	public function getAppendOnly() {
		return $this->getExtConfigProperty("appendOnly");
	}

	/**
	 * @cfg string (defaults to null)
	 * @param $string
	 * @return PhpExtUx_Form_Multiselect
	 */
	public function setSortField($value) {
		$this->setExtConfigProperty("sortField", $value);
		return $this;
	}
	/**
	 * @cfg strung (defaults to null)
	 * @return $string
	 */
	public function getSortField() {
		return $this->getExtConfigProperty("sortField");
	}

	/**
	 * @cfg string (defaults to 'ASC')
	 * @param $string
	 * @return PhpExtUx_Form_Multiselect
	 */
	public function setSortDir($value) {
		$this->setExtConfigProperty("sortDir", $value);
		return $this;
	}
	/**
	 * @cfg strung (defaults to 'ASC')
	 * @return $string
	 */
	public function getSortDir() {
		return $this->getExtConfigProperty("sortDir");
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

    // data
    /**
 	* @var PhpExt_ToolConfigObjectCollection
 	*/
    protected $data;
    /**
     * A {@link PhpExt_ToolConfigObjectCollection} of tool buttons to be added to the header tool area.
     * Note that apart from the toggle tool which is provided when a panel is collapsible, these tools only provide the visual button. Any required functionality must be provided by adding handlers that implement the necessary behavior.
     * @return PhpExt_ToolConfigObjectCollection
     */
    public function getData() {
    	return $this->getExtConfigProperty("data");
    }

    /**
     * A {@link PhpExt_ToolConfigObject} to add to the tools collection
     * @param PhpExt_ToolConfigObject $tool
     * @param string $name Optional key to locate the tool on the collection
     * @return PhpExt_Panel
     */
    public function setData($value) {
//        $this->_data->add($data, $name);
		$this->setExtConfigProperty("data", $value);
		return $this;
    }
    /**
     * A {@link PhpExt_ToolConfigObjectCollection} of tool buttons to be added to the header tool area.
     * Note that apart from the toggle tool which is provided when a panel is collapsible, these tools only provide the visual button. Any required functionality must be provided by adding handlers that implement the necessary behavior.
     * @return PhpExt_ToolConfigObjectCollection
     */
 //   public function getDataFields() {
//    	return $this->getExtConfigProperty("dataFields");
//    }

    /**
     * A {@link PhpExt_ToolConfigObject} to add to the tools collection
     * @param PhpExt_ToolConfigObject $tool
     * @param string $name Optional key to locate the tool on the collection
     * @return PhpExt_Panel
     */
    public function setDataFields($value) {
//        $this->_data->add($data, $name);
		$this->setExtConfigProperty("dataFields", $value);
		return $this;
    }
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.Multiselect","multiselect");
		$validProps = array(
		       "dataFields",
		       "data",
	           "copy",
	           "allowDup",
	           "allowTrash",
	           "legend",
	           "delimiter",
	           "dragGroup",
	           "dropGroup",
	           "tbar",
	           "appendOnly",
	           "sortField",
	           "sortDir"
		);
		$this->addValidConfigProperties($validProps);

		$this->_Toolbar = new PhpExt_Toolbar_Toolbar();
		$this->_extConfigProperties['tbar'] = $this->_Toolbar;

/*
		$this->_dataFields = new PhpExt_Config_ConfigObject();
		$this->_dataFields->setForceArray(true);
		$this->_extConfigProperties['dataFields'] = $this->_dataFields;

		$this->_data = new PhpExt_ToolConfigObjectCollection();
		$this->_data->setForceArray(true);
		$this->_extConfigProperties['data'] = $this->_data;
*/

	}
    protected function getConfigParams($lazy = false) {
		if ($this->_Toolbar->getItems()->getCount() == 0 && !$this->_Toolbar->getMustRender())
		    $this->setExtConfigProperty("tbar", null);
/*
		if ($this->_dataFields->getCount() == 0)
		    $this->setExtConfigProperty("dataFields", null);

		if ($this->_data->getCount() == 0)
		    $this->setExtConfigProperty("data", null);
*/
		return parent::getConfigParams($lazy);
	}

}

