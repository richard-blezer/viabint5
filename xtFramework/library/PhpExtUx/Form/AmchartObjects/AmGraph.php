<?php

/**
 * Class AmGraph
 * @see http://docs.amcharts.com/3/javascriptcharts/AmGraph
 * @author radoslav
 *
 */
class AmGraph extends AmChartConfigObject {
	
	/**
	 * Name of the alpha field in your dataProvider.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setAlphaField($value) {
		$this->setExtConfigProperty('alphaField', $value);
		return $this;
	}
	
	/**
	 * Get alpha field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getAlphaField() {
		return $this->getExtConfigProperty('alphaField');
	}
	
	/**
	 * Value balloon color. Will use graph or data item color if not set.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setBalloonColor($value) {
		$this->setExtConfigProperty('balloonColor', $value);
		return $this;
	}
	
	/**
	 * Get baloon color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBalloonColor() {
		return $this->getExtConfigProperty('balloonColor');
	}
	
	/**
	 * Balloon text. You can use tags like [[value]], [[description]], [[percents]], [[open]], [[category]] or any other field name from your data provider. 
	 * HTML tags can also be used.
	 * Default value : [[value]]
	 * @param string $value
	 * @return AmGraph
	 */
	public function setBalloonText($value) {
		$this->setExtConfigProperty('balloonText', $value);
		return $this;
	}
	
	/**
	 * Get balloon text
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBalloonText() {
		return $this->getExtConfigProperty('balloonText');
	}
	
	/**
	 * Specifies if the line graph should be placed behind column graphs
	 * Default value : false
	 * @param boolean $value
	 * @return AmGraph
	 */
	public function setBehindColumns($value) {
		$this->setExtConfigProperty('behindColumns', $value);
		return $this;
	}
	
	/**
	 * Get behind columns
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBehindColumns() {
		return $this->getExtConfigProperty('behindColumns');
	}
	
	/**
	 * Type of the bullets. Possible values are: "none", "round", "square", "triangleUp", "triangleDown",
	 * "triangleLeft", "triangleRight", "bubble", "diamond", "xError", "yError" and "custom".
	 * Default value : none
	 * @var string $value
	 * @return AmGraph
	 */
	public function setBullet($value) {
		$this->setExtConfigProperty('bullet', $value);
		return $this;
	}
	
	/**
	 * Get bullet
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBullet() {
		return $this->getExtConfigProperty('bullet');
	}
	
	/**
	 * Opacity of bullets. Value range is 0 - 1.
	 * Default value : 1
	 * @param number $value
	 * @return AmGraph
	 */
	public function setBulletAlpha($value) {
		$this->setExtConfigProperty('bulletAlpha', $value);
		return $this;
	}
	
	/**
	 * Get bullet alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBulletAlpha() {
		return $this->getExtConfigProperty('bulletAlpha');
	}
	
	/**
	 * Bullet border opacity.
	 * Default value : 0
	 * @param number $value
	 * @return AmGraph
	 */
	public function setBulletBorderAlpha($value) {
		$this->setExtConfigProperty('bulletBorderAlpha', $value);
		return $this;
	}
	
	/**
	 * Get bullet birder alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBulletBorderAlpha() {
		return $this->getExtConfigProperty('bulletBorderAlpha');
	}
	
	/**
	 * Bullet border color. Will use lineColor if not set.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setBulletBorderColor($value) {
		$this->setExtConfigProperty('bulletBorderColor', $value);
		return $this;
	}
	
	/**
	 * Get bullet border color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBulletBorderColor() {
		return $this->getExtConfigProperty('bulletBorderColor');
	}
	
	/**
	 * Bullet border thickness.
	 * Default value : 2
	 * @param number $value
	 * @return AmGraph
	 */
	public function setBulletBorderThickness($value) {
		$this->setExtConfigProperty('bulletBorderThickness', $value);
		return $this;
	}
	
