<?php

/**
 * AmPieChart class
 * @author radoslav
 * @see http://docs.amcharts.com/3/javascriptcharts/AmPieChart
 */
class AmPieChart extends AmSlicedChart {
	
	/**
	 * Balloon text. The following tags can be used: [[value]], [[title]], [[percents]], [[description]].
	 * Default: [[title]]: [[percents]]% ([[value]])\n[[description]]
	 * @param string $value
	 * @return AmPieChart
	 */
	public function setBalloonText($value) {
		$this->setExtConfigProperty('balloonText', $value);
		return $this;	
	}
	
	/**
	 * Get baloon text
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBalloonText() {
		return $this->getExtConfigProperty('balloonText');
	}
	
	/**
	 * Depth of the pie (for 3D effect).
	 * @param String $value
	 * @return AmPieChart
	 */
	public function setDepth3D($value) {
		$this->setExtConfigProperty('depth3D', $value);
		return $this;
	}
	
	/**
	 * Get depth
	 * @return string
	 */
	public function getDepth3D() {
		return $this->getExtConfigProperty('depth3D');
	}
	
	/**
	 * Pie lean angle (for 3D effect). Valid range is 0 - 90.
	 * @param string $value
	 * @return AmPieChart
	 */
	public function setAngle($value) {
		$this->setExtConfigProperty('angle', $value);
		return $this;
	}
	
	/**
	 * Get angle
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getAngle() {
		return $this->getExtConfigProperty('angle');
	}
	
	/**
	 * Sets a legend for this chart
	 * @param AmChartLegend $value
	 * @return AmChartLegend
	 */
	public function setLegend(AmChartLegend $value) {
		$this->setExtConfigProperty('legend', $value);
		return $this;
	}
	
	public function getLegend() {
		return $this->getExtConfigProperty('legend');
	}
	
	/**
	 * Height of a chart. "100%" means the chart's height will be equal to it's container's (DIV) height and will resize if height of the container changes. 
	 * Set a number instead of percents if your chart's size needs to be fixed.
	 * @param number|string $value
	 * @return AmPieChart
	 */
	public function setHeight($value) {
		$this->setExtConfigProperty('height', $value);
		return $this;
	}
	
	/**
	 * Get height
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getHeight() {
		return $this->getExtConfigProperty('height');
	}
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","amchart");
	
		$validProps = array(
			'angle',
			'balloonText',
			'depth3D',
			'height',
			'legend',
		);
		$this->addValidConfigProperties($validProps);
		$this->setAmChartType(AmChart::CHART_TYPE_PIE);
	}
	
}