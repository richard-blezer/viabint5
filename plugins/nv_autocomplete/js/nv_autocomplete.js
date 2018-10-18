(function($) {
	$(document).ready(function() {
		$.widget( "custom.nvcomplete", $.ui.autocomplete, {
			_create: function() {
				this._super();
				this.widget().menu( "option", "items", "> :not(.ui-autocomplete-type)" );
			},
			_renderMenu: function( ul, items ) {
				var that = this,
				currentType = "";
				$.each( items, function( index, item ) {
				var li;
				if ( item.type != currentType ) {
					ul.append( "<li class='ui-autocomplete-type'>" + item.type + "</li>" );
					currentType = item.type;
				}
				li = that._renderItemData( ul, item );
				if ( item.type ) {
					li.attr( "aria-label", item.type + " : " + item.label );
				}
				});
			},
			_renderItem: function( ul, item ) {
				var text = item.name;
				var search = $(document.activeElement).val();
				var regex = new RegExp(search, 'gi');
				text = text.replace(regex, function(replace) {
					return $('<span>').addClass('ui-autocomplete-term').text(replace).prop('outerHTML');
				});
				return $("<li>").append($("<a>").html(text)).appendTo(ul);
			}
		});

		$(NV_AUTOCOMPLETE_SEARCH_INPUT).nvcomplete({
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
						response(data);
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
})(jQuery);
