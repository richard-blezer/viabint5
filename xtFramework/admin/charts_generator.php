<?php

/*
 #########################################################################
#                       xt:Commerce  4.1 Shopsoftware
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#
# Copyright 2007-2014 xt:Commerce International Ltd. All Rights Reserved.
# This file may not be redistributed in whole or significant part.
# Content of this file is Protected By International Copyright Laws.
#
# ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
#
# http://www.xt-commerce.com
#
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#
# @version $Id$
# @copyright xt:Commerce International Ltd., www.xt-commerce.com
#
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#
# xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
#
# office@xt-commerce.com
#
#########################################################################
*/

class ChartsGenerator {
	
	/**
	 * Get shopping cart cahrt
	 * @param PhpExt_Panel $stat_shopping_cart
	 * @return null
	 */
	public function getShoppingCartsChart(PhpExt_Panel $stat_shopping_cart) {
		global $store_handler;
		
		// Instantiate new chart object
		$shoppingChart = new PhpExt_Amchart();
		// Set id
		$shoppingChart->setChartName('shopping-cart-amchart')->setId('shopping-cart-amchart-totals');
		
		// Set callback just before filtering so we can change chart axis values depending on the type of period
		// we want to filter
		$shoppingChart->attachListener('beforeFilterRequest', new PhpExt_Listener(PhpExt_Javascript::functionDef(null,
				'var displayType = Ext.getCmp("ShoppingCartsDisplayByFilter").getValue();' .
				'this.chart.valueAxes[0].title = (displayType == "carts_count") ? "' . TEXT_CARTS_COUNT . '" : "' . TEXT_PRODUCTS_COUNT . '";' .
				'var period = Ext.getCmp("ShoppingCartsPeriodTypeFilter").getValue();' .
				'switch (period) {' .
				'case "day":' .
					'this.chart.dataDateFormat = "YYYY-MM-DD";' .
					'this.chart.categoryAxis.minPeriod = "DD";' .
				'break;'.
				'case "month":' .
					'this.chart.dataDateFormat = "YYYY-MM";' .
					'this.chart.categoryAxis.minPeriod = "MM";' .
				'break;'.
				'case "year":' .
					'this.chart.dataDateFormat = "YYYY";' .
					'this.chart.categoryAxis.minPeriod = "YYYY";' .
				'break;'.
				'}'
		)));
		
		// Set data reader for this chart
		$reader = new PhpExt_Data_JsonReader();
		$reader->setRoot('topics')->setTotalProperty('totalCount');
		$reader->addField(new PhpExt_Data_FieldConfigObject("date"));
		
		// Set data proxy for this chart
		$fromStore = new PhpExt_Data_Store();
		$fromStore->setProxy(new PhpExt_Data_HttpProxy('chart.php?ChartType=shopping_carts'))->setReader($reader);
		
		$shoppingChart->setStore($fromStore);
		// Some filters for the chart
		$fromFilter = PhpExt_Form_DateField::createDateField('ShoppingCartsFromDate', TEXT_FROM);
		$fromFilter->setFormat('Y-m-d H:i:s')->setValue(date('Y-m-d', strtotime('-1 month')))->setWidth(150)->setId('ShoppingCartsFromDate');
		
		$toFilter = PhpExt_Form_DateField::createDateField('ShoppingCartsToDate', TEXT_TO);
		$toFilter->setFormat('Y-m-d H:i:s')->setValue(date('Y-m-d', strtotime('now')))->setWidth(150)->setId('ShoppingCartsToDate');
		
		$helper = new ExtFunctions();
		$periodTypeFilter = $helper->_comboBox('ShoppingCartsPeriodType', TEXT_PERIOD_TYPE, 'DropdownData.php?get=filter_period');
		$periodTypeFilter->setWidth(150)->setId('ShoppingCartsPeriodTypeFilter')->setValue('day');
		
		$customerStatusFilter = $helper->_comboBox('ShoppingCartsCustomersStatus', TEXT_CUSTOMERS_STATUS, 'DropdownData.php?get=customers_status');
		$customerStatusFilter->setWidth(150)->setId('ShoppingCartsCustomersStatusFilter');
		
		$shoppingCartsTypeFilter = $helper->_comboBox('ShoppingCartsType', TEXT_SHOPPING_CART, 'DropdownData.php?get=shopping_carts_type');
		$shoppingCartsTypeFilter->setWidth(150)->setId('ShoppingCartsTypeFilter');
		
		$shoppingCartsDisplayByFilter = $helper->_comboBox('ShoppingCartsDisplayBy', TEXT_DISPLAY_BY, 'DropdownData.php?get=shopping_carts_display_by');
		$shoppingCartsDisplayByFilter->setWidth(150)->setId('ShoppingCartsDisplayByFilter');
		
		// Set filter Ids to the chart so when filtering is performet we can grab their values and pass them to the proxy
		$shoppingChart->setFilterWidgetsNames(array(
			'ShoppingCartsFromDate',
			'ShoppingCartsToDate',
			'ShoppingCartsPeriodTypeFilter',
			'ShoppingCartsCustomersStatusFilter',
			'ShoppingCartsTypeFilter',
			'ShoppingCartsDisplayByFilter',
		));
		
		// Serial chart
		$serialChart = new AmSerialChart();
		$serialChart->setCategoryField('date');
		$serialChart->setPathToImages('../xtFramework/library/ext/ux/images/')->setDataDateFormat('YYYY-MM-DD')->setStartDuration(1);
		
		// Create axis object
		$axis = new AmCategoryAxis();
		$axis->setParseDates(true)
		->setMinPeriod('DD')
		->setAxisColor('#DADADA')
		->setTwoLineMode(true)
		->setGridPosition('start');
		//->setEqualSpacing(true);
		
		// Add the axis
		$serialChart->setCategoryAxis($axis);
		
		// Create cursor
		$cursor = new AmChartCursor();
		$cursor->setCursorAlpha(0.1)->setFullWidth(true);
		$serialChart->addChartCursor(AmChartCallable::createCallable($cursor));
		
		// Create legend
		$legend = new AmChartLegend();
		$legend->setMarginLeft(110)->setUseGraphSettings(false);
		$serialChart
		->addLegend(AmChartCallable::createCallable($legend))
		->addChartScrollbar(AmChartCallable::createCallable(new AmChartScrollbar()));
		
		$stores = $store_handler->getStores();
		
		// Create value axis
		$valueAxis = new AmValueAxis();
		$valueAxis
		->setAxisColor('#FF0000')
		->setAxisThickness(2)
		->setOffset(0)
		->setStackType('3d')
		->setTitle(TEXT_CARTS_COUNT)
		->setAxisAlpha(0);
		
		// Create graph for each store
		$graph = new AmGraph();
		$graph
		->setTitle(__define('TEXT_TOTAL'))
		->setValueField('total')
		->setBullet('round')
		->setBulletBorderThickness(1)
		->setHideBulletsCount(30)
		->setLineColor('#FF0000')
		->setFillAlphas(0.3)
		->setLineAlpha(0)
		->setType('column')
		->setBalloonText(__define('TEXT_TOTAL') . ' [[value]] ');
		$reader->addField(new PhpExt_Data_FieldConfigObject('total'));
		$serialChart->addValueAxis(AmChartCallable::createCallable($valueAxis))->addGraph(AmChartCallable::createCallable($graph));
		
		foreach ($stores as $storeData) {
			$color = random_color();
		
			$graph = new AmGraph();
			$graph
			->setTitle($storeData['text'])
			->setValueField($storeData['id'])
			->setBullet('round')
			->setBulletBorderThickness(1)
			->setHideBulletsCount(30)
			->setLineColor($color)
			->setBalloonText($storeData['text'] . ' - [[value]] ');
			$reader->addField(new PhpExt_Data_FieldConfigObject($storeData['id']));
			$serialChart->addGraph(AmChartCallable::createCallable($graph));
		}
		
		$shoppingChart->setChart($serialChart);
		
		// -------------- PIE CHART
		$shoppingCartsByStoreChart = new PhpExt_Amchart();
		$shoppingCartsByStoreChart->setChartName('shopping-cart-amchart-by-store')->setId('shopping-cart-amchart-totals-by-store');
		
		$reader = new PhpExt_Data_JsonReader();
		$reader->setRoot("topics")
		->setTotalProperty("totalCount");
		$reader->addField(new PhpExt_Data_FieldConfigObject("store_name"));
		$reader->addField(new PhpExt_Data_FieldConfigObject("store_total"));
		
		$fromstore = new PhpExt_Data_Store();
		$fromstore->setProxy(new PhpExt_Data_HttpProxy('chart.php?ChartType=shopping_carts_by_store'))
		->setReader($reader);
		
		$shoppingCartsByStoreChart->setStore($fromstore);
		
		$pieChart = new AmPieChart();
		$pieChart->setTitleField('store_name')
		->setValueField('store_total')
		->setAlpha(0.6)
		//->setBalloonText("[[title]]<br><span style=\"font-size:11px\"><b>[[value]] " . _STORE_CURRENCY . "</b> ([[percents]]%)</span>")
		->setHeight('100%');
		//->setDepth3D(15)
		//->setAngle(30);
		
		$legend = new AmChartLegend();
		$legend->setAlign("center")
		->setMarkerType("circle");
		
		$pieChart->addLegend(AmChartCallable::createCallable($legend));
		
		$shoppingCartsByStoreChart->setChart($pieChart);
		$shoppingCartsByStoreChart->setFilterWidgetsNames(array(
			'ShoppingCartsFromDate',
			'ShoppingCartsToDate',
			'ShoppingCartsPeriodTypeFilter',
			'ShoppingCartsCustomersStatusFilter',
			'ShoppingCartsTypeFilter',
			'ShoppingCartsDisplayByFilter',
		));
		
		// -------------- END PIE CHART
		
		// Create filter panel
		$filterColumnPanel = new PhpExt_Panel();
		$filterColumnPanel->setLayout(new PhpExt_Layout_ColumnLayout())->setAutoWidth(true)->setBorder(false)->setTitle(__define("TEXT_FILTER"));
		
		$filterPanel = new PhpExt_Panel();
		$filterPanel
		->setAutoHeight(true);
		$filterPanel->setLayout(new PhpExt_Layout_FormLayout())->setBodyStyle("padding: 5px;");
		$filterPanel
		->setAutoWidth(true)
		->setAutoHeight(true)
		->setCssStyle('border:none;')
		->setBorder(false);
		
		$filterPanel2 = new PhpExt_Panel();
		$filterPanel2
		->setAutoHeight(true);
		$filterPanel2->setLayout(new PhpExt_Layout_FormLayout())->setBodyStyle("padding: 5px;");
		$filterPanel2
		->setAutoWidth(true)
		->setAutoHeight(true)
		->setCssStyle('border:none;')
		->setBorder(false);
		
		$filterPanel
		->addItem($fromFilter)
		->addItem($toFilter)
		->addItem($periodTypeFilter)
		;
		$filterPanel->addItem(PhpExt_Toolbar_Button::createButton(__define("TEXT_FILTER"), null, new PhpExt_Handler(PhpExt_Javascript::stm(
				$shoppingChart->getFilterEventJs() . $shoppingCartsByStoreChart->getFilterEventJs()
		))));
		
		$filterPanel2
		->addItem($shoppingCartsTypeFilter)
		->addItem($customerStatusFilter)
		->addItem($shoppingCartsDisplayByFilter);
		
		$filterColumnPanel->addItem($filterPanel, new PhpExt_Layout_ColumnLayoutData(0.70));
		$filterColumnPanel->addItem($filterPanel2, new PhpExt_Layout_ColumnLayoutData(0.30));
		
		$columnPanel = new PhpExt_Panel();
		$columnPanel->setLayout(new PhpExt_Layout_ColumnLayout())->setAutoWidth(true)->setBorder(false);
		
		// Add filter panel to the tab
		$stat_shopping_cart->addItem($filterColumnPanel);
		
		// Column for first chart
		$firstColumn = new PhpExt_Panel();
		$firstColumn->setLayout(new PhpExt_Layout_FitLayout())->setBorder(false);
		$firstColumn->addItem($shoppingChart);
		
		// Column for pie chart
		$secondColumn = new PhpExt_Panel();
		$secondColumn->setLayout(new PhpExt_Layout_FitLayout())->setBorder(false);
		$secondColumn->addItem($shoppingCartsByStoreChart);
		$columnPanel->addItem($firstColumn, new PhpExt_Layout_ColumnLayoutData(0.70));
		$columnPanel->addItem($secondColumn, new PhpExt_Layout_ColumnLayoutData(0.30));
		$stat_shopping_cart->addItem($columnPanel);
	}
	
