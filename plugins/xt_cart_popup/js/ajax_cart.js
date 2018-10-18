
function showCartPopup(id)
{
	var wid = $(document).width();
	
	
	var popup_width = parseInt((parseInt(wid)/2) - 300);
	
	$('#cart_ajax_box').css({'left':  popup_width+'px'});
	$('#loader_white').show();
	$('#cart_ajax_box').fadeIn('slow');
	if (id >0) {
		$('#ajax_cart_row_'+id).addClass('active');
		setTimeout(function(){
			//console.log($('#ajax_cart_row_'+id).offset().top);
			$('#ajax_cart_products').animate({
				scrollTop: ($('#ajax_cart_row_'+id).offset().top - 250)
			}, 100)}, 500);
	}
	
}

function hideCartPopup()
{
	$('#loader_white').hide();
	$('#cart_ajax_box').fadeOut('slow' );
	
}