	/**
	 * Get bullet border thickness
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBulletBorderThickness() {
		return $this->getExtConfigProperty('bulletBorderThickness');
	}
	
	/**
	 * Bullet color. Will use lineColor if not set.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setBulletColor($value) {
		$this->setExtConfigProperty('bulletColor', $value);
		return $this;
	}
	
	/**
	 * Get bullet color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBulletColor() {
		return $this->getExtConfigProperty('bulletColor');
	}
	
	/**
	 * Name of the bullet field in your dataProvider.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setBulletField($value) {
		$this->setExtConfigProperty('bulletField', $value);
		return $this;
	}
	
	/**
	 * Get bullet field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBulletField() {
		return $this->getExtConfigProperty('bulletField');
	}
	
	/**
	 * Bullet offset. Distance from the actual data point to the bullet. Can be used to place custom bullets above the columns.
	 * Default value : 0
	 * @param number $value
	 * @return AmGraph
	 */
	public function setBulletOffset($value) {
		$this->setExtConfigProperty('bulletOffset', $value);
		return $this;
	}
	
	/**
	 * Get bullet offset
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBulletOffset() {
		return $this->getExtConfigProperty('bulletOffset');
	}
	
	/**
	 * Bullet size.
	 * Default value : 8
	 * @param number $value
	 * @return AmGraph
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
	 * Name of the bullet size field in your dataProvider.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setBulletSizeField($value) {
		$this->setExtConfigProperty('bulletSizeField', $value);
		return $this;
	}
	
	/**
	 * Get bullet size field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBulletSizeField() {
		return $this->getExtConfigProperty('bulletSizeField');
	}
	
	/**
	 * In case you want to place this graph's columns in front of other columns, set this to false. In case "true", the columns will be clustered next to each other.
	 * Default value : true
	 * @param boolean $value
	 * @return AmGraph
	 */
	public function setClustered($value) {
		$this->setExtConfigProperty('clustered', $value);
		return $this;
	}
	
	/**
	 * Get clustered
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getClustered() {
		return $this->getExtConfigProperty('clustered');
	}
	
	/**
	 * Color of value labels. Will use chart's color if not set.
	 * @param string $value
	 * @return AmGraph
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
	 * Name of the color field in your dataProvider.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setColorField($value) {
		$this->setExtConfigProperty('colorField', $value);
		return $this;
	}
	
	/**
	 * Get color field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getColorField() {
		return $this->getExtConfigProperty('colorField');
	}
	
	/**
	 * You can specify custom column width for each graph individually. Value range is 0 - 1 (we set relative width, not pixel width here).
	 * @param number $value
	 * @return AmGraph
	 */
	public function setColumnWidth($value) {
		$this->setExtConfigProperty('columnWidth', $value);
		return $this;
	}
	
	/**
	 * Get column width
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getColumnWidth() {
		return $this->getExtConfigProperty('columnWidth');
	}
	
	/**
	 * Specifies whether to connect data points if data is missing.
	 * Default value : true
	 * @param boolean $value
	 * @return AmGraph
	 */
	public function setConnect($value) {
		$this->setExtConfigProperty('connect', $value);
		return $this;
	}
	
	/**
	 * Get connect
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getConnect() {
		return $this->getExtConfigProperty('connect');
	}
	
	/**
	 * Corner radius of column. It can be set both in pixels or in percents. 
	 * The chart's depth and angle styles must be set to 0. The default value is 0. 
	 * Note, cornerRadiusTop will be applied for all corners of the column, JavaScript charts do not have a possibility to set separate corner radius for top and bottom. 
	 * As we want all the property names to be the same both on JS and Flex, we didn't change this too.
	 * Default value : 0
	 * @param number $value
	 * @return AmGraph
	 */
	public function setCornerRadiusTop($value) {
		$this->setExtConfigProperty('cornerRadiusTop', $value);
		return $this;
	}
	
