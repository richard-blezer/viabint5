<?php

/**
 * Class AmCoordinateChart
 * @see http://docs.amcharts.com/3/javascriptcharts/AmCoordinateChart
 * @author radoslav
 *
 */
class AmCoordinateChart extends AmChart {
	
	/**
	 * Specifies if grid should be drawn above the graphs or below. Will not work properly with 3D charts.
	 * Default value : false
	 * @param boolean $value
	 * @return AmCoordinateChart
	 */
	public function setGridAboveGraphs($value) {
		$this->setExtConfigProperty('gridAboveGraphs', $value);
		return $this;
	}
	
	/**
	 * Get grind above graphs
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getGridAboveGraphs() {
		return $this->getExtConfigProperty('gridAboveGraphs');
	}
	
	/**
	 * Specifies whether the animation should be sequenced or all objects should appear at once.
	 * Default value : true
	 * @param boolean $value
	 * @return AmCoordinateChart
	 */
	public function setSequencedAnimation($value) {
		$this->setExtConfigProperty('sequencedAnimation', $value);
		return $this;
	}
	
	/**
	 * Get sequence animanion
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getSequencedAnimation() {
		return $this->getExtConfigProperty('sequencedAnimation');
	}
	
	/**
	 * The initial opacity of the column/line. If you set startDuration to a value higher than 0, the columns/lines will fade in from startAlpha. Value range is 0 - 1.
	 * Default value : 1
	 * @param number $value
	 * @return AmCoordinateChart
	 */
	public function setStartAlpha($value) {
		$this->setExtConfigProperty('startAlpha', $value);
		return $this;
	}
	
	/**
	 * Get start alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getStartAlpha() {
		return $this->getExtConfigProperty('startAlpha');
	}
	
	/**
	 * Duration of the animation, in seconds.
	 * @param number $value
	 * @return AmSerialChart
	 */
	public function setStartDuration($value) {
		$this->setExtConfigProperty('startDuration', $value);
		return $this;
	}
	
	/**
	 * Get start duration
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getStartDuration() {
		return $this->getExtConfigProperty('startDuration');
	}
	
	/**
	 * Animation effect. Possible values are: easeOutSine, easeInSine, elastic, bounce
	 * Default value : elastic
	 * @param string $value
	 * @return AmCoordinateChart
	 */
	public function setStartEffect($value) {
		$this->setExtConfigProperty('startEffect', $value);
		return $this;
	}
	
	/**
	 * Get start effect
	 * @return string
	 */
	public function getStartEffect() {
		return $this->getExtConfigProperty('startEffect');
	}
	
	/**
	 * Target of url.
	 * Default value : _self
	 * @param string $value
	 * @return AmCoordinateChart
	 */
	public function setUrlTarget($value) {
		$this->setExtConfigProperty('urlTarget', $value);
		return $this;
	}
	
	/**
	 * Get url target
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getUrlTarget() {
		return $this->getExtConfigProperty('urlTarget');
	}
	
	/**
	 * The array of value axes. Chart creates one value axis automatically, so if you need only one value axis, you don't need to create it.
	 * @param PhpExt_ObjectCollection $value
	 * @return AmCoordinateChart
	 */
	public function setValueAxes(PhpExt_ObjectCollection $value) {
		$this->setExtConfigProperty('valueAxes', $value);
		return $this;
	}
	
	/**
	 * Get value axes
	 * @return PhpExt_ObjectCollection
	 */
	public function getValueAxes() {
		return $this->getExtConfigProperty('valueAxes');
	}
	
	/*-------------------------------- METHODS-------------------------------*/
	public function addGraph($value) {
		$this->addMethodCall('addGraph', $value);
		return $this;
	}
	
	public function addValueAxis($value) {
		$this->addMethodCall('addValueAxis', $value);
		return $this;
	}
	/*---------------------------------END METHODS --------------------------*/
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","amchart");
	
		$validProps = array(
			'gridAboveGraphs',
			'sequencedAnimation',
			'startAlpha',
			'startDuration',
			'startEffect',
			'urlTarget',
			'valueAxes',
		);
		$this->addValidConfigProperties($validProps);
	}
	
}