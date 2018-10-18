<?php

/**
 * AmChartLegend class
 * @see http://docs.amcharts.com/3/javascriptcharts/AmLegend
 * @author radoslav
 *
 */
class AmChartLegend extends AmChartConfigObject {
	
	/**
	 * Alignment of legend entries. Possible values are: "left", "center", "right".
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setAlign($value) {
		$this->setExtConfigProperty('align', $value);
		return $this;
	}
	
	/**
	 * Get align
	 * @return string
	 */
	public function getAlign() {
		return $this->getExtConfigProperty('align');
	}
	
	/**
	 * Used if chart is Serial or XY. In case true, margins of the legend are adjusted and made equal to chart's margins.
	 * Default value : true
	 * @param boolean $value
	 * @return AmChartLegend
	 */
	public function setAutoMargins($value) {
		$this->setExtConfigProperty('autoMargins', $value);
		return $this;
	}
	
	/**
	 * Get auto margins
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getAutoMargins() {
		return $this->getExtConfigProperty('autoMargins');
	}
	
	/**
	 * Opacity of legend's background. Value range is 0 - 1
	 * Default value : 0
	 * @param number $value
	 * @return AmChartLegend
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
	 * Background color. You should set backgroundAlpha to >0 vallue in order background to be visible.
	 * Default value : #ffffff
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setBackgroundColor($value) {
		$this->setExtConfigProperty('backgroundColor', $value);
		return $this;
	}
	
	/**
	 * Get background color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBackgroundColor() {
		return $this->getExtConfigProperty('backgroundColor');
	}
	
	/**
	 * Opacity of chart's border. Value range is 0 - 1.
	 * Default value : 0
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setBorderAlpha($value) {
		$this->setExtConfigProperty('borderAlpha', $value);
		return $this;
	}
	
	/**
	 * Get border alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBorderAlpha() {
		return $this->getExtConfigProperty('borderAlpha');
	}
	
	/**
	 * Color of legend's border. You should set borderAlpha >0 in order border to be visible.
	 * Default value : #000000
	 * @param string $value
	 * @return AmChartLegend
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
	 * In case legend position is set to "absolute", you can set distance from bottom of the chart, in pixels.
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setBottom($value) {
		$this->setExtConfigProperty('bottom', $value);
		return $this;
	}
	
	/**
	 * Get bottom
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBottom() {
		return $this->getExtConfigProperty('bottom');
	}
	
	/**
	 * Text color.
	 * Default value : #000000
	 * @param string $value
	 * @return AmChartLegend
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
	 * You can set id of a div or a reference to div object in case you want the legend to be placed in a separate container.
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setDivId($value) {
		$this->setExtConfigProperty('divId', $value);
		return $this;
	}
	
	/**
	 * Get div id
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getDivId() {
		return $this->getExtConfigProperty('divId');
	}
	
	/**
	 * Specifies if each of legend entry should be equal to the most wide entry. Won't look good if legend has more than one line.
	 * Default value : true
	 * @param boolean $value
	 * @return AmChartLegend
	 */
	public function setEqualWidths($value) {
		$this->setExtConfigProperty('equalWidths', $value);
		return $this;
	}
	