	/**
	 * Get corner radius top
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCornerRadiusTop() {
		return $this->getExtConfigProperty('cornerRadiusTop');
	}
	
	/**
	 * If bulletsEnabled of ChartCurosor is true, a bullet on each graph follows the cursor. 
	 * You can set opacity of each graphs bullet. In case you want to disable these bullets for a certain graph, set opacity to 0.
	 * Default value : 1
	 * @param number $value
	 * @return AmGraph
	 */
	public function setCursorBulletAlpha($value) {
		$this->setExtConfigProperty('cursorBulletAlpha', $value);
		return $this;
	}
	
	/**
	 * Get cursor bullet alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCursorBulletAlpha() {
		return $this->getExtConfigProperty('cursorBulletAlpha');
	}
	
	/**
	 * Path to the image of custom bullet.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setCustomBullet($value) {
		$this->setExtConfigProperty('customBullet', $value);
		return $this;
	}
	
	/**
	 * Get custom bullet
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCustomBullet() {
		return $this->getExtConfigProperty('customBullet');
	}
	
	/**
	 * Name of the custom bullet field in your dataProvider.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setCustomBulletField($value) {
		$this->setExtConfigProperty('customBulletField', $value);
		return $this;
	}
	
	/**
	 * Get custom bullet field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCustomBulletField() {
		return $this->getExtConfigProperty('customBulletField');
	}
	
	/**
	 * Path to the image for legend marker.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setCustomMarker($value) {
		$this->setExtConfigProperty('customMarker', $value);
		return $this;
	}
	
	/**
	 * Get custom marker
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getCustomMarker() {
		return $this->getExtConfigProperty('customMarker');
	}
	
	/**
	 * Dash length. If you set it to a value greater than 0, the graph line (or columns border) will be dashed.
	 * Default value : 0
	 * @param number $value
	 * @return AmGraph
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
	 * Name of the dash length field in your dataProvider. 
	 * This property adds a possibility to change graphs’ line from solid to dashed on any data point. 
	 * You can also make columns border dashed using this setting.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setDashLengthField($value) {
		$this->setExtConfigProperty('dashLengthField', $value);
		return $this;
	}
	
	/**
	 * Get dash length field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getDashLengthField() {
		return $this->getExtConfigProperty('dashLengthField');
	}
	
	/**
	 * Name of the description field in your dataProvider.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setDescriptionField($value) {
		$this->setExtConfigProperty('descriptionField', $value);
		return $this;
	}
	
	/**
	 * Get description field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getDescriptionField() {
		return $this->getExtConfigProperty('descriptionField');
	}
	
	/**
	 * Name of error value field in your data provider.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setErrorField($value) {
		$this->setExtConfigProperty('errorField', $value);
		return $this;
	}
	
	/**
	 * Get error field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getErrorField() {
		return $this->getExtConfigProperty('errorField');
	}
	
	/**
	 * Opacity of fill. Plural form is used to keep the same property names as our Flex charts'. 
	 * Flex charts can accept array of numbers to generate gradients. 
	 * Although you can set array here, only first value of this array will be used.
	 * Default value : 0
	 * @param number $value
	 * @return AmGraph
	 */
	public function setFillAlphas($value) {
		$this->setExtConfigProperty('fillAlphas', $value);
		return $this;
	}
	
	/**
	 * Get fill alphas
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getFillAlphas() {
		return $this->getExtConfigProperty('fillAlphas');
	}
	
	/**
	 * Fill color. Will use lineColor if not set. You can also set array of colors here.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setFillColors($value) {
		$this->setExtConfigProperty('fillColors', $value);
		return $this;
	}
	
	/**
	 * Get fill colors
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getFillColors() {
		return $this->getExtConfigProperty('fillColors');
	}
	
	/**
	 * Name of the fill colors field in your dataProvider. 
	 * This property adds a possibility to change line graphs’ fill color on any data point to create highlighted sections of the graph. 
	 * Works only with AmSerialChart.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setFillColorsField($value) {
		$this->setExtConfigProperty('fillColorsField', $value);
		return $this;
	}
	
	/**
	 * Get fill colors field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getFillColorsField() {
		return $this->getExtConfigProperty('fillColorsField');
	}
	
	/**
	 * XY chart only. If you set this property to id or reference of your X or Y axis, and the fillAlphas is > 0, 
	 * the area between graph and axis will be filled with color, like in this demo.
	 * @param AmValueAxis $value
	 * @return AmGraph
	 */
	public function setFillToAxis($value) {
		$this->setExtConfigProperty('fillToAxis', $value);
		return $this;
	}
	
