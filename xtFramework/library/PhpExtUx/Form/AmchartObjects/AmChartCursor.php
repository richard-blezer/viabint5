<?php

/**
 * Class AmChartCursor
 * @see http://docs.amcharts.com/3/javascriptcharts/ChartCursor
 * @author radoslav
 *
 */
class AmChartCursor extends AmChartConfigObject {
	
	/**
	 * If you set adjustment to -1, the balloon will be shown near previous, if you set it to 1 - near next data point.
	 * Default value : 0
	 * @param number $value
	 * @return AmChartCursor
	 */
	public function setAdjustment($value) {
		$this->setExtConfigProperty('adjustment', $value);
		return $this;
	}
	
	/**
	 * Get adjustment
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getAdjustment() {
		return $this->getExtConfigProperty('adjustment');
	}
	
	/**
	 * Duration of animation of a line, in seconds.
	 * Default value : 0.3
	 * @param number $value
	 * @return AmChartCursor
	 */
	public function setAnimationDuration($value) {
		$this->setExtConfigProperty('animationDuration', $value);
		return $this;
	}
	
	/**
	 * Get animation duration
	 */
	public function getAnimationDuration() {
		return $this->getExtConfigProperty('animationDuration');
	}
	
	/**
	 * Specifies if cursor should arrange balloons so they won't overlap. If chart is rotated, it might be good idea to turn this off.
	 * Default value : true
	 * @param boolean $value
	 * @return AmChartCursor
	 */
	public function setAvoidBalloonOverlapping($value) {
		$this->setExtConfigProperty('avoidBalloonOverlapping', $value);
		return $this;
	}
	
	/**
	 * Get avoid allon overlapping
	 * @return boolean
	 */
	public function getAvoidBalloonOverlapping() {
		return $this->getExtConfigProperty('avoidBalloonOverlapping');
	}
	
	/**
	 * Specifies if bullet for each graph will follow the cursor.
	 * Default value : false
	 * @param boolean $value
	 * @return AmChartCursor
	 */
	public function setBulletsEnabled($value) {
		$this->setExtConfigProperty('bulletsEnabled', $value);
		return $this;
	}
	
	/**
	 * Get bullets enabled
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBulletsEnabled() {
		return $this->getExtConfigProperty('bulletsEnabled');
	}
	
	/**
	 * Size of bullets, following the cursor.
	 * Default value : 8
	 * @param number $value
	 * @return AmChartCursor
	 */
	public function setBulletSize($value) {
		$this->setExtConfigProperty('bulletSize', $value);
		return $this;
	}
	
	/**
	 * Get bullet size
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBulletSize() {
		return $this->getExtConfigProperty('bulletSize');
	}
	
	/**
	 * Opacity of the category balloon.
	 * Default value : 1
	 * @param number $value
	 * @return AmChartCursor
	 */
	public function setCategoryBalloonAlpha($value) {
		$this->setExtConfigProperty('categoryBalloonAlpha', $value);
		return $this;
	}
	
	/**
	 * Get category baloon alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCategoryBalloonAlpha() {
		return $this->getExtConfigProperty('categoryBalloonAlpha');
	}
	
	/**
	 * Color of the category balloon. cursorColor is used if not set.
	 * @param string $value
	 * @return AmChartCursor
	 */
	public function setCategoryBalloonColor($value) {
		$this->setExtConfigProperty('categoryBalloonColor', $value);
		return $this;
	}
	
	/**
	 * Get category baloon color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCategoryBalloonColor() {
		return $this->getExtConfigProperty('categoryBalloonColor');
	}
	
	/**
	 * Category balloon date format (used only if category axis parses dates).
	 * Default value : MMM DD, YYYY
	 * @see https://amcharts.zendesk.com/entries/23272496-Formatting-dates-on-category-axis
	 * @var string $value
	 * @return AmChartCursor
	 */
	public function setCategoryBalloonDateFormat($value) {
		$this->setExtConfigProperty('categoryBalloonDateFormat', $value);
		return $this;
	}
	
	/**
	 * Get category baloon date format
	 */
	public function getCategoryBalloonDateFormat() {
		return $this->getExtConfigProperty('categoryBalloonDateFormat');
	}
	
