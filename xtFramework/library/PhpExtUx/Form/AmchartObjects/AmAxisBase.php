<?php

/**
 * Class AmAxisBase
 * @link http://docs.amcharts.com/3/javascriptcharts/AxisBase
 * @author radoslav
 *
 */
class AmAxisBase extends AmChartConfigObject {
	
	/**
	 * Specifies whether number of gridCount is specified automatically, acoarding to the axis size.
	 * Default value : true
	 * @param boolean $value
	 * @return AmAxisBase
	 */
	public function setAutoGridCount($value) {
		$this->setExtConfigProperty('autoGridCount', $value);
		return $this;
	}
	
	/**
	 * Get auto grid count
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getAutoGridCount() {
		return $this->getExtConfigProperty('autoGridCount');
	}
	
	/**
	 * Axis opacity. Value range is 0 - 1.
	 * Default value : 1
	 * @param number $value
	 * @return AmValueAxis
	 */
	public function setAxisAlpha($value) {
		$this->setExtConfigProperty('axisAlpha', $value);
		return $this;
	}
	
	/**
	 * Get axis alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getAxisAlpha() {
		return $this->getExtConfigProperty('axisAlpha');
	}
	
	/**
	 * Axis color.
	 * Default value : #000000
	 * @param string $value
	 * @return AmValueAxis
	 */
	public function setAxisColor($value) {
		$this->setExtConfigProperty('axisColor', $value);
		return $this;
	}
	
	/**
	 * Get axis color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getAxisColor() {
		return $this->getExtConfigProperty('axisColor');
	}
	
	/**
	 * Thickness of the axis.
	 * Default value : 1
	 * @param number $value
	 * @return AmValueAxis
	 */
	public function setAxisThickness($value) {
		$this->setExtConfigProperty('axisThickness', $value);
		return $this;
	}
	
	/**
	 * Get axis thickness
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getAxisThickness() {
		return $this->getExtConfigProperty('axisThickness');
	}
	
	/**
	 * Color of axis value labels. Will use chart's color if not set.
	 * @param string $value
	 * @return AmValueAxis
	 */
	public function setColor($value) {
		$this->setExtConfigProperty('color', $value);
		return $this;
	}
	
	/**
	 * Get color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getColor() {
		return $this->getExtConfigProperty('color');
	}
	
	/**
	 * Length of a dash. 0 means line is not dashed.
	 * Default value : 0
	 * @param number $value
	 * @return AmValueAxis
	 */
	public function setDashLength($value) {
		$this->setExtConfigProperty('dashLength', $value);
		return $this;
	}
	
	/**
	 * Get dash length
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getDashLength() {
		return $this->getExtConfigProperty('dashLength');
	}
	
	/**
	 * If your values represents time units, and you want value axis labels to be formatted as duration, you have to set the duration unit.
	 * Possible values are: "ss", "mm", "hh" and "DD".
	 * @param string $value
	 * @return AmValueAxis
	 */
	public function setDuration($value) {
		$this->setExtConfigProperty('duration', $value);
		return $this;
	}
	
	/**
	 * Get duration
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getDuration() {
		return $this->getExtConfigProperty('duration');
	}
	
	/**
	 * Fill opacity. Every second space between grid lines can be filled with color.
	 * Set fillAlpha to a value greater than 0 to see the fills.
	 * Default value : 0
	 * @param numbern $value
	 * @return AmValueAxis
	 */
	public function setFillAlpha($value) {
		$this->setExtConfigProperty('fillAlpha', $value);
		return $this;
	}
	
	/**
	 * Get fill alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getFillAlpha() {
		return $this->getExtConfigProperty('fillAlpha');
	}
	
	/**
	 * Fill color. Every second space between grid lines can be filled with color.
	 * Set fillAlpha to a value greater than 0 to see the fills.
	 * Default value : #FFFFFF
	 * @param string $value
	 * @return AmValueAxis
	 */
	public function setFillColor($value) {
		$this->setExtConfigProperty('fillColor', $value);
		return $this;
	}
	
