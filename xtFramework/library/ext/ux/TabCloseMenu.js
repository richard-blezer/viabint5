/*
 * Ext JS Library 2.0.1
 * Copyright(c) 2006-2008, Ext JS, LLC.
 * licensing@extjs.com
 *
 * http://extjs.com/license
 */


// Very simple plugin for adding a close context menu to tabs

Ext.ux.TabCloseMenu = function(){
    var tabs, menu, ctxItem;
    this.init = function(tp){
        tabs = tp;
        tabs.on('contextmenu', onContextMenu);
    }

    function onContextMenu(ts, item, e){
        if(!menu){ // create context menu on first right click
            menu = new Ext.menu.Menu([
            {
                id: tabs.id + '-refresh',
                text: 'Refesh Tab',
                icon: 'images/icons/tab.png',
                handler : function(){
                    if (ctxItem.getXType() == 'iframepanel') {
                      ctxItem.setSrc();
                    } else if (ctxItem.getXType() == 'panel') {
					  ctxItem.getUpdater().refresh();
					  var expl = ctxItem.id.split("node_product_");
					  if (expl.length>1){
					  	CallOnLoad(expl[expl.length-1]);
					  }
					}
                }
            },{            	
                id: tabs.id + '-close',
                text: 'Close Tab',
                icon: 'images/icons/tab_delete.png',
                handler : function(){
                    tabs.remove(ctxItem);
                }
            },{
                id: tabs.id + '-close-others',
                text: 'Close Other Tabs',
                icon: 'images/icons/tab_go.png',
                handler : function(){
                    tabs.items.each(function(item){
                        if(item.closable && item != ctxItem){
                            tabs.remove(item);
                        }
                    });
                }
            }]);
        }
        ctxItem = item;
        var items = menu.items;
        items.get(tabs.id + '-close').setDisabled(!item.closable);
        var disableOthers = true;
        tabs.items.each(function(){
            if(this != item && this.closable){
                disableOthers = false;
                return false;
            }
        });
        items.get(tabs.id + '-close-others').setDisabled(disableOthers);
        menu.showAt(e.getPoint());
    }
};