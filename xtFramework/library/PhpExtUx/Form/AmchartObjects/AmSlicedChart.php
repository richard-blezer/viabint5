<?php

/**
 * Class AmSlicedChart
 * @see http://docs.amcharts.com/3/javascriptcharts/AmSlicedChart
 * @author radoslav
 *
 */
class AmSlicedChart extends AmChart {
	
	/**
	 * Opacity of all slices.
	 * Default value : 1
	 * @param number $value
	 * @return AmSlicedChart
	 */
	public function setAlpha($value) {
		$this->setExtConfigProperty('alpha', $value);
		return $this;
	}
	
	/**
	 * Get alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getAlpha() {
		return $this->getExtConfigProperty('alpha');
	}
	
	/**
	 * Name of the field in chart's dataProvider which holds slice's alpha.
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setAlphaField($value) {
		$this->setExtConfigProperty('alphaField', $value);
		return $this;
	}
	
	/**
	 * Get alpha field
	 * @return string
	 */
	public function getAlphaField() {
		$this->getExtConfigProperty('alphaField');
	}
	
	/**
	 * Color of the first slice. All the other will be colored with darker or brighter colors.
	 * Default value : #000000
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setBaseColor($value) {
		$this->setExtConfigProperty('baseColor', $value);
		return $this;
	}
	
	/**
	 * Get base color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getBaseColor() {
		return $this->getExtConfigProperty('baseColor');
	}
	
	/**
	 * Lightness increase of each subsequent slice. 
	 * This is only useful if baseColor is set. Use negative values for darker colors. Value range is from -255 to 255.
	 * Default value : 30
	 * @param number $value
	 * @return AmSlicedChart
	 */
	public function setBrightnessStep($value) {
		$this->setExtConfigProperty('brightnessStep', $value);
		return $this;
	}
	
	/**
	 * Get brightness step
	 * @return number
	 */
	public function getBrightnessStep() {
		return $this->getExtConfigProperty('brightnessStep');
	}
	
	/**
	 * Name of the field in chart's dataProvider which holds slice's color.
	 * @param string $value
	 * @return AmSlicedChart
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
	 * Name of the field in chart's dataProvider which holds a string with description.
	 * @param string $value
	 * @return AmSlicedChart
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
	 * Example: [0,10]. Will make slices to be filled with color gradients.
	 * @param array $value
	 * @return AmSlicedChart
	 */
	public function setGradientRatio($value) {
		$this->setExtConfigProperty('gradientRatio', $value);
		return $this;
	}
	
	/**
	 * Get gradient ratio
	 * @return array
	 */
	public function getGradientRatio() {
		return $this->getExtConfigProperty('gradientRatio');
	}
	
	/**
	 * Opacity of the group slice. Value range is 0 - 1.
	 * Default value : 1
	 * @param number $value
	 * @return AmSlicedChart
	 */
	public function setGroupedAlpha($value) {
		$this->setExtConfigProperty('groupedAlpha', $value);
		return $this;
	}
	
	/**
	 * Get grouped alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getGroupedAlpha() {
		return $this->getExtConfigProperty('groupedAlpha');
	}
	
	/**
	 * Color of the group slice. 
	 * The default value is not set - this means the next available color from "colors" array will be used.
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setGroupedColor($value) {
		$this->setExtConfigProperty('groupedColor', $value);
		return $this;
	}
	
	/**
	 * Get grouped color
	 * @return string
	 */
	public function getGroupedColor() {
		return $this->getExtConfigProperty('groupedColor');
	}
	
	/**
	 * Description of the group slice.
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setGroupedDescription($value) {
		$this->setExtConfigProperty('groupedDescription', $value);
		return $this;
	}
	
	/**
	 * Get grouped description
	 * @return string
	 */
	public function getGroupedDescription() {
		return $this->getExtConfigProperty('groupedDescription');
	}
	
	/**
	 * If this is set to true, the group slice will be pulled out when the chart loads.
	 * Default value : false
	 * @param boolean $value
	 * @return AmSlicedChart
	 */
	public function setGroupedPulled($value) {
		$this->setExtConfigProperty('groupedPulled', $value);
		return $this;
	}
	