	/**
	 * Get equal widths
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getEqualWidths() {
		return $this->getExtConfigProperty('equalWidths');
	}
	
	/**
	 * Font size.
	 * Default value : 11
	 * @param number $value
	 * @return AmChartLegend
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
	 * Horizontal space between legend item and left/right border.
	 * Default value : 0
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setHorizontalGap($value) {
		$this->setExtConfigProperty('horizontalGap', $value);
		return $this;
	}
	
	/**
	 * Get horizontal gap
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getHorizontalGap() {
		return $this->getExtConfigProperty('horizontalGap');
	}
	
	/**
	 * The text which will be displayed in the legend. Tag [[title]] will be replaced with the title of the graph.
	 * Default value : [[title]]
	 * @param string $value
	 * @return AmChartLegend
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
	 * If width of the label is bigger than labelWidth, it will be wrapped.
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setLabelWidth($value) {
		$this->setExtConfigProperty('labelWidth', $value);
		return $this;
	}
	
	/**
	 * Get label width
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLabelWidth() {
		return $this->getExtConfigProperty('labelWidth');
	}
	
	/**
	 * In case legend position is set to "absolute", you can set distance from left side of the chart, in pixels.
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setLeft($value) {
		$this->setExtConfigProperty('left', $value);
		return $this;
	}
	
	/**
	 * Get left
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLeft() {
		return $this->getExtConfigProperty('left');
	}
	
	/**
	 * Bottom margin.
	 * Default value : 0
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setMarginBottom($value) {
		$this->setExtConfigProperty('marginBottom', $value);
		return $this;
	}
	
	/**
	 * Get margin bottom
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMarginBottom() {
		return $this->getExtConfigProperty('marginBottom');
	}
	
	/**
	 * Left margin. This property will be ignored if chart is Serial or XY and autoMargins property of the legend is true (default).
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setMarginLeft($value) {
		$this->setExtConfigProperty('marginLeft', $value);
		return $this;
	}
	
	/**
	 * Get margin left
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMarginLeft() {
		return $this->getExtConfigProperty('marginLeft');
	}
	
	/**
	 * Right margin. This property will be ignored if chart is Serial or XY and autoMargins property of the legend is true (default).
	 * Default value : 20
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setMarginRight($value) {
		$this->setExtConfigProperty('marginRight', $value);
		return $this;
	}
	
	/**
	 * Get margin right
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMarginRight() {
		return $this->getExtConfigProperty('marginRight');
	}
	
	/**
	 * Top margin.
	 * Default value : 0
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setMarginTop($value) {
		$this->setExtConfigProperty('marginTop', $value);
		return $this;
	}
	
	/**
	 * Get margin top
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMarginTop() {
		return $this->getExtConfigProperty('marginTop');
	}
	
	/**
	 * Marker border opacity.
	 * Default value : 1
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setMarkerBorderAlpha($value) {
		$this->setExtConfigProperty('markerBorderAlpha', $value);
		return $this;
	}
	
	/**
	 * Get marker border alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMarkerBorderAlpha() {
		return $this->getExtConfigProperty('markerBorderAlpha');
	}
	
	/**
	 * Marker border color. If not set, will use the same color as marker.
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setMarkerBorderColor($value) {
		$this->setExtConfigProperty('markerBorderColor', $value);
		return $this;
	}
	
	/**
	 * Get marker border color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMarkerBorderColor() {
		return $this->getExtConfigProperty('markerBorderColor');
	}
	
	/**
	 * Thickness of the legend border. The default value (0) means the line will be a "hairline" (1 px). 
	 * In case marker type is line, this style will be used for line thickness.
	 * Default value : 1
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setMarkerBorderThickness($value) {
		$this->setExtConfigProperty('markerBorderThickness', $value);
		return $this;
	}
	
	/**
	 * Get marker border thickness
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMarkerBorderThickness() {
		return $this->getExtConfigProperty('markerBorderThickness');
	}
	
	/**
	 * The color of the disabled marker (when the graph is hidden).
	 * Default value : #aab3b3
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setMarkerDisabledColor($value) {
		$this->setExtConfigProperty('markerDisabledColor', $value);
		return $this;
	}
	
	/**
	 * Get marker disaled color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMarkerDisabledColor() {
		return $this->getExtConfigProperty('markerDisabledColor');
	}
	
	/**
	 * Space between legend marker and legend text, in pixels.
	 * Default value : 5
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setMarkerLabelGap($value) {
		$this->setExtConfigProperty('markerLabelGap', $value);
		return $this;
	}
	
	/**
	 * Get marker label gap
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMarkerLabelGap() {
		return $this->getExtConfigProperty('markerLabelGap');
	}
	
	/**
	 * Size of the legend marker (key).
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setMarkerSize($value) {
		$this->setExtConfigProperty('markerSize', $value);
		return $this;
	}
	
	/**
	 * Get marker size
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMarkerSize() {
		return $this->getExtConfigProperty('markerSize');
	}
	
	/**
	 * Shape of the legend marker (key). Possible values are: "square", "circle", "line", "dashedLine", "triangleUp", "triangleDown", "bubble", "none".
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setMarkerType($value) {
		$this->setExtConfigProperty('markerType', $value);
		return $this;
	}
	
	/**
	 * Get marker type
	 * @return string
	 */
	public function getMarkerType() {
		return $this->getExtConfigProperty('markerType');
	}
	
	/**
	 * Maximum number of columns in the legend. If Legend's position is set to "right" or "left", maxColumns is automatically set to 1.
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setMaxColumns($value) {
		$this->setExtConfigProperty('maxColumns', $value);
		return $this;
	}
	
	/**
	 * Get max columns
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMaxColumns() {
		return $this->getExtConfigProperty('maxColumns');
	}
	
	/**
	 * The text which will be displayed in the value portion of the legend when user is not hovering above any data point. 
	 * The tags should be made out of two parts - the name of a field (value / open / close / high / low) and 
	 * the value of the period you want to be show - open / close / high / low / sum / average / count. 
	 * For example: [[value.sum]] means that sum of all data points of value field in the selected period will be displayed.
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setPeriodValueText($value) {
		$this->setExtConfigProperty('periodValueText', $value);
		return $this;
	}
	
	/**
	 * get period value text
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPeriodValueText() {
		return $this->getExtConfigProperty('periodValueText');
	}
	
	/**
	 * Position of a legend. Possible values are: "bottom", "top", "left", "right" and "absolute". In case "absolute", 
	 * you should set left and top properties too. (this setting is ignored in Stock charts). 
	 * In case legend is used with AmMap, position is set to "absolute" automatically.
	 * Default value : bottom
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setPosition($value) {
		$this->setExtConfigProperty('position', $value);
		return $this;
	}
	
	/**
	 * Get position
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPosition() {
		return $this->getExtConfigProperty('position');
	}
	
	/**
	 * Specifies whether legend entries should be placed in reversed order.
	 * Default value : false
	 * @param boolean $value
	 * @return AmChartLegend
	 */
	public function setReversedOrder($value) {
		$this->setExtConfigProperty('reversedOrder', $value);
		return $this;
	}
	
