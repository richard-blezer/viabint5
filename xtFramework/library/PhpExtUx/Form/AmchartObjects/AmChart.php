<?php

class AmChart extends AmChartInvokable {
	
	const CHART_TYPE_SERIAL = 'AmCharts.AmSerialChart';
	const CHART_TYPE_PIE = 'AmCharts.AmPieChart';
	
	public function setAmChartType($value) {
		$this->setExtConfigProperty('amChartType', $value);
		return $this;
	}
	
	public function getAmChartType() {
		return $this->getExtConfigProperty('amChartType');
	}
	
	/**
	 * Background color. You should set backgroundAlpha to >0 value in order background to be visible. We recommend setting background color directly on a chart's DIV instead of using this property.
	 * @param string $value
	 * @return AmChart
	 */
	public function setBackgroundColor($value) {
		$this->setExtConfigProperty('backgroundColor', $value);
		return $this;
	}
	
	/**
	 * Get background color
	 * @return string
	 */
	public function getBackgroundColor() {
		return $this->getExtConfigProperty('backgroundColor');
	}
	
	/**
	 * Opacity of chart's border. Value range is 0 - 1.
	 * @param string $value
	 * @return AmChart
	 */
	public function setBorderAlpha($value) {
		$this->setExtConfigProperty('borderAlpha', $value);
		return $this;
	}
	
	/**
	 * Get border alpha
	 * @return string
	 */
	public function getBorderAlpha() {
		return $this->getExtConfigProperty('borderAlpha');
	}
	
	/**
	 * Opacity of background. Set it to >0 value if you want backgroundColor to work. 
	 * However we recommend changing div's background-color style for changing background color.
	 * Default value : 0
	 * @param number $value
	 * @return AmChart
	 */
	public function setBackgroundAlpha($value) {
		$this->setExtConfigProperty('backgroundAlpha', $value);
		return $this;
	}
	
	/**
	 * Get background alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBackgroundAlpha() {
		return $this->getExtConfigProperty('backgroundAlpha');
	}
	
	/**
	 * Color of chart's border. You should set borderAlpha >0 in order border to be visible. 
	 * We recommend setting border color directly on a chart's DIV instead of using this property.
	 * Default value : #000000
	 * @param string $value
	 * @return AmChart
	 */
	public function setBorderColor($value) {
		$this->setExtConfigProperty('borderColor', $value);
		return $this;
	}
	
	/**
	 * Get border color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBorderColor() {
		return $this->getExtConfigProperty('borderColor');
	}
	
	/**
	 * Text color.
	 * Default value : #000000
	 * @param string $value
	 * @return AmChart
	 */
	public function setColor($value) {
		$this->setExtConfigProperty('color', $value);
		return $this;
	}
	
	/**
	 * Get color
	 * @return string
	 */
	public function getColor() {
		return $this->getExtConfigProperty('color');
	}
	
	/**
	 * Decimal separator.
	 * Default value : .
	 * @param string $value
	 * @return AmChart
	 */
	public function setDecimalSeparator($value) {
		$this->setExtConfigProperty('decimalSeparator', $value);
		return $this;
	}
	
	/**
	 * Get decimal separator
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getDecimalSeparator() {
		return $this->getExtConfigProperty('decimalSeparator');
	}
	
	/**
	 * Font family.
	 * Default value : Verdana
	 * @param string $value
	 * @return AmChart
	 */
	public function setFontFamily($value) {
		$this->setExtConfigProperty('fontFamily', $value);
		return $this;
	}
	
	/**
	 * Get font family
	 * @return string
	 */
	public function getFontFamily() {
		return $this->getExtConfigProperty('fontFamily');
	}
	
	/**
	 * Font size.
	 * Default value : 11
	 * @param number $value
	 * @return AmChart
	 */
	public function setFontSize($value) {
		$this->setExtConfigProperty('fontSize', $value);
		return $this;
	}
	
