<?php

/**
 * @see PhpExt_Component
 */
include_once 'PhpExt/Component.php';

class PhpExt_Amchart extends PhpExt_Component {
	
	/**
	 * Sets width of the chart
	 * @param string $value
	 * @return PhpExt_Amchart
	 */
	public function setWidth($value) {
		$this->setExtConfigProperty("width", $value);
		return $this;
	}
	
	/**
	 * Get width of the chart
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getWidth() {
		return $this->getExtConfigProperty("width");
	}
	
	/**
	 * Sets height of the chart
	 * @param string $value
	 * @return PhpExt_Amchart
	 */
	public function setHeight($value) {
		$this->setExtConfigProperty('height', $value);
		return $this;
	}
	
	/**
	 * Get height of the chart
	 * @return string
	 */
	public function getHeight() {
		return $this->getExtConfigProperty('height');
	}
	
	/**
	 * Sets chart name
	 * @param string $value
	 * @return PhpExt_Amchart
	 */
	public function setChartName($value) {
		$this->setExtConfigProperty('chartName', $value);
		return $this;
	}
	
	/**
	 * Get chart name
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getChartName() {
		return $this->getExtConfigProperty('chartName');
	}
	
	/**
	 * Sets store data source
	 * @param PhpExt_Data_Store $value
	 * @return PhpExt_Amchart
	 */
	public function setStore(PhpExt_Data_Store $value) {
		$this->setExtConfigProperty('store', $value);
		return $this;
	}
	
	/**
	 * Get data store
	 * @return PhpExt_Data_Store
	 */
	public function getStore() {
		return $this->getExtConfigProperty('store');
	}
	
	/**
	 * Set serial chart
	 * @param AmChart $chart
	 * @return PhpExt_Amchart
	 */
	public function setChart(AmChart $chart) {
		$this->setExtConfigProperty('chart', $chart);
		return $this;
	}
	
	/**
	 * Get serial chart
	 * @return AmChart
	 */
	public function getChart() {
		return $this->getExtConfigProperty('chart');
	}
	
	public function setFilterWidgetsNames($value) {
		$this->setExtConfigProperty('filterWidgetsNames', $value);
		return $this;
	}
	
	public function getFilterWidgetsNames() {
		return $this->getExtConfigProperty('filterWidgetsNames');
	}
	
	/**
	 * Set if the chart will use real time data
	 * @param bool $value
	 * @return PhpExt_Amchart
	 */
	public function setIsRealTimeChart($value) {
		$this->setExtConfigProperty('isRealTimeChart', $value);
		return $this;
	}
	
	/**
	 * Get is real time chart
	 * @return Ambigous <NULL, multitype:>
	 */
	public function getIsRealTimeChart() {
		return $this->getExtConfigProperty('isRealTimeChart');
	}
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.Amchart", "ExtAmchart");
	
		$validProps = array(
			'filterWidgetsNames',
			'isRealTimeChart',
			'chartName',
			'width',
			'height',
			'store',
			'chart',
		);
		$this->addValidConfigProperties($validProps);
	}
	
	public function getFilterEventJs() {
		return "Ext.getCmp('" . $this->getId() . "').fireEvent('filterChanged');";
	}
	
	public static function createAmchartPanel(PhpExt_Amchart $chart, $filter = null) {
		$wrapper = new PhpExt_Panel();
		if (null !== $filter) {
			$wrapper->addItem($filter);
		}
		$wrapper->addItem($chart);
		
		$wrapper->setAutoScroll(false)
		->setAutoWidth(true)
		->setAutoHeight(true)
		->setCssStyle('border:none;')
		//->attachListener('render', new PhpExt_Listener(PhpExt_Javascript::functionDef(null, 'alert("da");')))
		->setLayout(new PhpExt_Layout_FitLayout());
		return $wrapper;
	}
}

?>