	/**
	 * Get grouped pulled
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getGroupedPulled() {
		return $this->getExtConfigProperty('groupedPulled');
	}
	
	/**
	 * Title of the group slice.
	 * Default value : Other
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setGroupedTitle($value) {
		$this->setExtConfigProperty('groupedTitle', $value);
		return $this;
	}
	
	/**
	 * Get grouped title
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getGroupedTitle() {
		return $this->getExtConfigProperty('groupedTitle');
	}
	
	/**
	 * If there is more than one slice whose percentage of the pie is less than this number, those slices will be grouped together into one slice. 
	 * This is the "other" slice. It will always be the last slice in a pie.
	 * Default value : 0
	 * @param number $value
	 * @return AmSlicedChart
	 */
	public function setGroupPercent($value) {
		$this->setExtConfigProperty('groupPercent', $value);
		return $this;
	}
	
	/**
	 * Get grouped percent
	 * @return number
	 */
	public function getGroupPercent() {
		return $this->getExtConfigProperty('groupPercent');
	}
	
	/**
	 * Slices with percent less then hideLabelsPercent won't display labels. 
	 * This is useful to avoid cluttering up the chart, if you have a lot of small slices. 0 means all labels will be shown.
	 * Default value : 0
	 * @param number $value
	 * @return AmSlicedChart
	 */
	public function setHideLabelsPercent($value) {
		$this->setExtConfigProperty('hideLabelsPercent', $value);
		return $this;
	}
	
	/**
	 * Get hide labels percent
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getHideLabelsPercent() {
		return $this->getExtConfigProperty('hideLabelsPercent');
	}
	
	/**
	 * Opacity of a hovered slice. Value range is 0 - 1.
	 * Default value : 1
	 * @param number $value
	 * @return AmSlicedChart
	 */
	public function setHoverAlpha($value) {
		$this->setExtConfigProperty('hoverAlpha', $value);
		return $this;
	}
	
	/**
	 * Get hover alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getHoverAlpha() {
		return $this->getExtConfigProperty('hoverAlpha');
	}
	
	/**
	 * Specifies whether data labels are visible.
	 * Default value : true
	 * @param boolean $value
	 * @return AmSlicedChart
	 */
	public function setLabelsEnabled($value) {
		$this->setExtConfigProperty('labelsEnabled', $value);
		return $this;
	}
	
	/**
	 * Get labels is enabled
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getLabelsEnabled() {
		return $this->getExtConfigProperty('labelsEnabled');
	}
	
	/**
	 * Label tick opacity. Value range is 0 - 1.
	 * Default value : 0.2
	 * @param number $value
	 * @return AmSlicedChart
	 */
	public function setLabelTickAlpha($value) {
		$this->setExtConfigProperty('labelTickAlpha', $value);
		return $this;
	}
	
	/**
	 * Get label tick alpha
	 * @return number
	 */
	public function getLabelTickAlpha() {
		return $this->getExtConfigProperty('labelTickAlpha');
	}
	
	/**
	 * Label tick color.
	 * Default value : #000000
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setLabelTickColor($value) {
		$this->setExtConfigProperty('labelTickColor', $value);
		return $this;
	}
	
	/**
	 * Get label tick color
	 * @return string
	 */
	public function getLabelTickColor() {
		return $this->getExtConfigProperty('labelTickColor');
	}
	
	/**
	 * Bottom margin of the chart.
	 * Default value : 10
	 * @param number $value
	 * @return AmSlicedChart
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
	 * Left margin of the chart.
	 * Default value : 0
	 * @param number $value
	 * @return AmSlicedChart
	 */
	public function setMarginLeft($value) {
		$this->setExtConfigProperty('marginLeft', $value);
		return $this;
	}
	
	/**
	 * Get margin left
	 * @return number
	 */
	public function getMarginLeft() {
		return $this->getExtConfigProperty('marginLeft');
	}
	