	/**
	 * Get fill color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getFillColor() {
		return $this->getExtConfigProperty('fillColor');
	}
	
	/**
	 * Size of value labels text. Will use chart's fontSize if not set.
	 * @param number $value
	 * @return AmValueAxis
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
	 * Opacity of grid lines.
	 * Default value : 0.2
	 * @param number $value
	 * @return AmValueAxis
	 */
	public function setGridAlpha($value) {
		$this->setExtConfigProperty('gridAlpha', $value);
		return $this;
	}
	
	/**
	 * Get grid alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getGridAlpha() {
		return $this->getExtConfigProperty('gridAlpha');
	}
	
	/**
	 * Color of grid lines.
	 * Default value : #000000
	 * @param string $value
	 * @return AmValueAxis
	 */
	public function setGridColor($value) {
		$this->setExtConfigProperty('gridColor', $value);
		return $this;
	}
	
	/**
	 * Get grid color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getGridColor() {
		return $this->getExtConfigProperty('gridColor');
	}
	
	/**
	 * Number of grid lines. In case this is value axis, or your categoryAxis parses dates, the number is approximate.
	 * The default value is 5. If you set autoGridCount to true, this property is ignored.
	 * @param number $value
	 * @return AmValueAxis
	 */
	public function setGridCount($value) {
		$this->setExtConfigProperty('gridCount', $value);
		return $this;
	}
	
	/**
	 * Get grid count
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getGridCount() {
		return $this->getExtConfigProperty('gridCount');
	}
	
	/**
	 * Thickness of grid lines.
	 * Default value : 1
	 * @param number $value
	 * @return AmValueAxis
	 */
	public function setGridThickness($value) {
		$this->setExtConfigProperty('gridThickness', $value);
		return $this;
	}
	
	/**
	 * Get grid thickness
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getGridThickness() {
		return $this->getExtConfigProperty('gridThickness');
	}
	
	/**
	 * If autoMargins of a chart is set to true, but you want this axis not to be measured when calculating margin, set ignoreAxisWidth to true.
	 * Default value : false
	 * @param boolean $value
	 * @return AmAxisBase
	 */
	public function setIgnoreAxisWidth($value) {
		$this->setExtConfigProperty('ignoreAxisWidth', $value);
		return $this;
	}
	
	/**
	 * Get ignore axis width
	 * @return boolean
	 */
	public function getIgnoreAxisWidth() {
		return $this->getExtConfigProperty('ignoreAxisWidth');
	}
	
	/**
	 * Specifies whether values should be placed inside or outside plot area.
	 * Default value : false
	 * @param boolean $value
	 * @return AmAxisBase
	 */
	public function setInside($value) {
		$this->setExtConfigProperty('inside', $value);
		return $this;
	}
	
	/**
	 * Get inside
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getInside() {
		return $this->getExtConfigProperty('inside');
	}
	
	/**
	 * Frequency at which labels should be placed. Doesn't work for CategoryAxis if parseDates is set to true.
	 * Default value : 1
	 * @param number $value
	 * @return AmAxisBase
	 */
	public function setLabelFrequency($value) {
		$this->setExtConfigProperty('labelFrequency', $value);
		return $this;
	}
	
	/**
	 * Get label frequency
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLabelFrequency() {
		return $this->getExtConfigProperty('labelFrequency');
	}
	
	/**
	 * You can use it to adjust position of axes labels. Works both with CategoryAxis and ValueAxis.
	 * Default value : 0
	 * @param number $value
	 * @return AmAxisBase
	 */
	public function setLabelOffset($value) {
		$this->setExtConfigProperty('labelOffset', $value);
		return $this;
	}
	
	/**
	 * Get label offset
	 * @return number
	 */
	public function getLabelOffset() {
		return $this->getExtConfigProperty('labelOffset');
	}
	