	/**
	 * Get fill to axis
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getFillToAxis() {
		return $this->getExtConfigProperty('fillToAxis');
	}
	
	/**
	 * Size of value labels text. Will use chart's fontSize if not set.
	 * @param number $value
	 * @return AmGraph
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
	 * Orientation of the gradient fills (only for "column" graph type). Possible values are "vertical" and "horizontal".
	 * Default value : vertical
	 * @param string $value
	 * @return AmGraph
	 */
	public function setGradientOrientation($value) {
		$this->setExtConfigProperty('gradientOrientation', $value);
		return $this;
	}
	
	/**
	 * Get gradient orientation
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getGradientOrientation() {
		return $this->getExtConfigProperty('gradientOrientation');
	}
	
	/**
	 * Specifies whether the graph is hidden. 
	 * Do not use this to show/hide the graph, use hideGraph(graph) and showGraph(graph) methods instead.
	 * Default value : false
	 * @param boolean $value
	 * @return AmGraph
	 */
	public function setHidden($value) {
		$this->setExtConfigProperty('hidden', $value);
		return $this;
	}
	
	/**
	 * Get hidden
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getHidden() {
		return $this->getExtConfigProperty('hidden');
	}
	
	/**
	 * If there are more data points than hideBulletsCount, the bullets will not be shown. 0 means the bullets will always be visible.
	 * Default value : 0
	 * @param number $value
	 * @return AmGraph
	 */
	public function setHideBulletsCount($value) {
		$this->setExtConfigProperty('hideBulletsCount', $value);
		return $this;
	}
	
	/**
	 * Get hide bullets count
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getHideBulletsCount() {
		return $this->getExtConfigProperty('hideBulletsCount');
	}
	
	/**
	 * Name of the high field (used by candlesticks and ohlc) in your dataProvider.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setHighField($value) {
		$this->setExtConfigProperty('highField', $value);
		return $this;
	}
	
	/**
	 * Get high field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getHighField() {
		return $this->getExtConfigProperty('highField');
	}
	
	/**
	 * Unique id of a graph. It is not required to set one, unless you want to use this graph for as your scrollbar's graph and need to indicate which graph should be used.
	 * @param string $value
	 * @return AmGraph
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
	 * Whether to include this graph when calculating min and max value of the axis.
	 * Default value : true
	 * @param boolean $value
	 * @return AmGraph
	 */
	public function setIncludeInMinMax($value) {
		$this->setExtConfigProperty('includeInMinMax', $value);
		return $this;
	}
	
	/**
	 * Get include min max
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getIncludeInMinMax() {
		return $this->getExtConfigProperty('includeInMinMax');
	}
	
	/**
	 * Name of label color field in data provider.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setLabelColorField($value) {
		$this->setExtConfigProperty('labelColorField', $value);
		return $this;
	}
	
	/**
	 * Get label color field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLabelColorField() {
		return $this->getExtConfigProperty('labelColorField');
	}
	
	/**
	 * Position of value label. Possible values are: "bottom", "top", "right", "left", "inside", "middle". 
	 * Sometimes position is changed by the chart, depending on a graph type, rotation, etc.
	 * Default value : top
	 * @param string $value
	 * @return AmGraph
	 */
	public function setLabelPosition($value) {
		$this->setExtConfigProperty('labelPosition', $value);
		return $this;
	}
	