	/**
	 * Get font size
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getFontSize() {
		return $this->getExtConfigProperty('fontSize');
	}
	
	/**
	 * If you set this to true, the lines of the chart will be distorted and will produce hand-drawn effect. 
	 * Try to adjust chart.handDrawScatter and chart.handDrawThickness properties for a more scattered result.
	 * Default value : false
	 * @param boolean $value
	 * @return AmChart
	 */
	public function setHandDrawn($value) {
		$this->setExtConfigProperty('handDrawn', $value);
		return $this;
	}
	
	/**
	 * Get hand drawn
	 * @return
	 */
	public function getHandDrawn() {
		return $this->getExtConfigProperty('handDrawn');
	}
	
	/**
	 * Defines by how many pixels hand-drawn line (when handDrawn is set to true) will fluctuate.
	 * Defult value : 2
	 * @param number $value
	 * @return AmChart
	 */
	public function setHandDrawnScatter($value) {
		$this->setExtConfigProperty('handDrawnScatter', $value);
		return $this;
	}
	
	/**
	 * Get hand drawn scatter
	 */
	public function getHandDrawnScatter() {
		return $this->getExtConfigProperty('handDrawnScatter');
	}
	
	/**
	 * Defines by how many pixels line thickness will fluctuate (when handDrawn is set to true).
	 * Default value : 1
	 * @param number $value
	 * @return AmChart
	 */
	public function setHandDrawThickness($value) {
		$this->setExtConfigProperty('handDrawThickness', $value);
		return $this;
	}
	
	/**
	 * Get hand drawn thickness
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getHandDrawThickness() {
		return $this->getExtConfigProperty('handDrawThickness');
	}
	
	/**
	 * Time, in milliseconds after which balloon is hidden if the user rolls-out of the object. 
	 * Might be useful for AmMap to avoid balloon flickering while moving mouse over the areas. 
	 * Note, this is not duration of fade-out. Duration of fade-out is set in AmBalloon class.
	 * Default value : 150
	 * @param number $value
	 * @return AmChart
	 */
	public function setHideBalloonTime($value) {
		$this->setExtConfigProperty('hideBalloonTime', $value);
		return $this;
	}
	
	/**
	 * Get hide baloon time
	 * @return number
	 */
	public function getHideBalloonTime() {
		return $this->getExtConfigProperty('hideBalloonTime');
	}
	
	/**
	 * Allows changing language easily. 
	 * Note, you should include language js file from amcharts/lang or ammap/lang folder and then use variable name used in this file, like chart.language = "de"; 
	 * Note, for maps this works differently - you use language only for country names, as there are no other strings in the maps application.
	 * @param string $value
	 * @return AmChart
	 */
	public function setLanguage($value) {
		$this->setExtConfigProperty('language', $value);
		return $this;
	}
	
	/**
	 * Get language
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLanguage() {
		return $this->getExtConfigProperty('language');
	}
	
	/**
	 * Specifies path to the folder where images like resize grips, lens and similar are.
	 * @param string $value
	 * @return AmChart
	 */
	public function setPathToImages($value) {
		$this->setExtConfigProperty('pathToImages', $value);
		return $this;
	}
	
	/**
	 * Get path to images
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPathToImages() {
		return $this->getExtConfigProperty('pathToImages');
	}
	
	/**
	 * Precision of values. -1 means values won't be rounded at all and show as they are.
	 * Default value : -1
	 * @param number $value
	 * @return AmChart
	 */
	public function setPrecision($value) {
		$this->setExtConfigProperty('precision', $value);
		return $this;
	}
	
	/**
	 * Get precision
	 * @return number
	 */
	public function getPrecision() {
		return $this->getExtConfigProperty('precision');
	}
	
	/**
	 * Prefixes which are used to make big numbers shorter: 2M instead of 2000000, etc. 
	 * Prefixes are used on value axes and in the legend. To enable prefixes, set usePrefixes property to true.
	 * [{number:1e+3,prefix:"k"},{number:1e+6,prefix:"M"},{number:1e+9,prefix:"G"},{number:1e+12,prefix:"T"},{number:1e+15,prefix:"P"},{number:1e+18,prefix:"E"},{number:1e+21,prefix:"Z"},{number:1e+24,prefix:"Y"}]
	 * @param PhpExt_ObjectCollection $value
	 * @return AmChart
	 */
	public function setPrefixesOfBigNumbers(PhpExt_ObjectCollection $value) {
		$this->setExtConfigProperty('prefixesOfBigNumbers', $value);
		return $this;
	}
	
