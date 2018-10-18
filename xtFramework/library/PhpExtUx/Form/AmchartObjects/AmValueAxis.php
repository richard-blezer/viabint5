<?php

/**
 * AmValueAxisClass
 * @link http://docs.amcharts.com/3/javascriptcharts/ValueAxis
 * @author radoslav
 *
 */
class AmValueAxis extends AmAxisBase {
	
	/**
	 * Radar chart only. Specifies distance from axis to the axis title (category)
	 * Default value : 10
	 * @param number $value
	 * @return AmValueAxis
	 */
	public function setAxisTitleOffset($value) {
		$this->setExtConfigProperty('axisTitleOffset', $value);
		return $this;
	}
	
	/**
	 * Get axis title offset
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getAxisTitleOffset() {
		return $this->getExtConfigProperty('axisTitleOffset');
	}
	
	/**
	 * Specifies base value of the axis.
	 * Default value : 0
	 * @param number $value
	 * @return AmValueAxis
	 */
	public function setBaseValue($value) {
		$this->setExtConfigProperty('baseValue', $value);
		return $this;
	}
	
	/**
	 * Get base value
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBaseValue() {
		return $this->getExtConfigProperty('baseValue');
	}
	
	/**
	 * If your values represents time units, and you want value axis labels to be formatted as duration, you have to set the duration unit. Possible values are: "ss", "mm", "hh" and "DD".
	 * @var string $value
	 * @return AmValueAxis
	 */
	public function setDuration($value) {
		$this->setExtConfigProperty('duration', $value);
		return $this;
	}
	
	/**
	 * Get duration
	 * @return string
	 */
	public function getDuration() {
		return $this->getExtConfigProperty('duration');
	}
	
	/**
	 * If duration property is set, you can specify what string should be displayed next to day, hour, minute and second.
	 * Default value : {DD:'d. ', hh:':', mm:':',ss:''}
	 * @param object $value
	 * @return AmValueAxis
	 */
	public function setDurationUnits($value) {
		$this->setExtConfigProperty('durationUnits', $value);
		return $this;
	}
	
	/**
	 * Get duration units
	 * @return string
	 */
	public function getDurationUnits() {
		return $this->getExtConfigProperty('durationUnits');
	}
	
	/**
	 * Radar chart only. Possible values are: "polygons" and "circles". Set "circles" for polar charts.
	 * Default value : polygons
	 * @param string $value
	 * @return AmValueAxis
	 */
	public function setGridType($value) {
		$this->setExtConfigProperty('gridType', $value);
		return $this;
	}
	
	/**
	 * Get grid type
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getGridType() {
		return $this->getExtConfigProperty('gridType');
	}
	
	/**
	 * Unique id of value axis. It is not required to set it, unless you need to tell the graph which exact value axis it should use.
	 * @param string $value
	 * @return AmValueAxis
	 */
	public function setId($value) {
		$this->setExtConfigProperty('id', $value);
		return $this;
	}
	
	/**
	 * Get id
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getId() {
		return $this->getExtConfigProperty('id');
	}
	
	/**
	 * Specifies whether values on axis can only be integers or both integers and doubles.
	 * Default value : false
	 * @param boolean $value
	 * @return AmValueAxis
	 */
	public function setIntegersOnly($value) {
		$this->setExtConfigProperty('integersOnly', $value);
		return $this;
	}
	
	/**
	 * Get integers only
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getIntegersOnly() {
		return $this->getExtConfigProperty('integersOnly');
	}
	
	/**
	 * Specifies if this value axis' scale should be logarithmic.
	 * Default value : false
	 * @param boolean $value
	 * @return AmValueAxis
	 */
	public function setLogarithmic($value) {
		$this->setExtConfigProperty('logarithmic', $value);
		return $this;
	}
	
	/**
	 * Get logaritmic
	 * @return boolean
	 */
	public function getLogarithmic() {
		return $this->getExtConfigProperty('logarithmic');
	}
	
	/**
	 * If you don't want max value to be calculated by the chart, set it using this property. 
	 * This value might still be adjusted so that it would be possible to draw grid at rounded intervals.
	 * @param number $value
	 * @return AmValueAxis
	 */
	public function setMaximum($value) {
		$this->setExtConfigProperty('maximum', $value);
		return $this;
	}
	
	/**
	 * Get maximum
	 * @return number
	 */
	public function getMaximum() {
		return $this->getExtConfigProperty('maximum');
	}
	
	/**
	 * If you don't want min value to be calculated by the chart, set it using this property. 
	 * This value might still be adjusted so that it would be possible to draw grid at rounded intervals.
	 * @param number $value
	 * @return AmValueAxis
	 */
	public function setMinimum($value) {
		$this->setExtConfigProperty('minimum', $value);
		return $this;
	}
	
	/**
	 * Get minimum
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMinimum() {
		return $this->getExtConfigProperty('minimum');
	}
	
	/**
	 * If set value axis scale (min and max numbers) will be multiplied by it. I.e. if set to 1.2 the scope of values will increase by 20%.
	 * Default value : 1
	 * @param number $value
	 * @return AmValueAxis
	 */
	public function setMinMaxMultiplier($value) {
		$this->setExtConfigProperty('minMaxMultiplier', $value);
		return $this;
	}
	