	/**
	 * Get label position
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLabelPosition() {
		return $this->getExtConfigProperty('labelPosition');
	}
	
	/**
	 * Value label text. You can use tags like [[value]], [[description]], [[percents]], [[open]], [[category]].
	 * @param string $value
	 * @return AmGraph
	 */
	public function setLabelText($value) {
		$this->setExtConfigProperty('labelText', $value);
		return $this;
	}
	
	/**
	 * Get label text
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLabelText() {
		return $this->getExtConfigProperty('labelText');
	}
	
	/**
	 * Legend marker opacity. Will use lineAlpha if not set. Value range is 0 - 1.
	 * @param number $value
	 * @return AmGraph
	 */
	public function setLegendAlpha($value) {
		$this->setExtConfigProperty('legendAlpha', $value);
		return $this;
	}
	
	/**
	 * Get legend alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLegendAlpha() {
		return $this->getExtConfigProperty('legendAlpha');
	}
	
	/**
	 * Legend marker color. Will use lineColor if not set.
	 * @param color $value
	 * @return AmGraph
	 */
	public function setLegendColor($value) {
		$this->setExtConfigProperty('legendColor', $value);
		return $this;
	}
	
	/**
	 * Get legend color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLegendColor() {
		return $this->getExtConfigProperty('legendColor');
	}
	
	/**
	 * The text which will be displayed in the value portion of the legend when user is not hovering above any data point. 
	 * The tags should be made out of two parts - the name of a field 
	 * (value / open / close / high / low) and the value of the period you want to be show - open / close / high / low / sum / average / count. 
	 * For example: [[value.sum]] means that sum of all data points of value field in the selected period will be displayed.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setLegendPeriodValueText($value) {
		$this->setExtConfigProperty('legendPeriodValueText', $value);
		return $this;
	}
	
	/**
	 * Get legend period value text
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLegendPeriodValueText() {
		return $this->getExtConfigProperty('legendPeriodValueText');
	}
	
	/**
	 * Legend value text. You can use tags like [[value]], [[description]], [[percents]], [[open]], [[category]] 
	 * You can also use custom fields from your dataProvider. If not set, uses Legend's valueText.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setLegendValueText($value) {
		$this->setExtConfigProperty('legendValueText', $value);
		return $this;
	}
	
	/**
	 * Get legend value text
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLegendValueText() {
		return $this->getExtConfigProperty('legendValueText');
	}
	
	/**
	 * Opacity of the line (or column border). Value range is 0 - 1.
	 * Default value : 1
	 * @param number $value
	 * @return AmGraph
	 */
	public function setLineAlpha($value) {
		$this->setExtConfigProperty('lineAlpha', $value);
		return $this;
	}
	
	/**
	 * Get line alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLineAlpha() {
		return $this->getExtConfigProperty('lineAlpha');
	}
	
	/**
	 * Color of the line (or column border). If you do not set any, the color from [[AmCoordinateChart
	 * @param string $value
	 * @return AmGraph
	 */
	public function setLineColor($value) {
		$this->setExtConfigProperty('lineColor', $value);
		return $this;
	}
	
	/**
	 * Get line color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLineColor() {
		return $this->getExtConfigProperty('lineColor');
	}
	
	/**
	 * 	Name of the line color field in your dataProvider. 
	 * This property adds a possibility to change graphs’ line color on any data point to create highlighted sections of the graph. Works only with AmSerialChart.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setLineColorField($value) {
		$this->setExtConfigProperty('lineColorField', $value);
		return $this;
	}
	
	/**
	 * Get line color field
	 * @return string
	 */
	public function getLineColorField() {
		return $this->getExtConfigProperty('lineColorField');
	}
	
	/**
	 * Specifies thickness of the graph line (or column border).
	 * @param number $value
	 * @return AmGraph
	 */
	public function setLineThickness($value) {
		$this->setExtConfigProperty('lineThickness', $value);
		return $this;
	}
	
