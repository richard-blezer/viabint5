if (NV_AUTOCOMPLETE_USE_OWN_JQUERY == 'false') {
	var jqueryVersion = jQuery.fn.jquery.split('.');
	if (Number(jqueryVersion[1]) < 6) {
		alert("nv_autocomplete failed to load. Please install jQuery version 1.6.0 or set 'Use own jQuery version' to true!");
		throw new Error("nv_autocomplete failed to load. Please install jQuery version 1.6.0 or set 'Use own jQuery version' to true!");
	} else {
		nvJQuery = jQuery;
	}
} else {
	nvJQuery = jQuery.noConflict(true);
}
	
$(document).ready(function() {	
	nvJQuery(NV_AUTOCOMPLETE_SEARCH_INPUT).autocomplete({
		 delay: NV_AUTOCOMPLETE_DELAY,
		 minLength: NV_AUTOCOMPLETE_MIN_LENGTH,
		 source: function( request, response ) {
			 nvJQuery.ajax({
               url: base + "?page=nv_autocomplete",
               dataType: "json",
               data: {
                   keywords: nvJQuery(document.activeElement).val(),
               },
               success: function(data) {
            	   response(nvJQuery.map(data, function(item) {
						return {
							label: item.type + item.name,
							value: item.name,
							link: item.link
						};
					}));
               },
               error: function(request, status, error) {
            	   console.log(request.responseText);
               }
           });
		 },
		 select: function(event, ui) {
			 document.location.href = ui.item.link;
		},
	});
});