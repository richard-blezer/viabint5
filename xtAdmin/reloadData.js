var fields_to_hide = '';

function CallOnLoad(id){
	if (id!=''){
		var gh=Ext.getCmp('node_product_'+id);
	}

	if (gh) {
		setTimeout(function(){
			jQuery('#node_product_'+id+' input').each(
			function(){ 
				var input = jQuery(this);
				if (input.attr('name')){
					var spl = input.attr('name').split("reload_st_store");
					if (spl.length>1){
						if (spl[1]){
							var expl = spl[1].toString().split("_");
							OverLoadata(input.attr('id'),expl[1],expl[0],id,'');
						}
					}
				}
			}
		);
		
		}, 5000);
	}
}

function OverLoadata( id,lang,store_id,tab_id,msg){
	var gh=Ext.getCmp('node_product_'+tab_id);
	var show_fields = ["product_products_description_","product_products_short_description_","product_products_name_","meta_title_",
	                   "product_meta_description_","product_meta_keywords_","product_products_keywords_",
	                   "product_products_url_","product_meta_title_","wordcouter_product_meta_title_",
	                   "wordcouter_product_meta_description_","wordcouter_product_meta_keywords_"];
				
	if (gh) {
		
		if (fields_to_hide==''){
			var conn = new Ext.data.Connection();
	         conn.request({
	         url: 'adminHandler.php',
	         method:'GET',
	         params: {  pg:             'returnProductDescFields',
			            load_section:   'product'
	                 },
	         success: function(responseObject) {
	         		  var s= JSON.parse(responseObject.responseText);
						
						var fields = s.data.toString().split(",");
						fields_to_hide='';
						fields_to_hide = fields;
	                  }
	         });
	         
	         if (fields_to_hide=='') fields_to_hide = show_fields;
		}             
		if ((msg!='') && ((jQuery("#"+id).val()>0))){
			Ext.Msg.alert('Update', msg);
		}
		if ((jQuery("#"+id).val()>0)&&(jQuery("#"+id).val()!=store_id)){
			for(var i=0;i<fields_to_hide.length;i++){
				jQuery("#x-form-el-"+fields_to_hide[i]+"store"+store_id+'_'+lang+tab_id).css("display", "none");
				if (jQuery("label[for='"+fields_to_hide[i]+"store"+store_id+'_'+lang+tab_id+"']"))
					jQuery("label[for='"+fields_to_hide[i]+"store"+store_id+'_'+lang+tab_id+"']").css("display", "none");
			}
		}else{
			for(var i=0;i<fields_to_hide.length;i++){

				jQuery("#x-form-el-"+fields_to_hide[i]+"store"+store_id+'_'+lang+tab_id).css("display", "block");
				if (fields_to_hide[i].indexOf('wordcouter_')==-1)
					jQuery("label[for='"+fields_to_hide[i]+"store"+store_id+'_'+lang+tab_id+"']").css("display", "block");
			}
		}
		
	}
}

