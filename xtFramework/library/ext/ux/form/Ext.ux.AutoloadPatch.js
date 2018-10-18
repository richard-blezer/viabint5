Ext.form.ComboBox.prototype.initComponent_old = Ext.form.ComboBox.prototype.initComponent;
Ext.form.ComboBox.prototype.initComponent = function(){
    var val = this.value;
    if( this.autoLoad ) {
        this.mode = 'local';    // to be able to load store with store.load
    }
    this.initComponent_old();
    this.on( 'render', function(pnl) {
      if( pnl.store.autoLoad) {
          pnl.store.on( 'load', function() {
            if( typeof(val) == 'undefined' ) {
              var val1 = pnl.store.getAt( 0 );

              if( val1 )
                pnl.setValue( val1.get(pnl.valueField) );
            } else {
              this.setValue( val );
            }
          }, this, {single: true} );      // set values after loading
          //pnl.store.load( {options: {params: {start:0, limit: 999}}} );
      }
    });

};