	/**
	 * Get reverse order
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getReversedOrder() {
		return $this->getExtConfigProperty('reversedOrder');
	}
	
	/**
	 * In case legend position is set to "absolute", you can set distance from right side of the chart, in pixels.
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setRight($value) {
		$this->setExtConfigProperty('right', $value);
		return $this;
	}
	
	public function getRight() {
		return $this->getExtConfigProperty('right');
	}
	
	/**
	 * Legend item text color on roll-over.
	 * Default value : #cc0000
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setRollOverColor($value) {
		$this->setExtConfigProperty('rollOverColor', $value);
		return $this;
	}
	
	/**
	 * Get roll over color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getRollOverColor() {
		return $this->getExtConfigProperty('rollOverColor');
	}
	
	/**
	 * When you roll-over the legend entry, all other graphs can reduce their opacity, so that the graph you rolled-over would be distinguished. 
	 * This style specifies the opacity of the graphs.
	 * Default value : 1
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setRollOverGraphAlpha($value) {
		$this->setExtConfigProperty('rollOverGraphAlpha', $value);
		return $this;
	}
	
	/**
	 * Get roll over graph alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getRollOverGraphAlpha() {
		return $this->getExtConfigProperty('rollOverGraphAlpha');
	}
	
	/**
	 * You can use this property to turn all the legend entries off.
	 * Default value : true
	 * @param boolean $value
	 * @return AmChartLegend
	 */
	public function setShowEntries($value) {
		$this->setExtConfigProperty('showEntries', $value);
		return $this;
	}
	
	/**
	 * Get show entries
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getShowEntries() {
		return $this->getExtConfigProperty('showEntries');
	}
	
	/**
	 * Horizontal space between legend items, in pixels.
	 * Default value : 10
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setSpacing($value) {
		$this->setExtConfigProperty('spacing', $value);
		return $this;
	}
	
	/**
	 * Get spacing
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getSpacing() {
		return $this->getExtConfigProperty('spacing');
	}
	
	/**
	 * Whether showing/hiding of graphs by clicking on the legend marker is enabled or not. 
	 * In case legend is used with AmMap, this is set to false automatically.
	 * Default value : true
	 * @param boolean $value
	 * @return AmChartLegend
	 */
	public function setSwitchable($value) {
		$this->setExtConfigProperty('switchable', $value);
		return $this;
	}
	
	/**
	 * Get switchable
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getSwitchable() {
		return $this->getExtConfigProperty('switchable');
	}
	
	/**
	 * Legend switch color.
	 * Default value : #ffffff
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setSwitchColor($value) {
		$this->setExtConfigProperty('switchColor', $value);
		return $this;
	}
	
	/**
	 * Get switch color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getSwitchColor() {
		return $this->getExtConfigProperty('switchColor');
	}
	
	/**
	 * Legend switch type (in case the legend is switchable). Possible values are "x" and "v".
	 * Default value : x
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setSwitchType($value) {
		$this->setExtConfigProperty('switchType', $value);
		return $this;
	}
	
	/**
	 * Get swith type
	 * @return string
	 */
	public function getSwitchType() {
		return $this->getExtConfigProperty('switchType');
	}
	
	/**
	 * If true, clicking on the text will show/hide balloon of the graph. Otherwise it will show/hide graph/slice, if switchable is set to true.
	 * Default value : false
	 * @param boolean $value
	 * @return AmChartLegend
	 */
	public function setTextClickEnabled($value) {
		$this->setExtConfigProperty('textClickEnabled', $value);
		return $this;
	}
	
