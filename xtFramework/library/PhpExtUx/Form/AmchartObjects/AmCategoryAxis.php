<?php

/**
 * Class AmCategoryAxis
 * @see http://docs.amcharts.com/3/javascriptcharts/CategoryAxis
 * @author radoslav
 *
 */
class AmCategoryAxis extends AmAxisBase {
	
	/**
	 * Angle of label rotation, if the number of series exceeds autoRotateCount and parseDates is set to false.
	 * @param number $value
	 * @return AmCategoryAxis
	 */
	public function setAutoRotateAngle($value) {
		$this->setExtConfigProperty('autoRotateAngle', $value);
		return $this;
	}
	
	/**
	 * Get auto rotate angle
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getAutoRotateAngle() {
		return $this->getExtConfigProperty('autoRotateAngle');
	}
	
	/**
	 * In case your category axis values are Date objects, set this to true. 
	 * In this case the chart will parse dates and will place your data points at irregular intervals. 
	 * If you want dates to be parsed, but data points to be placed at equal intervals, set both parseDates and equalSpacing to true.
	 * @param boolean $bool
	 * @return AmCategoryAxis
	 */
	public function setParseDates($bool) {
		$this->setExtConfigProperty('parseDates', $bool);
		return $this;
	}
	
	/**
	 * Get parse dates
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getParseDates() {
		return $this->getExtConfigProperty('parseDates');
	}
	
	/**
	 * Specifies the shortest period of your data.
	 * This should be set only if parseDates is set to "true".
	 * Possible period values: fff - milliseconds, ss - seconds, mm - minutes, hh - hours, DD - days, MM - months, YYYY - years.
	 * It's also possible to supply a number for increments, i.e. "15mm" which will instruct the chart that your data is supplied in 15 minute increments.
	 * Default value : DD
	 * @var string $value
	 * @return AmCategoryAxis
	 */
	public function setMinPeriod($value) {
		$this->setExtConfigProperty('minPeriod', $value);
		return $this;
	}
	
	/**
	 * Get min period
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getMinPeriod() {
		return $this->getExtConfigProperty('minPeriod');
	}
	
	/**
	 * Works only when parseDates is set to true and equalSpacing is false. 
	 * If you set it to true, at the position where bigger period changes, category axis will display date strings of bot small and big period, in two rows.
	 * Default value : false
	 * @param boolean $value
	 * @return AmCategoryAxis
	 */
	public function setTwoLineMode($value) {
		$this->setExtConfigProperty('twoLineMode', $value);
		return $this;
	}
	
	/**
	 * Get two line mode
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getTwoLineMode() {
		return $this->getExtConfigProperty('twoLineMode');
	}
	
	/**
	 * Date formats of different periods. Possible period values: fff - milliseconds, ss - seconds, mm - minutes, hh - hours, DD - days, MM - months, WW - weeks, YYYY - years.
	 * Default value : [{period:'fff',format:'JJ:NN:SS'},{period:'ss',format:'JJ:NN:SS'},{period:'mm',format:'JJ:NN'},{period:'hh',format:'JJ:NN'},{period:'DD',format:'MMM DD'},{period:'WW',format:'MMM DD'},{period:'MM',format:'MMM'},{period:'YYYY',format:'YYYY'}] 
	 * @see http://www.amcharts.com/tutorials/formatting-dates/
	 * @param array $value
	 * @return AmCategoryAxis
	 */
	public function setDateFormats($value) {
		$this->setExtConfigProperty('dateFormats', $value);
		return $this;
	}
	
	/**
	 * Get date formats
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getDateFormats() {
		return $this->getExtConfigProperty('dateFormats');
	}
	
	/**
	 * Specifies if a grid line is placed on the center of a cell or on the beginning of a cell. 
	 * Possible values are: "start" and "middle" This setting doesn't work if parseDates is set to true.
	 * Default value : middle
	 * @param string $value
	 * @return AmCategoryAxis
	 */
	public function setGridPosition($value) {
		$this->setExtConfigProperty('gridPosition', $value);
		return $this;
	}
	
	/**
	 * Get grid position
	 * @return string
	 */
	public function getGridPosition() {
		return $this->getExtConfigProperty('gridPosition');
	}
	
	/**
	 * In case your category axis values are Date objects and parseDates is set to true, the chart will parse dates and will place your data points at irregular intervals. 
	 * However if you want dates to be parsed (displayed on the axis, baloons, etc), but data points to be placed at equal intervals (omiting dates with no data), set equalSpacing to true.
	 * Default value : false
	 * @param boolean $value
	 * @return AmCategoryAxis
	 */
	public function setEqualSpacing($value) {
		$this->setExtConfigProperty('equalSpacing', $value);
		return $this;
	}
	
	/**
	 * Get equal spacing
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getEqualSpacing() {
		return $this->getExtConfigProperty('equalSpacing');
	}
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","AmchartConfigObject");
	
		$validProps = array(
			'autoRotateAngle',
			'equalSpacing',
			'gridPosition',
			'parseDates',
			'minPeriod',
			'twoLineMode',
			'dateFormats',
		);
		$this->addValidConfigProperties($validProps);
	}
	
}