	/**
	 * Get Prefixes Of Big Numbers
	 * @return PhpExt_ObjectCollection
	 */
	public function getPrefixesOfBigNumbers() {
		return $this->getExtConfigProperty('prefixesOfBigNumbers');
	}
	
	/**
	 * Prefixes which are used to make small numbers shorter: 2μ instead of 0.000002, etc. 
	 * Prefixes are used on value axes and in the legend. To enable prefixes, set usePrefixes property to true.
	 * [{number:1e-24, prefix:"y"},{number:1e-21, prefix:"z"},{number:1e-18, prefix:"a"},{number:1e-15, prefix:"f"},{number:1e-12, prefix:"p"},{number:1e-9, prefix:"n"},{number:1e-6, prefix:"μ"},{number:1e-3, prefix:"m"}]
	 * @param PhpExt_ObjectCollection $value
	 * @return AmChart
	 */
	public function setPrefixesOfSmallNumbers(PhpExt_ObjectCollection $value) {
		$this->setExtConfigProperty('prefixesOfSmallNumbers', $value);
		return $this;
	}
	
	/**
	 * Get Prefixes Of Small Numbers
	 * @return PhpExt_ObjectCollection
	 */
	public function getPrefixesOfSmallNumbers() {
		return $this->getExtConfigProperty('prefixesOfSmallNumbers');
	}
	
	/**
	 * Theme of a chart. Config files of themes can be found in amcharts/themes/ folder. More info about using themes.
	 * Default value : none
	 * @param string $value
	 * @return AmChart
	 */
	public function setTheme($value) {
		$this->setExtConfigProperty('theme', $value);
		return $this;
	}
	
	/**
	 * Get theme
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getTheme() {
		return $this->getExtConfigProperty('theme');
	}
	
	/**
	 * Thousands separator.
	 * Default value : .
	 * @param string $value
	 * @return AmChart
	 */
	public function setThousandsSeparator($value) {
		$this->setExtConfigProperty('thousandsSeparator', $value);
		return $this;
	}
	
	/**
	 * Get Thousands Separator
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getThousandsSeparator() {
		return $this->getExtConfigProperty('thousandsSeparator');
	}
	
	/**
	 * Type of a chart. Required when creating chart using JSON. Possible types are: serial, pie, xy, radar, funnel, gauge, map, stock.
	 * @param string $value
	 * @return AmChart
	 */
	public function setType($value) {
		$this->setExtConfigProperty('type', $value);
		return $this;
	}
	
	/**
	 * Get Type
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getType() {
		return $this->getExtConfigProperty('type');
	}
	
	/**
	 * If true, prefixes will be used for big and small numbers. You can set arrays of prefixes via prefixesOfSmallNumbers and prefixesOfBigNumbers properties.
	 * Default value : false
	 * @param boolean $value
	 * @return AmChart
	 */
	public function setUsePrefixes($value) {
		$this->setExtConfigProperty('usePrefixes', $value);
		return $this;
	}
	
	/**
	 * Get use preffices
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getUsePrefixes() {
		return $this->getExtConfigProperty('usePrefixes');
	}
	
	/*--------------------------- METHODS ------------------------*/
	public function addLegend($value) {
		$this->addMethodCall('addLegend', $value);
		return $this;
	}
	/*--------------------------- END METHODS ------------------------*/
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","amchart");
	
		$validProps = array(
			'amChartType',
			'backgroundAlpha',
			'backgroundColor',
			// TODO balloon
			'borderAlpha',
			'borderColor',
			'color',
			'decimalSeparator',
			'fontFamily',
			'fontSize',
			'handDrawn',
			'handDrawnScatter',
			'handDrawThickness',
			'hideBalloonTime',
			'language',
			'pathToImages',
			'precision',
			'prefixesOfBigNumbers',
			'prefixesOfSmallNumbers',
			'theme',
			'thousandsSeparator',
			'type',
			'usePrefixes',
		);
		$this->addValidConfigProperties($validProps);
	}
	
}