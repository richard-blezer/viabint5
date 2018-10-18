<?php

// Customers online real time data
$stat_customers_online_real_time = new PhpExt_Panel();
$stat_customers_online_real_time->setTitle(TEXT_CUSTOMERS_ONLINE_REAL_TIME)
->setAutoScroll(true)
->setAutoWidth(true);

global $store_handler;

$customersChart = new PhpExt_Amchart();
$customersChart
->setChartName('customers-online')
->setId('customers-online-realtime')
->setIsRealTimeChart(true);

// Set data reader for this chart
$reader = new PhpExt_Data_JsonReader();
$reader->setRoot('topics')->setTotalProperty('totalCount');
$reader->addField(new PhpExt_Data_FieldConfigObject("date"));

// Set data proxy for this chart
$fromStore = new PhpExt_Data_Store();
$fromStore->setProxy(new PhpExt_Data_HttpProxy('chart.php?ChartType=customers_online'))->setReader($reader);

$customersChart->setStore($fromStore);


// Serial chart
$serialChart = new AmSerialChart();
$serialChart->setCategoryField('date');
$serialChart->setPathToImages('../xtFramework/library/ext/ux/images/')->setDataDateFormat('YYYY-MM-DD')->setStartDuration(1);

// Create axis object
$axis = new AmCategoryAxis();
$axis
//->setParseDates(true)
->setMinPeriod('ss')
->setAxisColor('#DADADA')
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
->setPosition('left')
->setTitle(TEXT_CUSTOMERS_COUNT);
/*->setAxisColor('#FF0000')
->setAxisThickness(2)
->setOffset(0)
->setStackType('3d')
->setTitle(TEXT_CUSTOMERS_COUNT)
->setAxisAlpha(0);*/

// Create graph for each store
/*$graph = new AmGraph();
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
$reader->addField(new PhpExt_Data_FieldConfigObject('total'));*/
$serialChart->addValueAxis(AmChartCallable::createCallable($valueAxis));
//->addGraph(AmChartCallable::createCallable($graph));

foreach ($stores as $storeData) {
	$color = random_color();

	$graph = new AmGraph();
	$graph
	->setTitle($storeData['text'])
	->setValueField($storeData['id'])
	->setFillAlphas(0.4);
	//->setBullet('round')
	//->setBulletBorderThickness(1)
	//->setHideBulletsCount(30)
	//->setLineColor($color)
	//->setBalloonText($storeData['text'] . ' - [[value]] ');
	$reader->addField(new PhpExt_Data_FieldConfigObject($storeData['id']));
	$serialChart->addGraph(AmChartCallable::createCallable($graph));
}

$customersChart->setChart($serialChart);

// -------------- PIE CHART
$customersChartByStoreChart = new PhpExt_Amchart();
$customersChartByStoreChart
->setChartName('customers-online-by-store')
->setId('customers-online-realtime-by-store')
->setIsRealTimeChart(true);

$reader = new PhpExt_Data_JsonReader();
$reader->setRoot("topics")
->setTotalProperty("totalCount");
$reader->addField(new PhpExt_Data_FieldConfigObject("store_name"));
$reader->addField(new PhpExt_Data_FieldConfigObject("store_total"));

$fromstore = new PhpExt_Data_Store();
$fromstore->setProxy(new PhpExt_Data_HttpProxy('chart.php?ChartType=customers_online_by_store'))
->setReader($reader);

$customersChartByStoreChart->setStore($fromstore);

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

$customersChartByStoreChart->setChart($pieChart);

// -------------- END PIE CHART

$columnPanel = new PhpExt_Panel();
$columnPanel->setLayout(new PhpExt_Layout_ColumnLayout())->setAutoWidth(true)->setBorder(false);

// Column for first chart
$firstColumn = new PhpExt_Panel();
$firstColumn->setLayout(new PhpExt_Layout_FitLayout())->setBorder(false);
$firstColumn->addItem($customersChart);

// Column for pie chart
$secondColumn = new PhpExt_Panel();
$secondColumn->setLayout(new PhpExt_Layout_FitLayout())->setBorder(false);
$secondColumn->addItem($customersChartByStoreChart);
$columnPanel->addItem($firstColumn, new PhpExt_Layout_ColumnLayoutData(0.70));
$columnPanel->addItem($secondColumn, new PhpExt_Layout_ColumnLayoutData(0.30));
$stat_customers_online_real_time->addItem($columnPanel);

$chartPanel->addItem($stat_customers_online_real_time);

$customers_online_table = new PhpExt_Panel();
$customers_online_table->setTitle(TEXT_CUSTOMERS_ONLINE_PREVIEW)
->setAutoScroll(false)
->setAutoWidth(true)
->setAutoHeight(true)
->setCssStyle('border:none;')
->setLayout(new PhpExt_Layout_FitLayout());

$customers_table_link = str_replace('xtAdmin/', '', $link_base) . 'plugins/xt_customers_online_tracking/pages/customers_online_table.php';
$customers_online_table->setHtml('<iframe height="500" width="100%" frameborder="0" src="'.$customers_table_link.'"></iframe>');
$chartPanel->addItem($customers_online_table);