	/**
	 * Specifies whether category balloon is enabled.
	 * Default value : true
	 * @param boolean $value
	 * @return AmChartCursor
	 */
	public function setCategoryBalloonEnabled($value) {
		$this->setExtConfigProperty('categoryBalloonEnabled', $value);
		return $this;
	}
	
	/**
	 * Get category baloon enabled
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCategoryBalloonEnabled() {
		return $this->getExtConfigProperty('categoryBalloonEnabled');
	}
	
	/**
	 * Text color.
	 * Default value : #ffffff
	 * @param string $value
	 * @return AmChartCursor
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
	 * Opacity of the cursor line.
	 * Default value : 1
	 * @param number $value
	 * @return AmChartCursor
	 */
	public function setCursorAlpha($value) {
		$this->setExtConfigProperty('cursorAlpha', $value);
		return $this;
	}
	
	/**
	 * Get cursor alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCursorAlpha() {
		return $this->getExtConfigProperty('cursorAlpha');
	}
	
	/**
	 * Color of the cursor line.
	 * Default value : #cc0000
	 * @param string $value
	 * @return AmChartCursor
	 */
	public function setCursorColor($value) {
		$this->setExtConfigProperty('cursorColor', $value);
		return $this;
	}
	
	/**
	 * Get cursor color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCursorColor() {
		return $this->getExtConfigProperty('cursorColor');
	}
	
	/**
	 * Specifies where the cursor line should be placed - on the beginning of the period (day, hour, etc) or in the middle (only when parseDates property of categoryAxis is set to true). 
	 * If you want the cursor to follow mouse and not to glue to the nearest data point, set "mouse" here. Possible values are: start, middle, mouse.
	 * Default value : middle
	 * @param string $value
	 * @return AmChartCursor
	 */
	public function setCursorPosition($value) {
		$this->setExtConfigProperty('cursorPosition', $value);
		return $this;
	}
	
	/**
	 * Get cursor position
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCursorPosition() {
		return $this->getExtConfigProperty('cursorPosition');
	}
	
	/**
	 * Specifies whether cursor is enabled.
	 * Default value : true
	 * @param boolean $value
	 * @return AmChartCursor
	 */
	public function setEnabled($value) {
		$this->setExtConfigProperty('enabled', $value);
		return $this;
	}
	
	/**
	 * Get enabled
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getEnabled() {
		return $this->getExtConfigProperty('enabled');
	}
	
	/**
	 * If set to true, instead of a cursor line user will see a fill which width will always be equal to the width of one data item. 
	 * We'd recommend setting cusrsorAlpha to 0.1 or some other small number if using this feature.
	 * Default value : false
	 * @param boolean $value
	 * @return AmChartCursor
	 */
	public function setFullWidth($value) {
		$this->setExtConfigProperty('fullWidth', $value);
		return $this;
	}
	
	/**
	 * Get full width
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getFullWidth() {
		return $this->getExtConfigProperty('fullWidth');
	}
	
	/**
	 * If you make graph's bullets invisible by setting their opacity to 0 and will set graphBulletAlpha to 1, the bullets will only appear at the cursor's position.
	 * @param number $value
	 * @return AmChartCursor
	 */
	public function setGraphBulletAlpha($value) {
		$this->setExtConfigProperty('graphBulletAlpha', $value);
		return $this;
	}
	
	/**
	 * Get graph bullet alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getGraphBulletAlpha() {
		return $this->getExtConfigProperty('graphBulletAlpha');
	}
	
	/**
	 * Size of a graph's bullet (if available) at the cursor position. If you don't want the bullet to change it's size, set this property to 1.
	 * Default value : 1.7
	 * @param number $value
	 * @return AmChartCursor
	 */
	public function setGraphBulletSize($value) {
		$this->setExtConfigProperty('graphBulletSize', $value);
		return $this;
	}
	
