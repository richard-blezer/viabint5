<?php

/**
 * Class AmRectangularChart
 * @see http://docs.amcharts.com/3/javascriptcharts/AmRectangularChart
 * @author radoslav
 *
 */
class AmRectangularChart extends AmCoordinateChart {
	
	/**
	 * The angle of the 3D part of plot area. This creates a 3D effect (if the "depth3D" is > 0).
	 * Default value : 0
	 * @param number $value
	 * @return AmRectangularChart
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
	 * Space left from axis labels/title to the chart's outside border, if autoMargins set to true.
	 * Default value : 10
	 * @param number $value
	 * @return AmRectangularChart
	 */
	public function setAutoMarginOffset($value) {
		$this->setExtConfigProperty('autoMarginOffset', $value);
		return $this;
	}
	
	/**
	 * Get auto margin offset
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getAutoMarginOffset() {
		return $this->getExtConfigProperty('autoMarginOffset');
	}
	
	/**
	 * Specifies if margins of a chart should be calculated automatically so that labels of axes would fit. 
	 * The chart will adjust only margins with axes. 
	 * Other margins will use values set with marginRight, marginTop, marginLeft and marginBottom properties.
	 * Default value : true
	 * @param boolean $value
	 * @return AmRectangularChart
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
	 * The depth of the 3D part of plot area. This creates a 3D effect (if the "angle" is > 0).
	 * Default value : 0
	 * @param number $value
	 * @return AmRectangularChart
	 */
	public function setDepth3D($value) {
		$this->setExtConfigProperty('depth3D', $value);
		return $this;
	}
	
	/**
	 * Get depth 3d
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getDepth3D() {
		return $this->getExtConfigProperty('depth3D');
	}
	
	/**
	 * Number of pixels between the container's bottom border and plot area. 
	 * This space can be used for bottom axis' values. 
	 * If autoMargin is true and bottom side has axis, this property is ignored.
	 * Default value : 20
	 * @param number $value
	 * @return AmRectangularChart
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
	 * Number of pixels between the container's left border and plot area. 
	 * This space can be used for left axis' values. 
	 * If autoMargin is true and left side has axis, this property is ignored.
	 * Default value : 20
	 * @param number $value
	 * @return AmRectangularChart
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
	 * Number of pixels between the container's right border and plot area. 
	 * This space can be used for Right axis' values. 
	 * If autoMargin is true and right side has axis, this property is ignored.
	 * Default value : 20
	 * @param number $value
	 * @return AmRectangularChart
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
	 * Number of pixels between the container's top border and plot area. 
	 * This space can be used for top axis' values. 
	 * If autoMargin is true and top side has axis, this property is ignored.
	 * Default value : 20
	 * @param number $value
	 * @return AmRectangularChart
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
	 * The opacity of plot area's border. Value range is 0 - 1.
	 * Default value : 0
	 * @param number $value
	 * @return AmRectangularChart
	 */
	public function setPlotAreaBorderAlpha($value) {
		$this->setExtConfigProperty('plotAreaBorderAlpha', $value);
		return $this;
	}
	
	/**
	 * Get plot area border alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPlotAreaBorderAlpha() {
		return $this->getExtConfigProperty('plotAreaBorderAlpha');
	}
	
	/**
	 * The color of the plot area's border. 
	 * Note, the it is invisible by default, as plotAreaBorderAlpha default value is 0. 
	 * Set it to a value higher than 0 to make it visible.
	 * Default value : #000000
	 * @param string $value
	 * @return AmRectangularChart
	 */
	public function setPlotAreaBorderColor($value) {
		$this->setExtConfigProperty('plotAreaBorderColor', $value);
		return $this;
	}
	
	/**
	 * Get plot area border color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPlotAreaBorderColor() {
		return $this->getExtConfigProperty('plotAreaBorderColor');
	}
	
	/**
	 * Opacity of plot area. Plural form is used to keep the same property names as our Flex charts'. 
	 * Flex charts can accept array of numbers to generate gradients. 
	 * Although you can set array here, only first value of this array will be used.
	 * Default value : 0
	 * @param number $value
	 * @return AmRectangularChart
	 */
	public function setPlotAreaFillAlphas($value) {
		$this->setExtConfigProperty('plotAreaFillAlphas', $value);
		return $this;
	}
	
	/**
	 * Get plot area fill alphas
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPlotAreaFillAlphas() {
		return $this->getExtConfigProperty('plotAreaFillAlphas');
	}
	
	/**
	 * You can set both one color if you need a solid color or array of colors to generate gradients, for example: ["#000000", "#0000CC"]
	 * Default value : #ffffff
	 * @param string $value
	 * @return AmRectangularChart
	 */
	public function setPlotAreaFillColors($value) {
		$this->setExtConfigProperty('plotAreaFillColors', $value);
		return $this;
	}
	
