<?php

class AmChartScrollbar extends AmChartConfigObject {

	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","AmchartConfigObject");

		$validProps = array(
		);
		$this->addValidConfigProperties($validProps);
		$this->setExtConfigProperty('configClassName', 'AmCharts.ChartScrollbar');
	}

}