	/**
	 * Get customers chart
	 * @param PhpExt_Panel $stat_customers
	 * @return null
	 */
	public function getCustomersChart(PhpExt_Panel $stat_customers) {
		global $store_handler;
		
		// Instantiate new chart object
		$customersChart = new PhpExt_Amchart();
		// Set id
		$customersChart->setChartName('customers-amchart')->setId('customers-amchart-totals');
		
		// Set callback just before filtering so we can change chart axis values depending on the type of period
		// we want to filter
		$customersChart->attachListener('beforeFilterRequest', new PhpExt_Listener(PhpExt_Javascript::functionDef(null,
				'var period = Ext.getCmp("CustomersPeriodTypeFilter").getValue();' .
				'switch (period) {' .
				'case "day":' .
				'this.chart.dataDateFormat = "YYYY-MM-DD";' .
				'this.chart.categoryAxis.minPeriod = "DD";' .
				'break;'.
				'case "month":' .
				'this.chart.dataDateFormat = "YYYY-MM";' .
				'this.chart.categoryAxis.minPeriod = "MM";' .
				'break;'.
				'case "year":' .
				'this.chart.dataDateFormat = "YYYY";' .
				'this.chart.categoryAxis.minPeriod = "YYYY";' .
				'break;'.
				'}'
		)));
		
		// Set data reader for this chart
		$reader = new PhpExt_Data_JsonReader();
		$reader->setRoot('topics')->setTotalProperty('totalCount');
		$reader->addField(new PhpExt_Data_FieldConfigObject("date"));
		
		// Set data proxy for this chart
		$fromStore = new PhpExt_Data_Store();
		$fromStore->setProxy(new PhpExt_Data_HttpProxy('chart.php?ChartType=customerscount'))->setReader($reader);
		
		$customersChart->setStore($fromStore);
		// Some filters for the chart
		$fromFilter = PhpExt_Form_DateField::createDateField('CustomersFromDate', TEXT_FROM);
		$fromFilter->setFormat('Y-m-d H:i:s')->setValue(date('Y-m-d', strtotime('-1 month')))->setWidth(150)->setId('CustomersFromDate');
		
		$toFilter = PhpExt_Form_DateField::createDateField('CustomersToDate', TEXT_TO);
		$toFilter->setFormat('Y-m-d H:i:s')->setValue(date('Y-m-d', strtotime('now')))->setWidth(150)->setId('CustomersToDate');
		
		$helper = new ExtFunctions();
		$periodTypeFilter = $helper->_comboBox('CustomersPeriodType', TEXT_PERIOD_TYPE, 'DropdownData.php?get=filter_period');
		$periodTypeFilter->setWidth(150)->setId('CustomersPeriodTypeFilter')->setValue('day');
		
		$customerStatusFilter = $helper->_comboBox('CustomersStatus', TEXT_CUSTOMERS_STATUS, 'DropdownData.php?get=customers_status');
		$customerStatusFilter->setWidth(150)->setId('CustomersStatusFilter');//->setValue('day');
		
		$customerDobFromFilter = PhpExt_Form_DateField::createDateField('CustomerDobFromFilterDate', TEXT_DATE_OF_BIRTH_FROM);
		$customerDobFromFilter->setFormat('Y-m-d H:i:s')->setWidth(150)->setId('CustomerDobFromFilterDate');//->setValue(date('Y-m-d', strtotime('-90 years')))
			
		$customerDobToFilter = PhpExt_Form_DateField::createDateField('CustomerDobToFilterDate', TEXT_DATE_OF_BIRTH_TO);
		$customerDobToFilter->setFormat('Y-m-d H:i:s')->setWidth(150)->setId('CustomerDobToFilterDate');//->setValue(date('Y-m-d', strtotime('now')))
		
		$customersCountryFilter = $helper->_comboBox('CustomersCountryFilter', TEXT_STORE_COUNTRY, 'DropdownData.php?get=countries');
		$customersCountryFilter->setWidth(150)->setId('CustomersCountryCodeFilter');
		
		// Set filter Ids to the chart so when filtering is performet we can grab their values and pass them to the proxy
		$customersChart->setFilterWidgetsNames(array(
				'CustomersFromDate',
				'CustomersToDate',
				'CustomersPeriodTypeFilter',
				'CustomersStatusFilter',
				'CustomerDobFromFilterDate',
				'CustomerDobToFilterDate',
				'CustomersCountryCodeFilter',
		));
		
		// Serial chart
		$serialChart = new AmSerialChart();
		$serialChart->setCategoryField('date');
		$serialChart->setPathToImages('../xtFramework/library/ext/ux/images/')->setDataDateFormat('YYYY-MM-DD')->setStartDuration(1);
		
		// Create axis object
		$axis = new AmCategoryAxis();
		$axis->setParseDates(true)
		->setMinPeriod('DD')
		->setAxisColor('#DADADA')
		->setTwoLineMode(true)
		->setGridPosition('start');
		//->setEqualSpacing(true);
		
		// Add the axis
		$serialChart->setCategoryAxis($axis);
		
		// Create cursor
		$cursor = new AmChartCursor();
		$cursor->setCursorAlpha(0.1)->setFullWidth(true);
		$serialChart->addChartCursor(AmChartCallable::createCallable($cursor));
		
		// Create legend
		$legend = new AmChartLegend();
		$legend->setMarginLeft(110)->setUseGraphSettings(false);
		$serialChart
		->addLegend(AmChartCallable::createCallable($legend))
		->addChartScrollbar(AmChartCallable::createCallable(new AmChartScrollbar()));
		
		$stores = $store_handler->getStores();
		
		// Create value axis
		$valueAxis = new AmValueAxis();
		$valueAxis
		->setAxisColor('#FF0000')
		->setAxisThickness(2)
		->setOffset(0)
		->setStackType('3d')
		->setTitle('Count')
		->setAxisAlpha(0);
		
		// Create graph for each store
		$graph = new AmGraph();
		$graph
		->setTitle(__define('TEXT_TOTAL'))
		->setValueField('total')
		->setBullet('round')
		->setBulletBorderThickness(1)
		->setHideBulletsCount(30)
		->setLineColor('#FF0000')
		->setFillAlphas(0.3)
		->setLineAlpha(0)
		->setType('column')
		->setBalloonText(__define('TEXT_TOTAL') . ' [[value]] ');
		$reader->addField(new PhpExt_Data_FieldConfigObject('total'));
		$serialChart->addValueAxis(AmChartCallable::createCallable($valueAxis))->addGraph(AmChartCallable::createCallable($graph));
		
		foreach ($stores as $storeData) {
			$color = random_color();
		
			$graph = new AmGraph();
			$graph
			->setTitle($storeData['text'])
			->setValueField($storeData['id'])
			->setBullet('round')
			->setBulletBorderThickness(1)
			->setHideBulletsCount(30)
			->setLineColor($color)
			->setBalloonText($storeData['text'] . ' - [[value]] ');
			$reader->addField(new PhpExt_Data_FieldConfigObject($storeData['id']));
			$serialChart->addGraph(AmChartCallable::createCallable($graph));
		}
		
		$customersChart->setChart($serialChart);
		
		// -------------- PIE CHART
		$customersByStoreChart = new PhpExt_Amchart();
		$customersByStoreChart->setChartName('customers-amchart-by-store')->setId('customers-amchart-totals-by-store');
		
		$reader = new PhpExt_Data_JsonReader();
		$reader->setRoot("topics")
		->setTotalProperty("totalCount");
		$reader->addField(new PhpExt_Data_FieldConfigObject("store_name"));
		$reader->addField(new PhpExt_Data_FieldConfigObject("store_total"));
		
		$fromstore = new PhpExt_Data_Store();
		$fromstore->setProxy(new PhpExt_Data_HttpProxy('chart.php?ChartType=customerscountbystore'))
		->setReader($reader);
		
		$customersByStoreChart->setStore($fromstore);
		
		$pieChart = new AmPieChart();
		$pieChart->setTitleField('store_name')
		->setValueField('store_total')
		->setAlpha(0.6)
		//->setBalloonText("[[title]]<br><span style=\"font-size:11px\"><b>[[value]] " . _STORE_CURRENCY . "</b> ([[percents]]%)</span>")
		->setHeight('100%');
		//->setDepth3D(15)
		//->setAngle(30);
		
		$legend = new AmChartLegend();
		$legend->setAlign("center")
		->setMarkerType("circle");
		
		$pieChart->addLegend(AmChartCallable::createCallable($legend));
		
		$customersByStoreChart->setChart($pieChart);
		$customersByStoreChart->setFilterWidgetsNames(array(
				'CustomersFromDate',
				'CustomersToDate',
				'CustomersPeriodTypeFilter',
				'CustomersStatusFilter',
				'CustomerDobFromFilterDate',
				'CustomerDobToFilterDate',
				'CustomersCountryCodeFilter',
		));
		
		// -------------- END PIE CHART
		
		// Create filter panel
		$filterColumnPanel = new PhpExt_Panel();
		$filterColumnPanel->setLayout(new PhpExt_Layout_ColumnLayout())->setAutoWidth(true)->setBorder(false)->setTitle(__define("TEXT_FILTER"));
		
		$filterPanel = new PhpExt_Panel();
		$filterPanel
		->setAutoHeight(true);
		$filterPanel->setLayout(new PhpExt_Layout_FormLayout())->setBodyStyle("padding: 5px;");
		$filterPanel
		->setAutoWidth(true)
		->setAutoHeight(true)
		->setCssStyle('border:none;')
		->setBorder(false);
		
		$filterPanel2 = new PhpExt_Panel();
		$filterPanel2
		->setAutoHeight(true);
		$filterPanel2->setLayout(new PhpExt_Layout_FormLayout())->setBodyStyle("padding: 5px;");
		$filterPanel2
		->setAutoWidth(true)
		->setAutoHeight(true)
		->setCssStyle('border:none;')
		->setBorder(false);
		
		$filterPanel
		->addItem($fromFilter)
		->addItem($toFilter)
		->addItem($periodTypeFilter)
		->addItem($customerStatusFilter);
		$filterPanel->addItem(PhpExt_Toolbar_Button::createButton(__define("TEXT_FILTER"), null, new PhpExt_Handler(PhpExt_Javascript::stm(
				$customersChart->getFilterEventJs() . $customersByStoreChart->getFilterEventJs()
		))));
		
		$filterPanel2->addItem($customerDobFromFilter)->addItem($customerDobToFilter);
		
		$filterPanel2->addItem($customersCountryFilter);
		$filterColumnPanel->addItem($filterPanel, new PhpExt_Layout_ColumnLayoutData(0.70));
		$filterColumnPanel->addItem($filterPanel2, new PhpExt_Layout_ColumnLayoutData(0.30));
		
		$columnPanel = new PhpExt_Panel();
		$columnPanel->setLayout(new PhpExt_Layout_ColumnLayout())->setAutoWidth(true)->setBorder(false);
		
		// Add filter panel to the tab
		$stat_customers->addItem($filterColumnPanel);
		
		// Column for first chart
		$firstColumn = new PhpExt_Panel();
		$firstColumn->setLayout(new PhpExt_Layout_FitLayout())->setBorder(false);
		$firstColumn->addItem(
				$customersChart
		);
		
		// Column for pie chart
		$secondColumn = new PhpExt_Panel();
		$secondColumn->setLayout(new PhpExt_Layout_FitLayout())->setBorder(false);
		$secondColumn->addItem(
				$customersByStoreChart
		);
		$columnPanel->addItem($firstColumn, new PhpExt_Layout_ColumnLayoutData(0.70));
		$columnPanel->addItem($secondColumn, new PhpExt_Layout_ColumnLayoutData(0.30));
		$stat_customers->addItem($columnPanel);
		
	}
	