	/**
	 * Get plot area fill colors
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPlotAreaFillColors() {
		return $this->getExtConfigProperty('plotAreaFillColors');
	}
	
	/**
	 * If you are using gradients to fill the plot area, you can use this property to set gradient angle. 
	 * The only allowed values are horizontal and vertical: 0, 90, 180, 270.
	 * Default value : 0
	 * @param number $value
	 * @return AmRectangularChart
	 */
	public function setPlotAreaGradientAngle($value) {
		$this->setExtConfigProperty('plotAreaGradientAngle', $value);
		return $this;
	}
	
	/**
	 * Get plot area gradient angle
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getPlotAreaGradientAngle() {
		return $this->getExtConfigProperty('plotAreaGradientAngle');
	}
	
	/**
	 * Opacity of zoom-out button background.
	 * Default value : 0
	 * @param number $value
	 * @return AmRectangularChart
	 */
	public function setZoomOutButtonAlpha($value) {
		$this->setExtConfigProperty('zoomOutButtonAlpha', $value);
		return $this;
	}
	
	/**
	 * Get zoom out button alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getZoomOutButtonAlpha() {
		return $this->getExtConfigProperty('zoomOutButtonAlpha');
	}
	
	/**
	 * Zoom-out button background color.
	 * Default value : #e5e5e5
	 * @param string $value
	 * @return AmRectangularChart
	 */
	public function setZoomOutButtonColor($value) {
		$this->setExtConfigProperty('zoomOutButtonColor', $value);
		return $this;
	}
	
	/**
	 * Get zoom out button color
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getZoomOutButtonColor() {
		return $this->getExtConfigProperty('zoomOutButtonColor');
	}
	
	/**
	 * Name of zoom-out button image. 
	 * In the images folder there is another lens image, called lensWhite.png. 
	 * You might want to have white lens when background is dark. Or you can simply use your own image.
	 * Default value : lens.png
	 * @param string $value
	 * @return AmRectangularChart
	 */
	public function setZoomOutButtonImage($value) {
		return $this->setExtConfigProperty('zoomOutButtonImage', $value);
		return $this;
	}
	
	/**
	 * Get zoom out button image
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getZoomOutButtonImage() {
		return $this->getExtConfigProperty('zoomOutButtonImage');
	}
	
	/**
	 * Size of zoom-out button image.
	 * Default value : 17
	 * @param number $value
	 * @return AmRectangularChart
	 */
	public function setZoomOutButtonImageSize($value) {
		$this->setExtConfigProperty('zoomOutButtonImageSize', $value);
		return $this;
	}
	
	/**
	 * Get zoom out button image size
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getZoomOutButtonImageSize() {
		return $this->getExtConfigProperty('zoomOutButtonImageSize');
	}
	
	/**
	 * Padding around the text and image.
	 * Default value : 8
	 * @param number $value
	 * @return AmRectangularChart
	 */
	public function setZoomOutButtonPadding($value) {
		$this->setExtConfigProperty('zoomOutButtonPadding', $value);
		return $this;
	}
	
	/**
	 * Get zoom out button padding
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getZoomOutButtonPadding() {
		return $this->getExtConfigProperty('zoomOutButtonPadding');
	}
	
	/**
	 * Opacity of zoom-out button background when mouse is over it.
	 * Default value : 1
	 * @param number $value
	 * @return AmRectangularChart
	 */
	public function setZoomOutButtonRollOverAlpha($value) {
		$this->setExtConfigProperty('zoomOutButtonRollOverAlpha', $value);
		return $this;
	}
	
	/**
	 * Get zoom out button roll over alpha
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getZoomOutButtonRollOverAlpha() {
		return $this->getExtConfigProperty('zoomOutButtonRollOverAlpha');
	}
	
	/**
	 * Text in the zoom-out button.
	 * Default value : Show all
	 * @param string $value
	 * @return AmRectangularChart
	 */
	public function setZoomOutText($value) {
		$this->setExtConfigProperty('zoomOutText', $value);
		return $this;
	}
	
	/**
	 * Get zoom out text
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getZoomOutText() {
		return $this->getExtConfigProperty('zoomOutText');
	}
	
	/*-------------------------------- METHODS-------------------------------*/
	public function addChartCursor($value) {
		$this->addMethodCall('addChartCursor', $value);
		return $this;
	}
	
	public function addChartScrollbar($value) {
		$this->addMethodCall('addChartScrollbar', $value);
		return $this;
	}
	/*---------------------------------END METHODS --------------------------*/
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","amchart");
	
		$validProps = array(
			'angle',
			'autoMarginOffset',
			'autoMargins',
			'depth3D',
			'marginBottom',
			'marginLeft',
			'marginRight',
			'marginTop',
			'plotAreaBorderAlpha',
			'plotAreaBorderColor',
			'plotAreaFillAlphas',
			'plotAreaFillColors',
			'plotAreaGradientAngle',
			'zoomOutButtonAlpha',
			'zoomOutButtonColor',
			'zoomOutButtonImage',
			'zoomOutButtonImageSize',
			'zoomOutButtonPadding',
			'zoomOutButtonRollOverAlpha',
			'zoomOutText',
		);
		$this->addValidConfigProperties($validProps);
	}
	
}