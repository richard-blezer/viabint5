/**
 * Component used to display charts
 * 
 * @author Radoslav Vitanov
 */
Ext.ux.Amchart = Ext.extend(Ext.BoxComponent, {
	/**
	 * Chart config object at initiliazation time. After the initiliazation this variable holds
	 * refference to chart object used to draw the chart.
	 * @var object
	 */
	chart : {},
	
	/**
	 * Ext data store loader
	 */
	store : null,
	
	/**
	 * Name of the chart container. If not set it will be generated automaticly
	 */
	chartName : null,
	
	/**
	 * Flag identifying if the chart uses real time data
	 */
	isRealTimeChart : false,
	
	/**
	 * Private. Used by setInterval
	 */
	timeout : {},
	
	/**
	 * Width of the chart
	 */
	width: '100%',
	
	/**
	 * Height of the chart
	 */
	height: '400px',
	
	/**
	 * Event listeners that will be attached to chart object
	 */
	chartEvenetListeners : {},
	
	/**
	 * Chart template where it will be rendered
	 */
	template : null,
	
	/**
	 * Filter Ids wich values this chat should use when filter action is performed
	 */
	filterWidgetsNames : [],
	
	/**
	 * Filter class
	 */
	cls : false,
	
	/**
	 * Refresh interval for setTimeout in miliseconds
	 */
	refreshInterval : 2000,
	
	/**
	 * Autocreate
	 */
	defaultAutoCreate : {tag: "div"},
	
	/**
	 * Initiliazation function
	 */
    initComponent: function(){
    	// Call parent constructor
		Ext.ux.Amchart.superclass.initComponent.call(this);
		// Create chart object depending of the type of the chart
		this.initChartObject();
		
		// Pre defined filters
		this.addListener('dataloaded', this.onDataLoad, this);
		this.addListener('filterChanged', this.onFilterChanged, this);
		this.addListener('hide', this.onHideEvent, this);
		
		var self = this;
		// Assign data load event
        this.store.on('load', function(store, records, autoload) {
        	self.fireEvent("dataloaded");
		});
	},
	
	/**
	 * Callback fired whenever a filter is performed
	 */
	onFilterChanged : function() {
		var filterParams = {};
		
		// Trigger event listeners
		this.fireEvent("beforeFilterRequest");
		// Get filter values and pass them to http proxy
		for (var i = 0; i < this.filterWidgetsNames.length; i++) {
			try {
				var cmpValue = Ext.getCmp(this.filterWidgetsNames[i]).getValue();
				// Workaround for date fields
				if (this.filterWidgetsNames[i].substr(-4) == 'Date') {
					cmpValue = cmpValue.format('Y-m-d');
				}
				filterParams[this.filterWidgetsNames[i]] = cmpValue;
			} catch (e) {
				// Just continue
			}
			
		}
		// Set the filter params so they can be used in the request
		this.store.baseParams = filterParams;
		// Reload the data
		this.store.load();
	},
	
	/**
	 * On render callback
	 */
	onRender: function(ct, position) {
		if (!this.template) {
			if (!this.chartName) {
				this.chartName = "chart-" + new Date().getTime();
			}
			var filter = '<div id="{0}"></div>';
			var str = '<div id="{0}" class="x-panel-body x-panel-body-noheader x-panel-body-noborder x-amchart" style="width: {1}; height: {2};"></div>';
			this.template = new Ext.Template(String.format(filter, this.chartName + "-filter") + String.format(str, this.chartName, this.width, this.height));
		}
		
		if(position){
			chart = this.template.insertBefore(position, '', true);
		}else{
			chart = this.template.append(ct, '', true);
		}

		this.el = chart;
		
		// Call parent
		Ext.ux.Amchart.superclass.onRender.call(this, ct, position);
		
		// Loader mask bount to data retriever
		if (!this.isRealTimeChart) {
			var mask = new Ext.LoadMask(this.el, {useMsg: false, store:this.store});
		} else {
			var self = this;
			this.timeout[this.chartName] = window.setInterval(function(){
				self.invokeDataReload();
			}, this.refreshInterval);
		}
		// Load data
		this.store.load();
	},
	
	invokeDataReload : function() {
		this.store.load();
	},
	
    // Generate some random data, quite different range for test purposes only
	generateChartData : function () {
        var firstDate = new Date(),
        	chartData = [];
        firstDate.setDate(firstDate.getDate() - 50);

        for (var i = 0; i < 50; i++) {
            // we create date objects here. In your data, you can have date strings
            // and then set format of your dates using chart.dataDateFormat property,
            // however when possible, use date objects, as this will speed up chart rendering.
            var newDate = new Date(firstDate);
            newDate.setDate(newDate.getDate() + i);

            var visits = Math.round(Math.random() * 40) + 100;
            var hits = Math.round(Math.random() * 80) + 500;
            var views = Math.round(Math.random() * 6000);

            chartData.push({
                date: newDate,
                visits: visits,
                hits: hits,
                views: views
            });
        }
        
        return chartData;
    },
    
    /**
     * Sets properties of config object to another
     */
    setConfigData : function(configObj, obj) {
    	for (var i in configObj) {
    		if ((typeof configObj[i] == "object") && (configObj[i] instanceof Ext.ux.AmchartConfigObject) && (typeof configObj[i].methodParams != 'undefined')) {
    			eval("var paramObject = new " + configObj[i].methodParams[0].className + "();");
    			
    			paramObject = this.initiliazeObject(paramObject, configObj[i].methodParams[0].config);
    			obj[i] = paramObject;
    			continue;
    		}
    		obj[i] = configObj[i];
    	}
    	return obj;
    },
    
    /**
     * Object initiliazator. Assigns all properties from config to object,
     * creating subobhects recrusively.
     */
    initiliazeObject : function(object, config) {
    	
    	// Check for invokable methods in the config object, and if tehere are one fire them from the context of object
    	if ((typeof config.invokableMethods != 'undefined') && (config.invokableMethods.length > 0)) {
    		for (var i = 0; i < config.invokableMethods.length; i++) {
    			var executable = config.invokableMethods[i],
    				// Actual method
    				method = executable.methodName,
    				// Method params
    				params = executable.methodParams;
    			
    			// If no params passed just call method
    			if (params.length == 0) {
    				object[method]();
    				continue;
    			}
    			
    			// Prepare params
    			var preparedParams = [];
    			for (var c = 0; c < params.length; c++) {
    				var param = params[c];
    				// If param is not another object just push it to the array of params
    				if (param.className.length == 0) {
    					preparedParams.push(param.config);
    					continue;
    				}
    				// If the param is another object create it
    				eval("var paramObject = new " + param.className + "();");
    				// Recrusevely set params to the newly created object
    				paramObject = this.initiliazeObject(paramObject, param.config);
    				// ... And the push it to the queue of params
    				preparedParams.push(paramObject);
    			}
    			//  We are ready to call object emthod with set of params
    			object[method].apply(object, preparedParams);
    		}
    	}
    	// After method calls just set object properties from config
    	object = this.setConfigData(config, object);
    	// And return it
    	return object;
    },
    
    /**
     * Initiliaze chart object depending on chart type
     */
    initChartObject : function() {
    	config = this.chart;
    	chart = eval("new " + config.amChartType + "();");
    	// Free some memory
    	delete config.amChartType;
    	
    	this.chart = chart;
    	// Init listeners
    	this.initChartListeners(config);
    	// Init chart properties
    	this.initiliazeObject(this.chart, config);
    },
    
    /**
     * Initiliaze chart listeners
     */
    initChartListeners : function(config) {
    	if (typeof(config.listeners) == 'object') {
    		var self = this;
    		for (var eventName in config.listeners) {
    			var callback = config.listeners[eventName].fn;
    			this.chart.addListener(eventName, function() {
    				callback(self.chart);
    			});
    		}
    		// Free some memory
    		delete config.listeners;
    	}
    },
	
    /**
     * Callback used when data from store is loaded.
     */
	onDataLoad : function(chart, store) {
		
		if (!this.isRealTimeChart || this.chart instanceof AmCharts.AmPieChart) {
			data = [];
		} else {
			data = this.chart.dataProvider || [];
			if (data.length == 0) {
				data = this.buildTimeline(this.store.data.items[0].data);
			} else {
				data.shift();
			}
		}
		//console.log(data)
		for (var i in this.store.data.items) {
			// Skip non numeric keys
			if (!(i == parseInt(i))) 
				continue;
			//console.log(this.store.data.items[i].data, typeof this.store.data.items[i].data)
			data.push(this.store.data.items[i].data);
		}
		//console.log(data);
		this.chart.dataProvider = data;
		
		this.chart.write(this.chartName);
		
		this.chart.validateData();
		if (!this.isRealTimeChart) {
			this.chart.animateAgain();
		}
	},
	
	onDestroy : function(){
		this.chart.clear();
		// Call parent
		Ext.ux.Amchart.superclass.onDestroy.call();
		window.clearInterval(this.timeout[this.chartName]);
	},
	
	onHideEvent : function() {
		this.chart.clear();
	},
	
	buildTimeline : function (obj) {
		var data = [],
			entriesToGenerate = 3600/(this.refreshInterval/1000), // Find how many entries will have in one hour (3600 seconds)
			categoryField = this.chart.categoryField,
			now = new Date();
		
		for (var i = 0; i < entriesToGenerate; i++) {
			var tmp = {};
			for (var c in obj) {
				if (!obj.hasOwnProperty(c)) {
					continue;
				}
				if (c !== categoryField) {
					tmp[c] = "0";
				} else {
					now.setSeconds(now.getSeconds()-this.refreshInterval/1000);
					tmp[c] = now.getHours() + ':' + (now.getMinutes()<10?'0':'') + now.getMinutes() + ':' + (now.getSeconds()<10?'0':'') + now.getSeconds(); 
				}
			}
			data.unshift(tmp);
		}
		return data;
	}
});

/**
 * Executable object class declaration
 */
Ext.ux.AmchartExecutableObject = function(config) {
	this.className = configClassName ? configClassName : '';
	this.config = config.config ? config.config : {};
};

/**
 * Config object helper. Used to set config values to amchart objects
 */
Ext.ux.AmchartConfigObject = function(values) {
	for (var i in values) {
		this[i] = values[i];
	}
};
Ext.reg("ExtAmchart", Ext.ux.Amchart);