	/**
	 * Get graph bullet size
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getGraphBulletSize() {
		return $this->getExtConfigProperty('graphBulletSize');
	}
	
	/**
	 * If this is set to true, only one balloon at a time will be displayed. Note, this is quite CPU consuming.
	 * Default value : false
	 * @param boolean $value
	 * @return AmChartCursor
	 */
	public function setOneBalloonOnly($value) {
		$this->setExtConfigProperty('oneBalloonOnly', $value);
		return $this;
	}
	
	/**
	 * Get one baloon only
	 * @return boolean
	 */
	public function getOneBalloonOnly() {
		return $this->getExtConfigProperty('oneBalloonOnly');
	}
	
	/**
	 * If this is set to true, the user will be able to pan the chart (Serial only) instead of zooming.
	 * Default value : false
	 * @param boolean $value
	 * @return AmChartCursor
	 */
	public function setPan($value) {
		$this->setExtConfigProperty('pan', $value);
		return $this;
	}
	
	/**
	 * Get pan
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPan() {
		return $this->getExtConfigProperty('pan');
	}
	
	/**
	 * Opacity of the selection.
	 * Default value : 0.2
	 * @param number $value
	 * @return AmChartCursor
	 */
	public function setSelectionAlpha($value) {
		$this->setExtConfigProperty('selectionAlpha', $value);
		return $this;
	}
	
	/**
	 * Get selection alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getSelectionAlpha() {
		return $this->getExtConfigProperty('selectionAlpha');
	}
	
	/**
	 * Specifies if cursor should only mark selected area but not zoom-in after user releases mouse button.
	 * Default value : false
	 * @param boolean $value
	 * @return AmChartCursor
	 */
	public function setSelectWithoutZooming($value) {
		$this->setExtConfigProperty('selectWithoutZooming', $value);
		return $this;
	}
	
	/**
	 * Get select without zooming
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getSelectWithoutZooming() {
		return $this->getExtConfigProperty('selectWithoutZooming');
	}
	
	/**
	 * If true, the graph will display balloon on next available data point if currently hovered item doesn't have value for this graph.
	 * Default value : false
	 * @param boolean $value
	 * @return AmChartCursor
	 */
	public function setShowNextAvailable($value) {
		$this->setExtConfigProperty('showNextAvailable', $value);
		return $this;
	}
	
	/**
	 * Get show next available
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getShowNextAvailable() {
		return $this->getExtConfigProperty('showNextAvailable');
	}
	
	/**
	 * Specifies whether value balloons are enabled. In case they are not, the balloons might be displayed anyway, when the user rolls-over the column or bullet.
	 * Default value : true
	 * @param boolean $value
	 * @return AmChartCursor
	 */
	public function setValueBalloonsEnabled($value) {
		$this->setExtConfigProperty('valueBalloonsEnabled', $value);
		return $this;
	}
	
	/**
	 * Get value baloon enabled
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getValueBalloonsEnabled() {
		return $this->getExtConfigProperty('valueBalloonsEnabled');
	}
	
	/**
	 * Specifies if the user can zoom-in the chart. If pan is set to true, zoomable is switched to false automatically.
	 * Default value : true
	 * @param boolean $value
	 * @return AmChartCursor
	 */
	public function setZoomable($value) {
		$this->setExtConfigProperty('zoomable', $value);
		return $this;
	}
	
	/**
	 * Get zoomable
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getZoomable() {
		return $this->getExtConfigProperty('zoomable');
	}
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","AmchartConfigObject");
	
		$validProps = array(
			'adjustment',
			'animationDuration',
			'avoidBalloonOverlapping',
			'bulletsEnabled',
			'bulletSize',
			'categoryBalloonAlpha',
			'categoryBalloonColor',
			'categoryBalloonDateFormat',
			'categoryBalloonEnabled',
			'color',
			'cursorAlpha',
			'cursorColor',
			'cursorPosition',
			'enabled',
			'fullWidth',
			'graphBulletAlpha',
			'graphBulletSize',
			'oneBalloonOnly',
			'pan',
			'selectionAlpha',
			'selectWithoutZooming',
			'showNextAvailable',
			'valueBalloonsEnabled',
			'zoomable',
		);
		$this->addValidConfigProperties($validProps);
		$this->setExtConfigProperty('configClassName', 'AmCharts.ChartCursor');
	}
	
}