	/**
	 * Rotation angle of a label. Only horizontal axis' values can be rotated. If you set this for vertical axis, the setting will be ignored. 
	 * Possible values from -90 to 90.
	 * Default value : 0
	 * @param number $value
	 * @return AmAxisBase
	 */
	public function setLabelRotation($value) {
		$this->setExtConfigProperty('labelRotation', $value);
		return $this;
	}
	
	/**
	 * Get label rotation
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLabelRotation() {
		return $this->getExtConfigProperty('labelRotation');
	}
	
	/**
	 * Specifies whether axis displays category axis' labels and value axis' values.
	 * Default value : true
	 * @param boolean $value
	 * @return AmAxisBase
	 */
	public function setLabelsEnabled($value) {
		$this->setExtConfigProperty('labelsEnabled', $value);
		return $this;
	}
	
	/**
	 * Get label is enabled
	 * @return boolean
	 */
	public function getLabelsEnabled() {
		return $this->getExtConfigProperty('labelsEnabled');
	}
	
	/**
	 * This property is used when calculating grid count (when autoGridCount is true). 
	 * It specifies minimum cell width required for one span between grid lines.
	 * Default value : 75
	 * @param number $value
	 * @return AmAxisBase
	 */
	public function setMinHorizontalGap($value) {
		$this->setExtConfigProperty('minHorizontalGap', $value);
		return $this;
	}
	
	/**
	 * Get minor horizontal gap
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMinHorizontalGap() {
		return $this->getExtConfigProperty('minHorizontalGap');
	}
	
	/**
	 * Opacity of minor grid. In order minor to be visible, you should set minorGridEnabled to true.
	 * Default value : 0.07
	 * @param number $value
	 * @return AmAxisBase
	 */
	public function setMinorGridAlpha($value) {
		$this->setExtConfigProperty('minorGridAlpha', $value);
		return $this;
	}
	
	/**
	 * Get minor grid alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMinorGridAlpha() {
		return $this->getExtConfigProperty('minorGridAlpha');
	}
	
	/**
	 * Specifies if minor grid should be displayed.
	 * Default value : false
	 * @param boolean $value
	 * @return AmAxisBase
	 */
	public function setMinorGridEnabled($value) {
		$this->setExtConfigProperty('minorGridEnabled', $value);
		return $this;
	}
	
	/**
	 * Get minor grid enabled
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMinorGridEnabled() {
		return $this->getExtConfigProperty('minorGridEnabled');
	}
	
	/**
	 * This property is used when calculating grid count (when autoGridCount is true). 
	 * It specifies minimum cell height required for one span between grid lines.
	 * Default value : 35
	 * @param number $value
	 * @return AmAxisBase
	 */
	public function setMinVerticalGap($value) {
		$this->setExtConfigProperty('minVerticalGap', $value);
		return $this;
	}
	
	/**
	 * Get mini vertical gap
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMinVerticalGap() {
		return $this->getExtConfigProperty('minVerticalGap');
	}
	
	/**
	 * The distance of the axis to the plot area, in pixels. Negative values can also be used.
	 * Default value : 0
	 * @param number $value
	 * @return AmAxisBase
	 */
	public function setOffset($value) {
		$this->setExtConfigProperty('offset', $value);
		return $this;
	}
	
	/**
	 * Get offset
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getOffset() {
		return $this->getExtConfigProperty('offset');
	}
	
	/**
	 * Possible values are: "top", "bottom", "left", "right". If axis is vertical, default position is "left". 
	 * If axis is horizontal, default position is "bottom".
	 * Default value : bottom
	 * @param string $value
	 * @return AmAxisBase
	 */
	public function setPosition($value) {
		$this->setExtConfigProperty('position', $value);
		return $this;
	}
	