	public function getOrdersChart(PhpExt_Panel $stat_orders) {
		global $store_handler;
		
		$chart = new PhpExt_Amchart();
		$chart->setChartName('orders-amchart')->setId('orders-amchart-totals');
		//$chart->attachListener('filterChanged', new PhpExt_Listener(PhpExt_Javascript::functionDef(null, 'alert("da");')));
		//$chart->attachListener('dataUpdated', new PhpExt_Listener(PhpExt_Javascript::functionDef(null, 'this.chart.zoomToIndexes(10, 20);')));
		$chart->attachListener('beforeFilterRequest', new PhpExt_Listener(PhpExt_Javascript::functionDef(null,
				'var period = Ext.getCmp("OrdersPeriodTypeFilter").getValue();' .
				'switch (period) {' .
				'case "day":' .
				'this.chart.dataDateFormat = "YYYY-MM-DD";' .
				'this.chart.categoryAxis.minPeriod = "DD";' .
				'break;'.
				'case "month":' .
				'this.chart.dataDateFormat = "YYYY-MM";' .
				'this.chart.categoryAxis.minPeriod = "MM";' .
				'break;'.
				'case "year":' .
				'this.chart.dataDateFormat = "YYYY";' .
				'this.chart.categoryAxis.minPeriod = "YYYY";' .
				'break;'.
				'}'
		)));
		// Store
		
		$reader = new PhpExt_Data_JsonReader();
		$reader->setRoot("topics")
		->setTotalProperty("totalCount");
		$reader->addField(new PhpExt_Data_FieldConfigObject("date"));
		
		$fromstore = new PhpExt_Data_Store();
		$fromstore->setProxy(new PhpExt_Data_HttpProxy('chart.php?ChartType=orders'))
		->setReader($reader);
		
		$chart->setStore($fromstore);
		
		// Filters
		$fromFilter = PhpExt_Form_DateField::createDateField('OrdersFromDate', TEXT_FROM);
		$fromFilter->setFormat('Y-m-d H:i:s')->setValue(date('Y-m-d', strtotime('-1 month')))->setWidth(150)->setId('OrdersFromDate');
		
		$toFilter = PhpExt_Form_DateField::createDateField('OrdersToDate', TEXT_TO);
		$toFilter->setFormat('Y-m-d H:i:s')->setValue(date('Y-m-d', strtotime('now')))->setWidth(150)->setId('OrdersToDate');
		
		$helper = new ExtFunctions();
		$periodTypeFilter = $helper->_comboBox('OrdersPeriodType', TEXT_PERIOD_TYPE, 'DropdownData.php?get=filter_period');
		$periodTypeFilter->setWidth(150)->setId('OrdersPeriodTypeFilter')->setValue('day');
		
		$customerStatusFilter = $helper->_comboBox('OrdersCustomersStatus', TEXT_CUSTOMERS_STATUS, 'DropdownData.php?get=customers_status');
		$customerStatusFilter->setWidth(150)->setId('OrdersCustomersStatusFilter')->setValue('');
		
		$orderDobFromFilter = PhpExt_Form_DateField::createDateField('OrderDobFromFilterDate', TEXT_DATE_OF_BIRTH_FROM);
		$orderDobFromFilter->setFormat('Y-m-d H:i:s')->setWidth(150)->setId('OrderDobFromFilterDate');
			
		$orderDobToFilter = PhpExt_Form_DateField::createDateField('OrderDobToFilterDate', TEXT_DATE_OF_BIRTH_TO);
		$orderDobToFilter->setFormat('Y-m-d H:i:s')->setWidth(150)->setId('OrderDobToFilterDate');
		
		$orderCountryFilter = $helper->_comboBox('OrderCountryCode', TEXT_STORE_COUNTRY, 'DropdownData.php?get=countries');
		$orderCountryFilter->setWidth(150)->setId('OrderCountryCodeFilter');
		// End filters
		
		$chart->setFilterWidgetsNames(array(
				'OrdersFromDate', 
				'OrdersToDate', 
				'OrdersPeriodTypeFilter', 
				'OrdersCustomersStatusFilter',
				'OrderDobFromFilterDate',
				'OrderDobToFilterDate',
				'OrderCountryCodeFilter',
		));
		
		// Chart
		$serialChart = new AmSerialChart();
		//$serialChart->attachListener('dataUpdated', new PhpExt_Listener(PhpExt_Javascript::functionDef(null, 'console.log(chart);', array('chart'))));
		$serialChart->setCategoryField('date');
		$serialChart->setPathToImages('../xtFramework/library/ext/ux/images/')->setDataDateFormat('YYYY-MM-DD')->setStartDuration(1);
		
		$axis = new AmCategoryAxis();
		$axis->setParseDates(true)
		->setMinPeriod('DD')
		->setAxisColor('#DADADA')
		->setTwoLineMode(true)
		->setGridPosition('start');
		//->setEqualSpacing(true);
		
		$serialChart->setCategoryAxis($axis);
		
		$cursor = new AmChartCursor();
		$cursor->setCursorAlpha(0.1)->setFullWidth(true);
		$serialChart->addChartCursor(AmChartCallable::createCallable($cursor));
		//$serialChart->setCursor($cursor);
		
		//$serialChart
		//->setScrollbar(new AmChartScrollbar());
		//->setHandDrawn(true)
		//->setHandDrawnScatter(3);
		
		$legend = new AmChartLegend();
		$legend
		->setMarginLeft(110)
		->setUseGraphSettings(false)
		->setSpacing(60)
		->setValueAlign('left')
		->setValueText("[[value]] " . _STORE_CURRENCY);
		$serialChart
		->addLegend(AmChartCallable::createCallable($legend))
		->addChartScrollbar(AmChartCallable::createCallable(new AmChartScrollbar()));
		
		
		$stores = $store_handler->getStores();
		
		$valueAxis = new AmValueAxis();
		$valueAxis
		->setAxisColor('#FF0000')
		->setAxisThickness(2)
		->setOffset(0)
		->setStackType('3d')
		->setTitle(__define('TEXT_AMOUNT'))
		->setUnit(' ' . _STORE_CURRENCY)
		->setAxisAlpha(0);
		
		$graph = new AmGraph();
		//->setValueAxis(AmChartCallable::createCallable($valueAxis))
		$graph
		->setTitle(__define('TEXT_TOTAL'))
		->setValueField('total')
		->setBullet('round')
		->setBulletBorderThickness(1)
		->setHideBulletsCount(30)
		->setLineColor('#FF0000')
		->setFillAlphas(0.3)
		->setLineAlpha(0)
		->setType('column')
		->setBalloonText(__define('TEXT_TOTAL') . ' [[value]] ' . _STORE_CURRENCY);
		$reader->addField(new PhpExt_Data_FieldConfigObject('total'));
		$serialChart->addValueAxis(AmChartCallable::createCallable($valueAxis))->addGraph(AmChartCallable::createCallable($graph));
		
		foreach ($stores as $storeData) {
			$color = random_color();
		
			$graph = new AmGraph();
			$graph
			->setTitle($storeData['text'])
			->setValueField($storeData['id'])
			->setBullet('round')
			->setBulletBorderThickness(1)
			->setHideBulletsCount(30)
			->setLineColor($color)
			->setBalloonText($storeData['text'] . ' - [[value]] ' . _STORE_CURRENCY);
			$reader->addField(new PhpExt_Data_FieldConfigObject($storeData['id']));
			$serialChart->addGraph(AmChartCallable::createCallable($graph));
		}
		
		$chart->setChart($serialChart);
		
		// -------------- PIE CHART
		$ordersByStoreChart = new PhpExt_Amchart();
		$ordersByStoreChart->setChartName('orders-amchart-by-store')->setId('orders-amchart-totals-by-store');
		
		$reader = new PhpExt_Data_JsonReader();
		$reader->setRoot("topics")
		->setTotalProperty("totalCount");
		$reader->addField(new PhpExt_Data_FieldConfigObject("store_name"));
		$reader->addField(new PhpExt_Data_FieldConfigObject("store_total"));
		
		$fromstore = new PhpExt_Data_Store();
		$fromstore->setProxy(new PhpExt_Data_HttpProxy('chart.php?ChartType=ordersbystore'))
		->setReader($reader);
		
		$ordersByStoreChart->setStore($fromstore);
		
		$pieChart = new AmPieChart();
		$pieChart->setTitleField('store_name')
		->setValueField('store_total')
		->setBalloonText("[[title]]<br><span style=\"font-size:11px\"><b>[[value]] " . _STORE_CURRENCY . "</b> ([[percents]]%)</span>")
		->setHeight('100%')
		->setAlpha(0.6);
		//->setDepth3D(15)
		//->setAngle(30);
		
		$legend = new AmChartLegend();
		$legend->setAlign("center")
		->setSpacing(60)
		->setValueAlign('left')
		->setValueText("[[value]] " . _STORE_CURRENCY)
		->setMarkerType("circle");
		
		$pieChart->addLegend(AmChartCallable::createCallable($legend));
		
		$ordersByStoreChart->setChart($pieChart);
		$ordersByStoreChart->setFilterWidgetsNames(array(
				'OrdersFromDate', 
				'OrdersToDate', 
				'OrdersPeriodTypeFilter', 
				'OrdersCustomersStatusFilter',
				'OrderDobFromFilterDate',
				'OrderDobToFilterDate',
				'OrderCountryCodeFilter',
		));
		
		// -------------- END PIE CHART
		
		$filterColumnPanel = new PhpExt_Panel();
		$filterColumnPanel->setLayout(new PhpExt_Layout_ColumnLayout())->setAutoWidth(true)->setBorder(false)->setTitle(__define("TEXT_FILTER"));
		
		$filterPanel = new PhpExt_Panel();
		$filterPanel
		->setAutoHeight(true);
		$filterPanel->setLayout(new PhpExt_Layout_FormLayout())->setBodyStyle("padding: 5px;");
		$filterPanel
		->setAutoWidth(true)
		->setAutoHeight(true)
		->setCssStyle('border:none;')
		->setBorder(false);
		
		$filterPanel2 = new PhpExt_Panel();
		$filterPanel2
		->setAutoHeight(true);
		$filterPanel2->setLayout(new PhpExt_Layout_FormLayout())->setBodyStyle("padding: 5px;");
		$filterPanel2
		->setAutoWidth(true)
		->setAutoHeight(true)
		->setCssStyle('border:none;')
		->setBorder(false);
		
		$filterPanel
		->addItem($fromFilter)
		->addItem($toFilter)
		->addItem($periodTypeFilter)
		->addItem($customerStatusFilter);
		$filterPanel->addItem(PhpExt_Toolbar_Button::createButton(__define("TEXT_FILTER"), null, new PhpExt_Handler(PhpExt_Javascript::stm(
				$chart->getFilterEventJs() . $ordersByStoreChart->getFilterEventJs()
		))));
		
		$filterPanel2->addItem($orderDobFromFilter)->addItem($orderDobToFilter);
		$filterPanel2->addItem($orderCountryFilter);
		$filterColumnPanel->addItem($filterPanel, new PhpExt_Layout_ColumnLayoutData(0.70));
		$filterColumnPanel->addItem($filterPanel2, new PhpExt_Layout_ColumnLayoutData(0.30));
		
		$columnPanel = new PhpExt_Panel();
		$columnPanel->setLayout(new PhpExt_Layout_ColumnLayout())->setAutoWidth(true)->setBorder(false);
		
		$stat_orders->addItem($filterColumnPanel);
		
		$firstColumn = new PhpExt_Panel();
		$firstColumn->setLayout(new PhpExt_Layout_FitLayout())->setBorder(false);
		$firstColumn->addItem(
				$chart
		);
		
		$secondColumn = new PhpExt_Panel();
		$secondColumn->setLayout(new PhpExt_Layout_FitLayout())->setBorder(false);
		$secondColumn->addItem(
				$ordersByStoreChart
		);
		$columnPanel->addItem($firstColumn, new PhpExt_Layout_ColumnLayoutData(0.70));
		$columnPanel->addItem($secondColumn, new PhpExt_Layout_ColumnLayoutData(0.30));
		$stat_orders->addItem($columnPanel);
	}
	
}