$(document).ready(function() {	
	$(NV_AUTOCOMPLETE_SEARCH_INPUT).autocomplete({
		 delay: NV_AUTOCOMPLETE_DELAY,
		 minLength: NV_AUTOCOMPLETE_MIN_LENGTH,
		 source: function( request, response ) {
			 $.ajax({
               url: base + "?page=nv_autocomplete",
               dataType: "json",
               data: {
                   keywords: $(document.activeElement).val(),
               },
               success: function(data) {
            	   response($.map(data, function(item) {
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