	/**
	 * Get line thickness
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLineThickness() {
		return $this->getExtConfigProperty('lineThickness');
	}
	
	/**
	 * Legend marker type. You can set legend marker (key) type for individual graphs. 
	 * Possible values are: square, circle, diamond, triangleUp, triangleDown, triangleLeft, triangleDown, bubble, line, none.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setMarkerType($value) {
		$this->setExtConfigProperty('markerType', $value);
		return $this;
	}
	
	/**
	 * Get marker type
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMarkerType() {
		return $this->getExtConfigProperty('markerType');
	}
	
	/**
	 * Specifies size of the bullet which value is the biggest (XY chart).
	 * Default value : 50
	 * @param number $value
	 * @return AmGraph
	 */
	public function setMaxBulletSize($value) {
		$this->setExtConfigProperty('maxBulletSize', $value);
		return $this;
	}
	
	/**
	 * Get max bullet size
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMaxBulletSize() {
		return $this->getExtConfigProperty('maxBulletSize');
	}
	
	/**
	 * Specifies minimum size of the bullet (XY chart).
	 * Default value : 0
	 * @param string $value
	 * @return AmGraph
	 */
	public function setMinBulletSize($value) {
		$this->setExtConfigProperty('minBulletSize', $value);
		return $this;
	}
	
	/**
	 * Get min bullet size
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMinBulletSize() {
		return $this->getExtConfigProperty('minBulletSize');
	}
	
	/**
	 * It is useful if you have really lots of data points. 
	 * Based on this property the graph will omit some of the lines (if the distance between points is less that minDistance, in pixels). 
	 * This will not affect the bullets or indicator in anyway, so the user will not see any difference (unless you set minValue to a bigger value, let say 5), 
	 * but will increase performance as less lines will be drawn. By setting value to a bigger number you can also make your lines look less jagged.
	 * Default value : 1
	 * @param number $value
	 * @return AmGraph
	 */
	public function setMinDistance($value) {
		$this->setExtConfigProperty('minDistance', $value);
		return $this;
	}
	
	/**
	 * Get min distance
	 * @return number
	 */
	public function getMinDistance() {
		return $this->getExtConfigProperty('minDistance');
	}
	
	/**
	 * If you use different colors for your negative values, a graph below zero line is filled with negativeColor. 
	 * With this property you can define a different base value at which colors should be changed to negative colors.
	 * Default value : 0
	 * @param number $value
	 * @return AmGraph
	 */
	public function setNegativeBase($value) {
		$this->setExtConfigProperty('negativeBase', $value);
		return $this;
	}
	
	/**
	 * Get negative base
	 * @return number
	 */
	public function getNegativeBase() {
		return $this->getExtConfigProperty('negativeBase');
	}
	
	/**
	 * Fill opacity of negative part of the graph. Will use fillAlphas if not set.
	 * @param number $value
	 * @return AmGraph
	 */
	public function setNegativeFillAlphas($value) {
		$this->setExtConfigProperty('negativeFillAlphas', $value);
		return $this;
	}
	
	/**
	 * Get negative fill alphas
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getNegativeFillAlphas() {
		return $this->getExtConfigProperty('negativeFillAlphas');
	}
	
	/**
	 * Fill color of negative part of the graph. Will use fillColors if not set.
	 * @param string $value
	 * @return AmGraph
	 */
	public function setNegativeFillColors($value) {
		$this->setExtConfigProperty('negativeFillColors', $value);
		return $this;
	}
	
	/**
	 * Get negative fill colors
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getNegativeFillColors() {
		return $this->getExtConfigProperty('negativeFillColors');
	}
	
	/**
	 * If you set it to true, column chart will begin new stack. This allows having Clustered and Stacked column/bar chart.
	 * Default value : false
	 * @param boolean $value
	 * @return AmGraph
	 */
	public function setNewStack($value) {
		$this->setExtConfigProperty('newStack', $value);
		return $this;
	}
	
	/**
	 * Get new stack
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getNewStack() {
		return $this->getExtConfigProperty('newStack');
	}
	
	/**
	 * In case you want to have a step line graph without risers, you should set this to true.
	 * Default value : false
	 * @param boolean $value
	 * @return AmGraph
	 */
	public function setNoStepRisers($value) {
		$this->setExtConfigProperty('noStepRisers', $value);
		return $this;
	}
	
