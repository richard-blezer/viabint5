<?php

/**
 * AmChartLegend class
 * @author radoslav
 *
 */
class AmChartConfigObject extends AmChartInvokable {
	
	public function setClassConfigName($value) {
		$this->setExtConfigProperty('configClassName', $value);
		return $this;
	}
	
	public function getClassConfigName() {
		return $this->getExtConfigProperty('configClassName');
	}
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","Ext.ux.AmchartConfigObject");
	
		$validProps = array(
			'configClassName',
		);
		$this->addValidConfigProperties($validProps);
	}
	
	public static function isConfigObject($value) {
		if (is_object($value)) {
			return ($value instanceof AmChartConfigObject);
		}
		return false;
	}
	
}