	/**
	 * Get osition
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPosition() {
		return $this->getExtConfigProperty('position');
	}
	
	/**
	 * Whether to show first axis label or not. This works properly only on ValueAxis. 
	 * With CategoryAxis it wont work 100%, it depends on the period, zooming, etc. 
	 * There is no guaranteed way to force category axis to show or hide first label.
	 * Default value : true
	 * @param boolean $value
	 * @return AmAxisBase
	 */
	public function setShowFirstLabel($value) {
		$this->setExtConfigProperty('showFirstLabel', $value);
		return $this;
	}
	
	/**
	 * Get show first label
	 * @return boolean
	 */
	public function getShowFirstLabel() {
		return $this->getExtConfigProperty('showFirstLabel');
	}
	
	/**
	 * Whether to show last axis label or not. This works properly only on ValueAxis. 
	 * With CategoryAxis it wont work 100%, it depends on the period, zooming, etc. 
	 * There is no guaranteed way to force category axis to show or hide last label.
	 * Default value : true
	 * @param boolean $value
	 * @return AmAxisBase
	 */
	public function setShowLastLabel($value) {
		$this->setExtConfigProperty('showLastLabel', $value);
		return $this;
	}
	
	/**
	 * Get show last label
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getShowLastLabel() {
		return $this->getExtConfigProperty('showLastLabel');
	}
	
	/**
	 * Length of the tick marks.
	 * Default value : 5
	 * @param number $value
	 * @return AmAxisBase
	 */
	public function setTickLength($value) {
		$this->setExtConfigProperty('tickLength', $value);
		return $this;
	}
	
	/**
	 * Get tick length
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getTickLength() {
		return $this->getExtConfigProperty('tickLength');
	}
	
	/**
	 * Title of the axis.
	 * @param string $value
	 * @return AmAxisBase
	 */
	public function setTitle($value) {
		$this->setExtConfigProperty('title', $value);
		return $this;
	}
	
	/**
	 * Get title
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getTitle() {
		return $this->getExtConfigProperty('title');
	}
	
	/**
	 * Specifies if title should be bold or not.
	 * Default value : true
	 * @param boolean $value
	 * @return AmAxisBase
	 */
	public function setTitleBold($value) {
		$this->setExtConfigProperty('titleBold', $value);
		return $this;
	}
	
	/**
	 * Get title bold
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getTitleBold() {
		return $this->getExtConfigProperty('titleBold');
	}
	
	/**
	 * Color of axis title. Will use text color of chart if not set any.
	 * @param string $value
	 * @return AmAxisBase
	 */
	public function setTitleColor($value) {
		$this->setExtConfigProperty('titleColor', $value);
		return $this;
	}
	
	/**
	 * Get title color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getTitleColor() {
		return $this->getExtConfigProperty('titleColor');
	}
	
	/**
	 * Font size of axis title. Will use font size of chart plus two pixels if not set any.
	 * @param number $value
	 * @return AmAxisBase
	 */
	public function setTitleFontSize($value) {
		$this->setExtConfigProperty('titleFontSize', $value);
		return $this;
	}
	
	/**
	 * Get title font size
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getTitleFontSize() {
		return $this->getExtConfigProperty('titleFontSize');
	}
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","AmchartConfigObject");
	
		$validProps = array(
				'autoGridCount',
				'axisAlpha',
				'axisColor',
				'axisThickness',
				'color',
				'dashLength',
				'duration',
				'fillAlpha',
				'fillColor',
				'fontSize',
				'gridAlpha',
				'gridColor',
				'gridCount',
				'gridThickness',
				'ignoreAxisWidth',
				'inside',
				'labelFrequency',
				'labelOffset',
				'labelRotation',
				'labelsEnabled',
				'minHorizontalGap',
				'minorGridAlpha',
				'minorGridEnabled',
				'minVerticalGap',
				'offset',
				'position',
				'showFirstLabel',
				'showLastLabel',
				'tickLength',
				'title',
				'titleBold',
				'titleColor',
				'titleFontSize',
		);
		$this->addValidConfigProperties($validProps);
	}
}