	/**
	 * Right margin of the chart.
	 * Default value : 0
	 * @param number $value
	 * @return AmSlicedChart
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
	 * Top margin of the chart.
	 * Default value : 10
	 * @param number $value
	 * @return AmSlicedChart
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
	 * If width of the label is bigger than maxLabelWidth, it will be wrapped.
	 * Default value : 200
	 * @param number $value
	 * @return AmSlicedChart
	 */
	public function setMaxLabelWidth($value) {
		$this->setExtConfigProperty('maxLabelWidth', $value);
		return $this;
	}
	
	/**
	 * Get max label width
	 * @return number
	 */
	public function getMaxLabelWidth() {
		return $this->getExtConfigProperty('maxLabelWidth');
	}
	
	/**
	 * Outline opacity. Value range is 0 - 1.
	 * Default value : 0
	 * @param number $value
	 * @return AmSlicedChart
	 */
	public function setOutlineAlpha($value) {
		$this->setExtConfigProperty('outlineAlpha', $value);
		return $this;
	}
	
	/**
	 * Get outline alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getOutlineAlpha() {
		return $this->getExtConfigProperty('outlineAlpha');
	}
	
	/**
	 * Outline color.
	 * Default value : #ffffff
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setOutlineColor($value) {
		$this->setExtConfigProperty('outlineColor', $value);
		return $this;
	}
	
	/**
	 * Get outline color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getOutlineColor() {
		return $this->getExtConfigProperty('outlineColor');
	}
	
	/**
	 * Pie outline thickness.
	 * Default value : 1
	 * @param number $value
	 * @return AmSlicedChart
	 */
	public function setOutlineThickness($value) {
		$this->setExtConfigProperty('outlineThickness', $value);
		return $this;
	}
	
	/**
	 * Get outline thickness
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getOutlineThickness() {
		return $this->getExtConfigProperty('outlineThickness');
	}
	
	/**
	 * Field name in your data provider which holds pattern information. 
	 * Value of pattern should be object with url, width, height of an image, optionally it might have x, y, randomX and randomY values. 
	 * For example: {"url":"../amcharts/patterns/black/pattern1.png", "width":4, "height":4}. 
	 * Check amcharts/patterns folder for some patterns. You can create your own patterns and use them. 
	 * Note, x, y, randomX and randomY properties won't work with IE8 and older. 3D bar/Pie charts won't work properly with patterns.
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setPatternField($value) {
		$this->setExtConfigProperty('patternField', $value);
		return $this;
	}
	
	/**
	 * Get pattern field
	 * @return string
	 */
	public function getPatternField() {
		return $this->getExtConfigProperty('patternField');
	}
	
	/**
	 * Name of the field in chart's dataProvider which holds a boolean value telling the chart whether this slice must be pulled or not.
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setPulledField($value) {
		$this->setExtConfigProperty('pulledField', $value);
		return $this;
	}
	
	/**
	 * Get pulled field
	 * @return string
	 */
	public function getPulledField() {
		return $this->getExtConfigProperty('pulledField');
	}
	
	/**
	 * Pull out duration, in seconds.
	 * Default value : 1
	 * @param number $value
	 * @return AmSlicedChart
	 */
	public function setPullOutDuration($value) {
		$this->setExtConfigProperty('pullOutDuration', $value);
		return $this;
	}
	
	/**
	 * Get pull out duration
	 * @return number
	 */
	public function getPullOutDuration() {
		return $this->getExtConfigProperty('pullOutDuration');
	}
	
	/**
	 * Pull out effect. Possible values are: easeOutSine, easeInSine, elastic, bounce
	 * Default value : bounce
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setPullOutEffect($value) {
		$this->setExtConfigProperty('pullOutEffect', $value);
		return $this;
	}
	
	/**
	 * Get pull out effect
	 * @return string
	 */
	public function getPullOutEffect() {
		return $this->getExtConfigProperty('pullOutEffect');
	}
	
	/**
	 * If this is set to true, only one slice can be pulled out at a time. If the viewer clicks on a slice, any other pulled-out slice will be pulled in.
	 * Default value : false
	 * @param boolean $value
	 * @return AmSlicedChart
	 */
	public function setPullOutOnlyOne($value) {
		$this->setExtConfigProperty('pullOutOnlyOne', $value);
		return $this;
	}
	
