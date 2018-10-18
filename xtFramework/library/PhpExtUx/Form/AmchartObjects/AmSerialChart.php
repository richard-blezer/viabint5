<?php

/**
 * @see AmChart
 */
include_once 'AmChart.php';

class AmSerialChart extends AmRectangularChart {
	
	/**
	 * Set category field
	 * @param string $value
	 */
	public function setCategoryField($value) {
		$this->setExtConfigProperty('categoryField', $value);
		return $this;
	}
	
	/**
	 * Get category field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCategoryField() {
		return $this->getExtConfigProperty('categoryField');
	}
	
	/**
	 * Set category axis
	 * @param PhpExt_Object $value
	 * @return PhpExt_Amchart
	 */
	public function setCategoryAxis(PhpExt_Object $value) {
		$this->setExtConfigProperty('categoryAxis', $value);
		return $this;
	}
	
	/**
	 * Get category Axis
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCategoryAxis() {
		return $this->getExtConfigProperty('categoryAxis');
	}
	
	public function setDataDateFormat($value) {
		$this->setExtConfigProperty('dataDateFormat', $value);
		return $this;
	}
	
	public function getDataDateFormat() {
		return $this->getExtConfigProperty('dataDateFormat');
	}
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","amchart");
	
		$validProps = array(
			'categoryAxis',
			'categoryField',
			'dataDateFormat',
			
		);
		$this->addValidConfigProperties($validProps);
		$this->setAmChartType(AmChart::CHART_TYPE_SERIAL);
	}
	
}