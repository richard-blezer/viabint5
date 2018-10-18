
Ext.override(Ext.Panel, {
      
	onCollapse : function(){
			this[this.collapseEl].hide();
			this.afterCollapse();
		      
	},
	afterCollapse : function(){
		this[this.collapseEl].applyStyles({position: 'absolute', top: '-10000px', left: '-10000px'});
		this.collapsed = true;
		this.el.addClass(this.collapsedCls);
		this.afterEffect();
		this.fireEvent('collapse', this);
               
               
	},
	onExpand : function(){
                       
			this[this.collapseEl].show();
			this.afterExpand();
		
	},
        afterExpand : function() {
        this[this.collapseEl].applyStyles({position: '', top: '', left: ''});
        this.collapsed = false;
        this.afterEffect();
        this.fireEvent('expand', this);
    }
});