	/**
	 * Get pull out only one
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPullOutOnlyOne() {
		return $this->getExtConfigProperty('pullOutOnlyOne');
	}
	
	/**
	 * Specifies whether the animation should be sequenced or all slices should appear at once.
	 * Default value : true
	 * @param boolean $value
	 * @return AmSlicedChart
	 */
	public function setSequencedAnimation($value) {
		$this->setExtConfigProperty('sequencedAnimation', $value);
		return $this;
	}
	
	/**
	 * Get sequenced animation
	 * @return booelan
	 */
	public function getSequencedAnimation() {
		return $this->getExtConfigProperty('sequencedAnimation');
	}
	
	/**
	 * Initial opacity of all slices. Slices will fade in from startAlpha.
	 * Default value : 0
	 * @param number $value
	 * @return AmSlicedChart
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
	 * @return AmPieChart
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
	 * Default value : bounce
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setStartEffect($value) {
		$this->setExtConfigProperty('startEffect', $value);
		return $this;
	}
	
	/**
	 * Get start effect
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getStartEffect() {
		return $this->getExtConfigProperty('startEffect');
	}
	
	/**
	 * Name of the field in chart's dataProvider which holds slice's title.
	 * @param String $value
	 * @return AmPieChart
	 */
	public function setTitleField($value) {
		$this->setExtConfigProperty('titleField', $value);
		return $this;
	}
	
	/**
	 * Get title field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getTitleField() {
		return $this->getExtConfigProperty('titleField');
	}
	
	/**
	 * Name of the field in chart's dataProvider which holds url which would be accessed if the user clicks on a slice.
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setUrlField($value) {
		$this->setExtConfigProperty('urlField', $value);
		return $this;
	}
	
	/**
	 * Get url field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getUrlField() {
		return $this->getExtConfigProperty('urlField');
	}
	
	/**
	 * If url is specified for a slice, it will be opened when user clicks on it. urlTarget specifies target of this url. 
	 * Use _blank if you want url to be opened in a new window.
	 * Default string : _self
	 * @param unknown $value
	 * @return AmSlicedChart
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
	 * Name of the field in chart's dataProvider which holds slice's value.
	 * @param string $value
	 * @return AmPieChart
	 */
	public function setValueField($value) {
		$this->setExtConfigProperty('valueField', $value);
		return $this;
	}
	
	/**
	 * Get value field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getValueField() {
		return $this->getExtConfigProperty('valueField');
	}
	
	/**
	 * Name of the field in chart's dataProvider which holds boolean variable defining whether this data item should have an entry in the legend.
	 * @param string $value
	 * @return AmSlicedChart
	 */
	public function setVisibleInLegendField($value) {
		$this->setExtConfigProperty('visibleInLegendField', $value);
		return $this;
	}
	
	/**
	 * Get visible in legend field
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getVisibleInLegendField() {
		return $this->getExtConfigProperty('visibleInLegendField');
	}
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","amchart");
	
		$validProps = array(
			'alpha',
			'alphaField',
			'baseColor',
			'brightnessStep',
			'colorField',
			'descriptionField',
			'gradientRatio',
			'groupedAlpha',
			'groupedColor',
			'groupedDescription',
			'groupedPulled',
			'groupedTitle',
			'groupPercent',
			'hideLabelsPercent',
			'hoverAlpha',
			'labelsEnabled',
			'labelTickAlpha',
			'labelTickColor',
			'marginBottom',
			'marginLeft',
			'marginRight',
			'marginTop',
			'maxLabelWidth',
			'outlineAlpha',
			'outlineColor',
			'outlineThickness',
			'patternField',
			'pulledField',
			'pullOutDuration',
			'pullOutEffect',
			'pullOutOnlyOne',
			'sequencedAnimation',
			'startAlpha',
			'startDuration',
			'startEffect',
			'titleField',
			'urlField',
			'urlTarget',
			'valueField',
			'visibleInLegendField',
		);
		$this->addValidConfigProperties($validProps);
	}
	
}