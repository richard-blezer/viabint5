// Create user extensions namespace (Ext.ux)
Ext.namespace('Ext.ux');
 
/**
  * Ext.ux.IconCombo Extension Class
  *
  * @author Jozef Sakalos, aka Saki
  * @version 1.0
  *
  * @class Ext.ux.IconCombo
  * @extends Ext.form.ComboBox
  * @constructor
  * @param {Object} config Configuration options
  */
Ext.ux.IconCombo = function(config) {
 
    // call parent constructor
    Ext.ux.IconCombo.superclass.constructor.call(this, config);

    // What we do here is that we override default combo box item template with our own that makes use of the iconClsField
    // Well, we have nice icons when the list is open ...
    this.tpl = config.tpl ||
    '<tpl for="."><div class="x-combo-list-item">'
    + '<table><tbody><tr>'
    + '<td>'
    + '<div class="{' + this.iconClsField + '} x-icon-combo-icon"></div></td>'
    + '<td>{' + this.displayField + '}</td>'
    + '</tr></tbody></table>'
    + '</div></tpl>'
    ;
    
    // ... but we'd like to have also flag when it's closed
    // This code adds render event listener that adjusts styles of elements and adds div container for flag
    this.on({
        render:{scope:this, fn:function() {
            var wrap = this.el.up('div.x-form-field-wrap');
            this.wrap.applyStyles({position:'relative'});
            this.el.addClass('x-icon-combo-input');
            this.flag = Ext.DomHelper.append(wrap, {
                tag: 'div', style:'position:absolute'
            });
            this.flag.className = 'x-icon-combo-icon ' + this.iconClsDefault;
        }}
    });
    
}; // end of Ext.ux.IconCombo constructor
 
// extend
Ext.extend(Ext.ux.IconCombo, Ext.form.ComboBox, {
	
	// We're adding a function setIconCls and overriding the setValue  function. 
	// Of course, we want the original setValue to do its job so we call it first in our scope and 
	// then we call our setIconCls function. 
	
    setIconCls: function() {
        var rec = this.store.query(this.valueField, this.getValue()).itemAt(0);
        if(rec) {
            this.flag.className = 'x-icon-combo-icon ' + rec.get(this.iconClsField);
        }
    },
	 
    setValue: function(value) {
        Ext.ux.IconCombo.superclass.setValue.call(this, value);
        this.setIconCls();
    },
	 
	
}); // end of extend
 

/**
  * Ext.ux.IconCombo Tutorial
  * by Jozef Sakalos, aka Saki
  * http://extjs.com/learn/Tutorial:Extending_Ext_Class
  */
 
//reference local blank image
Ext.BLANK_IMAGE_URL = '../../resources/images/default/s.gif';