	/**
	 * Get text clock enabled
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getTextClickEnabled() {
		return $this->getExtConfigProperty('textClickEnabled');
	}
	
	/**
	 * In case legend position is set to "absolute", you can set distance from top of the chart, in pixels.
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setTop($value) {
		$this->setExtConfigProperty('top', $value);
		return $this;
	}
	
	/**
	 * Get top
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getTop() {
		return $this->getExtConfigProperty('top');
	}
	
	/**
	 * Legend markers can mirror graph’s settings, displaying a line and a real bullet as in the graph itself. Set this property to true if you want to enable this feature.
	 * Default value : false
	 * @param boolean $value
	 * @return AmChartLegend
	 */
	public function setUseGraphSettings($value) {
		$this->setExtConfigProperty('useGraphSettings', $value);
		return $this;
	}
	
	/**
	 * Get use graph settings
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getUseGraphSettings() {
		return $this->getExtConfigProperty('useGraphSettings');
	}
	
	/**
	 * Labels will use marker color if you set this to true.
	 * Default value false
	 * @param boolean $value
	 * @return AmChartLegend
	 */
	public function setUseMarkerColorForLabels($value) {
		$this->setExtConfigProperty('useMarkerColorForLabels', $value);
		return $this;
	}
	
	/**
	 * Get use marker color for labels
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getUseMarkerColorForLabels() {
		return $this->getExtConfigProperty('useMarkerColorForLabels');
	}
	
	/**
	 * Specifies if legend values should be use same color as corresponding markers.
	 * Default value : false
	 * @param boolean $value
	 * @return AmChartLegend
	 */
	public function setUseMarkerColorForValues($value) {
		$this->setExtConfigProperty('useMarkerColorForValues', $value);
		return $this;
	}
	
	/**
	 * Get use marker color for value
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getUseMarkerColorForValues() {
		return $this->getExtConfigProperty('useMarkerColorForValues');
	}
	
	/**
	 * Alignment of the value text. Possible values are "left" and "right".
	 * Default value : right
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setValueAlign($value) {
		$this->setExtConfigProperty('valueAlign', $value);
		return $this;
	}
	
	/**
	 * Get value align
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getValueAlign() {
		return $this->getExtConfigProperty('valueAlign');
	}
	
	/**
	 * The text which will be displayed in the value portion of the legend. 
	 * You can use tags like [[value]], [[open]], [[high]], [[low]], [[close]], [[percents]], [[description]].
	 * Default value : [[value]]
	 * @param string $value
	 * @return AmChartLegend
	 */
	public function setValueText($value) {
		$this->setExtConfigProperty('valueText', $value);
		return $this;
	}
	
	/**
	 * Ge value text
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getValueText() {
		return $this->getExtConfigProperty('valueText');
	}
	
	/**
	 * Width of the value text.
	 * Default value : 50
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setValueWidth($value) {
		$this->setExtConfigProperty('valueWidth', $value);
		return $this;
	}
	
	/**
	 * Get value width
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getValueWidth() {
		return $this->getExtConfigProperty('valueWidth');
	}
	
	/**
	 * Vertical space between legend items also between legend border and first and last legend row.
	 * Default value : 10
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setVerticalGap($value) {
		$this->setExtConfigProperty('verticalGap', $value);
		return $this;
	}
	
	/**
	 * Get vertical gap
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getVerticalGap() {
		return $this->getExtConfigProperty('verticalGap');
	}
	
	/**
	 * Width of a legend, when position is set to absolute.
	 * @param number $value
	 * @return AmChartLegend
	 */
	public function setWidth($value) {
		$this->setExtConfigProperty('width', $value);
		return $this;
	}
	
	/**
	 * Get width
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getWidth() {
		return $this->getExtConfigProperty('width');
	}
	
	public function __construct() {
		parent::__construct();
		
		$validProps = array(
			'align',
			'autoMargins',
			'backgroundAlpha',
			'backgroundColor',
			'borderAlpha',
			'borderColor',
			'bottom',
			'color',
			'divId',
			'equalWidths',
			'fontSize',
			'horizontalGap',
			'labelText',
			'labelWidth',
			'left',
			'marginBottom',
			'marginLeft',
			'marginRight',
			'marginTop',
			'markerBorderAlpha',
			'markerBorderColor',
			'markerBorderThickness',
			'markerDisabledColor',
			'markerLabelGap',
			'markerSize',
			'markerType',
			'maxColumns',
			'periodValueText',
			'position',
			'reversedOrder',
			'right',
			'rollOverColor',
			'rollOverGraphAlpha',
			'showEntries',
			'spacing',
			'switchable',
			'switchColor',
			'switchType',
			'textClickEnabled',
			'top',
			'useGraphSettings',
			'useMarkerColorForLabels',
			'useMarkerColorForValues',
			'valueAlign',
			'valueText',
			'valueWidth',
			'verticalGap',
			'width',
		);
		$this->addValidConfigProperties($validProps);
		$this->setExtConfigProperty('configClassName', 'AmCharts.AmLegend');
	}
	
}