	/**
	 * Get no step risers
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getNoStepRisers() {
		return $this->getExtConfigProperty('noStepRisers');
	}
	
	/**
	 * This property can be used by step graphs - you can set how many periods one horizontal line should span.
	 * Default value : 1
	 * @param number $value
	 * @return AmGraph
	 */
	public function setPeriodSpan($value) {
		$this->setExtConfigProperty('periodSpan', $value);
		return $this;
	}
	
	/**
	 * Get period span
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPeriodSpan() {
		return $this->getExtConfigProperty('periodSpan');
	}
	
	/**
	 * Specifies where data points should be placed - on the beginning of the period (day, hour, etc) or in the middle (only when parseDates property of categoryAxis is set to true). 
	 * This setting affects Serial chart only. Possible values are "start", "middle" and "end"
	 * Default value : middle
	 * @param string $value
	 * @return AmGraph
	 */
	public function setPointPosition($value) {
		$this->setExtConfigProperty('pointPosition', $value);
		return $this;
	}
	
	/**
	 * Get point position
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPointPosition() {
		return $this->getExtConfigProperty('pointPosition');
	}
	
	/**
	 * Specifies which value axis the graph will use. Will use the first value axis if not set. You can use reference to the real ValueAxis object or set value axis id.
	 * @param AmValueAxis $axis
	 * @return AmGraph
	 */
	public function setValueAxis($axis) {
		$this->setExtConfigProperty('valueAxis', $axis);
		return $this;
	}
	
	/**
	 * Get value axis
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getValueAxis() {
		return $this->getExtConfigProperty('valueAxis');
	}
	
	public function setTitle($value) {
		$this->setExtConfigProperty('title', $value);
		return $this;
	}
	
	public function getTitle() {
		return $this->getExtConfigProperty('title');
	}
	
	public function setValueField($value) {
		$this->setExtConfigProperty('valueField', $value);
		return $this;
	}
	
	public function getValueField() {
		return $this->getExtConfigProperty('valueField');
	}
	
	public function setType($value) {
		$this->setExtConfigProperty('type', $value);
		return $this;
	}
	
	public function getType() {
		return $this->getExtConfigProperty('type');
	}
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","AmchartConfigObject");
	
		$validProps = array(
			'alphaField',
			'balloonColor',
			'balloonText',
			'behindColumns',
			'bullet',
			'bulletBorderAlpha',
			'bulletBorderColor',
			'bulletBorderThickness',
			'bulletColor',
			'bulletField',
			'bulletOffset',
			'bulletSize',
			'bulletSizeField',
			'clustered',
			'color',
			'colorField',
			'columnWidth',
			'connect',
			'cornerRadiusTop',
			'cursorBulletAlpha',
			'customBullet',
			'customBulletField',
			'customMarker',
			'dashLength',
			'dashLengthField',
			'descriptionField',
			'errorField',
			'fillAlphas',
			'fillColors',
			'fillColorsField',
			'fillToAxis',
			'fontSize',
			'gradientOrientation',
			'hidden',
			'hideBulletsCount',
			'highField',
			'id',
			'includeInMinMax',
			'labelColorField',
			'labelPosition',
			'labelText',
			'legendAlpha',
			'legendColor',
			'legendPeriodValueText',
			'legendValueText',
			'lineAlpha',
			'lineColor',
			'lineColorField',
			'lineThickness',
			'markerType',
			'maxBulletSize',
			'minBulletSize',
			'minDistance',
			'negativeBase',
			'negativeFillAlphas',
			'negativeFillColors',
			'newStack',
			'noStepRisers',
			'periodSpan',
			'pointPosition',
			'valueAxis',
			'title',
			'valueField',
			'type',
		);
		$this->addValidConfigProperties($validProps);
		$this->setExtConfigProperty('configClassName', 'AmCharts.AmGraph');
	}
	
}