	/**
	 * Get min max multiplier
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMinMaxMultiplier() {
		return $this->getExtConfigProperty('minMaxMultiplier');
	}
	
	/**
	 * Possible values are: "top", "bottom", "left", "right". If axis is vertical, default position is "left". 
	 * If axis is horizontal, default position is "bottom".(non-PHPdoc)
	 * Default value : left
	 * @var string $valuye
	 * @return AmValueAxis
	 */
	public function setPosition($value) {
		$this->setExtConfigProperty('position', $value);
		return $this;
	}
	
	/**
	 * Get position
	 * @return string
	 */
	public function getPosition() {
		return $this->getExtConfigProperty('position');
	}
	
	/**
	 * Precision (number of decimals) of values.
	 * @param number $value
	 * @return AmValueAxis
	 */
	public function setPrecision($value) {
		$this->setExtConfigProperty('precision', $value);
		return $this;
	}
	
	/**
	 * Get precision
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPrecision() {
		return $this->getExtConfigProperty('precision');
	}
	
	/**
	 * Specifies if graphs's values should be recalculated to percents.
	 * Default value : false
	 * @param boolean $value
	 * @return AmValueAxis
	 */
	public function setRecalculateToPercents($value) {
		$this->setExtConfigProperty('recalculateToPercents', $value);
		return $this;
	}
	
	/**
	 * Get recalculate to percents
	 * @return boolean
	 */
	public function getRecalculateToPercents() {
		return $this->getExtConfigProperty('recalculateToPercents');
	}
	
	/**
	 * Specifies if value axis should be reversed (smaller values on top).
	 * Default value : false
	 * @param boolean $value
	 * @return AmValueAxis
	 */
	public function setReversed($value) {
		$this->setExtConfigProperty('reversed', $value);
		return $this;
	}
	
	/**
	 * Get reversed
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getReversed() {
		return $this->getExtConfigProperty('reversed');
	}
	
	/**
	 * Stacking mode of the axis. Possible values are: "none", "regular", "100%", "3d". 
	 * Note, only graphs of one type will be stacked.
	 * Default value : none
	 * @param string $value
	 * @return AmValueAxis
	 */
	public function setStackType($value) {
		$this->setExtConfigProperty('stackType', $value);
		return $this;
	}
	
	/**
	 * Get stack type
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getStackType() {
		return $this->getExtConfigProperty('stackType');
	}
	
	/**
	 * If this value axis is stacked and has columns, setting valueAxis.totalText = "[[total]]" will make it to display total value above the most-top column.
	 * @param string $value
	 * @return AmValueAxis
	 */
	public function setTotalText($value) {
		$this->setExtConfigProperty('totalText', $value);
		return $this;
	}
	
	/**
	 * Get total text
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getTotalText() {
		return $this->getExtConfigProperty('totalText');
	}
	
	/**
	 * Color of total text.
	 * @param string $value
	 * @return AmValueAxis
	 */
	public function setTotalTextColor($value) {
		$this->setExtConfigProperty('totalTextColor', $value);
		return $this;
	}
	
	/**
	 * Get total text color
	 * @return string
	 */
	public function getTotalTextColor() {
		return $this->getExtConfigProperty('totalTextColor');
	}
	
	/**
	 * Unit which will be added to the value label.
	 * @param unknown $value
	 * @return AmValueAxis
	 */
	public function setUnit($value) {
		$this->setExtConfigProperty('unit', $value);
		return $this;
	}
	
	/**
	 * Get unit
	 * @return string
	 */
	public function getUnit() {
		return $this->getExtConfigProperty('unit');
	}

	/**
	 * Position of the unit. Possible values are "left" and "right".
	 * Default value : right
	 * @param string $value
	 * @return AmValueAxis
	 */
	public function setUnitPosition($value) {
		$this->setExtConfigProperty('unitPosition', $value);
		return $this;
	}
	
	/**
	 * Get unit position
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getUnitPosition() {
		return $this->getExtConfigProperty('unitPosition');
	}
	

	/**
	 * If true, prefixes will be used for big and small numbers.
	 * You can set arrays of prefixes directly to the chart object via prefixesOfSmallNumbers and prefixesOfBigNumbers.
	 * Default value : false
	 * @var boolean $value
	 * @return AmValueAxis
	 */
	public function setUsePrefixes($value) {
		$this->setExtConfigProperty('usePrefixes', $value);
		return $this;
	}
	
	/**
	 * Get use prefixes
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getUsePrefixes() {
		return $this->getExtConfigProperty('usePrefixes');
	}
	
	/**
	 * If true, values will always be formatted using scientific notation (5e+8, 5e-8...) 
	 * Otherwise only values bigger then 1e+21 and smaller then 1e-7 will be displayed in scientific notation.
	 * Default value : false
	 * @param boolean $value
	 * @return AmValueAxis
	 */
	public function setUseScientificNotation($value) {
		$this->setExtConfigProperty('useScientificNotation', $value);
		return $this;
	}
	
	/**
	 * Get use scientific notation
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getUseScientificNotation() {
		return $this->getExtConfigProperty('useScientificNotation');
	}
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","AmchartConfigObject");
	
		$validProps = array(
			'axisTitleOffset',
			'baseValue',
			'duration',
			'durationUnits',
			'gridType',
			'id',
			'integersOnly',
			'logarithmic',
			'maximum',
			'minimum',
			'minMaxMultiplier',
			'position',
			'precision',
			'recalculateToPercents',
			'reversed',
			'stackType',
			'totalText',
			'totalTextColor',
			'unit',
			'unitPosition',
			'usePrefixes',
			'useScientificNotation',
		);
		$this->addValidConfigProperties($validProps);
		$this->setExtConfigProperty('configClassName', 'AmCharts.ValueAxis');
	}
}